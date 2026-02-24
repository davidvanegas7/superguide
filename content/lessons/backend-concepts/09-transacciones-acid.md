# Transacciones y ACID

Una **transacción** es un conjunto de operaciones que se ejecutan como una unidad atómica: o se completan todas, o no se aplica ninguna.

---

## Las propiedades ACID

### Atomicidad (Atomicity)
Una transacción es **todo o nada**. Si alguna operación falla, todas las anteriores se revierten.

```sql
-- Transferencia bancaria: deben ejecutarse AMBAS o NINGUNA
BEGIN;

    UPDATE accounts SET balance_cents = balance_cents - 10000
    WHERE id = 1 AND balance_cents >= 10000;

    -- Si la fila actualizada fue 0 (sin fondos), hacemos ROLLBACK
    -- desde la aplicación

    UPDATE accounts SET balance_cents = balance_cents + 10000
    WHERE id = 2;

COMMIT;  -- o ROLLBACK si algo fue mal
```

```typescript
// En TypeScript con pg (node-postgres)
import { Pool } from 'pg';

const pool = new Pool({ connectionString: process.env.DATABASE_URL });

async function transferFunds(
  fromId: number,
  toId:   number,
  amount: number
): Promise<void> {
  const client = await pool.connect();

  try {
    await client.query('BEGIN');

    const debit = await client.query(
      `UPDATE accounts
       SET balance_cents = balance_cents - $1
       WHERE id = $2 AND balance_cents >= $1
       RETURNING id`,
      [amount, fromId]
    );

    if (debit.rowCount === 0) {
      throw new Error('Fondos insuficientes');
    }

    await client.query(
      `UPDATE accounts
       SET balance_cents = balance_cents + $1
       WHERE id = $2`,
      [amount, toId]
    );

    await client.query('COMMIT');
    console.log(`Transferencia de ${amount} completada`);

  } catch (err) {
    await client.query('ROLLBACK');
    throw err;
  } finally {
    client.release();
  }
}
```

### Consistencia (Consistency)
La transacción lleva la base de datos de un **estado válido a otro válido**, respetando todas las restricciones (FK, CHECK, UNIQUE...).

```sql
-- Esta transacción fallará si viola la constraint
BEGIN;
    INSERT INTO orders (user_id, total_cents, status)
    VALUES (999, 5000, 'pending');
    -- ERROR: foreign key violation (user 999 no existe)
ROLLBACK;  -- automático por el error
```

### Aislamiento (Isolation)
Las transacciones concurrentes se ejecutan **como si fueran secuenciales**. Cada transacción ve un snapshot consistente.

### Durabilidad (Durability)
Una vez confirmada (COMMIT), la transacción **persiste** aunque el sistema falle inmediatamente después (gracias al Write-Ahead Log).

---

## Niveles de aislamiento

Los problemas de concurrencia que cada nivel previene:

| Nivel | Dirty Read | Non-repeatable Read | Phantom Read |
|---|---|---|---|
| READ UNCOMMITTED | ❌ posible | ❌ posible | ❌ posible |
| READ COMMITTED | ✅ evitado | ❌ posible | ❌ posible |
| REPEATABLE READ | ✅ evitado | ✅ evitado | ❌ posible |
| SERIALIZABLE | ✅ evitado | ✅ evitado | ✅ evitado |

**PostgreSQL usa READ COMMITTED por defecto.**

```sql
-- Cambiar nivel de aislamiento para una transacción
BEGIN TRANSACTION ISOLATION LEVEL SERIALIZABLE;
    -- ...
COMMIT;
```

### Fenómenos de concurrencia explicados

```
Dirty Read: Transacción A lee datos no confirmados de B
  T1: UPDATE balance = 0 (sin COMMIT)
  T2: SELECT balance → ve 0  ← INCORRECTO (T1 puede hacer ROLLBACK)

Non-repeatable Read: misma fila, distinto valor en la misma transacción
  T1: SELECT balance → 1000
  T2: UPDATE balance = 500; COMMIT
  T1: SELECT balance → 500  ← CAMBIÓ dentro de la misma tx

Phantom Read: misma consulta, distinto conjunto de filas
  T1: SELECT COUNT(*) FROM orders WHERE status='pending' → 5
  T2: INSERT INTO orders ... (pending); COMMIT
  T1: SELECT COUNT(*) ... → 6  ← APARECIO nueva fila
```

---

## Bloqueos (Locks)

### Bloqueo pesimista (Pessimistic Locking)
Bloquea la fila en el momento de leerla para evitar conflictos.

