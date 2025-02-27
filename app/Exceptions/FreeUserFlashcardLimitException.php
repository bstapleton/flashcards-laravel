<?php

namespace App\Exceptions;

use Throwable;

class FreeUserFlashcardLimitException extends BaseException
{
    public function __construct()
    {
        $this->message = 'Free users have a limit of 10 questions.';

        parent::__construct();
    }
}
