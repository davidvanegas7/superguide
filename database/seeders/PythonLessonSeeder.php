<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class PythonLessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'python-desde-cero')->first();

        if (! $course) {
            $this->command->warn('Python course not found. Run CourseSeeder first.');
            return;
        }

        $tagPrincipiante = Tag::where('slug', 'principiante')->first();
        $tagIntermedio    = Tag::where('slug', 'intermedio')->first();
        $tagAvanzado      = Tag::where('slug', 'avanzado')->first();
        $tagPoo           = Tag::where('slug', 'poo')->first();
        $tagFunciones     = Tag::where('slug', 'funciones')->first();
        $tagArrays        = Tag::where('slug', 'arrays')->first();
        $tagBackend       = Tag::where('slug', 'backend')->first();

        $lessons = [
            ['slug' => 'introduccion-python',      'title' => 'Introducción a Python',                 'md_file_path' => 'content/lessons/python/01-introduccion-python.md',      'excerpt' => 'Historia, instalación, REPL, primer script y el Zen de Python.',                      'published' => true, 'sort_order' => 1,  'duration_minutes' => 20],
            ['slug' => 'variables-tipos-python',    'title' => 'Variables y Tipos de Datos',            'md_file_path' => 'content/lessons/python/02-variables-tipos.md',           'excerpt' => 'int, float, str, bool, None, conversiones y f-strings.',                              'published' => true, 'sort_order' => 2,  'duration_minutes' => 25],
            ['slug' => 'estructuras-control-python', 'title' => 'Estructuras de Control',              'md_file_path' => 'content/lessons/python/03-estructuras-control.md',       'excerpt' => 'if/elif/else, while, for, range(), break/continue y match-case.',                     'published' => true, 'sort_order' => 3,  'duration_minutes' => 25],
            ['slug' => 'listas-tuplas-python',      'title' => 'Listas y Tuplas',                       'md_file_path' => 'content/lessons/python/04-listas-tuplas.md',             'excerpt' => 'Listas mutables, slicing, comprehensions, tuplas inmutables y unpacking.',             'published' => true, 'sort_order' => 4,  'duration_minutes' => 25],
            ['slug' => 'diccionarios-sets-python',  'title' => 'Diccionarios y Sets',                   'md_file_path' => 'content/lessons/python/05-diccionarios-sets.md',         'excerpt' => 'dict, dict comprehensions, sets, operaciones de conjuntos y frozenset.',              'published' => true, 'sort_order' => 5,  'duration_minutes' => 25],
            ['slug' => 'funciones-python',          'title' => 'Funciones',                             'md_file_path' => 'content/lessons/python/06-funciones.md',                 'excerpt' => 'def, *args/**kwargs, scope LEGB, lambda, funciones como objetos.',                    'published' => true, 'sort_order' => 6,  'duration_minutes' => 30],
            ['slug' => 'modulos-paquetes-python',   'title' => 'Módulos y Paquetes',                    'md_file_path' => 'content/lessons/python/07-modulos-paquetes.md',          'excerpt' => 'import, __name__, pip, venv, requirements.txt y crear paquetes.',                     'published' => true, 'sort_order' => 7,  'duration_minutes' => 20],
            ['slug' => 'poo-basica-python',         'title' => 'POO: Fundamentos',                      'md_file_path' => 'content/lessons/python/08-poo-basica.md',                'excerpt' => 'Clases, __init__, self, herencia, super(), @property e isinstance.',                  'published' => true, 'sort_order' => 8,  'duration_minutes' => 30],
            ['slug' => 'poo-avanzada-python',       'title' => 'POO Avanzada',                          'md_file_path' => 'content/lessons/python/09-poo-avanzada.md',              'excerpt' => 'Herencia múltiple, MRO, ABC, dunder methods, dataclasses y slots.',                   'published' => true, 'sort_order' => 9,  'duration_minutes' => 30],
            ['slug' => 'excepciones-python',        'title' => 'Manejo de Excepciones',                 'md_file_path' => 'content/lessons/python/10-excepciones.md',               'excerpt' => 'try/except/else/finally, raise, excepciones custom y context managers.',              'published' => true, 'sort_order' => 10, 'duration_minutes' => 25],
            ['slug' => 'iteradores-generadores',    'title' => 'Iteradores y Generadores',              'md_file_path' => 'content/lessons/python/11-iteradores-generadores.md',    'excerpt' => 'Protocolo iterador, yield, generator expressions e itertools.',                       'published' => true, 'sort_order' => 11, 'duration_minutes' => 25],
            ['slug' => 'decoradores-python',        'title' => 'Decoradores',                           'md_file_path' => 'content/lessons/python/12-decoradores.md',               'excerpt' => 'Closures, @decorator, con argumentos, functools.wraps y ejemplos prácticos.',         'published' => true, 'sort_order' => 12, 'duration_minutes' => 30],
            ['slug' => 'archivos-io-python',        'title' => 'Archivos y E/S',                        'md_file_path' => 'content/lessons/python/13-archivos-io.md',               'excerpt' => 'open/read/write, pathlib, csv, json y pickle.',                                       'published' => true, 'sort_order' => 13, 'duration_minutes' => 25],
            ['slug' => 'regex-python',              'title' => 'Expresiones Regulares',                 'md_file_path' => 'content/lessons/python/14-expresiones-regulares.md',     'excerpt' => 'Módulo re, match/search/findall/sub, grupos y flags.',                                'published' => true, 'sort_order' => 14, 'duration_minutes' => 25],
            ['slug' => 'testing-pytest',            'title' => 'Testing con pytest',                    'md_file_path' => 'content/lessons/python/15-testing.md',                   'excerpt' => 'pytest, fixtures, parametrize, mocking y coverage.',                                  'published' => true, 'sort_order' => 15, 'duration_minutes' => 30],
            ['slug' => 'concurrencia-python',       'title' => 'Concurrencia y Paralelismo',            'md_file_path' => 'content/lessons/python/16-concurrencia.md',              'excerpt' => 'GIL, threading, multiprocessing, asyncio y concurrent.futures.',                      'published' => true, 'sort_order' => 16, 'duration_minutes' => 30],
            ['slug' => 'tipado-typing-python',      'title' => 'Tipado Estático con typing',            'md_file_path' => 'content/lessons/python/17-tipado-typing.md',             'excerpt' => 'Type hints, typing module, Protocol, Generic, TypeAlias y mypy.',                     'published' => true, 'sort_order' => 17, 'duration_minutes' => 25],
            ['slug' => 'buenas-practicas-python',   'title' => 'Buenas Prácticas y Pythonic Code',      'md_file_path' => 'content/lessons/python/18-buenas-practicas.md',          'excerpt' => 'PEP 8, EAFP vs LBYL, walrus operator, linters y estructura de proyecto.',             'published' => true, 'sort_order' => 18, 'duration_minutes' => 25],
            ['slug' => 'entrevista-python',         'title' => 'Preguntas de Entrevista: Python',        'md_file_path' => 'content/lessons/python/19-preguntas-entrevista.md',      'excerpt' => 'Mutable vs inmutable, GIL, decoradores, is vs ==, duck typing y más.',                'published' => true, 'sort_order' => 19, 'duration_minutes' => 25],
        ];

        foreach ($lessons as $data) {
            $lesson = Lesson::firstOrCreate(
                ['course_id' => $course->id, 'slug' => $data['slug']],
                $data + ['course_id' => $course->id]
            );

            $sort = $data['sort_order'];

            if ($tagPrincipiante && $sort <= 5) {
                $lesson->tags()->syncWithoutDetaching([$tagPrincipiante->id]);
            }
            if ($tagIntermedio && $sort >= 6 && $sort <= 13) {
                $lesson->tags()->syncWithoutDetaching([$tagIntermedio->id]);
            }
            if ($tagAvanzado && $sort >= 14) {
                $lesson->tags()->syncWithoutDetaching([$tagAvanzado->id]);
            }
            if ($tagPoo && in_array($sort, [8, 9])) {
                $lesson->tags()->syncWithoutDetaching([$tagPoo->id]);
            }
            if ($tagFunciones && in_array($sort, [6, 11, 12])) {
                $lesson->tags()->syncWithoutDetaching([$tagFunciones->id]);
            }
            if ($tagArrays && in_array($sort, [4, 5])) {
                $lesson->tags()->syncWithoutDetaching([$tagArrays->id]);
            }
            if ($tagBackend && in_array($sort, [15, 16, 17])) {
                $lesson->tags()->syncWithoutDetaching([$tagBackend->id]);
            }
        }
    }
}
