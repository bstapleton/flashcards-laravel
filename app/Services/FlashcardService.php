<?php

namespace App\Services;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Models\Flashcard;

class FlashcardService
{
    const int BASE_SCORE = 1;
    const int MULTIPLE_CHOICE_MODIFIER = 2;

    public function calculateStatementScore(Flashcard $flashcard, bool $answer): int
    {
        if ($flashcard->is_true === $answer) {
            $score = self::BASE_SCORE * self::handleMultiplier($flashcard->difficulty);
        }

        return isset($score) ? (int)$score : 0;
    }

    public function calculateSingleScore(Flashcard $flashcard, int $answer): int
    {
        if ($flashcard->correct_answer->id === $answer) {
            $score = self::BASE_SCORE * self::handleMultiplier($flashcard->difficulty);
        }

        return isset($score) ? (int)$score : 0;
    }

    /**
     * For a multiple choice, you have to get ALL the answers correct, so we apply an additional score modifier on top
     * of the difficulty modifier.
     * If the number of answers they provide does not match the number of correct answers, they get a 0
     * If the answers submitted do not match the correct answers, they get a 0
     *
     * @param Flashcard $flashcard
     * @param array $answers
     * @return int
     */
    public function calculateMultipleChoiceScore (Flashcard $flashcard, array $answers): int
    {
        if ($flashcard->type === QuestionType::MULTIPLE) {
            $correctAnswers = $flashcard->correct_answers;
            $correctSuppliedAnswers = array_intersect($correctAnswers->pluck('id')->toArray(), $answers);

            if ($correctAnswers->count() === count($correctSuppliedAnswers)) {
                $score = (self::BASE_SCORE * self::handleMultiplier($flashcard->difficulty)) * self::MULTIPLE_CHOICE_MODIFIER;
            }
        }

        return isset($score) ? (int)$score : 0;
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

    /**
     * Answering completely correctly will increase the difficulty to the next level until it's in the graveyard
     *
     * @param Flashcard $flashcard
     * @return void
     */
    public function increaseDifficulty(Flashcard $flashcard): void
    {
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
}
