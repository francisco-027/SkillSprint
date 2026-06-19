<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Summary;

class SummaryController extends Controller
{
    public function show(Summary $summary)
    {
        $summary->load('flashcards');

        $quizId = \App\Models\Quiz::where('summary_id', $summary->id)->value('id');

        $data = $summary->toArray();
        $data['quiz_id'] = $quizId;
        $data['quiz_attempted'] = $quizId
            ? \App\Models\QuizAttempt::where('user_id', auth()->id())->where('quiz_id', $quizId)->exists()
            : false;

        return response()->json($data);
    }
}