<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class DjangoQuizSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'django-fullstack')->first();

        if (! $course) {
            $this->command->warn('Django course not found. Run CourseSeeder first.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id],
            [
                'title'       => 'Evaluación: Django Fullstack',
                'description' => 'Pon a prueba tus conocimientos sobre Django: modelos, vistas, templates, ORM, REST Framework, testing, seguridad y deploy.',
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

        $this->command->info("Django quiz seeded: {$quiz->questions()->count()} preguntas.");
    }

    private function questions(): array
    {
        return [

            // ── 1 · Introducción a Django ─────────────────────────────
            [
                'question'    => '¿Qué patrón arquitectónico utiliza Django y en qué se diferencia de MVC?',
                'explanation' => 'Django sigue el patrón MVT (Model-View-Template). A diferencia de MVC, en Django la "View" maneja la lógica (equivalente al Controller de MVC), y el "Template" maneja la presentación (equivalente a la View de MVC). El framework actúa como el Controller que conecta URLs con Views.',
                'options'     => [
                    ['text' => 'MVT: Model-View-Template, donde la View de Django equivale al Controller de MVC y el Template a la View', 'correct' => true],
                    ['text' => 'MVC puro: Model-View-Controller sin ninguna variación', 'correct' => false],
                    ['text' => 'MVVM: Model-View-ViewModel como Angular o WPF', 'correct' => false],
                    ['text' => 'Django no sigue ningún patrón arquitectónico definido', 'correct' => false],
                ],
            ],

            // ── 2 · Modelos y ORM ─────────────────────────────────────
            [
                'question'    => '¿Qué hace `Meta: ordering = ["-created_at"]` en un modelo Django?',
                'explanation' => 'La clase Meta con ordering define el orden por defecto de los querysets de ese modelo. El prefijo "-" indica orden descendente. Esto afecta a todas las consultas que no especifiquen .order_by() explícitamente, incluyendo el admin. Puede impactar el rendimiento si no hay índice en el campo.',
                'options'     => [
                    ['text' => 'Define el orden descendente por created_at como default para todos los querysets del modelo', 'correct' => true],
                    ['text' => 'Crea automáticamente un índice descendente en la base de datos', 'correct' => false],
                    ['text' => 'Es solo documentación, no afecta las consultas reales', 'correct' => false],
                    ['text' => 'Ordena los campos del modelo en el código fuente', 'correct' => false],
                ],
            ],

            // ── 3 · Vistas y URLs ─────────────────────────────────────
            [
                'question'    => '¿Cuál es la ventaja de usar vistas basadas en clase (CBV) sobre vistas basadas en función (FBV)?',
                'explanation' => 'Las CBV permiten reutilizar lógica mediante herencia y mixins. Django incluye generic views (ListView, DetailView, CreateView) que implementan patrones CRUD comunes con mínimo código. Las FBV son más explícitas y simples para lógica personalizada. La elección depende de la complejidad.',
                'options'     => [
                    ['text' => 'Permiten reutilizar lógica CRUD con herencia, mixins y generic views predefinidas', 'correct' => true],
                    ['text' => 'Son siempre más rápidas que las FBV porque se compilan', 'correct' => false],
                    ['text' => 'Las FBV están deprecadas en Django moderno', 'correct' => false],
                    ['text' => 'Las CBV no necesitan URLs, se auto-registran', 'correct' => false],
                ],
            ],

            // ── 4 · Templates ─────────────────────────────────────────
            [
                'question'    => '¿Cómo funciona la herencia de templates en Django con `{% extends %}` y `{% block %}`?',
                'explanation' => 'El template hijo usa {% extends "base.html" %} para heredar la estructura del padre. Los bloques ({% block content %}) definen zonas reemplazables. El hijo solo puede redefinir bloques existentes; todo lo que esté fuera de un block en el hijo se ignora. Se puede usar {{ block.super }} para incluir el contenido del padre.',
                'options'     => [
                    ['text' => 'extends hereda la estructura base; block define zonas que el hijo puede reemplazar; block.super incluye el contenido del padre', 'correct' => true],
                    ['text' => 'extends copia el archivo HTML completo y block agrega contenido al final', 'correct' => false],
                    ['text' => 'Son iguales a include; solo cambia la sintaxis', 'correct' => false],
                    ['text' => 'extends solo funciona con un nivel de herencia, no se puede encadenar', 'correct' => false],
                ],
            ],

            // ── 5 · Formularios ───────────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre `Form` y `ModelForm` en Django?',
                'explanation' => 'Form requiere definir cada campo manualmente y no está vinculado a ningún modelo. ModelForm genera campos automáticamente a partir de un modelo Django, incluye validación basada en el modelo, y tiene el método save() para crear/actualizar instancias del modelo directamente.',
                'options'     => [
                    ['text' => 'ModelForm genera campos desde un modelo, incluye validación y save() para persistir; Form define campos manualmente sin vínculo a modelo', 'correct' => true],
                    ['text' => 'Form es para Django y ModelForm es para Django REST Framework', 'correct' => false],
                    ['text' => 'ModelForm solo funciona con formularios de creación, no de edición', 'correct' => false],
                    ['text' => 'No hay diferencia; ModelForm es un alias de Form', 'correct' => false],
                ],
            ],

            // ── 6 · Admin ─────────────────────────────────────────────
            [
                'question'    => '¿Qué personalización permite `list_display`, `list_filter` y `search_fields` en ModelAdmin?',
                'explanation' => 'list_display controla qué columnas se muestran en la lista. list_filter agrega filtros laterales por campo. search_fields habilita búsqueda por texto en los campos especificados. Juntos transforman el admin genérico en una interfaz de gestión potente sin escribir HTML.',
                'options'     => [
                    ['text' => 'list_display define columnas visibles, list_filter agrega filtros laterales, search_fields habilita búsqueda por texto', 'correct' => true],
                    ['text' => 'Solo cambian la apariencia CSS del admin, no la funcionalidad', 'correct' => false],
                    ['text' => 'list_display y list_filter son equivalentes, solo cambia la posición', 'correct' => false],
                    ['text' => 'Estas opciones solo funcionan con la versión premium de Django', 'correct' => false],
                ],
            ],

            // ── 7 · Relaciones y queries ──────────────────────────────
            [
                'question'    => '¿Cuándo se debe usar `select_related` vs `prefetch_related` en Django ORM?',
                'explanation' => 'select_related usa JOIN SQL y funciona con ForeignKey y OneToOne (una sola query). prefetch_related hace queries separadas y une en Python, funcionando con ManyToMany y relaciones inversas. Usar el incorrecto puede empeorar el rendimiento: JOIN con ManyToMany puede multiplicar filas.',
                'options'     => [
                    ['text' => 'select_related para ForeignKey/OneToOne (JOIN SQL); prefetch_related para ManyToMany y relaciones inversas (queries separadas)', 'correct' => true],
                    ['text' => 'select_related es para lectura y prefetch_related es para escritura', 'correct' => false],
                    ['text' => 'Son intercambiables; Django elige automáticamente el mejor', 'correct' => false],
                    ['text' => 'select_related carga lazy; prefetch_related carga eager', 'correct' => false],
                ],
            ],

            // ── 8 · Autenticación ─────────────────────────────────────
            [
                'question'    => '¿Cuál es la forma recomendada de extender el modelo User de Django?',
                'explanation' => 'La forma recomendada es AbstractUser (herencia) si se necesita cambiar campos, o un modelo Profile con OneToOneField para agregar campos sin modificar User. AbstractBaseUser da control total pero requiere implementar más. Cambiar el modelo User después de migrar es complejo.',
                'options'     => [
                    ['text' => 'AbstractUser para modificar campos, Profile con OneToOneField para agregar campos; definir AUTH_USER_MODEL al inicio del proyecto', 'correct' => true],
                    ['text' => 'Editar directamente el código fuente de django.contrib.auth.models.User', 'correct' => false],
                    ['text' => 'Crear un modelo completamente nuevo sin relación con el auth de Django', 'correct' => false],
                    ['text' => 'Usar monkey-patching para agregar campos al User en runtime', 'correct' => false],
                ],
            ],

            // ── 9 · Middleware ─────────────────────────────────────────
            [
                'question'    => '¿En qué orden se ejecutan los middleware de Django y por qué importa?',
                'explanation' => 'Los middleware se ejecutan en orden de MIDDLEWARE para la request (de arriba a abajo) y en orden inverso para la response (de abajo a arriba). SecurityMiddleware debe ir primero para aplicar HTTPS temprano. AuthenticationMiddleware debe ir después de SessionMiddleware porque necesita la sesión para identificar al usuario.',
                'options'     => [
                    ['text' => 'Request: de arriba a abajo; Response: de abajo a arriba. El orden importa por dependencias entre middleware', 'correct' => true],
                    ['text' => 'Se ejecutan en paralelo para máximo rendimiento', 'correct' => false],
                    ['text' => 'El orden no importa; Django los reordena automáticamente', 'correct' => false],
                    ['text' => 'Solo el primer y último middleware se ejecutan; los intermedios son opcionales', 'correct' => false],
                ],
            ],

            // ── 10 · Archivos estáticos ───────────────────────────────
            [
                'question'    => '¿Qué hace `collectstatic` y por qué es necesario en producción?',
                'explanation' => 'collectstatic recopila todos los archivos estáticos de cada app y de STATICFILES_DIRS en un solo directorio (STATIC_ROOT). En producción, Nginx/Apache sirve estos archivos directamente, mucho más rápido que Django. En desarrollo, django.contrib.staticfiles los sirve automáticamente.',
                'options'     => [
                    ['text' => 'Recopila estáticos de todas las apps en STATIC_ROOT para que Nginx/Apache los sirva directamente en producción', 'correct' => true],
                    ['text' => 'Comprime y minifica automáticamente CSS y JavaScript', 'correct' => false],
                    ['text' => 'Sube los archivos estáticos a un CDN automáticamente', 'correct' => false],
                    ['text' => 'Elimina archivos estáticos no utilizados para ahorrar espacio', 'correct' => false],
                ],
            ],

            // ── 11 · Django REST Framework ────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre `Serializer` y `ModelSerializer` en DRF?',
                'explanation' => 'Serializer requiere definir cada campo y los métodos create/update manualmente. ModelSerializer genera campos automáticamente desde el modelo, incluye validación del modelo, e implementa create/update que llaman a model.save(). ModelSerializer reduce significativamente el código boilerplate.',
                'options'     => [
                    ['text' => 'ModelSerializer genera campos, validación y create/update desde el modelo; Serializer requiere todo manualmente', 'correct' => true],
                    ['text' => 'Serializer es para JSON y ModelSerializer para XML', 'correct' => false],
                    ['text' => 'Son idénticos; ModelSerializer es un alias', 'correct' => false],
                    ['text' => 'Serializer es de Django, ModelSerializer es de una librería externa', 'correct' => false],
                ],
            ],

            // ── 12 · Signals ──────────────────────────────────────────
            [
                'question'    => '¿Cuándo es apropiado usar signals en Django y cuándo se deben evitar?',
                'explanation' => 'Las signals son apropiadas para desacoplar apps que no deberían conocerse mutuamente (ej: app de auditoría). Se deben evitar para lógica que debería estar en el modelo o vista (como enviar emails al crear usuario). El abuso de signals hace el código difícil de depurar y seguir.',
                'options'     => [
                    ['text' => 'Apropiadas para desacoplar apps independientes; evitarlas para lógica que pertenece al modelo o vista directamente', 'correct' => true],
                    ['text' => 'Siempre usar signals en lugar de métodos de modelo para mantener los modelos limpios', 'correct' => false],
                    ['text' => 'Las signals están deprecadas y no deberían usarse en Django moderno', 'correct' => false],
                    ['text' => 'Las signals solo funcionan entre modelos del mismo app', 'correct' => false],
                ],
            ],

            // ── 13 · Celery ───────────────────────────────────────────
            [
                'question'    => '¿Qué problema resuelve Celery en una aplicación Django?',
                'explanation' => 'Celery ejecuta tareas fuera del ciclo request-response de Django: emails, procesamiento de imágenes, llamadas a APIs externas, reportes pesados. Sin Celery, estas tareas bloquean al worker web. Celery usa un broker (Redis/RabbitMQ) para encolar tareas que workers separados procesan.',
                'options'     => [
                    ['text' => 'Ejecuta tareas pesadas asíncronamente fuera del ciclo request-response, usando workers separados con un broker de mensajes', 'correct' => true],
                    ['text' => 'Reemplaza la base de datos de Django por una más rápida', 'correct' => false],
                    ['text' => 'Es un sistema de caché que reemplaza a Redis', 'correct' => false],
                    ['text' => 'Solo sirve para enviar emails programados', 'correct' => false],
                ],
            ],

            // ── 14 · Testing ──────────────────────────────────────────
            [
                'question'    => '¿Qué ventaja ofrece `TestCase` de Django sobre el `TestCase` estándar de Python unittest?',
                'explanation' => 'Django TestCase envuelve cada test en una transacción que se revierte automáticamente (rollback), manteniendo la BD limpia sin borrar datos manualmente. Además provee un Client HTTP de testing, assertions específicas (assertContains, assertRedirects), y carga de fixtures.',
                'options'     => [
                    ['text' => 'Transacciones con rollback automático, Client HTTP de testing, y assertions específicas como assertContains', 'correct' => true],
                    ['text' => 'Es más rápido porque no ejecuta tests realmente, solo los simula', 'correct' => false],
                    ['text' => 'Solo funciona con pytest, no con unittest', 'correct' => false],
                    ['text' => 'Genera reportes de cobertura automáticamente', 'correct' => false],
                ],
            ],

            // ── 15 · Caché ────────────────────────────────────────────
            [
                'question'    => '¿Cuáles son los niveles de caché que ofrece Django?',
                'explanation' => 'Django ofrece caché a nivel de: 1) Sitio completo (CacheMiddleware), 2) Vista individual (@cache_page), 3) Template ({% cache %}), 4) Objeto/query (cache.set/get). El backend puede ser Memcached, Redis, base de datos, filesystem o memoria local. La estrategia correcta depende del patrón de acceso.',
                'options'     => [
                    ['text' => 'Sitio completo (middleware), vista (@cache_page), template ({% cache %}), y programático (cache.set/get)', 'correct' => true],
                    ['text' => 'Solo caché de queries SQL; no hay caché de vistas', 'correct' => false],
                    ['text' => 'Solo caché con Redis; otros backends no están soportados', 'correct' => false],
                    ['text' => 'Django no tiene sistema de caché integrado; se necesita una librería externa', 'correct' => false],
                ],
            ],

            // ── 16 · Seguridad ────────────────────────────────────────
            [
                'question'    => '¿Qué protecciones de seguridad incluye Django por defecto?',
                'explanation' => 'Django incluye: CSRF protection (tokens en formularios), XSS prevention (auto-escaping en templates), SQL injection prevention (ORM parametrizado), clickjacking protection (X-Frame-Options), HTTPS redirect (SecurityMiddleware), y password hashing (PBKDF2). Todo habilitado por defecto.',
                'options'     => [
                    ['text' => 'CSRF tokens, XSS auto-escaping, SQL injection prevention, clickjacking headers, HTTPS redirect y password hashing', 'correct' => true],
                    ['text' => 'Solo protección CSRF; las demás requieren librerías externas', 'correct' => false],
                    ['text' => 'Django no incluye seguridad por defecto; todo se configura manualmente', 'correct' => false],
                    ['text' => 'Solo encriptación de contraseñas; el resto depende del servidor web', 'correct' => false],
                ],
            ],

            // ── 17 · Channels y WebSockets ────────────────────────────
            [
                'question'    => '¿Qué cambio fundamental introduce Django Channels en la arquitectura de Django?',
                'explanation' => 'Django Channels reemplaza el servidor WSGI (síncrono, request-response) por ASGI (asíncrono). Esto permite manejar WebSockets, HTTP/2, y protocolos de larga duración. Los consumers de Channels son el equivalente asíncrono de las views, pero pueden mantener conexiones persistentes.',
                'options'     => [
                    ['text' => 'Reemplaza WSGI por ASGI, permitiendo WebSockets, HTTP/2 y conexiones persistentes con consumers asíncronos', 'correct' => true],
                    ['text' => 'Solo agrega un chat widget al admin de Django', 'correct' => false],
                    ['text' => 'Convierte Django en un framework JavaScript full-stack', 'correct' => false],
                    ['text' => 'Channels hace que Django sea más rápido pero no agrega nuevos protocolos', 'correct' => false],
                ],
            ],

            // ── 18 · Deploy ───────────────────────────────────────────
            [
                'question'    => '¿Cuál es la configuración mínima recomendada para deploy de Django en producción?',
                'explanation' => 'La configuración mínima incluye: 1) Gunicorn/uWSGI como servidor WSGI, 2) Nginx como proxy reverso para estáticos y SSL, 3) PostgreSQL como BD, 4) DEBUG=False, ALLOWED_HOSTS configurado, SECRET_KEY seguro. collectstatic para servir estáticos. Variables de entorno para secrets.',
                'options'     => [
                    ['text' => 'Gunicorn + Nginx + PostgreSQL + DEBUG=False + ALLOWED_HOSTS + SECRET_KEY seguro + collectstatic', 'correct' => true],
                    ['text' => 'Solo ejecutar manage.py runserver en el puerto 80', 'correct' => false],
                    ['text' => 'Django se auto-configura para producción al detectar el entorno', 'correct' => false],
                    ['text' => 'Solo se necesita Docker; no hacen falta más configuraciones', 'correct' => false],
                ],
            ],

            // ── 19 · Preguntas de entrevista ──────────────────────────
            [
                'question'    => '¿Qué es el problema N+1 en Django ORM y cómo se resuelve?',
                'explanation' => 'El N+1 ocurre cuando se accede a relaciones en un loop: 1 query para la lista + N queries individuales para cada relación. Se resuelve con select_related (JOIN para FK/O2O) o prefetch_related (query extra para M2M). django-debug-toolbar ayuda a detectarlo mostrando las queries ejecutadas.',
                'options'     => [
                    ['text' => '1 query para la lista + N queries por relación en loop; se resuelve con select_related/prefetch_related', 'correct' => true],
                    ['text' => 'Es un error de migración que crea N+1 columnas duplicadas', 'correct' => false],
                    ['text' => 'Es un problema de Django Channels, no del ORM', 'correct' => false],
                    ['text' => 'Solo ocurre con SQLite, no con PostgreSQL', 'correct' => false],
                ],
            ],

            // ── 20 · Pregunta integradora ─────────────────────────────
            [
                'question'    => '¿Por qué Django sigue la filosofía "batteries included" y qué implica para el desarrollador?',
                'explanation' => 'Django incluye ORM, admin, auth, forms, migrations, cache, i18n, testing, y más en su core. Esto reduce las decisiones técnicas y el tiempo de setup, asegura que los componentes funcionen bien juntos, y establece convenciones claras. La contrapartida es menos flexibilidad comparado con micro-frameworks.',
                'options'     => [
                    ['text' => 'Incluye todo lo necesario (ORM, admin, auth, etc.) de serie, reduciendo decisiones y garantizando integración', 'correct' => true],
                    ['text' => 'Significa que Django es compatible con todas las baterías y dispositivos IoT', 'correct' => false],
                    ['text' => 'Es un eslogan de marketing sin impacto técnico real', 'correct' => false],
                    ['text' => 'Solo se refiere a que Django incluye documentación extensa', 'correct' => false],
                ],
            ],
        ];
    }
}
