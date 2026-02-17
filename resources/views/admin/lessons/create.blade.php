@extends('layouts.admin')
@section('title', isset($lesson) ? 'Editar lección' : 'Nueva lección')

@section('content')

<div class="max-w-3xl">
    <form action="{{ isset($lesson) ? route('admin.lessons.update', $lesson) : route('admin.lessons.store') }}"
          method="POST" enctype="multipart/form-data"
          class="bg-white border border-gray-200 rounded-xl p-6 space-y-5">
        @csrf
        @isset($lesson) @method('PUT') @endisset

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Curso *</label>
                <select name="course_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">— Selecciona un curso —</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}"
                                {{ old('course_id', $lesson->course_id ?? '') == $course->id ? 'selected' : '' }}>
                            {{ $course->language->name }} — {{ $course->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
                <input type="text" name="title" value="{{ old('title', $lesson->title ?? '') }}"
                       required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Extracto / descripción corta</label>
            <input type="text" name="excerpt" value="{{ old('excerpt', $lesson->excerpt ?? '') }}"
                   placeholder="Descripción breve que se muestra en listados"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>

        {{-- Tabs: Editor / Archivo .md --}}
        <div x-data="{ tab: '{{ (isset($lesson) && $lesson->md_file_path) ? 'file' : 'editor' }}' }">
            <div class="flex border-b border-gray-200 mb-4">
                <button type="button" @click="tab='editor'"
                        :class="tab==='editor' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2 text-sm font-medium -mb-px">
                    ✏️ Editor de Markdown
                </button>
                <button type="button" @click="tab='file'"
                        :class="tab==='file' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2 text-sm font-medium -mb-px">
                    📁 Cargar archivo .md
                </button>
            </div>

            <div x-show="tab==='editor'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Contenido Markdown</label>
                <textarea name="content_md" rows="18" placeholder="# Mi Lección&#10;&#10;Escribe aquí el contenido en **Markdown**..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('content_md', $lesson->content_md ?? '') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Soporta Markdown estándar: encabezados, código, listas, tablas, etc.</p>
            </div>

            <div x-show="tab==='file'">
                @if(isset($lesson) && $lesson->md_file_path)
                    <div class="mb-3 text-sm text-gray-600 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3">
                        📄 Archivo actual: <code class="text-xs">{{ $lesson->md_file_path }}</code>
                    </div>
                @endif
                <label class="block text-sm font-medium text-gray-700 mb-1">Archivo Markdown (.md)</label>
                <input type="file" name="md_file" accept=".md,.txt"
                       class="block w-full text-sm text-gray-600 border border-gray-300 rounded-lg px-3 py-2 file:mr-4 file:py-1 file:px-4 file:rounded file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-gray-400 mt-1">Sube un archivo .md para que el contenido sea leído desde él.</p>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Etiquetas</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($tags as $tag)
                        <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                   {{ (isset($lesson) && $lesson->tags->contains($tag->id)) || in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
                                   class="w-4 h-4 text-indigo-600 rounded">
                            <span class="px-2 py-0.5 rounded-full text-xs text-white"
                                  style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duración (min)</label>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $lesson->duration_minutes ?? '') }}"
                           class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $lesson->sort_order ?? 0) }}"
                           class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="published" id="published" value="1"
                   {{ old('published', ($lesson->published ?? false) ? '1' : '0') == '1' ? 'checked' : '' }}
                   class="w-4 h-4 text-indigo-600 rounded">
            <label for="published" class="text-sm text-gray-700">Publicada</label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                {{ isset($lesson) ? 'Actualizar lección' : 'Crear lección' }}
            </button>
            <a href="{{ route('admin.lessons.index') }}" class="px-5 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script src="//cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

@endsection
