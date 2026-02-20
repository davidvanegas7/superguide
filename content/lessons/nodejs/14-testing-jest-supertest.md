# Testing: Jest + Supertest para APIs REST

## ¿Por qué testear el backend?

Una API sin tests es una API que rompes sin enterarte. Los tests en Node.js con TypeScript tienen tres niveles:

- **Unit tests**: función aislada, mocks de dependencias
- **Integration tests**: múltiples módulos reales interactuando
- **E2E (End-to-End)**: petición HTTP real → respuesta final

Para APIs REST, la combinación más efectiva es **Jest + Supertest**: Jest como runner/assertion library y Supertest para lanzar peticiones HTTP reales contra la app Express sin levantar un servidor real.

---

## Configuración de Jest con TypeScript

```bash
npm install -D jest @types/jest ts-jest supertest @types/supertest
```

```typescript
// jest.config.ts
import type { Config } from 'jest';

const config: Config = {
  preset: 'ts-jest',
  testEnvironment: 'node',
  roots: ['<rootDir>/tests'],
  testMatch: ['**/*.test.ts'],
  collectCoverageFrom: [
    'src/**/*.ts',
    '!src/**/*.d.ts',
    '!src/server.ts',
  ],
  coverageThreshold: {
    global: {
      branches: 70,
      functions: 80,
      lines: 80,
      statements: 80,
    },
  },
  // Limpiar mocks entre tests
  clearMocks: true,
  restoreMocks: true,
};

export default config;
```

```json
// package.json (scripts)
{
  "scripts": {
    "test": "jest",
    "test:watch": "jest --watch",
    "test:coverage": "jest --coverage",
    "test:ci": "jest --ci --coverage --runInBand"
  }
}
```

---

## Separar la app del servidor

Para testear con Supertest, la instancia Express debe estar separada de `server.listen()`:

```typescript
// src/app.ts — Solo la configuración de Express
import express from 'express';
import { errorHandler } from './middlewares/errorHandler';
import routes from './routes';

export function createApp() {
  const app = express();
  app.use(express.json());
  app.use('/api', routes);
  app.use(errorHandler);
  return app;
}
```

```typescript
// src/server.ts — Solo arranca el servidor
import { createApp } from './app';

const app = createApp();
app.listen(3000, () => console.log('Server running on port 3000'));
```

---

## Unit tests: testear servicios aislados

```typescript
// tests/unit/UserService.test.ts
import { UserService } from '../../src/services/UserService';
import { prisma } from '../../src/lib/prisma';
import { hashPassword } from '../../src/utils/password';

// Mockear Prisma completamente
jest.mock('../../src/lib/prisma', () => ({
  prisma: {
    user: {
      findUnique: jest.fn(),
      create: jest.fn(),
    },
  },
}));

// Mockear utilidades
jest.mock('../../src/utils/password');

const mockPrisma = prisma as jest.Mocked<typeof prisma>;

describe('UserService', () => {
  const service = new UserService();

  describe('findById', () => {
    it('retorna el usuario si existe', async () => {
      const mockUser = { id: '1', name: 'Ana', email: 'ana@test.com' };
      mockPrisma.user.findUnique.mockResolvedValueOnce(mockUser as any);

      const result = await service.findById('1');

      expect(result).toEqual(mockUser);
      expect(mockPrisma.user.findUnique).toHaveBeenCalledWith({
        where: { id: '1' },
      });
    });

    it('lanza NotFoundError si no existe', async () => {
      mockPrisma.user.findUnique.mockResolvedValueOnce(null);

      await expect(service.findById('999')).rejects.toMatchObject({
        statusCode: 404,
      });
    });
  });
});
```

---

## Integration tests con Supertest

```typescript
// tests/integration/auth.test.ts
import request from 'supertest';
import { createApp } from '../../src/app';

const app = createApp();

describe('POST /api/auth/register', () => {
  it('registra un usuario nuevo y retorna tokens', async () => {
    const response = await request(app)
      .post('/api/auth/register')
      .send({
        name: 'Carlos Dev',
        email: `user${Date.now()}@test.com`,
        password: 'SecurePass123!',
      });

    expect(response.status).toBe(201);
    expect(response.body).toMatchObject({
      accessToken: expect.any(String),
      refreshToken: expect.any(String),
    });
  });

  it('retorna 422 con email inválido', async () => {
    const response = await request(app)
      .post('/api/auth/register')
      .send({ name: 'Test', email: 'no-es-email', password: 'pass123' });

    expect(response.status).toBe(422);
    expect(response.body.fields).toHaveProperty('email');
  });

  it('retorna 409 si el email ya existe', async () => {
    const email = `dup${Date.now()}@test.com`;
    const body = { name: 'Test', email, password: 'SecurePass123!' };

    await request(app).post('/api/auth/register').send(body);
    const response = await request(app).post('/api/auth/register').send(body);

    expect(response.status).toBe(409);
  });
});
```

