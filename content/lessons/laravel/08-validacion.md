---
title: "Validación de Datos"
slug: "laravel-validacion"
description: "Aprende a validar datos de formularios en Laravel usando reglas built-in, Form Requests, mensajes personalizados y reglas custom."
---

# Validación de Datos

La validación es fundamental para garantizar que los datos que ingresan a tu aplicación sean correctos, seguros y cumplan con las reglas de negocio. Laravel ofrece un sistema de validación potente y flexible que puedes usar de múltiples maneras. En esta lección aprenderás desde la validación básica hasta la creación de reglas personalizadas.

## Validación Básica en el Controlador

La forma más rápida de validar es usar el método `validate()` directamente en el controlador:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function store(Request $request)
    {
        // Validar los datos de la petición
        $datos = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'precio' => 'required|numeric|min:0.01|max:999999.99',
            'stock' => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'imagen' => 'nullable|image|mimes:jpg,png,webp|max:2048',
        ]);

        // Si la validación falla, Laravel redirige automáticamente
        // con los errores. Si pasa, $datos contiene los valores validados.
        $producto = Producto::create($datos);

        return redirect()->route('productos.show', $producto)
            ->with('exito', 'Producto creado correctamente.');
    }
}
```

### Sintaxis con arreglo (alternativa a pipe)

```php
$datos = $request->validate([
    'nombre' => ['required', 'string', 'max:255'],
    'email' => ['required', 'email', 'unique:users,email'],
    'password' => ['required', 'string', 'min:8', 'confirmed'],
]);
```

## Reglas de Validación Built-in

Laravel incluye más de 90 reglas de validación. Estas son las más utilizadas:

### Presencia y tipo

```php
'campo' => 'required',               // Obligatorio
'campo' => 'nullable',               // Permite null
'campo' => 'sometimes',              // Solo valida si el campo está presente
'campo' => 'filled',                 // Si está presente, no puede estar vacío
'campo' => 'present',                // Debe existir (puede estar vacío)
```

### Cadenas de texto

```php
'nombre' => 'string|min:2|max:100',
'slug' => 'alpha_dash',              // Letras, números, guiones y guiones bajos
'codigo' => 'alpha_num',             // Solo letras y números
'url' => 'url',                      // URL válida
'ip' => 'ip',                        // Dirección IP válida
'uuid' => 'uuid',                    // UUID válido
'patron' => 'regex:/^[A-Z]{3}-\d{4}$/',  // Expresión regular
```

### Números

```php
'edad' => 'integer|between:18,120',
'precio' => 'numeric|min:0',
'cantidad' => 'integer|gte:1',       // Mayor o igual a 1
'descuento' => 'decimal:0,2',        // Decimal con 0-2 decimales
```

### Email y unicidad

```php
'email' => 'email:rfc,dns',          // Email válido (RFC + verificar DNS)
'email' => 'unique:users,email',     // Único en la tabla users
'email' => 'unique:users,email,' . $user->id,  // Único excepto este ID (para editar)
'email' => 'exists:users,email',     // Debe existir en la tabla
```

### Fechas

```php
'fecha' => 'date',                   // Fecha válida
'inicio' => 'date|after:today',      // Posterior a hoy
'fin' => 'date|after:inicio',        // Posterior al campo "inicio"
'nacimiento' => 'date|before:-18 years', // Al menos 18 años
'publicacion' => 'date_format:Y-m-d',
```

### Archivos

```php
'foto' => 'image|mimes:jpg,png,webp|max:5120',  // Imagen, máx 5MB
'documento' => 'file|mimetypes:application/pdf|max:10240',
'avatar' => 'dimensions:min_width=100,min_height=100,ratio=1/1',
```

### Arreglos

```php
'etiquetas' => 'array|min:1|max:10',
'etiquetas.*' => 'string|max:50',    // Cada elemento del array
'items' => 'array',
'items.*.nombre' => 'required|string',
'items.*.cantidad' => 'required|integer|min:1',
```

### Confirmación y comparación

```php
'password' => 'confirmed',           // Requiere campo password_confirmation
'password_actual' => 'current_password',
'terminos' => 'accepted',            // Debe ser "yes", "on", 1 o true
```

## Mostrar Errores en Blade

```blade
{{-- Mostrar todos los errores --}}
@if ($errors->any())
    <div class="alerta alerta-error">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Mostrar error de un campo específico --}}
<div class="campo">
    <label for="nombre">Nombre</label>
    <input type="text"
           name="nombre"
           id="nombre"
           value="{{ old('nombre') }}"
           class="@error('nombre') input-error @enderror">

    @error('nombre')
        <span class="texto-error">{{ $message }}</span>
    @enderror
</div>

{{-- old() recupera el valor previo del formulario --}}
<textarea name="descripcion">{{ old('descripcion') }}</textarea>

{{-- Errores de un bag específico --}}
@error('email', 'login')
    <span>{{ $message }}</span>
