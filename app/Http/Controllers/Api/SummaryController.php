<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Summary;

class SummaryController extends Controller
{
    public function show(Summary $summary)
    {
        $summary->load('flashcards');

        $userId = auth()->id();

        $quizzes = \App\Models\Quiz::where('summary_id', $summary->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($quiz) use ($userId) {
                $lastAttempt = \App\Models\QuizAttempt::where('user_id', $userId)
                    ->where('quiz_id', $quiz->id)
                    ->latest('completed_at')
                    ->first();

                return [
                    'id'             => $quiz->id,
                    'title'          => $quiz->title,
                    'difficulty'     => $quiz->difficulty,
                    'question_count' => $quiz->question_count,
                    'created_at'     => $quiz->created_at,
                    'attempted'      => (bool) $lastAttempt,
                    'accuracy'       => $lastAttempt?->accuracy,
                    'grade'          => $lastAttempt?->grade,
                ];
            })
            ->values();

        $data = $summary->toArray();
        $data['quizzes'] = $quizzes;
        // Legacy fields kept for any other consumers (point at the newest quiz).
        $data['quiz_id'] = $quizzes->first()['id'] ?? null;
        $data['quiz_attempted'] = $quizzes->first()['attempted'] ?? false;

        return response()->json($data);
    }
}