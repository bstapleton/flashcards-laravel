<?php

namespace App\Services;

use App\Enums\Correctness;
use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Exceptions\NoEligibleQuestionsException;
use App\Helpers\Score;
use App\Models\Flashcard;
use App\Models\Scorecard;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\UnauthorizedException;

class FlashcardService
{
    protected AttemptService $attemptService;

    public function __construct()
    {
        $this->attemptService = new AttemptService();
    }

    public function all()
    {
        if (!Gate::authorize('list', Flashcard::class)) {
            throw new UnauthorizedException();
        }

        return Flashcard::where('user_id', Auth::id())
            ->orderBy('created_at');
    }

    public function show(Flashcard $flashcard)
    {
        if (!Gate::authorize('show', $flashcard)) {
            throw new UnauthorizedException();
        }

        return $flashcard;
    }

    public function store(array $data)
    {
        if (!Gate::authorize('store', Flashcard::class)) {
            throw new UnauthorizedException();
        }

        return Flashcard::create([
            'text' => $data['text'],
            'is_true' => $data['is_true'] ?? null,
            'explanation' => $data['explanation'] ?? null,
        ]);
    }

    public function update(array $data, Flashcard $flashcard)
    {
        if (!Gate::authorize('update', $flashcard)) {
            throw new UnauthorizedException();
        }

        $flashcard->update($data);

        return $flashcard;
    }

    public function destroy(Flashcard $flashcard): void
    {
        if (!Gate::authorize('delete', $flashcard)) {
            throw new UnauthorizedException();
        }

        $flashcard->delete();
    }

    public function buried()
    {
        if (!Gate::authorize('list', Flashcard::class)) {
            throw new UnauthorizedException();
        }

        return Flashcard::buried();
    }

    public function alive()
    {
        if (!Gate::authorize('list', Flashcard::class)) {
            throw new UnauthorizedException();
        }

        return Flashcard::alive();
    }

    public function random()
    {
        if (!Gate::authorize('list', Flashcard::class)) {
            throw new UnauthorizedException();
        }

        $flashcards = Flashcard::inRandomOrder()->where('user_id', Auth::id())->get()->filter(function ($flashcard) {
            if ($flashcard->eligible_at->lessThan(now()) || !$flashcard->last_seen_at) {
                return true;
            }

            return false;
        });

        if (!$flashcards->count()) {
            // Consumer has no eligible flashcards
            throw new NoEligibleQuestionsException();
        }

        return $flashcards->first();
    }

    public function revive(Flashcard $flashcard): Flashcard
    {
        if (!Gate::authorize('revive', $flashcard)) {
            throw new UnauthorizedException();
        }

        $flashcard->update([
            'difficulty' => Difficulty::EASY,
        ]);

        return $flashcard;
    }

    /**
     * @param Flashcard $flashcard
     * @param array $answers
     * @param User $user
     * @return Scorecard
     */
    public function answer(Flashcard $flashcard, array $answers, User $user): Scorecard
    {
        if (!Gate::authorize('answer', $flashcard)) {
            throw new UnauthorizedException();
        }

        if ($flashcard->type === QuestionType::STATEMENT) {
            $providedAnswer = last($answers);
            $correctness = $this->calculateCorrectness($flashcard, null, $providedAnswer);

            $trueAnswer = $this->attemptService->createGivenAnswer(
                'True',
                $flashcard->is_true,
                (bool)$providedAnswer === true
            );
            $falseAnswer = $this->attemptService->createGivenAnswer(
                'False',
                !$flashcard->is_true,
                (bool)$providedAnswer === false
            );

            $givenAnswers = collect([$trueAnswer, $falseAnswer]);
        } else {
            $correctness = $this->calculateCorrectness($flashcard, $answers);
        }

        $flashcard->last_seen_at = Carbon::now();

        $score = new Score();
        $pointsEarned = $score->getScore($flashcard->type, $correctness, $flashcard->difficulty);

        $attempt = $this->attemptService->store([
            'question' => $flashcard->text,
            'question_type' => $flashcard->type,
            'answers' => $givenAnswers ?? collect($flashcard->answers->map(function ($answer) use ($answers) {
                return $this->attemptService->createGivenAnswer(
                    $answer->text,
                    $answer->is_correct,
                    in_array($answer->id, $answers),
                    $answer->id,
                    $answer->explanation
                );
            })),
            'tags' => implode(',', $flashcard->tags->map(function (Tag $tag) {
                return $tag->name;
            })->toArray()),
            'difficulty' => $flashcard->difficulty,
            'answered_at' => now(),
            'correctness' => $correctness,
            'points_earned' => $pointsEarned
        ]);

        $scorecard = new Scorecard($attempt->toArray());

        // Don't create an attempt if it's already buried
        if ($flashcard->difficulty !== Difficulty::BURIED) {
            $attempt->answers = $attempt->answers->map(function ($answer) {
                return [
                    'text' => $answer->getText(),
                    'is_correct' => $answer->getIsCorrect(),
                    'was_selected' => $answer->getWasSelected(),
                ];
            });

            $attempt->save();
        }

        if ($correctness !== Correctness::COMPLETE) {
            $this->resetDifficulty($flashcard);
        } else {
            $user->adjustPoints($pointsEarned);
            $this->increaseDifficulty($flashcard);
        }

        $flashcard->save();
        $scorecard->setNewDifficulty($flashcard->difficulty);

        $scorecard->setEligibleAt(Carbon::parse($flashcard->eligible_at));
        $scorecard->setTotalScore($user->points);
        $scorecard->setExplanation($flashcard->explanation);

        return $scorecard;
    }

