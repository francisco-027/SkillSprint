<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            UserPreferenceSeeder::class,
            SkillSeeder::class,
            BadgeSeeder::class,
            SummarySeeder::class,
            FlashcardSeeder::class,
            QuizSeeder::class,
            ActivitySeeder::class,
        ]);
    }
}