<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\QuizResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizAndProgressTest extends TestCase
{
    use RefreshDatabase;

    // ─── Quiz ─────────────────────────────────────────────────────────

    private function createFullQuiz(): array
    {
        $language = Language::factory()->create();
        $course   = Course::factory()->create(['language_id' => $language->id]);
        $quiz     = Quiz::factory()->create(['course_id' => $course->id]);

        $questions = [];
        for ($i = 1; $i <= 3; $i++) {
            $q = QuizQuestion::factory()->create([
                'quiz_id'    => $quiz->id,
                'sort_order' => $i,
            ]);
            // 1 correcta + 3 incorrectas
            QuizOption::factory()->correct()->create([
                'quiz_question_id' => $q->id,
                'sort_order'       => 1,
            ]);
            QuizOption::factory()->count(3)->create([
                'quiz_question_id' => $q->id,
            ]);
            $questions[] = $q;
        }

        return compact('language', 'course', 'quiz', 'questions');
    }

    public function test_quiz_page_loads(): void
    {
        ['language' => $lang, 'course' => $course, 'quiz' => $quiz] = $this->createFullQuiz();

        $response = $this->get("/{$lang->slug}/{$course->slug}/quiz/{$quiz->id}");
        $response->assertStatus(200);
    }

    public function test_unpublished_quiz_returns_404(): void
    {
        $language = Language::factory()->create();
        $course   = Course::factory()->create(['language_id' => $language->id]);
        $quiz     = Quiz::factory()->unpublished()->create(['course_id' => $course->id]);

        $this->get("/{$language->slug}/{$course->slug}/quiz/{$quiz->id}")->assertStatus(404);
    }

    public function test_quiz_check_returns_results(): void
    {
        ['language' => $lang, 'course' => $course, 'quiz' => $quiz, 'questions' => $questions] = $this->createFullQuiz();

        // Responder todas con la opción correcta
        $answers = [];
        foreach ($questions as $q) {
            $correct = $q->options()->where('is_correct', true)->first();
            $answers[$q->id] = $correct->id;
        }

        $response = $this->postJson(
            "/{$lang->slug}/{$course->slug}/quiz/{$quiz->id}/check",
            ['answers' => $answers]
        );

        $response->assertOk();
        $response->assertJsonStructure(['score', 'total', 'percent', 'results']);
        $response->assertJson([
            'score'   => 3,
            'total'   => 3,
            'percent' => 100,
        ]);
    }

    public function test_quiz_check_handles_wrong_answers(): void
    {
        ['language' => $lang, 'course' => $course, 'quiz' => $quiz, 'questions' => $questions] = $this->createFullQuiz();

        // Responder todas con una opción incorrecta
        $answers = [];
        foreach ($questions as $q) {
            $wrong = $q->options()->where('is_correct', false)->first();
            $answers[$q->id] = $wrong->id;
        }

        $response = $this->postJson(
            "/{$lang->slug}/{$course->slug}/quiz/{$quiz->id}/check",
            ['answers' => $answers]
        );

        $response->assertOk();
        $response->assertJson([
            'score'   => 0,
            'total'   => 3,
            'percent' => 0,
        ]);
    }

    public function test_quiz_check_saves_result_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        ['language' => $lang, 'course' => $course, 'quiz' => $quiz, 'questions' => $questions] = $this->createFullQuiz();

        $answers = [];
        foreach ($questions as $q) {
            $correct = $q->options()->where('is_correct', true)->first();
            $answers[$q->id] = $correct->id;
        }

        $this->actingAs($user)->postJson(
            "/{$lang->slug}/{$course->slug}/quiz/{$quiz->id}/check",
            ['answers' => $answers]
        );

        $this->assertDatabaseHas('quiz_results', [
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score'   => 3,
            'total'   => 3,
        ]);
    }

    public function test_quiz_check_does_not_save_for_guest(): void
    {
        ['language' => $lang, 'course' => $course, 'quiz' => $quiz] = $this->createFullQuiz();

        $this->postJson(
            "/{$lang->slug}/{$course->slug}/quiz/{$quiz->id}/check",
            ['answers' => []]
        );

        $this->assertDatabaseCount('quiz_results', 0);
    }

    // ─── Progress ─────────────────────────────────────────────────────

    public function test_progress_toggle_marks_lesson_completed(): void
    {
        $lesson = Lesson::factory()->create();

        $response = $this->postJson('/progress/toggle', ['lesson_id' => $lesson->id]);

        $response->assertOk();
        $response->assertJson(['completed' => true]);
    }

    public function test_progress_toggle_toggles_back(): void
    {
        $user   = User::factory()->create();
        $lesson = Lesson::factory()->create();

        // Primera vez: completar
        $this->actingAs($user)->postJson('/progress/toggle', ['lesson_id' => $lesson->id]);
        // Segunda vez: descompletar
        $response = $this->actingAs($user)->postJson('/progress/toggle', ['lesson_id' => $lesson->id]);

        $response->assertJson(['completed' => false]);
    }

    public function test_progress_toggle_requires_valid_lesson(): void
    {
        $response = $this->postJson('/progress/toggle', ['lesson_id' => 99999]);
        $response->assertStatus(422);
    }

    public function test_progress_for_authenticated_user_uses_user_id(): void
    {
        $user   = User::factory()->create();
        $lesson = Lesson::factory()->create();

        $this->actingAs($user)->postJson('/progress/toggle', ['lesson_id' => $lesson->id]);

        $this->assertDatabaseHas('progress', [
            'user_id'   => $user->id,
            'lesson_id' => $lesson->id,
            'completed' => true,
        ]);
    }
}
