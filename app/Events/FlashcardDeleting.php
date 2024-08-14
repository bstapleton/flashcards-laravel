<?php

namespace App\Events;

use App\Models\Flashcard;
use Illuminate\Foundation\Events\Dispatchable;

class FlashcardDeleting
{
    use Dispatchable;

    public function __construct(public Flashcard $flashcard)
    {
        $flashcard->tags()->detach();
        $flashcard->answers()->delete();
    }
}
