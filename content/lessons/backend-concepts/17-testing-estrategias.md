# Testing en Backend: Estrategias y Buenas Prácticas

El testing no es opcional en el desarrollo profesional backend. Una buena suite de tests permite refactorizar con confianza, detectar regresiones y documentar el comportamiento del sistema.

---

## La pirámide de testing

```
        ╱‾‾‾‾‾‾‾‾╲
       ╱  E2E/UI   ╲     ← Pocos (lentos, costosos, frágiles)
      ╱─────────────╲
     ╱  Integration  ╲   ← Moderados (BD real, servicios reales)
    ╱─────────────────╲
   ╱     Unit Tests    ╲ ← Muchos (rápidos, aislados, baratos)
  ╱───────────────────╲
```

---

## Tests unitarios

Testean una unidad de lógica de forma **aislada**, con dependencias mockeadas.

```typescript
import { describe, it, expect, vi, beforeEach } from 'vitest';

// Sistema bajo test
class PasswordService {
  validate(password: string): { valid: boolean; errors: string[] } {
    const errors: string[] = [];
    if (password.length < 8)         errors.push('Mínimo 8 caracteres');
    if (!/[A-Z]/.test(password))     errors.push('Necesita mayúscula');
    if (!/[0-9]/.test(password))     errors.push('Necesita número');
    if (!/[!@#$%]/.test(password))   errors.push('Necesita símbolo especial');
    return { valid: errors.length === 0, errors };
  }
}

// Tests
describe('PasswordService.validate', () => {
  const service = new PasswordService();

  it('acepta una contraseña válida', () => {
    const result = service.validate('Segura@123');
    expect(result.valid).toBe(true);
    expect(result.errors).toHaveLength(0);
  });

  it('rechaza contraseñas cortas', () => {
    const result = service.validate('Ab@1');
    expect(result.valid).toBe(false);
    expect(result.errors).toContain('Mínimo 8 caracteres');
  });

  it('rechaza contraseñas sin mayúsculas', () => {
    const result = service.validate('segura@123');
    expect(result.errors).toContain('Necesita mayúscula');
  });

  it('puede tener múltiples errores a la vez', () => {
    const result = service.validate('abc');
    expect(result.errors.length).toBeGreaterThan(1);
  });
});
```

### Mocking de dependencias

```typescript
interface UserRepository {
  findByEmail(email: string): Promise<User | null>;
  save(data: Omit<User, 'id'>): Promise<User>;
}

class UserService {
  constructor(
    private repo:   UserRepository,
    private mailer: Mailer,
    private hasher: PasswordHasher
  ) {}

  async register(email: string, password: string, name: string): Promise<User> {
    const existing = await this.repo.findByEmail(email);
    if (existing) throw new Error('Email ya registrado');

    const hash = await this.hasher.hash(password);
    const user = await this.repo.save({ email, name, passwordHash: hash });
    await this.mailer.sendWelcome(email, name);
    return user;
  }
}

describe('UserService.register', () => {
  let repo:    ReturnType<typeof vi.mocked<UserRepository>>;
  let mailer:  ReturnType<typeof vi.mocked<Mailer>>;
  let hasher:  ReturnType<typeof vi.mocked<PasswordHasher>>;
  let service: UserService;

  beforeEach(() => {
    // Crear mocks frescos en cada test
    repo   = { findByEmail: vi.fn(), save: vi.fn() };
    mailer = { sendWelcome: vi.fn().mockResolvedValue(undefined) };
    hasher = { hash: vi.fn().mockResolvedValue('hashed_password') };
    service = new UserService(repo, mailer, hasher);
  });

  it('registra un nuevo usuario', async () => {
    repo.findByEmail.mockResolvedValue(null);  // no existe
    repo.save.mockResolvedValue({ id: 1, email: 'ana@test.com', name: 'Ana' });

    const user = await service.register('ana@test.com', 'Pass@123', 'Ana');

    expect(user.id).toBe(1);
    expect(repo.save).toHaveBeenCalledWith({
      email: 'ana@test.com',
      name:  'Ana',
      passwordHash: 'hashed_password',
    });
    expect(mailer.sendWelcome).toHaveBeenCalledWith('ana@test.com', 'Ana');
  });

  it('lanza error si el email ya existe', async () => {
    repo.findByEmail.mockResolvedValue({ id: 1, email: 'ana@test.com', name: 'Ana' });

    await expect(service.register('ana@test.com', 'Pass@123', 'Ana'))
      .rejects.toThrow('Email ya registrado');

    expect(repo.save).not.toHaveBeenCalled();
    expect(mailer.sendWelcome).not.toHaveBeenCalled();
  });
});
```

---

## Tests de integración

Testean que varios componentes funcionan juntos — normalmente con una base de datos real de test.

