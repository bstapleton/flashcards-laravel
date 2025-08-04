<?php

namespace App\Exceptions;

class UndeterminedQuestionTypeException extends BaseException
{
    public function __construct()
    {
        $this->message = 'Question type could not be determined - you must have either an is_true flag or a set of questions, not both.';

        parent::__construct();
    }
}
