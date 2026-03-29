<?php

use App\Http\Controllers\web\AuthController;
use App\Http\Controllers\web\DashboardController;
use App\Http\Controllers\web\FlashcardController;
use App\Http\Controllers\web\RevisionController;
use App\Http\Controllers\web\StudyController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Study
    Route::prefix('answer')->name('answer.')->group(function () {
        Route::get('/', [StudyController::class, 'index'])->name('index');
        Route::get('/random', [StudyController::class, 'random'])->name('random');
        Route::get('/fresh-learning', [StudyController::class, 'easy'])->name('fresh-learning');
        Route::get('/intermediate-mastery', [StudyController::class, 'medium'])->name('intermediate-mastery');
        Route::get('/high-mastery', [StudyController::class, 'hard'])->name('high-mastery');
        Route::get('/{flashcard}', [StudyController::class, 'practice'])->name('show');
    });

    // Revision
    Route::prefix('revision')->name('revision.')->group(function () {
        Route::get('/', [RevisionController::class, 'index'])->name('index');
        Route::get('/random', [RevisionController::class, 'random'])->name('random');
        Route::get('/fresh-learning', [RevisionController::class, 'easy'])->name('fresh-learning');
        Route::get('/intermediate-mastery', [RevisionController::class, 'medium'])->name('intermediate-mastery');
        Route::get('/high-mastery', [RevisionController::class, 'hard'])->name('high-mastery');
        Route::get('/{flashcard}', [RevisionController::class, 'show'])->name('show');
    });

    // Flashcards
    Route::prefix('flashcards')->name('flashcards.')->group(function () {
        Route::get('/', [FlashcardController::class, 'index'])->name('index');
        Route::get('/create', [FlashcardController::class, 'create'])->name('create');
        Route::get('/create/statement', [FlashcardController::class, 'createStatement'])->name('create-statement');
        Route::get('/create/multiple-choice', [FlashcardController::class, 'createMultipleChoice'])->name('create-multiple-choice');
        Route::get('/fresh-learning', [FlashcardController::class, 'easy'])->name('fresh-learning');
        Route::get('/intermediate-mastery', [FlashcardController::class, 'medium'])->name('intermediate-mastery');
        Route::get('/high-mastery', [FlashcardController::class, 'hard'])->name('high-mastery');
        Route::get('/completely-mastered', [FlashcardController::class, 'graveyard'])->name('completely-mastered');
        Route::get('/drafts', [FlashcardController::class, 'drafts'])->name('drafts');
        Route::get('/hidden', [FlashcardController::class, 'hidden'])->name('hidden');
        Route::get('/{flashcard}', [FlashcardController::class, 'show'])->name('show');
    });

    // Legacy random route (redirect to revision random)
    Route::get('/flashcards/random', function() {
        return redirect()->route('revision.random');
    });
});
