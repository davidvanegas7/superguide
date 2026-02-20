# TypeScript para Backend: tipos, interfaces y generics

## ¿Por qué TypeScript en el backend?

En el frontend TypeScript es opcional — en el backend es casi indispensable. Las APIs manejan contratos (qué datos entran y salen), y TypeScript hace esos contratos explícitos y verificables en tiempo de compilación.

Beneficios concretos:
- El compilador atrapa errores antes de que lleguen a producción
- El IDE autocompleta nombres de campos de la BD gracias a los tipos generados por Prisma
- Refactorizar es seguro: si cambias un tipo, el compilador te dice dónde rompiste algo
- Los nuevos miembros del equipo entienden la estructura sin leer documentación extra

---

## Tipos básicos en contexto de API

```typescript
// Primitivos
const id:       number  = 1;
const nombre:   string  = 'Ana García';
const activo:   boolean = true;
const creacion: Date    = new Date();

// null vs undefined en backend
// null  = valor intencionalmente ausente (el usuario no tiene foto de perfil)
// undefined = el campo ni siquiera fue enviado en la petición

interface UpdateUserDto {
  name?:   string;    // opcional: puede no venir en el body
  avatar?: string | null;  // opcional Y anulable
}
```

### Tipos literales

Los tipos literales son valores específicos como tipos, muy útiles para estados y enums:

```typescript
type HttpMethod  = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
type UserRole     = 'admin' | 'editor' | 'viewer';
type OrderStatus = 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled';

// El compilador rechaza cualquier valor fuera del conjunto
function setRole(role: UserRole): void {
  console.log(`Rol asignado: ${role}`);
}

setRole('admin');    // ✅
setRole('superuser'); // ❌ Error de compilación
```

---

## Interfaces vs Types

Ambas sirven para describir la forma de un objeto, pero tienen diferencias importantes:

```typescript
// Interface — preferida para objetos y contratos de clase
interface User {
  id:        number;
  email:     string;
  role:      UserRole;
  createdAt: Date;
}

// Type alias — más flexible, permite uniones e intersecciones
type ApiResponse<T> = {
  data:    T;
  message: string;
  success: boolean;
};

type PaginatedResponse<T> = ApiResponse<T[]> & {
  total:    number;
  page:     number;
  pageSize: number;
};
```

### Extensión e intersección

```typescript
// Interfaces: extienden con extends
interface BaseEntity {
  id:        number;
  createdAt: Date;
  updatedAt: Date;
}

interface Product extends BaseEntity {
  name:        string;
  price:       number;
  stock:       number;
  categoryId:  number;
}

// Types: intersectan con &
type CreateProductDto = Omit<Product, 'id' | 'createdAt' | 'updatedAt'>;
// Resultado: { name: string; price: number; stock: number; categoryId: number }

type UpdateProductDto = Partial<CreateProductDto>;
// Resultado: todos los campos opcionales
```

---

## Utility Types más usados en backend

TypeScript incluye tipos de utilidad que evitan repetir código:

```typescript
interface User {
  id:       number;
  name:     string;
  email:    string;
  password: string;  // nunca debe salir en respuestas de la API
  role:     UserRole;
  active:   boolean;
}

// Omit — elimina campos
type PublicUser = Omit<User, 'password'>;
// { id, name, email, role, active }

// Pick — elige solo ciertos campos
type UserCredentials = Pick<User, 'email' | 'password'>;
// { email: string; password: string }

// Partial — hace todos los campos opcionales (para updates)
type UpdateUserDto = Partial<Omit<User, 'id'>>;

// Required — hace todos los campos obligatorios
type RequiredUser = Required<User>;

// Readonly — impide mutación
type ImmutableUser = Readonly<User>;

// Record — crea un tipo de objeto con keys y values tipados
type RolePermissions = Record<UserRole, string[]>;
const perms: RolePermissions = {
  admin:  ['create', 'read', 'update', 'delete'],
  editor: ['create', 'read', 'update'],
  viewer: ['read'],
};

// ReturnType — extrae el tipo de retorno de una función
async function findUser(id: number): Promise<User | null> {
  return null; // simulado
}
type FindUserReturn = Awaited<ReturnType<typeof findUser>>;
// User | null
```

---

## Generics

