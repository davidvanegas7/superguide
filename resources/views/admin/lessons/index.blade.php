@extends('layouts.admin')
@section('title', 'Lecciones')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div></div>
    <a href="{{ route('admin.lessons.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
        + Nueva lección
    </a>
</div>

<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left font-medium text-gray-500">Lección</th>
                <th class="px-6 py-3 text-left font-medium text-gray-500">Curso</th>
                <th class="px-6 py-3 text-left font-medium text-gray-500">Lenguaje</th>
                <th class="px-6 py-3 text-left font-medium text-gray-500">Fuente</th>
                <th class="px-6 py-3 text-left font-medium text-gray-500">Estado</th>
                <th class="px-6 py-3 text-right font-medium text-gray-500">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($lessons as $lesson)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium text-gray-900">{{ $lesson->title }}</td>
                <td class="px-6 py-4 text-gray-600">{{ $lesson->course->title }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium text-white"
                          style="background-color: {{ $lesson->course->language->color }}">
                        {{ $lesson->course->language->name }}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-500 text-xs">
                    @if($lesson->md_file_path)
                        📁 Archivo .md
                    @else
                        ✏️ Editor
                    @endif
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                 {{ $lesson->published ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $lesson->published ? 'Publicada' : 'Borrador' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                    <a href="{{ route('admin.lessons.edit', $lesson) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>
                    <form action="{{ route('admin.lessons.destroy', $lesson) }}" method="POST" class="inline"
                          onsubmit="return confirm('¿Eliminar esta lección?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-gray-400">No hay lecciones aún.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
