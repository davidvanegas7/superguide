# ORM: Conceptos y Patrones

Un **ORM** (Object-Relational Mapper) es una herramienta que mapea objetos del lenguaje de programación a tablas de una base de datos relacional, permitiendo trabajar con entidades en lugar de escribir SQL manualmente.

---

## Active Record vs Data Mapper

### Active Record
El objeto **conoce** su propio almacenamiento. El modelo contiene tanto la lógica de negocio como las operaciones de persistencia.

```typescript
// Patrón Active Record (ej: Laravel Eloquent, Rails ActiveRecord)
class User {
  id!:        number;
  email!:     string;
  name!:      string;
  createdAt!: Date;

  // Métodos de acceso a BD integrados en el modelo
  static async find(id: number): Promise<User | null> {
    const row = await db.query('SELECT * FROM users WHERE id = $1', [id]);
    return row ? Object.assign(new User(), row) : null;
  }

  static async findBy(email: string): Promise<User | null> {
    const row = await db.query('SELECT * FROM users WHERE email = $1', [email]);
    return row ? Object.assign(new User(), row) : null;
  }

  async save(): Promise<this> {
    if (this.id) {
      await db.query('UPDATE users SET email=$1, name=$2 WHERE id=$3',
        [this.email, this.name, this.id]);
    } else {
      const row = await db.query(
        'INSERT INTO users(email, name) VALUES($1,$2) RETURNING *',
        [this.email, this.name]
      );
      Object.assign(this, row);
    }
    return this;
  }

  async delete(): Promise<void> {
    await db.query('DELETE FROM users WHERE id = $1', [this.id]);
  }
}

// Uso
const user = await User.find(1);
user!.name = 'Ana García';
await user!.save();
```

**Ventajas**: Sencillo, natural para CRUDs simples.
**Desventajas**: Mezcla persistencia con lógica de dominio; dificulta los tests (necesitas la BD).

---

### Data Mapper
Separa completamente el **dominio** de la **persistencia**. Los objetos de dominio son ignorantes de la BD.

```typescript
// Entidad pura (no sabe nada de la BD)
class User {
  constructor(
    public readonly id:    number | null,
    public email:          string,
    public name:           string,
    public readonly createdAt: Date = new Date()
  ) {}

  rename(newName: string): void {
    if (!newName.trim()) throw new Error('El nombre no puede estar vacío');
    this.name = newName.trim();
  }

  changeEmail(newEmail: string): void {
    if (!newEmail.includes('@')) throw new Error('Email inválido');
    this.email = newEmail.toLowerCase();
  }
}

// Mapper: traduce entre dominio y BD
class UserMapper {
  toDomain(row: Record<string, unknown>): User {
    return new User(
      row.id as number,
      row.email as string,
      row.name as string,
      new Date(row.created_at as string)
    );
  }

  toDatabase(user: User): Record<string, unknown> {
    return {
      id:         user.id,
      email:      user.email,
      name:       user.name,
      created_at: user.createdAt,
    };
  }
}

// Repository: usa el mapper
class UserRepository {
  private mapper = new UserMapper();

  async findById(id: number): Promise<User | null> {
    const rows = await db.query('SELECT * FROM users WHERE id = $1', [id]);
    return rows[0] ? this.mapper.toDomain(rows[0]) : null;
  }

  async save(user: User): Promise<User> {
    const data = this.mapper.toDatabase(user);
    if (user.id) {
      await db.query('UPDATE users SET email=$1, name=$2 WHERE id=$3',
        [data.email, data.name, data.id]);
      return user;
    }
    const row = await db.query(
      'INSERT INTO users(email, name) VALUES($1,$2) RETURNING *',
      [data.email, data.name]
    );
    return this.mapper.toDomain(row[0]);
  }
}

// El dominio no depende de la BD → unit tests sin BD
const user = new User(null, 'ana@test.com', 'Ana');
user.rename('Ana García');  // se puede testear sin BD
```

---

## El problema N+1

El problema más común de rendimiento con ORMs. Ocurre cuando se hace 1 query para obtener una lista y luego N queries adicionales (una por cada elemento) para obtener datos relacionados.

