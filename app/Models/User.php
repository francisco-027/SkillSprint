<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'xp_total',
        'level',
        'daily_goal_minutes',
        'streak_current',
        'streak_best',
        'onboarding_completed_at',
        'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'       => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'last_active_at'          => 'datetime',
            'password'                => 'hashed',
            'xp_total'                => 'integer',
            'level'                   => 'integer',
            'daily_goal_minutes'      => 'integer',
            'streak_current'          => 'integer',
            'streak_best'             => 'integer',
        ];
    }

    public function preference()
    {
        return $this->hasOne(UserPreference::class);
    }

    public function uploads()
    {
        return $this->hasMany(Upload::class);
    }

    public function summaries()
    {
        return $this->hasMany(Summary::class);
    }

    public function xpLogs()
    {
        return $this->hasMany(XpLog::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function userBadges()
    {
        return $this->hasMany(UserBadge::class);
    }

    public function flashcardProgress()
    {
        return $this->hasMany(UserFlashcardProgress::class);
    }

    public function enrolledSkills()
    {
        return $this->belongsToMany(Skill::class, 'user_skills')
            ->withPivot('progress_percent', 'enrolled_at', 'completed_at')
            ->withTimestamps();
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}