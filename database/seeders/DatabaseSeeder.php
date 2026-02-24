<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            TagSeeder::class,
            LanguageSeeder::class,
            CourseSeeder::class,
            LessonSeeder::class,
            NodeJSLessonSeeder::class,
            NodeJSExercisesSeeder::class,
            NodeJSQuizSeeder::class,
            BackendConceptsLessonSeeder::class,
            BackendConceptsExercisesSeeder::class,
            BackendConceptsQuizSeeder::class,
            ReactLessonSeeder::class,
            ReactExercisesSeeder::class,
            ReactQuizSeeder::class,
            RubyLessonSeeder::class,
            RubyExercisesSeeder::class,
            RubyQuizSeeder::class,
            RailsLessonSeeder::class,
            RailsExercisesSeeder::class,
            RailsQuizSeeder::class,
            PhpProfessionalLessonSeeder::class,
            PhpProfessionalExercisesSeeder::class,
            PhpProfessionalQuizSeeder::class,
            LaravelLessonSeeder::class,
            LaravelExercisesSeeder::class,
            LaravelQuizSeeder::class,
            PythonLessonSeeder::class,
            PythonExercisesSeeder::class,
            PythonQuizSeeder::class,
            FlaskLessonSeeder::class,
            FlaskExercisesSeeder::class,
            FlaskQuizSeeder::class,
            DjangoLessonSeeder::class,
            DjangoExercisesSeeder::class,
            DjangoQuizSeeder::class,
            ElixirLessonSeeder::class,
            ElixirExercisesSeeder::class,
            ElixirQuizSeeder::class,
            PhoenixLessonSeeder::class,
            PhoenixExercisesSeeder::class,
            PhoenixQuizSeeder::class,
            AILessonSeeder::class,
            AIQuizSeeder::class,
            ExcelBeginnerLessonSeeder::class,
            ExcelBeginnerExercisesSeeder::class,
            ExcelBeginnerQuizSeeder::class,
            ExcelIntermediateLessonSeeder::class,
            ExcelIntermediateExercisesSeeder::class,
            ExcelIntermediateQuizSeeder::class,
            ExcelAdvancedLessonSeeder::class,
            ExcelAdvancedExercisesSeeder::class,
            ExcelAdvancedQuizSeeder::class,
        ]);
    }
}
