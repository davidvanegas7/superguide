# Caché: Estrategias y Patrones

La **caché** es una capa de almacenamiento temporal de alta velocidad que guarda resultados de operaciones costosas para no repetirlas. Bien implementada, puede reducir la latencia de segundos a milisegundos.

---

## Por qué usar caché

```
Sin caché:
Cliente → API → DB (consulta SQL costosa) → respuesta en 200ms

Con caché:
Cliente → API → Redis (hit) → respuesta en 2ms
                ↓ miss
               DB → Redis (store) → respuesta en 205ms
```

---

## Cache-Aside (Lazy Loading)

El patrón más común. La aplicación gestiona la caché manualmente.

```typescript
import Redis from 'ioredis';

const redis = new Redis({ host: 'localhost', port: 6379 });

class UserService {
  constructor(private userRepo: UserRepository) {}

  async findById(id: number): Promise<User | null> {
    const cacheKey = `user:${id}`;

    // 1. Buscar en caché
    const cached = await redis.get(cacheKey);
    if (cached) {
      console.log(`[CACHE HIT] ${cacheKey}`);
      return JSON.parse(cached) as User;
    }

    // 2. Cache miss: buscar en BD
    console.log(`[CACHE MISS] ${cacheKey}`);
    const user = await this.userRepo.findById(id);

    // 3. Guardar en caché (TTL: 5 minutos)
    if (user) {
      await redis.setex(cacheKey, 300, JSON.stringify(user));
    }

    return user;
  }

  async update(id: number, data: Partial<User>): Promise<User> {
    const user = await this.userRepo.update(id, data);

    // Invalida la caché al actualizar
    await redis.del(`user:${id}`);

    return user;
  }
}
```

---

## Write-Through

La escritura va **simultáneamente** a la caché y a la base de datos. La caché siempre está actualizada.

```typescript
class ProductService {
  async updatePrice(productId: number, newPriceCents: number): Promise<Product> {
    const cacheKey = `product:${productId}`;

    // Escribe en BD y caché al mismo tiempo
    const [product] = await Promise.all([
      this.productRepo.updatePrice(productId, newPriceCents),
      redis.setex(cacheKey, 3600, JSON.stringify({ id: productId, priceCents: newPriceCents })),
    ]);

    return product;
  }
}
```

---

## Write-Behind (Write-Back)

La escritura va a la caché primero; la base de datos se actualiza de forma asíncrona. Mayor rendimiento, riesgo de pérdida de datos.

```typescript
class AnalyticsService {
  private queue: Array<{ event: string; data: unknown }> = [];
  private flushInterval: NodeJS.Timeout;

  constructor() {
    // Persiste a BD cada 5 segundos
    this.flushInterval = setInterval(() => this.flush(), 5000);
  }

  track(event: string, data: unknown): void {
    // 1. Escribe en memoria inmediatamente (rápido)
    this.queue.push({ event, data });

    // 2. Actualiza contador en Redis (para tiempo real)
    redis.incr(`events:${event}:count`);
  }

  private async flush(): Promise<void> {
    if (!this.queue.length) return;
    const batch = this.queue.splice(0);  // vacía la cola
    // 3. Persiste en lote a BD (lento, pero asíncrono)
    await this.analyticsRepo.insertBatch(batch);
    console.log(`[Analytics] Flush: ${batch.length} eventos`);
  }
}
```

---

## Estrategias de invalidación

> "Hay solo dos cosas difíciles en informática: invalidar cachés y nombrar cosas" — Phil Karlton

