# Autorización: roles, permisos y guards

## Autenticación vs Autorización

- **Autenticación**: verifica *quién eres* (identidad)
- **Autorización**: verifica *qué puedes hacer* (permisos)

Después del middleware `authenticate` que inyecta `req.user`, necesitamos un sistema que decida si ese usuario tiene permiso para ejecutar la acción solicitada.

---

## Modelos de control de acceso

### RBAC (Role-Based Access Control)

Los permisos se asignan a roles y los usuarios tienen roles. Es simple y cubre la mayoría de casos:

```
Usuario → tiene Rol → Rol tiene Permisos
admin   → ADMIN   → [ create, read, update, delete ]
editor  → EDITOR  → [ create, read, update ]
viewer  → VIEWER  → [ read ]
```

### ABAC (Attribute-Based Access Control)

Los permisos se evalúan en función de atributos del usuario, el recurso y el contexto (hora, IP, etc.). Más flexible pero más complejo.

Para APIs típicas, **RBAC es suficiente** y es lo que implementaremos.

---

## Middleware `authorize`

```typescript
// src/middlewares/authorize.ts
import { Request, Response, NextFunction } from 'express';
import { AppError } from '../errors/AppError';

type Role = 'ADMIN' | 'EDITOR' | 'VIEWER' | 'USER';

/**
 * Middleware de fábrica: recibe los roles permitidos y retorna un middleware
 * que verifica si req.user.role está entre ellos.
 */
export function authorize(...allowedRoles: Role[]) {
  return (req: Request, _res: Response, next: NextFunction): void => {
    if (!req.user) {
      return next(new AppError('No autenticado', 401));
    }

    if (!allowedRoles.includes(req.user.role as Role)) {
      return next(
        new AppError(
          `Acceso denegado. Se requiere uno de estos roles: ${allowedRoles.join(', ')}`,
          403
        )
      );
    }

    next();
  };
}
```

Uso en rutas:

```typescript
import { authenticate } from '../middlewares/authenticate';
import { authorize } from '../middlewares/authorize';

// Solo admins pueden eliminar usuarios
router.delete('/users/:id', authenticate, authorize('ADMIN'), deleteUser);

// Admins y editores pueden crear artículos
router.post('/articles', authenticate, authorize('ADMIN', 'EDITOR'), createArticle);

// Cualquier usuario autenticado puede leer
router.get('/articles', authenticate, getArticles);
```

---

## Ownership checks: verificar que el recurso pertenece al usuario

No basta con verificar el rol. Un usuario `EDITOR` no debe poder editar los artículos de otro `EDITOR`. Este patrón se llama **ownership check**:

```typescript
// src/middlewares/checkOwnership.ts
import { Request, Response, NextFunction } from 'express';
import { prisma } from '../lib/prisma';
import { AppError } from '../errors/AppError';

/**
 * Verifica que el artículo pertenece al usuario autenticado
 * (o que el usuario es ADMIN, que puede hacer cualquier cosa)
 */
export async function checkArticleOwnership(
  req: Request,
  _res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const articleId = req.params.id;
    const userId = req.user!.sub;
    const userRole = req.user!.role;

    // Los admins pueden acceder a cualquier recurso
    if (userRole === 'ADMIN') {
      return next();
    }

    const article = await prisma.article.findUnique({
      where: { id: articleId },
      select: { authorId: true },
    });

    if (!article) {
      return next(new AppError('Artículo no encontrado', 404));
    }

    if (article.authorId !== userId) {
      return next(new AppError('No tienes permiso para modificar este recurso', 403));
    }

    next();
  } catch (err) {
    next(err);
  }
}
```

```typescript
// Ruta: solo el autor o un admin pueden editar
router.put(
  '/articles/:id',
  authenticate,
  authorize('ADMIN', 'EDITOR'),
  checkArticleOwnership,
  updateArticle
);
```

---

## Permission matrix: documentar qué puede hacer cada rol

Define una matriz de permisos como fuente de verdad:

