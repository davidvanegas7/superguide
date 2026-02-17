<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        $phpCourse = Course::where('slug', 'php-desde-cero')->first();
        $jsCourse  = Course::where('slug', 'javascript-moderno')->first();

        $tagPrincipiante = Tag::where('slug', 'principiante')->first();
        $tagFunciones    = Tag::where('slug', 'funciones')->first();
        $tagWeb          = Tag::where('slug', 'web')->first();

        if ($phpCourse) {
            $l1 = Lesson::firstOrCreate(
                ['course_id' => $phpCourse->id, 'slug' => 'introduccion-a-php'],
                [
                    'title'            => 'Introducción a PHP',
                    'md_file_path'     => 'content/lessons/php-introduccion.md',
                    'excerpt'          => 'Qué es PHP, cómo funciona y tu primer script.',
                    'published'        => true,
                    'sort_order'       => 1,
                    'duration_minutes' => 15,
                ]
            );
            if ($tagPrincipiante) $l1->tags()->syncWithoutDetaching([$tagPrincipiante->id]);

            $l2 = Lesson::firstOrCreate(
                ['course_id' => $phpCourse->id, 'slug' => 'operadores-y-estructuras-de-control'],
                [
                    'title'            => 'Operadores y Estructuras de Control',
                    'md_file_path'     => 'content/lessons/php-operadores-control.md',
                    'excerpt'          => 'if, switch, match, while, for y foreach en PHP.',
                    'published'        => true,
                    'sort_order'       => 2,
                    'duration_minutes' => 20,
                ]
            );
            if ($tagPrincipiante) $l2->tags()->syncWithoutDetaching([$tagPrincipiante->id]);
        }

        if ($jsCourse) {
            $l3 = Lesson::firstOrCreate(
                ['course_id' => $jsCourse->id, 'slug' => 'introduccion-a-javascript'],
                [
                    'title'            => 'Introducción a JavaScript',
                    'md_file_path'     => 'content/lessons/js-introduccion.md',
                    'excerpt'          => 'Variables, tipos de datos, funciones y template literals.',
                    'published'        => true,
                    'sort_order'       => 1,
                    'duration_minutes' => 18,
                ]
            );
            if ($tagWeb) $l3->tags()->syncWithoutDetaching([$tagWeb->id]);
        }
    }
}
