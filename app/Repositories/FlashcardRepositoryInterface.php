<?php

namespace App\Repositories;

use App\Models\Flashcard;
use App\Models\User;

interface FlashcardRepositoryInterface
{
    public function allFlashcards(User $user);
    public function storeFlashcard($data);
    public function findFlashcard(Flashcard $flashcard, User $user);
    public function updateFlashcard($data, Flashcard $flashcard, User $user);
    public function destroyFlashcard(Flashcard $flashcard, User $user);
}
