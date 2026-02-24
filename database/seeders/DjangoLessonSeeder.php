<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class DjangoLessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'django-fullstack')->first();

        if (! $course) {
            $this->command->warn('Django course not found. Run CourseSeeder first.');
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
            ['slug' => 'introduccion-django',     'title' => 'Introducción a Django',              'md_file_path' => 'content/lessons/django/01-introduccion-django.md',    'excerpt' => 'Framework fullstack, startproject, estructura y manage.py.',          'published' => true, 'sort_order' => 1,  'duration_minutes' => 20],
            ['slug' => 'modelos-orm-django',      'title' => 'Modelos y ORM',                      'md_file_path' => 'content/lessons/django/02-modelos-orm.md',            'excerpt' => 'models.py, Field types, migrations, QuerySets y managers.',           'published' => true, 'sort_order' => 2,  'duration_minutes' => 30],
            ['slug' => 'vistas-urls-django',      'title' => 'Vistas y URLs',                      'md_file_path' => 'content/lessons/django/03-vistas-urls.md',            'excerpt' => 'FBV, CBV, path(), include(), URL namespaces.',                        'published' => true, 'sort_order' => 3,  'duration_minutes' => 25],
            ['slug' => 'templates-django',        'title' => 'Sistema de Templates',               'md_file_path' => 'content/lessons/django/04-templates.md',              'excerpt' => 'DTL, variables, tags, filters, herencia y includes.',                 'published' => true, 'sort_order' => 4,  'duration_minutes' => 25],
            ['slug' => 'formularios-django',      'title' => 'Formularios',                        'md_file_path' => 'content/lessons/django/05-formularios.md',            'excerpt' => 'Form, ModelForm, validación, widgets, CSRF y formsets.',              'published' => true, 'sort_order' => 5,  'duration_minutes' => 25],
            ['slug' => 'admin-django',            'title' => 'Panel de Administración',            'md_file_path' => 'content/lessons/django/06-admin.md',                  'excerpt' => 'ModelAdmin, list_display, search, filters, inlines y actions.',       'published' => true, 'sort_order' => 6,  'duration_minutes' => 25],
            ['slug' => 'relaciones-queries',      'title' => 'Relaciones y Queries Avanzados',    'md_file_path' => 'content/lessons/django/07-relaciones-queries.md',     'excerpt' => 'FK, M2M, select_related, prefetch_related, Q, F y aggregates.',       'published' => true, 'sort_order' => 7,  'duration_minutes' => 30],
            ['slug' => 'autenticacion-django',    'title' => 'Autenticación y Autorización',      'md_file_path' => 'content/lessons/django/08-autenticacion.md',           'excerpt' => 'User model, login/logout, permisos, groups y custom User.',           'published' => true, 'sort_order' => 8,  'duration_minutes' => 30],
            ['slug' => 'middleware-django',       'title' => 'Middleware',                          'md_file_path' => 'content/lessons/django/09-middleware.md',              'excerpt' => 'Cadena de middleware, personalizado, SecurityMiddleware y CORS.',     'published' => true, 'sort_order' => 9,  'duration_minutes' => 20],
            ['slug' => 'archivos-static-django',  'title' => 'Archivos Estáticos y Media',         'md_file_path' => 'content/lessons/django/10-archivos-static.md',        'excerpt' => 'STATIC, MEDIA, collectstatic, FileField e ImageField.',               'published' => true, 'sort_order' => 10, 'duration_minutes' => 20],
            ['slug' => 'drf-django',              'title' => 'Django REST Framework',              'md_file_path' => 'content/lessons/django/11-rest-framework.md',         'excerpt' => 'Serializers, ViewSets, routers, permissions y pagination.',            'published' => true, 'sort_order' => 11, 'duration_minutes' => 30],
            ['slug' => 'signals-django',          'title' => 'Signals',                            'md_file_path' => 'content/lessons/django/12-signals.md',                'excerpt' => 'pre_save, post_save, @receiver, custom signals y AppConfig.',         'published' => true, 'sort_order' => 12, 'duration_minutes' => 20],
            ['slug' => 'celery-django',           'title' => 'Tareas con Celery',                  'md_file_path' => 'content/lessons/django/13-celery-tareas.md',           'excerpt' => 'Celery+Django, shared_task, periodic tasks y monitoring.',              'published' => true, 'sort_order' => 13, 'duration_minutes' => 25],
            ['slug' => 'testing-django',          'title' => 'Testing en Django',                  'md_file_path' => 'content/lessons/django/14-testing.md',                'excerpt' => 'TestCase, Client, factory_boy, testing models/views/forms.',           'published' => true, 'sort_order' => 14, 'duration_minutes' => 30],
            ['slug' => 'cache-django',            'title' => 'Caché y Performance',                'md_file_path' => 'content/lessons/django/15-cache.md',                  'excerpt' => 'Cache framework, @cache_page, Redis, query optimization.',             'published' => true, 'sort_order' => 15, 'duration_minutes' => 25],
            ['slug' => 'seguridad-django',        'title' => 'Seguridad en Django',                'md_file_path' => 'content/lessons/django/16-seguridad.md',              'excerpt' => 'CSRF, XSS, HTTPS, SECURE_* settings y Content Security Policy.',      'published' => true, 'sort_order' => 16, 'duration_minutes' => 25],
            ['slug' => 'channels-websockets',     'title' => 'Django Channels y WebSockets',      'md_file_path' => 'content/lessons/django/17-channels-websockets.md',    'excerpt' => 'ASGI, consumers, routing, Channel layers y chat en tiempo real.',      'published' => true, 'sort_order' => 17, 'duration_minutes' => 30],
            ['slug' => 'deploy-django',           'title' => 'Deploy y Producción',                'md_file_path' => 'content/lessons/django/18-deploy-produccion.md',      'excerpt' => 'Gunicorn+Nginx, Docker, collectstatic, CI/CD y deploy cloud.',         'published' => true, 'sort_order' => 18, 'duration_minutes' => 25],
            ['slug' => 'entrevista-django',       'title' => 'Preguntas de Entrevista: Django',    'md_file_path' => 'content/lessons/django/19-preguntas-entrevista.md',   'excerpt' => 'MVT, ORM vs raw SQL, N+1, signals, middleware y Django vs Flask.',     'published' => true, 'sort_order' => 19, 'duration_minutes' => 25],
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
            if ($tagBd && in_array($sort, [2, 7])) {
                $lesson->tags()->syncWithoutDetaching([$tagBd->id]);
            }
            if ($tagWeb && in_array($sort, [4, 5, 8, 10, 17])) {
                $lesson->tags()->syncWithoutDetaching([$tagWeb->id]);
            }
            if ($tagBackend && in_array($sort, [9, 13, 15, 16, 18])) {
                $lesson->tags()->syncWithoutDetaching([$tagBackend->id]);
            }
            if ($tagApi && in_array($sort, [11])) {
                $lesson->tags()->syncWithoutDetaching([$tagApi->id]);
            }
        }
    }
}
