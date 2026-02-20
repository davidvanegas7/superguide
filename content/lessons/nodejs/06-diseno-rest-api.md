# Diseño REST: recursos, verbos HTTP y buenas prácticas

## ¿Qué es REST?

REST (Representational State Transfer) es un estilo arquitectónico para APIs HTTP. No es un protocolo ni un estándar, sino un conjunto de restricciones. Una API que las cumple se llama "RESTful".

Las restricciones principales:
1. **Cliente-servidor**: separación de responsabilidades
2. **Sin estado (stateless)**: cada petición contiene toda la información necesaria — el servidor no guarda sesión
3. **Cacheable**: las respuestas deben indicar si se pueden cachear
4. **Interfaz uniforme**: URLs predecibles, verbos HTTP semánticos

---

## Recursos y URIs

El recurso es el concepto central de REST. Una URI identifica un recurso, no una acción.

```
❌ Orientado a acciones (RPC-style):
POST /getUser
POST /createUser
POST /deleteUser
POST /getUserOrders

✅ Orientado a recursos (REST):
GET    /users
GET    /users/:id
POST   /users
PUT    /users/:id
DELETE /users/:id
GET    /users/:id/orders
```

### Reglas de nomenclatura de URIs

| Regla | ❌ Incorrecto | ✅ Correcto |
|---|---|---|
| Sustantivos, no verbos | `/getProducts` | `/products` |
| Plural para colecciones | `/product` | `/products` |
| Minúsculas con guiones | `/ProductCategories` | `/product-categories` |
| Sin extensión de archivo | `/users.json` | `/users` (usar `Accept` header) |
| Jerarquía para relaciones | `/getOrderItems` | `/orders/:id/items` |

### Jerarquía y recursos anidados

```
/users                     → colección de usuarios
/users/:userId             → usuario específico
/users/:userId/orders      → pedidos de un usuario específico
/users/:userId/orders/:id  → pedido específico de un usuario
/orders/:id/items          → items de un pedido (recurso de primer nivel también válido)
```

> **Regla práctica**: no anidar más de 2 niveles. `/users/:id/orders` ✅, `/users/:id/orders/:orderId/items/:itemId` es demasiado.

---

## Verbos HTTP y su semántica

| Verbo | Uso | Idempotente | Con body |
|---|---|---|---|
| `GET` | Obtener recurso(s) | ✅ | ❌ |
| `POST` | Crear recurso | ❌ | ✅ |
| `PUT` | Reemplazar recurso completo | ✅ | ✅ |
| `PATCH` | Actualizar campos específicos | ✅* | ✅ |
| `DELETE` | Eliminar recurso | ✅ | opcional |
| `HEAD` | Como GET pero sin body | ✅ | ❌ |
| `OPTIONS` | Preflight CORS / capacidades | ✅ | ❌ |

**Idempotencia**: llamar la operación N veces tiene el mismo efecto que llamarla 1 vez.

### PUT vs PATCH

```typescript
// Recurso actual:
// { id: 1, name: "Ana", email: "ana@test.com", role: "viewer", active: true }

// PUT — reemplaza TODO el recurso (si omites un campo, se borra/pone default)
PUT /users/1
{ "name": "Ana García", "email": "ana@test.com", "role": "admin" }
// Resultado: { id: 1, name: "Ana García", email: "ana@test.com", role: "admin", active: undefined }

// PATCH — solo actualiza los campos enviados
PATCH /users/1
{ "role": "admin" }
// Resultado: { id: 1, name: "Ana", email: "ana@test.com", role: "admin", active: true }
```

---

## Códigos de estado HTTP

### 2xx — Éxito

```typescript
// 200 OK — respuesta genérica de éxito
res.status(200).json({ data: users });

// 201 Created — recurso creado exitosamente
res.status(201).json({ data: newUser });
// Buena práctica: incluir Location header
res.setHeader('Location', `/api/v1/users/${newUser.id}`);

// 204 No Content — éxito sin cuerpo (DELETE, PUT sin retorno)
res.status(204).send();

// 206 Partial Content — respuesta parcial (streaming, Range requests)
```

### 4xx — Errores del cliente

```typescript
// 400 Bad Request — petición mal formada
res.status(400).json({ error: 'JSON inválido en el body' });

// 401 Unauthorized — no autenticado (falta o token inválido)
res.status(401).json({ error: 'Token inválido o expirado' });

// 403 Forbidden — autenticado pero sin permisos
res.status(403).json({ error: 'No tienes permiso para esta acción' });

// 404 Not Found — recurso no existe
res.status(404).json({ error: 'Usuario no encontrado' });

// 409 Conflict — conflicto de estado (email ya registrado, versión desactualizada)
res.status(409).json({ error: 'El email ya está en uso' });

// 410 Gone — recurso eliminado permanentemente (a diferencia de 404)

// 422 Unprocessable Entity — validación semántica falla (formato correcto, datos inválidos)
res.status(422).json({
  error: 'Datos inválidos',
  details: [{ field: 'email', message: 'Formato de email inválido' }]
});

// 429 Too Many Requests — rate limit superado
res.status(429).json({ error: 'Demasiadas peticiones' });
```

