<?php

use App\Http\Controllers\web\AttemptController;
use App\Http\Controllers\web\AuthController;
use App\Http\Controllers\web\DashboardController;
use App\Http\Controllers\web\FlashcardController;
use App\Http\Controllers\web\RevisionController;
use App\Http\Controllers\web\StudyController;
use App\Http\Controllers\web\SubjectController;
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

    // Attempts
    Route::prefix('attempts')->name('attempts.')->group(function () {
        Route::get('/', [AttemptController::class, 'index'])->name('index');
        Route::get('/{attempt}', [AttemptController::class, 'show'])->name('show');
    });

    // Study
    Route::prefix('answer')->name('answer.')->group(function () {
        Route::get('/', [StudyController::class, 'index'])->name('index');
        Route::get('/random', [StudyController::class, 'random'])->name('random');
        Route::get('/fresh-learning', [StudyController::class, 'easy'])->name('fresh-learning');
        Route::get('/intermediate-mastery', [StudyController::class, 'medium'])->name('intermediate-mastery');
        Route::get('/high-mastery', [StudyController::class, 'hard'])->name('high-mastery');
        Route::get('/{flashcard}', [StudyController::class, 'practice'])->name('show');
        Route::post('/{flashcard}', [StudyController::class, 'submit'])->name('submit');
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
        Route::get('/create/statement', [FlashcardController::class, 'statementForm'])->name('create-statement');
        Route::get('/create/multiple-choice', [FlashcardController::class, 'multipleChoiceForm'])->name('create-multiple-choice');
        Route::post('/store/multiple-choice', [FlashcardController::class, 'storeMultipleChoice'])->name('store-multiple-choice');
        Route::post('/store/multiple-choice/draft', [FlashcardController::class, 'storeMultipleChoiceDraft'])->name('store-multiple-choice-draft');
        Route::post('/store/statement', [FlashcardController::class, 'storeStatement'])->name('store-statement');
        Route::post('/store/statement/draft', [FlashcardController::class, 'storeStatementDraft'])->name('store-statement-draft');
        Route::prefix('{flashcard}')->group(function () {
            Route::patch('/publish', [FlashcardController::class, 'publish'])->name('publish');
            Route::patch('/hide', [FlashcardController::class, 'hide'])->name('hide');
            Route::patch('/unhide', [FlashcardController::class, 'unhide'])->name('unhide');
            Route::get('/edit/statement', [FlashcardController::class, 'editStatement'])->name('edit-statement');
            Route::get('/edit/multiple-choice', [FlashcardController::class, 'editMultipleChoice'])->name('edit-multiple-choice');
            Route::patch('/update/statement', [FlashcardController::class, 'updateStatement'])->name('update-statement');
            Route::patch('/update/multiple-choice', [FlashcardController::class, 'updateMultipleChoice'])->name('update-multiple-choice');
        });
        Route::get('/fresh-learning', [FlashcardController::class, 'easy'])->name('fresh-learning');
        Route::get('/intermediate-mastery', [FlashcardController::class, 'medium'])->name('intermediate-mastery');
        Route::get('/high-mastery', [FlashcardController::class, 'hard'])->name('high-mastery');
        Route::get('/completely-mastered', [FlashcardController::class, 'graveyard'])->name('completely-mastered');
        Route::get('/drafts', [FlashcardController::class, 'drafts'])->name('drafts');
        Route::get('/hidden', [FlashcardController::class, 'hidden'])->name('hidden');
    });

    // Subjects
    Route::prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/', [SubjectController::class, 'index'])->name('index');
        Route::get('/create', [SubjectController::class, 'create'])->name('create');
        Route::post('/', [SubjectController::class, 'store'])->name('store');
        Route::get('/{tag}', [SubjectController::class, 'show'])->name('show');
        Route::delete('/{tag}', [SubjectController::class, 'destroy'])->name('destroy');
    });
});
