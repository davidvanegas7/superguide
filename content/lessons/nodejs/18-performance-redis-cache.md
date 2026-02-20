# Performance: caching con Redis y rate limiting

## ¿Por qué importa la performance en APIs?

Una API lenta no solo frustra a los usuarios, sino que aumenta costos de infraestructura. Las dos técnicas más impactantes para mejorar el rendimiento son:

1. **Caching**: evitar hacer el mismo trabajo dos veces almacenando resultados
2. **Rate limiting**: proteger la API de abuso y garantizar disponibilidad para todos

---

## Redis como capa de caché

Redis es una base de datos en memoria de estructura clave-valor. Con latencias de <1ms, es ideal para almacenar resultados de consultas costosas.

```bash
npm install ioredis
```

```typescript
// src/lib/redis.ts
import IORedis from 'ioredis';
import { env } from '../config/env';
import { logger } from './logger';

export const redis = new IORedis(env.REDIS_URL ?? 'redis://localhost:6379', {
  maxRetriesPerRequest: 3,
  enableReadyCheck: true,
  lazyConnect: true,
});

redis.on('connect', () => logger.info('Redis connected'));
redis.on('error', (err) => logger.error({ err }, 'Redis error'));
```

---

## Cache-aside pattern

El patrón más común: primero verificar el caché, si no hay, consultar la BD y guardar en caché.

```typescript
// src/lib/cache.ts
import { redis } from './redis';

export class CacheService {
  /**
   * getOrSet: si la clave existe en caché, retorna su valor.
   * Si no, ejecuta fn(), almacena el resultado y lo retorna.
   */
  async getOrSet<T>(
    key: string,
    fn: () => Promise<T>,
    ttlSeconds = 300
  ): Promise<T> {
    const cached = await redis.get(key);
    if (cached !== null) {
      return JSON.parse(cached) as T;
    }

    const result = await fn();
    await redis.set(key, JSON.stringify(result), 'EX', ttlSeconds);
    return result;
  }

  async invalidate(key: string): Promise<void> {
    await redis.del(key);
  }

  async invalidatePattern(pattern: string): Promise<void> {
    const keys = await redis.keys(pattern);
    if (keys.length > 0) {
      await redis.del(...keys);
    }
  }

  async set<T>(key: string, value: T, ttlSeconds = 300): Promise<void> {
    await redis.set(key, JSON.stringify(value), 'EX', ttlSeconds);
  }

  async get<T>(key: string): Promise<T | null> {
    const val = await redis.get(key);
    return val ? JSON.parse(val) : null;
  }
}

export const cache = new CacheService();
```

---

## Aplicar caché en servicios

```typescript
// src/services/ArticleService.ts
import { prisma } from '../lib/prisma';
import { cache } from '../lib/cache';

export class ArticleService {
  async findById(id: string) {
    return cache.getOrSet(
      `article:${id}`,
      () => prisma.article.findUniqueOrThrow({ where: { id } }),
      600 // 10 minutos
    );
  }

  async findAll(page = 1, limit = 20) {
    return cache.getOrSet(
      `articles:page:${page}:limit:${limit}`,
      () => prisma.article.findMany({
        skip: (page - 1) * limit,
        take: limit,
        orderBy: { createdAt: 'desc' },
      }),
      120 // 2 minutos (lista cambia más frecuente)
    );
  }

  async update(id: string, data: Partial<Article>) {
    const updated = await prisma.article.update({ where: { id }, data });
    // Invalidar caché del artículo y de todas las listas
    await cache.invalidate(`article:${id}`);
    await cache.invalidatePattern('articles:page:*');
    return updated;
  }

  async delete(id: string) {
    await prisma.article.delete({ where: { id } });
    await cache.invalidate(`article:${id}`);
    await cache.invalidatePattern('articles:page:*');
  }
}
```

---

## Middleware de caché para rutas GET

```typescript
// src/middlewares/cacheMiddleware.ts
import { Request, Response, NextFunction } from 'express';
import { redis } from '../lib/redis';
import { logger } from '../lib/logger';

export function cacheMiddleware(ttlSeconds = 60) {
  return async (req: Request, res: Response, next: NextFunction) => {
    if (req.method !== 'GET') return next();

    const key = `http:${req.originalUrl}`;

    try {
      const cached = await redis.get(key);
      if (cached) {
        res.setHeader('X-Cache', 'HIT');
        res.setHeader('Content-Type', 'application/json');
        res.send(cached);
        return;
      }
    } catch (err) {
      logger.warn({ err }, 'Cache read failed, proceeding without cache');
    }

    // Interceptar res.json para capturar la respuesta y guardarla
    const originalJson = res.json.bind(res);
    res.json = (body: unknown) => {
      if (res.statusCode === 200) {
        redis.set(key, JSON.stringify(body), 'EX', ttlSeconds).catch(() => {});
      }
      res.setHeader('X-Cache', 'MISS');
      return originalJson(body);
    };

    next();
  };
}
```

