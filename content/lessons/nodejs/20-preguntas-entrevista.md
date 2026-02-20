# Preguntas de entrevista: Node.js Backend

Esta lección recopila las preguntas más frecuentes en entrevistas para posiciones de **Backend con Node.js**. Están organizadas por categoría, con respuestas completas que puedes adaptar según el nivel de la posición.

---

## Node.js y el Event Loop

### ¿Qué es el Event Loop y cómo funciona en Node.js?

El Event Loop es el mecanismo que permite a Node.js realizar operaciones I/O no bloqueantes a pesar de ser single-threaded.

Flujo simplificado:
1. **Call stack**: ejecuta código sincrónico
2. **Node APIs**: las operaciones async (fs, http, timers) se delegan al sistema operativo o al thread pool de libuv
3. **Callback queue**: cuando una operación async termina, su callback se agrega a la cola
4. **Event Loop**: cuando el call stack está vacío, toma el siguiente callback de la cola y lo ejecuta

Las fases del Event Loop (en orden): timers → pending callbacks → idle/prepare → poll → check → close callbacks.

```javascript
console.log('1');
setTimeout(() => console.log('2'), 0);
Promise.resolve().then(() => console.log('3'));
console.log('4');
// Output: 1, 4, 3, 2
// Microtasks (Promise) se ejecutan antes que macrotasks (setTimeout)
```

---

### ¿Cuál es la diferencia entre process.nextTick() y Promise.then()?

Ambos son microtasks, pero `process.nextTick()` tiene **mayor prioridad**: se ejecuta antes de que el Event Loop pase a la siguiente fase, incluso antes de las Promises resueltas.

```javascript
process.nextTick(() => console.log('nextTick'));
Promise.resolve().then(() => console.log('Promise'));
console.log('sync');
// Output: sync → nextTick → Promise
```

Úsalo con cuidado: encadenar demasiados `nextTick` puede starve el I/O.

---

### ¿Cómo aprovechas múltiples núcleos de CPU con Node.js?

Node.js es single-threaded por defecto. Para aprovechar múltiples núcleos:

1. **Cluster module**: crea múltiples procesos hijo que comparten el mismo puerto
```javascript
import cluster from 'node:cluster';
import os from 'node:os';

if (cluster.isPrimary) {
  const workers = os.cpus().length;
  for (let i = 0; i < workers; i++) cluster.fork();
} else {
  // Cada worker ejecuta el servidor Express
  startServer();
}
```

2. **PM2**: `pm2 start app.js -i max` — gestiona el cluster automáticamente
3. **Worker Threads**: para CPU-intensive tasks (cálculos, image processing)

---

### ¿Qué es el thread pool de libuv y cuántos threads tiene?

libuv mantiene un thread pool (por defecto 4 threads) para operaciones que el OS no soporta de forma asíncrona nativa: `fs` sin `io_uring`, crypto, DNS resolución. Puedes ajustarlo con `UV_THREADPOOL_SIZE=8`.

---

## TypeScript en Backend

### ¿Cuál es la diferencia entre `interface` y `type` en TypeScript?

- `interface`: extensible con `extends`, puede ser re-declarada (declaration merging), ideal para objetos y contratos de API
- `type alias`: más flexible, permite unions (`A | B`), intersections (`A & B`), tipos primitivos, tuplas y expresiones condicionales

```typescript
// interface — se puede extender y mergear
interface User { id: string; name: string; }
interface AdminUser extends User { permissions: string[]; }

// type — más flexible para composición
type ID = string | number;
type ApiResponse<T> = { data: T; success: boolean; error?: string };
```

Regla práctica: usa `interface` para entidades de dominio y `type` para utilitarios y composición.

---

### ¿Qué son los Decorators de TypeScript y cuándo usarlos?

Los decorators son funciones que se aplican a clases, métodos, propiedades o parámetros en tiempo de compilación. Son muy comunes en frameworks como NestJS.

```typescript
function Log(target: any, key: string, descriptor: PropertyDescriptor) {
  const original = descriptor.value;
  descriptor.value = async function(...args: unknown[]) {
    console.log(`Calling ${key} with`, args);
    const result = await original.apply(this, args);
    console.log(`${key} returned`, result);
    return result;
  };
}

class UserService {
  @Log
  async findById(id: string) { /* ... */ }
}
```

Úsalos para cross-cutting concerns: logging, caché, validación, autorización.

---

## Express y APIs REST

### ¿Cómo manejas errores en Express de forma centralizada?

Con un **error handler middleware** de 4 parámetros registrado como último middleware. Todos los errores se propagan con `next(error)`:

```typescript
// Error handler de 4 parámetros
app.use((err: Error, req: Request, res: Response, next: NextFunction) => {
  const statusCode = err instanceof AppError ? err.statusCode : 500;
  res.status(statusCode).json({ message: err.message });
});
```

La clave es que Express detecta automáticamente los error handlers por la firma de 4 parámetros.

---

### ¿Cuál es la diferencia entre autenticación y autorización?

