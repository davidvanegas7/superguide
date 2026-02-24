<?php

namespace Database\Factories;

use App\Models\LessonExercise;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

class LessonExerciseFactory extends Factory
{
    protected $model = LessonExercise::class;

    public function definition(): array
    {
        return [
            'lesson_id'     => Lesson::factory(),
            'title'         => fake()->sentence(3),
            'description'   => '# Ejercicio' . "\n\n" . fake()->paragraph(),
            'starter_code'  => 'def solve():\n    pass',
            'solution_code' => 'def solve():\n    return 42',
            'language'      => fake()->randomElement(['python', 'javascript', 'php', 'elixir']),
        ];
    }
}
