@extends('layouts.app')

@section('title', $language->name)

@section('content')

<div class="flex items-center gap-2 text-sm text-gray-400 mb-6">
    <a href="{{ route('home') }}" class="hover:text-indigo-600">Inicio</a>
    <span>/</span>
    <span class="text-gray-700 font-medium">{{ $language->name }}</span>
</div>

<div class="flex items-center gap-4 mb-8">
    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl"
         style="background-color: {{ $language->color }}20;">
        {{ $language->icon ?? mb_strtoupper(mb_substr($language->name, 0, 2)) }}
    </div>
    <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ $language->name }}</h1>
        @if($language->description)
            <p class="text-gray-500 mt-1">{{ $language->description }}</p>
        @endif
    </div>
</div>

@if($courses->isEmpty())
    <div class="text-center py-16 text-gray-400">
        <p>No hay cursos disponibles en este lenguaje todavía.</p>
    </div>
@else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($courses as $course)
            <a href="{{ route('courses.show', [$language, $course]) }}"
               class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md hover:border-indigo-200 transition-all group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full"
                          style="background-color: {{ $language->color }}15; color: {{ $language->color }}">
                        {{ $course->level_label }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $course->published_lessons_count }} lección(es)</span>
                </div>
                <h2 class="font-semibold text-gray-900 text-lg group-hover:text-indigo-600">{{ $course->title }}</h2>
                @if($course->description)
                    <p class="text-sm text-gray-500 mt-2 line-clamp-3">{{ $course->description }}</p>
                @endif
            </a>
        @endforeach
    </div>
@endif

@endsection
