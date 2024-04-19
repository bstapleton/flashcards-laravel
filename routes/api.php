<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::post('/register', 'App\Http\Controllers\UserController@register')->name('auth.register');
Route::post('/login', 'App\Http\Controllers\LoginController@login')->name('auth.login');
Route::get('/user', 'App\Http\Controllers\UserController@getUserDetails')->middleware('auth:sanctum')->name('auth.user');

Route::controller(AnswerController::class)->prefix('answers')->middleware('auth:sanctum')->group(function () {
    Route::post('/', 'store')->name('answers.store');
    Route::get('/{answer}', 'show')->name('answers.show');
    Route::patch('/{answer}', 'update')->name('answers.update');
    Route::delete('/{answer}', 'destroy')->name('answers.destroy');
});

Route::controller(FlashcardController::class)->prefix('flashcards')->middleware('auth:sanctum')->group(function () {
    Route::get('/', 'index')->name('flashcard.index');
    Route::post('/', 'store')->name('flashcards.store');
    Route::get('/random', 'random')->name('flashcards.random');
    Route::get('/{flashcard}', 'show')->name('flashcards.show');
    Route::patch('/{flashcard}', 'update')->name('flashcards.update');
    Route::delete('/{flashcard}', 'destroy')->name('flashcards.destroy');
    Route::post('/{flashcard}/tags/{tag}', 'attachTag')->name('flashcards.tags.attach');
    Route::delete('/{flashcard}/tags/{tag}', 'detachTag')->name('flashcards.tags.detach');
});

Route::controller(LessonController::class)->prefix('lessons')->middleware('auth:sanctum')->group(function () {
    Route::get('/', 'index')->name('lessons.index');
    Route::post('/', 'store')->name('lessons.store');
    Route::get('/{lesson}', 'show')->name('lessons.show');
});

Route::controller(TagController::class)->prefix('tags')->middleware('auth:sanctum')->group(function () {
    Route::get('/', 'index')->name('tags.index');
    Route::post('/', 'store')->name('tags.store');
    Route::put('/{tag}', 'update')->name('tags.update');
    Route::delete('/{tag}', 'destroy')->name('tags.destroy');
});
