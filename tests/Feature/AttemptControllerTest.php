<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\Keyword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttemptControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $word;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $attempts = Attempt::factory()->count(2)->create([
            'user_id' => $this->user->id,
        ]);

        $attempts->map(function ($attempt) {
            Keyword::factory()->count(2)->create([
                'attempt_id' => $attempt->id,
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

        $data = $response->json('data');

        // Assert that the response contains the expected data in any order
        $attemptIds = collect($data)->pluck('id')->toArray();
        $this->assertContains($this->firstAttempt->id, $attemptIds);
        $this->assertContains($this->secondAttempt->id, $attemptIds);

        // Find the attempts in the response data
        $firstAttemptData = collect($data)->firstWhere('id', $this->firstAttempt->id);
        $secondAttemptData = collect($data)->firstWhere('id', $this->secondAttempt->id);

        $this->assertEquals($this->firstAttempt->question, $firstAttemptData['question']);
        $this->assertEquals($this->secondAttempt->question, $secondAttemptData['question']);

    }

    public function test_index_method_requires_authentication()
    {
        $response = $this->getJson('/api/attempts', [
            'Authorization' => 'Bearer ',
        ]);

        $response->assertUnauthorized();
    }

    public function test_filtering_single_tag()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/attempts?subjects='.$this->word);

        $response->assertSuccessful();

        // Assert that the response only contains the attempt with the filtered word
        $response->assertJsonCount(1, 'data');
    }

    // TODO: test show method
}
