# Principios SOLID

Los principios **SOLID** son cinco guías de diseño orientado a objetos formuladas por Robert C. Martin ("Uncle Bob"). Su objetivo es producir código más mantenible, flexible y fácil de testear.

| Letra | Principio | Pregunta clave |
|---|---|---|
| **S** | Single Responsibility | ¿Tiene mi clase una sola razón para cambiar? |
| **O** | Open/Closed | ¿Puedo extender sin modificar? |
| **L** | Liskov Substitution | ¿Puedo reemplazar la base por cualquier subclase? |
| **I** | Interface Segregation | ¿Mis interfaces son específicas o demasiado gordas? |
| **D** | Dependency Inversion | ¿Dependo de abstracciones, no de implementaciones? |

---

## S — Single Responsibility Principle (SRP)

> *Una clase debe tener una sola razón para cambiar.*

Una clase con múltiples responsabilidades se rompe cuando cambia cualquiera de ellas. Separarlas hace el código más cohesivo y testeable.

```typescript
// ❌ Viola SRP: esta clase hace demasiado
class UserBad {
  constructor(private name: string, private email: string) {}

  // Responsabilidad 1: lógica de dominio
  isValid(): boolean {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email);
  }

  // Responsabilidad 2: persistencia
  save(): void {
    console.log(`INSERT INTO users (name, email) VALUES ('${this.name}', '${this.email}')`);
  }

  // Responsabilidad 3: presentación/serialización
  toHtml(): string {
    return `<div><b>${this.name}</b> — ${this.email}</div>`;
  }

  // Responsabilidad 4: notificaciones
  sendWelcomeEmail(): void {
    console.log(`Enviando email a ${this.email}...`);
  }
}

// ✅ Cada clase tiene una responsabilidad
class User {
  constructor(public readonly name: string, public readonly email: string) {}

  isValid(): boolean {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email);
  }
}

class UserRepository {
  save(user: User): void {
    console.log(`INSERT INTO users VALUES ('${user.name}', '${user.email}')`);
  }
}

class UserPresenter {
  toHtml(user: User): string {
    return `<div><b>${user.name}</b> — ${user.email}</div>`;
  }
}

class WelcomeEmailService {
  send(user: User): void {
    console.log(`Enviando bienvenida a ${user.email}`);
  }
}
```

---

## O — Open/Closed Principle (OCP)

> *Las entidades de software deben estar abiertas para extensión, pero cerradas para modificación.*

Si cada vez que añades una funcionalidad nueva tienes que modificar código existente, tu diseño viola OCP y tiene riesgo de regresiones.

```typescript
// ❌ Viola OCP: añadir un nuevo tipo de descuento requiere modificar esta clase
class PricingBad {
  calculateDiscount(price: number, customerType: string): number {
    if (customerType === 'vip')      return price * 0.20;
    if (customerType === 'regular')  return price * 0.05;
    if (customerType === 'new')      return price * 0.10;
    // ← Cada nuevo tipo requiere tocar este método
    return 0;
  }
}

// ✅ OCP: extender añadiendo nuevas clases, sin tocar las existentes
interface DiscountStrategy {
  calculate(price: number): number;
  describe(): string;
}

class VipDiscount implements DiscountStrategy {
  calculate(price: number): number { return price * 0.20; }
  describe(): string { return '20% VIP'; }
}

class RegularDiscount implements DiscountStrategy {
  calculate(price: number): number { return price * 0.05; }
  describe(): string { return '5% cliente regular'; }
}

class SeasonalDiscount implements DiscountStrategy {
  constructor(private percent: number, private season: string) {}
  calculate(price: number): number { return price * (this.percent / 100); }
  describe(): string { return `${this.percent}% temporada ${this.season}`; }
}

class PricingService {
  applyDiscount(price: number, strategy: DiscountStrategy): number {
    const discount = strategy.calculate(price);
    console.log(`Descuento aplicado: ${strategy.describe()} = -$${discount.toFixed(2)}`);
    return price - discount;
  }
}

const pricing = new PricingService();
console.log(pricing.applyDiscount(100, new VipDiscount()));
console.log(pricing.applyDiscount(100, new SeasonalDiscount(15, 'verano')));
// Añadir BlackFridayDiscount no requiere tocar PricingService
```

