<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $table = 'user_preferences';

    protected $fillable = [
        'user_id',
        'dyslexia_font', 'font_size', 'letter_spacing', 'line_height', 'word_spacing',
        'high_contrast', 'contrast_theme', 'bold_text', 'focus_indicators',
        'wider_reading_column', 'highlight_active_line', 'font_family',
        'tts_enabled', 'tts_speed', 'auto_read_cards', 'auto_read_questions',
        'reduce_motion', 'slow_flip_speed',
        'learning_goals', 'learning_pace', 'difficulty_default',
        'output_language', 'simplify_language',
        'visual_diagrams', 'notifications', 'offline_mode',
    ];

    protected function casts(): array
    {
        return [
            'learning_goals' => 'array',
            'dyslexia_font' => 'boolean',
            'high_contrast' => 'boolean',
            'bold_text' => 'boolean',
            'focus_indicators' => 'boolean',
            'wider_reading_column' => 'boolean',
            'highlight_active_line' => 'boolean',
            'tts_enabled' => 'boolean',
            'auto_read_cards' => 'boolean',
            'auto_read_questions' => 'boolean',
            'reduce_motion' => 'boolean',
            'slow_flip_speed' => 'boolean',
            'simplify_language' => 'boolean',
            'visual_diagrams' => 'boolean',
            'notifications' => 'boolean',
            'offline_mode' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}