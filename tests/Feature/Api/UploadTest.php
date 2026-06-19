<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_text_content(): void
    {
        $this->markTestSkipped('Requires GEMINI_API_KEY in test environment.');
    }

    public function test_upload_recent_returns_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/uploads/recent');

        $response->assertOk();
    }
}