<?php

namespace App\Console\Commands;

use App\Enums\Difficulty;
use App\Models\User;
use Illuminate\Console\Command;

class ResetDemo extends Command
{
    protected $signature = 'app:reset-demo';

    protected $description = 'Reset demo user data';

    /**
     * @throws \Throwable
     */
    public function handle()
    {
        $user = User::find(1);

        if (!$user) {
            $this->fail('User does not exist.');
        }

        // Reset user data
        $user->points = 0;
        $user->email = 'f2@test.com';
        $user->password = bcrypt('password');
        $user->save();

        // Purge flashcard attempts and reset difficulty
        $user->flashcards->map(function ($flashcard) {
            $flashcard->difficulty = Difficulty::EASY;
            $flashcard->attempts()->delete();
            $flashcard->save();
        });

        $this->info('Demo user has been reset.');
    }
}
