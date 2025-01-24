<?php

namespace App\Repositories;

use App\Enums\Difficulty;
use App\Models\Flashcard;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class FlashcardRepository implements FlashcardRepositoryInterface
{
    public function all(): Builder
    {
        return Flashcard::where('user_id', Auth::id())
            ->orderBy('created_at');
    }

    public function show(int $id): Flashcard
    {
        $flashcard = Flashcard::where(['id' => $id, 'user_id' => Auth::id()])->first();

        if (!$flashcard) {
            throw new ModelNotFoundException();
        }

        return $flashcard;
    }

    public function store(array $data)
    {
        return Flashcard::create([
            'text' => $data['text'],
            'is_true' => $data['is_true'] ?? null,
            'explanation' => $data['explanation'] ?? null,
        ]);
    }

    public function update(array $data, int $id): Flashcard
    {
        $this->show($id)->update($data);

        return $this->show($id);
    }

    public function destroy(int $id): void
    {
        $this->show($id)->delete();
    }

    public function random(): Flashcard
    {
        $ids = Flashcard::where('user_id', Auth::id())->pluck('id')->toArray();

        if (0 === count($ids)) {
            // Consumer has no flashcards that are alive
            throw new ModelNotFoundException();
        } else if (1 === count($ids)) {
            $id = $ids[0];
        } else {
            $id = array_rand($ids);
        }

        return Flashcard::find($id);
    }

    // Get all the flashcards that have been commited to the graveyard
    public function buried(): Builder
    {
        return Flashcard::where(['user_id' => Auth::id(), 'difficulty' => Difficulty::BURIED])
            ->orderBy('created_at');
    }

    // Get all the flashcards that are NOT in the graveyard
    public function alive(): Builder
    {
        return Flashcard::where('user_id', Auth::id())
            ->whereNot('difficulty', Difficulty::BURIED)
            ->orderBy('created_at');
    }

    public function revive(int $id): Flashcard
    {
        $flashcard = $this->show($id);

        $flashcard->update([
            'difficulty' => Difficulty::EASY,
        ]);

        return $flashcard;
    }

    public function attachTag(int $id, int $tagId): Flashcard
    {
        $flashcard = $this->show($id);
        $tag = Tag::find($tagId);

        if (!$tag) {
            throw new ModelNotFoundException();
        }

        $flashcard->tags()->attach($tag);

        return $flashcard;
    }

    public function detachTag(int $id, int $tagId): Flashcard
    {
        $flashcard = $this->show($id);
        $tag = Tag::find($tagId);

        if (!$tag) {
            throw new ModelNotFoundException();
        }

        $flashcard->tags()->detach($tag);

        return $flashcard;
    }
}
