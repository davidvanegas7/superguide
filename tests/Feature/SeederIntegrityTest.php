<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederIntegrityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ejecuta los seeders y verifica la integridad de los datos.
     * Este test usa la BD real seeded para validar consistency.
     */
    public function test_all_seeders_run_without_errors(): void
    {
        $this->seed();

        // Verifica que se crearon lenguajes
        $languages = Language::all();
        $this->assertGreaterThanOrEqual(7, $languages->count(), 'Should have at least 7 languages');

        // Verifica que se crearon cursos
        $courses = Course::all();
        $this->assertGreaterThanOrEqual(14, $courses->count(), 'Should have at least 14 courses');

        // Verifica que cada curso tiene lecciones
        foreach ($courses as $course) {
            $this->assertGreaterThan(
                0,
                $course->lessons()->count(),
                "Course '{$course->title}' should have lessons"
            );
        }

        // Verifica que al menos el 80% de los cursos tienen quiz
        $coursesWithQuiz = $courses->filter(fn($c) => $c->quizzes()->count() > 0)->count();
        $this->assertGreaterThanOrEqual(
            (int) ($courses->count() * 0.8),
            $coursesWithQuiz,
            'At least 80% of courses should have a quiz'
        );
    }

    public function test_all_quizzes_have_questions_with_one_correct_option(): void
    {
        $this->seed();

        $quizzes = Quiz::with('questions.options')->get();

        foreach ($quizzes as $quiz) {
            $this->assertGreaterThan(
                0,
                $quiz->questions->count(),
                "Quiz '{$quiz->title}' should have questions"
            );

            foreach ($quiz->questions as $question) {
                $correctCount = $question->options->where('is_correct', true)->count();
                $this->assertEquals(
                    1,
                    $correctCount,
                    "Question '{$question->question}' in quiz '{$quiz->title}' should have exactly 1 correct option, got {$correctCount}"
                );

                $this->assertGreaterThanOrEqual(
                    2,
                    $question->options->count(),
                    "Question '{$question->question}' should have at least 2 options"
                );
            }
        }
    }

    public function test_all_courses_belong_to_existing_languages(): void
    {
        $this->seed();

        $courses = Course::all();

        foreach ($courses as $course) {
            $this->assertNotNull(
                $course->language,
                "Course '{$course->title}' should belong to a language"
            );
        }
    }

    public function test_all_lessons_belong_to_existing_courses(): void
    {
        $this->seed();

        $lessons = Lesson::all();

        foreach ($lessons as $lesson) {
            $this->assertNotNull(
                $lesson->course,
                "Lesson '{$lesson->title}' should belong to a course"
            );
        }
    }

    public function test_course_slugs_are_unique(): void
    {
        $this->seed();

        $slugs      = Course::pluck('slug');
        $uniqueSlugs = $slugs->unique();

        $this->assertEquals(
            $slugs->count(),
            $uniqueSlugs->count(),
            'All course slugs should be unique'
        );
    }
}
