<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavedMaterial;
use App\Models\Upload;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    /**
     * All public materials from every user (the Skill Library feed).
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $uploads = Upload::with(['summary:id,upload_id,title,difficulty,estimated_minutes', 'user:id,name'])
            ->where('is_public', true)
            ->where('status', 'done')
            ->whereHas('summary')
            ->orderBy('created_at', 'desc')
            ->get();

        $uploadIds = $uploads->pluck('id');

        $savedByUser = SavedMaterial::where('user_id', $userId)
            ->whereIn('upload_id', $uploadIds)
            ->pluck('upload_id');

        $learnerCounts = SavedMaterial::whereIn('upload_id', $uploadIds)
            ->where('viewed', true)
            ->selectRaw('upload_id, count(*) as c')
            ->groupBy('upload_id')
            ->pluck('c', 'upload_id');

        $data = $uploads->map(fn ($u) => [
            'upload_id'     => $u->id,
            'summary_id'    => $u->summary->id,
            'title'         => $u->summary->title,
            'category'      => $u->category,
            'difficulty'    => $u->summary->difficulty,
            'minutes'       => $u->summary->estimated_minutes,
            'owner'         => $u->user?->name ?? 'Unknown',
            'is_owner'      => $u->user_id === $userId,
            'is_saved'      => $savedByUser->contains($u->id),
            'learner_count' => (int) ($learnerCounts[$u->id] ?? 0),
        ]);

        return response()->json($data);
    }

    /**
     * Materials the current user has saved (Saved Materials on My Materials page).
     */
    public function saved(Request $request)
    {
        $userId = $request->user()->id;

        $saved = SavedMaterial::with([
                'upload.summary:id,upload_id,title',
                'upload.user:id,name',
            ])
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->filter(fn ($s) => $s->upload && $s->upload->summary)
            ->map(fn ($s) => [
                'id'         => $s->upload->id,
                'summary_id' => $s->upload->summary->id,
                'title'      => $s->upload->summary->title,
                'category'   => $s->upload->category,
                'owner'      => $s->upload->user?->name ?? 'Unknown',
                'created_at' => $s->created_at,
            ])
            ->values();

        return response()->json($saved);
    }

    public function save(Request $request, $uploadId)
    {
        $userId = $request->user()->id;
        $upload = Upload::where('is_public', true)->findOrFail($uploadId);

        if ($upload->user_id === $userId) {
            return response()->json(['message' => 'You cannot save your own material.'], 422);
        }

        SavedMaterial::firstOrCreate(['user_id' => $userId, 'upload_id' => $upload->id]);

        return response()->json(['saved' => true]);
    }

    public function unsave(Request $request, $uploadId)
    {
        SavedMaterial::where('user_id', $request->user()->id)
            ->where('upload_id', $uploadId)
            ->delete();

        return response()->json(['saved' => false]);
    }
}
