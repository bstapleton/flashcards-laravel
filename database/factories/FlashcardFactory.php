<?php

namespace Database\Factories;

use App\Enums\Difficulty;
use App\Enums\Status;
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
        ];
    }

    public function trueStatement(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_true' => true,
            ];
        });
    }

    public function falseStatement(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_true' => false,
            ];
        });
    }

    public function easyDifficulty(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'difficulty' => Difficulty::EASY,
            ];
        });
    }

    public function mediumDifficulty(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'difficulty' => Difficulty::MEDIUM,
            ];
        });
    }

    public function hardDifficulty(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'difficulty' => Difficulty::HARD,
            ];
        });
    }

    public function buriedDifficulty(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'difficulty' => Difficulty::BURIED,
            ];
        });
    }

    public function draftStatus(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Status::DRAFT,
            ];
        });
    }

    public function publishedStatus(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Status::PUBLISHED,
            ];
        });
    }

    public function hiddenStatus(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Status::HIDDEN,
            ];
        });
    }
}
