<?php

namespace App\Enums;

enum QuestionType: string
{
    case STATEMENT = 'statement';
    case SINGLE = 'single';
    case MULTIPLE = 'multiple';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function longName(): string
    {
        return match ($this) {
            self::STATEMENT => 'True/False statement',
            self::SINGLE => 'Multiple choice (single correct answer)',
            self::MULTIPLE => 'Multiple choice (two or more correct answers)',
        };
    }
}
