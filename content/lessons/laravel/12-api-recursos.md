---
title: "APIs RESTful y API Resources"
slug: "laravel-apis-restful-api-resources"
description: "Construye APIs RESTful profesionales con Laravel usando API Resources, paginación, versionado y autenticación con Sanctum."
---

# APIs RESTful y API Resources

Las APIs (Application Programming Interfaces) permiten que diferentes aplicaciones se comuniquen entre sí. Laravel facilita la construcción de APIs RESTful robustas gracias a sus rutas dedicadas, API Resources para transformar datos, y Laravel Sanctum para autenticación basada en tokens. En esta lección aprenderás a diseñar, construir y proteger APIs completas.

## Rutas API con `apiResource`

Laravel proporciona `Route::apiResource` que genera automáticamente las rutas REST sin las rutas de formularios (`create` y `edit`), ya que una API no sirve vistas HTML:

```php
// routes/api.php
use App\Http\Controllers\Api\ProductController;

// Genera: index, store, show, update, destroy
Route::apiResource('products', ProductController::class);

// Varios recursos a la vez
Route::apiResources([
    'products'   => ProductController::class,
    'categories' => CategoryController::class,
]);

// Con prefijo de versión
Route::prefix('v1')->group(function () {
    Route::apiResource('products', ProductController::class);
});
```

Las rutas generadas serán:

| Método | URI | Acción |
|---|---|---|
| GET | `/api/products` | index |
| POST | `/api/products` | store |
| GET | `/api/products/{product}` | show |
| PUT/PATCH | `/api/products/{product}` | update |
| DELETE | `/api/products/{product}` | destroy |

## JSON Resource: Transformando Modelos

Los API Resources actúan como una capa de transformación entre tus modelos Eloquent y las respuestas JSON. Permiten controlar exactamente qué datos se envían al cliente.

### Crear un Resource

```bash
php artisan make:resource ProductResource
```

### Definir la Transformación

```php
// app/Http/Resources/ProductResource.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transforma el recurso en un arreglo.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'price'       => $this->price,
            'price_formatted' => '$' . number_format($this->price, 2),
            'in_stock'    => $this->stock > 0,
            'category'    => new CategoryResource($this->whenLoaded('category')),
            'tags'        => TagResource::collection($this->whenLoaded('tags')),
            'created_at'  => $this->created_at->toISOString(),
            'updated_at'  => $this->updated_at->toISOString(),
        ];
    }
}
```

### Uso en el Controller

```php
// app/Http/Controllers/Api/ProductController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // GET /api/products
    public function index()
    {
        $products = Product::with(['category', 'tags'])->paginate(15);

        // Retorna una colección paginada
        return ProductResource::collection($products);
    }

    // GET /api/products/{product}
    public function show(Product $product)
    {
        $product->load(['category', 'tags']);

        return new ProductResource($product);
    }

    // POST /api/products
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create($validated);

        return new ProductResource($product);
    }

    // PUT /api/products/{product}
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
        ]);

        $product->update($validated);

        return new ProductResource($product);
    }

    // DELETE /api/products/{product}
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Producto eliminado'], 200);
    }
}
```

## ResourceCollection: Colecciones Personalizadas

Para colecciones con metadatos adicionales, crea un ResourceCollection dedicado:

```bash
php artisan make:resource ProductCollection --collection
```

```php
// app/Http/Resources/ProductCollection.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    /**
     * Transforma la colección en un arreglo.
     */
    public function toArray(Request $request): array
    {
        return [
            'data'       => $this->collection, // Usa ProductResource automáticamente
            'statistics' => [
                'total'       => $this->collection->count(),
                'avg_price'   => $this->collection->avg('price'),
                'max_price'   => $this->collection->max('price'),
            ],
        ];
    }
}
```

## Data Wrapping y Paginación

Por defecto, Laravel envuelve los recursos en una clave `data`:

```json
{
    "data": [
        { "id": 1, "name": "Laptop" },
        { "id": 2, "name": "Mouse" }
    ]
}
```

Para desactivar el wrapping globalmente:

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Http\Resources\Json\JsonResource;

