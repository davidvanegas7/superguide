<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class PhoenixQuizSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'phoenix-framework')->first();

        if (! $course) {
            $this->command->warn('Phoenix course not found. Run CourseSeeder first.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id],
            [
                'title'       => 'Evaluación: Phoenix Framework',
                'description' => 'Pon a prueba tus conocimientos sobre Phoenix: LiveView, Ecto, Channels, Plugs, testing, deploy y más.',
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

        $this->command->info("Phoenix quiz seeded: {$quiz->questions()->count()} preguntas.");
    }

    private function questions(): array
    {
        return [

            // ── 1 · Introducción a Phoenix ────────────────────────────
            [
                'question'    => '¿Cuál es la principal ventaja de Phoenix sobre frameworks web tradicionales como Django o Rails?',
                'explanation' => 'Phoenix hereda la concurrencia masiva de la BEAM. Puede manejar millones de conexiones simultáneas (WebSockets incluidos) con latencia predecible. Rails y Django usan hilos/procesos del SO, limitados a miles. Phoenix demostró 2 millones de conexiones WebSocket en un solo servidor.',
                'options'     => [
                    ['text' => 'Concurrencia masiva de la BEAM: millones de conexiones simultáneas con latencia predecible y fault tolerance', 'correct' => true],
                    ['text' => 'Tiene más paquetes disponibles que cualquier otro framework', 'correct' => false],
                    ['text' => 'Es más fácil de aprender porque usa JavaScript', 'correct' => false],
                    ['text' => 'Genera automáticamente el frontend completo sin código', 'correct' => false],
                ],
            ],

            // ── 2 · Rutas y Controllers ───────────────────────────────
            [
                'question'    => '¿Qué es un pipeline en el Router de Phoenix y para qué sirve?',
                'explanation' => 'Un pipeline es una secuencia nombrada de plugs que se aplican a un grupo de rutas. Por ejemplo, :browser incluye plugs para sesión, CSRF, y headers HTML; :api incluye accept JSON. Permiten aplicar middleware diferente según el tipo de request sin duplicar configuración.',
                'options'     => [
                    ['text' => 'Secuencia nombrada de plugs aplicada a un grupo de rutas; :browser para HTML con sesión/CSRF, :api para JSON', 'correct' => true],
                    ['text' => 'Un sistema de CI/CD integrado en Phoenix', 'correct' => false],
                    ['text' => 'Un mecanismo para conectar bases de datos en cadena', 'correct' => false],
                    ['text' => 'Es el equivalente al pipe operator |> pero para rutas', 'correct' => false],
                ],
            ],

            // ── 3 · Vistas y Templates ────────────────────────────────
            [
                'question'    => '¿Qué es HEEx y cómo se diferencia de EEx?',
                'explanation' => 'HEEx (HTML-aware EEx) es el motor de templates de Phoenix que entiende la estructura HTML. A diferencia de EEx (Embedded Elixir genérico), HEEx valida HTML en compile-time, detecta errores de sintaxis, escapa contenido automáticamente (anti-XSS), y soporta function components con slots.',
                'options'     => [
                    ['text' => 'HEEx entiende HTML: valida en compile-time, escapa automáticamente contra XSS, y soporta function components', 'correct' => true],
                    ['text' => 'HEEx es una versión más rápida de EEx sin ninguna funcionalidad extra', 'correct' => false],
                    ['text' => 'HEEx genera código JavaScript en lugar de HTML', 'correct' => false],
                    ['text' => 'Son idénticos; HEEx es solo el nombre nuevo de EEx', 'correct' => false],
                ],
            ],

            // ── 4 · Ecto y Modelos ────────────────────────────────────
            [
                'question'    => '¿Por qué Ecto separa Schema de Changeset en lugar de validar directamente en el modelo?',
                'explanation' => 'La separación permite diferentes validaciones para diferentes contextos: un changeset de registro pide password, uno de actualización de perfil no. Los changesets son datos (structs), no callbacks implícitos, lo que los hace testables, composables y explícitos. Siguen el principio funcional de transformar datos.',
                'options'     => [
                    ['text' => 'Permite diferentes validaciones por contexto (registro vs update); los changesets son datos composables y testables', 'correct' => true],
                    ['text' => 'Es una limitación de Elixir que no permite métodos en structs', 'correct' => false],
                    ['text' => 'Solo por convención; se pueden poner las validaciones donde sea', 'correct' => false],
                    ['text' => 'Schema define las tablas SQL; Changeset define los índices', 'correct' => false],
                ],
            ],

            // ── 5 · Formularios ───────────────────────────────────────
            [
                'question'    => '¿Cómo maneja Phoenix los formularios con changesets y por qué es ventajoso?',
                'explanation' => 'Los formularios se construyen a partir de changesets. Al crear, se usa un changeset vacío para el formulario. Al enviar, se valida el changeset: si es inválido, se re-renderiza el form con el changeset y sus errores (los campos mantienen sus valores). El error tracking es automático y type-safe.',
                'options'     => [
                    ['text' => 'Los forms se construyen desde changesets; errores y valores se preservan automáticamente al re-renderizar', 'correct' => true],
                    ['text' => 'Phoenix no tiene soporte nativo para formularios; se usa JavaScript puro', 'correct' => false],
                    ['text' => 'Los formularios se validan solo en el cliente, nunca en el servidor', 'correct' => false],
                    ['text' => 'Cada campo del formulario es un proceso Elixir separado', 'correct' => false],
                ],
            ],

            // ── 6 · LiveView Fundamentos ──────────────────────────────
            [
                'question'    => '¿Cómo funciona LiveView para crear interfaces reactivas sin JavaScript?',
                'explanation' => 'LiveView mantiene un proceso Elixir por conexión que renderiza HTML en el servidor. Al montar, envía HTML completo. Después, un WebSocket conecta al proceso. Los eventos del usuario se envían por WebSocket, el servidor re-renderiza y envía solo los diffs del HTML cambiado. El cliente aplica los patches.',
                'options'     => [
                    ['text' => 'Proceso server-side por conexión + WebSocket; el servidor envía diffs de HTML, el cliente aplica patches', 'correct' => true],
                    ['text' => 'Compila Elixir a JavaScript que se ejecuta en el navegador', 'correct' => false],
                    ['text' => 'Usa polling HTTP cada 100ms para detectar cambios', 'correct' => false],
                    ['text' => 'Genera una SPA React/Vue completa desde Elixir', 'correct' => false],
                ],
            ],

            // ── 7 · LiveView Avanzado ─────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre un LiveView y un LiveComponent?',
                'explanation' => 'Un LiveView es un proceso completo con su propio lifecycle (mount, handle_event, etc.) y WebSocket. Un LiveComponent vive DENTRO de un LiveView, compartiendo su proceso. Los LiveComponents encapsulan estado y eventos para componentes reutilizables, pero no tienen su propio socket ni proceso.',
                'options'     => [
                    ['text' => 'LiveView es un proceso con su propio WebSocket; LiveComponent vive dentro de un LiveView compartiendo su proceso', 'correct' => true],
                    ['text' => 'LiveComponent se ejecuta en el cliente; LiveView en el servidor', 'correct' => false],
                    ['text' => 'Son idénticos; LiveComponent es solo un alias', 'correct' => false],
                    ['text' => 'LiveView es para páginas, LiveComponent es para APIs', 'correct' => false],
                ],
            ],

            // ── 8 · Autenticación ─────────────────────────────────────
            [
                'question'    => '¿Qué genera `mix phx.gen.auth` y por qué es la forma recomendada de implementar auth?',
                'explanation' => 'Genera código de autenticación completo que vive en tu aplicación (no en una librería): migraciones, schemas, contexts, controllers, LiveViews, templates, y tests. A diferencia de Devise (Rails), el código es tuyo y transparente, fácil de personalizar sin pelear con una librería opaca.',
                'options'     => [
                    ['text' => 'Genera código de auth completo en tu app (no librería): schemas, contexts, views, tests; transparente y personalizable', 'correct' => true],
                    ['text' => 'Instala una librería externa como Devise que maneja todo automáticamente', 'correct' => false],
                    ['text' => 'Solo genera las migraciones de base de datos para usuarios', 'correct' => false],
                    ['text' => 'Configura OAuth con Google y GitHub automáticamente', 'correct' => false],
                ],
            ],

            // ── 9 · Channels ──────────────────────────────────────────
            [
                'question'    => '¿Cómo funcionan los Channels de Phoenix para comunicación en tiempo real?',
                'explanation' => 'Los Channels son abstracciones sobre WebSockets. Un usuario se conecta a un Socket, luego se une a topics (channels). Cada Channel es un proceso GenServer. Los mensajes se envían con push (server→client) y handle_in (client→server). broadcast envía a todos los suscriptores del topic.',
                'options'     => [
                    ['text' => 'Socket → join topic → cada Channel es un GenServer; push, handle_in, y broadcast para mensajería bidireccional', 'correct' => true],
                    ['text' => 'Son conexiones HTTP de larga duración (long polling) disfrazadas', 'correct' => false],
                    ['text' => 'Channels solo funcionan con LiveView, no de forma independiente', 'correct' => false],
                    ['text' => 'Requieren Redis como broker de mensajes obligatorio', 'correct' => false],
                ],
            ],

            // ── 10 · PubSub ──────────────────────────────────────────
            [
                'question'    => '¿Cómo funciona Phoenix.PubSub y por qué es clave para aplicaciones distribuidas?',
                'explanation' => 'Phoenix.PubSub permite publish/subscribe entre procesos. En un solo nodo usa ETS. En cluster, propaga mensajes entre nodos automáticamente (con pg2 o Redis adapter). Es la base de Channels y LiveView. subscribe/broadcast permiten comunicación desacoplada entre procesos sin conocerse.',
                'options'     => [
                    ['text' => 'Pub/sub entre procesos; en cluster propaga entre nodos automáticamente; base de Channels y LiveView', 'correct' => true],
                    ['text' => 'Es un wrapper de RabbitMQ que viene incluido en Phoenix', 'correct' => false],
                    ['text' => 'Solo funciona con un solo servidor, no en clusters', 'correct' => false],
                    ['text' => 'Es el sistema de logs de Phoenix, no de mensajería', 'correct' => false],
                ],
            ],

            // ── 11 · APIs REST ────────────────────────────────────────
            [
                'question'    => '¿Cómo se estructura una API JSON en Phoenix y qué diferencia hay con la parte web?',
                'explanation' => 'Se usa el pipeline :api (sin sesión ni CSRF) y controllers que renderizan JSON con render(conn, :index, data: items). Phoenix usa JSON views o Plug.JSON para serialización. A diferencia de la parte web, no hay templates HTML ni LiveView, solo transformación de datos a JSON.',
                'options'     => [
                    ['text' => 'Pipeline :api sin sesión/CSRF, controllers renderizan JSON, JSON views para serialización; sin templates HTML', 'correct' => true],
                    ['text' => 'Se necesita una librería externa como Absinthe para cualquier API', 'correct' => false],
                    ['text' => 'No hay diferencia; se usa el mismo pipeline para web y API', 'correct' => false],
                    ['text' => 'Phoenix no soporta APIs REST, solo GraphQL', 'correct' => false],
                ],
            ],

            // ── 12 · Plugs ───────────────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre un function plug y un module plug?',
                'explanation' => 'Un function plug es una función con firma (conn, opts) → conn. Un module plug es un módulo que implementa init/1 (compile-time) y call/2 (runtime). Los module plugs son más organizados para lógica compleja y permiten opciones de configuración. Ambos se componen en pipelines.',
                'options'     => [
                    ['text' => 'Function plug: función (conn, opts) → conn; Module plug: módulo con init/1 (compile-time) y call/2 (runtime)', 'correct' => true],
                    ['text' => 'Function plugs son más rápidos; module plugs son más lentos pero seguros', 'correct' => false],
                    ['text' => 'Function plugs solo funcionan en controllers; module plugs en routers', 'correct' => false],
                    ['text' => 'No hay diferencia; son dos nombres para el mismo concepto', 'correct' => false],
                ],
            ],

            // ── 13 · Contextos ────────────────────────────────────────
            [
                'question'    => '¿Qué son los Contextos en Phoenix y qué problema resuelven?',
                'explanation' => 'Los Contextos son módulos que agrupan funcionalidad relacionada (bounded contexts de DDD). Por ejemplo, Accounts maneja usuarios y auth, Catalog maneja productos. Evitan que controllers accedan directamente a Repo, creando una API interna limpia. mix phx.gen.context los genera automáticamente.',
                'options'     => [
                    ['text' => 'Módulos que agrupan funcionalidad por dominio (bounded contexts); crean APIs internas limpias entre capas', 'correct' => true],
                    ['text' => 'Son los equivalentes a los middlewares en Express.js', 'correct' => false],
                    ['text' => 'Son archivos de configuración para diferentes ambientes (dev/prod)', 'correct' => false],
                    ['text' => 'Son procesos GenServer que cachean datos en memoria', 'correct' => false],
                ],
            ],

            // ── 14 · Testing ──────────────────────────────────────────
            [
                'question'    => '¿Cómo funciona la Ecto SQL Sandbox para testing en Phoenix?',
                'explanation' => 'La SQL Sandbox envuelve cada test en una transacción que se revierte al terminar (rollback). Esto mantiene la BD limpia sin borrar datos manualmente, y es más rápido que truncar tablas. Con async: true, cada test usa su propia transacción aislada, permitiendo tests paralelos.',
                'options'     => [
                    ['text' => 'Envuelve cada test en una transacción con rollback automático; con async: true permite tests paralelos aislados', 'correct' => true],
                    ['text' => 'Crea una base de datos SQLite temporal para cada test', 'correct' => false],
                    ['text' => 'Mockea todas las queries sin tocar la base de datos real', 'correct' => false],
                    ['text' => 'Solo funciona con PostgreSQL, no con otros adaptadores', 'correct' => false],
                ],
            ],

            // ── 15 · GenServer en Phoenix ─────────────────────────────
            [
                'question'    => '¿Cuándo es apropiado usar un GenServer dentro de una aplicación Phoenix?',
                'explanation' => 'GenServer en Phoenix es ideal para: caché en memoria (ETS wraper), rate limiting, conexiones a servicios externos (pools), contadores/estadísticas en tiempo real, y state machines. Se inicia en el supervision tree de la app. No es apropiado para lógica de request/response simple.',
                'options'     => [
                    ['text' => 'Para caché en memoria, rate limiting, pools de conexiones, contadores real-time y estado persistente entre requests', 'correct' => true],
                    ['text' => 'Para reemplazar controllers y manejar todas las requests HTTP', 'correct' => false],
                    ['text' => 'Nunca; GenServer es de Erlang y no se debe usar en Phoenix', 'correct' => false],
                    ['text' => 'Solo para tareas de base de datos, no para otros estados', 'correct' => false],
                ],
            ],

            // ── 16 · Oban Jobs ────────────────────────────────────────
            [
                'question'    => '¿Qué ventaja ofrece Oban sobre soluciones como GenServer o Task para background jobs?',
                'explanation' => 'Oban persiste jobs en PostgreSQL, garantizando que no se pierdan si la app crashea. Ofrece: reintentos configurables, scheduling, prioridades, cron jobs, rate limiting, jobs únicos, y un dashboard. GenServer/Task pierden estado al crashear. Es el equivalente a Sidekiq/Celery en el ecosistema Elixir.',
                'options'     => [
                    ['text' => 'Persiste jobs en PostgreSQL (no se pierden al crash), con reintentos, scheduling, cron, rate limiting y dashboard', 'correct' => true],
                    ['text' => 'Es más rápido que GenServer porque usa código nativo en C', 'correct' => false],
                    ['text' => 'No tiene ventajas; GenServer es siempre superior para background jobs', 'correct' => false],
                    ['text' => 'Oban solo funciona con Redis, no con PostgreSQL', 'correct' => false],
                ],
            ],

            // ── 17 · Seguridad ────────────────────────────────────────
            [
                'question'    => '¿Qué protecciones de seguridad incluye Phoenix por defecto?',
                'explanation' => 'Phoenix incluye: protección CSRF (tokens en formularios via Plug.CSRFProtection), autoescaping en HEEx (anti-XSS), Content Security Policy headers, secure cookies (HttpOnly, SameSite), y HTTPS enforcement. También usa atoms con precaución para prevenir atom table exhaustion.',
                'options'     => [
                    ['text' => 'CSRF tokens, autoescaping HEEx anti-XSS, CSP headers, secure cookies (HttpOnly/SameSite) y HTTPS enforcement', 'correct' => true],
                    ['text' => 'Solo protección CSRF; las demás requieren librerías externas', 'correct' => false],
                    ['text' => 'Phoenix no incluye seguridad por defecto; todo se configura manualmente', 'correct' => false],
                    ['text' => 'Solo encriptación de passwords con bcrypt', 'correct' => false],
                ],
            ],

            // ── 18 · Deploy ───────────────────────────────────────────
            [
                'question'    => '¿Cuál es el proceso recomendado para deploy de una aplicación Phoenix?',
                'explanation' => 'Se usa mix release para crear un release autocontenido que incluye ERTS. Se compilan assets con mix assets.deploy y se recopila estáticos. Se puede desplegar con Docker, Fly.io (nativo Elixir), Gigalixir, o servidores con systemd. En cluster se necesita libcluster para conectar nodos.',
                'options'     => [
                    ['text' => 'mix release + assets compile + Docker/Fly.io/Gigalixir; libcluster para conectar nodos en cluster', 'correct' => true],
                    ['text' => 'Solo ejecutar mix phx.server en el puerto 80 del servidor', 'correct' => false],
                    ['text' => 'Phoenix se auto-despliega al hacer push a GitHub', 'correct' => false],
                    ['text' => 'Se necesita compilar a JavaScript y desplegar en Vercel', 'correct' => false],
                ],
            ],

            // ── 19 · Preguntas de Entrevista ──────────────────────────
            [
                'question'    => '¿Cuándo elegirías Phoenix+LiveView sobre una SPA con React/Vue y una API?',
                'explanation' => 'LiveView es ideal cuando: la app es mayormente CRUD con interactividad moderada, se quiere evitar mantener dos codebases (frontend+backend), se necesita tiempo real nativo, y el equipo conoce Elixir. Una SPA es mejor para: interacciones complejas offline-first, apps móviles con la misma API, o equipos con experiencia JS.',
                'options'     => [
                    ['text' => 'LiveView: CRUD interactivo, una sola codebase, real-time nativo. SPA: offline-first, apps móvil, interacción JS compleja', 'correct' => true],
                    ['text' => 'Siempre elegir LiveView; las SPAs son obsoletas', 'correct' => false],
                    ['text' => 'Nunca elegir LiveView; React/Vue siempre son mejores', 'correct' => false],
                    ['text' => 'LiveView solo funciona para blogs estáticos, no apps interactivas', 'correct' => false],
                ],
            ],

            // ── 20 · Pregunta integradora ─────────────────────────────
            [
                'question'    => '¿Cómo se beneficia Phoenix del modelo de actores de Erlang/OTP?',
                'explanation' => 'Cada conexión (HTTP, WebSocket, LiveView) es un proceso aislado. Si uno crashea, no afecta a los demás. Los supervisores reinician procesos fallidos. Channels/PubSub escalan naturalmente porque ya son procesos. Los GenServers manejan estado compartido sin locks. La distribución permite clusters sin cambiar código.',
                'options'     => [
                    ['text' => 'Cada conexión es un proceso aislado; supervisores auto-healing; Channels/PubSub escalan naturalmente; clusters sin cambiar código', 'correct' => true],
                    ['text' => 'Phoenix no usa el modelo de actores; usa hilos como Java', 'correct' => false],
                    ['text' => 'Solo beneficia al rendimiento, no a la arquitectura', 'correct' => false],
                    ['text' => 'El modelo de actores solo funciona en Erlang, no en Elixir', 'correct' => false],
                ],
            ],
        ];
    }
}
