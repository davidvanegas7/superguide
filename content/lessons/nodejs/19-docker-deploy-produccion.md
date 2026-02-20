# Docker y despliegue en producción

## ¿Por qué Docker?

Docker empaqueta tu aplicación con todas sus dependencias en una **imagen** portable. Esto elimina el clásico "en mi máquina funciona": si la imagen corre en tu laptop, también corre en el servidor.

Beneficios para APIs Node.js:
- Reproducibilidad garantizada entre entornos
- Aislamiento de dependencias del sistema operativo
- Despliegue consistente en cualquier proveedor cloud
- Escalado horizontal sencillo

---

## Dockerfile multi-stage

Un Dockerfile multi-stage usa múltiples `FROM`. La etapa final solo contiene lo necesario para producción, reduciendo el tamaño de la imagen de ~1GB a ~150MB:

```dockerfile
# ==================== Stage 1: Dependencies ====================
FROM node:20-alpine AS deps
WORKDIR /app

# Copiar solo los archivos de dependencias primero (para aprovechar el cache de Docker)
COPY package.json package-lock.json ./
COPY prisma ./prisma/

# Instalar TODAS las dependencias (incluyendo devDependencies para el build)
RUN npm ci

# ==================== Stage 2: Build ====================
FROM node:20-alpine AS builder
WORKDIR /app

COPY --from=deps /app/node_modules ./node_modules
COPY . .

# Generar el cliente de Prisma y compilar TypeScript
RUN npx prisma generate
RUN npm run build

# ==================== Stage 3: Production ====================
FROM node:20-alpine AS production
WORKDIR /app

# Variables de seguridad: ejecutar como usuario no-root
RUN addgroup --system --gid 1001 nodejs
RUN adduser --system --uid 1001 nodeuser

# Copiar solo lo necesario desde las etapas anteriores
COPY --from=builder --chown=nodeuser:nodejs /app/dist ./dist
COPY --from=builder --chown=nodeuser:nodejs /app/node_modules ./node_modules
COPY --from=builder --chown=nodeuser:nodejs /app/prisma ./prisma
COPY --from=builder --chown=nodeuser:nodejs /app/package.json ./

# Puerto que expone la app
EXPOSE 3000

# Health check: Docker verifica que la app esté viva
HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
  CMD wget --no-verbose --tries=1 --spider http://localhost:3000/health || exit 1

USER nodeuser

CMD ["node", "dist/server.js"]
```

---

## .dockerignore

```
node_modules
dist
.git
.gitignore
*.log
.env
.env.*
coverage
tests
*.md
.vscode
```

---

## Health check endpoint

```typescript
// src/routes/health.routes.ts
import { Router } from 'express';
import { prisma } from '../lib/prisma';
import { redis } from '../lib/redis';

const router = Router();

router.get('/health', async (_req, res) => {
  try {
    // Verificar conexión a BD
    await prisma.$queryRaw`SELECT 1`;

    // Verificar conexión a Redis
    await redis.ping();

    res.json({
      status: 'ok',
      timestamp: new Date().toISOString(),
      services: {
        database: 'ok',
        cache: 'ok',
      },
    });
  } catch (err) {
    res.status(503).json({
      status: 'error',
      message: String(err),
    });
  }
});

export default router;
```

---

## docker-compose para desarrollo local

```yaml
# docker-compose.yml
version: '3.9'

services:
  app:
    build:
      context: .
      target: production
    ports:
      - '3000:3000'
    environment:
      - NODE_ENV=production
      - DATABASE_URL=postgresql://postgres:postgres@postgres:5432/myapp
      - REDIS_URL=redis://redis:6379
      - JWT_SECRET=${JWT_SECRET}
      - JWT_REFRESH_SECRET=${JWT_REFRESH_SECRET}
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    restart: unless-stopped

  postgres:
    image: postgres:16-alpine
    environment:
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: postgres
      POSTGRES_DB: myapp
    volumes:
      - postgres_data:/var/lib/postgresql/data
    ports:
      - '5432:5432'
    healthcheck:
      test: ['CMD-SHELL', 'pg_isready -U postgres']
      interval: 10s
      timeout: 5s
      retries: 5

  redis:
    image: redis:7-alpine
    command: redis-server --appendonly yes
    volumes:
      - redis_data:/data
    ports:
      - '6379:6379'
    healthcheck:
      test: ['CMD', 'redis-cli', 'ping']
      interval: 10s
      timeout: 5s
      retries: 5

volumes:
  postgres_data:
  redis_data:
```

```yaml
# docker-compose.dev.yml — Sobreescritura para desarrollo
version: '3.9'

services:
  app:
    build:
      context: .
      target: deps         # Solo instalar dependencias
    command: npm run dev   # ts-node-dev con hot reload
    volumes:
      - .:/app             # Montar código fuente para live reload
      - /app/node_modules  # Excluir node_modules del mount
    environment:
      - NODE_ENV=development
```

