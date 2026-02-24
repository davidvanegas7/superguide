<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\LessonExercise;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicNavigationTest extends TestCase
{
    use RefreshDatabase;

    // ─── Home ─────────────────────────────────────────────────────────

    public function test_home_page_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_home_shows_active_languages(): void
    {
        $active   = Language::factory()->create(['active' => true, 'name' => 'Python']);
        $inactive = Language::factory()->create(['active' => false, 'name' => 'Cobol']);

        $response = $this->get('/');

        $response->assertSee('Python');
        $response->assertDontSee('Cobol');
    }

    public function test_home_shows_featured_courses(): void
    {
        $language = Language::factory()->create();
        $course   = Course::factory()->create([
            'language_id' => $language->id,
            'title'       => 'Laravel Avanzado',
            'published'   => true,
        ]);

        $response = $this->get('/');
        $response->assertSee('Laravel Avanzado');
    }

    // ─── Búsqueda ─────────────────────────────────────────────────────

    public function test_search_page_loads(): void
    {
        $this->get('/buscar')->assertStatus(200);
    }

    public function test_search_finds_lessons_by_title(): void
    {
        $language = Language::factory()->create();
        $course   = Course::factory()->create(['language_id' => $language->id]);
        Lesson::factory()->create([
            'course_id' => $course->id,
            'title'     => 'Pattern Matching en Elixir',
            'published' => true,
        ]);

        $response = $this->get('/buscar?q=Pattern');

        $response->assertStatus(200);
        $response->assertSee('Pattern Matching en Elixir');
    }

    public function test_search_ignores_short_queries(): void
    {
        $response = $this->get('/buscar?q=a');
        $response->assertStatus(200);
    }

    // ─── Language ─────────────────────────────────────────────────────

    public function test_language_page_shows_published_courses(): void
    {
        $language  = Language::factory()->create();
        $published = Course::factory()->create([
            'language_id' => $language->id,
            'title'       => 'Curso Visible',
            'published'   => true,
        ]);
        Course::factory()->create([
            'language_id' => $language->id,
            'title'       => 'Curso Oculto',
            'published'   => false,
        ]);

        $response = $this->get("/{$language->slug}");

        $response->assertStatus(200);
        $response->assertSee('Curso Visible');
        $response->assertDontSee('Curso Oculto');
    }

    // ─── Course ───────────────────────────────────────────────────────

    public function test_course_page_shows_lessons(): void
    {
        $language = Language::factory()->create();
        $course   = Course::factory()->create(['language_id' => $language->id]);
        $lesson   = Lesson::factory()->create([
            'course_id'  => $course->id,
            'title'      => 'Lección de Prueba',
            'published'  => true,
            'sort_order' => 1,
        ]);

        $response = $this->get("/{$language->slug}/{$course->slug}");

        $response->assertStatus(200);
        $response->assertSee('Lección de Prueba');
    }

    public function test_unpublished_course_returns_404(): void
    {
        $language = Language::factory()->create();
        $course   = Course::factory()->unpublished()->create(['language_id' => $language->id]);

        $this->get("/{$language->slug}/{$course->slug}")->assertStatus(404);
    }

    public function test_course_with_wrong_language_returns_404(): void
    {
        $lang1  = Language::factory()->create();
        $lang2  = Language::factory()->create();
        $course = Course::factory()->create(['language_id' => $lang1->id]);

        $this->get("/{$lang2->slug}/{$course->slug}")->assertStatus(404);
    }

    // ─── Lesson ───────────────────────────────────────────────────────

    public function test_lesson_page_loads(): void
    {
        $language = Language::factory()->create();
        $course   = Course::factory()->create(['language_id' => $language->id]);
        $lesson   = Lesson::factory()->create([
            'course_id' => $course->id,
            'published' => true,
        ]);

        $response = $this->get("/{$language->slug}/{$course->slug}/{$lesson->slug}");
        $response->assertStatus(200);
    }

    public function test_unpublished_lesson_returns_404(): void
    {
        $language = Language::factory()->create();
        $course   = Course::factory()->create(['language_id' => $language->id]);
        $lesson   = Lesson::factory()->unpublished()->create(['course_id' => $course->id]);

        $this->get("/{$language->slug}/{$course->slug}/{$lesson->slug}")->assertStatus(404);
    }

    public function test_lesson_with_wrong_course_returns_404(): void
    {
        $language = Language::factory()->create();
        $course1  = Course::factory()->create(['language_id' => $language->id]);
        $course2  = Course::factory()->create(['language_id' => $language->id]);
        $lesson   = Lesson::factory()->create(['course_id' => $course1->id]);

        $this->get("/{$language->slug}/{$course2->slug}/{$lesson->slug}")->assertStatus(404);
    }

    public function test_lesson_shows_navigation_links(): void
    {
        $language = Language::factory()->create();
        $course   = Course::factory()->create(['language_id' => $language->id]);
        $lesson1  = Lesson::factory()->create(['course_id' => $course->id, 'sort_order' => 1, 'title' => 'Primera']);
        $lesson2  = Lesson::factory()->create(['course_id' => $course->id, 'sort_order' => 2, 'title' => 'Segunda']);

        $response = $this->get("/{$language->slug}/{$course->slug}/{$lesson1->slug}");
        $response->assertStatus(200);
    }

    public function test_lesson_page_includes_exercise_if_exists(): void
    {
        $language = Language::factory()->create();
        $course   = Course::factory()->create(['language_id' => $language->id]);
        $lesson   = Lesson::factory()->create(['course_id' => $course->id]);
        LessonExercise::factory()->create(['lesson_id' => $lesson->id, 'title' => 'Ejercicio Práctico']);

        $response = $this->get("/{$language->slug}/{$course->slug}/{$lesson->slug}");
        $response->assertStatus(200);
        $response->assertSee('Ejercicio Práctico');
    }
}
