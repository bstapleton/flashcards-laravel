<?php

namespace App\Http\Controllers\web;

use App\Exceptions\NoEligibleQuestionsException;
use App\Http\Controllers\Controller;
use App\Models\Flashcard;
use App\Services\FlashcardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudyController extends Controller
{
    protected FlashcardService $flashcardService;

    public function __construct(FlashcardService $flashcardService)
    {
        $this->flashcardService = $flashcardService;
        $this->middleware('auth');
    }

    public function index()
    {
        return view('answer');
    }

    public function random()
    {
        try {
            $flashcard = $this->flashcardService->random();

            return view('study.practice', compact('flashcard'));
        } catch (\Exception $e) {
            return view('answer')->with('error', 'No eligible flashcards available');
        }
    }

    public function practice(Flashcard $flashcard)
    {
        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('study.practice', compact('flashcard'));
    }

    public function submit(Request $request, Flashcard $flashcard)
    {
        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Validate the request
        if ($flashcard->type->value === 'statement') {
            $request->validate([
                'is_true' => 'required|boolean',
            ]);
            $answers = [$request->input('is_true')];
        } else {
            $request->validate([
                'answers' => 'required|array|min:1',
                'answers.*' => 'integer',
            ]);
            $answers = $request->input('answers');
        }

        // Process the answer using the FlashcardService
        $scorecard = $this->flashcardService->answer($flashcard, $answers, Auth::user());

        return view('study.scorecard', compact('scorecard', 'flashcard'));
    }

    public function easy()
    {
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
