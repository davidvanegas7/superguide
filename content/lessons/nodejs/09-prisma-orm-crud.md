# Base de datos con Prisma ORM: setup y CRUD

## ¿Qué es Prisma?

Prisma es un ORM de siguiente generación para Node.js y TypeScript. A diferencia de ORMs clásicos (Sequelize, TypeORM), Prisma genera un cliente completamente tipado a partir de tu schema — no necesitas declarar tipos manualmente.

Componentes:
- **Prisma Schema** (`prisma/schema.prisma`) — define modelos y relaciones
- **Prisma Migrate** — genera y aplica migraciones SQL
- **Prisma Client** — cliente generado con tipos perfectos para tu schema
- **Prisma Studio** — GUI visual para explorar la BD

---

## Instalación y setup

```bash
pnpm add @prisma/client
pnpm add -D prisma

# Inicializar (crea prisma/schema.prisma y .env)
npx prisma init --datasource-provider postgresql
```

---

## Schema de Prisma

```prisma
// prisma/schema.prisma

generator client {
  provider = "prisma-client-js"
}

datasource db {
  provider = "postgresql"
  url      = env("DATABASE_URL")
}

model User {
  id        Int      @id @default(autoincrement())
  email     String   @unique
  name      String
  password  String
  role      Role     @default(VIEWER)
  active    Boolean  @default(true)
  createdAt DateTime @default(now())
  updatedAt DateTime @updatedAt

  // Relaciones (se definen en la lección siguiente)
  posts    Post[]
  profile  Profile?

  @@index([email])
  @@map("users") // nombre de la tabla en la BD
}

model Profile {
  id     Int     @id @default(autoincrement())
  bio    String?
  avatar String?
  userId Int     @unique
  user   User    @relation(fields: [userId], references: [id], onDelete: Cascade)

  @@map("profiles")
}

model Post {
  id          Int       @id @default(autoincrement())
  title       String
  content     String    @db.Text
  published   Boolean   @default(false)
  publishedAt DateTime?
  viewCount   Int       @default(0)
  authorId    Int
  author      User      @relation(fields: [authorId], references: [id])
  tags        Tag[]     @relation("PostTags")
  createdAt   DateTime  @default(now())
  updatedAt   DateTime  @updatedAt

  @@index([authorId])
  @@index([published, publishedAt(sort: Desc)])
  @@map("posts")
}

model Tag {
  id    Int    @id @default(autoincrement())
  name  String @unique
  slug  String @unique
  posts Post[] @relation("PostTags")

  @@map("tags")
}

enum Role {
  ADMIN
  EDITOR
  VIEWER
}
```

---

## Migraciones

```bash
# Crear y aplicar migración en desarrollo
npx prisma migrate dev --name "create-users-posts-tags"
# Esto: genera el SQL, lo aplica, y regenera el Prisma Client

# Aplicar migraciones en producción (sin generar nuevas)
npx prisma migrate deploy

# Ver estado de migraciones
npx prisma migrate status

# Regenerar cliente sin migrar (después de cambiar el schema manualmente)
npx prisma generate

# Reset completo de la BD (solo en desarrollo)
npx prisma migrate reset

# Explorar la BD visualmente
npx prisma studio
```

---

## Inicializar el cliente

```typescript
// src/lib/prisma.ts
import { PrismaClient } from '@prisma/client';
import { config } from '../config/index.js';

// Singleton: evitar múltiples instancias en desarrollo con hot-reload
const globalForPrisma = globalThis as unknown as { prisma: PrismaClient };

export const prisma =
  globalForPrisma.prisma ??
  new PrismaClient({
    log: config.app.isDev
      ? ['query', 'info', 'warn', 'error']
      : ['warn', 'error'],
  });

if (config.app.isDev) {
  globalForPrisma.prisma = prisma;
}

// Desconectar correctamente al cerrar la app
process.on('beforeExit', async () => {
  await prisma.$disconnect();
});
```

---

## CRUD completo

### CREATE

```typescript
import { prisma } from '../lib/prisma.js';
import { Prisma } from '@prisma/client';
import bcrypt from 'bcryptjs';

// Crear un registro
async function createUser(data: {
  name: string;
  email: string;
  password: string;
}) {
  const hashedPassword = await bcrypt.hash(data.password, 12);

  const user = await prisma.user.create({
    data: {
      name:     data.name,
      email:    data.email,
      password: hashedPassword,
      // Crear relación anidada en la misma operación
      profile: {
        create: { bio: '' },
      },
    },
    // Seleccionar qué campos retornar
    select: {
      id:        true,
      name:      true,
      email:     true,
      role:      true,
      createdAt: true,
      // password: false — omitir campos sensibles
    },
  });

  return user;
}

// Crear múltiples registros eficientemente
async function createManyTags(tags: { name: string; slug: string }[]) {
  return prisma.tag.createMany({
    data: tags,
    skipDuplicates: true, // ignorar duplicados en vez de fallar
  });
}
```

### READ