```typescript
import { describe, it, expect, beforeAll, afterAll, beforeEach } from 'vitest';
import { Pool } from 'pg';

describe('UserRepository (integration)', () => {
  let pool: Pool;
  let repo: PgUserRepository;

  beforeAll(async () => {
    // Conecta a la BD de test (configurada en .env.test)
    pool = new Pool({ connectionString: process.env.DATABASE_URL_TEST });
    repo = new PgUserRepository(pool);

    await pool.query(`
      CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        name  VARCHAR(100) NOT NULL
      )
    `);
  });

  beforeEach(async () => {
    // Limpia entre tests
    await pool.query('TRUNCATE users RESTART IDENTITY CASCADE');
  });

  afterAll(async () => {
    await pool.end();
  });

  it('guarda y recupera un usuario', async () => {
    const saved = await repo.save({ email: 'ana@test.com', name: 'Ana' });

    expect(saved.id).toBeTypeOf('number');
    expect(saved.email).toBe('ana@test.com');

    const found = await repo.findById(saved.id);
    expect(found?.name).toBe('Ana');
  });

  it('no permite emails duplicados', async () => {
    await repo.save({ email: 'ana@test.com', name: 'Ana' });

    await expect(repo.save({ email: 'ana@test.com', name: 'Otra Ana' }))
      .rejects.toThrow();  // violación de UNIQUE constraint
  });
});
```

---

## Tests de API (supertest)

```typescript
import request from 'supertest';
import { app } from '../src/app';

describe('POST /api/users', () => {
  it('crea un usuario con datos válidos', async () => {
    const res = await request(app)
      .post('/api/users')
      .send({ email: 'ana@test.com', name: 'Ana', password: 'Segura@123' })
      .expect(201)
      .expect('Content-Type', /json/);

    expect(res.body.data).toMatchObject({
      email: 'ana@test.com',
      name:  'Ana',
    });
    expect(res.body.data.id).toBeDefined();
    expect(res.body.data.password).toBeUndefined();  // nunca exponer contraseña
  });

  it('retorna 422 con datos inválidos', async () => {
    const res = await request(app)
      .post('/api/users')
      .send({ email: 'no-es-email', name: '' })
      .expect(422);

    expect(res.body.error.code).toBe('VALIDATION_ERROR');
    expect(res.body.error.details).toHaveProperty('email');
  });

  it('retorna 409 si el email ya existe', async () => {
    // Primer registro
    await request(app)
      .post('/api/users')
      .send({ email: 'ana@test.com', name: 'Ana', password: 'Segura@123' });

    // Segundo con el mismo email
    const res = await request(app)
      .post('/api/users')
      .send({ email: 'ana@test.com', name: 'Otra', password: 'Segura@123' })
      .expect(409);

    expect(res.body.error.code).toBe('CONFLICT');
  });
});
```

---

## TDD (Test-Driven Development)

Ciclo **Red → Green → Refactor**:

```typescript
// 1. RED: escribe el test que falla
it('calcula descuento por volumen', () => {
  const pricer = new VolumeDiscountPricer();
  expect(pricer.calculate(100, 10)).toBe(850);  // 10 unidades: 15% descuento
  expect(pricer.calculate(100, 5)).toBe(475);   // 5 unidades: 5% descuento
  expect(pricer.calculate(100, 1)).toBe(100);   // sin descuento
});

// 2. GREEN: implementa el mínimo código para que pase
class VolumeDiscountPricer {
  calculate(unitPrice: number, quantity: number): number {
    const discount = quantity >= 10 ? 0.15 : quantity >= 5 ? 0.05 : 0;
    return unitPrice * quantity * (1 - discount);
  }
}

// 3. REFACTOR: mejora el diseño sin romper los tests
class VolumeDiscountPricer {
  private readonly tiers = [
    { minQty: 10, discount: 0.15 },
    { minQty: 5,  discount: 0.05 },
    { minQty: 0,  discount: 0    },
  ];

  calculate(unitPrice: number, quantity: number): number {
    const tier    = this.tiers.find(t => quantity >= t.minQty)!;
    return unitPrice * quantity * (1 - tier.discount);
  }
}
```

---

## Buenas prácticas de testing

| Práctica | Descripción |
|---|---|
| **AAA (Arrange-Act-Assert)** | Estructura clara de cada test |
| **Un assertion por test** | Cada test verifica una sola cosa |
| **Tests deterministas** | Sin aleatoriedad ni fechas hardcoded (`vi.useFakeTimers`) |
| **Limpia el estado** | `beforeEach` limpia la BD o los mocks |
| **Nombra los tests como especificaciones** | `it('rechaza emails duplicados', ...)` |
| **Testea casos borde** | null, vacío, número máximo, caracteres especiales |
| **No testees implementación** | Testea comportamiento externo, no detalles internos |
| **Coverage como guía, no meta** | 80% de coverage con buenos tests > 100% con tests vacíos |