Los generics son el mecanismo más potente de TypeScript para reutilizar lógica con tipos variables.

### Funciones genéricas

```typescript
// Función de respuesta estandarizada para toda la API
function successResponse<T>(data: T, message = 'OK'): ApiResponse<T> {
  return { data, success: true, message };
}

function errorResponse(message: string, code = 400): ApiResponse<null> {
  return { data: null, success: false, message };
}

// Uso:
const userResp = successResponse({ id: 1, name: 'Ana' });
// TypeScript infiere: ApiResponse<{ id: number; name: string }>

const listResp = successResponse([1, 2, 3]);
// TypeScript infiere: ApiResponse<number[]>
```

### Clase Repository genérica

```typescript
// Contrato para cualquier repositorio de la aplicación
interface Repository<T, ID = number> {
  findById(id: ID): Promise<T | null>;
  findAll(filters?: Partial<T>): Promise<T[]>;
  create(data: Omit<T, 'id' | 'createdAt' | 'updatedAt'>): Promise<T>;
  update(id: ID, data: Partial<T>): Promise<T>;
  delete(id: ID): Promise<void>;
}

// Implementación genérica en memoria (útil para tests)
class InMemoryRepository<T extends { id: number }> implements Repository<T> {
  protected items: T[] = [];
  private nextId = 1;

  async findById(id: number): Promise<T | null> {
    return this.items.find(item => item.id === id) ?? null;
  }

  async findAll(): Promise<T[]> {
    return [...this.items];
  }

  async create(data: Omit<T, 'id'>): Promise<T> {
    const item = { id: this.nextId++, ...data } as T;
    this.items.push(item);
    return item;
  }

  async update(id: number, data: Partial<T>): Promise<T> {
    const index = this.items.findIndex(i => i.id === id);
    if (index === -1) throw new Error(`Elemento con id ${id} no encontrado`);
    this.items[index] = { ...this.items[index], ...data };
    return this.items[index];
  }

  async delete(id: number): Promise<void> {
    this.items = this.items.filter(i => i.id !== id);
  }
}
```

### Constraints (restricciones en generics)

```typescript
// T extends object garantiza que solo se acepten objetos
function logEntity<T extends { id: number; constructor: { name: string } }>(entity: T): void {
  console.log(`[${entity.constructor.name}] id=${entity.id}`);
}

// K extends keyof T garantiza que K sea una clave válida de T
function getField<T, K extends keyof T>(obj: T, key: K): T[K] {
  return obj[key];
}

const user: User = { id: 1, name: 'Ana', email: 'ana@test.com', password: 'hash', role: 'admin', active: true };
const email = getField(user, 'email');   // tipo inferido: string
const id    = getField(user, 'id');      // tipo inferido: number
// getField(user, 'noExiste');           // ❌ Error de compilación
```

---

## Discriminated Unions (tipos discriminados)

Son una de las herramientas más poderosas para modelar resultados de operaciones:

```typescript
// Patrón Result para manejo de errores tipado
type Result<T, E = Error> =
  | { success: true;  data:  T }
  | { success: false; error: E };

// En lugar de lanzar excepciones, retorna un Result
async function findUserById(id: number): Promise<Result<User, 'USER_NOT_FOUND' | 'DB_ERROR'>> {
  try {
    const user = await db.findUser(id); // simulado
    if (!user) return { success: false, error: 'USER_NOT_FOUND' };
    return { success: true, data: user };
  } catch {
    return { success: false, error: 'DB_ERROR' };
  }
}

// El consumidor DEBE manejar ambos casos
async function getUser(id: number) {
  const result = await findUserById(id);

  if (!result.success) {
    // TypeScript sabe que result.error existe aquí
    console.error('Error:', result.error);
    return null;
  }

  // TypeScript sabe que result.data existe aquí
  return result.data;
}
```

---

## Type Guards

Permiten reducir (narrow) un tipo dentro de un bloque condicional:

