# Patrones de Diseño Estructurales

Los **patrones estructurales** se ocupan de **cómo se componen clases y objetos** para formar estructuras más grandes. Permiten que estructuras incompatibles trabajen juntas y añaden funcionalidades sin modificar el código original.

---

## Repository Pattern

Abstrae la capa de acceso a datos, exponiendo una colección en memoria. El dominio no conoce si los datos vienen de SQL, MongoDB, una API externa, o un array.

```typescript
// Entidad de dominio (no conoce la BD)
interface User {
  id:        number;
  email:     string;
  name:      string;
  role:      'admin' | 'user';
  createdAt: Date;
}

// Contrato: qué operaciones existen
interface UserRepository {
  findById(id: number):         Promise<User | null>;
  findByEmail(email: string):   Promise<User | null>;
  findAll(filters?: Partial<Pick<User, 'role'>>): Promise<User[]>;
  save(user: Omit<User, 'id' | 'createdAt'>): Promise<User>;
  update(id: number, data: Partial<User>): Promise<User | null>;
  delete(id: number): Promise<boolean>;
}

// Implementación con array (para tests)
class InMemoryUserRepository implements UserRepository {
  private store: User[] = [];
  private nextId = 1;

  async findById(id: number): Promise<User | null> {
    return this.store.find(u => u.id === id) ?? null;
  }

  async findByEmail(email: string): Promise<User | null> {
    return this.store.find(u => u.email === email) ?? null;
  }

  async findAll(filters?: Partial<Pick<User, 'role'>>): Promise<User[]> {
    return this.store.filter(u =>
      !filters?.role || u.role === filters.role
    );
  }

  async save(data: Omit<User, 'id' | 'createdAt'>): Promise<User> {
    const user: User = { ...data, id: this.nextId++, createdAt: new Date() };
    this.store.push(user);
    return user;
  }

  async update(id: number, data: Partial<User>): Promise<User | null> {
    const idx = this.store.findIndex(u => u.id === id);
    if (idx === -1) return null;
    this.store[idx] = { ...this.store[idx], ...data };
    return this.store[idx];
  }

  async delete(id: number): Promise<boolean> {
    const before = this.store.length;
    this.store = this.store.filter(u => u.id !== id);
    return this.store.length < before;
  }
}

// Implementación con "base de datos" real (simulada)
class PrismaUserRepository implements UserRepository {
  // constructor(private prisma: PrismaClient) {}

  async findById(id: number): Promise<User | null> {
    // return this.prisma.user.findUnique({ where: { id } });
    console.log(`[DB] SELECT * FROM users WHERE id = ${id}`);
    return null;
  }

  async findByEmail(email: string): Promise<User | null> {
    console.log(`[DB] SELECT * FROM users WHERE email = '${email}'`);
    return null;
  }

  async findAll(filters?: Partial<Pick<User, 'role'>>): Promise<User[]> {
    const where = filters?.role ? `WHERE role = '${filters.role}'` : '';
    console.log(`[DB] SELECT * FROM users ${where}`);
    return [];
  }

  async save(data: Omit<User, 'id' | 'createdAt'>): Promise<User> {
    console.log(`[DB] INSERT INTO users ...`);
    return { ...data, id: 1, createdAt: new Date() };
  }

  async update(id: number, data: Partial<User>): Promise<User | null> {
    console.log(`[DB] UPDATE users SET ... WHERE id = ${id}`);
    return null;
  }

  async delete(id: number): Promise<boolean> {
    console.log(`[DB] DELETE FROM users WHERE id = ${id}`);
    return true;
  }
}

// Servicio de dominio: trabaja con el contrato, no la implementación
class UserService {
  constructor(private repo: UserRepository) {}

  async registerUser(email: string, name: string): Promise<User> {
    const existing = await this.repo.findByEmail(email);
    if (existing) throw new Error(`Email ${email} ya está registrado`);

    return this.repo.save({ email, name, role: 'user' });
  }

  async promoteToAdmin(userId: number): Promise<User> {
    const user = await this.repo.update(userId, { role: 'admin' });
    if (!user) throw new Error(`Usuario ${userId} no encontrado`);
    return user;
  }
}

// En tests:
const service = new UserService(new InMemoryUserRepository());
// En producción:
// const service = new UserService(new PrismaUserRepository(prismaClient));
```

