<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('display_name');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->unsignedInteger('easy_time')->default(config('flashcard.difficulty_minutes.easy'));
            $table->unsignedInteger('medium_time')->default(config('flashcard.difficulty_minutes.medium')); // 7d
            $table->unsignedInteger('hard_time')->default(config('flashcard.difficulty_minutes.hard')); // 28d
            $table->bigInteger('points')->default(0);
            $table->unsignedInteger('page_limit')->default(config('app.default_page_limit')); // 25
            $table->tinyInteger('lose_points')->default(0); // TODO allow losing points on incorrect answers
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
