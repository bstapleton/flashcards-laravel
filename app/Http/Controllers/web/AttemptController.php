<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Services\AttemptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttemptController extends Controller
{
    protected AttemptService $attemptService;

    public function __construct(AttemptService $attemptService)
    {
        $this->attemptService = $attemptService;
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // Get attempts for the current user
        $attempts = Attempt::where('user_id', $user->id)
            ->with('flashcard')
            ->orderBy('answered_at', 'desc')
            ->paginate(20);

        return view('attempts.index', compact('attempts'));
    }

    public function show(Attempt $attempt)
    {
        // Verify user owns this attempt
        if ($attempt->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $attempt->load('flashcard');
        } catch (\Exception $e) {
            return redirect()->route('attempts.index')->with('error', 'Flashcard not found');
        }

        return view('attempts.show', compact('attempt'));
    }
}
