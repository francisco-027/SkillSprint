<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'name'                   => $this->name,
            'email'                  => $this->email,
            'avatar'                 => $this->avatar,
            'xp_total'               => $this->xp_total ?? 0,
            'level'                  => $this->level ?? 1,
            'streak_current'         => $this->streak_current ?? 0,
            'streak_best'            => $this->streak_best ?? 0,
            'daily_goal_minutes'     => $this->daily_goal_minutes ?? 20,
            'onboarding_completed_at'=> $this->onboarding_completed_at,
        ];
    }
}