# Middlewares: CORS, logging y manejo de errores

## ¿Qué es un middleware?

Un middleware en Express es una función que recibe `(req, res, next)` y se ejecuta en el orden en que fue registrada. Cada middleware puede:

- Modificar `req` o `res`
- Terminar el ciclo de petición (llamando a `res.send()` etc.)
- Pasar al siguiente middleware (llamando a `next()`)
- Pasar un error al manejador de errores (llamando a `next(error)`)

```
Petición → MW1 → MW2 → MW3 → Controlador → Respuesta
                               ↓ error
                           ErrorHandler
```

```typescript
import { Request, Response, NextFunction } from 'express';

// Estructura básica
function miMiddleware(req: Request, res: Response, next: NextFunction): void {
  console.log(`${req.method} ${req.path}`);
  next(); // SIEMPRE llamar a next() o enviar respuesta
}
```

---

## CORS

Cross-Origin Resource Sharing: permite que tu API reciba peticiones desde dominios distintos (necesario cuando el frontend está en otro origen).

```bash
pnpm add cors
pnpm add -D @types/cors
```

```typescript
import cors from 'cors';

// Configuración básica — permite todos los orígenes (solo desarrollo)
app.use(cors());

// Configuración para producción
app.use(cors({
  origin: (origin, callback) => {
    const allowed = [
      'https://mi-frontend.com',
      'https://admin.mi-frontend.com',
      process.env.NODE_ENV === 'development' ? 'http://localhost:4200' : '',
    ].filter(Boolean);

    if (!origin || allowed.includes(origin)) {
      callback(null, true);
    } else {
      callback(new Error(`Origen ${origin} no permitido por CORS`));
    }
  },
  methods:          ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
  allowedHeaders:   ['Content-Type', 'Authorization', 'X-Request-ID'],
  exposedHeaders:   ['X-Total-Count', 'X-Page'],
  credentials:      true,   // permite cookies / Authorization header
  maxAge:           86400,  // cache del preflight 24h
}));
```

---

## Logging con Pino HTTP

`pino` es el logger más rápido para Node.js. `pino-http` lo integra como middleware Express:

```bash
pnpm add pino pino-http
pnpm add -D pino-pretty  # solo en desarrollo
```

```typescript
// src/middleware/logger.ts
import pinoHttp from 'pino-http';
import pino from 'pino';

// Logger base — reutilizable en toda la app
export const logger = pino({
  level: process.env.LOG_LEVEL ?? 'info',
  transport: process.env.NODE_ENV === 'development'
    ? { target: 'pino-pretty', options: { colorize: true } }
    : undefined, // en producción: JSON puro (para ingestar en Datadog/Loki)
});

// Middleware HTTP
export const httpLogger = pinoHttp({
  logger,
  // No loguear health checks
  autoLogging: {
    ignore: req => req.url === '/health',
  },
  // Personalizar el mensaje de log
  customSuccessMessage: (req, res) =>
    `${req.method} ${req.url} → ${res.statusCode}`,
  customErrorMessage: (req, res, err) =>
    `${req.method} ${req.url} → ${res.statusCode} — ${err.message}`,
  // Qué campos incluir en el log
  serializers: {
    req: (req) => ({
      method: req.method,
      url:    req.url,
      id:     req.id,
    }),
    res: (res) => ({
      statusCode: res.statusCode,
    }),
  },
});
```

```typescript
// src/app.ts
import { httpLogger, logger } from './middleware/logger.js';

app.use(httpLogger);

// Usar el logger en controladores
export { logger };

// En cualquier archivo:
import { logger } from '../app.js';
logger.info({ userId: 1 }, 'Usuario creado');
logger.error({ err }, 'Error inesperado');
```

---

## Añadir Request ID

Identificar cada petición con un ID único facilita el debug:

```typescript
// src/middleware/requestId.ts
import { Request, Response, NextFunction } from 'express';
import { randomUUID } from 'node:crypto';

export function requestId(req: Request, _res: Response, next: NextFunction): void {
  // Propaga el ID si viene del cliente (útil en microservicios), o genera uno nuevo
  req.requestId = (req.headers['x-request-id'] as string) ?? randomUUID();
  next();
}

// En app.ts — ANTES del httpLogger para que lo incluya en los logs
app.use(requestId);
app.use(httpLogger);
```

---

## Rate Limiting

Protege la API de abuso y ataques de fuerza bruta:

```bash
pnpm add express-rate-limit
```

```typescript
// src/middleware/rateLimiter.ts
import rateLimit from 'express-rate-limit';

// Límite general para toda la API
export const apiLimiter = rateLimit({
  windowMs: 15 * 60 * 1000, // ventana de 15 minutos
  max:      100,             // máximo 100 peticiones por ventana por IP
  standardHeaders: 'draft-7', // cabeceras RateLimit-*
  legacyHeaders:  false,
  message: { error: 'Demasiadas peticiones, intenta de nuevo en 15 minutos' },
});

// Límite más estricto para endpoints sensibles (login, registro)
export const authLimiter = rateLimit({
  windowMs: 60 * 60 * 1000, // 1 hora
  max:      10,              // 10 intentos por hora
  message:  { error: 'Demasiados intentos de autenticación' },
  skipSuccessfulRequests: true, // no contar intentos exitosos
});
```

