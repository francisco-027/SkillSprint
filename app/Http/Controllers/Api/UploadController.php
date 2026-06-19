<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessUploadJob;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function recent(Request $request)
    {
        $uploads = Upload::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return response()->json($uploads);
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $uploads = Upload::with('summary:id,upload_id,title')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Which materials have a quiz the user has actually attempted?
        $summaryIds = $uploads->pluck('summary.id')->filter()->values();
        $quizzes = \App\Models\Quiz::whereIn('summary_id', $summaryIds)->get(['id', 'summary_id']);
        $attemptedQuizIds = \App\Models\QuizAttempt::where('user_id', $userId)
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->pluck('quiz_id')
            ->unique();
        $quizTakenSummaryIds = $quizzes
            ->filter(fn ($q) => $attemptedQuizIds->contains($q->id))
            ->pluck('summary_id')
            ->unique();

        $data = $uploads->map(fn ($u) => [
            'id'                => $u->id,
            'original_filename' => $u->original_filename,
            'type'              => $u->type,
            'category'          => $u->category,
            'status'            => $u->status,
            'word_count'        => $u->word_count,
            'created_at'        => $u->created_at,
            'summary_id'        => $u->summary?->id,
            'title'             => $u->summary?->title ?? $u->title,
            'is_public'         => $u->is_public,
            'is_new'            => $u->status === 'done' && $u->opened_at === null,
            'quiz_taken'        => $u->summary && $quizTakenSummaryIds->contains($u->summary->id),
        ]);

        return response()->json($data);
    }

    public function categories(Request $request)
    {
        $categories = Upload::where('user_id', $request->user()->id)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return response()->json($categories);
    }

    public function open(Request $request, $id)
    {
        $upload = Upload::findOrFail($id);
        $userId = $request->user()->id;

        if ($upload->user_id === $userId) {
            // Owner viewing their own material clears the "New" badge.
            if ($upload->opened_at === null) {
                $upload->update(['opened_at' => now()]);
            }
        } else {
            // A saver studying the flashcards counts as a learner (once).
            $saved = \App\Models\SavedMaterial::where('user_id', $userId)
                ->where('upload_id', $upload->id)
                ->first();

            if ($saved && !$saved->viewed) {
                $saved->update(['viewed' => true]);
            }
        }

        return response()->json(['opened' => true]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'     => 'nullable|string|max:255',
            'category'  => 'nullable|string|max:100',
            'is_public' => 'nullable|boolean',
        ]);

        $upload = Upload::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $upload->update([
            'title'     => $request->title ?: null,
            'category'  => $request->category ?: null,
            'is_public' => $request->boolean('is_public'),
        ]);

        // Keep the generated lesson's title in sync with the user's title.
        if ($request->filled('title') && $upload->summary) {
            $upload->summary->update(['title' => $request->title]);
        }

        return response()->json(['updated' => true]);
    }

    public function destroy(Request $request, $id)
    {
        $upload = Upload::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // upload_id is nullOnDelete, so remove the generated content explicitly.
        if ($summary = \App\Models\Summary::where('upload_id', $upload->id)->first()) {
            \App\Models\Quiz::where('summary_id', $summary->id)->delete(); // cascades quiz_questions
            $summary->delete();                                            // cascades flashcards
        }

        $upload->delete();

        return response()->json(['deleted' => true]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'nullable|string|max:255',
            'category'   => 'nullable|string|max:100',
            'is_public'  => 'nullable|boolean',
            'type'       => 'required|in:text,file,url,sample',
            'text'       => 'required_if:type,text|nullable|string|min:50|max:50000',
            'file'       => 'required_if:type,file|nullable|file|mimes:pdf,docx,txt|max:10240',
            'url'        => 'required_if:type,url|nullable|url',
            'skill_id'   => 'required_if:type,sample|nullable|exists:skills,id',
        ]);

        $rawContent      = '';
        $originalFilename = null;
        $filePath        = null;

        switch ($request->type) {
            case 'text':
                $rawContent       = $request->text;
                $originalFilename = 'pasted-text.txt';
                break;

            case 'file':
                $file             = $request->file('file');
                $originalFilename = $file->getClientOriginalName();
                $filePath         = $file->store('uploads/' . $request->user()->id, 'local');
                $rawContent       = $this->extractTextFromFile($file);
                break;

            case 'url':
                $rawContent       = $this->fetchTextFromUrl($request->url);
                $originalFilename = parse_url($request->url, PHP_URL_HOST) . '.txt';
                break;

            case 'sample':
                $skill      = \App\Models\Skill::findOrFail($request->skill_id);
                $rawContent = $skill->description ?? $skill->title;
                $originalFilename = 'sample-' . $skill->slug . '.txt';
                break;
        }

        $upload = Upload::create([
            'user_id'           => $request->user()->id,
            'original_filename' => $originalFilename,
            'title'             => $request->title ?: null,
            'category'          => $request->category ?: null,
            'is_public'         => $request->boolean('is_public'),
            'type'              => $request->type,
            'raw_content'       => $rawContent,
            'file_path'         => $filePath,
            'word_count'        => str_word_count($rawContent),
            'status'            => 'pending',
        ]);

        ProcessUploadJob::dispatch($upload);

        return response()->json(['upload_id' => $upload->id, 'status' => 'pending'], 202);
    }

    public function status(Request $request, $id)
    {
        $upload = Upload::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $summaryId = null;
        if ($upload->status === 'done') {
            $summary   = \App\Models\Summary::where('upload_id', $upload->id)->first();
            $summaryId = $summary?->id;
        }

        return response()->json([
            'id'            => $upload->id,
            'status'        => $upload->status,
            'summary_id'    => $summaryId,
            'error_message' => $upload->status === 'failed' ? 'Processing failed. Please try again.' : null,
        ]);
    }

    private function extractTextFromFile(\Illuminate\Http\UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext === 'txt') {
            return file_get_contents($file->getRealPath());
        }

        if ($ext === 'pdf') {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf    = $parser->parseFile($file->getRealPath());
                return $pdf->getText();
            } catch (\Throwable $e) {
                return '';
            }
        }

        if ($ext === 'docx') {
            try {
                $phpWord  = \PhpOffice\PhpWord\IOFactory::load($file->getRealPath());
                $text     = '';
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $text .= $element->getText() . "\n";
                        }
                    }
                }
                return $text;
            } catch (\Throwable $e) {
                return '';
            }
        }

        return '';
    }

    private function fetchTextFromUrl(string $url): string
    {
        $response = Http::timeout(15)->get($url);

        if (!$response->successful()) {
            return '';
        }

        return strip_tags($response->body());
    }
}