### 5xx — Errores del servidor

```typescript
// 500 Internal Server Error — error no controlado
// 502 Bad Gateway — el servidor upstream falló
// 503 Service Unavailable — el servicio está caído (mantenimiento)
// 504 Gateway Timeout — el servidor upstream tardó demasiado
```

---

## Formato de respuesta consistente

Define un formato estándar para todas las respuestas de tu API:

```typescript
// src/utils/response.ts

// Respuesta exitosa con un recurso
interface SuccessResponse<T> {
  data:    T;
  message?: string;
}

// Respuesta exitosa con colección y paginación
interface PaginatedResponse<T> {
  data:  T[];
  meta: {
    total:    number;
    page:     number;
    pageSize: number;
    pages:    number;
  };
}

// Respuesta de error
interface ErrorResponse {
  error:    string;
  code?:    string;
  details?: { field: string; message: string }[];
}

// Helpers
export const respond = {
  ok<T>(res: Response, data: T, status = 200) {
    return res.status(status).json({ data });
  },
  created<T>(res: Response, data: T, location?: string) {
    if (location) res.setHeader('Location', location);
    return res.status(201).json({ data });
  },
  paginated<T>(res: Response, data: T[], total: number, page: number, pageSize: number) {
    res.setHeader('X-Total-Count', total);
    return res.json({
      data,
      meta: { total, page, pageSize, pages: Math.ceil(total / pageSize) },
    });
  },
  noContent(res: Response) {
    return res.status(204).send();
  },
  error(res: Response, status: number, message: string, details?: unknown) {
    return res.status(status).json({ error: message, details });
  },
};
```

---

## Paginación, filtrado y ordenamiento

```typescript
// src/utils/queryParams.ts
import { Request } from 'express';
import { z } from 'zod';

const paginationSchema = z.object({
  page:     z.coerce.number().int().positive().default(1),
  pageSize: z.coerce.number().int().min(1).max(100).default(20),
  sortBy:   z.string().optional(),
  order:    z.enum(['asc', 'desc']).default('asc'),
});

export function parsePagination(query: Request['query']) {
  return paginationSchema.parse(query);
}

// GET /products?page=2&pageSize=10&sortBy=price&order=desc&minPrice=100&category=electronics
```

### Estrategias de paginación

**Offset-based** (la más común, pero tiene problemas con datos que cambian):
```
GET /users?page=2&pageSize=20
→ OFFSET 20 LIMIT 20
```

**Cursor-based** (más eficiente para grandes volúmenes y datos en tiempo real):
```
GET /users?cursor=eyJpZCI6MjB9&pageSize=20
→ WHERE id > 20 LIMIT 20
```

---

## Versionado de API

Cuando necesitas hacer cambios incompatibles, versiona la API:

```typescript
// Opción 1: URL path (más visible, más fácil de probar)
app.use('/api/v1', v1Router);
app.use('/api/v2', v2Router);

// Opción 2: Header (más "RESTful" pero menos práctico)
// Accept: application/vnd.miapi.v2+json

// Opción 3: Query param (no recomendada)
// GET /api/users?version=2
```

---

## Acciones no-CRUD

Cuando una operación no encaja en CRUD puro, usa sub-recursos con verbos descriptivos:

```
POST /orders/:id/cancel          → cancelar un pedido
POST /users/:id/activate         → activar cuenta de usuario
POST /users/:id/password-reset   → iniciar reset de contraseña
POST /invoices/:id/send          → enviar factura por email
POST /products/:id/duplicate     → duplicar un producto
```

---

## Checklist de una buena API REST

- [ ] URIs con sustantivos en plural (`/products`, no `/getProduct`)
- [ ] Verbos HTTP semánticos (GET para leer, POST para crear, etc.)
- [ ] Códigos de estado correctos (201 al crear, 204 al borrar, 422 para validación)
- [ ] Formato de respuesta consistente en toda la API
- [ ] Paginación en todos los listados
- [ ] Versionado desde el día uno (`/api/v1/`)
- [ ] Headers de seguridad (ver lección de middlewares)
- [ ] Documentación con OpenAPI/Swagger

---

## Resumen

| Concepto | Regla |
|---|---|
| URIs | Sustantivos en plural, minúsculas, guiones |
| Anidación | Máximo 2 niveles de profundidad |
| POST vs PUT vs PATCH | Crear / Reemplazar todo / Actualizar parcial |
| Errores de cliente | 400, 401, 403, 404, 409, 422, 429 |
| Errores de servidor | 500, 502, 503, 504 |
| Paginación | Offset para casos simples, cursor para alto volumen |
| Acciones | Sub-recursos con verbo (`/orders/:id/cancel`) |

En la siguiente lección implementamos validación robusta con **Zod**, eliminando `any` de los cuerpos de las peticiones.
