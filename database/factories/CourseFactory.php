<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'language_id' => Language::factory(),
            'title'       => ucfirst($title),
            'slug'        => Str::slug($title),
            'description' => fake()->paragraph(),
            'level'       => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'published'   => true,
            'sort_order'  => fake()->numberBetween(1, 20),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['published' => false]);
    }
}
