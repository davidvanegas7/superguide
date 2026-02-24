<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ExcelIntermediateLessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'excel-intermedio')->first();

        if (! $course) {
            $this->command->warn('Excel Intermedio course not found. Run CourseSeeder first.');
            return;
        }

        $tagIntermedio = Tag::where('slug', 'intermedio')->first();

        $lessons = [
            ['slug' => 'buscarv-buscarh', 'title' => 'BUSCARV y BUSCARH: búsquedas en tablas', 'md_file_path' => 'content/lessons/excel/excel-intermedio-01-buscarv.md', 'excerpt' => 'Domina BUSCARV, BUSCARH, INDICE+COINCIDIR y BUSCARX.', 'published' => true, 'sort_order' => 1, 'duration_minutes' => 25],
            ['slug' => 'funciones-condicionales', 'title' => 'Funciones condicionales: SUMAR.SI, CONTAR.SI', 'md_file_path' => 'content/lessons/excel/excel-intermedio-02-funciones-condicionales.md', 'excerpt' => 'SUMAR.SI, CONTAR.SI, PROMEDIO.SI y sus versiones .CONJUNTO.', 'published' => true, 'sort_order' => 2, 'duration_minutes' => 25],
            ['slug' => 'tablas-dinamicas', 'title' => 'Tablas dinámicas (Pivot Tables)', 'md_file_path' => 'content/lessons/excel/excel-intermedio-03-tablas-dinamicas.md', 'excerpt' => 'La herramienta más poderosa de Excel para analizar grandes volúmenes de datos.', 'published' => true, 'sort_order' => 3, 'duration_minutes' => 30],
            ['slug' => 'formato-condicional-avanzado', 'title' => 'Formato condicional avanzado', 'md_file_path' => 'content/lessons/excel/excel-intermedio-04-formato-condicional-avanzado.md', 'excerpt' => 'Fórmulas personalizadas, semáforos y mapas de calor.', 'published' => true, 'sort_order' => 4, 'duration_minutes' => 20],
            ['slug' => 'funciones-logicas-avanzadas', 'title' => 'Funciones lógicas avanzadas', 'md_file_path' => 'content/lessons/excel/excel-intermedio-05-funciones-logicas.md', 'excerpt' => 'SI.CONJUNTO, CAMBIAR, LET, LAMBDA y SUMAPRODUCTO.', 'published' => true, 'sort_order' => 5, 'duration_minutes' => 25],
            ['slug' => 'graficos-avanzados', 'title' => 'Gráficos avanzados y visualización', 'md_file_path' => 'content/lessons/excel/excel-intermedio-06-graficos-avanzados.md', 'excerpt' => 'Gráficos combinados, cascada, embudo, dashboards y sparklines.', 'published' => true, 'sort_order' => 6, 'duration_minutes' => 25],
            ['slug' => 'validacion-avanzada', 'title' => 'Validación de datos avanzada', 'md_file_path' => 'content/lessons/excel/excel-intermedio-07-validacion-avanzada.md', 'excerpt' => 'Listas dependientes, fórmulas de validación y formularios robustos.', 'published' => true, 'sort_order' => 7, 'duration_minutes' => 20],
            ['slug' => 'busquedas-avanzadas', 'title' => 'Funciones de búsqueda avanzadas', 'md_file_path' => 'content/lessons/excel/excel-intermedio-08-busquedas-avanzadas.md', 'excerpt' => 'FILTRAR, ORDENAR, UNICOS y búsquedas con múltiples criterios.', 'published' => true, 'sort_order' => 8, 'duration_minutes' => 25],
            ['slug' => 'matematicas-estadisticas', 'title' => 'Funciones matemáticas y estadísticas', 'md_file_path' => 'content/lessons/excel/excel-intermedio-09-matematicas-estadisticas.md', 'excerpt' => 'SUMAPRODUCTO, redondeo, percentiles, desviación estándar y más.', 'published' => true, 'sort_order' => 9, 'duration_minutes' => 25],
            ['slug' => 'arrays-dinamicos', 'title' => 'Fórmulas matriciales y arrays dinámicos', 'md_file_path' => 'content/lessons/excel/excel-intermedio-10-arrays-dinamicos.md', 'excerpt' => 'Arrays dinámicos, SECUENCIA, MAP, REDUCE y derrame automático.', 'published' => true, 'sort_order' => 10, 'duration_minutes' => 25],
            ['slug' => 'nombres-rangos', 'title' => 'Nombres definidos y rangos con nombre', 'md_file_path' => 'content/lessons/excel/excel-intermedio-11-nombres-rangos.md', 'excerpt' => 'Crea alias para rangos, constantes y fórmulas reutilizables.', 'published' => true, 'sort_order' => 11, 'duration_minutes' => 20],
            ['slug' => 'texto-avanzado', 'title' => 'Funciones de texto avanzadas', 'md_file_path' => 'content/lessons/excel/excel-intermedio-12-texto-avanzado.md', 'excerpt' => 'UNIRCADENAS, limpieza de datos, SUSTITUIR y TEXT.', 'published' => true, 'sort_order' => 12, 'duration_minutes' => 20],
            ['slug' => 'analisis-hipotesis', 'title' => 'Análisis de hipótesis y escenarios', 'md_file_path' => 'content/lessons/excel/excel-intermedio-13-analisis-hipotesis.md', 'excerpt' => 'Tablas de datos, Buscar objetivo, Solver y funciones financieras.', 'published' => true, 'sort_order' => 13, 'duration_minutes' => 25],
            ['slug' => 'importar-datos', 'title' => 'Importar y conectar datos externos', 'md_file_path' => 'content/lessons/excel/excel-intermedio-14-importar-datos.md', 'excerpt' => 'CSV, bases de datos, web y Power Query básico.', 'published' => true, 'sort_order' => 14, 'duration_minutes' => 20],
            ['slug' => 'fechas-avanzadas', 'title' => 'Funciones de fecha y hora avanzadas', 'md_file_path' => 'content/lessons/excel/excel-intermedio-15-fechas-avanzadas.md', 'excerpt' => 'DIAS.LAB, SIFECHA, FIN.MES y cálculos temporales avanzados.', 'published' => true, 'sort_order' => 15, 'duration_minutes' => 20],
            ['slug' => 'proteccion-auditoria', 'title' => 'Protección avanzada y auditoría de fórmulas', 'md_file_path' => 'content/lessons/excel/excel-intermedio-16-proteccion-auditoria.md', 'excerpt' => 'Rastrear precedentes, evaluar fórmulas paso a paso y protección por rangos.', 'published' => true, 'sort_order' => 16, 'duration_minutes' => 20],
            ['slug' => 'funciones-bd', 'title' => 'Funciones de base de datos', 'md_file_path' => 'content/lessons/excel/excel-intermedio-17-funciones-bd.md', 'excerpt' => 'BDSUMA, BDPROMEDIO, BDCONTAR y criterios estructurados.', 'published' => true, 'sort_order' => 17, 'duration_minutes' => 20],
            ['slug' => 'macros-intro', 'title' => 'Introducción a macros y automatización', 'md_file_path' => 'content/lessons/excel/excel-intermedio-18-macros-intro.md', 'excerpt' => 'Graba y edita tus primeras macros para automatizar tareas.', 'published' => true, 'sort_order' => 18, 'duration_minutes' => 25],
            ['slug' => 'proyecto-dashboard', 'title' => 'Proyecto: Dashboard de ventas interactivo', 'md_file_path' => 'content/lessons/excel/excel-intermedio-19-proyecto-dashboard.md', 'excerpt' => 'Construye un dashboard profesional con tablas dinámicas y gráficos.', 'published' => true, 'sort_order' => 19, 'duration_minutes' => 35],
        ];

        foreach ($lessons as $data) {
            Lesson::firstOrCreate(
                ['course_id' => $course->id, 'slug' => $data['slug']],
                array_merge($data, ['course_id' => $course->id])
            );
        }

        $tagIds = array_filter([$tagIntermedio?->id]);
        if ($tagIds) {
            foreach ($course->lessons as $lesson) {
                $lesson->tags()->syncWithoutDetaching($tagIds);
            }
        }

        $this->command->info('Excel Intermedio lessons seeded: ' . count($lessons));
    }
}
