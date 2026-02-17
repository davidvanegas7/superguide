<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — @yield('title', 'Panel') | SuperGuide</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-100 font-sans antialiased">

<div class="flex h-full">
    {{-- Sidebar --}}
    <aside class="w-60 bg-gray-900 text-gray-100 flex flex-col shrink-0">
        <a href="{{ route('home') }}" class="flex items-center gap-2 px-6 py-5 font-bold text-lg text-white border-b border-gray-700">
            <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            SuperGuide
        </a>
        <nav class="flex-1 px-4 py-6 space-y-1 text-sm">
            <p class="px-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Contenido</p>
            <a href="{{ route('admin.languages.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.languages*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                🌐 Lenguajes
            </a>
            <a href="{{ route('admin.courses.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.courses*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                📚 Cursos
            </a>
            <a href="{{ route('admin.lessons.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.lessons*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                📝 Lecciones
            </a>
        </nav>
        <div class="px-4 py-4 border-t border-gray-700">
            <a href="{{ route('home') }}" class="text-xs text-gray-500 hover:text-gray-300">← Ver sitio público</a>
        </div>
    </aside>

    {{-- Content --}}
    <div class="flex-1 overflow-auto">
        <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-800">@yield('title', 'Panel de administración')</h1>
        </header>

        @if(session('success'))
            <div class="mx-8 mt-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <main class="px-8 py-6">
            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
