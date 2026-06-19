<?php

namespace App\Providers;

use App\Models\QuizAttempt;
use App\Models\Summary;
use App\Models\Upload;
use App\Policies\QuizAttemptPolicy;
use App\Policies\SummaryPolicy;
use App\Policies\UploadPolicy;
use App\Services\GamificationService;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GamificationService::class);
        $this->app->singleton(GeminiService::class);
    }

    public function boot(): void
    {
        Gate::policy(Upload::class, UploadPolicy::class);
        Gate::policy(Summary::class, SummaryPolicy::class);
        Gate::policy(QuizAttempt::class, QuizAttemptPolicy::class);
    }
}