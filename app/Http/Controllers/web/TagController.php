<?php

namespace App\Http\Controllers\web;

use App\Enums\TagColour;
use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $tags = Auth::user()->tags()->withCount('flashcards')->get();

        return view('tags.index', compact('tags'));
    }

    public function create()
    {
        $colours = TagColour::cases();

        return view('tags.create', compact('colours'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'colour' => ['required', new \Illuminate\Validation\Rules\Enum(TagColour::class)],
        ]);

        $tag = Auth::user()->tags()->create([
            'name' => $request->name,
            'colour' => (int)$request->colour,
        ]);

        return redirect()->route('tags.show', $tag)
            ->with('success', 'Tag created successfully!');
    }

    public function show(Tag $tag)
    {
        // Verify user owns this tag
        if ($tag->user_id !== Auth::id()) {
            abort(403);
        }

        $tag->load('flashcards');

        return view('tags.show', compact('tag'));
    }

    public function destroy(Tag $tag)
    {
        // Verify user owns this tag
        if ($tag->user_id !== Auth::id()) {
            abort(403);
        }

        $flashcardCount = $tag->flashcards()->count();
        
        // Detach from all flashcards and delete the tag
        $tag->flashcards()->detach();
        $tag->delete();

        return redirect()->route('tags.index')
            ->with('success', "Tag deleted successfully. It was detached from {$flashcardCount} flashcard" . ($flashcardCount !== 1 ? 's' : '') . '.');
    }
}
