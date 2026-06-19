<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function index(Request $request)
    {
        $skills = Skill::query()
            ->withCount('enrolledUsers')
            ->when($request->search,   fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->level,    fn($q) => $q->where('level', $request->level))
            ->when($request->is_featured, fn($q) => $q->where('is_featured', true))
            ->orderBy($request->sort === 'newest' ? 'created_at' : 'enrolled_users_count', 'desc')
            ->get()
            ->each(function ($skill) {
                // Real learner count comes from actual enrollments, not the seeded number.
                $skill->learner_count = $skill->enrolled_users_count;
                $skill->makeHidden('enrolled_users_count');
            });

        return response()->json($skills);
    }

    public function show(Skill $skill)
    {
        return response()->json($skill);
    }
}