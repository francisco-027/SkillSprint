<?php

namespace Database\Seeders;

use App\Models\UserPreference;
use Illuminate\Database\Seeder;

class UserPreferenceSeeder extends Seeder
{
    public function run(): void
    {
        UserPreference::create([
            'user_id'             => 1,
            'dyslexia_font'       => false,
            'font_size'           => 16,
            'letter_spacing'      => 0,
            'line_height'         => 1.5,
            'word_spacing'        => 0,
            'high_contrast'       => false,
            'contrast_theme'      => 'default',
            'bold_text'           => false,
            'focus_indicators'    => true,
            'tts_enabled'         => true,
            'tts_speed'           => 1.0,
            'auto_read_cards'     => false,
            'auto_read_questions' => false,
            'reduce_motion'       => false,
            'slow_flip_speed'     => false,
            'learning_goals'      => json_encode(['career_advancement', 'certification_prep']),
            'learning_pace'       => 'regular',
            'difficulty_default'  => 'beginner',
            'output_language'     => 'English',
            'simplify_language'   => true,
            'visual_diagrams'     => true,
            'notifications'       => true,
            'offline_mode'        => true,
        ]);
    }
}