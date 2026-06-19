<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_returns_data(): void
    {
        $user = User::factory()->create(['xp_total' => 1000, 'streak_current' => 5]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/analytics');

        $response->assertOk()
            ->assertJsonStructure([
                'stats',
                'weekly_progress',
                'skill_growth',
                'quiz_accuracy_by_subject',
                'skill_progress',
                'ai_insights',
            ]);
    }

    public function test_analytics_filter_by_range(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/analytics?range=month');

        $response->assertOk();
    }
}