```typescript
// Aplicar en app.ts o en rutas específicas
import { apiLimiter, authLimiter } from './middleware/rateLimiter.js';

app.use('/api/', apiLimiter);                   // toda la API
app.use('/api/v1/auth/login',   authLimiter);   // solo login
app.use('/api/v1/auth/register', authLimiter);  // y registro
```

---

## Middleware de autenticación

```typescript
// src/middleware/authenticate.ts
import { Request, Response, NextFunction } from 'express';
import jwt from 'jsonwebtoken';

interface TokenPayload {
  userId: number;
  role:   string;
}

export async function authenticate(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  const authHeader = req.headers.authorization;

  if (!authHeader?.startsWith('Bearer ')) {
    res.status(401).json({ error: 'Token no proporcionado' });
    return;
  }

  const token = authHeader.slice(7); // quitar "Bearer "

  try {
    const payload = jwt.verify(token, process.env.JWT_SECRET!) as TokenPayload;
    req.user = { id: payload.userId, role: payload.role } as any;
    next();
  } catch (err) {
    if (err instanceof jwt.TokenExpiredError) {
      res.status(401).json({ error: 'Token expirado' });
    } else {
      res.status(401).json({ error: 'Token inválido' });
    }
  }
}

// Middleware de autorización — debe ir DESPUÉS de authenticate
export function authorize(...roles: string[]) {
  return (req: Request, res: Response, next: NextFunction): void => {
    if (!req.user) {
      res.status(401).json({ error: 'No autenticado' });
      return;
    }
    if (!roles.includes(req.user.role as string)) {
      res.status(403).json({ error: 'Sin permiso para esta operación' });
      return;
    }
    next();
  };
}

// Uso en rutas:
// router.delete('/:id', authenticate, authorize('admin'), deleteUser);
```

---

## Manejo global de errores (completo)

```typescript
// src/middleware/errorHandler.ts
import { Request, Response, NextFunction, ErrorRequestHandler } from 'express';
import { ZodError } from 'zod';
import { logger } from '../middleware/logger.js';

export class AppError extends Error {
  constructor(
    public readonly statusCode: number,
    message: string,
    public readonly code?: string,
    public readonly details?: unknown
  ) {
    super(message);
    this.name = 'AppError';
  }
}

export const errorHandler: ErrorRequestHandler = (err, req, res, _next) => {
  // Errores de validación Zod
  if (err instanceof ZodError) {
    res.status(422).json({
      error:   'Error de validación',
      details: err.errors.map(e => ({
        field:   e.path.join('.'),
        message: e.message,
      })),
    });
    return;
  }

  // Errores controlados de la aplicación
  if (err instanceof AppError) {
    logger.warn({ err, requestId: req.requestId }, err.message);
    res.status(err.statusCode).json({
      error:   err.message,
      code:    err.code,
      details: err.details,
    });
    return;
  }

  // Errores de CORS
  if (err.message?.includes('no permitido por CORS')) {
    res.status(403).json({ error: err.message });
    return;
  }

  // Error inesperado — loguear completo
  logger.error({ err, requestId: req.requestId }, 'Error no controlado');
  res.status(500).json({ error: 'Error interno del servidor' });
};

// Ruta catch-all para 404
export function notFoundHandler(req: Request, res: Response): void {
  res.status(404).json({
    error: `Ruta ${req.method} ${req.path} no encontrada`,
  });
}
```

```typescript
// src/app.ts — orden IMPORTANTE
app.use(requestId);
app.use(httpLogger);
app.use(cors(corsOptions));
app.use(express.json());
app.use('/api/', apiLimiter);

// Rutas
app.use('/api/v1/users', usersRouter);

// Handlers al final
app.use(notFoundHandler);  // 404
app.use(errorHandler);     // 500+
```

---

## Middleware de validación de body

```typescript
// src/middleware/validate.ts
import { Request, Response, NextFunction } from 'express';
import { ZodSchema } from 'zod';

export function validate(schema: ZodSchema) {
  return (req: Request, res: Response, next: NextFunction): void => {
    const result = schema.safeParse(req.body);

    if (!result.success) {
      res.status(422).json({
        error:   'Datos inválidos',
        details: result.error.errors.map(e => ({
          field:   e.path.join('.'),
          message: e.message,
        })),
      });
      return;
    }

    req.body = result.data; // reemplaza con datos transformados/sanitizados
    next();
  };
}

// Uso en router (veremos Zod en detalle en la lección siguiente):
// router.post('/', validate(createUserSchema), createUser);
```

---

## Resumen de orden recomendado

```typescript
// src/app.ts — orden óptimo de middlewares
app.use(requestId);           // 1. ID único de petición
app.use(httpLogger);          // 2. Log de la petición (ya tiene el ID)
app.use(cors(corsOptions));   // 3. CORS (antes de parsear body)
app.use(express.json());      // 4. Parsear JSON
app.use(express.urlencoded()); // 5. Parsear form data
app.use('/api/', apiLimiter); // 6. Rate limit

// Rutas de la API
app.use('/api/v1/users', usersRouter);

// Siempre al final
app.use(notFoundHandler);     // 404
app.use(errorHandler);        // errores
```

En la próxima lección diseñamos la API REST de forma correcta: recursos, verbos HTTP, códigos de estado, versionado y paginación.
