<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = [
        'slug', 'title', 'description', 'icon', 'xp_reward', 'criteria',
    ];

    protected function casts(): array
    {
        return [
            'criteria' => 'array',
        ];
    }
}