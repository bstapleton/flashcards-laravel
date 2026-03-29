<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $stats = [
            'total_flashcards' => $user->flashcards()->count(),
            'active_flashcards' => $user->flashcards()->alive()->published()->count(),
            'fresh_learning' => $user->flashcards()->easy()->count(),
            'intermediate_mastery' => $user->flashcards()->medium()->count(),
            'high_mastery' => $user->flashcards()->hard()->count(),
            'buried_flashcards' => $user->flashcards()->buried()->count(),
            'hidden_flashcards' => $user->flashcards()->hidden()->count(),
            'draft_flashcards' => $user->flashcards()->draft()->count(),
        ];

        return view('dashboard', compact('stats'));
    }
}
