<?php

namespace App\Http\Controllers\web;

use App\Enums\Difficulty;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\MultipleChoiceRequest;
use App\Http\Requests\StatementRequest;
use App\Models\Flashcard;
use App\Services\FlashcardService;
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

    public function statementForm()
    {
        $tags = Auth::user()->tags()->get();

        return view('flashcards.create-statement', compact('tags'));
    }

    public function multipleChoiceForm()
    {
        $tags = Auth::user()->tags()->get();

        return view('flashcards.create-multiple-choice', compact('tags'));
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

    public function storeMultipleChoice(MultipleChoiceRequest $request)
    {
        $validatedData = $request->validated();

        $flashcard = $this->flashcardService->store($validatedData);
        $this->flashcardService->setStatus($flashcard, Status::PUBLISHED);

        return redirect()->route('revision.show', $flashcard)
            ->with('success', 'Multiple choice flashcard created successfully!');
    }

    public function storeMultipleChoiceDraft(MultipleChoiceRequest $request)
    {
        $validatedData = $request->validated();

        $flashcard = $this->flashcardService->store($validatedData);
        $this->flashcardService->setStatus($flashcard, Status::DRAFT);

        return redirect()->route('flashcards.drafts')
            ->with('success', 'Multiple choice draft saved successfully!');
    }

    public function storeStatement(StatementRequest $request)
    {
        $validatedData = $request->validated();

        $flashcard = $this->flashcardService->store($validatedData);
        $this->flashcardService->setStatus($flashcard, Status::PUBLISHED);

        return redirect()->route('revision.show', $flashcard)
            ->with('success', 'Statement flashcard created successfully!');
    }

    public function storeStatementDraft(StatementRequest $request)
    {
        $validatedData = $request->validated();

        $flashcard = $this->flashcardService->store($validatedData);
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

        return redirect()->route('revision.show', $flashcard)
            ->with('success', 'Flashcard published successfully!');
    }

    public function editStatement(Flashcard $flashcard)
    {
        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        $selectedTags = $flashcard->tags->pluck('id')->toArray();
        $tags = Auth::user()->tags()->get();

        return view('flashcards.edit-statement', compact('flashcard', 'tags', 'selectedTags'));
    }

    public function editMultipleChoice(Flashcard $flashcard)
    {
        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        $selectedTags = $flashcard->tags->pluck('id')->toArray();
        $tags = Auth::user()->tags()->get();

        return view('flashcards.edit-multiple-choice', compact('flashcard', 'tags', 'selectedTags'));
    }

    public function updateStatement(StatementRequest $request, Flashcard $flashcard)
    {
        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        $validatedData = $request->validated();

        $flashcard = $this->flashcardService->update($validatedData, $flashcard);

        // Handle subjects
        if (! empty($validatedData['subjects'])) {
            $flashcard->tags()->sync($validatedData['subjects']);
        } else {
            $flashcard->tags()->detach();
        }

        // Check if we should publish after update
        if ($validatedData['publish_after_update'] ?? false) {
            $this->flashcardService->setStatus($flashcard, Status::PUBLISHED);

            return redirect()->route('revision.show', $flashcard)
                ->with('success', 'Statement flashcard updated and published successfully!');
        }

        return redirect()->route('flashcards.drafts', $flashcard)
            ->with('success', 'Statement flashcard updated successfully!');
    }

    public function updateMultipleChoice(MultipleChoiceRequest $request, Flashcard $flashcard)
    {
        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        $validatedData = $request->validated();

        $flashcard = $this->flashcardService->update($validatedData, $flashcard);

        // Update answers
        $flashcard->answers()->delete();
        $flashcard->answers()->createMany($validatedData['answers']);

        // Handle subjects
        if (! empty($validatedData['subjects'])) {
            $flashcard->tags()->sync($validatedData['subjects']);
        } else {
            $flashcard->tags()->detach();
        }

        // Check if we should publish after update
        if ($validatedData['publish_after_update'] ?? false) {
            $this->flashcardService->setStatus($flashcard, Status::PUBLISHED);

            return redirect()->route('revision.show', $flashcard)
                ->with('success', 'Multiple choice flashcard updated and published successfully!');
        }

        return redirect()->route('flashcards.drafts', $flashcard)
            ->with('success', 'Multiple choice flashcard updated successfully!');
    }

    public function hide(Flashcard $flashcard)
    {
        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        $this->flashcardService->hide($flashcard);

        return redirect()->route('revision.show', $flashcard)
            ->with('success', 'Flashcard hidden successfully!');
    }

    public function unhide(Flashcard $flashcard)
    {
        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        $this->flashcardService->unhide($flashcard);

        return redirect()->route('revision.show', $flashcard)
            ->with('success', 'Flashcard unhidden successfully!');
    }

    public function revive(Flashcard $flashcard)
    {
        // Verify user owns this flashcard
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        $this->flashcardService->revive($flashcard);

        return redirect()->route('revision.show', $flashcard)
            ->with('success', 'Flashcard revived successfully!');
    }
}
