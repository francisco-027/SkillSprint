<?php

use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FlashcardController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\SummaryController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public auth routes — no middleware
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

// Public skill catalog — used by the guest-accessible Quick Start page
Route::get('/skills',      [SkillController::class, 'index']);
Route::get('/skills/{id}', [SkillController::class, 'show']);

// All other routes require a valid Sanctum token
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    Route::get('/user',              [UserController::class, 'show']);
    Route::get('/user/preferences',  [UserController::class, 'preferences']);
    Route::put('/user/preferences',  [UserController::class, 'updatePreferences']);

    Route::post('/user/enroll',      [UserController::class, 'enroll']);
    Route::get('/user/enrolled-skills',        [UserController::class, 'enrolledSkills']);
    Route::delete('/user/enrolled-skills/{id}', [UserController::class, 'unenroll']);

    Route::get('/dashboard',         [DashboardController::class, 'index']);

    Route::get('/uploads',           [UploadController::class, 'index']);
    Route::get('/uploads/categories', [UploadController::class, 'categories']);
    Route::get('/uploads/recent',    [UploadController::class, 'recent']);
    Route::post('/uploads',          [UploadController::class, 'store']);
    Route::get('/uploads/{id}/status', [UploadController::class, 'status']);
    Route::patch('/uploads/{id}/open', [UploadController::class, 'open']);
    Route::put('/uploads/{id}',      [UploadController::class, 'update']);
    Route::delete('/uploads/{id}',   [UploadController::class, 'destroy']);

    Route::get('/summaries/{summary}', [SummaryController::class, 'show']);

    Route::get('/flashcards/{deckId}',                  [FlashcardController::class, 'show']);
    Route::patch('/flashcards/{deckId}/cards/{cardId}', [FlashcardController::class, 'updateCard']);

    Route::get('/quizzes/{quizId}',          [QuizController::class, 'show']);
    Route::post('/quizzes/{quizId}/submit',  [QuizController::class, 'submit']);
    Route::get('/quizzes/{quizId}/results',  [QuizController::class, 'results']);

    Route::get('/library',                   [LibraryController::class, 'index']);
    Route::get('/library/saved',             [LibraryController::class, 'saved']);
    Route::post('/library/{uploadId}/save',  [LibraryController::class, 'save']);
    Route::delete('/library/{uploadId}/save', [LibraryController::class, 'unsave']);

    Route::get('/analytics',         [AnalyticsController::class, 'index']);
    Route::get('/achievements',      [AchievementController::class, 'index']);
});