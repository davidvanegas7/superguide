# Arquitectura en Capas

La **arquitectura en capas** organiza el código en niveles con responsabilidades claramente separadas. Cada capa solo se comunica con la capa adyacente, lo que reduce el acoplamiento y facilita los cambios.

---

## MVC: Model-View-Controller

El patrón más extendido para aplicaciones web.

```
Petición HTTP
      │
      ▼
┌─────────────┐
│  Controller  │  ← Recibe la petición, valida entrada, coordina
└──────┬──────┘
       │ llama
       ▼
┌─────────────┐
│   Service   │  ← Lógica de negocio (casos de uso)
└──────┬──────┘
       │ usa
       ▼
┌─────────────┐
│    Model    │  ← Entidades + acceso a datos (ORM/Repository)
└─────────────┘
       │ persiste en
       ▼
  [ Base de datos ]
```

```typescript
// Model: entidad + repositorio
interface User {
  id:    number;
  email: string;
  name:  string;
  role:  'admin' | 'user';
}

// Service: lógica de negocio
class UserService {
  constructor(
    private userRepo: UserRepository,
    private mailer:   MailService
  ) {}

  async register(email: string, name: string): Promise<User> {
    const existing = await this.userRepo.findByEmail(email);
    if (existing) throw new ConflictError('Email ya registrado');

    const user = await this.userRepo.save({ email, name, role: 'user' });
    await this.mailer.sendWelcome(user.email, user.name);
    return user;
  }

  async findAll(page: number, limit: number): Promise<{ data: User[]; total: number }> {
    return this.userRepo.paginate(page, limit);
  }
}

// Controller: maneja HTTP, no contiene lógica de negocio
class UserController {
  constructor(private service: UserService) {}

  async register(req: Request, res: Response): Promise<void> {
    const { email, name } = req.body;

    // Validación de entrada (responsabilidad del controller)
    if (!email || !name) {
      res.status(400).json({ error: 'email y name son requeridos' });
      return;
    }

    try {
      const user = await this.service.register(email, name);
      res.status(201).json({ data: user });
    } catch (err) {
      if (err instanceof ConflictError) {
        res.status(409).json({ error: err.message });
      } else {
        res.status(500).json({ error: 'Error interno' });
      }
    }
  }

  async list(req: Request, res: Response): Promise<void> {
    const page  = parseInt(req.query.page  as string ?? '1');
    const limit = parseInt(req.query.limit as string ?? '20');
    const result = await this.service.findAll(page, limit);
    res.json(result);
  }
}
```

---

## Clean Architecture

Propuesta por Robert C. Martin. Las **dependencias solo apuntan hacia el interior**. El dominio no depende de frameworks, BD ni UI.

```
┌─────────────────────────────────────────┐
│         Frameworks & Drivers            │  ← Express, Prisma, Redis, S3
│  ┌──────────────────────────────────┐   │
│  │    Interface Adapters            │   │  ← Controllers, Repositories, Presenters
│  │  ┌───────────────────────────┐   │   │
│  │  │    Application/Use Cases  │   │   │  ← Casos de uso: RegisterUser, PlaceOrder...
│  │  │  ┌────────────────────┐   │   │   │
│  │  │  │     Entities       │   │   │   │  ← Lógica de negocio pura (sin deps externas)
│  │  │  └────────────────────┘   │   │   │
│  │  └───────────────────────────┘   │   │
│  └──────────────────────────────────┘   │
└─────────────────────────────────────────┘
           Regla de dependencia: →  hacia adentro
```

