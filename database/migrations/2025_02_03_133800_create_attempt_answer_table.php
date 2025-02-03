<?php

use App\Models\Answer;
use App\Models\Attempt;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('answer_attempt', function (Blueprint $table) {
            $table->foreignIdFor(Attempt::class);
            $table->foreignIdFor(Answer::class);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answer_attempt');
    }
};
