<?php

namespace App\Services;

use App\Enums\Correctness;
use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Exceptions\AnswerMismatchException;
use App\Models\Flashcard;

class FlashcardService
{
    protected Flashcard $flashcard;

    public function setFlashcard(Flashcard $flashcard): void
    {
        $this->flashcard = $flashcard;
    }

    /**
     * Handles if none of the answer IDs passed by teh consumer are valid for the flashcard
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
        $this->flashcard->save();

        return $this->flashcard->difficulty;
    }

    /**
     * They got it wrong, push it back to easy difficulty
     *
     * @return void
     */
    public function resetDifficulty(): void
    {
        $this->flashcard->difficulty = Difficulty::EASY;
        $this->flashcard->last_seen = NOW()->toIso8601String();
        $this->flashcard->save();
    }
}
