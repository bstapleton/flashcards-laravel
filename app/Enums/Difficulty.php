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
}
