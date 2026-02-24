<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class PhpProfessionalLessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'php-profesional')->first();

        if (! $course) {
            $this->command->warn('PHP Professional course not found. Run CourseSeeder first.');
            return;
        }

        $tagIntermedio = Tag::where('slug', 'intermedio')->first();
        $tagAvanzado   = Tag::where('slug', 'avanzado')->first();
        $tagPoo        = Tag::where('slug', 'poo')->first();
        $tagFunciones  = Tag::where('slug', 'funciones')->first();
        $tagArrays     = Tag::where('slug', 'arrays')->first();
        $tagBd         = Tag::where('slug', 'bases-de-datos')->first();
        $tagBackend    = Tag::where('slug', 'backend')->first();

        $lessons = [
            [
                'slug'             => 'poo-avanzada-php',
                'title'            => 'POO Avanzada en PHP',
                'md_file_path'     => 'content/lessons/php-profesional/01-poo-avanzada.md',
                'excerpt'          => 'Clases abstractas, interfaces, traits, readonly, enums, clonación profunda y comparación de objetos.',
                'published'        => true,
                'sort_order'       => 1,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'interfaces-traits-php',
                'title'            => 'Interfaces, Traits y Composición',
                'md_file_path'     => 'content/lessons/php-profesional/02-interfaces-traits.md',
                'excerpt'          => 'Cuándo usar interface vs trait vs herencia, contratos, conflictos y adapter pattern.',
                'published'        => true,
                'sort_order'       => 2,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'namespaces-autoloading-php',
                'title'            => 'Namespaces y Autoloading',
                'md_file_path'     => 'content/lessons/php-profesional/03-namespaces-autoloading.md',
                'excerpt'          => 'Namespaces, use, alias, PSR-4, Composer autoloading y estructura de directorios.',
                'published'        => true,
                'sort_order'       => 3,
                'duration_minutes' => 20,
            ],
            [
                'slug'             => 'composer-paquetes-php',
                'title'            => 'Composer y Gestión de Paquetes',
                'md_file_path'     => 'content/lessons/php-profesional/04-composer-paquetes.md',
                'excerpt'          => 'composer.json, require, versionado, composer.lock, scripts y crear paquetes propios.',
                'published'        => true,
                'sort_order'       => 4,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'manejo-errores-php',
                'title'            => 'Manejo de Errores y Excepciones',
                'md_file_path'     => 'content/lessons/php-profesional/05-manejo-errores.md',
                'excerpt'          => 'try/catch/finally, excepciones personalizadas, SPL exceptions y error handlers.',
                'published'        => true,
                'sort_order'       => 5,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'generadores-iteradores-php',
                'title'            => 'Generadores e Iteradores',
                'md_file_path'     => 'content/lessons/php-profesional/06-generadores-iteradores.md',
                'excerpt'          => 'yield, yield from, Generator, Iterator, IteratorAggregate, lazy evaluation y eficiencia.',
                'published'        => true,
                'sort_order'       => 6,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'tipos-estrictos-php',
                'title'            => 'Sistema de Tipos y Tipado Estricto',
                'md_file_path'     => 'content/lessons/php-profesional/07-tipos-estrictos.md',
                'excerpt'          => 'strict_types, union/intersection types, nullable, never, void, mixed y Enums.',
                'published'        => true,
                'sort_order'       => 7,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'psr-standards-php',
                'title'            => 'Estándares PSR',
                'md_file_path'     => 'content/lessons/php-profesional/08-psr-standards.md',
                'excerpt'          => 'PSR-1, PSR-4, PSR-7, PSR-11, PSR-12, PSR-15, PSR-18 y PHP-FIG.',
                'published'        => true,
                'sort_order'       => 8,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'patrones-diseno-php',
                'title'            => 'Patrones de Diseño en PHP',
                'md_file_path'     => 'content/lessons/php-profesional/09-patrones-diseno.md',
                'excerpt'          => 'Singleton, Factory, Strategy, Observer, Decorator, Repository y DI Container.',
                'published'        => true,
                'sort_order'       => 9,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'testing-phpunit-php',
                'title'            => 'Testing con PHPUnit',
                'md_file_path'     => 'content/lessons/php-profesional/10-testing-phpunit.md',
                'excerpt'          => 'PHPUnit, assertions, data providers, mocks/stubs, setUp/tearDown y code coverage.',
                'published'        => true,
                'sort_order'       => 10,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'arrays-avanzados-php',
                'title'            => 'Arrays Avanzados en PHP',
                'md_file_path'     => 'content/lessons/php-profesional/11-arrays-avanzados.md',
                'excerpt'          => 'array_map, array_filter, array_reduce, sorting, destructuring y spread operator.',
                'published'        => true,
                'sort_order'       => 11,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'strings-regex-php',
                'title'            => 'Strings y Expresiones Regulares',
                'md_file_path'     => 'content/lessons/php-profesional/12-strings-regex.md',
                'excerpt'          => 'mb_string, preg_match/replace/split, named groups, heredoc y funciones PHP 8.',
                'published'        => true,
                'sort_order'       => 12,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'filesystem-php',
                'title'            => 'Sistema de Archivos',
                'md_file_path'     => 'content/lessons/php-profesional/13-filesystem.md',
                'excerpt'          => 'file_get/put_contents, SplFileObject, DirectoryIterator, glob, streams y file locking.',
                'published'        => true,
                'sort_order'       => 13,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'sesiones-cookies-php',
                'title'            => 'Sesiones y Cookies',
                'md_file_path'     => 'content/lessons/php-profesional/14-sesiones-cookies.md',
                'excerpt'          => 'session_start, SessionHandlerInterface, setcookie, httponly, secure y CSRF.',
                'published'        => true,
                'sort_order'       => 14,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'pdo-bases-datos-php',
                'title'            => 'PDO y Bases de Datos',
                'md_file_path'     => 'content/lessons/php-profesional/15-pdo-bases-datos.md',
                'excerpt'          => 'PDO connection, prepared statements, transactions, fetch modes y query builder.',
                'published'        => true,
                'sort_order'       => 15,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'seguridad-php',
                'title'            => 'Seguridad en PHP',
                'md_file_path'     => 'content/lessons/php-profesional/16-seguridad.md',
                'excerpt'          => 'SQL injection, XSS, CSRF, password_hash, sanitización, CORS y CSP.',
                'published'        => true,
                'sort_order'       => 16,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'rendimiento-php',
                'title'            => 'Rendimiento y Optimización',
                'md_file_path'     => 'content/lessons/php-profesional/17-rendimiento.md',
                'excerpt'          => 'OPcache, JIT, profiling, Fibers, preloading, caching y gestión de memoria.',
                'published'        => true,
                'sort_order'       => 17,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'php8-novedades',
                'title'            => 'Novedades de PHP 8.x',
                'md_file_path'     => 'content/lessons/php-profesional/18-php8-novedades.md',
                'excerpt'          => 'Named args, match, nullsafe, constructor promotion, Fibers, Enums y readonly.',
                'published'        => true,
                'sort_order'       => 18,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'preguntas-entrevista-php',
                'title'            => 'Preguntas de Entrevista: PHP',
                'md_file_path'     => 'content/lessons/php-profesional/19-preguntas-entrevista.md',
                'excerpt'          => '== vs ===, abstract vs interface, type juggling, late static binding, closures y DI.',
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

            if ($tagIntermedio && $sort <= 10) {
                $lesson->tags()->syncWithoutDetaching([$tagIntermedio->id]);
            }
            if ($tagAvanzado && $sort >= 11) {
                $lesson->tags()->syncWithoutDetaching([$tagAvanzado->id]);
            }
            if ($tagPoo && in_array($sort, [1, 2, 9])) {
                $lesson->tags()->syncWithoutDetaching([$tagPoo->id]);
            }
            if ($tagFunciones && in_array($sort, [6, 11])) {
                $lesson->tags()->syncWithoutDetaching([$tagFunciones->id]);
            }
            if ($tagArrays && in_array($sort, [11])) {
                $lesson->tags()->syncWithoutDetaching([$tagArrays->id]);
            }
            if ($tagBd && in_array($sort, [15])) {
                $lesson->tags()->syncWithoutDetaching([$tagBd->id]);
            }
            if ($tagBackend && in_array($sort, [14, 16, 17])) {
                $lesson->tags()->syncWithoutDetaching([$tagBackend->id]);
            }
        }
    }
}
