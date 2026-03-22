<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
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
}
