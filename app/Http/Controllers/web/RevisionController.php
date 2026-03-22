<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Services\FlashcardService;
use App\Transformers\QuestionTransformer;
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
            $flashcard = $this->flashcardService->random();
            return view('revision.show', compact('flashcard'));
        } catch (\Exception $e) {
            return view('revision')->with('error', 'No eligible flashcards available');
        }
    }
}