```sql
-- FOR UPDATE: bloquea las filas seleccionadas
BEGIN;
    SELECT id, balance_cents
    FROM   accounts
    WHERE  id = 1
    FOR    UPDATE;              -- nadie más puede leer/modificar esta fila

    UPDATE accounts SET balance_cents = balance_cents - 10000 WHERE id = 1;
COMMIT;

-- FOR SHARE: permite otras lecturas FOR SHARE, bloquea writes
SELECT * FROM products WHERE id = 5 FOR SHARE;

-- NOWAIT: falla si no puede obtener el lock inmediatamente
SELECT * FROM accounts WHERE id = 1 FOR UPDATE NOWAIT;

-- SKIP LOCKED: útil para colas de trabajo (job queues)
SELECT * FROM job_queue WHERE status = 'pending'
ORDER BY created_at
LIMIT 1
FOR UPDATE SKIP LOCKED;
```

### Bloqueo optimista (Optimistic Locking)
Detecta conflictos al escribir en lugar de bloquear al leer. Usando un campo `version`.

```typescript
interface Product {
  id:          number;
  name:        string;
  stock:       number;
  version:     number;  // campo de versión
}

async function updateStock(
  client: any,
  productId: number,
  delta: number
): Promise<void> {
  // Paso 1: leer con versión actual
  const { rows } = await client.query(
    'SELECT id, stock, version FROM products WHERE id = $1',
    [productId]
  );
  const product = rows[0] as Product;

  const newStock = product.stock + delta;
  if (newStock < 0) throw new Error('Stock insuficiente');

  // Paso 2: actualizar SOLO si la versión no cambió
  const result = await client.query(
    `UPDATE products
     SET stock = $1, version = version + 1
     WHERE id = $2 AND version = $3`,
    [newStock, productId, product.version]
  );

  if (result.rowCount === 0) {
    // Otro proceso modificó el producto entre nuestra lectura y escritura
    throw new Error('Conflicto de concurrencia, reintenta la operación');
  }
}
```

---

## Deadlocks

Dos transacciones se bloquean mutuamente esperando recursos que la otra tiene.

```
T1: LOCK A → espera B
T2: LOCK B → espera A
→ DEADLOCK
```

```sql
-- Prevención: siempre bloquear recursos en el MISMO ORDEN
-- T1 y T2 siempre hacen: cuenta con id menor primero

-- ❌ T1: UPDATE cuenta 1, luego cuenta 2
-- ❌ T2: UPDATE cuenta 2, luego cuenta 1  → DEADLOCK

-- ✅ Ambas: UPDATE MIN(id) primero, luego MAX(id)
BEGIN;
    SELECT id FROM accounts WHERE id IN (1, 2) ORDER BY id FOR UPDATE;
    -- ahora ambas transacciones bloquean en el mismo orden
    UPDATE accounts SET balance_cents = balance_cents - 10000 WHERE id = 1;
    UPDATE accounts SET balance_cents = balance_cents + 10000 WHERE id = 2;
COMMIT;
```

PostgreSQL detecta deadlocks automáticamente y aborta una de las transacciones con error `ERROR: deadlock detected`. La aplicación debe capturar este error y reintentar.

---

## SAVEPOINT: Rollback parcial

```sql
BEGIN;
    INSERT INTO users (email, name) VALUES ('a@test.com', 'Ana');

    SAVEPOINT sp1;

    INSERT INTO users (email, name) VALUES ('a@test.com', 'Duplicado');
    -- ERROR: duplicate key

    ROLLBACK TO SAVEPOINT sp1;  -- solo deshace desde sp1

    -- La inserción de Ana sigue en pie
    INSERT INTO users (email, name) VALUES ('b@test.com', 'Bob');

COMMIT;  -- Ana y Bob insertados, el duplicado nunca ocurrió
```

---

## Resumen

| Concepto | Descripción |
|---|---|
| **BEGIN / COMMIT / ROLLBACK** | Control de transacción explícito |
| **ACID** | Garantías que ofrece una transacción |
| **READ COMMITTED** | Nivel de aislamiento más común, evita dirty reads |
| **SERIALIZABLE** | Máximo aislamiento, máxima consistencia, menor rendimiento |
| **Pessimistic Lock** | `FOR UPDATE` — bloquea al leer, más seguro con alta contención |
| **Optimistic Lock** | Campo `version` — detecta conflictos al escribir, mejor rendimiento con baja contención |
| **DEADLOCK** | Bloquea al ordenar recursos consistentemente; captura y reintenta en la app |
