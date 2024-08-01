<?php

namespace App\Enums;

enum Correctness: string
{
    case NONE = 'none';
    case PARTIAL = 'partial';
    case COMPLETE = 'complete';
}
