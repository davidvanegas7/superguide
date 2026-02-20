# Relaciones y consultas avanzadas con Prisma

## Tipos de relaciones

Prisma soporta los tres tipos de relaciones de bases de datos relacionales:

```prisma
// schema.prisma

// ─── 1:1 — Un usuario tiene un perfil ────────────────────────────────────────
model User {
  id      Int      @id @default(autoincrement())
  profile Profile?
}
model Profile {
  id     Int  @id @default(autoincrement())
  userId Int  @unique
  user   User @relation(fields: [userId], references: [id], onDelete: Cascade)
}

// ─── 1:N — Un autor tiene muchos posts ───────────────────────────────────────
model User {
  id    Int    @id @default(autoincrement())
  posts Post[]
}
model Post {
  id       Int  @id @default(autoincrement())
  authorId Int
  author   User @relation(fields: [authorId], references: [id])
}

// ─── N:M — Posts tienen muchos Tags y viceversa ───────────────────────────────
model Post {
  id   Int   @id @default(autoincrement())
  tags Tag[] @relation("PostTags")
}
model Tag {
  id    Int    @id @default(autoincrement())
  posts Post[] @relation("PostTags")
  // Prisma crea la tabla pivot automáticamente: _PostTags
}

// ─── N:M explícita (con campos extra en la tabla pivot) ──────────────────────
model Post {
  id        Int            @id @default(autoincrement())
  userLikes UserPostLike[]
}
model User {
  id        Int            @id @default(autoincrement())
  postLikes UserPostLike[]
}
model UserPostLike {
  userId    Int
  postId    Int
  likedAt   DateTime @default(now())
  user      User     @relation(fields: [userId], references: [id])
  post      Post     @relation(fields: [postId], references: [id])
  @@id([userId, postId])  // clave primaria compuesta
}
```

---

## include — Eager loading de relaciones

```typescript
// Incluir relaciones anidadas
const userWithPosts = await prisma.user.findUnique({
  where: { id: 1 },
  include: {
    profile: true,
    posts: {
      where:   { published: true },   // filtrar posts incluidos
      orderBy: { publishedAt: 'desc' },
      take:    5,                      // solo los últimos 5
      include: {
        tags: true,                   // incluir tags de cada post
      },
    },
  },
});
// userWithPosts.posts[0].tags → Tag[] — totalmente tipado

// include vs select — no se pueden mezclar en el mismo nivel
// include: true → todos los campos del modelo + relaciones especificadas
// select: {} → solo los campos seleccionados explícitamente
```

---

## select — Proyección de campos

```typescript
// Seleccionar campos específicos (más eficiente que incluir todo)
const publicUser = await prisma.user.findUnique({
  where: { id: 1 },
  select: {
    id:        true,
    name:      true,
    email:     true,
    // password: false — omitido implícitamente
    profile: {
      select: { bio: true, avatar: true },
    },
    _count: {
      select: { posts: true }, // contar posts del usuario
    },
  },
});

// publicUser.password → Error de compilación (no está en el tipo)
// publicUser._count.posts → number
```

### Tipo inferido de select

```typescript
import { Prisma } from '@prisma/client';

// Obtener el tipo de un select específico
const userSelect = {
  id:    true,
  name:  true,
  email: true,
  posts: { select: { id: true, title: true } },
} satisfies Prisma.UserSelect;

type UserWithPosts = Prisma.UserGetPayload<{ select: typeof userSelect }>;
// { id: number; name: string; email: string; posts: { id: number; title: string }[] }
```

---

## Filtros avanzados

```typescript
// Operadores de comparación
await prisma.post.findMany({
  where: {
    viewCount: { gt: 100 },              // mayor que
    createdAt: { gte: new Date('2024-01-01'), lte: new Date('2024-12-31') },
    title:     { contains: 'typescript', mode: 'insensitive' }, // case-insensitive
    content:   { startsWith: '##' },
  },
});

// Operadores lógicos
await prisma.user.findMany({
  where: {
    AND: [
      { active: true },
      { role: { in: ['ADMIN', 'EDITOR'] } },
    ],
    OR: [
      { email: { endsWith: '@empresa.com' } },
      { createdAt: { gte: new Date('2024-01-01') } },
    ],
    NOT: { email: { contains: 'test' } },
  },
});

// Filtrar por relaciones
await prisma.post.findMany({
  where: {
    // Posts cuyo autor está activo Y tiene el rol EDITOR
    author: {
      active: true,
      role:   'EDITOR',
    },
    // Posts que tienen AL MENOS UNA tag llamada 'typescript'
    tags: {
      some: { name: 'typescript' },
    },
    // Posts que tienen TODAS sus tags publicadas
    // tags: { every: { active: true } },
    // Posts que NO tienen ninguna tag
    // tags: { none: {} },
  },
});
```

---

## orderBy avanzado

