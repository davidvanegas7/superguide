<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class ElixirQuizSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'elixir-funcional')->first();

        if (! $course) {
            $this->command->warn('Elixir course not found. Run CourseSeeder first.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id],
            [
                'title'       => 'Evaluación: Elixir Funcional',
                'description' => 'Pon a prueba tus conocimientos sobre Elixir: pattern matching, procesos, OTP, GenServer, supervisión y más.',
                'published'   => true,
            ]
        );

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

        $this->command->info("Elixir quiz seeded: {$quiz->questions()->count()} preguntas.");
    }

    private function questions(): array
    {
        return [

            // ── 1 · Introducción a Elixir ─────────────────────────────
            [
                'question'    => '¿Sobre qué máquina virtual se ejecuta Elixir y cuál es su principal ventaja?',
                'explanation' => 'Elixir se ejecuta sobre la BEAM (Erlang VM), diseñada para sistemas concurrentes, distribuidos y tolerantes a fallos. La BEAM usa procesos livianos (no hilos del SO), permitiendo millones de procesos simultáneos con recolección de basura por proceso.',
                'options'     => [
                    ['text' => 'BEAM (Erlang VM): permite millones de procesos livianos con tolerancia a fallos y baja latencia', 'correct' => true],
                    ['text' => 'JVM (Java Virtual Machine): permite interoperabilidad con Java', 'correct' => false],
                    ['text' => 'V8: la misma de Node.js para máximo rendimiento JavaScript', 'correct' => false],
                    ['text' => 'CLR (.NET): para compatibilidad con C# y F#', 'correct' => false],
                ],
            ],

            // ── 2 · Tipos de datos ────────────────────────────────────
            [
                'question'    => '¿Qué son los átomos en Elixir y cuándo se usan?',
                'explanation' => 'Los átomos son constantes cuyo nombre es su valor (como :ok, :error, true, false). Se almacenan en una tabla global y se comparan por identidad (muy rápido). Se usan para estados, claves de mapas, y tagged tuples como {:ok, result} o {:error, reason}.',
                'options'     => [
                    ['text' => 'Constantes cuyo nombre es su valor; se usan como tags en tuplas {:ok, val}, claves y estados', 'correct' => true],
                    ['text' => 'Variables inmutables similares a const en JavaScript', 'correct' => false],
                    ['text' => 'Tipos numéricos de precisión atómica para cálculos científicos', 'correct' => false],
                    ['text' => 'Strings especiales que no ocupan memoria en el heap', 'correct' => false],
                ],
            ],

            // ── 3 · Colecciones ───────────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia fundamental entre listas y tuplas en Elixir?',
                'explanation' => 'Las listas son linked lists: eficientes para prepend (O(1)) y recorrido secuencial, pero O(n) para acceso por índice. Las tuplas se almacenan contiguas en memoria: O(1) para acceso por índice pero costoso modificarlas (se copia toda la tupla). Se usan tuplas para datos fijos, listas para colecciones.',
                'options'     => [
                    ['text' => 'Listas son linked lists (prepend O(1)); tuplas son contiguas en memoria (acceso O(1) por índice)', 'correct' => true],
                    ['text' => 'Las listas son mutables y las tuplas inmutables', 'correct' => false],
                    ['text' => 'Las tuplas solo aceptan átomos, las listas cualquier tipo', 'correct' => false],
                    ['text' => 'No hay diferencia; tupla es un alias de lista', 'correct' => false],
                ],
            ],

            // ── 4 · Pattern matching ──────────────────────────────────
            [
                'question'    => '¿Qué hace el operador pin `^` en pattern matching?',
                'explanation' => 'En Elixir, el = es match, no asignación. Por defecto, una variable en el lado izquierdo se rebindea. El pin ^ fuerza a usar el valor existente de la variable en vez de rebindearla. Ejemplo: ^x = 5 falla si x no es 5, en vez de reasignar x.',
                'options'     => [
                    ['text' => 'Fuerza usar el valor actual de la variable en el match, en vez de rebindearla', 'correct' => true],
                    ['text' => 'Convierte la variable en una constante inmutable', 'correct' => false],
                    ['text' => 'Indica que la variable es un puntero a memoria', 'correct' => false],
                    ['text' => 'Eleva la variable al scope global del módulo', 'correct' => false],
                ],
            ],

            // ── 5 · Funciones ─────────────────────────────────────────
            [
                'question'    => '¿Qué hace el pipe operator `|>` y por qué es tan importante en Elixir?',
                'explanation' => 'El pipe operator pasa el resultado de la expresión izquierda como primer argumento de la función derecha. Transforma código anidado como c(b(a(x))) en x |> a() |> b() |> c(), que se lee de izquierda a derecha. Es fundamental para el estilo funcional de transformación de datos.',
                'options'     => [
                    ['text' => 'Pasa el resultado como primer argumento de la siguiente función, creando pipelines legibles de izquierda a derecha', 'correct' => true],
                    ['text' => 'Ejecuta funciones en paralelo y combina sus resultados', 'correct' => false],
                    ['text' => 'Es equivalente al operador OR lógico (||)', 'correct' => false],
                    ['text' => 'Crea un proceso separado para cada función en la cadena', 'correct' => false],
                ],
            ],

            // ── 6 · Módulos y Structs ─────────────────────────────────
            [
                'question'    => '¿Qué diferencia hay entre un map y un struct en Elixir?',
                'explanation' => 'Un struct es un map con un conjunto fijo de campos definidos en un módulo con defstruct. Tiene un campo especial __struct__ que indica su tipo. A diferencia de un map libre, un struct no acepta campos no definidos y permite implementar protocolos específicos para ese tipo.',
                'options'     => [
                    ['text' => 'Un struct es un map con campos fijos definidos en un módulo; no acepta campos arbitrarios y tiene tipo (__struct__)', 'correct' => true],
                    ['text' => 'Un struct es mutable mientras que un map es inmutable', 'correct' => false],
                    ['text' => 'Son exactamente iguales; struct es solo syntactic sugar', 'correct' => false],
                    ['text' => 'Los structs se almacenan en la base de datos, los maps solo en memoria', 'correct' => false],
                ],
            ],

            // ── 7 · Control de flujo ──────────────────────────────────
            [
                'question'    => '¿Cuál es la ventaja de usar `with` sobre múltiples `case` anidados?',
                'explanation' => 'with encadena múltiples pattern matches y cortocircuita al primer fallo, evitando el "staircase" de cases anidados. La cláusula else captura los fallos. Es ideal para validaciones secuenciales donde cada paso depende del anterior (como parsear datos de una API).',
                'options'     => [
                    ['text' => 'Encadena pattern matches secuenciales, cortocircuita al primer fallo, y evita cases anidados', 'correct' => true],
                    ['text' => 'with ejecuta todas las expresiones en paralelo para mayor velocidad', 'correct' => false],
                    ['text' => 'Es solo una forma alternativa de escribir if/else', 'correct' => false],
                    ['text' => 'with solo funciona con átomos, no con otros tipos', 'correct' => false],
                ],
            ],

            // ── 8 · Recursión y Enumerables ───────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre Enum y Stream en Elixir?',
                'explanation' => 'Enum es eager: ejecuta cada operación sobre toda la colección inmediatamente, creando listas intermedias. Stream es lazy: compone las operaciones sin ejecutarlas hasta que se consume (con Enum.to_list, Enum.take, etc.). Stream es mejor para datos grandes o infinitos.',
                'options'     => [
                    ['text' => 'Enum es eager (evalúa inmediatamente); Stream es lazy (compone operaciones sin ejecutar hasta consumir)', 'correct' => true],
                    ['text' => 'Enum trabaja con listas y Stream con maps', 'correct' => false],
                    ['text' => 'Stream es más rápido que Enum en todos los casos', 'correct' => false],
                    ['text' => 'Son módulos idénticos con nombres diferentes', 'correct' => false],
                ],
            ],

            // ── 9 · Procesos y Concurrencia ───────────────────────────
            [
                'question'    => '¿Por qué los procesos de la BEAM son diferentes de los hilos del sistema operativo?',
                'explanation' => 'Los procesos BEAM son extremadamente livianos (~2KB iniciales), gestionados por la VM (no el SO), con recolección de basura individual. Se pueden crear millones sin problema. Los hilos del SO pesan ~1MB, son gestionados por el kernel, y miles de ellos pueden saturar un sistema.',
                'options'     => [
                    ['text' => 'Pesan ~2KB (vs ~1MB de hilos SO), tienen GC individual, y la BEAM puede manejar millones simultáneamente', 'correct' => true],
                    ['text' => 'Son más lentos pero más seguros que los hilos del SO', 'correct' => false],
                    ['text' => 'Son idénticos a los hilos del SO; solo cambia la API', 'correct' => false],
                    ['text' => 'Solo pueden ejecutarse en un solo core de CPU', 'correct' => false],
                ],
            ],

            // ── 10 · GenServer ────────────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre `handle_call` y `handle_cast` en un GenServer?',
                'explanation' => 'handle_call es síncrono: el proceso que llama espera una respuesta ({:reply, resp, state}). handle_cast es asíncrono: envía un mensaje sin esperar respuesta ({:noreply, state}). Se usa call para operaciones que necesitan resultado, cast para fire-and-forget como logging.',
                'options'     => [
                    ['text' => 'call es síncrono (espera respuesta); cast es asíncrono (fire-and-forget, no espera)', 'correct' => true],
                    ['text' => 'call es para lectura y cast es para escritura del estado', 'correct' => false],
                    ['text' => 'Son sinónimos; la diferencia es solo convención de naming', 'correct' => false],
                    ['text' => 'call crea un proceso nuevo, cast reutiliza el existente', 'correct' => false],
                ],
            ],

            // ── 11 · Supervisión y OTP ────────────────────────────────
            [
                'question'    => '¿Qué diferencia hay entre las estrategias `:one_for_one` y `:one_for_all` de un Supervisor?',
                'explanation' => ':one_for_one reinicia solo el proceso que falló, dejando los demás intactos. :one_for_all reinicia TODOS los hijos cuando uno falla. Se usa one_for_all cuando los procesos son interdependientes y su estado debe ser consistente. También existe :rest_for_one que reinicia los hijos posteriores al fallido.',
                'options'     => [
                    ['text' => 'one_for_one reinicia solo el proceso caído; one_for_all reinicia todos los hijos del supervisor', 'correct' => true],
                    ['text' => 'one_for_one es para desarrollo y one_for_all para producción', 'correct' => false],
                    ['text' => 'one_for_all permite un solo proceso hijo; one_for_one permite muchos', 'correct' => false],
                    ['text' => 'No hay diferencia práctica; es solo preferencia del desarrollador', 'correct' => false],
                ],
            ],

            // ── 12 · Mix y Proyectos ──────────────────────────────────
            [
                'question'    => '¿Qué papel juega el archivo `mix.exs` en un proyecto Elixir?',
                'explanation' => 'mix.exs es el corazón del proyecto: define nombre, versión, dependencias (deps), la aplicación OTP (application), configuración de compilador, alias de tareas, y environments. Es equivalente a package.json (Node) + Cargo.toml (Rust). Mix lo usa para compilar, testear y gestionar deps.',
                'options'     => [
                    ['text' => 'Define el proyecto completo: nombre, versión, deps, aplicación OTP, configuración y alias de tareas', 'correct' => true],
                    ['text' => 'Solo lista las dependencias externas del proyecto', 'correct' => false],
                    ['text' => 'Es el archivo de configuración de la base de datos', 'correct' => false],
                    ['text' => 'Es un script de shell para compilar el proyecto', 'correct' => false],
                ],
            ],

            // ── 13 · Testing con ExUnit ───────────────────────────────
            [
                'question'    => '¿Qué son los doctests en Elixir y por qué son tan valiosos?',
                'explanation' => 'Los doctests son ejemplos ejecutables escritos en la documentación (@doc) de una función. ExUnit los extrae y ejecuta como tests automáticamente. Esto garantiza que la documentación siempre esté sincronizada con el código — si el ejemplo falla, el test falla.',
                'options'     => [
                    ['text' => 'Ejemplos en @doc que ExUnit ejecuta como tests, garantizando que la documentación siempre sea correcta', 'correct' => true],
                    ['text' => 'Tests especiales solo para documentar el código, no se ejecutan realmente', 'correct' => false],
                    ['text' => 'Son tests generados automáticamente por el compilador', 'correct' => false],
                    ['text' => 'Son archivos Markdown con ejemplos que se publican en HexDocs', 'correct' => false],
                ],
            ],

            // ── 14 · Ecto ─────────────────────────────────────────────
            [
                'question'    => '¿Qué son los changesets en Ecto y por qué se prefieren sobre validaciones en el modelo?',
                'explanation' => 'Los changesets son estructuras que rastrean cambios, aplican castings (conversión de tipos) y validaciones de forma explícita y composable. A diferencia de validaciones en el modelo (como en ActiveRecord), los changesets son datos, no callbacks. Puedes hacer diferentes changesets para diferentes contextos (registro vs actualización).',
                'options'     => [
                    ['text' => 'Estructuras explícitas que rastrean cambios con castings y validaciones; son datos composables, no callbacks implícitos', 'correct' => true],
                    ['text' => 'Son equivalentes a las migraciones de base de datos', 'correct' => false],
                    ['text' => 'Son los objetos que representan una conexión a la base de datos', 'correct' => false],
                    ['text' => 'Son hooks que se ejecutan automáticamente antes de guardar', 'correct' => false],
                ],
            ],

            // ── 15 · Metaprogramación ─────────────────────────────────
            [
                'question'    => '¿Qué hace `quote` en la metaprogramación de Elixir?',
                'explanation' => 'quote convierte código Elixir en su representación AST (Abstract Syntax Tree) como tuplas de 3 elementos {función, metadata, argumentos}. Esto permite inspeccionar y manipular código en tiempo de compilación. unquote inyecta valores calculados dentro de un bloque quote.',
                'options'     => [
                    ['text' => 'Convierte código Elixir en su AST (tuplas de 3 elementos) para manipulación en compile-time', 'correct' => true],
                    ['text' => 'Crea strings a partir de expresiones de código', 'correct' => false],
                    ['text' => 'Comenta bloques de código para documentación', 'correct' => false],
                    ['text' => 'Ejecuta código dentro de un sandbox seguro', 'correct' => false],
                ],
            ],

            // ── 16 · Protocolos y Behaviours ──────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre un Protocolo y un Behaviour en Elixir?',
                'explanation' => 'Los Protocolos son polimorfismo dispatch-by-data: definen funciones que se implementan de forma diferente según el tipo del primer argumento (como interfaces externas). Los Behaviours son contratos de módulo: definen callbacks que un módulo debe implementar (como interfaces internas). Protocolos para datos polimórficos, Behaviours para módulos intercambiables.',
                'options'     => [
                    ['text' => 'Protocolos: polimorfismo por tipo de dato (dispatch externo); Behaviours: contratos de callbacks para módulos (interfaz interna)', 'correct' => true],
                    ['text' => 'Son sinónimos; Behaviour es el nombre antiguo de Protocol', 'correct' => false],
                    ['text' => 'Protocolos son para concurrencia, Behaviours para herencia', 'correct' => false],
                    ['text' => 'Behaviours corren en compile-time, Protocolos en runtime', 'correct' => false],
                ],
            ],

            // ── 17 · Manejo de errores ────────────────────────────────
            [
                'question'    => '¿Por qué Elixir prefiere tagged tuples `{:ok, val}` / `{:error, reason}` sobre excepciones?',
                'explanation' => 'Las tagged tuples hacen explícito que una operación puede fallar, forzando al llamador a manejar ambos casos con pattern matching. Las excepciones son para situaciones excepcionales (bugs, no errores de negocio). Este estilo "Let it crash" delega la recuperación a los supervisores, simplificando el código.',
                'options'     => [
                    ['text' => 'Hacen explícitos los errores esperados; pattern matching fuerza su manejo; excepciones se reservan para bugs reales', 'correct' => true],
                    ['text' => 'Porque Elixir no soporta excepciones (try/rescue no existe)', 'correct' => false],
                    ['text' => 'Las tuplas son más rápidas que las excepciones en todos los casos', 'correct' => false],
                    ['text' => 'Es solo una convención de estilo sin ventaja técnica', 'correct' => false],
                ],
            ],

            // ── 18 · Deploy y Producción ──────────────────────────────
            [
                'question'    => '¿Qué es un release en Elixir y por qué se prefiere sobre `mix` en producción?',
                'explanation' => 'Un release es un paquete autocontenido con la aplicación compilada, el runtime ERTS y config. No necesita Elixir/Erlang instalados en el servidor. mix requiere el compilador y es para desarrollo. Los releases arrancan más rápido, usan menos memoria y soportan hot upgrades.',
                'options'     => [
                    ['text' => 'Paquete autocontenido con ERTS incluido; no necesita Elixir instalado, arranca rápido y soporta hot upgrades', 'correct' => true],
                    ['text' => 'Es una versión publicada en Hex.pm para uso público', 'correct' => false],
                    ['text' => 'Es un branch de Git marcado como estable', 'correct' => false],
                    ['text' => 'No hay diferencia; mix y release son equivalentes en producción', 'correct' => false],
                ],
            ],

            // ── 19 · Preguntas de Entrevista ──────────────────────────
            [
                'question'    => '¿Qué es la filosofía "Let it crash" de Erlang/Elixir y cómo se implementa?',
                'explanation' => 'En vez de programación defensiva (try/catch en todo), se permite que procesos con estado inválido crasheen. Los supervisores los reinician automáticamente en un estado limpio. Esto simplifica el código (no hay manejo de error dentro del proceso) y mejora la resiliencia ya que el sistema se auto-repara.',
                'options'     => [
                    ['text' => 'Dejar que procesos con estado inválido crasheen y que los supervisores los reinicien en estado limpio automáticamente', 'correct' => true],
                    ['text' => 'No manejar ningún error y dejar que la aplicación se caiga completamente', 'correct' => false],
                    ['text' => 'Usar try/rescue en cada función para capturar todos los errores posibles', 'correct' => false],
                    ['text' => 'Registrar todos los errores en logs sin tomar ninguna acción', 'correct' => false],
                ],
            ],

            // ── 20 · Pregunta integradora ─────────────────────────────
            [
                'question'    => '¿Por qué Elixir es especialmente adecuado para aplicaciones en tiempo real y alta concurrencia?',
                'explanation' => 'La BEAM fue diseñada para telecomunicaciones (alta disponibilidad 99.9999%). Combina procesos livianos (millones), modelo de actores (sin estado compartido), preemptive scheduling (sin starvation), supervisores (auto-healing), distribución nativa (clusters), y hot code loading. Todo esto sin locks ni race conditions.',
                'options'     => [
                    ['text' => 'Procesos livianos, modelo de actores sin estado compartido, preemptive scheduling, supervisores y distribución nativa en clusters', 'correct' => true],
                    ['text' => 'Porque compila a código máquino nativo más rápido que C', 'correct' => false],
                    ['text' => 'Porque usa un solo hilo como Node.js pero con mejor event loop', 'correct' => false],
                    ['text' => 'Solo es adecuado para aplicaciones pequeñas, no para alta concurrencia', 'correct' => false],
                ],
            ],
        ];
    }
}
