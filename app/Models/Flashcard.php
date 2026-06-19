<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Flashcard extends Model
{
    protected $fillable = [
        'summary_id', 'question', 'answer', 'category', 'status', 'sort_order',
    ];

    public function summary(): BelongsTo
    {
        return $this->belongsTo(Summary::class);
    }
}