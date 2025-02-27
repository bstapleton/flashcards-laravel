<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Carbon\Carbon;
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

    #[Test]
    public function user_that_can_lose_points()
    {
        $user = UserFactory::new()->losePoints()->create();

        $this->assertTrue($user->lose_points);
    }

    #[Test]
    public function role_has_name_and_code()
    {
        $name = 'Test Role';
        $code = 'test-role';
        $role = Role::create([
            'name' => $name,
            'code' => $code
        ]);

        $this->assertEquals($name, $role->name);
        $this->assertEquals($code, $role->code);
    }

    #[Test]
    public function role_user_pivot_has_valid_until_and_auto_renew()
    {
        $role = Role::create([
            'name' => 'Test Role',
            'code' => 'test-role',
        ]);

        $user = UserFactory::new()->create();

        $user->roles()->attach($role, [
            'valid_until' => Carbon::now()->addDays(30),
            'auto_renew' => true,
        ]);

        foreach($user->roles as $role) {
            $this->assertEquals(Carbon::now()->addDays(30), $role->pivot->valid_until);
            $this->assertTrue($role->pivot->auto_renew === 1);
        }
    }

    #[Test]
    public function role_user_pivot_can_be_updated()
    {
        $role = new Role();
        $role->name = 'Test Role';
        $role->code = 'test-role';

        $user = UserFactory::new()->create();

        $user->roles()->attach($user, [
            'valid_until' => Carbon::now()->addDays(30),
            'auto_renew' => true,
        ]);

        $newValidity = Carbon::now()->addDays(60);

        foreach ($user->roles as $role) {
            $role->pivot->valid_until = $newValidity;
            $role->pivot->auto_renew = false;

            $this->assertEquals($newValidity, $role->pivot->valid_until);
            $this->assertEquals(0, $role->pivot->auto_renew);
        }
    }
}
