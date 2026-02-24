<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $php = Language::where('slug', 'php')->first();
        $js  = Language::where('slug', 'javascript')->first();

        if ($php) {
            Course::firstOrCreate(
                ['slug' => 'php-desde-cero'],
                [
                    'language_id' => $php->id,
                    'title'       => 'PHP desde Cero',
                    'description' => 'Aprende PHP desde los fundamentos hasta programación orientada a objetos.',
                    'level'       => 'beginner',
                    'published'   => true,
                    'sort_order'  => 1,
                ]
            );
        }

        if ($js) {
            Course::firstOrCreate(
                ['slug' => 'javascript-moderno'],
                [
                    'language_id' => $js->id,
                    'title'       => 'JavaScript Moderno (ES6+)',
                    'description' => 'Domina el JavaScript moderno: arrow functions, promesas, async/await y más.',
                    'level'       => 'intermediate',
                    'published'   => true,
                    'sort_order'  => 1,
                ]
            );
        }

        $ts = Language::where('slug', 'typescript')->first();

        if ($ts) {
            Course::firstOrCreate(
                ['slug' => 'nodejs-backend-typescript'],
                [
                    'language_id' => $ts->id,
                    'title'       => 'Node.js Backend con TypeScript',
                    'description' => 'Construye APIs REST profesionales con Node.js, Express, Prisma, JWT y Docker.',
                    'level'       => 'intermediate',
                    'published'   => true,
                    'sort_order'  => 2,
                ]
            );

            Course::firstOrCreate(
                ['slug' => 'backend-conceptos'],
                [
                    'language_id' => $ts->id,
                    'title'       => 'Conceptos de Backend',
                    'description' => 'POO, SOLID, patrones de diseño, bases de datos, ORM, arquitectura REST, seguridad, caché y todo lo que necesitas saber para trabajar como backend developer.',
                    'level'       => 'intermediate',
                    'published'   => true,
                    'sort_order'  => 3,
                ]
            );

            Course::firstOrCreate(
                ['slug' => 'react-frontend'],
                [
                    'language_id' => $ts->id,
                    'title'       => 'React: Frontend Moderno con TypeScript',
                    'description' => 'Domina React desde cero: componentes, hooks, estado, routing, testing, performance, Next.js y todo lo necesario para construir aplicaciones frontend profesionales.',
                    'level'       => 'intermediate',
                    'published'   => true,
                    'sort_order'  => 4,
                ]
            );
        }

        $ruby = Language::where('slug', 'ruby')->first();

        if ($ruby) {
            Course::firstOrCreate(
                ['slug' => 'ruby-desde-cero'],
                [
                    'language_id' => $ruby->id,
                    'title'       => 'Ruby: Lenguaje Elegante y Expresivo',
                    'description' => 'Aprende Ruby desde cero: sintaxis expresiva, bloques, POO, metaprogramación, gemas y todo lo necesario para dominar un lenguaje diseñado para la productividad.',
                    'level'       => 'beginner',
                    'published'   => true,
                    'sort_order'  => 5,
                ]
            );

            Course::firstOrCreate(
                ['slug' => 'rails-8-fullstack'],
                [
                    'language_id' => $ruby->id,
                    'title'       => 'Ruby on Rails 8: Desarrollo Fullstack',
                    'description' => 'Construye aplicaciones web completas con Rails 8: MVC, Active Record, Hotwire, Turbo, Stimulus, Action Cable, testing y deploy a producción.',
                    'level'       => 'intermediate',
                    'published'   => true,
                    'sort_order'  => 6,
                ]
            );
        }

        if ($php) {
            Course::firstOrCreate(
                ['slug' => 'php-profesional'],
                [
                    'language_id' => $php->id,
                    'title'       => 'PHP Profesional: De Intermedio a Avanzado',
                    'description' => 'Domina PHP moderno: POO avanzada, namespaces, Composer, PSR, testing con PHPUnit, patrones de diseño y buenas prácticas para desarrollo profesional.',
                    'level'       => 'intermediate',
                    'published'   => true,
                    'sort_order'  => 7,
                ]
            );
        }

        if ($php) {
            Course::firstOrCreate(
                ['slug' => 'laravel-fullstack'],
                [
                    'language_id' => $php->id,
                    'title'       => 'Laravel: Desarrollo Fullstack Moderno',
                    'description' => 'Construye aplicaciones web profesionales con Laravel: Eloquent, Blade, middleware, autenticación, API REST, queues, testing y deploy.',
                    'level'       => 'intermediate',
                    'published'   => true,
                    'sort_order'  => 8,
                ]
            );
        }

        $python = Language::where('slug', 'python')->first();

        if ($python) {
            Course::firstOrCreate(
                ['slug' => 'python-desde-cero'],
                [
                    'language_id' => $python->id,
                    'title'       => 'Python: Desde Cero hasta Profesional',
                    'description' => 'Domina Python desde los fundamentos: variables, estructuras de datos, POO, decoradores, generadores, testing, manejo de archivos y buenas prácticas.',
                    'level'       => 'beginner',
                    'published'   => true,
                    'sort_order'  => 9,
                ]
            );

            Course::firstOrCreate(
                ['slug' => 'flask-backend'],
                [
                    'language_id' => $python->id,
                    'title'       => 'Flask: Backend Ligero con Python',
                    'description' => 'Construye APIs y aplicaciones web con Flask: routing, Jinja2, SQLAlchemy, autenticación, REST APIs, blueprints, testing y deploy.',
                    'level'       => 'intermediate',
                    'published'   => true,
                    'sort_order'  => 10,
                ]
            );

            Course::firstOrCreate(
                ['slug' => 'django-fullstack'],
                [
                    'language_id' => $python->id,
                    'title'       => 'Django: Desarrollo Web Fullstack',
                    'description' => 'Domina Django: ORM, templates, vistas, formularios, autenticación, Django REST Framework, Celery, testing y deploy a producción.',
                    'level'       => 'intermediate',
                    'published'   => true,
                    'sort_order'  => 11,
                ]
            );
        }

        $elixir = Language::where('slug', 'elixir')->first();

        if ($elixir) {
            Course::firstOrCreate(
                ['slug' => 'elixir-funcional'],
                [
                    'language_id' => $elixir->id,
                    'title'       => 'Elixir: Programación Funcional y Concurrente',
                    'description' => 'Domina Elixir desde cero: programación funcional, pattern matching, procesos, OTP, GenServer, supervisión, Ecto, metaprogramación y deploy.',
                    'level'       => 'intermediate',
                    'published'   => true,
                    'sort_order'  => 12,
                ]
            );

            Course::firstOrCreate(
                ['slug' => 'phoenix-framework'],
                [
                    'language_id' => $elixir->id,
                    'title'       => 'Phoenix Framework: Web en Tiempo Real',
                    'description' => 'Construye aplicaciones web de alto rendimiento con Phoenix: LiveView, Channels, Ecto, Plugs, autenticación, APIs REST, Oban, testing y deploy.',
                    'level'       => 'intermediate',
                    'published'   => true,
                    'sort_order'  => 13,
                ]
            );
        }

        $excel = Language::where('slug', 'excel')->first();

        if ($excel) {
            Course::firstOrCreate(
                ['slug' => 'excel-principiante'],
                [
                    'language_id' => $excel->id,
                    'title'       => 'Excel Principiante: Desde Cero',
                    'description' => 'Aprende Excel desde cero: navegación, celdas, formato, fórmulas básicas, gráficos y las funciones esenciales para ser productivo.',
                    'level'       => 'beginner',
                    'published'   => true,
                    'sort_order'  => 15,
                ]
            );

            Course::firstOrCreate(
                ['slug' => 'excel-intermedio'],
                [
                    'language_id' => $excel->id,
                    'title'       => 'Excel Intermedio: Fórmulas y Análisis',
                    'description' => 'Domina fórmulas avanzadas, tablas dinámicas, validación de datos, formato condicional, funciones lógicas y de búsqueda.',
                    'level'       => 'intermediate',
                    'published'   => true,
                    'sort_order'  => 16,
                ]
            );

            Course::firstOrCreate(
                ['slug' => 'excel-avanzado'],
                [
                    'language_id' => $excel->id,
                    'title'       => 'Excel Avanzado: Power User',
                    'description' => 'Técnicas expertas: Power Query, Power Pivot, DAX, macros VBA, dashboards profesionales, solver y automatización avanzada.',
                    'level'       => 'advanced',
                    'published'   => true,
                    'sort_order'  => 17,
                ]
            );
        }

        if ($python) {
            Course::firstOrCreate(
                ['slug' => 'ia-llms'],
                [
                    'language_id' => $python->id,
                    'title'       => 'Inteligencia Artificial y LLMs',
                    'description' => 'Domina IA desde los fundamentos: ML, deep learning, transformers, LLMs (GPT-4o, Claude, Gemini, Llama), prompt engineering, RAG, agentes, fine-tuning, ética y deploy.',
                    'level'       => 'intermediate',
                    'published'   => true,
                    'sort_order'  => 14,
                ]
            );
        }
    }
}
