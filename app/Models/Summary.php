<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Summary extends Model
{
    protected $fillable = [
        'user_id', 'upload_id', 'title', 'difficulty', 'estimated_minutes',
        'source_filename', 'content_sections', 'key_terms', 'timeline_steps',
    ];

    protected function casts(): array
    {
        return [
            'content_sections' => 'array',
            'key_terms' => 'array',
            'timeline_steps' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }

    public function flashcards()
    {
        return $this->hasMany(Flashcard::class);
    }
}