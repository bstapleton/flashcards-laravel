<?php

use App\Models\Flashcard;
use App\Models\Lesson;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_flashcard', function (Blueprint $table) {
            $table->foreignIdFor(Lesson::class)->constrained();
            $table->foreignIdFor(Flashcard::class)->constrained();
            $table->integer('score')->nullable();
            $table->dateTime('answered_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_flashcard');
    }
};
