<?php

namespace Database\Factories;

use App\Enums\Difficulty;
use App\Models\Flashcard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Flashcard>
 */
class FlashcardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'text' => fake()->text(),
            'eligible_at' => now(),
        ];
    }

    public function easyDifficulty(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'difficulty' => Difficulty::EASY
            ];
        });
    }

    public function mediumDifficulty(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'difficulty' => Difficulty::MEDIUM
            ];
        });
    }

    public function hardDifficulty(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'difficulty' => Difficulty::HARD
            ];
        });
    }

    public function buriedDifficulty(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'difficulty' => Difficulty::BURIED
            ];
        });
    }
}
