<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Course;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function show(Language $language, Course $course): View
    {
        abort_unless($course->published && $course->language_id === $language->id, 404);

        $lessons = $course->publishedLessons()->with('tags')->get();
        $sessionId = session()->getId();

        $completedIds = \App\Models\Progress::where('session_id', $sessionId)
            ->where('completed', true)
            ->pluck('lesson_id')
            ->toArray();

        return view('courses.show', compact('language', 'course', 'lessons', 'completedIds'));
    }
}
