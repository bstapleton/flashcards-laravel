<?php

namespace Database\Seeders;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TagSeeder::class,
        ]);

        $user = User::factory()->create([
            'email' => 'f2@test.com'
        ]);

        // Generate some new flashcards that the user has never seen before
        Flashcard::factory()->count(3)->create([
            'user_id' => $user->id,
            'type' => QuestionType::SINGLE,
            'difficulty' => Difficulty::EASY,
        ])->map(function ($flashcard) {
            // Create 3 incorrect answers
            Answer::factory()->count(3)->create([
                'flashcard_id' => $flashcard->id,
            ]);

            // Create 1 correct answer
            Answer::factory()->correct()->create([
                'flashcard_id' => $flashcard->id,
                'text' => 'correct'
            ]);
        });

        // Give the first flashcard some tags
        Flashcard::first()->tags()->sync([1, 3]);

        // Now do the same but for a multiple choice
        $multiChoiceFlashcard = Flashcard::factory()->create([
            'user_id' => $user->id,
            'type' => QuestionType::MULTIPLE,
            'difficulty' => Difficulty::MEDIUM,
        ]);

        // Create 2 incorrect answers
        Answer::factory()->count(2)->create([
            'flashcard_id' => $multiChoiceFlashcard->id,
        ]);

        // Create 2 correct answers
        Answer::factory()->correct()->count(2)->create([
            'flashcard_id' => $multiChoiceFlashcard->id,
            'text' => 'correct'
        ]);

        // Generate an old flashcard that the user has seen before. It's a
        // statement, so no answers are needed.
        $oldFlashcard = Flashcard::factory()->hardDifficulty()->create([
            'user_id' => $user->id,
            'type' => QuestionType::STATEMENT,
            'last_seen' => NOW()->subMonths(2),
        ]);

        // Put one flashcard into the graveyard
        $buriedFlashcard = Flashcard::factory()->buriedDifficulty()->create([
            'user_id' => $user->id,
            'type' => QuestionType::STATEMENT,
            'last_seen' => NOW(),
        ]);
    }
}
