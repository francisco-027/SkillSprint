<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Badge;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserFlashcardProgress;
use App\Models\XpLog;
use Carbon\Carbon;

class GamificationService
{
    public function awardXp(User $user, string $event, int $xp): void
    {
        if ($xp <= 0) return;

        XpLog::create([
            'user_id'     => $user->id,
            'event'       => $event,
            'description' => $this->descriptionFor($event),
            'xp'          => $xp,
        ]);

        ActivityLog::create([
            'user_id'     => $user->id,
            'event'       => $event,
            'description' => $this->descriptionFor($event),
            'xp'          => $xp,
        ]);

        $user->xp_total = ($user->xp_total ?? 0) + $xp;
        $user->level    = $this->calculateLevel($user->xp_total);
        $user->save();
    }

    public function calculateLevel(int $xpTotal): int
    {
        return min(100, (int) floor($xpTotal / 200) + 1);
    }

    public function updateStreak(User $user): void
    {
        $now          = Carbon::now();
        $lastActive   = $user->last_active_at ? Carbon::parse($user->last_active_at) : null;

        if (!$lastActive) {
            $user->streak_current = 1;
            $user->streak_best    = 1;
        } elseif ($lastActive->isToday()) {
            // Already active today — streak unchanged
        } elseif ($lastActive->isYesterday()) {
            $user->streak_current = ($user->streak_current ?? 0) + 1;
            if ($user->streak_current > ($user->streak_best ?? 0)) {
                $user->streak_best = $user->streak_current;
            }
        } else {
            $user->streak_current = 1;
        }

        $user->last_active_at = $now;
        $user->save();
    }

    public function checkBadgeUnlock(User $user): array
    {
        $allBadges      = Badge::all();
        $earnedIds      = UserBadge::where('user_id', $user->id)->pluck('badge_id')->toArray();
        $newlyUnlocked  = [];

        foreach ($allBadges as $badge) {
            if (in_array($badge->id, $earnedIds)) continue;

            if ($this->criteriaIsMet($user, $badge->slug)) {
                UserBadge::create([
                    'user_id'   => $user->id,
                    'badge_id'  => $badge->id,
                    'earned_at' => now(),
                    'is_new'    => true,
                ]);

                $this->awardXp($user, 'badge_unlocked', $badge->xp_reward);

                $newlyUnlocked[] = [
                    'slug'  => $badge->slug,
                    'title' => $badge->title,
                    'xp'    => $badge->xp_reward,
                ];
            }
        }

        return $newlyUnlocked;
    }

    private function criteriaIsMet(User $user, string $slug): bool
    {
        return match ($slug) {
            'quiz-champion' => QuizAttempt::where('user_id', $user->id)->where('passed', true)->count() >= 1,

            'perfect-score' => QuizAttempt::where('user_id', $user->id)->where('accuracy', 100)->count() >= 1,

            'quick-learner' => QuizAttempt::where('user_id', $user->id)
                ->whereNotNull('duration_seconds')
                ->where('duration_seconds', '<', 180)
                ->where('passed', true)
                ->count() >= 1,

            'flashcard-hero' => UserFlashcardProgress::where('user_id', $user->id)
                ->where('status', 'mastered')
                ->count() >= 100,

            'streak-master' => ($user->streak_current ?? 0) >= 7,

            'streak-legend' => ($user->streak_current ?? 0) >= 30,

            'level-10' => ($user->level ?? 1) >= 10,

            default => false,
        };
    }

    private function descriptionFor(string $event): string
    {
        return match ($event) {
            'quiz_completed'     => 'Completed a quiz',
            'badge_unlocked'     => 'Unlocked a badge',
            'flashcard_mastered' => 'Mastered a flashcard',
            'upload_processed'   => 'Processed an upload',
            default              => ucfirst(str_replace('_', ' ', $event)),
        };
    }
}