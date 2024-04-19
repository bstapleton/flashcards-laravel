<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Answer>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'started_at' => NOW()->subHour(),
        ];
    }

    public function ended(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'ended_at' => NOW()->subMinute(),
            ];
        });
    }

    public function old(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'ended_at' => NOW()->subYear(),
            ];
        });
    }
}
