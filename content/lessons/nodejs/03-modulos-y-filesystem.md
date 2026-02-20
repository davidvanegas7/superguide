# Sistema de módulos: CommonJS, ESM y filesystem

## Dos sistemas de módulos en Node.js

Node.js tiene dos sistemas de módulos que coexisten y a veces causan confusión:

| | CommonJS (CJS) | ES Modules (ESM) |
|---|---|---|
| Extensión | `.js` / `.cjs` | `.mjs` / `.js` con `"type":"module"` |
| Sintaxis | `require()` / `module.exports` | `import` / `export` |
| Carga | **Síncrona** | **Asíncrona** |
| `__dirname` | ✅ disponible | ❌ no existe (hay alternativa) |
| Top-level `await` | ❌ | ✅ |
| Tree-shaking | ❌ difícil | ✅ |

### ¿Cuál usar en proyectos nuevos?

Para proyectos backend con TypeScript: **ESM**. El compilador de TypeScript emite `.js` con sintaxis ESM si configuras `"module": "NodeNext"` o `"ESNext"` en `tsconfig.json`.

---

## CommonJS en detalle

```typescript
// math.ts (CommonJS implícito con module: CommonJS en tsconfig)
function add(a: number, b: number): number {
  return a + b;
}

function multiply(a: number, b: number): number {
  return a * b;
}

// Named exports con module.exports
module.exports = { add, multiply };

// O con exports (referencia al mismo objeto)
exports.subtract = (a: number, b: number) => a - b;
```

```typescript
// main.ts
const { add, multiply } = require('./math');
const math = require('./math');

console.log(add(2, 3));       // 5
console.log(math.multiply(4, 5)); // 20
```

### El objeto `module` en CJS

```typescript
console.log(module.id);       // ruta del archivo actual
console.log(module.filename); // ruta absoluta
console.log(module.loaded);   // false durante la carga, true después
console.log(module.parent);   // módulo que hizo el require()
console.log(module.children); // módulos que este requirió
console.log(require.main === module); // true si es el punto de entrada
```

### Caché de módulos

Una de las características más importantes de CJS: **los módulos se cachean tras el primer `require()`**.

```typescript
// config.ts
let callCount = 0;

const config = {
  get count() { return ++callCount; }
};

module.exports = config;

// main.ts
const a = require('./config');
const b = require('./config'); // devuelve el mismo objeto cacheado

console.log(a === b);     // true (misma referencia)
console.log(a.count);     // 1
console.log(b.count);     // 2 (mismo objeto, mismo contador)
```

---

## ES Modules (ESM)

### Exports nombrados y default

```typescript
// users/types.ts
export interface User {
  id:    number;
  name:  string;
  email: string;
}

export type CreateUserInput = Omit<User, 'id'>;

// Export default (solo uno por módulo)
export default class UserService {
  private users: User[] = [];

  create(input: CreateUserInput): User {
    const user: User = { id: Date.now(), ...input };
    this.users.push(user);
    return user;
  }

  findAll(): User[] {
    return this.users;
  }
}
```

```typescript
// main.ts
import UserService, { type User, type CreateUserInput } from './users/types.js';
// Nota: en ESM con TypeScript debes usar la extensión .js en los imports
// (TypeScript la resuelve a .ts en tiempo de compilación)

const svc = new UserService();
const user = svc.create({ name: 'Ana', email: 'ana@test.com' });
console.log(user);
```

### Re-exports y barrel files

```typescript
// src/modules/users/index.ts — barrel file
export { default as UserService } from './UserService.js';
export type { User, CreateUserInput } from './types.js';
export { UserRepository } from './UserRepository.js';

// Ahora el consumidor importa desde un solo punto:
import { UserService, type User } from './modules/users/index.js';
```

### Import dinámico

```typescript
// Útil para carga condicional o lazy loading
async function loadPlugin(name: string) {
  try {
    // El import() retorna una Promise
    const plugin = await import(`./plugins/${name}.js`);
    return plugin.default;
  } catch {
    console.error(`Plugin "${name}" no encontrado`);
    return null;
  }
}

// También útil para importar módulos CJS desde ESM
const { createRequire } = await import('node:module');
const require = createRequire(import.meta.url);
const legacyLib = require('some-legacy-cjs-lib');
```

