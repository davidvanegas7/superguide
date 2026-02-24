# Bases de Datos Relacionales

Las **bases de datos relacionales** organizan los datos en **tablas** (relaciones) con filas (registros) y columnas (atributos). Se basan en la teoría de conjuntos y el álgebra relacional.

---

## Conceptos fundamentales

### Tabla, fila y columna

```sql
-- Una tabla representa una entidad del dominio
CREATE TABLE users (
    id         SERIAL        PRIMARY KEY,      -- PK: identificador único
    email      VARCHAR(255)  NOT NULL UNIQUE,   -- restricción de unicidad
    name       VARCHAR(100)  NOT NULL,
    role       VARCHAR(20)   NOT NULL DEFAULT 'user',
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP     NULL               -- soft delete
);

-- Tabla con clave foránea (relación)
CREATE TABLE orders (
    id          SERIAL         PRIMARY KEY,
    user_id     INT            NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    total_cents INT            NOT NULL CHECK (total_cents >= 0),
    status      VARCHAR(20)    NOT NULL DEFAULT 'pending',
    created_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

### Tipos de relaciones

| Tipo | Ejemplo | Implementación |
|---|---|---|
| **1:1** | Cada usuario tiene un perfil | FK en una de las tablas |
| **1:N** | Un usuario tiene muchos pedidos | FK en la tabla "muchos" |
| **N:M** | Pedidos tienen muchos productos | Tabla intermedia (junction table) |

```sql
-- Relación N:M con tabla intermedia
CREATE TABLE products (
    id        SERIAL        PRIMARY KEY,
    name      VARCHAR(100)  NOT NULL,
    price_cents INT         NOT NULL
);

CREATE TABLE order_items (
    order_id    INT  NOT NULL REFERENCES orders(id),
    product_id  INT  NOT NULL REFERENCES products(id),
    quantity    INT  NOT NULL DEFAULT 1,
    unit_price  INT  NOT NULL,           -- precio al momento del pedido
    PRIMARY KEY (order_id, product_id)  -- clave compuesta
);
```

---

## Normalización

La normalización elimina redundancias y anomalías de actualización/inserción/borrado.

### Primera Forma Normal (1NF)
Cada celda contiene un **valor atómico** (no listas ni grupos repetidos).

```sql
-- ❌ Viola 1NF: columnas repetidas
CREATE TABLE bad_orders (
    id          INT,
    product1    VARCHAR(100),
    quantity1   INT,
    product2    VARCHAR(100),
    quantity2   INT
);

-- ✅ Cumple 1NF: tabla order_items separada
```

### Segunda Forma Normal (2NF)
Cumple 1NF + cada columna no-clave depende de **toda** la clave primaria.

```sql
-- ❌ Viola 2NF: product_name depende solo de product_id, no de la PK compuesta
CREATE TABLE bad_order_items (
    order_id      INT,
    product_id    INT,
    product_name  VARCHAR(100),  -- debería estar en products
    quantity      INT,
    PRIMARY KEY (order_id, product_id)
);

-- ✅ 2NF: product_name va en la tabla products
```

### Tercera Forma Normal (3NF)
Cumple 2NF + no hay dependencias transitivas (A→B→C donde A es PK).

```sql
-- ❌ Viola 3NF: city depende de zip_code, no de user_id
CREATE TABLE bad_users (
    id        INT PRIMARY KEY,
    name      VARCHAR(100),
    zip_code  VARCHAR(10),
    city      VARCHAR(100)   -- dependencia transitiva: id→zip_code→city
);

-- ✅ 3NF: separar zip_codes
CREATE TABLE zip_codes (
    code VARCHAR(10) PRIMARY KEY,
    city VARCHAR(100)
);

CREATE TABLE users_3nf (
    id       INT PRIMARY KEY,
    name     VARCHAR(100),
    zip_code VARCHAR(10) REFERENCES zip_codes(code)
);
```

---

## Índices

Los índices aceleran las consultas a costa de espacio en disco y rendimiento en escrituras.

```sql
-- Índice simple (para búsquedas por email)
CREATE INDEX idx_users_email ON users(email);

