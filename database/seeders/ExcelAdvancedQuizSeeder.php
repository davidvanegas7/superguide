<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class ExcelAdvancedQuizSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'excel-avanzado')->first();

        if (! $course) {
            $this->command->warn('Excel Avanzado course not found.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id],
            [
                'title'       => 'Evaluación: Excel Avanzado',
                'description' => 'Pon a prueba tus conocimientos de Power Query, DAX, VBA, LAMBDA y técnicas avanzadas.',
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

        $this->command->info("Excel Avanzado quiz seeded: {$quiz->questions()->count()} preguntas.");
    }

    private function questions(): array
    {
        return [
            [
                'question' => '¿Qué es Power Query en Excel?',
                'explanation' => 'Power Query es el motor ETL (Extract, Transform, Load) de Excel para importar, limpiar y transformar datos de múltiples fuentes de forma reproducible.',
                'options' => [
                    ['text' => 'El motor ETL para importar, limpiar y transformar datos', 'correct' => true],
                    ['text' => 'Una función de búsqueda avanzada', 'correct' => false],
                    ['text' => 'Un tipo de gráfico dinámico', 'correct' => false],
                    ['text' => 'Un complemento para consultas SQL', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Cuál es la función más importante de DAX en Power Pivot?',
                'explanation' => 'CALCULATE es la función central de DAX. Modifica el contexto de filtro, permitiendo calcular medidas con filtros diferentes a los aplicados en la tabla dinámica.',
                'options' => [
                    ['text' => 'CALCULATE (modifica el contexto de filtro)', 'correct' => true],
                    ['text' => 'SUM (suma valores)', 'correct' => false],
                    ['text' => 'FILTER (filtra filas)', 'correct' => false],
                    ['text' => 'RELATED (busca en tablas relacionadas)', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Cuál es la diferencia entre una medida y una columna calculada en DAX?',
                'explanation' => 'Las columnas calculadas se evalúan al actualizar datos (contexto de fila). Las medidas se calculan dinámicamente según los filtros de la tabla dinámica (contexto de filtro).',
                'options' => [
                    ['text' => 'Las columnas se calculan al actualizar datos; las medidas al consultar según los filtros activos', 'correct' => true],
                    ['text' => 'No hay diferencia, son lo mismo', 'correct' => false],
                    ['text' => 'Las medidas solo funcionan con números y las columnas con cualquier tipo', 'correct' => false],
                    ['text' => 'Las columnas calculadas son más rápidas', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué hace Option Explicit en VBA?',
                'explanation' => 'Option Explicit obliga a declarar todas las variables con Dim antes de usarlas. Previene errores difíciles de detectar causados por tipeos en nombres de variables.',
                'options' => [
                    ['text' => 'Obliga a declarar todas las variables, previniendo errores de tipeo', 'correct' => true],
                    ['text' => 'Acelera la ejecución del código', 'correct' => false],
                    ['text' => 'Permite usar tipos de datos explícitos', 'correct' => false],
                    ['text' => 'Activa la depuración detallada', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Por qué se usa Application.ScreenUpdating = False en VBA?',
                'explanation' => 'Desactivar ScreenUpdating evita que Excel redibuje la pantalla en cada cambio durante un macro, mejorando drásticamente el rendimiento.',
                'options' => [
                    ['text' => 'Para evitar que Excel redibuje la pantalla y mejorar el rendimiento', 'correct' => true],
                    ['text' => 'Para ocultar el código VBA al usuario', 'correct' => false],
                    ['text' => 'Para prevenir que el usuario vea errores', 'correct' => false],
                    ['text' => 'Para desactivar las fórmulas durante la ejecución', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué hace la función LAMBDA en Excel 365?',
                'explanation' => 'LAMBDA permite crear funciones personalizadas reutilizables sin VBA. Se asignan a un nombre definido y se usan como cualquier función nativa.',
                'options' => [
                    ['text' => 'Crea funciones personalizadas reutilizables sin necesidad de VBA', 'correct' => true],
                    ['text' => 'Ejecuta código VBA dentro de una celda', 'correct' => false],
                    ['text' => 'Crea expresiones regulares', 'correct' => false],
                    ['text' => 'Conecta con funciones de AWS Lambda', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Cuál es la ventaja de usar arrays en VBA sobre leer celda por celda?',
                'explanation' => 'Leer un rango completo a un array en memoria es instantáneo comparado con acceder celda por celda (cada acceso a celda implica comunicación COM). La diferencia puede ser de segundos vs minutos.',
                'options' => [
                    ['text' => 'Es dramáticamente más rápido porque evita accesos individuales a celdas', 'correct' => true],
                    ['text' => 'Permite usar más memoria RAM', 'correct' => false],
                    ['text' => 'Habilita cálculos estadísticos automáticos', 'correct' => false],
                    ['text' => 'Los arrays son más fáciles de programar', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué hace la función MAP en Excel 365?',
                'explanation' => 'MAP aplica una función LAMBDA a cada elemento de un array y devuelve los resultados como un nuevo array dinámico.',
                'options' => [
                    ['text' => 'Aplica una función LAMBDA a cada elemento de un array', 'correct' => true],
                    ['text' => 'Crea un gráfico de mapa con datos geográficos', 'correct' => false],
                    ['text' => 'Mapea columnas de una tabla a otra', 'correct' => false],
                    ['text' => 'Asigna nombres a rangos automáticamente', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué patrón de manejo de errores es el más robusto en VBA?',
                'explanation' => 'El patrón con sección Cleanup garantiza que Application.ScreenUpdating y Calculation se restauren siempre, incluso si hay errores. Resume Cleanup salta a la limpieza.',
                'options' => [
                    ['text' => 'On Error GoTo ErrorHandler con sección Cleanup que siempre restaura settings', 'correct' => true],
                    ['text' => 'On Error Resume Next en todo el código', 'correct' => false],
                    ['text' => 'Múltiples bloques On Error GoTo anidados', 'correct' => false],
                    ['text' => 'No usar manejo de errores y depurar manualmente', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué es el lenguaje M en el contexto de Power Query?',
                'explanation' => 'M (Mashup) es el lenguaje funcional detrás de Power Query. Cada transformación visual genera código M. Se puede editar directamente para operaciones avanzadas.',
                'options' => [
                    ['text' => 'El lenguaje funcional generado por Power Query para definir transformaciones', 'correct' => true],
                    ['text' => 'Una extensión de VBA para macros modernas', 'correct' => false],
                    ['text' => 'El lenguaje de Microsoft Access', 'correct' => false],
                    ['text' => 'Un compilador de fórmulas de Excel', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué son las funciones Time Intelligence en DAX?',
                'explanation' => 'Time Intelligence incluye funciones como TOTALYTD, SAMEPERIODLASTYEAR, DATEADD que calculan automáticamente acumulados, comparaciones periodo anterior y otras métricas temporales.',
                'options' => [
                    ['text' => 'Funciones DAX para cálculos temporales: YTD, período anterior, media móvil', 'correct' => true],
                    ['text' => 'Funciones que miden cuánto tarda una consulta', 'correct' => false],
                    ['text' => 'Herramientas de pronóstico basadas en IA', 'correct' => false],
                    ['text' => 'Funciones de fecha nativas de Excel como HOY() y AHORA()', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué son los Office Scripts?',
                'explanation' => 'Office Scripts son la alternativa moderna a VBA para Excel Online. Usan TypeScript, funcionan en la nube, y pueden ejecutarse desde Power Automate.',
                'options' => [
                    ['text' => 'La alternativa a VBA para Excel Online, escritos en TypeScript', 'correct' => true],
                    ['text' => 'Scripts de Google Apps Script adaptados para Office', 'correct' => false],
                    ['text' => 'Macros grabadas que se guardan en la nube', 'correct' => false],
                    ['text' => 'Plugins de JavaScript para Excel desktop', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué herramienta de Excel se activa con Alt+F11?',
                'explanation' => 'Alt+F11 abre el Editor de Visual Basic (VBE), donde se escribe, edita y depura código VBA. Incluye la ventana de proyecto, código, inmediato y locales.',
                'options' => [
                    ['text' => 'El Editor de Visual Basic (VBE)', 'correct' => true],
                    ['text' => 'Power Query Editor', 'correct' => false],
                    ['text' => 'El diálogo de macros', 'correct' => false],
                    ['text' => 'Las opciones de Excel', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué es un UserForm en VBA?',
                'explanation' => 'Un UserForm es un formulario visual personalizado creado en VBA con controles como TextBox, ComboBox, Buttons, etc. Permite crear interfaces de usuario profesionales.',
                'options' => [
                    ['text' => 'Un formulario visual con controles interactivos para entrada de datos', 'correct' => true],
                    ['text' => 'Un formato de usuario personalizado para celdas', 'correct' => false],
                    ['text' => 'Un tipo de validación de datos', 'correct' => false],
                    ['text' => 'Una plantilla de hoja de cálculo', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué son las fórmulas volátiles y por qué afectan el rendimiento?',
                'explanation' => 'Las funciones volátiles (HOY, AHORA, ALEATORIO, DESREF, INDIRECTO) se recalculan CADA VEZ que algo cambia en la hoja, sin importar si sus argumentos cambiaron.',
                'options' => [
                    ['text' => 'Funciones que se recalculan en cada cambio de la hoja, como HOY() y DESREF()', 'correct' => true],
                    ['text' => 'Fórmulas que pueden causar errores aleatorios', 'correct' => false],
                    ['text' => 'Fórmulas que se eliminan automáticamente al cerrar el archivo', 'correct' => false],
                    ['text' => 'Funciones que solo funcionan en la versión actual de Excel', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Cuál es la diferencia entre un modelo estrella y un esquema plano en Power Pivot?',
                'explanation' => 'El modelo estrella tiene una tabla de hechos central y tablas de dimensiones conectadas (estrella). El esquema plano es una sola tabla con datos duplicados, menos eficiente.',
                'options' => [
                    ['text' => 'El modelo estrella separa hechos y dimensiones en tablas relacionadas; el plano pone todo en una sola tabla', 'correct' => true],
                    ['text' => 'No hay diferencia, son nombres diferentes para lo mismo', 'correct' => false],
                    ['text' => 'El modelo estrella es solo para Power BI, no para Excel', 'correct' => false],
                    ['text' => 'El esquema plano es más eficiente para archivos grandes', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Cómo se consume una API REST desde VBA?',
                'explanation' => 'Se usa MSXML2.XMLHTTP (late binding con CreateObject) para hacer peticiones HTTP. Se configura método, URL, headers y se envía. La respuesta se procesa como texto/JSON.',
                'options' => [
                    ['text' => 'Con CreateObject("MSXML2.XMLHTTP") para hacer peticiones HTTP', 'correct' => true],
                    ['text' => 'Con la función WEB.SERVICIO nativa de Excel', 'correct' => false],
                    ['text' => 'No es posible consumir APIs desde VBA', 'correct' => false],
                    ['text' => 'Con un complemento externo obligatorio', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué herramienta de Excel permite optimización con restricciones (programación lineal)?',
                'explanation' => 'Solver encuentra valores óptimos (máximo, mínimo o específico) de una celda objetivo modificando celdas variables y respetando restricciones definidas.',
                'options' => [
                    ['text' => 'Solver', 'correct' => true],
                    ['text' => 'Buscar objetivo', 'correct' => false],
                    ['text' => 'Análisis de escenarios', 'correct' => false],
                    ['text' => 'Analysis ToolPak', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué permite Python en Excel (Microsoft 365)?',
                'explanation' => 'Python en Excel permite usar bibliotecas como pandas, matplotlib y scikit-learn directamente en celdas, combinando la interfaz de Excel con la potencia de Python.',
                'options' => [
                    ['text' => 'Usar pandas, matplotlib y scikit-learn directamente en celdas de Excel', 'correct' => true],
                    ['text' => 'Reemplazar VBA con scripts Python', 'correct' => false],
                    ['text' => 'Crear complementos de Excel en Python', 'correct' => false],
                    ['text' => 'Ejecutar Jupyter Notebooks dentro de Excel', 'correct' => false],
                ],
            ],
            [
                'question' => '¿Qué es un Add-in (.xlam) en Excel?',
                'explanation' => 'Un Add-in es un archivo .xlam que agrega funcionalidades a Excel: funciones personalizadas, botones en el Ribbon y automatizaciones disponibles en cualquier libro.',
                'options' => [
                    ['text' => 'Un archivo que agrega funciones y herramientas reutilizables a Excel', 'correct' => true],
                    ['text' => 'Un archivo de respaldo automático', 'correct' => false],
                    ['text' => 'Un formato de archivo para tablas dinámicas', 'correct' => false],
                    ['text' => 'Una plantilla de diseño para dashboards', 'correct' => false],
                ],
            ],
        ];
    }
}
