<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class ExcelIntermediateQuizSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'excel-intermedio')->first();

        if (! $course) {
            $this->command->warn('Excel Intermedio course not found.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id],
            [
                'title'       => 'Evaluación: Excel Intermedio',
                'description' => 'Pon a prueba tus conocimientos de BUSCARV, tablas dinámicas, funciones condicionales, arrays y más.',
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

        $this->command->info("Excel Intermedio quiz seeded: {$quiz->questions()->count()} preguntas.");
    }

    private function questions(): array
    {
        return [
            [
                'question' => '¿Cuál es la principal limitación de BUSCARV?',
                'explanation' => 'BUSCARV solo puede buscar en la primera columna del rango y devolver valores de columnas a la derecha. No puede buscar hacia la izquierda.',
                'options' => [
                    ['text' => 'Solo busca hacia la derecha (la columna clave debe ser la primera)', 'correct' => true],
                    ['text' => 'No puede manejar más de 100 filas', 'correct' => false],
                    ['text' => 'Solo funciona con números', 'correct' => false],
                    ['text' => 'No permite coincidencia exacta', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué hace =SUMAR.SI.CONJUNTO(E:E, B:B, "Ventas", C:C, ">1000")?',
                'explanation' => 'SUMAR.SI.CONJUNTO suma valores de E donde B="Ventas" Y C>1000. Es la versión multi-criterio de SUMAR.SI.',
                'options' => [
                    ['text' => 'Suma la columna E donde B es "Ventas" y C es mayor a 1000', 'correct' => true],
                    ['text' => 'Suma la columna E donde B es "Ventas" o C es mayor a 1000', 'correct' => false],
                    ['text' => 'Cuenta las filas que cumplen ambas condiciones', 'correct' => false],
                    ['text' => 'Suma las columnas B y C condicionalmente', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Cuáles son las 4 áreas de una tabla dinámica?',
                'explanation' => 'Las tablas dinámicas tienen 4 áreas: Filas (etiquetas de fila), Columnas (etiquetas de columna), Valores (cálculos) y Filtros (filtro general).',
                'options' => [
                    ['text' => 'Filas, Columnas, Valores y Filtros', 'correct' => true],
                    ['text' => 'Datos, Formato, Gráfico y Filtro', 'correct' => false],
                    ['text' => 'Encabezados, Cuerpo, Totales y Filtros', 'correct' => false],
                    ['text' => 'Título, Datos, Resumen y Estilo', 'correct' => false],
                ],
            ],
            [
                'question' => 'En formato condicional con fórmula, ¿por qué se usa =$E2 en lugar de =E2?',
                'explanation' => 'El $ antes de E fija la columna, mientras que la fila 2 es relativa. Así, al aplicar la regla a todo el rango, siempre evalúa la columna E de cada fila.',
                'options' => [
                    ['text' => 'Para fijar la columna y que la fila sea relativa al evaluar cada celda del rango', 'correct' => true],
                    ['text' => 'No hay diferencia, ambas funcionan igual', 'correct' => false],
                    ['text' => 'El $ indica que el valor es monetario', 'correct' => false],
                    ['text' => 'Evita errores circulares', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué función reemplaza a SI anidados en Excel moderno?',
                'explanation' => 'SI.CONJUNTO evalúa múltiples condiciones en orden sin necesidad de anidar. Es más legible que múltiples SI dentro de SI.',
                'options' => [
                    ['text' => 'SI.CONJUNTO (IFS)', 'correct' => true],
                    ['text' => 'SI.MULTIPLE', 'correct' => false],
                    ['text' => 'CAMBIAR', 'correct' => false],
                    ['text' => 'CASO', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué ventaja tiene INDICE+COINCIDIR sobre BUSCARV?',
                'explanation' => 'INDICE+COINCIDIR puede buscar en cualquier dirección (izquierda, derecha) y no depende de números de columna fijos, haciéndolo más flexible y robusto.',
                'options' => [
                    ['text' => 'Puede buscar en cualquier dirección y no depende de números de columna fijos', 'correct' => true],
                    ['text' => 'Es más rápido para archivos pequeños', 'correct' => false],
                    ['text' => 'Soporta comodines y BUSCARV no', 'correct' => false],
                    ['text' => 'No requiere que los datos estén en una tabla', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué hace la función FILTRAR en Excel 365?',
                'explanation' => 'FILTRAR devuelve dinámicamente las filas de un rango que cumplen las condiciones especificadas. El resultado se "derrama" automáticamente.',
                'options' => [
                    ['text' => 'Devuelve un rango dinámico con las filas que cumplen una condición', 'correct' => true],
                    ['text' => 'Aplica el autofiltro a una tabla', 'correct' => false],
                    ['text' => 'Oculta las filas que no cumplen el criterio', 'correct' => false],
                    ['text' => 'Elimina los datos duplicados', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué es el "derrame" (spill) en arrays dinámicos de Excel 365?',
                'explanation' => 'El derrame es cuando una fórmula devuelve múltiples valores que se expanden automáticamente a celdas adyacentes. El operador # referencia el rango derramado.',
                'options' => [
                    ['text' => 'Cuando una fórmula devuelve múltiples valores que se expanden a celdas adyacentes', 'correct' => true],
                    ['text' => 'Un error que ocurre cuando una celda tiene demasiado texto', 'correct' => false],
                    ['text' => 'El proceso de copiar una fórmula a todo un rango', 'correct' => false],
                    ['text' => 'Una función que divide texto en múltiples celdas', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Para qué sirve la función SUMAPRODUCTO?',
                'explanation' => 'SUMAPRODUCTO multiplica los elementos correspondientes de dos o más arrays y suma los resultados. Es la navaja suiza para sumas condicionales complejas.',
                'options' => [
                    ['text' => 'Multiplica arrays elemento por elemento y suma los resultados', 'correct' => true],
                    ['text' => 'Suma todos los productos de una tabla', 'correct' => false],
                    ['text' => 'Calcula el producto de una suma', 'correct' => false],
                    ['text' => 'Cuenta los productos en un inventario', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué hace =LET(x, A1*2, y, B1+x, y^2) en Excel 365?',
                'explanation' => 'LET define variables locales: x = A1*2, y = B1+x, y devuelve y^2. Mejora legibilidad y evita recalcular expresiones repetidas.',
                'options' => [
                    ['text' => 'Define variables locales (x e y) y devuelve y² como resultado', 'correct' => true],
                    ['text' => 'Crea dos celdas con nombre x e y', 'correct' => false],
                    ['text' => 'Es una macro que asigna valores', 'correct' => false],
                    ['text' => 'Genera un error porque no se pueden usar variables en fórmulas', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué son las segmentaciones (Slicers) en tablas dinámicas?',
                'explanation' => 'Las segmentaciones son filtros visuales con botones que permiten filtrar una o más tablas dinámicas de forma interactiva y fácil de usar.',
                'options' => [
                    ['text' => 'Filtros visuales con botones para filtrar tablas dinámicas interactivamente', 'correct' => true],
                    ['text' => 'Herramientas para dividir una tabla en partes', 'correct' => false],
                    ['text' => 'Gráficos pequeños dentro de celdas', 'correct' => false],
                    ['text' => 'Separadores de hojas para impresión', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué función oculta de Excel calcula la diferencia entre fechas?',
                'explanation' => 'SIFECHA (DATEDIF) es una función oculta que no aparece en autocompletado. Calcula diferencias en años ("Y"), meses ("M") o días ("D").',
                'options' => [
                    ['text' => 'SIFECHA (DATEDIF)', 'correct' => true],
                    ['text' => 'DIFERENCIA.FECHA', 'correct' => false],
                    ['text' => 'FECHA.DIF', 'correct' => false],
                    ['text' => 'RESTAR.FECHAS', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué hace =SUBTOTALES(109, A2:A100)?',
                'explanation' => 'SUBTOTALES con código 109 calcula SUMA ignorando filas ocultas por filtros. Los códigos 101-111 ignoran filas filtradas; 1-11 no las ignoran.',
                'options' => [
                    ['text' => 'Suma el rango ignorando filas ocultas por filtros', 'correct' => true],
                    ['text' => 'Suma solo las primeras 109 filas', 'correct' => false],
                    ['text' => 'Calcula el subtotal de impuestos', 'correct' => false],
                    ['text' => 'Genera un error porque 109 no es un código válido', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Cuál es la mejor forma de crear listas desplegables dependientes (cascada)?',
                'explanation' => 'Se usan rangos con nombre (nombradas igual que las opciones del primer combo) y la función INDIRECTO en la validación de la segunda lista.',
                'options' => [
                    ['text' => 'Rangos con nombre + INDIRECTO en la validación', 'correct' => true],
                    ['text' => 'Dos validaciones de lista independientes', 'correct' => false],
                    ['text' => 'Formato condicional con listas', 'correct' => false],
                    ['text' => 'Solo se puede hacer con VBA', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué herramienta de Excel permite rastrear qué celdas alimentan una fórmula?',
                'explanation' => 'Rastrear precedentes (en Fórmulas) muestra flechas desde las celdas que alimentan la fórmula seleccionada. Rastrear dependientes muestra qué celdas usan el valor.',
                'options' => [
                    ['text' => 'Rastrear precedentes', 'correct' => true],
                    ['text' => 'Inspeccionar fórmula', 'correct' => false],
                    ['text' => 'Buscar y reemplazar', 'correct' => false],
                    ['text' => 'Administrador de nombres', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué operador actúa como AND dentro de SUMAPRODUCTO?',
                'explanation' => 'En SUMAPRODUCTO, el * (multiplicación) actúa como AND: (condición1)*(condición2) devuelve 1 solo si ambas son verdaderas. El + actúa como OR.',
                'options' => [
                    ['text' => '* (multiplicación)', 'correct' => true],
                    ['text' => '+ (suma)', 'correct' => false],
                    ['text' => '& (concatenación)', 'correct' => false],
                    ['text' => 'Y() (función AND)', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué hace la función DIAS.LAB (NETWORKDAYS)?',
                'explanation' => 'DIAS.LAB calcula el número de días laborables entre dos fechas, excluyendo fines de semana y opcionalmente festivos.',
                'options' => [
                    ['text' => 'Calcula los días laborables entre dos fechas excluyendo fines de semana y festivos', 'correct' => true],
                    ['text' => 'Calcula los días del calendario entre dos fechas', 'correct' => false],
                    ['text' => 'Devuelve el número de la semana laboral', 'correct' => false],
                    ['text' => 'Cuenta los días hábiles en un mes', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué es una macro en Excel?',
                'explanation' => 'Una macro es un programa en VBA que automatiza una serie de acciones. Se puede grabar con la grabadora o escribir manualmente en el editor VBA.',
                'options' => [
                    ['text' => 'Un programa en VBA que automatiza tareas repetitivas', 'correct' => true],
                    ['text' => 'Una fórmula especialmente compleja', 'correct' => false],
                    ['text' => 'Un tipo de gráfico grande', 'correct' => false],
                    ['text' => 'Un complemento externo de Excel', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Cuándo se debe usar BUSCARV con VERDADERO (coincidencia aproximada)?',
                'explanation' => 'VERDADERO (aproximada) es ideal para tablas de rangos como comisiones, impuestos o calificaciones donde buscas el valor más cercano menor o igual. Los datos DEBEN estar ordenados.',
                'options' => [
                    ['text' => 'En tablas de rangos (comisiones, impuestos) con datos ordenados', 'correct' => true],
                    ['text' => 'Cuando no sabes la ortografía exacta del valor', 'correct' => false],
                    ['text' => 'Siempre, porque es más rápido que FALSO', 'correct' => false],
                    ['text' => 'Cuando los datos tienen duplicados', 'correct' => false],
                ],
            ],
            [
                'question' => '¿En qué formato se debe guardar un archivo de Excel con macros?',
                'explanation' => 'Los archivos con macros deben guardarse como .xlsm (habilitado para macros). El formato .xlsx NO guarda macros y Excel lo advertirá.',
                'options' => [
                    ['text' => '.xlsm (habilitado para macros)', 'correct' => true],
                    ['text' => '.xlsx (libro normal)', 'correct' => false],
                    ['text' => '.xls (formato antiguo)', 'correct' => false],
                    ['text' => '.csv (texto separado por comas)', 'correct' => false],
                ],
            ],
        ];
    }
}
