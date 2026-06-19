<?php

namespace Tests\Feature\Api;

use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashcardTest extends TestCase
{
    use RefreshDatabase;

    public function test_flashcard_deck_returns_data(): void
    {
        $this->markTestSkipped('Requires seeded summary data — setup pending.');
    }

    public function test_update_card_status(): void
    {
        $this->markTestSkipped('Requires seeded summary data — setup pending.');
    }
}