---

## Adapter

Permite que clases con interfaces **incompatibles** trabajen juntas. Convierte la interfaz de una clase en otra que el cliente espera.

```typescript
// Interfaz que necesita nuestra app
interface PaymentGateway {
  charge(amountCents: number, currency: string, source: string): Promise<{ id: string; status: string }>;
  refund(chargeId: string, amountCents?: number): Promise<{ id: string; status: string }>;
}

// API externa de Stripe (interfaz diferente)
class StripeSDK {
  createCharge(params: { amount: number; currency: string; payment_method: string }) {
    return Promise.resolve({ charge_id: 'ch_123', outcome: 'succeeded' });
  }
  createRefund(params: { charge: string; amount?: number }) {
    return Promise.resolve({ refund_id: 'ref_456', status: 'succeeded' });
  }
}

// Adapter: traduce nuestra interfaz a la de Stripe
class StripePaymentAdapter implements PaymentGateway {
  constructor(private stripe: StripeSDK) {}

  async charge(amountCents: number, currency: string, source: string) {
    const result = await this.stripe.createCharge({
      amount:         amountCents,
      currency:       currency,
      payment_method: source,
    });
    return { id: result.charge_id, status: result.outcome };
  }

  async refund(chargeId: string, amountCents?: number) {
    const result = await this.stripe.createRefund({
      charge: chargeId,
      amount: amountCents,
    });
    return { id: result.refund_id, status: result.status };
  }
}

// API externa de PayPal (otra interfaz diferente)
class PayPalSDK {
  makePayment(data: { total_amount: number; curr: string; token: string }) {
    return Promise.resolve({ transaction_id: 'txn_789', result: 'COMPLETED' });
  }
  reversePayment(data: { transaction: string }) {
    return Promise.resolve({ reversal_id: 'rev_000', result: 'COMPLETED' });
  }
}

class PayPalPaymentAdapter implements PaymentGateway {
  constructor(private paypal: PayPalSDK) {}

  async charge(amountCents: number, currency: string, source: string) {
    const result = await this.paypal.makePayment({
      total_amount: amountCents / 100,
      curr:         currency,
      token:        source,
    });
    return { id: result.transaction_id, status: result.result };
  }

  async refund(chargeId: string) {
    const result = await this.paypal.reversePayment({ transaction: chargeId });
    return { id: result.reversal_id, status: result.result };
  }
}

// Servicio que usa el contrato unificado
class OrderService {
  constructor(private payment: PaymentGateway) {}

  async processOrder(orderId: string, totalCents: number, paymentToken: string) {
    const charge = await this.payment.charge(totalCents, 'EUR', paymentToken);
    console.log(`Orden ${orderId} pagada. Cargo: ${charge.id}`);
    return charge;
  }
}

const orderService = new OrderService(new StripePaymentAdapter(new StripeSDK()));
// o bien:
// const orderService = new OrderService(new PayPalPaymentAdapter(new PayPalSDK()));
```

---

## Decorator

Añade funcionalidades a un objeto **dinámicamente** sin alterar su clase. Alternativa flexible a la herencia.

