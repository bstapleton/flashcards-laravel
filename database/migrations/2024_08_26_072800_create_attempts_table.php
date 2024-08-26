<?php

use App\Enums\Correctness;
use App\Models\Flashcard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->foreignIdFor(Flashcard::class);
            $table->timestamp('answered_at');
            $table->tinyText('correctness')->default(Correctness::NONE);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
