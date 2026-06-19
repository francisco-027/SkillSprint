<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\QuizAttempt;
use App\Models\Summary;
use App\Models\User;
use App\Models\UserFlashcardProgress;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $range = $request->input('range', 'week');

        $since = match ($range) {
            'month' => now()->subMonth(),
            'all'   => now()->subYear(),
            default => now()->subWeek(),
        };

        $totalMinutes   = ActivityLog::where('user_id', $user->id)->sum(DB::raw('COALESCE(xp, 0)')) / 10;
        $quizAccuracy   = QuizAttempt::where('user_id', $user->id)->avg('accuracy') ?? 0;
        $xpEarned       = ActivityLog::where('user_id', $user->id)->where('created_at', '>=', $since)->sum('xp');
        $lessonsCompleted = Summary::where('user_id', $user->id)->count();

        // Previous period (for deltas)
        $periodLength = now()->diffInDays($since) ?: 7;
        $prevSince    = (clone $since)->subDays($periodLength);
        $xpPrev       = ActivityLog::where('user_id', $user->id)
            ->whereBetween('created_at', [$prevSince, $since])->sum('xp');
        $xpDelta      = $xpEarned - $xpPrev;
        $hoursDelta   = $xpPrev > 0 ? round((($xpEarned - $xpPrev) / $xpPrev) * 100) : 0;

        // Time totals
        $hoursIn = fn ($from) => round(ActivityLog::where('user_id', $user->id)
            ->where('created_at', '>=', $from)->sum(DB::raw('COALESCE(xp, 0)')) / 10 / 60, 1);
        $todayHours = $hoursIn(now()->startOfDay());
        $weekHours  = $hoursIn(now()->subWeek());
        $monthHours = $hoursIn(now()->subMonth());

        // Time breakdown by activity type
        $eventXp = ActivityLog::where('user_id', $user->id)
            ->select('event', DB::raw('SUM(COALESCE(xp, 0)) as xp'))
            ->groupBy('event')->pluck('xp', 'event');
        $breakdown = ['reading' => 0, 'flashcards' => 0, 'quizzes' => 0, 'learning_path' => 0];
        foreach ($eventXp as $event => $xp) {
            $h = $xp / 10 / 60;
            if (str_contains($event, 'summary') || str_contains($event, 'lesson')) $breakdown['reading'] += $h;
            elseif (str_contains($event, 'flash')) $breakdown['flashcards'] += $h;
            elseif (str_contains($event, 'quiz')) $breakdown['quizzes'] += $h;
            else $breakdown['learning_path'] += $h;
        }
        $breakdown = array_map(fn ($h) => round($h, 1), $breakdown);

        // Streak calendar (last 14 days)
        $streakCalendar = collect(range(13, 0))->map(function ($d) use ($user) {
            $date = now()->subDays($d);
            return [
                'date'      => $date->toDateString(),
                'label'     => substr($date->format('D'), 0, 1),
                'completed' => ActivityLog::where('user_id', $user->id)
                    ->whereDate('created_at', $date->toDateString())->exists(),
            ];
        })->values();

        $weeklyProgress = collect(range(6, 0))->map(function ($daysAgo) use ($user) {
            return (int) ActivityLog::where('user_id', $user->id)
                ->whereDate('created_at', now()->subDays($daysAgo)->toDateString())
                ->sum('xp');
        })->values()->toArray();

        $quizAccuracyBySubject = QuizAttempt::where('quiz_attempts.user_id', $user->id)
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->select('quizzes.title as label', DB::raw('ROUND(AVG(quiz_attempts.accuracy)) as value'))
            ->groupBy('quizzes.id', 'quizzes.title')
            ->get()
            ->toArray();

        $skillProgress = $user->enrolledSkills()
            ->withPivot('progress_percent')
            ->get()
            ->map(fn($s) => [
                'title'       => $s->title,
                'level'       => $s->level,
                'proficiency' => $s->pivot->progress_percent,
            ])
            ->toArray();

        $skillGrowthLabels = collect(range(7, 1))->map(fn($w) => "Wk {$w}")->values();
        $skillGrowthSeries = [];
        $summaries = Summary::where('user_id', $user->id)->with('flashcards')->get();
        foreach ($summaries->take(3) as $summary) {
            $flashcardIds = $summary->flashcards->pluck('id');
            $series = collect(range(7, 1))->map(function ($weeksAgo) use ($user, $flashcardIds) {
                $weeksAgoDate = now()->subWeeks($weeksAgo)->endOfWeek();
                return (int) UserFlashcardProgress::where('user_id', $user->id)
                    ->whereIn('flashcard_id', $flashcardIds)
                    ->where('status', 'mastered')
                    ->where('updated_at', '<=', $weeksAgoDate)
                    ->count();
            })->values()->toArray();
            $skillGrowthSeries[] = ['label' => $summary->title, 'values' => $series];
        }

        $aiInsights = $this->buildInsights($user, [
            'current_streak'           => $user->streak_current ?? 0,
            'best_streak'              => $user->streak_best ?? 0,
            'avg_quiz_accuracy'        => round($quizAccuracy),
            'total_hours'              => round($totalMinutes / 60, 1),
            'lessons_completed'        => $lessonsCompleted,
            'weekly_minutes'           => $weeklyProgress,
            'time_breakdown_hours'     => $breakdown,
            'quiz_accuracy_by_subject' => $quizAccuracyBySubject,
            'skills'                   => $skillProgress,
        ], $lessonsCompleted, $xpEarned);

        $skillRadar = [
            'labels' => collect($skillProgress)->pluck('title')->take(6)->values(),
            'values' => collect($skillProgress)->pluck('proficiency')->take(6)->values(),
        ];

        return response()->json([
            'stats' => [
                'total_hours'        => round($totalMinutes / 60, 1),
                'total_hours_delta'  => $hoursDelta,
                'quiz_accuracy'      => round($quizAccuracy),
                'quiz_accuracy_delta'=> 5,
                'streak_days'        => $user->streak_current ?? 0,
                'streak_best'        => $user->streak_best ?? 0,
                'lessons_completed'  => $lessonsCompleted,
                'xp_earned'          => $xpEarned,
                'xp_delta'           => $xpDelta,
            ],
            'time_totals' => [
                'today' => $todayHours,
                'week'  => $weekHours,
                'month' => $monthHours,
            ],
            'time_breakdown'           => $breakdown,
            'streak_calendar'          => $streakCalendar,
            'weekly_progress'          => $weeklyProgress,
            'skill_growth'             => [
                'weeks'  => $skillGrowthLabels,
                'series' => $skillGrowthSeries,
            ],
            'quiz_accuracy_by_subject' => $quizAccuracyBySubject,
            'skill_radar'              => $skillRadar,
            'skill_progress'           => $skillProgress,
            'ai_insights'              => $aiInsights,
        ]);
    }

    /**
     * Personalized insights from Gemini, cached per user for 7 days.
     * Falls back to a static message if the user has no activity yet
     * or if the Gemini call fails (missing key, rate limit, etc.).
     */
    private function buildInsights(User $user, array $context, int $lessons, int $xp): array
    {
        // New/empty accounts: don't spend an API call on nothing.
        if ($lessons === 0 && $xp === 0) {
            return [[
                'title' => 'Start Your First Lesson',
                'body'  => 'Add a material and take a quiz to unlock personalized, AI-powered insights here.',
            ]];
        }

        $cacheKey = "ai_insights_user_{$user->id}";

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            $insights = app(GeminiService::class)->generateInsights($context);

            if (!empty($insights)) {
                Cache::put($cacheKey, $insights, now()->addDays(7)); // weekly refresh
                return $insights;
            }
        } catch (\Throwable $e) {
            Log::warning('AI insights generation failed: ' . $e->getMessage());
        }

        // Fallback — not cached, so it retries on the next load.
        return [[
            'title' => 'Consistent Practice',
            'body'  => 'Keep up your daily learning habit to build long-term retention.',
        ]];
    }
}