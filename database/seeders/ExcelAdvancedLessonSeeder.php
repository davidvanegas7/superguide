<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ExcelAdvancedLessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'excel-avanzado')->first();

        if (! $course) {
            $this->command->warn('Excel Avanzado course not found. Run CourseSeeder first.');
            return;
        }

        $tagAvanzado = Tag::where('slug', 'avanzado')->first();

        $lessons = [
            ['slug' => 'power-query', 'title' => 'Power Query: transformación de datos profesional', 'md_file_path' => 'content/lessons/excel/excel-avanzado-01-power-query.md', 'excerpt' => 'Motor ETL de Excel para importar, limpiar y transformar datos.', 'published' => true, 'sort_order' => 1, 'duration_minutes' => 30],
            ['slug' => 'power-pivot', 'title' => 'Power Pivot y modelo de datos', 'md_file_path' => 'content/lessons/excel/excel-avanzado-02-power-pivot.md', 'excerpt' => 'Relaciones entre tablas, DAX introductorio y KPIs.', 'published' => true, 'sort_order' => 2, 'duration_minutes' => 30],
            ['slug' => 'dax-avanzado', 'title' => 'DAX: fórmulas avanzadas para análisis', 'md_file_path' => 'content/lessons/excel/excel-avanzado-03-dax.md', 'excerpt' => 'CALCULATE, Time Intelligence, RANKX y variables en DAX.', 'published' => true, 'sort_order' => 3, 'duration_minutes' => 30],
            ['slug' => 'vba-fundamentos', 'title' => 'VBA: fundamentos de programación en Excel', 'md_file_path' => 'content/lessons/excel/excel-avanzado-04-vba-fundamentos.md', 'excerpt' => 'Variables, bucles, condicionales y manipulación de rangos con VBA.', 'published' => true, 'sort_order' => 4, 'duration_minutes' => 30],
            ['slug' => 'vba-datos', 'title' => 'VBA: manipulación avanzada de datos', 'md_file_path' => 'content/lessons/excel/excel-avanzado-05-vba-datos.md', 'excerpt' => 'Arrays, Diccionarios, archivos y rendimiento en VBA.', 'published' => true, 'sort_order' => 5, 'duration_minutes' => 25],
            ['slug' => 'userforms', 'title' => 'UserForms: formularios interactivos en VBA', 'md_file_path' => 'content/lessons/excel/excel-avanzado-06-userforms.md', 'excerpt' => 'Construye interfaces gráficas personalizadas para entrada de datos.', 'published' => true, 'sort_order' => 6, 'duration_minutes' => 25],
            ['slug' => 'lambda-arrays', 'title' => 'Arrays dinámicos y funciones LAMBDA', 'md_file_path' => 'content/lessons/excel/excel-avanzado-07-lambda-arrays.md', 'excerpt' => 'LAMBDA, MAP, REDUCE, SCAN y funciones personalizadas sin VBA.', 'published' => true, 'sort_order' => 7, 'duration_minutes' => 25],
            ['slug' => 'dashboards-pro', 'title' => 'Dashboards profesionales y visualización avanzada', 'md_file_path' => 'content/lessons/excel/excel-avanzado-08-dashboards.md', 'excerpt' => 'KPIs, gráficos avanzados, controles interactivos y diseño ejecutivo.', 'published' => true, 'sort_order' => 8, 'duration_minutes' => 30],
            ['slug' => 'vba-eventos', 'title' => 'VBA: eventos y automatización avanzada', 'md_file_path' => 'content/lessons/excel/excel-avanzado-09-vba-eventos.md', 'excerpt' => 'Eventos de libro y hoja, temporizadores, emails y PDFs automáticos.', 'published' => true, 'sort_order' => 9, 'duration_minutes' => 25],
            ['slug' => 'lenguaje-m', 'title' => 'Power Query avanzado: lenguaje M', 'md_file_path' => 'content/lessons/excel/excel-avanzado-10-lenguaje-m.md', 'excerpt' => 'Sintaxis M, funciones personalizadas, APIs y manejo de errores.', 'published' => true, 'sort_order' => 10, 'duration_minutes' => 25],
            ['slug' => 'vba-clases', 'title' => 'Clases y programación orientada a objetos en VBA', 'md_file_path' => 'content/lessons/excel/excel-avanzado-11-vba-clases.md', 'excerpt' => 'Property Get/Let, métodos, colecciones de objetos y patrón Factory.', 'published' => true, 'sort_order' => 11, 'duration_minutes' => 25],
            ['slug' => 'addins', 'title' => 'Add-ins y complementos personalizados', 'md_file_path' => 'content/lessons/excel/excel-avanzado-12-addins.md', 'excerpt' => 'Crea funciones UDF, personaliza el Ribbon y distribuye complementos.', 'published' => true, 'sort_order' => 12, 'duration_minutes' => 25],
            ['slug' => 'optimizacion', 'title' => 'Optimización y rendimiento de Excel', 'md_file_path' => 'content/lessons/excel/excel-avanzado-13-optimizacion.md', 'excerpt' => 'Fórmulas volátiles, arrays VBA, cálculo selectivo y archivos eficientes.', 'published' => true, 'sort_order' => 13, 'duration_minutes' => 20],
            ['slug' => 'apis-web', 'title' => 'Automatización con APIs y conexiones web', 'md_file_path' => 'content/lessons/excel/excel-avanzado-14-apis.md', 'excerpt' => 'Consumir APIs REST, Outlook y actualizaciones automáticas.', 'published' => true, 'sort_order' => 14, 'duration_minutes' => 25],
            ['slug' => 'debugging-vba', 'title' => 'Manejo de errores y debugging en VBA', 'md_file_path' => 'content/lessons/excel/excel-avanzado-15-debugging.md', 'excerpt' => 'On Error, breakpoints, Debug.Print, logging y patrones robustos.', 'published' => true, 'sort_order' => 15, 'duration_minutes' => 20],
            ['slug' => 'estadistica-excel', 'title' => 'Análisis estadístico y regresión en Excel', 'md_file_path' => 'content/lessons/excel/excel-avanzado-16-estadistica.md', 'excerpt' => 'Analysis ToolPak, correlación, regresión y pruebas de hipótesis.', 'published' => true, 'sort_order' => 16, 'duration_minutes' => 25],
            ['slug' => 'colaboracion-nube', 'title' => 'Colaboración y Excel en la nube', 'md_file_path' => 'content/lessons/excel/excel-avanzado-17-colaboracion.md', 'excerpt' => 'Co-autoría, Office Scripts, Power Automate y versionamiento.', 'published' => true, 'sort_order' => 17, 'duration_minutes' => 20],
            ['slug' => 'python-excel', 'title' => 'Excel y Python: integración moderna', 'md_file_path' => 'content/lessons/excel/excel-avanzado-18-python-excel.md', 'excerpt' => 'Python en Excel con pandas, matplotlib y scikit-learn.', 'published' => true, 'sort_order' => 18, 'duration_minutes' => 25],
            ['slug' => 'proyecto-gestion', 'title' => 'Proyecto final: sistema de gestión empresarial', 'md_file_path' => 'content/lessons/excel/excel-avanzado-19-proyecto-final.md', 'excerpt' => 'Construye un sistema completo con Power Pivot, VBA y dashboards.', 'published' => true, 'sort_order' => 19, 'duration_minutes' => 40],
        ];

        foreach ($lessons as $data) {
            Lesson::firstOrCreate(
                ['course_id' => $course->id, 'slug' => $data['slug']],
                array_merge($data, ['course_id' => $course->id])
            );
        }

        $tagIds = array_filter([$tagAvanzado?->id]);
        if ($tagIds) {
            foreach ($course->lessons as $lesson) {
                $lesson->tags()->syncWithoutDetaching($tagIds);
            }
        }

        $this->command->info('Excel Avanzado lessons seeded: ' . count($lessons));
    }
}
