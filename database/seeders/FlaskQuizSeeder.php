<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class FlaskQuizSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'flask-backend')->first();

        if (! $course) {
            $this->command->warn('Flask course not found. Run CourseSeeder first.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id],
            [
                'title'       => 'Evaluación: Flask Backend con Python',
                'description' => 'Pon a prueba tus conocimientos sobre Flask: blueprints, bases de datos, APIs REST, autenticación, testing y deploy.',
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

        $this->command->info("Flask quiz seeded: {$quiz->questions()->count()} preguntas.");
    }

    private function questions(): array
    {
        return [

            // ── 1 · Introducción a Flask ──────────────────────────────
            [
                'question'    => '¿Qué concepto define mejor a Flask como "micro-framework"?',
                'explanation' => 'Flask es un micro-framework porque su núcleo es mínimo y no impone decisiones sobre base de datos, formularios u ORM. Provee routing, request/response y templates (Jinja2), pero todo lo demás se agrega mediante extensiones. Esto da máxima flexibilidad al desarrollador.',
                'options'     => [
                    ['text' => 'Su núcleo es mínimo: solo routing, request/response y templates; todo lo demás se agrega con extensiones', 'correct' => true],
                    ['text' => 'Solo puede crear aplicaciones pequeñas con pocas rutas', 'correct' => false],
                    ['text' => 'No soporta bases de datos ni autenticación', 'correct' => false],
                    ['text' => 'Es una versión reducida de Django sin ninguna funcionalidad propia', 'correct' => false],
                ],
            ],

            // ── 2 · Rutas y vistas ────────────────────────────────────
            [
                'question'    => '¿Qué sucede si se define una ruta Flask con y sin trailing slash, como `/users` vs `/users/`?',
                'explanation' => 'Si la ruta se define con trailing slash (/users/), Flask redirige automáticamente /users a /users/ (301). Si se define sin slash (/users), acceder a /users/ retorna 404. Este comportamiento imita el de carpetas vs archivos en un sistema de archivos.',
                'options'     => [
                    ['text' => 'Con slash final Flask redirige automáticamente la versión sin slash; sin slash final, la versión con slash da 404', 'correct' => true],
                    ['text' => 'No hay diferencia, Flask trata ambas URLs como equivalentes', 'correct' => false],
                    ['text' => 'Flask lanza un error si no se incluye trailing slash en todas las rutas', 'correct' => false],
                    ['text' => 'El trailing slash solo afecta a métodos POST, no a GET', 'correct' => false],
                ],
            ],

            // ── 3 · Templates con Jinja2 ──────────────────────────────
            [
                'question'    => '¿Por qué Jinja2 escapa automáticamente las variables con `{{ variable }}`?',
                'explanation' => 'Jinja2 habilita autoescaping por defecto en templates HTML. Convierte caracteres como <, >, &, " a sus entidades HTML (&lt;, &gt;, etc.) para prevenir ataques XSS (Cross-Site Scripting). Para insertar HTML seguro se usa el filtro |safe o Markup().',
                'options'     => [
                    ['text' => 'Para prevenir ataques XSS convirtiendo caracteres HTML peligrosos a entidades seguras', 'correct' => true],
                    ['text' => 'Para formatear el texto con estilos CSS automáticos', 'correct' => false],
                    ['text' => 'Por compatibilidad con navegadores antiguos que no soportan UTF-8', 'correct' => false],
                    ['text' => 'Para validar que las variables sean del tipo correcto', 'correct' => false],
                ],
            ],

            // ── 4 · Formularios y validación ──────────────────────────
            [
                'question'    => '¿Cuál es la ventaja principal de usar Flask-WTF/WTForms en lugar de procesar request.form directamente?',
                'explanation' => 'Flask-WTF integra WTForms con Flask, proporcionando validación declarativa de campos, protección CSRF automática, renderizado de formularios en templates, y manejo de errores estructurado. Procesar request.form directamente requiere implementar todo esto manualmente.',
                'options'     => [
                    ['text' => 'Proporciona validación declarativa, protección CSRF automática y manejo de errores estructurado', 'correct' => true],
                    ['text' => 'Es más rápido porque procesa formularios a nivel de C', 'correct' => false],
                    ['text' => 'Es obligatorio para que Flask acepte datos POST', 'correct' => false],
                    ['text' => 'Solo sirve para formularios con subida de archivos', 'correct' => false],
                ],
            ],

            // ── 5 · Base de datos con SQLAlchemy ──────────────────────
            [
                'question'    => '¿Qué problema resuelve el patrón Unit of Work implementado por la session de SQLAlchemy?',
                'explanation' => 'La session de SQLAlchemy acumula cambios (inserts, updates, deletes) en memoria y los persiste en una sola transacción con commit(). Esto garantiza atomicidad (todo o nada), reduce round-trips a la BD, y mantiene un identity map que asegura que cada fila tenga un solo objeto Python.',
                'options'     => [
                    ['text' => 'Acumula cambios en memoria y los persiste atómicamente en commit(), reduciendo round-trips y garantizando consistencia', 'correct' => true],
                    ['text' => 'Convierte automáticamente SQL a código Python', 'correct' => false],
                    ['text' => 'Replica los datos en múltiples bases de datos para alta disponibilidad', 'correct' => false],
                    ['text' => 'Ejecuta cada operación inmediatamente sin necesidad de transacciones', 'correct' => false],
                ],
            ],

            // ── 6 · Migraciones con Alembic ───────────────────────────
            [
                'question'    => '¿Por qué es importante no editar migraciones ya aplicadas en producción?',
                'explanation' => 'Las migraciones son un historial lineal. Si se edita una migración ya aplicada, la BD de producción no la re-ejecuta (ya tiene su registro en alembic_version). Esto crea divergencia entre el esquema real y el código. Para corregir, se debe crear una nueva migración con los cambios.',
                'options'     => [
                    ['text' => 'Porque la BD no re-ejecuta migraciones marcadas como aplicadas, causando divergencia entre esquema y código', 'correct' => true],
                    ['text' => 'Porque Alembic bloquea los archivos de migración para escritura', 'correct' => false],
                    ['text' => 'Porque cada migración tiene un hash que la invalida si se modifica', 'correct' => false],
                    ['text' => 'No hay problema en editarlas; se re-aplican automáticamente', 'correct' => false],
                ],
            ],

            // ── 7 · APIs REST ─────────────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre PUT y PATCH en una API REST?',
                'explanation' => 'PUT reemplaza el recurso completo (debe enviar todos los campos), mientras que PATCH aplica una actualización parcial (solo los campos que cambian). Si usas PUT omitiendo campos, estos se pueden perder o resetear a valores por defecto. PATCH es más eficiente para cambios pequeños.',
                'options'     => [
                    ['text' => 'PUT reemplaza el recurso completo; PATCH actualiza solo los campos enviados (actualización parcial)', 'correct' => true],
                    ['text' => 'PUT es para crear recursos y PATCH para eliminarlos', 'correct' => false],
                    ['text' => 'Son idénticos; PATCH es solo un alias moderno de PUT', 'correct' => false],
                    ['text' => 'PUT usa JSON y PATCH usa form-data', 'correct' => false],
                ],
            ],

            // ── 8 · Autenticación ─────────────────────────────────────
            [
                'question'    => '¿Por qué JWT (JSON Web Token) no requiere almacenamiento en el servidor para validar sesiones?',
                'explanation' => 'JWT es auto-contenido: incluye los datos del usuario (claims) y una firma digital. El servidor solo necesita la clave secreta para verificar la firma, sin consultar una base de datos de sesiones. La contrapartida es que no se puede invalidar un JWT individual antes de que expire.',
                'options'     => [
                    ['text' => 'Porque es auto-contenido: incluye datos y firma verificable con la clave secreta, sin consultar BD', 'correct' => true],
                    ['text' => 'Porque los tokens se almacenan en la cookie del navegador que el servidor lee directamente', 'correct' => false],
                    ['text' => 'Porque usa encriptación end-to-end que no necesita verificación', 'correct' => false],
                    ['text' => 'Sí requiere almacenamiento; JWT siempre necesita una tabla de sesiones', 'correct' => false],
                ],
            ],

            // ── 9 · Blueprints y estructura ───────────────────────────
            [
                'question'    => '¿Qué problema principal resuelven los Blueprints de Flask?',
                'explanation' => 'Los Blueprints permiten organizar una aplicación Flask en módulos reutilizables con sus propias rutas, templates, archivos estáticos y error handlers. Sin blueprints, todas las rutas estarían en un solo archivo, haciendo el código inmanejable en proyectos medianos/grandes.',
                'options'     => [
                    ['text' => 'Permiten modularizar la app en componentes reutilizables con rutas, templates y estáticos propios', 'correct' => true],
                    ['text' => 'Mejoran el rendimiento compilando las rutas a código nativo', 'correct' => false],
                    ['text' => 'Son necesarios para usar bases de datos en Flask', 'correct' => false],
                    ['text' => 'Generan documentación automática de la API', 'correct' => false],
                ],
            ],

            // ── 10 · Middleware y hooks ────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre `@app.before_request` y un middleware WSGI en Flask?',
                'explanation' => 'before_request es un hook de Flask que tiene acceso al contexto de la aplicación (g, session, request). Un middleware WSGI opera a nivel más bajo, envolviendo la aplicación WSGI completa sin acceso a los objetos de Flask. before_request es más conveniente; WSGI middleware es más portátil.',
                'options'     => [
                    ['text' => 'before_request opera dentro del contexto Flask; WSGI middleware envuelve la app a nivel más bajo sin acceso a objetos Flask', 'correct' => true],
                    ['text' => 'Son exactamente iguales; before_request es syntactic sugar de WSGI', 'correct' => false],
                    ['text' => 'before_request solo intercepta GET; WSGI intercepta todos los métodos', 'correct' => false],
                    ['text' => 'WSGI middleware solo funciona en producción, no en desarrollo', 'correct' => false],
                ],
            ],

            // ── 11 · Manejo de errores ────────────────────────────────
            [
                'question'    => '¿Qué ventaja ofrece usar `@app.errorhandler(404)` en lugar de manejar errores con try/except en cada vista?',
                'explanation' => 'errorhandler centraliza el manejo de errores HTTP en un solo lugar, evitando duplicación de código. Se ejecuta automáticamente cuando cualquier vista retorna o lanza ese error. Además, permite personalizar la respuesta de error (JSON para API, HTML para web) de forma consistente.',
                'options'     => [
                    ['text' => 'Centraliza el manejo de errores HTTP, evitando duplicación y garantizando respuestas consistentes', 'correct' => true],
                    ['text' => 'Es más rápido porque evita el overhead de try/except', 'correct' => false],
                    ['text' => 'Solo funciona con errores 404, no con otros códigos HTTP', 'correct' => false],
                    ['text' => 'Impide que la aplicación se caiga por completo ante cualquier error', 'correct' => false],
                ],
            ],

            // ── 12 · Archivos y uploads ───────────────────────────────
            [
                'question'    => '¿Por qué se debe usar `secure_filename()` de Werkzeug al guardar archivos subidos?',
                'explanation' => 'Los usuarios pueden enviar nombres de archivo maliciosos como "../../../etc/passwd" o "cmd.exe". secure_filename() sanitiza el nombre eliminando caracteres peligrosos, resolviendo path traversal, y normalizando el nombre para que sea seguro en cualquier sistema de archivos.',
                'options'     => [
                    ['text' => 'Previene path traversal y nombres maliciosos eliminando caracteres peligrosos del nombre de archivo', 'correct' => true],
                    ['text' => 'Comprime el archivo para que ocupe menos espacio', 'correct' => false],
                    ['text' => 'Verifica que el contenido del archivo no contenga virus', 'correct' => false],
                    ['text' => 'Convierte el archivo a un formato seguro como PDF', 'correct' => false],
                ],
            ],

            // ── 13 · Testing ──────────────────────────────────────────
            [
                'question'    => '¿Qué proporciona el test client de Flask (`app.test_client()`) para las pruebas?',
                'explanation' => 'El test client simula peticiones HTTP (GET, POST, etc.) sin necesidad de un servidor real ni conexión de red. Ejecuta la aplicación WSGI internamente y retorna objetos Response completos con status_code, data y headers, permitiendo tests rápidos y aislados.',
                'options'     => [
                    ['text' => 'Simula peticiones HTTP sin servidor real, ejecutando la app WSGI internamente con responses completas', 'correct' => true],
                    ['text' => 'Levanta un servidor real en un puerto aleatorio para hacer peticiones reales', 'correct' => false],
                    ['text' => 'Solo permite testear templates, no rutas ni respuestas', 'correct' => false],
                    ['text' => 'Genera tests automáticos basados en las rutas definidas', 'correct' => false],
                ],
            ],

            // ── 14 · Seguridad ────────────────────────────────────────
            [
                'question'    => '¿Cómo previene Flask los ataques CSRF por defecto?',
                'explanation' => 'Flask por sí solo NO previene CSRF. Se necesita Flask-WTF que genera un token CSRF único por sesión, lo incluye como campo oculto en formularios, y lo valida en cada POST. Sin Flask-WTF o implementación manual, las aplicaciones Flask son vulnerables a CSRF.',
                'options'     => [
                    ['text' => 'Flask no previene CSRF por defecto; se necesita Flask-WTF que genera y valida tokens CSRF por sesión', 'correct' => true],
                    ['text' => 'Flask bloquea automáticamente todas las peticiones POST de otros dominios', 'correct' => false],
                    ['text' => 'El SECRET_KEY de Flask protege automáticamente contra CSRF', 'correct' => false],
                    ['text' => 'Flask usa headers SameSite en todas las cookies por defecto', 'correct' => false],
                ],
            ],

            // ── 15 · Caché y rendimiento ──────────────────────────────
            [
                'question'    => '¿Cuándo es apropiado usar `@cache.cached()` vs `@cache.memoize()` en Flask-Caching?',
                'explanation' => 'cached() cachea por URL/ruta (ideal para vistas que siempre retornan lo mismo). memoize() cachea por argumentos de función (ideal para funciones con diferentes inputs). Si una vista depende de query params, memoize es mejor; si es estática, cached es suficiente.',
                'options'     => [
                    ['text' => 'cached() cachea por ruta (vistas estáticas); memoize() cachea por argumentos (funciones con distintos inputs)', 'correct' => true],
                    ['text' => 'cached() es para desarrollo y memoize() para producción', 'correct' => false],
                    ['text' => 'Son idénticos; memoize es un alias de cached', 'correct' => false],
                    ['text' => 'cached() usa Redis y memoize() usa memoria local', 'correct' => false],
                ],
            ],

            // ── 16 · WebSockets ───────────────────────────────────────
            [
                'question'    => '¿Qué biblioteca se usa típicamente para WebSockets en Flask y cómo se diferencia de HTTP normal?',
                'explanation' => 'Flask-SocketIO permite comunicación bidireccional en tiempo real. A diferencia de HTTP (request-response), WebSockets mantienen una conexión persistente donde tanto cliente como servidor pueden enviar mensajes en cualquier momento. Es ideal para chat, notificaciones y datos en tiempo real.',
                'options'     => [
                    ['text' => 'Flask-SocketIO; a diferencia de HTTP request-response, WebSockets mantienen conexión persistente bidireccional', 'correct' => true],
                    ['text' => 'Flask-WebSocket; funciona igual que HTTP pero más rápido', 'correct' => false],
                    ['text' => 'No es posible usar WebSockets en Flask', 'correct' => false],
                    ['text' => 'Requests; WebSockets es solo HTTP con polling frecuente', 'correct' => false],
                ],
            ],

            // ── 17 · Tareas en segundo plano ──────────────────────────
            [
                'question'    => '¿Cuál es el riesgo de ejecutar tareas largas directamente en una vista Flask sin usar Celery o similar?',
                'explanation' => 'Las vistas Flask se ejecutan en el hilo del worker WSGI. Una tarea larga (email, procesamiento, API externa) bloquea ese worker, impidiendo que atienda otras peticiones. Con pocos workers, esto puede causar que la aplicación parezca caída. Celery/RQ ejecutan estas tareas en procesos separados.',
                'options'     => [
                    ['text' => 'Bloquea el worker WSGI, impidiendo atender otras peticiones y pudiendo causar timeouts', 'correct' => true],
                    ['text' => 'Flask cancela automáticamente tareas que toman más de 1 segundo', 'correct' => false],
                    ['text' => 'No hay riesgo; Flask es asíncrono por defecto', 'correct' => false],
                    ['text' => 'La tarea se ejecuta pero los datos se corrompen', 'correct' => false],
                ],
            ],

            // ── 18 · Deploy y producción ──────────────────────────────
            [
                'question'    => '¿Por qué no se debe usar `app.run(debug=True)` en producción?',
                'explanation' => 'El servidor de desarrollo de Flask (Werkzeug) es mono-hilo, no optimizado para rendimiento, y con debug=True expone un debugger interactivo que permite ejecutar código Python arbitrario en el servidor. En producción se debe usar Gunicorn/uWSGI detrás de Nginx, con debug=False.',
                'options'     => [
                    ['text' => 'El debugger interactivo permite ejecutar código arbitrario, y el servidor de desarrollo no soporta concurrencia', 'correct' => true],
                    ['text' => 'Porque debug=True hace que Flask sea más lento', 'correct' => false],
                    ['text' => 'Porque Flask no funciona sin un proxy reverso', 'correct' => false],
                    ['text' => 'No hay problema real; es solo una recomendación de estilo', 'correct' => false],
                ],
            ],

            // ── 19 · Preguntas de entrevista ──────────────────────────
            [
                'question'    => '¿Qué es el contexto de aplicación (`app_context`) y el contexto de request (`request_context`) en Flask?',
                'explanation' => 'Flask usa context locals para evitar pasar app/request a cada función. El app_context (current_app, g) existe mientras la app se ejecuta. El request_context (request, session) existe durante una petición HTTP. Fuera de una petición (ej: CLI, tests), se debe crear explícitamente con app.app_context().',
                'options'     => [
                    ['text' => 'Son context locals: app_context da acceso a current_app/g; request_context a request/session, existiendo solo durante una petición', 'correct' => true],
                    ['text' => 'Son configuraciones diferentes del archivo .env', 'correct' => false],
                    ['text' => 'app_context es para producción y request_context para desarrollo', 'correct' => false],
                    ['text' => 'Son sinónimos que se refieren a la misma funcionalidad', 'correct' => false],
                ],
            ],

            // ── 20 · Pregunta integradora ─────────────────────────────
            [
                'question'    => '¿Cuál es la principal ventaja de usar el Application Factory pattern (`create_app()`) en Flask?',
                'explanation' => 'El factory pattern evita variables globales permitiendo crear múltiples instancias de la app con diferentes configuraciones. Esto es esencial para testing (app de test vs producción), para evitar imports circulares, y para configurar la app según el entorno (dev/staging/prod) dinámicamente.',
                'options'     => [
                    ['text' => 'Permite crear múltiples instancias con diferentes configs, evitando globals e imports circulares', 'correct' => true],
                    ['text' => 'Mejora el rendimiento porque compila la app a bytecode optimizado', 'correct' => false],
                    ['text' => 'Es obligatorio para usar blueprints en Flask', 'correct' => false],
                    ['text' => 'Solo sirve para generar la documentación automática de la API', 'correct' => false],
                ],
            ],
        ];
    }
}
