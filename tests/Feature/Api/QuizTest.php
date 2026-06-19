<?php

namespace Tests\Feature\Api;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_quiz_returns_data(): void
    {
        $this->markTestSkipped('Requires seeded summary/upload data — setup pending.');
    }

    public function test_submit_saves_attempt_and_awards_xp(): void
    {
        $this->markTestSkipped('Requires seeded summary/upload data — setup pending.');
    }
}