<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    public function definition(): array
    {
        return [
            'course_id'   => Course::factory(),
            'title'       => 'Quiz: ' . fake()->words(3, true),
            'description' => fake()->sentence(),
            'published'   => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['published' => false]);
    }
}
