<?php

namespace App\Exceptions;

use Throwable;

class LessThanOneCorrectAnswerException extends BaseException
{
    public function __construct()
    {
        $this->message = 'When creating a multiple choice questions type, there must be at least one answer that has been flagged as correct or the question is impossible to answer without failing.';

        parent::__construct();
    }
}