- **Autenticación**: verificar *quién eres* (JWT, sesión, API key)
- **Autorización**: verificar *qué puedes hacer* (roles, permisos, ownership)

Un usuario puede estar autenticado (401 si no lo está) pero no autorizado para una acción específica (403 si no tiene permiso).

---

### ¿Qué es CORS y cómo lo configuras correctamente?

CORS (Cross-Origin Resource Sharing) es un mecanismo de seguridad del navegador que bloquea peticiones cross-origin. El servidor debe indicar qué orígenes están permitidos:

```typescript
import cors from 'cors';

app.use(cors({
  origin: ['https://myapp.com', 'https://admin.myapp.com'],
  methods: ['GET', 'POST', 'PUT', 'DELETE'],
  allowedHeaders: ['Content-Type', 'Authorization'],
  credentials: true,      // Necesario si usas cookies
  maxAge: 86400,          // Cache del preflight por 24h
}));
```

---

### ¿Qué son los HTTP status codes más importantes para una API REST?

| Código | Significado | Cuándo usarlo |
|---|---|---|
| 200 | OK | GET/PUT/PATCH exitoso |
| 201 | Created | POST que creó un recurso |
| 204 | No Content | DELETE exitoso, o PUT sin body |
| 400 | Bad Request | Request malformado o inválido |
| 401 | Unauthorized | No autenticado |
| 403 | Forbidden | Autenticado pero sin permiso |
| 404 | Not Found | Recurso no existe |
| 409 | Conflict | Conflicto (email duplicado) |
| 422 | Unprocessable Entity | Validación falló |
| 429 | Too Many Requests | Rate limit alcanzado |
| 500 | Internal Server Error | Error inesperado del servidor |
| 503 | Service Unavailable | Servidor sobrecargado o en mantenimiento |

---

## Bases de datos y Prisma

### ¿Qué es el N+1 problem y cómo lo resuelves?

El problema N+1 ocurre cuando haces 1 query para obtener una lista y luego N queries adicionales para obtener datos relacionados de cada elemento.

```typescript
// ❌ N+1: 1 query para posts + N queries para author de cada post
const posts = await prisma.post.findMany();
for (const post of posts) {
  const author = await prisma.user.findUnique({ where: { id: post.authorId } });
}

// ✅ Solución: eager loading con include
const posts = await prisma.post.findMany({
  include: { author: { select: { id: true, name: true } } },
});
```

---

### ¿Cuándo usarías Redis en lugar de PostgreSQL?

| Caso de uso | Redis | PostgreSQL |
|---|---|---|
| Sesiones / tokens temporales | ✅ (TTL nativo) | ❌ (overhead) |
| Caché de consultas | ✅ | ❌ |
| Rate limiting (contadores) | ✅ | ❌ |
| Pub/sub en tiempo real | ✅ | ❌ |
| Datos permanentes y relacionales | ❌ | ✅ |
| Consultas complejas con JOINs | ❌ | ✅ |
| Transacciones ACID | ❌ | ✅ |

---

### ¿Cómo diseñas las migraciones de BD sin downtime?

1. **Expand-contract pattern**: 
   - Expand: añadir nueva columna nullable (sin romper)
   - Migrate data: rellenar la nueva columna
   - Contract: hacer la columna required y eliminar la vieja

2. Nunca hagas `ALTER TABLE` que bloquee en producción (PostgreSQL puede hacer `ADD COLUMN` sin bloquear si es nullable)

3. Las migraciones deben ser idempotentes (pueden correrse dos veces sin error)

---

## Seguridad

### ¿Cuáles son las vulnerabilidades más comunes en APIs Node.js?

| Vulnerabilidad | Cómo prevenirla |
|---|---|
| SQL Injection | Usar ORMs (Prisma) o queries parametrizadas |
| NoSQL Injection | Validar inputs con Zod antes de pasarlos a la BD |
| XSS | No devolver HTML con datos del usuario sin sanitizar |
| CSRF | Tokens CSRF o cookies `SameSite=Strict` |
| Brute Force | Rate limiting en endpoints de auth |
| Exposición de secretos | Variables de entorno, nunca en código |
| Dependency vulnerabilities | `npm audit` en CI, Dependabot |
| Privilege escalation | Principio de mínimo privilegio en BD y sistema |

---

### ¿Cuál es la diferencia entre JWT y sessions?

| | JWT | Sessions |
|---|---|---|
| Estado | Stateless (el servidor no almacena nada) | Stateful (session store en servidor) |
| Escalado | Fácil (cualquier instancia puede verificar) | Necesita session store compartido (Redis) |
| Revocación | Difícil (esperar expiración) | Fácil (eliminar de session store) |
| Payload | El token contiene datos del usuario | Solo el session ID |
| Tamaño | Más grande (datos en el token) | Pequeño (solo ID) |

Para microservicios y APIs públicas: JWT. Para monolitos con necesidad de revocación inmediata: sessions con Redis.

---

## Performance y escalado

### ¿Qué es el CAP theorem?

