<?php

use App\Enums\Correctness;
use App\Enums\Difficulty;
use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Flashcard::class);
            $table->foreignIdFor(User::class);
            $table->timestamp('answered_at');
            $table->unsignedInteger('points_earned')->default(0);
            $table->tinyText('difficulty')->default(Difficulty::EASY);
            $table->tinyText('correctness')->default(Correctness::NONE);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
