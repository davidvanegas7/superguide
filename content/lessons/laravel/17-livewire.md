---
title: "Laravel Livewire"
slug: "laravel-livewire"
description: "Construye interfaces dinámicas y reactivas sin JavaScript usando Laravel Livewire y su integración con Alpine.js."
---

# Laravel Livewire

**Livewire** es un framework full-stack para Laravel que permite construir interfaces dinámicas y reactivas directamente desde PHP, sin escribir JavaScript manualmente. Con Livewire, cada interacción del usuario (click, input, submit) envía una petición AJAX al servidor, donde PHP procesa la lógica y actualiza solo las partes del DOM que cambiaron. Es la herramienta ideal para desarrolladores Laravel que quieren interactividad sin aprender un framework JavaScript completo.

## Instalación

```bash
# Instalar Livewire
composer require livewire/livewire

# Publicar assets (opcional)
php artisan livewire:publish --assets
```

Incluye los scripts de Livewire en tu layout principal:

```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Mi App</title>
    @livewireStyles
</head>
<body>
    {{ $slot }}

    @livewireScripts
</body>
</html>
```

## Crear Componentes

```bash
# Crear un componente
php artisan make:livewire Counter

# Crea dos archivos:
# app/Livewire/Counter.php (clase)
# resources/views/livewire/counter.blade.php (vista)

# Componente con subdirectorio
php artisan make:livewire Admin/UserTable
```

### Componente Básico: Contador

```php
// app/Livewire/Counter.php
namespace App\Livewire;

use Livewire\Component;

class Counter extends Component
{
    // Propiedades públicas son accesibles desde la vista
    public int $count = 0;

    // Métodos públicos son acciones invocables desde la vista
    public function increment(): void
    {
        $this->count++;
    }

    public function decrement(): void
    {
        if ($this->count > 0) {
            $this->count--;
        }
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

```blade
{{-- resources/views/livewire/counter.blade.php --}}
<div>
    <h2>Contador: {{ $count }}</h2>

    <button wire:click="increment">+</button>
    <button wire:click="decrement">-</button>

    {{-- Pasar parámetros a la acción --}}
    <button wire:click="increment">Sumar</button>
</div>
```

### Usar el Componente

```blade
{{-- En cualquier vista Blade --}}
<livewire:counter />

{{-- Con parámetros iniciales --}}
<livewire:counter :count="5" />

{{-- Sintaxis alternativa --}}
@livewire('counter', ['count' => 5])
```

## Data Binding con `wire:model`

`wire:model` sincroniza automáticamente un input HTML con una propiedad del componente:

```php
// app/Livewire/SearchUsers.php
namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class SearchUsers extends Component
{
    use WithPagination;

    public string $search = '';
    public string $role = '';

    // Se ejecuta cuando cambia $search o $role
    public function updatedSearch(): void
    {
        $this->resetPage(); // Volver a la página 1 al buscar
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->when($this->role, fn ($q) =>
                $q->where('role', $this->role)
            )
            ->paginate(10);

        return view('livewire.search-users', compact('users'));
    }
}
```

```blade
{{-- resources/views/livewire/search-users.blade.php --}}
<div>
    {{-- Sincronización en tiempo real (cada tecla) --}}
    <input type="text" wire:model.live="search" placeholder="Buscar usuarios...">

    {{-- Sincronización al perder foco --}}
    <select wire:model.live="role">
        <option value="">Todos los roles</option>
        <option value="admin">Admin</option>
        <option value="editor">Editor</option>
        <option value="user">Usuario</option>
    </select>

    {{-- Tabla de resultados --}}
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No se encontraron usuarios</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $users->links() }}
</div>
```

## Acciones y Eventos

### wire:click y Acciones

```blade
{{-- Llamar métodos con parámetros --}}
<button wire:click="delete({{ $user->id }})">Eliminar</button>

{{-- Confirmación antes de ejecutar --}}
<button wire:click="delete({{ $user->id }})"
        wire:confirm="¿Estás seguro de eliminar este usuario?">
    Eliminar
</button>

