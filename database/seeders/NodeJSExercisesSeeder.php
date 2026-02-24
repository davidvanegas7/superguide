<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NodeJSExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'nodejs-backend-typescript')->first();

        if (! $course) {
            $this->command->warn('NodeJS course not found. Run CourseSeeder + NodeJSLessonSeeder first.');
            return;
        }

        /** @var \Illuminate\Support\Collection<int,Lesson> $lessons */
        $lessons = Lesson::where('course_id', $course->id)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('sort_order');

        $exercises = $this->exercises($lessons);
        $now = now();

        foreach ($exercises as $ex) {
            DB::table('lesson_exercises')->updateOrInsert(
                ['lesson_id' => $ex['lesson_id']],
                array_merge($ex, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        $this->command->info('NodeJS exercises seeded: ' . count($exercises) . ' exercises.');
    }

    private function exercises(\Illuminate\Support\Collection $lessons): array
    {
        $ex = [];

        // ── Lección 1: Introducción a Node.js ─────────────────────────────
        if ($l = $lessons->get(1)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Implementa utilidades de concurrencia async',
            'language'     => 'typescript',
            'description'  => <<<'MD'
El corazón de Node.js es su capacidad de manejar múltiples operaciones **sin bloquear el hilo principal**.
Implementa tres utilidades esenciales de concurrencia:
`processAll` (ejecuta tareas en paralelo), `retry` (reintenta con backoff exponencial) y `timeout` (cancela si tarda demasiado).
MD,
            'starter_code' => <<<'TS'
// ─── Simulación de operaciones asíncronas ────────────────────────────────
const delay = (ms: number) => new Promise<void>(r => setTimeout(r, ms));

async function fetchUser(id: number): Promise<{ id: number; name: string }> {
  await delay(Math.random() * 100);
  if (id === 3) throw new Error(`Usuario ${id} no encontrado`);
  return { id, name: `Usuario ${id}` };
}

// ─── 1. processAll ───────────────────────────────────────────────────────
// Ejecuta todas las tareas en PARALELO y retorna sus resultados.
// Si alguna falla, esa posición tendrá { error: string } en vez del resultado.
async function processAll<T>(
  tasks: (() => Promise<T>)[]
): Promise<Array<T | { error: string }>> {
  // Tu código aquí (tip: Promise.allSettled)
  return [];
}

// ─── 2. retry ────────────────────────────────────────────────────────────
// Reintenta fn hasta maxRetries veces. Entre cada intento espera
// baseDelay * 2^intento ms (backoff exponencial). Si agota los reintentos, lanza.
async function retry<T>(
  fn: () => Promise<T>,
  maxRetries: number,
  baseDelay: number
): Promise<T> {
  // Tu código aquí
  throw new Error('No implementado');
}

// ─── 3. timeout ──────────────────────────────────────────────────────────
// Si la promesa no resuelve en `ms` milisegundos, rechaza con "Timeout".
async function timeout<T>(promise: Promise<T>, ms: number): Promise<T> {
  // Tu código aquí (tip: Promise.race)
  return promise;
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
async function main() {
  // processAll: ids 1-4 (3 fallará)
  const results = await processAll([1, 2, 3, 4].map(id => () => fetchUser(id)));
  console.log('processAll:', JSON.stringify(results));
  // [{ id:1, name:'Usuario 1' }, { id:2, name:'Usuario 2' }, { error:'...' }, { id:4, name:'Usuario 4' }]

  // retry: fetchUser(3) siempre falla, debe agotar los reintentos
  try {
    await retry(() => fetchUser(3), 3, 10);
  } catch (e: any) {
    console.log('retry agotado:', e.message); // 'Usuario 3 no encontrado' (o similar)
  }

  // timeout: fetchUser(1) tarda ~50ms; con 5ms debe fallar
  try {
    await timeout(fetchUser(1), 5);
  } catch (e: any) {
    console.log('timeout:', e.message); // 'Timeout'
  }

  // timeout: con 500ms debe tener tiempo de resolverse
  const user = await timeout(fetchUser(2), 500);
  console.log('timeout ok:', user.name); // 'Usuario 2'
}

main();
TS,
        ];

        // ── Lección 2: TypeScript para Backend ────────────────────────────
        if ($l = $lessons->get(2)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Diseña el dominio con tipos y generics TypeScript',
            'language'     => 'typescript',
            'description'  => <<<'MD'
TypeScript en el backend no es opcional: los contratos de la API se modelan con **interfaces**, **types** y **generics**.
Implementa el tipo `Result<T, E>`, el tipo `PaginatedResponse<T>` y el repositorio genérico `InMemoryRepository<T>`.
MD,
            'starter_code' => <<<'TS'
// ─── 1. Result<T, E> ─────────────────────────────────────────────────────
// Tipo discriminado para manejar éxito o error sin lanzar excepciones.
type Result<T, E extends Error = Error> =
  | { success: true;  data: T }
  | { success: false; error: E };

function ok<T>(data: T): Result<T> {
  // Tu código aquí
  return {} as any;
}

function fail<E extends Error>(error: E): Result<never, E> {
  // Tu código aquí
  return {} as any;
}

// ─── 2. PaginatedResponse<T> ─────────────────────────────────────────────
interface PaginatedResponse<T> {
  data:       T[];
  total:      number;
  page:       number;
  pageSize:   number;
  totalPages: number;
  hasNext:    boolean;
  hasPrev:    boolean;
}

function paginate<T>(items: T[], page: number, pageSize: number): PaginatedResponse<T> {
  // Tu código aquí: calcula el slice correcto y llena todos los campos
  return {} as any;
}

// ─── 3. InMemoryRepository<T> ────────────────────────────────────────────
interface Entity { id: number }

class InMemoryRepository<T extends Entity> {
  private store = new Map<number, T>();
  private nextId = 1;

  create(data: Omit<T, 'id'>): T {
    // Asigna un id autoincremental y guarda
    // Tu código aquí
    return {} as any;
  }

  findById(id: number): Result<T> {
    // Retorna ok(entity) o fail(new Error('Not found'))
    // Tu código aquí
    return fail(new Error('Not found'));
  }

  findAll(filter?: Partial<T>): T[] {
    // Sin filter → todos; con filter → solo los que coincidan en esas propiedades
    // Tu código aquí
    return [];
  }

  update(id: number, patch: Partial<Omit<T, 'id'>>): Result<T> {
    // Actualiza solo los campos del patch y retorna el objeto actualizado
    // Tu código aquí
    return fail(new Error('Not found'));
  }

  delete(id: number): boolean {
    return this.store.delete(id);
  }

  count(): number { return this.store.size; }
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
interface User extends Entity { name: string; role: 'admin' | 'user'; active: boolean; }

const repo = new InMemoryRepository<User>();
const u1   = repo.create({ name: 'Ana',  role: 'admin', active: true  });
const u2   = repo.create({ name: 'Bob',  role: 'user',  active: true  });
const u3   = repo.create({ name: 'Carl', role: 'user',  active: false });

console.log('count:', repo.count()); // 3

const found = repo.findById(2);
if (found.success) console.log('findById:', found.data.name); // Bob

console.log('activos:', repo.findAll({ active: true }).map(u => u.name)); // ['Ana', 'Bob']

const upd = repo.update(2, { role: 'admin' });
if (upd.success) console.log('updated role:', upd.data.role); // admin

const page = paginate(repo.findAll(), 1, 2);
console.log('página 1:', page.data.map(u => u.name)); // ['Ana', 'Bob']
console.log('total pages:', page.totalPages);          // 2
console.log('hasNext:', page.hasNext);                 // true

const r1 = ok(42);
const r2 = fail(new Error('algo salió mal'));
console.log('ok:', r1);  // { success: true, data: 42 }
console.log('fail:', r2.success, r2.error.message); // false, 'algo salió mal'
TS,
        ];

        // ── Lección 3: Módulos y Filesystem ───────────────────────────────
        if ($l = $lessons->get(3)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Utilidades de rutas y árbol de directorios',
            'language'     => 'typescript',
            'description'  => <<<'MD'
El módulo `path` de Node.js es indispensable para manejar rutas entre sistemas operativos.
Implementa **sin usar `path`**: `joinPath`, `normalizePath`, `getExtension` y `buildDirectoryTree` para construir una representación en árbol de un listado de rutas.
MD,
            'starter_code' => <<<'TS'
// ─── 1. joinPath ─────────────────────────────────────────────────────────
// Une segmentos de ruta usando "/" como separador, eliminando duplicados y
// manejando segmentos vacíos. No añade "/" al inicio si el primero no lo tiene.
// joinPath('src', 'utils', 'helpers.ts') → 'src/utils/helpers.ts'
// joinPath('/var', '/www', 'html/')      → '/var/www/html'
function joinPath(...segments: string[]): string {
  // Tu código aquí
  return '';
}

// ─── 2. normalizePath ────────────────────────────────────────────────────
// Resuelve ".." y "." en una ruta.
// normalizePath('src/../lib/./utils') → 'lib/utils'
// normalizePath('/a/b/../c/./d')      → '/a/c/d'
function normalizePath(path: string): string {
  // Tu código aquí (tip: split, procesa ".." con una pila)
  return '';
}

// ─── 3. getExtension ─────────────────────────────────────────────────────
// Retorna la extensión con punto, o '' si no tiene.
// getExtension('server.ts')      → '.ts'
// getExtension('Makefile')       → ''
// getExtension('archive.tar.gz') → '.gz'
function getExtension(filename: string): string {
  // Tu código aquí
  return '';
}

// ─── 4. buildDirectoryTree ───────────────────────────────────────────────
// Dado un array de rutas relativas, construye un árbol anidado.
// buildDirectoryTree(['src/index.ts', 'src/utils/helpers.ts', 'README.md'])
// → { src: { 'index.ts': null, utils: { 'helpers.ts': null } }, 'README.md': null }
type Tree = { [key: string]: Tree | null };

function buildDirectoryTree(paths: string[]): Tree {
  // Tu código aquí (recorre cada path dividiéndolo en partes)
  return {};
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
console.log(joinPath('src', 'utils', 'helpers.ts'));     // src/utils/helpers.ts
console.log(joinPath('/var', '/www/', '/html/'));         // /var/www/html
console.log(joinPath('a', '', 'b', '', 'c'));            // a/b/c

console.log(normalizePath('src/../lib/./utils'));         // lib/utils
console.log(normalizePath('/a/b/../c/./d'));              // /a/c/d

console.log(getExtension('server.ts'));                  // .ts
console.log(getExtension('Makefile'));                   // ''
console.log(getExtension('archive.tar.gz'));             // .gz

const tree = buildDirectoryTree([
  'src/index.ts',
  'src/utils/helpers.ts',
  'src/utils/logger.ts',
  'README.md',
]);
console.log(JSON.stringify(tree, null, 2));
// { src: { 'index.ts': null, utils: { 'helpers.ts': null, 'logger.ts': null } }, 'README.md': null }
TS,
        ];

        // ── Lección 4: Express + TypeScript ───────────────────────────────
        if ($l = $lessons->get(4)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mini router con parámetros de URL',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Express interpreta las URLs y extrae los parámetros dinámicos (`:id`, `:slug`).
Implementa la clase `Router` con los métodos `get/post/put/delete`, la función `matchRoute` que extrae parámetros, y simula el ciclo **request → middleware → handler → response**.
MD,
            'starter_code' => <<<'TS'
// ─── Tipos ────────────────────────────────────────────────────────────────
interface Request  { method: string; path: string; params: Record<string,string>; body?: any; }
interface Response { status: number; body: any; }
type Handler    = (req: Request) => Response;
type RouteEntry = { method: string; pattern: string; handler: Handler; };

// ─── 1. matchRoute ────────────────────────────────────────────────────────
// Comprueba si `path` encaja con `pattern` (que puede tener :param).
// Si encaja, retorna los params extraídos; si no, retorna null.
// matchRoute('/users/:id/posts/:postId', '/users/42/posts/7')
//   → { id: '42', postId: '7' }
// matchRoute('/users/:id', '/products/99') → null
function matchRoute(
  pattern: string,
  path: string
): Record<string, string> | null {
  // Tu código aquí (split ambos por '/', compara segmento a segmento)
  return null;
}

// ─── 2. Router ────────────────────────────────────────────────────────────
class Router {
  private routes: RouteEntry[] = [];

  private add(method: string, pattern: string, handler: Handler): this {
    this.routes.push({ method: method.toUpperCase(), pattern, handler });
    return this;
  }

  get(pattern: string,    handler: Handler): this { return this.add('GET',    pattern, handler); }
  post(pattern: string,   handler: Handler): this { return this.add('POST',   pattern, handler); }
  put(pattern: string,    handler: Handler): this { return this.add('PUT',    pattern, handler); }
  delete(pattern: string, handler: Handler): this { return this.add('DELETE', pattern, handler); }

  // Busca la ruta que coincida y la ejecuta. Si no hay coincidencia → 404.
  handle(req: Omit<Request, 'params'>): Response {
    // Tu código aquí
    // 1. Busca en this.routes la primera que coincida en method y pattern
    // 2. Si la encuentra, llama al handler con req + params
    // 3. Si no, retorna { status: 404, body: { error: 'Not Found' } }
    return { status: 404, body: { error: 'Not Found' } };
  }
}

// ─── Pruebas ──────────────────────────────────────────────────────────────
const router = new Router();

router
  .get('/health', () => ({ status: 200, body: { ok: true } }))
  .get('/users',  () => ({ status: 200, body: [{ id: 1, name: 'Ana' }, { id: 2, name: 'Bob' }] }))
  .get('/users/:id', req => ({
    status: 200,
    body: { id: Number(req.params.id), name: 'Usuario ' + req.params.id },
  }))
  .post('/users', req => ({ status: 201, body: { created: true, data: req.body } }))
  .delete('/users/:id', req => ({ status: 200, body: { deleted: req.params.id } }));

console.log(router.handle({ method: 'GET',    path: '/health' }));
// { status: 200, body: { ok: true } }

console.log(router.handle({ method: 'GET',    path: '/users/42' }));
// { status: 200, body: { id: 42, name: 'Usuario 42' } }

console.log(router.handle({ method: 'DELETE', path: '/users/7' }));
// { status: 200, body: { deleted: '7' } }

console.log(router.handle({ method: 'GET',    path: '/unknown' }));
// { status: 404, body: { error: 'Not Found' } }

// matchRoute individual
console.log(matchRoute('/posts/:slug/comments/:cid', '/posts/hola-mundo/comments/5'));
// { slug: 'hola-mundo', cid: '5' }
TS,
        ];

        // ── Lección 5: Middlewares ─────────────────────────────────────────
        if ($l = $lessons->get(5)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Pipeline de middlewares con compose',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Los middlewares de Express forman un **pipeline**: cada uno recibe el contexto, lo procesa y decide si pasa al siguiente.
Implementa `compose` para encadenar middlewares, un middleware de **logging**, uno de **rate limiting** (en memoria) y uno de **autenticación por API key**.
MD,
            'starter_code' => <<<'TS'
// ─── Tipos ────────────────────────────────────────────────────────────────
interface Ctx {
  method:  string;
  path:    string;
  headers: Record<string, string>;
  ip:      string;
  body?:   unknown;
  userId?: number;      // el middleware de auth puede llenarlo
  logs:    string[];    // acumula mensajes del logger
  status?: number;
  response?: unknown;
}
type Next       = () => void;
type Middleware = (ctx: Ctx, next: Next) => void;

// ─── 1. compose ──────────────────────────────────────────────────────────
// Encadena middlewares de izquierda a derecha. Cuando se llama next() dentro
// de uno, pasa la ejecución al siguiente en la cadena.
function compose(...middlewares: Middleware[]): (ctx: Ctx) => void {
  return (ctx: Ctx) => {
    // Tu código aquí (índice + función dispatch recursiva)
  };
}

// ─── 2. loggerMiddleware ─────────────────────────────────────────────────
// Agrega a ctx.logs el mensaje: "[METHOD] path — START" antes de next()
// y "[METHOD] path — DONE (status)" después de que la cadena termine.
const loggerMiddleware: Middleware = (ctx, next) => {
  // Tu código aquí
  next();
};

// ─── 3. rateLimiter ──────────────────────────────────────────────────────
// Permite como máximo `max` peticiones por `windowMs` desde la misma IP.
// Si se supera: ctx.status = 429, ctx.response = { error: 'Too Many Requests' } y NO llama next().
function rateLimiter(max: number, windowMs: number): Middleware {
  const hits = new Map<string, { count: number; resetAt: number }>();
  return (ctx, next) => {
    // Tu código aquí
    next();
  };
}

// ─── 4. apiKeyAuth ────────────────────────────────────────────────────────
// Lee ctx.headers['x-api-key']. Si coincide con alguna clave del mapa,
// asigna ctx.userId. Si no: ctx.status = 401, ctx.response = { error: 'Unauthorized' }
const API_KEYS: Record<string, number> = { 'secret-admin': 1, 'secret-user': 2 };
const apiKeyAuth: Middleware = (ctx, next) => {
  // Tu código aquí
  next();
};

// ─── Pipeline final ───────────────────────────────────────────────────────
const limiter = rateLimiter(3, 5000); // 3 req / 5 seg por IP

const pipeline = compose(
  loggerMiddleware,
  limiter,
  apiKeyAuth,
  (ctx, next) => {
    ctx.status   = 200;
    ctx.response = { message: 'OK', userId: ctx.userId };
    next();
  }
);

function makeCtx(path: string, apiKey?: string, ip = '127.0.0.1'): Ctx {
  return { method: 'GET', path, headers: apiKey ? { 'x-api-key': apiKey } : {}, ip, logs: [] };
}

const c1 = makeCtx('/api/data', 'secret-admin');
pipeline(c1);
console.log('status:', c1.status, '| userId:', c1.userId, '| logs:', c1.logs);
// status: 200 | userId: 1 | logs: ['[GET] /api/data — START', '[GET] /api/data — DONE (200)']

const c2 = makeCtx('/api/data'); // sin api key
pipeline(c2);
console.log('sin auth:', c2.status, c2.response);
// 401 { error: 'Unauthorized' }

// superar rate limit
for (let i = 0; i < 4; i++) {
  const c = makeCtx('/api/data', 'secret-user', '10.0.0.1');
  pipeline(c);
  if (c.status === 429) { console.log('rate limit hit en petición', i + 1); break; }
}
TS,
        ];

        // ── Lección 6: Diseño REST API ─────────────────────────────────────
        if ($l = $lessons->get(6)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Respuestas REST consistentes y paginación',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Una buena API REST siempre retorna **respuestas con estructura consistente** y paginación correcta.
Implementa `ApiResponse` (envelope genérico), `HttpStatus` (mapa de códigos) y la función `buildPaginationLinks` que genera los links `next`, `prev`, `first` y `last`.
MD,
            'starter_code' => <<<'TS'
// ─── 1. ApiResponse ───────────────────────────────────────────────────────
// Envelope estándar: { success, data?, error?, meta? }
interface ApiResponse<T> {
  success:  boolean;
  data?:    T;
  error?:   { code: string; message: string; details?: unknown };
  meta?:    Record<string, unknown>;
}

function successResponse<T>(data: T, meta?: Record<string, unknown>): ApiResponse<T> {
  // Tu código aquí
  return {} as any;
}

function errorResponse(
  code: string,
  message: string,
  details?: unknown
): ApiResponse<never> {
  // Tu código aquí
  return {} as any;
}

// ─── 2. HttpStatus ────────────────────────────────────────────────────────
// Mapa con los códigos HTTP más usados en APIs REST y su texto oficial.
// Implementa al menos: 200, 201, 204, 400, 401, 403, 404, 409, 422, 429, 500
const HttpStatus: Record<number, string> = {
  // Tu código aquí
};

function getStatusText(code: number): string {
  return HttpStatus[code] ?? 'Unknown';
}

// ─── 3. buildPaginationLinks ──────────────────────────────────────────────
// Dado baseUrl, la página actual, tamaño de página y total de registros,
// retorna los links de navegación HATEOAS.
// Si estamos en la primera página → no hay 'prev'.
// Si estamos en la última → no hay 'next'.
interface PaginationLinks {
  first: string;
  last:  string;
  prev?: string;
  next?: string;
}

function buildPaginationLinks(
  baseUrl: string,
  page: number,
  pageSize: number,
  total: number
): PaginationLinks {
  const totalPages = Math.ceil(total / pageSize);
  // Tu código aquí
  return {} as any;
}

// ─── Pruebas ──────────────────────────────────────────────────────────────
console.log(JSON.stringify(successResponse({ id: 1, name: 'Ana' })));
// { success: true, data: { id: 1, name: 'Ana' } }

console.log(JSON.stringify(errorResponse('NOT_FOUND', 'Usuario no existe', { id: 99 })));
// { success: false, error: { code: 'NOT_FOUND', message: '...', details: { id: 99 } } }

console.log(getStatusText(201)); // Created
console.log(getStatusText(422)); // Unprocessable Entity
console.log(getStatusText(999)); // Unknown

const links = buildPaginationLinks('https://api.example.com/users', 2, 10, 55);
console.log(links);
// { first: '.../users?page=1&pageSize=10',
//   last:  '.../users?page=6&pageSize=10',
//   prev:  '.../users?page=1&pageSize=10',
//   next:  '.../users?page=3&pageSize=10' }

const lastPage = buildPaginationLinks('https://api.example.com/users', 6, 10, 55);
console.log('last page next:', lastPage.next); // undefined
TS,
        ];

        // ── Lección 7: Validación con Zod ─────────────────────────────────
        if ($l = $lessons->get(7)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Construye un validador de esquemas tipo Zod',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Zod valida datos en **runtime** con una API fluida y encadenable. Para entender cómo funciona por dentro,
implementa las clases `StringSchema`, `NumberSchema` y `ObjectSchema` con los métodos `parse` y `safeParse`.
MD,
            'starter_code' => <<<'TS'
// ─── Base ──────────────────────────────────────────────────────────────────
interface ValidationError { field: string; message: string; }

interface SafeParseResult<T> {
  success: true;  data: T;
} | {
  success: false; errors: ValidationError[];
}

abstract class Schema<T> {
  protected _rules: Array<(v: unknown) => string | null> = [];

  // Lanza un Error con los mensajes si hay errores de validación
  parse(value: unknown): T {
    const result = this.safeParse(value);
    if (!result.success) {
      throw new Error(result.errors.map(e => `${e.field}: ${e.message}`).join('; '));
    }
    return result.data;
  }

  abstract safeParse(value: unknown): SafeParseResult<T>;
}

// ─── StringSchema ──────────────────────────────────────────────────────────
class StringSchema extends Schema<string> {
  min(n: number, msg = `Mínimo ${n} caracteres`): this {
    this._rules.push(v => (typeof v === 'string' && v.length >= n) ? null : msg);
    return this;
  }

  max(n: number, msg = `Máximo ${n} caracteres`): this {
    this._rules.push(v => (typeof v === 'string' && v.length <= n) ? null : msg);
    return this;
  }

  email(msg = 'Email inválido'): this {
    this._rules.push(v =>
      typeof v === 'string' && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) ? null : msg
    );
    return this;
  }

  // Implementa safeParse: verifica que value sea string, luego aplica las reglas.
  // Retorna el PRIMER error encontrado o { success: true, data: value as string }.
  safeParse(value: unknown): SafeParseResult<string> {
    // Tu código aquí
    return { success: false, errors: [{ field: 'value', message: 'No implementado' }] };
  }
}

// ─── NumberSchema ──────────────────────────────────────────────────────────
class NumberSchema extends Schema<number> {
  positive(msg = 'Debe ser positivo'): this {
    this._rules.push(v => (typeof v === 'number' && v > 0) ? null : msg);
    return this;
  }

  int(msg = 'Debe ser entero'): this {
    this._rules.push(v => (typeof v === 'number' && Number.isInteger(v)) ? null : msg);
    return this;
  }

  // Implementa safeParse: verifica que value sea number, luego aplica reglas.
  safeParse(value: unknown): SafeParseResult<number> {
    // Tu código aquí
    return { success: false, errors: [{ field: 'value', message: 'No implementado' }] };
  }
}

// ─── ObjectSchema ──────────────────────────────────────────────────────────
type SchemaShape = Record<string, Schema<any>>;
type InferShape<S extends SchemaShape> = { [K in keyof S]: S[K] extends Schema<infer T> ? T : never };

class ObjectSchema<S extends SchemaShape> extends Schema<InferShape<S>> {
  constructor(private shape: S) { super(); }

  // Aplica cada schema del shape al campo correspondiente.
  // Acumula TODOS los errores (no solo el primero).
  // Si el campo no existe en value → pasa null/undefined al schema hijo.
  safeParse(value: unknown): SafeParseResult<InferShape<S>> {
    // Tu código aquí
    return { success: false, errors: [{ field: 'root', message: 'No implementado' }] };
  }
}

// ─── Helpers de instancia ──────────────────────────────────────────────────
const z = {
  string: () => new StringSchema(),
  number: () => new NumberSchema(),
  object: <S extends SchemaShape>(shape: S) => new ObjectSchema(shape),
};

// ─── Pruebas ──────────────────────────────────────────────────────────────
const createUserSchema = z.object({
  name:  z.string().min(2).max(50),
  email: z.string().email(),
  age:   z.number().int().positive(),
});

const r1 = createUserSchema.safeParse({ name: 'Ana', email: 'ana@example.com', age: 25 });
console.log('válido:', r1.success); // true
if (r1.success) console.log('data:', r1.data); // { name, email, age }

const r2 = createUserSchema.safeParse({ name: 'A', email: 'no-es-email', age: -1 });
console.log('inválido:', r2.success); // false
if (!r2.success) console.log('errores:', r2.errors.map(e => `${e.field}: ${e.message}`));
// name: Mínimo 2 caracteres, email: Email inválido, age: Debe ser positivo

try {
  z.string().min(5).parse('hi');
} catch (e: any) {
  console.log('parse lanza:', e.message); // 'value: Mínimo 5 caracteres'
}
TS,
        ];

        // ── Lección 8: Variables de entorno ───────────────────────────────
        if ($l = $lessons->get(8)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Gestiona y valida la configuración de la app',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Una app bien configurada valida todas sus variables de entorno **al arrancar** y lanza errores claros si faltan.
Implementa `ConfigManager` con soporte para tipos (`string`, `number`, `boolean`), valores por defecto y validación de campos requeridos.
MD,
            'starter_code' => <<<'TS'
type ConfigValue = string | number | boolean;

// ─── ConfigManager ────────────────────────────────────────────────────────
class ConfigManager {
  private store: Record<string, string> = {};
  private missing: string[] = [];

  // Carga un objeto de pares clave-valor (simula process.env)
  load(env: Record<string, string | undefined>): this {
    for (const [k, v] of Object.entries(env)) {
      if (v !== undefined) this.store[k] = v;
    }
    return this;
  }

  // Retorna el valor como string, o defaultValue si no existe
  get(key: string, defaultValue?: string): string | undefined {
    // Tu código aquí
    return undefined;
  }

  // Requiere que la clave exista; si no, la agrega a this.missing
  // Retorna el valor o '' como placeholder
  require(key: string): string {
    // Tu código aquí
    return '';
  }

  // Como require() pero convierte a number. Lanza si no es un número válido.
  requireNumber(key: string): number {
    // Tu código aquí
    return 0;
  }

  // Como get() pero convierte el valor a boolean.
  // 'true', '1', 'yes' → true; todo lo demás → false
  getBoolean(key: string, defaultValue = false): boolean {
    // Tu código aquí
    return defaultValue;
  }

  // Si hay claves missing, lanza un Error con todas ellas listadas
  validate(): this {
    if (this.missing.length > 0) {
      throw new Error(
        `Variables de entorno faltantes: ${this.missing.join(', ')}`
      );
    }
    return this;
  }
}

// ─── Pruebas ──────────────────────────────────────────────────────────────
const env = new ConfigManager().load({
  NODE_ENV:    'production',
  PORT:        '3000',
  DB_URL:      'postgresql://user:pass@localhost/mydb',
  ENABLE_LOGS: 'true',
  // JWT_SECRET está ausente
});

console.log('NODE_ENV:', env.get('NODE_ENV'));                      // production
console.log('PORT:', env.requireNumber('PORT'));                    // 3000
console.log('LOGS:', env.getBoolean('ENABLE_LOGS'));                // true
console.log('FALLBACK:', env.get('REDIS_URL', 'redis://localhost')); // redis://localhost
env.require('JWT_SECRET'); // lo marca como faltante

try {
  env.validate();
} catch (e: any) {
  console.log('Error:', e.message); // 'Variables de entorno faltantes: JWT_SECRET'
}

// Configuración completa sin errores
const fullEnv = new ConfigManager()
  .load({ NODE_ENV: 'development', PORT: '8080', JWT_SECRET: 'supersecret', ENABLE_LOGS: '0' });

console.log('env:', fullEnv.require('NODE_ENV'));         // development
console.log('port:', fullEnv.requireNumber('PORT'));      // 8080
console.log('jwt:', fullEnv.require('JWT_SECRET'));       // supersecret
console.log('logs:', fullEnv.getBoolean('ENABLE_LOGS'));  // false
fullEnv.validate(); // no lanza
console.log('Configuración válida ✓');
TS,
        ];

        // ── Lección 9: Prisma CRUD ─────────────────────────────────────────
        if ($l = $lessons->get(9)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Implementa un repositorio CRUD genérico en memoria',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Prisma abstrae el acceso a base de datos. Para entender sus patrones, implementa `BaseRepository<T>` que replica la API de Prisma Client:
`findMany` (con `where` y `orderBy`), `findUnique`, `create`, `update`, `upsert` y `delete`.
MD,
            'starter_code' => <<<'TS'
type OrderDirection = 'asc' | 'desc';

interface FindManyArgs<T> {
  where?:   Partial<T>;
  orderBy?: { [K in keyof T]?: OrderDirection };
  skip?:    number;
  take?:    number;
}

// ─── BaseRepository ────────────────────────────────────────────────────────
class BaseRepository<T extends { id: number }> {
  protected db = new Map<number, T>();
  private   seq = 0;

  protected nextId(): number { return ++this.seq; }

  // Filtra, ordena y pagina. Para el filtro: cada par clave-valor debe coincidir exactamente.
  findMany(args: FindManyArgs<T> = {}): T[] {
    let result = [...this.db.values()];

    // 1. Aplica where
    if (args.where) {
      const where = args.where;
      result = result.filter(item =>
        (Object.keys(where) as (keyof T)[]).every(k => item[k] === where[k])
      );
    }

    // 2. Aplica orderBy (solo el primer campo)
    if (args.orderBy) {
      // Tu código aquí: obtén la clave y dirección, ordena result
    }

    // 3. Aplica skip y take
    const skip = args.skip ?? 0;
    // Tu código aquí
    return result;
  }

  findUnique(id: number): T | null {
    // Tu código aquí
    return null;
  }

  create(data: Omit<T, 'id'>): T {
    const id   = this.nextId();
    const item = { id, ...data } as T;
    this.db.set(id, item);
    return item;
  }

  update(id: number, data: Partial<Omit<T, 'id'>>): T | null {
    // Tu código aquí: si no existe, retorna null; si existe, fusiona y guarda
    return null;
  }

  // Si existe lo actualiza, si no lo crea
  upsert(id: number | null, data: Omit<T, 'id'>): T {
    // Tu código aquí
    return this.create(data);
  }

  delete(id: number): boolean {
    return this.db.delete(id);
  }

  count(where?: Partial<T>): number {
    return this.findMany({ where }).length;
  }
}

// ─── Pruebas ──────────────────────────────────────────────────────────────
interface Post { id: number; title: string; published: boolean; authorId: number; views: number; }

class PostRepository extends BaseRepository<Post> {
  createPost(title: string, authorId: number): Post {
    return this.create({ title, published: false, authorId, views: 0 });
  }
  publish(id: number): Post | null {
    return this.update(id, { published: true });
  }
}

const posts = new PostRepository();
posts.createPost('Intro a Node.js',  1);
posts.createPost('TypeScript Tips',  1);
posts.createPost('REST API Design',  2);
posts.createPost('Docker Basics',    2);
posts.createPost('JWT explicado',    1);
posts.publish(1);
posts.publish(3);

console.log('Total:', posts.count()); // 5
console.log('Publicados:', posts.count({ published: true })); // 2

const byAuthor1 = posts.findMany({ where: { authorId: 1 } });
console.log('Autor 1:', byAuthor1.map(p => p.title));
// ['Intro a Node.js', 'TypeScript Tips', 'JWT explicado']

const paged = posts.findMany({ skip: 1, take: 2 });
console.log('Paged:', paged.map(p => p.title)); // 2 posts a partir del índice 1

const updated = posts.update(2, { views: 150, published: true });
console.log('Updated views:', updated?.views); // 150

const unique = posts.findUnique(3);
console.log('findUnique:', unique?.title); // REST API Design

posts.delete(5);
console.log('Tras delete:', posts.count()); // 4
TS,
        ];

        // ── Lección 10: Prisma Relaciones ─────────────────────────────────
        if ($l = $lessons->get(10)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Consultas relacionales con include y aggregations',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Prisma permite cargar relaciones con `include` y calcular aggregaciones (`_count`, `_avg`).
Implementa `RelationalDB` con datos de **usuarios → posts → comentarios** y las funciones de consulta que replican estos patrones.
MD,
            'starter_code' => <<<'TS'
// ─── Tipos ────────────────────────────────────────────────────────────────
interface User    { id: number; name: string; email: string; }
interface Post    { id: number; title: string; published: boolean; authorId: number; }
interface Comment { id: number; body: string; postId: number; authorId: number; }

// ─── Datos de prueba ──────────────────────────────────────────────────────
const users: User[] = [
  { id: 1, name: 'Ana García',  email: 'ana@example.com'  },
  { id: 2, name: 'Bob Martín',  email: 'bob@example.com'  },
  { id: 3, name: 'Clara Ruiz',  email: 'clara@example.com' },
];

const posts: Post[] = [
  { id: 1, title: 'Intro Node.js',  published: true,  authorId: 1 },
  { id: 2, title: 'TypeScript Tips', published: true,  authorId: 1 },
  { id: 3, title: 'REST API',        published: false, authorId: 2 },
  { id: 4, title: 'Docker Basics',   published: true,  authorId: 2 },
];

const comments: Comment[] = [
  { id: 1, body: 'Muy útil!',     postId: 1, authorId: 2 },
  { id: 2, body: 'Excelente',     postId: 1, authorId: 3 },
  { id: 3, body: 'Gracias',       postId: 2, authorId: 3 },
  { id: 4, body: 'Muy claro',     postId: 4, authorId: 1 },
];

// ─── 1. findUsersWithPosts ────────────────────────────────────────────────
// Retorna cada usuario con sus posts (publicados si onlyPublished=true).
// Equivalente a: prisma.user.findMany({ include: { posts: { where: { published: true } } } })
function findUsersWithPosts(onlyPublished = false): Array<User & { posts: Post[] }> {
  // Tu código aquí
  return [];
}

// ─── 2. findPostWithComments ──────────────────────────────────────────────
// Retorna el post con sus comentarios y el autor de cada comentario incluido.
// Equivalente a: prisma.post.findUnique({ where: { id }, include: { comments: { include: { author: true } } } })
function findPostWithComments(postId: number): (Post & {
  author:   User;
  comments: Array<Comment & { author: User }>;
}) | null {
  // Tu código aquí
  return null;
}

// ─── 3. getPostStats ─────────────────────────────────────────────────────
// Retorna estadísticas de posts por usuario.
// Equivalente a: prisma.user.findMany({ include: { _count: { select: { posts: true } } } })
function getPostStats(): Array<{ userId: number; name: string; totalPosts: number; publishedPosts: number }> {
  // Tu código aquí
  return [];
}

// ─── Pruebas ──────────────────────────────────────────────────────────────
const withPosts = findUsersWithPosts(true);
console.log('Users con posts publicados:');
withPosts.forEach(u => console.log(`  ${u.name}: ${u.posts.map(p => p.title).join(', ')}`));
// Ana: Intro Node.js, TypeScript Tips
// Bob: Docker Basics
// Clara: (sin posts)

const postDetail = findPostWithComments(1);
if (postDetail) {
  console.log('\nPost:', postDetail.title, '| Autor:', postDetail.author.name);
  postDetail.comments.forEach(c =>
    console.log(`  Comentario de ${c.author.name}: ${c.body}`)
  );
}
// Post: Intro Node.js | Autor: Ana García
//   Comentario de Bob Martín: Muy útil!
//   Comentario de Clara Ruiz: Excelente

console.log('\nEstadísticas:');
getPostStats().forEach(s =>
  console.log(`  ${s.name}: ${s.totalPosts} total, ${s.publishedPosts} publicados`)
);
// Ana: 2 total, 2 publicados
// Bob: 2 total, 1 publicado
// Clara: 0 total, 0 publicados
TS,
        ];

        // ── Lección 11: Autenticación JWT ─────────────────────────────────
        if ($l = $lessons->get(11)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Construye y verifica tokens JWT sin librerías',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Un JWT tiene tres partes: `header.payload.signature`. Para entender cómo funciona antes de usar `jsonwebtoken`,
implementa `signToken`, `verifyToken` y el `TokenStore` para gestionar refresh tokens (lista negra y renovación).
MD,
            'starter_code' => <<<'TS'
// ─── Base64url ────────────────────────────────────────────────────────────
// (disponible en entornos modernos; usamos btoa/atob)
function b64encode(str: string): string {
  return btoa(str).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
}
function b64decode(str: string): string {
  const pad = str + '='.repeat((4 - str.length % 4) % 4);
  return atob(pad.replace(/-/g, '+').replace(/_/g, '/'));
}

// ─── Firma HMAC simple (NO usar en producción — solo didáctico) ───────────
// Usa un hash muy simplificado: suma de char codes XOR con la clave
function simpleSign(data: string, secret: string): string {
  let hash = 0;
  const combined = data + secret;
  for (let i = 0; i < combined.length; i++) {
    hash = ((hash << 5) - hash + combined.charCodeAt(i)) | 0;
  }
  return Math.abs(hash).toString(16).padStart(8, '0');
}

// ─── 1. signToken ─────────────────────────────────────────────────────────
interface TokenPayload {
  sub:   string;
  email: string;
  role:  string;
  iat?:  number;
  exp?:  number;
}

// Firma un JWT con el payload dado. Añade iat (now) y exp (now + expiresInSec).
function signToken(
  payload: Omit<TokenPayload, 'iat' | 'exp'>,
  secret: string,
  expiresInSec: number
): string {
  const header  = b64encode(JSON.stringify({ alg: 'HS256', typ: 'JWT' }));
  const now     = Math.floor(Date.now() / 1000);
  const claims: TokenPayload = { ...payload, iat: now, exp: now + expiresInSec };
  const pl      = b64encode(JSON.stringify(claims));
  // Tu código aquí: genera la firma y retorna `header.pl.firma`
  return '';
}

// ─── 2. verifyToken ───────────────────────────────────────────────────────
type VerifyResult =
  | { valid: true;  payload: TokenPayload }
  | { valid: false; reason: 'expired' | 'invalid_signature' | 'malformed' };

function verifyToken(token: string, secret: string): VerifyResult {
  // Tu código aquí:
  // 1. Divide por '.' y verifica que haya 3 partes
  // 2. Recalcula la firma y compara con la del token
  // 3. Parsea el payload y verifica exp
  return { valid: false, reason: 'malformed' };
}

// ─── 3. TokenStore ────────────────────────────────────────────────────────
// Gestiona refresh tokens: los guarda por userId, permite invalidar (logout)
// y renovar el access token a partir de un refresh válido.
class TokenStore {
  private refreshTokens = new Map<string, { userId: string; expiresAt: number }>();

  issue(userId: string, secret: string): { accessToken: string; refreshToken: string } {
    const accessToken  = signToken({ sub: userId, email: `${userId}@app.com`, role: 'user' }, secret, 900);
    const refreshToken = signToken({ sub: userId, email: `${userId}@app.com`, role: 'refresh' }, secret, 604800);
    this.refreshTokens.set(refreshToken, {
      userId,
      expiresAt: Math.floor(Date.now() / 1000) + 604800,
    });
    return { accessToken, refreshToken };
  }

  // Invalida el refresh token (logout)
  revoke(refreshToken: string): boolean {
    // Tu código aquí
    return false;
  }

  // Si el refresh token es válido, emite un nuevo access token
  refresh(refreshToken: string, secret: string): string | null {
    // Tu código aquí
    return null;
  }

  isRevoked(refreshToken: string): boolean {
    return !this.refreshTokens.has(refreshToken);
  }
}

// ─── Pruebas ──────────────────────────────────────────────────────────────
const SECRET = 'mi-secreto-super-seguro';

const token = signToken({ sub: '42', email: 'ana@example.com', role: 'admin' }, SECRET, 3600);
console.log('Token generado:', token.split('.').length === 3 ? '3 partes ✓' : '❌');

const result = verifyToken(token, SECRET);
console.log('Verificación:', result.valid); // true
if (result.valid) console.log('Payload:', result.payload.email, result.payload.role);

// Token expirado
const expired = signToken({ sub: '1', email: 'x@x.com', role: 'user' }, SECRET, -1);
const expResult = verifyToken(expired, SECRET);
console.log('Expirado:', !expResult.valid && expResult.reason); // 'expired'

// TokenStore
const store = new TokenStore();
const { accessToken, refreshToken } = store.issue('user-99', SECRET);
console.log('Refresh token emitido:', !store.isRevoked(refreshToken)); // true

const newAccess = store.refresh(refreshToken, SECRET);
console.log('Renovado:', newAccess !== null); // true

store.revoke(refreshToken);
console.log('Revocado:', store.isRevoked(refreshToken)); // true
console.log('Refresh tras revocar:', store.refresh(refreshToken, SECRET)); // null
TS,
        ];

        // ── Lección 12: Autorización RBAC ─────────────────────────────────
        if ($l = $lessons->get(12)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema de autorización por roles y permisos (RBAC)',
            'language'     => 'typescript',
            'description'  => <<<'MD'
La autorización responde: **¿qué puede hacer este usuario?**
Implementa un sistema **RBAC** con jerarquía de roles (`admin > editor > viewer`), permisos por recurso/acción y un `PermissionGuard` que evalúa si una petición está autorizada.
MD,
            'starter_code' => <<<'TS'
// ─── Tipos ────────────────────────────────────────────────────────────────
type Role    = 'admin' | 'editor' | 'viewer' | 'guest';
type Action  = 'create' | 'read' | 'update' | 'delete';
type Resource = 'post' | 'user' | 'comment' | 'media';

interface Permission { resource: Resource; action: Action; }
interface AuthUser   { id: number; role: Role; }

// ─── 1. ROLE_HIERARCHY ────────────────────────────────────────────────────
// Un admin hereda permisos de editor y viewer; editor hereda de viewer.
// Implementa como mapa: rol → array de roles que incluye (él mismo + inferiores).
const ROLE_HIERARCHY: Record<Role, Role[]> = {
  admin:  [],  // Tu código aquí: admin hereda de editor, viewer
  editor: [],  // editor hereda de viewer
  viewer: [],  // solo viewer
  guest:  [],  // solo guest
};

// ─── 2. PERMISSIONS ───────────────────────────────────────────────────────
// Define qué permisos tiene cada rol BASE (sin herencia).
// admin:  todo sobre user; editor: CRUD sobre post, CRUD sobre comment, CRUD sobre media
// viewer: read sobre post, comment; guest: read sobre post
const PERMISSIONS: Record<Role, Permission[]> = {
  admin:  [],  // Tu código aquí
  editor: [],
  viewer: [],
  guest:  [],
};

// ─── 3. can ───────────────────────────────────────────────────────────────
// Verifica si un usuario puede ejecutar `action` sobre `resource`.
// Tiene en cuenta la jerarquía: si es admin, incluye los permisos de editor y viewer.
function can(user: AuthUser, action: Action, resource: Resource): boolean {
  const roles = ROLE_HIERARCHY[user.role] ?? [user.role];
  // Tu código aquí: junta todos los permisos de los roles en `roles`
  // y verifica si alguno coincide con action+resource
  return false;
}

// ─── 4. PermissionGuard ───────────────────────────────────────────────────
// Retorna { allowed: true } o { allowed: false, reason: string }
interface GuardResult { allowed: boolean; reason?: string; }

function permissionGuard(user: AuthUser, action: Action, resource: Resource): GuardResult {
  // Tu código aquí
  return { allowed: false, reason: 'No autorizado' };
}

// ─── 5. Ownership check ───────────────────────────────────────────────────
// Un viewer puede editar su propio comentario aunque no tenga el permiso global.
// Retorna true si el user es admin, editor, o es el dueño del recurso.
function canEditOwn(user: AuthUser, ownerId: number): boolean {
  // Tu código aquí
  return false;
}

// ─── Pruebas ──────────────────────────────────────────────────────────────
const admin:  AuthUser = { id: 1, role: 'admin'  };
const editor: AuthUser = { id: 2, role: 'editor' };
const viewer: AuthUser = { id: 3, role: 'viewer' };
const guest:  AuthUser = { id: 4, role: 'guest'  };

console.log('admin  → delete user:   ', can(admin,  'delete', 'user'));    // true
console.log('editor → create post:   ', can(editor, 'create', 'post'));    // true
console.log('editor → delete user:   ', can(editor, 'delete', 'user'));    // false
console.log('viewer → read post:     ', can(viewer, 'read',   'post'));    // true
console.log('viewer → update post:   ', can(viewer, 'update', 'post'));    // false
console.log('guest  → read post:     ', can(guest,  'read',   'post'));    // true
console.log('guest  → create comment:', can(guest,  'create', 'comment')); // false

const g = permissionGuard(viewer, 'delete', 'post');
console.log('guard:', g.allowed, g.reason); // false, 'No autorizado' (o similar)

console.log('viewer edita propio (id 3):', canEditOwn(viewer, 3)); // true
console.log('viewer edita ajeno  (id 9):', canEditOwn(viewer, 9)); // false
console.log('editor edita ajeno  (id 9):', canEditOwn(editor, 9)); // true
TS,
        ];

        // ── Lección 13: Errores y Logging ─────────────────────────────────
        if ($l = $lessons->get(13)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Jerarquía de errores y logger estructurado',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Un buen sistema de errores **comunica exactamente qué salió mal** con un código HTTP y un código de aplicación.
Implementa la jerarquía `AppError → ValidationError / NotFoundError / UnauthorizedError`, el `errorSerializer` y un `Logger` estructurado con niveles.
MD,
            'starter_code' => <<<'TS'
// ─── 1. AppError y subclases ──────────────────────────────────────────────
class AppError extends Error {
  constructor(
    public readonly message:    string,
    public readonly statusCode: number,
    public readonly code:       string,
    public readonly details?:   unknown
  ) {
    super(message);
    this.name = new.target.name;
  }

  isOperational(): boolean { return true; } // errores esperados vs bugs
}

class ValidationError extends AppError {
  constructor(details: Array<{ field: string; message: string }>) {
    // Tu código aquí: statusCode=422, code='VALIDATION_ERROR'
    super('', 0, '');
  }
}

class NotFoundError extends AppError {
  constructor(resource: string, id: number | string) {
    // Tu código aquí: statusCode=404, code='NOT_FOUND'
    // message: '${resource} con id ${id} no encontrado'
    super('', 0, '');
  }
}

class UnauthorizedError extends AppError {
  constructor(reason = 'No autenticado') {
    // Tu código aquí: statusCode=401, code='UNAUTHORIZED'
    super('', 0, '');
  }
}

class ForbiddenError extends AppError {
  constructor(action = 'realizar esta acción') {
    // Tu código aquí: statusCode=403, code='FORBIDDEN'
    super('', 0, '');
  }
}

// ─── 2. errorSerializer ───────────────────────────────────────────────────
// Convierte cualquier error en un objeto JSON-serializable listo para HTTP.
// Si es AppError: incluye statusCode, code, message, details (si los hay).
// Si es Error nativo: statusCode=500, code='INTERNAL_ERROR', message genérico.
interface SerializedError {
  statusCode: number;
  code:       string;
  message:    string;
  details?:   unknown;
}

function serializeError(err: unknown): SerializedError {
  // Tu código aquí
  return { statusCode: 500, code: 'INTERNAL_ERROR', message: 'Error interno' };
}

// ─── 3. Logger estructurado ───────────────────────────────────────────────
type LogLevel = 'debug' | 'info' | 'warn' | 'error';

const LOG_LEVELS: Record<LogLevel, number> = { debug: 0, info: 1, warn: 2, error: 3 };

class Logger {
  constructor(
    private readonly context: string,
    private readonly minLevel: LogLevel = 'info'
  ) {}

  private log(level: LogLevel, message: string, meta?: Record<string, unknown>): void {
    if (LOG_LEVELS[level] < LOG_LEVELS[this.minLevel]) return;

    const entry = {
      timestamp: new Date().toISOString(),
      level,
      context:   this.context,
      message,
      ...meta,
    };
    // En producción esto iría a Pino/stdout en JSON
    console.log(JSON.stringify(entry));
  }

  debug(msg: string, meta?: Record<string, unknown>): void { this.log('debug', msg, meta); }
  info (msg: string, meta?: Record<string, unknown>): void { this.log('info',  msg, meta); }
  warn (msg: string, meta?: Record<string, unknown>): void { this.log('warn',  msg, meta); }
  error(msg: string, meta?: Record<string, unknown>): void { this.log('error', msg, meta); }

  // Loguea un AppError con su código y detalles
  logError(err: unknown): void {
    // Tu código aquí: si es AppError usa warn para errores operacionales y error para el resto
    this.error('Unhandled error', { error: String(err) });
  }
}

// ─── Pruebas ──────────────────────────────────────────────────────────────
const logger = new Logger('UserService', 'debug');

const notFound = new NotFoundError('User', 42);
console.log('NotFoundError:', notFound.statusCode, notFound.code, notFound.message);
// 404, NOT_FOUND, 'User con id 42 no encontrado'

const valError = new ValidationError([
  { field: 'email', message: 'Email inválido' },
  { field: 'name',  message: 'Requerido' },
]);
console.log('ValidationError:', valError.statusCode, valError.code);
// 422, VALIDATION_ERROR

console.log('Serialized:', JSON.stringify(serializeError(notFound)));
console.log('Serialized native:', JSON.stringify(serializeError(new Error('ups'))));
// { statusCode:500, code:'INTERNAL_ERROR', message:'Error interno' }

logger.info('Usuario creado', { userId: 1, email: 'ana@example.com' });
logger.warn('Intento de acceso denegado', { ip: '10.0.0.1' });
logger.logError(new UnauthorizedError('Token expirado'));
logger.logError(notFound);
TS,
        ];

        // ── Lección 14: Testing ────────────────────────────────────────────
        if ($l = $lessons->get(14)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Tests unitarios con mocks e inyección de dependencias',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Los buenos tests son **aislados**: no dependen de la base de datos ni de servicios externos.
Implementa un `UserService` con inyección de dependencias, un `MockUserRepository` y escribe los tests con el mini-framework incluido.
MD,
            'starter_code' => <<<'TS'
// ─── Mini framework de testing ────────────────────────────────────────────
let passed = 0, failed = 0;
function describe(name: string, fn: () => void) { console.log(`\n▶ ${name}`); fn(); }
function it(desc: string, fn: () => void) {
  try   { fn(); passed++; console.log(`  ✅ ${desc}`); }
  catch (e: any) { failed++; console.log(`  ❌ ${desc}\n     ${e.message}`); }
}
function expect<T>(actual: T) {
  return {
    toBe:        (e: T)   => { if (actual !== e) throw new Error(`esperaba ${JSON.stringify(e)}, recibí ${JSON.stringify(actual)}`); },
    toEqual:     (e: T)   => { if (JSON.stringify(actual) !== JSON.stringify(e)) throw new Error(`esperaba ${JSON.stringify(e)}, recibí ${JSON.stringify(actual)}`); },
    toBeTruthy:  ()       => { if (!actual) throw new Error(`esperaba truthy, recibí ${actual}`); },
    toBeFalsy:   ()       => { if (actual)  throw new Error(`esperaba falsy, recibí ${actual}`);  },
    toThrow:     ()       => { throw new Error('usa expect(() => fn()).toThrow()'); },
    toContain:   (e: any) => { if (!JSON.stringify(actual).includes(JSON.stringify(e).slice(1,-1))) throw new Error(`no contiene ${e}`); },
  };
}
function expectThrows(fn: () => void, expectedMsg?: string) {
  try { fn(); throw new Error('No lanzó error'); }
  catch (e: any) {
    if (e.message === 'No lanzó error') throw e;
    if (expectedMsg && !e.message.includes(expectedMsg))
      throw new Error(`esperaba mensaje "${expectedMsg}", recibí "${e.message}"`);
  }
}

// ─── Interfaz del repositorio (para poder mockearla) ─────────────────────
interface IUserRepository {
  findById(id: number): User | null;
  findByEmail(email: string): User | null;
  create(data: Omit<User, 'id' | 'createdAt'>): User;
  count(): number;
}

interface User { id: number; name: string; email: string; role: string; createdAt: Date; }

// ─── MockUserRepository ───────────────────────────────────────────────────
class MockUserRepository implements IUserRepository {
  private store: User[] = [];
  private seq = 0;

  // Precarga usuarios para los tests
  seed(users: Omit<User, 'id' | 'createdAt'>[]): void {
    users.forEach(u => this.create(u));
  }

  findById(id: number): User | null {
    // Tu código aquí
    return null;
  }

  findByEmail(email: string): User | null {
    // Tu código aquí
    return null;
  }

  create(data: Omit<User, 'id' | 'createdAt'>): User {
    const user: User = { ...data, id: ++this.seq, createdAt: new Date() };
    this.store.push(user);
    return user;
  }

  count(): number { return this.store.length; }
}

// ─── UserService ──────────────────────────────────────────────────────────
class UserService {
  constructor(private repo: IUserRepository) {}

  // Crea un usuario. Lanza 'El email ya está registrado' si el email existe.
  register(name: string, email: string, role = 'user'): User {
    // Tu código aquí
    return {} as User;
  }

  // Retorna el usuario o lanza 'Usuario no encontrado'
  getById(id: number): User {
    // Tu código aquí
    return {} as User;
  }

  isAdmin(id: number): boolean {
    const user = this.repo.findById(id);
    return user?.role === 'admin';
  }
}

// ─── Tests ────────────────────────────────────────────────────────────────
const repo    = new MockUserRepository();
const service = new UserService(repo);

repo.seed([
  { name: 'Ana', email: 'ana@example.com', role: 'admin' },
  { name: 'Bob', email: 'bob@example.com', role: 'user'  },
]);

describe('UserService.register', () => {
  it('crea un usuario nuevo', () => {
    const u = service.register('Clara', 'clara@example.com');
    expect(u.name).toBe('Clara');
    expect(u.email).toBe('clara@example.com');
    expect(u.role).toBe('user');
    expect(repo.count()).toBe(3);
  });

  it('lanza si el email ya existe', () => {
    expectThrows(() => service.register('Dup', 'ana@example.com'), 'ya está registrado');
  });
});

describe('UserService.getById', () => {
  it('retorna el usuario correcto', () => {
    const u = service.getById(1);
    expect(u.name).toBe('Ana');
  });

  it('lanza si no existe', () => {
    expectThrows(() => service.getById(999), 'no encontrado');
  });
});

describe('UserService.isAdmin', () => {
  it('detecta admin', ()  => { expect(service.isAdmin(1)).toBeTruthy(); });
  it('detecta no admin', () => { expect(service.isAdmin(2)).toBeFalsy();  });
  it('no existente → false', () => { expect(service.isAdmin(999)).toBeFalsy(); });
});

console.log(`\nResultado: ${passed}/${passed + failed} tests pasados`);
TS,
        ];

        // ── Lección 15: Subida de archivos ────────────────────────────────
        if ($l = $lessons->get(15)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Valida y procesa metadatos de archivos subidos',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Multer filtra archivos antes de guardarlos. Para entender la lógica, implementa `FileValidator` con reglas de **tamaño máximo**, **extensiones permitidas** y **tipo MIME**, y la función `sanitizeFilename` que elimina caracteres peligrosos.
MD,
            'starter_code' => <<<'TS'
// ─── Tipos ────────────────────────────────────────────────────────────────
interface UploadedFile {
  originalname: string;
  mimetype:     string;
  size:         number;  // bytes
  buffer?:      Uint8Array;
}

interface ValidationResult {
  valid:    boolean;
  errors:   string[];
  filename: string;  // el nombre sanitizado
}

// ─── 1. sanitizeFilename ──────────────────────────────────────────────────
// Elimina caracteres peligrosos del nombre del archivo.
// - Reemplaza espacios por guiones bajos
// - Elimina caracteres que no sean alfanuméricos, guiones, puntos o guiones bajos
// - Convierte a minúsculas
// - Si queda vacío, retorna 'archivo_sin_nombre'
function sanitizeFilename(original: string): string {
  // Tu código aquí
  return 'archivo_sin_nombre';
}

// ─── 2. FileValidator ─────────────────────────────────────────────────────
interface FileRules {
  maxSizeBytes:      number;
  allowedMimeTypes:  string[];
  allowedExtensions: string[];  // con punto: ['.jpg', '.png']
}

class FileValidator {
  constructor(private rules: FileRules) {}

  validate(file: UploadedFile): ValidationResult {
    const errors: string[] = [];
    const filename = sanitizeFilename(file.originalname);
    const ext = filename.substring(filename.lastIndexOf('.')).toLowerCase();

    // 1. Verifica el tamaño
    // Tu código aquí

    // 2. Verifica el tipo MIME
    // Tu código aquí

    // 3. Verifica la extensión
    // Tu código aquí

    return { valid: errors.length === 0, errors, filename };
  }

  // Retorna un resumen de las reglas como string descriptivo
  describe(): string {
    const mb = (this.rules.maxSizeBytes / 1024 / 1024).toFixed(1);
    return `Máx: ${mb} MB | Tipos: ${this.rules.allowedMimeTypes.join(', ')} | Ext: ${this.rules.allowedExtensions.join(', ')}`;
  }
}

// ─── 3. ImageValidator y DocumentValidator ────────────────────────────────
const imageValidator = new FileValidator({
  maxSizeBytes:      5 * 1024 * 1024, // 5 MB
  allowedMimeTypes:  ['image/jpeg', 'image/png', 'image/webp'],
  allowedExtensions: ['.jpg', '.jpeg', '.png', '.webp'],
});

const docValidator = new FileValidator({
  maxSizeBytes:      10 * 1024 * 1024, // 10 MB
  allowedMimeTypes:  ['application/pdf', 'text/plain'],
  allowedExtensions: ['.pdf', '.txt'],
});

// ─── Pruebas ──────────────────────────────────────────────────────────────
console.log('sanitize:');
console.log(sanitizeFilename('Mi Foto de Vacaciones.JPG'));   // mi_foto_de_vacaciones.jpg
console.log(sanitizeFilename('../../../etc/passwd'));          // etcpasswd (o similar sin chars peligrosos)
console.log(sanitizeFilename(''));                             // archivo_sin_nombre
console.log(sanitizeFilename('report 2026 final.pdf'));       // report_2026_final.pdf

console.log('\nimageValidator:', imageValidator.describe());

const validImage: UploadedFile = { originalname: 'avatar.png', mimetype: 'image/png', size: 2 * 1024 * 1024 };
const r1 = imageValidator.validate(validImage);
console.log('\nImagen válida:', r1.valid, r1.filename); // true, 'avatar.png'

const tooBig: UploadedFile = { originalname: 'foto.jpg', mimetype: 'image/jpeg', size: 8 * 1024 * 1024 };
const r2 = imageValidator.validate(tooBig);
console.log('Muy grande:', r2.valid, r2.errors); // false, ['Tamaño...']

const wrongType: UploadedFile = { originalname: 'virus.exe', mimetype: 'application/octet-stream', size: 1000 };
const r3 = imageValidator.validate(wrongType);
console.log('Tipo inválido:', r3.valid, r3.errors.length, 'errores'); // false, 2 errores (mime + ext)

const pdf: UploadedFile = { originalname: 'Manual del Usuario.PDF', mimetype: 'application/pdf', size: 3 * 1024 * 1024 };
const r4 = docValidator.validate(pdf);
console.log('PDF válido:', r4.valid, r4.filename); // true, 'manual_del_usuario.pdf'
TS,
        ];

        // ── Lección 16: WebSockets ─────────────────────────────────────────
        if ($l = $lessons->get(16)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema pub/sub con salas y broadcasting',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Socket.io se basa en un sistema de **eventos y salas**. Para entender sus patrones, implementa `EventBus` (pub/sub), `Room` (grupo de clientes) y `ChatServer` que combina ambos para simular un chat en tiempo real.
MD,
            'starter_code' => <<<'TS'
// ─── 1. EventBus ──────────────────────────────────────────────────────────
// Pub/sub simple: on, off, emit y once (solo escucha una vez).
type Listener<T = unknown> = (data: T) => void;

class EventBus {
  private listeners = new Map<string, Set<Listener>>();

  on<T>(event: string, listener: Listener<T>): () => void {
    // Tu código aquí. Retorna función para desuscribirse.
    return () => {};
  }

  once<T>(event: string, listener: Listener<T>): void {
    // Escucha solo la primera emisión, luego se auto-desuscribe
    // Tu código aquí
  }

  emit<T>(event: string, data: T): void {
    // Tu código aquí: llama a todos los listeners de ese evento
  }

  off(event: string, listener: Listener): void {
    this.listeners.get(event)?.delete(listener);
  }

  listenerCount(event: string): number {
    return this.listeners.get(event)?.size ?? 0;
  }
}

// ─── 2. Room ──────────────────────────────────────────────────────────────
// Abstracción de "sala" de Socket.io: gestiona miembros y mensajes.
interface ChatMessage { from: string; text: string; timestamp: number; }
interface SocketClient { id: string; username: string; }

class Room {
  private members = new Map<string, SocketClient>();
  public  messages: ChatMessage[] = [];

  constructor(public readonly name: string) {}

  join(client: SocketClient): void {
    // Tu código aquí
  }

  leave(clientId: string): SocketClient | undefined {
    // Elimina y retorna el cliente
    // Tu código aquí
    return undefined;
  }

  // Envía un mensaje a todos EXCEPTO al remitente (broadcast)
  broadcast(from: string, text: string, emitFn: (clientId: string, msg: ChatMessage) => void): void {
    const msg: ChatMessage = { from, text, timestamp: Date.now() };
    this.messages.push(msg);
    // Tu código aquí: itera members, llama emitFn para todos excepto `from`
  }

  getMemberCount(): number { return this.members.size; }
  getMembers(): SocketClient[] { return [...this.members.values()]; }
}

// ─── 3. ChatServer ────────────────────────────────────────────────────────
class ChatServer {
  private rooms   = new Map<string, Room>();
  private bus     = new EventBus();
  private log: string[] = [];

  private emit(clientId: string, msg: ChatMessage): void {
    this.log.push(`→ ${clientId}: [${msg.from}] ${msg.text}`);
    this.bus.emit(`client:${clientId}`, msg);
  }

  join(client: SocketClient, roomName: string): void {
    if (!this.rooms.has(roomName)) this.rooms.set(roomName, new Room(roomName));
    const room = this.rooms.get(roomName)!;
    room.join(client);
    // Notifica a todos en la sala que alguien entró (broadcast)
    room.broadcast('Sistema', `${client.username} se unió`, (id, m) => this.emit(id, m));
  }

  send(fromId: string, roomName: string, text: string): void {
    const room = this.rooms.get(roomName);
    if (!room) return;
    const client = room.getMembers().find(m => m.id === fromId);
    if (!client) return;
    room.broadcast(client.username, text, (id, m) => this.emit(id, m));
  }

  leave(clientId: string, roomName: string): void {
    const room = this.rooms.get(roomName);
    const client = room?.leave(clientId);
    if (client) {
      room!.broadcast('Sistema', `${client.username} salió`, (id, m) => this.emit(id, m));
    }
  }

  getLog(): string[] { return [...this.log]; }
  getRoom(name: string): Room | undefined { return this.rooms.get(name); }
}

// ─── Pruebas ──────────────────────────────────────────────────────────────
// EventBus
const bus = new EventBus();
const msgs: string[] = [];

const unsub = bus.on<string>('msg', m => msgs.push(`on: ${m}`));
bus.once<string>('msg', m => msgs.push(`once: ${m}`));

bus.emit('msg', 'hola');
bus.emit('msg', 'mundo');  // once ya no escucha

console.log('EventBus msgs:', msgs);
// ['on: hola', 'once: hola', 'on: mundo']

unsub();
bus.emit('msg', 'ignorado');
console.log('Tras unsub:', msgs.length); // sigue en 3

// ChatServer
const chat = new ChatServer();
const ana  = { id: 'a1', username: 'Ana'  };
const bob  = { id: 'b1', username: 'Bob'  };
const carl = { id: 'c1', username: 'Carl' };

chat.join(ana,  'general');
chat.join(bob,  'general');
chat.join(carl, 'general');

chat.send('a1', 'general', 'Hola a todos!');
chat.leave('b1', 'general');

console.log('\nChatServer log:');
chat.getLog().forEach(l => console.log(' ', l));
console.log('Miembros en general:', chat.getRoom('general')?.getMemberCount()); // 2
TS,
        ];

        // ── Lección 17: Tareas programadas ────────────────────────────────
        if ($l = $lessons->get(17)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Cola de tareas con prioridad y reintentos',
            'language'     => 'typescript',
            'description'  => <<<'MD'
BullMQ es una cola de trabajos con reintentos, prioridades y monitoreo. Para entender sus conceptos,
implementa `JobQueue` con jobs priorizados, estados (`pending → active → completed/failed`) y lógica de reintentos con backoff.
MD,
            'starter_code' => <<<'TS'
type JobStatus = 'pending' | 'active' | 'completed' | 'failed';

interface Job<T = unknown> {
  id:        string;
  name:      string;
  data:      T;
  priority:  number;   // mayor número = mayor prioridad
  attempts:  number;   // intentos realizados
  maxRetries: number;
  status:    JobStatus;
  result?:   unknown;
  error?:    string;
  createdAt: number;
}

// ─── JobQueue ─────────────────────────────────────────────────────────────
class JobQueue<T = unknown> {
  private pending:   Job<T>[] = [];
  private completed: Job<T>[] = [];
  private failed:    Job<T>[] = [];
  private idSeq = 0;

  add(name: string, data: T, options: { priority?: number; maxRetries?: number } = {}): Job<T> {
    const job: Job<T> = {
      id:         `job-${++this.idSeq}`,
      name,
      data,
      priority:   options.priority  ?? 0,
      maxRetries: options.maxRetries ?? 3,
      attempts:   0,
      status:     'pending',
      createdAt:  Date.now(),
    };
    // Inserta manteniendo el orden por prioridad descendente
    // Tu código aquí
    this.pending.push(job);
    return job;
  }

  // Extrae el job de mayor prioridad de la cola pending
  private dequeue(): Job<T> | null {
    // Tu código aquí
    return this.pending.shift() ?? null;
  }

  // Procesa el siguiente job usando `processor`. Si falla y quedan reintentos,
  // lo reencola con la misma prioridad. Si se agotan los reintentos, va a failed.
  async processNext(
    processor: (job: Job<T>) => Promise<unknown>
  ): Promise<Job<T> | null> {
    const job = this.dequeue();
    if (!job) return null;

    job.status   = 'active';
    job.attempts += 1;

    try {
      job.result = await processor(job);
      job.status = 'completed';
      this.completed.push(job);
    } catch (err: any) {
      job.error = err.message;
      if (job.attempts < job.maxRetries) {
        // Reencola (lo vuelve a poner en pending)
        job.status = 'pending';
        // Tu código aquí: vuelve a insertar en la cola
      } else {
        job.status = 'failed';
        this.failed.push(job);
      }
    }

    return job;
  }

  // Procesa todos los jobs pendientes secuencialmente
  async processAll(processor: (job: Job<T>) => Promise<unknown>): Promise<void> {
    while (this.pending.length > 0) {
      await this.processNext(processor);
    }
  }

  getStats() {
    return {
      pending:   this.pending.length,
      completed: this.completed.length,
      failed:    this.failed.length,
    };
  }

  getPending():   Job<T>[] { return [...this.pending];   }
  getCompleted(): Job<T>[] { return [...this.completed]; }
  getFailed():    Job<T>[] { return [...this.failed];    }
}

// ─── Pruebas ──────────────────────────────────────────────────────────────
interface EmailJob { to: string; subject: string; }

const queue = new JobQueue<EmailJob>();

queue.add('welcome-email',  { to: 'ana@example.com',   subject: 'Bienvenida'       }, { priority: 1 });
queue.add('alert-email',    { to: 'admin@example.com', subject: 'Alerta crítica'   }, { priority: 10 });
queue.add('newsletter',     { to: 'users@example.com', subject: 'Newsletter Mayo'  }, { priority: 0  });
queue.add('retry-test',     { to: 'fail@example.com',  subject: 'Fallará siempre'  }, { priority: 5, maxRetries: 2 });

console.log('Pendientes antes:', queue.getStats().pending); // 4

// Verificar orden por prioridad
const pendingNames = queue.getPending().map(j => j.name);
console.log('Orden de prioridad:', pendingNames);
// ['alert-email'(10), 'retry-test'(5), 'welcome-email'(1), 'newsletter'(0)]

let callCount = 0;
async function emailProcessor(job: Job<EmailJob>): Promise<string> {
  callCount++;
  if (job.data.to === 'fail@example.com') {
    throw new Error('SMTP connection refused');
  }
  return `sent:${job.id}`;
}

(async () => {
  await queue.processAll(emailProcessor);
  const stats = queue.getStats();
  console.log('Stats:', stats); // { pending: 0, completed: 3, failed: 1 }

  const failed = queue.getFailed();
  console.log('Failed:', failed[0]?.name, '| attempts:', failed[0]?.attempts);
  // retry-test | 2 (maxRetries)

  console.log('Total calls (con reintentos):', callCount);
  // 3 (success) + 2 (retry-test reintentos) = 5
})();
TS,
        ];

        // ── Lección 18: Performance y Redis ───────────────────────────────
        if ($l = $lessons->get(18)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Implementa caché LRU con TTL y patron cache-aside',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Redis implementa una caché **LRU** (Least Recently Used) con TTL. Para entender el algoritmo,
implementa `LRUCache<K, V>` que evicta el elemento menos reciente cuando se llena,
y el decorador `withCache` que aplica el patrón **cache-aside** a cualquier función async.
MD,
            'starter_code' => <<<'TS'
// ─── LRUCache ─────────────────────────────────────────────────────────────
// Usa un Map que mantiene el orden de inserción para simular LRU.
// Al acceder (get) a un elemento: lo mueve al "más reciente".
// Al insertar (set) con la caché llena: elimina el "menos reciente".
interface CacheEntry<V> { value: V; expiresAt: number | null; }

class LRUCache<K, V> {
  private store = new Map<K, CacheEntry<V>>();

  constructor(private readonly capacity: number) {}

  get(key: K): V | undefined {
    const entry = this.store.get(key);
    if (!entry) return undefined;

    // Verifica TTL
    if (entry.expiresAt !== null && Date.now() > entry.expiresAt) {
      this.store.delete(key);
      return undefined;
    }

    // Mueve al final del Map (más reciente)
    // Tu código aquí: elimina y vuelve a insertar
    return entry.value;
  }

  // ttl en milisegundos, null = sin expiración
  set(key: K, value: V, ttl: number | null = null): void {
    // Si ya existe, actualízalo (y muévelo al final)
    if (this.store.has(key)) this.store.delete(key);

    // Si estamos al límite, elimina el menos reciente (primero del Map)
    if (this.store.size >= this.capacity) {
      // Tu código aquí: obtén y elimina la primera clave del Map
    }

    const expiresAt = ttl !== null ? Date.now() + ttl : null;
    this.store.set(key, { value, expiresAt });
  }

  has(key: K): boolean {
    return this.get(key) !== undefined;
  }

  delete(key: K): boolean { return this.store.delete(key); }
  size(): number          { return this.store.size; }
  clear(): void           { this.store.clear(); }

  // Retorna las claves en orden de "menos reciente" a "más reciente"
  keys(): K[] { return [...this.store.keys()]; }
}

// ─── withCache (cache-aside) ──────────────────────────────────────────────
// Wraps una función async. En el primer call guarda el resultado en caché.
// En calls siguientes (mientras no expire), retorna el valor cacheado.
function withCache<Args extends unknown[], R>(
  fn: (...args: Args) => Promise<R>,
  cache: LRUCache<string, R>,
  ttl: number | null = null,
  keyFn?: (...args: Args) => string
): (...args: Args) => Promise<R> {
  return async (...args: Args): Promise<R> => {
    const key = keyFn ? keyFn(...args) : JSON.stringify(args);
    // Tu código aquí:
    // 1. Si está en caché, retorna el valor cacheado
    // 2. Si no, llama a fn, guarda en caché y retorna el resultado
    return fn(...args);
  };
}

// ─── Pruebas ──────────────────────────────────────────────────────────────
const cache = new LRUCache<string, number>(3);

cache.set('a', 1);
cache.set('b', 2);
cache.set('c', 3);
console.log('LRU orden inicial:', cache.keys()); // ['a', 'b', 'c']

cache.get('a'); // accede a 'a' → lo mueve al final
console.log('Tras get(a):', cache.keys()); // ['b', 'c', 'a']

cache.set('d', 4); // evicta 'b' (menos reciente)
console.log('Tras set(d):', cache.keys()); // ['c', 'a', 'd']
console.log('b fue evictado:', cache.get('b')); // undefined

// TTL
const shortCache = new LRUCache<string, string>(5);
shortCache.set('token', 'abc123', 50); // expira en 50ms
console.log('Token antes:', shortCache.get('token')); // 'abc123'

// withCache
let fetchCount = 0;
async function fetchUserName(id: number): Promise<string> {
  fetchCount++;
  return `Usuario ${id}`;
}

(async () => {
  const userCache  = new LRUCache<string, string>(10);
  const cachedFetch = withCache(fetchUserName, userCache, 5000, (id) => `user:${id}`);

  const r1 = await cachedFetch(42);
  const r2 = await cachedFetch(42); // cache hit
  const r3 = await cachedFetch(99); // cache miss

  console.log('r1:', r1, 'r2:', r2, 'r3:', r3);
  console.log('fetchCount (sin cache sería 3):', fetchCount); // 2 (42 una vez, 99 una vez)
  console.log('Cache size:', userCache.size()); // 2
})();
TS,
        ];

        // ── Lección 19: Docker y Deploy ───────────────────────────────────
        if ($l = $lessons->get(19)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Health checks y configuración de producción',
            'language'     => 'typescript',
            'description'  => <<<'MD'
En producción, el orquestador necesita saber si tu app está viva y lista para recibir tráfico.
Implementa `HealthChecker` con checks registrables, `AppConfig` que valida el entorno y el patrón de **graceful shutdown** que espera a que las peticiones activas terminen.
MD,
            'starter_code' => <<<'TS'
// ─── 1. HealthChecker ─────────────────────────────────────────────────────
type HealthStatus = 'healthy' | 'degraded' | 'unhealthy';

interface HealthCheck {
  name:    string;
  check:   () => Promise<{ status: HealthStatus; message?: string }>;
  critical: boolean;  // si falla → estado global = unhealthy
}

interface HealthReport {
  status:    HealthStatus;
  timestamp: string;
  checks:    Record<string, { status: HealthStatus; message?: string }>;
}

class HealthChecker {
  private checks: HealthCheck[] = [];

  register(check: HealthCheck): this {
    this.checks.push(check);
    return this;
  }

  // Ejecuta todos los checks en PARALELO y calcula el estado global:
  // - Si alguno crítico falla → 'unhealthy'
  // - Si alguno no crítico falla → 'degraded'
  // - Todo bien → 'healthy'
  async getReport(): Promise<HealthReport> {
    const results = await Promise.allSettled(
      this.checks.map(async c => ({ name: c.name, critical: c.critical, ...(await c.check()) }))
    );

    const checksMap: Record<string, { status: HealthStatus; message?: string }> = {};
    let globalStatus: HealthStatus = 'healthy';

    for (const r of results) {
      if (r.status === 'fulfilled') {
        const { name, status, message, critical } = r.value;
        checksMap[name] = { status, message };
        // Tu código aquí: actualiza globalStatus según critical y status
      } else {
        // El check lanzó una excepción
        // Tu código aquí: márcalo como unhealthy
      }
    }

    return { status: globalStatus, timestamp: new Date().toISOString(), checks: checksMap };
  }
}

// ─── 2. AppConfig ─────────────────────────────────────────────────────────
interface ProductionConfig {
  port:       number;
  nodeEnv:    string;
  dbUrl:      string;
  jwtSecret:  string;
  redisUrl:   string;
  logLevel:   'debug' | 'info' | 'warn' | 'error';
}

function loadConfig(env: Record<string, string | undefined>): ProductionConfig {
  const required = ['PORT', 'NODE_ENV', 'DATABASE_URL', 'JWT_SECRET'];
  const missing  = required.filter(k => !env[k]);

  if (missing.length > 0) {
    throw new Error(`Config inválida. Faltan: ${missing.join(', ')}`);
  }

  const logLevels = ['debug', 'info', 'warn', 'error'];
  const rawLevel  = env['LOG_LEVEL'] ?? 'info';

  return {
    port:      parseInt(env['PORT']!),
    nodeEnv:   env['NODE_ENV']!,
    dbUrl:     env['DATABASE_URL']!,
    jwtSecret: env['JWT_SECRET']!,
    redisUrl:  env['REDIS_URL'] ?? 'redis://localhost:6379',
    logLevel:  (logLevels.includes(rawLevel) ? rawLevel : 'info') as ProductionConfig['logLevel'],
  };
}

// ─── 3. GracefulShutdown ──────────────────────────────────────────────────
// Simula el apagado elegante: espera a que las peticiones activas terminen
// antes de cerrar, con un timeout máximo.
class GracefulShutdown {
  private active  = 0;
  private closing = false;
  private log: string[] = [];

  // Incrementa el contador de peticiones activas
  requestStarted():  void { if (!this.closing) this.active++; }
  // Decrementa el contador
  requestFinished(): void { if (this.active > 0) this.active--; }

  // Inicia el apagado: espera hasta timeoutMs a que active llegue a 0
  async shutdown(timeoutMs: number): Promise<'clean' | 'timeout'> {
    this.closing = true;
    this.log.push(`[shutdown] iniciado, ${this.active} petición(es) activa(s)`);

    const deadline = Date.now() + timeoutMs;

    while (this.active > 0 && Date.now() < deadline) {
      await new Promise(r => setTimeout(r, 50));
    }

    if (this.active > 0) {
      this.log.push(`[shutdown] timeout! ${this.active} petición(es) sin terminar`);
      return 'timeout';
    }

    this.log.push('[shutdown] limpio ✓');
    return 'clean';
  }

  getLog(): string[] { return this.log; }
}

// ─── Pruebas ──────────────────────────────────────────────────────────────
// AppConfig
try {
  loadConfig({ PORT: '3000', NODE_ENV: 'production', DATABASE_URL: 'pg://...', JWT_SECRET: 'secret' });
  console.log('Config OK ✓');
} catch (e: any) {
  console.log('Error:', e.message);
}

try {
  loadConfig({ PORT: '3000' }); // faltan campos
} catch (e: any) {
  console.log('Config incompleta:', e.message);
  // 'Config inválida. Faltan: NODE_ENV, DATABASE_URL, JWT_SECRET'
}

// HealthChecker
const health = new HealthChecker();
health
  .register({
    name: 'database', critical: true,
    check: async () => ({ status: 'healthy', message: 'latencia: 5ms' }),
  })
  .register({
    name: 'redis', critical: false,
    check: async () => ({ status: 'degraded', message: 'latencia alta: 200ms' }),
  })
  .register({
    name: 'storage', critical: false,
    check: async () => ({ status: 'healthy' }),
  });

(async () => {
  const report = await health.getReport();
  console.log('\nHealth status:', report.status); // degraded (redis no crítico)
  console.log('Checks:', Object.entries(report.checks).map(([k, v]) => `${k}: ${v.status}`).join(', '));

  // GracefulShutdown
  const gs = new GracefulShutdown();
  gs.requestStarted();
  gs.requestStarted();

  // Simula que una petición termina durante el shutdown
  setTimeout(() => { gs.requestFinished(); gs.requestFinished(); }, 100);

  const result = await gs.shutdown(500);
  console.log('\nShutdown:', result); // 'clean'
  gs.getLog().forEach(l => console.log(' ', l));
})();
TS,
        ];

        return $ex;
    }
}
