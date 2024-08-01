<?php

namespace App\Helpers;

use App\Enums\Correctness;
use App\Enums\Difficulty;
use App\Enums\QuestionType;

class Score
{
    const int BASE_SCORE = 1;
    const int MULTIPLE_CHOICE_MODIFIER = 2;

    /**
     * @param QuestionType $type
     * @param Correctness $correctness
     * @param Difficulty $currentDifficulty
     * @return int
     */
    public static function getScore(QuestionType $type, Correctness $correctness, Difficulty $currentDifficulty): int
    {
        if (
            in_array($type, [QuestionType::STATEMENT, QuestionType::SINGLE])
            && Correctness::COMPLETE === $correctness
        ) {
            return self::BASE_SCORE * self::getMultiplier($currentDifficulty);
        }

        if (QuestionType::MULTIPLE === $type && Correctness::COMPLETE === $correctness) {
            return (self::BASE_SCORE * self::getMultiplier($currentDifficulty)) * self::MULTIPLE_CHOICE_MODIFIER;
        }

        return 0;
    }

    /**
     * @param Difficulty $difficulty
     * @return int
     */
    private static function getMultiplier(Difficulty $difficulty): int
    {
        return match ($difficulty) {
            Difficulty::MEDIUM => 3,
            Difficulty::HARD => 8,
            default => 1,
        };
    }
}
