@extends('layouts.app')

@section('title', $lesson->title)

@section('content')

<div class="flex items-center gap-2 text-sm text-gray-400 mb-6 flex-wrap">
    <a href="{{ route('home') }}" class="hover:text-indigo-600">Inicio</a>
    <span>/</span>
    <a href="{{ route('languages.show', $language) }}" class="hover:text-indigo-600">{{ $language->name }}</a>
    <span>/</span>
    <a href="{{ route('courses.show', [$language, $course]) }}" class="hover:text-indigo-600">{{ $course->title }}</a>
    <span>/</span>
    <span class="text-gray-700 font-medium">{{ $lesson->title }}</span>
</div>

<div class="lg:grid lg:grid-cols-4 lg:gap-10">

    {{-- Sidebar: índice del curso --}}
    <aside class="hidden lg:block">
        <div class="bg-white border border-gray-200 rounded-xl p-4 sticky top-20">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">En este curso</p>
            <nav class="space-y-1">
                @foreach($allLessons as $idx => $l)
                    <a href="{{ route('lessons.show', [$language, $course, $l]) }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors
                              {{ $l->id === $lesson->id ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                        <span class="w-5 h-5 rounded-full text-xs flex items-center justify-center shrink-0
                                     {{ $l->id === $lesson->id ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                            {{ $idx + 1 }}
                        </span>
                        <span class="truncate">{{ $l->title }}</span>
                    </a>
                @endforeach
            </nav>
        </div>
    </aside>

    {{-- Contenido principal --}}
    <div class="lg:col-span-3">
        <div class="bg-white border border-gray-200 rounded-xl p-6 lg:p-10">

            {{-- Header de la lección --}}
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-6">
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $lesson->title }}</h1>
                    @if($lesson->excerpt)
                        <p class="text-gray-500 mt-2 text-sm sm:text-base">{{ $lesson->excerpt }}</p>
                    @endif
                    @if($lesson->tags->isNotEmpty())
                        <div class="flex gap-2 mt-3 flex-wrap">
                            @foreach($lesson->tags as $tag)
                                <span class="text-xs px-2.5 py-1 rounded-full text-white font-medium"
                                      style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
                <button id="markComplete"
                        data-lesson="{{ $lesson->id }}"
                        class="w-full sm:w-auto shrink-0 flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border transition-colors
                               {{ $isCompleted
                                    ? 'bg-green-50 text-green-700 border-green-300'
                                    : 'bg-white text-gray-600 border-gray-300 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-300' }}">
                    <span id="checkIcon">{{ $isCompleted ? '✓' : '○' }}</span>
                    <span id="markLabel">{{ $isCompleted ? 'Completada' : 'Marcar completada' }}</span>
                </button>
            </div>

            <hr class="border-gray-100 mb-8">

            {{-- Contenido Markdown convertido a HTML --}}
            <div class="overflow-x-auto -mx-6 lg:-mx-10">
            <article class="prose prose-gray max-w-none px-6 lg:px-10
                            prose-headings:font-semibold prose-headings:text-gray-900
                            prose-a:text-indigo-600 prose-a:no-underline hover:prose-a:underline
                            prose-code:font-mono prose-code:text-indigo-600 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:font-normal prose-code:before:content-none prose-code:after:content-none
                            prose-pre:bg-gray-900 prose-pre:text-gray-100 prose-pre:rounded-xl prose-pre:shadow-none prose-pre:text-xs sm:prose-pre:text-sm
                            prose-table:border-collapse prose-th:bg-gray-100 prose-th:px-3 prose-th:py-2 prose-th:text-left prose-td:px-3 prose-td:py-2 prose-td:border prose-td:border-gray-200 prose-table:text-sm">
                {!! $lesson->html_content !!}
            </article>
            </div>

            {{-- Ejercicio de práctica --}}
            @if($lesson->exercise)
                @if($lesson->exercise->language === 'excel')
                    @include('lessons._spreadsheet', ['exercise' => $lesson->exercise])
                @else
                    @include('lessons._exercise', ['exercise' => $lesson->exercise])
                @endif
            @endif

        </div>

        {{-- Navegación prev/next --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mt-6">
            @if($prev)
                <a href="{{ route('lessons.show', [$language, $course, $prev]) }}"
                   class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:border-indigo-300 hover:text-indigo-600 transition-colors truncate">
                    <span class="shrink-0">←</span>
                    <span class="truncate">{{ $prev->title }}</span>
                </a>
            @else
                <div class="hidden sm:block"></div>
            @endif

            @if($next)
                <a href="{{ route('lessons.show', [$language, $course, $next]) }}"
                   class="flex items-center justify-end gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors truncate">
                    <span class="truncate">{{ $next->title }}</span>
                    <span class="shrink-0">→</span>
                </a>
            @else
                <a href="{{ route('courses.show', [$language, $course]) }}"
                   class="text-center px-4 py-2.5 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                    🎉 Ver resumen del curso
                </a>
            @endif
        </div>
    </div>

</div>

<script>
document.getElementById('markComplete').addEventListener('click', function () {
    const btn   = this;
    const icon  = document.getElementById('checkIcon');
    const label = document.getElementById('markLabel');

    fetch('/progress/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ lesson_id: btn.dataset.lesson }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.completed) {
            btn.classList.remove('bg-white','text-gray-600','border-gray-300','hover:bg-indigo-50','hover:text-indigo-600','hover:border-indigo-300');
            btn.classList.add('bg-green-50','text-green-700','border-green-300');
            icon.textContent  = '✓';
            label.textContent = 'Completada';
        } else {
            btn.classList.remove('bg-green-50','text-green-700','border-green-300');
            btn.classList.add('bg-white','text-gray-600','border-gray-300','hover:bg-indigo-50','hover:text-indigo-600','hover:border-indigo-300');
            icon.textContent  = '○';
            label.textContent = 'Marcar completada';
        }
    });
});
</script>

@endsection
