<?php

namespace App\Http\Controllers;

use App\Models\Flashcard;
use App\Services\FlashcardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebController extends Controller
{
    protected FlashcardService $flashcardService;

    public function __construct(FlashcardService $flashcardService)
    {
        $this->flashcardService = $flashcardService;
    }

    public function dashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $stats = [
            'total_flashcards' => $user->flashcards()->count(),
            'active_flashcards' => $user->flashcards()->alive()->published()->count(),
            'buried_flashcards' => $user->flashcards()->buried()->count(),
            'hidden_flashcards' => $user->flashcards()->hidden()->count(),
        ];

        return view('dashboard', compact('stats'));
    }

    public function flashcards()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $flashcards = $this->flashcardService->alive()->published()->paginate(10);
        
        return view('flashcards.index', compact('flashcards'));
    }

    public function createFlashcard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('flashcards.create');
    }

    public function study()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('study');
    }

    public function randomFlashcard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $flashcard = $this->flashcardService->random();
            return view('flashcards.show', compact('flashcard'));
        } catch (\Exception $e) {
            return view('study')->with('error', 'No eligible flashcards available');
        }
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
