<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementTest extends TestCase
{
    use RefreshDatabase;

    public function test_achievements_returns_data(): void
    {
        $user = User::factory()->create(['xp_total' => 500, 'level' => 3, 'streak_current' => 5]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/achievements');

        $response->assertOk()
            ->assertJsonStructure([
                'profile',
                'badges',
                'xp_history',
                'leaderboard',
            ]);
    }
}