<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Upload extends Model
{
    protected $fillable = [
        'user_id', 'original_filename', 'title', 'category', 'is_public', 'type', 'raw_content', 'file_path',
        'word_count', 'status', 'processed_at', 'opened_at',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
            'opened_at'    => 'datetime',
            'is_public'    => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function summary(): HasOne
    {
        return $this->hasOne(Summary::class);
    }
}