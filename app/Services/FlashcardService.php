<?php

namespace App\Services;

use App\Enums\Correctness;
use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Enums\Status;
use App\Enums\TagColour;
use App\Exceptions\DraftQuestionsCannotChangeStatusException;
use App\Exceptions\FreeUserFlashcardLimitException;
use App\Exceptions\LessThanOneCorrectAnswerException;
use App\Exceptions\NoEligibleQuestionsException;
use App\Exceptions\UndeterminedQuestionTypeException;
use App\Helpers\Score;
use App\Models\Answer;
use App\Models\Flashcard;
use App\Models\Scorecard;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\UnauthorizedException;

class FlashcardService
{
    protected AttemptService $attemptService;

    public function __construct()
    {
        $this->attemptService = new AttemptService;
    }

    public function all()
    {
        if (! Gate::authorize('list', Flashcard::class)) {
            throw new UnauthorizedException;
        }

        return Flashcard::currentUser()
            ->orderBy('created_at');
    }

    public function show(Flashcard $flashcard)
    {
        if (! Gate::authorize('show', $flashcard)) {
            throw new UnauthorizedException;
        }

        return $flashcard;
    }

    /**
     * @throws UndeterminedQuestionTypeException
     * @throws LessThanOneCorrectAnswerException
     */
    public function store(array $data)
    {
        if (! Gate::authorize('store', Flashcard::class)) {
            throw new UnauthorizedException;
        }

        $role = Auth::user()->roles->where('code', 'advanced_user')->first();

        // Cannot create questions if you're a free user and already have 10 created
        if (! $role && Auth::user()->flashcards->count() >= config('flashcard.free_account_limit')) {
            throw new FreeUserFlashcardLimitException;
        }

        // Cannot create a statement with answers
        if (isset($data['is_true']) && isset($data['answers'])) {
            throw new UndeterminedQuestionTypeException;
        }

        $flashcard = Flashcard::create([
            'user_id' => Auth::id(),
            'text' => $data['text'],
            'is_true' => $data['is_true'] ?? null,
            'explanation' => $data['explanation'] ?? null,
            'difficulty' => Difficulty::EASY,
        ]);

        if (isset($data['answers'])) {
            // Set a maximum number of answers per question
            $answers = array_slice($data['answers'], 0, config('flashcard.answer_per_question_limit'));

            $hasAtLeastOneCorrectAnswer = false;

            // Ensure that at least one answer is flagged as being the correct one
            foreach ($answers as $answer) {
                if ($answer['is_correct'] ?? false) {
                    $hasAtLeastOneCorrectAnswer = true;
                    break;
                }
            }

            if (! $hasAtLeastOneCorrectAnswer) {
                throw new LessThanOneCorrectAnswerException;
            }

            $flashcard->answers()->createMany($answers);
        }

        if (isset($data['tags'])) {
            $flashcard->tags()->attach($data['tags']);
        }

        if ($flashcard->answers()->count() > 1 || $flashcard->is_true) {
            $this->setStatus($flashcard, Status::PUBLISHED);
        }

        return $flashcard;
    }

    public function update(array $data, Flashcard $flashcard): Flashcard
    {
        if (! Gate::authorize('update', $flashcard)) {
            throw new UnauthorizedException;
        }

        // Only update the truthiness of a question if it's already set up as a statement type
        if (isset($flashcard->is_true) && isset($data['is_true'])) {
            $flashcard->is_true = $data['is_true'];
        }

        if (isset($data['text'])) {
            $flashcard->text = $data['text'];
        }

        if (isset($data['explanation'])) {
            $flashcard->explanation = $data['explanation'];
        }

        $flashcard->save();
        $this->resetDifficulty($flashcard);

        return $flashcard;
    }

    public function destroy(Flashcard $flashcard): void
    {
        if (! Gate::authorize('delete', $flashcard)) {
            throw new UnauthorizedException;
        }

        $flashcard->delete();
    }

