<?php

namespace App\Helpers;

class Boolean
{
    public function handle(string|int|bool $val): bool
    {
        if (gettype($val) === 'boolean') {
            return $val;
        }

        if (gettype($val) === 'integer') {
            if ($val >= 1) {
                return true;
            }

            return false;
        }

        if (gettype($val) === 'string') {
            if (in_array($val, ['true', 'True', 'TRUE'])) {
                return true;
            }

            return false;
        }

        return false;
    }
}
