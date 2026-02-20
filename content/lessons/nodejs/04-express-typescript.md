# Express.js con TypeScript: primera API REST

## Instalación

```bash
pnpm add express
pnpm add -D @types/express @types/node tsx typescript
```

Estructura de proyecto que usaremos:

```
src/
├── app.ts          # configuración de Express (sin listen)
├── server.ts       # punto de entrada (listen aquí)
├── routes/
│   ├── index.ts    # combina todos los routers
│   └── users.ts
├── controllers/
│   └── users.controller.ts
├── middleware/
│   └── errorHandler.ts
└── types/
    └── express.d.ts
```

---

## Configurar Express con TypeScript

```typescript
// src/app.ts
import express, { Application } from 'express';
import { usersRouter } from './routes/users.js';

export function createApp(): Application {
  const app = express();

  // Parsear body JSON
  app.use(express.json());

  // Parsear body URL-encoded (formularios HTML)
  app.use(express.urlencoded({ extended: true }));

  // Rutas
  app.use('/api/v1/users', usersRouter);

  return app;
}
```

```typescript
// src/server.ts
import { createApp } from './app.js';

const PORT = process.env.PORT ?? 3000;
const app  = createApp();

app.listen(PORT, () => {
  console.log(`🚀 Servidor corriendo en http://localhost:${PORT}`);
});
```

---

## Tipando Request y Response

Express exporta tipos genéricos para tipar los parámetros de rutas, query string, body y respuesta:

```typescript
import { Request, Response, NextFunction } from 'express';

// Request<Params, ResBody, ReqBody, Query>
type CreateUserReq = Request<
  {},                           // params de URL (ej: /users/:id)
  UserResponse,                 // tipo de la respuesta
  CreateUserDto,                // tipo del body
  {}                            // query string
>;

// Ejemplo completo tipado
async function createUser(req: CreateUserReq, res: Response<UserResponse>): Promise<void> {
  const { name, email } = req.body; // TypeScript conoce los campos
  // ...
}
```

### Extender `Request` con propiedades personalizadas

```typescript
// src/types/express.d.ts
import { User } from '../models/User.js';

declare global {
  namespace Express {
    interface Request {
      user?: User;         // añadido por el middleware de autenticación
      requestId?: string;  // añadido por el middleware de logging
    }
  }
}
```

---

## Router y controladores

```typescript
// src/controllers/users.controller.ts
import { Request, Response, NextFunction } from 'express';

// Simulamos una "base de datos" en memoria
interface User {
  id:    number;
  name:  string;
  email: string;
}

let users: User[] = [
  { id: 1, name: 'Ana García',  email: 'ana@test.com' },
  { id: 2, name: 'Bob Martínez', email: 'bob@test.com' },
];
let nextId = 3;

// GET /users
export async function getUsers(_req: Request, res: Response): Promise<void> {
  res.json({ data: users, total: users.length });
}

// GET /users/:id
export async function getUserById(
  req: Request<{ id: string }>,
  res: Response
): Promise<void> {
  const id   = parseInt(req.params.id, 10);
  const user = users.find(u => u.id === id);

  if (!user) {
    res.status(404).json({ error: 'Usuario no encontrado' });
    return;
  }

  res.json({ data: user });
}

// POST /users
export async function createUser(
  req: Request<{}, {}, { name: string; email: string }>,
  res: Response
): Promise<void> {
  const { name, email } = req.body;

  if (!name || !email) {
    res.status(400).json({ error: 'name y email son requeridos' });
    return;
  }

  const user: User = { id: nextId++, name, email };
  users.push(user);
  res.status(201).json({ data: user });
}

// PUT /users/:id
export async function updateUser(
  req: Request<{ id: string }, {}, Partial<{ name: string; email: string }>>,
  res: Response
): Promise<void> {
  const id    = parseInt(req.params.id, 10);
  const index = users.findIndex(u => u.id === id);

  if (index === -1) {
    res.status(404).json({ error: 'Usuario no encontrado' });
    return;
  }

  users[index] = { ...users[index], ...req.body };
  res.json({ data: users[index] });
}

// DELETE /users/:id
export async function deleteUser(
  req: Request<{ id: string }>,
  res: Response
): Promise<void> {
  const id    = parseInt(req.params.id, 10);
  const index = users.findIndex(u => u.id === id);

  if (index === -1) {
    res.status(404).json({ error: 'Usuario no encontrado' });
    return;
  }

  users.splice(index, 1);
  res.status(204).send();
}
```

```typescript
// src/routes/users.ts
import { Router } from 'express';
import {
  getUsers, getUserById, createUser, updateUser, deleteUser
} from '../controllers/users.controller.js';

