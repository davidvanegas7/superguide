<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class RubyLessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'ruby-desde-cero')->first();

        if (! $course) {
            $this->command->warn('Ruby course not found. Run CourseSeeder first.');
            return;
        }

        $tagPrincipiante = Tag::where('slug', 'principiante')->first();
        $tagIntermedio   = Tag::where('slug', 'intermedio')->first();
        $tagAvanzado     = Tag::where('slug', 'avanzado')->first();
        $tagFunciones    = Tag::where('slug', 'funciones')->first();
        $tagPoo          = Tag::where('slug', 'poo')->first();

        $lessons = [
            [
                'slug'             => 'introduccion-ruby',
                'title'            => 'Introducción a Ruby',
                'md_file_path'     => 'content/lessons/ruby/01-introduccion-ruby.md',
                'excerpt'          => 'Instalación, filosofía del lenguaje, primeros pasos, variables, strings, números y convenciones.',
                'published'        => true,
                'sort_order'       => 1,
                'duration_minutes' => 20,
            ],
            [
                'slug'             => 'tipos-datos-operadores-ruby',
                'title'            => 'Tipos de Datos y Operadores',
                'md_file_path'     => 'content/lessons/ruby/02-tipos-datos-operadores.md',
                'excerpt'          => 'Integers, Floats, Strings, Symbols, Booleans, Arrays, Hashes, Ranges y operadores.',
                'published'        => true,
                'sort_order'       => 2,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'estructuras-control-ruby',
                'title'            => 'Estructuras de Control',
                'md_file_path'     => 'content/lessons/ruby/03-estructuras-control.md',
                'excerpt'          => 'if/unless, case/when, pattern matching, bucles, iteradores y operador &:symbol.',
                'published'        => true,
                'sort_order'       => 3,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'metodos-bloques-ruby',
                'title'            => 'Métodos y Bloques',
                'md_file_path'     => 'content/lessons/ruby/04-metodos-bloques.md',
                'excerpt'          => 'Definición de métodos, parámetros, keyword args, bloques, yield, Procs y Lambdas.',
                'published'        => true,
                'sort_order'       => 4,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'poo-ruby',
                'title'            => 'Programación Orientada a Objetos',
                'md_file_path'     => 'content/lessons/ruby/05-poo.md',
                'excerpt'          => 'Clases, objetos, attr_accessor, herencia, módulos, visibilidad, duck typing y Struct.',
                'published'        => true,
                'sort_order'       => 5,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'herencia-modulos-avanzados-ruby',
                'title'            => 'Herencia, Módulos y Mixins Avanzados',
                'md_file_path'     => 'content/lessons/ruby/06-herencia-modulos-avanzados.md',
                'excerpt'          => 'Ancestor chain, hooks, prepend vs include, Concern pattern, composición y refinements.',
                'published'        => true,
                'sort_order'       => 6,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'manejo-errores-ruby',
                'title'            => 'Manejo de Errores y Excepciones',
                'md_file_path'     => 'content/lessons/ruby/07-manejo-errores.md',
                'excerpt'          => 'begin/rescue/ensure, raise, excepciones personalizadas, retry y Result Object.',
                'published'        => true,
                'sort_order'       => 7,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'archivos-io-ruby',
                'title'            => 'Archivos, I/O y Serialización',
                'md_file_path'     => 'content/lessons/ruby/08-archivos-io.md',
                'excerpt'          => 'Lectura/escritura de archivos, directorios, Pathname, JSON, YAML y CSV.',
                'published'        => true,
                'sort_order'       => 8,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'expresiones-regulares-ruby',
                'title'            => 'Expresiones Regulares',
                'md_file_path'     => 'content/lessons/ruby/09-expresiones-regulares.md',
                'excerpt'          => 'Regex en Ruby, match, scan, sub/gsub, grupos, lookahead/lookbehind y validaciones.',
                'published'        => true,
                'sort_order'       => 9,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'enumerables-colecciones-ruby',
                'title'            => 'Enumerables y Colecciones Avanzadas',
                'md_file_path'     => 'content/lessons/ruby/10-enumerables-colecciones.md',
                'excerpt'          => 'Enumerable a fondo, transformación, filtrado, reducción, lazy enumerators y Set.',
                'published'        => true,
                'sort_order'       => 10,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'programacion-funcional-ruby',
                'title'            => 'Procs, Lambdas y Programación Funcional',
                'md_file_path'     => 'content/lessons/ruby/11-programacion-funcional.md',
                'excerpt'          => 'Higher-order functions, composición, curry, memoization, pipelines con then/tap.',
                'published'        => true,
                'sort_order'       => 11,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'metaprogramacion-ruby',
                'title'            => 'Metaprogramación en Ruby',
                'md_file_path'     => 'content/lessons/ruby/12-metaprogramacion.md',
                'excerpt'          => 'Introspección, define_method, method_missing, class_eval, instance_eval y DSLs.',
                'published'        => true,
                'sort_order'       => 12,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'gemas-bundler-ruby',
                'title'            => 'Gemas y Bundler',
                'md_file_path'     => 'content/lessons/ruby/13-gemas-bundler.md',
                'excerpt'          => 'RubyGems, Gemfile, Bundler, crear gemas propias, Rake y gestión de versiones.',
                'published'        => true,
                'sort_order'       => 13,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'testing-ruby',
                'title'            => 'Testing con RSpec y Minitest',
                'md_file_path'     => 'content/lessons/ruby/14-testing.md',
                'excerpt'          => 'Minitest, RSpec (describe/it), matchers, let, hooks, mocks, stubs y shared examples.',
                'published'        => true,
                'sort_order'       => 14,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'concurrencia-ruby',
                'title'            => 'Concurrencia y Paralelismo',
                'md_file_path'     => 'content/lessons/ruby/15-concurrencia.md',
                'excerpt'          => 'Threads, Mutex, Fibers, Ractors, Async gem, fork/Process y GVL.',
                'published'        => true,
                'sort_order'       => 15,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'patrones-diseno-ruby',
                'title'            => 'Patrones de Diseño en Ruby',
                'md_file_path'     => 'content/lessons/ruby/16-patrones-diseno.md',
                'excerpt'          => 'Singleton, Observer, Strategy, Decorator, Builder, Repository y Service Object.',
                'published'        => true,
                'sort_order'       => 16,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'http-apis-ruby',
                'title'            => 'HTTP, APIs y Networking',
                'md_file_path'     => 'content/lessons/ruby/17-http-apis.md',
                'excerpt'          => 'Net::HTTP, HTTParty, Faraday, WEBrick, Sinatra, WebSockets y Sockets TCP.',
                'published'        => true,
                'sort_order'       => 17,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'ruby-moderno',
                'title'            => 'Ruby Moderno (3.x): Novedades y Best Practices',
                'md_file_path'     => 'content/lessons/ruby/18-ruby-moderno.md',
                'excerpt'          => 'Ruby 3.0-3.3: Pattern matching, Ractors, Data class, YJIT, endless methods y best practices.',
                'published'        => true,
                'sort_order'       => 18,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'preguntas-entrevista-ruby',
                'title'            => 'Preguntas de Entrevista: Ruby',
                'md_file_path'     => 'content/lessons/ruby/19-preguntas-entrevista.md',
                'excerpt'          => 'Symbol vs String, GVL, Proc vs Lambda, duck typing, method_missing y pattern matching.',
                'published'        => true,
                'sort_order'       => 19,
                'duration_minutes' => 25,
            ],
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
            if ($tagFunciones && in_array($sort, [4, 11])) {
                $lesson->tags()->syncWithoutDetaching([$tagFunciones->id]);
            }
            if ($tagPoo && in_array($sort, [5, 6])) {
                $lesson->tags()->syncWithoutDetaching([$tagPoo->id]);
            }
        }
    }
}
