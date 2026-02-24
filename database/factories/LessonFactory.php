<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'course_id'        => Course::factory(),
            'title'            => ucfirst($title),
            'slug'             => Str::slug($title),
            'excerpt'          => fake()->sentence(),
            'content_md'       => '# ' . ucfirst($title) . "\n\n" . fake()->paragraphs(2, true),
            'published'        => true,
            'sort_order'       => fake()->numberBetween(1, 20),
            'duration_minutes' => fake()->numberBetween(10, 45),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['published' => false]);
    }
}
