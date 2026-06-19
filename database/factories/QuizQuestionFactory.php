<?php

namespace Database\Factories;

use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizQuestionFactory extends Factory
{
    protected $model = QuizQuestion::class;

    public function definition(): array
    {
        $options = [
            fake()->sentence(4),
            fake()->sentence(4),
            fake()->sentence(4),
            fake()->sentence(4),
        ];

        return [
            'quiz_id'        => 1,
            'body'           => fake()->sentence(),
            'options'        => json_encode($options),
            'correct_option' => $options[0],
            'explanation'    => fake()->sentence(),
            'type'           => 'Conceptual',
            'difficulty'     => 'Medium',
            'sort_order'     => fake()->numberBetween(1, 10),
            'xp_reward'      => 15,
        ];
    }
}