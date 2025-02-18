<?php

use App\Models\Attempt;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempt_keyword', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Attempt::class);
            $table->string('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_keyword');
    }
};
