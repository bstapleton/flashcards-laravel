<?php

namespace App\Policies;

use App\Models\Answer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AnswerPolicy
{
    public function show(User $user, Answer $answer): Response
    {
        return self::currentUser($user, $answer);
    }

    public function update(User $user, Answer $answer): Response
    {
        return self::currentUser($user, $answer);
    }

    public function delete(User $user, Answer $answer): Response
    {
        return self::currentUser($user, $answer);
    }

    /**
     * Check if the request user is the owner of the model
     *
     * @param User $user
     * @param Answer $answer
     * @return Response
     */
    private function currentUser(User $user, Answer $answer)
    {
        return $user->id === $answer->flashcard->user_id
            ? Response::allow()
            : Response::deny('Permission denied');
    }
}
