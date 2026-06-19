<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key')
            ?? throw new RuntimeException('GEMINI_API_KEY is not set in .env');
        $this->model  = config('services.gemini.model', 'gemini-2.5-flash-lite');
    }

    public function generateSummary(string $content): array
    {
        $prompt = <<<PROMPT
You are an expert learning content creator. Analyze the following text and return a structured JSON summary.

Return ONLY valid JSON (no markdown, no code fences) with this exact shape:
{
  "title": "string — concise topic title",
  "difficulty": "Beginner | Intermediate | Advanced",
  "estimated_minutes": integer,
  "content_sections": [
    { "heading": "string", "body": "string" }
  ],
  "key_terms": [
    { "term": "string", "definition": "string" }
  ],
  "timeline_steps": [
    { "step": "string", "description": "string" }
  ]
}

TEXT TO ANALYZE:
{$content}
PROMPT;

        return $this->callApi($prompt);
    }

    public function generateFlashcards(string $content, int $count = 12): array
    {
        $prompt = <<<PROMPT
You are a flashcard generator. Create exactly {$count} flashcards from the following text.

Return ONLY valid JSON (no markdown, no code fences) as an array:
[
  { "question": "string", "answer": "string", "category": "string" }
]

TEXT:
{$content}
PROMPT;

        $result = $this->callApi($prompt);
        return is_array($result) && isset($result[0]) ? $result : ($result['flashcards'] ?? []);
    }

    /**
     * Generate a quiz of mixed question types.
     *
     * @param  string[]  $types  Any of: multiple_choice, true_false, identification, enumeration
     */
    public function generateQuiz(
        string $content,
        int $questionCount = 10,
        string $difficulty = 'Intermediate',
        array $types = ['multiple_choice'],
    ): array {
        $typeLabels = [
            'multiple_choice' => 'Multiple Choice',
            'true_false'      => 'True/False',
            'identification'  => 'Identification (single short answer)',
            'enumeration'     => 'Enumeration (list of several answers)',
        ];

        $requested = array_values(array_intersect(array_keys($typeLabels), $types));
        if (empty($requested)) {
            $requested = ['multiple_choice'];
        }

        $requestedList = collect($requested)->map(fn ($t) => "- {$t} ({$typeLabels[$t]})")->implode("\n");
        $mixedNote = count($requested) > 1
            ? "This is a MIXED quiz. Distribute the {$questionCount} questions across the requested types and GROUP them by type in contiguous blocks (all questions of one type together, then the next type). You decide a sensible split based on the question count and the content."
            : "Use only the single requested type for every question.";

        $prompt = <<<PROMPT
You are a quiz generator. Create EXACTLY {$questionCount} questions from the text below.
Difficulty level: {$difficulty}.

Allowed question types (use ONLY these):
{$requestedList}

{$mixedNote}

Return ONLY valid JSON (no markdown, no code fences) as an array. Each object's shape depends on its "type":

multiple_choice:
{ "type": "multiple_choice", "body": "string", "options": ["A","B","C","D"], "correct_option": "string exactly matching one option", "explanation": "string", "difficulty": "{$difficulty}", "xp_reward": 10-30 }

true_false:
{ "type": "true_false", "body": "a statement to judge", "options": ["True","False"], "correct_option": "True" or "False", "explanation": "string", "difficulty": "{$difficulty}", "xp_reward": 10-30 }

identification:
{ "type": "identification", "body": "question asking for one specific term/answer", "correct_option": "the single correct answer", "explanation": "string", "difficulty": "{$difficulty}", "xp_reward": 10-30 }

enumeration:
{ "type": "enumeration", "body": "instruction to list specific items (state how many)", "correct_answers": ["item1","item2","item3"], "explanation": "string", "difficulty": "{$difficulty}", "xp_reward": 10-30 }

Rules:
- The total number of questions across all types MUST equal exactly {$questionCount}.
- For enumeration, keep correct_answers short and unambiguous (single words or short phrases).
- Base every question strictly on the text.

TEXT:
{$content}
PROMPT;

        $result = $this->callApi($prompt);
        return is_array($result) && isset($result[0]) ? $result : ($result['questions'] ?? []);
    }

    public function generateInsights(array $stats): array
    {
        $json = json_encode($stats);

        $prompt = <<<PROMPT
You are a supportive learning analytics coach. Based on this learner's real study data, write EXACTLY 3 short, personalized, actionable insights.

Rules:
- Reference the actual numbers where useful (streak, accuracy, weak/strong subjects, time split).
- Be specific and encouraging, never generic filler.
- Each body is 1-2 sentences.

Return ONLY valid JSON (no markdown, no code fences) as an array:
[
  { "title": "string — 3-6 word headline", "body": "string — specific, actionable advice" }
]

LEARNER DATA (JSON):
{$json}
PROMPT;

        $result = $this->callApi($prompt);

        $insights = (is_array($result) && isset($result[0])) ? $result : ($result['insights'] ?? []);

        return array_slice($insights, 0, 3);
    }

    public function recommendMaterials(array $context, array $candidates): array
    {
        $candidatesJson = json_encode($candidates);
        $contextJson = json_encode($context);

        $prompt = <<<PROMPT
You are a learning recommendation engine. From the AVAILABLE materials below, choose the 2 best "next lessons" for this learner based on their interests and what they have already studied. You MUST only choose ids that appear in the list.

Return ONLY valid JSON (no markdown, no code fences) — an array of the chosen materials' numeric ids, best first:
[id, id]

LEARNER:
{$contextJson}

AVAILABLE MATERIALS (id, title, category, difficulty):
{$candidatesJson}
PROMPT;

        $result = $this->callApi($prompt);

        $ids = [];
        foreach ((array) $result as $r) {
            if (is_numeric($r)) {
                $ids[] = (int) $r;
            } elseif (is_array($r) && isset($r['id'])) {
                $ids[] = (int) $r['id'];
            }
        }

        return $ids;
    }

    private function callApi(string $prompt): array
    {
        $url = "{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::timeout(60)->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature'     => 0.4,
                'maxOutputTokens' => 8192,
            ],
        ]);

        if (!$response->successful()) {
            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new RuntimeException("Gemini API returned HTTP {$response->status()}");
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (!$text) {
            throw new RuntimeException('Gemini API returned an empty response');
        }

        $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $text = preg_replace('/```\s*$/m', '', $text);
        $text = trim($text);

        $decoded = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Gemini JSON parse error', ['text' => $text]);
            throw new RuntimeException('Failed to parse Gemini response as JSON: ' . json_last_error_msg());
        }

        return $decoded;
    }
}