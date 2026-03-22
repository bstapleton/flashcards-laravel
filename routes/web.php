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
    Route::get('/study', [StudyController::class, 'index'])->name('study');
    Route::get('/study/random', [StudyController::class, 'random'])->name('study.random');
    Route::get('/study/{flashcard}', [StudyController::class, 'practice'])->name('study.practice');
    
    // Revision
    Route::get('/revision', [RevisionController::class, 'index'])->name('revision');
    Route::get('/revision/random', [RevisionController::class, 'random'])->name('revision.random');
    
    // Flashcards
    Route::prefix('flashcards')->name('flashcards.')->group(function () {
        Route::get('/', [FlashcardController::class, 'index'])->name('index');
        Route::get('/create', [FlashcardController::class, 'create'])->name('create');
        Route::get('/graveyard', [FlashcardController::class, 'graveyard'])->name('graveyard');
        Route::get('/drafts', [FlashcardController::class, 'drafts'])->name('drafts');
        Route::get('/hidden', [FlashcardController::class, 'hidden'])->name('hidden');
        Route::get('/{flashcard}', [FlashcardController::class, 'show'])->name('show');
    });
    
    // Legacy random route (redirect to revision random)
    Route::get('/flashcards/random', function() {
        return redirect()->route('revision.random');
    });
});
