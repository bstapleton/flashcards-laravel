<?php

namespace App\Services;

use App\Enums\Correctness;
use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Exceptions\AnswerMismatchException;
use App\Helpers\Score;
use App\Models\Attempt;
use App\Models\Flashcard;
use App\Models\GivenAnswer;
use App\Models\Scorecard;
use App\Models\Tag;
use App\Models\User;
use App\Repositories\AttemptRepository;
use App\Repositories\FlashcardRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\UnauthorizedException;

class FlashcardService
{
    protected Flashcard $flashcard;
    protected AttemptService $attemptService;

    public function __construct(protected FlashcardRepository $repository)
    {
        $this->attemptService = new AttemptService(new AttemptRepository());
    }

    public function all()
    {
        if (!Gate::authorize('list', Flashcard::class)) {
            throw new UnauthorizedException();
        }

        return $this->repository->all();
    }

    public function show(int $id)
    {
        $flashcard = $this->repository->show($id);

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

        return $this->repository->store($data);
    }

    public function update(array $data, int $id)
    {
        $flashcard = $this->repository->show($id);

        if (!Gate::authorize('update', $flashcard)) {
            throw new UnauthorizedException();
        }

        return $this->repository->update($data, $id);
    }

    public function destroy(int $id): void
    {
        $flashcard = $this->repository->show($id);

        if (!Gate::authorize('delete', $flashcard)) {
            throw new UnauthorizedException();
        }

        $this->repository->destroy($id);
    }

    public function buried()
    {
        if (!Gate::authorize('list', Flashcard::class)) {
            throw new UnauthorizedException();
        }

        return $this->repository->buried();
    }

    public function alive()
    {
        if (!Gate::authorize('list', Flashcard::class)) {
            throw new UnauthorizedException();
        }

        return $this->repository->alive();
    }

    public function random()
    {
        if (!Gate::authorize('list', Flashcard::class)) {
            throw new UnauthorizedException();
        }

        return $this->repository->random();
    }

    public function revive(int $id)
    {
        $flashcard = $this->repository->show($id);

        if (!Gate::authorize('revive', $flashcard)) {
            throw new UnauthorizedException();
        }

        return $this->repository->revive($id);
    }

    /**
     * @throws AnswerMismatchException
     */
    public function answer(int $id, array $answers, User $user)
    {
        $this->flashcard = $this->repository->show($id);

        if (!Gate::authorize('answer', $this->flashcard)) {
            throw new UnauthorizedException();
        }

        if (in_array($this->flashcard->type, [QuestionType::SINGLE, QuestionType::MULTIPLE])) {
            $correctness = $this->calculateCorrectness($answers);
        } elseif ($this->flashcard->type === QuestionType::STATEMENT) {
            $providedAnswer = last($answers);
            $correctness = $this->calculateCorrectness(null, $providedAnswer);

            $trueAnswer = new GivenAnswer();
            $trueAnswer->setText('True');
            $trueAnswer->setIsCorrect($this->flashcard->is_true);
            $trueAnswer->setWasSelected((bool)$providedAnswer === true);

            $falseAnswer = new GivenAnswer();
            $falseAnswer->setText('False');
            $falseAnswer->setIsCorrect(!$this->flashcard->is_true);
            $falseAnswer->setWasSelected((bool)$providedAnswer === false);

            $givenAnswers = collect([$trueAnswer, $falseAnswer]);
        }

        $this->flashcard->last_seen_at = Carbon::now();

        $score = new Score();
        $pointsEarned = $score->getScore($this->flashcard->type, $correctness, $this->flashcard->difficulty);

        $attempt = $this->attemptService->store([
            'question' => $this->flashcard->text,
            'question_type' => $this->flashcard->type,
            'answers' => $givenAnswers ?? collect($this->flashcard->answers->map(function ($answer) use ($answers) {
                $givenAnswer = new GivenAnswer();
                $givenAnswer->setId($answer->id);
                $givenAnswer->setIsCorrect($answer->is_correct);
                $givenAnswer->setWasSelected(in_array($answer->id, $answers));
                $givenAnswer->setText($answer->text);
                $givenAnswer->setExplanation($answer->explanation);

                return $givenAnswer;
            })),
            'tags' => implode(',', $this->flashcard->tags->map(function (Tag $tag) {
                return $tag->name;
            })->toArray()),
            'difficulty' => $this->flashcard->difficulty,
            'answered_at' => now(),
            'correctness' => $correctness,
            'points_earned' => $pointsEarned
        ]);

        $scorecard = new Scorecard($attempt->toArray());

        // Don't create an attempt if it's already buried
        if ($this->flashcard->difficulty !== Difficulty::BURIED) {
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
            $this->resetDifficulty();
        } else {
            $user->adjustPoints($pointsEarned);
            $this->increaseDifficulty();
        }

        $this->save();
        $scorecard->setNewDifficulty($this->flashcard->difficulty);

        $scorecard->setEligibleAt(Carbon::parse($this->flashcard->eligible_at));
        $scorecard->setTotalScore($user->points);
        $scorecard->setExplanation($this->flashcard->explanation);

        return $scorecard;
    }

