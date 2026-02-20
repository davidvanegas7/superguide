# Introducción a Node.js y entorno de desarrollo

## ¿Qué es Node.js?

Node.js es un **entorno de ejecución de JavaScript (y TypeScript) fuera del navegador**, construido sobre el motor V8 de Chrome. Fue creado en 2009 por Ryan Dahl con un objetivo claro: construir servidores capaces de manejar miles de conexiones simultáneas sin que el rendimiento colapse.

Lo que lo hace especial no es el lenguaje en sí, sino su **arquitectura de entrada/salida no bloqueante** basada en un event loop. Mientras un servidor tradicional crea un hilo por cada conexión, Node.js maneja todo en un único hilo pero de forma asíncrona.

```
Servidor tradicional (bloqueante):
  Cliente 1 → Hilo 1 → espera BD → responde
  Cliente 2 → Hilo 2 → espera BD → responde
  Cliente 3 → Hilo 3 → espera BD → responde

Node.js (no bloqueante):
  Cliente 1 ─┐
  Cliente 2 ─┤─→ Event Loop ─→ delega I/O → sigue atendiendo
  Cliente 3 ─┘                ← recibe resultado → responde
```

---

## El Event Loop

El event loop es el corazón de Node.js. Entenderlo evita bugs sutiles y te permite escribir código asíncrono correcto.

```
   ┌─────────────────────────────┐
   │           timers            │  ← setTimeout, setInterval
   ├─────────────────────────────┤
   │     pending callbacks       │  ← callbacks de I/O diferidos
   ├─────────────────────────────┤
   │        idle, prepare        │  ← interno de Node
   ├─────────────────────────────┤
   │            poll             │  ← espera y ejecuta I/O
   ├─────────────────────────────┤
   │           check             │  ← setImmediate
   ├─────────────────────────────┤
   │      close callbacks        │  ← socket.on('close', ...)
   └─────────────────────────────┘
          ↕ entre cada fase: microtasks
          (Promise.then, queueMicrotask)
```

### Orden de ejecución

```javascript
console.log('1 — síncrono');

setTimeout(() => console.log('4 — setTimeout'), 0);

Promise.resolve().then(() => console.log('3 — microtask (Promise)'));

queueMicrotask(() => console.log('3b — microtask (queueMicrotask)'));

console.log('2 — síncrono');

// Output:
// 1 — síncrono
// 2 — síncrono
// 3 — microtask (Promise)
// 3b — microtask (queueMicrotask)
// 4 — setTimeout
```

**Regla clave:** las microtasks (Promises, queueMicrotask) siempre se ejecutan antes de pasar a la siguiente fase del event loop.

---

## Instalación del entorno

### 1. Node.js con nvm (recomendado)

