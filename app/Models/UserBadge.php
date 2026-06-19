<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    protected $table = 'user_badges';

    protected $fillable = [
        'user_id', 'badge_id', 'earned_at', 'is_new',
    ];

    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
            'is_new' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}