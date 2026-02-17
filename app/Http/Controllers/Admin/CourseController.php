<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::with('language')->withCount('lessons')->latest()->get();
        return view('admin.courses.index', compact('courses'));
    }

    public function create(): View
    {
        $languages = Language::where('active', true)->orderBy('name')->get();
        return view('admin.courses.create', compact('languages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'language_id' => 'required|exists:languages,id',
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string',
            'level'       => 'required|in:beginner,intermediate,advanced',
            'sort_order'  => 'integer',
        ]);
        $data['slug']      = Str::slug($data['title']);
        $data['published'] = $request->boolean('published', false);
        Course::create($data);
        return redirect()->route('admin.courses.index')->with('success', 'Curso creado.');
    }

    public function edit(Course $course): View
    {
        $languages = Language::where('active', true)->orderBy('name')->get();
        return view('admin.courses.edit', compact('course', 'languages'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'language_id' => 'required|exists:languages,id',
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string',
            'level'       => 'required|in:beginner,intermediate,advanced',
            'sort_order'  => 'integer',
        ]);
        $data['slug']      = Str::slug($data['title']);
        $data['published'] = $request->boolean('published', false);
        $course->update($data);
        return redirect()->route('admin.courses.index')->with('success', 'Curso actualizado.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();
        return redirect()->route('admin.courses.index')->with('success', 'Curso eliminado.');
    }
}
