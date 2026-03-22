<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Flashcard;
use App\Services\FlashcardService;
use Illuminate\Support\Facades\Auth;

class FlashcardController extends Controller
{
    protected FlashcardService $flashcardService;

    public function __construct(FlashcardService $flashcardService)
    {
        $this->flashcardService = $flashcardService;
    }

    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $flashcards = $this->flashcardService->alive()->published()->paginate(10);
        
        return view('flashcards.index', compact('flashcards'));
    }

    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('flashcards.create');
    }

    public function show(Flashcard $flashcard)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        return view('flashcards.show', compact('flashcard'));
    }

    public function graveyard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $flashcards = $this->flashcardService->buried()->paginate(10);
        
        return view('flashcards.graveyard', compact('flashcards'));
    }

    public function drafts()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $flashcards = $this->flashcardService->draft()->paginate(10);
        
        return view('flashcards.drafts', compact('flashcards'));
    }

    public function hidden()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $flashcards = $this->flashcardService->hidden()->paginate(10);
        
        return view('flashcards.hidden', compact('flashcards'));
    }
}
