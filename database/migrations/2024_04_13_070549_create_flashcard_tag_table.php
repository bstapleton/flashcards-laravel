<?php

use App\Models\Flashcard;
use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flashcard_tag', function (Blueprint $table) {
            $table->foreignIdFor(Flashcard::class);
            $table->foreignIdFor(Tag::class);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcard_tag');
    }
};
