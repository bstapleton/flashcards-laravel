<?php

namespace Tests\Feature;

use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_valid_request()
    {
        $response = $this->postJson('/api/register', [
            'username' => 'testuser',
            'password' => 'password',
            'password_confirmation' => 'password',
            'display_name' => 'Test User',
        ]);

        $response->assertStatus(201);
        $this->assertCount(1, User::all());
    }

    public function test_register_invalid_request()
    {
        $response = $this->postJson('/api/register', [
            'username' => 'testuser',
            'password' => 'password',
            'display_name' => 'Test User',
        ]);

        $response->assertStatus(422);
        $this->assertCount(0, User::all());
    }

    public function test_show_logged_in_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200);
        $this->assertEquals($user->sqid, $response['data']['id']);
        $this->assertEquals($user->username, $response['data']['username']);
        $this->assertEquals($user->display_name, $response['data']['name']);
    }

    public function test_show_unauthorized()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_count_trial_user_questions()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/user/count_questions');

        $response->assertStatus(200);
        $this->assertEquals(0, $response['data']['count']);
        $this->assertEquals(config('flashcard.free_account_limit'), $response['data']['remaining']);
    }

    public function test_count_trial_user_questions_with_some_utilisation()
    {
        $user = User::factory()->create();
        $flashcards = Flashcard::factory()->count(2)->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $response = $this->getJson('/api/user/count_questions');

        $response->assertStatus(200);
        $this->assertEquals($flashcards->count(), $response['data']['count']);
        $this->assertEquals(config('flashcard.free_account_limit') - $flashcards->count(), $response['data']['remaining']);
    }

    public function test_count_advanced_user_questions()
    {
        $user = User::factory()->hasRoles(1, ['name' => 'Advanced user', 'code' => 'advanced_user'])->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/user/count_questions');

        $response->assertStatus(200);
        $this->assertEquals(0, $response['data']['count']);
        $this->assertNull($response['data']['remaining']);
    }
}