```typescript
class ProductCacheService {
  // Estrategia 1: TTL (Time To Live) — la más simple
  async cacheProduct(product: Product, ttlSeconds = 300): Promise<void> {
    await redis.setex(`product:${product.id}`, ttlSeconds, JSON.stringify(product));
  }

  // Estrategia 2: Invalidación activa al escribir
  async invalidateProduct(productId: number): Promise<void> {
    const keys = [
      `product:${productId}`,
      `product:${productId}:related`,
      `products:category:*`,     // patrón (cuidado: KEYS es lento en producción)
    ];
    // En producción, usa tagged caches o patrones con SCAN
    await redis.del(...keys);
  }

  // Estrategia 3: Cache Tags (invalidación por grupo)
  async setCached(key: string, value: unknown, tags: string[], ttl = 300): Promise<void> {
    const pipeline = redis.pipeline();
    pipeline.setex(key, ttl, JSON.stringify(value));

    // Guarda la clave en cada tag set
    for (const tag of tags) {
      pipeline.sadd(`tag:${tag}`, key);
      pipeline.expire(`tag:${tag}`, ttl + 60);
    }
    await pipeline.exec();
  }

  async invalidateByTag(tag: string): Promise<void> {
    const keys = await redis.smembers(`tag:${tag}`);
    if (keys.length) {
      await redis.del(...keys);
    }
    await redis.del(`tag:${tag}`);
  }
}

// Uso de cache tags
await cacheService.setCached(`product:1`, product, ['products', 'category:electronics']);
await cacheService.setCached(`product:2`, product2, ['products', 'category:electronics']);

// Invalida todos los productos de una categoría a la vez
await cacheService.invalidateByTag('category:electronics');
```

---

## Memoización (caché en memoria de la app)

Para resultados de funciones puras y configuraciones que no cambian con frecuencia.

```typescript
function memoize<T extends (...args: unknown[]) => unknown>(
  fn: T,
  keyFn: (...args: Parameters<T>) => string = (...args) => JSON.stringify(args)
): T {
  const cache = new Map<string, { value: ReturnType<T>; expires: number }>();

  return function(this: unknown, ...args: Parameters<T>): ReturnType<T> {
    const key     = keyFn(...args);
    const cached  = cache.get(key);
    const now     = Date.now();

    if (cached && cached.expires > now) {
      return cached.value;
    }

    const result = fn.apply(this, args) as ReturnType<T>;
    cache.set(key, { value: result, expires: now + 60_000 });  // 1 minuto
    return result;
  } as T;
}

// Uso
const getConfig = memoize(async (env: string) => {
  console.log(`Cargando config para ${env}...`);
  return { dbUrl: process.env.DATABASE_URL };
});

await getConfig('prod');  // carga
await getConfig('prod');  // retorna del cache
```

---

## Patrones avanzados de Redis

```typescript
// Distributed Lock (mutex distribuido)
async function withLock<T>(key: string, ttlMs: number, fn: () => Promise<T>): Promise<T> {
  const lockKey  = `lock:${key}`;
  const lockId   = crypto.randomUUID();
  const acquired = await redis.set(lockKey, lockId, 'PX', ttlMs, 'NX');

  if (!acquired) throw new Error(`No se pudo adquirir lock: ${key}`);

  try {
    return await fn();
  } finally {
    // Solo libera si somos el dueño del lock (script Lua para atomicidad)
    const script = `
      if redis.call("get", KEYS[1]) == ARGV[1] then
        return redis.call("del", KEYS[1])
      else
        return 0
      end
    `;
    await redis.eval(script, 1, lockKey, lockId);
  }
}

// Uso: evita procesar la misma orden dos veces
await withLock(`order:${orderId}:process`, 30_000, async () => {
  await orderService.processPayment(orderId);
});

// Rate limiting con sliding window en Redis
async function checkRateLimit(userId: string, limit: number, windowSecs: number): Promise<boolean> {
  const key = `ratelimit:${userId}`;
  const now  = Date.now();
  const windowMs = windowSecs * 1000;

  const pipeline = redis.pipeline();
  pipeline.zremrangebyscore(key, 0, now - windowMs);  // elimina las antiguas
  pipeline.zadd(key, now, `${now}`);                  // añade la actual
  pipeline.zcard(key);                                // cuenta el total
  pipeline.expire(key, windowSecs);

  const results = await pipeline.exec();
  const count   = results?.[2]?.[1] as number;

  return count <= limit;
}
```

---

## Cuándo usar cada estrategia

| Estrategia | Cuándo usarla |
|---|---|
| **Cache-Aside** | Acceso a datos con lecturas frecuentes y escrituras moderadas |
| **Write-Through** | Cuando la consistencia entre caché y BD es crítica |
| **Write-Behind** | Eventos de alta frecuencia donde la pérdida mínima es aceptable |
| **TTL corto** | Datos que cambian frecuentemente |
| **TTL largo + invalidación activa** | Datos que cambian poco pero se leen mucho |
| **Memoización** | Cómputos costosos con los mismos argumentos en la misma request |
