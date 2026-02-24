# Patrones de Diseño de Comportamiento

Los **patrones de comportamiento** gestionan algoritmos y la comunicación entre objetos, distribuyendo responsabilidades y desacoplando quién hace qué de quién sabe qué.

---

## Strategy

Define una familia de algoritmos, los encapsula y los hace intercambiables. Permite variar el algoritmo independientemente de los clientes que lo usan.

```typescript
// Estrategia de descuento
interface DiscountStrategy {
  name:  string;
  apply(originalPrice: number): number;
}

class NoDiscount implements DiscountStrategy {
  name = 'Sin descuento';
  apply(price: number): number { return price; }
}

class PercentageDiscount implements DiscountStrategy {
  name: string;
  constructor(private percent: number) {
    this.name = `Descuento ${percent}%`;
  }
  apply(price: number): number { return price * (1 - this.percent / 100); }
}

class FixedDiscount implements DiscountStrategy {
  name: string;
  constructor(private amount: number) {
    this.name = `Descuento fijo ${amount}€`;
  }
  apply(price: number): number { return Math.max(0, price - this.amount); }
}

class BuyXGetYDiscount implements DiscountStrategy {
  name: string;
  constructor(private buyQty: number, private getQty: number) {
    this.name = `${buyQty}+${getQty} gratis`;
  }
  apply(price: number, _quantity = 1): number {
    // Simplificado: descuento del precio por unidad gratuita
    const freeRatio = this.getQty / (this.buyQty + this.getQty);
    return price * (1 - freeRatio);
  }
}

class ShoppingCart {
  private strategy: DiscountStrategy = new NoDiscount();

  setDiscountStrategy(strategy: DiscountStrategy): this {
    this.strategy = strategy;
    return this;
  }

  calculateTotal(items: Array<{ name: string; price: number }>): number {
    const subtotal = items.reduce((sum, item) => sum + item.price, 0);
    const total    = this.strategy.apply(subtotal);
    console.log(`[${this.strategy.name}] ${subtotal.toFixed(2)}€ → ${total.toFixed(2)}€`);
    return total;
  }
}

const cart  = new ShoppingCart();
const items = [{ name: 'Libro', price: 29.99 }, { name: 'Curso', price: 49.99 }];

cart.setDiscountStrategy(new PercentageDiscount(20)).calculateTotal(items);
cart.setDiscountStrategy(new FixedDiscount(15)).calculateTotal(items);
cart.setDiscountStrategy(new BuyXGetYDiscount(2, 1)).calculateTotal(items);
```

---

## Observer

Define una dependencia uno-a-muchos: cuando el **sujeto** cambia de estado, todos sus **observadores** son notificados automáticamente.

```typescript
type EventMap = {
  'user.registered':   { userId: number; email: string };
  'order.created':     { orderId: string; total: number; userId: number };
  'order.shipped':     { orderId: string; trackingCode: string };
  'payment.failed':    { orderId: string; reason: string };
};

type EventHandler<T> = (payload: T) => void | Promise<void>;

class EventBus {
  private listeners = new Map<string, EventHandler<unknown>[]>();

  on<K extends keyof EventMap>(event: K, handler: EventHandler<EventMap[K]>): void {
    const list = this.listeners.get(event) ?? [];
    list.push(handler as EventHandler<unknown>);
    this.listeners.set(event, list);
  }

  off<K extends keyof EventMap>(event: K, handler: EventHandler<EventMap[K]>): void {
    const list = this.listeners.get(event) ?? [];
    this.listeners.set(event, list.filter(h => h !== handler));
  }

  async emit<K extends keyof EventMap>(event: K, payload: EventMap[K]): Promise<void> {
    const handlers = this.listeners.get(event) ?? [];
    await Promise.all(handlers.map(h => h(payload)));
  }
}

// Observadores concretos
const bus = new EventBus();

// Envía email de bienvenida
bus.on('user.registered', async ({ userId, email }) => {
  console.log(`[Email] Bienvenido! → ${email} (usuario ${userId})`);
});

// Crea perfil inicial
bus.on('user.registered', async ({ userId }) => {
  console.log(`[Profile] Creando perfil para usuario ${userId}`);
});

// Notifica al almacén
bus.on('order.created', async ({ orderId, total }) => {
  console.log(`[Warehouse] Nueva orden ${orderId} por ${total}€`);
});

// Genera factura
bus.on('order.created', async ({ orderId, userId }) => {
  console.log(`[Billing] Factura para usuario ${userId}, orden ${orderId}`);
});

// Notifica envío
bus.on('order.shipped', async ({ orderId, trackingCode }) => {
  console.log(`[SMS] Tu pedido ${orderId} está en camino. Código: ${trackingCode}`);
});

// Retry al fallar el pago
bus.on('payment.failed', async ({ orderId, reason }) => {
  console.log(`[Retry] Reintentando pago de ${orderId}: ${reason}`);
});

// Emitir eventos
await bus.emit('user.registered', { userId: 1, email: 'ana@example.com' });
await bus.emit('order.created',   { orderId: 'ORD-001', total: 149.99, userId: 1 });
await bus.emit('order.shipped',   { orderId: 'ORD-001', trackingCode: 'ES123456789' });
```

---

## Command

Encapsula una **solicitud como un objeto**, permitiendo deshacer/rehacer operaciones, hacer colas de comandos y logging de acciones.

