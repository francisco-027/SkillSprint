<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Auth::routes();

// Public — no login required (standalone, not part of the SPA)
Route::view('/quick-start', 'quickstart');

// Authenticated app pages. Every SPA URL serves the same shell; Vue Router
// renders the correct page client-side. Deep links / refresh still work because
// the server matches each path here and returns the shell.
Route::middleware('auth')->group(function () {
    Route::view('/onboarding', 'onboarding'); // standalone full-screen flow

    Route::view('/home', 'spa')->name('home');
    Route::view('/skills', 'spa');
    Route::view('/upload', 'spa');
    Route::view('/materials/{id}', 'spa');
    Route::view('/summaries/{id}', 'spa');
    Route::view('/flashcards/{deckId}', 'spa');
    Route::view('/quizzes/{quizId}', 'spa');
    Route::view('/quizzes/{quizId}/results', 'spa');
    Route::view('/analytics', 'spa');
    Route::view('/achievements', 'spa');
    Route::view('/settings', 'spa');
});
