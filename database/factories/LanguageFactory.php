<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name'        => ucfirst($name),
            'slug'        => Str::slug($name),
            'color'       => fake()->hexColor(),
            'icon'        => '🔤',
            'description' => fake()->sentence(),
            'active'      => true,
            'sort_order'  => fake()->numberBetween(0, 20),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['active' => false]);
    }
}