```typescript
interface Command {
  execute(): void;
  undo():    void;
  describe(): string;
}

// Estado que los comandos modifican
class TextEditor {
  private content = '';
  private cursor  = 0;

  insert(pos: number, text: string): void {
    this.content = this.content.slice(0, pos) + text + this.content.slice(pos);
    this.cursor = pos + text.length;
  }

  delete(pos: number, length: number): void {
    this.content = this.content.slice(0, pos) + this.content.slice(pos + length);
    this.cursor = pos;
  }

  getContent():  string { return this.content; }
  getCursor():   number { return this.cursor; }
}

// Comandos concretos
class InsertTextCommand implements Command {
  constructor(
    private editor:    TextEditor,
    private position:  number,
    private text:      string
  ) {}

  execute(): void { this.editor.insert(this.position, this.text); }
  undo():    void { this.editor.delete(this.position, this.text.length); }
  describe(): string { return `Insert "${this.text}" at ${this.position}`; }
}

class DeleteTextCommand implements Command {
  private deleted = '';

  constructor(
    private editor:   TextEditor,
    private position: number,
    private length:   number
  ) {}

  execute(): void {
    this.deleted = this.editor.getContent().slice(this.position, this.position + this.length);
    this.editor.delete(this.position, this.length);
  }

  undo(): void {
    this.editor.insert(this.position, this.deleted);
  }

  describe(): string { return `Delete ${this.length} chars at ${this.position}`; }
}

// Invoker: gestiona el historial
class EditorHistory {
  private history: Command[] = [];
  private pointer = -1;

  execute(cmd: Command): void {
    // Elimina el historial futuro al ejecutar nuevo comando
    this.history = this.history.slice(0, this.pointer + 1);
    cmd.execute();
    this.history.push(cmd);
    this.pointer++;
  }

  undo(): boolean {
    if (this.pointer < 0) return false;
    this.history[this.pointer].undo();
    this.pointer--;
    return true;
  }

  redo(): boolean {
    if (this.pointer >= this.history.length - 1) return false;
    this.pointer++;
    this.history[this.pointer].execute();
    return true;
  }

  getLog(): string[] { return this.history.map(c => c.describe()); }
}

const editor  = new TextEditor();
const history = new EditorHistory();

history.execute(new InsertTextCommand(editor, 0, 'Hola'));
history.execute(new InsertTextCommand(editor, 4, ' Mundo'));
console.log(editor.getContent()); // "Hola Mundo"

history.undo();
console.log(editor.getContent()); // "Hola"

history.redo();
console.log(editor.getContent()); // "Hola Mundo"
```

---

## Chain of Responsibility

Pasa una solicitud a lo largo de una **cadena de manejadores** hasta que uno la procese.

```typescript
interface Request {
  path:    string;
  method:  string;
  headers: Record<string, string>;
  body?:   unknown;
}

interface Response {
  status: number;
  body:   unknown;
}

type NextFn = () => Promise<Response>;

type Middleware = (req: Request, next: NextFn) => Promise<Response>;

// Middlewares concretos
const rateLimiter: Middleware = async (req, next) => {
  const ip = req.headers['x-forwarded-for'] ?? '127.0.0.1';
  // Lógica de rate limiting simplificada
  console.log(`[RateLimit] IP: ${ip} — OK`);
  return next();
};

const authMiddleware: Middleware = async (req, next) => {
  const token = req.headers['authorization'];
  if (!token || !token.startsWith('Bearer ')) {
    return { status: 401, body: { error: 'No autorizado' } };
  }
  console.log(`[Auth] Token válido`);
  return next();
};

const loggingMiddleware: Middleware = async (req, next) => {
  const start = Date.now();
  const res   = await next();
  console.log(`[Log] ${req.method} ${req.path} → ${res.status} (${Date.now() - start}ms)`);
  return res;
};

const corsMiddleware: Middleware = async (req, next) => {
  const res = await next();
  return {
    ...res,
    headers: { 'Access-Control-Allow-Origin': '*' },
  } as Response;
};

// Pipeline que encadena middlewares
function compose(...middlewares: Middleware[]) {
  return function pipeline(req: Request, finalHandler: () => Promise<Response>): Promise<Response> {
    let idx = -1;

    const dispatch = (i: number): Promise<Response> => {
      if (i <= idx) return Promise.reject(new Error('next() llamado múltiples veces'));
      idx = i;
      const fn = i === middlewares.length ? finalHandler : middlewares[i];
      return fn(req, () => dispatch(i + 1));
    };

    return dispatch(0);
  };
}

const pipeline = compose(loggingMiddleware, rateLimiter, authMiddleware, corsMiddleware);

await pipeline(
  { path: '/api/users', method: 'GET', headers: { authorization: 'Bearer token123' } },
  async () => ({ status: 200, body: [{ id: 1, name: 'Ana' }] })
);
```

---

## Resumen

| Patrón | Problema | Cuándo usarlo |
|---|---|---|
| **Strategy** | Variar un algoritmo en tiempo de ejecución | Descuentos, ordenación, validación con reglas cambiantes |
| **Observer** | Notificar cambios sin acoplar emisor/receptor | Eventos de dominio, sistemas reactivos, webhooks |
| **Command** | Encapsular operaciones para revertir/auditar | Undo/redo, colas de tareas, transacciones locales |
| **Chain of Responsibility** | Procesar una petición en pasos sucesivos | Middlewares HTTP, validaciones en cadena, pipelines |
