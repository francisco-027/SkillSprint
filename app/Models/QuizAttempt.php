<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    protected $fillable = [
        'user_id', 'quiz_id', 'answers', 'correct', 'wrong', 'skipped',
        'accuracy', 'grade', 'passed', 'xp_earned', 'duration_seconds', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'answers'      => 'array',
            'passed'       => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}