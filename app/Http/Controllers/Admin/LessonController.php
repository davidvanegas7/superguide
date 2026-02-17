<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(): View
    {
        $lessons = Lesson::with(['course.language', 'tags'])->latest()->get();
        return view('admin.lessons.index', compact('lessons'));
    }

    public function create(): View
    {
        $courses = Course::with('language')->orderBy('title')->get();
        $tags    = Tag::orderBy('name')->get();
        return view('admin.lessons.create', compact('courses', 'tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'course_id'        => 'required|exists:courses,id',
            'title'            => 'required|string|max:200',
            'content_md'       => 'nullable|string',
            'md_file'          => 'nullable|file|mimes:md,txt|max:2048',
            'excerpt'          => 'nullable|string|max:500',
            'duration_minutes' => 'nullable|integer',
            'sort_order'       => 'integer',
            'tags'             => 'nullable|array',
            'tags.*'           => 'exists:tags,id',
        ]);

        $data['slug']      = Str::slug($data['title']);
        $data['published'] = $request->boolean('published', false);

        if ($request->hasFile('md_file')) {
            $path = $request->file('md_file')->store('lessons', 'local');
            $data['md_file_path'] = 'storage/app/' . $path;
        }

        $lesson = Lesson::create($data);

        if (!empty($data['tags'])) {
            $lesson->tags()->sync($data['tags']);
        }

        return redirect()->route('admin.lessons.index')->with('success', 'Lección creada.');
    }

    public function edit(Lesson $lesson): View
    {
        $courses = Course::with('language')->orderBy('title')->get();
        $tags    = Tag::orderBy('name')->get();
        return view('admin.lessons.edit', compact('lesson', 'courses', 'tags'));
    }

    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $data = $request->validate([
            'course_id'        => 'required|exists:courses,id',
            'title'            => 'required|string|max:200',
            'content_md'       => 'nullable|string',
            'md_file'          => 'nullable|file|mimes:md,txt|max:2048',
            'excerpt'          => 'nullable|string|max:500',
            'duration_minutes' => 'nullable|integer',
            'sort_order'       => 'integer',
            'tags'             => 'nullable|array',
            'tags.*'           => 'exists:tags,id',
        ]);

        $data['slug']      = Str::slug($data['title']);
        $data['published'] = $request->boolean('published', false);

        if ($request->hasFile('md_file')) {
            $path = $request->file('md_file')->store('lessons', 'local');
            $data['md_file_path'] = 'storage/app/' . $path;
        }

        $lesson->update($data);
        $lesson->tags()->sync($data['tags'] ?? []);

        return redirect()->route('admin.lessons.index')->with('success', 'Lección actualizada.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $lesson->delete();
        return redirect()->route('admin.lessons.index')->with('success', 'Lección eliminada.');
    }
}
