<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XpLog extends Model
{
    protected $table = 'xp_log';

    protected $fillable = [
        'user_id', 'event', 'description', 'xp',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}