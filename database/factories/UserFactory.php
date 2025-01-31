<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => Str::random(10),
            'display_name' => fake()->name(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Set some non-default difficulty timers
     */
    public function differentTimes(): static
    {
        return $this->state(fn (array $attributes) => [
            'easy_time' => config('flashcard.difficulty_minutes.easy') + rand(10, 120),
            'medium_time' => config('flashcard.difficulty_minutes.medium') + rand(12000, 20000),
            'hard_time' => config('flashcard.difficulty_minutes.hard') + rand(42000, 70000),
        ]);
    }
}
