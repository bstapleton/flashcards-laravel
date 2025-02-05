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
}
