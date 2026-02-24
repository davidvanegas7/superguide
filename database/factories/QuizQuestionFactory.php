<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizQuestionFactory extends Factory
{
    protected $model = QuizQuestion::class;

    public function definition(): array
    {
        return [
            'quiz_id'     => Quiz::factory(),
            'question'    => fake()->sentence() . '?',
            'explanation' => fake()->paragraph(),
            'sort_order'  => fake()->numberBetween(1, 20),
        ];
    }
}