```typescript
// src/config/permissions.ts

type Action = 'create' | 'read' | 'update' | 'delete';
type Resource = 'article' | 'user' | 'comment';
type Role = 'ADMIN' | 'EDITOR' | 'VIEWER';

type PermissionMatrix = Record<Role, Record<Resource, Action[]>>;

export const PERMISSIONS: PermissionMatrix = {
  ADMIN: {
    article: ['create', 'read', 'update', 'delete'],
    user:    ['create', 'read', 'update', 'delete'],
    comment: ['create', 'read', 'update', 'delete'],
  },
  EDITOR: {
    article: ['create', 'read', 'update'],
    user:    ['read'],
    comment: ['create', 'read', 'update', 'delete'],
  },
  VIEWER: {
    article: ['read'],
    user:    ['read'],
    comment: ['read'],
  },
};

export function can(role: Role, resource: Resource, action: Action): boolean {
  return PERMISSIONS[role]?.[resource]?.includes(action) ?? false;
}
```

```typescript
// Uso en un servicio
import { can } from '../config/permissions';

export class ArticleService {
  async delete(articleId: string, requesterRole: string): Promise<void> {
    if (!can(requesterRole as Role, 'article', 'delete')) {
      throw new AppError('No tienes permiso para eliminar artículos', 403);
    }
    await prisma.article.delete({ where: { id: articleId } });
  }
}
```

---

## Guards granulares con caché

Para sistemas con permisos dinámicos (guardados en BD), un guard con caché evita consultar la BD en cada request:

```typescript
// src/guards/PermissionGuard.ts
import NodeCache from 'node-cache';
import { prisma } from '../lib/prisma';

const cache = new NodeCache({ stdTTL: 300 }); // 5 min

export class PermissionGuard {
  async getUserPermissions(userId: string): Promise<string[]> {
    const cacheKey = `perms:${userId}`;
    const cached = cache.get<string[]>(cacheKey);
    if (cached) return cached;

    const user = await prisma.user.findUnique({
      where: { id: userId },
      include: {
        roles: {
          include: { permissions: true },
        },
      },
    });

    const permissions = user?.roles
      .flatMap(r => r.permissions)
      .map(p => p.name) ?? [];

    cache.set(cacheKey, permissions);
    return permissions;
  }

  async can(userId: string, permission: string): Promise<boolean> {
    const perms = await this.getUserPermissions(userId);
    return perms.includes(permission);
  }

  invalidateCache(userId: string): void {
    cache.del(`perms:${userId}`);
  }
}

export const permissionGuard = new PermissionGuard();
```

---

## Estructura de rutas con múltiples guards

```typescript
// src/routes/admin.routes.ts
import { Router } from 'express';
import { authenticate } from '../middlewares/authenticate';
import { authorize } from '../middlewares/authorize';

const router = Router();

// Todas las rutas bajo /admin requieren autenticación y rol ADMIN
router.use(authenticate);
router.use(authorize('ADMIN'));

router.get('/users', listUsers);
router.delete('/users/:id', deleteUser);
router.get('/stats', getDashboardStats);

export default router;
```

```typescript
// src/app.ts
import adminRoutes from './routes/admin.routes';
import articleRoutes from './routes/article.routes';

app.use('/admin', adminRoutes);         // Todos protegidos por ADMIN
app.use('/articles', articleRoutes);   // Permisos granulares por ruta
```

---

## Pruebas de los guards

```typescript
// tests/authorize.test.ts
import { authorize } from '../src/middlewares/authorize';
import { Request, Response, NextFunction } from 'express';

function mockReq(role: string): Partial<Request> {
  return { user: { sub: '1', email: 'test@test.com', role } };
}

describe('authorize middleware', () => {
  it('permite acceso con rol correcto', () => {
    const middleware = authorize('ADMIN');
    const next = jest.fn();
    middleware(mockReq('ADMIN') as Request, {} as Response, next);
    expect(next).toHaveBeenCalledWith(); // sin error
  });

  it('rechaza con rol insuficiente', () => {
    const middleware = authorize('ADMIN');
    const next = jest.fn();
    middleware(mockReq('VIEWER') as Request, {} as Response, next);
    expect(next).toHaveBeenCalledWith(expect.objectContaining({ statusCode: 403 }));
  });
});
```

---

## Resumen

| Concepto | Implementación |
|---|---|
| RBAC básico | Middleware `authorize(...roles)` |
| Ownership | Middleware `checkOwnership` con consulta a BD |
| Permission matrix | Objeto estático `PERMISSIONS` con función `can()` |
| Permisos dinámicos | `PermissionGuard` con caché en memoria |
| Admin bypass | `if (role === 'ADMIN') return next()` en ownership checks |
