<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class RailsLessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'rails-8-fullstack')->first();

        if (! $course) {
            $this->command->warn('Rails 8 course not found. Run CourseSeeder first.');
            return;
        }

        $tagPrincipiante = Tag::where('slug', 'principiante')->first();
        $tagIntermedio   = Tag::where('slug', 'intermedio')->first();
        $tagAvanzado     = Tag::where('slug', 'avanzado')->first();
        $tagWeb          = Tag::where('slug', 'web')->first();
        $tagBackend      = Tag::where('slug', 'backend')->first();
        $tagApi          = Tag::where('slug', 'api')->first();
        $tagBd           = Tag::where('slug', 'bases-de-datos')->first();

        $lessons = [
            [
                'slug'             => 'introduccion-rails',
                'title'            => 'Introducción a Rails 8',
                'md_file_path'     => 'content/lessons/rails/01-introduccion-rails.md',
                'excerpt'          => 'Filosofía, instalación, rails new, estructura de proyecto y primer Hello World.',
                'published'        => true,
                'sort_order'       => 1,
                'duration_minutes' => 20,
            ],
            [
                'slug'             => 'arquitectura-mvc-rails',
                'title'            => 'Arquitectura MVC en Rails',
                'md_file_path'     => 'content/lessons/rails/02-arquitectura-mvc.md',
                'excerpt'          => 'Model-View-Controller, flujo de peticiones HTTP, Zeitwerk y convenciones de nombres.',
                'published'        => true,
                'sort_order'       => 2,
                'duration_minutes' => 20,
            ],
            [
                'slug'             => 'routing-rails',
                'title'            => 'Sistema de Rutas',
                'md_file_path'     => 'content/lessons/rails/03-routing.md',
                'excerpt'          => 'resources, rutas RESTful, nested routes, namespace/scope, constraints y route helpers.',
                'published'        => true,
                'sort_order'       => 3,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'active-record-modelos-rails',
                'title'            => 'Active Record: Modelos y Migraciones',
                'md_file_path'     => 'content/lessons/rails/04-active-record-modelos.md',
                'excerpt'          => 'Crear modelos, migraciones, tipos de datos, db:migrate, db:rollback y schema.rb.',
                'published'        => true,
                'sort_order'       => 4,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'asociaciones-rails',
                'title'            => 'Asociaciones en Active Record',
                'md_file_path'     => 'content/lessons/rails/05-asociaciones.md',
                'excerpt'          => 'belongs_to, has_many, has_one, :through, polimórficas, eager loading y N+1.',
                'published'        => true,
                'sort_order'       => 5,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'validaciones-callbacks-rails',
                'title'            => 'Validaciones y Callbacks',
                'md_file_path'     => 'content/lessons/rails/06-validaciones-callbacks.md',
                'excerpt'          => 'presence, uniqueness, format, custom validations, errors, before_save y callbacks.',
                'published'        => true,
                'sort_order'       => 6,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'controllers-acciones-rails',
                'title'            => 'Controllers y Acciones',
                'md_file_path'     => 'content/lessons/rails/07-controllers-acciones.md',
                'excerpt'          => 'CRUD, strong parameters, before_action, respond_to, render, flash y concerns.',
                'published'        => true,
                'sort_order'       => 7,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'vistas-layouts-rails',
                'title'            => 'Vistas, Layouts y Partials',
                'md_file_path'     => 'content/lessons/rails/08-vistas-layouts.md',
                'excerpt'          => 'ERB, layouts, yield, content_for, partials, colecciones y helpers.',
                'published'        => true,
                'sort_order'       => 8,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'hotwire-turbo-rails',
                'title'            => 'Hotwire y Turbo',
                'md_file_path'     => 'content/lessons/rails/09-hotwire-turbo.md',
                'excerpt'          => 'Turbo Drive, Turbo Frames, Turbo Streams, Broadcasting y morphing en Rails 8.',
                'published'        => true,
                'sort_order'       => 9,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'stimulus-rails',
                'title'            => 'Stimulus',
                'md_file_path'     => 'content/lessons/rails/10-stimulus.md',
                'excerpt'          => 'Controllers, targets, values, actions, lifecycle callbacks, outlets y CSS classes.',
                'published'        => true,
                'sort_order'       => 10,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'action-cable-rails',
                'title'            => 'Action Cable (WebSockets)',
                'md_file_path'     => 'content/lessons/rails/11-action-cable.md',
                'excerpt'          => 'Channels, subscriptions, broadcasting, stream_from, authentication y chat en tiempo real.',
                'published'        => true,
                'sort_order'       => 11,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'autenticacion-rails',
                'title'            => 'Autenticación en Rails 8',
                'md_file_path'     => 'content/lessons/rails/12-autenticacion.md',
                'excerpt'          => 'has_secure_password, generador de autenticación, sessions, current_user y tokens.',
                'published'        => true,
                'sort_order'       => 12,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'autorizacion-rails',
                'title'            => 'Autorización',
                'md_file_path'     => 'content/lessons/rails/13-autorizacion.md',
                'excerpt'          => 'Roles, before_action, Pundit, policies, scopes y authorization patterns.',
                'published'        => true,
                'sort_order'       => 13,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'api-mode-rails',
                'title'            => 'Rails como API',
                'md_file_path'     => 'content/lessons/rails/14-api-mode.md',
                'excerpt'          => 'API mode, jbuilder, serializers, versionado, CORS, rate limiting y tokens.',
                'published'        => true,
                'sort_order'       => 14,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'active-job-mailers-rails',
                'title'            => 'Active Job y Action Mailer',
                'md_file_path'     => 'content/lessons/rails/15-active-job-mailers.md',
                'excerpt'          => 'Jobs, perform_later, Solid Queue, Action Mailer, previews y deliver_later.',
                'published'        => true,
                'sort_order'       => 15,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'testing-rails',
                'title'            => 'Testing en Rails',
                'md_file_path'     => 'content/lessons/rails/16-testing-rails.md',
                'excerpt'          => 'Minitest vs RSpec, model/controller/integration tests, Capybara y FactoryBot.',
                'published'        => true,
                'sort_order'       => 16,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'deployment-rails',
                'title'            => 'Deployment y DevOps',
                'md_file_path'     => 'content/lessons/rails/17-deployment.md',
                'excerpt'          => 'Producción, Kamal 2, Docker, credentials, Solid Cache, Solid Queue y Thruster.',
                'published'        => true,
                'sort_order'       => 17,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'rails8-novedades',
                'title'            => 'Novedades de Rails 8',
                'md_file_path'     => 'content/lessons/rails/18-rails8-novedades.md',
                'excerpt'          => 'Solid Trifecta, Authentication generator, Kamal 2, Thruster, Propshaft e Importmaps.',
                'published'        => true,
                'sort_order'       => 18,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'preguntas-entrevista-rails',
                'title'            => 'Preguntas de Entrevista: Rails',
                'md_file_path'     => 'content/lessons/rails/19-preguntas-entrevista.md',
                'excerpt'          => 'N+1, strong params, concerns, STI, scopes, REST, caching, seguridad y más.',
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

            // Nivel
            if ($tagPrincipiante && $sort <= 4) {
                $lesson->tags()->syncWithoutDetaching([$tagPrincipiante->id]);
            }
            if ($tagIntermedio && $sort >= 5 && $sort <= 13) {
                $lesson->tags()->syncWithoutDetaching([$tagIntermedio->id]);
            }
            if ($tagAvanzado && $sort >= 14) {
                $lesson->tags()->syncWithoutDetaching([$tagAvanzado->id]);
            }

            // Temáticas
            if ($tagBd && in_array($sort, [4, 5, 6])) {
                $lesson->tags()->syncWithoutDetaching([$tagBd->id]);
            }
            if ($tagWeb && in_array($sort, [8, 9, 10, 11])) {
                $lesson->tags()->syncWithoutDetaching([$tagWeb->id]);
            }
            if ($tagBackend && in_array($sort, [7, 12, 13, 15])) {
                $lesson->tags()->syncWithoutDetaching([$tagBackend->id]);
            }
            if ($tagApi && in_array($sort, [14])) {
                $lesson->tags()->syncWithoutDetaching([$tagApi->id]);
            }
        }
    }
}
