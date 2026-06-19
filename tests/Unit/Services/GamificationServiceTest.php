<?php

namespace Tests\Unit\Services;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GamificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_level_calculation(): void
    {
        $service = app(GamificationService::class);
        $this->assertEquals(1,  $service->calculateLevel(0));
        $this->assertEquals(1,  $service->calculateLevel(199));
        $this->assertEquals(2,  $service->calculateLevel(200));
        $this->assertEquals(6,  $service->calculateLevel(1000));
        $this->assertEquals(100, $service->calculateLevel(99999));
    }

    public function test_award_xp_increments_user(): void
    {
        $user = User::factory()->create(['xp_total' => 0, 'level' => 1]);

        $service = app(GamificationService::class);
        $service->awardXp($user, 'quiz_completed', 150);

        $user->refresh();
        $this->assertEquals(150, $user->xp_total);
        $this->assertEquals(1, $user->level);
    }

    public function test_xp_zero_does_nothing(): void
    {
        $user = User::factory()->create(['xp_total' => 100]);

        $service = app(GamificationService::class);
        $service->awardXp($user, 'quiz_completed', 0);

        $user->refresh();
        $this->assertEquals(100, $user->xp_total);
    }

    public function test_streak_increments_for_yesterday_activity(): void
    {
        $user = User::factory()->create([
            'streak_current' => 3,
            'streak_best'    => 3,
            'last_active_at' => now()->subDay(),
        ]);

        $service = app(GamificationService::class);
        $service->updateStreak($user);
        $user->refresh();

        $this->assertEquals(4, $user->streak_current);
        $this->assertEquals(4, $user->streak_best);
    }

    public function test_streak_resets_after_gap(): void
    {
        $user = User::factory()->create([
            'streak_current' => 10,
            'last_active_at' => now()->subDays(3),
        ]);

        $service = app(GamificationService::class);
        $service->updateStreak($user);
        $user->refresh();

        $this->assertEquals(1, $user->streak_current);
    }

    public function test_streak_does_not_change_within_same_day(): void
    {
        $user = User::factory()->create([
            'streak_current' => 5,
            'last_active_at' => now(),
        ]);

        $service = app(GamificationService::class);
        $service->updateStreak($user);
        $user->refresh();

        $this->assertEquals(5, $user->streak_current);
    }

    public function test_check_badge_unlock_streak_master(): void
    {
        Badge::create([
            'slug'        => 'streak-master',
            'title'       => 'Streak Master',
            'description' => '7-day streak',
            'icon'        => 'star',
            'xp_reward'   => 100,
        ]);

        $user = User::factory()->create(['streak_current' => 7]);

        $service = app(GamificationService::class);
        $unlocked = $service->checkBadgeUnlock($user);

        $this->assertCount(1, $unlocked);
        $this->assertEquals('streak-master', $unlocked[0]['slug']);

        $this->assertDatabaseHas('user_badges', [
            'user_id'  => $user->id,
            'badge_id' => 1,
        ]);
    }

    public function test_check_badge_unlock_skips_already_earned(): void
    {
        $badge = Badge::create([
            'slug'        => 'streak-master',
            'title'       => 'Streak Master',
            'description' => '7-day streak',
            'icon'        => 'star',
            'xp_reward'   => 100,
        ]);

        $user = User::factory()->create(['streak_current' => 7]);
        UserBadge::create(['user_id' => $user->id, 'badge_id' => $badge->id, 'earned_at' => now()]);

        $service = app(GamificationService::class);
        $unlocked = $service->checkBadgeUnlock($user);

        $this->assertCount(0, $unlocked);
    }
}