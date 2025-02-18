<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\Keyword;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttemptControllerTest extends TestCase
{
    use RefreshDatabase;
    protected User $user;
    protected string $word;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $attempts = Attempt::factory()->count(2)->create([
            'user_id' => $this->user->id,
        ]);

        $attempts->map(function ($attempt) {
            Keyword::factory()->count(2)->create([
                'attempt_id' => $attempt->id
            ]);
        });

        $this->firstAttempt = $attempts[0];
        $this->secondAttempt = $attempts[1];

        $this->word = fake()->word();
        Keyword::factory()->create([
            'attempt_id' => $this->firstAttempt->id,
            'name' => $this->word,
        ]);
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
                    'id' => $this->firstAttempt->id,
                    'question' => $this->firstAttempt->question,
                    'correctness' => $this->firstAttempt->correctness->value,
                    'question_type' => $this->firstAttempt->question_type->value,
                    'difficulty' => $this->firstAttempt->difficulty->value,
                    'points_earned' => $this->firstAttempt->points_earned,
                    'older_attempts' => [],
                    'newer_attempts' => [],
                    'answered_at' => Carbon::parse($this->firstAttempt->answered_at)->toIso8601String(),
                    'answers_given' => json_decode($this->firstAttempt->answers) ?? [],
                    'keywords' => $this->firstAttempt->keywords->pluck('name')->toArray(),
                ],
                [
                    'id' => $this->secondAttempt->id,
                    'question' => $this->secondAttempt->question,
                    'correctness' => $this->secondAttempt->correctness->value,
                    'question_type' => $this->secondAttempt->question_type->value,
                    'difficulty' => $this->secondAttempt->difficulty->value,
                    'points_earned' => $this->secondAttempt->points_earned,
                    'older_attempts' => [],
                    'newer_attempts' => [],
                    'answered_at' => Carbon::parse($this->secondAttempt->answered_at)->toIso8601String(),
                    'answers_given' => json_decode($this->secondAttempt->answers) ?? [],
                    'keywords' => $this->secondAttempt->keywords->pluck('name')->toArray(),
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

    public function test_filtering_single_tag()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/attempts?tags=' . $this->word);

        $response->assertSuccessful();

        // Assert that the response only contains the attempt with the filtered word
        $response->assertJsonCount(1, 'data');
    }

    // TODO: test show method
}
