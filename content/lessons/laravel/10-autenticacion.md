---
title: "Autenticación"
slug: "laravel-autenticacion"
description: "Domina el sistema de autenticación de Laravel: guards, providers, paquetes oficiales (Breeze, Fortify, Sanctum), protección de rutas, recuperación de contraseñas y verificación de email."
---

# Autenticación

La autenticación es la piedra angular de la seguridad en cualquier aplicación web. Laravel proporciona un sistema de autenticación completo y flexible que incluye login, registro, recuperación de contraseña, verificación de email y más. En esta lección explorarás la arquitectura del sistema y aprenderás a implementar flujos de autenticación completos.

## Arquitectura de Autenticación en Laravel

El sistema de autenticación de Laravel se basa en dos conceptos clave:

- **Guards**: determinan *cómo* se autentican los usuarios (por sesión, por token, etc.).
- **Providers**: determinan *de dónde* se obtienen los usuarios (base de datos, API externa, etc.).

```php
// config/auth.php
return [
    // Guard por defecto
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    // Definición de guards
    'guards' => [
        'web' => [
            'driver' => 'session',        // Autenticación por sesión (web)
            'provider' => 'users',
        ],
        'api' => [
            'driver' => 'sanctum',        // Autenticación por token (API)
            'provider' => 'users',
        ],
    ],

    // Definición de providers
    'providers' => [
        'users' => [
            'driver' => 'eloquent',       // Usar Eloquent para obtener usuarios
            'model' => App\Models\User::class,
        ],
        // Provider con Query Builder (alternativo)
        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    // Configuración de recuperación de contraseña
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,               // Minutos de validez del token
            'throttle' => 60,             // Segundos entre solicitudes
        ],
    ],
];
```

## Paquetes Oficiales de Autenticación

Laravel ofrece varios paquetes oficiales según tus necesidades:

### Laravel Breeze (Recomendado para empezar)

Implementación sencilla y ligera de autenticación con vistas prediseñadas:

```bash
# Instalar Breeze
composer require laravel/breeze --dev

# Publicar las vistas y rutas (Blade + Tailwind)
php artisan breeze:install blade

# Otras opciones de frontend:
# php artisan breeze:install vue       # Con Vue.js
# php artisan breeze:install react     # Con React
# php artisan breeze:install api       # Solo API (sin vistas)

# Instalar dependencias de frontend y compilar
npm install && npm run build

# Ejecutar migraciones
php artisan migrate
```

Breeze incluye:
- Registro de usuarios
- Login / Logout
- Recuperación de contraseña
- Verificación de email
- Confirmación de contraseña
- Vistas con Tailwind CSS

### Laravel Fortify (Backend sin vistas)

Proporciona la lógica del backend sin vistas predefinidas, ideal para aplicaciones con frontend personalizado:

```bash
composer require laravel/fortify
php artisan fortify:install
php artisan migrate
```

```php
// config/fortify.php
'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
    Features::updateProfileInformation(),
    Features::updatePasswords(),
    Features::twoFactorAuthentication(),  // 2FA
],
```

### Laravel Sanctum (APIs y SPAs)

Sistema de autenticación ligero para APIs y aplicaciones SPA:

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

```php
// app/Models/User.php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
}
```

```php
// Crear un token para un usuario
$token = $user->createToken('nombre-del-token');
$textoToken = $token->plainTextToken;

// Crear token con habilidades (permisos)
$token = $user->createToken('admin-token', ['crear-posts', 'eliminar-posts']);

// Verificar habilidades
if ($user->tokenCan('crear-posts')) {
    // Tiene permiso
}

// Revocar tokens
$user->tokens()->delete();              // Todos los tokens
$user->currentAccessToken()->delete();  // Token actual
```

## El Facade Auth

El facade `Auth` es la interfaz principal para interactuar con el sistema de autenticación:

```php
use Illuminate\Support\Facades\Auth;

// Verificar si hay un usuario autenticado
if (Auth::check()) {
    // El usuario está logueado
}

// Obtener el usuario autenticado
$usuario = Auth::user();
$id = Auth::id();

// También disponible desde el request
$usuario = $request->user();

// Verificar si el usuario es invitado
if (Auth::guest()) {
    // No hay usuario autenticado
}
```

## Flujo de Login y Logout

### Login manual

```php
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Validar credenciales
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Intentar autenticar
        if (Auth::attempt($credenciales)) {
            // Regenerar sesión para prevenir session fixation
            $request->session()->regenerate();

            // Redirigir a la página que el usuario intentaba visitar
            return redirect()->intended('/dashboard');
        }

        // Autenticación fallida
        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden.',
        ])->onlyInput('email');
    }
}
```

### Login con "Recordarme"

```php
public function login(Request $request)
{
    $credenciales = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // El tercer parámetro activa "remember me"
    $recordar = $request->boolean('recordar');

    if (Auth::attempt($credenciales, $recordar)) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    return back()->withErrors([
        'email' => 'Credenciales incorrectas.',
    ]);
}
```

```blade
{{-- Formulario con "recordarme" --}}
<form method="POST" action="{{ route('login') }}">
    @csrf

    <div>
        <label for="email">Correo electrónico</label>
        <input type="email" name="email" id="email"
               value="{{ old('email') }}" required autofocus>
        @error('email')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="password">Contraseña</label>
        <input type="password" name="password" id="password" required>
    </div>

    <div>
        <label>
            <input type="checkbox" name="recordar">
            Recordarme
        </label>
    </div>

    <button type="submit">Iniciar sesión</button>

    <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
</form>
```

