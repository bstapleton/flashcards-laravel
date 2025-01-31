<?php

namespace Tests\Unit;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(UserFactory::class)]
class UserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_with_default_state()
    {
        $user = UserFactory::new()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->username);
        $this->assertNotNull($user->display_name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotNull($user->password);
        $this->assertNotNull($user->remember_token);
    }

    #[Test]
    public function user_with_unverified_email()
    {
        $user = UserFactory::new()->unverified()->create();

        $this->assertNull($user->email_verified_at);
    }

    #[Test]
    public function user_with_custom_difficulty_timers()
    {
        $user = UserFactory::new()->differentTimes()->create();

        // Custom timers must be longer than default
        $this->assertGreaterThan(config('flashcard.difficulty_minutes.easy'), $user->easy_time);
        $this->assertGreaterThan(config('flashcard.difficulty_minutes.medium'), $user->medium_time);
        $this->assertGreaterThan(config('flashcard.difficulty_minutes.hard'), $user->hard_time);

        // Easy must be shorter than medium
        $this->assertLessThan($user->medium_time, $user->easy_time);

        // Medium must be shorter than hard
        $this->assertLessThan($user->hard_time, $user->medium_time);

        // Hard must be longer than medium
        $this->assertGreaterThan($user->medium_time, $user->hard_time);
    }
}
