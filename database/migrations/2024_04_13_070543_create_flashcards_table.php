<?php

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flashcards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->text('text');
            $table->dateTime('last_seen'); // TODO: might be redundant if we force an update on the difficulty each time
            $table->tinyText('difficulty')->default(Difficulty::EASY);
            $table->boolean('is_true')->nullable();
            $table->longText('explanation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcards');
    }
};
