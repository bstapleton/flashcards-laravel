<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\AttemptController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\FlashcardTagController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckAuthed;
use Illuminate\Support\Facades\Route;

Route::post('/register', 'App\Http\Controllers\UserController@register')->name('auth.register');
Route::post('/login', 'App\Http\Controllers\LoginController@login')->name('auth.login');

Route::controller(UserController::class)->prefix('user')->middleware(['auth:sanctum', CheckAuthed::class])->group(function () {
    Route::get('/', 'show')->name('auth.user');
    Route::get('/count_questions', 'countQuestions')->name('user.questions');
});

Route::controller(AnswerController::class)->prefix('answers')->middleware(['auth:sanctum', CheckAuthed::class])->group(function () {
    Route::post('/', 'store')->name('answers.store');
    Route::get('/{answer}', 'show')->name('answers.show');
    Route::patch('/{answer}', 'update')->name('answers.update');
    Route::delete('/{answer}', 'destroy')->name('answers.destroy');
});

Route::controller(FlashcardController::class)->prefix('flashcards')->middleware(['auth:sanctum', CheckAuthed::class])->group(function () {
    Route::get('/', 'index')->name('flashcards.index');
    Route::post('/', 'store')->name('flashcards.store');
    Route::get('/all', 'all')->name('flashcards.all');
    Route::get('/random', 'random')->name('flashcards.random');
    Route::get('/graveyard', 'graveyard')->name('flashcards.graveyard');
    Route::get('/drafts', 'draft')->name('flashcards.drafts');
    Route::get('/hidden', 'hidden')->name('flashcards.hidden');
    Route::get('/suggest', 'suggest')->name('flashcards.suggest');
    Route::post('/import', 'import')->name('flashcards.import');
    Route::prefix('{flashcard}')->group(function () {
        Route::get('/', 'show')->name('flashcards.show');
        Route::post('/', 'answer')->name('flashcards.answer');
        Route::patch('/', 'update')->name('flashcards.update');
        Route::delete('/', 'destroy')->name('flashcards.destroy');
        Route::post('/revive', 'revive')->name('flashcards.revive');
        Route::post('/hide', 'hide')->name('flashcards.hide');
        Route::post('/unhide', 'unhide')->name('flashcards.unhide');

        Route::controller(FlashcardTagController::class)->prefix('tags')->group(function () {
            Route::post('/{tag}', 'attachTag')->name('flashcards.tags.attach');
            Route::delete('/{tag}', 'detachTag')->name('flashcards.tags.detach');
        });
    });
});

Route::controller(TagController::class)->prefix('tags')->middleware(['auth:sanctum', CheckAuthed::class])->group(function () {
    Route::get('/', 'index')->name('tags.index');
    Route::post('/', 'store')->name('tags.store');
    Route::put('/{tag}', 'update')->name('tags.update');
    Route::delete('/{tag}', 'destroy')->name('tags.destroy');
});

Route::controller(AttemptController::class)->prefix('attempts')->middleware(['auth:sanctum', CheckAuthed::class])->group(function () {
    Route::get('/', 'index')->name('attempts.index');
    Route::get('/{attempt}', 'show')->name('attempts.show');
});
