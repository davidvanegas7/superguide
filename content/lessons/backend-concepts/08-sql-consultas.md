# SQL: Consultas y Optimización

SQL (Structured Query Language) es el lenguaje estándar para interactuar con bases de datos relacionales. Dominar sus construcciones avanzadas es esencial para escribir código backend eficiente.

---

## SELECT: Fundamentos

```sql
-- Proyección y filtrado
SELECT id, name, email
FROM   users
WHERE  role = 'admin'
  AND  created_at >= '2024-01-01'
  AND  deleted_at IS NULL
ORDER  BY created_at DESC
LIMIT  10 OFFSET 20;  -- Paginación: página 3, 10 por página

-- Alias y expresiones
SELECT
    id,
    UPPER(name)                              AS name_upper,
    DATE_TRUNC('month', created_at)          AS month,
    EXTRACT(YEAR FROM created_at)            AS year,
    CASE
        WHEN total_cents > 10000 THEN 'VIP'
        WHEN total_cents > 5000  THEN 'Premium'
        ELSE                          'Standard'
    END AS customer_tier
FROM users u
JOIN orders o ON o.user_id = u.id;
```

---

## JOINs

```sql
-- INNER JOIN: solo coincidencias en ambas tablas
SELECT u.name, o.id AS order_id, o.total_cents
FROM   users  u
JOIN   orders o ON o.user_id = u.id
WHERE  o.status = 'shipped';

-- LEFT JOIN: todos los usuarios, hayan pedido o no
SELECT u.name, COUNT(o.id) AS order_count
FROM   users  u
LEFT JOIN orders o ON o.user_id = u.id AND o.deleted_at IS NULL
GROUP BY u.id, u.name
ORDER BY order_count DESC;

-- JOIN múltiple con tabla intermedia N:M
SELECT
    o.id AS order_id,
    u.name AS customer,
    p.name AS product,
    oi.quantity,
    oi.unit_price
FROM orders      o
JOIN users       u  ON u.id  = o.user_id
JOIN order_items oi ON oi.order_id = o.id
JOIN products    p  ON p.id  = oi.product_id
WHERE o.id = 42;

-- SELF JOIN: empleado y su manager
SELECT
    e.name AS employee,
    m.name AS manager
FROM   employees e
LEFT JOIN employees m ON m.id = e.manager_id;
```

---

## Funciones de agregación y GROUP BY

```sql
-- Estadísticas por usuario
SELECT
    u.id,
    u.name,
    COUNT(o.id)              AS total_orders,
    SUM(o.total_cents)       AS lifetime_value,
    AVG(o.total_cents)       AS avg_order,
    MAX(o.total_cents)       AS max_order,
    MIN(o.created_at)        AS first_order
FROM users  u
JOIN orders o ON o.user_id = u.id
WHERE o.status != 'cancelled'
GROUP BY u.id, u.name
HAVING COUNT(o.id) >= 3          -- filtrar DESPUÉS de agrupar
ORDER BY lifetime_value DESC;

-- Productos más vendidos
SELECT
    p.name,
    SUM(oi.quantity)         AS units_sold,
    SUM(oi.quantity * oi.unit_price) AS revenue
FROM   products    p
JOIN   order_items oi ON oi.product_id = p.id
JOIN   orders      o  ON o.id = oi.order_id
WHERE  o.status = 'completed'
  AND  o.created_at >= NOW() - INTERVAL '30 days'
GROUP  BY p.id, p.name
ORDER  BY revenue DESC
LIMIT  10;
```

---

## Subconsultas

```sql
-- Subconsulta en WHERE
SELECT name, email
FROM   users
WHERE  id IN (
    SELECT DISTINCT user_id
    FROM   orders
    WHERE  total_cents > 50000
);

-- Subconsulta correlacionada (referencia tabla exterior)
SELECT
    u.name,
    (
        SELECT COUNT(*)
        FROM   orders o
        WHERE  o.user_id = u.id
          AND  o.status  = 'completed'
    ) AS completed_orders
FROM users u;

-- Subconsulta en FROM (tabla derivada)
SELECT
    month,
    SUM(revenue) AS monthly_revenue
FROM (
    SELECT
        DATE_TRUNC('month', created_at) AS month,
        total_cents AS revenue
    FROM orders
    WHERE status = 'completed'
) AS monthly_orders
GROUP BY month
ORDER BY month;
```

---

## Common Table Expressions (CTE)

