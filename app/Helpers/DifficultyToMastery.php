<?php

namespace App\Helpers;

use App\Enums\Difficulty;

class DifficultyToMastery
{
    public function convert(Difficulty $difficulty, ?bool $titleCase = false): string
    {
        $text = match ($difficulty) {
            Difficulty::BURIED => 'completely mastered',
            Difficulty::HARD => 'high mastery',
            Difficulty::MEDIUM => 'intermediate mastery',
            default => 'fresh learning',
        };

        if ($titleCase) {
            return ucfirst($text);
        }

        return $text;
    }
}
