<?php

namespace App\Enums;

enum Correctness: string
{
    case NONE = 'none';
    case PARTIAL = 'partial';
    case COMPLETE = 'complete';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
