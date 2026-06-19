<?php

namespace App\Jobs;

use App\Models\Flashcard;
use App\Models\Summary;
use App\Models\Upload;
use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(public Upload $upload) {}

    public function handle(GeminiService $gemini): void
    {
        $this->upload->update(['status' => 'processing']);

        $content = $this->upload->raw_content;

        if (empty(trim($content))) {
            $this->upload->update(['status' => 'failed']);
            return;
        }

        $summaryData = $gemini->generateSummary($content);

        $summary = Summary::create([
            'user_id'            => $this->upload->user_id,
            'upload_id'          => $this->upload->id,
            'title'              => $this->upload->title ?: ($summaryData['title'] ?? 'Untitled Summary'),
            'difficulty'         => $summaryData['difficulty'] ?? 'Intermediate',
            'estimated_minutes'  => $summaryData['estimated_minutes'] ?? 10,
            'source_filename'    => $this->upload->original_filename,
            'content_sections'   => $summaryData['content_sections'] ?? [],
            'key_terms'          => $summaryData['key_terms'] ?? [],
            'timeline_steps'     => $summaryData['timeline_steps'] ?? [],
        ]);

        $flashcardsData = $gemini->generateFlashcards($content, $this->flashcardCountFor($content));

        foreach ($flashcardsData as $index => $card) {
            Flashcard::create([
                'summary_id' => $summary->id,
                'question'   => $card['question'] ?? 'Question unavailable',
                'answer'     => $card['answer'] ?? 'Answer unavailable',
                'category'   => $card['category'] ?? 'General',
                'sort_order' => $index + 1,
            ]);
        }

        $this->upload->update([
            'status'       => 'done',
            'processed_at' => now(),
        ]);
    }

    /**
     * Scale the number of flashcards to the amount of source material.
     * Roughly one card per ~110 words, clamped to a sensible range so short
     * notes still get a usable deck and long documents stay within the
     * model's output limit.
     */
    private function flashcardCountFor(string $content): int
    {
        $words = str_word_count($content);
        $count = (int) round($words / 110);

        return max(8, min(40, $count));
    }

    public function failed(Throwable $e): void
    {
        Log::error("ProcessUploadJob failed for upload {$this->upload->id}: {$e->getMessage()}");

        $this->upload->update(['status' => 'failed']);
    }
}