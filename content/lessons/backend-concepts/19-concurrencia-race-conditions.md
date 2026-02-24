# Concurrencia y Race Conditions

En sistemas con múltiples usuarios simultáneos, las operaciones que parecen seguras de forma aislada pueden producir resultados incorrectos cuando se ejecutan en paralelo.

---

## ¿Qué es una Race Condition?

Una **race condition** ocurre cuando el resultado de una operación depende del **orden de ejecución** de procesos concurrentes, y ese orden no está controlado.

```typescript
// ❌ Race condition clásica: dos usuarios intentan usar el mismo cupón

// Usuario A y B llegan al mismo tiempo con el cupón "DESCUENTO50"
// El cupón solo puede usarse UNA vez

// Ambos leen: coupon.used = false (el check pasa para ambos)
const coupon = await db.findCoupon('DESCUENTO50');
if (coupon.used) throw new Error('Cupón ya usado');

// Aquí hay una "ventana" donde ambos pasan el check
// Ambos escriben: SET used = true
await db.markCouponUsed('DESCUENTO50');  // Los dos llegan aquí
await db.applyDiscount(orderId, coupon.discount);  // ¡Ambos obtienen el descuento!
```

---

## Soluciones con la base de datos

### UPDATE atómico (sin SELECT previo)

```typescript
// ✅ UPDATE condicional: atómico en la BD
async function useCoupon(couponCode: string, orderId: string): Promise<boolean> {
  const result = await db.query(`
    UPDATE coupons
    SET used = TRUE, used_at = NOW(), order_id = $2
    WHERE code = $1 AND used = FALSE   -- condición en el UPDATE
    RETURNING id
  `, [couponCode, orderId]);

  // Si rowCount = 0, alguien llegó primero
  return result.rowCount! > 0;
}

// Uso
const success = await useCoupon('DESCUENTO50', orderId);
if (!success) throw new Error('El cupón ya fue utilizado');
```

### SELECT ... FOR UPDATE (Pessimistic Lock)

```typescript
async function decrementStock(productId: number, qty: number): Promise<void> {
  const client = await pool.connect();
  try {
    await client.query('BEGIN');

    // Bloquea la fila durante la transacción
    const { rows } = await client.query(
      'SELECT id, stock FROM products WHERE id = $1 FOR UPDATE',
      [productId]
    );

    if (!rows[0]) throw new Error('Producto no encontrado');
    if (rows[0].stock < qty) throw new Error('Stock insuficiente');

    await client.query(
      'UPDATE products SET stock = stock - $1 WHERE id = $2',
      [qty, productId]
    );

    await client.query('COMMIT');
  } catch (err) {
    await client.query('ROLLBACK');
    throw err;
  } finally {
    client.release();
  }
}
```

### Optimistic Locking con campo `version`

```typescript
async function updateUserProfile(
  userId: number,
  data:    Partial<User>,
  version: number
): Promise<User> {
  const result = await db.query(`
    UPDATE users
    SET name = $1, bio = $2, version = version + 1, updated_at = NOW()
    WHERE id = $3 AND version = $4
    RETURNING *
  `, [data.name, data.bio, userId, version]);

  if (!result.rows[0]) {
    // version no coincide: otro proceso actualizó antes que nosotros
    throw new OptimisticLockError('El perfil fue modificado por otro proceso. Recarga y vuelve a intentarlo.');
  }

  return result.rows[0];
}

class OptimisticLockError extends Error {
  constructor(message: string) {
    super(message);
    this.name = 'OptimisticLockError';
  }
}
```

---

## Distributed Locks (Redis)

Cuando tienes **múltiples instancias** de la app, los locks de base de datos y los de proceso no son suficientes.

```typescript
import Redis from 'ioredis';

const redis = new Redis();

class DistributedLock {
  constructor(private redis: Redis) {}

  async acquire(key: string, ttlMs: number): Promise<string | null> {
    const lockId = crypto.randomUUID();
    // SET key lockId PX ttlMs NX (atómico)
    const result = await this.redis.set(`lock:${key}`, lockId, 'PX', ttlMs, 'NX');
    return result === 'OK' ? lockId : null;
  }

  async release(key: string, lockId: string): Promise<boolean> {
    // Lua script para verificar y liberar atómicamente
    const script = `
      if redis.call("get", KEYS[1]) == ARGV[1] then
        return redis.call("del", KEYS[1])
      else
        return 0
      end
    `;
    const result = await this.redis.eval(script, 1, `lock:${key}`, lockId);
    return result === 1;
  }

  async withLock<T>(
    key:    string,
    ttlMs:  number,
    fn:     () => Promise<T>
  ): Promise<T> {
    const lockId = await this.acquire(key, ttlMs);
    if (!lockId) throw new Error(`No se pudo adquirir el lock: ${key}`);

    try {
      return await fn();
    } finally {
      await this.release(key, lockId);
    }
  }
}

const lock = new DistributedLock(redis);

// Solo una instancia procesa el pago a la vez
async function processPayment(orderId: string): Promise<void> {
  await lock.withLock(`payment:${orderId}`, 30_000, async () => {
    // Verificar que no se procesó ya
    const order = await orderRepo.findById(orderId);
    if (order.status !== 'pending') return;

    await paymentGateway.charge(order.totalCents, order.paymentMethod);
    await orderRepo.updateStatus(orderId, 'paid');
  });
}
```

