# Performance y Escalabilidad en Backend

El rendimiento es un requisito no funcional crítico. Antes de optimizar, siempre **mide**. La optimización prematura es la raíz de muchos males.

---

## Complejidad algorítmica (Big O)

Entender Big O es fundamental para predecir cómo escala tu código.

```typescript
// O(1) — tiempo constante
function getFirstElement<T>(arr: T[]): T | undefined {
  return arr[0];  // siempre igual de rápido, sin importar el tamaño
}

// O(log n) — logarítmico (búsqueda binaria)
function binarySearch(sorted: number[], target: number): number {
  let low = 0, high = sorted.length - 1;
  while (low <= high) {
    const mid = Math.floor((low + high) / 2);
    if (sorted[mid] === target) return mid;
    if (sorted[mid] < target)  low  = mid + 1;
    else                        high = mid - 1;
  }
  return -1;
}

// O(n) — lineal
function findUser(users: User[], email: string): User | undefined {
  return users.find(u => u.email === email);
}

// O(n²) — cuadrático (evitar con n > 1000)
function hasDuplicates(arr: string[]): boolean {
  for (let i = 0; i < arr.length; i++) {
    for (let j = i + 1; j < arr.length; j++) {
      if (arr[i] === arr[j]) return true;  // ❌ O(n²)
    }
  }
  return false;
}

// ✅ O(n) — usando Set
function hasDuplicatesFast(arr: string[]): boolean {
  const seen = new Set<string>();
  for (const item of arr) {
    if (seen.has(item)) return true;
    seen.add(item);
  }
  return false;
}
```

---

## Optimización de consultas a BD

### El problema N+1 (revisión práctica)

```typescript
// ❌ N+1: 1 query para usuarios + N queries para pedidos
async function getReportSlow(userIds: number[]) {
  const users = await userRepo.findByIds(userIds);       // 1 query
  const result = [];

  for (const user of users) {
    const orders = await orderRepo.findByUser(user.id);  // N queries!
    result.push({ user, orderCount: orders.length, total: orders.reduce(...) });
  }
  return result;
}

// ✅ 2 queries: mucho más eficiente
async function getReportFast(userIds: number[]) {
  const [users, orders] = await Promise.all([
    userRepo.findByIds(userIds),
    orderRepo.findByUserIds(userIds),   // WHERE user_id IN (...)
  ]);

  const ordersByUser = Map.groupBy(orders, o => o.userId);  // O(n)

  return users.map(user => {
    const userOrders = ordersByUser.get(user.id) ?? [];
    return {
      user,
      orderCount: userOrders.length,
      total: userOrders.reduce((s, o) => s + o.totalCents, 0),
    };
  });
}
```

### Índices de BD

```sql
-- Identifica queries lentas
-- PostgreSQL: pg_stat_statements
SELECT query, calls, mean_exec_time, total_exec_time
FROM pg_stat_statements
ORDER BY mean_exec_time DESC
LIMIT 10;

-- Identifica índices faltantes
SELECT schemaname, tablename, seq_scan, idx_scan, n_live_tup
FROM pg_stat_user_tables
WHERE seq_scan > idx_scan
ORDER BY seq_scan DESC;

-- Índice covering: incluye todas las columnas de la query
CREATE INDEX idx_orders_status_covering ON orders(status, created_at)
INCLUDE (user_id, total_cents);
-- → la query puede resolverse solo con el índice, sin ir a la tabla
```

---

## Connection Pooling

```typescript
import { Pool } from 'pg';

// ❌ Nueva conexión por petición (costoso: ~10-50ms por conexión)
app.get('/users', async (req, res) => {
  const client = new Client({ connectionString: process.env.DATABASE_URL });
  await client.connect();    // lento
  const { rows } = await client.query('SELECT * FROM users');
  await client.end();        // libera la conexión
  res.json(rows);
});

// ✅ Pool: reutiliza conexiones
const pool = new Pool({
  connectionString: process.env.DATABASE_URL,
  max:              20,     // máximo 20 conexiones simultáneas
  min:              2,      // mínimo 2 conexiones en espera
  idleTimeoutMillis: 30000, // cierra conexiones inactivas después de 30s
  connectionTimeoutMillis: 5000,  // error si no hay conexión disponible en 5s
});

app.get('/users', async (req, res) => {
  const { rows } = await pool.query('SELECT * FROM users');
  res.json(rows);
  // La conexión vuelve automáticamente al pool
});
```

