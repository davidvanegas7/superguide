@extends('layouts.app')

@section('title', 'Inicio')

@section('content')

{{-- Hero --}}
<div class="text-center py-12">
    <h1 class="text-4xl font-bold text-gray-900 mb-4">
        Aprende programación con <span class="text-indigo-600">SuperGuide</span>
    </h1>
    <p class="text-lg text-gray-500 max-w-2xl mx-auto">
        Guías interactivas con ejemplos de código, lecciones basadas en Markdown
        y seguimiento de tu progreso. Elige un lenguaje y empieza hoy.
    </p>
</div>

{{-- Lenguajes disponibles --}}
<section class="mt-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Lenguajes disponibles</h2>

    @if($languages->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/>
            </svg>
            <p>Aún no hay lenguajes. <a href="{{ route('admin.languages.create') }}" class="text-indigo-600 underline">Crea el primero</a>.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($languages as $lang)
                <a href="{{ route('languages.show', $lang) }}"
                   class="group bg-white rounded-xl border border-gray-200 p-5 text-center hover:shadow-md hover:border-indigo-200 transition-all">
                    <div class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center text-2xl"
                         style="background-color: {{ $lang->color }}20;">
                        @if($lang->icon)
                            <span>{{ $lang->icon }}</span>
                        @else
                            <span style="color: {{ $lang->color }}">{{ mb_strtoupper(mb_substr($lang->name, 0, 2)) }}</span>
                        @endif
                    </div>
                    <p class="font-semibold text-gray-800 group-hover:text-indigo-600">{{ $lang->name }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $lang->published_courses_count }} curso(s)</p>
                </a>
            @endforeach
        </div>
    @endif
</section>

{{-- Cursos destacados --}}
@if($featuredCourses->isNotEmpty())
<section class="mt-12">
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Cursos recientes</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($featuredCourses as $course)
            <a href="{{ route('courses.show', [$course->language, $course]) }}"
               class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-indigo-200 transition-all">
                <div class="flex items-center gap-2 mb-3">
                    <span class="badge text-xs px-2 py-0.5 rounded-full font-medium text-white"
                          style="background-color: {{ $course->language->color }}">
                        {{ $course->language->name }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $course->level_label }}</span>
                </div>
                <h3 class="font-semibold text-gray-900">{{ $course->title }}</h3>
                @if($course->description)
                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $course->description }}</p>
                @endif
            </a>
        @endforeach
    </div>
</section>
@endif

@endsection
