# Programación Orientada a Objetos: Fundamentos

## ¿Qué es la POO?

La **Programación Orientada a Objetos** es un paradigma que organiza el código en torno a **objetos** que combinan datos (atributos) y comportamiento (métodos). Es el paradigma dominante en el backend porque facilita modelar dominios complejos, reutilizar código y escribir sistemas mantenibles.

Los cuatro pilares son: **encapsulamiento**, **herencia**, **polimorfismo** y **abstracción**. En esta lección nos centramos en los fundamentos: clases, objetos, constructores y visibilidad.

---

## Clases y objetos

Una **clase** es un plano o plantilla. Un **objeto** es una instancia concreta de esa clase.

```typescript
class Product {
  id:    number;
  name:  string;
  price: number;

  constructor(id: number, name: string, price: number) {
    this.id    = id;
    this.name  = name;
    this.price = price;
  }

  getFormattedPrice(): string {
    return `$${this.price.toFixed(2)}`;
  }

  applyDiscount(percent: number): void {
    if (percent < 0 || percent > 100) {
      throw new Error('El descuento debe estar entre 0 y 100');
    }
    this.price = this.price * (1 - percent / 100);
  }
}

const laptop = new Product(1, 'Laptop Pro', 1200);
console.log(laptop.getFormattedPrice()); // $1200.00
laptop.applyDiscount(10);
console.log(laptop.getFormattedPrice()); // $1080.00
```

---

## Modificadores de acceso

TypeScript (y la mayoría de lenguajes OO) tiene tres niveles de visibilidad:

| Modificador | Accesible desde |
|---|---|
| `public` | Cualquier lugar (por defecto) |
| `protected` | La clase y sus subclases |
| `private` | Solo dentro de la clase |

```typescript
class BankAccount {
  private balance: number;
  protected owner:  string;
  public  id:       string;

  constructor(id: string, owner: string, initialBalance: number) {
    this.id      = id;
    this.owner   = owner;
    this.balance = initialBalance;
  }

  // Método público: interfaz hacia el exterior
  deposit(amount: number): void {
    this.validateAmount(amount);
    this.balance += amount;
  }

  withdraw(amount: number): void {
    this.validateAmount(amount);
    if (amount > this.balance) {
      throw new Error('Saldo insuficiente');
    }
    this.balance -= amount;
  }

  getBalance(): number {
    return this.balance; // solo exponemos lectura, no escritura directa
  }

  // Método privado: lógica interna que nadie debe llamar desde fuera
  private validateAmount(amount: number): void {
    if (amount <= 0) throw new Error('El monto debe ser positivo');
  }
}

const account = new BankAccount('ACC-001', 'Ana García', 1000);
account.deposit(500);
account.withdraw(200);
console.log(account.getBalance()); // 1300

// account.balance = 9999; ← Error: 'balance' is private
```

---

## Getters y setters

Los **getters** y **setters** permiten controlar el acceso a propiedades privadas con lógica adicional:

```typescript
class Temperature {
  private _celsius: number;

  constructor(celsius: number) {
    this._celsius = celsius;
  }

  get celsius(): number {
    return this._celsius;
  }

  set celsius(value: number) {
    if (value < -273.15) throw new Error('Por debajo del cero absoluto');
    this._celsius = value;
  }

  get fahrenheit(): number {
    return this._celsius * 9/5 + 32;
  }

  get kelvin(): number {
    return this._celsius + 273.15;
  }
}

const temp = new Temperature(25);
console.log(temp.fahrenheit); // 77
console.log(temp.kelvin);     // 298.15
temp.celsius = 100;
console.log(temp.fahrenheit); // 212
```

---

## Propiedades y parámetros readonly

`readonly` garantiza que una propiedad no puede modificarse después de la inicialización:

```typescript
class Point {
  constructor(
    public readonly x: number,
    public readonly y: number
  ) {}

  distanceTo(other: Point): number {
    return Math.sqrt((this.x - other.x) ** 2 + (this.y - other.y) ** 2);
  }

  toString(): string {
    return `(${this.x}, ${this.y})`;
  }
}

const p1 = new Point(0, 0);
const p2 = new Point(3, 4);
console.log(p1.distanceTo(p2)); // 5
// p1.x = 5; ← Error: Cannot assign to 'x' because it is a read-only property
```

---

## Métodos estáticos y propiedades de clase

Los miembros `static` pertenecen a la clase, no a las instancias. Son útiles para factories, contadores y utilidades:

