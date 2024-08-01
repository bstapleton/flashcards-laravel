<?php

namespace App\Services;

use App\Enums\Correctness;
use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Exceptions\AnswerMismatchException;
use App\Models\Flashcard;

class FlashcardService
{
    const int BASE_SCORE = 1;
    const int MULTIPLE_CHOICE_MODIFIER = 2;

    /**
     * @throws AnswerMismatchException
     */
    public function validateAnswers(array $validatedAnswers): array
    {
        if (0 === count($validatedAnswers)) {
            throw new AnswerMismatchException();
        }

        return $validatedAnswers;
    }

    public function filterValidAnswers(Flashcard $flashcard, array $answers): array
    {
        $possibleAnswers = $flashcard->answers->pluck('id')->toArray();

        return array_values(array_intersect($answers, $possibleAnswers));
    }

    public function calculateStatementScore(Flashcard $flashcard, bool $answer): int
    {
        // TODO: rework based off of correctness instead
        if ($flashcard->is_true === $answer) {
            $score = self::BASE_SCORE * self::handleMultiplier($flashcard->difficulty);
        }

        return isset($score) ? (int)$score : 0;
    }

    public function calculateSingleScore(Flashcard $flashcard, array $answers): int
    {
        // TODO: rework based off of correctness instead
        if ($flashcard->correct_answer->id === array_key_first($answers)) {
            $score = self::BASE_SCORE * self::handleMultiplier($flashcard->difficulty);
        }

        return isset($score) ? (int)$score : 0;
    }

    /**
     * For a multiple choice, you have to get ALL the answers correct, so we apply an additional score modifier on top
     * of the difficulty modifier. This is static, meaning that 2/2 correct answers and 3/3 correct answers of the same
     * difficulty will score the same: they'll only score more than a 1/1 (i.e. single type question) or statement.
     * If the number of answers they provide does not match the number of correct answers, they get a 0
     * If the answers submitted do not match the correct answers, they get a 0
     *
     * @param Flashcard $flashcard
     * @param array $answers
     * @return int
     */
    public function calculateMultipleChoiceScore (Flashcard $flashcard, array $answers): int
    {
        // TODO: rework based off of correctness instead
        if ($flashcard->type === QuestionType::MULTIPLE) {
            $correctAnswers = $flashcard->correct_answers;
            $correctSuppliedAnswers = array_intersect($correctAnswers->pluck('id')->toArray(), $answers);

            if ($correctAnswers->count() === count($correctSuppliedAnswers)) {
                $score = (self::BASE_SCORE * self::handleMultiplier($flashcard->difficulty)) * self::MULTIPLE_CHOICE_MODIFIER;
            }


        }

        return isset($score) ? (int)$score : 0;
    }

    /**
     * For statements, you can either be completely correct or not at all
     * For multiple-choice, single-correct, you can either be completely correct or not at all
     * For multiple-choice, multiple-correct, you can be completely correct (e.g. 3/3), partially correct (e.g 2/3), or
     * not at all (e.g. 0/3)
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

                return match ($correctAnswers->count() - count($correctSuppliedAnswers)) {
                    0 => Correctness::COMPLETE,
                    $correctAnswers->count() => Correctness::NONE,
                    default => Correctness::PARTIAL,
                };
            default:
                return $isTrue === $flashcard->is_true ? Correctness::COMPLETE : Correctness::NONE;
        }
    }

    /**
     * Answering completely correctly will increase the difficulty to the next level until it's in the graveyard
     *
     * @param Flashcard $flashcard
     * @return void
     */
    public function increaseDifficulty(Flashcard $flashcard): void
    {
        // TODO this is cycling
        switch ($flashcard->difficulty) {
            case Difficulty::EASY:
                $flashcard->difficulty = Difficulty::MEDIUM;
                break;
            case Difficulty::MEDIUM:
                $flashcard->difficulty = Difficulty::HARD;
                break;
            case Difficulty::HARD:
                $flashcard->difficulty = Difficulty::BURIED;
                break;
            case Difficulty::BURIED:
                break;
        }

        $flashcard->last_seen = NOW()->toIso8601String();
        $flashcard->save();
    }

    /**
     * They got it wrong, push it back to easy difficulty
     *
     * @param Flashcard $flashcard
     * @return void
     */
    public function forceFail(Flashcard $flashcard): void
    {
        $flashcard->difficulty = Difficulty::EASY;
        $flashcard->last_seen = NOW()->toIso8601String();
        $flashcard->save();
    }

    private function handleMultiplier(Difficulty $difficulty): int
    {
        return match ($difficulty) {
            Difficulty::EASY => 1,
            Difficulty::MEDIUM => 3,
            Difficulty::HARD => 8,
            default => 0,
        };
    }
}
