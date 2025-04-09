<?php

namespace App\Console\Commands;

use App\Enums\Status;
use App\Enums\TagColour;
use App\Models\Answer;
use App\Models\Flashcard;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportFlashcards extends Command
{
    protected $signature = 'app:import-flashcards {username}';

    protected $description = 'Imports flashcards from the /public/import/ directory - pop in a questions.json file '.
        'and run this. More info can be found in the readme regarding formatting.';

    public function handle(): int
    {
        $user = User::where('username', $this->argument('username'))->first();

        if (! $user) {
            $user = User::factory()->create([
                'username' => $this->argument('username'),
                'display_name' => ucfirst($this->argument('username')),
            ]);
        }

        $role = Role::where('code', 'advanced_user')->first();
        $userRoles = $user->roles->pluck('code')->toArray();

        if (! in_array($role->code, $userRoles)) {
            $user->roles()->attach($role);
        }

        $data = Storage::disk('import')->json('questions.json');

        if (! $data) {
            $this->error('File invalid or not found in expected location.');

            return 1;
        }

        $json = json_decode(json_encode($data));

        foreach ($json as $question) {
            if (property_exists($question, 'answers')) {
                $this->createFlashcardWithAnswers($user, $question);
            } else {
                $this->createStatementFlashcard($user, $question);
            }
        }

        $this->info('The command ran to completion. If you had any warnings relating to the question types, '.
            'correct them and rerun the command - duplicates will be skipped.');

        return 1;
    }

    private function createStatementFlashcard(User $user, \stdClass $question): void
    {
        $flashcard = $user->flashcards()->firstOrCreate([
            'text' => $question->text,
        ], [
            'is_true' => $question->is_true,
            'explanation' => property_exists($question, 'explanation') ? $question->explanation : null,
            'status' => Status::PUBLISHED,
        ]);

        $this->handleTags($question->tags, $flashcard);
    }

    private function createFlashcardWithAnswers(User $user, \stdClass $question): void
    {
        $flashcard = $user->flashcards()->firstOrCreate([
            'text' => $question->text,
        ], [
            'status' => Status::PUBLISHED,
        ]);

        foreach ($question->answers as $a) {
            $this->createAnswer($flashcard, $a);
        }

        $this->handleTags($question->tags, $flashcard);
    }

    private function createAnswer(Flashcard $flashcard, \stdClass $a): void
    {
        $answer = Answer::make([
            'text' => $a->text,
            'explanation' => property_exists($a, 'explanation') ? $a->explanation : null,
            'is_correct' => $a->is_correct ?? false,
        ]);

        $answer->flashcard()->associate($flashcard);
        $answer->save();
    }

    private function handleTags(array $tags, Flashcard $flashcard): void
    {
        foreach ($tags as $topic) {
            $colour = array_rand(TagColour::cases());
            $tag = Tag::firstOrCreate([
                'name' => $topic,
                'user_id' => User::where('username', $this->argument('username'))->first()->id,
            ], [
                'colour' => TagColour::from($colour),
            ]);

            $tag->flashcards()->syncWithoutDetaching($flashcard);
        }
    }
}
