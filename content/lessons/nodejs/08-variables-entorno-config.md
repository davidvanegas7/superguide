# Variables de entorno y configuración

## El problema de la configuración hardcodeada

```typescript
// ❌ Nunca hagas esto
const db = new PrismaClient({
  datasourceUrl: 'postgresql://user:password123@localhost:5432/mydb',
});

const jwt = sign(payload, 'mi-secreto-muy-inseguro');
```

Problemas:
- Secretos expuestos en el repositorio
- Configuración diferente por entorno (dev/test/prod) imposible de manejar
- Escalar o rotar secretos requiere cambios de código

La solución: **variables de entorno** + **validación al inicio**.

---

## dotenv: cargar archivos `.env`

```bash
pnpm add dotenv
```

```
# .env (NUNCA commitear al repositorio)
NODE_ENV=development
PORT=3000
DATABASE_URL=postgresql://sguser:password@localhost:5432/myapp_dev
JWT_SECRET=super-secret-key-with-at-least-32-characters-here
JWT_EXPIRES=7d
LOG_LEVEL=debug
```

```
# .env.example (SÍ commitear — sirve como documentación)
NODE_ENV=development
PORT=3000
DATABASE_URL=postgresql://USER:PASSWORD@HOST:PORT/DB_NAME
JWT_SECRET=   # mínimo 32 caracteres
JWT_EXPIRES=7d
LOG_LEVEL=info
```

```typescript
// src/server.ts — cargar dotenv LO ANTES POSIBLE
import 'dotenv/config'; // shorthand: carga y aplica .env automáticamente

// O más explícito:
import dotenv from 'dotenv';
dotenv.config(); // carga .env del directorio actual
dotenv.config({ path: '.env.local', override: true }); // override con overrides locales
```

---

## Patrón: objeto de configuración centralizado

En lugar de acceder a `process.env` directamente en todo el código, centraliza la configuración en un solo módulo:

```typescript
// src/config/env.ts
import 'dotenv/config';
import { z } from 'zod';

const envSchema = z.object({
  // App
  NODE_ENV: z.enum(['development', 'test', 'production']).default('development'),
  PORT:     z.coerce.number().int().positive().default(3000),

  // Base de datos
  DATABASE_URL: z.string().url(),

  // JWT
  JWT_SECRET:          z.string().min(32),
  JWT_EXPIRES_IN:      z.string().default('15m'),  // access token corto
  JWT_REFRESH_EXPIRES: z.string().default('7d'),   // refresh token largo

  // Logging
  LOG_LEVEL: z.enum(['trace','debug','info','warn','error','fatal']).default('info'),

  // Redis (opcional)
  REDIS_URL: z.string().url().optional(),

  // Email (opcional)
  SMTP_HOST:     z.string().optional(),
  SMTP_PORT:     z.coerce.number().optional(),
  SMTP_USER:     z.string().optional(),
  SMTP_PASSWORD: z.string().optional(),
  EMAIL_FROM:    z.string().email().optional(),

  // Almacenamiento
  UPLOAD_MAX_SIZE_MB: z.coerce.number().positive().default(10),
  UPLOAD_DIR:         z.string().default('./storage/uploads'),
});

// Validar al inicio — si falla, la app no arranca
const parsed = envSchema.safeParse(process.env);

if (!parsed.success) {
  console.error('\n❌ Error en variables de entorno:\n');
  parsed.error.issues.forEach(({ path, message }) => {
    console.error(`  • ${path.join('.')}: ${message}`);
  });
  console.error('\nRevisa tu archivo .env\n');
  process.exit(1);
}

export const env = parsed.data;
```

