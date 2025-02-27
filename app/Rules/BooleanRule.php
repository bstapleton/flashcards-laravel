<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BooleanRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        if (in_array(gettype($value), ['boolean', 'integer', 'string'])) {
            return true;
        }

        return false;
    }

    public function message(): string
    {
        return 'The :attribute must be interpretable as a boolean.';
    }
}
