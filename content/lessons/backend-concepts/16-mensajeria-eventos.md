# Mensajería y Arquitectura Orientada a Eventos

En una **arquitectura orientada a eventos**, los componentes se comunican emitiendo y escuchando eventos en lugar de llamarse directamente. Esto desacopla los servicios y permite procesar tareas de forma asíncrona.

---

## Comunicación síncrona vs asíncrona

```
Síncrona (llamada directa):
  OrderService → llama → InventoryService (espera respuesta)
                       → llama → EmailService (espera respuesta)
                       → llama → InvoiceService (espera respuesta)
  Latencia total = suma de todas las esperas

Asíncrona (eventos):
  OrderService → publica evento "order.placed" → Queue
  (responde inmediatamente al cliente)

  En paralelo, los consumidores procesan:
  InventoryService ← consume "order.placed" → descuenta stock
  EmailService     ← consume "order.placed" → envía confirmación
  InvoiceService   ← consume "order.placed" → genera factura
```

---

## Event Bus (in-process)

Para comunicación entre módulos dentro del mismo proceso.

```typescript
type EventPayload = Record<string, unknown>;

class EventBus {
  private static instance: EventBus | null = null;
  private handlers = new Map<string, Array<(payload: EventPayload) => Promise<void>>>();

  static getInstance(): EventBus {
    if (!EventBus.instance) EventBus.instance = new EventBus();
    return EventBus.instance;
  }

  subscribe(event: string, handler: (payload: EventPayload) => Promise<void>): () => void {
    const list = this.handlers.get(event) ?? [];
    list.push(handler);
    this.handlers.set(event, list);

    // Retorna una función para desuscribirse
    return () => {
      const updated = this.handlers.get(event)?.filter(h => h !== handler) ?? [];
      this.handlers.set(event, updated);
    };
  }

  async publish(event: string, payload: EventPayload): Promise<void> {
    const handlers = this.handlers.get(event) ?? [];
    console.log(`[EventBus] ${event} → ${handlers.length} suscriptores`);

    const results = await Promise.allSettled(handlers.map(h => h(payload)));

    for (const result of results) {
      if (result.status === 'rejected') {
        console.error(`[EventBus] Handler falló para ${event}:`, result.reason);
      }
    }
  }
}

// Uso
const bus = EventBus.getInstance();

bus.subscribe('order.placed', async ({ orderId, userId, total }) => {
  console.log(`[Inventory] Descontando stock para orden ${orderId}`);
});

bus.subscribe('order.placed', async ({ orderId, email }) => {
  console.log(`[Email] Enviando confirmación de ${orderId} a ${email}`);
});

// Desde el servicio de pedidos
await bus.publish('order.placed', {
  orderId: 'ORD-001',
  userId:  1,
  total:   4999,
  email:   'ana@example.com',
});
```

---

## Message Queue (Bull/BullMQ con Redis)

Para procesar tareas en background de forma resiliente.

```typescript
import { Queue, Worker, Job } from 'bullmq';

const connection = { host: 'localhost', port: 6379 };

// Definir tipos de jobs
interface EmailJobData {
  to:       string;
  template: 'welcome' | 'order_confirmation' | 'password_reset';
  params:   Record<string, string>;
}

// Productor: añade trabajos a la cola
const emailQueue = new Queue<EmailJobData>('email', {
  connection,
  defaultJobOptions: {
    attempts:  3,
    backoff: { type: 'exponential', delay: 2000 },  // 2s, 4s, 8s
    removeOnComplete: 100,   // conserva los últimos 100 completados
    removeOnFail:     500,   // conserva los últimos 500 fallidos
  },
});

async function sendWelcomeEmail(userId: number, email: string, name: string): Promise<void> {
  await emailQueue.add('send', {
    to:       email,
    template: 'welcome',
    params:   { name, userId: String(userId) },
  }, {
    delay: 5000,  // esperar 5 segundos antes de procesar
  });
  console.log(`[Queue] Email de bienvenida encolado para ${email}`);
}

// Consumidor: procesa los trabajos
const emailWorker = new Worker<EmailJobData>(
  'email',
  async (job: Job<EmailJobData>) => {
    console.log(`[Worker] Procesando job ${job.id}: ${job.data.template} → ${job.data.to}`);

    // Lógica real de envío
    await mailerService.send(job.data.to, job.data.template, job.data.params);

    return { sentAt: new Date().toISOString() };  // guardado en job.returnvalue
  },
  {
    connection,
    concurrency: 5,  // procesa 5 emails simultáneamente
  }
);

// Listeners de eventos del worker
emailWorker.on('completed', (job) => {
  console.log(`[Worker] ✅ Job ${job.id} completado`);
});

emailWorker.on('failed', (job, err) => {
  console.error(`[Worker] ❌ Job ${job?.id} falló:`, err.message);
});
```

