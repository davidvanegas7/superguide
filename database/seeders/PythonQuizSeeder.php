<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class PythonQuizSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'python-desde-cero')->first();

        if (! $course) {
            $this->command->warn('Python course not found. Run CourseSeeder first.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id],
            [
                'title'       => 'Evaluación: Python desde Cero',
                'description' => 'Pon a prueba tus conocimientos sobre Python: tipos de datos, funciones, POO, módulos, testing y más.',
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

        $this->command->info("Python quiz seeded: {$quiz->questions()->count()} preguntas.");
    }

    private function questions(): array
    {
        return [

            // ── 1 · Introducción a Python ─────────────────────────────
            [
                'question'    => '¿Cuál es la principal razón por la que Python usa indentación en lugar de llaves para delimitar bloques de código?',
                'explanation' => 'Python fue diseñado con la filosofía de que el código debe ser fácil de leer. La indentación obligatoria garantiza que la estructura visual del código siempre coincida con su estructura lógica, evitando discrepancias comunes en lenguajes con llaves.',
                'options'     => [
                    ['text' => 'Para que la estructura visual y lógica del código siempre coincidan, mejorando la legibilidad', 'correct' => true],
                    ['text' => 'Porque es más rápido de interpretar que las llaves', 'correct' => false],
                    ['text' => 'Por limitaciones técnicas del intérprete CPython', 'correct' => false],
                    ['text' => 'Para reducir el tamaño de los archivos fuente', 'correct' => false],
                ],
            ],

            // ── 2 · Variables y tipos de datos ────────────────────────
            [
                'question'    => '¿Qué diferencia fundamental existe entre listas y tuplas en Python?',
                'explanation' => 'Las listas son mutables (se pueden modificar tras su creación) mientras que las tuplas son inmutables. Esto hace que las tuplas sean hashables (pueden usarse como keys de diccionarios) y ligeramente más eficientes en memoria, mientras que las listas son más flexibles para colecciones que cambian.',
                'options'     => [
                    ['text' => 'Las listas son mutables y las tuplas inmutables; las tuplas pueden ser keys de diccionarios', 'correct' => true],
                    ['text' => 'Las listas solo contienen un tipo de dato y las tuplas cualquier tipo', 'correct' => false],
                    ['text' => 'Las tuplas se acceden por nombre y las listas por índice numérico', 'correct' => false],
                    ['text' => 'No hay diferencia real, es solo una convención de estilo', 'correct' => false],
                ],
            ],

            // ── 3 · Operadores y control de flujo ─────────────────────
            [
                'question'    => '¿Qué hace el operador walrus `:=` introducido en Python 3.8?',
                'explanation' => 'El operador walrus (:=) permite asignar un valor a una variable dentro de una expresión. Esto evita tener que llamar una función dos veces (una para el if y otra para usar el valor), o declarar la variable en una línea separada. Ejemplo: if (n := len(a)) > 10: print(n)',
                'options'     => [
                    ['text' => 'Asigna un valor a una variable dentro de una expresión, evitando líneas extra', 'correct' => true],
                    ['text' => 'Compara dos valores y retorna el menor', 'correct' => false],
                    ['text' => 'Es un alias para el operador is que compara identidad', 'correct' => false],
                    ['text' => 'Permite definir constantes inmutables', 'correct' => false],
                ],
            ],

            // ── 4 · Strings y colecciones ─────────────────────────────
            [
                'question'    => '¿Qué retorna la expresión `"python"[::-1]`?',
                'explanation' => 'El slice [::-1] crea una copia invertida de la secuencia. Con step=-1, recorre el string de derecha a izquierda. Es un patrón idiomático de Python para invertir strings, listas y otras secuencias sin usar funciones adicionales.',
                'options'     => [
                    ['text' => '"nohtyp" — invierte el string usando slice con paso negativo', 'correct' => true],
                    ['text' => '"python" — no modifica el string original', 'correct' => false],
                    ['text' => 'Un error TypeError porque strings no soportan paso negativo', 'correct' => false],
                    ['text' => '"p" — retorna solo el primer carácter', 'correct' => false],
                ],
            ],

            // ── 5 · Funciones ─────────────────────────────────────────
            [
                'question'    => '¿Cuál es el peligro de usar un argumento mutable como valor por defecto en una función, como `def add(item, lst=[])`?',
                'explanation' => 'Los valores por defecto mutables se crean una sola vez cuando se define la función, no en cada llamada. Todas las llamadas sin el argumento comparten el mismo objeto, acumulando valores entre llamadas. La solución es usar None como default y crear el objeto dentro de la función.',
                'options'     => [
                    ['text' => 'El objeto mutable se comparte entre todas las llamadas, acumulando valores inesperadamente', 'correct' => true],
                    ['text' => 'Python lanza un SyntaxError al definir la función', 'correct' => false],
                    ['text' => 'Se crea una copia nueva del objeto en cada llamada', 'correct' => false],
                    ['text' => 'El argumento se convierte automáticamente en una tupla inmutable', 'correct' => false],
                ],
            ],

            // ── 6 · Comprensiones y generadores ──────────────────────
            [
                'question'    => '¿Cuál es la diferencia principal entre una list comprehension `[x for x in range(10)]` y un generator expression `(x for x in range(10))`?',
                'explanation' => 'La list comprehension crea toda la lista en memoria de una vez. El generator expression produce valores uno a uno bajo demanda (lazy evaluation), usando memoria constante sin importar el tamaño. Para iteraciones grandes, los generadores son mucho más eficientes.',
                'options'     => [
                    ['text' => 'El generador produce valores bajo demanda (lazy), usando memoria constante; la lista los crea todos en memoria', 'correct' => true],
                    ['text' => 'El generador es más lento pero más legible que la comprehension', 'correct' => false],
                    ['text' => 'Son equivalentes; la diferencia es solo sintáctica', 'correct' => false],
                    ['text' => 'El generador solo funciona con range(), no con otras iterables', 'correct' => false],
                ],
            ],

            // ── 7 · POO Fundamentos ───────────────────────────────────
            [
                'question'    => '¿Qué diferencia hay entre `__str__` y `__repr__` en una clase Python?',
                'explanation' => '__str__ está orientado al usuario final (legible) y se llama con str() o print(). __repr__ está orientado al desarrollador (no ambiguo) y se llama en la consola interactiva. Si solo se define __repr__, Python lo usa también como __str__. La convención es que repr() debería retornar algo que pueda recrear el objeto.',
                'options'     => [
                    ['text' => '__str__ es para usuarios (legible), __repr__ es para desarrolladores (preciso y no ambiguo)', 'correct' => true],
                    ['text' => '__str__ solo acepta strings, __repr__ acepta cualquier tipo', 'correct' => false],
                    ['text' => 'Son exactamente iguales; solo cambia el nombre por convención', 'correct' => false],
                    ['text' => '__repr__ solo se usa para la serialización JSON del objeto', 'correct' => false],
                ],
            ],

            // ── 8 · POO Avanzada ──────────────────────────────────────
            [
                'question'    => '¿Cómo funciona el MRO (Method Resolution Order) en Python con herencia múltiple?',
                'explanation' => 'Python usa el algoritmo C3 Linearization para determinar el orden en que busca métodos en la jerarquía de clases. Garantiza que cada clase aparezca antes que sus padres y respeta el orden de declaración. Se puede inspeccionar con Clase.__mro__ o Clase.mro().',
                'options'     => [
                    ['text' => 'Usa el algoritmo C3 Linearization: busca de izquierda a derecha respetando la jerarquía sin repetir', 'correct' => true],
                    ['text' => 'Busca siempre en la primera clase padre declarada, ignorando las demás', 'correct' => false],
                    ['text' => 'Busca en orden alfabético por nombre de clase', 'correct' => false],
                    ['text' => 'Python no soporta herencia múltiple, así que no existe MRO', 'correct' => false],
                ],
            ],

            // ── 9 · Módulos y paquetes ────────────────────────────────
            [
                'question'    => '¿Cuál es la función del archivo `__init__.py` en un paquete Python?',
                'explanation' => '__init__.py marca un directorio como paquete Python importable. Se ejecuta cuando se importa el paquete. Desde Python 3.3 no es estrictamente necesario (namespace packages), pero sigue siendo la práctica recomendada para definir la API pública del paquete y ejecutar código de inicialización.',
                'options'     => [
                    ['text' => 'Marca el directorio como paquete, se ejecuta al importarlo, y define la API pública del paquete', 'correct' => true],
                    ['text' => 'Es el punto de entrada principal para ejecutar el paquete como script', 'correct' => false],
                    ['text' => 'Contiene la documentación obligatoria del paquete', 'correct' => false],
                    ['text' => 'No tiene función real; es un archivo vacío requerido por pip', 'correct' => false],
                ],
            ],

            // ── 10 · Manejo de archivos ───────────────────────────────
            [
                'question'    => '¿Por qué es importante usar `with open(...) as f:` en lugar de `f = open(...)`?',
                'explanation' => 'El context manager (with) garantiza que el archivo se cierre correctamente en TODOS los casos, incluyendo cuando ocurre una excepción. Sin with, un error podría dejar el archivo abierto, causando pérdida de datos (buffer sin flush), fugas de file descriptors, y archivos bloqueados.',
                'options'     => [
                    ['text' => 'Garantiza el cierre del archivo incluso si ocurre una excepción, evitando fugas de recursos', 'correct' => true],
                    ['text' => 'Hace que la lectura sea más rápida por usar un buffer optimizado', 'correct' => false],
                    ['text' => 'Permite abrir múltiples archivos a la vez', 'correct' => false],
                    ['text' => 'Es solo una convención de estilo sin diferencia funcional', 'correct' => false],
                ],
            ],

            // ── 11 · Excepciones ──────────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre `except Exception` y `except BaseException`?',
                'explanation' => 'BaseException es la clase raíz de todas las excepciones, incluyendo SystemExit, KeyboardInterrupt y GeneratorExit. Exception hereda de BaseException y cubre solo errores "normales". Capturar BaseException puede atrapar Ctrl+C y sys.exit(), haciendo que el programa sea imposible de terminar normalmente.',
                'options'     => [
                    ['text' => 'Exception no captura SystemExit ni KeyboardInterrupt; BaseException sí, lo cual puede impedir cerrar el programa', 'correct' => true],
                    ['text' => 'Son equivalentes; BaseException es un alias de Exception', 'correct' => false],
                    ['text' => 'Exception solo captura errores de sintaxis', 'correct' => false],
                    ['text' => 'BaseException es un tipo que no existe en Python 3', 'correct' => false],
                ],
            ],

            // ── 12 · Decoradores ──────────────────────────────────────
            [
                'question'    => '¿Por qué se debe usar `@functools.wraps(func)` al escribir un decorador?',
                'explanation' => 'Sin @functools.wraps, la función decorada pierde sus metadatos originales (__name__, __doc__, __module__). Esto rompe herramientas de debugging, documentación automática, y funciones como help(). wraps copia los atributos de la función original al wrapper.',
                'options'     => [
                    ['text' => 'Para preservar los metadatos (__name__, __doc__) de la función original en el wrapper', 'correct' => true],
                    ['text' => 'Para hacer que el decorador sea más rápido', 'correct' => false],
                    ['text' => 'Es obligatorio o Python lanza un error de decoración', 'correct' => false],
                    ['text' => 'Para permitir apilar múltiples decoradores', 'correct' => false],
                ],
            ],

            // ── 13 · Iteradores y protocolos ──────────────────────────
            [
                'question'    => '¿Qué métodos debe implementar un objeto para ser un iterador en Python?',
                'explanation' => 'El protocolo iterador requiere dos métodos: __iter__() que retorna el propio iterador, y __next__() que retorna el siguiente valor o lanza StopIteration cuando se agotan los elementos. Un iterable solo necesita __iter__() que retorne un iterador.',
                'options'     => [
                    ['text' => '__iter__() que retorna self, y __next__() que retorna el siguiente valor o lanza StopIteration', 'correct' => true],
                    ['text' => 'Solo __getitem__() con índices incrementales', 'correct' => false],
                    ['text' => '__len__() y __contains__() para soportar for-in', 'correct' => false],
                    ['text' => 'yield y return dentro de cualquier función', 'correct' => false],
                ],
            ],

            // ── 14 · Programación funcional ───────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre `map(func, iterable)` y una list comprehension `[func(x) for x in iterable]`?',
                'explanation' => 'map() retorna un iterador (lazy, no materializa en memoria), mientras que la list comprehension retorna una lista completa. Ambos aplican func a cada elemento, pero map puede ser mejor para grandes datasets. Las comprehensions son más Pythónicas y flexibles (permiten condiciones con if).',
                'options'     => [
                    ['text' => 'map() retorna un iterador lazy, la comprehension materializa toda la lista; la comprehension es más Pythónica', 'correct' => true],
                    ['text' => 'map() es siempre más rápido que la comprehension', 'correct' => false],
                    ['text' => 'Son exactamente equivalentes en rendimiento y resultado', 'correct' => false],
                    ['text' => 'map() solo funciona con funciones lambda, no con funciones nombradas', 'correct' => false],
                ],
            ],

            // ── 15 · Manejo de datos (JSON, CSV) ─────────────────────
            [
                'question'    => '¿Qué precaución se debe tener al deserializar datos con `json.loads()` de una fuente no confiable?',
                'explanation' => 'json.loads() es seguro contra ejecución de código (a diferencia de eval() o pickle). Sin embargo, un JSON malicioso puede ser extremadamente grande o profundamente anidado, causando DoS por consumo de memoria o recursión. Se recomienda validar el tamaño y la estructura del JSON antes de procesarlo.',
                'options'     => [
                    ['text' => 'JSON muy grande o profundamente anidado puede causar DoS por consumo de memoria; validar tamaño y estructura', 'correct' => true],
                    ['text' => 'json.loads() puede ejecutar código arbitrario embebido en el JSON', 'correct' => false],
                    ['text' => 'No hay riesgos; JSON es inherentemente seguro', 'correct' => false],
                    ['text' => 'Solo funciona con strings ASCII, no UTF-8', 'correct' => false],
                ],
            ],

            // ── 16 · Entornos virtuales y pip ─────────────────────────
            [
                'question'    => '¿Cuál es el propósito principal de un entorno virtual (venv) en Python?',
                'explanation' => 'Un venv crea un entorno aislado con su propio directorio de paquetes, evitando conflictos entre proyectos que requieren diferentes versiones de las mismas dependencias. Cada proyecto puede tener sus propias versiones de paquetes sin afectar al Python del sistema ni a otros proyectos.',
                'options'     => [
                    ['text' => 'Aislar las dependencias de cada proyecto para evitar conflictos de versiones entre proyectos', 'correct' => true],
                    ['text' => 'Mejorar el rendimiento del intérprete Python', 'correct' => false],
                    ['text' => 'Ejecutar código Python en un sandbox de seguridad', 'correct' => false],
                    ['text' => 'Compilar Python a código máquina nativo', 'correct' => false],
                ],
            ],

            // ── 17 · Testing ──────────────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre unittest.mock.patch y unittest.mock.MagicMock?',
                'explanation' => 'MagicMock es un objeto que imita cualquier interfaz (acepta cualquier llamada y atributo). patch es un decorador/context manager que reemplaza temporalmente un objeto real por un mock durante el test y lo restaura después. patch usa MagicMock internamente por defecto.',
                'options'     => [
                    ['text' => 'patch reemplaza temporalmente un objeto por un mock y lo restaura; MagicMock es el objeto mock en sí', 'correct' => true],
                    ['text' => 'MagicMock es para clases y patch es para funciones', 'correct' => false],
                    ['text' => 'patch solo funciona en testing de Django, MagicMock es general', 'correct' => false],
                    ['text' => 'Son herramientas de frameworks diferentes (pytest vs unittest)', 'correct' => false],
                ],
            ],

            // ── 18 · Buenas prácticas y PEP8 ─────────────────────────
            [
                'question'    => '¿Qué establece el principio "Explicit is better than implicit" del Zen de Python?',
                'explanation' => 'Este principio dice que el código debe expresar claramente su intención. Es mejor escribir código un poco más largo pero obvio, que código corto pero críptico. Por ejemplo, usar nombres descriptivos en vez de abreviaciones, o hacer imports explícitos en vez de wildcard (from x import *).',
                'options'     => [
                    ['text' => 'Que el código debe expresar su intención de forma clara y obvia, evitando magia implícita o atajos crípticos', 'correct' => true],
                    ['text' => 'Que se deben usar type hints en todas las funciones', 'correct' => false],
                    ['text' => 'Que todas las clases deben heredar explícitamente de object', 'correct' => false],
                    ['text' => 'Que no se debe usar indentación implícita', 'correct' => false],
                ],
            ],

            // ── 19 · Proyecto final ───────────────────────────────────
            [
                'question'    => '¿Qué archivo se usa para definir los metadatos y dependencias de un paquete Python moderno?',
                'explanation' => 'pyproject.toml (PEP 518/621) es el estándar moderno para definir metadatos del proyecto, dependencias, y configuración de herramientas. Reemplaza a setup.py/setup.cfg. Aunque setup.py sigue funcionando, pyproject.toml es el formato recomendado por el Python Packaging Authority.',
                'options'     => [
                    ['text' => 'pyproject.toml — es el estándar moderno (PEP 621) que reemplaza a setup.py', 'correct' => true],
                    ['text' => 'package.json — igual que en Node.js', 'correct' => false],
                    ['text' => 'requirements.txt — ahí se definen metadatos y dependencias', 'correct' => false],
                    ['text' => 'Pipfile.lock — es el estándar oficial', 'correct' => false],
                ],
            ],

            // ── 20 · Pregunta integradora ─────────────────────────────
            [
                'question'    => '¿Qué patrón de diseño implementan los context managers de Python (with statement)?',
                'explanation' => 'Los context managers implementan el patrón RAII (Resource Acquisition Is Initialization) adaptado a Python. Garantizan que los recursos se adquieran al entrar (__enter__) y se liberen al salir (__exit__), sin importar si hubo excepciones. Es fundamental para archivos, conexiones de BD, locks, etc.',
                'options'     => [
                    ['text' => 'RAII adaptado: adquisición de recurso al entrar y liberación garantizada al salir, incluso con excepciones', 'correct' => true],
                    ['text' => 'Observer: notifica a los suscriptores cuando el recurso cambia', 'correct' => false],
                    ['text' => 'Singleton: garantiza una única instancia del recurso', 'correct' => false],
                    ['text' => 'Factory: crea instancias de recursos según el tipo solicitado', 'correct' => false],
                ],
            ],
        ];
    }
}
