<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class FlaskLessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'flask-backend')->first();

        if (! $course) {
            $this->command->warn('Flask course not found. Run CourseSeeder first.');
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
            ['slug' => 'introduccion-flask',      'title' => 'Introducción a Flask',              'md_file_path' => 'content/lessons/flask/01-introduccion-flask.md',    'excerpt' => 'Microframework Flask, instalación, app mínima y modo debug.',         'published' => true, 'sort_order' => 1,  'duration_minutes' => 20],
            ['slug' => 'rutas-vistas-flask',      'title' => 'Rutas y Vistas',                    'md_file_path' => 'content/lessons/flask/02-rutas-vistas.md',          'excerpt' => '@app.route, métodos HTTP, variables de ruta y url_for.',              'published' => true, 'sort_order' => 2,  'duration_minutes' => 25],
            ['slug' => 'templates-jinja2',        'title' => 'Templates con Jinja2',              'md_file_path' => 'content/lessons/flask/03-templates-jinja2.md',      'excerpt' => 'render_template, herencia, filtros, macros y autoescaping.',          'published' => true, 'sort_order' => 3,  'duration_minutes' => 25],
            ['slug' => 'formularios-request',     'title' => 'Formularios y Request',             'md_file_path' => 'content/lessons/flask/04-formularios-request.md',   'excerpt' => 'request object, Flask-WTF, CSRF protection y validación.',            'published' => true, 'sort_order' => 4,  'duration_minutes' => 25],
            ['slug' => 'sqlalchemy-flask',        'title' => 'Base de Datos con SQLAlchemy',      'md_file_path' => 'content/lessons/flask/05-base-datos-sqlalchemy.md', 'excerpt' => 'Flask-SQLAlchemy, modelos, CRUD, queries y relationships.',            'published' => true, 'sort_order' => 5,  'duration_minutes' => 30],
            ['slug' => 'migraciones-alembic',     'title' => 'Migraciones con Alembic',           'md_file_path' => 'content/lessons/flask/06-migraciones-alembic.md',   'excerpt' => 'Flask-Migrate, flask db init/migrate/upgrade y cambios de schema.',   'published' => true, 'sort_order' => 6,  'duration_minutes' => 20],
            ['slug' => 'blueprints-flask',        'title' => 'Blueprints y Estructura',           'md_file_path' => 'content/lessons/flask/07-blueprints.md',            'excerpt' => 'Blueprint, application factory, config por entorno.',                 'published' => true, 'sort_order' => 7,  'duration_minutes' => 25],
            ['slug' => 'autenticacion-flask',     'title' => 'Autenticación y Sesiones',          'md_file_path' => 'content/lessons/flask/08-autenticacion.md',          'excerpt' => 'Flask-Login, session, password hashing y @login_required.',           'published' => true, 'sort_order' => 8,  'duration_minutes' => 30],
            ['slug' => 'api-rest-flask',          'title' => 'APIs REST con Flask',               'md_file_path' => 'content/lessons/flask/09-api-rest.md',              'excerpt' => 'jsonify, marshmallow, error handlers y CORS.',                        'published' => true, 'sort_order' => 9,  'duration_minutes' => 30],
            ['slug' => 'middleware-hooks-flask',   'title' => 'Middleware y Hooks',                'md_file_path' => 'content/lessons/flask/10-middleware-hooks.md',       'excerpt' => 'before_request, after_request, g object y contextos.',                'published' => true, 'sort_order' => 10, 'duration_minutes' => 20],
            ['slug' => 'testing-flask',           'title' => 'Testing en Flask',                  'md_file_path' => 'content/lessons/flask/11-testing-flask.md',          'excerpt' => 'test_client, pytest fixtures, testing routes y APIs.',                 'published' => true, 'sort_order' => 11, 'duration_minutes' => 25],
            ['slug' => 'archivos-uploads-flask',  'title' => 'Archivos y Uploads',                'md_file_path' => 'content/lessons/flask/12-archivos-uploads.md',      'excerpt' => 'File uploads, secure_filename, UPLOAD_FOLDER y cloud storage.',       'published' => true, 'sort_order' => 12, 'duration_minutes' => 20],
            ['slug' => 'websockets-flask',        'title' => 'WebSockets con Flask-SocketIO',     'md_file_path' => 'content/lessons/flask/13-websockets.md',             'excerpt' => 'Flask-SocketIO, emit, rooms, namespaces y chat en tiempo real.',       'published' => true, 'sort_order' => 13, 'duration_minutes' => 25],
            ['slug' => 'celery-flask',            'title' => 'Tareas Asíncronas con Celery',      'md_file_path' => 'content/lessons/flask/14-tareas-celery.md',          'excerpt' => 'Celery, tasks, delay, periodic tasks y Redis broker.',                 'published' => true, 'sort_order' => 14, 'duration_minutes' => 25],
            ['slug' => 'seguridad-flask',         'title' => 'Seguridad en Flask',                'md_file_path' => 'content/lessons/flask/15-seguridad.md',              'excerpt' => 'CSRF, XSS, rate limiting, HTTPS y headers de seguridad.',              'published' => true, 'sort_order' => 15, 'duration_minutes' => 25],
            ['slug' => 'cache-performance-flask', 'title' => 'Caché y Rendimiento',               'md_file_path' => 'content/lessons/flask/16-cache-performance.md',     'excerpt' => 'Flask-Caching, cache decorators, Redis, compression y profiling.',    'published' => true, 'sort_order' => 16, 'duration_minutes' => 25],
            ['slug' => 'jwt-oauth-flask',         'title' => 'JWT y OAuth2',                      'md_file_path' => 'content/lessons/flask/17-jwt-oauth.md',              'excerpt' => 'Flask-JWT-Extended, access/refresh tokens, OAuth2 y third-party login.', 'published' => true, 'sort_order' => 17, 'duration_minutes' => 30],
            ['slug' => 'deploy-flask',            'title' => 'Deploy y Producción',               'md_file_path' => 'content/lessons/flask/18-deploy-produccion.md',      'excerpt' => 'Gunicorn, nginx, Docker, CI/CD y deploy en la nube.',                  'published' => true, 'sort_order' => 18, 'duration_minutes' => 25],
            ['slug' => 'entrevista-flask',        'title' => 'Preguntas de Entrevista: Flask',     'md_file_path' => 'content/lessons/flask/19-preguntas-entrevista.md',   'excerpt' => 'Flask vs Django, app context, blueprints, WSGI y thread safety.',      'published' => true, 'sort_order' => 19, 'duration_minutes' => 25],
        ];

        foreach ($lessons as $data) {
            $lesson = Lesson::firstOrCreate(
                ['course_id' => $course->id, 'slug' => $data['slug']],
                $data + ['course_id' => $course->id]
            );

            $sort = $data['sort_order'];

            if ($tagPrincipiante && $sort <= 3) {
                $lesson->tags()->syncWithoutDetaching([$tagPrincipiante->id]);
            }
            if ($tagIntermedio && $sort >= 4 && $sort <= 12) {
                $lesson->tags()->syncWithoutDetaching([$tagIntermedio->id]);
            }
            if ($tagAvanzado && $sort >= 13) {
                $lesson->tags()->syncWithoutDetaching([$tagAvanzado->id]);
            }
            if ($tagBd && in_array($sort, [5, 6])) {
                $lesson->tags()->syncWithoutDetaching([$tagBd->id]);
            }
            if ($tagWeb && in_array($sort, [3, 4, 8, 13])) {
                $lesson->tags()->syncWithoutDetaching([$tagWeb->id]);
            }
            if ($tagBackend && in_array($sort, [10, 14, 15, 16, 18])) {
                $lesson->tags()->syncWithoutDetaching([$tagBackend->id]);
            }
            if ($tagApi && in_array($sort, [9, 17])) {
                $lesson->tags()->syncWithoutDetaching([$tagApi->id]);
            }
        }
    }
}
