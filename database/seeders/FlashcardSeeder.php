<?php

namespace Database\Seeders;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Flashcard;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class FlashcardSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
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
                'text' => 'correct',
            ]);

            Tag::factory()->count(3)->create([
                'user_id' => $flashcard->user_id,
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
            'text' => 'correct',
        ]);

        // Generate some old flashcards that the user has seen before
        Flashcard::factory()->count(2)->hardDifficulty()->create([
            'user_id' => $user->id,
            'type' => QuestionType::STATEMENT,
            'is_true' => true,
        ]);

        // Put one flashcard into the graveyard
        Flashcard::factory()->buriedDifficulty()->create([
            'user_id' => $user->id,
            'type' => QuestionType::STATEMENT,
            'is_true' => false,
        ]);
    }
}
