@extends('layouts.app')

@section('title', 'Crear cuenta')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full" style="max-width: 400px;">

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            
            {{-- Header --}}
            <div class="px-6 py-8 text-center" style="background: linear-gradient(to right, #4f46e5, #7c3aed);">
                <div class="mx-auto w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background-color: rgba(255,255,255,0.2);">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white">Crear cuenta</h1>
                <p class="text-indigo-100 mt-1 text-sm">Únete a SuperGuide gratis</p>
            </div>

            {{-- Form --}}
            <div class="p-6 sm:p-8">
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    {{-- Name --}}
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre completo
                        </label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            autocomplete="name"
                            autofocus
                            required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('name') ? 'border-red-500 bg-red-50' : '' }}"
                            placeholder="Tu nombre"
                        >
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Correo electrónico
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('email') ? 'border-red-500 bg-red-50' : '' }}"
                            placeholder="tu@email.com"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('password') ? 'border-red-500 bg-red-50' : '' }}"
                            placeholder="Mínimo 8 caracteres"
                        >
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirmar contraseña
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            autocomplete="new-password"
                            required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Repite tu contraseña"
                        >
                    </div>

                    {{-- Submit --}}
                    <button
                        type="submit"
                        class="w-full bg-indigo-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
                    >
                        Crear mi cuenta
                    </button>
                </form>

                {{-- Divider --}}
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="px-3 bg-white text-sm text-gray-500">¿Ya tienes cuenta?</span>
                    </div>
                </div>

                {{-- Login link --}}
                <a href="{{ route('login') }}"
                   class="block w-full text-center bg-white text-indigo-600 font-semibold py-3 px-4 rounded-lg border-2 border-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                    Iniciar sesión
                </a>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            &copy; {{ date('Y') }} SuperGuide
        </p>
    </div>
</div>
@endsection
