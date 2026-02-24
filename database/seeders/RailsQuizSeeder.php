<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class RailsQuizSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'rails-8-fullstack')->first();

        if (! $course) {
            $this->command->warn('Rails 8 course not found. Run CourseSeeder first.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id],
            [
                'title'       => 'Evaluación: Ruby on Rails 8',
                'description' => 'Evalúa tus conocimientos sobre Rails 8: MVC, Active Record, Hotwire, autenticación, APIs y más.',
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

        $this->command->info("Rails 8 quiz seeded: {$quiz->questions()->count()} preguntas.");
    }

    private function questions(): array
    {
        return [

            // 1. Introducción
            [
                'question'    => '¿Cuáles son los principios fundamentales de Rails?',
                'explanation' => 'Rails se basa en Convention over Configuration (CoC) y Don\'t Repeat Yourself (DRY). CoC reduce configuración usando convenciones de nombres. DRY evita la duplicación de código.',
                'options'     => [
                    ['text' => 'Convention over Configuration (CoC) y Don\'t Repeat Yourself (DRY)', 'correct' => true],
                    ['text' => 'Configuration over Convention y Write Everything Twice', 'correct' => false],
                    ['text' => 'Inversion of Control y Dependency Injection', 'correct' => false],
                    ['text' => 'SOLID principles y Clean Architecture', 'correct' => false],
                ],
            ],

            // 2. MVC
            [
                'question'    => '¿Qué componente de MVC en Rails se encarga de la lógica de negocio y acceso a datos?',
                'explanation' => 'El Modelo (Model) encapsula la lógica de negocio, validaciones, asociaciones y acceso a la base de datos mediante Active Record. El Controller coordina y la View presenta.',
                'options'     => [
                    ['text' => 'Model (Modelo)', 'correct' => true],
                    ['text' => 'View (Vista)', 'correct' => false],
                    ['text' => 'Controller (Controlador)', 'correct' => false],
                    ['text' => 'Router', 'correct' => false],
                ],
            ],

            // 3. Routing
            [
                'question'    => '¿Qué genera "resources :articles" en config/routes.rb?',
                'explanation' => 'resources genera las 7 rutas RESTful estándar: index, show, new, create, edit, update y destroy, con los helpers correspondientes (articles_path, article_path, etc.).',
                'options'     => [
                    ['text' => '7 rutas RESTful: index, show, new, create, edit, update, destroy', 'correct' => true],
                    ['text' => 'Solo las rutas GET para index y show', 'correct' => false],
                    ['text' => 'Una sola ruta comodín que acepta cualquier acción', 'correct' => false],
                    ['text' => '4 rutas CRUD básicas sin formularios', 'correct' => false],
                ],
            ],

            // 4. Active Record
            [
                'question'    => '¿Qué comando deshace la última migración ejecutada?',
                'explanation' => 'rails db:rollback deshace la última migración, ejecutando el método down o revirtiendo el change. Se puede usar STEP=N para revertir múltiples migraciones.',
                'options'     => [
                    ['text' => 'rails db:rollback', 'correct' => true],
                    ['text' => 'rails db:undo', 'correct' => false],
                    ['text' => 'rails db:revert', 'correct' => false],
                    ['text' => 'rails migrate:down', 'correct' => false],
                ],
            ],

            // 5. Asociaciones
            [
                'question'    => '¿Qué problema resuelve el método includes() en Active Record?',
                'explanation' => 'includes() resuelve el problema N+1: cuando iteras sobre registros y accedes a asociaciones, sin includes se ejecuta una query por cada registro. includes() pre-carga las asociaciones en 1-2 queries.',
                'options'     => [
                    ['text' => 'El problema de queries N+1, pre-cargando asociaciones en 1-2 queries', 'correct' => true],
                    ['text' => 'Incluir módulos Ruby en el modelo', 'correct' => false],
                    ['text' => 'Añadir columnas temporales a la consulta', 'correct' => false],
                    ['text' => 'Incluir registros soft-deleted en los resultados', 'correct' => false],
                ],
            ],

            // 6. Validaciones
            [
                'question'    => '¿En qué momento se ejecuta un callback before_save?',
                'explanation' => 'before_save se ejecuta antes de guardar el registro, tanto en create como en update. Se ejecuta después de las validaciones. Si retorna false (throw :abort en Rails 5+), cancela la operación.',
                'options'     => [
                    ['text' => 'Antes de guardar el registro, tanto en create como en update, después de validar', 'correct' => true],
                    ['text' => 'Solo antes de crear un registro nuevo', 'correct' => false],
                    ['text' => 'Antes de ejecutar las validaciones', 'correct' => false],
                    ['text' => 'Después de guardar el registro en la base de datos', 'correct' => false],
                ],
            ],

            // 7. Controllers
            [
                'question'    => '¿Para qué sirven los Strong Parameters en Rails?',
                'explanation' => 'Strong Parameters (params.require(:model).permit(:field1, :field2)) previenen Mass Assignment: solo permiten los atributos explícitamente autorizados, evitando que un usuario malicioso modifique campos protegidos.',
                'options'     => [
                    ['text' => 'Prevenir Mass Assignment permitiendo solo atributos explícitamente autorizados', 'correct' => true],
                    ['text' => 'Encriptar los parámetros para mayor seguridad', 'correct' => false],
                    ['text' => 'Validar el tipo de dato de cada parámetro', 'correct' => false],
                    ['text' => 'Limitar el número de parámetros en una petición', 'correct' => false],
                ],
            ],

            // 8. Vistas
            [
                'question'    => '¿Cuál es la diferencia entre <%= %> y <% %> en ERB?',
                'explanation' => '<%= %> evalúa la expresión Ruby Y la imprime en el HTML. <% %> solo evalúa sin imprimir (para control flow como if, each, etc.). Olvidar el = es un error frecuente.',
                'options'     => [
                    ['text' => '<%= %> evalúa e imprime el resultado; <% %> solo evalúa sin imprimir', 'correct' => true],
                    ['text' => '<%= %> es para HTML seguro; <% %> para HTML sin escapar', 'correct' => false],
                    ['text' => 'No hay diferencia, son intercambiables', 'correct' => false],
                    ['text' => '<%= %> es para variables; <% %> es para métodos', 'correct' => false],
                ],
            ],

            // 9. Hotwire
            [
                'question'    => '¿Qué es un Turbo Frame en Rails?',
                'explanation' => 'Un Turbo Frame es un contenedor HTML (<turbo-frame id="...">) que permite actualizar solo esa sección de la página al navegar, sin recargar toda la página. Las peticiones dentro del frame solo reemplazan el contenido de ese frame.',
                'options'     => [
                    ['text' => 'Un contenedor que permite actualizar solo una sección de la página sin recargar todo el HTML', 'correct' => true],
                    ['text' => 'Un iframe mejorado para incrustar páginas externas', 'correct' => false],
                    ['text' => 'Un framework CSS para crear layouts responsive', 'correct' => false],
                    ['text' => 'Un animation frame optimizado de JavaScript', 'correct' => false],
                ],
            ],

            // 10. Stimulus
            [
                'question'    => '¿Cómo se conecta un controller de Stimulus al HTML?',
                'explanation' => 'Se usa el atributo data-controller="nombre" en el HTML. Stimulus busca automáticamente un archivo nombre_controller.js y lo instancia. Actions se conectan con data-action="event->nombre#método".',
                'options'     => [
                    ['text' => 'Con el atributo data-controller="nombre" en el elemento HTML', 'correct' => true],
                    ['text' => 'Importándolo y montándolo como un componente React', 'correct' => false],
                    ['text' => 'Con un tag <script src="controller.js"> en el head', 'correct' => false],
                    ['text' => 'Registrándolo en config/routes.rb', 'correct' => false],
                ],
            ],

            // 11. Action Cable
            [
                'question'    => '¿Qué protocolo usa Action Cable para comunicación en tiempo real?',
                'explanation' => 'Action Cable usa WebSockets para mantener una conexión bidireccional persistente entre cliente y servidor. Esto permite enviar datos del servidor al cliente sin que el cliente haga polling.',
                'options'     => [
                    ['text' => 'WebSockets para conexión bidireccional persistente', 'correct' => true],
                    ['text' => 'HTTP Long Polling con AJAX', 'correct' => false],
                    ['text' => 'Server-Sent Events (SSE) unidireccionales', 'correct' => false],
                    ['text' => 'gRPC con Protocol Buffers', 'correct' => false],
                ],
            ],

            // 12. Autenticación
            [
                'question'    => '¿Qué proporciona has_secure_password en un modelo Rails?',
                'explanation' => 'has_secure_password usa bcrypt para hashear contraseñas. Requiere columna password_digest. Proporciona el método authenticate(password) que retorna el user o false, y validación de presencia de password.',
                'options'     => [
                    ['text' => 'Hasheo con bcrypt, método authenticate() y validación de presencia del password', 'correct' => true],
                    ['text' => 'Solo encriptación AES-256 de la contraseña', 'correct' => false],
                    ['text' => 'Autenticación OAuth2 con proveedores externos', 'correct' => false],
                    ['text' => 'Generación automática de JWT tokens', 'correct' => false],
                ],
            ],

            // 13. Autorización
            [
                'question'    => '¿Qué es una Policy en Pundit?',
                'explanation' => 'Una Policy es una clase PORO (Plain Old Ruby Object) que encapsula las reglas de autorización para un modelo. Tiene métodos como create?, update?, destroy? que reciben user y record, retornando true/false.',
                'options'     => [
                    ['text' => 'Una clase Ruby que encapsula reglas de autorización para un modelo con métodos como create?, update?', 'correct' => true],
                    ['text' => 'Un archivo de configuración YAML que define permisos', 'correct' => false],
                    ['text' => 'Un middleware que intercepta todas las peticiones HTTP', 'correct' => false],
                    ['text' => 'Una migración que crea la tabla de permisos', 'correct' => false],
                ],
            ],

            // 14. API
            [
                'question'    => '¿Qué diferencia hay entre rails new app y rails new app --api?',
                'explanation' => 'El flag --api crea una aplicación más ligera: hereda de ActionController::API (sin cookies, sessions, CSRF), omite middleware innecesario y no genera views ni assets. Ideal para APIs JSON.',
                'options'     => [
                    ['text' => '--api crea app ligera sin views, sessions ni CSRF, con ActionController::API', 'correct' => true],
                    ['text' => '--api solo cambia el puerto por defecto a 3001', 'correct' => false],
                    ['text' => '--api instala automáticamente GraphQL', 'correct' => false],
                    ['text' => 'No hay diferencia, --api es una opción decorativa', 'correct' => false],
                ],
            ],

            // 15. Active Job
            [
                'question'    => '¿Qué es Solid Queue en Rails 8?',
                'explanation' => 'Solid Queue es el backend de Active Job basado en bases de datos relacionales (SQLite/PostgreSQL). Reemplaza la necesidad de Redis/Sidekiq para muchos casos. Es el backend por defecto en Rails 8.',
                'options'     => [
                    ['text' => 'Un backend de Active Job basado en BD relacional, reemplazando la necesidad de Redis para colas', 'correct' => true],
                    ['text' => 'Una cola de mensajes en memoria que se pierde al reiniciar', 'correct' => false],
                    ['text' => 'Un servicio externo de Amazon para procesar jobs', 'correct' => false],
                    ['text' => 'Un wrapper de Sidekiq incluido en Rails', 'correct' => false],
                ],
            ],

            // 16. Testing
            [
                'question'    => '¿Cuál es la diferencia entre un test de modelo y un test de integración en Rails?',
                'explanation' => 'Un test de modelo prueba la lógica de negocio (validaciones, scopes, métodos) de forma aislada. Un test de integración prueba el flujo completo: hace peticiones HTTP, verifica responses, sigue redirects y comprueba la interacción entre componentes.',
                'options'     => [
                    ['text' => 'Model test prueba lógica aislada; integration test prueba el flujo HTTP completo entre componentes', 'correct' => true],
                    ['text' => 'Son lo mismo pero con nombres diferentes', 'correct' => false],
                    ['text' => 'Model test usa RSpec; integration test usa Minitest', 'correct' => false],
                    ['text' => 'Model test es automático; integration test es manual', 'correct' => false],
                ],
            ],

            // 17. Deployment
            [
                'question'    => '¿Qué es Kamal 2 en el ecosistema Rails 8?',
                'explanation' => 'Kamal 2 es la herramienta oficial de deploy de Rails 8 que usa Docker para desplegar aplicaciones en cualquier servidor (VPS, bare metal). Gestiona zero-downtime deploys, SSL, load balancing con Traefik y rollbacks.',
                'options'     => [
                    ['text' => 'Herramienta de deploy con Docker para cualquier servidor, con zero-downtime y Traefik', 'correct' => true],
                    ['text' => 'Un servicio de hosting exclusivo para Rails', 'correct' => false],
                    ['text' => 'Un plugin de Heroku para deploys automáticos', 'correct' => false],
                    ['text' => 'Un orquestador de Kubernetes para Rails', 'correct' => false],
                ],
            ],

            // 18. Novedades Rails 8
            [
                'question'    => '¿Qué es la "Solid Trifecta" en Rails 8?',
                'explanation' => 'La Solid Trifecta se refiere a Solid Cache (caché en BD), Solid Queue (colas en BD) y Solid Cable (WebSockets en BD). Los tres eliminan la dependencia de Redis, simplificando la infraestructura.',
                'options'     => [
                    ['text' => 'Solid Cache + Solid Queue + Solid Cable: cache, colas y WebSockets basados en BD, sin Redis', 'correct' => true],
                    ['text' => 'Tres nuevos frameworks frontend incluidos en Rails', 'correct' => false],
                    ['text' => 'Tres niveles de testing: unit, integration, system', 'correct' => false],
                    ['text' => 'Tres bases de datos soportadas: SQLite, PostgreSQL, MySQL', 'correct' => false],
                ],
            ],

            // 19. Entrevista - scopes
            [
                'question'    => '¿Qué es un scope en Active Record?',
                'explanation' => 'Un scope es una query reutilizable definida en el modelo con scope :name, -> { where(...) }. Son encadenables (chainable), retornan ActiveRecord::Relation y se pueden usar como class methods.',
                'options'     => [
                    ['text' => 'Una query reutilizable y encadenable definida en el modelo con scope :name, -> { where(...) }', 'correct' => true],
                    ['text' => 'Un namespace para agrupar controladores', 'correct' => false],
                    ['text' => 'Una variable de ámbito limitado dentro de un bloque', 'correct' => false],
                    ['text' => 'Un permiso de acceso a nivel de base de datos', 'correct' => false],
                ],
            ],

            // 20. Entrevista - seguridad
            [
                'question'    => '¿Cómo protege Rails contra ataques CSRF por defecto?',
                'explanation' => 'Rails genera un token CSRF único por sesión e incluye protect_from_forgery en ApplicationController. Cada formulario incluye un campo hidden con el token (authenticity_token). Las peticiones POST/PUT/DELETE deben incluir este token válido.',
                'options'     => [
                    ['text' => 'Con protect_from_forgery y un authenticity_token único incluido en cada formulario', 'correct' => true],
                    ['text' => 'Bloqueando todas las peticiones POST desde dominios externos', 'correct' => false],
                    ['text' => 'Requiriendo autenticación OAuth2 para todo formulario', 'correct' => false],
                    ['text' => 'Rails no protege contra CSRF, hay que configurarlo manualmente', 'correct' => false],
                ],
            ],

        ];
    }
}