    public function attachTag(int $id, int $tagId)
    {
        if (!Gate::authorize('attachTag', Flashcard::class)) {
            throw new UnauthorizedException();
        }

        return $this->repository->attachTag($id, $tagId);
    }

    public function detachTag(int $id, int $tagId)
    {
        if (!Gate::authorize('detachTag', Flashcard::class)) {
            throw new UnauthorizedException();
        }

        return $this->repository->detachTag($id, $tagId);
    }

    public function setFlashcard(Flashcard $flashcard): void
    {
        $this->flashcard = $flashcard;
    }

    /**
     * Handles if none of the answer IDs passed by the consumer are valid for the flashcard
     *
     * @throws AnswerMismatchException
     */
    public function validateAnswers(array $validatedAnswers): array
    {
        if (0 === count($validatedAnswers)) {
            throw new AnswerMismatchException();
        }

        return $validatedAnswers;
    }

    /**
     * Filters out any answer IDs that are not valid for the flashcard
     *
     * @param array $answers
     * @return array
     */
    public function filterValidAnswers(array $answers): array
    {
        $possibleAnswers = $this->flashcard->answers->pluck('id')->toArray();

        return array_values(array_intersect($answers, $possibleAnswers));
    }

    /**
     * For statements, you can either be completely correct or not at all
     * For multiple-choice, single-correct, you can either be completely correct or not at all
     * For multiple-choice, multiple-correct, you can be completely correct (e.g. 3/3), partially correct (e.g 2/3), or
     * not at all (e.g. 0/3). As long as AT LEAST ONE answer is correct, you're partially correct, even if all the
     * others you selected are incorrect.
     *
     * @param array|null $answers
     * @param bool|null $isTrue
     * @return Correctness
     */
    public function calculateCorrectness(?array $answers = null, ?bool $isTrue = null): Correctness
    {
        switch ($this->flashcard->type) {
            case QuestionType::SINGLE:
                return $this->flashcard->correct_answer->id === last($answers) && count($answers) === 1
                    ? Correctness::COMPLETE
                    : Correctness::NONE;
            case QuestionType::MULTIPLE:
                $correctAnswers = $this->flashcard->correct_answers;
                $correctSuppliedAnswers = array_intersect($correctAnswers->pluck('id')->toArray(), $answers);

                if (count($correctSuppliedAnswers) && $correctAnswers->count() === count($correctSuppliedAnswers) && count($answers) === $correctAnswers->count()) {
                    return Correctness::COMPLETE;
                }

                if (count($correctSuppliedAnswers) === 0) {
                    return Correctness::NONE;
                }

                return Correctness::PARTIAL;
            default:
                return $isTrue === $this->flashcard->is_true ? Correctness::COMPLETE : Correctness::NONE;
        }
    }

    /**
     * Push the flashcard from the current difficulty to the next hardest until it's buried
     *
     * @return Difficulty
     */
    public function increaseDifficulty(): Difficulty
    {
        switch ($this->flashcard->difficulty) {
            case Difficulty::EASY:
                $this->setDifficulty(Difficulty::MEDIUM);
                break;
            case Difficulty::MEDIUM:
                $this->setDifficulty(Difficulty::HARD);
                break;
            case Difficulty::HARD:
                $this->setDifficulty(Difficulty::BURIED);
                break;
            case Difficulty::BURIED:
                break;
        }

        return $this->flashcard->difficulty;
    }

    public function setDifficulty(Difficulty $difficulty): void
    {
        $this->flashcard->difficulty = $difficulty;
        $this->save();
    }

    public function setEligibleAt(Carbon $carbon): void
    {
        $this->flashcard->eligible_at = $carbon;
        $this->save();
    }

    /**
     * They got it wrong, push it back to easy difficulty
     *
     * @return Difficulty
     */
    public function resetDifficulty(): Difficulty
    {
        $this->flashcard->difficulty = Difficulty::EASY;
        $this->save();

        return $this->flashcard->difficulty;
    }

    public function save(): void
    {
        $this->flashcard->save();
    }

    public function createAttempt(Correctness $correctness, Difficulty $difficulty, int $points, array $answers = []): void
    {
        Attempt::create([
            'user_id' => $this->flashcard->user_id,
            'question' => $this->flashcard->text,
            'question_type' => $this->flashcard->type->value,
            'answers_given' => $answers,
            'tags' => $this->flashcard->tags->pluck('name')->toArray(),
            'correctness' => $correctness,
            'difficulty' => $difficulty,
            'points_earned' => $points,
            'answered_at' => now(),
        ]);
    }
}