{{-- Mostrar indicador de carga --}}
<button wire:click="save">
    <span wire:loading.remove wire:target="save">Guardar</span>
    <span wire:loading wire:target="save">Guardando...</span>
</button>

{{-- Deshabilitar botón durante carga --}}
<button wire:click="save" wire:loading.attr="disabled">
    Guardar
</button>
```

### Eventos entre Componentes

```php
// Componente hijo: emitir evento
class CreateComment extends Component
{
    public string $body = '';

    public function save(): void
    {
        Comment::create(['body' => $this->body, 'post_id' => $this->postId]);

        $this->body = '';

        // Emitir evento al padre
        $this->dispatch('comment-created');
    }
}

// Componente padre: escuchar evento
class CommentList extends Component
{
    // Escuchar el evento para refrescar
    #[\Livewire\Attributes\On('comment-created')]
    public function refreshComments(): void
    {
        // El componente se re-renderiza automáticamente
    }
}
```

## Lifecycle Hooks

Livewire ofrece hooks para interceptar diferentes momentos del ciclo de vida:

```php
class UserForm extends Component
{
    public string $name = '';
    public string $email = '';

    // Se ejecuta al montar el componente (equivalente a constructor)
    public function mount(User $user): void
    {
        $this->name = $user->name;
        $this->email = $user->email;
    }

    // Se ejecuta antes de actualizar una propiedad
    public function updatingName(string $value): void
    {
        // $value es el nuevo valor antes de asignarse
    }

    // Se ejecuta después de actualizar una propiedad
    public function updatedName(string $value): void
    {
        // Validación en tiempo real
        $this->validateOnly('name');
    }

    // Se ejecuta antes de cada render
    public function rendering(): void
    {
        // ...
    }

