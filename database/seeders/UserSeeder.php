<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'               => 'Alex Rivera',
            'email'              => 'alex@skillsprint.dev',
            'password'           => bcrypt('password'),
            'avatar'             => null,
            'bio'                => 'Passionate about AI, data science, and continuous learning. Currently mastering machine learning fundamentals.',
            'xp_total'           => 2545,
            'level'              => 12,
            'daily_goal_minutes' => 30,
            'streak_current'     => 7,
            'streak_best'        => 14,
            'onboarding_completed_at' => now(),
            'last_active_at'     => now(),
        ]);

        User::create([
            'name'               => 'Marcus T.',
            'email'              => 'marcus@example.com',
            'password'           => bcrypt('password'),
            'avatar'             => null,
            'bio'                => 'Full-stack developer exploring AI and cloud computing.',
            'xp_total'           => 3820,
            'level'              => 15,
            'daily_goal_minutes' => 45,
            'streak_current'     => 12,
            'streak_best'        => 21,
            'onboarding_completed_at' => now()->subMonths(2),
            'last_active_at'     => now(),
        ]);

        User::create([
            'name'               => 'Sarah Kim',
            'email'              => 'sarah@example.com',
            'password'           => bcrypt('password'),
            'avatar'             => null,
            'bio'                => 'Data scientist specializing in NLP and computer vision.',
            'xp_total'           => 3610,
            'level'              => 14,
            'daily_goal_minutes' => 40,
            'streak_current'     => 9,
            'streak_best'        => 18,
            'onboarding_completed_at' => now()->subMonth(),
            'last_active_at'     => now()->subDay(),
        ]);

        User::create([
            'name'               => 'James L.',
            'email'              => 'james@example.com',
            'password'           => bcrypt('password'),
            'avatar'             => null,
            'bio'                => 'DevOps engineer learning ML for automation workflows.',
            'xp_total'           => 3100,
            'level'              => 13,
            'daily_goal_minutes' => 25,
            'streak_current'     => 5,
            'streak_best'        => 10,
            'onboarding_completed_at' => now()->subWeeks(3),
            'last_active_at'     => now()->subDays(2),
        ]);
    }
}