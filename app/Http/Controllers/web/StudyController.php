<?php

namespace App\Http\Controllers\web;

use App\Exceptions\NoEligibleQuestionsException;
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

        return view('answer');
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
            return view('answer')->with('error', 'No eligible flashcards available');
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

    public function easy()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $flashcard = $this->flashcardService->easy();
            $isPooled = true;
            $route = 'answer.'.$flashcard->mastery_route;

            return view('study.practice', compact('flashcard', 'isPooled', 'route'));
        } catch (NoEligibleQuestionsException $e) {
            return view('answer')->with('error', 'No easy flashcards available');
        }
    }

    public function medium()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $flashcard = $this->flashcardService->medium();
            $isPooled = true;
            $route = 'answer.'.$flashcard->mastery_route;

            return view('study.practice', compact('flashcard', 'isPooled', 'route'));
        } catch (NoEligibleQuestionsException $e) {
            return view('answer')->with('error', 'No medium flashcards available');
        }
    }

    public function hard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $flashcard = $this->flashcardService->hard();
            $isPooled = true;
            $route = 'answer.'.$flashcard->mastery_route;

            return view('study.practice', compact('flashcard', 'isPooled', 'route'));
        } catch (NoEligibleQuestionsException $e) {
            return view('answer')->with('error', 'No hard flashcards available');
        }
    }
}
