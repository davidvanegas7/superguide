<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class LaravelQuizSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'laravel-fullstack')->first();

        if (! $course) {
            $this->command->warn('Laravel course not found. Run CourseSeeder first.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id],
            [
                'title'       => 'Evaluación: Laravel Fullstack',
                'description' => 'Evalúa tus conocimientos de Laravel: Eloquent, Blade, middleware, testing, APIs y más.',
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

        $this->command->info("Laravel quiz seeded: {$quiz->questions()->count()} preguntas.");
    }

    private function questions(): array
    {
        return [

            // 1. Instalación y estructura
            [
                'question'    => '¿Cuál es el punto de entrada principal de una aplicación Laravel?',
                'explanation' => 'public/index.php es el punto de entrada. Todas las peticiones HTTP pasan por este archivo, que carga el autoloader de Composer y arranca el kernel de Laravel.',
                'options'     => [
                    ['text' => 'public/index.php: carga el autoloader y arranca el kernel HTTP', 'correct' => true],
                    ['text' => 'app/Http/Kernel.php: maneja directamente las peticiones', 'correct' => false],
                    ['text' => 'routes/web.php: es el primer archivo que se ejecuta', 'correct' => false],
                    ['text' => 'artisan: es el punto de entrada tanto para web como CLI', 'correct' => false],
                ],
            ],

            // 2. Rutas y controladores
            [
                'question'    => '¿Qué genera Route::resource("posts", PostController::class)?',
                'explanation' => 'Route::resource genera 7 rutas RESTful: index (GET /posts), create (GET /posts/create), store (POST /posts), show (GET /posts/{post}), edit (GET /posts/{post}/edit), update (PUT /posts/{post}), destroy (DELETE /posts/{post}).',
                'options'     => [
                    ['text' => '7 rutas RESTful: index, create, store, show, edit, update, destroy', 'correct' => true],
                    ['text' => '4 rutas CRUD: create, read, update, delete', 'correct' => false],
                    ['text' => '5 rutas API: index, store, show, update, destroy', 'correct' => false],
                    ['text' => 'Solo genera las rutas GET para listado y detalle', 'correct' => false],
                ],
            ],

            // 3. Blade
            [
                'question'    => '¿Cuál es la diferencia entre {{ }} y {!! !!} en Blade?',
                'explanation' => '{{ $var }} escapa HTML con htmlspecialchars() para prevenir XSS. {!! $var !!} muestra el contenido sin escapar (raw HTML). Se debe usar {!! !!} solo con contenido confiable.',
                'options'     => [
                    ['text' => '{{ }} escapa HTML (previene XSS); {!! !!} muestra HTML sin escapar (raw)', 'correct' => true],
                    ['text' => '{{ }} es para variables, {!! !!} es para funciones', 'correct' => false],
                    ['text' => 'No hay diferencia, son intercambiables', 'correct' => false],
                    ['text' => '{!! !!} es la versión deprecada de {{ }}', 'correct' => false],
                ],
            ],

            // 4. Migraciones
            [
                'question'    => '¿Qué comando de Artisan elimina TODAS las tablas y re-ejecuta las migraciones?',
                'explanation' => 'php artisan migrate:fresh elimina todas las tablas y ejecuta todas las migraciones desde cero. migrate:refresh hace rollback y migrate, pero puede fallar si el schema cambió.',
                'options'     => [
                    ['text' => 'php artisan migrate:fresh — elimina todas las tablas y re-ejecuta migraciones', 'correct' => true],
                    ['text' => 'php artisan migrate:reset — solo hace rollback', 'correct' => false],
                    ['text' => 'php artisan migrate:rollback — deshace el último batch', 'correct' => false],
                    ['text' => 'php artisan db:wipe — solo elimina tablas sin re-migrar', 'correct' => false],
                ],
            ],

            // 5. Eloquent fundamentos
            [
                'question'    => '¿Para qué sirve la propiedad $fillable en un modelo Eloquent?',
                'explanation' => '$fillable define qué campos se pueden asignar masivamente (via create(), update() con array). Protege contra vulnerabilidades de mass assignment. $guarded es el opuesto: campos que NO se pueden asignar masivamente.',
                'options'     => [
                    ['text' => 'Define qué campos aceptan asignación masiva (mass assignment) para proteger contra inyección de datos', 'correct' => true],
                    ['text' => 'Indica qué campos deben tener un valor por defecto', 'correct' => false],
                    ['text' => 'Lista los campos que se muestran al serializar a JSON', 'correct' => false],
                    ['text' => 'Determina qué campos se indexan automáticamente en la BD', 'correct' => false],
                ],
            ],

            // 6. Eloquent relaciones
            [
                'question'    => '¿Qué es el problema N+1 y cómo se resuelve en Eloquent?',
                'explanation' => 'N+1 ocurre cuando se ejecuta 1 query para los registros padre y N queries más (1 por cada padre) para cargar la relación. Se resuelve con eager loading: Model::with("relation")->get() que hace solo 2 queries.',
                'options'     => [
                    ['text' => 'Ejecutar N queries extra para relaciones; se resuelve con with() (eager loading) que reduce a 2 queries', 'correct' => true],
                    ['text' => 'Es un error de sintaxis al definir relaciones', 'correct' => false],
                    ['text' => 'Ocurre cuando faltan índices en la base de datos', 'correct' => false],
                    ['text' => 'Es un problema de migraciones duplicadas', 'correct' => false],
                ],
            ],

            // 7. Seeders y Factories
            [
                'question'    => '¿Qué son los states en una Factory de Laravel?',
                'explanation' => 'Los states permiten definir variaciones del modelo. Por ejemplo: User::factory()->admin()->create() aplica el state "admin" que sobreescribe atributos específicos. Se definen como métodos que retornan $this->state(fn() => [...]).',
                'options'     => [
                    ['text' => 'Variaciones del modelo que sobreescriben atributos específicos, como admin() o published()', 'correct' => true],
                    ['text' => 'Los diferentes estados del ciclo de vida de un seeder', 'correct' => false],
                    ['text' => 'Estados de la base de datos (vacía, con datos, en migración)', 'correct' => false],
                    ['text' => 'Configuraciones regionales para generar datos falsos', 'correct' => false],
                ],
            ],

            // 8. Validación
            [
                'question'    => '¿Cuál es la ventaja de usar Form Requests sobre $request->validate()?',
                'explanation' => 'Form Requests encapsulan la lógica de validación y autorización en una clase dedicada, manteniendo el controlador limpio. Incluyen authorize() para verificar permisos y rules() para las reglas. Son reusables y testeables.',
                'options'     => [
                    ['text' => 'Encapsulan validación y autorización en una clase separada, manteniendo controladores limpios y testeables', 'correct' => true],
                    ['text' => 'Son más rápidos porque cachean las reglas de validación', 'correct' => false],
                    ['text' => 'Permiten validar archivos, algo que $request->validate() no puede', 'correct' => false],
                    ['text' => 'No hay ventaja real, son simplemente una sintaxis alternativa', 'correct' => false],
                ],
            ],

            // 9. Middleware
            [
                'question'    => '¿Qué diferencia hay entre middleware "before" y "after"?',
                'explanation' => 'Un middleware "before" ejecuta lógica ANTES de pasar al siguiente (antes de $next($request)). Un middleware "after" deja pasar la petición primero ($response = $next($request)) y ejecuta lógica DESPUÉS, pudiendo modificar la respuesta.',
                'options'     => [
                    ['text' => 'Before ejecuta lógica antes de $next(); after deja pasar y modifica la respuesta después', 'correct' => true],
                    ['text' => 'Before se aplica a GET, after se aplica a POST', 'correct' => false],
                    ['text' => 'Before es global, after es por ruta', 'correct' => false],
                    ['text' => 'No hay diferencia, se ejecutan en el mismo punto', 'correct' => false],
                ],
            ],

            // 10. Autenticación
            [
                'question'    => '¿Qué paquete de Laravel se usa para autenticación de APIs con tokens?',
                'explanation' => 'Laravel Sanctum proporciona autenticación por tokens personales para APIs y SPAs. Es más ligero que Passport (OAuth2). Sanctum genera tokens que se guardan en la tabla personal_access_tokens.',
                'options'     => [
                    ['text' => 'Laravel Sanctum: tokens personales ligeros para APIs y SPAs', 'correct' => true],
                    ['text' => 'Laravel Breeze: solo para APIs', 'correct' => false],
                    ['text' => 'Laravel Guard: el sistema nativo de tokens', 'correct' => false],
                    ['text' => 'Laravel JWT: JSON Web Tokens integrado', 'correct' => false],
                ],
            ],

            // 11. Autorización
            [
                'question'    => '¿Cuál es la diferencia entre Gates y Policies en Laravel?',
                'explanation' => 'Gates son closures simples para acciones que NO están ligadas a un modelo (ej: ver dashboard). Policies son clases dedicadas a autorizar acciones sobre un modelo específico (ej: puede editar un Post). Policies se auto-descubren por convención.',
                'options'     => [
                    ['text' => 'Gates: closures para acciones generales; Policies: clases para autorizar acciones sobre un modelo específico', 'correct' => true],
                    ['text' => 'Gates son para admin, Policies para usuarios normales', 'correct' => false],
                    ['text' => 'No hay diferencia, son sinónimos', 'correct' => false],
                    ['text' => 'Gates se ejecutan en middleware, Policies en controladores únicamente', 'correct' => false],
                ],
            ],

            // 12. API Resources
            [
                'question'    => '¿Para qué sirven las API Resources (JsonResource) en Laravel?',
                'explanation' => 'JsonResource actúa como capa de transformación entre los modelos Eloquent y las respuestas JSON de la API. Permite controlar exactamente qué campos se exponen, incluir relaciones condicionalmente (whenLoaded), y estandarizar la estructura de respuesta.',
                'options'     => [
                    ['text' => 'Transforman modelos Eloquent a respuestas JSON controlando campos expuestos y relaciones condicionales', 'correct' => true],
                    ['text' => 'Generan automáticamente endpoints REST para los modelos', 'correct' => false],
                    ['text' => 'Son un reemplazo de Eloquent para APIs', 'correct' => false],
                    ['text' => 'Solo sirven para paginar resultados', 'correct' => false],
                ],
            ],

            // 13. Colas y Jobs
            [
                'question'    => '¿Cuándo se debe usar un Job en cola en lugar de procesamiento síncrono?',
                'explanation' => 'Jobs en cola son ideales para tareas lentas que no necesitan respuesta inmediata: envío de emails, procesamiento de imágenes, generación de reportes, llamadas a APIs externas. Mejoran la experiencia del usuario al responder rápidamente.',
                'options'     => [
                    ['text' => 'Para tareas lentas que no requieren respuesta inmediata: emails, imágenes, reportes, APIs externas', 'correct' => true],
                    ['text' => 'Para todas las operaciones de base de datos', 'correct' => false],
                    ['text' => 'Solo cuando la aplicación tiene más de 100 usuarios', 'correct' => false],
                    ['text' => 'Únicamente para tareas programadas con cron', 'correct' => false],
                ],
            ],

            // 14. Eventos y Listeners
            [
                'question'    => '¿Qué son los Model Observers en Laravel?',
                'explanation' => 'Los Observers agrupan los event handlers de un modelo en una clase dedicada. Pueden escuchar: creating, created, updating, updated, deleting, deleted, restoring, restored. Se registran en AppServiceProvider o con el atributo #[ObservedBy].',
                'options'     => [
                    ['text' => 'Clases que agrupan handlers para eventos del ciclo de vida de un modelo (creating, updating, deleting, etc.)', 'correct' => true],
                    ['text' => 'Patrones para observar cambios en las vistas Blade', 'correct' => false],
                    ['text' => 'Herramientas para monitorear queries de base de datos', 'correct' => false],
                    ['text' => 'Middleware especiales que observan las peticiones HTTP', 'correct' => false],
                ],
            ],

            // 15. Notificaciones
            [
                'question'    => '¿Qué método define los canales por los que se envía una notificación?',
                'explanation' => 'El método via() en la clase Notification retorna un array con los canales: "mail", "database", "broadcast", "slack", etc. Laravel invoca toMail(), toDatabase(), etc. según los canales especificados.',
                'options'     => [
                    ['text' => 'via(): retorna un array de canales como ["mail", "database", "broadcast"]', 'correct' => true],
                    ['text' => 'channels(): define los canales de envío', 'correct' => false],
                    ['text' => 'sendTo(): especifica dónde enviar', 'correct' => false],
                    ['text' => 'Se configura globalmente en config/notifications.php', 'correct' => false],
                ],
            ],

            // 16. Testing
            [
                'question'    => '¿Qué hace Mail::fake() en un test de Laravel?',
                'explanation' => 'Mail::fake() intercepta todos los correos y evita que se envíen realmente. Luego se puede verificar que se enviaron los correos esperados con Mail::assertSent(), Mail::assertNotSent(), Mail::assertQueued(), etc.',
                'options'     => [
                    ['text' => 'Intercepta correos sin enviarlos realmente y permite verificar con assertSent()/assertNotSent()', 'correct' => true],
                    ['text' => 'Crea un servidor SMTP falso para recibir correos', 'correct' => false],
                    ['text' => 'Genera correos de prueba con datos falsos de Faker', 'correct' => false],
                    ['text' => 'Redirige todos los correos a una dirección de testing', 'correct' => false],
                ],
            ],

            // 17. Livewire
            [
                'question'    => '¿Cómo funciona wire:model en Livewire?',
                'explanation' => 'wire:model crea data binding bidireccional entre un input HTML y una propiedad pública del componente Livewire. Cuando el usuario escribe, se envía al servidor via AJAX y actualiza la propiedad. wire:model.live actualiza en tiempo real.',
                'options'     => [
                    ['text' => 'Data binding bidireccional: cambios en el input se sincronizan con propiedades del componente vía AJAX', 'correct' => true],
                    ['text' => 'Solo funciona con Alpine.js, no con el servidor', 'correct' => false],
                    ['text' => 'Es un atributo HTML estándar para formularios', 'correct' => false],
                    ['text' => 'Crea una copia local del modelo Eloquent', 'correct' => false],
                ],
            ],

            // 18. Deploy
            [
                'question'    => '¿Qué hace php artisan optimize en producción?',
                'explanation' => 'php artisan optimize cachea la configuración, rutas y vistas compiladas para mejorar el rendimiento. Equivale a ejecutar config:cache + route:cache + view:cache. En desarrollo no se debe usar porque los cambios no se reflejan.',
                'options'     => [
                    ['text' => 'Cachea configuración, rutas y vistas para mejorar rendimiento en producción', 'correct' => true],
                    ['text' => 'Minimiza los assets CSS y JavaScript', 'correct' => false],
                    ['text' => 'Optimiza las queries de base de datos', 'correct' => false],
                    ['text' => 'Comprime los archivos del proyecto para reducir tamaño', 'correct' => false],
                ],
            ],

            // 19. Entrevista - Service Container
            [
                'question'    => '¿Qué es el Service Container de Laravel?',
                'explanation' => 'El Service Container es un contenedor de inyección de dependencias (IoC). Gestiona la creación de clases y sus dependencias automáticamente. Cuando un controlador recibe una clase en su constructor, el Container la resuelve automáticamente (autowiring).',
                'options'     => [
                    ['text' => 'Un contenedor IoC que gestiona creación de clases y resuelve dependencias automáticamente (autowiring)', 'correct' => true],
                    ['text' => 'Un patrón para almacenar servicios de terceros como Stripe o AWS', 'correct' => false],
                    ['text' => 'Un contenedor Docker integrado en Laravel', 'correct' => false],
                    ['text' => 'Una base de datos en memoria para caché de servicios', 'correct' => false],
                ],
            ],

            // 20. Entrevista - Facades
            [
                'question'    => '¿Qué son las Facades en Laravel y cómo funcionan internamente?',
                'explanation' => 'Las Facades proporcionan una API estática a clases del Service Container. Internamente, __callStatic() resuelve la instancia real del Container y delega la llamada. Cache::get() en realidad llama a app("cache")->get(). No son estáticas reales.',
                'options'     => [
                    ['text' => 'API estática que internamente resuelve instancias del Container via __callStatic(); no son estáticas reales', 'correct' => true],
                    ['text' => 'Clases estáticas puras que no tienen instancias', 'correct' => false],
                    ['text' => 'Un patrón de diseño exclusivo de Laravel sin equivalente en PHP', 'correct' => false],
                    ['text' => 'Wrappers sobre funciones globales de PHP', 'correct' => false],
                ],
            ],

        ];
    }
}
