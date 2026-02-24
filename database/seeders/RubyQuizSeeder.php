<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class RubyQuizSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'ruby-desde-cero')->first();

        if (! $course) {
            $this->command->warn('Ruby course not found. Run CourseSeeder first.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id],
            [
                'title'       => 'Evaluación: Ruby desde Cero',
                'description' => 'Pon a prueba tus conocimientos sobre Ruby: tipos, POO, bloques, metaprogramación, concurrencia y más.',
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

        $this->command->info("Ruby quiz seeded: {$quiz->questions()->count()} preguntas.");
    }

    private function questions(): array
    {
        return [

            // ── 1. Introducción ───────────────────────────────────────────
            [
                'question'    => '¿Cuál es una característica fundamental de Ruby?',
                'explanation' => 'En Ruby, absolutamente todo es un objeto, incluyendo números enteros, nil y booleanos. Esto permite llamar métodos directamente sobre cualquier valor: 42.even?, nil.nil?, true.class.',
                'options'     => [
                    ['text' => 'Todo es un objeto, incluyendo números, nil y booleanos', 'correct' => true],
                    ['text' => 'Es un lenguaje compilado de tipado estático', 'correct' => false],
                    ['text' => 'Solo soporta programación funcional pura', 'correct' => false],
                    ['text' => 'No permite herencia ni polimorfismo', 'correct' => false],
                ],
            ],

            // ── 2. Tipos de datos ─────────────────────────────────────────
            [
                'question'    => '¿Qué valores son "falsy" en Ruby?',
                'explanation' => 'En Ruby, solo nil y false son falsy. A diferencia de JavaScript o Python, 0, "" (string vacío) y [] (array vacío) son todos truthy.',
                'options'     => [
                    ['text' => 'Solo nil y false', 'correct' => true],
                    ['text' => 'nil, false, 0 y "" (string vacío)', 'correct' => false],
                    ['text' => 'nil, false, 0, "" y [] (array vacío)', 'correct' => false],
                    ['text' => 'Solo nil', 'correct' => false],
                ],
            ],

            // ── 3. Estructuras de control ────────────────────────────────
            [
                'question'    => '¿Qué hace el operador &:upcase en [\"hola\"].map(&:upcase)?',
                'explanation' => 'El operador & convierte un Symbol en un Proc mediante Symbol#to_proc. El símbolo :upcase se convierte en un proc que llama al método upcase en cada argumento, equivalente a .map { |s| s.upcase }.',
                'options'     => [
                    ['text' => 'Convierte el Symbol :upcase en un Proc que llama al método upcase sobre cada elemento', 'correct' => true],
                    ['text' => 'Pasa el string "upcase" como argumento a map', 'correct' => false],
                    ['text' => 'Crea una constante UPCASE y la aplica al array', 'correct' => false],
                    ['text' => 'Ejecuta el método upcase de la clase Array', 'correct' => false],
                ],
            ],

            // ── 4. Métodos y bloques ─────────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia principal entre un Proc y un Lambda?',
                'explanation' => 'Lambda verifica el número de argumentos (aridad estricta) y return solo sale del lambda. Proc no verifica aridad y return sale del método contenedor, lo que puede causar comportamientos inesperados.',
                'options'     => [
                    ['text' => 'Lambda verifica la aridad de argumentos y return solo sale del lambda; Proc no verifica aridad y return sale del método contenedor', 'correct' => true],
                    ['text' => 'No hay diferencia, son sinónimos', 'correct' => false],
                    ['text' => 'Proc es más rápido que Lambda porque no es un objeto', 'correct' => false],
                    ['text' => 'Lambda solo puede recibir un argumento, Proc puede recibir varios', 'correct' => false],
                ],
            ],

            // ── 5. POO ──────────────────────────────────────────────────
            [
                'question'    => '¿Qué genera attr_accessor :nombre en una clase?',
                'explanation' => 'attr_accessor es un macro que genera automáticamente un método getter (nombre) que retorna @nombre, y un método setter (nombre=) que asigna @nombre. Es equivalente a definir ambos métodos manualmente.',
                'options'     => [
                    ['text' => 'Un método getter (nombre) y un setter (nombre=) para la variable de instancia @nombre', 'correct' => true],
                    ['text' => 'Solo un método getter de solo lectura', 'correct' => false],
                    ['text' => 'Una constante NOMBRE accesible globalmente', 'correct' => false],
                    ['text' => 'Una variable de clase @@nombre compartida entre instancias', 'correct' => false],
                ],
            ],

            // ── 6. Herencia y módulos ────────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre include y prepend al mezclar un módulo?',
                'explanation' => 'include inserta el módulo después de la clase en la cadena de ancestros. prepend lo inserta antes de la clase, permitiendo que el módulo intercepte llamadas a métodos de la clase (ideal para logging, validación, etc.).',
                'options'     => [
                    ['text' => 'include inserta el módulo después de la clase en ancestors; prepend lo inserta antes, pudiendo interceptar métodos', 'correct' => true],
                    ['text' => 'include es para módulos y prepend es para clases', 'correct' => false],
                    ['text' => 'prepend solo funciona con métodos privados', 'correct' => false],
                    ['text' => 'No hay diferencia, ambos hacen lo mismo', 'correct' => false],
                ],
            ],

            // ── 7. Manejo de errores ─────────────────────────────────────
            [
                'question'    => '¿Por qué no se debe rescatar Exception en Ruby?',
                'explanation' => 'Exception es la clase base que incluye errores del sistema como NoMemoryError, Interrupt (Ctrl+C) y SyntaxError. Rescatar Exception impediría terminar el programa con Ctrl+C. Se debe rescatar StandardError (o subclases), que es lo que rescue captura por defecto.',
                'options'     => [
                    ['text' => 'Porque capturaría errores del sistema como Interrupt (Ctrl+C) y NoMemoryError, impidiendo terminar el programa', 'correct' => true],
                    ['text' => 'Porque Exception no existe en Ruby', 'correct' => false],
                    ['text' => 'Porque solo se pueden rescatar clases que hereden de RuntimeError', 'correct' => false],
                    ['text' => 'Porque Exception es más lento que StandardError', 'correct' => false],
                ],
            ],

            // ── 8. Archivos e I/O ───────────────────────────────────────
            [
                'question'    => '¿Cuál es la ventaja de usar File.open con un bloque en Ruby?',
                'explanation' => 'Cuando usas File.open con un bloque, Ruby cierra automáticamente el archivo al finalizar el bloque (incluso si hay una excepción). Sin bloque, debes cerrar el archivo manualmente con file.close, arriesgando resource leaks.',
                'options'     => [
                    ['text' => 'El archivo se cierra automáticamente al finalizar el bloque, incluso si ocurre una excepción', 'correct' => true],
                    ['text' => 'Es más rápido que File.read porque usa buffers', 'correct' => false],
                    ['text' => 'Permite leer archivos binarios, cosa que File.read no hace', 'correct' => false],
                    ['text' => 'Convierte automáticamente el contenido a UTF-8', 'correct' => false],
                ],
            ],

            // ── 9. Expresiones regulares ────────────────────────────────
            [
                'question'    => '¿Qué retorna el operador =~ en Ruby?',
                'explanation' => 'El operador =~ retorna la posición (índice entero) donde se encontró la primera coincidencia, o nil si no hay match. Es el operador de match más básico. Para obtener datos del match, se usan match() o las variables especiales $1, $2, etc.',
                'options'     => [
                    ['text' => 'La posición (Integer) de la primera coincidencia, o nil si no hay match', 'correct' => true],
                    ['text' => 'true o false', 'correct' => false],
                    ['text' => 'Un objeto MatchData con los grupos capturados', 'correct' => false],
                    ['text' => 'Un array con todas las coincidencias', 'correct' => false],
                ],
            ],

            // ── 10. Enumerables ──────────────────────────────────────────
            [
                'question'    => '¿Qué método de Enumerable retorna las frecuencias de cada elemento?',
                'explanation' => 'tally (Ruby 2.7+) retorna un hash con cada elemento como clave y su cantidad como valor. Es equivalente a each_with_object(Hash.new(0)) { |e, h| h[e] += 1 }.',
                'options'     => [
                    ['text' => 'tally', 'correct' => true],
                    ['text' => 'count_by', 'correct' => false],
                    ['text' => 'frequencies', 'correct' => false],
                    ['text' => 'histogram', 'correct' => false],
                ],
            ],

            // ── 11. Programación funcional ───────────────────────────────
            [
                'question'    => '¿Qué hace el operador >> con Procs/Lambdas en Ruby?',
                'explanation' => 'El operador >> (Ruby 2.6+) compone dos funciones: f >> g crea una nueva función que primero ejecuta f y luego pasa el resultado a g. Es la composición de funciones de izquierda a derecha.',
                'options'     => [
                    ['text' => 'Compone dos funciones: f >> g ejecuta primero f, luego g con el resultado', 'correct' => true],
                    ['text' => 'Compara los valores de retorno de dos lambdas', 'correct' => false],
                    ['text' => 'Ejecuta ambas funciones en paralelo', 'correct' => false],
                    ['text' => 'Convierte un Proc en Lambda', 'correct' => false],
                ],
            ],

            // ── 12. Metaprogramación ────────────────────────────────────
            [
                'question'    => '¿Qué debe implementarse siempre junto con method_missing?',
                'explanation' => 'respond_to_missing? debe implementarse junto con method_missing para que is_a?, respond_to?, method(:name) y otras introspecciones funcionen correctamente. Sin él, respond_to? retornaría false para métodos que method_missing sí maneja.',
                'options'     => [
                    ['text' => 'respond_to_missing? para que la introspección funcione correctamente', 'correct' => true],
                    ['text' => 'method_added para registrar los nuevos métodos', 'correct' => false],
                    ['text' => 'define_method para cachear los métodos dinámicos', 'correct' => false],
                    ['text' => 'inherited para propagar a subclases', 'correct' => false],
                ],
            ],

            // ── 13. Gemas y Bundler ──────────────────────────────────────
            [
                'question'    => '¿Qué significa el operador ~> (pessimistic) en un Gemfile?',
                'explanation' => '~> 2.1 significa >= 2.1.0 y < 3.0 (permite minor updates). ~> 2.1.3 significa >= 2.1.3 y < 2.2.0 (solo patch updates). Previene breaking changes al limitar las actualizaciones.',
                'options'     => [
                    ['text' => 'Permite actualizaciones del último dígito: ~> 2.1 permite >= 2.1, < 3.0', 'correct' => true],
                    ['text' => 'Instala exactamente esa versión, sin actualizaciones', 'correct' => false],
                    ['text' => 'Instala la última versión disponible sin restricciones', 'correct' => false],
                    ['text' => 'Descarga el código fuente de la gema en vez del binario', 'correct' => false],
                ],
            ],

            // ── 14. Testing ─────────────────────────────────────────────
            [
                'question'    => '¿En RSpec, cuál es la diferencia entre let y let!?',
                'explanation' => 'let es lazy: el valor solo se calcula la primera vez que se usa en un test. let! es eager: se ejecuta antes de cada test (equivale a un before(:each)). Usa let! cuando necesitas efectos secundarios (crear registros en BD, etc.).',
                'options'     => [
                    ['text' => 'let es lazy (se evalúa al usar la variable); let! es eager (se evalúa antes de cada test)', 'correct' => true],
                    ['text' => 'let crea variables locales; let! crea variables de instancia', 'correct' => false],
                    ['text' => 'let es para tests unitarios; let! es para tests de integración', 'correct' => false],
                    ['text' => 'No hay diferencia, el ! es opcional', 'correct' => false],
                ],
            ],

            // ── 15. Concurrencia ────────────────────────────────────────
            [
                'question'    => '¿Qué son los Ractors en Ruby 3.0+?',
                'explanation' => 'Los Ractors son actores que permiten verdadero paralelismo en Ruby (sin GVL). Cada Ractor tiene su propio GVL y no comparte objetos mutables con otros Ractors. Se comunican mediante mensajes (send/receive), evitando race conditions.',
                'options'     => [
                    ['text' => 'Actores que permiten paralelismo real (sin GVL), con comunicación por mensajes', 'correct' => true],
                    ['text' => 'Una versión más rápida de Threads que ignora el GVL', 'correct' => false],
                    ['text' => 'Procesos del sistema operativo creados con fork()', 'correct' => false],
                    ['text' => 'Un alias de Fibers con mejor sintaxis', 'correct' => false],
                ],
            ],

            // ── 16. Patrones de diseño ──────────────────────────────────
            [
                'question'    => '¿Cuál es la forma más "Ruby-like" de implementar el patrón Decorator?',
                'explanation' => 'Ruby ofrece prepend para insertar módulos antes de la clase en la cadena de ancestros, y SimpleDelegator para delegar métodos. Ambos son más idiomáticos que la herencia clásica. prepend permite interceptar métodos con super.',
                'options'     => [
                    ['text' => 'Usando prepend con módulos o SimpleDelegator, más idiomáticos que herencia clásica', 'correct' => true],
                    ['text' => 'Usando herencia múltiple con < Decorator', 'correct' => false],
                    ['text' => 'Definiendo manualmente todos los métodos en una subclase', 'correct' => false],
                    ['text' => 'Usando eval() para inyectar código dinámicamente', 'correct' => false],
                ],
            ],

            // ── 17. HTTP y APIs ─────────────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre Net::HTTP y HTTParty?',
                'explanation' => 'Net::HTTP es la librería estándar de Ruby para HTTP (verbose pero sin dependencias externas). HTTParty es una gema que simplifica las peticiones HTTP con una API más concisa y funcionalidades como parseo automático de JSON.',
                'options'     => [
                    ['text' => 'Net::HTTP es la stdlib (verbose, sin dependencias); HTTParty es una gema con API simplificada y parseo automático', 'correct' => true],
                    ['text' => 'HTTParty solo funciona con APIs REST, Net::HTTP soporta cualquier protocolo', 'correct' => false],
                    ['text' => 'Net::HTTP es asíncrono por defecto, HTTParty es síncrono', 'correct' => false],
                    ['text' => 'No hay diferencia significativa, ambos tienen la misma API', 'correct' => false],
                ],
            ],

            // ── 18. Ruby moderno ────────────────────────────────────────
            [
                'question'    => '¿Qué es Data.define en Ruby 3.2+?',
                'explanation' => 'Data.define crea clases de valor inmutables (value objects). Similar a Struct pero los objetos son frozen por defecto: no se pueden modificar después de la creación. Ideal para objetos que representan datos sin comportamiento mutable.',
                'options'     => [
                    ['text' => 'Una forma de crear clases de valor inmutables (value objects), similar a Struct pero frozen por defecto', 'correct' => true],
                    ['text' => 'Un método para definir migraciones de base de datos', 'correct' => false],
                    ['text' => 'Una alternativa a Hash para almacenar datos clave-valor', 'correct' => false],
                    ['text' => 'Un generador de archivos de configuración YAML', 'correct' => false],
                ],
            ],

            // ── 19. Entrevista (Symbol vs String) ─────────────────────────
            [
                'question'    => '¿Por qué se prefieren Symbols sobre Strings como keys de Hash?',
                'explanation' => 'Los Symbols son inmutables y singleton: :hello siempre referencia al mismo objeto en memoria. Los Strings son mutables y cada "hello" crea un nuevo objeto. Esto hace que los Symbols sean más eficientes para comparación (comparan object_id) y uso de memoria.',
                'options'     => [
                    ['text' => 'Los Symbols son inmutables, singleton (un solo objeto en memoria) y más eficientes para comparación', 'correct' => true],
                    ['text' => 'Los Symbols son más cortos de escribir', 'correct' => false],
                    ['text' => 'Los Strings no se pueden usar como keys de Hash', 'correct' => false],
                    ['text' => 'Los Symbols soportan interpolación y los Strings no', 'correct' => false],
                ],
            ],

            // ── 20. Entrevista (freeze) ─────────────────────────────────
            [
                'question'    => '¿Qué efecto tiene el magic comment # frozen_string_literal: true al inicio de un archivo Ruby?',
                'explanation' => 'Este comentario especial hace que todos los string literals del archivo sean automáticamente frozen (inmutables). Cualquier intento de mutar un string ("hello" << " world") lanzará FrozenError. Mejora el rendimiento y previene bugs de mutación.',
                'options'     => [
                    ['text' => 'Hace que todos los string literals del archivo sean inmutables (frozen), mejorando rendimiento y seguridad', 'correct' => true],
                    ['text' => 'Congela todas las variables del archivo para que no se puedan reasignar', 'correct' => false],
                    ['text' => 'Desactiva el garbage collector para strings', 'correct' => false],
                    ['text' => 'Convierte todos los strings a encoding ASCII', 'correct' => false],
                ],
            ],

        ];
    }
}