```typescript
// ── ENTITIES (capa más interna) ────────────────────────────────
// Sin imports de frameworks ni librerías externas

class Money {
  constructor(
    public readonly amount: number,
    public readonly currency: 'EUR' | 'USD'
  ) {
    if (amount < 0) throw new Error('Monto no puede ser negativo');
  }

  add(other: Money): Money {
    if (this.currency !== other.currency) throw new Error('Divisas distintas');
    return new Money(this.amount + other.amount, this.currency);
  }
}

class Order {
  private items: Array<{ productId: number; qty: number; price: Money }> = [];
  public readonly id: string;
  public status: 'draft' | 'confirmed' | 'shipped' | 'cancelled' = 'draft';

  constructor(public readonly userId: number) {
    this.id = `ORD-${Date.now()}`;
  }

  addItem(productId: number, qty: number, price: Money): void {
    if (this.status !== 'draft') throw new Error('No se puede modificar un pedido confirmado');
    if (qty <= 0) throw new Error('Cantidad debe ser positiva');
    this.items.push({ productId, qty, price });
  }

  get total(): Money {
    return this.items.reduce(
      (sum, item) => sum.add(new Money(item.price.amount * item.qty, item.price.currency)),
      new Money(0, this.items[0]?.price.currency ?? 'EUR')
    );
  }

  confirm(): void {
    if (!this.items.length) throw new Error('El pedido está vacío');
    this.status = 'confirmed';
  }
}

// ── APPLICATION / USE CASES ─────────────────────────────────────
// Depende solo de Entities + interfaces (abstracciones)

interface OrderRepository {
  save(order: Order):     Promise<Order>;
  findById(id: string):   Promise<Order | null>;
}

interface ProductRepository {
  findById(id: number):   Promise<{ id: number; name: string; priceCents: number } | null>;
}

interface EventPublisher {
  publish(event: string, data: unknown): Promise<void>;
}

class PlaceOrderUseCase {
  constructor(
    private orders:   OrderRepository,
    private products: ProductRepository,
    private events:   EventPublisher
  ) {}

  async execute(userId: number, items: Array<{ productId: number; qty: number }>): Promise<Order> {
    const order = new Order(userId);

    for (const item of items) {
      const product = await this.products.findById(item.productId);
      if (!product) throw new Error(`Producto ${item.productId} no existe`);

      order.addItem(item.productId, item.qty, new Money(product.priceCents, 'EUR'));
    }

    order.confirm();
    const saved = await this.orders.save(order);

    await this.events.publish('order.placed', {
      orderId: saved.id,
      userId,
      total: saved.total.amount,
    });

    return saved;
  }
}

// ── INTERFACE ADAPTERS ──────────────────────────────────────────
// Implementaciones concretas de los repositorios

class PrismaOrderRepository implements OrderRepository {
  constructor(private prisma: any) {}  // PrismaClient en producción

  async save(order: Order): Promise<Order> {
    // Serializa la entidad y la guarda
    await this.prisma.order.create({ data: { id: order.id, userId: order.userId } });
    return order;
  }

  async findById(id: string): Promise<Order | null> {
    const row = await this.prisma.order.findUnique({ where: { id } });
    if (!row) return null;
    // Reconstruye la entidad desde el registro
    const order = new Order(row.userId);
    return order;
  }
}

// ── FRAMEWORKS & DRIVERS ────────────────────────────────────────
// Controller que usa el caso de uso

class OrderController {
  constructor(private placeOrder: PlaceOrderUseCase) {}

  async create(req: any, res: any): Promise<void> {
    const { items } = req.body;
    const userId    = req.user.id;

    const order = await this.placeOrder.execute(userId, items);
    res.status(201).json({ orderId: order.id, total: order.total.amount });
  }
}
```

---

## Comparativa de arquitecturas

| Arquitectura | Complejidad | Ideal para |
|---|---|---|
| **Script plano** | ⭐ | Scripts y utilidades pequeñas |
| **MVC** | ⭐⭐ | CRUD APIs, aplicaciones web estándar |
| **Clean Architecture** | ⭐⭐⭐⭐ | Dominio complejo, múltiples interfaces, alta testabilidad |
| **Hexagonal** | ⭐⭐⭐⭐ | Similar a Clean, con concepto de puertos y adaptadores |
| **DDD** | ⭐⭐⭐⭐⭐ | Dominios muy complejos con equipos grandes |

---

## Reglas de oro

1. **El dominio no importa frameworks** — si tu entidad usa `import from 'express'`, algo está mal
2. **Los controllers no tienen lógica de negocio** — solo validación de entrada y mapeo de respuesta
3. **Cada capa tiene una única razón para cambiar**
4. **Los tests del dominio no necesitan base de datos** — usan repos en memoria
5. **Empieza simple** — no apliques Clean Architecture a un CRUD de 3 tablas
