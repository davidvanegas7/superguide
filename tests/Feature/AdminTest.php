<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    // ─── Acceso ───────────────────────────────────────────────────────

    public function test_guest_cannot_access_admin(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_admin(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)->get('/admin')->assertStatus(403);
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin');

        // Admin root redirects to languages.index
        $response->assertRedirect(route('admin.languages.index'));
    }

    // ─── Languages CRUD ───────────────────────────────────────────────

    public function test_admin_can_list_languages(): void
    {
        $admin = User::factory()->admin()->create();
        Language::factory()->create(['name' => 'Rust']);

        $response = $this->actingAs($admin)->get(route('admin.languages.index'));

        $response->assertStatus(200);
        $response->assertSee('Rust');
    }

    public function test_admin_can_create_language(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.languages.create'))->assertStatus(200);

        $response = $this->actingAs($admin)->post(route('admin.languages.store'), [
            'name'        => 'Go',
            'slug'        => 'go',
            'color'       => '#00ADD8',
            'icon'        => '🐹',
            'description' => 'Fast and concurrent.',
            'active'      => true,
            'sort_order'  => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('languages', ['slug' => 'go']);
    }

    public function test_admin_can_update_language(): void
    {
        $admin    = User::factory()->admin()->create();
        $language = Language::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($admin)->put(
            route('admin.languages.update', $language),
            [
                'name'        => 'New Name',
                'slug'        => $language->slug,
                'color'       => $language->color,
                'icon'        => $language->icon,
                'description' => $language->description,
                'active'      => true,
                'sort_order'  => 0,
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('languages', ['id' => $language->id, 'name' => 'New Name']);
    }

    public function test_admin_can_delete_language(): void
    {
        $admin    = User::factory()->admin()->create();
        $language = Language::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.languages.destroy', $language));

        $response->assertRedirect();
        $this->assertDatabaseMissing('languages', ['id' => $language->id]);
    }

    // ─── Courses CRUD ─────────────────────────────────────────────────

    public function test_admin_can_list_courses(): void
    {
        $admin = User::factory()->admin()->create();
        $lang  = Language::factory()->create();
        Course::factory()->create(['language_id' => $lang->id, 'title' => 'Mi Curso']);

        $response = $this->actingAs($admin)->get(route('admin.courses.index'));

        $response->assertStatus(200);
        $response->assertSee('Mi Curso');
    }

    // ─── Lessons CRUD ─────────────────────────────────────────────────

    public function test_admin_can_list_lessons(): void
    {
        $admin  = User::factory()->admin()->create();
        $course = Course::factory()->create();
        Lesson::factory()->create(['course_id' => $course->id, 'title' => 'Mi Lección']);

        $response = $this->actingAs($admin)->get(route('admin.lessons.index'));

        $response->assertStatus(200);
        $response->assertSee('Mi Lección');
    }
}