En sistemas distribuidos, un sistema solo puede garantizar 2 de estas 3 propiedades:

- **C**onsistency: todos los nodos ven los mismos datos al mismo tiempo
- **A**vailability: cada request recibe una respuesta (aunque no sea la más reciente)
- **P**artition Tolerance: el sistema funciona aunque haya partición de red

Las bases de datos relacionales como PostgreSQL prefieren CP (consistencia + tolerancia a partición). DynamoDB y Cassandra prefieren AP.

---

### ¿Cómo optimizas una API que está lenta?

Proceso sistemático:
1. **Medir primero**: identificar el cuello de botella con profiling, no asumir
2. **Revisar queries**: `EXPLAIN ANALYZE` en PostgreSQL, agregar índices faltantes
3. **Agregar caché**: Redis para datos que se leen frecuentemente
4. **Revisar N+1**: usar `include` de Prisma
5. **Paginación**: nunca cargar todos los registros
6. **Compresión**: gzip para respuestas grandes
7. **Connection pooling**: Prisma + PgBouncer para alta concurrencia

---

### ¿Qué estrategias usas para escalar una API?

**Escalado vertical** (más CPU/RAM): límite físico, costoso.

**Escalado horizontal** (más instancias): el preferido en cloud.
- Load balancer (Nginx, AWS ALB) distribuye el tráfico
- Estado compartido en Redis (sesiones, caché, colas)
- BD con read replicas para distribuir las lecturas
- CDN para contenido estático

**Estrategias adicionales**:
- Colas de trabajo (BullMQ) para tareas pesadas fuera del request cycle
- Sharding de BD para volúmenes muy altos
- Microservicios para escalar componentes independientemente

---

## Diseño de sistemas

### ¿Cómo diseñarías el sistema de notificaciones de una red social?

```
Usuario realiza acción → API → Publica evento en cola (BullMQ/Kafka)
                                        ↓
                             Notification Worker consume evento
                                        ↓
                    ┌─────────────────────────────┐
                    │                             │
              Push notification              In-app notification
              (Firebase/APNs)            (guardada en BD + WebSocket)
```

Claves:
- Procesamiento async para no bloquear la respuesta de la acción original
- Fan-out en la cola para usuarios con muchos seguidores
- Rate limiting de notificaciones por usuario (máximo X por hora)
- Preferencias de notificación respetadas

---

### ¿Cómo implementarías un sistema de rate limiting distribuido?

1. **Token bucket con Redis**: cada usuario tiene un bucket con N tokens, se recargan a tasa R
2. **Sliding window con sorted sets**: más preciso, guarda timestamps de cada request
3. **Leaky bucket**: cola FIFO con velocidad de salida fija

Implementación con Redis sliding window:
```typescript
const now = Date.now();
const windowMs = 60_000; // 1 minuto
const limit = 100;

await redis.zremrangebyscore(key, 0, now - windowMs); // Limpiar ventana
const count = await redis.zcard(key);                  // Contar requests
if (count < limit) {
  await redis.zadd(key, now, `${now}`);               // Registrar request
  // Permitir
} else {
  // Rechazar con 429
}
```

---

## Preguntas de comportamiento técnico

### ¿Cómo debuggeas un memory leak en Node.js?

1. Activar el inspector: `node --inspect server.js`
2. Chrome DevTools → `chrome://inspect` → Memory → Heap Snapshot
3. Comparar snapshots antes y después de tráfico
4. Buscar objetos que crecen sin liberarse
5. Herramientas: `clinic.js`, `heapdump`, `memwatch-next`

Causas comunes: event listeners no removidos, closures que retienen referencias, caché sin límite de tamaño, conexiones de BD no cerradas.

---

### ¿Cómo diseñas la estructura de un proyecto Node.js escalable?

```
src/
├── config/          # Variables de entorno validadas
├── lib/             # Clientes externos (prisma, redis, s3)
├── middlewares/     # Express middlewares
├── routes/          # Definición de rutas (solo routing)
├── controllers/     # Lógica de la petición HTTP (delegar al service)
├── services/        # Lógica de negocio
├── repositories/    # Acceso a datos (abstraer la BD)
├── queues/          # Definición de colas
├── workers/         # Procesadores de colas
├── jobs/            # Cron jobs
├── errors/          # Jerarquía de errores
├── utils/           # Funciones utilitarias puras
└── types/           # Tipos TypeScript compartidos
```

---

## Resumen de puntos clave

| Tema | Lo más importante |
|---|---|
| Event Loop | Microtasks (Promise) antes de macrotasks (setTimeout) |
| Concurrencia | Cluster para múltiples CPUs, Worker Threads para CPU-bound |
| Errores | Error handler de 4 parámetros al final de Express |
| Seguridad | JWT cortos + refresh tokens rotativos, rate limiting en auth |
| Performance | Caché Redis, índices en BD, evitar N+1, paginación |
| Escalado | Stateless + Redis compartido + colas para async |
| Testing | Jest + Supertest, separar createApp() de listen() |
| Docker | Multi-stage build, usuario no-root, health check |
