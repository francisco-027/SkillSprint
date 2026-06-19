<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends Model
{
    use HasFactory;
    protected $fillable = [
        'quiz_id', 'body', 'correct_option', 'correct_answers', 'explanation', 'type',
        'difficulty', 'sort_order', 'xp_reward', 'options',
    ];

    protected function casts(): array
    {
        return [
            'options'         => 'array',
            'correct_answers' => 'array',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Normalize an answer for comparison: lowercase, trimmed, collapsed spaces.
     */
    private static function normalize($value): string
    {
        return preg_replace('/\s+/', ' ', strtolower(trim((string) $value)));
    }

    /**
     * Grade a submitted answer against this question.
     * For enumeration, $selected is an array; otherwise a string.
     */
    public function isCorrect($selected): bool
    {
        if ($this->type === 'enumeration') {
            $expected = collect($this->correct_answers ?? [])
                ->map(fn ($a) => self::normalize($a))
                ->filter()
                ->unique();

            if ($expected->isEmpty()) {
                return false;
            }

            $given = collect((array) $selected)
                ->map(fn ($a) => self::normalize($a))
                ->filter()
                ->unique();

            // Correct only when every expected item is present in the answer.
            return $expected->every(fn ($e) => $given->contains($e));
        }

        // multiple_choice, true_false, identification
        $answer = self::normalize($selected);

        return $answer !== '' && $answer === self::normalize($this->correct_option);
    }

    /**
     * Human-readable correct answer for results display.
     */
    public function correctAnswerDisplay(): string
    {
        if ($this->type === 'enumeration') {
            return implode(', ', $this->correct_answers ?? []);
        }

        return (string) $this->correct_option;
    }
}
