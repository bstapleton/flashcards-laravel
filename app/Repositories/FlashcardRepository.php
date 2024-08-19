<?php

namespace App\Repositories;

use App\Enums\Difficulty;
use App\Models\Flashcard;
use App\Models\Tag;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class FlashcardRepository implements FlashcardRepositoryInterface
{
    public function all(): LengthAwarePaginator
    {
        return Flashcard::where('user_id', Auth::id())->paginate(25);
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
            'type' => $data['type'],
            'is_true' => $data['is_true'],
            'explanation' => $data['explanation'],
        ]);
    }

    public function update(array $data, int $id): Flashcard
    {
        $this->show($id)->update([
            'text' => $data['text'],
            'type' => $data['type'],
            'is_true' => $data['is_true'],
            'explanation' => $data['explanation'],
        ]);

        return $this->show($id);
    }

    public function destroy(int $id): void
    {
        $this->show($id)->delete();
    }

    public function random(): Flashcard
    {
        $flashcard = Flashcard::where('user_id', Auth::id())->whereNotIn('difficulty', Difficulty::BURIED)->inRandomOrder()->first();

        if (!$flashcard) {
            // Consumer has no flashcards that are alive
            throw new ModelNotFoundException();
        }

        return $flashcard;
    }

    // Get all the flashcards that have been commited to the graveyard
    public function buried(): LengthAwarePaginator
    {
        return Flashcard::where(['user_id' => Auth::id(), 'difficulty' => Difficulty::BURIED])->paginate(25);
    }

    // Get all the flashcards that are NOT in the graveyard
    public function alive(): LengthAwarePaginator
    {
        return Flashcard::where('user_id', Auth::id())->whereNot('difficulty', Difficulty::BURIED)->paginate(25);
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
