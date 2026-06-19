<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Public — no login required
Route::view('/quick-start', 'quickstart');

// Authenticated app pages — redirect guests to login before the page calls the API
Route::middleware('auth')->group(function () {
    Route::view('/onboarding', 'onboarding');
    Route::view('/skills', 'skills');
    Route::view('/upload', 'upload');
    Route::view('/materials/{id}', 'material');
    Route::view('/summaries/{id}', 'summary');
    Route::view('/flashcards/{deckId}', 'flashcards');
    Route::view('/quizzes/{quizId}', 'quiz');
    Route::view('/quizzes/{quizId}/results', 'quiz-results');
    Route::view('/analytics', 'analytics');
    Route::view('/achievements', 'achievements');
    Route::view('/settings', 'settings');
});
