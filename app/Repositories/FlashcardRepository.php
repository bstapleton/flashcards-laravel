<?php

namespace App\Repositories;

use App\Enums\Difficulty;
use App\Models\Flashcard;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\UnauthorizedException;

class FlashcardRepository
{
    public function allFlashcards(User $user)
    {
        if ($user->cannot('list')) {
            throw new UnauthorizedException();
        }

        return Flashcard::where('user_id', $user->id)->paginate(25);
    }

    // Get all the flashcards that have been commited to the graveyard
    public function buried(User $user)
    {
        if ($user->cannot('list')) {
            throw new UnauthorizedException();
        }

        return Flashcard::where(['user_id' => $user->id, 'difficulty' => Difficulty::BURIED])->paginate(25);
    }

    // Get all the flashcards that are NOT in the graveyard
    public function alive(User $user)
    {
        if ($user->cannot('list')) {
            throw new UnauthorizedException();
        }

        return Flashcard::where('user_id', $user->id)->whereNotIn('difficulty', Difficulty::BURIED)->paginate(25);
    }

    public function storeFlashcard($data, User $user)
    {
        if ($user->cannot('store')) {
            throw new UnauthorizedException();
        }

        return Flashcard::create([
            'text' => $data['text'],
            'type' => $data['type'],
            'is_true' => $data['is_true'],
            'explanation' => $data['explanation'],
        ]);
    }

    public function findFlashcard(Flashcard $flashcard, User $user): Flashcard
    {
        if ($user->cannot('show', $flashcard)) {
            throw new ModelNotFoundException();
        }

        return $flashcard;
    }

    public function randomFlashcard(User $user): Flashcard
    {
        if ($user->cannot('list')) {
            throw new UnauthorizedException();
        }

        $flashcard = Flashcard::where('user_id', $user->id)->whereNotIn('difficulty', Difficulty::BURIED)->inRandomOrder()->first();

        if (!$flashcard) {
            // Consumer has no flashcards that are alive
            throw new ModelNotFoundException();
        }

        return $flashcard;
    }

    public function updateFlashcard($data, Flashcard $flashcard, User $user): Flashcard
    {
        if ($user->cannot('show', $flashcard)) {
            throw new ModelNotFoundException();
        }

        if ($user->cannot('update', $flashcard)) {
            throw new UnauthorizedException();
        }

        $flashcard->update([
            'text' => $data['text'],
            'type' => $data['type'],
            'is_true' => $data['is_true'],
            'explanation' => $data['explanation'],
        ]);

        return $flashcard;
    }

    public function destroyFlashcard(Flashcard $flashcard, User $user): void
    {
        if ($user->cannot('show', $flashcard)) {
            throw new ModelNotFoundException();
        }

        if ($user->cannot('update', $flashcard)) {
            throw new UnauthorizedException();
        }

        $flashcard->answers->each(function ($answer) {
            $answer->delete();
        });
        $flashcard->tags()->detach();

        $flashcard->delete();
    }

    public function reviveFlashcard(Flashcard $flashcard, User $user): Flashcard
    {
        if ($user->cannot('show', $flashcard)) {
            throw new ModelNotFoundException();
        }

        if ($user->cannot('revive', $flashcard)) {
            throw new UnauthorizedException();
        }

        $flashcard->update([
            'difficulty' => Difficulty::EASY,
        ]);

        return $flashcard;
    }

    public function attachTag(Flashcard $flashcard, User $user, Tag $tag)
    {
        if ($user->cannot('show', $flashcard)) {
            throw new ModelNotFoundException();
        }

        if ($user->cannot('attachTag', $flashcard)) {
            throw new UnauthorizedException();
        }

        $flashcard->tags()->attach($tag);

        return $flashcard;
    }

    public function detachTag(Flashcard $flashcard, User $user, Tag $tag)
    {
        if ($user->cannot('show', $flashcard)) {
            throw new ModelNotFoundException();
        }

        if ($user->cannot('detachTag', $flashcard)) {
            throw new UnauthorizedException();
        }

        $flashcard->tags()->detach($tag);

        return $flashcard;
    }
}
