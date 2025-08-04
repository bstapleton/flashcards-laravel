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
        if ($user->roles()->where('code', 'advanced_user')->exists()) {
            return Response::allow();
        }

        if ($user->is_trial_expired) {
            return Response::denyWithStatus(401, 'Your trial has expired');
        }

        return Response::allow();
    }

    public function update(User $user, Flashcard $flashcard): Response
    {
        if ($user->id !== $flashcard->user_id) {
            return Response::denyAsNotFound();
        }

        if ($user->roles()->where('code', 'advanced_user')->exists()) {
            return Response::allow();
        }

        if ($user->is_trial_expired) {
            return Response::denyWithStatus(401, 'Your trial has expired');
        }

        return Response::allow();
    }

    public function revive(User $user, Flashcard $flashcard): Response
    {
        return self::currentUser($user, $flashcard);
    }

    public function answer(User $user, Flashcard $flashcard): Response
    {
        if ($user->id !== $flashcard->user_id) {
            return Response::denyAsNotFound();
        }

        if ($user->roles()->where('code', 'advanced_user')->exists()) {
            return Response::allow();
        }

        if ($user->is_trial_expired) {
            return Response::denyWithStatus(401, 'Your trial has expired');
        }

        return Response::allow();
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
     */
    private function currentUser(User $user, Flashcard $flashcard): Response
    {
        return $user->id === $flashcard->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