    public function attachTag(Flashcard $flashcard, Tag $tag)
    {
        if (!Gate::authorize('attachTag', Flashcard::class)) {
            throw new UnauthorizedException();
        }

        $flashcard->tags()->attach($tag);

        return $flashcard;
    }

    public function detachTag(Flashcard $flashcard, Tag $tag)
    {
        if (!Gate::authorize('detachTag', Flashcard::class)) {
            throw new UnauthorizedException();
        }

        $flashcard->tags()->detach($tag);

        return $flashcard;
    }

    /**
     * Filters out any answer IDs that are not valid for the flashcard
     *
     * @param Flashcard $flashcard
     * @param array $answers
     * @return array
     */
    public function filterValidAnswers(Flashcard $flashcard, array $answers): array
    {
        $possibleAnswers = $flashcard->answers->pluck('id')->toArray();

        return array_values(array_intersect($answers, $possibleAnswers));
    }

    /**
     * For statements, you can either be completely correct or not at all
     * For multiple-choice, single-correct, you can either be completely correct or not at all
     * For multiple-choice, multiple-correct, you can be completely correct (e.g. 3/3), partially correct (e.g 2/3), or
     * not at all (e.g. 0/3). As long as AT LEAST ONE answer is correct, you're partially correct, even if all the
     * others you selected are incorrect.
     *
     * @param Flashcard $flashcard
     * @param array|null $answers
     * @param bool|null $isTrue
     * @return Correctness
     */
    public function calculateCorrectness(Flashcard $flashcard, ?array $answers = null, ?bool $isTrue = null): Correctness
    {
        switch ($flashcard->type) {
            case QuestionType::SINGLE:
                return $flashcard->correct_answer->id === last($answers) && count($answers) === 1
                    ? Correctness::COMPLETE
                    : Correctness::NONE;
            case QuestionType::MULTIPLE:
                $correctAnswers = $flashcard->correct_answers;
                $correctSuppliedAnswers = array_intersect($correctAnswers->pluck('id')->toArray(), $answers);

                if (count($correctSuppliedAnswers) && $correctAnswers->count() === count($correctSuppliedAnswers) && count($answers) === $correctAnswers->count()) {
                    return Correctness::COMPLETE;
                }

                if (count($correctSuppliedAnswers) === 0) {
                    return Correctness::NONE;
                }

                return Correctness::PARTIAL;
            default:
                return $isTrue === $flashcard->is_true ? Correctness::COMPLETE : Correctness::NONE;
        }
    }

    /**
     * Push the flashcard from the current difficulty to the next hardest until it's buried
     *
     * @param Flashcard $flashcard
     * @return Difficulty
     */
    public function increaseDifficulty(Flashcard $flashcard): Difficulty
    {
        switch ($flashcard->difficulty) {
            case Difficulty::EASY:
                $this->setDifficulty($flashcard, Difficulty::MEDIUM);
                break;
            case Difficulty::MEDIUM:
                $this->setDifficulty($flashcard, Difficulty::HARD);
                break;
            case Difficulty::HARD:
                $this->setDifficulty($flashcard, Difficulty::BURIED);
                break;
            case Difficulty::BURIED:
                break;
        }

        return $flashcard->difficulty;
    }

    /**
     * @param Flashcard $flashcard
     * @param Difficulty $difficulty
     * @return void
     */
    public function setDifficulty(Flashcard $flashcard, Difficulty $difficulty): void
    {
        $flashcard->difficulty = $difficulty;
        $flashcard->save();
    }

    /**
     * @param Flashcard $flashcard
     * @param Carbon $carbon
     * @return void
     */
    public function setEligibleAt(Flashcard $flashcard, Carbon $carbon): void
    {
        $flashcard->eligible_at = $carbon;
        $flashcard->save();
    }

    /**
     * They got it wrong, push it back to easy difficulty
     *
     * @param Flashcard $flashcard
     * @return Difficulty
     */
    public function resetDifficulty(Flashcard $flashcard): Difficulty
    {
        $flashcard->difficulty = Difficulty::EASY;
        $flashcard->save();

        return $flashcard->difficulty;
    }
}
