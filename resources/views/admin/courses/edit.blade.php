@extends('layouts.admin')
@section('title', isset($course) ? 'Editar curso' : 'Nuevo curso')

@section('content')

<div class="max-w-xl">
    <form action="{{ isset($course) ? route('admin.courses.update', $course) : route('admin.courses.store') }}"
          method="POST" class="bg-white border border-gray-200 rounded-xl p-6 space-y-5">
        @csrf
        @isset($course) @method('PUT') @endisset

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lenguaje *</label>
            <select name="language_id" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">— Selecciona —</option>
                @foreach($languages as $lang)
                    <option value="{{ $lang->id }}"
                            {{ old('language_id', $course->language_id ?? '') == $lang->id ? 'selected' : '' }}>
                        {{ $lang->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
            <input type="text" name="title" value="{{ old('title', $course->title ?? '') }}"
                   required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <textarea name="description" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('description', $course->description ?? '') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nivel *</label>
            <select name="level" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                @foreach(['beginner' => 'Principiante', 'intermediate' => 'Intermedio', 'advanced' => 'Avanzado'] as $val => $label)
                    <option value="{{ $val }}" {{ old('level', $course->level ?? 'beginner') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $course->sort_order ?? 0) }}"
                   class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="published" id="published" value="1"
                   {{ old('published', ($course->published ?? false) ? '1' : '0') == '1' ? 'checked' : '' }}
                   class="w-4 h-4 text-indigo-600 rounded">
            <label for="published" class="text-sm text-gray-700">Publicado</label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                {{ isset($course) ? 'Actualizar' : 'Crear curso' }}
            </button>
            <a href="{{ route('admin.courses.index') }}" class="px-5 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                Cancelar
            </a>
        </div>
    </form>
</div>

@endsection
