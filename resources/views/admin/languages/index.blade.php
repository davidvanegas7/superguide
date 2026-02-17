@extends('layouts.admin')
@section('title', 'Lenguajes')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div></div>
    <a href="{{ route('admin.languages.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
        + Nuevo lenguaje
    </a>
</div>

<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left font-medium text-gray-500">Lenguaje</th>
                <th class="px-6 py-3 text-left font-medium text-gray-500">Color</th>
                <th class="px-6 py-3 text-left font-medium text-gray-500">Cursos</th>
                <th class="px-6 py-3 text-left font-medium text-gray-500">Estado</th>
                <th class="px-6 py-3 text-right font-medium text-gray-500">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($languages as $lang)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium text-gray-900 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm"
                         style="background-color: {{ $lang->color }}25;">
                        {{ $lang->icon ?? mb_strtoupper(mb_substr($lang->name, 0, 2)) }}
                    </div>
                    {{ $lang->name }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded" style="background-color: {{ $lang->color }}"></div>
                        <code class="text-xs text-gray-500">{{ $lang->color }}</code>
                    </div>
                </td>
                <td class="px-6 py-4 text-gray-600">{{ $lang->courses_count }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                 {{ $lang->active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $lang->active ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                    <a href="{{ route('admin.languages.edit', $lang) }}"
                       class="text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>
                    <form action="{{ route('admin.languages.destroy', $lang) }}" method="POST" class="inline"
                          onsubmit="return confirm('¿Eliminar este lenguaje?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-10 text-center text-gray-400">No hay lenguajes aún.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
