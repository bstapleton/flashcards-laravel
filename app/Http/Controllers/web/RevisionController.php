<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Flashcard;
use App\Services\FlashcardService;
use Illuminate\Support\Facades\Auth;

class RevisionController extends Controller
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

        return view('revision');
    }

    public function random()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $flashcard = $this->flashcardService->random(true);
            return view('revision.show', compact('flashcard'));
        } catch (\Exception $e) {
            return view('revision')->with('error', 'No eligible flashcards available');
        }
    }

    public function show(Flashcard $flashcard)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $flashcard = $this->flashcardService->show($flashcard, true);

            return view('revision.show', compact('flashcard'));
        } catch (\Exception $e) {
            return view('revision')->with('error', 'Flashcard not found');
        }
    }

    public function easy()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $flashcard = $this->flashcardService->easy(true);
            // TODO: pass value to change random button in view to next in this difficulty

            return view('revision.show', compact('flashcard'));
        } catch (\Exception $e) {
            return view('revision')->with('error', 'No eligible flashcards available');
        }
    }

    public function medium()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $flashcard = $this->flashcardService->medium(true);

            return view('revision.show', compact('flashcard'));
        } catch (\Exception $e) {
            return view('revision')->with('error', 'No eligible flashcards available');
        }
    }

    public function hard()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $flashcard = $this->flashcardService->hard(true);

            return view('revision.show', compact('flashcard'));
        } catch (\Exception $e) {
            return view('revision')->with('error', 'No eligible flashcards available');
        }
    }
}
