<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class NodeJSQuizSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'nodejs-backend-typescript')->first();

        if (! $course) {
            $this->command->warn('NodeJS course not found. Run CourseSeeder first.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id],
            [
                'title'       => 'Evaluación: Node.js Backend con TypeScript',
                'description' => 'Pon a prueba tus conocimientos sobre Node.js, Express, Prisma, JWT, testing y deploy.',
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

        $this->command->info("NodeJS quiz seeded: {$quiz->questions()->count()} preguntas.");
    }

    private function questions(): array
    {
        return [

            // ── Lección 1: Introducción / Event Loop ──────────────────────
            [
                'question'    => '¿Cuál es el orden correcto de salida del siguiente código?\n\nconsole.log("A");\nsetTimeout(() => console.log("B"), 0);\nPromise.resolve().then(() => console.log("C"));\nconsole.log("D");',
                'explanation' => '"A" y "D" son síncronos y van al call stack primero. Las Promises son microtasks y se ejecutan antes que los macrotasks como setTimeout. Por eso "C" sale antes que "B". Orden: A → D → C → B.',
                'options'     => [
                    ['text' => 'A, D, C, B', 'correct' => true],
                    ['text' => 'A, B, C, D', 'correct' => false],
                    ['text' => 'A, D, B, C', 'correct' => false],
                    ['text' => 'A, C, D, B', 'correct' => false],
                ],
            ],

            // ── Lección 1: Event Loop fases ───────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre process.nextTick() y Promise.then() en el Event Loop?',
                'explanation' => 'Ambos son microtasks, pero process.nextTick() tiene mayor prioridad: se drena completamente antes de pasar a las Promises resueltas, y ambas antes de que el Event Loop pase a la siguiente fase (timers, poll, etc.).',
                'options'     => [
                    ['text' => 'process.nextTick() se ejecuta antes que Promise.then() porque drena su cola antes de procesar las Promises', 'correct' => true],
                    ['text' => 'Promise.then() se ejecuta antes porque las Promises tienen mayor prioridad en V8', 'correct' => false],
                    ['text' => 'No hay diferencia; ambos se ejecutan en el mismo tick', 'correct' => false],
                    ['text' => 'process.nextTick() se ejecuta en la fase "check" del Event Loop, después de Promise.then()', 'correct' => false],
                ],
            ],

            // ── Lección 2: TypeScript tipos ───────────────────────────────
            [
                'question'    => '¿Qué ventaja concreta tiene usar el tipo Result<T, E> en lugar de lanzar excepciones en un servicio de backend?',
                'explanation' => 'Result<T, E> convierte los errores en valores que el compilador puede verificar. La función que llama está obligada a manejar el caso de error (revisar result.success), evitando errores silenciosos en runtime.',
                'options'     => [
                    ['text' => 'El compilador obliga a manejar el caso de error, haciendo los flujos de fallo explícitos y verificables en tiempo de compilación', 'correct' => true],
                    ['text' => 'Es más rápido que throw porque evita crear un objeto Error con stack trace', 'correct' => false],
                    ['text' => 'Permite lanzar múltiples errores al mismo tiempo desde una sola función', 'correct' => false],
                    ['text' => 'TypeScript no permite usar tipos genéricos con Error, por lo que Result es la única opción', 'correct' => false],
                ],
            ],

            // ── Lección 2: TypeScript generics ────────────────────────────
            [
                'question'    => '¿Qué hace el tipo utilitario Omit<T, "id" | "createdAt"> en el contexto de DTOs de una API?',
                'explanation' => 'Omit elimina propiedades específicas de un tipo. En DTOs de creación, el cliente no debe enviar "id" ni "createdAt" porque los genera el servidor. Usar Omit evita duplicar la interfaz manualmente.',
                'options'     => [
                    ['text' => 'Crea un nuevo tipo igual a T pero sin las propiedades "id" y "createdAt", útil para tipado de cuerpos de petición donde esos campos los genera el servidor', 'correct' => true],
                    ['text' => 'Hace que las propiedades "id" y "createdAt" sean opcionales en T', 'correct' => false],
                    ['text' => 'Elimina esas propiedades del objeto en runtime, no solo en el tipo', 'correct' => false],
                    ['text' => 'Convierte esas propiedades en readonly para que no puedan modificarse', 'correct' => false],
                ],
            ],

            // ── Lección 3: Módulos ────────────────────────────────────────
            [
                'question'    => '¿Por qué el código con require() puede causar un bug sutil al reusar objetos entre módulos?',
                'explanation' => 'CommonJS cachea los módulos: el primer require() ejecuta el módulo y guarda el resultado. Los siguientes require() del mismo archivo retornan la misma referencia cacheada. Mutar el objeto compartido afecta a todos los módulos que lo importaron.',
                'options'     => [
                    ['text' => 'require() cachea los módulos, por lo que todos los importadores comparten la misma referencia del objeto exportado y mutar uno afecta a todos', 'correct' => true],
                    ['text' => 'require() ejecuta el módulo una vez por importador, creando copias independientes', 'correct' => false],
                    ['text' => 'require() es síncrono, lo que bloquea el Event Loop y causa condiciones de carrera', 'correct' => false],
                    ['text' => 'require() no soporta objetos exportados, solo funciones y primitivos', 'correct' => false],
                ],
            ],

            // ── Lección 4: Express ────────────────────────────────────────
            [
                'question'    => '¿Cuál es la forma correcta de tipar el body de una petición POST en Express con TypeScript?',
                'explanation' => 'Express tipifica Request con genéricos: Request<Params, ResBody, ReqBody, Query>. El tercer genérico es el tipo del body. Esto da autocompletado en el handler, aunque la validación real en runtime debe hacerse con Zod u otro validador.',
                'options'     => [
                    ['text' => 'Request<{}, {}, CreateUserDto> — el tercer genérico de Request es el tipo del body', 'correct' => true],
                    ['text' => 'req.body as CreateUserDto — el cast de TypeScript valida el tipo en runtime', 'correct' => false],
                    ['text' => 'Express no soporta tipado de body; hay que usar any y validar manualmente', 'correct' => false],
                    ['text' => 'Response<CreateUserDto> — el tipo va en Response, no en Request', 'correct' => false],
                ],
            ],

            // ── Lección 5: Middlewares ────────────────────────────────────
            [
                'question'    => '¿Qué distingue a un middleware de manejo de errores de Express de uno normal?',
                'explanation' => 'Express identifica un middleware de error por su aridad: recibe 4 parámetros (err, req, res, next) en vez de 3. Debe registrarse al final de la cadena, después de todas las rutas. Si tiene 3 params, Express lo trata como middleware normal aunque se llame "errorHandler".',
                'options'     => [
                    ['text' => 'Recibe 4 parámetros: (err, req, res, next), a diferencia de los 3 de un middleware normal', 'correct' => true],
                    ['text' => 'Se registra antes de las rutas con app.useError() en lugar de app.use()', 'correct' => false],
                    ['text' => 'Lanza la excepción de nuevo con next(err) para propagarla al cliente', 'correct' => false],
                    ['text' => 'Se define con app.on("error", handler) en lugar de app.use()', 'correct' => false],
                ],
            ],

            // ── Lección 6: REST API ───────────────────────────────────────
            [
                'question'    => '¿Cuál de los siguientes diseños de endpoint sigue mejor los principios REST?',
                'explanation' => 'REST usa sustantivos en plural para recursos y verbos HTTP para acciones. PUT /users/:id/password es correcto: el método PUT indica actualización y "password" es un sub-recurso. Los verbos en la URL (changePassword, updatePass) rompen el diseño REST.',
                'options'     => [
                    ['text' => 'PUT /users/:id/password', 'correct' => true],
                    ['text' => 'POST /users/changePassword/:id', 'correct' => false],
                    ['text' => 'GET /users/updatePass?id=1', 'correct' => false],
                    ['text' => 'PATCH /updateUserPassword/:id', 'correct' => false],
                ],
            ],

            // ── Lección 7: Zod ────────────────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre z.parse() y z.safeParse() en Zod?',
                'explanation' => 'parse() lanza un ZodError si la validación falla, útil cuando quieres que el error se propague. safeParse() nunca lanza: retorna { success: true, data } o { success: false, error }, ideal para manejar errores de validación en un middleware sin try/catch.',
                'options'     => [
                    ['text' => 'parse() lanza ZodError si falla; safeParse() retorna { success, data/error } sin lanzar nunca', 'correct' => true],
                    ['text' => 'safeParse() es más rápido porque no construye el objeto ZodError', 'correct' => false],
                    ['text' => 'parse() valida solo tipos primitivos; safeParse() soporta objetos y arrays', 'correct' => false],
                    ['text' => 'No hay diferencia funcional; safeParse() es un alias de parse() con try/catch interno', 'correct' => false],
                ],
            ],

            // ── Lección 8: Variables de entorno ───────────────────────────
            [
                'question'    => '¿Por qué es recomendable validar las variables de entorno al arrancar la aplicación en lugar de en cada uso?',
                'explanation' => 'Si una variable crítica falta y solo se detecta al usarse, la app puede fallar en producción durante una petición real (dando un error críptico al usuario). Validar al arrancar da un error claro e inmediato antes de aceptar tráfico, y el proceso no arranca si la configuración es inválida.',
                'options'     => [
                    ['text' => 'Porque si falta una variable crítica, la app falla inmediatamente con un mensaje claro antes de aceptar tráfico, en lugar de fallar en runtime durante una petición', 'correct' => true],
                    ['text' => 'Porque process.env solo está disponible durante el arranque; después se borra de memoria', 'correct' => false],
                    ['text' => 'Para evitar que dotenv sobrescriba variables del sistema operativo en producción', 'correct' => false],
                    ['text' => 'Porque las variables de entorno son inmutables después del arranque en Node.js', 'correct' => false],
                ],
            ],

            // ── Lección 9: Prisma CRUD ────────────────────────────────────
            [
                'question'    => '¿Qué hace prisma.user.upsert() y cuándo es preferible a una combinación de findUnique + create/update?',
                'explanation' => 'upsert() es una operación atómica: busca por el campo "where", y si existe actualiza con "update", si no existe crea con "create". La ventaja sobre findUnique + create/update es que es atómica y evita condiciones de carrera en entornos con alta concurrencia.',
                'options'     => [
                    ['text' => 'Busca el registro y lo actualiza si existe o lo crea si no, en una sola operación atómica que evita condiciones de carrera', 'correct' => true],
                    ['text' => 'Es igual a findUnique() + update() pero con menos código; no tiene ventajas de atomicidad', 'correct' => false],
                    ['text' => 'Actualiza múltiples registros que coincidan con el where, igual que updateMany()', 'correct' => false],
                    ['text' => 'Inserta el registro ignorando errores de duplicado, similar al INSERT IGNORE de MySQL', 'correct' => false],
                ],
            ],

            // ── Lección 10: Prisma relaciones ─────────────────────────────
            [
                'question'    => '¿Qué problema puede causar el uso de include sin límites en Prisma y cómo se mitiga?',
                'explanation' => 'include carga todas las relaciones completas. Si un Post tiene 10.000 comentarios, prisma.post.findMany({ include: { comments: true } }) los carga todos en memoria. Se mitiga usando "take" para paginar las relaciones incluidas o usando "select" para traer solo los campos necesarios.',
                'options'     => [
                    ['text' => 'Puede cargar miles de registros relacionados en memoria; se mitiga con "take" para paginar las relaciones o "select" para limitar campos', 'correct' => true],
                    ['text' => 'Provoca un deadlock en la base de datos si dos queries usan include al mismo tiempo', 'correct' => false],
                    ['text' => 'include no es compatible con where en la misma query; hay que hacer dos queries separadas', 'correct' => false],
                    ['text' => 'Solo puede usarse una vez por query; include anidado no está soportado en Prisma', 'correct' => false],
                ],
            ],

            // ── Lección 11: JWT ───────────────────────────────────────────
            [
                'question'    => '¿Por qué los access tokens JWT deben tener una duración corta (ej. 15 minutos) combinados con refresh tokens de larga duración?',
                'explanation' => 'Un JWT firmado es válido hasta su expiración y no puede invalidarse sin un sistema de lista negra (costoso). Si el access token se roba, el atacante tiene acceso solo durante su corta vida. El refresh token (almacenado de forma segura en el servidor) permite obtener nuevos access tokens y puede revocarse en cualquier momento.',
                'options'     => [
                    ['text' => 'Porque un JWT no puede invalidarse sin lista negra; si es robado, la ventana de ataque se limita a los 15 minutos de vida del token', 'correct' => true],
                    ['text' => 'Porque JWT solo soporta payloads pequeños; los tokens largos necesitan refresh para resetear el tamaño', 'correct' => false],
                    ['text' => 'Para reducir la carga en el servidor de autenticación, que solo valida tokens cada 15 minutos', 'correct' => false],
                    ['text' => 'Porque el algoritmo HS256 pierde seguridad criptográfica después de 15 minutos', 'correct' => false],
                ],
            ],

            // ── Lección 12: Autorización RBAC ─────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre autenticación (AuthN) y autorización (AuthZ) en el contexto de una API REST?',
                'explanation' => 'Autenticación responde "¿quién eres?" — verifica la identidad del cliente (ej. validando un JWT). Autorización responde "¿qué puedes hacer?" — verifica si ese usuario tiene permisos para ejecutar la acción solicitada. Son capas separadas: puedes estar autenticado pero no autorizado.',
                'options'     => [
                    ['text' => 'Autenticación verifica la identidad ("¿quién eres?"); autorización verifica los permisos ("¿qué puedes hacer?")', 'correct' => true],
                    ['text' => 'Autenticación protege rutas con middleware; autorización usa JWT para identificar al usuario', 'correct' => false],
                    ['text' => 'Son sinónimos en REST; la diferencia es solo terminológica', 'correct' => false],
                    ['text' => 'Autorización se hace en el cliente; autenticación en el servidor', 'correct' => false],
                ],
            ],

            // ── Lección 13: Errores y Logging ─────────────────────────────
            [
                'question'    => '¿Por qué se recomienda usar logging estructurado (JSON) en lugar de console.log en producción?',
                'explanation' => 'Los logs JSON son parseables por herramientas de observabilidad (Datadog, Loki, CloudWatch) que indexan campos como level, timestamp y requestId. console.log emite strings sin estructura que son difíciles de filtrar y correlacionar. El nivel de log también permite silenciar debug en producción sin tocar código.',
                'options'     => [
                    ['text' => 'Los logs JSON son parseables por herramientas de observabilidad, permiten filtrado por campo y nivel, y son correlacionables por requestId', 'correct' => true],
                    ['text' => 'console.log bloquea el Event Loop en producción porque es síncrono; Pino usa streams asíncronos', 'correct' => false],
                    ['text' => 'console.log no está disponible en Node.js en modo producción (NODE_ENV=production)', 'correct' => false],
                    ['text' => 'Los logs estructurados usan menos memoria porque comprimen automáticamente los mensajes', 'correct' => false],
                ],
            ],

            // ── Lección 14: Testing ───────────────────────────────────────
            [
                'question'    => '¿Qué ventaja tiene inyectar el repositorio como dependencia en un servicio en lugar de instanciarlo dentro?',
                'explanation' => 'Al inyectar la dependencia, en los tests puedes pasar un repositorio falso (mock/stub) que no toca la base de datos real. El servicio queda desacoplado: no sabe si habla con PostgreSQL, SQLite o un Map en memoria. Esto hace los tests rápidos, aislados y deterministas.',
                'options'     => [
                    ['text' => 'Permite sustituir el repositorio real por un mock en tests, sin tocar la base de datos y sin cambiar el código del servicio', 'correct' => true],
                    ['text' => 'Mejora el rendimiento porque el repositorio se reutiliza entre instancias del servicio (singleton)', 'correct' => false],
                    ['text' => 'Es obligatorio en TypeScript para que el compilador resuelva los tipos correctamente', 'correct' => false],
                    ['text' => 'Evita que Prisma abra múltiples conexiones a la base de datos simultáneamente', 'correct' => false],
                ],
            ],

            // ── Lección 16: WebSockets ────────────────────────────────────
            [
                'question'    => '¿Cuándo es preferible usar WebSockets sobre HTTP polling para actualizar datos en el cliente?',
                'explanation' => 'El polling HTTP hace peticiones repetidas (cada N segundos), consumiendo recursos aunque no haya datos nuevos. WebSocket mantiene una conexión TCP persistente y el servidor empuja datos solo cuando hay cambios. Es preferible para casos con alta frecuencia de actualizaciones: chats, notificaciones en tiempo real, precios en vivo.',
                'options'     => [
                    ['text' => 'Cuando los datos cambian con alta frecuencia y la latencia importa, porque el servidor empuja los cambios sin que el cliente tenga que preguntar repetidamente', 'correct' => true],
                    ['text' => 'Siempre que se necesite enviar más de 1 KB de datos, porque HTTP tiene un límite de payload', 'correct' => false],
                    ['text' => 'WebSocket es preferible para todos los casos; el polling HTTP es obsoleto y no debe usarse', 'correct' => false],
                    ['text' => 'Solo cuando el cliente es un navegador; en apps móviles el polling es más eficiente', 'correct' => false],
                ],
            ],

            // ── Lección 17: Colas y tareas programadas ────────────────────
            [
                'question'    => '¿Qué problema resuelve una cola de trabajos (BullMQ) que no resuelve un simple setTimeout en Node.js?',
                'explanation' => 'setTimeout no sobrevive a reinicios del proceso. Si el servidor cae, los trabajos pendientes se pierden. BullMQ persiste los jobs en Redis, soporta reintentos automáticos con backoff, permite múltiples workers, prioridades y monitoreo. Es la solución correcta para tareas críticas como envío de emails o procesamiento de pagos.',
                'options'     => [
                    ['text' => 'Los jobs de BullMQ persisten en Redis y sobreviven a reinicios; setTimeout se pierde si el proceso muere', 'correct' => true],
                    ['text' => 'BullMQ ejecuta tareas en hilos separados para no bloquear el Event Loop; setTimeout es single-threaded', 'correct' => false],
                    ['text' => 'setTimeout tiene un máximo de 24 horas; BullMQ permite programar tareas con meses de antelación', 'correct' => false],
                    ['text' => 'BullMQ usa cron syntax; setTimeout solo acepta milisegundos', 'correct' => false],
                ],
            ],

            // ── Lección 18: Redis/Caché ────────────────────────────────────
            [
                'question'    => '¿Qué es el patrón cache-aside y cuál es su principal desventaja?',
                'explanation' => 'Cache-aside (lazy loading): la app primero busca en caché; si no hay (cache miss), consulta la BD, guarda el resultado en caché y lo retorna. La desventaja es la inconsistencia temporal (stale data): si el dato cambia en BD, la caché tiene la versión antigua hasta que expire el TTL o se invalide manualmente.',
                'options'     => [
                    ['text' => 'La app lee primero de caché y solo consulta la BD en un miss. La desventaja es la posible inconsistencia temporal (stale data) hasta que el TTL expira', 'correct' => true],
                    ['text' => 'La BD escribe directamente en caché al actualizarse. La desventaja es la mayor carga en la BD', 'correct' => false],
                    ['text' => 'Siempre escribe en caché y BD al mismo tiempo. La desventaja es la latencia adicional en escrituras', 'correct' => false],
                    ['text' => 'Mantiene dos cachés en paralelo. La desventaja es el doble uso de memoria', 'correct' => false],
                ],
            ],

            // ── Lección 19: Docker ────────────────────────────────────────
            [
                'question'    => '¿Qué ventaja aporta un Dockerfile multi-stage para una app Node.js en producción?',
                'explanation' => 'Un build multi-stage usa una imagen con todas las dev-dependencies (tsc, eslint, etc.) para compilar, y luego copia solo el output (dist/) a una imagen base limpia de producción. La imagen final no tiene el compilador ni las devDependencies, resultando mucho más pequeña (puede pasar de 1GB a ~150MB) y con menor superficie de ataque.',
                'options'     => [
                    ['text' => 'La imagen final de producción contiene solo el código compilado, sin devDependencies ni el compilador de TypeScript, reduciendo su tamaño y superficie de ataque', 'correct' => true],
                    ['text' => 'Permite compilar y ejecutar en paralelo, reduciendo el tiempo total de arranque de la app', 'correct' => false],
                    ['text' => 'Multi-stage es necesario para que Docker pueda cachear las capas de node_modules correctamente', 'correct' => false],
                    ['text' => 'Permite que la misma imagen funcione tanto en arquitectura x86 como ARM sin recompilación', 'correct' => false],
                ],
            ],

        ];
    }
}
