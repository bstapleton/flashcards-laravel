<?php

namespace Database\Factories;

use App\Enums\Correctness;
use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Models\Answer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Answer>
 */
class AttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(?string $question = null): array
    {
        $answers = [];
        for ($i = 0; $i < rand(2, 5); $i++) {
            $answers[$i] = [
                'is_correct' => fake()->boolean(),
                'text' => fake()->sentence(rand(2, 5)),
                'was_selected' => fake()->boolean(),
            ];
        }

        return [
            'question' => $question ?? fake()->sentence(4),
            'correctness' => Correctness::cases()[rand(0, count(Correctness::cases()) - 1)],
            'question_type' => QuestionType::cases()[rand(0, count(QuestionType::cases()) - 1)],
            'difficulty' => Difficulty::cases()[rand(0, count(Difficulty::cases()) - 1)],
            'points_earned' => rand(1, 8),
            'answered_at' => now()->subMinutes(rand(30, 6000)),
            'answers' => json_encode($answers),
        ];
    }
}
