<?php

namespace Tests\Feature;

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

    public function test_count_questions()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/user/count_questions');

        $response->assertStatus(200);
        $this->assertEquals(0, $response['data']['count']);
    }
}
