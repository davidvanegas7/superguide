<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class ReactQuizSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'react-frontend')->first();

        if (! $course) {
            $this->command->warn('React course not found. Run CourseSeeder first.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id],
            [
                'title'       => 'Evaluación: React Frontend con TypeScript',
                'description' => 'Pon a prueba tus conocimientos sobre React, hooks, estado, routing, testing y patrones avanzados.',
                'published'   => true,
            ]
        );

        // Borra preguntas previas para hacer el seeder idempotente
        $quiz->questions()->each(fn ($q) => $q->options()->delete());
        $quiz->questions()->delete();

        foreach ($this->questions() as $i => $q) {
            $question = QuizQuestion::create([
                'quiz_id'     => $quiz->id,
                'question'    => $q['question'],
                'explanation' => $q['explanation'],
                'sort_order'  => $i + 1,
            ]);

            foreach ($q['options'] as $j => $opt) {
                QuizOption::create([
                    'quiz_question_id' => $question->id,
                    'text'             => $opt['text'],
                    'is_correct'       => $opt['correct'],
                    'sort_order'       => $j + 1,
                ]);
            }
        }

        $this->command->info("React quiz seeded: {$quiz->questions()->count()} preguntas.");
    }

    private function questions(): array
    {
        return [

            // ── Lección 1: Introducción a React ───────────────────────────
            [
                'question'    => '¿Cuál de las siguientes afirmaciones describe mejor a React?',
                'explanation' => 'React es una biblioteca de JavaScript para construir interfaces de usuario, no un framework completo como Angular. Solo se encarga de la capa de vista (UI), delegando routing, estado global y fetching a librerías del ecosistema.',
                'options'     => [
                    ['text' => 'Es una biblioteca de JavaScript enfocada en la capa de vista (UI), no un framework completo', 'correct' => true],
                    ['text' => 'Es un framework MVC completo que incluye routing, estado global y HTTP client', 'correct' => false],
                    ['text' => 'Es un lenguaje de programación diseñado para crear interfaces web', 'correct' => false],
                    ['text' => 'Es una herramienta de build como Webpack o Vite', 'correct' => false],
                ],
            ],

            // ── Lección 2: JSX y Rendering ────────────────────────────────
            [
                'question'    => '¿Qué sucede cuando se usa un índice de array como key en una lista que puede reordenarse?',
                'explanation' => 'Cuando la lista se reordena, el índice cambia para cada elemento, pero React cree que el componente con key=0 sigue siendo el mismo. Esto causa que React reutilice el estado interno del componente anterior (ej: el valor de un input), produciendo bugs visuales. Las keys deben ser estables y únicas (como un ID de base de datos).',
                'options'     => [
                    ['text' => 'React reutiliza incorrectamente el estado de componentes anteriores al reordenar, causando bugs visuales', 'correct' => true],
                    ['text' => 'No hay ningún problema; el índice es la key recomendada por React', 'correct' => false],
                    ['text' => 'React lanza un error en consola y no renderiza la lista', 'correct' => false],
                    ['text' => 'La lista se renderiza dos veces, una por cada key duplicada', 'correct' => false],
                ],
            ],

            // ── Lección 3: Componentes y Props ────────────────────────────
            [
                'question'    => '¿Por qué las props en React son de solo lectura (inmutables)?',
                'explanation' => 'React se basa en un flujo de datos unidireccional: los datos fluyen de padre a hijo via props. Si un hijo pudiera mutar las props, el padre no sabría que sus datos cambiaron, rompiendo la predictibilidad del rendering. Para cambiar datos, el hijo debe llamar a un callback del padre (elevación de estado).',
                'options'     => [
                    ['text' => 'Para garantizar el flujo unidireccional de datos y que el padre mantenga el control sobre sus datos', 'correct' => true],
                    ['text' => 'Porque JavaScript no permite mutar objetos pasados como parámetros', 'correct' => false],
                    ['text' => 'Para reducir el uso de memoria al compartir la misma referencia', 'correct' => false],
                    ['text' => 'Es una limitación de TypeScript, no de React', 'correct' => false],
                ],
            ],

            // ── Lección 4: Estado y Eventos ───────────────────────────────
            [
                'question'    => '¿Qué problema causa mutar el estado directamente con `state.push(item)` en vez de usar `setState([...state, item])`?',
                'explanation' => 'React detecta cambios por referencia (===). Si mutamos el array original, la referencia no cambia, y React no sabe que debe re-renderizar. Crear un nuevo array con spread ([...state, item]) genera una referencia nueva que React detecta como un cambio de estado.',
                'options'     => [
                    ['text' => 'La referencia del array no cambia, React no detecta el cambio y no re-renderiza el componente', 'correct' => true],
                    ['text' => 'JavaScript lanza un TypeError porque los arrays de state son frozen', 'correct' => false],
                    ['text' => 'El componente se re-renderiza pero con un delay de 1 segundo', 'correct' => false],
                    ['text' => 'React actualiza el DOM pero los tests fallan porque Jest detecta la mutación', 'correct' => false],
                ],
            ],

            // ── Lección 5: Hooks Fundamentales ────────────────────────────
            [
                'question'    => '¿Qué sucede si omites una dependencia en el array de useEffect?',
                'explanation' => 'Si omites una dependencia, el efecto captura el valor que tenía esa variable en el render en que se creó la closure (stale closure). El efecto no se re-ejecuta cuando esa variable cambia, trabajando con un valor obsoleto. Esto causa bugs donde el efecto parece "ignorar" actualizaciones de estado.',
                'options'     => [
                    ['text' => 'El efecto usa un valor obsoleto de la variable omitida (stale closure) porque no se re-ejecuta cuando esa dependencia cambia', 'correct' => true],
                    ['text' => 'React lanza un error en runtime y el efecto no se ejecuta', 'correct' => false],
                    ['text' => 'El efecto se ejecuta en cada render porque React ignora el array de deps incompleto', 'correct' => false],
                    ['text' => 'No pasa nada; React detecta automáticamente las dependencias que faltan', 'correct' => false],
                ],
            ],

            // ── Lección 6: Hooks Avanzados ────────────────────────────────
            [
                'question'    => '¿Cuándo es preferible usar useReducer en lugar de useState?',
                'explanation' => 'useReducer es preferible cuando el estado tiene múltiples transiciones complejas relacionadas entre sí. El reducer centraliza toda la lógica de actualización en una función pura, haciendo las transiciones explícitas y más fáciles de debuggear y testear. useState es ideal para estado simple (un booleano, un string, un número).',
                'options'     => [
                    ['text' => 'Cuando el estado tiene múltiples transiciones complejas que se benefician de estar centralizadas en un reducer puro y testeable', 'correct' => true],
                    ['text' => 'Siempre que el estado sea un objeto, independientemente de su complejidad', 'correct' => false],
                    ['text' => 'Solo cuando se usa Context API, porque useReducer es un requisito de los providers', 'correct' => false],
                    ['text' => 'useReducer es siempre mejor que useState; useState existe solo por compatibilidad', 'correct' => false],
                ],
            ],

            // ── Lección 7: Formularios ────────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia principal entre un componente controlado y uno no controlado en formularios?',
                'explanation' => 'En un componente controlado, React mantiene el valor del input en su estado (value={state} + onChange). En un no controlado, el DOM mantiene el valor y se accede con useRef. Los controlados dan más control (validación en cada keystroke, formateo), pero los no controlados son útiles para integración con código que no usa React.',
                'options'     => [
                    ['text' => 'En un controlado, React gestiona el valor vía state + onChange; en un no controlado, el DOM lo gestiona y se accede con useRef', 'correct' => true],
                    ['text' => 'Un componente controlado valida automáticamente; uno no controlado permite cualquier valor', 'correct' => false],
                    ['text' => 'Los controlados son solo para TypeScript; los no controlados son para JavaScript', 'correct' => false],
                    ['text' => 'No hay diferencia funcional; es solo una convención de nomenclatura', 'correct' => false],
                ],
            ],

            // ── Lección 8: React Router ───────────────────────────────────
            [
                'question'    => '¿Qué componente de React Router se usa para renderizar rutas hijas dentro de un layout padre?',
                'explanation' => '<Outlet /> actúa como un "slot" donde React Router renderiza el componente hijo que coincide con la ruta anidada. Se coloca dentro del layout padre. Cuando navegas a /dashboard/settings, el layout de Dashboard se mantiene y Settings se renderiza donde está el <Outlet />.',
                'options'     => [
                    ['text' => '<Outlet /> — renderiza la ruta hija dentro del layout padre, similar a un slot', 'correct' => true],
                    ['text' => '<Route /> — las rutas hijas se renderizan automáticamente sin componente especial', 'correct' => false],
                    ['text' => '<Children /> — componente de React Router para inyectar rutas anidadas', 'correct' => false],
                    ['text' => '<RouterView /> — componente heredado de la versión anterior de React Router', 'correct' => false],
                ],
            ],

            // ── Lección 9: Fetching de datos ──────────────────────────────
            [
                'question'    => '¿Qué problema resuelve TanStack Query que un useEffect con fetch no resuelve de forma nativa?',
                'explanation' => 'Un useEffect con fetch no maneja: caché automática (evitar refetch innecesarios), deduplicación de requests simultáneos, invalidación inteligente, refetch en background, paginación infinita, ni optimistic updates. TanStack Query resuelve todo esto con una API declarativa, reduciendo drásticamente el boilerplate.',
                'options'     => [
                    ['text' => 'Caché automática, deduplicación de requests, invalidación, refetch en background y retry con backoff', 'correct' => true],
                    ['text' => 'TanStack Query usa WebSockets en vez de HTTP, lo que es más rápido que fetch', 'correct' => false],
                    ['text' => 'useEffect no puede hacer peticiones HTTP; siempre se necesita una librería externa', 'correct' => false],
                    ['text' => 'TanStack Query convierte las respuestas a TypeScript automáticamente sin necesidad de tipar', 'correct' => false],
                ],
            ],

            // ── Lección 10: Context API ───────────────────────────────────
            [
                'question'    => '¿Cuál es el principal problema de rendimiento de Context API y cómo se mitiga?',
                'explanation' => 'Cuando el valor del Context cambia, TODOS los componentes que consumen ese Context se re-renderizan, aunque solo usen una parte del valor que no cambió. Se mitiga: 1) Separando en múltiples Contexts (ThemeContext, AuthContext), 2) Usando React.memo en los consumidores, 3) Memoizando el value del Provider con useMemo.',
                'options'     => [
                    ['text' => 'Todos los consumidores se re-renderizan cuando cualquier parte del valor cambia; se mitiga dividiendo en múltiples Contexts y memoizando valores', 'correct' => true],
                    ['text' => 'Context no funciona con componentes funcionales; solo con componentes de clase', 'correct' => false],
                    ['text' => 'Context API bloquea el hilo principal durante las actualizaciones de estado', 'correct' => false],
                    ['text' => 'El Provider recrea el DOM completo de sus hijos en cada actualización', 'correct' => false],
                ],
            ],

            // ── Lección 11: Redux Toolkit ─────────────────────────────────
            [
                'question'    => '¿Por qué Redux Toolkit permite "mutar" el estado dentro de los reducers de createSlice?',
                'explanation' => 'Redux Toolkit usa Immer internamente. Immer crea un proxy del estado: las mutaciones que escribes (state.count++) se interceptan y se convierten automáticamente en actualizaciones inmutables. El estado original nunca se muta realmente. Esto reduce boilerplate pero mantiene la inmutabilidad que Redux requiere.',
                'options'     => [
                    ['text' => 'Usa Immer internamente: las mutaciones se interceptan con un proxy y se convierten en actualizaciones inmutables', 'correct' => true],
                    ['text' => 'Redux Toolkit abandonó la inmutabilidad porque causaba problemas de rendimiento', 'correct' => false],
                    ['text' => 'Solo funciona en modo desarrollo; en producción las mutaciones se bloquean', 'correct' => false],
                    ['text' => 'Usa Object.freeze() recursivo para prevenir mutaciones accidentales del estado', 'correct' => false],
                ],
            ],

            // ── Lección 12: Testing ───────────────────────────────────────
            [
                'question'    => '¿Cuál es la filosofía principal de React Testing Library y cómo difiere de Enzyme?',
                'explanation' => '"Mientras más tests se parezcan a cómo el usuario usa la app, más confianza dan." RTL testea comportamiento (lo que el usuario ve y hace), no implementación. Enzyme permitía testear estado interno y métodos del componente, generando tests frágiles que se rompían con refactors internos aunque el comportamiento fuera el mismo.',
                'options'     => [
                    ['text' => 'RTL testea el comportamiento visible al usuario (queries por role, texto) en vez de la implementación interna (estado, métodos)', 'correct' => true],
                    ['text' => 'RTL renderiza componentes en un browser real; Enzyme usa un DOM simulado', 'correct' => false],
                    ['text' => 'RTL solo testea componentes funcionales; Enzyme soportaba componentes de clase', 'correct' => false],
                    ['text' => 'No hay diferencia funcional; RTL es simplemente la versión más reciente de Enzyme', 'correct' => false],
                ],
            ],

            // ── Lección 13: Performance ───────────────────────────────────
            [
                'question'    => '¿Cuándo React.memo puede EMPEORAR el rendimiento en vez de mejorarlo?',
                'explanation' => 'React.memo tiene un costo: compara todas las props (shallow comparison) en cada render. Si el componente siempre recibe props diferentes (nuevos objetos/arrays/funciones creados en el padre), la comparación se ejecuta pero siempre falla, y el componente se re-renderiza de todos modos. Es overhead neto sin beneficio.',
                'options'     => [
                    ['text' => 'Cuando las props siempre son diferentes (nuevos objetos/funciones), la comparación shallow es overhead sin beneficio', 'correct' => true],
                    ['text' => 'React.memo nunca empeora el rendimiento; siempre es una optimización segura', 'correct' => false],
                    ['text' => 'Cuando el componente tiene children, porque React.memo no puede comparar ReactNode', 'correct' => false],
                    ['text' => 'En modo producción, porque React.memo solo se aplica en desarrollo', 'correct' => false],
                ],
            ],

            // ── Lección 14: Custom Hooks ──────────────────────────────────
            [
                'question'    => '¿Cuál es la convención obligatoria para que React reconozca una función como un hook personalizado?',
                'explanation' => 'El nombre debe empezar con "use" seguido de una letra mayúscula (ej: useAuth, useFetch). Esta convención no es solo estética: el linter de React (eslint-plugin-react-hooks) la usa para aplicar las reglas de hooks (no llamar condicionalmente, etc.). Sin el prefijo "use", el linter no aplicaría las reglas.',
                'options'     => [
                    ['text' => 'El nombre debe empezar con "use" (ej: useAuth), lo que permite al linter aplicar las reglas de hooks automáticamente', 'correct' => true],
                    ['text' => 'Debe usar la directiva "use hook" al inicio del archivo, similar a "use client"', 'correct' => false],
                    ['text' => 'Debe registrarse con React.registerHook() antes de usarse en un componente', 'correct' => false],
                    ['text' => 'Debe exportarse desde un archivo .hook.ts para que React lo reconozca', 'correct' => false],
                ],
            ],

            // ── Lección 15: TypeScript con React ──────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre usar `interface` y `type` para las props de un componente React?',
                'explanation' => 'Funcionalmente son casi equivalentes. La diferencia clave es que interface soporta declaration merging (puede extenderse en múltiples declaraciones) y type soporta unions y tipos más complejos. La convención más extendida es usar interface para props (extensibles) y type para unions y tipos complejos. Ambas funcionan con React.',
                'options'     => [
                    ['text' => 'Son casi equivalentes; interface soporta declaration merging y type soporta unions. La convención común es interface para props', 'correct' => true],
                    ['text' => 'Solo interface funciona con React; type causa errores de tipado en JSX', 'correct' => false],
                    ['text' => 'type es más rápido que interface porque TypeScript lo resuelve en una sola pasada', 'correct' => false],
                    ['text' => 'interface genera código JavaScript; type es solo para compilación', 'correct' => false],
                ],
            ],

            // ── Lección 16: Estilos ───────────────────────────────────────
            [
                'question'    => '¿Por qué Tailwind CSS tiene mejor rendimiento que Styled Components en producción?',
                'explanation' => 'Tailwind genera CSS estático en build time y purga las clases no usadas, resultando en un CSS mínimo sin JavaScript runtime. Styled Components inyecta estilos dinámicamente en runtime: parsea template literals, genera classnames únicos y los inserta en el DOM con cada render. Ese overhead de runtime no existe con Tailwind.',
                'options'     => [
                    ['text' => 'Tailwind genera CSS estático en build time sin runtime JS; Styled Components parsea y genera estilos dinámicamente en cada render', 'correct' => true],
                    ['text' => 'Tailwind usa CSS nativo que el navegador optimiza; Styled Components usa JavaScript para estilar', 'correct' => false],
                    ['text' => 'No hay diferencia de rendimiento; Tailwind simplemente genera menos líneas de CSS', 'correct' => false],
                    ['text' => 'Styled Components carga toda la librería (50KB) aunque solo uses un componente', 'correct' => false],
                ],
            ],

            // ── Lección 17: SSR / Next.js ─────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia fundamental entre un Server Component y un Client Component en Next.js?',
                'explanation' => 'Un Server Component se ejecuta SOLO en el servidor: su código JavaScript nunca se envía al cliente, puede acceder a la BD directamente, pero no puede usar hooks ni event handlers. Un Client Component (marcado con "use client") se envía al navegador y puede usar useState, useEffect, onClick, etc. La clave es que por defecto todo es Server Component.',
                'options'     => [
                    ['text' => 'El Server Component se ejecuta solo en el servidor y su JS no se envía al cliente; el Client Component ("use client") se hidrata en el navegador con interactividad', 'correct' => true],
                    ['text' => 'El Server Component usa Node.js y el Client Component usa Deno como runtime', 'correct' => false],
                    ['text' => 'No hay diferencia funcional; "use client" solo indica que el componente necesita TypeScript', 'correct' => false],
                    ['text' => 'Los Server Components son más lentos porque esperan al servidor; los Client Components renderizan instantáneamente', 'correct' => false],
                ],
            ],

            // ── Lección 18: Patrones avanzados ────────────────────────────
            [
                'question'    => '¿Qué problema resuelve el patrón Compound Components que la composición simple con props no resuelve?',
                'explanation' => 'Compound Components permite que un grupo de componentes relacionados compartan estado implícitamente vía Context, sin exponer props explícitas. El consumidor tiene control total sobre la estructura y el orden de los hijos. Es como <select> y <option> en HTML: trabajan juntos sin pasarse props manualmente.',
                'options'     => [
                    ['text' => 'Permite que componentes hermanos compartan estado implícito vía Context, dando al consumidor control total sobre la estructura sin props explícitas', 'correct' => true],
                    ['text' => 'Es más rápido que las props normales porque usa refs internamente para evitar re-renders', 'correct' => false],
                    ['text' => 'Permite que los componentes se comuniquen sin un padre común, como un event bus', 'correct' => false],
                    ['text' => 'Solo sirve para componentes de formulario; no funciona con otros tipos de UI', 'correct' => false],
                ],
            ],

            // ── Lección 19: Preguntas de entrevista ───────────────────────
            [
                'question'    => '¿Qué es la reconciliación (reconciliation) en React y cuál es su complejidad algorítmica?',
                'explanation' => 'La reconciliación es el proceso de comparar el Virtual DOM anterior con el nuevo para determinar los cambios mínimos al DOM real. React usa un algoritmo de diffing O(n) basado en dos heurísticas: 1) Elementos de diferente tipo producen subárboles diferentes, 2) Las keys identifican elementos estables entre renders. Sin estas heurísticas, el diff sería O(n³).',
                'options'     => [
                    ['text' => 'Compara Virtual DOM anterior con el nuevo mediante diffing O(n), usando heurísticas de tipo de elemento y keys para eficiencia', 'correct' => true],
                    ['text' => 'Reemplaza todo el DOM real en cada render con O(1) gracias al batching automático', 'correct' => false],
                    ['text' => 'Usa un algoritmo O(n log n) similar a merge sort para ordenar los cambios por prioridad', 'correct' => false],
                    ['text' => 'Compara cada nodo del DOM real con el Virtual DOM usando O(n²) con optimizaciones de caché', 'correct' => false],
                ],
            ],

            // ── Pregunta general: React 18+ ───────────────────────────────
            [
                'question'    => '¿Qué mejora introduce useTransition en React 18 para la experiencia de usuario?',
                'explanation' => 'useTransition permite marcar actualizaciones de estado como "no urgentes" (transitions). React puede interrumpir estas actualizaciones para priorizar las urgentes (como escribir en un input). La UI se mantiene responsive porque las actualizaciones costosas (filtrar una lista grande) se procesan sin bloquear las interacciones del usuario.',
                'options'     => [
                    ['text' => 'Marca actualizaciones como no urgentes, permitiendo que React las interrumpa para priorizar interacciones del usuario y mantener la UI responsive', 'correct' => true],
                    ['text' => 'Permite animar transiciones CSS entre renders de React, reemplazando librerías como Framer Motion', 'correct' => false],
                    ['text' => 'Mueve las actualizaciones a un Web Worker para no bloquear el hilo principal', 'correct' => false],
                    ['text' => 'Agrupa múltiples setState en una sola actualización para reducir renders, algo que no existía antes de React 18', 'correct' => false],
                ],
            ],

        ];
    }
}
