<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\GamificationService;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function __construct(private GamificationService $gamification) {}

    public function show($quizId)
    {
        $quiz = Quiz::with('questions')->findOrFail($quizId);

        return response()->json([
            'quiz' => [
                'id'             => $quiz->id,
                'title'          => $quiz->title,
                'mode'           => $quiz->mode,
                'difficulty'     => $quiz->difficulty,
                'question_count' => $quiz->question_count,
            ],
            'questions' => $quiz->questions->map(fn($q) => [
                'id'         => $q->id,
                'body'       => $q->body,
                'options'    => $q->options,
                'difficulty' => $q->difficulty,
                'xp_reward'  => $q->xp_reward,
            ]),
        ]);
    }

    public function submit(Request $request, $quizId)
    {
        $quiz    = Quiz::with('questions')->findOrFail($quizId);
        $user    = $request->user();
        $answers = $request->input('answers', []);
        $startedAt = $request->input('started_at');

        $correct = 0;
        $wrong   = 0;
        $skipped = 0;

        foreach ($answers as $answer) {
            $question = $quiz->questions->firstWhere('id', $answer['question_id']);
            if (empty($answer['selected'])) {
                $skipped++;
            } elseif ($question && $answer['selected'] === $question->correct_option) {
                $correct++;
            } else {
                $wrong++;
            }
        }

        $total    = $correct + $wrong + $skipped;
        $accuracy = ($correct + $wrong) > 0
            ? round(($correct / ($correct + $wrong)) * 100)
            : 0;
        $grade    = $accuracy >= 90 ? 'A' : ($accuracy >= 80 ? 'B' : ($accuracy >= 70 ? 'C' : 'D'));
        $passed   = $accuracy >= 70;

        $durationSeconds = $startedAt
            ? now()->diffInSeconds(\Carbon\Carbon::parse($startedAt))
            : null;

        $baseXp = $quiz->questions
            ->whereIn('id', collect($answers)->where(fn($a) => !empty($a['selected']))->pluck('question_id'))
            ->sum('xp_reward');

        $streakBonus       = ($user->streak_current >= 3) ? 15 : 0;
        $perfectBonus      = ($accuracy === 100) ? 25 : 0;
        $speedBonus        = ($durationSeconds && $durationSeconds < 180) ? 10 : 0;
        $firstAttemptBonus = QuizAttempt::where('user_id', $user->id)->where('quiz_id', $quizId)->count() === 0 ? 5 : 0;
        $totalXp           = $baseXp + $streakBonus + $perfectBonus + $speedBonus + $firstAttemptBonus;

        $attempt = QuizAttempt::create([
            'user_id'          => $user->id,
            'quiz_id'          => $quiz->id,
            'answers'          => $answers,
            'correct'          => $correct,
            'wrong'            => $wrong,
            'skipped'          => $skipped,
            'accuracy'         => $accuracy,
            'grade'            => $grade,
            'passed'           => $passed,
            'xp_earned'        => $totalXp,
            'duration_seconds' => $durationSeconds,
            'completed_at'     => now(),
        ]);

        $this->gamification->awardXp($user, 'quiz_completed', $totalXp);
        $this->gamification->updateStreak($user);
        $unlockedBadges = $this->gamification->checkBadgeUnlock($user);

        return response()->json([
            'attempt_id' => $attempt->id,
            'quiz'       => ['title' => $quiz->title, 'completed_at' => now()->toISOString(), 'mode' => $quiz->mode],
            'score'      => compact('correct', 'wrong', 'skipped', 'accuracy', 'grade', 'passed'),
            'xp'         => [
                'earned'              => $baseXp,
                'streak_bonus'        => $streakBonus,
                'speed_bonus'         => $speedBonus,
                'first_attempt_bonus' => $firstAttemptBonus,
                'perfect_bonus'       => $perfectBonus,
                'total'               => $totalXp,
            ],
            'achievements_unlocked' => $unlockedBadges,
            'redirect'              => "/quizzes/{$quizId}/results",
        ]);
    }

    public function results(Request $request, $quizId)
    {
        $quiz = Quiz::with('questions')->findOrFail($quizId);
        $user = $request->user();

        $attempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->latest('completed_at')
            ->firstOrFail();

        $answerMap = collect($attempt->answers)->keyBy('question_id');

        $questions = $quiz->questions->map(function ($q) use ($answerMap) {
            $submitted = $answerMap->get($q->id);
            $selected  = $submitted['selected'] ?? null;
            $isCorrect = $selected === $q->correct_option;

            return [
                'body'           => $q->body,
                'options'        => $q->options,
                'user_answer'    => $selected,
                'correct_answer' => $q->correct_option,
                'explanation'    => $q->explanation,
                'is_correct'     => $isCorrect,
                'xp'             => $q->xp_reward,
                'tag'            => ($q->type ?? 'Foundation') . ' · ' . $q->difficulty,
            ];
        });

        $masteredSkills  = $questions->where('is_correct', true)->pluck('tag')->unique()->values();
        $needsPractice   = $questions->where('is_correct', false)->pluck('tag')->unique()->values();

        $unlocked = \App\Models\UserBadge::where('user_id', $user->id)
            ->where('earned_at', '>=', $attempt->completed_at)
            ->with('badge')
            ->get()
            ->map(fn ($ub) => [
                'slug'  => $ub->badge->slug,
                'title' => $ub->badge->title,
                'xp'    => $ub->badge->xp_reward,
            ])
            ->values();

        return response()->json([
            'summary_id' => $quiz->summary_id,
            'quiz' => [
                'title'            => $quiz->title,
                'completed_at'     => $attempt->completed_at?->toISOString(),
                'duration_seconds' => $attempt->duration_seconds,
                'mode'             => $quiz->mode,
            ],
            'score' => [
                'correct'  => $attempt->correct,
                'wrong'    => $attempt->wrong,
                'skipped'  => $attempt->skipped,
                'accuracy' => $attempt->accuracy,
                'grade'    => $attempt->grade,
                'passed'   => $attempt->passed,
            ],
            'xp' => [
                'earned' => $attempt->xp_earned,
            ],
            'mastered_skills'       => $masteredSkills,
            'needs_practice'        => $needsPractice,
            'questions'             => $questions,
            'achievements_unlocked' => $unlocked,
        ]);
    }
}