```typescript
// src/config/index.ts — objeto de configuración con estructura semántica
import { env } from './env.js';

export const config = {
  app: {
    env:  env.NODE_ENV,
    port: env.PORT,
    isDev:  env.NODE_ENV === 'development',
    isProd: env.NODE_ENV === 'production',
    isTest: env.NODE_ENV === 'test',
  },
  db: {
    url: env.DATABASE_URL,
  },
  jwt: {
    secret:         env.JWT_SECRET,
    expiresIn:      env.JWT_EXPIRES_IN,
    refreshExpires: env.JWT_REFRESH_EXPIRES,
  },
  log: {
    level: env.LOG_LEVEL,
  },
  redis: {
    url: env.REDIS_URL,
  },
  email: {
    host:     env.SMTP_HOST,
    port:     env.SMTP_PORT,
    user:     env.SMTP_USER,
    password: env.SMTP_PASSWORD,
    from:     env.EMAIL_FROM,
  },
  upload: {
    maxSizeMB: env.UPLOAD_MAX_SIZE_MB,
    dir:       env.UPLOAD_DIR,
  },
} as const;

// Uso en cualquier archivo:
// import { config } from '../config/index.js';
// const token = sign(payload, config.jwt.secret, { expiresIn: config.jwt.expiresIn });
```

---

## Múltiples entornos

```bash
# Archivos por entorno (cargar el correspondiente según NODE_ENV)
.env                 # valores base / desarrollo
.env.local           # overrides locales (no commitear)
.env.test            # configuración para tests
.env.production      # NO usar — los secretos de prod van en el servidor, no en archivos
```

```typescript
// src/config/env.ts — cargar el archivo correcto según el entorno
import { config as dotenvConfig } from 'dotenv';
import { resolve } from 'node:path';

const nodeEnv = process.env.NODE_ENV ?? 'development';

// Orden de prioridad: .env.local > .env.[NODE_ENV] > .env
dotenvConfig({ path: resolve(process.cwd(), '.env') });
dotenvConfig({ path: resolve(process.cwd(), `.env.${nodeEnv}`), override: true });
dotenvConfig({ path: resolve(process.cwd(), '.env.local'), override: true });
```

---

## Variables de entorno en testing

```typescript
// tests/setup.ts — configuración global para Jest/Vitest
process.env.NODE_ENV    = 'test';
process.env.DATABASE_URL = 'postgresql://test:test@localhost:5432/myapp_test';
process.env.JWT_SECRET  = 'test-secret-that-is-long-enough-to-pass-validation';
process.env.LOG_LEVEL   = 'silent'; // silenciar logs durante tests
```

```json
// package.json
{
  "scripts": {
    "test": "NODE_ENV=test jest --forceExit",
    "test:watch": "NODE_ENV=test jest --watch"
  }
}
```

---

## Secrets en producción

En producción **nunca** uses archivos `.env`. Usa el sistema de secretos de tu plataforma:

```bash
# Heroku / Railway / Render
heroku config:set DATABASE_URL=postgresql://...
heroku config:set JWT_SECRET=...

# Docker / docker-compose
docker run -e DATABASE_URL="..." -e JWT_SECRET="..." myapp

# Docker Compose
services:
  api:
    environment:
      DATABASE_URL: ${DATABASE_URL}
      JWT_SECRET: ${JWT_SECRET}

# Kubernetes (secrets)
kubectl create secret generic app-secrets \
  --from-literal=DATABASE_URL="..." \
  --from-literal=JWT_SECRET="..."

# AWS Parameter Store / Secrets Manager, GCP Secret Manager, Vault, etc.
```

---

## `.gitignore` — qué nunca commitear

```gitignore
# Variables de entorno con secretos
.env
.env.local
.env.*.local

# Solo commitear el ejemplo
# .env.example  ← mantener en el repo
```

---

## Resumen

| Paso | Qué hacer |
|---|---|
| 1. Definir schema | `z.object({...})` con todas las variables requeridas |
| 2. Validar al inicio | `safeParse(process.env)` → `process.exit(1)` si falla |
| 3. Exportar objeto tipado | `export const env = parsed.data` |
| 4. Crear `config/index.ts` | Objeto semántico agrupado por dominio |
| 5. Nunca `process.env` directo | Siempre importar desde `config/` |
| 6. Documentar con `.env.example` | Commitear el ejemplo, nunca los secretos reales |

En la siguiente lección conectamos todo con **Prisma ORM**: setup, migraciones y operaciones CRUD completas.