### `import.meta` — el reemplazo de `__dirname`

```typescript
// En ESM, __dirname y __filename no existen. Usa import.meta:
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const __filename = fileURLToPath(import.meta.url);
const __dirname  = dirname(__filename);

// Rutas relativas al archivo actual
const configPath = join(__dirname, '..', 'config', 'app.json');
const templatesDir = join(__dirname, 'templates');

// import.meta.url también sirve para detectar si es el punto de entrada
const isMain = import.meta.url === `file://${process.argv[1]}`;
```

---

## Filesystem con `fs/promises`

Siempre usa la API de promesas, nunca la síncrona (bloquea el event loop):

```typescript
import { readFile, writeFile, mkdir, readdir, stat, unlink, copyFile } from 'node:fs/promises';
import { join } from 'node:path';

// Leer un archivo de texto
async function readConfig(filePath: string): Promise<Record<string, unknown>> {
  const raw = await readFile(filePath, 'utf-8');
  return JSON.parse(raw);
}

// Escribir un archivo (crea o sobreescribe)
async function writeConfig(filePath: string, data: unknown): Promise<void> {
  const json = JSON.stringify(data, null, 2);
  await writeFile(filePath, json, 'utf-8');
}

// Leer directorio con información de cada entrada
async function listDirectory(dirPath: string) {
  const entries = await readdir(dirPath, { withFileTypes: true });

  return entries.map(entry => ({
    name:      entry.name,
    isDir:     entry.isDirectory(),
    isFile:    entry.isFile(),
    isSymlink: entry.isSymbolicLink(),
    path:      join(dirPath, entry.name),
  }));
}

// Crear directorios anidados (equivalente a mkdir -p)
async function ensureDir(dirPath: string): Promise<void> {
  await mkdir(dirPath, { recursive: true });
}

// Verificar si un archivo existe
async function fileExists(filePath: string): Promise<boolean> {
  try {
    await stat(filePath);
    return true;
  } catch {
    return false;
  }
}
```

### Leer y procesar archivos grandes con streams

Para archivos grandes, **nunca uses `readFile` completo**. Usa streams para procesar línea a línea:

```typescript
import { createReadStream } from 'node:fs';
import { createInterface } from 'node:readline';

async function processLargeCSV(filePath: string): Promise<void> {
  const fileStream = createReadStream(filePath, { encoding: 'utf-8' });

  const rl = createInterface({
    input: fileStream,
    crlfDelay: Infinity, // maneja \r\n de Windows
  });

  let lineNumber = 0;

  for await (const line of rl) {
    lineNumber++;
    if (lineNumber === 1) continue; // saltar header

    const [id, name, email] = line.split(',').map(s => s.trim());
    console.log(`Procesando usuario ${id}: ${name} <${email}>`);
    // En producción: insertar en BD, transformar datos, etc.
  }

  console.log(`Total líneas procesadas: ${lineNumber - 1}`);
}
```

### Escribir con streams (alta performance)

```typescript
import { createWriteStream } from 'node:fs';
import { pipeline } from 'node:stream/promises';
import { Readable } from 'node:stream';

async function writeMillionRows(outputPath: string): Promise<void> {
  const writeStream = createWriteStream(outputPath);

  // Generador como Readable stream
  async function* generateRows() {
    yield 'id,name,value\n'; // header
    for (let i = 1; i <= 1_000_000; i++) {
      yield `${i},item-${i},${Math.random().toFixed(4)}\n`;
    }
  }

  await pipeline(Readable.from(generateRows()), writeStream);
  console.log('Archivo generado');
}
```

---

## Módulo `path`

```typescript
import { join, resolve, relative, extname, basename, dirname, parse, format } from 'node:path';

// join — construye rutas compatibles con el SO
const filePath = join('src', 'modules', 'users', 'index.ts');
// Linux: 'src/modules/users/index.ts'
// Windows: 'src\\modules\\users\\index.ts'