    public function buried()
    {
        if (! Gate::authorize('list', Flashcard::class)) {
            throw new UnauthorizedException;
        }

        return Flashcard::currentUser()
            ->buried()
            ->orderBy('last_seen_at', 'desc');
    }

    public function alive()
    {
        if (! Gate::authorize('list', Flashcard::class)) {
            throw new UnauthorizedException;
        }

        return Flashcard::currentUser()
            ->alive()
            ->orderBy('last_seen_at', 'desc')
            ->orderBy('created_at', 'desc');
    }

    public function draft()
    {
        if (! Gate::authorize('list', Flashcard::class)) {
            throw new UnauthorizedException;
        }

        return Flashcard::currentUser()
            ->draft()
            ->orderBy('created_at', 'desc');
    }

    public function hidden()
    {
        if (! Gate::authorize('list', Flashcard::class)) {
            throw new UnauthorizedException;
        }

        return Flashcard::currentUser()
            ->hidden()
            ->orderBy('created_at', 'desc');
    }

    /**
     * @throws NoEligibleQuestionsException
     */
    public function random()
    {
        if (! Gate::authorize('list', Flashcard::class)) {
            throw new UnauthorizedException;
        }

        $eligibleQuestions = Flashcard::currentUser()
            ->published()
            ->alive()
            ->inRandomOrder()
            ->get()
            ->filter(function ($flashcard) {
                return $flashcard->eligible_at->lessThan(now()) || ! $flashcard->last_seen_at;
            }
            );

        if (! $eligibleQuestions->count()) {
            $flashcard = Flashcard::currentUser()
                ->published()
                ->alive()
                ->get()
                ->filter(function ($flashcard) {
                    return (bool) $flashcard->eligible_at->greaterThan(now());
                })
                ->sortBy(function ($flashcard) {
                    return $flashcard->eligible_at;
                })
                ->first();

            if (! $flashcard) {
                throw new NoEligibleQuestionsException;
            }

            throw new NoEligibleQuestionsException($flashcard->eligible_at);
        }

        return $eligibleQuestions->first();
    }

    public function revive(Flashcard $flashcard): Flashcard
    {
        if (! Gate::authorize('revive', $flashcard)) {
            throw new UnauthorizedException;
        }

        $flashcard->update([
            'difficulty' => Difficulty::EASY,
        ]);

        // It can only be hidden if it was preciously published, and since we're adding it back to the pool, make sure
        // it gets unhidden at the same time.
        if ($flashcard->status === Status::HIDDEN) {
            $this->setStatus($flashcard, Status::PUBLISHED);
        }

        return $flashcard;
    }

    public function answer(Flashcard $flashcard, array $answers, User $user): Scorecard
    {
        if (! Gate::authorize('answer', $flashcard)) {
            throw new UnauthorizedException;
        }

        if ($flashcard->type === QuestionType::STATEMENT) {
            $providedAnswer = last($answers);
            $correctness = $this->calculateCorrectness($flashcard, null, $providedAnswer);

            $trueAnswer = $this->attemptService->createGivenAnswer(
                'True',
                $flashcard->is_true,
                (bool) $providedAnswer === true
            );
            $falseAnswer = $this->attemptService->createGivenAnswer(
                'False',
                ! $flashcard->is_true,
                (bool) $providedAnswer === false
            );

            $givenAnswers = collect([$trueAnswer, $falseAnswer]);
        } else {
            $correctness = $this->calculateCorrectness($flashcard, $answers);
        }

        $flashcard->last_seen_at = Carbon::now();

        $score = new Score;
        $pointsEarned = $score->getScore($flashcard->type, $correctness, $flashcard->difficulty, $user->lose_points);

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
            'points_earned' => $pointsEarned,
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
        if (! Gate::authorize('attachTag', Flashcard::class)) {
            throw new UnauthorizedException;
        }

        $flashcard->tags()->attach($tag);

        return $flashcard;
    }

