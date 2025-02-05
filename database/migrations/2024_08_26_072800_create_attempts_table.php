<?php

use App\Enums\Correctness;
use App\Enums\Difficulty;
use App\Enums\QuestionType;
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
            $table->foreignIdFor(User::class);
            $table->string('question');
            $table->enum('question_type', QuestionType::toArray());
            $table->json('answers');
            $table->string('tags');
            $table->enum('correctness', Correctness::toArray())->default(Correctness::NONE);
            $table->enum('difficulty', Difficulty::toArray())->default(Difficulty::EASY);
            $table->unsignedInteger('points_earned')->default(0);
            $table->timestamp('answered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