public function boot(): void
{
    JsonResource::withoutWrapping();
}
```

### Paginación Automática

Cuando pasas un paginador a un Resource, Laravel incluye automáticamente los metadatos de paginación:

```json
{
    "data": [...],
    "links": {
        "first": "http://app.test/api/products?page=1",
        "last": "http://app.test/api/products?page=5",
        "prev": null,
        "next": "http://app.test/api/products?page=2"
    },
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 73
    }
}
```

## Atributos Condicionales

Los Resources permiten incluir datos solo cuando se cumplen ciertas condiciones:

```php
public function toArray(Request $request): array
{
    return [
        'id'    => $this->id,
        'name'  => $this->name,
        'price' => $this->price,

        // Solo incluir si la relación está cargada
        'category' => new CategoryResource($this->whenLoaded('category')),
        'reviews'  => ReviewResource::collection($this->whenLoaded('reviews')),

        // Solo incluir si se cumple una condición
        'secret_code' => $this->when($request->user()?->isAdmin(), $this->secret_code),

        // Incluir un campo solo si el usuario lo solicita via query string
        // GET /api/products?include=stock
        'stock' => $this->when(
            $request->has('include') && str_contains($request->include, 'stock'),
            $this->stock
        ),

        // Merge condicional de múltiples campos
        $this->mergeWhen($request->user()?->isAdmin(), [
            'cost'   => $this->cost,
            'margin' => $this->price - $this->cost,
        ]),
    ];
}
```

## API Versioning

Una estrategia común es versionar tus APIs mediante prefijos de ruta y namespaces separados:

```php
// routes/api.php

// Versión 1
Route::prefix('v1')->group(function () {
    Route::apiResource('products', App\Http\Controllers\Api\V1\ProductController::class);
});

// Versión 2 con cambios en la estructura de respuesta
Route::prefix('v2')->group(function () {
    Route::apiResource('products', App\Http\Controllers\Api\V2\ProductController::class);
});
```

```php
// Estructura de carpetas recomendada:
// app/Http/Controllers/Api/V1/ProductController.php
// app/Http/Controllers/Api/V2/ProductController.php
// app/Http/Resources/V1/ProductResource.php
// app/Http/Resources/V2/ProductResource.php
```

## Autenticación con Sanctum

Laravel Sanctum proporciona autenticación ligera basada en tokens para APIs:

```bash
# Instalar Sanctum (incluido por defecto en Laravel 11+)
php artisan install:api
```

### Generar Tokens

```php
// En un controlador de autenticación
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        // Crear token con habilidades específicas
        $token = $user->createToken('api-token', ['products:read', 'products:write']);

        return response()->json([
            'token' => $token->plainTextToken,
            'type'  => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        // Revocar el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }
}
```

### Proteger Rutas

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class);
    Route::get('/user', fn (Request $request) => $request->user());
});
```

### Verificar Habilidades del Token

```php
if ($request->user()->tokenCan('products:write')) {
    // El token tiene permiso de escritura
}
```

## Ejercicio Práctico

Construye una API RESTful completa para un sistema de biblioteca:

1. Crea un modelo `Book` con: `title`, `author`, `isbn`, `genre_id`, `available`.
2. Genera `BookResource` que incluya:
   - Todos los campos básicos transformados.
   - La relación `genre` con `whenLoaded`.
   - Un campo `availability_label` condicional.
3. Crea `BookController` como API controller con los 5 métodos REST.
4. Implementa paginación en el `index` (10 libros por página).
5. Protege las rutas `store`, `update` y `destroy` con Sanctum.
6. Versiona la API bajo `v1`.

```bash
# Probando con curl
curl -X GET http://localhost/api/v1/books
curl -X POST http://localhost/api/v1/books \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Laravel Pro","author":"Taylor","isbn":"123","genre_id":1}'
```

## Resumen

- `Route::apiResource` genera rutas REST sin vistas (index, store, show, update, destroy).
- **JsonResource** transforma modelos a JSON, controlando la estructura de la respuesta.
- **ResourceCollection** permite agregar metadatos a colecciones de recursos.
- `whenLoaded` y `when` incluyen datos condicionalmente, evitando N+1 y exponiendo solo lo necesario.
- La paginación se integra automáticamente con links y meta en la respuesta.
- **Sanctum** ofrece autenticación por tokens simple y segura para proteger endpoints.
- Versiona tus APIs con prefijos de ruta (`v1`, `v2`) para mantener compatibilidad.