    public function detachTag(Flashcard $flashcard, Tag $tag)
    {
        if (! Gate::authorize('detachTag', Flashcard::class)) {
            throw new UnauthorizedException;
        }

        $flashcard->tags()->detach($tag);

        return $flashcard;
    }

    /**
     * Filters out any answer IDs that are not valid for the flashcard
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

    public function setDifficulty(Flashcard $flashcard, Difficulty $difficulty): void
    {
        $flashcard->difficulty = $difficulty;
        $flashcard->save();
    }

    public function setEligibleAt(Flashcard $flashcard, Carbon $carbon): void
    {
        $flashcard->eligible_at = $carbon;
        $flashcard->save();
    }

    /**
     * They got it wrong, push it back to easy difficulty
     */
    public function resetDifficulty(Flashcard $flashcard): Difficulty
    {
        $flashcard->difficulty = Difficulty::EASY;
        $flashcard->save();

        return $flashcard->difficulty;
    }

    /**
     * User wishes to hide the question from the being eligible
     *
     * @throws DraftQuestionsCannotChangeStatusException
     */
    public function hide(Flashcard $flashcard): Flashcard
    {
        if ($flashcard->status === Status::DRAFT) {
            throw new DraftQuestionsCannotChangeStatusException;
        }

        $this->setStatus($flashcard, Status::HIDDEN);

        return $flashcard;
    }

    /**
     * User wishes to unhide a question so it is eligible for answering again
     *
     * @throws DraftQuestionsCannotChangeStatusException
     */
    public function unhide(Flashcard $flashcard): Flashcard
    {
        if ($flashcard->status === Status::DRAFT) {
            throw new DraftQuestionsCannotChangeStatusException;
        }

        $this->setStatus($flashcard, Status::PUBLISHED);

        return $flashcard;
    }

    public function setStatus(Flashcard $flashcard, Status $status): void
    {
        $flashcard->status = $status;
        $flashcard->save();
    }

    public function import(string $topic)
    {
        $existingCount = Auth::user()->flashcards()->count();
        $freeLimit = config('flashcard.free_account_limit');
        $amountRemaining = $freeLimit - $existingCount;

        // User is on a trial and has already met or exceeded the limit
        if ($existingCount >= $freeLimit && Auth::user()->is_trial_user) {
            return null;
        }

        if (! Storage::disk('import')->exists($topic.'.json')) {
            throw new FileNotFoundException;
        }

        $data = Storage::disk('import')->json($topic.'.json');

        $questions = collect(json_decode(json_encode($data)));

        // Only try to import the amount that a trial user has remaining. Other users can import anything they want.
        if (Auth::user()->is_trial_user) {
            $questions = $questions->take($amountRemaining);
        }

        // Can't import anything? Abort
        if ($questions->count() === 0) {
            return null;
        }

        // Create the tag
        $tag = Tag::firstOrCreate([
            'user_id' => Auth::id(),
            'name' => $topic,
        ], [
            'colour' => array_rand(TagColour::cases()),
        ]);

        $importCount = 0;
        foreach ($questions as $question) {
            if (! Flashcard::where('text', $question->text)->where('user_id', Auth::id())->exists()) {
                $importCount++;
            }
            $flashcard = Flashcard::firstOrCreate([
                'user_id' => Auth::id(),
                'text' => $question->text,
            ], [
                'status' => Status::PUBLISHED,
                'explanation' => property_exists($question, 'explanation') ? $question->explanation : null,
            ]);

            $flashcard->tags()->syncWithoutDetaching($tag);

            if (property_exists($question, 'answers')) {
                foreach ($question->answers as $a) {
                    Answer::firstOrCreate([
                        'flashcard_id' => $flashcard->id,
                        'text' => $a->text,
                    ], [
                        'explanation' => property_exists($a, 'explanation') ? $a->explanation : null,
                        'is_correct' => $a->is_correct ?? false,
                    ]);
                }
            } else {
                $flashcard->update([
                    'is_true' => $question->is_true,
                ]);
            }
        }

        return $importCount;
    }
}
