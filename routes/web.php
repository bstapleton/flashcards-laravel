<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebController;
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
    Route::get('/', [WebController::class, 'dashboard'])->name('dashboard');
    
    // Study
    Route::get('/study', [WebController::class, 'study'])->name('study');
    
    // Flashcards
    Route::prefix('flashcards')->name('flashcards.')->group(function () {
        Route::get('/', [WebController::class, 'flashcards'])->name('index');
        Route::get('/create', [WebController::class, 'createFlashcard'])->name('create');
        Route::get('/random', [WebController::class, 'randomFlashcard'])->name('random');
        Route::get('/graveyard', [WebController::class, 'graveyard'])->name('graveyard');
        Route::get('/drafts', [WebController::class, 'drafts'])->name('drafts');
        Route::get('/hidden', [WebController::class, 'hidden'])->name('hidden');
        Route::get('/{flashcard}', [WebController::class, 'show'])->name('show');
    });
});
