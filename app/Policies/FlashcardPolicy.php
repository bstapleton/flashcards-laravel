<?php

namespace App\Policies;

use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FlashcardPolicy
{
    public function list(User $user): Response
    {
        return Response::allow();
    }

    public function show(User $user, Flashcard $flashcard): Response
    {
        return self::currentUser($user, $flashcard);
    }

    public function store(User $user): Response
    {
        return Response::allow();
    }

    public function update(User $user, Flashcard $flashcard): Response
    {
        return self::currentUser($user, $flashcard);
    }

    public function revive(User $user, Flashcard $flashcard): Response
    {
        return self::currentUser($user, $flashcard);
    }

    public function answer(User $user, Flashcard $flashcard): Response
    {
        return self::currentUser($user, $flashcard);
    }

    public function delete(User $user, Flashcard $flashcard): Response
    {
        return self::currentUser($user, $flashcard);
    }

    public function attachTag(User $user, Flashcard $flashcard): Response
    {
        return Response::allow();
    }

    public function detachTag(User $user, Flashcard $flashcard): Response
    {
        return Response::allow();
    }

    /**
     * Check if the request user is the owner of the model
     *
     * @param User $user
     * @param Flashcard $flashcard
     * @return Response
     */
    private function currentUser(User $user, Flashcard $flashcard): Response
    {
        return $user->id === $flashcard->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
