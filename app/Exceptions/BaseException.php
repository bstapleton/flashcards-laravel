<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BaseException extends Exception
{
    public function report()
    {
        Log::info(
            $this,
            array_filter([
                'userId' => Auth::id(),
            ]),
        );
    }
}
