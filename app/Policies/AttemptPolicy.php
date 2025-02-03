<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttemptPolicy
{
    public function list(User $user): Response
    {
        return Response::allow();
    }
}