```typescript
interface HttpClient {
  get(url: string): Promise<{ status: number; data: unknown }>;
  post(url: string, body: unknown): Promise<{ status: number; data: unknown }>;
}

// Implementación base
class FetchHttpClient implements HttpClient {
  async get(url: string) {
    console.log(`GET ${url}`);
    return { status: 200, data: { ok: true } };
  }
  async post(url: string, body: unknown) {
    console.log(`POST ${url}`, body);
    return { status: 201, data: { id: 1 } };
  }
}

// Decorator: añade logging
class LoggingHttpClient implements HttpClient {
  constructor(private inner: HttpClient) {}

  async get(url: string) {
    console.time(`GET ${url}`);
    const res = await this.inner.get(url);
    console.timeEnd(`GET ${url}`);
    console.log(`→ Status ${res.status}`);
    return res;
  }

  async post(url: string, body: unknown) {
    console.time(`POST ${url}`);
    const res = await this.inner.post(url, body);
    console.timeEnd(`POST ${url}`);
    return res;
  }
}

// Decorator: añade reintentos
class RetryHttpClient implements HttpClient {
  constructor(private inner: HttpClient, private maxRetries = 3) {}

  private async withRetry<T>(fn: () => Promise<T>): Promise<T> {
    let lastError: Error | undefined;
    for (let attempt = 1; attempt <= this.maxRetries; attempt++) {
      try {
        return await fn();
      } catch (err) {
        lastError = err as Error;
        console.warn(`Intento ${attempt}/${this.maxRetries} fallido`);
        await new Promise(r => setTimeout(r, attempt * 500));
      }
    }
    throw lastError;
  }

  get(url: string)                { return this.withRetry(() => this.inner.get(url)); }
  post(url: string, body: unknown) { return this.withRetry(() => this.inner.post(url, body)); }
}

// Composición de decoradores
const client = new LoggingHttpClient(
  new RetryHttpClient(
    new FetchHttpClient(),
    3
  )
);
```

---

## Facade

Proporciona una **interfaz simplificada** a un conjunto de interfaces complejas en un subsistema.

```typescript
// Subsistema complejo de notificaciones
class EmailService {
  sendEmail(to: string, subject: string, body: string) {
    console.log(`[Email] → ${to}: ${subject}`);
  }
}

class SMSService {
  sendSMS(phone: string, msg: string) {
    console.log(`[SMS] → ${phone}: ${msg}`);
  }
}

class PushService {
  sendPush(deviceToken: string, title: string, body: string) {
    console.log(`[Push] → ${deviceToken}: ${title}`);
  }
}

class UserPreferenceService {
  getPreferences(userId: number) {
    // Simula preferencias del usuario
    return { wantsEmail: true, wantsSMS: true, wantsPush: false };
  }
}

// Facade: una sola operación de alto nivel
class NotificationFacade {
  private email   = new EmailService();
  private sms     = new SMSService();
  private push    = new PushService();
  private prefs   = new UserPreferenceService();

  notifyUser(userId: number, event: {
    type:   'order_shipped' | 'password_reset' | 'new_message';
    email:  string;
    phone:  string;
    device: string;
    data:   Record<string, string>;
  }): void {
    const p = this.prefs.getPreferences(userId);
    const subjects: Record<string, string> = {
      order_shipped:  `Tu pedido ${event.data.orderId} fue enviado`,
      password_reset: 'Restablecer contraseña',
      new_message:    `Nuevo mensaje de ${event.data.from}`,
    };
    const subject = subjects[event.type] ?? event.type;

    if (p.wantsEmail) this.email.sendEmail(event.email, subject, subject);
    if (p.wantsSMS)   this.sms.sendSMS(event.phone, subject);
    if (p.wantsPush)  this.push.sendPush(event.device, subject, subject);
  }
}

const notif = new NotificationFacade();
notif.notifyUser(42, {
  type:   'order_shipped',
  email:  'ana@example.com',
  phone:  '+34600000000',
  device: 'token-abc123',
  data:   { orderId: '1234' },
});
```

---

## Resumen: cuándo aplicar cada patrón

| Patrón | Problema que resuelve |
|---|---|
| **Repository** | Desacoplar la lógica de negocio del almacenamiento de datos |
| **Adapter** | Integrar una librería/servicio externo con una interfaz diferente |
| **Decorator** | Añadir cross-cutting concerns (logging, retry, cache, auth) sin modificar clases |
| **Facade** | Simplificar un subsistema complejo en una sola interfaz de alto nivel |