```typescript
// Type guard con typeof
function processValue(value: string | number): string {
  if (typeof value === 'number') {
    return value.toFixed(2); // TypeScript sabe que value es number aquí
  }
  return value.toUpperCase(); // TypeScript sabe que value es string aquí
}

// Type guard con instanceof
function handleError(error: unknown): string {
  if (error instanceof Error) {
    return error.message;  // TypeScript sabe que error tiene .message
  }
  if (typeof error === 'string') {
    return error;
  }
  return 'Error desconocido';
}

// Type guard personalizado (user-defined)
interface AdminUser  { role: 'admin';  permissions: string[] }
interface RegularUser { role: 'viewer'; watchlist: number[]  }

type AppUser = AdminUser | RegularUser;

function isAdmin(user: AppUser): user is AdminUser {
  return user.role === 'admin';
}

function processUser(user: AppUser): void {
  if (isAdmin(user)) {
    // TypeScript sabe que user es AdminUser
    console.log('Permisos:', user.permissions);
  } else {
    // TypeScript sabe que user es RegularUser
    console.log('Watchlist:', user.watchlist);
  }
}
```

---

## Decoradores (experimentales)

Los decoradores son una forma de añadir metadatos o comportamiento a clases y métodos. Se usan en frameworks como NestJS.

> Requiere `"experimentalDecorators": true` en `tsconfig.json`.

```typescript
// Decorator de clase: añade un prefijo de log a todos los métodos
function Loggable(prefix: string) {
  return function <T extends { new (...args: unknown[]): object }>(constructor: T) {
    return class extends constructor {
      constructor(...args: unknown[]) {
        super(...args);
        console.log(`[${prefix}] instancia creada`);
      }
    };
  };
}

// Decorator de método: mide el tiempo de ejecución
function Benchmark() {
  return function (_target: object, key: string, descriptor: PropertyDescriptor) {
    const original = descriptor.value as (...args: unknown[]) => unknown;
    descriptor.value = async function (...args: unknown[]) {
      const start = performance.now();
      const result = await original.apply(this, args);
      const ms = (performance.now() - start).toFixed(2);
      console.log(`[${key}] tardó ${ms}ms`);
      return result;
    };
    return descriptor;
  };
}

@Loggable('UserService')
class UserService {
  @Benchmark()
  async findAll(): Promise<string[]> {
    await new Promise(r => setTimeout(r, 50)); // simula latencia
    return ['Ana', 'Bob', 'Carlos'];
  }
}

const svc = new UserService();
// [UserService] instancia creada
svc.findAll().then(users => console.log(users));
// [findAll] tardó 52.31ms
// ['Ana', 'Bob', 'Carlos']
```

---

## Patrones comunes en proyectos backend

### DTOs con validación inferida

```typescript
import { z } from 'zod'; // veremos Zod en profundidad en la lección 7

const createUserSchema = z.object({
  name:     z.string().min(2).max(100),
  email:    z.string().email(),
  password: z.string().min(8),
  role:     z.enum(['admin', 'editor', 'viewer']).default('viewer'),
});

// Inferir el tipo directamente del schema — fuente única de verdad
type CreateUserDto = z.infer<typeof createUserSchema>;
// { name: string; email: string; password: string; role: "admin" | "editor" | "viewer" }
```

### Tipos de entorno tipados

```typescript
// src/types/env.d.ts — tipado de process.env
declare global {
  namespace NodeJS {
    interface ProcessEnv {
      NODE_ENV:     'development' | 'test' | 'production';
      PORT:         string;
      DATABASE_URL: string;
      JWT_SECRET:   string;
      JWT_EXPIRES:  string;
    }
  }
}

export {};

// Ahora process.env.NODE_ENV tiene autocompletado y verificación de tipos
const env = process.env.NODE_ENV; // tipo: 'development' | 'test' | 'production'
```

---

## Resumen

| Concepto | Cuándo usarlo |
|---|---|
| `interface` | Contratos de objetos y clases |
| `type` + uniones | Valores alternativos, intersecciones |
| Utility types (`Omit`, `Pick`, `Partial`) | Transformar tipos existentes para DTOs |
| Generics | Lógica reutilizable con tipos variables |
| Discriminated unions | Resultados de operaciones (éxito/error) |
| Type guards | Reducir tipos dentro de condicionales |
| Decoradores | Metaprogramación (NestJS, logging, etc.) |

En la siguiente lección veremos el sistema de módulos de Node.js: CommonJS, ESM, y cómo manejar el filesystem de forma asíncrona con `fs/promises` y `path`.
