@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full" style="max-width: 400px;">

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            
            {{-- Header --}}
            <div class="px-6 py-8 text-center" style="background: linear-gradient(to right, #4f46e5, #7c3aed);">
                <div class="mx-auto w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background-color: rgba(255,255,255,0.2);">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white">¡Bienvenido!</h1>
                <p class="text-indigo-100 mt-1 text-sm">Inicia sesión en tu cuenta</p>
            </div>

            {{-- Form --}}
            <div class="p-6 sm:p-8">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- Email --}}
                    <div class="mb-5">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Correo electrónico
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            autofocus
                            required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('email') ? 'border-red-500 bg-red-50' : '' }}"
                            placeholder="tu@email.com"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-5">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('password') ? 'border-red-500 bg-red-50' : '' }}"
                            placeholder="••••••••"
                        >
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember --}}
                    <div class="flex items-center mb-6">
                        <input
                            id="remember"
                            type="checkbox"
                            name="remember"
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        >
                        <label for="remember" class="ml-2 text-sm text-gray-600">
                            Recordarme
                        </label>
                    </div>

                    {{-- Submit --}}
                    <button
                        type="submit"
                        class="w-full bg-indigo-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
                    >
                        Iniciar sesión
                    </button>
                </form>

                {{-- Divider --}}
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="px-3 bg-white text-sm text-gray-500">¿No tienes cuenta?</span>
                    </div>
                </div>

                {{-- Register link --}}
                <a href="{{ route('register') }}"
                   class="block w-full text-center bg-white text-indigo-600 font-semibold py-3 px-4 rounded-lg border-2 border-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                    Crear una cuenta
                </a>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            &copy; {{ date('Y') }} SuperGuide
        </p>
    </div>
</div>
@endsection