```typescript
class IdGenerator {
  private static counter = 0;

  static next(): string {
    return `ID-${++IdGenerator.counter}`.padStart(8, '0');
  }

  static reset(): void {
    IdGenerator.counter = 0;
  }

  static getCurrent(): number {
    return IdGenerator.counter;
  }
}

console.log(IdGenerator.next()); // ID-000001
console.log(IdGenerator.next()); // ID-000002
console.log(IdGenerator.getCurrent()); // 2
```

---

## Clases con interfaces

Las interfaces definen **contratos**: garantizan que una clase expone ciertos métodos sin importar su implementación interna.

```typescript
interface Serializable {
  serialize(): string;
  toJSON(): Record<string, unknown>;
}

interface Validatable {
  isValid(): boolean;
  getErrors(): string[];
}

class UserProfile implements Serializable, Validatable {
  constructor(
    public readonly id: number,
    public name: string,
    public email: string,
    public age: number
  ) {}

  isValid(): boolean {
    return this.getErrors().length === 0;
  }

  getErrors(): string[] {
    const errors: string[] = [];
    if (!this.name || this.name.length < 2) errors.push('Nombre muy corto');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email)) errors.push('Email inválido');
    if (this.age < 0 || this.age > 150) errors.push('Edad inválida');
    return errors;
  }

  serialize(): string {
    return JSON.stringify(this.toJSON());
  }

  toJSON(): Record<string, unknown> {
    return { id: this.id, name: this.name, email: this.email, age: this.age };
  }
}

const user = new UserProfile(1, 'Ana', 'ana@example.com', 25);
console.log(user.isValid());   // true
console.log(user.serialize()); // '{"id":1,"name":"Ana",...}'
```

---

## Clases abstractas

Las clases abstractas son un punto intermedio entre una clase normal y una interfaz: pueden tener implementación, pero no se pueden instanciar directamente:

```typescript
abstract class Shape {
  constructor(public color: string) {}

  // Método abstracto: cada subclase DEBE implementarlo
  abstract area(): number;
  abstract perimeter(): number;

  // Método concreto: disponible para todas las subclases
  describe(): string {
    return `Figura de color ${this.color}: área=${this.area().toFixed(2)}, perímetro=${this.perimeter().toFixed(2)}`;
  }
}

class Circle extends Shape {
  constructor(color: string, private radius: number) {
    super(color);
  }

  area():      number { return Math.PI * this.radius ** 2; }
  perimeter(): number { return 2 * Math.PI * this.radius; }
}

class Rectangle extends Shape {
  constructor(color: string, private width: number, private height: number) {
    super(color);
  }

  area():      number { return this.width * this.height; }
  perimeter(): number { return 2 * (this.width + this.height); }
}

// new Shape('rojo'); ← Error: Cannot create an instance of an abstract class
const circle = new Circle('azul', 5);
const rect   = new Rectangle('rojo', 4, 6);

console.log(circle.describe()); // Figura de color azul: área=78.54, perímetro=31.42
console.log(rect.describe());   // Figura de color rojo: área=24.00, perímetro=20.00
```

---

## Composición vs Herencia

> **"Prefiere composición sobre herencia"** — Gang of Four

La **herencia** modela relaciones "es-un". La **composición** modela relaciones "tiene-un". La composición es más flexible porque puedes cambiar el comportamiento en runtime.

```typescript
// ❌ Herencia profunda — frágil y difícil de mantener
class Animal {}
class Vertebrate extends Animal {}
class Mammal extends Vertebrate {}
class DomesticMammal extends Mammal {}
class Dog extends DomesticMammal {}

// ✅ Composición — flexible y testeable
interface Logger {
  log(msg: string): void;
}

interface Notifier {
  notify(userId: string, msg: string): void;
}

class OrderService {
  constructor(
    private logger: Logger,
    private notifier: Notifier
  ) {}

  placeOrder(userId: string, amount: number): void {
    this.logger.log(`Orden creada: usuario=${userId}, monto=${amount}`);
    this.notifier.notify(userId, `Tu orden por $${amount} fue procesada`);
  }
}
```

---

## Buenas prácticas

- **Una clase, una responsabilidad**: si describes lo que hace una clase con "y", es señal de que tiene demasiadas.
- **Nombra en sustantivos**: `UserRepository`, `EmailSender`, `OrderCalculator`.
- **Constructor limpio**: no hagas I/O ni operaciones costosas en el constructor.
- **Métodos cortos**: si un método tiene más de 20 líneas, considera refactorizarlo.
- **Evita setters innecesarios**: expón comportamiento (`withdraw()`), no datos (`setBalance()`).
