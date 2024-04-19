<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Flashcard>
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
            'last_seen' => fake()->dateTime(),
        ];
    }

    public function easyDifficulty(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'difficulty' => 'easy'
            ];
        });
    }

    public function mediumDifficulty(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'difficulty' => 'medium'
            ];
        });
    }

    public function hardDifficulty(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'difficulty' => 'hard'
            ];
        });
    }
}
