<?php

use App\Enums\Difficulty;
use App\Enums\Status;
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
            $table->tinyText('difficulty')->default(Difficulty::EASY);
            $table->boolean('is_true')->nullable();
            $table->longText('explanation')->nullable();
            $table->dateTime('last_seen_at')->nullable();
            $table->tinyText('status')->default(Status::DRAFT);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcards');
    }
};
