<?php

namespace App\Exceptions;

use Throwable;

class AnswerMismatchException extends BaseException
{
    public function __construct()
    {
        $this->message = 'None of the provided answer/s match possible answers for the question';

        parent::__construct();
    }
}
