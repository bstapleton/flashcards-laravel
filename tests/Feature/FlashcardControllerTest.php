<?php

namespace Tests\Feature;

use App\Enums\Difficulty;
use App\Enums\Status;
use App\Models\Answer;
use App\Models\Flashcard;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashcardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        // Create test flashcards for the main user
        Flashcard::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'difficulty' => Difficulty::EASY,
            'status' => Status::PUBLISHED,
        ]);

        // Create a true statement flashcard for answer testing
        Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'difficulty' => Difficulty::EASY,
            'status' => Status::PUBLISHED,
            'is_true' => true,
        ]);

        // Create a flashcard for another user (should not be accessible)
        Flashcard::factory()->create([
            'user_id' => $this->otherUser->id,
            'difficulty' => Difficulty::EASY,
            'status' => Status::PUBLISHED,
        ]);

        // Create flashcards with different statuses and difficulties
        Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'difficulty' => Difficulty::BURIED,
            'status' => Status::PUBLISHED,
        ]);

        Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'difficulty' => Difficulty::MEDIUM,
            'status' => Status::DRAFT,
        ]);

        Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'difficulty' => Difficulty::HARD,
            'status' => Status::HIDDEN,
        ]);

        // Create answers for flashcards to support ScorecardTransformer mapping
        $flashcards = Flashcard::where('user_id', $this->user->id)->get();

        foreach ($flashcards as $flashcard) {
            // Skip statement type flashcards (they use is_true field)
            if ($flashcard->is_true !== null) {
                continue;
            }

            // Create multiple choice answers for non-statement flashcards
            Answer::factory()->count(2)->create([
                'flashcard_id' => $flashcard->id,
                'is_correct' => false,
            ]);
            Answer::factory()->create([
                'flashcard_id' => $flashcard->id,
                'is_correct' => true,
            ]);
        }
    }

    // Index method tests
    public function test_index_method_returns_active_flashcards()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/flashcards');

        $response->assertSuccessful();

        // Should return only active (non-buried) flashcards for the user
        $activeFlashcards = Flashcard::where('user_id', $this->user->id)
            ->whereNot('difficulty', Difficulty::BURIED)
            ->get();
        $expectedCount = min($activeFlashcards->count(), $this->user->page_limit);
        $response->assertJsonCount($expectedCount, 'data');
    }

    public function test_index_method_requires_authentication()
    {
        $response = $this->getJson('/api/flashcards');

        $response->assertUnauthorized();
    }

    // All method tests
    public function test_all_method_returns_all_flashcards()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/flashcards/all');

        $response->assertSuccessful();

        // Should return all flashcards (including buried) for the user, respecting pagination
        $allFlashcards = Flashcard::where('user_id', $this->user->id)->get();
        $pageLimit = $this->user->page_limit;
        $expectedCount = min($allFlashcards->count(), $pageLimit);
        $response->assertJsonCount($expectedCount, 'data');
    }

    // Store statement method tests
    public function test_store_statement_creates_flashcard()
    {
        $this->actingAs($this->user);

        $data = [
            'text' => 'What is the capital of France?',
            'is_true' => false,
            'explanation' => 'The capital of France is Paris',
        ];

        $response = $this->postJson('/api/flashcards/statement', $data);

        $response->assertSuccessful();
        $this->assertDatabaseHas('flashcards', [
            'text' => $data['text'],
            'is_true' => $data['is_true'],
            'user_id' => $this->user->id,
        ]);
    }

    public function test_store_statement_requires_authentication()
    {
        $data = [
            'text' => 'What is the capital of France?',
            'is_true' => false,
        ];

        $response = $this->postJson('/api/flashcards/statement', $data);

        $response->assertUnauthorized();
    }

    public function test_store_statement_validates_required_fields()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards/statement', []);

        $response->assertJsonValidationErrors(['text', 'is_true']);
    }

    public function test_store_statement_validates_text_max_length()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards/statement', [
            'text' => str_repeat('a', 1025),
            'is_true' => true,
        ]);

        $response->assertJsonValidationErrors(['text']);
    }

    // Store multiple choice method tests
    public function test_store_multiple_choice_creates_flashcard()
    {
        $this->actingAs($this->user);

        $data = [
            'text' => 'What is the capital of France?',
            'explanation' => 'Paris is the capital of France',
            'answers' => [
                ['text' => 'Paris', 'is_correct' => true],
                ['text' => 'London', 'is_correct' => false],
                ['text' => 'Berlin', 'is_correct' => false],
            ],
        ];

        $response = $this->postJson('/api/flashcards/multiple-choice', $data);

        $response->assertSuccessful();
        $this->assertDatabaseHas('flashcards', [
            'text' => $data['text'],
            'user_id' => $this->user->id,
        ]);
        $this->assertDatabaseHas('answers', [
            'text' => 'Paris',
            'is_correct' => true,
        ]);
    }

    public function test_store_multiple_choice_validates_answers()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards/multiple-choice', [
            'text' => 'What is the capital of France?',
            'answers' => [],
        ]);

        $response->assertJsonValidationErrors(['answers']);
    }

    public function test_store_multiple_choice_requires_at_least_one_correct_answer()
    {
        $this->actingAs($this->user);

        $data = [
            'text' => 'What is the capital of France?',
            'answers' => [
                ['text' => 'Paris', 'is_correct' => false],
                ['text' => 'London', 'is_correct' => false],
            ],
        ];

        $response = $this->postJson('/api/flashcards/multiple-choice', $data);

        $response->assertJsonFragment([
            'title' => 'Less than one correct answer',
            'code' => 'less_than_one_correct_answer',
        ]);
    }

    // Update method tests
    public function test_update_modifies_flashcard()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::where('user_id', $this->user->id)->first();

        $data = [
            'text' => 'Updated question text',
            'explanation' => 'Updated explanation',
        ];

        $response = $this->patchJson("/api/flashcards/{$flashcard->id}", $data);

        $response->assertSuccessful();
        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard->id,
            'text' => 'Updated question text',
            'explanation' => 'Updated explanation',
        ]);
    }

    public function test_update_requires_authentication()
    {
        $flashcard = Flashcard::where('user_id', $this->user->id)->first();

        $response = $this->patchJson("/api/flashcards/{$flashcard->id}", [
            'text' => 'Updated text',
        ]);

        $response->assertUnauthorized();
    }

    public function test_update_returns_403_for_other_user_flashcard()
    {
        $this->actingAs($this->user);

        $otherFlashcard = Flashcard::where('user_id', $this->otherUser->id)->first();

        $response = $this->patchJson("/api/flashcards/{$otherFlashcard->id}", [
            'text' => 'Updated text',
        ]);

        $response->assertForbidden();
    }

    // Destroy method tests
    public function test_destroy_deletes_flashcard()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::where('user_id', $this->user->id)->first();

        $response = $this->deleteJson("/api/flashcards/{$flashcard->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('flashcards', ['id' => $flashcard->id]);
    }

    public function test_destroy_requires_authentication()
    {
        $flashcard = Flashcard::where('user_id', $this->user->id)->first();

        $response = $this->deleteJson("/api/flashcards/{$flashcard->id}");

        $response->assertUnauthorized();
    }

    public function test_destroy_returns_403_for_other_user_flashcard()
    {
        $this->actingAs($this->user);

        $otherFlashcard = Flashcard::where('user_id', $this->otherUser->id)->first();

        $response = $this->deleteJson("/api/flashcards/{$otherFlashcard->id}");

        $response->assertForbidden();
    }

    // Graveyard method tests
    public function test_graveyard_returns_buried_flashcards()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/flashcards/graveyard');

        $response->assertSuccessful();
        $response->assertJsonCount(1, 'data'); // Only one buried flashcard
    }

    // Random method tests
    public function test_random_returns_flashcard()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/flashcards/random');

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['id', 'text']]);
    }

    public function test_random_requires_authentication()
    {
        $response = $this->getJson('/api/flashcards/random');

        $response->assertUnauthorized();
    }

    // By subjects method tests
    public function test_by_subjects_filters_flashcards()
    {
        $this->actingAs($this->user);

        // Create tags and associate with flashcards
        $tag = Tag::factory()->create(['user_id' => $this->user->id]);
        $flashcard = Flashcard::where('user_id', $this->user->id)->first();
        $flashcard->tags()->attach($tag->id);

        $response = $this->getJson('/api/flashcards/subjects?subjects[]='.$tag->name);

        $response->assertSuccessful();
    }

    public function test_by_subjects_validates_subjects_parameter()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/flashcards/subjects');

        $response->assertJsonValidationErrors(['subjects']);
    }

    // Revive method tests
    public function test_revive_restores_flashcard()
    {
        $this->actingAs($this->user);

        $buriedFlashcard = Flashcard::where('user_id', $this->user->id)
            ->where('difficulty', Difficulty::BURIED)
            ->first();

        $response = $this->postJson("/api/flashcards/{$buriedFlashcard->id}/revive");

        $response->assertSuccessful();
        $this->assertDatabaseHas('flashcards', [
            'id' => $buriedFlashcard->id,
            'difficulty' => Difficulty::EASY,
        ]);
    }

    public function test_revive_requires_authentication()
    {
        $buriedFlashcard = Flashcard::where('user_id', $this->user->id)
            ->where('difficulty', Difficulty::BURIED)
            ->first();

        $response = $this->postJson("/api/flashcards/{$buriedFlashcard->id}/revive");

        $response->assertUnauthorized();
    }

    // Revive difficulty method tests
    public function test_revive_difficulty_restores_flashcards()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards/revive?difficulty=hard');

        $response->assertNoContent();
    }

    public function test_revive_difficulty_validates_difficulty()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards/revive?difficulty=invalid');

        $response->assertJsonValidationErrors(['difficulty']);
    }

    // Hide method tests
    public function test_hide_conceals_flashcard()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::where('user_id', $this->user->id)
            ->where('status', Status::PUBLISHED)
            ->first();

        $response = $this->postJson("/api/flashcards/{$flashcard->id}/hide");

        $response->assertSuccessful();
        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard->id,
            'status' => Status::HIDDEN,
        ]);
    }

    public function test_hide_fails_for_draft_flashcards()
    {
        $this->actingAs($this->user);

        $draftFlashcard = Flashcard::where('user_id', $this->user->id)
            ->where('status', Status::DRAFT)
            ->first();

        $response = $this->postJson("/api/flashcards/{$draftFlashcard->id}/hide");

        $response->assertJsonFragment([
            'title' => 'Cannot change status',
            'code' => 'draft_status_cannot_change',
        ]);
    }

    // Unhide method tests
    public function test_unhide_reveals_flashcard()
    {
        $this->actingAs($this->user);

        $hiddenFlashcard = Flashcard::where('user_id', $this->user->id)
            ->where('status', Status::HIDDEN)
            ->first();

        // Debug: Ensure the flashcard exists and has correct data
        $this->assertNotNull($hiddenFlashcard, 'Hidden flashcard should exist');
        $this->assertEquals($this->user->id, $hiddenFlashcard->user_id);
        $this->assertEquals(Status::HIDDEN, $hiddenFlashcard->status);

        // Debug: Check current authenticated user
        $this->assertEquals($this->user->id, auth()->id());

        $response = $this->postJson("/api/flashcards/{$hiddenFlashcard->id}/unhide");

        $response->assertSuccessful();
        $this->assertDatabaseHas('flashcards', [
            'id' => $hiddenFlashcard->id,
            'status' => Status::PUBLISHED,
        ]);
    }

    // Answer method tests
    public function test_answer_submits_response()
    {
        $this->actingAs($this->user);

        $flashcard = Flashcard::where('user_id', $this->user->id)
            ->where('is_true', true)
            ->first();

        $response = $this->postJson("/api/flashcards/{$flashcard->id}", [
            'answers' => [true],
        ]);

        $response->assertSuccessful();

        // Debug: Check if flashcard exists and has ID
        $this->assertNotNull($flashcard, 'Flashcard should exist');
        $this->assertNotNull($flashcard->id, 'Flashcard should have an ID');

        $this->assertDatabaseHas('attempts', [
            'flashcard_id' => $flashcard->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_answer_requires_authentication()
    {
        $flashcard = Flashcard::where('user_id', $this->user->id)->first();

        $response = $this->postJson("/api/flashcards/{$flashcard->id}", [
            'answers' => [true],
        ]);

        $response->assertUnauthorized();
    }

    public function test_answer_returns_403_for_other_user_flashcard()
    {
        $this->actingAs($this->user);

        $otherFlashcard = Flashcard::where('user_id', $this->otherUser->id)->first();

        $response = $this->postJson("/api/flashcards/{$otherFlashcard->id}", [
            'answers' => true,
        ]);

        $response->assertForbidden();
    }

    // Draft method tests
    public function test_draft_returns_draft_flashcards()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/flashcards/drafts');

        $response->assertSuccessful();
        $response->assertJsonCount(1, 'data'); // Only one draft flashcard
    }

    // Hidden method tests
    public function test_hidden_returns_hidden_flashcards()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/flashcards/hidden');

        $response->assertSuccessful();
        $response->assertJsonCount(1, 'data'); // Only one hidden flashcard
    }

    // Import method tests
    public function test_import_flashcards()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards/import?topic=dogs');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data' => [
                'count',
                'imported',
                'remaining',
            ],
        ]);
    }

    public function test_import_requires_topic_parameter()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards/import');

        $response->assertJsonFragment([
            'title' => 'Validation error',
            'code' => 'validation_error',
        ]);
    }

    public function test_import_returns_404_for_non_existent_file()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/flashcards/import?topic=non-existent');

        $response->assertNotFound();
    }

    // Authorization tests for all methods
    public function test_all_methods_require_authentication()
    {
        $flashcard = Flashcard::where('user_id', $this->user->id)->first();

        // Test all endpoints that require authentication
        $endpoints = [
            ['method' => 'GET', 'url' => '/api/flashcards'],
            ['method' => 'GET', 'url' => '/api/flashcards/all'],
            ['method' => 'GET', 'url' => '/api/flashcards/random'],
            ['method' => 'GET', 'url' => '/api/flashcards/graveyard'],
            ['method' => 'GET', 'url' => '/api/flashcards/drafts'],
            ['method' => 'GET', 'url' => '/api/flashcards/hidden'],
            ['method' => 'GET', 'url' => '/api/flashcards/subjects'],
            ['method' => 'POST', 'url' => '/api/flashcards/statement'],
            ['method' => 'POST', 'url' => '/api/flashcards/multiple-choice'],
            ['method' => 'POST', 'url' => '/api/flashcards/import'],
            ['method' => 'POST', 'url' => '/api/flashcards/revive'],
            ['method' => 'PATCH', 'url' => "/api/flashcards/{$flashcard->id}"],
            ['method' => 'DELETE', 'url' => "/api/flashcards/{$flashcard->id}"],
            ['method' => 'POST', 'url' => "/api/flashcards/{$flashcard->id}"],
            ['method' => 'POST', 'url' => "/api/flashcards/{$flashcard->id}/revive"],
            ['method' => 'POST', 'url' => "/api/flashcards/{$flashcard->id}/hide"],
            ['method' => 'POST', 'url' => "/api/flashcards/{$flashcard->id}/unhide"],
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->json($endpoint['method'], $endpoint['url']);
            $response->assertUnauthorized();
        }
    }
}
