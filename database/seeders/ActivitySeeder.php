<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $activities = [];

        // Spread events over 14 days with varied types
        for ($daysAgo = 0; $daysAgo < 14; $daysAgo++) {
            $date = now()->subDays($daysAgo);

            // 1-2 activities per day
            $dayActivities = match (rand(0, 3)) {
                0 => [
                    ['event' => 'quiz_completed', 'description' => 'Completed quiz session', 'xp' => rand(30, 100)],
                ],
                1 => [
                    ['event' => 'quiz_completed', 'description' => 'Completed quiz session', 'xp' => rand(30, 100)],
                    ['event' => 'flashcard_mastered', 'description' => 'Mastered flashcards', 'xp' => rand(20, 80)],
                ],
                2 => [
                    ['event' => 'upload_processed', 'description' => 'Processed learning materials', 'xp' => rand(50, 120)],
                ],
                3 => [
                    ['event' => 'badge_unlocked', 'description' => 'Earned a new badge', 'xp' => rand(100, 200)],
                    ['event' => 'flashcard_mastered', 'description' => 'Mastered flashcard deck', 'xp' => rand(40, 90)],
                ],
            };

            foreach ($dayActivities as $act) {
                $act['created_at'] = $date;
                $activities[] = $act;
            }
        }

        // Add some specific higher-XP milestones
        $activities[] = ['event' => 'badge_unlocked', 'description' => 'Streak Master Badge Earned — 7-day milestone', 'xp' => 150, 'created_at' => now()->subDay()];
        $activities[] = ['event' => 'badge_unlocked', 'description' => 'Quiz Champion Badge Earned — Scored 90%+ on 5 quizzes', 'xp' => 200, 'created_at' => now()->subDay()];
        $activities[] = ['event' => 'quiz_completed', 'description' => 'ML Fundamentals Quiz — 88% · 12 questions', 'xp' => 88, 'created_at' => now()->subDay()];
        $activities[] = ['event' => 'upload_processed', 'description' => 'AI Summary — Neural Networks · Read & completed', 'xp' => 30, 'created_at' => now()->subDays(4)];

        foreach ($activities as $activity) {
            ActivityLog::create([
                'user_id'     => 1,
                'event'       => $activity['event'],
                'description' => $activity['description'],
                'xp'          => $activity['xp'],
                'created_at'  => $activity['created_at'],
                'updated_at'  => $activity['created_at'],
            ]);
        }
    }
}