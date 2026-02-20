# Manejo global de errores y logging con Pino

## El problema con el manejo de errores ad-hoc

Cuando cada ruta maneja sus propios errores con `try/catch` y `res.status(500).json(...)`, obtienes:
- Respuestas de error inconsistentes
- Sin trazabilidad entre requests
- Logs mezclados e ilegibles
- Información sensible filtrada accidentalmente en producción

La solución es un **sistema centralizado**: jerarquía de errores + error handler global + logger estructurado.

---

## Jerarquía de errores personalizada

```typescript
// src/errors/AppError.ts
export class AppError extends Error {
  public readonly statusCode: number;
  public readonly isOperational: boolean;

  constructor(
    message: string,
    statusCode = 500,
    isOperational = true
  ) {
    super(message);
    this.statusCode = statusCode;
    this.isOperational = isOperational;
    // Mantener el nombre correcto en el stack trace
    Object.setPrototypeOf(this, new.target.prototype);
    Error.captureStackTrace(this, this.constructor);
  }
}
```

```typescript
// src/errors/NotFoundError.ts
import { AppError } from './AppError';

export class NotFoundError extends AppError {
  constructor(resource = 'Recurso') {
    super(`${resource} no encontrado`, 404);
  }
}
```

```typescript
// src/errors/ValidationError.ts
import { AppError } from './AppError';

export class ValidationError extends AppError {
  public readonly fields: Record<string, string[]>;

  constructor(fields: Record<string, string[]>) {
    super('Error de validación', 422);
    this.fields = fields;
  }
}
```

```typescript
// src/errors/ConflictError.ts
import { AppError } from './AppError';

export class ConflictError extends AppError {
  constructor(message = 'Conflicto de datos') {
    super(message, 409);
  }
}
```

```typescript
// src/errors/UnauthorizedError.ts
import { AppError } from './AppError';

export class UnauthorizedError extends AppError {
  constructor(message = 'No autorizado') {
    super(message, 401);
  }
}
```

---

## Error handler global de Express

Este middleware captura **todos** los errores que pasen por `next(err)`:

```typescript
// src/middlewares/errorHandler.ts
import { Request, Response, NextFunction } from 'express';
import { ZodError } from 'zod';
import { Prisma } from '@prisma/client';
import { AppError } from '../errors/AppError';
import { ValidationError } from '../errors/ValidationError';
import { logger } from '../lib/logger';

interface ErrorResponse {
  status: 'error';
  message: string;
  statusCode: number;
  fields?: Record<string, string[]>;
  requestId?: string;
}

export function errorHandler(
  err: Error,
  req: Request,
  res: Response,
  _next: NextFunction
): void {
  const requestId = req.headers['x-request-id'] as string;

  // 1. Errores de Zod → ValidationError
  if (err instanceof ZodError) {
    const fields: Record<string, string[]> = {};
    err.errors.forEach(e => {
      const key = e.path.join('.');
      fields[key] = fields[key] ?? [];
      fields[key].push(e.message);
    });

    const response: ErrorResponse = {
      status: 'error',
      message: 'Error de validación',
      statusCode: 422,
      fields,
      requestId,
    };
    logger.warn({ requestId, fields }, 'Validation error');
    res.status(422).json(response);
    return;
  }

  // 2. Errores de Prisma
  if (err instanceof Prisma.PrismaClientKnownRequestError) {
    if (err.code === 'P2002') {
      // Unique constraint violated
      res.status(409).json({
        status: 'error',
        message: 'Ya existe un registro con esos datos',
        statusCode: 409,
        requestId,
      });
      return;
    }
    if (err.code === 'P2025') {
      // Record not found
      res.status(404).json({
        status: 'error',
        message: 'Registro no encontrado',
        statusCode: 404,
        requestId,
      });
      return;
    }
  }

  // 3. Errores operacionales de la app
  if (err instanceof AppError && err.isOperational) {
    const response: ErrorResponse = {
      status: 'error',
      message: err.message,
      statusCode: err.statusCode,
      requestId,
    };
    if (err instanceof ValidationError) {
      response.fields = err.fields;
    }

    logger.warn({ requestId, statusCode: err.statusCode, err: err.message }, 'Operational error');
    res.status(err.statusCode).json(response);
    return;
  }

  // 4. Errores inesperados (bugs)
  logger.error({ requestId, err, stack: err.stack }, 'Unexpected error');

  const statusCode = 500;
  res.status(statusCode).json({
    status: 'error',
    message: process.env.NODE_ENV === 'production'
      ? 'Error interno del servidor'
      : err.message,
    statusCode,
    requestId,
  });
}
```

