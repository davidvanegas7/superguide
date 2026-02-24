<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class PhoenixLessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'phoenix-framework')->first();

        if (! $course) {
            $this->command->warn('Phoenix course not found. Run CourseSeeder first.');
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
            ['slug' => 'introduccion-phoenix',      'title' => 'Introducción a Phoenix',              'md_file_path' => 'content/lessons/phoenix/01-introduccion-phoenix.md',    'excerpt' => 'Framework web en Elixir, mix phx.new, estructura y MVC.',            'published' => true, 'sort_order' => 1,  'duration_minutes' => 20],
            ['slug' => 'rutas-controllers-phoenix',  'title' => 'Rutas y Controllers',                 'md_file_path' => 'content/lessons/phoenix/02-rutas-controllers.md',       'excerpt' => 'Router, pipelines, resources, controller actions y conn.',           'published' => true, 'sort_order' => 2,  'duration_minutes' => 25],
            ['slug' => 'vistas-templates-phoenix',   'title' => 'Vistas y Templates',                  'md_file_path' => 'content/lessons/phoenix/03-vistas-templates.md',        'excerpt' => 'HEEx templates, layouts, components y function components.',         'published' => true, 'sort_order' => 3,  'duration_minutes' => 25],
            ['slug' => 'ecto-modelos-phoenix',       'title' => 'Ecto y Modelos',                      'md_file_path' => 'content/lessons/phoenix/04-ecto-modelos.md',            'excerpt' => 'Schema, changesets, migrations, Repo y validaciones.',               'published' => true, 'sort_order' => 4,  'duration_minutes' => 30],
            ['slug' => 'formularios-phoenix',        'title' => 'Formularios',                         'md_file_path' => 'content/lessons/phoenix/05-formularios.md',             'excerpt' => 'Forms con changesets, validación, uploads y componentes.',            'published' => true, 'sort_order' => 5,  'duration_minutes' => 25],
            ['slug' => 'liveview-fundamentos',       'title' => 'LiveView Fundamentos',                'md_file_path' => 'content/lessons/phoenix/06-liveview-fundamentos.md',    'excerpt' => 'mount, render, handle_event, LiveView lifecycle y state.',            'published' => true, 'sort_order' => 6,  'duration_minutes' => 30],
            ['slug' => 'liveview-avanzado',          'title' => 'LiveView Avanzado',                   'md_file_path' => 'content/lessons/phoenix/07-liveview-avanzado.md',       'excerpt' => 'LiveComponents, streams, uploads, JS hooks y navegación.',            'published' => true, 'sort_order' => 7,  'duration_minutes' => 30],
            ['slug' => 'autenticacion-phoenix',      'title' => 'Autenticación',                       'md_file_path' => 'content/lessons/phoenix/08-autenticacion.md',           'excerpt' => 'mix phx.gen.auth, sessions, tokens, Guardian y OAuth.',               'published' => true, 'sort_order' => 8,  'duration_minutes' => 30],
            ['slug' => 'channels-phoenix',           'title' => 'Channels y WebSockets',               'md_file_path' => 'content/lessons/phoenix/09-channels.md',                'excerpt' => 'Socket, Channel, join, handle_in, broadcast y presences.',            'published' => true, 'sort_order' => 9,  'duration_minutes' => 30],
            ['slug' => 'pubsub-phoenix',             'title' => 'PubSub y Tiempo Real',                'md_file_path' => 'content/lessons/phoenix/10-pubsub.md',                  'excerpt' => 'Phoenix.PubSub, subscribe, broadcast, clusters y chat.',              'published' => true, 'sort_order' => 10, 'duration_minutes' => 25],
            ['slug' => 'api-rest-phoenix',           'title' => 'APIs REST con Phoenix',               'md_file_path' => 'content/lessons/phoenix/11-api-rest.md',                'excerpt' => 'JSON API, controllers API, versioning, Swagger y CORS.',              'published' => true, 'sort_order' => 11, 'duration_minutes' => 30],
            ['slug' => 'plugs-phoenix',              'title' => 'Plugs y Middleware',                  'md_file_path' => 'content/lessons/phoenix/12-plugs.md',                   'excerpt' => 'Module plugs, function plugs, pipelines y custom plugs.',             'published' => true, 'sort_order' => 12, 'duration_minutes' => 25],
            ['slug' => 'contextos-phoenix',          'title' => 'Contextos y Arquitectura',            'md_file_path' => 'content/lessons/phoenix/13-contextos.md',               'excerpt' => 'Bounded contexts, generators, separación de dominios.',               'published' => true, 'sort_order' => 13, 'duration_minutes' => 25],
            ['slug' => 'testing-phoenix',            'title' => 'Testing en Phoenix',                  'md_file_path' => 'content/lessons/phoenix/14-testing.md',                 'excerpt' => 'ConnTest, DataCase, feature tests, mocks y factories.',               'published' => true, 'sort_order' => 14, 'duration_minutes' => 30],
            ['slug' => 'genserver-phoenix',          'title' => 'GenServer en Phoenix',                'md_file_path' => 'content/lessons/phoenix/15-genserver-phoenix.md',       'excerpt' => 'GenServer en apps Phoenix, cache, rate limiting y estado.',            'published' => true, 'sort_order' => 15, 'duration_minutes' => 25],
            ['slug' => 'oban-jobs-phoenix',          'title' => 'Jobs con Oban',                       'md_file_path' => 'content/lessons/phoenix/16-oban-jobs.md',               'excerpt' => 'Oban workers, queues, scheduling, retries y cron.',                   'published' => true, 'sort_order' => 16, 'duration_minutes' => 25],
            ['slug' => 'seguridad-phoenix',          'title' => 'Seguridad en Phoenix',                'md_file_path' => 'content/lessons/phoenix/17-seguridad.md',               'excerpt' => 'CSRF, CSP, HTTPS, rate limiting y security headers.',                 'published' => true, 'sort_order' => 17, 'duration_minutes' => 25],
            ['slug' => 'deploy-phoenix',             'title' => 'Deploy y Producción',                 'md_file_path' => 'content/lessons/phoenix/18-deploy.md',                  'excerpt' => 'Releases, Docker, Fly.io, Gigalixir y hot upgrades.',                 'published' => true, 'sort_order' => 18, 'duration_minutes' => 25],
            ['slug' => 'entrevista-phoenix',         'title' => 'Preguntas de Entrevista: Phoenix',    'md_file_path' => 'content/lessons/phoenix/19-preguntas-entrevista.md',    'excerpt' => 'LiveView, Channels, Ecto, Plugs, OTP y concurrencia.',                'published' => true, 'sort_order' => 19, 'duration_minutes' => 25],
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
            if ($tagBd && in_array($sort, [4])) {
                $lesson->tags()->syncWithoutDetaching([$tagBd->id]);
            }
            if ($tagWeb && in_array($sort, [3, 5, 6, 7])) {
                $lesson->tags()->syncWithoutDetaching([$tagWeb->id]);
            }
            if ($tagBackend && in_array($sort, [9, 10, 12, 15, 16, 17, 18])) {
                $lesson->tags()->syncWithoutDetaching([$tagBackend->id]);
            }
            if ($tagApi && in_array($sort, [11])) {
                $lesson->tags()->syncWithoutDetaching([$tagApi->id]);
            }
        }
    }
}