    // Se ejecuta cuando el componente se deshidrata (envía al navegador)
    public function dehydrate(): void
    {
        // ...
    }
}
```

## Validación en Tiempo Real

```php
class ContactForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $message = '';

    // Reglas de validación
    protected $rules = [
        'name'    => 'required|min:3|max:100',
        'email'   => 'required|email',
        'message' => 'required|min:10|max:1000',
    ];

    // Mensajes personalizados
    protected $messages = [
        'name.required'    => 'El nombre es obligatorio.',
        'email.required'   => 'El email es obligatorio.',
        'message.min'      => 'El mensaje debe tener al menos 10 caracteres.',
    ];

    // Validar al actualizar cada campo
    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function submit(): void
    {
        $validated = $this->validate();

        Contact::create($validated);

        // Resetear formulario
        $this->reset(['name', 'email', 'message']);

        // Mostrar mensaje flash
        session()->flash('success', '¡Mensaje enviado correctamente!');
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
```

```blade
<div>
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <form wire:submit="submit">
        <div>
            <label>Nombre</label>
            <input type="text" wire:model.blur="name">
            @error('name') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div>
            <label>Email</label>
            <input type="email" wire:model.blur="email">
            @error('email') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div>
            <label>Mensaje</label>
            <textarea wire:model.blur="message"></textarea>
            @error('message') <span class="error">{{ $message }}</span> @enderror
        </div>

        <button type="submit">
            <span wire:loading.remove>Enviar</span>
            <span wire:loading>Enviando...</span>
        </button>
    </form>
</div>
```

## Integración con Alpine.js

Livewire incluye Alpine.js automáticamente. Puedes combinarlo para interactividad del lado del cliente:

```blade
<div>
    {{-- Alpine.js para interactividad local (sin servidor) --}}
    <div x-data="{ open: false }">
        <button @click="open = !open">
            Toggle Menú
        </button>

        <ul x-show="open" x-transition>
            <li>Opción 1</li>
            <li>Opción 2</li>
        </ul>
    </div>

    {{-- Combinar Alpine con Livewire --}}
    <div x-data="{ confirming: false }">
        <button @click="confirming = true">Eliminar</button>

        <div x-show="confirming">
            <p>¿Estás seguro?</p>
            <button wire:click="delete" @click="confirming = false">Sí</button>
            <button @click="confirming = false">No</button>
        </div>
    </div>

    {{-- Sincronizar datos entre Alpine y Livewire --}}
    <div x-data="{ tab: @entangle('activeTab') }">
        <button @click="tab = 'general'"
                :class="{ 'active': tab === 'general' }">
            General
        </button>
        <button @click="tab = 'settings'"
                :class="{ 'active': tab === 'settings' }">
            Configuración
        </button>
    </div>
</div>
```

## File Uploads

```php
use Livewire\WithFileUploads;

class ProfilePhoto extends Component
{
    use WithFileUploads;

    public $photo;

    protected $rules = [
        'photo' => 'image|max:2048', // 2MB máximo
    ];

    public function save(): void
    {
        $this->validate();

        $path = $this->photo->store('avatars', 'public');

        auth()->user()->update(['avatar' => $path]);

        session()->flash('success', 'Foto actualizada');
    }

    public function render()
    {
        return view('livewire.profile-photo');
    }
}
```

```blade
<div>
    <form wire:submit="save">
        <input type="file" wire:model="photo">

        @error('photo') <span class="error">{{ $message }}</span> @enderror

        {{-- Preview antes de subir --}}
        @if($photo)
            <img src="{{ $photo->temporaryUrl() }}" width="100">
        @endif

        {{-- Indicador de progreso --}}
        <div wire:loading wire:target="photo">Subiendo...</div>

        <button type="submit">Guardar</button>
    </form>
</div>
```

## Polling y Lazy Loading

```blade
{{-- Actualizar cada 5 segundos --}}
<div wire:poll.5s>
    Usuarios en línea: {{ $onlineUsers }}
</div>

{{-- Polling solo cuando la pestaña está visible --}}
<div wire:poll.visible.10s>
    Notificaciones: {{ $notificationCount }}
</div>

{{-- Lazy loading: cargar el componente después de la página --}}
<livewire:heavy-report lazy />

{{-- En el componente, definir el placeholder --}}
{{-- app/Livewire/HeavyReport.php --}}
public function placeholder()
{
    return view('livewire.placeholders.loading-spinner');
}
```

## Ejercicio Práctico

Construye un componente Livewire de gestión de tareas (Todo List):

1. Crea el componente `TodoList` con las propiedades: `$tasks` (colección), `$newTask` (string).
2. Implementa las acciones: `addTask()`, `toggleComplete($id)`, `deleteTask($id)`.
3. Añade validación en tiempo real para `newTask` (mínimo 3 caracteres).
4. Usa `wire:model.live` para el input y `wire:click` para las acciones.
5. Muestra un indicador de carga con `wire:loading` al agregar tareas.
6. Integra Alpine.js para una animación de confirmación al eliminar.
7. Agrega `wire:poll.30s` para refrescar la lista automáticamente.

```blade
{{-- Estructura sugerida --}}
<div>
    <form wire:submit="addTask">
        <input wire:model.live="newTask" placeholder="Nueva tarea...">
        <button type="submit">Agregar</button>
    </form>

    @foreach($tasks as $task)
        <div wire:key="{{ $task->id }}">
            <input type="checkbox" wire:click="toggleComplete({{ $task->id }})">
            <span>{{ $task->title }}</span>
            <button wire:click="deleteTask({{ $task->id }})">×</button>
        </div>
    @endforeach
</div>
```

## Resumen

- **Livewire** permite construir interfaces reactivas desde PHP, sin escribir JavaScript manualmente.
- Los componentes tienen una clase PHP (lógica) y una vista Blade (presentación).
- `wire:model` sincroniza inputs con propiedades; `wire:click` invoca métodos del componente.
- La validación en tiempo real se logra con `validateOnly()` en el hook `updated`.
- **Alpine.js** se integra para interactividad del lado del cliente (toggles, animaciones, modales).
- `wire:loading` muestra indicadores de carga durante las peticiones al servidor.
- File uploads se manejan con el trait `WithFileUploads` y `temporaryUrl()` para previews.
- **Polling** (`wire:poll`) actualiza datos automáticamente; **lazy loading** difiere componentes pesados.
