<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RotateDailyChallenge extends Command
{
    protected $signature = 'app:rotate-daily-challenge';

    protected $description = 'Select a random active quiz as the daily challenge';

    public function handle(): void
    {
        $quiz = \App\Models\Quiz::inRandomOrder()->first();
        if ($quiz) {
            cache()->put('daily_challenge_quiz_id', $quiz->id, now()->addDay());
            $this->info("Daily challenge set to quiz #{$quiz->id}");
        }
    }
}