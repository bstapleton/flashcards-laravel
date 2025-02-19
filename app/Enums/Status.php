<?php

namespace App\Enums;

enum Status: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case HIDDEN = 'hidden';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