```typescript
// Ordenamiento múltiple
await prisma.post.findMany({
  orderBy: [
    { publishedAt: 'desc' },
    { title:       'asc'  },
  ],
});

// Ordenar por campo de relación
await prisma.post.findMany({
  orderBy: {
    author: { name: 'asc' },
  },
});

// Ordenar por conteo de relación
await prisma.user.findMany({
  orderBy: {
    posts: { _count: 'desc' }, // usuarios con más posts primero
  },
});
```

---

## Paginación cursor-based

Más eficiente que offset para grandes volúmenes:

```typescript
// Primera página
const firstPage = await prisma.post.findMany({
  take:    20,
  orderBy: { id: 'asc' },
  select:  { id: true, title: true, createdAt: true },
});

// Siguiente página — usar el último ID como cursor
const lastId = firstPage.at(-1)?.id;

const nextPage = await prisma.post.findMany({
  take:   20,
  skip:   1,          // saltar el cursor
  cursor: { id: lastId },
  orderBy: { id: 'asc' },
  select: { id: true, title: true, createdAt: true },
});
```

---

## Agregaciones y agrupamiento

```typescript
// Agregaciones numéricas
const stats = await prisma.post.aggregate({
  where: { published: true },
  _count: { id: true },
  _sum:   { viewCount: true },
  _avg:   { viewCount: true },
  _min:   { viewCount: true },
  _max:   { viewCount: true },
});
// stats._sum.viewCount → number | null

// groupBy — contar por categoría
const postsByRole = await prisma.user.groupBy({
  by:    ['role'],
  _count: { id: true },
  where:  { active: true },
  orderBy: { _count: { id: 'desc' } },
});
// [{ role: 'VIEWER', _count: { id: 145 } }, { role: 'EDITOR', _count: { id: 12 } }]

// Contar relaciones sin cargarlas
const usersWithPostCount = await prisma.user.findMany({
  select: {
    id:     true,
    name:   true,
    _count: { select: { posts: true } },
  },
});
```

---

## Raw queries

Para consultas SQL que Prisma no puede expresar:

```typescript
import { Prisma } from '@prisma/client';

// $queryRaw — retorna filas tipadas
const result = await prisma.$queryRaw<
  { id: number; name: string; postCount: bigint }[]
>`
  SELECT u.id, u.name, COUNT(p.id)::bigint AS "postCount"
  FROM users u
  LEFT JOIN posts p ON p.author_id = u.id
  WHERE u.active = true
  GROUP BY u.id
  ORDER BY "postCount" DESC
  LIMIT ${Prisma.sql`${10}`}
`;

// Convertir bigint a number si es necesario
const formatted = result.map(r => ({
  ...r,
  postCount: Number(r.postCount),
}));

// $executeRaw — para INSERT/UPDATE/DELETE sin retorno de filas
const updated = await prisma.$executeRaw`
  UPDATE posts SET view_count = view_count + 1
  WHERE id = ${postId}
`;
// updated = número de filas afectadas

// SIEMPRE usar Prisma.sql o interpolación de template para evitar SQL injection
// NUNCA: prisma.$queryRawUnsafe(`SELECT * FROM users WHERE id = ${userId}`)
```

---

## Patrón Repository con Prisma

Abstraer Prisma detrás de un repositorio facilita el testing y el cambio de ORM:

```typescript
// src/repositories/post.repository.ts
import { Prisma } from '@prisma/client';
import { prisma } from '../lib/prisma.js';

export class PostRepository {
  async findPublished(page: number, pageSize: number) {
    const skip = (page - 1) * pageSize;
    const where: Prisma.PostWhereInput = { published: true };

    const [posts, total] = await Promise.all([
      prisma.post.findMany({
        where,
        include: { author: { select: { id: true, name: true } }, tags: true },
        orderBy: { publishedAt: 'desc' },
        skip,
        take:    pageSize,
      }),
      prisma.post.count({ where }),
    ]);

    return { posts, total, pages: Math.ceil(total / pageSize) };
  }

  async findBySlug(slug: string) {
    return prisma.post.findFirst({
      where:   { slug, published: true },
      include: { author: { select: { id: true, name: true } }, tags: true },
    });
  }

  async create(data: Prisma.PostCreateInput) {
    return prisma.post.create({ data });
  }

  async publish(id: number) {
    return prisma.post.update({
      where: { id },
      data:  { published: true, publishedAt: new Date() },
    });
  }
}
```

---

## Resumen

| Operación | API |
|---|---|
| Cargar relaciones | `include: { modelo: true }` |
| Proyectar campos | `select: { campo: true }` |
| Tipo de select | `Prisma.UserGetPayload<{ select: ... }>` |
| Filtrar por relación | `where: { relacion: { some/every/none/is } }` |
| Ordenar por conteo | `orderBy: { relacion: { _count: 'desc' } }` |
| Paginación cursor | `cursor: { id }` + `skip: 1` |
| Agregaciones | `aggregate`, `groupBy`, `_count` |
| SQL directo | `$queryRaw\`...\`` |

En la siguiente lección implementamos **autenticación completa**: registro, login, JWT access token, refresh token y hashing con bcrypt.
