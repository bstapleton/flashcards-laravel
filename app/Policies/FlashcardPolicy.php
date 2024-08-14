<?php

namespace App\Policies;

use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FlashcardPolicy
{
    public function listFlashcard(User $user): Response
    {
        return Response::allow();
    }

    public function showFlashcard(User $user, Flashcard $flashcard): Response
    {
        return self::currentUser($user, $flashcard);
    }

    public function storeFlashcard(User $user): Response
    {
        return Response::allow();
    }

    public function updateFlashcard(User $user, Flashcard $flashcard): Response
    {
        return self::currentUser($user, $flashcard);
    }

    public function reviveFlashcard(User $user, Flashcard $flashcard): Response
    {
        return self::currentUser($user, $flashcard);
    }

    public function answerFlashcard(User $user, Flashcard $flashcard): Response
    {
        return self::currentUser($user, $flashcard);
    }

    public function deleteFlashcard(User $user, Flashcard $flashcard): Response
    {
        return self::currentUser($user, $flashcard);
    }

    public function attachFlashcardTag(User $user, Flashcard $flashcard): Response
    {
        return Response::allow();
    }

    public function detachFlashcardTag(User $user, Flashcard $flashcard): Response
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
