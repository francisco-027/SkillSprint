<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'category', 'level', 'icon',
        'estimated_minutes', 'learner_count', 'tags',
        'is_featured', 'is_popular',
    ];

    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_skills');
    }

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_featured' => 'boolean',
            'is_popular' => 'boolean',
        ];
    }
}