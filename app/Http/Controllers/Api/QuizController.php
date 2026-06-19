<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Summary;
use App\Services\GamificationService;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    public function __construct(private GamificationService $gamification) {}

    /**
     * Generate a brand-new quiz for a material from user-chosen settings.
     */
    public function generate(Request $request, Summary $summary, GeminiService $gemini)
    {
        abort_unless($summary->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'difficulty'     => 'required|in:Beginner,Intermediate,Advanced',
            'question_count' => 'required|integer|min:3|max:30',
            'types'          => 'required|array|min:1',
            'types.*'        => 'in:multiple_choice,true_false,identification,enumeration',
        ]);

        // Prefer the original source text; fall back to the summary sections.
        $content = $summary->upload?->raw_content;
        if (empty(trim((string) $content))) {
            $content = collect($summary->content_sections ?? [])
                ->map(fn ($s) => ($s['heading'] ?? $s['title'] ?? '') . "\n" . ($s['body'] ?? ''))
                ->implode("\n\n");
        }
        if (empty(trim((string) $content))) {
            return response()->json(['message' => 'No source content is available to build a quiz from.'], 422);
        }

        try {
            $questions = $gemini->generateQuiz(
                $content,
                $data['question_count'],
                $data['difficulty'],
                $data['types'],
            );
        } catch (\Throwable $e) {
            Log::error('Quiz generation failed', ['summary_id' => $summary->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Quiz generation failed. Please try again.'], 500);
        }

        if (empty($questions)) {
            return response()->json(['message' => 'The AI did not return any questions. Please try again.'], 422);
        }

        $quiz = Quiz::create([
            'user_id'        => $request->user()->id,
            'summary_id'     => $summary->id,
            'title'          => $summary->title . ' — Quiz',
            'mode'           => 'practice',
            'question_count' => count($questions),
            'difficulty'     => $data['difficulty'],
        ]);

        foreach (array_values($questions) as $i => $q) {
            $type = in_array($q['type'] ?? '', ['multiple_choice', 'true_false', 'identification', 'enumeration'], true)
                ? $q['type']
                : 'multiple_choice';

            QuizQuestion::create([
                'quiz_id'         => $quiz->id,
                'body'            => $q['body'] ?? 'Question unavailable',
                'options'         => $type === 'true_false' ? ($q['options'] ?? ['True', 'False']) : ($q['options'] ?? null),
                'correct_option'  => $type === 'enumeration' ? null : ($q['correct_option'] ?? ''),
                'correct_answers' => $type === 'enumeration' ? array_values((array) ($q['correct_answers'] ?? [])) : null,
                'explanation'     => $q['explanation'] ?? '',
                'difficulty'      => $q['difficulty'] ?? $data['difficulty'],
                'type'            => $type,
                'xp_reward'       => (int) ($q['xp_reward'] ?? 15),
                'sort_order'      => $i + 1,
            ]);
        }

        return response()->json(['quiz_id' => $quiz->id], 201);
    }

    public function show($quizId)
    {
        $quiz = Quiz::with(['questions' => fn ($q) => $q->orderBy('sort_order')])->findOrFail($quizId);

        return response()->json([
            'quiz' => [
                'id'             => $quiz->id,
                'title'          => $quiz->title,
                'mode'           => $quiz->mode,
                'difficulty'     => $quiz->difficulty,
                'question_count' => $quiz->question_count,
            ],
            'questions' => $quiz->questions->map(fn ($q) => [
                'id'             => $q->id,
                'body'           => $q->body,
                'type'           => $q->type ?? 'multiple_choice',
                'options'        => $q->options,
                'expected_count' => $q->type === 'enumeration' ? count($q->correct_answers ?? []) : null,
                'difficulty'     => $q->difficulty,
                'xp_reward'      => $q->xp_reward,
            ]),
        ]);
    }

    public function submit(Request $request, $quizId)
    {
        $quiz      = Quiz::with('questions')->findOrFail($quizId);
        $user      = $request->user();
        $answers   = $request->input('answers', []);
        $startedAt = $request->input('started_at');

        $correct = 0;
        $wrong   = 0;
        $skipped = 0;

        foreach ($answers as $answer) {
            $question = $quiz->questions->firstWhere('id', $answer['question_id'] ?? null);
            if ($this->answerIsEmpty($answer['selected'] ?? null)) {
                $skipped++;
            } elseif ($question && $question->isCorrect($answer['selected'])) {
                $correct++;
            } else {
                $wrong++;
            }
        }

        $accuracy = ($correct + $wrong) > 0
            ? round(($correct / ($correct + $wrong)) * 100)
            : 0;
        $grade  = $accuracy >= 90 ? 'A' : ($accuracy >= 80 ? 'B' : ($accuracy >= 70 ? 'C' : 'D'));
        $passed = $accuracy >= 70;

        $durationSeconds = $startedAt
            ? now()->diffInSeconds(\Carbon\Carbon::parse($startedAt))
            : null;

        $answeredIds = collect($answers)
            ->reject(fn ($a) => $this->answerIsEmpty($a['selected'] ?? null))
            ->pluck('question_id');
        $baseXp = $quiz->questions->whereIn('id', $answeredIds)->sum('xp_reward');

        $streakBonus       = ($user->streak_current >= 3) ? 15 : 0;
        $perfectBonus      = ($accuracy === 100) ? 25 : 0;
        $speedBonus        = ($durationSeconds && $durationSeconds < 180) ? 10 : 0;
        $firstAttemptBonus = QuizAttempt::where('user_id', $user->id)->where('quiz_id', $quizId)->count() === 0 ? 5 : 0;
        $totalXp           = $baseXp + $streakBonus + $perfectBonus + $speedBonus + $firstAttemptBonus;

        $attempt = QuizAttempt::create([
            'user_id'          => $user->id,
            'quiz_id'          => $quiz->id,
            'answers'          => $answers,
            'correct'          => $correct,
            'wrong'            => $wrong,
            'skipped'          => $skipped,
            'accuracy'         => $accuracy,
            'grade'            => $grade,
            'passed'           => $passed,
            'xp_earned'        => $totalXp,
            'duration_seconds' => $durationSeconds,
            'completed_at'     => now(),
        ]);

        $this->gamification->awardXp($user, 'quiz_completed', $totalXp);
        $this->gamification->updateStreak($user);
        $unlockedBadges = $this->gamification->checkBadgeUnlock($user);

        return response()->json([
            'attempt_id' => $attempt->id,
            'quiz'       => ['title' => $quiz->title, 'completed_at' => now()->toISOString(), 'mode' => $quiz->mode],
            'score'      => compact('correct', 'wrong', 'skipped', 'accuracy', 'grade', 'passed'),
            'xp'         => [
                'earned'              => $baseXp,
                'streak_bonus'        => $streakBonus,
                'speed_bonus'         => $speedBonus,
                'first_attempt_bonus' => $firstAttemptBonus,
                'perfect_bonus'       => $perfectBonus,
                'total'               => $totalXp,
            ],
            'achievements_unlocked' => $unlockedBadges,
            'redirect'              => "/quizzes/{$quizId}/results",
        ]);
    }

    public function results(Request $request, $quizId)
    {
        $quiz = Quiz::with(['questions' => fn ($q) => $q->orderBy('sort_order')])->findOrFail($quizId);
        $user = $request->user();

        $attempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->latest('completed_at')
            ->firstOrFail();

        $answerMap = collect($attempt->answers)->keyBy('question_id');

        $questions = $quiz->questions->map(function ($q) use ($answerMap) {
            $submitted = $answerMap->get($q->id);
            $selected  = $submitted['selected'] ?? null;

            return [
                'body'           => $q->body,
                'type'           => $q->type ?? 'multiple_choice',
                'options'        => $q->options,
                'user_answer'    => is_array($selected) ? implode(', ', array_filter($selected)) : $selected,
                'correct_answer' => $q->correctAnswerDisplay(),
                'explanation'    => $q->explanation,
                'is_correct'     => $q->isCorrect($selected),
                'xp'             => $q->xp_reward,
                'tag'            => ($q->type ?? 'Foundation') . ' · ' . $q->difficulty,
            ];
        });

        $masteredSkills = $questions->where('is_correct', true)->pluck('tag')->unique()->values();
        $needsPractice  = $questions->where('is_correct', false)->pluck('tag')->unique()->values();

        $unlocked = \App\Models\UserBadge::where('user_id', $user->id)
            ->where('earned_at', '>=', $attempt->completed_at)
            ->with('badge')
            ->get()
            ->map(fn ($ub) => [
                'slug'  => $ub->badge->slug,
                'title' => $ub->badge->title,
                'xp'    => $ub->badge->xp_reward,
            ])
            ->values();

        return response()->json([
            'summary_id' => $quiz->summary_id,
            'quiz' => [
                'title'            => $quiz->title,
                'completed_at'     => $attempt->completed_at?->toISOString(),
                'duration_seconds' => $attempt->duration_seconds,
                'mode'             => $quiz->mode,
            ],
            'score' => [
                'correct'  => $attempt->correct,
                'wrong'    => $attempt->wrong,
                'skipped'  => $attempt->skipped,
                'accuracy' => $attempt->accuracy,
                'grade'    => $attempt->grade,
                'passed'   => $attempt->passed,
            ],
            'xp' => [
                'earned' => $attempt->xp_earned,
            ],
            'mastered_skills'       => $masteredSkills,
            'needs_practice'        => $needsPractice,
            'questions'             => $questions,
            'achievements_unlocked' => $unlocked,
        ]);
    }

    /**
     * An answer counts as skipped when it's null, blank, or an all-blank list.
     */
    private function answerIsEmpty($selected): bool
    {
        if (is_array($selected)) {
            return count(array_filter($selected, fn ($s) => trim((string) $s) !== '')) === 0;
        }

        return trim((string) $selected) === '';
    }
}
