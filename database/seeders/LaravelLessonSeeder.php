<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class LaravelLessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'laravel-fullstack')->first();

        if (! $course) {
            $this->command->warn('Laravel course not found. Run CourseSeeder first.');
            return;
        }

        $tagPrincipiante = Tag::where('slug', 'principiante')->first();
        $tagIntermedio    = Tag::where('slug', 'intermedio')->first();
        $tagAvanzado      = Tag::where('slug', 'avanzado')->first();
        $tagBd            = Tag::where('slug', 'bases-de-datos')->first();
        $tagWeb           = Tag::where('slug', 'web')->first();
        $tagBackend       = Tag::where('slug', 'backend')->first();
        $tagApi           = Tag::where('slug', 'api')->first();

        $lessons = [
            [
                'slug'             => 'instalacion-estructura-laravel',
                'title'            => 'Instalación y Estructura de un Proyecto Laravel',
                'md_file_path'     => 'content/lessons/laravel/01-instalacion-estructura.md',
                'excerpt'          => 'Instalación con Composer, estructura de carpetas, archivo .env y Artisan CLI.',
                'published'        => true,
                'sort_order'       => 1,
                'duration_minutes' => 20,
            ],
            [
                'slug'             => 'rutas-controladores-laravel',
                'title'            => 'Rutas y Controladores',
                'md_file_path'     => 'content/lessons/laravel/02-rutas-y-controladores.md',
                'excerpt'          => 'Definición de rutas, parámetros, grupos, named routes y resource controllers.',
                'published'        => true,
                'sort_order'       => 2,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'vistas-blade-laravel',
                'title'            => 'Vistas y Motor de Plantillas Blade',
                'md_file_path'     => 'content/lessons/laravel/03-vistas-blade.md',
                'excerpt'          => 'Layouts, directivas Blade, componentes, slots y directivas personalizadas.',
                'published'        => true,
                'sort_order'       => 3,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'migraciones-schema-laravel',
                'title'            => 'Migraciones y Schema Builder',
                'md_file_path'     => 'content/lessons/laravel/04-migraciones-schema.md',
                'excerpt'          => 'Crear y gestionar migraciones, tipos de columna, índices, foreign keys y rollback.',
                'published'        => true,
                'sort_order'       => 4,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'eloquent-basico-laravel',
                'title'            => 'Eloquent ORM: Fundamentos',
                'md_file_path'     => 'content/lessons/laravel/05-eloquent-basico.md',
                'excerpt'          => 'Modelos, CRUD, mass assignment, scopes, accessors, mutators y casting.',
                'published'        => true,
                'sort_order'       => 5,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'eloquent-relaciones-laravel',
                'title'            => 'Eloquent: Relaciones',
                'md_file_path'     => 'content/lessons/laravel/06-eloquent-relaciones.md',
                'excerpt'          => 'hasOne, hasMany, belongsTo, belongsToMany, polimorfismo, eager loading y withCount.',
                'published'        => true,
                'sort_order'       => 6,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'seeders-factories-laravel',
                'title'            => 'Seeders y Factories',
                'md_file_path'     => 'content/lessons/laravel/07-seeders-factories.md',
                'excerpt'          => 'Factories con Faker, estados, relaciones en factories y DatabaseSeeder.',
                'published'        => true,
                'sort_order'       => 7,
                'duration_minutes' => 20,
            ],
            [
                'slug'             => 'validacion-laravel',
                'title'            => 'Validación de Datos',
                'md_file_path'     => 'content/lessons/laravel/08-validacion.md',
                'excerpt'          => 'Form Requests, reglas built-in, mensajes personalizados y custom rules.',
                'published'        => true,
                'sort_order'       => 8,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'middleware-laravel',
                'title'            => 'Middleware',
                'md_file_path'     => 'content/lessons/laravel/09-middleware.md',
                'excerpt'          => 'Middleware global, de ruta y de grupo, before/after, rate limiting y terminate.',
                'published'        => true,
                'sort_order'       => 9,
                'duration_minutes' => 20,
            ],
            [
                'slug'             => 'autenticacion-laravel',
                'title'            => 'Autenticación',
                'md_file_path'     => 'content/lessons/laravel/10-autenticacion.md',
                'excerpt'          => 'Breeze, Sanctum, guards, login/logout, password reset y email verification.',
                'published'        => true,
                'sort_order'       => 10,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'autorizacion-laravel',
                'title'            => 'Autorización: Gates y Policies',
                'md_file_path'     => 'content/lessons/laravel/11-autorizacion.md',
                'excerpt'          => 'Gates, Policies, @can/@cannot, authorize() y policy auto-discovery.',
                'published'        => true,
                'sort_order'       => 11,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'api-recursos-laravel',
                'title'            => 'APIs RESTful y API Resources',
                'md_file_path'     => 'content/lessons/laravel/12-api-recursos.md',
                'excerpt'          => 'apiResource, JsonResource, ResourceCollection, paginación y Sanctum tokens.',
                'published'        => true,
                'sort_order'       => 12,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'colas-jobs-laravel',
                'title'            => 'Colas y Jobs',
                'md_file_path'     => 'content/lessons/laravel/13-colas-jobs.md',
                'excerpt'          => 'Jobs, dispatching, queue drivers, retries, batching, chaining y failed jobs.',
                'published'        => true,
                'sort_order'       => 13,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'eventos-listeners-laravel',
                'title'            => 'Eventos y Listeners',
                'md_file_path'     => 'content/lessons/laravel/14-eventos-listeners.md',
                'excerpt'          => 'Event dispatching, listeners, subscribers, model observers y event discovery.',
                'published'        => true,
                'sort_order'       => 14,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'notificaciones-mail-laravel',
                'title'            => 'Notificaciones y Mail',
                'md_file_path'     => 'content/lessons/laravel/15-notificaciones-mail.md',
                'excerpt'          => 'Notification channels, mailables, markdown mail y notificaciones en cola.',
                'published'        => true,
                'sort_order'       => 15,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'testing-laravel',
                'title'            => 'Testing en Laravel',
                'md_file_path'     => 'content/lessons/laravel/16-testing.md',
                'excerpt'          => 'PHPUnit y Pest, HTTP tests, database testing, mocking y faking.',
                'published'        => true,
                'sort_order'       => 16,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'livewire-laravel',
                'title'            => 'Laravel Livewire',
                'md_file_path'     => 'content/lessons/laravel/17-livewire.md',
                'excerpt'          => 'Componentes Livewire, data binding, acciones, validación en tiempo real y Alpine.js.',
                'published'        => true,
                'sort_order'       => 17,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'deploy-produccion-laravel',
                'title'            => 'Deploy y Producción',
                'md_file_path'     => 'content/lessons/laravel/18-deploy-produccion.md',
                'excerpt'          => 'Cacheo de config/rutas/vistas, optimize, Forge/Vapor, supervisor y health checks.',
                'published'        => true,
                'sort_order'       => 18,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'preguntas-entrevista-laravel',
                'title'            => 'Preguntas de Entrevista: Laravel',
                'md_file_path'     => 'content/lessons/laravel/19-preguntas-entrevista.md',
                'excerpt'          => 'Service Container, Facades, Request lifecycle, N+1, CSRF, Eloquent vs Query Builder.',
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

            // Niveles
            if ($tagPrincipiante && $sort <= 4) {
                $lesson->tags()->syncWithoutDetaching([$tagPrincipiante->id]);
            }
            if ($tagIntermedio && $sort >= 5 && $sort <= 12) {
                $lesson->tags()->syncWithoutDetaching([$tagIntermedio->id]);
            }
            if ($tagAvanzado && $sort >= 13) {
                $lesson->tags()->syncWithoutDetaching([$tagAvanzado->id]);
            }

            // DB: migrations, eloquent basic, eloquent relations, seeders
            if ($tagBd && in_array($sort, [4, 5, 6, 7])) {
                $lesson->tags()->syncWithoutDetaching([$tagBd->id]);
            }
            // Web: Blade, middleware, auth, Livewire
            if ($tagWeb && in_array($sort, [3, 9, 10, 17])) {
                $lesson->tags()->syncWithoutDetaching([$tagWeb->id]);
            }
            // Backend: queues, events, notifications, testing, deploy
            if ($tagBackend && in_array($sort, [13, 14, 15, 16, 18])) {
                $lesson->tags()->syncWithoutDetaching([$tagBackend->id]);
            }
            // API
            if ($tagApi && in_array($sort, [12])) {
                $lesson->tags()->syncWithoutDetaching([$tagApi->id]);
            }
        }
    }
}
