<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class ExcelBeginnerQuizSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'excel-principiante')->first();

        if (! $course) {
            $this->command->warn('Excel Principiante course not found.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id],
            [
                'title'       => 'Evaluación: Excel Principiante',
                'description' => 'Pon a prueba tus conocimientos básicos de Excel: fórmulas, funciones, formato y más.',
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

        $this->command->info("Excel Principiante quiz seeded: {$quiz->questions()->count()} preguntas.");
    }

    private function questions(): array
    {
        return [
            [
                'question' => '¿Cuál es la intersección de una fila y una columna en Excel?',
                'explanation' => 'Una celda es la intersección de una fila (horizontal, números) y una columna (vertical, letras). Se identifica por su referencia como A1, B3, etc.',
                'options' => [
                    ['text' => 'Una celda', 'correct' => true],
                    ['text' => 'Un rango', 'correct' => false],
                    ['text' => 'Una hoja', 'correct' => false],
                    ['text' => 'Un libro', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Cómo se inicia una fórmula en Excel?',
                'explanation' => 'Toda fórmula en Excel debe comenzar con el signo igual (=). Sin él, Excel interpreta la entrada como texto.',
                'options' => [
                    ['text' => 'Con el signo =', 'correct' => true],
                    ['text' => 'Con el signo +', 'correct' => false],
                    ['text' => 'Con la palabra FORMULA', 'correct' => false],
                    ['text' => 'Con paréntesis ()', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué resultado da la fórmula =5+3*2 en Excel?',
                'explanation' => 'Excel respeta la jerarquía de operaciones. Primero multiplica 3*2=6, luego suma 5+6=11.',
                'options' => [
                    ['text' => '11', 'correct' => true],
                    ['text' => '16', 'correct' => false],
                    ['text' => '13', 'correct' => false],
                    ['text' => '10', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué hace la referencia $A$1?',
                'explanation' => 'El signo $ fija la columna ($A) y la fila ($1). Es una referencia absoluta que no cambia al copiar la fórmula a otras celdas.',
                'options' => [
                    ['text' => 'Es una referencia absoluta que no cambia al copiar la fórmula', 'correct' => true],
                    ['text' => 'Indica que la celda contiene un valor monetario', 'correct' => false],
                    ['text' => 'Es una referencia relativa normal', 'correct' => false],
                    ['text' => 'Bloquea la celda para que no se pueda editar', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué función calcula el promedio de un rango?',
                'explanation' => 'PROMEDIO (AVERAGE en inglés) calcula la media aritmética dividiendo la suma entre el conteo de valores numéricos.',
                'options' => [
                    ['text' => '=PROMEDIO(A1:A10)', 'correct' => true],
                    ['text' => '=MEDIA(A1:A10)', 'correct' => false],
                    ['text' => '=SUMA(A1:A10)/10', 'correct' => false],
                    ['text' => '=PROM(A1:A10)', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Cuál es la diferencia entre CONTAR y CONTARA?',
                'explanation' => 'CONTAR solo cuenta celdas con números. CONTARA cuenta todas las celdas no vacías, incluyendo texto, fechas y valores lógicos.',
                'options' => [
                    ['text' => 'CONTAR cuenta solo números, CONTARA cuenta celdas no vacías', 'correct' => true],
                    ['text' => 'No hay diferencia, son la misma función', 'correct' => false],
                    ['text' => 'CONTARA cuenta solo texto', 'correct' => false],
                    ['text' => 'CONTAR incluye celdas vacías, CONTARA no', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué atajo de teclado selecciona todas las celdas de la hoja?',
                'explanation' => 'Ctrl+A selecciona toda la hoja activa. También puedes hacer clic en el botón "Seleccionar todo" (esquina superior izquierda).',
                'options' => [
                    ['text' => 'Ctrl+A', 'correct' => true],
                    ['text' => 'Ctrl+S', 'correct' => false],
                    ['text' => 'Ctrl+E', 'correct' => false],
                    ['text' => 'Alt+A', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué función SI devuelve si la condición es verdadera?\n=SI(A1>10, "Alto", "Bajo")',
                'explanation' => 'La función SI evalúa la condición (A1>10). Si es verdadera, devuelve el segundo argumento ("Alto"). Si es falsa, devuelve el tercero ("Bajo").',
                'options' => [
                    ['text' => '"Alto"', 'correct' => true],
                    ['text' => '"Bajo"', 'correct' => false],
                    ['text' => 'VERDADERO', 'correct' => false],
                    ['text' => '10', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Cómo se convierte un rango de datos en una tabla de Excel?',
                'explanation' => 'Ctrl+T (o Ctrl+L) convierte un rango con encabezados en una tabla de Excel, agregando filtros automáticos, formato alterno y referencias estructuradas.',
                'options' => [
                    ['text' => 'Seleccionar rango y presionar Ctrl+T', 'correct' => true],
                    ['text' => 'Clic derecho → Convertir a tabla', 'correct' => false],
                    ['text' => 'Pestaña Vista → Nueva tabla', 'correct' => false],
                    ['text' => 'Las tablas se crean automáticamente', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué función extrae los primeros N caracteres de un texto?',
                'explanation' => 'IZQUIERDA(texto, n) devuelve los primeros n caracteres. DERECHA devuelve los últimos, y EXTRAE devuelve caracteres desde una posición específica.',
                'options' => [
                    ['text' => '=IZQUIERDA(texto, n)', 'correct' => true],
                    ['text' => '=DERECHA(texto, n)', 'correct' => false],
                    ['text' => '=EXTRAE(texto, 1, n)', 'correct' => false],
                    ['text' => '=PRIMEROS(texto, n)', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué función devuelve la fecha actual en Excel?',
                'explanation' => 'HOY() devuelve la fecha actual sin hora. AHORA() devuelve fecha y hora. Ambas se actualizan automáticamente al recalcular.',
                'options' => [
                    ['text' => '=HOY()', 'correct' => true],
                    ['text' => '=FECHA()', 'correct' => false],
                    ['text' => '=ACTUAL()', 'correct' => false],
                    ['text' => '=DIA()', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué hace el formato condicional en Excel?',
                'explanation' => 'El formato condicional aplica formato visual (colores, iconos, barras) automáticamente según el valor de las celdas, facilitando la identificación de patrones.',
                'options' => [
                    ['text' => 'Aplica formato visual automáticamente según el valor de las celdas', 'correct' => true],
                    ['text' => 'Formatea celdas solo cuando el usuario lo solicita', 'correct' => false],
                    ['text' => 'Convierte texto a números automáticamente', 'correct' => false],
                    ['text' => 'Aplica fórmulas condicionalmente', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Cómo se referencia la celda A1 de la Hoja2 desde la Hoja1?',
                'explanation' => 'Para referenciar celdas de otra hoja, se usa el formato NombreHoja!Celda. Si el nombre tiene espacios, se envuelve en comillas simples.',
                'options' => [
                    ['text' => '=Hoja2!A1', 'correct' => true],
                    ['text' => '=A1@Hoja2', 'correct' => false],
                    ['text' => '=Hoja2.A1', 'correct' => false],
                    ['text' => '=Hoja2:A1', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué es la validación de datos en Excel?',
                'explanation' => 'La validación de datos permite definir reglas sobre qué valores pueden ingresarse en una celda, como listas desplegables, rangos numéricos o restricciones de fecha.',
                'options' => [
                    ['text' => 'Una herramienta para restringir qué valores pueden ingresarse en una celda', 'correct' => true],
                    ['text' => 'Un proceso para verificar si las fórmulas son correctas', 'correct' => false],
                    ['text' => 'Una función que valida emails y teléfonos', 'correct' => false],
                    ['text' => 'Un complemento que se instala aparte', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué tipo de referencia cambia la fila pero no la columna al copiar?',
                'explanation' => '$A1 es una referencia mixta donde la columna A está fija ($A) pero la fila es relativa (1). Al copiar hacia abajo, la fila cambia; al copiar a la derecha, la columna no.',
                'options' => [
                    ['text' => 'Referencia mixta: $A1', 'correct' => true],
                    ['text' => 'Referencia absoluta: $A$1', 'correct' => false],
                    ['text' => 'Referencia relativa: A1', 'correct' => false],
                    ['text' => 'Referencia mixta: A$1', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Cuál es el atajo para guardar un archivo en Excel?',
                'explanation' => 'Ctrl+S guarda el archivo actual. Ctrl+G es guardar como en algunas versiones en español. F12 abre Guardar como directamente.',
                'options' => [
                    ['text' => 'Ctrl+S', 'correct' => true],
                    ['text' => 'Ctrl+G', 'correct' => false],
                    ['text' => 'Alt+S', 'correct' => false],
                    ['text' => 'Ctrl+P', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué función devuelve el valor más alto de un rango?',
                'explanation' => 'MAX devuelve el valor máximo (más alto) de un rango. MIN devuelve el mínimo. Ambas ignoran celdas vacías y de texto.',
                'options' => [
                    ['text' => '=MAX(rango)', 'correct' => true],
                    ['text' => '=MAYOR(rango)', 'correct' => false],
                    ['text' => '=ALTO(rango)', 'correct' => false],
                    ['text' => '=SUPERIOR(rango)', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué sucede al presionar F4 mientras editas una referencia de celda en una fórmula?',
                'explanation' => 'F4 cicla entre tipos de referencia: A1 → $A$1 → A$1 → $A1 → A1. Es la forma más rápida de cambiar entre relativa, absoluta y mixta.',
                'options' => [
                    ['text' => 'Cicla entre referencia relativa, absoluta y mixta', 'correct' => true],
                    ['text' => 'Repite la última acción', 'correct' => false],
                    ['text' => 'Abre el diálogo de nombre de rango', 'correct' => false],
                    ['text' => 'Cierra el editor de fórmulas', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué operador se usa para concatenar texto en fórmulas?',
                'explanation' => 'El operador & une (concatena) textos. También existe la función CONCATENAR, pero & es más práctico: ="Hola " & A1 & "!".',
                'options' => [
                    ['text' => '& (ampersand)', 'correct' => true],
                    ['text' => '+ (más)', 'correct' => false],
                    ['text' => '| (pipe)', 'correct' => false],
                    ['text' => '. (punto)', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Para qué sirve Ctrl+Z en Excel?',
                'explanation' => 'Ctrl+Z deshace la última acción realizada. Puedes presionarlo múltiples veces para deshacer varias acciones. Ctrl+Y rehace la acción deshecha.',
                'options' => [
                    ['text' => 'Deshacer la última acción', 'correct' => true],
                    ['text' => 'Hacer zoom', 'correct' => false],
                    ['text' => 'Cerrar el archivo', 'correct' => false],
                    ['text' => 'Copiar la selección', 'correct' => false],
                ],
            ],
        ];
    }
}