---

## Pub/Sub con Redis

Para notificaciones en tiempo real entre instancias de la app.

```typescript
import Redis from 'ioredis';

const publisher  = new Redis();
const subscriber = new Redis();  // conexión separada para suscripción

// Publicar
async function broadcastUserOnline(userId: number): Promise<void> {
  await publisher.publish('presence', JSON.stringify({ type: 'online', userId }));
}

// Suscribir
subscriber.subscribe('presence', 'notifications', 'system');

subscriber.on('message', (channel: string, message: string) => {
  const data = JSON.parse(message);
  console.log(`[PubSub] ${channel}:`, data);

  if (channel === 'presence') {
    websocketServer.broadcast(data);  // reenvía por WebSocket a los clientes
  }
});
```

---

## Outbox Pattern: garantía de entrega

El problema: si la BD se guarda pero el evento falla, se pierde la consistencia.

```typescript
// ✅ Outbox Pattern: guarda el evento EN LA MISMA TRANSACCIÓN que el dato

async function placeOrder(userId: number, items: OrderItem[]): Promise<void> {
  await db.transaction(async (trx) => {
    // 1. Crea el pedido
    const [order] = await trx.query(`
      INSERT INTO orders (user_id, status) VALUES ($1, 'confirmed')
      RETURNING id
    `, [userId]);

    // 2. Guarda el evento en la misma transacción (outbox table)
    await trx.query(`
      INSERT INTO outbox_events (aggregate_type, aggregate_id, event_type, payload)
      VALUES ('Order', $1, 'order.placed', $2)
    `, [order.id, JSON.stringify({ orderId: order.id, userId })]);

    // Si la transacción falla → ni el pedido ni el evento se guardan (atomicidad)
  });
}

// Un proceso aparte lee la outbox y publica los eventos
class OutboxProcessor {
  async processOutbox(): Promise<void> {
    const events = await db.query(`
      SELECT * FROM outbox_events
      WHERE processed_at IS NULL
      ORDER BY created_at
      LIMIT 100
      FOR UPDATE SKIP LOCKED
    `);

    for (const event of events) {
      try {
        await messageBroker.publish(event.event_type, event.payload);
        await db.query(
          'UPDATE outbox_events SET processed_at = NOW() WHERE id = $1',
          [event.id]
        );
      } catch (err) {
        console.error(`Error procesando evento ${event.id}:`, err);
      }
    }
  }
}
```

---

## CQRS (Command Query Responsibility Segregation)

Separa las operaciones de escritura (Commands) de las de lectura (Queries).

```typescript
// Commands: escriben, retornan void o ID
interface PlaceOrderCommand {
  type: 'PLACE_ORDER';
  userId: number;
  items: Array<{ productId: number; qty: number }>;
}

interface CancelOrderCommand {
  type: 'CANCEL_ORDER';
  orderId: string;
  reason: string;
}

// Queries: solo leen, nunca modifican estado
interface GetOrderByIdQuery {
  type: 'GET_ORDER';
  orderId: string;
}

interface GetOrdersByUserQuery {
  type: 'GET_ORDERS_BY_USER';
  userId: number;
  page:   number;
}

// Modelos de lectura (optimizados para queries, pueden ser desnormalizados)
interface OrderReadModel {
  id:           string;
  userEmail:    string;
  userName:     string;
  productNames: string[];
  total:        number;
  status:       string;
  createdAt:    string;
}
```

---

## Resumen de patrones

| Patrón | Uso |
|---|---|
| **Event Bus** | Comunicación in-process entre módulos |
| **Message Queue** | Procesamiento asíncrono resiliente (emails, PDFs, pagos) |
| **Pub/Sub** | Notificaciones en tiempo real entre instancias |
| **Outbox Pattern** | Garantizar que los eventos se publican si la transacción confirma |
| **CQRS** | Escalar lecturas y escrituras de forma independiente |
