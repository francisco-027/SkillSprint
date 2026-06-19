<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Badge;
use App\Models\QuizAttempt;
use App\Models\Summary;
use App\Models\Upload;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserFlashcardProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AchievementController extends Controller
{
    public function index(Request $request)
    {
        $user           = $request->user();
        $badges         = Badge::all();
        $earnedBadgeIds = UserBadge::where('user_id', $user->id)->pluck('badge_id')->toArray();

        // Metrics used to show progress toward locked badges.
        $streak    = $user->streak_current ?? 0;
        $mastered  = UserFlashcardProgress::where('user_id', $user->id)->where('status', 'mastered')->count();
        $uploads   = Upload::where('user_id', $user->id)->count();
        $summaries = Summary::where('user_id', $user->id)->count();
        $passed    = QuizAttempt::where('user_id', $user->id)->where('passed', true)->count();

        $daysActive = ActivityLog::where('user_id', $user->id)
            ->distinct()
            ->count(DB::raw('DATE(created_at)')); // Postgres-safe distinct-day count

        $progressFor = function (string $slug) use ($streak, $mastered, $uploads, $summaries, $passed, $daysActive) {
            $map = [
                'streak-master'     => [$streak, 7],
                'fortnight-warrior' => [$streak, 14],
                'consistency-king'  => [$daysActive, 30],
                'flashcard-hero'    => [$mastered, 100],
                'content-creator'   => [$uploads, 5],
                'knowledge-vault'   => [$uploads, 20],
                'speed-reader'      => [$summaries, 10],
                'bookworm'          => [$summaries, 50],
                'quiz-champion'     => [$passed, 5],
            ];
            if (!isset($map[$slug])) return null;
            [$current, $target] = $map[$slug];
            return ['current' => min($current, $target), 'target' => $target];
        };

        $badgeData = $badges->map(function ($badge) use ($earnedBadgeIds, $user, $progressFor) {
            $earned    = in_array($badge->id, $earnedBadgeIds);
            $userBadge = $earned
                ? UserBadge::where('user_id', $user->id)->where('badge_id', $badge->id)->first()
                : null;

            return [
                'slug'        => $badge->slug,
                'title'       => $badge->title,
                'description' => $badge->description,
                'icon'        => $badge->icon,
                'xp_reward'   => $badge->xp_reward,
                'earned'      => $earned,
                'is_new'      => $userBadge?->is_new ?? false,
                'earned_at'   => $userBadge?->earned_at?->toISOString(),
                'progress'    => $earned ? null : $progressFor($badge->slug),
            ];
        });

        $xpHistory = ActivityLog::where('user_id', $user->id)
            ->where('xp', '>', 0)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(fn($a) => [
                'event'       => $a->event,
                'description' => $a->description,
                'xp'          => $a->xp,
                'created_at'  => $a->created_at->diffForHumans(),
            ]);

        $leaderboard = User::orderBy('xp_total', 'desc')
            ->take(10)
            ->get()
            ->values()
            ->map(fn($u, $index) => [
                'rank'            => $index + 1,
                'name'            => $u->name,
                'level'           => $u->level ?? 1,
                'xp'              => $u->xp_total ?? 0,
                'is_current_user' => $u->id === $user->id,
            ]);

        $rank = User::where('xp_total', '>', $user->xp_total ?? 0)->count() + 1;

        // XP needed to overtake the next-higher-ranked learner.
        $nextAbove = User::where('xp_total', '>', $user->xp_total ?? 0)
            ->orderBy('xp_total', 'asc')->first();
        $xpToNextRank = $nextAbove ? max(0, $nextAbove->xp_total - ($user->xp_total ?? 0) + 1) : 0;

        return response()->json([
            'profile' => [
                'name'          => $user->name,
                'bio'           => $user->bio,
                'xp'            => $user->xp_total ?? 0,
                'level'         => $user->level ?? 1,
                'streak'        => $streak,
                'badges_earned' => count($earnedBadgeIds),
                'total_badges'  => $badges->count(),
                'rank'          => $rank,
                'days_active'   => $daysActive,
            ],
            'badges'      => $badgeData,
            'xp_history'  => $xpHistory,
            'leaderboard' => $leaderboard,
            'current_user_entry' => [
                'rank'  => $rank,
                'name'  => $user->name,
                'level' => $user->level ?? 1,
                'xp'    => $user->xp_total ?? 0,
            ],
            'xp_to_next_rank' => $xpToNextRank,
        ]);
    }
}