[nvm](https://github.com/nvm-sh/nvm) permite tener múltiples versiones de Node.js instaladas y cambiar entre ellas fácilmente.

```bash
# Instalar nvm
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.1/install.sh | bash

# Recargar el shell
source ~/.bashrc   # o ~/.zshrc

# Instalar la última versión LTS
nvm install --lts

# Verificar
node --version    # v22.x.x
npm --version     # 10.x.x
```

### 2. pnpm como gestor de paquetes

pnpm es más rápido que npm y usa un store compartido que ahorra disco:

```bash
npm install -g pnpm
pnpm --version   # 9.x.x
```

---

## Crear el proyecto con TypeScript

### Estructura inicial

```bash
mkdir mi-api && cd mi-api
pnpm init
```

### Instalar dependencias

```bash
# TypeScript y tipos de Node
pnpm add -D typescript @types/node ts-node

# ts-node-dev: recarga el servidor al detectar cambios (como nodemon pero para TS)
pnpm add -D ts-node-dev
```

### Configurar TypeScript

```bash
npx tsc --init
```

Reemplaza el contenido de `tsconfig.json` con esta configuración optimizada para backend:

```json
{
  "compilerOptions": {
    "target": "ES2022",
    "module": "CommonJS",
    "moduleResolution": "node",
    "outDir": "./dist",
    "rootDir": "./src",
    "strict": true,
    "esModuleInterop": true,
    "skipLibCheck": true,
    "forceConsistentCasingInFileNames": true,
    "resolveJsonModule": true,
    "declaration": true,
    "declarationMap": true,
    "sourceMap": true
  },
  "include": ["src/**/*"],
  "exclude": ["node_modules", "dist"]
}
```

### Scripts en `package.json`

```json
{
  "scripts": {
    "dev":   "ts-node-dev --respawn --transpile-only src/index.ts",
    "build": "tsc",
    "start": "node dist/index.js",
    "lint":  "eslint src --ext .ts"
  }
}
```

---

## Estructura del proyecto

Una buena estructura desde el principio evita refactorizaciones dolorosas:

```
mi-api/
├── src/
│   ├── index.ts          ← punto de entrada (arranca el servidor)
│   ├── app.ts            ← configuración de Express (sin .listen)
│   ├── config/
│   │   └── env.ts        ← validación de variables de entorno
│   ├── routes/
│   │   └── index.ts      ← registro de todas las rutas
│   ├── controllers/      ← maneja la petición HTTP
│   ├── services/         ← lógica de negocio
│   ├── repositories/     ← acceso a datos (Prisma)
│   ├── middlewares/      ← auth, validación, errores
│   ├── schemas/          ← schemas de Zod
│   └── types/            ← tipos e interfaces compartidos
├── prisma/
│   └── schema.prisma
├── tests/
├── .env
├── .env.example
├── tsconfig.json
└── package.json
```

> **¿Por qué separar `app.ts` e `index.ts`?** Para tests: puedes importar `app` sin arrancar el servidor real, lo que permite a Supertest crear su propio servidor de pruebas.

---

## Tu primer servidor HTTP con Node puro

Antes de usar Express, es útil entender qué hace por debajo:

```typescript
// src/index.ts — versión sin frameworks
import http from 'node:http';

const PORT = 3000;

const server = http.createServer((req, res) => {
  const url    = req.url    ?? '/';
  const method = req.method ?? 'GET';

  console.log(`${method} ${url}`);

  // Manejar rutas manualmente
  if (method === 'GET' && url === '/') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ message: 'Hola desde Node.js!' }));
    return;
  }

  if (method === 'GET' && url === '/health') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ status: 'ok', uptime: process.uptime() }));
    return;
  }

  // 404 por defecto
  res.writeHead(404, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify({ error: 'Not Found' }));
});

server.listen(PORT, () => {
  console.log(`🚀 Servidor corriendo en http://localhost:${PORT}`);
});
```

Ejecuta con:
```bash
pnpm dev
```

Prueba en otra terminal:
```bash
curl http://localhost:3000
# {"message":"Hola desde Node.js!"}

curl http://localhost:3000/health
# {"status":"ok","uptime":12.345}
```

---

## Leer el body de una petición POST

Con Node puro, el body llega como stream y hay que leerlo manualmente:

```typescript
import http from 'node:http';

interface UserPayload {
  name: string;
  email: string;
}

const server = http.createServer((req, res) => {
  if (req.method === 'POST' && req.url === '/users') {
    let body = '';

    req.on('data', (chunk: Buffer) => {
      body += chunk.toString();
    });

    req.on('end', () => {
      try {
        const data = JSON.parse(body) as UserPayload;

        if (!data.name || !data.email) {
          res.writeHead(400, { 'Content-Type': 'application/json' });
          res.end(JSON.stringify({ error: 'name y email son requeridos' }));
          return;
        }

        res.writeHead(201, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ id: Date.now(), ...data }));
      } catch {
        res.writeHead(400, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ error: 'JSON inválido' }));
      }
    });

    return;
  }

  res.writeHead(404, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify({ error: 'Not Found' }));
});

server.listen(3000, () => console.log('Servidor en http://localhost:3000'));
```

```bash
curl -X POST http://localhost:3000/users \
  -H "Content-Type: application/json" \
  -d '{"name":"Ana García","email":"ana@ejemplo.com"}'