```typescript
// ❌ N+1 queries
const users = await userRepo.findAll();          // 1 query
for (const user of users) {
  const orders = await orderRepo.findByUser(user.id);  // N queries
  console.log(`${user.name}: ${orders.length} pedidos`);
}
// Con 1000 usuarios → 1001 queries!

// ✅ 1 query con JOIN
const usersWithOrders = await db.query(`
  SELECT u.id, u.name, COUNT(o.id) AS order_count
  FROM users u
  LEFT JOIN orders o ON o.user_id = u.id
  GROUP BY u.id, u.name
`);

// ✅ O 2 queries: IN para los IDs (mejor para objetos complejos)
const users      = await userRepo.findAll();
const userIds    = users.map(u => u.id);
const allOrders  = await orderRepo.findByUserIds(userIds);  // 1 query con WHERE user_id IN (...)

const ordersByUser = new Map<number, Order[]>();
for (const order of allOrders) {
  const list = ordersByUser.get(order.userId) ?? [];
  list.push(order);
  ordersByUser.set(order.userId, list);
}

for (const user of users) {
  const orders = ordersByUser.get(user.id) ?? [];
  console.log(`${user.name}: ${orders.length} pedidos`);
}
```

---

## Lazy Loading vs Eager Loading

```typescript
// Lazy Loading: carga relaciones cuando se acceden
// Implícito en algunos ORMs (Hibernate, TypeORM con lazy: true)
// ❌ Puede causar N+1 si no se gestiona bien
const user = await User.findOne(1);
const orders = await user.orders;  // query aquí, solo si se accede

// Eager Loading: carga todo de una vez
// ✅ Más eficiente cuando sabes que necesitarás las relaciones
const user = await User.findOne({
  where: { id: 1 },
  include: ['orders', 'profile']   // JOIN en la query
});

// Implementación manual de eager loading
class UserRepository {
  async findWithOrders(userId: number): Promise<UserWithOrders | null> {
    const rows = await db.query(`
      SELECT
        u.id, u.name, u.email,
        o.id     AS order_id,
        o.total_cents,
        o.status,
        o.created_at AS order_date
      FROM users  u
      LEFT JOIN orders o ON o.user_id = u.id
      WHERE u.id = $1
    `, [userId]);

    if (!rows.length) return null;

    const first = rows[0];
    return {
      id:     first.id,
      name:   first.name,
      email:  first.email,
      orders: rows
        .filter(r => r.order_id !== null)
        .map(r => ({
          id:         r.order_id,
          totalCents: r.total_cents,
          status:     r.status,
          createdAt:  r.order_date,
        })),
    };
  }
}
```

---

## ORMs populares en el ecosistema TypeScript/Node.js

```typescript
// Prisma (Data Mapper moderno)
const user = await prisma.user.findUnique({
  where: { id: 1 },
  include: {
    orders: {
      where:   { status: 'completed' },
      orderBy: { createdAt: 'desc' },
      take:    5,
    },
  },
});

// TypeORM (Active Record o Data Mapper)
@Entity()
class User {
  @PrimaryGeneratedColumn() id!: number;
  @Column()                 email!: string;
  @OneToMany(() => Order, o => o.user) orders!: Order[];
}

const user = await userRepo.findOne({
  where: { id: 1 },
  relations: { orders: true },
});

// Drizzle ORM (type-safe SQL builder)
const result = await db
  .select({ name: users.name, orderCount: count() })
  .from(users)
  .leftJoin(orders, eq(orders.userId, users.id))
  .groupBy(users.id);
```

---

## Cuándo NO usar un ORM

| Caso | Solución recomendada |
|---|---|
| Queries complejas de análisis/reporting | SQL puro o query builder |
| Bulk inserts/updates masivos | SQL puro con COPY o batch INSERT |
| Full-text search complejo | SQL puro + extensiones (pg_trgm, ts_vector) |
| Migraciones complejas | Herramientas dedicadas (Flyway, Liquibase) |
| Performance crítico sub-ms | SQL compilado o stored procedures |

---

## Buenas prácticas

1. **Siempre proyecta** solo las columnas que necesitas (`SELECT id, name` no `SELECT *`)
2. **Identifica el N+1** en el ORM con query logging en desarrollo
3. **Usa eager loading** cuando sepas de antemano qué relaciones vas a usar
4. **Evita lazy loading en loops** — es la causa #1 del problema N+1
5. **Las migraciones van en control de versiones** siempre
6. **No pongas lógica de negocio en los hooks del ORM** — usa servicios de dominio
