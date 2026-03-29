<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Flashcard;
use App\Models\Tag;
use App\Services\FlashcardService;
use Illuminate\Support\Facades\Auth;

class FlashcardController extends Controller
{
    protected FlashcardService $flashcardService;

    public function __construct(FlashcardService $flashcardService)
    {
        $this->flashcardService = $flashcardService;
        $this->middleware('auth');
    }

    public function index()
    {
        $flashcards = $this->flashcardService->alive()->published()->paginate(20);

        return view('flashcards.index', compact('flashcards'));
    }

    public function create()
    {
        return view('flashcards.create');
    }

    public function createStatement()
    {
        $tags = Tag::all();

        return view('flashcards.create-statement', compact('tags'));
    }

    public function createMultipleChoice()
    {
        $tags = Tag::all();

        return view('flashcards.create-multiple-choice', compact('tags'));
    }

    public function show(Flashcard $flashcard)
    {
        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        $flashcard = $this->flashcardService->show($flashcard);

        return view('flashcards.show', compact('flashcard'));
    }

    public function graveyard()
    {
        $flashcards = $this->flashcardService->buried()->paginate(20);

        return view('flashcards.graveyard', compact('flashcards'));
    }

    public function drafts()
    {
        $flashcards = $this->flashcardService->draft()->paginate(20);

        return view('flashcards.drafts', compact('flashcards'));
    }

    public function hidden()
    {
        $flashcards = $this->flashcardService->hidden()->paginate(20);

        return view('flashcards.hidden', compact('flashcards'));
    }
}