---

## Paginación eficiente

```typescript
// ❌ OFFSET es O(n): escanea y descarta las primeras n filas
const page10000 = await db.query(
  'SELECT * FROM events ORDER BY id LIMIT 20 OFFSET 200000'
);
// PostgreSQL debe leer 200.020 filas para devolver 20

// ✅ Cursor pagination: siempre O(log n) con índice
interface CursorPage<T> {
  data:    T[];
  cursor:  string | null;  // ID del último elemento, opaco para el cliente
  hasMore: boolean;
}

async function getEventsCursor(
  afterId: number | null,
  limit = 20
): Promise<CursorPage<Event>> {
  const where = afterId ? `AND id > ${afterId}` : '';
  const rows  = await db.query(`
    SELECT * FROM events
    WHERE 1=1 ${where}
    ORDER BY id ASC
    LIMIT ${limit + 1}     -- pide uno más para saber si hay siguiente página
  `);

  const hasMore = rows.length > limit;
  const data    = rows.slice(0, limit);

  return {
    data,
    cursor:  hasMore ? String(data[data.length - 1].id) : null,
    hasMore,
  };
}
```

---

## Procesamiento en paralelo

```typescript
// ❌ Secuencial: 300ms + 200ms + 150ms = 650ms
async function getUserDashboardSlow(userId: number) {
  const user     = await userRepo.findById(userId);      // 300ms
  const orders   = await orderRepo.findByUser(userId);   // 200ms
  const notifs   = await notifRepo.findByUser(userId);   // 150ms
  return { user, orders, notifications: notifs };
}

// ✅ Paralelo con Promise.all: max(300, 200, 150) = 300ms
async function getUserDashboardFast(userId: number) {
  const [user, orders, notifs] = await Promise.all([
    userRepo.findById(userId),
    orderRepo.findByUser(userId),
    notifRepo.findByUser(userId),
  ]);
  return { user, orders, notifications: notifs };
}

// Promise.allSettled: continúa aunque alguno falle
async function getUserDashboardResilient(userId: number) {
  const results = await Promise.allSettled([
    userRepo.findById(userId),
    orderRepo.findByUser(userId),
    notifRepo.findByUser(userId),
  ]);

  return {
    user:   results[0].status === 'fulfilled' ? results[0].value : null,
    orders: results[1].status === 'fulfilled' ? results[1].value : [],
    notifs: results[2].status === 'fulfilled' ? results[2].value : [],
  };
}
```

---

## Streaming de respuestas grandes

```typescript
import { createReadStream } from 'fs';
import { Transform } from 'stream';

// ❌ Carga todo en memoria
app.get('/export/users', async (req, res) => {
  const users = await db.query('SELECT * FROM users');  // puede ser GB
  res.json(users);                                       // OOM para datasets grandes
});

// ✅ Streaming: memoria O(1)
app.get('/export/users', (req, res) => {
  res.setHeader('Content-Type', 'application/x-ndjson');  // newline-delimited JSON

  const cursor = db.queryCursor('SELECT * FROM users ORDER BY id');

  cursor.on('data', (row) => {
    res.write(JSON.stringify(row) + '\n');
  });

  cursor.on('end', () => res.end());
  cursor.on('error', (err) => res.destroy(err));
});
```

---

## Métricas clave de performance

| Métrica | Objetivo típico | Herramienta |
|---|---|---|
| **Latencia P50** | < 50ms | Prometheus, Datadog |
| **Latencia P99** | < 500ms | Prometheus, Datadog |
| **Throughput** | > 1000 req/s (depende del sistema) | k6, Artillery |
| **Error rate** | < 0.1% | Sentry, Datadog |
| **DB query time** | < 50ms para P99 | pg_stat_statements |
| **Memory usage** | < 80% del límite del proceso | process.memoryUsage() |

```typescript
// Middleware para medir latencia de cada request
app.use((req, res, next) => {
  const start = process.hrtime.bigint();

  res.on('finish', () => {
    const durationMs = Number(process.hrtime.bigint() - start) / 1_000_000;
    metrics.histogram('http.request.duration', durationMs, {
      method:   req.method,
      route:    req.route?.path ?? 'unknown',
      status:   String(res.statusCode),
    });
  });

  next();
});
```
