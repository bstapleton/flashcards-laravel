<?php

namespace App\Http\Controllers\web;

use App\Enums\Difficulty;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Flashcard;
use App\Models\Tag;
use App\Services\FlashcardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FlashcardController extends Controller
{
    protected FlashcardService $flashcardService;

    public function __construct(FlashcardService $flashcardService)
    {
        $this->flashcardService = $flashcardService;
        $this->middleware('auth');
    }

    public function index()
    {
        $flashcards = $this->flashcardService->alive()->published()->paginate(20);

        return view('flashcards.index', compact('flashcards'));
    }

    public function create()
    {
        return view('flashcards.create');
    }

    public function createStatement()
    {
        $tags = Tag::all();

        return view('flashcards.create-statement', compact('tags'));
    }

    public function createMultipleChoice()
    {
        $tags = Tag::all();

        return view('flashcards.create-multiple-choice', compact('tags'));
    }

    public function show(Flashcard $flashcard)
    {
        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        $flashcard = $this->flashcardService->show($flashcard);

        return view('flashcards.show', compact('flashcard'));
    }

    public function graveyard()
    {
        $flashcards = $this->flashcardService->buried()->paginate(20);

        return view('flashcards.graveyard', compact('flashcards'));
    }

    public function easy()
    {
        $flashcards = $this->flashcardService->category(Difficulty::EASY)->paginate(20);
        $title = 'Fresh learning';
        $description = 'Questions that you haven\'t attempted yet, or those you have answered incorrectly before, causing them to be reset back to this category.';
        $route = 'fresh-learning';

        return view('flashcards.category', compact('flashcards', 'title', 'description', 'route'));
    }

    public function medium()
    {
        $flashcards = $this->flashcardService->category(Difficulty::MEDIUM)->paginate(20);
        $title = 'Intermediate mastery';
        $description = 'Questions you\'ve answered correctly at least once before.';
        $route = 'intermediate-mastery';

        return view('flashcards.category', compact('flashcards', 'title', 'description', 'route'));
    }

    public function hard()
    {
        $title = 'High mastery';
        $description = 'Questions you\'ve answered correctly twice in a row.';
        $route = 'high-mastery';
        $flashcards = $this->flashcardService->category(Difficulty::HARD)->paginate(20);

        return view('flashcards.category', compact('flashcards', 'title', 'description', 'route'));
    }

    public function drafts()
    {
        $flashcards = $this->flashcardService->draft()->paginate(20);

        return view('flashcards.drafts', compact('flashcards'));
    }

    public function hidden()
    {
        $flashcards = $this->flashcardService->hidden()->paginate(20);

        return view('flashcards.hidden', compact('flashcards'));
    }

    public function storeMultipleChoice(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string',
            'answers' => 'required|array|min:2',
            'answers.*.text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
            'explanation' => 'nullable|string',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:tags,id',
        ]);

        $flashcard = $this->flashcardService->store($data);
        $this->flashcardService->setStatus($flashcard, Status::PUBLISHED);

        return redirect()->route('revision.show', $flashcard)
            ->with('success', 'Multiple choice flashcard created successfully!');
    }

    public function storeMultipleChoiceDraft(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string',
            'answers' => 'nullable|array',
            'answers.*.text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
            'explanation' => 'nullable|string',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:tags,id',
        ]);

        $flashcard = $this->flashcardService->store($data);
        $this->flashcardService->setStatus($flashcard, Status::DRAFT);

        return redirect()->route('flashcards.drafts')
            ->with('success', 'Multiple choice draft saved successfully!');
    }

    public function storeStatement(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string',
            'is_true' => 'required|boolean',
            'explanation' => 'nullable|string',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:tags,id',
        ]);

        $flashcard = $this->flashcardService->store($data);
        $this->flashcardService->setStatus($flashcard, Status::PUBLISHED);

        return redirect()->route('revision.show', $flashcard)
            ->with('success', 'Statement flashcard created successfully!');
    }

    public function storeStatementDraft(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string',
            'is_true' => 'required|boolean',
            'explanation' => 'nullable|string',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:tags,id',
        ]);

        $flashcard = $this->flashcardService->store($data);
        $this->flashcardService->setStatus($flashcard, Status::DRAFT);

        return redirect()->route('flashcards.drafts')
            ->with('success', 'Statement draft saved successfully!');
    }

    public function publish(Flashcard $flashcard)
    {
        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        $this->flashcardService->setStatus($flashcard, Status::PUBLISHED);

        return redirect()->route('flashcards.show', $flashcard)
            ->with('success', 'Flashcard published successfully!');
    }

    public function editStatement(Flashcard $flashcard)
    {
        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        $selectedTags = $flashcard->tags->pluck('id')->toArray();
        $tags = Tag::all();

        return view('flashcards.edit-statement', compact('flashcard', 'tags', 'selectedTags'));
    }

    public function editMultipleChoice(Flashcard $flashcard)
    {
        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        $selectedTags = $flashcard->tags->pluck('id')->toArray();
        $tags = Tag::all();

        return view('flashcards.edit-multiple-choice', compact('flashcard', 'tags', 'selectedTags'));
    }

    public function updateStatement(Request $request, Flashcard $flashcard)
    {
        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'text' => 'required|string',
            'is_true' => 'required|boolean',
            'explanation' => 'nullable|string',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:tags,id',
        ]);

        $flashcard = $this->flashcardService->update($data, $flashcard);

        // Handle subjects
        if (! empty($data['subjects'])) {
            $flashcard->tags()->sync($data['subjects']);
        } else {
            $flashcard->tags()->detach();
        }

        // Check if we should publish after update
        if ($request->has('publish_after_update')) {
            $this->flashcardService->setStatus($flashcard, Status::PUBLISHED);

            return redirect()->route('revision.show', $flashcard)
                ->with('success', 'Statement flashcard updated and published successfully!');
        }

        return redirect()->route('flashcards.drafts', $flashcard)
            ->with('success', 'Statement flashcard updated successfully!');
    }

    public function updateMultipleChoice(Request $request, Flashcard $flashcard)
    {
        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'text' => 'required|string',
            'answers' => 'required|array|min:2',
            'answers.*.text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
            'explanation' => 'nullable|string',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:tags,id',
        ]);

        $flashcard = $this->flashcardService->update($data, $flashcard);

        // Update answers
        $flashcard->answers()->delete();
        $flashcard->answers()->createMany($data['answers']);

        // Handle subjects
        if (! empty($data['subjects'])) {
            $flashcard->tags()->sync($data['subjects']);
        } else {
            $flashcard->tags()->detach();
        }

        // Check if we should publish after update
        if ($request->has('publish_after_update')) {
            $this->flashcardService->setStatus($flashcard, Status::PUBLISHED);

            return redirect()->route('revision.show', $flashcard)
                ->with('success', 'Multiple choice flashcard updated and published successfully!');
        }

        return redirect()->route('flashcards.drafts', $flashcard)
            ->with('success', 'Multiple choice flashcard updated successfully!');
    }
}
