@extends('layouts.app')

@section('title', $course->title)

@section('content')

<div class="flex items-center gap-2 text-sm text-gray-400 mb-6">
    <a href="{{ route('home') }}" class="hover:text-indigo-600">Inicio</a>
    <span>/</span>
    <a href="{{ route('languages.show', $language) }}" class="hover:text-indigo-600">{{ $language->name }}</a>
    <span>/</span>
    <span class="text-gray-700 font-medium">{{ $course->title }}</span>
</div>

<div class="lg:grid lg:grid-cols-3 lg:gap-10">

    {{-- Info del curso --}}
    <div class="lg:col-span-2">
        <div class="flex items-center gap-3 mb-2">
            <span class="text-sm font-medium px-3 py-1 rounded-full"
                  style="background-color: {{ $language->color }}15; color: {{ $language->color }}">
                {{ $language->name }}
            </span>
            <span class="text-sm text-gray-400">{{ $course->level_label }}</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ $course->title }}</h1>
        @if($course->description)
            <p class="text-gray-600 mb-6">{{ $course->description }}</p>
        @endif

        {{-- Lista de lecciones --}}
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
            Lecciones ({{ $lessons->count() }})
        </h2>

        @if($lessons->isEmpty())
            <p class="text-gray-400">Este curso aún no tiene lecciones publicadas.</p>
        @else
            <div class="space-y-2">
                @foreach($lessons as $index => $lesson)
                    @php $completed = in_array($lesson->id, $completedIds); @endphp
                    <a href="{{ route('lessons.show', [$language, $course, $lesson]) }}"
                       class="flex items-center gap-4 p-4 bg-white border rounded-xl hover:border-indigo-300 hover:shadow-sm transition-all group">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium shrink-0
                                    {{ $completed ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-500' }}">
                            @if($completed)
                                ✓
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 group-hover:text-indigo-600 truncate">{{ $lesson->title }}</p>
                            @if($lesson->excerpt)
                                <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $lesson->excerpt }}</p>
                            @endif
                        </div>
                        @if($lesson->duration_minutes)
                            <span class="text-xs text-gray-400 shrink-0">{{ $lesson->duration_minutes }} min</span>
                        @endif
                        @if(!empty($lesson->tags) && $lesson->tags->isNotEmpty())
                            <div class="flex gap-1 shrink-0">
                                @foreach($lesson->tags->take(2) as $tag)
                                    <span class="text-xs px-2 py-0.5 rounded-full text-white"
                                          style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Panel lateral --}}
    <aside class="mt-8 lg:mt-0">
        <div class="bg-white border border-gray-200 rounded-xl p-5 sticky top-20">
            <h3 class="font-semibold text-gray-800 mb-4">Tu progreso</h3>
            @php
                $total    = $lessons->count();
                $done     = count($completedIds);
                $percent  = $total > 0 ? round(($done / $total) * 100) : 0;
            @endphp
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="text-gray-500">{{ $done }} / {{ $total }} completadas</span>
                <span class="font-semibold text-indigo-600">{{ $percent }}%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2 mb-5">
                <div class="bg-indigo-500 h-2 rounded-full transition-all" style="width: {{ $percent }}%"></div>
            </div>

            {{-- Quiz de evaluación --}}
            @php
                $quiz = $course->quizzes()->where('published', true)->first();
            @endphp
            @if($quiz)
                <a href="{{ route('quizzes.show', [$language, $course, $quiz]) }}"
                   class="flex items-center gap-3 w-full px-4 py-3 bg-indigo-50 border border-indigo-200 rounded-xl hover:bg-indigo-100 hover:border-indigo-300 transition-all group">
                    <span class="text-2xl">🧠</span>
                    <div class="min-w-0">
                        <p class="font-semibold text-indigo-700 text-sm group-hover:text-indigo-900 leading-tight">Evaluación del curso</p>
                        <p class="text-xs text-indigo-500 mt-0.5 leading-tight">{{ $quiz->questions()->count() }} preguntas · Opción múltiple</p>
                    </div>
                </a>
            @endif
        </div>
    </aside>

</div>

@endsection

