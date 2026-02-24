<?php

namespace Database\Factories;

use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizOptionFactory extends Factory
{
    protected $model = QuizOption::class;

    public function definition(): array
    {
        return [
            'quiz_question_id' => QuizQuestion::factory(),
            'text'             => fake()->sentence(),
            'is_correct'       => false,
            'sort_order'       => fake()->numberBetween(1, 4),
        ];
    }

    public function correct(): static
    {
        return $this->state(fn () => ['is_correct' => true]);
    }
}
