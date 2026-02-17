@extends('layouts.app')

@section('title', 'Búsqueda')

@section('content')

<h1 class="text-2xl font-bold text-gray-900 mb-2">Resultados de búsqueda</h1>
<p class="text-gray-500 mb-6">
    @if($query)
        {{ $lessons->count() }} resultado(s) para <strong>"{{ $query }}"</strong>
    @else
        Ingresa un término para buscar.
    @endif
</p>

<form action="{{ route('search') }}" method="GET" class="flex gap-3 mb-8">
    <input type="search" name="q" value="{{ $query }}"
           placeholder="Buscar lecciones…"
           class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">
        Buscar
    </button>
</form>

@if($lessons->isNotEmpty())
    <div class="space-y-3">
        @foreach($lessons as $lesson)
            <a href="{{ route('lessons.show', [$lesson->course->language, $lesson->course, $lesson]) }}"
               class="block bg-white border border-gray-200 rounded-xl p-5 hover:border-indigo-300 hover:shadow-sm transition-all">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full text-white"
                          style="background-color: {{ $lesson->course->language->color }}">
                        {{ $lesson->course->language->name }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $lesson->course->title }}</span>
                </div>
                <h2 class="font-semibold text-gray-900">{{ $lesson->title }}</h2>
                @if($lesson->excerpt)
                    <p class="text-sm text-gray-500 mt-1">{{ $lesson->excerpt }}</p>
                @endif
            </a>
        @endforeach
    </div>
@elseif($query)
    <div class="text-center py-12 text-gray-400">
        <p class="text-lg">No se encontraron resultados.</p>
        <p class="text-sm mt-1">Intenta con otros términos.</p>
    </div>
@endif

@endsection
