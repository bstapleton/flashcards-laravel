<?php

namespace App\Exceptions;

class DraftQuestionsCannotChangeStatusException extends BaseException
{
    public function __construct()
    {
        $this->message = 'Questions that are in a draft status must be published before hiding or unhiding.';

        parent::__construct();
    }
}
