<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BackendConceptsExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'backend-conceptos')->first();

        if (!$course) {
            $this->command->warn('Curso backend-conceptos no encontrado. Ejecuta CourseSeeder primero.');
            return;
        }

        $lessons = Lesson::where('course_id', $course->id)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('sort_order');

        $exercises = [
            // Lección 1: POO Fundamentos
            [
                'lesson_id'    => $lessons->get(1)?->id,
                'title'        => 'Clase BankAccount',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Clase BankAccount

Implementa una clase `BankAccount` que modele una cuenta bancaria con las siguientes características:

**Requisitos:**
- Constructor que recibe `owner: string` y `initialBalance: number`
- Propiedades privadas: `_owner`, `_balance`, `_transactions`
- Getters de solo lectura: `owner`, `balance`, `transactions`
- Método `deposit(amount: number)`: añade fondos (lanza error si `amount <= 0`)
- Método `withdraw(amount: number)`: retira fondos (lanza error si fondos insuficientes o `amount <= 0`)
- Método `getStatement(): string`: devuelve un resumen con el owner, balance y número de transacciones
- Método estático `compare(a: BankAccount, b: BankAccount)`: devuelve la cuenta con mayor balance

**Ejemplo:**
```typescript
const acc = new BankAccount('Ana', 1000);
acc.deposit(500);
acc.withdraw(200);
console.log(acc.balance);        // 1300
console.log(acc.transactions);   // ['deposit: +500', 'withdraw: -200']
console.log(acc.getStatement());
```
MD,
                'starter_code' => <<<'TS'
class BankAccount {
  private _owner: string;
  private _balance: number;
  private _transactions: string[] = [];

  constructor(owner: string, initialBalance: number) {
    // TODO: inicializar propiedades
    // Validar que initialBalance >= 0
  }

  get owner(): string {
    // TODO
    return '';
  }

  get balance(): number {
    // TODO
    return 0;
  }

  get transactions(): string[] {
    // TODO: devuelve una copia para evitar mutaciones externas
    return [];
  }

  deposit(amount: number): void {
    // TODO: validar amount > 0, actualizar _balance y registrar transacción
  }

  withdraw(amount: number): void {
    // TODO: validar amount > 0 y fondos suficientes
    // Registrar transacción si tiene éxito
  }

  getStatement(): string {
    // TODO: retornar string con owner, balance y número de transacciones
    return '';
  }

  static compare(a: BankAccount, b: BankAccount): BankAccount {
    // TODO: retornar la cuenta con mayor balance
    return a;
  }
}

// Tests
const acc = new BankAccount('Ana', 1000);
acc.deposit(500);
acc.withdraw(200);
console.log(acc.balance);           // 1300
console.log(acc.transactions.length); // 2

const acc2 = new BankAccount('Bob', 2000);
console.log(BankAccount.compare(acc, acc2) === acc2); // true

try {
  acc.withdraw(99999);
} catch (e) {
  console.log('Error capturado:', (e as Error).message);
}

console.log(acc.getStatement());
TS,
            ],
            // Lección 2: POO Pilares
            [
                'lesson_id'    => $lessons->get(2)?->id,
                'title'        => 'Figuras Geométricas con Polimorfismo',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Figuras Geométricas con Polimorfismo

Implementa una jerarquía de clases que demuestre los 4 pilares de la POO.

**Requisitos:**
- Clase abstracta `Shape` con propiedades `color: string` y métodos abstractos `area(): number` y `perimeter(): number`
- Método concreto `describe(): string` en `Shape` que use `area()` y `perimeter()`
- Clase `Circle` con `radius: number`
- Clase `Rectangle` con `width: number` y `height: number`
- Clase `Triangle` con `a: number`, `b: number`, `c: number` (lados)
- Función `printShapes(shapes: Shape[])` que llama a `describe()` en cada figura (polimorfismo)
- Función `largestArea(shapes: Shape[]): Shape` que retorna la figura con mayor área

**Fórmulas:**
- Círculo: área = π×r², perímetro = 2×π×r
- Rectángulo: área = w×h, perímetro = 2×(w+h)
- Triángulo (Herón): s = (a+b+c)/2, área = √(s(s-a)(s-b)(s-c))
MD,
                'starter_code' => <<<'TS'
abstract class Shape {
  constructor(public color: string) {}

  abstract area(): number;
  abstract perimeter(): number;

  describe(): string {
    // TODO: retorna string como "Red Circle — Area: 78.54, Perimeter: 31.42"
    return '';
  }
}

class Circle extends Shape {
  constructor(color: string, public radius: number) {
    super(color);
  }

  area(): number {
    // TODO
    return 0;
  }

  perimeter(): number {
    // TODO
    return 0;
  }
}

class Rectangle extends Shape {
  constructor(color: string, public width: number, public height: number) {
    super(color);
  }

  area(): number {
    // TODO
    return 0;
  }

  perimeter(): number {
    // TODO
    return 0;
  }
}

class Triangle extends Shape {
  constructor(color: string, public a: number, public b: number, public c: number) {
    super(color);
    if (a + b <= c || a + c <= b || b + c <= a) {
      throw new Error('Lados inválidos para un triángulo');
    }
  }

  area(): number {
    // TODO: fórmula de Herón
    return 0;
  }

  perimeter(): number {
    // TODO
    return 0;
  }
}

function printShapes(shapes: Shape[]): void {
  // TODO: imprime describe() de cada forma
}

function largestArea(shapes: Shape[]): Shape {
  // TODO: retorna la figura con mayor área
  return shapes[0];
}

// Tests
const shapes: Shape[] = [
  new Circle('Red', 5),
  new Rectangle('Blue', 4, 6),
  new Triangle('Green', 3, 4, 5),
];

printShapes(shapes);

const largest = largestArea(shapes);
console.log('Mayor área:', largest.describe());
TS,
            ],
            // Lección 3: SOLID
            [
                'lesson_id'    => $lessons->get(3)?->id,
                'title'        => 'Refactorización SOLID',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Refactorización SOLID

El código de inicio viola varios principios SOLID. Tu tarea es refactorizarlo.

**Problemas a resolver:**
1. **SRP**: `OrderProcessor` hace demasiado (procesar, enviar email, guardar log)
2. **OCP**: el `switch` de pagos no es extensible sin modificar la clase
3. **DIP**: las dependencias se instancian internamente

**Requisitos de la refactorización:**
- Separar en clases con responsabilidad única
- Usar interfaces para pagos, notifications y logging
- Inyectar dependencias por constructor
- El código refactorizado debe producir la misma salida que el original
MD,
                'starter_code' => <<<'TS'
// ❌ CÓDIGO ORIGINAL — viola SRP, OCP y DIP
class BadOrderProcessor {
  process(order: { id: string; total: number; paymentMethod: string; email: string }) {
    // Procesa pago (viola OCP: switch no extensible)
    if (order.paymentMethod === 'card') {
      console.log(`[Card] Cobrando ${order.total}€`);
    } else if (order.paymentMethod === 'paypal') {
      console.log(`[PayPal] Cobrando ${order.total}€`);
    } else if (order.paymentMethod === 'bank') {
      console.log(`[Bank] Transfiriendo ${order.total}€`);
    }

    // Envía email (viola SRP)
    console.log(`[Email] Enviando confirmación a ${order.email}`);

    // Guarda log (viola SRP)
    console.log(`[Log] Orden ${order.id} procesada: ${order.total}€`);
  }
}

// ============================================================
// TODO: Refactoriza el código usando SOLID
// ============================================================

// 1. Define las interfaces

// 2. Implementa las clases concretas

// 3. Crea la clase OrderProcessor refactorizada

// ============================================================
// Test: el resultado debe ser el mismo
const order = { id: 'ORD-001', total: 99.99, paymentMethod: 'card', email: 'ana@test.com' };

// Con el código original:
const bad = new BadOrderProcessor();
bad.process(order);

// Con tu refactorización (debe producir la misma salida):
// const processor = new OrderProcessor(...);
// processor.process(order);
TS,
            ],
            // Lección 4: Patrones Creacionales
            [
                'lesson_id'    => $lessons->get(4)?->id,
                'title'        => 'Factory Method + Builder',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Factory Method + Builder

Implementa dos patrones creacionales para configurar conexiones de base de datos.

**Parte 1 — Factory Method:**
- Interfaz `DatabaseConfig` con `host`, `port`, `database`, `ssl: boolean`
- Clase `DatabaseConfigFactory` con método estático `create(type: 'postgres' | 'mysql' | 'sqlite'): DatabaseConfig`
- Cada tipo tiene sus valores por defecto diferentes

**Parte 2 — Builder:**
- Clase `HttpRequestBuilder` con métodos encadenables:
  - `url(u: string)`, `method(m: string)`, `header(key, value)`, `body(data)`, `timeout(ms)`, `retry(times)`
  - `build()`: valida que `url` y `method` estén presentes y retorna el objeto `HttpRequest`
MD,
                'starter_code' => <<<'TS'
interface DatabaseConfig {
  host:     string;
  port:     number;
  database: string;
  ssl:      boolean;
}

// TODO: Implementa DatabaseConfigFactory
class DatabaseConfigFactory {
  static create(type: 'postgres' | 'mysql' | 'sqlite'): DatabaseConfig {
    // postgres: host=localhost, port=5432, database=mydb, ssl=false
    // mysql:    host=localhost, port=3306, database=mydb, ssl=false
    // sqlite:   host=:memory:, port=0,    database=dev.db, ssl=false
    throw new Error('Implementa este método');
  }
}

interface HttpRequest {
  url:      string;
  method:   string;
  headers:  Record<string, string>;
  body?:    unknown;
  timeout:  number;
  retries:  number;
}

// TODO: Implementa HttpRequestBuilder
class HttpRequestBuilder {
  private _url?:     string;
  private _method?:  string;
  private _headers:  Record<string, string> = {};
  private _body?:    unknown;
  private _timeout   = 5000;
  private _retries   = 0;

  url(u: string): this        { /* TODO */ return this; }
  method(m: string): this     { /* TODO */ return this; }
  header(k: string, v: string): this { /* TODO */ return this; }
  body(data: unknown): this   { /* TODO */ return this; }
  timeout(ms: number): this   { /* TODO */ return this; }
  retry(times: number): this  { /* TODO */ return this; }

  build(): HttpRequest {
    // TODO: validar que url y method existen
    throw new Error('Implementa este método');
  }
}

// Tests
const pgConfig = DatabaseConfigFactory.create('postgres');
console.log(pgConfig.port);   // 5432
console.log(pgConfig.ssl);    // false

const mysqlConfig = DatabaseConfigFactory.create('mysql');
console.log(mysqlConfig.port); // 3306

const req = new HttpRequestBuilder()
  .url('https://api.example.com/users')
  .method('POST')
  .header('Content-Type', 'application/json')
  .header('Authorization', 'Bearer token123')
  .body({ name: 'Ana' })
  .timeout(10000)
  .retry(3)
  .build();

console.log(req.method);               // POST
console.log(req.retries);             // 3
console.log(Object.keys(req.headers)); // ['Content-Type', 'Authorization']

try {
  new HttpRequestBuilder().build(); // debe lanzar error
} catch (e) {
  console.log('Error capturado:', (e as Error).message);
}
TS,
            ],
            // Lección 5: Patrones Estructurales
            [
                'lesson_id'    => $lessons->get(5)?->id,
                'title'        => 'Repository Pattern',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Repository Pattern

Implementa el patrón Repository para gestionar productos.

**Requisitos:**
- Interfaz `Product` con `id: number`, `name: string`, `priceCents: number`, `stock: number`, `category: string`
- Interfaz `ProductRepository` con:
  - `findById(id)`, `findAll(filter?)`, `findByCategory(cat)`, `save(data)`, `update(id, data)`, `delete(id)`
- Clase `InMemoryProductRepository` que implementa la interfaz usando un array
- Clase `ProductService` que recibe `ProductRepository` por constructor con método:
  - `purchaseProduct(productId, quantity)`: descuenta stock, lanza error si insuficiente

**El servicio no debe conocer la implementación del repositorio.**
MD,
                'starter_code' => <<<'TS'
interface Product {
  id:         number;
  name:       string;
  priceCents: number;
  stock:      number;
  category:   string;
}

interface ProductRepository {
  findById(id: number): Promise<Product | null>;
  findAll(filter?: Partial<Pick<Product, 'category'>>): Promise<Product[]>;
  findByCategory(category: string): Promise<Product[]>;
  save(data: Omit<Product, 'id'>): Promise<Product>;
  update(id: number, data: Partial<Product>): Promise<Product | null>;
  delete(id: number): Promise<boolean>;
}

// TODO: Implementa InMemoryProductRepository
class InMemoryProductRepository implements ProductRepository {
  private store: Product[] = [];
  private nextId = 1;

  async findById(id: number): Promise<Product | null> {
    // TODO
    return null;
  }

  async findAll(filter?: Partial<Pick<Product, 'category'>>): Promise<Product[]> {
    // TODO
    return [];
  }

  async findByCategory(category: string): Promise<Product[]> {
    // TODO
    return [];
  }

  async save(data: Omit<Product, 'id'>): Promise<Product> {
    // TODO
    throw new Error('No implementado');
  }

  async update(id: number, data: Partial<Product>): Promise<Product | null> {
    // TODO
    return null;
  }

  async delete(id: number): Promise<boolean> {
    // TODO
    return false;
  }
}

// TODO: Implementa ProductService
class ProductService {
  constructor(private repo: ProductRepository) {}

  async purchaseProduct(productId: number, quantity: number): Promise<Product> {
    // TODO: busca producto, valida stock, descuenta y guarda
    throw new Error('No implementado');
  }
}

// Tests
async function main() {
  const repo    = new InMemoryProductRepository();
  const service = new ProductService(repo);

  const p1 = await repo.save({ name: 'Laptop', priceCents: 99900, stock: 10, category: 'electronics' });
  const p2 = await repo.save({ name: 'Mouse',  priceCents: 2500,  stock: 50, category: 'electronics' });
  await repo.save({ name: 'Desk', priceCents: 45000, stock: 5, category: 'furniture' });

  console.log((await repo.findAll()).length);                    // 3
  console.log((await repo.findByCategory('electronics')).length); // 2

  const updated = await service.purchaseProduct(p1.id, 3);
  console.log(updated.stock); // 7

  try {
    await service.purchaseProduct(p2.id, 100); // stock insuficiente
  } catch (e) {
    console.log('Error:', (e as Error).message);
  }

  await repo.delete(p2.id);
  console.log((await repo.findAll()).length); // 2
}

main();
TS,
            ],
            // Lección 6: Patrones de Comportamiento
            [
                'lesson_id'    => $lessons->get(6)?->id,
                'title'        => 'Strategy + Observer',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Strategy + Observer

Combina los patrones Strategy y Observer en un sistema de precios con notificaciones.

**Parte 1 — Strategy (Descuentos):**
- Interfaz `PricingStrategy` con método `apply(basePrice: number, quantity: number): number`
- Implementa 3 estrategias: `StandardPricing`, `BulkPricing` (5%+ en 10+, 15%+ en 50+), `SeasonalPricing` (descuento fijo pasado como parámetro)
- Clase `PriceCalculator` que acepta una estrategia y tiene método `calculate(price, qty)`

**Parte 2 — Observer (Precio alertas):**
- Interfaz `PriceObserver` con método `onPriceCalculated(basePrice, finalPrice, strategy)`
- Clase `PriceCalculator` debe permitir `addObserver` y `removeObserver`
- Al calcular un precio, notifica a todos los observadores
MD,
                'starter_code' => <<<'TS'
// ── STRATEGY ──────────────────────────────────────────────────

interface PricingStrategy {
  name:   string;
  apply(basePrice: number, quantity: number): number;
}

class StandardPricing implements PricingStrategy {
  name = 'Standard';
  apply(basePrice: number, quantity: number): number {
    // TODO: precio sin descuento
    return 0;
  }
}

class BulkPricing implements PricingStrategy {
  name = 'Bulk';
  apply(basePrice: number, quantity: number): number {
    // TODO: >= 50 unidades → 15% dto; >= 10 unidades → 5% dto; resto → sin dto
    return 0;
  }
}

class SeasonalPricing implements PricingStrategy {
  name: string;
  constructor(private discountPercent: number) {
    this.name = `Seasonal ${discountPercent}%`;
  }
  apply(basePrice: number, quantity: number): number {
    // TODO
    return 0;
  }
}

// ── OBSERVER ──────────────────────────────────────────────────

interface PriceObserver {
  onPriceCalculated(basePrice: number, finalPrice: number, strategyName: string): void;
}

class PriceCalculator {
  private observers: PriceObserver[] = [];

  constructor(private strategy: PricingStrategy) {}

  setStrategy(strategy: PricingStrategy): void {
    this.strategy = strategy;
  }

  addObserver(observer: PriceObserver): void {
    // TODO
  }

  removeObserver(observer: PriceObserver): void {
    // TODO
  }

  calculate(basePrice: number, quantity: number): number {
    // TODO: calcula precio, notifica observadores, retorna precio final
    return 0;
  }
}

// Tests
const logger: PriceObserver = {
  onPriceCalculated: (base, final, strategy) =>
    console.log(`[${strategy}] ${base * 1} × qty → ${final.toFixed(2)}€`),
};

const calculator = new PriceCalculator(new StandardPricing());
calculator.addObserver(logger);

calculator.calculate(100, 1);   // Standard: 100€
calculator.calculate(100, 10);  // Standard: 1000€

calculator.setStrategy(new BulkPricing());
calculator.calculate(100, 10);  // Bulk 5%: 950€
calculator.calculate(100, 50);  // Bulk 15%: 4250€

calculator.setStrategy(new SeasonalPricing(20));
calculator.calculate(100, 5);   // Seasonal 20%: 400€

calculator.removeObserver(logger);
calculator.calculate(100, 1);   // Sin log (observador eliminado)
TS,
            ],
            // Lección 7: Bases de Datos Relacionales
            [
                'lesson_id'    => $lessons->get(7)?->id,
                'title'        => 'Modelo Relacional en Memoria',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Modelo Relacional en Memoria

Simula un sistema de base de datos relacional en memoria que respete las restricciones de un modelo relacional.

**Modelo de datos:**
- `User`: `id`, `email` (único), `name`
- `Order`: `id`, `userId` (FK → User.id), `status`, `createdAt`
- `OrderItem`: `orderId` (FK → Order.id), `productName`, `quantity`, `unitPriceCents`

**Implementa las siguientes funciones:**
- `createUser(email, name)`: crea usuario, lanza error si email duplicado
- `createOrder(userId)`: crea pedido, lanza error si userId no existe (FK check)
- `addOrderItem(orderId, productName, quantity, unitPrice)`: añade ítem, valida FK
- `getUserOrders(userId)`: retorna pedidos de un usuario con sus ítems (JOIN simulado)
- `getOrderTotal(orderId)`: calcula el total de un pedido
- `deleteUser(userId)`: elimina el usuario y en cascade sus pedidos e ítems
MD,
                'starter_code' => <<<'TS'
interface User       { id: number; email: string; name: string }
interface Order      { id: number; userId: number; status: string; createdAt: Date }
interface OrderItem  { orderId: number; productName: string; quantity: number; unitPriceCents: number }

// Almacenes "en memoria" (simula tablas)
const users:      User[]      = [];
const orders:     Order[]     = [];
const orderItems: OrderItem[] = [];

let userSeq  = 1;
let orderSeq = 1;

function createUser(email: string, name: string): User {
  // TODO: validar email único, crear y guardar usuario
  throw new Error('No implementado');
}

function createOrder(userId: number): Order {
  // TODO: validar FK (userId debe existir), crear y guardar pedido
  throw new Error('No implementado');
}

function addOrderItem(orderId: number, productName: string, quantity: number, unitPriceCents: number): OrderItem {
  // TODO: validar FK (orderId debe existir), crear y guardar ítem
  throw new Error('No implementado');
}

interface OrderWithItems extends Order {
  items: OrderItem[];
}

function getUserOrders(userId: number): OrderWithItems[] {
  // TODO: JOIN simulado — retorna los pedidos del usuario con sus ítems
  return [];
}

function getOrderTotal(orderId: number): number {
  // TODO: SUM(quantity * unitPriceCents) para los ítems del pedido
  return 0;
}

function deleteUser(userId: number): void {
  // TODO: DELETE CASCADE — elimina ítems → pedidos → usuario
}

// Tests
const ana  = createUser('ana@test.com', 'Ana');
const bob  = createUser('bob@test.com', 'Bob');

const ord1 = createOrder(ana.id);
addOrderItem(ord1.id, 'Laptop', 1, 99900);
addOrderItem(ord1.id, 'Mouse',  2, 2500);

const ord2 = createOrder(ana.id);
addOrderItem(ord2.id, 'Teclado', 1, 7900);

console.log(getUserOrders(ana.id).length);    // 2
console.log(getOrderTotal(ord1.id));          // 104900 (99900 + 5000)

try {
  createUser('ana@test.com', 'Otra Ana');     // email duplicado
} catch (e) { console.log('Error:', (e as Error).message); }

try {
  createOrder(9999);                          // userId no existe
} catch (e) { console.log('Error FK:', (e as Error).message); }

deleteUser(ana.id);
console.log(getUserOrders(ana.id).length);    // 0 (cascade delete)
console.log(orders.filter(o => o.userId === ana.id).length); // 0
TS,
            ],
            // Lección 8: SQL Consultas
            [
                'lesson_id'    => $lessons->get(8)?->id,
                'title'        => 'Operaciones tipo SQL en Arrays',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Operaciones tipo SQL en Arrays

Implementa funciones que repliquen el comportamiento de operaciones SQL avanzadas usando arrays de TypeScript.

**Datos de entrada** (ya definidos en el starter):
- `users`: array de usuarios con `id`, `name`, `age`, `city`
- `orders`: array de pedidos con `id`, `userId`, `amount`, `status`, `date`

**Funciones a implementar:**
1. `innerJoin<T, U>(left, right, on)`: JOIN que retorna combinaciones donde `on(l, r)` es true
2. `groupBy<T, K>(arr, keyFn)`: agrupa en un Map por clave
3. `aggregate(orders)`: retorna `{ count, total, avg, max, min }` del campo `amount`
4. `topNByCity(n)`: retorna los N usuarios con mayor gasto por ciudad (requiere JOIN + GROUP + ORDER)
5. `ordersPaginated(page, limit)`: paginación por offset con órdenes más recientes primero
MD,
                'starter_code' => <<<'TS'
const users = [
  { id: 1, name: 'Ana',   age: 28, city: 'Madrid'    },
  { id: 2, name: 'Bob',   age: 34, city: 'Barcelona'  },
  { id: 3, name: 'Carol', age: 22, city: 'Madrid'    },
  { id: 4, name: 'Dave',  age: 41, city: 'Sevilla'   },
  { id: 5, name: 'Eve',   age: 29, city: 'Barcelona'  },
];

const orders = [
  { id: 1, userId: 1, amount: 150,  status: 'completed', date: '2024-03-01' },
  { id: 2, userId: 1, amount: 80,   status: 'completed', date: '2024-03-15' },
  { id: 3, userId: 2, amount: 300,  status: 'completed', date: '2024-02-10' },
  { id: 4, userId: 3, amount: 50,   status: 'cancelled', date: '2024-03-20' },
  { id: 5, userId: 3, amount: 120,  status: 'completed', date: '2024-03-25' },
  { id: 6, userId: 4, amount: 500,  status: 'completed', date: '2024-01-05' },
  { id: 7, userId: 5, amount: 200,  status: 'completed', date: '2024-03-10' },
  { id: 8, userId: 5, amount: 75,   status: 'pending',   date: '2024-03-28' },
];

// TODO: Implementa las siguientes funciones

function innerJoin<T, U>(
  left: T[],
  right: U[],
  on: (l: T, u: U) => boolean
): Array<T & U> {
  // TODO
  return [];
}

function groupBy<T, K extends string | number>(
  arr: T[],
  keyFn: (item: T) => K
): Map<K, T[]> {
  // TODO
  return new Map();
}

function aggregate(items: Array<{ amount: number }>) {
  // TODO: retorna { count, total, avg, max, min }
  return { count: 0, total: 0, avg: 0, max: 0, min: 0 };
}

function topNByCity(n: number): Array<{ city: string; userName: string; spent: number }> {
  // TODO: para cada ciudad, retorna los N usuarios con mayor gasto (solo órdenes 'completed')
  return [];
}

function ordersPaginated(page: number, limit: number) {
  // TODO: paginación — página 1 es la más reciente primero
  return { data: [] as typeof orders, total: 0, page, limit };
}

// Tests
const joined = innerJoin(users, orders, (u, o) => u.id === o.userId);
console.log(joined.length); // 8 (todos los pedidos tienen usuario válido)

const grouped = groupBy(orders, o => o.userId);
console.log(grouped.get(5)?.length); // 2 pedidos del usuario 5

const completedOrders = orders.filter(o => o.status === 'completed');
const stats = aggregate(completedOrders);
console.log(stats.count); // 6
console.log(stats.total); // 1350
console.log(stats.avg.toFixed(2)); // 225.00

const top = topNByCity(1);
console.log(top.find(t => t.city === 'Madrid')?.userName); // Ana (230 > Carol 120)

const page1 = ordersPaginated(1, 3);
console.log(page1.data[0].date); // más reciente: 2024-03-28
console.log(page1.total); // 8
TS,
            ],
            // Lección 9: Transacciones ACID
            [
                'lesson_id'    => $lessons->get(9)?->id,
                'title'        => 'Sistema de Transferencias con Rollback',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Sistema de Transferencias con Rollback

Implementa un sistema de cuentas bancarias que garantice propiedades ACID simulando una base de datos en memoria.

**Requisitos:**
- Clase `MemoryDB` con métodos `begin()`, `commit()`, `rollback()` que guarden/restauren estado
- Clase `AccountService` con:
  - `createAccount(id, owner, initialBalance)`: crea cuenta
  - `getAccount(id)`: retorna cuenta o lanza error
  - `transfer(fromId, toId, amount)`: transfiere fondos de forma atómica
    - Si alguno de los pasos falla, hace rollback (ambas cuentas quedan como estaban)
  - `getBalance(id)`: retorna el saldo actual

**La transferencia debe ser ATÓMICA**: si el débito tiene éxito pero el crédito falla (ej: cuenta destino no existe), el débito debe revertirse.
MD,
                'starter_code' => <<<'TS'
interface Account {
  id:            string;
  owner:         string;
  balanceCents:  number;
}

class MemoryDB {
  private state: Map<string, Account> = new Map();
  private snapshot: Map<string, Account> | null = null;

  begin(): void {
    // TODO: guarda una copia profunda del estado actual
  }

  commit(): void {
    // TODO: descarta el snapshot (los cambios son permanentes)
  }

  rollback(): void {
    // TODO: restaura el estado al snapshot guardado en begin()
  }

  getAccount(id: string): Account | undefined {
    return this.state.get(id);
  }

  setAccount(account: Account): void {
    this.state.set(account.id, { ...account });
  }
}

class AccountService {
  constructor(private db: MemoryDB) {}

  createAccount(id: string, owner: string, initialBalanceCents: number): Account {
    // TODO: validar balance >= 0, crear y guardar cuenta
    throw new Error('No implementado');
  }

  getAccount(id: string): Account {
    // TODO: retorna cuenta o lanza Error si no existe
    throw new Error('No implementado');
  }

  transfer(fromId: string, toId: string, amountCents: number): void {
    if (amountCents <= 0) throw new Error('El monto debe ser positivo');

    // TODO: implementa la transferencia ATÓMICA
    // 1. db.begin()
    // 2. Obtener cuenta origen
    // 3. Validar fondos suficientes
    // 4. Débito (origin - amount)
    // 5. Obtener cuenta destino (puede no existir → lanzará error)
    // 6. Crédito (destino + amount)
    // 7. db.commit()
    // Si CUALQUIER paso lanza error → db.rollback() y re-lanzar
  }

  getBalance(id: string): number {
    return this.getAccount(id).balanceCents;
  }
}

// Tests
const db      = new MemoryDB();
const service = new AccountService(db);

service.createAccount('ACC-1', 'Ana', 100000);  // 1000€
service.createAccount('ACC-2', 'Bob', 50000);   // 500€

console.log(service.getBalance('ACC-1')); // 100000

service.transfer('ACC-1', 'ACC-2', 30000);
console.log(service.getBalance('ACC-1')); // 70000
console.log(service.getBalance('ACC-2')); // 80000

// Transferencia que falla (fondos insuficientes) → ROLLBACK
try {
  service.transfer('ACC-2', 'ACC-1', 200000); // más de lo que tiene Bob
} catch (e) {
  console.log('Error:', (e as Error).message);
}
console.log(service.getBalance('ACC-2')); // 80000 (sin cambios, rollback)

// Transferencia a cuenta que no existe → ROLLBACK
try {
  service.transfer('ACC-1', 'ACC-INEXISTENTE', 10000);
} catch (e) {
  console.log('Error:', (e as Error).message);
}
console.log(service.getBalance('ACC-1')); // 70000 (sin cambios, rollback)
TS,
            ],
            // Lección 10: ORM
            [
                'lesson_id'    => $lessons->get(10)?->id,
                'title'        => 'Data Mapper con Lazy/Eager Loading',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Data Mapper con Lazy/Eager Loading

Implementa el patrón Data Mapper con soporte para lazy y eager loading de relaciones.

**Entidades:**
- `Author`: `id`, `name`, `email`
- `Book`: `id`, `authorId`, `title`, `year`, `priceCents`

**Mapper:**
- `AuthorMapper` con `toDomain(row)` y `toDatabase(author)`
- `BookMapper` con `toDomain(row)` y `toDatabase(book)`

**Repository con eager loading:**
- `AuthorRepository.findWithBooks(id)`: retorna autor con array `books` cargado
- `AuthorRepository.findAll()`: retorna autores SIN books (lazy - no carga relaciones)
- `BookRepository.findByAuthor(authorId)`: retorna libros de un autor

**Demuestra el N+1 problem y su solución:**
- `getBooksNPlusOne(authorIds)`: hace N+1 queries (log cada una)
- `getBooksOptimized(authorIds)`: hace 1 sola consulta
MD,
                'starter_code' => <<<'TS'
// Tipos de dominio (no conocen la BD)
interface Author { id: number; name: string; email: string }
interface Book   { id: number; authorId: number; title: string; year: number; priceCents: number }
interface AuthorWithBooks extends Author { books: Book[] }

// Simulación de "base de datos"
const db = {
  authors: [
    { id: 1, name: 'Clean Code Author', email: 'robert@example.com' },
    { id: 2, name: 'SICP Author',       email: 'harold@example.com' },
  ],
  books: [
    { id: 1, author_id: 1, title: 'Clean Code',     year: 2008, price_cents: 3500 },
    { id: 2, author_id: 1, title: 'Clean Coder',    year: 2011, price_cents: 3200 },
    { id: 3, author_id: 1, title: 'Clean Architecture', year: 2017, price_cents: 3800 },
    { id: 4, author_id: 2, title: 'SICP',           year: 1984, price_cents: 4500 },
  ],
  queryLog: [] as string[],
  query(sql: string, params?: unknown[]) {
    this.queryLog.push(sql + (params ? ` -- params: ${JSON.stringify(params)}` : ''));
  }
};

// TODO: Implementa los mappers
class AuthorMapper {
  toDomain(row: { id: number; name: string; email: string }): Author {
    // TODO
    return {} as Author;
  }
}

class BookMapper {
  toDomain(row: { id: number; author_id: number; title: string; year: number; price_cents: number }): Book {
    // TODO: mapea author_id → authorId, price_cents → priceCents
    return {} as Book;
  }
}

// TODO: Implementa los repositorios
class AuthorRepository {
  private mapper = new AuthorMapper();
  private bookMapper = new BookMapper();

  findAll(): Author[] {
    // TODO: log query, retorna todos los autores (sin books)
    return [];
  }

  findWithBooks(id: number): AuthorWithBooks | null {
    // TODO: busca autor + todos sus libros (1 query simulada con JOIN o 2 queries)
    return null;
  }
}

class BookRepository {
  private mapper = new BookMapper();

  findByAuthor(authorId: number): Book[] {
    // TODO: filtra books por authorId, log query
    return [];
  }
}

// Demostrar N+1 problem
function getBooksNPlusOne(authorIds: number[]): Map<number, Book[]> {
  const bookRepo = new BookRepository();
  const result   = new Map<number, Book[]>();

  db.queryLog.length = 0; // limpiar log

  for (const id of authorIds) {
    // TODO: hace 1 query POR cada autor → N+1
    result.set(id, bookRepo.findByAuthor(id));
  }

  console.log(`[N+1] ${db.queryLog.length} queries para ${authorIds.length} autores`);
  return result;
}

function getBooksOptimized(authorIds: number[]): Map<number, Book[]> {
  db.queryLog.length = 0;
  const mapper = new BookMapper();

  // TODO: 1 sola query → filtra en memoria → agrupa por authorId
  const allBooks: Book[] = [];

  console.log(`[Optimized] ${db.queryLog.length} query para ${authorIds.length} autores`);

  const result = new Map<number, Book[]>();
  for (const book of allBooks) {
    const list = result.get(book.authorId) ?? [];
    list.push(book);
    result.set(book.authorId, list);
  }
  return result;
}

// Tests
const authorRepo = new AuthorRepository();
const authors    = authorRepo.findAll();
console.log(authors.length); // 2

const withBooks = authorRepo.findWithBooks(1);
console.log(withBooks?.books.length); // 3

getBooksNPlusOne([1, 2]);    // 2 queries (N+1 con N=2 autores → 1+2=3 en un caso real)
getBooksOptimized([1, 2]);   // 1 query
TS,
            ],
            // Lección 11: Arquitectura en Capas
            [
                'lesson_id'    => $lessons->get(11)?->id,
                'title'        => 'Clean Architecture: Caso de Uso',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Clean Architecture: Caso de Uso

Implementa un sistema de gestión de tareas siguiendo los principios de Clean Architecture.

**Capas:**
1. **Entities** (dominio puro): clase `Task` con validaciones
2. **Use Cases**: `CreateTaskUseCase`, `CompleteTaskUseCase`, `GetUserTasksUseCase`
3. **Interface Adapters**: interfaces `TaskRepository`, `NotificationPort`
4. **Frameworks**: `InMemoryTaskRepository`, `ConsoleNotification`

**Entidad `Task`:**
- `id: string`, `userId: number`, `title: string`, `status: 'pending' | 'done'`, `createdAt: Date`
- Método `complete()`: cambia status a 'done', lanza error si ya estaba completada
- Validación en constructor: title no puede estar vacío

**Regla**: las entidades y casos de uso no importan frameworks ni implementaciones concretas.
MD,
                'starter_code' => <<<'TS'
// ── ENTITIES ───────────────────────────────────────────────────

class Task {
  public status: 'pending' | 'done' = 'pending';
  public readonly createdAt: Date    = new Date();

  constructor(
    public readonly id:     string,
    public readonly userId: number,
    public title:           string
  ) {
    // TODO: validar que title no esté vacío (trim)
  }

  complete(): void {
    // TODO: cambia status, lanza error si ya está done
  }

  isPending(): boolean { return this.status === 'pending'; }
}

// ── PORTS (interfaces de la capa application) ──────────────────

interface TaskRepository {
  save(task: Task):               Promise<Task>;
  findById(id: string):           Promise<Task | null>;
  findByUser(userId: number):     Promise<Task[]>;
  update(task: Task):             Promise<Task>;
}

interface NotificationPort {
  notifyTaskCompleted(userId: number, taskTitle: string): Promise<void>;
}

// ── USE CASES ──────────────────────────────────────────────────

class CreateTaskUseCase {
  constructor(private repo: TaskRepository) {}

  async execute(userId: number, title: string): Promise<Task> {
    // TODO: crea Task con id = crypto.randomUUID() (o Date.now().toString())
    // guarda y retorna
    throw new Error('No implementado');
  }
}

class CompleteTaskUseCase {
  constructor(
    private repo:   TaskRepository,
    private notify: NotificationPort
  ) {}

  async execute(taskId: string, requestingUserId: number): Promise<Task> {
    // TODO:
    // 1. busca la tarea
    // 2. valida que pertenece al usuario solicitante
    // 3. llama a task.complete()
    // 4. guarda en repo
    // 5. notifica
    throw new Error('No implementado');
  }
}

class GetUserTasksUseCase {
  constructor(private repo: TaskRepository) {}

  async execute(userId: number, filter?: 'pending' | 'done' | 'all'): Promise<Task[]> {
    // TODO: obtiene tareas del usuario, filtra por status si se especifica
    throw new Error('No implementado');
  }
}

// ── INFRASTRUCTURE ─────────────────────────────────────────────

class InMemoryTaskRepository implements TaskRepository {
  private store: Map<string, Task> = new Map();

  async save(task: Task): Promise<Task>         { this.store.set(task.id, task); return task; }
  async findById(id: string): Promise<Task | null> { return this.store.get(id) ?? null; }
  async findByUser(userId: number): Promise<Task[]> {
    return [...this.store.values()].filter(t => t.userId === userId);
  }
  async update(task: Task): Promise<Task>       { this.store.set(task.id, task); return task; }
}

class ConsoleNotification implements NotificationPort {
  async notifyTaskCompleted(userId: number, taskTitle: string): Promise<void> {
    console.log(`[Notify] Usuario ${userId}: tarea "${taskTitle}" completada`);
  }
}

// ── TESTS ──────────────────────────────────────────────────────

async function main() {
  const repo     = new InMemoryTaskRepository();
  const notify   = new ConsoleNotification();
  const create   = new CreateTaskUseCase(repo);
  const complete = new CompleteTaskUseCase(repo, notify);
  const getTasks = new GetUserTasksUseCase(repo);

  const t1 = await create.execute(1, 'Estudiar Clean Architecture');
  const t2 = await create.execute(1, 'Hacer ejercicios de TypeScript');
  const t3 = await create.execute(2, 'Tarea de otro usuario');

  console.log((await getTasks.execute(1)).length);             // 2
  console.log((await getTasks.execute(1, 'pending')).length);  // 2

  await complete.execute(t1.id, 1);

  console.log((await getTasks.execute(1, 'done')).length);     // 1
  console.log((await getTasks.execute(1, 'pending')).length);  // 1

  try {
    await complete.execute(t1.id, 1); // ya completada
  } catch (e) { console.log('Error:', (e as Error).message); }

  try {
    await complete.execute(t3.id, 1); // no pertenece al usuario 1
  } catch (e) { console.log('Error de autorización:', (e as Error).message); }

  try {
    await create.execute(1, '   '); // título vacío
  } catch (e) { console.log('Error de validación:', (e as Error).message); }
}

main();
TS,
            ],
            // Lección 12: Inyección de Dependencias
            [
                'lesson_id'    => $lessons->get(12)?->id,
                'title'        => 'Contenedor IoC',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Contenedor IoC

Implementa un contenedor de Inversión de Control (IoC) sencillo que gestione el registro y resolución de dependencias.

**Requisitos del contenedor:**
- `register(token, factory, options?)`: registra una dependencia con su factory
- `singleton(token, factory)`: registra como singleton (misma instancia siempre)
- `resolve<T>(token)`: resuelve la dependencia, lanza error si no está registrada
- Para singletons: la instancia se crea en el primer `resolve`, no al registrar
- Para transient: nueva instancia en cada `resolve`

**Demuestra con un ejemplo real:**
- `Logger` (singleton), `HttpClient` (singleton), `UserService` (transient)
- El `UserService` depende de `Logger` y `HttpClient`
- Verifica que dos `resolve<Logger>()` devuelven **la misma instancia**
- Verifica que dos `resolve<UserService>()` devuelven **instancias distintas**
MD,
                'starter_code' => <<<'TS'
type Token     = string | symbol;
type Factory<T> = () => T;

interface Registration<T> {
  factory:   Factory<T>;
  singleton: boolean;
  instance?: T;
}

class Container {
  private registry = new Map<Token, Registration<unknown>>();

  register<T>(token: Token, factory: Factory<T>, options: { singleton?: boolean } = {}): this {
    // TODO: guarda en registry
    return this;
  }

  singleton<T>(token: Token, factory: Factory<T>): this {
    // TODO: llama a register con singleton: true
    return this;
  }

  resolve<T>(token: Token): T {
    // TODO:
    // - lanza Error si el token no está registrado
    // - para singleton: crea la instancia si no existe, luego la retorna siempre
    // - para transient: crea una nueva instancia en cada llamada
    throw new Error('No implementado');
  }

  // Opcional: limpia las instancias singleton (útil en tests)
  reset(): void {
    for (const [, reg] of this.registry) {
      reg.instance = undefined;
    }
  }
}

// ── Clases de ejemplo ─────────────────────────────────────────

class Logger {
  private id = Math.random().toString(36).slice(2, 8);
  log(msg: string) { console.log(`[Logger:${this.id}] ${msg}`); }
}

class HttpClient {
  constructor(private logger: Logger) {}
  get(url: string) { this.logger.log(`GET ${url}`); return { status: 200, data: {} }; }
}

class UserService {
  private id = Math.random().toString(36).slice(2, 8);
  constructor(private logger: Logger, private http: HttpClient) {}

  getUser(userId: number) {
    this.logger.log(`[UserService:${this.id}] getUser(${userId})`);
    return this.http.get(`/api/users/${userId}`);
  }
}

// ── Tokens ────────────────────────────────────────────────────
const LOGGER      = Symbol('Logger');
const HTTP_CLIENT = Symbol('HttpClient');
const USER_SVC    = Symbol('UserService');

// TODO: configura el contenedor
const container = new Container();

// Logger → singleton
// HttpClient → singleton, depende de Logger
// UserService → transient, depende de Logger y HttpClient

// ── Tests ─────────────────────────────────────────────────────
const logger1 = container.resolve<Logger>(LOGGER);
const logger2 = container.resolve<Logger>(LOGGER);
console.log('Misma instancia Logger:', logger1 === logger2);   // true

const userService1 = container.resolve<UserService>(USER_SVC);
const userService2 = container.resolve<UserService>(USER_SVC);
console.log('Distinta instancia UserService:', userService1 !== userService2); // true

userService1.getUser(42);

try {
  container.resolve(Symbol('noExiste'));
} catch (e) {
  console.log('Error esperado:', (e as Error).message);
}
TS,
            ],
            // Lección 13: API REST
            [
                'lesson_id'    => $lessons->get(13)?->id,
                'title'        => 'Router REST con Validación',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Router REST con Validación

Implementa un mini router que simule el comportamiento de una API REST con validación y respuestas estandarizadas.

**Requisitos:**
- Clase `Router` con métodos `get`, `post`, `put`, `patch`, `delete`
- Clase `Request` con `params`, `query`, `body`, `headers`
- Clase `Response` con métodos `status(code)`, `json(data)`, `send()`
- Middleware de validación `validateBody(schema)` que verifica campos requeridos
- Función `paginate(data, page, limit)` que retorna respuesta paginada
- La respuesta de error debe seguir el formato `{ error: { code, message, details? } }`

**Implementa los siguientes endpoints:**
- `GET /users` con paginación por query params `?page=1&limit=10`
- `POST /users` con validación de `email` y `name`
- `GET /users/:id` con manejo de 404
- `DELETE /users/:id` devuelve 204
MD,
                'starter_code' => <<<'TS'
// ── Tipos base ────────────────────────────────────────────────

interface ApiRequest {
  params:  Record<string, string>;
  query:   Record<string, string>;
  body:    Record<string, unknown>;
  headers: Record<string, string>;
}

interface ApiResponse {
  statusCode: number;
  body:       unknown;
}

type Handler = (req: ApiRequest) => ApiResponse;

// ── Router ────────────────────────────────────────────────────

class Router {
  private routes: Array<{ method: string; path: string; handler: Handler }> = [];

  get(path: string,    handler: Handler): void { this.routes.push({ method: 'GET',    path, handler }); }
  post(path: string,   handler: Handler): void { this.routes.push({ method: 'POST',   path, handler }); }
  put(path: string,    handler: Handler): void { this.routes.push({ method: 'PUT',    path, handler }); }
  patch(path: string,  handler: Handler): void { this.routes.push({ method: 'PATCH',  path, handler }); }
  delete(path: string, handler: Handler): void { this.routes.push({ method: 'DELETE', path, handler }); }

  dispatch(method: string, path: string, req: Omit<ApiRequest, 'params'>): ApiResponse {
    // TODO:
    // 1. Busca la ruta que coincide (considera :param en el path)
    // 2. Extrae los params del path (ej: /users/42 → params.id = '42')
    // 3. Llama al handler con el request completo
    // 4. Si no hay ruta → retorna 404
    return { statusCode: 404, body: { error: { code: 'NOT_FOUND', message: 'Ruta no encontrada' } } };
  }
}

// ── Helpers ───────────────────────────────────────────────────

function ok(data: unknown, statusCode = 200): ApiResponse {
  return { statusCode, body: { data } };
}

function error(code: string, message: string, statusCode: number, details?: unknown): ApiResponse {
  return { statusCode, body: { error: { code, message, details } } };
}

function paginate<T>(data: T[], total: number, page: number, limit: number): ApiResponse {
  // TODO: retorna respuesta paginada con meta.page, meta.totalPages, etc.
  return ok({ data, meta: { page, limit, total, totalPages: Math.ceil(total / limit) } });
}

function validateBody(required: string[]): (handler: Handler) => Handler {
  // TODO: retorna un middleware que verifica que los campos requeridos existen en req.body
  // Si falta alguno → retorna 422 con details: { field: ['campo requerido'] }
  return (handler) => handler;
}

// ── Datos en memoria ─────────────────────────────────────────

interface User { id: number; email: string; name: string }
const users: User[] = [
  { id: 1, email: 'ana@test.com', name: 'Ana'   },
  { id: 2, email: 'bob@test.com', name: 'Bob'   },
  { id: 3, email: 'carol@test.com', name: 'Carol' },
];
let nextId = 4;

// ── Endpoints ─────────────────────────────────────────────────

const router = new Router();

router.get('/users', (req) => {
  const page  = parseInt(req.query.page  ?? '1');
  const limit = parseInt(req.query.limit ?? '10');
  // TODO: aplica paginación
  return ok(users);
});

router.post('/users', validateBody(['email', 'name'])((req) => {
  // TODO: crea usuario, verifica email único, retorna 201
  return ok({}, 201);
}));

router.get('/users/:id', (req) => {
  // TODO: busca por id, retorna 404 si no existe
  return ok(null);
});

router.delete('/users/:id', (req) => {
  // TODO: elimina usuario, retorna 204
  return { statusCode: 204, body: null };
});

// ── Tests ─────────────────────────────────────────────────────

const baseReq = { query: {}, body: {}, headers: {} };

// GET /users?page=1&limit=2
let res = router.dispatch('GET', '/users', { ...baseReq, query: { page: '1', limit: '2' } });
console.log(res.statusCode); // 200

// GET /users/1
res = router.dispatch('GET', '/users/1', baseReq);
console.log(res.statusCode); // 200
console.log((res.body as any).data?.name); // Ana

// GET /users/999
res = router.dispatch('GET', '/users/999', baseReq);
console.log(res.statusCode); // 404

// POST /users sin body → 422
res = router.dispatch('POST', '/users', { ...baseReq, body: {} });
console.log(res.statusCode); // 422

// POST /users con body válido → 201
res = router.dispatch('POST', '/users', { ...baseReq, body: { email: 'dave@test.com', name: 'Dave' } });
console.log(res.statusCode); // 201

// DELETE /users/1
res = router.dispatch('DELETE', '/users/1', baseReq);
console.log(res.statusCode); // 204
TS,
            ],
            // Lección 14: Seguridad
            [
                'lesson_id'    => $lessons->get(14)?->id,
                'title'        => 'Validación y Rate Limiting',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Validación, Sanitización y Rate Limiting

Implementa un sistema de seguridad para un endpoint de login.

**Parte 1 — Validación y sanitización:**
- Función `validateEmail(email)`: verifica formato RFC básico
- Función `validatePassword(password)`: verifica longitud mínima 8, al menos 1 mayúscula, 1 número, 1 símbolo
- Función `sanitizeHtml(input)`: elimina tags HTML y secuencias `<script>`
- Función `isValidInput(obj, schema)`: valida un objeto contra un esquema de tipos

**Parte 2 — Rate Limiting:**
- Clase `RateLimiter` con:
  - `constructor(maxAttempts: number, windowMs: number)`
  - `check(key: string): boolean`: retorna `true` si está permitido, `false` si límite superado
  - `getRemainingAttempts(key: string): number`
  - `reset(key: string)`: limpia el contador (ej: al hacer login exitoso)

**Demuestra con un login simulado** que combina validación + rate limiting.
MD,
                'starter_code' => <<<'TS'
// ── Validación ────────────────────────────────────────────────

function validateEmail(email: string): { valid: boolean; error?: string } {
  // TODO: verifica que tiene formato básico user@domain.tld
  // Sin librerías externas: usa una regex
  return { valid: false };
}

function validatePassword(password: string): { valid: boolean; errors: string[] } {
  const errors: string[] = [];
  // TODO: añade errores para cada condición que no se cumple:
  // - Mínimo 8 caracteres
  // - Al menos 1 mayúscula (A-Z)
  // - Al menos 1 número (0-9)
  // - Al menos 1 símbolo (!@#$%^&*)
  return { valid: errors.length === 0, errors };
}

function sanitizeHtml(input: string): string {
  // TODO: elimina <tags> y sus contenidos si son scripts
  // Mínimo: reemplaza < y > con entidades HTML: &lt; &gt;
  return input;
}

type SchemaType = 'string' | 'number' | 'boolean';
type Schema = Record<string, { type: SchemaType; required?: boolean; maxLength?: number }>;

function validateObject(
  obj: Record<string, unknown>,
  schema: Schema
): { valid: boolean; errors: Record<string, string[]> } {
  const errors: Record<string, string[]> = {};
  // TODO: valida tipo, required y maxLength para cada campo del schema
  return { valid: Object.keys(errors).length === 0, errors };
}

// ── Rate Limiter ──────────────────────────────────────────────

class RateLimiter {
  private windows = new Map<string, { attempts: number; resetAt: number }>();

  constructor(
    private maxAttempts: number,
    private windowMs:    number
  ) {}

  check(key: string): boolean {
    // TODO:
    // - Si no hay ventana o expiró → crea nueva y permite
    // - Si hay ventana activa → incrementa contador
    // - Si contador > maxAttempts → bloquea (retorna false)
    return true;
  }

  getRemainingAttempts(key: string): number {
    // TODO
    return this.maxAttempts;
  }

  reset(key: string): void {
    // TODO: elimina la ventana del key
  }
}

// ── Login simulado ────────────────────────────────────────────

const loginLimiter = new RateLimiter(5, 60_000);  // 5 intentos / minuto

const users = new Map([
  ['ana@test.com', { passwordHash: 'hashedPass123' }],  // simplificado
]);

function login(email: string, password: string, ip: string): { success: boolean; message: string } {
  // TODO:
  // 1. Verificar rate limit para el IP
  // 2. Validar formato de email
  // 3. Buscar usuario (retorna 'Credenciales inválidas' tanto si no existe como si la pass es incorrecta)
  // 4. Si login exitoso → resetear el rate limiter
  return { success: false, message: '' };
}

// Tests
console.log(validateEmail('ana@test.com').valid);    // true
console.log(validateEmail('no-es-email').valid);     // false

const passResult = validatePassword('weak');
console.log(passResult.valid);                        // false
console.log(passResult.errors.length);               // >= 3

console.log(sanitizeHtml('<b>Hola</b>'));             // &lt;b&gt;Hola&lt;/b&gt; (o similar)

const schema: Schema = {
  email: { type: 'string', required: true, maxLength: 255 },
  age:   { type: 'number', required: true },
};
const v1 = validateObject({ email: 'ana@test.com', age: 25 }, schema);
console.log(v1.valid); // true
const v2 = validateObject({ email: 'x'.repeat(300), age: 'no es número' }, schema);
console.log(v2.valid); // false

// Rate limiter: 5 intentos de login fallidos → bloqueado
for (let i = 0; i < 6; i++) {
  const result = login('ana@test.com', 'wrongpass', '192.168.1.1');
  console.log(`Intento ${i + 1}: ${result.message}`);
}
TS,
            ],
            // Lección 15: Caché
            [
                'lesson_id'    => $lessons->get(15)?->id,
                'title'        => 'Cache-Aside con Invalidación por Tags',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Cache-Aside con Invalidación por Tags

Implementa un sistema de caché en memoria con soporte para TTL e invalidación por tags.

**Clase `Cache<T>`:**
- `set(key, value, ttlMs?, tags?)`: guarda valor con TTL opcional y tags
- `get(key): T | null`: retorna el valor si no ha expirado, null si expiró o no existe
- `del(key)`: elimina una entrada
- `invalidateByTag(tag)`: elimina todas las entradas que tienen ese tag
- `size()`: número de entradas activas (no expiradas)
- `stats()`: `{ hits, misses, evictions }` — métricas de uso

**Demuestra con un `UserCacheService`:**
- `getUser(id)`: cache-aside con TTL de 5 segundos (en el test usa tiempos cortos)
- `updateUser(id, data)`: actualiza en el "repo" e invalida la caché
- Usa tags `['users', 'user:${id}']` para poder invalidar individualmente o en grupo
MD,
                'starter_code' => <<<'TS'
interface CacheEntry<T> {
  value:     T;
  expiresAt: number | null;  // null = no expira
  tags:      string[];
}

class Cache<T> {
  private store = new Map<string, CacheEntry<T>>();
  private _hits     = 0;
  private _misses   = 0;
  private _evictions = 0;

  set(key: string, value: T, ttlMs?: number, tags: string[] = []): void {
    // TODO: guarda la entrada con expiresAt = Date.now() + ttlMs (o null si no se pasa ttl)
  }

  get(key: string): T | null {
    // TODO:
    // - Si no existe → _misses++, retorna null
    // - Si expiró → _evictions++, elimina la entrada, retorna null
    // - Si válida → _hits++, retorna el valor
    return null;
  }

  del(key: string): void {
    // TODO
  }

  invalidateByTag(tag: string): number {
    // TODO: elimina todas las entradas que tengan el tag especificado
    // Retorna el número de entradas eliminadas
    return 0;
  }

  size(): number {
    // TODO: cuenta solo las entradas no expiradas
    return 0;
  }

  stats() {
    return { hits: this._hits, misses: this._misses, evictions: this._evictions };
  }
}

// ── User Service con caché ────────────────────────────────────

interface User { id: number; name: string; email: string }

const fakeDb = new Map<number, User>([
  [1, { id: 1, name: 'Ana',   email: 'ana@test.com' }],
  [2, { id: 2, name: 'Bob',   email: 'bob@test.com' }],
  [3, { id: 3, name: 'Carol', email: 'carol@test.com' }],
]);
let dbQueryCount = 0;

class UserCacheService {
  private cache = new Cache<User>();

  getUser(id: number): User | null {
    const key = `user:${id}`;
    // TODO: cache-aside
    // 1. Intenta obtener del cache
    // 2. Si miss: consulta fakeDb (dbQueryCount++), guarda en cache (TTL: 300ms, tags: ['users', key])
    // 3. Retorna el usuario (puede ser null si no existe en la DB)
    return null;
  }

  updateUser(id: number, data: Partial<Omit<User, 'id'>>): User | null {
    const user = fakeDb.get(id);
    if (!user) return null;

    const updated = { ...user, ...data };
    fakeDb.set(id, updated);

    // TODO: invalida la entrada específica del cache
    return updated;
  }

  invalidateAll(): number {
    // TODO: invalida todas las entradas del tag 'users'
    return 0;
  }

  get cacheStats() { return this.cache.stats(); }
}

// Tests
async function sleep(ms: number) { return new Promise(r => setTimeout(r, ms)); }

async function main() {
  const service = new UserCacheService();

  // Primera llamada → DB
  dbQueryCount = 0;
  const u1 = service.getUser(1);
  console.log(u1?.name);        // Ana
  console.log(dbQueryCount);    // 1 (fue a la DB)

  // Segunda llamada → cache
  const u1b = service.getUser(1);
  console.log(u1b?.name);       // Ana
  console.log(dbQueryCount);    // 1 (del cache)

  console.log(service.cacheStats.hits);   // 1
  console.log(service.cacheStats.misses); // 1

  // Actualizar invalida la caché
  service.updateUser(1, { name: 'Ana García' });
  const u1c = service.getUser(1);
  console.log(u1c?.name);       // Ana García (fue a la DB de nuevo)
  console.log(dbQueryCount);    // 2

  // TTL expirado
  service.getUser(2); // guarda en cache
  await sleep(350);   // espera que expire el TTL (300ms)
  dbQueryCount = 0;
  service.getUser(2); // debe ir a la DB de nuevo (TTL expirado)
  console.log(dbQueryCount); // 1

  // Estadísticas
  console.log(service.cacheStats);
}

main();
TS,
            ],
            // Lección 16: Mensajería
            [
                'lesson_id'    => $lessons->get(16)?->id,
                'title'        => 'Event Bus Tipado',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Event Bus Tipado con Garantía de Entrega

Implementa un Event Bus con tipado fuerte, reintentos y registro de eventos fallidos.

**Clase `TypedEventBus<Events>`:**
- `on<K>(event, handler)`: suscribe un handler, retorna función para desuscribirse
- `once<K>(event, handler)`: suscribe un handler que se ejecuta solo UNA vez
- `emit<K>(event, payload)`: emite el evento a todos los handlers
- Los handlers fallidos se reintentarán hasta `maxRetries` veces con backoff exponencial
- Eventos fallidos (agotados los reintentos) se guardan en `deadLetterQueue`

**Demuestra con un flujo de pedido:**
1. Al crear un pedido → emite `order.created`
2. Handler de stock que puede fallar (simula fallo en los primeros 2 intentos)
3. Handler de email que siempre tiene éxito
4. Verifica que tras los reintentos el stock se descuenta
MD,
                'starter_code' => <<<'TS'
type EventMap = {
  'order.created':   { orderId: string; userId: number; items: Array<{ productId: string; qty: number }> };
  'order.cancelled': { orderId: string; reason: string };
  'user.registered': { userId: number; email: string };
};

interface FailedEvent {
  event:   string;
  payload: unknown;
  error:   string;
  at:      Date;
}

class TypedEventBus<Events extends Record<string, unknown>> {
  private handlers  = new Map<string, Array<{ fn: Function; once: boolean }>>();
  private _deadLetterQueue: FailedEvent[] = [];
  private maxRetries: number;
  private baseDelayMs: number;

  constructor(options: { maxRetries?: number; baseDelayMs?: number } = {}) {
    this.maxRetries  = options.maxRetries  ?? 3;
    this.baseDelayMs = options.baseDelayMs ?? 100;
  }

  on<K extends keyof Events>(
    event: K,
    handler: (payload: Events[K]) => void | Promise<void>
  ): () => void {
    // TODO: registra el handler, retorna función de desuscripción
    return () => {};
  }

  once<K extends keyof Events>(
    event: K,
    handler: (payload: Events[K]) => void | Promise<void>
  ): void {
    // TODO: registra el handler marcado como once=true
  }

  async emit<K extends keyof Events>(event: K, payload: Events[K]): Promise<void> {
    // TODO:
    // Para cada handler de este evento:
    //   - ejecuta con reintentos (máximo maxRetries)
    //   - backoff: espera baseDelayMs * 2^intento entre reintentos
    //   - si es once → elimínalo después de ejecutarse (exitoso o no)
    //   - si se agotan los reintentos → guarda en deadLetterQueue
  }

  get deadLetterQueue(): ReadonlyArray<FailedEvent> {
    return this._deadLetterQueue;
  }
}

// ── Tests ─────────────────────────────────────────────────────

async function main() {
  const bus = new TypedEventBus<EventMap>({ maxRetries: 3, baseDelayMs: 50 });

  let stockAttempts = 0;
  const stockHandler = async (payload: EventMap['order.created']) => {
    stockAttempts++;
    if (stockAttempts <= 2) throw new Error('Stock service temporarily unavailable');
    console.log(`[Stock] Descontando stock para ${payload.orderId} (intento ${stockAttempts})`);
  };

  const emailHandler = async (payload: EventMap['order.created']) => {
    console.log(`[Email] Confirmación enviada para ${payload.orderId} al usuario ${payload.userId}`);
  };

  bus.on('order.created', stockHandler);
  bus.on('order.created', emailHandler);

  // once: solo se ejecuta una vez
  bus.once('order.created', async ({ orderId }) => {
    console.log(`[Analytics] Primera vez que veo la orden ${orderId}`);
  });

  await bus.emit('order.created', {
    orderId: 'ORD-001',
    userId:  1,
    items:   [{ productId: 'P-1', qty: 2 }],
  });

  // El handler de analytics no debe ejecutarse en la segunda emisión
  await bus.emit('order.created', {
    orderId: 'ORD-002',
    userId:  2,
    items:   [{ productId: 'P-2', qty: 1 }],
  });

  console.log('Intentos de stock (debe ser >= 3):', stockAttempts);
  console.log('Dead letter queue:', bus.deadLetterQueue.length); // 0 (tuvo éxito al 3er intento)

  // Handler que siempre falla → acaba en dead letter queue
  bus.on('order.cancelled', async () => { throw new Error('Service down'); });
  await bus.emit('order.cancelled', { orderId: 'ORD-001', reason: 'Out of stock' });

  console.log('Dead letter entries:', bus.deadLetterQueue.length); // 1
  console.log('Dead letter event:', bus.deadLetterQueue[0]?.event); // order.cancelled
}

main();
TS,
            ],
            // Lección 17: Testing
            [
                'lesson_id'    => $lessons->get(17)?->id,
                'title'        => 'Tests para OrderService',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Tests para OrderService

Escribe una suite de tests completa para `OrderService` usando el patrón AAA (Arrange-Act-Assert).

**El `OrderService` ya está implementado.** Tu tarea es escribir los tests.

**Tests requeridos:**
1. `createOrder` — crea una orden con items válidos
2. `createOrder` — lanza error si el usuario no existe
3. `createOrder` — lanza error si algún producto no existe
4. `createOrder` — lanza error si quantity <= 0
5. `applyDiscount` — aplica descuento porcentual correctamente
6. `applyDiscount` — lanza error si descuento > 100 o < 0
7. `cancelOrder` — cancela una orden pendiente
8. `cancelOrder` — lanza error si la orden ya está completada
9. `getOrderTotal` — calcula el total correctamente (quantity × price)
10. `getOrderTotal` — retorna 0 si no hay items

**Los mocks de repositorios ya están preparados. Completa los tests.**
MD,
                'starter_code' => <<<'TS'
// ── Sistema bajo test ─────────────────────────────────────────

interface User    { id: number; name: string; email: string }
interface Product { id: number; name: string; priceCents: number }
interface OrderItem { productId: number; quantity: number; unitPriceCents: number }
interface Order {
  id:       string;
  userId:   number;
  items:    OrderItem[];
  status:   'pending' | 'completed' | 'cancelled';
  discount: number;  // 0-100
}

interface UserRepository    { findById(id: number): Promise<User | null> }
interface ProductRepository { findById(id: number): Promise<Product | null> }
interface OrderRepository   { save(order: Order): Promise<Order>; findById(id: string): Promise<Order | null> }

class OrderService {
  constructor(
    private users:    UserRepository,
    private products: ProductRepository,
    private orders:   OrderRepository
  ) {}

  async createOrder(userId: number, items: Array<{ productId: number; quantity: number }>): Promise<Order> {
    const user = await this.users.findById(userId);
    if (!user) throw new Error(`Usuario ${userId} no existe`);

    const orderItems: OrderItem[] = [];
    for (const item of items) {
      if (item.quantity <= 0) throw new Error('La cantidad debe ser mayor a 0');
      const product = await this.products.findById(item.productId);
      if (!product) throw new Error(`Producto ${item.productId} no existe`);
      orderItems.push({ productId: item.productId, quantity: item.quantity, unitPriceCents: product.priceCents });
    }

    return this.orders.save({
      id: `ORD-${Date.now()}`, userId, items: orderItems, status: 'pending', discount: 0
    });
  }

  async applyDiscount(orderId: string, discountPercent: number): Promise<Order> {
    if (discountPercent < 0 || discountPercent > 100) throw new Error('Descuento inválido (0-100)');
    const order = await this.orders.findById(orderId);
    if (!order) throw new Error('Orden no encontrada');
    return this.orders.save({ ...order, discount: discountPercent });
  }

  async cancelOrder(orderId: string): Promise<Order> {
    const order = await this.orders.findById(orderId);
    if (!order) throw new Error('Orden no encontrada');
    if (order.status === 'completed') throw new Error('No se puede cancelar una orden completada');
    return this.orders.save({ ...order, status: 'cancelled' });
  }

  getOrderTotal(order: Order): number {
    const subtotal = order.items.reduce((sum, i) => sum + i.quantity * i.unitPriceCents, 0);
    return subtotal * (1 - order.discount / 100);
  }
}

// ── Mocks ─────────────────────────────────────────────────────

function makeRepos(overrides: {
  user?: User | null;
  product?: Product | null;
  savedOrder?: Order;
}) {
  const defaultOrder: Order = {
    id: 'ORD-TEST', userId: 1, items: [], status: 'pending', discount: 0
  };

  const userRepo: UserRepository = {
    findById: async () => overrides.user !== undefined
      ? overrides.user
      : { id: 1, name: 'Ana', email: 'ana@test.com' }
  };

  const productRepo: ProductRepository = {
    findById: async () => overrides.product !== undefined
      ? overrides.product
      : { id: 1, name: 'Laptop', priceCents: 99900 }
  };

  const orderRepo: OrderRepository = {
    save:    async (o) => ({ ...o }),
    findById: async () => overrides.savedOrder ?? defaultOrder
  };

  return { userRepo, productRepo, orderRepo };
}

// ── Tests ─────────────────────────────────────────────────────
// Implementa cada función de test. Cada una debe:
// - Crear los mocks necesarios (Arrange)
// - Ejecutar la operación (Act)
// - Verificar el resultado con console.assert o throw si falla (Assert)

async function test_createOrder_success() {
  // TODO: test 1 — crea una orden correctamente
  // Assert: order.userId === 1, order.items.length === 1, order.status === 'pending'
  console.log('✅ test_createOrder_success');
}

async function test_createOrder_userNotFound() {
  // TODO: test 2 — lanza error si usuario no existe (user: null)
  // Assert: el error contiene "no existe"
  console.log('✅ test_createOrder_userNotFound');
}

async function test_createOrder_productNotFound() {
  // TODO: test 3 — lanza error si producto no existe (product: null)
  console.log('✅ test_createOrder_productNotFound');
}

async function test_createOrder_invalidQuantity() {
  // TODO: test 4 — lanza error si quantity <= 0
  console.log('✅ test_createOrder_invalidQuantity');
}

async function test_applyDiscount_valid() {
  // TODO: test 5 — aplica un descuento del 20%
  console.log('✅ test_applyDiscount_valid');
}

async function test_applyDiscount_invalid() {
  // TODO: test 6 — lanza error con descuento 150
  console.log('✅ test_applyDiscount_invalid');
}

async function test_cancelOrder_pending() {
  // TODO: test 7 — cancela una orden pending
  console.log('✅ test_cancelOrder_pending');
}

async function test_cancelOrder_completed() {
  // TODO: test 8 — lanza error al cancelar una orden completada
  // Pasa savedOrder con status: 'completed'
  console.log('✅ test_cancelOrder_completed');
}

async function test_getTotal_withItems() {
  // TODO: test 9 — calcula total: 2×99900 + 3×2500 = 207300
  console.log('✅ test_getTotal_withItems');
}

async function test_getTotal_empty() {
  // TODO: test 10 — retorna 0 con items vacíos
  console.log('✅ test_getTotal_empty');
}

// Ejecutar todos los tests
async function runTests() {
  const tests = [
    test_createOrder_success, test_createOrder_userNotFound,
    test_createOrder_productNotFound, test_createOrder_invalidQuantity,
    test_applyDiscount_valid, test_applyDiscount_invalid,
    test_cancelOrder_pending, test_cancelOrder_completed,
    test_getTotal_withItems, test_getTotal_empty,
  ];

  for (const test of tests) {
    try {
      await test();
    } catch (e) {
      console.error(`❌ ${test.name}: ${(e as Error).message}`);
    }
  }
}

runTests();
TS,
            ],
            // Lección 18: Performance
            [
                'lesson_id'    => $lessons->get(18)?->id,
                'title'        => 'Optimización: N+1 y Memoización',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Optimización: N+1 y Memoización

Implementa soluciones para los problemas de rendimiento más comunes en backend.

**Parte 1 — N+1 Problem:**
- Función `getBlogPostsSlow(authorIds)`: simula N+1 (1 query + N queries para comentarios)
- Función `getBlogPostsFast(authorIds)`: resuelve N+1 con 2 queries + agrupación en memoria
- Compara el número de queries generadas

**Parte 2 — Memoización:**
- Función genérica `memoize<T>(fn, options?)` con soporte para:
  - TTL (tiempo de vida de la caché)
  - Tamaño máximo de caché (LRU básico: elimina la entrada más antigua al llenarse)
  - Función de clave personalizada `keyFn`
- Demuestra con una función de Fibonacci memoizada

**Parte 3 — Paginación eficiente:**
- `paginateByOffset(data, page, limit)`: paginación clásica
- `paginateByIdCursor(data, afterId, limit)`: paginación por cursor (más eficiente)
MD,
                'starter_code' => <<<'TS'
// ── Datos simulados ───────────────────────────────────────────

interface Author { id: number; name: string }
interface Post   { id: number; authorId: number; title: string }
interface Comment { id: number; postId: number; text: string }

const authors: Author[] = Array.from({ length: 5 }, (_, i) => ({ id: i + 1, name: `Autor ${i + 1}` }));
const posts: Post[] = Array.from({ length: 20 }, (_, i) => ({
  id: i + 1, authorId: (i % 5) + 1, title: `Post ${i + 1}`
}));
const comments: Comment[] = Array.from({ length: 50 }, (_, i) => ({
  id: i + 1, postId: (i % 20) + 1, text: `Comentario ${i + 1}`
}));

let queryCount = 0;

function queryPosts(authorId: number): Post[] {
  queryCount++;
  return posts.filter(p => p.authorId === authorId);
}

function queryComments(postId: number): Comment[] {
  queryCount++;
  return comments.filter(c => c.postId === postId);
}

function queryCommentsByPostIds(postIds: number[]): Comment[] {
  queryCount++;
  return comments.filter(c => postIds.includes(c.postId));
}

// ── Parte 1: N+1 ─────────────────────────────────────────────

interface PostWithComments extends Post { comments: Comment[] }

function getBlogPostsSlow(authorIds: number[]): PostWithComments[] {
  // TODO: para cada author → queryPosts, para cada post → queryComments → N+1
  return [];
}

function getBlogPostsFast(authorIds: number[]): PostWithComments[] {
  // TODO:
  // 1. queryPosts con IN (simula: filtra posts cuyos authorId está en la lista) — 1 query
  // 2. queryCommentsByPostIds con los IDs de posts encontrados — 1 query
  // 3. Agrupa en memoria
  return [];
}

// ── Parte 2: Memoización ──────────────────────────────────────

interface MemoOptions<T extends unknown[]> {
  ttlMs?:  number;
  maxSize?: number;
  keyFn?:  (...args: T) => string;
}

function memoize<T extends unknown[], R>(
  fn:      (...args: T) => R,
  options: MemoOptions<T> = {}
): (...args: T) => R {
  const cache = new Map<string, { value: R; expiresAt: number | null; createdAt: number }>();
  const { ttlMs, maxSize, keyFn = (...args: T) => JSON.stringify(args) } = options;

  return function(...args: T): R {
    const key    = keyFn(...args);
    const cached = cache.get(key);
    const now    = Date.now();

    // TODO:
    // 1. Si existe y no ha expirado → retorna cached.value
    // 2. Si existe pero expiró → elimina del cache
    // 3. Si el cache está lleno (maxSize) → elimina la entrada más antigua
    // 4. Calcula el valor, guarda en cache, retorna
    return fn(...args);
  };
}

// ── Parte 3: Paginación ───────────────────────────────────────

function paginateByOffset<T>(data: T[], page: number, limit: number) {
  // TODO: retorna { data, total, page, limit, totalPages }
  return { data: [] as T[], total: data.length, page, limit, totalPages: 0 };
}

function paginateByIdCursor<T extends { id: number }>(
  data:    T[],
  afterId: number | null,
  limit:   number
) {
  // TODO: retorna los limit elementos con id > afterId (orden ascendente)
  // Incluye nextCursor: id del último elemento retornado (o null si no hay más)
  return { data: [] as T[], nextCursor: null as number | null, hasMore: false };
}

// ── Tests ─────────────────────────────────────────────────────

queryCount = 0;
const slowResult = getBlogPostsSlow([1, 2]);
console.log(`[Slow] Queries: ${queryCount}`); // Debería ser 1 + N posts

queryCount = 0;
const fastResult = getBlogPostsFast([1, 2]);
console.log(`[Fast] Queries: ${queryCount}`); // Debería ser 2

// Fibonacci con memoización
let fibCalls = 0;
const fib = memoize((n: number): number => {
  fibCalls++;
  if (n <= 1) return n;
  return fib(n - 1) + fib(n - 2);
}, { maxSize: 100 });

fibCalls = 0;
console.log(fib(10)); // 55
const callsWith10 = fibCalls;
fibCalls = 0;
console.log(fib(10)); // 55 (del cache)
console.log('Calls con cache:', fibCalls); // 0 (vino del cache)
console.log('Calls sin cache:', callsWith10);

// Paginación
const page1 = paginateByOffset(posts, 1, 5);
console.log(page1.data.length);   // 5
console.log(page1.totalPages);    // 4

const cursor1 = paginateByIdCursor(posts, null, 5);
console.log(cursor1.data[0].id);     // 1
console.log(cursor1.nextCursor);      // 5
const cursor2 = paginateByIdCursor(posts, cursor1.nextCursor!, 5);
console.log(cursor2.data[0].id);     // 6
TS,
            ],
            // Lección 19: Concurrencia
            [
                'lesson_id'    => $lessons->get(19)?->id,
                'title'        => 'Optimistic Locking',
                'language'     => 'typescript',
                'description'  => <<<'MD'
## Optimistic Locking y Operaciones Atómicas

Implementa un sistema de gestión de inventario que prevenga race conditions usando optimistic locking.

**Requisitos:**
- Interfaz `Product` con campo `version: number`
- Clase `InventoryStore` (BD en memoria) con operaciones atómicas
- Método `findForUpdate(id)`: retorna el producto con su versión actual
- Método `updateWithVersion(id, newData, expectedVersion)`: actualiza SOLO si la versión coincide, retorna `null` si hay conflicto (otro proceso actualizó antes)
- Función `purchaseWithRetry(store, productId, qty, maxRetries)`: intenta hacer la compra con reintentos en caso de conflicto de versión
- Función `simulateConcurrentPurchases(store, productId, buyers, qtyEach)`: simula compras concurrentes y verifica que el stock no quede negativo

**Demuestra que sin el locking el stock puede quedar negativo, pero con el optimistic lock no.**
MD,
                'starter_code' => <<<'TS'
interface Product {
  id:      number;
  name:    string;
  stock:   number;
  version: number;
}

class InventoryStore {
  private products = new Map<number, Product>();

  seed(products: Product[]): void {
    products.forEach(p => this.products.set(p.id, { ...p }));
  }

  findForUpdate(id: number): Product | null {
    const p = this.products.get(id);
    return p ? { ...p } : null;  // retorna copia para simular lectura de BD
  }

  updateWithVersion(
    id:              number,
    updates:         Partial<Omit<Product, 'id' | 'version'>>,
    expectedVersion: number
  ): Product | null {
    // TODO:
    // 1. Obtener el producto actual
    // 2. Si no existe → retorna null
    // 3. Si la versión actual !== expectedVersion → retorna null (conflicto)
    // 4. Si coincide → aplica updates, incrementa version, guarda y retorna
    return null;
  }

  getStock(id: number): number {
    return this.products.get(id)?.stock ?? 0;
  }
}

// ── Sin locking: INSEGURO ─────────────────────────────────────

async function purchaseUnsafe(
  store:     InventoryStore,
  productId: number,
  qty:       number,
  buyerName: string
): Promise<boolean> {
  // Simula latencia de red
  await new Promise(r => setTimeout(r, Math.random() * 10));

  const product = store.findForUpdate(productId);
  if (!product || product.stock < qty) return false;

  // Simula latencia de procesamiento (aquí ocurre la race condition)
  await new Promise(r => setTimeout(r, Math.random() * 10));

  // ❌ No verifica versión — otro proceso pudo haber modificado el stock
  store.updateWithVersion(productId, { stock: product.stock - qty }, -1); // fuerza actualización
  console.log(`[Unsafe] ${buyerName} compró ${qty}. Stock: ${store.getStock(productId)}`);
  return true;
}

// ── Con Optimistic Locking: SEGURO ────────────────────────────

async function purchaseWithRetry(
  store:      InventoryStore,
  productId:  number,
  qty:        number,
  buyerName:  string,
  maxRetries: number = 3
): Promise<boolean> {
  // TODO: implementa la compra con reintentos
  // Para cada intento:
  //   1. Lee el producto con findForUpdate (obtiene versión actual)
  //   2. Verifica que hay stock suficiente
  //   3. Intenta updateWithVersion con la versión leída
  //   4. Si retorna null (conflicto) → espera un poco y reintenta
  //   5. Si tiene éxito → log y retorna true
  //   6. Si se agotan los reintentos → retorna false
  return false;
}

// ── Simulación concurrente ────────────────────────────────────

async function simulateConcurrentPurchases(
  store:     InventoryStore,
  productId: number,
  buyers:    string[],
  qtyEach:   number
): Promise<{ success: number; failed: number; finalStock: number }> {
  // TODO: ejecuta todas las compras EN PARALELO (Promise.all)
  // Retorna cuántas tuvieron éxito, cuántas fallaron y el stock final
  return { success: 0, failed: 0, finalStock: store.getStock(productId) };
}

// ── Tests ─────────────────────────────────────────────────────

async function main() {
  const buyers = Array.from({ length: 10 }, (_, i) => `Buyer-${i + 1}`);

  // Escenario 1: sin locking (puede quedar stock negativo)
  const unsafeStore = new InventoryStore();
  unsafeStore.seed([{ id: 1, name: 'Laptop', stock: 5, version: 0 }]);

  // Simulamos directamente comprando 6 personas al mismo tiempo (hay 5 laptops)
  await Promise.all(buyers.slice(0, 6).map(b => purchaseUnsafe(unsafeStore, 1, 1, b)));
  console.log(`[Unsafe] Stock final: ${unsafeStore.getStock(1)}`);  // puede ser negativo!

  // Escenario 2: con optimistic locking (stock nunca negativo)
  const safeStore = new InventoryStore();
  safeStore.seed([{ id: 1, name: 'Laptop', stock: 5, version: 0 }]);

  const result = await simulateConcurrentPurchases(safeStore, 1, buyers, 1);

  console.log(`[Safe] Éxitos: ${result.success}, Fallidos: ${result.failed}`);
  console.log(`[Safe] Stock final: ${result.finalStock}`);
  console.log(`[Safe] Stock coherente: ${result.finalStock >= 0}`); // siempre true
  console.log(`[Safe] Éxitos + stock restante = 10 inicial: ${result.success + result.finalStock === 5}`); // true
}

main();
TS,
            ],
        ];

        foreach ($exercises as $ex) {
            if (empty($ex['lesson_id'])) continue;

            DB::table('lesson_exercises')->updateOrInsert(
                ['lesson_id' => $ex['lesson_id']],
                [
                    'title'        => $ex['title'],
                    'language'     => $ex['language'],
                    'description'  => $ex['description'],
                    'starter_code' => $ex['starter_code'],
                    'updated_at'   => now(),
                    'created_at'   => now(),
                ]
            );
        }

        $total = DB::table('lesson_exercises')
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->count();
        $this->command->info("Backend Concepts ejercicios: {$total} ejercicios cargados.");
    }
}
