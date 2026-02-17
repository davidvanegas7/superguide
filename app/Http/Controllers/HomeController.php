<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Course;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $languages = Language::where('active', true)
            ->withCount(['publishedCourses'])
            ->orderBy('sort_order')
            ->get();

        $featuredCourses = Course::where('published', true)
            ->with('language')
            ->latest()
            ->take(6)
            ->get();

        return view('home', compact('languages', 'featuredCourses'));
    }

    public function search(): View
    {
        $query   = request('q', '');
        $lessons = collect();

        if (strlen($query) >= 2) {
            $lessons = \App\Models\Lesson::where('published', true)
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('excerpt', 'like', "%{$query}%")
                      ->orWhere('content_md', 'like', "%{$query}%");
                })
                ->with(['course.language'])
                ->take(20)
                ->get();
        }

        return view('search', compact('lessons', 'query'));
    }
}
