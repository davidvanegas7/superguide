---
title: "Vistas y Motor de Plantillas Blade"
slug: "laravel-vistas-blade"
description: "Aprende a crear vistas dinámicas con Blade, el motor de plantillas de Laravel: herencia de layouts, directivas, componentes y slots."
---

# Vistas y Motor de Plantillas Blade

Blade es el motor de plantillas incluido en Laravel. A diferencia de otros motores, Blade no te impide usar PHP puro en tus vistas y compila las plantillas a código PHP nativo para un rendimiento óptimo. En esta lección dominarás todas las directivas esenciales de Blade.

## Crear y Devolver Vistas

Las vistas se almacenan en `resources/views/` con la extensión `.blade.php`:

```php
// Desde una ruta o controlador, devolver una vista
Route::get('/inicio', function () {
    return view('inicio'); // => resources/views/inicio.blade.php
});

// Pasar datos a la vista
Route::get('/saludo', function () {
    return view('saludo', ['nombre' => 'María']);
});

// Usando compact() para pasar variables
public function index()
{
    $productos = Producto::all();
    $titulo = 'Nuestros Productos';
    return view('productos.index', compact('productos', 'titulo'));
    // => resources/views/productos/index.blade.php
}
```

## Herencia de Plantillas: Layouts

Blade permite crear un layout maestro que las páginas hijas extienden, evitando duplicar HTML.

### Layout maestro

```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Mi Aplicación')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('estilos')
</head>
<body>
    <nav>
        {{-- Barra de navegación compartida --}}
        <a href="{{ route('inicio') }}">Inicio</a>
        <a href="{{ route('productos.index') }}">Productos</a>
    </nav>

    <main class="container">
        @yield('contenido')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} Mi Aplicación</p>
    </footer>

    @stack('scripts')
</body>
</html>
```

### Vista hija que extiende el layout

```blade
{{-- resources/views/productos/index.blade.php --}}
@extends('layouts.app')

@section('titulo', 'Catálogo de Productos')

@section('contenido')
    <h1>Catálogo de Productos</h1>

    <div class="grid">
        @foreach ($productos as $producto)
            <div class="card">
                <h2>{{ $producto->nombre }}</h2>
                <p>{{ $producto->descripcion }}</p>
                <span class="precio">${{ number_format($producto->precio, 2) }}</span>
            </div>
        @endforeach
    </div>
@endsection
```

## Mostrar Datos: `{{ }}` vs `{!! !!}`

```blade
{{-- Escapa HTML automáticamente (seguro contra XSS) --}}
<p>Hola, {{ $nombre }}</p>
{{-- Si $nombre = "<script>alert('hack')</script>" --}}
{{-- Renderiza: &lt;script&gt;alert('hack')&lt;/script&gt; --}}

{{-- Muestra HTML sin escapar (¡usar con precaución!) --}}
<div>{!! $contenidoHtml !!}</div>
{{-- Úsalo solo cuando confías en el contenido, p.ej. contenido del CMS --}}

{{-- Valor por defecto si la variable no existe --}}
<p>{{ $nombre ?? 'Invitado' }}</p>

{{-- Mostrar llaves literales (para frameworks JS como Vue) --}}
<p>@{{ variableDeVue }}</p>
```

## Estructuras de Control

### Condicionales

```blade
{{-- if / elseif / else --}}
@if ($usuario->esAdmin())
    <span class="badge">Administrador</span>
@elseif ($usuario->esModerador())
    <span class="badge">Moderador</span>
@else
    <span class="badge">Usuario</span>
@endif

{{-- unless: lo contrario de if --}}
@unless ($usuario->estaBaneado())
    <p>Bienvenido al sitio.</p>
@endunless

{{-- isset y empty --}}
@isset($registros)
    <p>Hay registros disponibles.</p>
@endisset

@empty($notificaciones)
    <p>No tienes notificaciones.</p>
@endempty

{{-- Directiva de autenticación --}}
@auth
    <p>Hola, {{ auth()->user()->name }}</p>
@endauth

@guest
    <a href="{{ route('login') }}">Iniciar sesión</a>
@endguest
```

### Bucles

```blade
{{-- foreach --}}
@foreach ($productos as $producto)
    <p>{{ $loop->iteration }}. {{ $producto->nombre }}</p>
@endforeach

{{-- forelse: foreach con caso vacío --}}
@forelse ($pedidos as $pedido)
    <div class="pedido">
        <p>Pedido #{{ $pedido->id }} — {{ $pedido->fecha }}</p>
    </div>
@empty
    <p>No tienes pedidos todavía.</p>
@endforelse

{{-- for clásico --}}
@for ($i = 0; $i < 10; $i++)
    <p>Iteración {{ $i }}</p>
@endfor

{{-- while --}}
@while ($condicion)
    <p>Procesando...</p>
@endwhile
```

### La variable `$loop`

Dentro de cualquier bucle, Blade proporciona la variable `$loop` con información útil:

```blade
@foreach ($elementos as $elemento)
    @if ($loop->first)
        <p><strong>Primer elemento</strong></p>
    @endif

    <p>{{ $elemento->nombre }}</p>
    {{-- $loop->index       => Índice actual (base 0) --}}
    {{-- $loop->iteration   => Iteración actual (base 1) --}}
    {{-- $loop->remaining   => Iteraciones restantes --}}
    {{-- $loop->count       => Total de elementos --}}
    {{-- $loop->first       => ¿Es el primero? --}}
    {{-- $loop->last        => ¿Es el último? --}}
    {{-- $loop->even / odd  => ¿Es par/impar? --}}
    {{-- $loop->depth       => Nivel de anidación --}}
    {{-- $loop->parent      => $loop del bucle padre --}}

    @if ($loop->last)
        <p><em>Último elemento</em></p>
    @endif
@endforeach
```