Registrarlo como el **último middleware** en `app.ts`:

```typescript
// Al final, después de todas las rutas
app.use(errorHandler);
```

---

## Logging estructurado con Pino

Pino es el logger más rápido para Node.js. Escribe JSON por defecto, lo que permite integrarlo con sistemas como Datadog, Logtail o CloudWatch.

```bash
npm install pino pino-http pino-pretty
npm install -D @types/pino-http
```

```typescript
// src/lib/logger.ts
import pino from 'pino';
import { env } from '../config/env';

export const logger = pino({
  level: env.LOG_LEVEL ?? 'info',
  // En desarrollo: formato legible; en producción: JSON puro
  transport:
    env.NODE_ENV === 'development'
      ? { target: 'pino-pretty', options: { colorize: true } }
      : undefined,
  base: {
    service: 'api',
    env: env.NODE_ENV,
  },
  // Redactar campos sensibles
  redact: {
    paths: ['*.password', '*.passwordHash', '*.token', 'req.headers.authorization'],
    censor: '[REDACTED]',
  },
  timestamp: pino.stdTimeFunctions.isoTime,
});
```

### Child loggers con contexto

Un child logger hereda el contexto del padre y añade campos extra. Ideal para inyectar el `requestId`:

```typescript
// src/middlewares/requestLogger.ts
import { Request, Response, NextFunction } from 'express';
import { randomUUID } from 'node:crypto';
import { logger } from '../lib/logger';

export function requestLogger(req: Request, res: Response, next: NextFunction): void {
  const requestId = (req.headers['x-request-id'] as string) ?? randomUUID();
  req.headers['x-request-id'] = requestId;

  // Child logger con requestId en todos los logs de esta request
  const reqLogger = logger.child({ requestId, method: req.method, url: req.url });
  req.log = reqLogger; // Adjuntar al request para usar en controllers

  const start = Date.now();
  res.on('finish', () => {
    reqLogger.info(
      { statusCode: res.statusCode, duration: `${Date.now() - start}ms` },
      'Request completed'
    );
  });

  next();
}
```

Extender el tipo de `Request` para el logger:

```typescript
// src/types/express.d.ts
import { Logger } from 'pino';

declare global {
  namespace Express {
    interface Request {
      log: Logger;
    }
  }
}
```

### Uso en controladores

```typescript
// En cualquier controlador
export async function getUser(req: Request, res: Response, next: NextFunction) {
  try {
    req.log.info({ userId: req.params.id }, 'Fetching user');
    const user = await userService.findById(req.params.id);
    req.log.info({ userId: user.id }, 'User found');
    res.json(user);
  } catch (err) {
    next(err);
  }
}
```

---

## Niveles de log y cuándo usarlos

| Nivel | Uso |
|---|---|
| `trace` | Depuración muy granular (desactivado en producción) |
| `debug` | Información de depuración durante desarrollo |
| `info` | Eventos normales del sistema (peticiones, operaciones exitosas) |
| `warn` | Situaciones anómalas pero recuperables (errores 4xx, validaciones) |
| `error` | Errores inesperados que requieren atención (errores 5xx) |
| `fatal` | El proceso va a terminar |

---

## Capturar excepciones no manejadas

```typescript
// src/server.ts

// Capturar rechazos de promesas no manejados
process.on('unhandledRejection', (reason: unknown) => {
  logger.fatal({ reason }, 'Unhandled Promise Rejection — shutting down');
  process.exit(1);
});

// Capturar excepciones síncronas no atrapadas
process.on('uncaughtException', (err: Error) => {
  logger.fatal({ err }, 'Uncaught Exception — shutting down');
  process.exit(1);
});
```

---

## Pino con pino-http (alternativa integrada)

```typescript
import pinoHttp from 'pino-http';
import { logger } from '../lib/logger';

export const httpLogger = pinoHttp({
  logger,
  customLogLevel: (_req, res) => {
    if (res.statusCode >= 500) return 'error';
    if (res.statusCode >= 400) return 'warn';
    return 'info';
  },
  customSuccessMessage: (req, res) =>
    `${req.method} ${req.url} → ${res.statusCode}`,
  redact: ['req.headers.authorization'],
});

// En app.ts:
app.use(httpLogger);
```

---

## Resumen

- Jerarquía: `AppError` → `NotFoundError`, `ValidationError`, `ConflictError`, `UnauthorizedError`
- Error handler global como último middleware en Express
- Pino para logging estructurado con transport pretty en desarrollo
- Child loggers con `requestId` para correlacionar logs de una misma request
- Redactar campos sensibles con `redact` de Pino
- `process.on('unhandledRejection')` y `uncaughtException` como red de seguridad
