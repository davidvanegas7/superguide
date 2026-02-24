<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NodeJSLessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'nodejs-backend-typescript')->first();

        if (! $course) {
            $this->command->warn('NodeJS course not found. Run CourseSeeder first.');
            return;
        }

        $tagBackend    = Tag::where('slug', 'backend')->first();
        $tagApi        = Tag::where('slug', 'api')->first();
        $tagAvanzado   = Tag::where('slug', 'avanzado')->first();
        $tagPrincipiante = Tag::where('slug', 'principiante')->first();

        $lessons = [
            [
                'slug'             => 'introduccion-nodejs',
                'title'            => 'Introducción a Node.js y entorno de desarrollo',
                'md_file_path'     => 'content/lessons/nodejs/01-introduccion-nodejs.md',
                'excerpt'          => 'Qué es Node.js, el Event Loop y cómo configurar el entorno con TypeScript.',
                'published'        => true,
                'sort_order'       => 1,
                'duration_minutes' => 20,
            ],
            [
                'slug'             => 'typescript-para-backend',
                'title'            => 'TypeScript para Backend: tipos, interfaces y generics',
                'md_file_path'     => 'content/lessons/nodejs/02-typescript-para-backend.md',
                'excerpt'          => 'Tipos avanzados, interfaces, generics y decoradores aplicados al backend.',
                'published'        => true,
                'sort_order'       => 2,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'modulos-y-filesystem',
                'title'            => 'Sistema de módulos: CommonJS, ESM y filesystem',
                'md_file_path'     => 'content/lessons/nodejs/03-modulos-y-filesystem.md',
                'excerpt'          => 'CommonJS vs ESM, rutas con path y lectura/escritura de archivos con fs/promises.',
                'published'        => true,
                'sort_order'       => 3,
                'duration_minutes' => 20,
            ],
            [
                'slug'             => 'express-typescript',
                'title'            => 'Express con TypeScript: rutas y controladores',
                'md_file_path'     => 'content/lessons/nodejs/04-express-typescript.md',
                'excerpt'          => 'Configura Express con TypeScript, tipado de Request/Response y estructura de proyecto.',
                'published'        => true,
                'sort_order'       => 4,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'middlewares-express',
                'title'            => 'Middlewares en Express',
                'md_file_path'     => 'content/lessons/nodejs/05-middlewares-express.md',
                'excerpt'          => 'Middlewares globales, de ruta y de error. Autenticación, logging y CORS.',
                'published'        => true,
                'sort_order'       => 5,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'diseno-rest-api',
                'title'            => 'Diseño de REST API',
                'md_file_path'     => 'content/lessons/nodejs/06-diseno-rest-api.md',
                'excerpt'          => 'Principios REST, versionado, respuestas consistentes y manejo de errores HTTP.',
                'published'        => true,
                'sort_order'       => 6,
                'duration_minutes' => 20,
            ],
            [
                'slug'             => 'validacion-zod',
                'title'            => 'Validación de datos con Zod',
                'md_file_path'     => 'content/lessons/nodejs/07-validacion-zod.md',
                'excerpt'          => 'Esquemas de validación runtime con Zod: parse, safeParse y transformaciones.',
                'published'        => true,
                'sort_order'       => 7,
                'duration_minutes' => 20,
            ],
            [
                'slug'             => 'variables-entorno-config',
                'title'            => 'Variables de entorno y configuración',
                'md_file_path'     => 'content/lessons/nodejs/08-variables-entorno-config.md',
                'excerpt'          => 'Gestión segura de secrets con dotenv, validación con Zod y config centralizada.',
                'published'        => true,
                'sort_order'       => 8,
                'duration_minutes' => 15,
            ],
            [
                'slug'             => 'prisma-orm-crud',
                'title'            => 'Prisma ORM: CRUD y migraciones',
                'md_file_path'     => 'content/lessons/nodejs/09-prisma-orm-crud.md',
                'excerpt'          => 'Modela tu base de datos con Prisma Schema, migraciones y operaciones CRUD.',
                'published'        => true,
                'sort_order'       => 9,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'prisma-relaciones-avanzado',
                'title'            => 'Prisma: relaciones y consultas avanzadas',
                'md_file_path'     => 'content/lessons/nodejs/10-prisma-relaciones-avanzado.md',
                'excerpt'          => 'Relaciones 1:N y M:N con Prisma, includes, select, aggregations y transacciones.',
                'published'        => true,
                'sort_order'       => 10,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'autenticacion-jwt',
                'title'            => 'Autenticación: bcrypt, JWT y refresh tokens',
                'md_file_path'     => 'content/lessons/nodejs/11-autenticacion-jwt.md',
                'excerpt'          => 'Hashing con bcrypt, firma de JWT, refresh tokens y flujo completo de auth.',
                'published'        => true,
                'sort_order'       => 11,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'autorizacion-roles-permisos',
                'title'            => 'Autorización, roles y permisos',
                'md_file_path'     => 'content/lessons/nodejs/12-autorizacion-roles-permisos.md',
                'excerpt'          => 'RBAC con guards de Express, jerarquía de roles y control de acceso a recursos.',
                'published'        => true,
                'sort_order'       => 12,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'errores-logging-pino',
                'title'            => 'Manejo de errores y logging con Pino',
                'md_file_path'     => 'content/lessons/nodejs/13-errores-logging-pino.md',
                'excerpt'          => 'Jerarquía de errores custom, manejador global y logging estructurado con Pino.',
                'published'        => true,
                'sort_order'       => 13,
                'duration_minutes' => 20,
            ],
            [
                'slug'             => 'testing-jest-supertest',
                'title'            => 'Testing con Jest y Supertest',
                'md_file_path'     => 'content/lessons/nodejs/14-testing-jest-supertest.md',
                'excerpt'          => 'Tests unitarios con Jest, tests de integración con Supertest y mocking.',
                'published'        => true,
                'sort_order'       => 14,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'subida-archivos-multer',
                'title'            => 'Subida de archivos con Multer',
                'md_file_path'     => 'content/lessons/nodejs/15-subida-archivos-multer.md',
                'excerpt'          => 'Gestiona uploads con Multer: filtrado de tipos, límites de tamaño y almacenamiento.',
                'published'        => true,
                'sort_order'       => 15,
                'duration_minutes' => 20,
            ],
            [
                'slug'             => 'websockets-socketio',
                'title'            => 'WebSockets en tiempo real con Socket.io',
                'md_file_path'     => 'content/lessons/nodejs/16-websockets-socketio.md',
                'excerpt'          => 'Comunicación bidireccional con Socket.io: events, rooms y broadcast.',
                'published'        => true,
                'sort_order'       => 16,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'tareas-programadas-cron-bull',
                'title'            => 'Tareas programadas con Cron y Bull',
                'md_file_path'     => 'content/lessons/nodejs/17-tareas-programadas-cron-bull.md',
                'excerpt'          => 'Automatiza procesos con node-cron y gestiona colas de trabajo con BullMQ.',
                'published'        => true,
                'sort_order'       => 17,
                'duration_minutes' => 20,
            ],
            [
                'slug'             => 'performance-redis-cache',
                'title'            => 'Performance y caché con Redis',
                'md_file_path'     => 'content/lessons/nodejs/18-performance-redis-cache.md',
                'excerpt'          => 'Optimiza tu API con Redis: cache-aside, TTL, rate limiting e invalidación.',
                'published'        => true,
                'sort_order'       => 18,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'docker-deploy-produccion',
                'title'            => 'Docker y deploy a producción',
                'md_file_path'     => 'content/lessons/nodejs/19-docker-deploy-produccion.md',
                'excerpt'          => 'Conteneriza tu API con Docker, multi-stage builds y despliega con Docker Compose.',
                'published'        => true,
                'sort_order'       => 19,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'preguntas-entrevista-nodejs',
                'title'            => 'Preguntas de entrevista: Node.js Backend',
                'md_file_path'     => 'content/lessons/nodejs/20-preguntas-entrevista.md',
                'excerpt'          => 'Las preguntas más frecuentes sobre Node.js, Event Loop, JWT y arquitectura REST.',
                'published'        => true,
                'sort_order'       => 20,
                'duration_minutes' => 25,
            ],
        ];

        foreach ($lessons as $data) {
            $lesson = Lesson::firstOrCreate(
                ['course_id' => $course->id, 'slug' => $data['slug']],
                $data + ['course_id' => $course->id]
            );

            // Tags según el tipo de lección
            $sort = $data['sort_order'];
            if ($tagBackend)      $lesson->tags()->syncWithoutDetaching([$tagBackend->id]);
            if ($tagApi && $sort >= 4 && $sort <= 13) {
                $lesson->tags()->syncWithoutDetaching([$tagApi->id]);
            }
            if ($tagAvanzado && $sort >= 9) {
                $lesson->tags()->syncWithoutDetaching([$tagAvanzado->id]);
            }
            if ($tagPrincipiante && $sort <= 3) {
                $lesson->tags()->syncWithoutDetaching([$tagPrincipiante->id]);
            }
        }
    }
}
