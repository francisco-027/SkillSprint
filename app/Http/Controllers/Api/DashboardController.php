<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\SavedMaterial;
use App\Models\Summary;
use App\Models\Upload;
use App\Models\UserFlashcardProgress;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $xpDelta = ActivityLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDay())
            ->sum('xp');

        $lessonsCompleted = Summary::where('user_id', $user->id)->count();

        $quizAccuracy = QuizAttempt::where('user_id', $user->id)->avg('accuracy') ?? 0;

        $latestSummary = Summary::with('flashcards')
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $continueLearning = null;
        if ($latestSummary) {
            $totalCards   = $latestSummary->flashcards->count();
            $masteredCards = UserFlashcardProgress::where('user_id', $user->id)
                ->whereIn('flashcard_id', $latestSummary->flashcards->pluck('id'))
                ->where('status', 'mastered')
                ->count();
            $progress = $totalCards > 0 ? round(($masteredCards / $totalCards) * 100) : 0;

            $continueLearning = [
                'title'           => $latestSummary->title,
                'progress'        => $progress,
                'flashcard_count' => $totalCards,
                'minutes_left'    => max(1, $latestSummary->estimated_minutes - (int) ($progress / 100 * $latestSummary->estimated_minutes)),
                'summary_id'      => $latestSummary->id,
            ];
        }

        $recommended = $this->buildRecommendations($user);

        $attemptedToday = QuizAttempt::where('user_id', $user->id)
            ->whereDate('completed_at', today())
            ->pluck('quiz_id');

        $dailyQuiz = \App\Models\Quiz::whereNotIn('id', $attemptedToday)->latest()->first();
        $dailyChallenge = null;
        if ($dailyQuiz) {
            $dailyChallenge = [
                'title'      => $dailyQuiz->title,
                'questions'  => $dailyQuiz->question_count,
                'difficulty' => $dailyQuiz->difficulty,
                'quiz_id'    => $dailyQuiz->id,
                'resets_at'  => now()->endOfDay()->toISOString(),
            ];
        }

        $recentActivity = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(fn($a) => [
                'event'       => $a->event,
                'description' => $a->description,
                'xp'          => $a->xp,
                'created_at'  => $a->created_at->diffForHumans(),
            ]);

        // Progress overview is based on the user's materials (owned + saved):
        //   completed   = the material's quiz has been taken
        //   in_progress = the material has been opened/studied but quiz not taken
        //   not_started = never opened
        $items = [];

        $ownedUploads = Upload::with('summary:id,upload_id')
            ->where('user_id', $user->id)->where('status', 'done')->whereHas('summary')->get();
        foreach ($ownedUploads as $u) {
            $items[] = ['summary_id' => $u->summary->id, 'opened' => $u->opened_at !== null];
        }

        $savedMaterials = SavedMaterial::with('upload.summary:id,upload_id')
            ->where('user_id', $user->id)->get();
        foreach ($savedMaterials as $s) {
            if ($s->upload && $s->upload->summary) {
                $items[] = ['summary_id' => $s->upload->summary->id, 'opened' => (bool) $s->viewed];
            }
        }

        $summaryIds = collect($items)->pluck('summary_id')->unique();
        $matQuizzes = Quiz::whereIn('summary_id', $summaryIds)->get(['id', 'summary_id']);
        $matAttempted = QuizAttempt::where('user_id', $user->id)
            ->whereIn('quiz_id', $matQuizzes->pluck('id'))->pluck('quiz_id')->unique();
        $completedSummaryIds = $matQuizzes
            ->filter(fn ($q) => $matAttempted->contains($q->id))->pluck('summary_id')->unique();

        $totalEnrolled = count($items);
        $completed = $inProgress = $notStarted = 0;
        foreach ($items as $it) {
            if ($completedSummaryIds->contains($it['summary_id'])) $completed++;
            elseif ($it['opened']) $inProgress++;
            else $notStarted++;
        }

        $activeSkills = $user->enrolledSkills()->withPivot('progress_percent')->get()
            ->whereBetween('pivot.progress_percent', [1, 99])
            ->take(5)
            ->map(fn($s) => [
                'skill_id' => $s->id,
                'title'    => $s->title,
                'progress' => $s->pivot->progress_percent,
            ])
            ->values();

        return response()->json([
            'stats' => [
                'xp'                  => $user->xp_total ?? 0,
                'xp_delta'            => $xpDelta,
                'streak'              => $user->streak_current ?? 0,
                'streak_best'         => $user->streak_best ?? 0,
                'lessons'             => $lessonsCompleted,
                'quiz_accuracy'       => round($quizAccuracy),
            ],
            'continue_learning'  => $continueLearning,
            'recommended'        => $recommended,
            'daily_challenge'    => $dailyChallenge,
            'recent_activity'    => $recentActivity,
            'progress_overview'  => [
                'total'       => $totalEnrolled,
                'completed'   => $completed,
                'in_progress' => $inProgress,
                'not_started' => $notStarted,
            ],
            'active_skills' => $activeSkills,
        ]);
    }

    /**
     * Gemini-picked "next lessons" from the public Skill Library.
     * The selection (which materials) is cached per user for 7 days;
     * each request re-resolves them so save-state stays live.
     */
    private function buildRecommendations($user): \Illuminate\Support\Collection
    {
        // Public materials from other users that the learner could study next.
        $candidates = Upload::with(['summary:id,upload_id,title,difficulty', 'user:id,name'])
            ->where('is_public', true)
            ->where('status', 'done')
            ->where('user_id', '!=', $user->id)
            ->whereHas('summary')
            ->latest()
            ->take(20)
            ->get();

        if ($candidates->isEmpty()) {
            return collect();
        }

        $cacheKey  = "dashboard_reco_user_{$user->id}";
        $chosenIds = Cache::get($cacheKey);

        if (!$chosenIds) {
            try {
                $context = [
                    'studied_titles' => Upload::where('user_id', $user->id)->whereHas('summary')
                        ->with('summary:id,upload_id,title')->get()->pluck('summary.title')->filter()->take(8)->values(),
                    'interests' => Upload::where('user_id', $user->id)->whereNotNull('category')
                        ->distinct()->pluck('category'),
                ];
                $list = $candidates->map(fn ($u) => [
                    'id'         => $u->id,
                    'title'      => $u->summary->title,
                    'category'   => $u->category,
                    'difficulty' => $u->summary->difficulty,
                ])->values()->all();

                $ids = app(GeminiService::class)->recommendMaterials($context, $list);
                $valid = collect($ids)->filter(fn ($id) => $candidates->contains('id', $id))->take(2)->values();

                if ($valid->isNotEmpty()) {
                    $chosenIds = $valid->all();
                    Cache::put($cacheKey, $chosenIds, now()->addDays(7)); // weekly refresh
                }
            } catch (\Throwable $e) {
                Log::warning('Dashboard recommendations failed: ' . $e->getMessage());
            }
        }

        // Fallback (no key / rate limited / no pick): newest two, not cached.
        if (!$chosenIds) {
            $chosenIds = $candidates->take(2)->pluck('id')->all();
        }

        $savedIds = SavedMaterial::where('user_id', $user->id)->pluck('upload_id');

        // Resolve directly so a cached pick survives even if it left the candidate window,
        // and gets dropped if it was deleted / made private since.
        $byId = Upload::with(['summary:id,upload_id,title,difficulty', 'user:id,name'])
            ->whereIn('id', $chosenIds)
            ->where('is_public', true)
            ->where('status', 'done')
            ->whereHas('summary')
            ->get()
            ->keyBy('id');

        return collect($chosenIds)
            ->map(fn ($id) => $byId->get($id))
            ->filter()
            ->map(fn ($u) => [
                'upload_id'  => $u->id,
                'summary_id' => $u->summary->id,
                'title'      => $u->summary->title,
                'category'   => $u->category,
                'difficulty' => $u->summary->difficulty,
                'owner'      => $u->user?->name ?? 'Unknown',
                'is_saved'   => $savedIds->contains($u->id),
            ])
            ->values();
    }
}