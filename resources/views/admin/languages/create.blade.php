@extends('layouts.admin')
@section('title', isset($language) ? 'Editar lenguaje' : 'Nuevo lenguaje')

@section('content')

<div class="max-w-lg">
    <form action="{{ isset($language) ? route('admin.languages.update', $language) : route('admin.languages.store') }}"
          method="POST" class="bg-white border border-gray-200 rounded-xl p-6 space-y-5">
        @csrf
        @isset($language) @method('PUT') @endisset

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
            <input type="text" name="name" value="{{ old('name', $language->name ?? '') }}"
                   required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Color (hex) *</label>
            <div class="flex items-center gap-3">
                <input type="color" name="color" value="{{ old('color', $language->color ?? '#6366f1') }}"
                       class="w-10 h-10 rounded cursor-pointer border border-gray-300">
                <input type="text" id="colorText" value="{{ old('color', $language->color ?? '#6366f1') }}"
                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                       onchange="document.querySelector('input[type=color]').value = this.value">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Icono (emoji o texto)</label>
            <input type="text" name="icon" value="{{ old('icon', $language->icon ?? '') }}"
                   placeholder="🐍 / JS / TS"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <textarea name="description" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('description', $language->description ?? '') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $language->sort_order ?? 0) }}"
                   class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="active" id="active" value="1"
                   {{ old('active', ($language->active ?? true) ? '1' : '0') == '1' ? 'checked' : '' }}
                   class="w-4 h-4 text-indigo-600 rounded">
            <label for="active" class="text-sm text-gray-700">Activo</label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                {{ isset($language) ? 'Actualizar' : 'Crear lenguaje' }}
            </button>
            <a href="{{ route('admin.languages.index') }}" class="px-5 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.querySelector('input[type=color]').addEventListener('input', function() {
    document.getElementById('colorText').value = this.value;
});
</script>

@endsection
