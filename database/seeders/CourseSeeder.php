<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $php = Language::where('slug', 'php')->first();
        $js  = Language::where('slug', 'javascript')->first();

        if ($php) {
            Course::firstOrCreate(
                ['slug' => 'php-desde-cero'],
                [
                    'language_id' => $php->id,
                    'title'       => 'PHP desde Cero',
                    'description' => 'Aprende PHP desde los fundamentos hasta programación orientada a objetos.',
                    'level'       => 'beginner',
                    'published'   => true,
                    'sort_order'  => 1,
                ]
            );
        }

        if ($js) {
            Course::firstOrCreate(
                ['slug' => 'javascript-moderno'],
                [
                    'language_id' => $js->id,
                    'title'       => 'JavaScript Moderno (ES6+)',
                    'description' => 'Domina el JavaScript moderno: arrow functions, promesas, async/await y más.',
                    'level'       => 'intermediate',
                    'published'   => true,
                    'sort_order'  => 1,
                ]
            );
        }
    }
}
