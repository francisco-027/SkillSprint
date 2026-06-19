<?php

namespace App\Http\Middleware;

use App\Services\GamificationService;
use Closure;
use Illuminate\Http\Request;

class UpdateStreak
{
    public function __construct(private GamificationService $gamification) {}

    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            $this->gamification->updateStreak($request->user());
        }

        return $next($request);
    }
}