---

## L — Liskov Substitution Principle (LSP)

> *Si S es subtipo de T, los objetos de tipo T pueden ser reemplazados por objetos de tipo S sin alterar las propiedades del programa.*

En términos prácticos: una subclase debe poder usarse en cualquier lugar donde se use su clase base, sin sorpresas.

```typescript
// ❌ Viola LSP: Square extiende Rectangle pero rompe su contrato
class RectangleBad {
  constructor(protected width: number, protected height: number) {}
  setWidth(w: number):  void { this.width = w; }
  setHeight(h: number): void { this.height = h; }
  area(): number { return this.width * this.height; }
}

class SquareBad extends RectangleBad {
  // Un cuadrado DEBE tener width === height, pero RectangleBad no lo garantiza
  setWidth(w: number):  void { this.width = this.height = w; }
  setHeight(h: number): void { this.width = this.height = h; }
}

function testRectangle(r: RectangleBad): void {
  r.setWidth(5);
  r.setHeight(3);
  // Para un Rectangle esto siempre será 15, pero para SquareBad será 9
  console.assert(r.area() === 15, `Esperaba 15, obtuve ${r.area()}`);
}

testRectangle(new SquareBad(1)); // ← ¡Rompe la aserción!

// ✅ Diseño que respeta LSP: Shape es la abstracción correcta
interface Shape {
  area(): number;
  perimeter(): number;
}

class Rectangle implements Shape {
  constructor(private width: number, private height: number) {}
  area():      number { return this.width * this.height; }
  perimeter(): number { return 2 * (this.width + this.height); }
}

class Square implements Shape {
  constructor(private side: number) {}
  area():      number { return this.side ** 2; }
  perimeter(): number { return 4 * this.side; }
}

function printShapeInfo(shape: Shape): void {
  console.log(`Área: ${shape.area()}, Perímetro: ${shape.perimeter()}`);
}

printShapeInfo(new Rectangle(5, 3)); // 15, 16
printShapeInfo(new Square(4));       // 16, 16 — correcto para un cuadrado
```

---

## I — Interface Segregation Principle (ISP)

> *Los clientes no deben depender de interfaces que no usan.*

Las "interfaces gordas" obligan a las clases a implementar métodos que no necesitan, creando dependencias innecesarias.

```typescript
// ❌ Viola ISP: interfaz "gorda" que mezcla responsabilidades
interface WorkerBad {
  work():    void;
  eat():     void;
  sleep():   void;
  charge():  void;  // ← Solo los robots se cargan
  breathe(): void;  // ← Solo los humanos respiran
}

class RobotBad implements WorkerBad {
  work():    void { console.log('Robot trabajando'); }
  eat():     void { throw new Error('Los robots no comen!'); } // ← obligado a implementar
  sleep():   void { throw new Error('Los robots no duermen!'); }
  charge():  void { console.log('Cargando batería'); }
  breathe(): void { throw new Error('Los robots no respiran!'); }
}

// ✅ ISP: interfaces pequeñas y específicas
interface Workable   { work(): void; }
interface Eatable    { eat(): void;  }
interface Sleepable  { sleep(): void; }
interface Chargeable { charge(): void; }

class Human implements Workable, Eatable, Sleepable {
  work():  void { console.log('Humano trabajando'); }
  eat():   void { console.log('Humano comiendo'); }
  sleep(): void { console.log('Humano durmiendo'); }
}

class Robot implements Workable, Chargeable {
  work():   void { console.log('Robot trabajando'); }
  charge(): void { console.log('Robot cargándose'); }
}

// Funciones que aceptan solo lo que necesitan
function makeWork(worker: Workable):  void { worker.work(); }
function chargeRobot(r: Chargeable): void { r.charge(); }
```

---

## D — Dependency Inversion Principle (DIP)

> *Los módulos de alto nivel no deben depender de módulos de bajo nivel. Ambos deben depender de abstracciones.*

