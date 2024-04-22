<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Flashcard;
use App\Models\Lesson;
use App\Models\Tag;
use App\Models\Type;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TypeSeeder::class,
            TagSeeder::class,
        ]);

        $user = User::factory()->create();

        // Generate some new flashcards that the user has never seen before
        Flashcard::factory()->count(3)->create([
            'user_id' => $user->id,
            'type_id' => Type::where('name', 'Question')->first()->id,
            'difficulty' => 'easy',
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
            'type_id' => 3,
            'difficulty' => 'medium',
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
            'type_id' => Type::where('name', 'Statement')->first()->id,
            'last_seen' => NOW()->subMonths(2),
        ]);

        // A current, active lesson
        $currentLesson = Lesson::factory()->create([
            'user_id' => $user->id,
        ]);

        // A lesson that was started a long time ago and has been ended
        $oldLesson = Lesson::factory()->old()->ended()->create([
            'user_id' => $user->id,
        ]);

        // Get the lessons assigned to the user
        $currentLesson->flashcards()->sync(Flashcard::all()->pluck('id')->toArray());
        $oldLesson->flashcards()->sync([Flashcard::first()->id, $oldFlashcard->id]);
//        $oldLesson->flashcards->first()->update([
//            'score' => 100
//        ]);
    }
}
