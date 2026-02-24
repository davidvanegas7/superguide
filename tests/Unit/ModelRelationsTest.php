<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\LessonExercise;
use App\Models\Progress;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\QuizResult;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationsTest extends TestCase
{
    use RefreshDatabase;

    // ─── User ─────────────────────────────────────────────────────────
    public function test_user_is_admin(): void
    {
        $admin   = User::factory()->admin()->create();
        $student = User::factory()->student()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($student->isAdmin());
    }

    public function test_user_has_progress(): void
    {
        $user   = User::factory()->create();
        $lesson = Lesson::factory()->create();

        Progress::create([
            'user_id'    => $user->id,
            'session_id' => 'test-session',
            'lesson_id'  => $lesson->id,
            'completed'  => true,
        ]);

        $this->assertCount(1, $user->progress);
    }

    public function test_user_has_quiz_results(): void
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create();

        QuizResult::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score'   => 15,
            'total'   => 20,
            'percent' => 75,
        ]);

        $this->assertCount(1, $user->quizResults);
    }

    // ─── Language ─────────────────────────────────────────────────────
    public function test_language_has_courses(): void
    {
        $language = Language::factory()->create();
        Course::factory()->count(3)->create(['language_id' => $language->id]);

        $this->assertCount(3, $language->courses);
    }

    public function test_language_published_courses_filters_correctly(): void
    {
        $language = Language::factory()->create();
        Course::factory()->count(2)->create(['language_id' => $language->id, 'published' => true]);
        Course::factory()->create(['language_id' => $language->id, 'published' => false]);

        $this->assertCount(2, $language->publishedCourses);
    }

    public function test_language_uses_slug_for_route_key(): void
    {
        $language = Language::factory()->create(['slug' => 'python']);
        $this->assertEquals('slug', $language->getRouteKeyName());
    }

    // ─── Course ───────────────────────────────────────────────────────
    public function test_course_belongs_to_language(): void
    {
        $course = Course::factory()->create();

        $this->assertInstanceOf(Language::class, $course->language);
    }

    public function test_course_has_lessons_ordered(): void
    {
        $course = Course::factory()->create();
        Lesson::factory()->create(['course_id' => $course->id, 'sort_order' => 2]);
        Lesson::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);

        $lessons = $course->lessons;

        $this->assertCount(2, $lessons);
        $this->assertEquals(1, $lessons->first()->sort_order);
    }

    public function test_course_published_lessons_excludes_unpublished(): void
    {
        $course = Course::factory()->create();
        Lesson::factory()->create(['course_id' => $course->id, 'published' => true]);
        Lesson::factory()->create(['course_id' => $course->id, 'published' => false]);

        $this->assertCount(1, $course->publishedLessons);
    }

    public function test_course_has_quizzes(): void
    {
        $course = Course::factory()->create();
        Quiz::factory()->create(['course_id' => $course->id]);

        $this->assertCount(1, $course->quizzes);
    }

    public function test_course_level_label(): void
    {
        $beginner     = Course::factory()->create(['level' => 'beginner']);
        $intermediate = Course::factory()->create(['level' => 'intermediate']);
        $advanced     = Course::factory()->create(['level' => 'advanced']);

        $this->assertEquals('Principiante', $beginner->level_label);
        $this->assertEquals('Intermedio', $intermediate->level_label);
        $this->assertEquals('Avanzado', $advanced->level_label);
    }

    // ─── Lesson ───────────────────────────────────────────────────────
    public function test_lesson_belongs_to_course(): void
    {
        $lesson = Lesson::factory()->create();

        $this->assertInstanceOf(Course::class, $lesson->course);
    }

    public function test_lesson_has_tags(): void
    {
        $lesson = Lesson::factory()->create();
        $tag    = Tag::factory()->create();
        $lesson->tags()->attach($tag);

        $this->assertCount(1, $lesson->tags);
        $this->assertEquals($tag->id, $lesson->tags->first()->id);
    }

    public function test_lesson_has_exercise(): void
    {
        $lesson   = Lesson::factory()->create();
        $exercise = LessonExercise::factory()->create(['lesson_id' => $lesson->id]);

        $this->assertInstanceOf(LessonExercise::class, $lesson->exercise);
        $this->assertEquals($exercise->id, $lesson->exercise->id);
    }

    public function test_lesson_renders_markdown_from_content_md(): void
    {
        $lesson = Lesson::factory()->create(['content_md' => '# Hello World']);

        $this->assertStringContainsString('<h1>Hello World</h1>', $lesson->html_content);
    }

    // ─── Quiz ─────────────────────────────────────────────────────────
    public function test_quiz_belongs_to_course(): void
    {
        $quiz = Quiz::factory()->create();

        $this->assertInstanceOf(Course::class, $quiz->course);
    }

    public function test_quiz_has_questions_ordered(): void
    {
        $quiz = Quiz::factory()->create();
        QuizQuestion::factory()->create(['quiz_id' => $quiz->id, 'sort_order' => 2]);
        QuizQuestion::factory()->create(['quiz_id' => $quiz->id, 'sort_order' => 1]);

        $questions = $quiz->questions;

        $this->assertCount(2, $questions);
        $this->assertEquals(1, $questions->first()->sort_order);
    }

    // ─── QuizQuestion ─────────────────────────────────────────────────
    public function test_question_has_options_ordered(): void
    {
        $question = QuizQuestion::factory()->create();
        QuizOption::factory()->create(['quiz_question_id' => $question->id, 'sort_order' => 2]);
        QuizOption::factory()->create(['quiz_question_id' => $question->id, 'sort_order' => 1]);

        $options = $question->options;

        $this->assertCount(2, $options);
        $this->assertEquals(1, $options->first()->sort_order);
    }

    // ─── Tag ──────────────────────────────────────────────────────────
    public function test_tag_has_lessons(): void
    {
        $tag    = Tag::factory()->create();
        $lesson = Lesson::factory()->create();
        $tag->lessons()->attach($lesson);

        $this->assertCount(1, $tag->lessons);
    }

    // ─── Progress ─────────────────────────────────────────────────────
    public function test_progress_belongs_to_lesson_and_user(): void
    {
        $user   = User::factory()->create();
        $lesson = Lesson::factory()->create();

        $progress = Progress::create([
            'user_id'    => $user->id,
            'session_id' => 'abc',
            'lesson_id'  => $lesson->id,
            'completed'  => false,
        ]);

        $this->assertInstanceOf(Lesson::class, $progress->lesson);
        $this->assertInstanceOf(User::class, $progress->user);
    }

    // ─── LessonExercise ───────────────────────────────────────────────
    public function test_exercise_belongs_to_lesson(): void
    {
        $exercise = LessonExercise::factory()->create();

        $this->assertInstanceOf(Lesson::class, $exercise->lesson);
    }

    public function test_exercise_renders_description_html(): void
    {
        $exercise = LessonExercise::factory()->create([
            'description' => '# Ejercicio\n\nImplementa la función.',
        ]);

        $this->assertNotEmpty($exercise->description_html);
    }
}
