<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class PhpProfessionalQuizSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'php-profesional')->first();

        if (! $course) {
            $this->command->warn('PHP Professional course not found. Run CourseSeeder first.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id],
            [
                'title'       => 'Evaluación: PHP Profesional',
                'description' => 'Evalúa tus conocimientos avanzados de PHP: POO, tipos, patrones, seguridad, rendimiento y PHP 8.x.',
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

        $this->command->info("PHP Professional quiz seeded: {$quiz->questions()->count()} preguntas.");
    }

    private function questions(): array
    {
        return [

            // 1. POO Avanzada
            [
                'question'    => '¿Cuál es la diferencia entre una clase abstracta y una interfaz en PHP 8?',
                'explanation' => 'Una clase abstracta puede tener métodos implementados y propiedades, y una clase solo puede extender UNA. Una interfaz solo define contratos (métodos sin implementación) pero una clase puede implementar MÚLTIPLES interfaces.',
                'options'     => [
                    ['text' => 'Abstract puede tener métodos implementados y solo permite herencia simple; interface solo define contratos pero permite implementación múltiple', 'correct' => true],
                    ['text' => 'No hay diferencia en PHP 8, son sinónimos', 'correct' => false],
                    ['text' => 'Las interfaces pueden tener propiedades, las clases abstractas no', 'correct' => false],
                    ['text' => 'Solo las interfaces pueden tener constructores', 'correct' => false],
                ],
            ],

            // 2. Interfaces y Traits
            [
                'question'    => '¿Cómo se resuelve un conflicto cuando dos traits definen el mismo método?',
                'explanation' => 'Se usa insteadof para elegir qué implementación usar y opcionalmente as para crear un alias del método excluido. Ej: TraitA::method insteadof TraitB; TraitB::method as aliasMethod;',
                'options'     => [
                    ['text' => 'Con insteadof para elegir la implementación y as para crear un alias del método excluido', 'correct' => true],
                    ['text' => 'PHP automáticamente usa el último trait declarado', 'correct' => false],
                    ['text' => 'Se lanza un error y no se puede resolver', 'correct' => false],
                    ['text' => 'Se deben renombrar los métodos antes de usar los traits', 'correct' => false],
                ],
            ],

            // 3. Namespaces
            [
                'question'    => '¿Qué estándar define las reglas de autoloading por namespaces en PHP?',
                'explanation' => 'PSR-4 define que el namespace completo de una clase debe mapear directamente a la estructura de directorios. Ej: App\\Models\\User mapea a src/Models/User.php. Reemplaza a PSR-0.',
                'options'     => [
                    ['text' => 'PSR-4: el namespace mapea directamente a la estructura de directorios', 'correct' => true],
                    ['text' => 'PSR-1: Basic Coding Standard', 'correct' => false],
                    ['text' => 'PSR-12: Extended Coding Style', 'correct' => false],
                    ['text' => 'PSR-7: HTTP Message Interface', 'correct' => false],
                ],
            ],

            // 4. Composer
            [
                'question'    => '¿Qué significa el operador ^ (caret) en composer.json?',
                'explanation' => '^2.1 permite versiones >= 2.1.0 y < 3.0.0. Es el operador más usado porque permite minor y patch updates (compatibles según semver) pero bloquea major updates que podrían romper la compatibilidad.',
                'options'     => [
                    ['text' => 'Permite minor y patch updates sin cambiar el major: ^2.1 significa >=2.1.0 <3.0.0', 'correct' => true],
                    ['text' => 'Instala exactamente esa versión sin actualizaciones', 'correct' => false],
                    ['text' => 'Instala la última versión disponible ignorando restricciones', 'correct' => false],
                    ['text' => 'Solo permite patch updates: ^2.1 significa >=2.1.0 <2.2.0', 'correct' => false],
                ],
            ],

            // 5. Errores
            [
                'question'    => '¿Cuál es la jerarquía correcta de excepciones en PHP?',
                'explanation' => 'Throwable es la interfaz raíz. De ella derivan Error (errores internos del motor PHP) y Exception (errores de la aplicación). RuntimeException extiende Exception. Las clases SPL proporcionan excepciones semánticas.',
                'options'     => [
                    ['text' => 'Throwable → Error + Exception → RuntimeException → SPL Exceptions', 'correct' => true],
                    ['text' => 'Exception → Error → Throwable → RuntimeException', 'correct' => false],
                    ['text' => 'Error → Exception → Throwable', 'correct' => false],
                    ['text' => 'Throwable → Exception → Error', 'correct' => false],
                ],
            ],

            // 6. Generadores
            [
                'question'    => '¿Cuál es la principal ventaja de los generadores (yield) sobre arrays?',
                'explanation' => 'Los generadores producen valores bajo demanda (lazy), sin cargar toda la colección en memoria. Un generador que itera 1 millón de registros usa la misma memoria que uno de 10 registros.',
                'options'     => [
                    ['text' => 'Producen valores bajo demanda (lazy), usando memoria constante independientemente del tamaño de los datos', 'correct' => true],
                    ['text' => 'Son más rápidos porque usan multithreading', 'correct' => false],
                    ['text' => 'Permiten acceder a valores por índice como un array', 'correct' => false],
                    ['text' => 'Se pueden serializar y guardar en disco', 'correct' => false],
                ],
            ],

            // 7. Tipos estrictos
            [
                'question'    => '¿Qué hace declare(strict_types=1) al inicio de un archivo PHP?',
                'explanation' => 'Activa el modo estricto de tipos para ESE archivo. Las llamadas a funciones deben pasar exactamente el tipo declarado. Sin strict_types, PHP convierte automáticamente (type juggling): "123" se acepta como int.',
                'options'     => [
                    ['text' => 'Desactiva la coerción automática de tipos: los argumentos deben coincidir exactamente con el tipo declarado', 'correct' => true],
                    ['text' => 'Convierte todas las variables a su tipo más estricto', 'correct' => false],
                    ['text' => 'Hace que todas las funciones del archivo requieran type hints', 'correct' => false],
                    ['text' => 'Solo afecta a los return types, no a los parámetros', 'correct' => false],
                ],
            ],

            // 8. PSR Standards
            [
                'question'    => '¿Qué define PSR-7 en el ecosistema PHP?',
                'explanation' => 'PSR-7 define las interfaces para HTTP Messages: RequestInterface, ResponseInterface, ServerRequestInterface, StreamInterface, etc. Son inmutables (withHeader retorna nueva instancia). Usado por middleware (PSR-15).',
                'options'     => [
                    ['text' => 'Interfaces inmutables para mensajes HTTP: Request, Response, Stream y URI', 'correct' => true],
                    ['text' => 'Reglas de estilo de código y formateo', 'correct' => false],
                    ['text' => 'Cómo implementar logging en aplicaciones PHP', 'correct' => false],
                    ['text' => 'El estándar para Connection Pool de base de datos', 'correct' => false],
                ],
            ],

            // 9. Patrones de diseño
            [
                'question'    => '¿Por qué el patrón Singleton es considerado un anti-pattern en muchos contextos?',
                'explanation' => 'El Singleton crea acoplamiento global (como una variable global), dificulta el testing (no se puede sustituir fácilmente por mocks) y oculta las dependencias. Se prefiere Dependency Injection para manejar instancias únicas.',
                'options'     => [
                    ['text' => 'Crea acoplamiento global, dificulta testing y oculta dependencias; se prefiere DI', 'correct' => true],
                    ['text' => 'Porque consume mucha memoria al crear múltiples instancias', 'correct' => false],
                    ['text' => 'Porque PHP no soporta constructores privados', 'correct' => false],
                    ['text' => 'No es un anti-pattern, es siempre la mejor opción', 'correct' => false],
                ],
            ],

            // 10. Testing
            [
                'question'    => '¿Qué es un Data Provider en PHPUnit?',
                'explanation' => 'Un Data Provider es un método que retorna un array de datasets para ejecutar el mismo test con diferentes datos. Se conecta con #[DataProvider(\'providerMethod\')]. Evita duplicar tests para diferentes inputs.',
                'options'     => [
                    ['text' => 'Un método que provee múltiples datasets para ejecutar el mismo test con diferentes datos', 'correct' => true],
                    ['text' => 'Una conexión a base de datos de prueba', 'correct' => false],
                    ['text' => 'Un mock que genera datos aleatorios', 'correct' => false],
                    ['text' => 'Un fixture que se carga antes de cada test', 'correct' => false],
                ],
            ],

            // 11. Arrays avanzados
            [
                'question'    => '¿Qué diferencia hay entre array_map y array_walk en PHP?',
                'explanation' => 'array_map retorna un NUEVO array con los resultados (funcional, sin side effects). array_walk modifica el array IN-PLACE pasando valores por referencia y retorna bool. array_map puede recibir múltiples arrays.',
                'options'     => [
                    ['text' => 'array_map retorna nuevo array (funcional); array_walk modifica in-place por referencia', 'correct' => true],
                    ['text' => 'Son exactamente iguales, solo cambia el nombre', 'correct' => false],
                    ['text' => 'array_walk es más rápido porque no crea nuevo array', 'correct' => false],
                    ['text' => 'array_map solo funciona con arrays numéricos', 'correct' => false],
                ],
            ],

            // 12. Strings y regex
            [
                'question'    => '¿Qué flag se usa en preg_match para obtener named groups?',
                'explanation' => 'Los named groups se definen con (?P<name>pattern) o (?<name>pattern) en la regex. No requieren flag especial: los matches se populan tanto con índice numérico como con el nombre. PREG_SET_ORDER agrupa por match.',
                'options'     => [
                    ['text' => 'No requieren flag especial; se definen con (?P<name>pattern) y los resultados incluyen el nombre como key', 'correct' => true],
                    ['text' => 'Se usa PREG_NAMED_GROUPS como segundo argumento', 'correct' => false],
                    ['text' => 'Se añade /n al final del patrón regex', 'correct' => false],
                    ['text' => 'Named groups no son soportados en PHP', 'correct' => false],
                ],
            ],

            // 13. Filesystem
            [
                'question'    => '¿Cuál es la ventaja de SplFileObject sobre fopen/fclose?',
                'explanation' => 'SplFileObject es orientado a objetos, implementa Iterator (se puede usar en foreach), cierra el archivo automáticamente al destruirse (RAII), y tiene métodos integrados para CSV, locking y seeking.',
                'options'     => [
                    ['text' => 'Es OOP, implementa Iterator, cierra archivo automáticamente y tiene soporte integrado para CSV', 'correct' => true],
                    ['text' => 'Es más rápido porque usa buffers más grandes', 'correct' => false],
                    ['text' => 'Solo funciona con archivos de texto, no binarios', 'correct' => false],
                    ['text' => 'No hay ventaja, es solo un wrapper innecesario', 'correct' => false],
                ],
            ],

            // 14. Sesiones
            [
                'question'    => '¿Qué atributo de cookie previene el acceso desde JavaScript?',
                'explanation' => 'El atributo HttpOnly previene que JavaScript acceda a la cookie con document.cookie, protegiendo contra ataques XSS. Secure asegura que solo se envíe por HTTPS. SameSite protege contra CSRF.',
                'options'     => [
                    ['text' => 'HttpOnly: previene acceso desde document.cookie, protegiendo contra XSS', 'correct' => true],
                    ['text' => 'Secure: solo permite HTTPS', 'correct' => false],
                    ['text' => 'SameSite: restringe envío cross-origin', 'correct' => false],
                    ['text' => 'Path: limita a qué rutas se envía la cookie', 'correct' => false],
                ],
            ],

            // 15. PDO
            [
                'question'    => '¿Por qué son importantes los Prepared Statements en PDO?',
                'explanation' => 'Prepared Statements separan la estructura SQL de los datos, previniendo SQL Injection. Los valores se pasan como parámetros que PDO escapa automáticamente. Además mejoran rendimiento en queries repetidas.',
                'options'     => [
                    ['text' => 'Previenen SQL Injection al separar SQL de datos, y mejoran rendimiento en queries repetidas', 'correct' => true],
                    ['text' => 'Solo son necesarios para queries INSERT, no para SELECT', 'correct' => false],
                    ['text' => 'Preparan la base de datos creando índices automáticamente', 'correct' => false],
                    ['text' => 'Son obligatorios en PHP 8, sin ellos el código no ejecuta', 'correct' => false],
                ],
            ],

            // 16. Seguridad
            [
                'question'    => '¿Qué función de PHP se debe usar para hashear contraseñas?',
                'explanation' => 'password_hash() con PASSWORD_BCRYPT o PASSWORD_ARGON2ID genera un hash seguro con salt automático. NUNCA usar md5() o sha1() para contraseñas. password_verify() compara de forma time-safe.',
                'options'     => [
                    ['text' => 'password_hash() con BCRYPT o ARGON2ID, que genera salt automático; verificar con password_verify()', 'correct' => true],
                    ['text' => 'md5() porque es el más rápido y seguro', 'correct' => false],
                    ['text' => 'sha256() con un salt manual concatenado', 'correct' => false],
                    ['text' => 'base64_encode() para ofuscar la contraseña', 'correct' => false],
                ],
            ],

            // 17. Rendimiento
            [
                'question'    => '¿Qué es OPcache en PHP?',
                'explanation' => 'OPcache almacena bytecode pre-compilado en memoria compartida, eliminando la necesidad de parsear y compilar scripts PHP en cada petición. Es la optimización de rendimiento más impactante, incluida en PHP desde 5.5.',
                'options'     => [
                    ['text' => 'Un caché de bytecode que almacena scripts pre-compilados en memoria, eliminando parsing en cada petición', 'correct' => true],
                    ['text' => 'Un caché de queries de base de datos', 'correct' => false],
                    ['text' => 'Un sistema de caché de respuestas HTTP', 'correct' => false],
                    ['text' => 'Una extensión para cachear sesiones en Redis', 'correct' => false],
                ],
            ],

            // 18. PHP 8.x
            [
                'question'    => '¿Qué son los Enums en PHP 8.1?',
                'explanation' => 'Los Enums son tipos que definen un conjunto fijo de valores posibles. Pueden ser Pure (sin valor subyacente) o Backed (con string o int). Soportan métodos, interfaces y traits. Reemplazan las constantes de clase para representar estados.',
                'options'     => [
                    ['text' => 'Tipos con conjunto fijo de valores, Pure o Backed (string/int), con soporte para métodos e interfaces', 'correct' => true],
                    ['text' => 'Una forma abreviada de definir arrays constantes', 'correct' => false],
                    ['text' => 'Un wrapper para usar constantes de otras clases', 'correct' => false],
                    ['text' => 'Solo están disponibles en PHP 8.3+', 'correct' => false],
                ],
            ],

            // 19. Entrevista - type juggling
            [
                'question'    => '¿Qué resultado da la comparación 0 == "hello" en PHP 7 vs PHP 8?',
                'explanation' => 'En PHP 7, 0 == "hello" es TRUE porque "hello" se convierte a int 0. En PHP 8 cambió: ahora es FALSE porque 0 se convierte a string "0" para comparar con string. Este cambio de type juggling rompió código legacy.',
                'options'     => [
                    ['text' => 'PHP 7: true (string "hello" se convierte a int 0); PHP 8: false (se compara como strings)', 'correct' => true],
                    ['text' => 'Ambos retornan true', 'correct' => false],
                    ['text' => 'Ambos retornan false', 'correct' => false],
                    ['text' => 'PHP 7 lanza error; PHP 8 retorna false', 'correct' => false],
                ],
            ],

            // 20. Entrevista - closures
            [
                'question'    => '¿Qué diferencia hay entre use ($var) y use (&$var) en un Closure de PHP?',
                'explanation' => 'use ($var) captura el VALOR de la variable en el momento de definir el closure (copia). use (&$var) captura una REFERENCIA: cambios dentro del closure afectan la variable original y viceversa.',
                'options'     => [
                    ['text' => 'use ($var) captura el valor (copia); use (&$var) captura por referencia (cambios se reflejan afuera)', 'correct' => true],
                    ['text' => 'use (&$var) es solo para arrays, use ($var) para escalares', 'correct' => false],
                    ['text' => 'No hay diferencia, el & es opcional y decorativo', 'correct' => false],
                    ['text' => 'use (&$var) crea una variable global dentro del closure', 'correct' => false],
                ],
            ],

        ];
    }
}
