<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\TagController;
use App\Http\Middleware\CheckAuthed;
use Illuminate\Support\Facades\Route;

Route::post('/register', 'App\Http\Controllers\UserController@register')->name('auth.register');
Route::post('/login', 'App\Http\Controllers\LoginController@login')->name('auth.login');
Route::get('/user', 'App\Http\Controllers\UserController@show')->middleware('auth:sanctum')->name('auth.user');

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
    Route::prefix('{flashcard}')->group(function () {
        Route::get('/', 'show')->name('flashcards.show');
        Route::post('/', 'answer')->name('flashcards.answer');
        Route::patch('/', 'update')->name('flashcards.update');
        Route::delete('/', 'destroy')->name('flashcards.destroy');
        Route::post('/revive', 'promote')->name('flashcards.revive');

        Route::prefix('tags')->group(function () {
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
