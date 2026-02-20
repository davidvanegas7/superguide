<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AngularExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $exercises = $this->exercises();
        $now = now();

        foreach ($exercises as $ex) {
            DB::table('lesson_exercises')->updateOrInsert(
                ['lesson_id' => $ex['lesson_id']],
                array_merge($ex, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }

    private function exercises(): array
    {
        return [

            // ── Lección 1: Introducción a Angular (id=4) ───────────────────
            [
                'lesson_id'   => 4,
                'title'       => 'Modela los datos de tu primera app',
                'language'    => 'typescript',
                'description' => <<<'MD'
Implementa la interfaz `Hero` y la clase `HeroService` con los métodos `getAll()`, `getById(id)` y `getActive()`.
Al final crea una instancia y llama cada método con `console.log` para ver los resultados.
MD,
                'starter_code' => <<<'TS'
interface Hero {
  id: number;
  name: string;
  power: string;
  isActive: boolean;
}

class HeroService {
  private heroes: Hero[] = [
    { id: 1, name: 'Windstorm',   power: 'Wind control', isActive: true  },
    { id: 2, name: 'Magneta',     power: 'Magnetism',    isActive: false },
    { id: 3, name: 'Bombasto',    power: 'Explosions',   isActive: true  },
  ];

  getAll(): Hero[] {
    // Devuelve todos los héroes
    return [];
  }

  getById(id: number): Hero | undefined {
    // Busca el héroe con ese id
    return undefined;
  }

  getActive(): Hero[] {
    // Devuelve solo los héroes activos
    return [];
  }
}

// Prueba tu servicio:
const service = new HeroService();
console.log('Todos:', JSON.stringify(service.getAll()));
console.log('ID 2:', JSON.stringify(service.getById(2)));
console.log('Activos:', JSON.stringify(service.getActive()));
TS,
            ],

            // ── Lección 2: TypeScript para Angular (id=5) ──────────────────
            [
                'lesson_id'   => 5,
                'title'       => 'Implementa una pila genérica Stack<T>',
                'language'    => 'typescript',
                'description' => <<<'MD'
Crea la clase `Stack<T>` con los métodos `push`, `pop`, `peek`, la propiedad `size` y el método `isEmpty`.
Pruébala con strings y verifica que las operaciones son correctas.
MD,
                'starter_code' => <<<'TS'
class Stack<T> {
  private items: T[] = [];

  push(item: T): void {
    // Tu código aquí
  }

  pop(): T | undefined {
    // Tu código aquí
    return undefined;
  }

  peek(): T | undefined {
    // Tu código aquí
    return undefined;
  }

  get size(): number {
    // Tu código aquí
    return 0;
  }

  isEmpty(): boolean {
    // Tu código aquí
    return true;
  }
}

// Prueba:
const stack = new Stack<string>();
stack.push('Angular');
stack.push('React');
stack.push('Vue');

console.log('Tope:', stack.peek());           // Vue
console.log('Tamaño:', stack.size);           // 3
console.log('Pop:', stack.pop());             // Vue
console.log('Nuevo tamaño:', stack.size);    // 2
console.log('¿Vacía?', stack.isEmpty());     // false

// Prueba con números:
const nums = new Stack<number>();
console.log('Nums vacía:', nums.isEmpty());   // true
nums.push(42);
console.log('Nums tope:', nums.peek());       // 42
TS,
            ],

            // ── Lección 3: Componentes en Angular (id=6) ───────────────────
            [
                'lesson_id'   => 6,
                'title'       => 'Lógica de un componente contador',
                'language'    => 'typescript',
                'description' => <<<'MD'
Un componente Angular encapsula **estado** (data) y **comportamiento** (métodos). Implementa la lógica de un contador con mínimo, máximo y step configurable.
Asegúrate de que `increment` y `decrement` respeten los límites.
MD,
                'starter_code' => <<<'TS'
class CounterComponent {
  count  = 0;
  step   = 1;
  readonly min = 0;
  readonly max = 10;

  // Incrementa count en step, sin superar max
  increment(): void {
    // Tu código aquí
  }

  // Decrementa count en step, sin bajar de min
  decrement(): void {
    // Tu código aquí
  }

  reset(): void {
    // Tu código aquí
  }

  setStep(newStep: number): void {
    if (newStep > 0) this.step = newStep;
  }

  get label(): string {
    return `Contador: ${this.count} (step: ${this.step}, límites: ${this.min}-${this.max})`;
  }
}

const c = new CounterComponent();
c.increment(); c.increment(); c.increment();
console.log(c.label);        // Contador: 3 (step: 1, límites: 0-10)

c.setStep(5);
c.increment();               // 3 + 5 = 8
console.log(c.label);        // 8

c.increment();               // 8 + 5 = 13 → tope en 10
console.log(c.label);        // 10

c.decrement();               // 10 - 5 = 5
console.log(c.label);        // 5

c.reset();
console.log(c.label);        // 0
TS,
            ],

            // ── Lección 4: Directivas en Angular (id=7) ────────────────────
            [
                'lesson_id'   => 7,
                'title'       => 'Funciones que replican directivas estructurales',
                'language'    => 'typescript',
                'description' => <<<'MD'
Las directivas `*ngIf` y `*ngFor` aplican lógica al DOM. Implementa funciones puras en TypeScript que replican esa misma lógica:
`highlight`, `renderList` y `hasPermission`.
MD,
                'starter_code' => <<<'TS'
// 1. highlight: rodea todas las ocurrencias de `query` con ** en el texto
//    Ejemplo: highlight("Angular es genial", "Angular") → "**Angular** es genial"
function highlight(text: string, query: string): string {
  if (!query) return text;
  // Tu código aquí (tip: usa split y join, o un regex global)
  return text;
}

// 2. renderList: dado un array de productos, genera "N. Nombre — $Precio"
interface Product { id: number; name: string; price: number; }

function renderList(products: Product[]): string[] {
  // Tu código aquí (usa map con índice)
  return [];
}

// 3. hasPermission: simula *ngIf="user | hasRole:'admin'"
type Role = 'admin' | 'editor' | 'viewer';
interface AppUser { name: string; roles: Role[]; }

function hasPermission(user: AppUser, required: Role): boolean {
  // Tu código aquí
  return false;
}

// Pruebas:
console.log(highlight('Angular es genial y Angular es potente', 'Angular'));
// **Angular** es genial y **Angular** es potente

const products: Product[] = [
  { id: 1, name: 'Laptop', price: 1200 },
  { id: 2, name: 'Mouse',  price: 25   },
  { id: 3, name: 'Teclado',price: 80   },
];
console.log(renderList(products));
// ["1. Laptop — $1200", "2. Mouse — $25", "3. Teclado — $80"]

const admin:  AppUser = { name: 'Ana', roles: ['admin', 'editor'] };
const viewer: AppUser = { name: 'Bob', roles: ['viewer'] };
console.log(hasPermission(admin,  'admin'));  // true
console.log(hasPermission(viewer, 'admin'));  // false
console.log(hasPermission(viewer, 'viewer')); // true
TS,
            ],

            // ── Lección 5: Pipes en Angular (id=8) ─────────────────────────
            [
                'lesson_id'   => 8,
                'title'       => 'Implementa pipes como funciones puras',
                'language'    => 'typescript',
                'description' => <<<'MD'
En Angular, los **pipes** son funciones de transformación puras. Implementa cuatro: `truncate`, `formatCurrency`, `timeAgo` e `initials`.
MD,
                'starter_code' => <<<'TS'
// 1. truncate: corta a maxLen y agrega "..." si fue cortado
function truncate(text: string, maxLen: number): string {
  // Tu código aquí
  return text;
}

// 2. formatCurrency: 1234567 → "$ 1.234.567"
function formatCurrency(amount: number, symbol = '$'): string {
  // Tu código aquí (tip: toLocaleString o manipulación manual)
  return '';
}

// 3. timeAgo: < 60s → "hace X segundos", < 3600s → "hace X minutos",
//             < 86400s → "hace X horas", sino → "hace X días"
function timeAgo(date: Date): string {
  const diff = Math.floor((Date.now() - date.getTime()) / 1000);
  // Tu código aquí
  return '';
}

// 4. initials: "María García López" → "MGL"
function initials(fullName: string): string {
  // Tu código aquí
  return '';
}

// Pruebas:
console.log(truncate('Angular es un framework de Google', 20));
// "Angular es un frame..."

console.log(formatCurrency(1234567));
// "$ 1.234.567"

const sevenMinutesAgo = new Date(Date.now() - 7 * 60 * 1000);
console.log(timeAgo(sevenMinutesAgo));
// "hace 7 minutos"

console.log(initials('María García López'));
// "MGL"
TS,
            ],

            // ── Lección 6: Servicios (id=9) ────────────────────────────────
            [
                'lesson_id'   => 9,
                'title'       => 'Implementa un CartService completo',
                'language'    => 'typescript',
                'description' => <<<'MD'
Los servicios en Angular encapsulan la lógica de negocio. Implementa un `CartService` con manejo de cantidad, total y control de duplicados.
MD,
                'starter_code' => <<<'TS'
interface CartItem {
  id: number;
  name: string;
  price: number;
  quantity: number;
}

class CartService {
  private items: CartItem[] = [];

  // Si el producto ya existe, incrementa quantity; si no, lo agrega con quantity=1
  add(product: Omit<CartItem, 'quantity'>): void {
    // Tu código aquí
  }

  remove(id: number): void {
    // Tu código aquí
  }

  // Si quantity <= 0, elimina el item
  setQuantity(id: number, quantity: number): void {
    // Tu código aquí
  }

  getTotal(): number {
    // Suma price * quantity de cada item
    return 0;
  }

  getItems(): CartItem[] {
    return [...this.items];
  }

  getItemCount(): number {
    // Suma de todas las quantities
    return 0;
  }

  clear(): void { this.items = []; }
}

// Prueba:
const cart = new CartService();
cart.add({ id: 1, name: 'Laptop', price: 1200 });
cart.add({ id: 2, name: 'Mouse',  price: 25   });
cart.add({ id: 1, name: 'Laptop', price: 1200 }); // duplicado → quantity 2

console.log('Unidades:', cart.getItemCount()); // 3
console.log('Total:', cart.getTotal());         // 2425

cart.setQuantity(2, 4);
console.log('Total tras setQuantity:', cart.getTotal()); // 2500

cart.remove(1);
console.log('Items:', JSON.stringify(cart.getItems()));
TS,
            ],

            // ── Lección 7: Routing (id=10) ─────────────────────────────────
            [
                'lesson_id'   => 10,
                'title'       => 'Simula guards de navegación y breadcrumbs',
                'language'    => 'typescript',
                'description' => <<<'MD'
Los guards de Angular protegen rutas. Implementa `canNavigate` (verifica autenticación y roles) y `buildBreadcrumbs` (genera la ruta de migas de pan desde un path).
MD,
                'starter_code' => <<<'TS'
interface AppRoute { path: string; requiresAuth: boolean; roles?: string[]; }
interface Session  { isLoggedIn: boolean; roles: string[]; }

class RouterSimulator {
  private routes: AppRoute[] = [
    { path: '/home',       requiresAuth: false },
    { path: '/perfil',     requiresAuth: true  },
    { path: '/admin',      requiresAuth: true,  roles: ['admin']            },
    { path: '/reportes',   requiresAuth: true,  roles: ['admin', 'editor']  },
  ];

  // { allowed: true } o { allowed: false, reason: '...' }
  canNavigate(path: string, session: Session): { allowed: boolean; reason?: string } {
    const route = this.routes.find(r => r.path === path);
    if (!route) return { allowed: false, reason: 'ruta no encontrada' };
    // Tu código aquí
    return { allowed: true };
  }

  // "/admin/usuarios/editar" →
  // [{ label: 'Admin', path: '/admin' },
  //  { label: 'Usuarios', path: '/admin/usuarios' },
  //  { label: 'Editar', path: '/admin/usuarios/editar' }]
  buildBreadcrumbs(path: string): { label: string; path: string }[] {
    // Tu código aquí
    return [];
  }
}

const router  = new RouterSimulator();
const guest:  Session = { isLoggedIn: false, roles: [] };
const editor: Session = { isLoggedIn: true,  roles: ['editor'] };
const admin:  Session = { isLoggedIn: true,  roles: ['admin'] };

console.log(router.canNavigate('/home',    guest));   // { allowed: true }
console.log(router.canNavigate('/perfil',  guest));   // { allowed: false, reason: ... }
console.log(router.canNavigate('/admin',   editor));  // { allowed: false, reason: ... }
console.log(router.canNavigate('/admin',   admin));   // { allowed: true }
console.log(JSON.stringify(router.buildBreadcrumbs('/admin/usuarios/editar')));
TS,
            ],

            // ── Lección 8: Formularios (id=11) ─────────────────────────────
            [
                'lesson_id'   => 11,
                'title'       => 'Sistema de validación de formularios',
                'language'    => 'typescript',
                'description' => <<<'MD'
Los Reactive Forms validan con funciones puras. Implementa los validadores `required`, `minLength`, `email` y `matchField`, y la función `validateField` que los compone.
MD,
                'starter_code' => <<<'TS'
type Validator = (value: string, allValues?: Record<string, string>) => string | null;

// Retorna el mensaje de error o null si es válido
const required: Validator = (v) => {
  // Tu código aquí
  return null;
};

const minLength = (min: number): Validator => (v) => {
  // Tu código aquí
  return null;
};

const email: Validator = (v) => {
  // Tip: /^[^\s@]+@[^\s@]+\.[^\s@]+$/ es un regex útil
  return null;
};

// matchField: verifica que value === allValues[otherField]
const matchField = (otherField: string, label: string): Validator => (v, all = {}) => {
  // Tu código aquí
  return null;
};

// Corre todos los validadores y devuelve el PRIMER error encontrado
function validateField(
  value: string,
  validators: Validator[],
  allValues?: Record<string, string>
): string | null {
  // Tu código aquí
  return null;
}

// Pruebas:
console.log(validateField('', [required]));
// 'Este campo es requerido'

console.log(validateField('abc', [required, minLength(8)]));
// 'Mínimo 8 caracteres'

console.log(validateField('noesvalido', [required, email]));
// 'Ingresa un email válido'

console.log(validateField('user@example.com', [required, email]));
// null ← válido

const vals = { password: 'secret123', confirm: 'secret456' };
console.log(validateField(vals.confirm, [matchField('password', 'contraseña')], vals));
// 'Las contraseñas no coinciden'
TS,
            ],

            // ── Lección 9: HTTP Client (id=12) ─────────────────────────────
            [
                'lesson_id'   => 12,
                'title'       => 'Consume JSONPlaceholder y transforma los datos',
                'language'    => 'typescript',
                'description' => <<<'MD'
El `HttpClient` de Angular envuelve `fetch`. Practica los patrones de transformación de datos que usarás con `map` y `tap`. Implementa `toTitleCase`, `toSummary` y `loadPosts`.
MD,
                'starter_code' => <<<'TS'
interface Post { userId: number; id: number; title: string; body: string; }
interface PostSummary { id: number; title: string; preview: string; }

// "hello world of angular" → "Hello World Of Angular"
function toTitleCase(str: string): string {
  // Tu código aquí
  return str;
}

// Transforma un Post en PostSummary:
// title en TitleCase, preview = primeros 60 chars del body + "..."
function toSummary(post: Post): PostSummary {
  // Tu código aquí
  return { id: post.id, title: '', preview: '' };
}

async function loadPosts(): Promise<void> {
  const res   = await fetch('https://jsonplaceholder.typicode.com/posts?_limit=5');
  const posts: Post[] = await res.json();

  // Transforma cada post con toSummary y muestra cada uno con console.log
  // Formato: "[ID] Título — Preview..."
  // Tu código aquí
}

loadPosts();
TS,
            ],

            // ── Lección 10: RxJS (id=13) ───────────────────────────────────
            [
                'lesson_id'   => 13,
                'title'       => 'Practica operadores RxJS esenciales',
                'language'    => 'typescript',
                'description' => <<<'MD'
Usa RxJS directamente desde CDN. Implementa los tres pipelines comentados usando `filter`, `map`, `mergeMap`, `reduce` y `take`.
MD,
                'starter_code' => <<<'TS'
import { from, of } from 'https://esm.sh/rxjs';
import { filter, map, mergeMap, reduce, take } from 'https://esm.sh/rxjs/operators';

// 1. Del array [1..10]: filtra pares y eleva al cuadrado
from([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]).pipe(
  // Tu código aquí (filter + map)
).subscribe(n => console.log('Par²:', n));
// Espera: 4, 16, 36, 64, 100

// 2. Aplanar: dado un array de usuarios, emite "nombre: producto" por cada pedido
interface User  { id: number; name: string; }
interface Order { userId: number; product: string; }

const users:  User[]  = [{ id: 1, name: 'Ana' }, { id: 2, name: 'Bob' }];
const orders: Order[] = [
  { userId: 1, product: 'Laptop'  },
  { userId: 1, product: 'Mouse'   },
  { userId: 2, product: 'Teclado' },
];

from(users).pipe(
  // Tu código aquí (mergeMap → from(...))
).subscribe(line => console.log(line));
// Ana: Laptop, Ana: Mouse, Bob: Teclado

// 3. Suma total con reduce
from([10, 20, 30, 40, 50]).pipe(
  // Tu código aquí (reduce)
).subscribe(total => console.log('Total:', total));
// Total: 150
TS,
            ],

            // ── Lección 11: Signals (id=14) ────────────────────────────────
            [
                'lesson_id'   => 14,
                'title'       => 'Implementa Signals y computed desde cero',
                'language'    => 'typescript',
                'description' => <<<'MD'
Para entender cómo funcionan los Signals de Angular internamente, impleméntalos tú mismo. Crea la clase `Signal<T>` con `get/set/update/subscribe` y la función `computed`.
MD,
                'starter_code' => <<<'TS'
class Signal<T> {
  private _value: T;
  private _subs: Array<(v: T) => void> = [];

  constructor(initial: T) { this._value = initial; }

  get(): T { return this._value; }

  set(newVal: T): void {
    this._value = newVal;
    // Notifica todos los suscriptores
    // Tu código aquí
  }

  update(fn: (current: T) => T): void {
    // Tu código aquí (llama a set con fn(this._value))
  }

  // Retorna una función para desuscribirse
  subscribe(fn: (v: T) => void): () => void {
    this._subs.push(fn);
    return () => { this._subs = this._subs.filter(s => s !== fn); };
  }
}

// computed: signal de solo lectura, recalcula cuando cambia algún dep
function computed<T>(fn: () => T, deps: Signal<any>[]): { get: () => T } {
  let cache = fn();
  deps.forEach(dep => dep.subscribe(() => { cache = fn(); }));
  return { get: () => cache };
}

// Prueba:
const count  = new Signal(0);
const double = computed(() => count.get() * 2, [count]);

const unsub = count.subscribe(v => console.log('  → cambió a:', v));

count.set(5);
console.log('count:', count.get(), '| double:', double.get()); // 5 | 10

count.update(c => c + 1);
console.log('count:', count.get(), '| double:', double.get()); // 6 | 12

unsub(); // dejar de escuchar
count.set(100);
console.log('count final:', count.get()); // 100 (sin log del subscriber)
TS,
            ],

            // ── Lección 12: Seguridad JWT (id=15) ──────────────────────────
            [
                'lesson_id'   => 15,
                'title'       => 'Decodifica y valida un JWT sin librerías',
                'language'    => 'typescript',
                'description' => <<<'MD'
Un JWT es `header.payload.signature` en base64url. Implementa `decodePayload`, `isExpired` y `getClaim` para entender cómo funcionan los tokens antes de usarlos con interceptors.
MD,
                'starter_code' => <<<'TS'
// base64url usa - y _ en vez de + y /; puede faltar el padding =
function base64UrlDecode(str: string): string {
  const base64 = str.replace(/-/g, '+').replace(/_/g, '/');
  const padded  = base64.padEnd(base64.length + (4 - base64.length % 4) % 4, '=');
  return atob(padded); // atob está disponible en el navegador
}

// Extrae y parsea el payload del JWT (segunda parte)
function decodePayload(token: string): Record<string, any> {
  // Tip: token.split('.')[1]
  // Tu código aquí
  return {};
}

// El campo "exp" es Unix timestamp en segundos; compara con Date.now()/1000
function isExpired(token: string): boolean {
  // Tu código aquí
  return false;
}

function getClaim<T>(token: string, claim: string): T | null {
  // Tu código aquí
  return null;
}

// Token válido hasta 2030 (sub=user123, roles=[admin,editor])
const token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9' +
  '.eyJzdWIiOiJ1c2VyMTIzIiwibmFtZSI6IkFuYSBHYXJjw61hIiwicm9sZXMiOlsiYWRtaW4iLCJlZGl0b3IiXSwiZXhwIjoxODkzNDU2MDAwfQ' +
  '.firma_simulada';

console.log('Payload:', JSON.stringify(decodePayload(token)));
// { sub: 'user123', name: 'Ana García', roles: [...], exp: 1893456000 }

console.log('¿Expirado?', isExpired(token)); // false

console.log('sub:', getClaim(token, 'sub'));         // user123
console.log('roles:', getClaim(token, 'roles'));     // ['admin', 'editor']
TS,
            ],

            // ── Lección 13: NgRx (id=16) ───────────────────────────────────
            [
                'lesson_id'   => 16,
                'title'       => 'Implementa un mini-store con patrón Redux',
                'language'    => 'typescript',
                'description' => <<<'MD'
NgRx se basa en el patrón Redux. Implementa el `todoReducer` (inmutable) y el `Store` que lo envuelve. Todas las mutaciones deben retornar **nuevo estado**, nunca mutar el existente.
MD,
                'starter_code' => <<<'TS'
interface Todo { id: number; text: string; done: boolean; }
interface State { todos: Todo[]; filter: 'all' | 'active' | 'done'; nextId: number; }

type Action =
  | { type: 'ADD';    text: string }
  | { type: 'TOGGLE'; id: number   }
  | { type: 'DELETE'; id: number   }
  | { type: 'FILTER'; filter: State['filter'] };

const initial: State = { todos: [], filter: 'all', nextId: 1 };

// IMPORTANTE: nunca mutates state directamente, siempre retorna un objeto nuevo
function reducer(state: State = initial, action: Action): State {
  switch (action.type) {
    case 'ADD':
      // Tu código aquí: { ...state, todos: [...state.todos, nuevo], nextId: state.nextId + 1 }
      return state;
    case 'TOGGLE':
      // Tu código aquí: mapea todos y cambia done del que tenga ese id
      return state;
    case 'DELETE':
      // Tu código aquí: filtra para eliminar ese id
      return state;
    case 'FILTER':
      // Tu código aquí
      return state;
    default:
      return state;
  }
}

class Store {
  private state = initial;
  dispatch(action: Action) { this.state = reducer(this.state, action); }
  getState() { return this.state; }
  getVisible() {
    const { todos, filter } = this.state;
    if (filter === 'active') return todos.filter(t => !t.done);
    if (filter === 'done')   return todos.filter(t =>  t.done);
    return todos;
  }
}

const store = new Store();
store.dispatch({ type: 'ADD', text: 'Aprender Angular' });
store.dispatch({ type: 'ADD', text: 'Practicar NgRx'   });
store.dispatch({ type: 'ADD', text: 'Hacer ejercicios'  });
store.dispatch({ type: 'TOGGLE', id: 1 });
console.log('Todos:', JSON.stringify(store.getState().todos));

store.dispatch({ type: 'FILTER', filter: 'done' });
console.log('Solo done:', JSON.stringify(store.getVisible()));

store.dispatch({ type: 'DELETE', id: 2 });
console.log('Tras delete id=2:', JSON.stringify(store.getState().todos));
TS,
            ],

            // ── Lección 14: Performance (id=17) ────────────────────────────
            [
                'lesson_id'   => 17,
                'title'       => 'Memoización y detección de cambios en listas',
                'language'    => 'typescript',
                'description' => <<<'MD'
Implementa `memoize` para cachear resultados de funciones costosas (equivalente a `OnPush` + `computed`) y `diffById` para detectar qué elementos de una lista cambiaron (equivalente a `trackBy`).
MD,
                'starter_code' => <<<'TS'
// 1. memoize: retorna una versión cacheada de fn
//    La key del cache es JSON.stringify(args)
function memoize<A extends any[], R>(fn: (...args: A) => R): (...args: A) => R {
  const cache = new Map<string, R>();
  return (...args: A): R => {
    const key = JSON.stringify(args);
    // Tu código aquí: si está en cache, retórnalo; si no, calcula, guarda y retorna
    return fn(...args);
  };
}

// Función costosa sin memoize
function fibonacci(n: number): number {
  if (n <= 1) return n;
  return fibonacci(n - 1) + fibonacci(n - 2);
}

const memoFib = memoize(fibonacci);

console.time('Sin memo fib(35)');
console.log('fib(35):', fibonacci(35));
console.timeEnd('Sin memo fib(35)');

console.time('Con memo fib(35) — 1ª vez');
console.log('memoFib(35):', memoFib(35));
console.timeEnd('Con memo fib(35) — 1ª vez');

console.time('Con memo fib(35) — 2ª vez (cache hit)');
console.log('memoFib(35):', memoFib(35));
console.timeEnd('Con memo fib(35) — 2ª vez (cache hit)');

// 2. diffById: trackBy logic — detecta qué IDs se agregaron o eliminaron
interface Entity { id: number; name: string; }

function diffById(prev: Entity[], next: Entity[]): { added: number[]; removed: number[] } {
  // Tu código aquí
  return { added: [], removed: [] };
}

const prev = [{ id: 1, name: 'A' }, { id: 2, name: 'B' }, { id: 3, name: 'C' }];
const next = [{ id: 2, name: 'B' }, { id: 3, name: 'C' }, { id: 4, name: 'D' }];
console.log('Diff:', JSON.stringify(diffById(prev, next)));
// { added: [4], removed: [1] }
TS,
            ],

            // ── Lección 15: Testing (id=18) ────────────────────────────────
            [
                'lesson_id'   => 18,
                'title'       => 'Escribe y ejecuta tests unitarios',
                'language'    => 'typescript',
                'description' => <<<'MD'
El testing en Angular usa Jasmine, pero los conceptos son universales. Implementa las tres funciones (`capitalize`, `sum`, `unique`) y haz pasar todos los tests del mini-framework incluido.
MD,
                'starter_code' => <<<'TS'
// ── Mini framework de testing ────────────────────────────────────────────
let passed = 0, failed = 0;

function test(desc: string, fn: () => void) {
  try   { fn(); passed++; console.log(`  ✅ ${desc}`); }
  catch (e: any) { failed++; console.log(`  ❌ ${desc}\n     → ${e.message}`); }
}

function expect<T>(actual: T) {
  return {
    toBe:      (exp: T)   => { if (actual !== exp) throw new Error(`esperaba ${JSON.stringify(exp)}, recibí ${JSON.stringify(actual)}`); },
    toEqual:   (exp: T)   => { if (JSON.stringify(actual) !== JSON.stringify(exp)) throw new Error(`esperaba ${JSON.stringify(exp)}, recibí ${JSON.stringify(actual)}`); },
    toBeTruthy:()         => { if (!actual) throw new Error(`esperaba truthy`); },
    toBeFalsy: ()         => { if (actual)  throw new Error(`esperaba falsy`); },
  };
}

// ── Funciones a implementar ──────────────────────────────────────────────

function capitalize(str: string): string {
  // Primera letra en mayúscula, resto sin cambio
  // Tu código aquí
  return str;
}

function sum(numbers: number[]): number {
  // Suma de todos los elementos
  // Tu código aquí
  return 0;
}

function unique<T>(arr: T[]): T[] {
  // Elimina duplicados manteniendo el orden
  // Tu código aquí
  return arr;
}

// ── Tests ─────────────────────────────────────────────────────────────────
console.log('capitalize:');
test('primera letra en mayúscula',  () => expect(capitalize('angular')).toBe('Angular'));
test('string vacío sin error',      () => expect(capitalize('')).toBe(''));
test('no altera el resto',          () => expect(capitalize('hola mundo')).toBe('Hola mundo'));

console.log('sum:');
test('suma números positivos',      () => expect(sum([1, 2, 3])).toBe(6));
test('array vacío es 0',            () => expect(sum([])).toBe(0));
test('con negativos',               () => expect(sum([-1, 5, -2])).toBe(2));

console.log('unique:');
test('elimina duplicados',          () => expect(unique([1,2,2,3])).toEqual([1,2,3]));
test('sin duplicados intacto',      () => expect(unique([1,2,3])).toEqual([1,2,3]));
test('array vacío',                 () => expect(unique([])).toEqual([]));

console.log(`\nResultado: ${passed}/${passed+failed} pasados`);
TS,
            ],

            // ── Lección 16: SSR (id=20) ────────────────────────────────────
            [
                'lesson_id'   => 20,
                'title'       => 'Patrones SSR-safe: isPlatformBrowser y TransferState',
                'language'    => 'typescript',
                'description' => <<<'MD'
En SSR el código corre en Node.js donde `window` no existe. Implementa `isPlatformBrowser`, `SafeStorage` y `TransferState` para escribir código que funcione tanto en servidor como en cliente.
MD,
                'starter_code' => <<<'TS'
type Platform = 'browser' | 'server';

function isPlatformBrowser(platform: Platform): boolean {
  // Tu código aquí
  return false;
}

function isPlatformServer(platform: Platform): boolean {
  // Tu código aquí
  return false;
}

// SafeStorage: en servidor solo guarda en memoria y loga la acción
class SafeStorage {
  private mem = new Map<string, string>();
  constructor(private platform: Platform) {}

  getItem(key: string): string | null {
    return this.mem.get(key) ?? null;
  }

  setItem(key: string, value: string): void {
    if (isPlatformBrowser(this.platform)) {
      this.mem.set(key, value);
      console.log('[Browser] guardado:', key, '=', value);
    } else {
      // En servidor: no persiste pero loga que fue ignorado
      console.log('[Server] setItem ignorado en SSR:', key);
    }
  }
}

// TransferState: serializa datos del servidor para que el cliente los lea
class TransferState {
  private store = new Map<string, any>();
  set<T>(key: string, value: T): void { this.store.set(key, value); }
  get<T>(key: string, def: T): T      { return (this.store.get(key) ?? def) as T; }
  hasKey(key: string): boolean         { return this.store.has(key); }
  remove(key: string): void            { this.store.delete(key); }
}

// Simula carga de datos: servidor guarda en TS, cliente lee sin petición HTTP
function loadData(platform: Platform, ts: TransferState) {
  const KEY = 'posts_cache';
  if (isPlatformServer(platform)) {
    const data = [{ id: 1, title: 'SSR con Angular' }];
    ts.set(KEY, data);
    console.log('[Server] guardó en TransferState:', data);
    return data;
  }
  if (ts.hasKey(KEY)) {
    const cached = ts.get(KEY, []);
    console.log('[Browser] leyó del cache (sin petición HTTP):', cached);
    ts.remove(KEY);
    return cached;
  }
  console.log('[Browser] sin cache, haría petición HTTP');
  return [];
}

// Pruebas:
console.log('isPlatformBrowser("browser"):', isPlatformBrowser('browser')); // true
console.log('isPlatformServer("server"):', isPlatformServer('server'));      // true

const transferState = new TransferState();
loadData('server',  transferState);  // guarda
loadData('browser', transferState);  // lee del cache

const serverStore = new SafeStorage('server');
serverStore.setItem('token', 'abc'); // debe logar [Server] ignorado

const browserStore = new SafeStorage('browser');
browserStore.setItem('token', 'xyz'); // debe logar [Browser] guardado
TS,
            ],

            // ── Lección 17: Animaciones (id=21) ────────────────────────────
            [
                'lesson_id'   => 21,
                'title'       => 'Construye configuraciones de animación',
                'language'    => 'typescript',
                'description' => <<<'MD'
Las animaciones de Angular son **objetos de configuración** en TypeScript. Practica construyendo triggers `fadeInOut`, `slideDown` y `pulse` usando las funciones helper provistas.
MD,
                'starter_code' => <<<'TS'
// ── Helper types (réplica simplificada de @angular/animations) ───────────
type StyleMap   = Record<string, string | number>;
interface AStyle    { type: 'style';      styles: StyleMap; }
interface AAnimate  { type: 'animate';    timings: string; styles?: AStyle; }
interface AState    { type: 'state';      name: string;    styles: AStyle; }
interface ATransition { type: 'transition'; expr: string; steps: AAnimate[]; }
interface ATrigger  { name: string; defs: Array<AState | ATransition>; }

const style     = (s: StyleMap): AStyle       => ({ type: 'style', styles: s });
const animate   = (t: string, s?: AStyle): AAnimate => ({ type: 'animate', timings: t, styles: s });
const aState    = (name: string, s: AStyle): AState => ({ type: 'state', name, styles: s });
const transition= (expr: string, ...steps: AAnimate[]): ATransition => ({ type: 'transition', expr, steps });
const trigger   = (name: string, ...defs: Array<AState | ATransition>): ATrigger => ({ name, defs });

// ── Ejercicio 1: fadeInOut ────────────────────────────────────────────────
// Estado 'visible': opacity 1, translateY(0)
// Estado 'hidden':  opacity 0, translateY(-10px)
// Transición visible→hidden: 200ms ease-in
// Transición hidden→visible: 300ms ease-out
const fadeInOut = trigger(
  'fadeInOut',
  // Tu código aquí usando aState(), transition() y animate()
);

console.log('fadeInOut tiene', fadeInOut.defs.length, 'definiciones'); // 4 (2 states + 2 transitions)

// ── Ejercicio 2: slideDown (:enter / :leave) ──────────────────────────────
// :enter → parte de opacity 0, translateY(-20px) y llega a opacity 1, translateY(0) en 250ms ease-out
// :leave → de opacity 1 a opacity 0 en 200ms ease-in
const slideDown = trigger(
  'slideDown',
  // Tu código aquí (solo transitions, sin states)
);

console.log('slideDown tiene', slideDown.defs.length, 'transiciones'); // 2

// ── Ejercicio 3: pulse (botón de éxito) ───────────────────────────────────
// Transición '* => active': escala 1 → 1.05 → 1 en 300ms (simula pulso)
// Tip: dos animaciones en secuencia dentro del transition
const pulse = trigger(
  'pulse',
  transition('* => active',
    animate('150ms ease-out', style({ transform: 'scale(1.05)' })),
    animate('150ms ease-in',  style({ transform: 'scale(1)'    })),
  )
);

console.log('pulse:', pulse.name, '| pasos:', (pulse.defs[0] as ATransition).steps.length); // pulse | 2
TS,
            ],

            // ── Lección 18: i18n (id=22) ───────────────────────────────────
            [
                'lesson_id'   => 22,
                'title'       => 'Sistema de traducción con interpolación',
                'language'    => 'typescript',
                'description' => <<<'MD'
Implementa un sistema i18n funcional con `translate` (interpola `{{params}}`), `plural` y `formatDate` que respeta el formato regional de cada idioma.
MD,
                'starter_code' => <<<'TS'
const dict: Record<string, Record<string, string>> = {
  es: {
    'nav.home':        'Inicio',
    'nav.about':       'Acerca de',
    'greeting':        'Hola, {{name}}! Tienes {{count}} mensaje(s).',
    'error.required':  'El campo "{{field}}" es obligatorio.',
    'date.format':     '{{day}}/{{month}}/{{year}}',
  },
  en: {
    'nav.home':        'Home',
    'nav.about':       'About',
    'greeting':        'Hello, {{name}}! You have {{count}} message(s).',
    'error.required':  'The field "{{field}}" is required.',
    'date.format':     '{{month}}/{{day}}/{{year}}',
  },
};

// Busca la key en `lang`, fallback a 'es', luego a la propia key
// Reemplaza {{param}} con params[param]
function translate(
  key: string,
  params: Record<string, string | number> = {},
  lang = 'es'
): string {
  // Tu código aquí
  return key;
}

// 1 → "1 mensaje", 5 → "5 mensajes"
function plural(count: number, singular: string, pluralForm: string): string {
  // Tu código aquí
  return '';
}

// Usa translate('date.format', {...}, lang) para respetar el formato regional
function formatDate(date: Date, lang = 'es'): string {
  const day   = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year  = String(date.getFullYear());
  // Tu código aquí
  return '';
}

// Pruebas:
console.log(translate('nav.home', {}, 'es'));            // Inicio
console.log(translate('nav.home', {}, 'en'));            // Home
console.log(translate('greeting', { name: 'Ana', count: 3 }, 'es'));
// Hola, Ana! Tienes 3 mensaje(s).
console.log(translate('error.required', { field: 'email' }, 'en'));
// The field "email" is required.

console.log(plural(1, 'mensaje', 'mensajes'));  // 1 mensaje
console.log(plural(5, 'mensaje', 'mensajes'));  // 5 mensajes

const d = new Date(2026, 1, 20);               // 20 Feb 2026
console.log(formatDate(d, 'es'));              // 20/02/2026
console.log(formatDate(d, 'en'));              // 02/20/2026
TS,
            ],

        ]; // fin array
    }
}
