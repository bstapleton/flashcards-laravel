<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Flashcard;
use App\Services\FlashcardService;

class RevisionController extends Controller
{
    protected FlashcardService $flashcardService;

    public function __construct(FlashcardService $flashcardService)
    {
        $this->flashcardService = $flashcardService;
        $this->middleware('auth');
    }

    public function index()
    {
        return view('revision');
    }

    public function random()
    {
        try {
            $flashcard = $this->flashcardService->random(true);

            return view('revision.show', compact('flashcard'));
        } catch (\Exception $e) {
            return view('revision')->with('error', 'No eligible flashcards available');
        }
    }

    public function show(Flashcard $flashcard)
    {
        try {
            $flashcard = $this->flashcardService->show($flashcard, true);
            $attempts = $flashcard->attempts()->orderBy('answered_at', 'asc')->get();

            return view('revision.show', compact('flashcard', 'attempts'));
        } catch (\Exception $e) {
            return view('revision')->with('error', 'Flashcard not found');
        }
    }

    public function easy()
    {
        try {
            $flashcard = $this->flashcardService->easy(true);
            $isPooled = true;
            $route = 'revision.'.$flashcard->mastery_route;

            return view('revision.show', compact('flashcard', 'isPooled', 'route'));
        } catch (\Exception $e) {
            return view('revision')->with('error', 'No eligible flashcards available');
        }
    }

    public function medium()
    {
        try {
            $flashcard = $this->flashcardService->medium(true);
            $isPooled = true;
            $route = 'revision.'.$flashcard->mastery_route;

            return view('revision.show', compact('flashcard', 'isPooled', 'route'));
        } catch (\Exception $e) {
            return view('revision')->with('error', 'No eligible flashcards available');
        }
    }

    public function hard()
    {
        try {
            $flashcard = $this->flashcardService->hard(true);
            $isPooled = true;
            $route = 'revision.'.$flashcard->mastery_route;

            return view('revision.show', compact('flashcard', 'isPooled', 'route'));
        } catch (\Exception $e) {
            return view('revision')->with('error', 'No eligible flashcards available');
        }
    }
}
