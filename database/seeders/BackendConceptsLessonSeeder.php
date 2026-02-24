<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class BackendConceptsLessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'backend-conceptos')->first();

        if (!$course) {
            $this->command->warn('Curso backend-conceptos no encontrado. Ejecuta CourseSeeder primero.');
            return;
        }

        $lessons = [
            [
                'sort_order'    => 1,
                'title'         => 'POO: Fundamentos',
                'slug'          => 'poo-fundamentos',
                'excerpt'       => 'Clases, objetos, constructores, modificadores de acceso, interfaces y clases abstractas con TypeScript.',
                'md_file_path'  => 'content/lessons/backend-concepts/01-poo-fundamentos.md',
                'tags'          => ['backend'],
            ],
            [
                'sort_order'    => 2,
                'title'         => 'POO: Los 4 Pilares',
                'slug'          => 'poo-pilares',
                'excerpt'       => 'Encapsulamiento, herencia, polimorfismo y abstracción con ejemplos prácticos en TypeScript.',
                'md_file_path'  => 'content/lessons/backend-concepts/02-poo-pilares.md',
                'tags'          => ['backend'],
            ],
            [
                'sort_order'    => 3,
                'title'         => 'SOLID: Principios de Diseño',
                'slug'          => 'solid-principios',
                'excerpt'       => 'Los 5 principios SOLID con ejemplos reales: SRP, OCP, LSP, ISP y DIP.',
                'md_file_path'  => 'content/lessons/backend-concepts/03-solid-principios.md',
                'tags'          => ['backend', 'avanzado'],
            ],
            [
                'sort_order'    => 4,
                'title'         => 'Patrones Creacionales',
                'slug'          => 'patrones-creacionales',
                'excerpt'       => 'Factory Method, Abstract Factory, Builder y Singleton con casos de uso reales.',
                'md_file_path'  => 'content/lessons/backend-concepts/04-patrones-creacionales.md',
                'tags'          => ['backend', 'avanzado'],
            ],
            [
                'sort_order'    => 5,
                'title'         => 'Patrones Estructurales',
                'slug'          => 'patrones-estructurales',
                'excerpt'       => 'Repository, Adapter, Decorator y Facade: cómo componer clases y objetos eficientemente.',
                'md_file_path'  => 'content/lessons/backend-concepts/05-patrones-estructurales.md',
                'tags'          => ['backend', 'avanzado'],
            ],
            [
                'sort_order'    => 6,
                'title'         => 'Patrones de Comportamiento',
                'slug'          => 'patrones-comportamiento',
                'excerpt'       => 'Strategy, Observer, Command y Chain of Responsibility con implementaciones en TypeScript.',
                'md_file_path'  => 'content/lessons/backend-concepts/06-patrones-comportamiento.md',
                'tags'          => ['backend', 'avanzado'],
            ],
            [
                'sort_order'    => 7,
                'title'         => 'Bases de Datos Relacionales',
                'slug'          => 'bases-datos-relacionales',
                'excerpt'       => 'Modelo relacional, normalización, claves foráneas, índices y restricciones en SQL.',
                'md_file_path'  => 'content/lessons/backend-concepts/07-bases-datos-relacionales.md',
                'tags'          => ['backend'],
            ],
            [
                'sort_order'    => 8,
                'title'         => 'SQL: Consultas y Optimización',
                'slug'          => 'sql-consultas',
                'excerpt'       => 'JOINs, subconsultas, CTEs, window functions y estrategias de optimización.',
                'md_file_path'  => 'content/lessons/backend-concepts/08-sql-consultas.md',
                'tags'          => ['backend'],
            ],
            [
                'sort_order'    => 9,
                'title'         => 'Transacciones y ACID',
                'slug'          => 'transacciones-acid',
                'excerpt'       => 'Propiedades ACID, niveles de aislamiento, bloqueos pesimistas y optimistas.',
                'md_file_path'  => 'content/lessons/backend-concepts/09-transacciones-acid.md',
                'tags'          => ['backend', 'avanzado'],
            ],
            [
                'sort_order'    => 10,
                'title'         => 'ORM: Conceptos y Patrones',
                'slug'          => 'orm-patrones',
                'excerpt'       => 'Active Record vs Data Mapper, problema N+1, lazy vs eager loading y ORMs en Node.js.',
                'md_file_path'  => 'content/lessons/backend-concepts/10-orm-patrones.md',
                'tags'          => ['backend'],
            ],
            [
                'sort_order'    => 11,
                'title'         => 'Arquitectura en Capas',
                'slug'          => 'arquitectura-capas',
                'excerpt'       => 'MVC, Clean Architecture y arquitectura hexagonal: cómo organizar el código backend.',
                'md_file_path'  => 'content/lessons/backend-concepts/11-arquitectura-capas.md',
                'tags'          => ['backend', 'avanzado'],
            ],
            [
                'sort_order'    => 12,
                'title'         => 'Inyección de Dependencias',
                'slug'          => 'inyeccion-dependencias',
                'excerpt'       => 'DI por constructor, contenedores IoC, inversión de control y cómo facilitar el testing.',
                'md_file_path'  => 'content/lessons/backend-concepts/12-inyeccion-dependencias.md',
                'tags'          => ['backend', 'avanzado'],
            ],
            [
                'sort_order'    => 13,
                'title'         => 'APIs REST Avanzado',
                'slug'          => 'api-rest-avanzado',
                'excerpt'       => 'Diseño de URIs, versionado, paginación por cursor, errores consistentes e idempotencia.',
                'md_file_path'  => 'content/lessons/backend-concepts/13-api-rest-avanzado.md',
                'tags'          => ['backend', 'api'],
            ],
            [
                'sort_order'    => 14,
                'title'         => 'Seguridad en Backend',
                'slug'          => 'seguridad-backend',
                'excerpt'       => 'OWASP Top 10, SQL injection, hashing de contraseñas, JWT, CORS y validación de entradas.',
                'md_file_path'  => 'content/lessons/backend-concepts/14-seguridad-backend.md',
                'tags'          => ['backend', 'avanzado'],
            ],
            [
                'sort_order'    => 15,
                'title'         => 'Caché: Estrategias y Patrones',
                'slug'          => 'cache-estrategias',
                'excerpt'       => 'Cache-aside, write-through, invalidación por tags, Redis y distributed locks.',
                'md_file_path'  => 'content/lessons/backend-concepts/15-cache-estrategias.md',
                'tags'          => ['backend', 'avanzado'],
            ],
            [
                'sort_order'    => 16,
                'title'         => 'Mensajería y Eventos',
                'slug'          => 'mensajeria-eventos',
                'excerpt'       => 'Event bus, message queues con BullMQ, Pub/Sub, Outbox Pattern y CQRS básico.',
                'md_file_path'  => 'content/lessons/backend-concepts/16-mensajeria-eventos.md',
                'tags'          => ['backend', 'avanzado'],
            ],
            [
                'sort_order'    => 17,
                'title'         => 'Testing en Backend',
                'slug'          => 'testing-backend',
                'excerpt'       => 'Pirámide de testing, unit tests con mocks, integration tests, supertest y TDD.',
                'md_file_path'  => 'content/lessons/backend-concepts/17-testing-estrategias.md',
                'tags'          => ['backend'],
            ],
            [
                'sort_order'    => 18,
                'title'         => 'Performance y Escalabilidad',
                'slug'          => 'performance-escalabilidad',
                'excerpt'       => 'Big O, N+1 queries, connection pooling, paginación por cursor y procesamiento paralelo.',
                'md_file_path'  => 'content/lessons/backend-concepts/18-performance-escalabilidad.md',
                'tags'          => ['backend', 'avanzado'],
            ],
            [
                'sort_order'    => 19,
                'title'         => 'Concurrencia y Race Conditions',
                'slug'          => 'concurrencia-race-conditions',
                'excerpt'       => 'Race conditions, locks pesimistas y optimistas, distributed locks y diseño idempotente.',
                'md_file_path'  => 'content/lessons/backend-concepts/19-concurrencia-race-conditions.md',
                'tags'          => ['backend', 'avanzado'],
            ],
            [
                'sort_order'    => 20,
                'title'         => 'Preguntas de Entrevista: Backend',
                'slug'          => 'preguntas-entrevista-backend',
                'excerpt'       => 'Las preguntas más frecuentes en entrevistas técnicas de backend: POO, BD, REST, seguridad y system design.',
                'md_file_path'  => 'content/lessons/backend-concepts/20-preguntas-entrevista-backend.md',
                'tags'          => ['backend'],
            ],
        ];

        foreach ($lessons as $data) {
            $lesson = Lesson::firstOrCreate(
                [
                    'course_id'  => $course->id,
                    'sort_order' => $data['sort_order'],
                ],
                [
                    'title'        => $data['title'],
                    'slug'         => $data['slug'],
                    'excerpt'      => $data['excerpt'],
                    'md_file_path' => $data['md_file_path'],
                    'published'    => true,
                ]
            );

            if (isset($data['tags'])) {
                $tagIds = Tag::whereIn('slug', $data['tags'])->pluck('id');
                $lesson->tags()->syncWithoutDetaching($tagIds);
            }
        }

        $this->command->info("Backend Concepts: {$course->lessons()->count()} lecciones cargadas.");
    }
}
