<?php

namespace App\Enums;

enum Difficulty: string
{
    case EASY = 'easy';
    case MEDIUM = 'medium';
    case HARD = 'hard';
    case BURIED = 'buried';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function mastery(): string
    {
        return match ($this) {
            self::EASY => 'Fresh learning',
            self::MEDIUM => 'Intermediate mastery',
            self::HARD => 'High mastery',
            self::BURIED => 'Completely mastered',
        };
    }
}
