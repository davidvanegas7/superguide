# Los Cuatro Pilares de la POO

## Encapsulamiento

El **encapsulamiento** consiste en ocultar los detalles internos de un objeto y exponer solo lo necesario a través de una interfaz pública. Protege el estado interno de modificaciones incorrectas.

```typescript
// ❌ Sin encapsulamiento — el estado puede corromperse desde fuera
class OrderBad {
  items: string[] = [];
  total: number = 0;
  status: string = 'pending';
}

const order = new OrderBad();
order.total = -999;   // ¡Nadie lo impide!
order.status = 'pagado en efectivo del futuro'; // inválido

// ✅ Con encapsulamiento — el objeto controla su propio estado
class Order {
  private _items:  Array<{ name: string; price: number }> = [];
  private _status: 'pending' | 'confirmed' | 'shipped' | 'delivered' = 'pending';

  addItem(name: string, price: number): void {
    if (price <= 0) throw new Error('El precio debe ser positivo');
    this._items.push({ name, price });
  }

  confirm(): void {
    if (this._items.length === 0) throw new Error('La orden está vacía');
    this._status = 'confirmed';
  }

  get total(): number {
    return this._items.reduce((sum, i) => sum + i.price, 0);
  }

  get status(): string { return this._status; }
  get items(): ReadonlyArray<{ name: string; price: number }> { return this._items; }
}

const o = new Order();
o.addItem('Laptop', 1200);
o.addItem('Mouse', 25);
o.confirm();
console.log(o.total);  // 1225
console.log(o.status); // confirmed
```

---

## Herencia

La **herencia** permite crear nuevas clases basadas en clases existentes, reutilizando y extendiendo su comportamiento. Modela la relación **"es-un"**.

```typescript
class Vehicle {
  constructor(
    protected make:  string,
    protected model: string,
    protected year:  number,
    private mileage: number = 0
  ) {}

  drive(km: number): void {
    if (km <= 0) throw new Error('Los km deben ser positivos');
    this.mileage += km;
  }

  get info(): string {
    return `${this.year} ${this.make} ${this.model} (${this.mileage} km)`;
  }

  // Puede ser sobreescrito por subclases
  fuelType(): string { return 'gasolina'; }
}

class ElectricCar extends Vehicle {
  private batteryLevel = 100;

  constructor(make: string, model: string, year: number, private rangeKm: number) {
    super(make, model, year);
  }

  // Sobrescribe el método del padre
  fuelType(): string { return 'eléctrico'; }

  charge(): void { this.batteryLevel = 100; }

  get remainingRange(): number {
    return (this.batteryLevel / 100) * this.rangeKm;
  }
}

class Truck extends Vehicle {
  constructor(make: string, model: string, year: number, public payloadTons: number) {
    super(make, model, year);
  }

  fuelType(): string { return 'diésel'; }
}

const tesla = new ElectricCar('Tesla', 'Model 3', 2024, 480);
const truck = new Truck('Volvo', 'FH16', 2022, 25);

tesla.drive(100);
console.log(tesla.info);          // 2024 Tesla Model 3 (100 km)
console.log(tesla.remainingRange); // 458.something
console.log(tesla.fuelType());     // eléctrico
console.log(truck.fuelType());     // diésel
```

### Cuándo NO usar herencia

- Cuando la relación no es verdaderamente "es-un"
- Cuando necesitas heredar de múltiples clases (problema del diamante)
- Cuando el árbol de herencia tiene más de 2-3 niveles

---

## Polimorfismo

El **polimorfismo** permite que objetos de diferentes clases sean tratados de forma uniforme a través de una interfaz común. Existen dos tipos principales:

### Polimorfismo de subtipos (runtime)

```typescript
abstract class Notification {
  constructor(
    protected recipient: string,
    protected message:   string
  ) {}

  abstract send(): Promise<void>;

  protected log(): void {
    console.log(`[${this.constructor.name}] → ${this.recipient}: ${this.message}`);
  }
}

class EmailNotification extends Notification {
  async send(): Promise<void> {
    // En producción: llamaría a un servicio de email
    this.log();
    console.log('  → Enviado por SMTP');
  }
}

class SmsNotification extends Notification {
  async send(): Promise<void> {
    this.log();
    console.log('  → Enviado por Twilio');
  }
}

class PushNotification extends Notification {
  constructor(recipient: string, message: string, private deviceToken: string) {
    super(recipient, message);
  }

  async send(): Promise<void> {
    this.log();
    console.log(`  → Push a dispositivo ${this.deviceToken}`);
  }
}

// Polimorfismo en acción: el código cliente no necesita saber el tipo concreto
async function sendAll(notifications: Notification[]): Promise<void> {
  for (const n of notifications) {
    await n.send(); // cada objeto sabe cómo enviarse
  }
}

const notifications: Notification[] = [
  new EmailNotification('ana@example.com', '¡Tu pedido fue confirmado!'),
  new SmsNotification('+34 600 000 000', 'Código de verificación: 1234'),
  new PushNotification('Bob', 'Tienes un mensaje nuevo', 'TOKEN-ABC'),
];

sendAll(notifications);
```