```typescript
// Encontrar por ID (null si no existe)
async function findUserById(id: number) {
  return prisma.user.findUnique({
    where: { id },
  });
}

// Lanzar error si no existe (útil en controladores)
async function getUserOrThrow(id: number) {
  return prisma.user.findUniqueOrThrow({
    where: { id },
    select: { id: true, name: true, email: true, role: true },
  });
}

// Encontrar primero que coincida con criterios
async function findByEmail(email: string) {
  return prisma.user.findFirst({
    where: { email, active: true },
  });
}

// Listar con paginación
async function getUsers(page = 1, pageSize = 20) {
  const skip = (page - 1) * pageSize;

  const [users, total] = await Promise.all([
    prisma.user.findMany({
      where:   { active: true },
      select:  { id: true, name: true, email: true, role: true, createdAt: true },
      orderBy: { createdAt: 'desc' },
      skip,
      take:    pageSize,
    }),
    prisma.user.count({ where: { active: true } }),
  ]);

  return {
    data:  users,
    total,
    page,
    pages: Math.ceil(total / pageSize),
  };
}
```

### UPDATE

```typescript
// Actualizar por clave única
async function updateUser(id: number, data: Prisma.UserUpdateInput) {
  return prisma.user.update({
    where: { id },
    data,
    select: { id: true, name: true, email: true, role: true, updatedAt: true },
  });
}

// updateMany — actualizar varios registros a la vez
async function deactivateInactiveUsers(beforeDate: Date) {
  const result = await prisma.user.updateMany({
    where: {
      active:    true,
      updatedAt: { lt: beforeDate },
    },
    data: { active: false },
  });

  return result.count; // número de registros actualizados
}

// upsert — crear si no existe, actualizar si existe
async function upsertUser(email: string, name: string) {
  return prisma.user.upsert({
    where:  { email },
    update: { name },
    create: { email, name, password: '' },
  });
}
```

### DELETE

```typescript
// Eliminar por clave única (lanza error si no existe)
async function deleteUser(id: number) {
  return prisma.user.delete({ where: { id } });
}

// Soft delete (marcar como inactivo en vez de eliminar)
async function softDeleteUser(id: number) {
  return prisma.user.update({
    where: { id },
    data:  { active: false },
  });
}

// Eliminar múltiples
async function deleteDraftPosts(authorId: number) {
  const result = await prisma.post.deleteMany({
    where: { authorId, published: false },
  });
  return result.count;
}
```

---

## Transacciones

Cuando necesitas que múltiples operaciones sean atómicas (todo o nada):

```typescript
// Transacción interactiva — más flexible, permite lógica condicional
async function transferCredits(fromId: number, toId: number, amount: number) {
  return prisma.$transaction(async (tx) => {
    // 1. Verificar saldo suficiente
    const sender = await tx.user.findUniqueOrThrow({ where: { id: fromId } });
    // (asumiendo que User tiene un campo credits)

    // 2. Descontar del remitente
    await tx.user.update({
      where: { id: fromId },
      data:  { credits: { decrement: amount } },
    });

    // 3. Acreditar al destinatario
    await tx.user.update({
      where: { id: toId },
      data:  { credits: { increment: amount } },
    });

    // Si cualquier paso falla → rollback automático
  });
}

// Transacción batch — más rápida, sin lógica condicional
async function createPostWithTags(postData: { title: string; authorId: number }, tagIds: number[]) {
  return prisma.$transaction([
    prisma.post.create({
      data: {
        ...postData,
        content: '',
        tags: { connect: tagIds.map(id => ({ id })) },
      },
    }),
    prisma.user.update({
      where: { id: postData.authorId },
      data:  { postCount: { increment: 1 } },
    }),
  ]);
}
```

---

## Manejo de errores de Prisma

```typescript
import { Prisma } from '@prisma/client';

async function safeCreateUser(data: { email: string; name: string; password: string }) {
  try {
    return await createUser(data);
  } catch (err) {
    if (err instanceof Prisma.PrismaClientKnownRequestError) {
      // P2002: violación de constraint único
      if (err.code === 'P2002') {
        const field = (err.meta?.target as string[])?.join(', ');
        throw new AppError(409, `El ${field} ya está en uso`);
      }
      // P2025: registro no encontrado (update/delete)
      if (err.code === 'P2025') {
        throw new AppError(404, 'Registro no encontrado');
      }
    }
    throw err; // re-lanzar errores desconocidos
  }
}
```

---

## Resumen

| Operación | Método Prisma |
|---|---|
| Crear uno | `prisma.model.create({ data })` |
| Crear muchos | `prisma.model.createMany({ data })` |
| Buscar por ID único | `prisma.model.findUnique({ where })` |
| Buscar primero | `prisma.model.findFirst({ where })` |
| Listar | `prisma.model.findMany({ where, skip, take, orderBy })` |
| Contar | `prisma.model.count({ where })` |
| Actualizar | `prisma.model.update({ where, data })` |
| Crear o actualizar | `prisma.model.upsert({ where, create, update })` |
| Eliminar | `prisma.model.delete({ where })` |
| Transacción | `prisma.$transaction([...])` o `prisma.$transaction(async tx => ...)` |

En la siguiente lección profundizamos en **relaciones y consultas avanzadas**: `include`, `select` anidado, filtros complejos, agregaciones y queries con `$queryRaw`.
