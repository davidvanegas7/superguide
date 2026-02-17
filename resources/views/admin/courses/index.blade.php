@extends('layouts.admin')
@section('title', 'Cursos')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div></div>
    <a href="{{ route('admin.courses.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
        + Nuevo curso
    </a>
</div>

<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left font-medium text-gray-500">Título</th>
                <th class="px-6 py-3 text-left font-medium text-gray-500">Lenguaje</th>
                <th class="px-6 py-3 text-left font-medium text-gray-500">Nivel</th>
                <th class="px-6 py-3 text-left font-medium text-gray-500">Lecciones</th>
                <th class="px-6 py-3 text-left font-medium text-gray-500">Estado</th>
                <th class="px-6 py-3 text-right font-medium text-gray-500">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($courses as $course)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium text-gray-900">{{ $course->title }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium text-white"
                          style="background-color: {{ $course->language->color }}">
                        {{ $course->language->name }}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-600">{{ $course->level_label }}</td>
                <td class="px-6 py-4 text-gray-600">{{ $course->lessons_count }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                 {{ $course->published ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $course->published ? 'Publicado' : 'Borrador' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                    <a href="{{ route('admin.courses.edit', $course) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>
                    <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" class="inline"
                          onsubmit="return confirm('¿Eliminar este curso y todas sus lecciones?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-gray-400">No hay cursos aún.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
