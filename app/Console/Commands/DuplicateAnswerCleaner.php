<?php

namespace App\Console\Commands;

use App\Models\Answer;
use App\Models\Flashcard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DuplicateAnswerCleaner extends Command
{
    protected $signature = 'app:deduplicate-answers {--dry-run}';

    protected $description = 'Attempts to remove duplicated answers';

    public function handle(): void
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('Performing a dry run. No answers will be deleted.');
        }

        $this->info('Starting clean-up...');

        Flashcard::has('answers')->chunk(100, function ($questions) use ($isDryRun) {
            foreach ($questions as $question) {
                $this->processQuestionForDuplicates($question, $isDryRun);
            }
        });

    }

    protected function processQuestionForDuplicates(Flashcard $question, bool $isDryRun): void
    {
        $this->comment("Processing Question ID: {$question->id}");

        $duplicatesFound = DB::table('answers')
            ->select('text', DB::raw('COUNT(*) as count'), DB::raw('GROUP_CONCAT(id ORDER BY id ASC) as ids'))
            ->where('flashcard_id', $question->id)
            ->groupBy('text')
            ->having('count', '>', 1)
            ->get();

        if ($duplicatesFound->isEmpty()) {
            $this->line("  No duplicates found for Question ID: {$question->id}");

            return;
        }

        $this->warn("  Duplicates found for Question ID: {$question->id}");

        foreach ($duplicatesFound as $duplicateGroup) {
            $answerIds = explode(',', $duplicateGroup->ids);
            $count = $duplicateGroup->count;
            $text = $duplicateGroup->text;

            // Keep the first answer (lowest ID) and delete the rest
            $keepId = array_shift($answerIds);
            $deleteIds = $answerIds;

            if ($isDryRun) {
                $this->info("    Text: '{$text}' (Count: {$count}). Would keep ID: {$keepId}. Would delete IDs: ".implode(', ', $deleteIds));
            } else {
                $this->info("    Text: '{$text}' (Count: {$count}). Keeping ID: {$keepId}. Deleting IDs: ".implode(', ', $deleteIds));
            }

            if (! $isDryRun) {
                Answer::whereIn('id', $deleteIds)->delete();
                $this->line('    Successfully deleted '.count($deleteIds).' duplicate answers.');
            }
        }
    }
}
