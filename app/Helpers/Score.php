<?php

namespace App\Helpers;

use App\Enums\Correctness;
use App\Enums\Difficulty;
use App\Enums\QuestionType;

class Score
{
    protected int $baseScore;
    protected int $multiplier;

    public function __construct()
    {
        $this->baseScore = config('flashcard.scoring.base_score');
        $this->multiplier = config('flashcard.scoring.multiple_correct_multiplier');
    }

    /**
     * @param QuestionType $type
     * @param Correctness $correctness
     * @param Difficulty $currentDifficulty
     * @return int
     */
    public function getScore(QuestionType $type, Correctness $correctness, Difficulty $currentDifficulty): int
    {
        if (
            in_array($type, [QuestionType::STATEMENT, QuestionType::SINGLE])
            && Correctness::COMPLETE === $correctness
        ) {
            return $this->baseScore * self::getMultiplier($currentDifficulty);
        }

        if (QuestionType::MULTIPLE === $type && Correctness::COMPLETE === $correctness) {
            return ($this->baseScore * self::getMultiplier($currentDifficulty)) * $this->multiplier;
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
