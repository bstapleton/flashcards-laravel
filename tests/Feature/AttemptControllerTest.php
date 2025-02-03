<?php

namespace Tests\Feature;

use App\Enums\Correctness;
use App\Enums\Difficulty;
use App\Models\Attempt;
use App\Models\Flashcard;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttemptControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->firstFlashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'text' => 'Multiple choice question',
        ]);
        $this->firstFlashcard->answers()->createMany([
            [
                'text' => 'Correct answer 1',
                'is_correct' => true,
            ],
            [
                'text' => 'Incorrect answer 1',
                'is_correct' => false,
            ],
            [
                'text' => 'Correct answer 2',
                'is_correct' => true,
            ]
        ]);

        $this->secondFlashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'text' => 'Statement question',
            'is_true' => true,
        ]);

        $this->firstAttempt = Attempt::create([
            'flashcard_id' => $this->firstFlashcard->id,
            'user_id' => $this->user->id,
            'answered_at' => now()->addDay(),
            'difficulty' => Difficulty::EASY,
            'correctness' => Correctness::PARTIAL,
            'points_earned' => 10
        ]);

        $this->firstAttempt->answers()->attach($this->firstFlashcard->answers->all());

        $this->secondAttempt = Attempt::create([
            'flashcard_id' => $this->secondFlashcard->id,
            'user_id' => $this->user->id,
            'answered_at' => now(),
            'difficulty' => Difficulty::MEDIUM,
            'correctness' => Correctness::NONE,
            'points_earned' => 0
        ]);
    }

    public function test_index_method_returns_all_attempts()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/attempts');

        $response->assertSuccessful();

        // Assert that the response contains all attempts
        $response->assertJsonCount(2, 'data');

        $answers = [];
        foreach ($this->firstAttempt->answers as $answer) {
            $answers[$answer->text] = $answer->is_correct;
        }

        $answersGiven = $answers;

        // Assert that the response contains the expected data
        $response->assertJsonFragment([
            'data' => [
                [
                    'question' => $this->firstAttempt->flashcard->text,
                    'correctness' => $this->firstAttempt->correctness->value,
                    'question_type' => $this->firstAttempt->flashcard->type->value,
                    'difficulty' => $this->firstAttempt->difficulty->value,
                    'points_earned' => $this->firstAttempt->points_earned,
                    'answered_at' => Carbon::parse($this->firstAttempt->answered_at)->toIso8601String(),
                    'answers_given' => $answersGiven ?? [],
                    'tags' => [],
                ],
                [
                    'question' => $this->secondAttempt->flashcard->text,
                    'correctness' => $this->secondAttempt->correctness->value,
                    'question_type' => $this->secondAttempt->flashcard->type->value,
                    'difficulty' => $this->secondAttempt->difficulty->value,
                    'points_earned' => $this->secondAttempt->points_earned,
                    'answered_at' => Carbon::parse($this->secondAttempt->answered_at)->toIso8601String(),
                    'answers_given' => [],
                    'tags' => [],
                ],
            ],
        ]);
    }

    public function test_index_method_requires_authentication()
    {
        $response = $this->getJson('/api/attempts', [
            'Authorization' => 'Bearer '
        ]);

        $response->assertUnauthorized();
    }
}
