<?php

namespace App\Console\Commands;

use App\Enums\QuestionType;
use App\Enums\TagColour;
use App\Models\Answer;
use App\Models\Flashcard;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportFlashcards extends Command
{
    protected $signature = 'app:import-flashcards {user}';

    protected $description = 'Imports flashcards from the /public/import/ directory - pop in a questions.json file ' .
    'and run this. More info can be found in the readme regarding formatting.';

    public function handle(): int
    {
        $user = User::find($this->argument('user'));

        if (!$user) {
            $this->error('User not found.');

            return 1;
        }

        $data = Storage::disk('import')->json('questions.json');

        if (!$data) {
            $this->error('File invalid or not found in expected location.');

            return 1;
        }

        $json = json_decode(json_encode($data));

        foreach ($json as $question) {
            if (
                property_exists($question, 'answers')
                && in_array($question->type, [QuestionType::MULTIPLE->value, QuestionType::SINGLE->value])
            ) {
                $this->createFlashcardWithAnswers($user, $question);
            } elseif (QuestionType::STATEMENT->value === $question->type) {
                $this->createStatementFlashcard($user, $question);
            } else {
                $this->warn('Invalid question type "' . $question->type . '", skipping. Valid types are: ' .
                    'statement, single, multiple.');
            }
        }

        $this->info('The command ran to completion. If you had any warnings relating to the question types, ' .
            'correct them and rerun the command - duplicates will be skipped.');

        return 1;
    }

    private function createStatementFlashcard(User $user, \stdClass $question): void
    {
        $flashcard = $user->flashcards()->firstOrCreate([
            'text' => $question->text,
        ], [
            'is_true' => $question->is_true,
            'explanation' => property_exists($question, 'explanation') ? $question->explanation : null
        ]);

        $this->handleTags($question->tags, $flashcard);
    }

    private function createFlashcardWithAnswers(User $user, \stdClass $question): void
    {
        $flashcard = $user->flashcards()->firstOrCreate([
            'text' => $question->text,
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
            'is_correct' => $a->is_correct ?? false
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
            ], [
                'colour' => TagColour::from($colour),
            ]);

            $tag->flashcards()->syncWithoutDetaching($flashcard);
        }
    }
}
