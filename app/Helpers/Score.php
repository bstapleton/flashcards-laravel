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
     * If the question is in the graveyard, don't score it. Otherwise, work out the score based on its correctness and
     * difficulty.
     */
    public function getScore(QuestionType $type, Correctness $correctness, Difficulty $currentDifficulty, bool $userCanLosePoints = false): int
    {
        if ($currentDifficulty === Difficulty::BURIED) {
            return 0;
        }

        if ($userCanLosePoints && $correctness !== Correctness::COMPLETE) {
            if (in_array($type, [QuestionType::STATEMENT, QuestionType::SINGLE])) {
                return 0 - ($this->baseScore * self::getMultiplier($currentDifficulty));
            }

            if ($type === QuestionType::MULTIPLE) {
                return 0 - (($this->baseScore * self::getMultiplier($currentDifficulty)) * $this->multiplier);
            }
        }

        if (in_array($type, [QuestionType::STATEMENT, QuestionType::SINGLE]) && $correctness === Correctness::COMPLETE) {
            return $this->baseScore * self::getMultiplier($currentDifficulty);
        }

        if ($type === QuestionType::MULTIPLE && $correctness === Correctness::COMPLETE) {
            return ($this->baseScore * self::getMultiplier($currentDifficulty)) * $this->multiplier;
        }

        return 0;
    }

    private static function getMultiplier(Difficulty $difficulty): int
    {
        return match ($difficulty) {
            Difficulty::MEDIUM => 3,
            Difficulty::HARD => 8,
            default => 1,
        };
    }
}
