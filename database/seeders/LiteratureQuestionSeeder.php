<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Enums\TagColour;
use App\Models\Answer;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class LiteratureQuestionSeeder extends Seeder
{
    public function run(User $user): void
    {
        $existingCount = $user->flashcards()->count();
        $freeLimit = config('flashcard.free_account_limit');
        $amountRemaining = $freeLimit - $existingCount;

        // User is on a trial and has already met or exceeded the limit
        if ($existingCount >= $freeLimit && $user->is_trial_user) {
            return;
        }

        $data = Storage::disk('import')->json('literature.json');

        $json = collect(json_decode(json_encode($data)));

        // Only try to import the amount that a trial user has remaining. Other users can import anything they want.
        if ($user->is_trial_user) {
            $json = $json->take($amountRemaining);
        }

        // Can't import anything? Abort
        if ($json->count() === 0) {
            return;
        }

        // Create the tag
        $tag = Tag::firstOrCreate([
            'user_id' => $user->id,
            'name' => 'literature',
        ], [
            'colour' => TagColour::GREEN,
        ]);

        foreach ($json as $question) {
            if (property_exists($question, 'answers')) {
                $user->flashcards()->firstOrCreate([
                    'text' => $question->text,
                ], [
                    'status' => Status::PUBLISHED,
                    'explanation' => property_exists($question, 'explanation') ? $question->explanation : null
                ])->tags()->syncWithoutDetaching($tag);
            } else {
                $flashcard = $user->flashcards()->firstOrCreate([
                    'text' => $question->text,
                    'is_true' => $question->is_true
                ], [
                    'status' => Status::PUBLISHED,
                    'explanation' => property_exists($question, 'explanation') ? $question->explanation : null
                ])->tags()->syncWithoutDetaching($tag);

                foreach ($question->answers as $a) {
                    $answer = Answer::make([
                        'text' => $a->text,
                        'explanation' => property_exists($a, 'explanation') ? $a->explanation : null,
                        'is_correct' => $a->is_correct ?? false
                    ]);

                    $answer->flashcard()->associate($flashcard);
                    $answer->save();
                }
            }
        }
    }
}
