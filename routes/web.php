<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\QuizController;

// ─── Pública ───────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/buscar', [HomeController::class, 'search'])->name('search');

// ─── Administración (debe estar ANTES de los wildcards) ─────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn() => redirect()->route('admin.languages.index'));

    Route::resource('languages', App\Http\Controllers\Admin\LanguageController::class)
        ->except(['show']);

    Route::resource('courses', App\Http\Controllers\Admin\CourseController::class)
        ->except(['show']);

    Route::resource('lessons', App\Http\Controllers\Admin\LessonController::class)
        ->except(['show']);
});

// ─── Progreso (AJAX) ────────────────────────────────────────────────────────
Route::post('/progress/toggle', [ProgressController::class, 'toggle'])->name('progress.toggle');

// ─── Rutas públicas con wildcards (DEBEN ir al final) ───────────────────────
Route::get('/{language}', [LanguageController::class, 'show'])->name('languages.show');
Route::get('/{language}/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/{language}/{course}/quiz/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
Route::post('/{language}/{course}/quiz/{quiz}/check', [QuizController::class, 'check'])->name('quizzes.check');
Route::get('/{language}/{course}/{lesson}', [LessonController::class, 'show'])->name('lessons.show');

