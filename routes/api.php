<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\AttemptController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\FlashcardTagController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckAuthed;
use Illuminate\Support\Facades\Route;

Route::post('/register', 'App\Http\Controllers\UserController@register')->name('api.auth.register');
Route::post('/login', 'App\Http\Controllers\LoginController@login')->name('api.auth.login');

Route::controller(UserController::class)->prefix('user')->middleware(['auth:sanctum', CheckAuthed::class])->group(function () {
    Route::get('/', 'show')->name('api.auth.user');
    Route::get('/count_questions', 'countQuestions')->name('api.user.questions');
});

Route::controller(AnswerController::class)->prefix('answers')->middleware(['auth:sanctum', CheckAuthed::class])->group(function () {
    Route::post('/', 'store')->name('api.answers.store');
    Route::get('/{answer}', 'show')->name('api.answers.show');
    Route::patch('/{answer}', 'update')->name('api.answers.update');
    Route::delete('/{answer}', 'destroy')->name('api.answers.destroy');
});

Route::controller(FlashcardController::class)->prefix('flashcards')->middleware(['auth:sanctum', CheckAuthed::class])->group(function () {
    Route::get('/', 'index')->name('api.flashcards.index');
    Route::post('/', 'store')->name('api.flashcards.store');
    Route::get('/all', 'all')->name('api.flashcards.all');
    Route::get('/random', 'random')->name('api.flashcards.random');
    Route::get('/graveyard', 'graveyard')->name('api.flashcards.graveyard');
    Route::get('/drafts', 'draft')->name('api.flashcards.drafts');
    Route::get('/hidden', 'hidden')->name('api.flashcards.hidden');
    Route::post('/import', 'import')->name('api.flashcards.import');
    Route::prefix('{flashcard}')->group(function () {
        Route::get('/', 'show')->name('api.flashcards.show');
        Route::post('/', 'answer')->name('api.flashcards.answer');
        Route::patch('/', 'update')->name('api.flashcards.update');
        Route::delete('/', 'destroy')->name('api.flashcards.destroy');
        Route::post('/revive', 'revive')->name('api.flashcards.revive');
        Route::post('/hide', 'hide')->name('api.flashcards.hide');
        Route::post('/unhide', 'unhide')->name('api.flashcards.unhide');

        Route::controller(FlashcardTagController::class)->prefix('tags')->group(function () {
            Route::post('/{tag}', 'attachTag')->name('api.flashcards.tags.attach');
            Route::delete('/{tag}', 'detachTag')->name('api.flashcards.tags.detach');
        });
    });
});

Route::controller(TagController::class)->prefix('tags')->middleware(['auth:sanctum', CheckAuthed::class])->group(function () {
    Route::get('/', 'index')->name('api.tags.index');
    Route::post('/', 'store')->name('api.tags.store');
    Route::put('/{tag}', 'update')->name('api.tags.update');
    Route::delete('/{tag}', 'destroy')->name('api.tags.destroy');
});

Route::controller(AttemptController::class)->prefix('attempts')->middleware(['auth:sanctum', CheckAuthed::class])->group(function () {
    Route::get('/', 'index')->name('api.attempts.index');
    Route::get('/{attempt}', 'show')->name('api.attempts.show');
});