# {"id":1708445231234,"name":"Ana García","email":"ana@ejemplo.com"}
```

> Esto es exactamente lo que **Express** abstrae para ti. Con Express, `req.body` ya está parseado automáticamente gracias al middleware `express.json()`.

---

## Variables de proceso y módulos globales de Node

Node expone información del entorno a través de objetos globales:

```typescript
// process.env — variables de entorno
const port    = parseInt(process.env.PORT ?? '3000', 10);
const nodeEnv = process.env.NODE_ENV ?? 'development';

// process.argv — argumentos de la línea de comandos
// node script.ts --port 4000
const args = process.argv.slice(2);
console.log('Args:', args);

// process.cwd() — directorio de trabajo actual
console.log('CWD:', process.cwd());

// process.uptime() — segundos que lleva corriendo el proceso
console.log('Uptime:', process.uptime(), 'segundos');

// process.memoryUsage() — uso de memoria en bytes
const mem = process.memoryUsage();
console.log('Heap usado:', Math.round(mem.heapUsed / 1024 / 1024), 'MB');

// Manejar cierre limpio del proceso
process.on('SIGINT',  () => { console.log('\nApagando servidor...'); process.exit(0); });
process.on('SIGTERM', () => { console.log('\nSIGTERM recibido');      process.exit(0); });

// Capturar errores no manejados (último recurso)
process.on('uncaughtException',  (err) => { console.error('Error no capturado:', err);  process.exit(1); });
process.on('unhandledRejection', (err) => { console.error('Promesa rechazada:', err);   process.exit(1); });
```

---

## Asincronía: callbacks → Promises → async/await

Node.js fue originalmente orientado a callbacks. Hoy se usa async/await casi exclusivamente, pero es importante entender la evolución:

```typescript
import fs from 'node:fs';
import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';

const file = path.join(process.cwd(), 'data.txt');

// ── Estilo antiguo: callbacks (evitar) ──────────────────────────────────────
fs.readFile(file, 'utf-8', (err, data) => {
  if (err) { console.error(err); return; }
  console.log('Callback:', data);
});

// ── Estilo moderno: async/await (preferido) ─────────────────────────────────
async function leerArchivo() {
  try {
    const contenido = await readFile(file, 'utf-8');
    console.log('Async/await:', contenido);
  } catch (err) {
    console.error('Error al leer:', err);
  }
}

// ── Operaciones en paralelo con Promise.all ──────────────────────────────────
async function procesarMultiplesArchivos() {
  const archivos = ['a.txt', 'b.txt', 'c.txt'];

  // ❌ Malo: secuencial (espera cada lectura antes de la siguiente)
  for (const archivo of archivos) {
    const contenido = await readFile(archivo, 'utf-8');
    console.log(contenido);
  }

  // ✅ Bueno: paralelo (todas las lecturas se inician al mismo tiempo)
  const contenidos = await Promise.all(
    archivos.map(a => readFile(a, 'utf-8'))
  );
  contenidos.forEach(c => console.log(c));
}
```

---

## Configurar ESLint para TypeScript

```bash
pnpm add -D eslint @typescript-eslint/parser @typescript-eslint/eslint-plugin
```

`eslint.config.mjs`:

```javascript
import tseslint from '@typescript-eslint/eslint-plugin';
import tsparser from '@typescript-eslint/parser';

export default [
  {
    files: ['src/**/*.ts'],
    languageOptions: { parser: tsparser },
    plugins: { '@typescript-eslint': tseslint },
    rules: {
      '@typescript-eslint/no-unused-vars': 'error',
      '@typescript-eslint/no-explicit-any': 'warn',
      '@typescript-eslint/explicit-function-return-type': 'warn',
      'no-console': 'off',
    },
  },
];
```

---

## Resumen: lo que configuraste

| Herramienta | Propósito |
|---|---|
| `nvm` | Gestionar versiones de Node.js |
| `pnpm` | Gestor de paquetes rápido y eficiente |
| `TypeScript` | Tipado estático sobre JavaScript |
| `ts-node-dev` | Hot-reload para TypeScript en desarrollo |
| `tsconfig.json` | Configuración del compilador TS |
| `ESLint` | Detectar errores y malas prácticas |
| `src/app.ts` + `src/index.ts` | Separación para facilitar tests |

En la siguiente lección profundizamos en los tipos avanzados de TypeScript que más se usan al construir APIs: utility types, generics, decorators y discriminated unions.
