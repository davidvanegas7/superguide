# APIs REST: Diseño Avanzado

REST (Representational State Transfer) es un estilo arquitectónico para sistemas distribuidos basado en recursos, HTTP y sin estado. Diseñar una API REST correctamente va más allá de mapear CRUD a verbos HTTP.

---

## Principios REST

| Restricción | Descripción |
|---|---|
| **Stateless** | Cada petición contiene todo lo necesario; el servidor no guarda estado del cliente |
| **Uniform Interface** | URIs identifican recursos; representaciones estándar (JSON, XML) |
| **Client-Server** | Separación de responsabilidades entre cliente y servidor |
| **Cacheable** | Las respuestas deben indicar si son cacheables |
| **Layered System** | El cliente no sabe si habla directamente con el servidor o a través de proxies |
| **Code on Demand** | Opcional: el servidor puede enviar código ejecutable al cliente |

---

## Diseño de URIs

```
# ✅ Recursos en plural, sustantivos (no verbos)
GET    /users                    # listar usuarios
GET    /users/42                 # usuario específico
POST   /users                    # crear usuario
PUT    /users/42                 # reemplazar usuario
PATCH  /users/42                 # actualización parcial
DELETE /users/42                 # eliminar usuario

# ✅ Recursos anidados para relaciones
GET    /users/42/orders          # pedidos del usuario 42
POST   /users/42/orders          # crear pedido para usuario 42
GET    /users/42/orders/7        # pedido 7 del usuario 42

# ❌ Verbos en URIs (no es REST)
POST   /createUser
GET    /getUserById?id=42
POST   /deleteUser/42

# ✅ Acciones como sub-recursos (cuando no hay verbo adecuado)
POST   /orders/7/cancel          # acción específica
POST   /users/42/password-reset  # acción específica
POST   /invoices/15/send         # acción específica
```

---

## Verbos HTTP correctamente usados

```typescript
// GET: idempotente, no modifica estado, cacheable
app.get('/users', async (req, res) => {
  const { page = 1, limit = 20, role } = req.query;
  const users = await userService.findAll({ page: +page, limit: +limit, role: role as string });
  res.json(users);
});

// POST: crea un recurso, no idempotente
app.post('/users', async (req, res) => {
  const user = await userService.register(req.body);
  res
    .status(201)                                           // Created
    .setHeader('Location', `/users/${user.id}`)           // URI del nuevo recurso
    .json({ data: user });
});

// PUT: reemplaza COMPLETAMENTE el recurso, idempotente
app.put('/users/:id', async (req, res) => {
  const user = await userService.replace(req.params.id, req.body);
  res.json({ data: user });
});

// PATCH: actualización PARCIAL, no necesariamente idempotente
app.patch('/users/:id', async (req, res) => {
  const user = await userService.update(req.params.id, req.body);
  res.json({ data: user });
});

// DELETE: elimina el recurso, idempotente
app.delete('/users/:id', async (req, res) => {
  await userService.delete(req.params.id);
  res.status(204).send();  // No Content
});
```

---

## Códigos de estado HTTP

```
2xx — Éxito
  200 OK               Operación exitosa (GET, PUT, PATCH)
  201 Created          Recurso creado (POST)
  204 No Content       Sin cuerpo de respuesta (DELETE, PATCH sin retorno)

3xx — Redirección
  301 Moved Permanently  URI cambió permanentemente
  304 Not Modified       Recurso no cambió (para caché ETag/Last-Modified)

4xx — Error del cliente
  400 Bad Request        Datos inválidos o malformados
  401 Unauthorized       No autenticado (falta/token inválido)
  403 Forbidden          Autenticado pero sin permisos
  404 Not Found          Recurso no existe
  409 Conflict           Conflicto (ej: email duplicado)
  422 Unprocessable      Validación de negocio fallida
  429 Too Many Requests  Rate limit superado

5xx — Error del servidor
  500 Internal Server Error  Error inesperado del servidor
  502 Bad Gateway            Error en servicio upstream
  503 Service Unavailable    Sobrecarga o mantenimiento
```

---

## Versionado de APIs

```typescript
// ✅ Versionado en la URI (más común, explícito)
app.use('/api/v1', v1Router);
app.use('/api/v2', v2Router);

// GET /api/v1/users → comportamiento v1
// GET /api/v2/users → comportamiento v2

// ✅ Versionado por cabecera (más RESTful, menos visible)
app.get('/api/users', (req, res) => {
  const version = req.headers['api-version'] ?? '1';
  if (version === '2') {
    return usersV2Handler(req, res);
  }
  return usersV1Handler(req, res);
});

// ✅ Accept header (Content Negotiation)
// GET /api/users
// Accept: application/vnd.myapp.v2+json
app.get('/api/users', (req, res) => {
  const accept = req.headers['accept'] ?? '';
  const version = accept.includes('v2') ? 2 : 1;
  // ...
});
```