```typescript
// Uso en rutas
router.get('/articles', cacheMiddleware(120), getArticles);
router.get('/articles/:id', cacheMiddleware(600), getArticleById);
```

---

## Rate limiting con express-rate-limit

```bash
npm install express-rate-limit rate-limit-redis
```

### Rate limiter básico (en memoria)

```typescript
// src/middlewares/rateLimiter.ts
import rateLimit from 'express-rate-limit';

// Límite global: 100 requests por 15 minutos por IP
export const globalRateLimiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  limit: 100,
  standardHeaders: 'draft-7', // Incluir headers RateLimit-*
  legacyHeaders: false,
  message: {
    status: 'error',
    message: 'Demasiadas peticiones, intenta más tarde',
  },
});

// Límite estricto para login (prevenir brute force)
export const authRateLimiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  limit: 10, // Solo 10 intentos de login por 15 min
  message: {
    status: 'error',
    message: 'Demasiados intentos de login, espera 15 minutos',
  },
});
```

### Rate limiter con Redis (sliding window, multi-instancia)

```typescript
// src/middlewares/rateLimiterRedis.ts
import rateLimit from 'express-rate-limit';
import { RedisStore } from 'rate-limit-redis';
import { redis } from '../lib/redis';

export const apiRateLimiter = rateLimit({
  windowMs: 60 * 1000, // 1 minuto
  limit: 60,           // 60 requests por minuto
  standardHeaders: true,
  legacyHeaders: false,
  // Usar Redis para compartir estado entre múltiples instancias del servidor
  store: new RedisStore({
    sendCommand: (...args: string[]) => redis.call(...args),
  }),
  // Personalizar la clave: por usuario autenticado o por IP
  keyGenerator: (req) => {
    return req.user?.sub ?? req.ip ?? 'anonymous';
  },
});
```

---

## Rate limiting avanzado: sliding window con Redis

Implementación manual para control total:

```typescript
// src/lib/slidingWindowRateLimiter.ts
import { redis } from './redis';

interface RateLimitResult {
  allowed: boolean;
  remaining: number;
  reset: number; // timestamp Unix
}

export async function slidingWindowRateLimit(
  identifier: string,
  limit: number,
  windowSeconds: number
): Promise<RateLimitResult> {
  const now = Date.now();
  const windowStart = now - windowSeconds * 1000;
  const key = `ratelimit:${identifier}`;

  // Usar un pipeline para atomicidad
  const pipeline = redis.pipeline();
  pipeline.zremrangebyscore(key, 0, windowStart);         // Eliminar requests fuera de la ventana
  pipeline.zadd(key, now, `${now}-${Math.random()}`);     // Agregar request actual
  pipeline.zcard(key);                                    // Contar requests en la ventana
  pipeline.expire(key, windowSeconds);                    // TTL del key

  const results = await pipeline.exec();
  const count = results?.[2]?.[1] as number;

  return {
    allowed: count <= limit,
    remaining: Math.max(0, limit - count),
    reset: Math.floor((now + windowSeconds * 1000) / 1000),
  };
}
```

---

## Compresión de respuestas

```bash
npm install compression
npm install -D @types/compression
```

```typescript
// src/app.ts
import compression from 'compression';

// Comprimir respuestas mayores a 1KB con gzip
app.use(compression({
  level: 6, // Nivel de compresión (0-9)
  threshold: 1024, // Comprimir solo si > 1KB
  filter: (req, res) => {
    // No comprimir si el cliente lo indica explícitamente
    if (req.headers['x-no-compression']) return false;
    return compression.filter(req, res);
  },
}));
```

---

## Connection pooling con Prisma

```typescript
// En DATABASE_URL: añadir parámetros de pool
// postgresql://user:pass@host:5432/db?connection_limit=10&pool_timeout=20

// O configurar en Prisma:
const prisma = new PrismaClient({
  datasourceUrl: process.env.DATABASE_URL,
  log: ['query', 'error'],
});
```

---

## Estrategias de invalidación de caché

| Estrategia | Descripción | Cuándo usarla |
|---|---|---|
| TTL (Time-To-Live) | Expirar automáticamente | Datos que cambian poco (artículos, productos) |
| Write-through | Actualizar caché al escribir en BD | Datos de perfil de usuario |
| Write-behind | Escribir en caché primero, BD después | Alta frecuencia de escritura |
| Event-based | Invalidar al ocurrir un evento | Con sistemas de mensajería (pub/sub) |
| Cache-aside | Cargar bajo demanda | Patrón general recomendado |

---

## Resumen

- Redis con `ioredis` para caché de alta velocidad
- Patrón cache-aside con `getOrSet()` y TTL configurable
- Invalidación por clave exacta o patrón glob
- Rate limiting en memoria para desarrollo, Redis para producción multi-instancia
- Sliding window manual con Redis sorted sets para máxima precisión
- Compresión gzip con `compression` para reducir bytes transferidos
