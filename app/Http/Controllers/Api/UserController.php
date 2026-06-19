<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\UserPreference;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id'                     => $user->id,
            'name'                   => $user->name,
            'email'                  => $user->email,
            'avatar'                 => $user->avatar,
            'bio'                    => $user->bio,
            'xp_total'               => $user->xp_total,
            'level'                  => $user->level,
            'daily_goal_minutes'     => $user->daily_goal_minutes,
            'streak_current'         => $user->streak_current,
            'streak_best'            => $user->streak_best,
            'onboarding_completed_at'=> $user->onboarding_completed_at,
            'last_active_at'         => $user->last_active_at,
        ]);
    }

    public function preferences(Request $request)
    {
        $prefs = UserPreference::firstOrCreate(['user_id' => $request->user()->id]);

        return response()->json($prefs);
    }

    public function updatePreferences(Request $request)
    {
        $prefs = UserPreference::firstOrCreate(['user_id' => $request->user()->id]);
        $prefs->update($request->all());

        return response()->json($prefs);
    }

    public function enroll(Request $request)
    {
        $request->validate(['skill_id' => 'required|exists:skills,id']);
        $user = $request->user();

        if (!$user->enrolledSkills()->where('skill_id', $request->skill_id)->exists()) {
            $user->enrolledSkills()->attach($request->skill_id, [
                'progress_percent' => 0,
                'enrolled_at'      => now(),
            ]);
        }

        return response()->json(['enrolled' => true, 'skill_id' => $request->skill_id]);
    }

    public function enrolledSkills(Request $request)
    {
        $skills = $request->user()->enrolledSkills()->withPivot('progress_percent', 'enrolled_at', 'completed_at')->get();

        return response()->json($skills);
    }

    public function unenroll(Request $request, $skillId)
    {
        $request->user()->enrolledSkills()->detach($skillId);

        return response()->json(['unenrolled' => true, 'skill_id' => $skillId]);
    }
}