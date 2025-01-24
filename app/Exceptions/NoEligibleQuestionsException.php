<?php

namespace App\Exceptions;

use Throwable;

class NoEligibleQuestionsException extends BaseException
{
    public function __construct()
    {
        $this->message = 'You have no eligible questions!';

        parent::__construct();
    }
}
