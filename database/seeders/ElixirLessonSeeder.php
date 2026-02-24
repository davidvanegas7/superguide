<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ElixirLessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'elixir-funcional')->first();

        if (! $course) {
            $this->command->warn('Elixir course not found. Run CourseSeeder first.');
            return;
        }

        $tagPrincipiante = Tag::where('slug', 'principiante')->first();
        $tagIntermedio    = Tag::where('slug', 'intermedio')->first();
        $tagAvanzado      = Tag::where('slug', 'avanzado')->first();
        $tagFunciones     = Tag::where('slug', 'funciones')->first();
        $tagBd            = Tag::where('slug', 'bases-de-datos')->first();
        $tagBackend       = Tag::where('slug', 'backend')->first();

        $lessons = [
            ['slug' => 'introduccion-elixir',       'title' => 'Introducción a Elixir',                'md_file_path' => 'content/lessons/elixir/01-introduccion-elixir.md',      'excerpt' => 'BEAM VM, IEx, Mix y tu primer programa funcional.',                'published' => true, 'sort_order' => 1,  'duration_minutes' => 20],
            ['slug' => 'tipos-datos-elixir',        'title' => 'Tipos de Datos y Variables',           'md_file_path' => 'content/lessons/elixir/02-tipos-datos.md',              'excerpt' => 'Átomos, strings, integers, floats, booleans y pattern matching.', 'published' => true, 'sort_order' => 2,  'duration_minutes' => 25],
            ['slug' => 'colecciones-elixir',        'title' => 'Colecciones',                          'md_file_path' => 'content/lessons/elixir/03-colecciones.md',              'excerpt' => 'Listas, tuplas, keyword lists, maps, MapSet y ranges.',           'published' => true, 'sort_order' => 3,  'duration_minutes' => 25],
            ['slug' => 'pattern-matching-elixir',   'title' => 'Pattern Matching Avanzado',            'md_file_path' => 'content/lessons/elixir/04-pattern-matching.md',         'excerpt' => 'Destructuring, match en funciones, guards y pin operator.',       'published' => true, 'sort_order' => 4,  'duration_minutes' => 25],
            ['slug' => 'funciones-elixir',          'title' => 'Funciones',                            'md_file_path' => 'content/lessons/elixir/05-funciones.md',                'excerpt' => 'Funciones anónimas, named, pipe operator y captura &.',           'published' => true, 'sort_order' => 5,  'duration_minutes' => 25],
            ['slug' => 'modulos-structs-elixir',    'title' => 'Módulos y Structs',                    'md_file_path' => 'content/lessons/elixir/06-modulos.md',                  'excerpt' => 'defmodule, module attributes, structs y protocolos.',             'published' => true, 'sort_order' => 6,  'duration_minutes' => 25],
            ['slug' => 'control-flujo-elixir',      'title' => 'Control de Flujo',                     'md_file_path' => 'content/lessons/elixir/07-control-flujo.md',            'excerpt' => 'case, cond, if/unless, with y comprehensions.',                   'published' => true, 'sort_order' => 7,  'duration_minutes' => 20],
            ['slug' => 'recursion-enumerables',     'title' => 'Recursión y Enumerables',              'md_file_path' => 'content/lessons/elixir/08-recursion.md',                'excerpt' => 'Tail recursion, Enum, Stream y lazy evaluation.',                 'published' => true, 'sort_order' => 8,  'duration_minutes' => 30],
            ['slug' => 'procesos-concurrencia',     'title' => 'Procesos y Concurrencia',              'md_file_path' => 'content/lessons/elixir/09-procesos.md',                 'excerpt' => 'spawn, send/receive, links, monitors y Task.',                    'published' => true, 'sort_order' => 9,  'duration_minutes' => 30],
            ['slug' => 'genserver-elixir',          'title' => 'GenServer',                            'md_file_path' => 'content/lessons/elixir/10-genserver.md',                'excerpt' => 'init, handle_call, handle_cast, handle_info y state.',            'published' => true, 'sort_order' => 10, 'duration_minutes' => 30],
            ['slug' => 'supervision-otp',           'title' => 'Supervisión y OTP',                    'md_file_path' => 'content/lessons/elixir/11-supervision.md',              'excerpt' => 'Supervisor, strategies, Application y supervision trees.',        'published' => true, 'sort_order' => 11, 'duration_minutes' => 30],
            ['slug' => 'mix-proyectos-elixir',      'title' => 'Mix y Gestión de Proyectos',           'md_file_path' => 'content/lessons/elixir/12-mix-proyectos.md',            'excerpt' => 'mix new, deps, config, environments, tasks y Hex.',               'published' => true, 'sort_order' => 12, 'duration_minutes' => 20],
            ['slug' => 'testing-exunit',            'title' => 'Testing con ExUnit',                   'md_file_path' => 'content/lessons/elixir/13-testing.md',                  'excerpt' => 'ExUnit, assert, setup, describe, mocking y doctests.',            'published' => true, 'sort_order' => 13, 'duration_minutes' => 25],
            ['slug' => 'ecto-bases-datos',          'title' => 'Ecto y Bases de Datos',                'md_file_path' => 'content/lessons/elixir/14-ecto.md',                    'excerpt' => 'Repo, Schema, changesets, queries y migrations.',                 'published' => true, 'sort_order' => 14, 'duration_minutes' => 30],
            ['slug' => 'metaprogramacion-elixir',   'title' => 'Metaprogramación',                     'md_file_path' => 'content/lessons/elixir/15-metaprogramacion.md',         'excerpt' => 'quote, unquote, macros, __using__ y compile-time code.',          'published' => true, 'sort_order' => 15, 'duration_minutes' => 30],
            ['slug' => 'protocolos-behaviours',     'title' => 'Protocolos y Behaviours',              'md_file_path' => 'content/lessons/elixir/16-protocolos-behaviours.md',    'excerpt' => 'Protocol, defimpl, @behaviour, @callback y polimorfismo.',        'published' => true, 'sort_order' => 16, 'duration_minutes' => 25],
            ['slug' => 'manejo-errores-elixir',     'title' => 'Manejo de Errores',                    'md_file_path' => 'content/lessons/elixir/17-manejo-errores.md',           'excerpt' => 'try/rescue, tuplas ok/error, with y custom exceptions.',          'published' => true, 'sort_order' => 17, 'duration_minutes' => 20],
            ['slug' => 'deploy-produccion-elixir',  'title' => 'Deploy y Producción',                  'md_file_path' => 'content/lessons/elixir/18-deploy-produccion.md',        'excerpt' => 'Releases, Docker, CI/CD, hot upgrades y monitoring.',             'published' => true, 'sort_order' => 18, 'duration_minutes' => 25],
            ['slug' => 'entrevista-elixir',         'title' => 'Preguntas de Entrevista: Elixir',      'md_file_path' => 'content/lessons/elixir/19-preguntas-entrevista.md',     'excerpt' => 'BEAM, procesos, OTP, pattern matching y concurrencia.',           'published' => true, 'sort_order' => 19, 'duration_minutes' => 25],
        ];

        foreach ($lessons as $data) {
            $lesson = Lesson::firstOrCreate(
                ['course_id' => $course->id, 'slug' => $data['slug']],
                $data + ['course_id' => $course->id]
            );

            $sort = $data['sort_order'];

            if ($tagPrincipiante && $sort <= 4) {
                $lesson->tags()->syncWithoutDetaching([$tagPrincipiante->id]);
            }
            if ($tagIntermedio && $sort >= 5 && $sort <= 12) {
                $lesson->tags()->syncWithoutDetaching([$tagIntermedio->id]);
            }
            if ($tagAvanzado && $sort >= 13) {
                $lesson->tags()->syncWithoutDetaching([$tagAvanzado->id]);
            }
            if ($tagFunciones && in_array($sort, [5, 6, 8, 15])) {
                $lesson->tags()->syncWithoutDetaching([$tagFunciones->id]);
            }
            if ($tagBd && in_array($sort, [14])) {
                $lesson->tags()->syncWithoutDetaching([$tagBd->id]);
            }
            if ($tagBackend && in_array($sort, [9, 10, 11, 18])) {
                $lesson->tags()->syncWithoutDetaching([$tagBackend->id]);
            }
        }
    }
}
