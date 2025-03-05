<?php

namespace App\Exceptions;

use Carbon\Carbon;
use Throwable;

class NoEligibleQuestionsException extends BaseException
{
    protected Carbon|null $eligibleAt;
    public function __construct($eligibleAt = null)
    {
        $this->eligibleAt = $eligibleAt;
        $this->message = 'You have no eligible questions at this time. Please come back later.';

        parent::__construct();
    }

    public function getEligibleAt()
    {
        return $this->eligibleAt;
    }
}
