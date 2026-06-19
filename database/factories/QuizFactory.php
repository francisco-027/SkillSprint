<?php

namespace Database\Factories;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    public function definition(): array
    {
        return [
            'user_id'        => 1,
            'summary_id'     => 1,
            'title'          => fake()->sentence(3),
            'mode'           => 'practice',
            'question_count' => 10,
            'difficulty'     => 'Medium',
        ];
    }
}