-- Índice compuesto (para consultas con múltiples condiciones)
CREATE INDEX idx_orders_user_status ON orders(user_id, status);

-- Índice único (garantiza unicidad + mejora búsqueda)
CREATE UNIQUE INDEX idx_users_email_unique ON users(email);

-- Índice parcial (indexa solo un subconjunto)
CREATE INDEX idx_orders_pending ON orders(created_at)
WHERE status = 'pending';

-- Ver si la consulta usa el índice
EXPLAIN SELECT * FROM orders WHERE user_id = 1 AND status = 'shipped';
```

**Cuándo NO crear un índice:**
- Tablas muy pequeñas (< 1000 filas)
- Columnas con muy baja cardinalidad (ej. un campo booleano)
- Tablas con muchas escrituras y pocas lecturas

---

## Restricciones (Constraints)

```sql
CREATE TABLE accounts (
    id            SERIAL      PRIMARY KEY,
    user_id       INT         NOT NULL REFERENCES users(id),
    balance_cents INT         NOT NULL DEFAULT 0,
    currency      CHAR(3)     NOT NULL DEFAULT 'EUR',
    is_active     BOOLEAN     NOT NULL DEFAULT TRUE,

    -- Restricción de dominio
    CONSTRAINT chk_balance_non_negative CHECK (balance_cents >= 0),
    CONSTRAINT chk_currency_valid       CHECK (currency IN ('EUR', 'USD', 'GBP')),

    -- Unicidad compuesta
    CONSTRAINT uq_user_currency UNIQUE (user_id, currency)
);
```

---

## DDL vs DML vs DCL

```sql
-- DDL (Data Definition Language): define estructura
CREATE TABLE ...;
ALTER TABLE users ADD COLUMN phone VARCHAR(20);
ALTER TABLE users DROP COLUMN phone;
DROP TABLE old_table;

-- DML (Data Manipulation Language): manipula datos
INSERT INTO users (email, name) VALUES ('ana@test.com', 'Ana');
SELECT id, name FROM users WHERE role = 'admin';
UPDATE users SET name = 'Ana García' WHERE id = 1;
DELETE FROM users WHERE deleted_at IS NOT NULL;

-- DCL (Data Control Language): permisos
GRANT SELECT, INSERT ON users TO app_user;
REVOKE DELETE ON users FROM app_user;
```

---

## Modelo entidad-relación

```
┌─────────────┐          ┌────────────────┐          ┌──────────────┐
│   USERS     │  1     N │    ORDERS      │  N     M │   PRODUCTS   │
│─────────────│──────────│────────────────│──────────│──────────────│
│ id (PK)     │          │ id (PK)        │          │ id (PK)      │
│ email       │          │ user_id (FK)   │          │ name         │
│ name        │          │ total_cents    │          │ price_cents  │
│ role        │          │ status         │          │ stock        │
└─────────────┘          └────────────────┘          └──────────────┘
                                 │ 1                          │
                                 │ N                          │
                         ┌───────────────┐                   │
                         │  ORDER_ITEMS  │───────────────────┘
                         │───────────────│
                         │ order_id (FK) │
                         │ product_id(FK)│
                         │ quantity      │
                         │ unit_price    │
                         └───────────────┘
```

---

## Buenas prácticas

1. **Usa claves subrogadas** (`SERIAL` / `UUID`) en vez de claves naturales cuando sea posible
2. **Prefiere FK con ON DELETE** apropiado: `CASCADE`, `SET NULL`, o `RESTRICT`
3. **Nombra constraints explícitamente** para mensajes de error legibles
4. **Soft delete** con `deleted_at` en vez de `DELETE` físico (para auditoría)
5. **Columnas de auditoría** (`created_at`, `updated_at`) en todas las tablas de entidad
6. **Normaliza primero**, desnormaliza solo cuando midas un problema de rendimiento real