---

## Mocking con jest.spyOn

`jest.spyOn` permite espiar (y opcionalmente reemplazar) un método sin mockear todo el módulo:

```typescript
// tests/integration/articles.test.ts
import request from 'supertest';
import { createApp } from '../../src/app';
import { articleService } from '../../src/services/ArticleService';

const app = createApp();

describe('GET /api/articles', () => {
  it('retorna lista de artículos', async () => {
    // Espiar el método y controlar su respuesta
    const spy = jest.spyOn(articleService, 'findAll').mockResolvedValueOnce([
      { id: '1', title: 'Test Article', content: 'Content', authorId: '1' },
    ] as any);

    const res = await request(app).get('/api/articles');

    expect(res.status).toBe(200);
    expect(res.body).toHaveLength(1);
    expect(spy).toHaveBeenCalledTimes(1);
  });
});
```

---

## Setup y teardown

```typescript
// tests/setup.ts — ejecutado antes de todos los tests
import { prisma } from '../src/lib/prisma';

// Antes de cada test: limpiar la BD de test
beforeEach(async () => {
  await prisma.tokenBlacklist.deleteMany();
  await prisma.article.deleteMany();
  await prisma.user.deleteMany();
});

// Al final de todos los tests: cerrar la conexión
afterAll(async () => {
  await prisma.$disconnect();
});
```

```typescript
// jest.config.ts — registrar el setup
const config: Config = {
  // ...
  globalSetup: './tests/globalSetup.ts',   // se ejecuta una vez antes de todo
  setupFilesAfterFramework: ['./tests/setup.ts'], // antes de cada archivo de test
};
```

```typescript
// tests/globalSetup.ts
export default async function globalSetup() {
  process.env.DATABASE_URL = 'postgresql://user:pass@localhost:5432/test_db';
  process.env.JWT_SECRET = 'test-secret-key';
  process.env.JWT_REFRESH_SECRET = 'test-refresh-secret';
}
```

---

## Factories de datos con faker

En lugar de hardcodear datos en cada test, usa factories:

```typescript
// tests/factories/userFactory.ts
import { faker } from '@faker-js/faker';

export interface UserInput {
  name: string;
  email: string;
  password: string;
}

export function buildUser(overrides: Partial<UserInput> = {}): UserInput {
  return {
    name: faker.person.fullName(),
    email: faker.internet.email(),
    password: 'Password123!',
    ...overrides,
  };
}
```

```typescript
// En tests:
import { buildUser } from '../factories/userFactory';

it('registra correctamente', async () => {
  const userData = buildUser();
  const res = await request(app).post('/api/auth/register').send(userData);
  expect(res.status).toBe(201);
});
```

---

## Autenticación en integration tests

```typescript
// tests/helpers/auth.ts
import request from 'supertest';
import { Express } from 'express';
import { buildUser } from '../factories/userFactory';

export async function registerAndLogin(app: Express) {
  const userData = buildUser();
  const registerRes = await request(app)
    .post('/api/auth/register')
    .send(userData);
  return registerRes.body.accessToken as string;
}

// Uso en tests protegidos:
describe('GET /api/admin/users', () => {
  it('requiere autenticación', async () => {
    const token = await registerAndLogin(app);
    const res = await request(app)
      .get('/api/admin/users')
      .set('Authorization', `Bearer ${token}`);
    expect(res.status).toBe(200);
  });
});
```

---

## Cobertura y buenas prácticas

```bash
# Ejecutar con reporte de cobertura
npx jest --coverage

# Falla si la cobertura cae por debajo de los umbrales en jest.config.ts
npx jest --ci --coverage
```

| Práctica | Descripción |
|---|---|
| Un `describe` por entidad | Organiza los tests por recurso o servicio |
| AAA (Arrange-Act-Assert) | Estructura clara dentro de cada `it` |
| Tests independientes | Cada test limpia su estado (beforeEach) |
| Nombres descriptivos | `it('retorna 404 cuando el usuario no existe')` |
| No testear implementación | Testea el comportamiento observable (inputs/outputs) |
| Mocks solo cuando es necesario | Prefiere tests de integración reales en BD de test |

---

## Resumen

- Configurar `ts-jest` con `jest.config.ts` para TypeScript nativo
- Separar `createApp()` de `server.listen()` para poder usar Supertest
- Unit tests con `jest.mock()` para aislar servicios de la BD
- Integration tests con `supertest(app)` para peticiones HTTP reales
- `jest.spyOn()` para espiar métodos sin mockear todo el módulo
- Factories con faker para datos de prueba reutilizables
- `beforeEach` limpia la BD de test para mantener independencia
