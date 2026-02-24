<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ExcelBeginnerLessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'excel-principiante')->first();

        if (! $course) {
            $this->command->warn('Excel Principiante course not found. Run CourseSeeder first.');
            return;
        }

        $tagPrincipiante = Tag::where('slug', 'principiante')->first();

        $lessons = [
            ['slug' => 'excel-intro', 'title' => 'Introducción a Excel: interfaz y conceptos básicos', 'md_file_path' => 'content/lessons/excel/excel-principiante-01-introduccion.md', 'excerpt' => 'Conoce la interfaz de Excel, libros, hojas, celdas y los conceptos fundamentales.', 'published' => true, 'sort_order' => 1, 'duration_minutes' => 15],
            ['slug' => 'formato-celdas', 'title' => 'Formato de celdas y tipos de datos', 'md_file_path' => 'content/lessons/excel/excel-principiante-02-formato-celdas.md', 'excerpt' => 'Números, texto, fechas, formato de celdas, alineación y bordes.', 'published' => true, 'sort_order' => 2, 'duration_minutes' => 15],
            ['slug' => 'formulas-basicas', 'title' => 'Fórmulas básicas: suma, resta, multiplicación, división', 'md_file_path' => 'content/lessons/excel/excel-principiante-03-formulas-basicas.md', 'excerpt' => 'Escribe tus primeras fórmulas con operadores aritméticos y referencias.', 'published' => true, 'sort_order' => 3, 'duration_minutes' => 20],
            ['slug' => 'referencias-celdas', 'title' => 'Referencias relativas, absolutas y mixtas', 'md_file_path' => 'content/lessons/excel/excel-principiante-04-referencias.md', 'excerpt' => 'Entiende la diferencia entre A1, $A$1 y $A1 al copiar fórmulas.', 'published' => true, 'sort_order' => 4, 'duration_minutes' => 20],
            ['slug' => 'funciones-basicas', 'title' => 'Funciones básicas: PROMEDIO, MAX, MIN, CONTAR', 'md_file_path' => 'content/lessons/excel/excel-principiante-05-funciones-basicas.md', 'excerpt' => 'Las funciones más utilizadas para análisis rápido de datos.', 'published' => true, 'sort_order' => 5, 'duration_minutes' => 20],
            ['slug' => 'rangos-seleccion', 'title' => 'Trabajar con rangos y selección', 'md_file_path' => 'content/lessons/excel/excel-principiante-06-rangos.md', 'excerpt' => 'Selección eficiente, autorrelleno y operaciones con rangos.', 'published' => true, 'sort_order' => 6, 'duration_minutes' => 15],
            ['slug' => 'ordenar-filtrar', 'title' => 'Ordenar y filtrar datos', 'md_file_path' => 'content/lessons/excel/excel-principiante-07-ordenar-filtrar.md', 'excerpt' => 'Organiza y filtra datos para encontrar información rápidamente.', 'published' => true, 'sort_order' => 7, 'duration_minutes' => 15],
            ['slug' => 'graficos-basicos', 'title' => 'Gráficos básicos', 'md_file_path' => 'content/lessons/excel/excel-principiante-08-graficos-basicos.md', 'excerpt' => 'Crea gráficos de barras, líneas, circulares y más.', 'published' => true, 'sort_order' => 8, 'duration_minutes' => 20],
            ['slug' => 'funcion-si', 'title' => 'Función SI (IF)', 'md_file_path' => 'content/lessons/excel/excel-principiante-09-funcion-si.md', 'excerpt' => 'Toma decisiones en tus hojas con la función condicional SI.', 'published' => true, 'sort_order' => 9, 'duration_minutes' => 20],
            ['slug' => 'funciones-texto', 'title' => 'Funciones de texto', 'md_file_path' => 'content/lessons/excel/excel-principiante-10-funciones-texto.md', 'excerpt' => 'CONCATENAR, MAYUSC, MINUSC, LARGO, IZQUIERDA, DERECHA y más.', 'published' => true, 'sort_order' => 10, 'duration_minutes' => 20],
            ['slug' => 'funciones-fecha', 'title' => 'Funciones de fecha y hora', 'md_file_path' => 'content/lessons/excel/excel-principiante-11-fechas.md', 'excerpt' => 'HOY, AHORA, AÑO, MES, DIA y cálculos con fechas.', 'published' => true, 'sort_order' => 11, 'duration_minutes' => 15],
            ['slug' => 'tablas-excel', 'title' => 'Tablas de Excel', 'md_file_path' => 'content/lessons/excel/excel-principiante-12-tablas.md', 'excerpt' => 'Convierte rangos en tablas con Ctrl+T y aprovecha sus ventajas.', 'published' => true, 'sort_order' => 12, 'duration_minutes' => 15],
            ['slug' => 'impresion-pagina', 'title' => 'Impresión y configuración de página', 'md_file_path' => 'content/lessons/excel/excel-principiante-13-impresion.md', 'excerpt' => 'Configura márgenes, orientación, encabezados y vista previa de impresión.', 'published' => true, 'sort_order' => 13, 'duration_minutes' => 10],
            ['slug' => 'formato-condicional-basico', 'title' => 'Formato condicional básico', 'md_file_path' => 'content/lessons/excel/excel-principiante-14-formato-condicional.md', 'excerpt' => 'Resalta automáticamente celdas según su valor con reglas visuales.', 'published' => true, 'sort_order' => 14, 'duration_minutes' => 15],
            ['slug' => 'multiples-hojas', 'title' => 'Trabajar con múltiples hojas', 'md_file_path' => 'content/lessons/excel/excel-principiante-15-multiples-hojas.md', 'excerpt' => 'Navega, referencia y organiza datos entre múltiples hojas.', 'published' => true, 'sort_order' => 15, 'duration_minutes' => 15],
            ['slug' => 'validacion-datos-basica', 'title' => 'Validación de datos básica', 'md_file_path' => 'content/lessons/excel/excel-principiante-16-validacion.md', 'excerpt' => 'Crea listas desplegables y restricciones de entrada de datos.', 'published' => true, 'sort_order' => 16, 'duration_minutes' => 15],
            ['slug' => 'atajos-teclado', 'title' => 'Atajos de teclado esenciales', 'md_file_path' => 'content/lessons/excel/excel-principiante-17-atajos.md', 'excerpt' => 'Los atajos más útiles para trabajar más rápido en Excel.', 'published' => true, 'sort_order' => 17, 'duration_minutes' => 10],
            ['slug' => 'proteger-compartir', 'title' => 'Proteger y compartir libros', 'md_file_path' => 'content/lessons/excel/excel-principiante-18-proteccion.md', 'excerpt' => 'Protege hojas, libros y celdas. Comparte archivos de forma segura.', 'published' => true, 'sort_order' => 18, 'duration_minutes' => 15],
            ['slug' => 'proyecto-presupuesto', 'title' => 'Proyecto final: presupuesto personal', 'md_file_path' => 'content/lessons/excel/excel-principiante-19-proyecto-presupuesto.md', 'excerpt' => 'Construye tu propio gestor de presupuesto aplicando todo lo aprendido.', 'published' => true, 'sort_order' => 19, 'duration_minutes' => 30],
        ];

        foreach ($lessons as $data) {
            Lesson::firstOrCreate(
                ['course_id' => $course->id, 'slug' => $data['slug']],
                array_merge($data, ['course_id' => $course->id])
            );
        }

        // Assign tags
        $tagIds = array_filter([$tagPrincipiante?->id]);
        if ($tagIds) {
            foreach ($course->lessons as $lesson) {
                $lesson->tags()->syncWithoutDetaching($tagIds);
            }
        }

        $this->command->info('Excel Principiante lessons seeded: ' . count($lessons));
    }
}
