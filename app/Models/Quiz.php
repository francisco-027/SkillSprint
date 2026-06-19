<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quiz extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'summary_id', 'title', 'mode', 'question_count', 'difficulty',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function summary(): BelongsTo
    {
        return $this->belongsTo(Summary::class);
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }
}