@enderror
```

## Form Requests: Validación Separada

Para formularios complejos, es mejor separar la lógica de validación en una clase dedicada:

```bash
php artisan make:request StoreProductoRequest
```

```php
<?php
// app/Http/Requests/StoreProductoRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{
    /**
     * ¿El usuario está autorizado para esta petición?
     */
    public function authorize(): bool
    {
        // Verificar permisos (true = cualquiera puede acceder)
        return $this->user()->can('crear-productos');
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'precio' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'stock' => ['required', 'integer', 'min:0'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'etiquetas' => ['array', 'max:5'],
            'etiquetas.*' => ['exists:tags,id'],
            'imagen' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'precio.required' => 'Debes indicar un precio.',
            'precio.min' => 'El precio debe ser al menos $0.01.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'imagen.max' => 'La imagen no puede pesar más de 2MB.',
        ];
    }

    /**
     * Nombres personalizados para los atributos.
     */
    public function attributes(): array
    {
        return [
            'categoria_id' => 'categoría',
            'etiquetas.*' => 'etiqueta',
        ];
    }

    /**
     * Preparar datos antes de la validación.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => \Str::slug($this->nombre),
        ]);
    }
}
```

### Usar el Form Request en el controlador

```php
use App\Http\Requests\StoreProductoRequest;

class ProductoController extends Controller
{
    // Laravel inyecta y ejecuta la validación automáticamente
    public function store(StoreProductoRequest $request)
    {
        // Si llegamos aquí, la validación ya pasó
        $datos = $request->validated(); // Solo campos validados

        $producto = Producto::create($datos);

        return redirect()->route('productos.show', $producto);
    }
}
```

## Validación Condicional

### `Rule::when()`

```php
use Illuminate\Validation\Rule;

$datos = $request->validate([
    'tipo' => 'required|in:personal,empresa',

    // Solo requerido si tipo es "empresa"
    'rfc' => Rule::when(
        $request->tipo === 'empresa',
        ['required', 'string', 'size:13'],
        ['nullable']
    ),

    // Solo requerido si el campo "envio" está presente
    'direccion' => Rule::when(
        $request->has('envio'),
        ['required', 'string', 'max:500']
    ),
]);
```

### `required_if` y `required_with`

```php
$datos = $request->validate([
    'metodo_pago' => 'required|in:tarjeta,transferencia,efectivo',

    // Requerido si metodo_pago es "tarjeta"
    'numero_tarjeta' => 'required_if:metodo_pago,tarjeta|digits:16',

    // Requerido si "direccion" está presente
    'codigo_postal' => 'required_with:direccion',

    // Requerido si "telefono" Y "email" están presentes
    'notificar' => 'required_with_all:telefono,email',

    // Requerido a menos que "tipo" sea "invitado"
    'password' => 'required_unless:tipo,invitado',

    // Prohibido si el campo "gratis" es true
    'precio' => 'prohibited_if:gratis,true',
]);
```

### Validación con closures

```php
$datos = $request->validate([
    'codigo_descuento' => [
        'sometimes',
        'string',
        function (string $attribute, mixed $value, \Closure $fail) {
            if (!CodigoDescuento::where('codigo', $value)->where('activo', true)->exists()) {
                $fail('El código de descuento no es válido o ha expirado.');
            }
        },
    ],
]);
```

## Reglas Personalizadas (Custom Rules)

Para reglas complejas y reutilizables, crea una clase de regla:

```bash
php artisan make:rule Mayuscula
```

```php
<?php
// app/Rules/Mayuscula.php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Mayuscula implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strtoupper($value) !== $value) {
            $fail('El campo :attribute debe estar completamente en mayúsculas.');
        }
    }
}
```

```php
// Uso de la regla personalizada
use App\Rules\Mayuscula;

$request->validate([
    'codigo' => ['required', 'string', new Mayuscula],
]);
```

### Regla con parámetros

```php
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PalabraProhibida implements ValidationRule
{
    public function __construct(
        private array $palabras = []
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($this->palabras as $palabra) {
            if (str_contains(strtolower($value), strtolower($palabra))) {
                $fail("El campo :attribute contiene una palabra prohibida: $palabra.");
                return;
            }
        }
    }
}

// Uso
$request->validate([
    'comentario' => ['required', new PalabraProhibida(['spam', 'phishing', 'hack'])],
]);
```

## Ejercicio Práctico

Crea un sistema de validación completo para el registro de usuarios en una plataforma:

1. **Crea un Form Request** `RegistroUsuarioRequest` con las siguientes reglas:
   - `nombre`: obligatorio, cadena, entre 2 y 100 caracteres.
   - `email`: obligatorio, email válido, único en la tabla users.
   - `password`: obligatorio, mínimo 8 caracteres, confirmado, debe contener al menos una mayúscula y un número.
   - `fecha_nacimiento`: obligatorio, fecha, el usuario debe tener al menos 13 años.
   - `telefono`: opcional, formato de teléfono válido.
   - `terminos`: debe ser aceptado.
2. **Añade mensajes personalizados** en español para cada regla.
3. **Crea una regla personalizada** `ContraseñaSegura` que verifique mayúsculas, números y caracteres especiales.
4. **Implementa la vista** del formulario con `old()` y `@error` para cada campo.
5. **Usa el Form Request** en el controlador `RegistroController@store`.

## Resumen

- `$request->validate()` es la forma más rápida de validar datos en un controlador.
- Laravel incluye más de **90 reglas de validación** built-in: `required`, `email`, `unique`, `exists`, `between`, `regex`, etc.
- **Form Requests** (`php artisan make:request`) separan la lógica de validación del controlador.
- Los **mensajes personalizados** se definen en el método `messages()` del Form Request.
- La validación **condicional** se logra con `Rule::when()`, `required_if`, `required_with` y closures.
- Las **reglas personalizadas** (`php artisan make:rule`) encapsulan lógica de validación reutilizable.
- En Blade, `@error` muestra mensajes de error y `old()` recupera los valores previos del formulario.