### Polimorfismo paramétrico (generics)

```typescript
class Stack<T> {
  private items: T[] = [];

  push(item: T): void  { this.items.push(item); }
  pop():  T | undefined { return this.items.pop(); }
  peek(): T | undefined { return this.items[this.items.length - 1]; }
  isEmpty(): boolean   { return this.items.length === 0; }
  get size(): number   { return this.items.length; }
}

// La misma clase funciona para cualquier tipo
const numStack  = new Stack<number>();
const strStack  = new Stack<string>();

numStack.push(1); numStack.push(2); numStack.push(3);
strStack.push('a'); strStack.push('b');

console.log(numStack.peek()); // 3
console.log(strStack.pop());  // 'b'
```

---

## Abstracción

La **abstracción** consiste en exponer solo los detalles relevantes para el caso de uso, ocultando la complejidad interna. Es el "qué hace" sin el "cómo lo hace".

```typescript
// Interfaz de alto nivel — qué puede hacer un repositorio
interface UserRepository {
  findById(id: number): Promise<User | null>;
  findByEmail(email: string): Promise<User | null>;
  save(user: User): Promise<User>;
  delete(id: number): Promise<void>;
}

interface User {
  id?: number;
  name: string;
  email: string;
  role: 'admin' | 'user';
}

// El servicio solo conoce la abstracción, no la implementación concreta
class UserService {
  constructor(private repo: UserRepository) {}

  async getProfile(id: number): Promise<User> {
    const user = await this.repo.findById(id);
    if (!user) throw new Error(`Usuario ${id} no encontrado`);
    return user;
  }

  async changeEmail(id: number, newEmail: string): Promise<User> {
    const existing = await this.repo.findByEmail(newEmail);
    if (existing && existing.id !== id) throw new Error('Email ya en uso');

    const user = await this.getProfile(id);
    return this.repo.save({ ...user, email: newEmail });
  }
}

// Implementación concreta para tests (in-memory)
class InMemoryUserRepository implements UserRepository {
  private store = new Map<number, User>();
  private seq = 0;

  async findById(id: number): Promise<User | null> {
    return this.store.get(id) ?? null;
  }

  async findByEmail(email: string): Promise<User | null> {
    return [...this.store.values()].find(u => u.email === email) ?? null;
  }

  async save(user: User): Promise<User> {
    const saved = { ...user, id: user.id ?? ++this.seq };
    this.store.set(saved.id!, saved);
    return saved;
  }

  async delete(id: number): Promise<void> {
    this.store.delete(id);
  }
}
```

---

## Comparación de los cuatro pilares

| Pilar | Pregunta que responde | Beneficio principal |
|---|---|---|
| **Encapsulamiento** | ¿Quién puede cambiar el estado? | Protege la integridad del objeto |
| **Herencia** | ¿Cómo reutilizo comportamiento? | Evita duplicación de código |
| **Polimorfismo** | ¿Cómo trato distintos tipos uniformemente? | Código extensible sin modificar el existente |
| **Abstracción** | ¿Qué expongo y qué oculto? | Reduce el acoplamiento entre componentes |

---

## Herencia de interfaces

TypeScript permite que las interfaces hereden de otras interfaces, componiendo contratos más ricos:

```typescript
interface Timestamped {
  createdAt: Date;
  updatedAt: Date;
}

interface SoftDeletable {
  deletedAt: Date | null;
  isDeleted(): boolean;
}

interface BaseEntity extends Timestamped, SoftDeletable {
  id: number;
}

// Una clase puede implementar múltiples interfaces
class Post implements BaseEntity {
  id:        number;
  createdAt: Date;
  updatedAt: Date;
  deletedAt: Date | null = null;
  title:     string;
  body:      string;

  constructor(id: number, title: string, body: string) {
    this.id        = id;
    this.title     = title;
    this.body      = body;
    this.createdAt = new Date();
    this.updatedAt = new Date();
  }

  isDeleted(): boolean { return this.deletedAt !== null; }

  softDelete(): void {
    this.deletedAt = new Date();
    this.updatedAt = new Date();
  }
}
```

---

## Errores comunes

### Romper el encapsulamiento con getters/setters innecesarios

```typescript
// ❌ Esto es básicamente un struct público — no hay encapsulamiento
class BadUser {
  private _name: string = '';
  getName(): string { return this._name; }
  setName(n: string): void { this._name = n; }
}

// ✅ Expón comportamiento, no datos crudos
class GoodUser {
  private _name: string;

  constructor(name: string) {
    this.setName(name); // validación centralizada
  }

  rename(newName: string): void { this.setName(newName); }
  get name(): string { return this._name; }

  private setName(name: string): void {
    if (name.trim().length < 2) throw new Error('Nombre muy corto');
    this._name = name.trim();
  }
}
```

### Herencia para reutilizar código en vez de para "es-un"

```typescript
// ❌ Un Stack NO es un Array (no deberías poder hacer push arbitrario)
class BadStack extends Array {}

// ✅ Composición
class GoodStack<T> {
  private items: T[] = [];
  push(item: T): void { this.items.push(item); }
  // solo exponemos la interfaz de Stack, no toda la de Array
}
```