---

## Idempotencia como solución

Diseñar operaciones **idempotentes** es la solución más robusta: si se ejecutan múltiples veces, el resultado es el mismo.

```typescript
// ❌ No idempotente: múltiples llamadas crean múltiples pedidos
app.post('/orders', async (req, res) => {
  const order = await orderService.create(req.body);
  res.status(201).json(order);
});

// ✅ Idempotente con Idempotency-Key
app.post('/orders', async (req, res) => {
  const key = req.headers['idempotency-key'] as string;
  if (!key) return res.status(400).json({ error: 'Idempotency-Key requerida' });

  // Intenta obtener resultado previo
  const cached = await redis.get(`idem:${key}`);
  if (cached) {
    return res.status(200).json(JSON.parse(cached));  // misma respuesta
  }

  // Adquiere lock para evitar procesamiento doble
  const lockId = await lock.acquire(`idem-lock:${key}`, 10_000);
  if (!lockId) {
    // Otra instancia está procesando, espera y reinspecciona
    await new Promise(r => setTimeout(r, 500));
    const retryCache = await redis.get(`idem:${key}`);
    if (retryCache) return res.status(200).json(JSON.parse(retryCache));
    return res.status(409).json({ error: 'Operación en progreso' });
  }

  try {
    const order    = await orderService.create(req.body);
    const response = { data: order };

    // Guarda el resultado con TTL de 24 horas
    await redis.setex(`idem:${key}`, 86400, JSON.stringify(response));

    res.status(201).json(response);
  } finally {
    await lock.release(`idem-lock:${key}`, lockId);
  }
});
```

---

## Deadlocks: cómo prevenirlos

```typescript
// ❌ Puede causar deadlock: T1 bloquea cuenta 1 luego 2; T2 bloquea cuenta 2 luego 1
async function transferBad(fromId: number, toId: number, amount: number) {
  const client = await pool.connect();
  await client.query('BEGIN');
  await client.query('SELECT * FROM accounts WHERE id = $1 FOR UPDATE', [fromId]);
  // T2 entra aquí con fromId=toId y toId=fromId → deadlock
  await client.query('SELECT * FROM accounts WHERE id = $1 FOR UPDATE', [toId]);
  // ...
}

// ✅ Siempre bloquear en el mismo orden (menor ID primero)
async function transferSafe(fromId: number, toId: number, amount: number) {
  const client = await pool.connect();
  const [first, second] = [Math.min(fromId, toId), Math.max(fromId, toId)];

  try {
    await client.query('BEGIN');

    // Bloquea siempre en orden ascendente de ID
    const { rows } = await client.query(
      'SELECT id, balance_cents FROM accounts WHERE id = ANY($1) ORDER BY id FOR UPDATE',
      [[first, second]]
    );

    const fromAccount = rows.find(r => r.id === fromId)!;
    const toAccount   = rows.find(r => r.id === toId)!;

    if (fromAccount.balance_cents < amount) throw new Error('Fondos insuficientes');

    await client.query('UPDATE accounts SET balance_cents = balance_cents - $1 WHERE id = $2', [amount, fromId]);
    await client.query('UPDATE accounts SET balance_cents = balance_cents + $1 WHERE id = $2', [amount, toId]);

    await client.query('COMMIT');
  } catch (err) {
    await client.query('ROLLBACK');
    throw err;
  } finally {
    client.release();
  }
}
```

---

## Resumen

| Técnica | Cuándo usarla |
|---|---|
| **UPDATE condicional** | Operaciones simples: marcar cupones, reservar slots |
| **SELECT FOR UPDATE** | Lecturas seguidas de escrituras en la misma TX |
| **Optimistic Lock** | Baja contención, muchas lecturas, pocas escrituras |
| **Distributed Lock** | Múltiples instancias, operaciones exclusivas (pagos, emails únicos) |
| **Idempotency Key** | Operaciones que el cliente puede reintentar (API externa) |
| **Orden de locks consistente** | Prevenir deadlocks en operaciones multi-recurso |