### Logout

```php
public function logout(Request $request)
{
    Auth::logout();

    // Invalidar la sesión
    $request->session()->invalidate();

    // Regenerar el token CSRF
    $request->session()->regenerateToken();

    return redirect('/');
}
```

### Condiciones adicionales en el login

```php
// Login solo si el usuario está activo
if (Auth::attempt([
    'email' => $request->email,
    'password' => $request->password,
    'activo' => true,  // Condición adicional
])) {
    // Autenticado y activo
}

// Login con un guard específico
if (Auth::guard('admin')->attempt($credenciales)) {
    // Autenticado como admin
}
```

## Proteger Rutas con Middleware

### Middleware `auth`

```php
// Proteger rutas individuales
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth');

// Proteger un grupo de rutas
Route::middleware('auth')->group(function () {
    Route::get('/perfil', [PerfilController::class, 'show'])->name('perfil');
    Route::put('/perfil', [PerfilController::class, 'update']);
    Route::get('/mis-pedidos', [PedidoController::class, 'index']);
    Route::resource('posts', PostController::class);
});

// Proteger en el controlador
class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // O solo en ciertos métodos:
        $this->middleware('auth')->only(['create', 'store', 'edit', 'update']);
        $this->middleware('auth')->except(['index', 'show']);
    }
}

// Redirigiendo a invitados
Route::get('/login', [LoginController::class, 'showForm'])
    ->middleware('guest')  // Solo accesible si NO estás logueado
    ->name('login');
```

### Middleware `verified` (email verificado)

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    // Solo usuarios con email verificado pueden acceder
});
```

## Recuperación de Contraseña (Password Reset)

Laravel incluye un flujo completo de recuperación de contraseña:

```php
// routes/web.php (ya incluidas si usas Breeze)

// 1. Mostrar formulario para solicitar reseteo
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');

// 2. Enviar email con link de reseteo
Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
        ? back()->with('status', __($status))
        : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

// 3. Mostrar formulario para nueva contraseña
Route::get('/reset-password/{token}', function (string $token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

// 4. Procesar el cambio de contraseña
Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
        ? redirect()->route('login')->with('status', __($status))
        : back()->withErrors(['email' => [__($status)]]);
})->middleware('guest')->name('password.update');
```

## Verificación de Email

Para activar la verificación de email, el modelo User debe implementar la interfaz `MustVerifyEmail`:

```php
// app/Models/User.php
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;
    // ...
}
```

```php
// routes/web.php

// Mostrar aviso de que debe verificar su email
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// Procesar la verificación (cuando clica el link del email)
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/dashboard');
})->middleware(['auth', 'signed'])->name('verification.verify');

// Reenviar email de verificación
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '¡Email de verificación reenviado!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
```

## Autenticación en Vistas Blade

```blade
{{-- Mostrar contenido según autenticación --}}
@auth
    <p>Bienvenido, {{ auth()->user()->name }}</p>

    <nav>
        <a href="{{ route('perfil') }}">Mi Perfil</a>
        <a href="{{ route('mis-pedidos') }}">Mis Pedidos</a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Cerrar sesión</button>
        </form>
    </nav>
@endauth

@guest
    <nav>
        <a href="{{ route('login') }}">Iniciar sesión</a>
        <a href="{{ route('register') }}">Registrarse</a>
    </nav>
@endguest

{{-- Verificar guard específico --}}
@auth('admin')
    <a href="/admin">Panel de Administración</a>
@endauth
```

## Ejercicio Práctico

Implementa un sistema de autenticación completo para una aplicación de gestión de proyectos:

1. **Instala Laravel Breeze** con Blade y ejecuta las migraciones.
2. **Añade campos al modelo User**: `rol` (enum: admin, editor, usuario), `avatar`, `biografia`, `activo` (boolean).
3. **Crea un middleware** `VerificarActivo` que verifique que el campo `activo` sea `true`, y redirige al logout si no lo es.
4. **Protege las rutas** del panel con `auth` y `verified`.
5. **Implementa el flujo completo**: registro → verificación de email → login → dashboard protegido → logout.
6. **Personaliza el login** para que solo permita acceso a usuarios activos.
7. **Añade "Recordarme"** al formulario de login.

```bash
# Paso 1: Instalar Breeze
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run build
php artisan migrate
```

```php
// Paso 6: Login personalizado
if (Auth::attempt([
    'email' => $request->email,
    'password' => $request->password,
    'activo' => true,
], $request->boolean('recordar'))) {
    $request->session()->regenerate();
    return redirect()->intended('/dashboard');
}

return back()->withErrors([
    'email' => 'Credenciales inválidas o cuenta desactivada.',
]);
```

## Resumen

- La autenticación en Laravel se basa en **Guards** (cómo autenticar) y **Providers** (de dónde obtener usuarios).
- **Laravel Breeze** es la opción más sencilla para implementar autenticación con vistas incluidas.
- **Sanctum** es ideal para APIs y SPAs con autenticación por tokens.
- **`Auth::attempt()`** verifica credenciales y crea la sesión; **`Auth::logout()`** la destruye.
- Las rutas se protegen con el middleware **`auth`**; el middleware **`guest`** restringe a usuarios no autenticados.
- El flujo de **"Recordarme"** se activa pasando `true` como segundo parámetro de `attempt()`.
- La **recuperación de contraseña** usa tokens firmados enviados por email.
- La **verificación de email** requiere implementar `MustVerifyEmail` y usar el middleware `verified`.