---

## Paginación

```typescript
// Paginación por offset
interface PaginatedResponse<T> {
  data:  T[];
  meta: {
    page:       number;
    limit:      number;
    total:      number;
    totalPages: number;
    hasNext:    boolean;
    hasPrev:    boolean;
  };
  links: {
    self:  string;
    first: string;
    last:  string;
    next:  string | null;
    prev:  string | null;
  };
}

function paginate<T>(
  data:  T[],
  total: number,
  page:  number,
  limit: number,
  baseUrl: string
): PaginatedResponse<T> {
  const totalPages = Math.ceil(total / limit);
  return {
    data,
    meta: {
      page, limit, total, totalPages,
      hasNext: page < totalPages,
      hasPrev: page > 1,
    },
    links: {
      self:  `${baseUrl}?page=${page}&limit=${limit}`,
      first: `${baseUrl}?page=1&limit=${limit}`,
      last:  `${baseUrl}?page=${totalPages}&limit=${limit}`,
      next:  page < totalPages ? `${baseUrl}?page=${page + 1}&limit=${limit}` : null,
      prev:  page > 1          ? `${baseUrl}?page=${page - 1}&limit=${limit}` : null,
    },
  };
}

// Paginación por cursor (más eficiente para grandes datasets)
interface CursorPaginatedResponse<T> {
  data:    T[];
  cursor:  { next: string | null; prev: string | null };
  hasMore: boolean;
}
```

---

## Formato de errores consistente

```typescript
interface ApiError {
  error: {
    code:    string;          // Código interno: USER_NOT_FOUND, VALIDATION_ERROR
    message: string;          // Mensaje legible para el desarrollador
    details?: Record<string, string[]>;  // Errores de campo en validaciones
    traceId?: string;         // Para correlacionar logs
  };
}

// Middleware de errores en Express
app.use((err: Error, req: Request, res: Response, _next: NextFunction) => {
  const traceId = req.headers['x-trace-id'] as string ?? crypto.randomUUID();

  if (err instanceof ValidationError) {
    return res.status(422).json({
      error: { code: 'VALIDATION_ERROR', message: err.message, details: err.fields, traceId }
    });
  }
  if (err instanceof NotFoundError) {
    return res.status(404).json({
      error: { code: 'NOT_FOUND', message: err.message, traceId }
    });
  }
  // Error desconocido: no exponer detalles internos
  console.error(err);
  res.status(500).json({
    error: { code: 'INTERNAL_ERROR', message: 'Ha ocurrido un error', traceId }
  });
});
```

---

## Idempotencia

```typescript
// Los clientes pueden reintentar operaciones idempotentes de forma segura
// POST no es idempotente por defecto, pero se puede hacer idempotente
// usando una Idempotency-Key en la cabecera

app.post('/payments', async (req, res) => {
  const idempotencyKey = req.headers['idempotency-key'] as string;
  if (!idempotencyKey) {
    return res.status(400).json({ error: { code: 'MISSING_IDEMPOTENCY_KEY', message: 'Requerida' } });
  }

  // Verificar si ya procesamos esta key
  const cached = await cache.get(`idempotency:${idempotencyKey}`);
  if (cached) {
    return res.status(200).json(JSON.parse(cached));  // Respuesta idéntica
  }

  const payment = await paymentService.process(req.body);
  const response = { data: payment };

  // Guardar respuesta para futuras repeticiones (TTL: 24h)
  await cache.set(`idempotency:${idempotencyKey}`, JSON.stringify(response), 86400);

  res.status(201).json(response);
});
```

---

## Rate Limiting

```typescript
import rateLimit from 'express-rate-limit';
import RedisStore from 'rate-limit-redis';

const limiter = rateLimit({
  windowMs: 15 * 60 * 1000,  // 15 minutos
  max:      100,              // máximo 100 requests por ventana
  message:  { error: { code: 'RATE_LIMIT_EXCEEDED', message: 'Demasiadas peticiones' } },
  headers:  true,             // X-RateLimit-Limit, X-RateLimit-Remaining, Retry-After
  store:    new RedisStore({ client: redisClient }),
  keyGenerator: (req) => req.user?.id ?? req.ip,  // por usuario autenticado
});

app.use('/api/', limiter);

// Rate limit más estricto para endpoints sensibles
const authLimiter = rateLimit({
  windowMs: 60 * 60 * 1000,  // 1 hora
  max: 5,                     // solo 5 intentos de login
  message: { error: { code: 'TOO_MANY_LOGIN_ATTEMPTS' } },
});
app.use('/api/auth/login', authLimiter);
```
