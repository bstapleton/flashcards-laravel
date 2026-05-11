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

        // Assert that the response contains all attempts
        $response->assertJsonCount(2, 'data');

        $data = $response->json('data');

        // Assert that the response contains the expected data
        $this->assertEquals($this->firstAttempt->id, $data[0]['id']);
        $this->assertEquals($this->firstAttempt->question, $data[0]['question']);
        $this->assertEquals($this->secondAttempt->id, $data[1]['id']);
        $this->assertEquals($this->secondAttempt->question, $data[1]['question']);

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
