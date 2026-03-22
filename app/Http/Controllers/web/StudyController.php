<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Flashcard;
use App\Services\FlashcardService;
use Illuminate\Support\Facades\Auth;

class StudyController extends Controller
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

        return view('study');
    }

    public function random()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $flashcard = $this->flashcardService->random();
            return view('study.practice', compact('flashcard'));
        } catch (\Exception $e) {
            return view('study')->with('error', 'No eligible flashcards available');
        }
    }

    public function practice(Flashcard $flashcard)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('study.practice', compact('flashcard'));
    }
}
