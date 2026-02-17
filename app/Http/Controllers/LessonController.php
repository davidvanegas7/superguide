<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function show(Language $language, Course $course, Lesson $lesson): View
    {
        abort_unless(
            $lesson->published
            && $course->published
            && $lesson->course_id === $course->id
            && $course->language_id === $language->id,
            404
        );

        $allLessons = $course->publishedLessons()->get();
        $currentIndex = $allLessons->search(fn($l) => $l->id === $lesson->id);

        $prev = $currentIndex > 0 ? $allLessons->get($currentIndex - 1) : null;
        $next = $currentIndex < $allLessons->count() - 1 ? $allLessons->get($currentIndex + 1) : null;

        $sessionId = session()->getId();
        $isCompleted = \App\Models\Progress::where('session_id', $sessionId)
            ->where('lesson_id', $lesson->id)
            ->where('completed', true)
            ->exists();

        return view('lessons.show', compact(
            'language', 'course', 'lesson', 'allLessons',
            'prev', 'next', 'isCompleted'
        ));
    }
}