```typescript
// ❌ Viola DIP: el servicio de alto nivel depende de implementaciones concretas
class MySQLDatabase {
  query(sql: string): unknown[] { return []; }
}

class FileLogger {
  log(msg: string): void { console.log(`[FILE] ${msg}`); }
}

class UserServiceBad {
  private db     = new MySQLDatabase(); // ← acoplado a MySQL
  private logger = new FileLogger();    // ← acoplado a FileLogger

  findUser(id: number): unknown {
    this.logger.log(`Buscando usuario ${id}`);
    return this.db.query(`SELECT * FROM users WHERE id = ${id}`)[0];
  }
}

// ✅ DIP: dependemos de abstracciones, no de implementaciones
interface Database {
  query(sql: string, params?: unknown[]): unknown[];
}

interface Logger {
  log(level: 'info' | 'warn' | 'error', msg: string): void;
}

// Implementaciones concretas
class PostgresDatabase implements Database {
  query(sql: string, params?: unknown[]): unknown[] {
    console.log(`[PG] ${sql}`, params);
    return [];
  }
}

class ConsoleLogger implements Logger {
  log(level: string, msg: string): void {
    console.log(`[${level.toUpperCase()}] ${msg}`);
  }
}

// El servicio de alto nivel solo conoce las abstracciones
class UserService {
  constructor(
    private db:     Database,  // ← abstracción
    private logger: Logger     // ← abstracción
  ) {}

  findUser(id: number): unknown {
    this.logger.log('info', `Buscando usuario ${id}`);
    return this.db.query('SELECT * FROM users WHERE id = ?', [id])[0];
  }
}

// En producción:
const service = new UserService(new PostgresDatabase(), new ConsoleLogger());

// En tests: inyecta mocks sin tocar UserService
class MockDatabase implements Database {
  query(): unknown[] { return [{ id: 1, name: 'Ana' }]; }
}

class SilentLogger implements Logger {
  log(): void {} // no hace nada en tests
}

const testService = new UserService(new MockDatabase(), new SilentLogger());
```

---

## SOLID juntos: un ejemplo completo

```typescript
// Sistema de notificaciones que respeta los 5 principios

// ISP: interfaces pequeñas y específicas
interface MessageSender {
  send(to: string, subject: string, body: string): Promise<void>;
}

interface MessageLogger {
  logSent(to: string, channel: string): void;
}

// SRP: cada clase tiene una responsabilidad
class EmailSender implements MessageSender {
  async send(to: string, subject: string, body: string): Promise<void> {
    console.log(`[Email] → ${to} | ${subject}`);
  }
}

class SmsSender implements MessageSender {
  async send(to: string, _subject: string, body: string): Promise<void> {
    console.log(`[SMS] → ${to} | ${body.slice(0, 160)}`);
  }
}

// OCP: nuevo canal = nueva clase, sin tocar las existentes
class SlackSender implements MessageSender {
  async send(to: string, subject: string, body: string): Promise<void> {
    console.log(`[Slack] → ${to} | *${subject}* ${body}`);
  }
}

class AuditLogger implements MessageLogger {
  logSent(to: string, channel: string): void {
    console.log(`[AUDIT] ${new Date().toISOString()} → ${channel}:${to}`);
  }
}

// DIP: NotificationService depende de abstracciones (MessageSender, MessageLogger)
class NotificationService {
  constructor(
    private senders: MessageSender[],  // LSP: cualquier MessageSender sirve
    private logger:  MessageLogger
  ) {}

  async notifyAll(to: string, subject: string, body: string): Promise<void> {
    for (const sender of this.senders) {
      await sender.send(to, subject, body);
      this.logger.logSent(to, sender.constructor.name);
    }
  }
}

const notifier = new NotificationService(
  [new EmailSender(), new SmsSender(), new SlackSender()],
  new AuditLogger()
);

notifier.notifyAll('ana@example.com', 'Pedido confirmado', 'Tu pedido #1234 está en camino.');
```

---

## Cuándo NO aplicar SOLID a ciegas

SOLID es una guía, no un dogma. En proyectos pequeños, abstraer en exceso crea complejidad innecesaria ("over-engineering"):

- **Crea la abstracción cuando la necesites**, no anticipadamente (YAGNI: You Ain't Gonna Need It).
- **SRP no significa una función por archivo**: la cohesión importa.
- **DIP en scripts simples** puede ser excesivo; úsalo donde el desacoplamiento aporte valor real (tests, cambios frecuentes).