// resolve — ruta absoluta desde el directorio actual
const absPath = resolve('src', 'config.json');
// '/home/ubuntu/proyecto/src/config.json'

// relative — ruta relativa entre dos rutas absolutas
const rel = relative('/home/ubuntu/proyecto/src', '/home/ubuntu/proyecto/tests');
// '../tests'

// Información del path
const info = parse('/home/ubuntu/proyecto/src/app.service.ts');
// { root: '/', dir: '/home/ubuntu/proyecto/src', base: 'app.service.ts', ext: '.ts', name: 'app.service' }

console.log(extname('server.test.ts'));  // '.ts'
console.log(basename('/src/app.ts'));    // 'app.ts'
console.log(dirname('/src/app.ts'));     // '/src'
```

---

## Módulo `os`

```typescript
import os from 'node:os';

console.log(os.platform());     // 'linux', 'darwin', 'win32'
console.log(os.arch());         // 'x64', 'arm64'
console.log(os.cpus().length);  // número de CPUs lógicas
console.log(os.totalmem());     // bytes de RAM total
console.log(os.freemem());      // bytes de RAM disponible
console.log(os.homedir());      // '/home/ubuntu'
console.log(os.tmpdir());       // '/tmp'
console.log(os.hostname());     // nombre del host

// Útil para configurar concurrencia
const CONCURRENCY = Math.max(1, os.cpus().length - 1);
```

---

## Gestión de rutas de proyecto: patrón `paths.ts`

En proyectos reales centraliza todas las rutas en un archivo:

```typescript
// src/config/paths.ts
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const __dirname = dirname(fileURLToPath(import.meta.url));

export const ROOT_DIR    = join(__dirname, '..', '..');
export const SRC_DIR     = join(ROOT_DIR, 'src');
export const CONFIG_DIR  = join(ROOT_DIR, 'config');
export const UPLOADS_DIR = join(ROOT_DIR, 'storage', 'uploads');
export const LOGS_DIR    = join(ROOT_DIR, 'storage', 'logs');
export const TEMP_DIR    = join(ROOT_DIR, 'storage', 'temp');

// Asegura que los directorios existen al iniciar la app
export async function ensureDirectories(): Promise<void> {
  const { mkdir } = await import('node:fs/promises');
  await Promise.all([
    mkdir(UPLOADS_DIR, { recursive: true }),
    mkdir(LOGS_DIR,    { recursive: true }),
    mkdir(TEMP_DIR,    { recursive: true }),
  ]);
}
```

---

## Interoperabilidad CJS ↔ ESM

```typescript
// Importar un módulo CJS desde ESM (funciona sin problemas)
import lodash from 'lodash';               // ✅ default import
import { cloneDeep } from 'lodash';        // ✅ named imports

// Importar un módulo ESM desde CJS (requiere import() dinámico)
// En CJS NO puedes usar import estático
async function loadESModule() {
  const { default: chalk } = await import('chalk'); // chalk v5+ es ESM puro
  return chalk.green('¡Texto verde!');
}

// tsconfig.json recomendado para ESM con Node.js
// {
//   "compilerOptions": {
//     "module":       "NodeNext",    // emite ESM correcto para Node
//     "moduleResolution": "NodeNext",
//     "target":       "ES2022",
//     "outDir":       "dist"
//   }
// }
```

---

## Resumen

| Tarea | API recomendada |
|---|---|
| Leer archivo completo | `fs/promises` → `readFile` |
| Escribir archivo | `fs/promises` → `writeFile` |
| Listar directorio | `fs/promises` → `readdir({ withFileTypes: true })` |
| Crear directorios | `fs/promises` → `mkdir({ recursive: true })` |
| Archivos grandes | `createReadStream` + `readline.createInterface` |
| Construir rutas | `node:path` → `join`, `resolve` |
| Info del sistema | `node:os` |
| `__dirname` en ESM | `dirname(fileURLToPath(import.meta.url))` |

En la siguiente lección construimos nuestra primera API REST con Express.js y TypeScript, conectando todo lo aprendido hasta aquí.
