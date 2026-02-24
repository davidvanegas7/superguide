<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class BackendConceptsQuizSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'backend-conceptos')->first();

        if (!$course) {
            $this->command->warn('Curso backend-conceptos no encontrado. Ejecuta CourseSeeder primero.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id],
            [
                'title'       => 'Evaluación: Conceptos de Backend',
                'description' => 'Pon a prueba tu conocimiento de POO, SOLID, patrones de diseño, bases de datos, ORM, arquitectura, seguridad, caché y más.',
                'published'   => true,
            ]
        );

        $quiz->questions()->each(fn ($q) => $q->options()->delete());
        $quiz->questions()->delete();

        $questions = [
            // 1 — POO Fundamentos
            [
                'question'    => '¿Cuál es el propósito principal del encapsulamiento en POO?',
                'explanation' => 'El encapsulamiento oculta los detalles de implementación interna y expone solo una interfaz pública controlada, protegiendo la integridad del objeto.',
                'options'     => [
                    ['text' => 'Ocultar datos internos y controlar el acceso mediante una interfaz pública', 'correct' => true],
                    ['text' => 'Permitir que una clase herede métodos de múltiples clases padre',            'correct' => false],
                    ['text' => 'Reutilizar código entre clases del mismo módulo',                            'correct' => false],
                    ['text' => 'Crear instancias de clase más rápidamente',                                  'correct' => false],
                ],
            ],
            // 2 — POO Pilares
            [
                'question'    => 'En TypeScript, ¿qué ocurre cuando una clase hija redefine un método de la clase padre?',
                'explanation' => 'Cuando una clase hija redefine un método heredado, se habla de sobreescritura (override). El polimorfismo permite llamar al método del objeto real en tiempo de ejecución, no del tipo de referencia.',
                'options'     => [
                    ['text' => 'El método de la clase hija sobreescribe al del padre (polimorfismo en tiempo de ejecución)', 'correct' => true],
                    ['text' => 'Ambos métodos coexisten y se llaman según el tipo de la variable',                           'correct' => false],
                    ['text' => 'El compilador lanza un error porque los métodos no pueden tener el mismo nombre',            'correct' => false],
                    ['text' => 'El método del padre tiene prioridad sobre el de la hija',                                    'correct' => false],
                ],
            ],
            // 3 — SOLID
            [
                'question'    => '¿Cuál de los siguientes escenarios es un ejemplo de violación del Principio de Responsabilidad Única (SRP)?',
                'explanation' => 'SRP establece que una clase debe tener una sola razón para cambiar. Una clase que valida usuarios Y envía emails tiene dos responsabilidades distintas y debe dividirse.',
                'options'     => [
                    ['text' => 'Una clase UserService que valida datos del usuario y también envía emails de confirmación', 'correct' => true],
                    ['text' => 'Una interfaz con un solo método abstracto',                                                 'correct' => false],
                    ['text' => 'Una función de utilidad que formatea fechas',                                               'correct' => false],
                    ['text' => 'Una clase Repository que contiene métodos CRUD para una sola entidad',                     'correct' => false],
                ],
            ],
            // 4 — Patrones Creacionales
            [
                'question'    => '¿Cuál es la diferencia clave entre el patrón Factory Method y Abstract Factory?',
                'explanation' => 'Factory Method crea un solo tipo de objeto mediante un método que las subclases sobreescriben. Abstract Factory crea familias de objetos relacionados a través de una interfaz con múltiples métodos de creación.',
                'options'     => [
                    ['text' => 'Factory Method crea un tipo de objeto; Abstract Factory crea familias de objetos relacionados', 'correct' => true],
                    ['text' => 'Abstract Factory es simplemente Factory Method con más parámetros',                             'correct' => false],
                    ['text' => 'Factory Method requiere interfaces mientras Abstract Factory usa clases abstractas',            'correct' => false],
                    ['text' => 'Ambos patrones son intercambiables y resuelven exactamente el mismo problema',                 'correct' => false],
                ],
            ],
            // 5 — Patrones Estructurales
            [
                'question'    => '¿Qué ventaja principal ofrece el patrón Repository sobre el acceso directo a la base de datos?',
                'explanation' => 'El patrón Repository abstrae la capa de acceso a datos, permitiendo cambiar la implementación (MySQL → PostgreSQL → InMemory para tests) sin modificar la lógica de negocio.',
                'options'     => [
                    ['text' => 'Abstrae el almacenamiento, permitiendo cambiar la implementación sin alterar la lógica de negocio', 'correct' => true],
                    ['text' => 'Genera automáticamente las consultas SQL a partir de los modelos',                                  'correct' => false],
                    ['text' => 'Mejora el rendimiento al cachear todas las consultas en memoria',                                   'correct' => false],
                    ['text' => 'Garantiza que los datos se persisten de forma transaccional',                                       'correct' => false],
                ],
            ],
            // 6 — Patrones de Comportamiento
            [
                'question'    => '¿En qué se diferencia el patrón Strategy del patrón State?',
                'explanation' => 'Strategy permite elegir un algoritmo intercambiable en tiempo de ejecución (el contexto no cambia). State permite que un objeto cambie su comportamiento cuando cambia su estado interno; los estados se auto-transicionan.',
                'options'     => [
                    ['text' => 'Strategy elige un algoritmo intercambiable; State cambia comportamiento según el estado interno del objeto', 'correct' => true],
                    ['text' => 'Son exactamente iguales; solo difieren en el nombre',                                                       'correct' => false],
                    ['text' => 'Strategy se usa para estructuras de datos; State para flujos de negocio',                                   'correct' => false],
                    ['text' => 'State no permite que el objeto cambie su propio estado',                                                    'correct' => false],
                ],
            ],
            // 7 — Bases de Datos Relacionales
            [
                'question'    => 'En 3NF (Tercera Forma Normal), ¿qué tipo de dependencia se debe eliminar?',
                'explanation' => 'La 3NF elimina las dependencias transitivas: cuando un atributo no clave depende de otro atributo no clave (en lugar de depender directamente de la clave primaria).',
                'options'     => [
                    ['text' => 'Dependencias transitivas (atributo no clave → otro atributo no clave → clave primaria)', 'correct' => true],
                    ['text' => 'Grupos repetitivos de columnas en la misma fila',                                        'correct' => false],
                    ['text' => 'Dependencias parciales de atributos a la clave primaria compuesta',                      'correct' => false],
                    ['text' => 'Claves foráneas circulares entre tablas',                                                'correct' => false],
                ],
            ],
            // 8 — SQL Consultas
            [
                'question'    => '¿Cuál es la diferencia entre INNER JOIN y LEFT JOIN?',
                'explanation' => 'INNER JOIN devuelve solo las filas que tienen coincidencias en ambas tablas. LEFT JOIN devuelve todas las filas de la tabla izquierda, más las coincidencias de la derecha (NULL si no hay coincidencia).',
                'options'     => [
                    ['text' => 'INNER JOIN solo muestra filas con coincidencia en ambas tablas; LEFT JOIN muestra todas las filas de la tabla izquierda', 'correct' => true],
                    ['text' => 'LEFT JOIN es más rápido que INNER JOIN en todos los casos',                                                               'correct' => false],
                    ['text' => 'INNER JOIN incluye valores NULL para filas sin coincidencia',                                                             'correct' => false],
                    ['text' => 'No hay diferencia práctica entre ambos tipos de JOIN',                                                                    'correct' => false],
                ],
            ],
            // 9 — Transacciones ACID
            [
                'question'    => '¿Qué problema de concurrencia ocurre cuando una transacción lee datos que han sido modificados pero no confirmados por otra transacción?',
                'explanation' => 'El "dirty read" ocurre cuando una transacción lee datos sucios (no confirmados) de otra transacción. Si esa transacción hace rollback, los datos leídos nunca existieron realmente.',
                'options'     => [
                    ['text' => 'Dirty Read (lectura sucia)',             'correct' => true],
                    ['text' => 'Non-Repeatable Read (lectura no repetible)', 'correct' => false],
                    ['text' => 'Phantom Read (lectura fantasma)',        'correct' => false],
                    ['text' => 'Lost Update (actualización perdida)',    'correct' => false],
                ],
            ],
            // 10 — ORM
            [
                'question'    => '¿Qué es el problema N+1 en el contexto de un ORM?',
                'explanation' => 'El problema N+1 ocurre cuando se hace 1 consulta para obtener N registros y luego N consultas adicionales para cargar la relación de cada registro, resultando en N+1 consultas en total.',
                'options'     => [
                    ['text' => 'Se realiza 1 consulta para obtener N registros y luego N consultas más para cargar sus relaciones', 'correct' => true],
                    ['text' => 'El ORM genera queries con N condiciones WHERE adicionales innecesarias',                            'correct' => false],
                    ['text' => 'Una entidad tiene más de N relaciones definidas en el modelo',                                      'correct' => false],
                    ['text' => 'La base de datos devuelve N filas duplicadas por cada query ejecutada',                            'correct' => false],
                ],
            ],
            // 11 — Arquitectura en Capas
            [
                'question'    => 'En Clean Architecture, ¿en qué dirección fluyen las dependencias?',
                'explanation' => 'La regla de dependencia de Clean Architecture establece que el código debe depender hacia adentro: los frameworks dependen de adaptadores, que dependen de casos de uso, que dependen de entidades. Las entidades no conocen nada externo.',
                'options'     => [
                    ['text' => 'Hacia adentro: las capas externas dependen de las internas, nunca al revés', 'correct' => true],
                    ['text' => 'Hacia afuera: las entidades dependen de los frameworks externos',           'correct' => false],
                    ['text' => 'Bidireccional: todas las capas se conocen entre sí',                        'correct' => false],
                    ['text' => 'No importa la dirección siempre que se use inyección de dependencias',      'correct' => false],
                ],
            ],
            // 12 — Inyección de Dependencias
            [
                'question'    => '¿Cuál es la diferencia entre un servicio "singleton" y un servicio "transient" en un contenedor IoC?',
                'explanation' => 'Singleton: el contenedor crea una sola instancia y la reutiliza en todas las resoluciones. Transient: el contenedor crea una nueva instancia cada vez que se solicita.',
                'options'     => [
                    ['text' => 'Singleton: una instancia compartida siempre; Transient: nueva instancia en cada resolución', 'correct' => true],
                    ['text' => 'Singleton: se destruye al finalizar la petición; Transient: vive durante toda la aplicación', 'correct' => false],
                    ['text' => 'Singleton solo puede tener dependencias de tipo singleton',                                  'correct' => false],
                    ['text' => 'Transient es más eficiente en memoria que singleton en todos los casos',                     'correct' => false],
                ],
            ],
            // 13 — API REST
            [
                'question'    => 'Según las convenciones REST, ¿qué código de estado HTTP se debe devolver cuando se crea un recurso correctamente?',
                'explanation' => '201 Created es el código correcto para la creación exitosa de un recurso. Debe ir acompañado del encabezado Location con la URL del nuevo recurso.',
                'options'     => [
                    ['text' => '201 Created',      'correct' => true],
                    ['text' => '200 OK',            'correct' => false],
                    ['text' => '204 No Content',    'correct' => false],
                    ['text' => '202 Accepted',      'correct' => false],
                ],
            ],
            // 14 — Seguridad
            [
                'question'    => '¿Por qué se usa bcrypt en lugar de SHA-256 para hashear contraseñas?',
                'explanation' => 'bcrypt es intencionalmente lento (tiene un "cost factor" ajustable) y genera un salt aleatorio automáticamente. SHA-256 es rápido (ideal para integridad de datos, no para contraseñas) y susceptible a ataques de rainbow tables.',
                'options'     => [
                    ['text' => 'bcrypt es lento por diseño (brute-force resistente) e incluye salt automático; SHA-256 es rápido y vulnerable a rainbow tables', 'correct' => true],
                    ['text' => 'bcrypt produce hashes más cortos que ocupan menos espacio en la base de datos',                                                   'correct' => false],
                    ['text' => 'SHA-256 no es determinista, por eso no se puede usar para contraseñas',                                                          'correct' => false],
                    ['text' => 'bcrypt es un algoritmo de cifrado simétrico que permite revertir el hash',                                                       'correct' => false],
                ],
            ],
            // 15 — Caché
            [
                'question'    => '¿En qué consiste la estrategia de caché "cache-aside" (lazy loading)?',
                'explanation' => 'En cache-aside, la aplicación gestiona la caché manualmente: primero busca en caché, si no encuentra (miss) va a la base de datos, guarda el resultado en caché y lo devuelve. La caché no se actualiza automáticamente.',
                'options'     => [
                    ['text' => 'La aplicación busca en caché primero; si hay miss, lee de la BD, escribe en caché y retorna', 'correct' => true],
                    ['text' => 'Cada escritura en la BD actualiza automáticamente la caché al mismo tiempo',                  'correct' => false],
                    ['text' => 'Los datos se escriben primero en caché y luego de forma asíncrona en la BD',                  'correct' => false],
                    ['text' => 'La caché precarga todos los datos al iniciar la aplicación',                                  'correct' => false],
                ],
            ],
            // 16 — Mensajería
            [
                'question'    => '¿Qué problema resuelve el patrón Outbox Pattern en sistemas de mensajería?',
                'explanation' => 'El Outbox Pattern resuelve la dualidad de escritura: garantiza que si guardas un registro en la BD, el evento asociado también se publicará, aunque el mensaje broker falle. La tabla outbox actúa como buffer transaccional.',
                'options'     => [
                    ['text' => 'Garantiza consistencia entre guardar en BD y publicar en el message broker dentro de la misma transacción', 'correct' => true],
                    ['text' => 'Mejora el rendimiento al procesar mensajes en lotes en lugar de uno a uno',                               'correct' => false],
                    ['text' => 'Permite que los consumidores procesen mensajes en orden LIFO en vez de FIFO',                             'correct' => false],
                    ['text' => 'Elimina la necesidad de un message broker externo usando solo la base de datos',                         'correct' => false],
                ],
            ],
            // 17 — Testing
            [
                'question'    => '¿Qué diferencia hay entre un "mock" y un "stub" en testing?',
                'explanation' => 'Un stub proporciona respuestas predefinidas a las llamadas (controla el estado). Un mock también verifica que las llamadas se produjeron con los parámetros correctos (verifica comportamiento/interacciones).',
                'options'     => [
                    ['text' => 'Un stub devuelve datos predefinidos; un mock además verifica que se llamó correctamente (verifica interacciones)', 'correct' => true],
                    ['text' => 'Un mock es una implementación real; un stub es una implementación falsa',                                         'correct' => false],
                    ['text' => 'Son términos sinónimos que describen lo mismo',                                                                   'correct' => false],
                    ['text' => 'Los stubs se usan en tests unitarios; los mocks solo en tests de integración',                                    'correct' => false],
                ],
            ],
            // 18 — Performance
            [
                'question'    => '¿Por qué la paginación por cursor es más eficiente que la paginación por OFFSET en tablas grandes?',
                'explanation' => 'OFFSET obliga a la BD a leer y descartar las primeras N filas en cada consulta (O(N) por página). El cursor usa el índice del último ID visto y solo lee desde ahí (O(log N)), siendo mucho más eficiente en tablas grandes.',
                'options'     => [
                    ['text' => 'El cursor usa el índice directamente (O(log N)); OFFSET debe escanear y descartar filas previas (O(N))', 'correct' => true],
                    ['text' => 'La paginación por cursor ocupa menos memoria RAM en el servidor',                                        'correct' => false],
                    ['text' => 'OFFSET no funciona en bases de datos NoSQL mientras el cursor sí',                                       'correct' => false],
                    ['text' => 'El cursor permite saltar a cualquier página arbitraria más rápido que OFFSET',                           'correct' => false],
                ],
            ],
            // 19 — Concurrencia
            [
                'question'    => '¿Cuándo es preferible usar Optimistic Locking sobre Pessimistic Locking?',
                'explanation' => 'Optimistic Locking es preferible cuando los conflictos son poco frecuentes (baja contención). Evita bloquear filas y mejora el rendimiento en escenarios donde la mayoría de las operaciones tienen éxito sin conflicto.',
                'options'     => [
                    ['text' => 'Cuando los conflictos son poco frecuentes (baja contención), para evitar bloquear recursos innecesariamente', 'correct' => true],
                    ['text' => 'Cuando múltiples procesos actualizan el mismo registro muy frecuentemente',                                   'correct' => false],
                    ['text' => 'Cuando se necesita garantizar que ninguna transacción sea rechazada por conflicto',                          'correct' => false],
                    ['text' => 'Cuando la base de datos no soporta transacciones ACID nativas',                                              'correct' => false],
                ],
            ],
            // 20 — Repaso General
            [
                'question'    => '¿Cuál de los siguientes describe correctamente la regla de Liskov Substitution Principle (LSP)?',
                'explanation' => 'LSP establece que si S es un subtipo de T, los objetos de tipo T deberían poder ser reemplazados por objetos de tipo S sin alterar el comportamiento correcto del programa. Viola LSP cuando una subclase rompe las precondiciones o postcondiciones de la clase base.',
                'options'     => [
                    ['text' => 'Las subclases deben poder sustituir a su clase base sin alterar el comportamiento esperado del programa', 'correct' => true],
                    ['text' => 'Una clase debe depender de abstracciones, no de implementaciones concretas',                             'correct' => false],
                    ['text' => 'Una clase debe estar cerrada para modificación pero abierta para extensión',                             'correct' => false],
                    ['text' => 'Los clientes no deben depender de interfaces que no utilizan',                                           'correct' => false],
                ],
            ],
        ];

        foreach ($questions as $i => $qData) {
            $question = QuizQuestion::create([
                'quiz_id'     => $quiz->id,
                'question'    => $qData['question'],
                'explanation' => $qData['explanation'],
                'sort_order'  => $i + 1,
            ]);

            foreach ($qData['options'] as $j => $opt) {
                QuizOption::create([
                    'quiz_question_id' => $question->id,
                    'text'             => $opt['text'],
                    'is_correct'       => $opt['correct'],
                    'sort_order'       => $j + 1,
                ]);
            }
        }

        $this->command->info("Backend Concepts quiz: {$quiz->questions()->count()} preguntas cargadas.");
    }
}