```bash
# Desarrollo con hot reload
docker compose -f docker-compose.yml -f docker-compose.dev.yml up

# Producción
docker compose up --build
```

---

## Variables de entorno en Docker

**Nunca** incluyas secretos en la imagen. Usa variables de entorno:

```bash
# .env.production (no subir al repositorio)
DATABASE_URL=postgresql://user:password@host:5432/myapp
REDIS_URL=redis://:password@host:6379
JWT_SECRET=tu-secreto-muy-largo-y-aleatorio
JWT_REFRESH_SECRET=otro-secreto-diferente
PORT=3000
NODE_ENV=production
```

```bash
# Al correr el contenedor
docker run --env-file .env.production myapp:latest

# O con docker-compose
docker compose --env-file .env.production up
```

---

## Despliegue en Railway

Railway detecta el Dockerfile automáticamente:

```bash
# Instalar CLI de Railway
npm install -g @railway/cli

# Autenticarse
railway login

# Crear proyecto y desplegar
railway init
railway up

# Configurar variables de entorno
railway variables set JWT_SECRET=tu-secreto
railway variables set DATABASE_URL=postgresql://...
```

O desde el dashboard de Railway:
1. Conectar el repositorio de GitHub
2. Railway detecta el Dockerfile y construye la imagen
3. Configurar variables de entorno en el panel
4. Deploy automático en cada push a `main`

---

## Despliegue en Render

```yaml
# render.yaml — Infrastructure as Code
services:
  - type: web
    name: my-api
    runtime: docker
    dockerfilePath: ./Dockerfile
    dockerContext: .
    envVars:
      - key: NODE_ENV
        value: production
      - key: DATABASE_URL
        fromDatabase:
          name: my-postgres
          property: connectionString
      - key: REDIS_URL
        fromService:
          name: my-redis
          type: redis
          property: connectionString
      - key: JWT_SECRET
        generateValue: true

databases:
  - name: my-postgres
    databaseName: myapp
    user: myapp

  - name: my-redis
    type: redis
```

---

## CI/CD con GitHub Actions

```yaml
# .github/workflows/deploy.yml
name: Build, Test and Deploy

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

env:
  REGISTRY: ghcr.io
  IMAGE_NAME: ${{ github.repository }}

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_PASSWORD: postgres
          POSTGRES_DB: test_db
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
      redis:
        image: redis:7
        options: >-
          --health-cmd "redis-cli ping"
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

    steps:
      - uses: actions/checkout@v4

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'

      - name: Install dependencies
        run: npm ci

      - name: Run tests
        env:
          DATABASE_URL: postgresql://postgres:postgres@localhost:5432/test_db
          REDIS_URL: redis://localhost:6379
          JWT_SECRET: test-secret
          JWT_REFRESH_SECRET: test-refresh-secret
          NODE_ENV: test
        run: npm run test:ci

  build-and-push:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    permissions:
      contents: read
      packages: write

    steps:
      - uses: actions/checkout@v4

      - name: Log in to Container Registry
        uses: docker/login-action@v3
        with:
          registry: ${{ env.REGISTRY }}
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}

      - name: Build and push Docker image
        uses: docker/build-push-action@v5
        with:
          context: .
          push: true
          tags: ${{ env.REGISTRY }}/${{ env.IMAGE_NAME }}:latest

  deploy:
    needs: build-and-push
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to production
        run: |
          curl -X POST ${{ secrets.DEPLOY_WEBHOOK_URL }}
```

---

## Graceful shutdown

```typescript
// src/server.ts
import { httpServer } from './app';
import { prisma } from './lib/prisma';
import { redis } from './lib/redis';
import { logger } from './lib/logger';

async function gracefulShutdown(signal: string) {
  logger.info(`Received ${signal}, starting graceful shutdown`);

  // 1. Dejar de aceptar nuevas conexiones
  httpServer.close(async () => {
    logger.info('HTTP server closed');

    // 2. Cerrar conexiones a BD y Redis
    await prisma.$disconnect();
    await redis.quit();

    logger.info('Cleanup complete — exiting');
    process.exit(0);
  });

  // 3. Forzar salida si tarda más de 30s
  setTimeout(() => {
    logger.error('Graceful shutdown timeout — forcing exit');
    process.exit(1);
  }, 30_000);
}

process.on('SIGTERM', () => gracefulShutdown('SIGTERM'));
process.on('SIGINT', () => gracefulShutdown('SIGINT'));
```

---

## Resumen

| Concepto | Implementación |
|---|---|
| Imagen pequeña | Multi-stage build (deps → builder → production) |
| Seguridad | Usuario no-root, `.dockerignore`, sin secretos en imagen |
| Health check | Endpoint `/health` + `HEALTHCHECK` en Dockerfile |
| Orquestación local | `docker-compose.yml` con postgres + redis |
| Secretos | Variables de entorno desde `.env` o secretos del CI |
| CI/CD | GitHub Actions: test → build/push → deploy |
| Apagado limpio | `SIGTERM` handler que espera conexiones activas |