```sql
-- CTE: consultas más legibles y reutilizables
WITH active_users AS (
    SELECT id, name, email
    FROM   users
    WHERE  deleted_at IS NULL AND role = 'user'
),
user_stats AS (
    SELECT
        u.id,
        u.name,
        COUNT(o.id)        AS order_count,
        COALESCE(SUM(o.total_cents), 0) AS total_spent
    FROM active_users u
    LEFT JOIN orders o ON o.user_id = u.id AND o.status = 'completed'
    GROUP BY u.id, u.name
)
SELECT
    name,
    order_count,
    total_spent,
    RANK() OVER (ORDER BY total_spent DESC) AS spending_rank
FROM user_stats
WHERE order_count > 0
ORDER BY spending_rank;

-- CTE recursiva: árbol de categorías
WITH RECURSIVE category_tree AS (
    -- Caso base: categorías raíz
    SELECT id, name, parent_id, 0 AS depth, name AS path
    FROM categories
    WHERE parent_id IS NULL

    UNION ALL

    -- Caso recursivo: hijos
    SELECT c.id, c.name, c.parent_id, ct.depth + 1, ct.path || ' > ' || c.name
    FROM categories c
    JOIN category_tree ct ON ct.id = c.parent_id
)
SELECT * FROM category_tree ORDER BY path;
```

---

## Window Functions

```sql
-- Ranking dentro de grupos
SELECT
    u.name,
    o.total_cents,
    RANK()       OVER (ORDER BY o.total_cents DESC) AS global_rank,
    ROW_NUMBER() OVER (PARTITION BY o.status ORDER BY o.created_at) AS rank_in_status,
    LAG(o.total_cents)  OVER (PARTITION BY o.user_id ORDER BY o.created_at) AS prev_order,
    LEAD(o.total_cents) OVER (PARTITION BY o.user_id ORDER BY o.created_at) AS next_order,
    SUM(o.total_cents)  OVER (PARTITION BY o.user_id ORDER BY o.created_at
                              ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                             ) AS running_total
FROM orders o
JOIN users  u ON u.id = o.user_id
ORDER BY u.name, o.created_at;
```

---

## Optimización de consultas

```sql
-- EXPLAIN ANALYZE: ver el plan de ejecución
EXPLAIN ANALYZE
SELECT * FROM orders WHERE user_id = 1 AND status = 'pending';

-- Resultado típico (sin índice):
-- Seq Scan on orders  (cost=0.00..150.00 rows=5 width=50)
--   Filter: (user_id = 1 AND status = 'pending')

-- Con índice:
CREATE INDEX idx_orders_user_status ON orders(user_id, status);
-- Index Scan using idx_orders_user_status on orders  (cost=0.15..8.17 rows=5)
```

### Patrones de optimización

```sql
-- ❌ Evita funciones en columnas indexadas (rompe el índice)
SELECT * FROM users WHERE LOWER(email) = 'ana@test.com';

-- ✅ Usa índices funcionales o normaliza los datos al insertar
CREATE INDEX idx_users_email_lower ON users(LOWER(email));
-- o bien: guarda el email en minúsculas

-- ❌ SELECT * (trae columnas innecesarias)
SELECT * FROM users WHERE id = 1;

-- ✅ Proyecta solo lo que necesitas
SELECT id, name, email FROM users WHERE id = 1;

-- ❌ N+1 problem: una query por cada usuario
-- for user in users: SELECT * FROM orders WHERE user_id = user.id

-- ✅ Una sola query con JOIN
SELECT u.id, u.name, o.id AS order_id, o.total_cents
FROM users  u
JOIN orders o ON o.user_id = u.id
WHERE u.id = ANY(ARRAY[1, 2, 3, 4, 5]);

-- ❌ OFFSET grande es lento (escanea todas las filas anteriores)
SELECT * FROM orders ORDER BY created_at LIMIT 10 OFFSET 100000;

-- ✅ Paginación por cursor (keyset pagination)
SELECT * FROM orders
WHERE created_at < '2024-06-15 10:30:00'  -- último valor de la página anterior
ORDER BY created_at DESC
LIMIT 10;
```

---

## Buenas prácticas

| Práctica | Descripción |
|---|---|
| **Usa parámetros, nunca concatenes** | Previene SQL injection |
| **Pagina con cursor** | Más eficiente que OFFSET en tablas grandes |
| **EXPLAIN antes de optimizar** | No asumas, mide el plan real |
| **Índices en FK** | PostgreSQL no los crea automáticamente |
| **Evita columnas nullables en índices** | Pueden no usarse en algunos SGBD |
| **Usa transacciones** | Para mantener consistencia en múltiples operaciones |