## Incluir Sub-vistas

```blade
{{-- Incluir una vista parcial --}}
@include('partials.navegacion')

{{-- Incluir pasando datos adicionales --}}
@include('partials.alerta', ['tipo' => 'exito', 'mensaje' => 'Guardado correctamente'])

{{-- Incluir solo si la vista existe --}}
@includeIf('partials.sidebar')

{{-- Incluir condicionalmente --}}
@includeWhen($usuario->esAdmin(), 'partials.panel-admin')

{{-- Incluir para cada elemento de una colección --}}
@each('partials.producto-card', $productos, 'producto', 'partials.sin-productos')
```

## Componentes Blade

Los componentes son piezas reutilizables de interfaz con su propia lógica y plantilla.

### Componentes anónimos (solo vista)

```blade
{{-- resources/views/components/alerta.blade.php --}}
@props(['tipo' => 'info', 'mensaje'])

<div class="alerta alerta-{{ $tipo }}">
    <p>{{ $mensaje }}</p>
    {{ $slot }}
</div>
```

Uso del componente:

```blade
<x-alerta tipo="exito" mensaje="Operación completada">
    <small>Puedes continuar navegando.</small>
</x-alerta>
```

### Componentes con clase

```bash
php artisan make:component Tarjeta
```

```php
<?php
// app/View/Components/Tarjeta.php
namespace App\View\Components;

use Illuminate\View\Component;

class Tarjeta extends Component
{
    public function __construct(
        public string $titulo,
        public string $color = 'azul'
    ) {}

    public function render()
    {
        return view('components.tarjeta');
    }
}
```

```blade
{{-- resources/views/components/tarjeta.blade.php --}}
<div class="tarjeta tarjeta-{{ $color }}">
    <h3>{{ $titulo }}</h3>
    <div class="tarjeta-cuerpo">
        {{ $slot }}
    </div>
</div>
```

### Slots con nombre

```blade
{{-- Definición del componente --}}
<div class="modal">
    <div class="modal-cabecera">{{ $cabecera }}</div>
    <div class="modal-cuerpo">{{ $slot }}</div>
    <div class="modal-pie">{{ $pie }}</div>
</div>

{{-- Uso con slots nombrados --}}
<x-modal>
    <x-slot:cabecera>
        <h2>Confirmar eliminación</h2>
    </x-slot:cabecera>

    <p>¿Estás seguro de que deseas eliminar este registro?</p>

    <x-slot:pie>
        <button type="button">Cancelar</button>
        <button type="submit">Confirmar</button>
    </x-slot:pie>
</x-modal>
```

## Formularios: CSRF y Method Spoofing

```blade
{{-- Formulario con protección CSRF --}}
<form action="{{ route('productos.store') }}" method="POST">
    @csrf

    <label for="nombre">Nombre:</label>
    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}">

    @error('nombre')
        <span class="error">{{ $message }}</span>
    @enderror

    <button type="submit">Guardar</button>
</form>

{{-- Formulario PUT/PATCH/DELETE (method spoofing) --}}
<form action="{{ route('productos.update', $producto) }}" method="POST">
    @csrf
    @method('PUT')

    <input type="text" name="nombre" value="{{ $producto->nombre }}">
    <button type="submit">Actualizar</button>
</form>

{{-- Formulario de eliminación --}}
<form action="{{ route('productos.destroy', $producto) }}" method="POST">
    @csrf
    @method('DELETE')

    <button type="submit">Eliminar</button>
</form>
```

## Directivas Personalizadas

Puedes crear tus propias directivas Blade en un Service Provider:

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Blade;

public function boot(): void
{
    // Directiva para formatear moneda
    Blade::directive('moneda', function (string $expresion) {
        return "<?php echo '$' . number_format($expresion, 2); ?>";
    });

    // Directiva condicional
    Blade::if('entorno', function (string $entorno) {
        return app()->environment($entorno);
    });
}
```

```blade
{{-- Usar la directiva personalizada --}}
<p>Precio: @moneda($producto->precio)</p>

@entorno('local')
    <p>Estás en el entorno de desarrollo.</p>
@endentorno
```

## Ejercicio Práctico

Crea un sistema de vistas para un blog con layout, componentes y datos dinámicos:

1. **Crea un layout** en `resources/views/layouts/blog.blade.php` con `@yield('contenido')` y `@stack('scripts')`.
2. **Crea un componente** anónimo `<x-post-card>` que reciba `titulo`, `autor` y `fecha` como props.
3. **Crea la vista** `resources/views/blog/index.blade.php` que extienda el layout y use `@forelse` para mostrar las tarjetas de posts.
4. **Añade un formulario** de búsqueda con `@csrf` y muestra errores de validación con `@error`.
5. **Crea una directiva** personalizada `@fecha()` que formatee fechas al formato "día de mes de año" en español.

## Resumen

- Blade usa **`{{ }}`** para mostrar datos escapados y **`{!! !!}`** para HTML sin escapar.
- La **herencia de layouts** se logra con `@extends`, `@section` y `@yield`.
- Las directivas **`@if`**, **`@foreach`** y **`@forelse`** controlan el flujo con sintaxis limpia.
- **`@include`** inserta sub-vistas; los **componentes** (`<x-nombre>`) encapsulan UI reutilizable con props y slots.
- **`@csrf`** protege formularios contra ataques CSRF; **`@method`** permite simular PUT, PATCH o DELETE.
- Puedes crear **directivas personalizadas** en un Service Provider con `Blade::directive()`.
