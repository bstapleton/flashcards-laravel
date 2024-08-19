<?php

namespace App\Services;

use App\Enums\Correctness;
use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Exceptions\AnswerMismatchException;
use App\Models\Flashcard;
use App\Repositories\FlashcardRepositoryInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\UnauthorizedException;

class FlashcardService
{
    protected Flashcard $flashcard;

    public function __construct(protected FlashcardRepositoryInterface $repository)
    {
    }

    public function all()
    {
        if (!Gate::authorize('listFlashcard', Flashcard::class)) {
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

    public function destroy(int $id)
    {
        $flashcard = $this->repository->show($id);

        if (!Gate::authorize('delete', $flashcard)) {
            throw new UnauthorizedException();
        }

        return $this->repository->destroy($id);
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
        if (!Gate::authorize('show', Flashcard::class)) {
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

        return $this->repository->revive($flashcard);
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
     * not at all (e.g. 0/3)
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

                return match ($correctAnswers->count() - count($correctSuppliedAnswers)) {
                    0 => Correctness::COMPLETE,
                    $correctAnswers->count() => Correctness::NONE,
                    default => Correctness::PARTIAL,
                };
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
                $this->flashcard->difficulty = Difficulty::MEDIUM;
                break;
            case Difficulty::MEDIUM:
                $this->flashcard->difficulty = Difficulty::HARD;
                break;
            case Difficulty::HARD:
                $this->flashcard->difficulty = Difficulty::BURIED;
                break;
            case Difficulty::BURIED:
                break;
        }

        $this->flashcard->last_seen = NOW()->toIso8601String();

        return $this->flashcard->difficulty;
    }

    /**
     * They got it wrong, push it back to easy difficulty
     *
     * @return Difficulty
     */
    public function resetDifficulty(): Difficulty
    {
        $this->flashcard->difficulty = Difficulty::EASY;
        $this->flashcard->last_seen = NOW()->toIso8601String();

        return $this->flashcard->difficulty;
    }

    public function resetLastSeen(): void
    {
        $this->flashcard->last_seen = NOW()->toIso8601String();
    }

    public function save(): void
    {
        $this->flashcard->save();
    }
}
