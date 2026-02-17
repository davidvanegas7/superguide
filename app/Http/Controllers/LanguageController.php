<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\View\View;

class LanguageController extends Controller
{
    public function show(Language $language): View
    {
        $courses = $language->publishedCourses()
            ->withCount('publishedLessons')
            ->orderBy('sort_order')
            ->get();

        return view('languages.show', compact('language', 'courses'));
    }
}