export const usersRouter = Router();

usersRouter.get('/',    getUsers);
usersRouter.get('/:id', getUserById);
usersRouter.post('/',   createUser);
usersRouter.put('/:id', updateUser);
usersRouter.delete('/:id', deleteUser);
```

---

## Manejo de errores

Express tiene un middleware especial de 4 parámetros para errores:

```typescript
// src/middleware/errorHandler.ts
import { Request, Response, NextFunction, ErrorRequestHandler } from 'express';

// Error personalizado con código HTTP
export class AppError extends Error {
  constructor(
    public readonly statusCode: number,
    message: string,
    public readonly code?: string
  ) {
    super(message);
    this.name = 'AppError';
  }
}

// Middleware de errores — DEBE tener exactamente 4 parámetros
export const errorHandler: ErrorRequestHandler = (err, _req, res, _next) => {
  if (err instanceof AppError) {
    res.status(err.statusCode).json({
      error:   err.message,
      code:    err.code,
      success: false,
    });
    return;
  }

  // Error inesperado
  console.error('Error no controlado:', err);
  res.status(500).json({
    error:   'Error interno del servidor',
    success: false,
  });
};
```

```typescript
// src/app.ts — añadir DESPUÉS de las rutas
import { errorHandler } from './middleware/errorHandler.js';

// ...rutas...

// DEBE ser el último middleware
app.use(errorHandler);
```

### Capturar errores async automáticamente

Sin wrapper, los errores en controladores async no llegan al error handler:

```typescript
// src/utils/asyncHandler.ts
import { Request, Response, NextFunction, RequestHandler } from 'express';

// Wrapper que pasa errores al next() automáticamente
export function asyncHandler(
  fn: (req: Request, res: Response, next: NextFunction) => Promise<void>
): RequestHandler {
  return (req, res, next) => {
    fn(req, res, next).catch(next); // los errores van al error handler global
  };
}

// Uso en el router:
usersRouter.get('/:id', asyncHandler(getUserById));

// Con Express 5 (actualmente en beta) esto ya no es necesario:
// Express 5 maneja Promises automáticamente
```

---

## Query string tipado

```typescript
// GET /users?page=1&limit=10&search=ana
async function getUsers(
  req: Request<{}, {}, {}, { page?: string; limit?: string; search?: string }>,
  res: Response
): Promise<void> {
  const page   = parseInt(req.query.page  ?? '1',  10);
  const limit  = parseInt(req.query.limit ?? '10', 10);
  const search = req.query.search?.toLowerCase() ?? '';

  let result = users;

  if (search) {
    result = result.filter(u =>
      u.name.toLowerCase().includes(search) ||
      u.email.toLowerCase().includes(search)
    );
  }

  const start      = (page - 1) * limit;
  const paginated  = result.slice(start, start + limit);

  res.json({
    data:  paginated,
    total: result.length,
    page,
    pages: Math.ceil(result.length / limit),
  });
}
```

---

## Scripts en package.json

```json
{
  "scripts": {
    "dev":   "tsx watch src/server.ts",
    "build": "tsc",
    "start": "node dist/server.js"
  }
}
```

`tsx watch` reinicia el servidor automáticamente cuando guardas un archivo — no necesitas `nodemon`.

---

## Probando la API con curl

```bash
# Listar usuarios
curl http://localhost:3000/api/v1/users

# Crear usuario
curl -X POST http://localhost:3000/api/v1/users \
  -H "Content-Type: application/json" \
  -d '{"name":"Carlos","email":"carlos@test.com"}'

# Obtener usuario por ID
curl http://localhost:3000/api/v1/users/1

# Actualizar
curl -X PUT http://localhost:3000/api/v1/users/1 \
  -H "Content-Type: application/json" \
  -d '{"name":"Ana López"}'

# Eliminar
curl -X DELETE http://localhost:3000/api/v1/users/1
```

---

## Resumen

| Concepto | Implementación |
|---|---|
| Separar config de arranque | `app.ts` vs `server.ts` |
| Tipos de Request | `Request<Params, ResBody, ReqBody, Query>` |
| Extender Request | `declare global { namespace Express { interface Request } }` |
| Async errors | `asyncHandler()` wrapper (hasta Express 5) |
| Error handler | Middleware con 4 parámetros al final |
| Hot reload | `tsx watch` |

En la siguiente lección profundizamos en middlewares: CORS, logging con Pino HTTP, rate limiting y autenticación básica.
