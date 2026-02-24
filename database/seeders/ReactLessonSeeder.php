<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ReactLessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'react-frontend')->first();

        if (! $course) {
            $this->command->warn('React course not found. Run CourseSeeder first.');
            return;
        }

        $tagWeb          = Tag::where('slug', 'web')->first();
        $tagPrincipiante = Tag::where('slug', 'principiante')->first();
        $tagIntermedio   = Tag::where('slug', 'intermedio')->first();
        $tagAvanzado     = Tag::where('slug', 'avanzado')->first();
        $tagFunciones    = Tag::where('slug', 'funciones')->first();

        $lessons = [
            [
                'slug'             => 'introduccion-react',
                'title'            => 'Introducción a React y configuración del entorno',
                'md_file_path'     => 'content/lessons/react/01-introduccion-react.md',
                'excerpt'          => 'Qué es React, su arquitectura basada en componentes y cómo iniciar un proyecto con Vite.',
                'published'        => true,
                'sort_order'       => 1,
                'duration_minutes' => 20,
            ],
            [
                'slug'             => 'jsx-rendering',
                'title'            => 'JSX y Rendering',
                'md_file_path'     => 'content/lessons/react/02-jsx-rendering.md',
                'excerpt'          => 'Sintaxis JSX, expresiones, renderizado condicional, listas y el Virtual DOM.',
                'published'        => true,
                'sort_order'       => 2,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'componentes-props',
                'title'            => 'Componentes y Props',
                'md_file_path'     => 'content/lessons/react/03-componentes-props.md',
                'excerpt'          => 'Componentes funcionales, props, children, composición y organización de archivos.',
                'published'        => true,
                'sort_order'       => 3,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'estado-eventos',
                'title'            => 'Estado y Eventos',
                'md_file_path'     => 'content/lessons/react/04-estado-eventos.md',
                'excerpt'          => 'useState, inmutabilidad, eventos sintéticos y elevación de estado.',
                'published'        => true,
                'sort_order'       => 4,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'hooks-fundamentales',
                'title'            => 'Hooks Fundamentales: useState y useEffect',
                'md_file_path'     => 'content/lessons/react/05-hooks-fundamentales.md',
                'excerpt'          => 'Reglas de hooks, useEffect, array de dependencias, cleanup y errores comunes.',
                'published'        => true,
                'sort_order'       => 5,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'hooks-avanzados',
                'title'            => 'Hooks Avanzados: useReducer, useContext, useRef, useMemo, useCallback',
                'md_file_path'     => 'content/lessons/react/06-hooks-avanzados.md',
                'excerpt'          => 'useReducer para estado complejo, useContext, useRef, memoización con useMemo y useCallback.',
                'published'        => true,
                'sort_order'       => 6,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'formularios-react',
                'title'            => 'Formularios en React',
                'md_file_path'     => 'content/lessons/react/07-formularios.md',
                'excerpt'          => 'Componentes controlados y no controlados, validación y React Hook Form con Zod.',
                'published'        => true,
                'sort_order'       => 7,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'react-router',
                'title'            => 'React Router: navegación SPA',
                'md_file_path'     => 'content/lessons/react/08-react-router.md',
                'excerpt'          => 'BrowserRouter, rutas dinámicas, anidadas, guards, lazy loading y loaders.',
                'published'        => true,
                'sort_order'       => 8,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'fetching-datos',
                'title'            => 'Fetching de datos y TanStack Query',
                'md_file_path'     => 'content/lessons/react/09-fetching-datos.md',
                'excerpt'          => 'Fetch con useEffect, custom hook useFetch, TanStack Query y Suspense.',
                'published'        => true,
                'sort_order'       => 9,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'context-api',
                'title'            => 'Context API y estado global',
                'md_file_path'     => 'content/lessons/react/10-context-api.md',
                'excerpt'          => 'Prop drilling, createContext, Provider, consumer hooks y Context + useReducer.',
                'published'        => true,
                'sort_order'       => 10,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'redux-toolkit',
                'title'            => 'Redux Toolkit: estado global predecible',
                'md_file_path'     => 'content/lessons/react/11-redux-toolkit.md',
                'excerpt'          => 'createSlice, configureStore, typed hooks, createAsyncThunk y RTK Query.',
                'published'        => true,
                'sort_order'       => 11,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'testing-react',
                'title'            => 'Testing con Vitest y React Testing Library',
                'md_file_path'     => 'content/lessons/react/12-testing.md',
                'excerpt'          => 'Tests unitarios y de componentes con Vitest, RTL, user events y MSW.',
                'published'        => true,
                'sort_order'       => 12,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'performance-react',
                'title'            => 'Rendimiento y Optimización',
                'md_file_path'     => 'content/lessons/react/13-performance.md',
                'excerpt'          => 'React.memo, useMemo, useCallback, lazy loading, virtualización y Profiler.',
                'published'        => true,
                'sort_order'       => 13,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'custom-hooks',
                'title'            => 'Custom Hooks: reutilización de lógica',
                'md_file_path'     => 'content/lessons/react/14-custom-hooks.md',
                'excerpt'          => 'Diseño de hooks personalizados: useLocalStorage, useFetch, useDebounce y más.',
                'published'        => true,
                'sort_order'       => 14,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'typescript-react',
                'title'            => 'TypeScript con React',
                'md_file_path'     => 'content/lessons/react/15-typescript-react.md',
                'excerpt'          => 'Tipado de props, eventos, hooks, componentes genéricos y forwardRef.',
                'published'        => true,
                'sort_order'       => 15,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'estilos-react',
                'title'            => 'Estilos: CSS Modules, Tailwind y Styled Components',
                'md_file_path'     => 'content/lessons/react/16-estilos.md',
                'excerpt'          => 'CSS Modules, Tailwind CSS, Styled Components y comparativa de enfoques.',
                'published'        => true,
                'sort_order'       => 16,
                'duration_minutes' => 25,
            ],
            [
                'slug'             => 'ssr-nextjs',
                'title'            => 'SSR con Next.js',
                'md_file_path'     => 'content/lessons/react/17-ssr-nextjs.md',
                'excerpt'          => 'Server Components, App Router, data fetching, layouts, Server Actions y SEO.',
                'published'        => true,
                'sort_order'       => 17,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'patrones-avanzados-react',
                'title'            => 'Patrones avanzados de React',
                'md_file_path'     => 'content/lessons/react/18-patrones-avanzados.md',
                'excerpt'          => 'Compound components, render props, HOC, provider pattern y state machines.',
                'published'        => true,
                'sort_order'       => 18,
                'duration_minutes' => 30,
            ],
            [
                'slug'             => 'preguntas-entrevista-react',
                'title'            => 'Preguntas de entrevista: React',
                'md_file_path'     => 'content/lessons/react/19-preguntas-entrevista.md',
                'excerpt'          => 'Las preguntas más frecuentes sobre React, hooks, estado, routing y optimización.',
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

            // Tags según el tipo de lección
            $sort = $data['sort_order'];
            if ($tagWeb)          $lesson->tags()->syncWithoutDetaching([$tagWeb->id]);
            if ($tagPrincipiante && $sort <= 4) {
                $lesson->tags()->syncWithoutDetaching([$tagPrincipiante->id]);
            }
            if ($tagIntermedio && $sort >= 5 && $sort <= 12) {
                $lesson->tags()->syncWithoutDetaching([$tagIntermedio->id]);
            }
            if ($tagAvanzado && $sort >= 13) {
                $lesson->tags()->syncWithoutDetaching([$tagAvanzado->id]);
            }
            if ($tagFunciones && $sort >= 5 && $sort <= 7) {
                $lesson->tags()->syncWithoutDetaching([$tagFunciones->id]);
            }
        }
    }
}
