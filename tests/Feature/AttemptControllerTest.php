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

        $attempts = Attempt::factory()->count(2)->create([
            'user_id' => $this->user->id,
        ]);

        $this->firstAttempt = $attempts[0];
        $this->secondAttempt = $attempts[1];
    }

    public function test_index_method_returns_all_attempts()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/attempts');

        $response->assertSuccessful();

        // Assert that the response contains all attempts
        $response->assertJsonCount(2, 'data');

        // Assert that the response contains the expected data
        $response->assertJsonFragment([
            'data' => [
                [
                    'question' => $this->firstAttempt->question,
                    'correctness' => $this->firstAttempt->correctness->value,
                    'question_type' => $this->firstAttempt->question_type->value,
                    'difficulty' => $this->firstAttempt->difficulty->value,
                    'points_earned' => $this->firstAttempt->points_earned,
                    'others' => [],
                    'answered_at' => Carbon::parse($this->firstAttempt->answered_at)->toIso8601String(),
                    'answers_given' => json_decode($this->firstAttempt->answers) ?? [],
                    'tags' => explode(',', $this->firstAttempt->tags),
                ],
                [
                    'question' => $this->secondAttempt->question,
                    'correctness' => $this->secondAttempt->correctness->value,
                    'question_type' => $this->secondAttempt->question_type->value,
                    'difficulty' => $this->secondAttempt->difficulty->value,
                    'points_earned' => $this->secondAttempt->points_earned,
                    'others' => [],
                    'answered_at' => Carbon::parse($this->secondAttempt->answered_at)->toIso8601String(),
                    'answers_given' => json_decode($this->secondAttempt->answers) ?? [],
                    'tags' => explode(',', $this->secondAttempt->tags),
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

    // TODO: test show method
}
