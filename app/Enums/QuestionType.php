<?php

namespace App\Enums;

enum QuestionType: string
{
    case STATEMENT = 'statement';
    case SINGLE = 'single';
    case MULTIPLE = 'multiple';
}
