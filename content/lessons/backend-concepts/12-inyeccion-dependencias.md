# Inyección de Dependencias e IoC

La **Inyección de Dependencias (DI)** es un patrón donde las dependencias de un objeto son proporcionadas desde el exterior en lugar de creadas internamente. La **Inversión de Control (IoC)** es el principio más amplio: el control del flujo se invierte (el framework llama tu código, no al revés).

---

## El problema sin DI

```typescript
// ❌ Alta cohesión: UserService crea sus propias dependencias
class UserService {
  private db    = new PostgresDatabase();   // acoplado a Postgres
  private mailer = new SendGridMailer();    // acoplado a SendGrid
  private logger = new ConsoleLogger();     // acoplado a console

  async register(email: string, name: string) {
    const user = await this.db.query('INSERT ...');
    await this.mailer.send(email, 'Bienvenido');
    this.logger.log('Usuario creado');
    return user;
  }
}

// Problemas:
// 1. No puedo testear sin BD real, sin SendGrid real
// 2. Si cambio de Postgres a MySQL, tengo que modificar UserService
// 3. Si quiero mockear el mailer en tests → imposible
```

---

## Inyección por constructor (la más recomendada)

```typescript
// Abstracciones (interfaces/contratos)
interface Database {
  query(sql: string, params?: unknown[]): Promise<unknown[]>;
}

interface Mailer {
  send(to: string, subject: string, body: string): Promise<void>;
}

interface Logger {
  info(msg: string): void;
  error(msg: string, err?: Error): void;
}

// ✅ UserService depende de abstracciones, no de implementaciones
class UserService {
  constructor(
    private db:     Database,
    private mailer: Mailer,
    private logger: Logger
  ) {}

  async register(email: string, name: string): Promise<{ id: number; email: string }> {
    const existing = await this.db.query('SELECT id FROM users WHERE email = $1', [email]);
    if (existing.length) throw new Error('Email ya registrado');

    const [user] = await this.db.query(
      'INSERT INTO users(email, name) VALUES($1,$2) RETURNING id',
      [email, name]
    ) as [{ id: number }];

    await this.mailer.send(email, 'Bienvenido', `Hola ${name}, bienvenido!`);
    this.logger.info(`Usuario registrado: ${email}`);

    return { id: user.id, email };
  }
}

// Implementaciones concretas
class PostgresDatabase implements Database {
  async query(sql: string, params?: unknown[]): Promise<unknown[]> {
    console.log(`[PG] ${sql}`, params);
    return [];  // producción: usa el driver pg
  }
}

class SendGridMailer implements Mailer {
  async send(to: string, subject: string, body: string): Promise<void> {
    console.log(`[SendGrid] → ${to}: ${subject}`);
  }
}

class WinstonLogger implements Logger {
  info(msg: string)              { console.log(`[INFO] ${msg}`); }
  error(msg: string, err?: Error){ console.error(`[ERROR] ${msg}`, err); }
}

// Producción: wiring manual
const service = new UserService(
  new PostgresDatabase(),
  new SendGridMailer(),
  new WinstonLogger()
);

// Tests: mocks triviales
const mockDb:     Database = { query: async () => [] };
const mockMailer: Mailer   = { send: async () => {} };
const mockLogger: Logger   = { info: () => {}, error: () => {} };

const testService = new UserService(mockDb, mockMailer, mockLogger);
```

---

## Contenedor IoC manual

Un contenedor IoC gestiona el ciclo de vida de las dependencias y las inyecta automáticamente.

```typescript
type Constructor<T = unknown> = new (...args: unknown[]) => T;
type Token = string | symbol | Constructor;

interface Registration {
  factory:   () => unknown;
  singleton: boolean;
  instance?: unknown;
}

class Container {
  private registry = new Map<Token, Registration>();

  // Registra una implementación
  register<T>(
    token:     Token,
    factory:   () => T,
    options:   { singleton?: boolean } = {}
  ): this {
    this.registry.set(token, {
      factory,
      singleton: options.singleton ?? false,
    });
    return this;
  }

  // Registra como singleton
  singleton<T>(token: Token, factory: () => T): this {
    return this.register(token, factory, { singleton: true });
  }

  // Resuelve una dependencia
  resolve<T>(token: Token): T {
    const reg = this.registry.get(token);
    if (!reg) throw new Error(`Token no registrado: ${String(token)}`);

    if (reg.singleton) {
      if (!reg.instance) reg.instance = reg.factory();
      return reg.instance as T;
    }

    return reg.factory() as T;
  }
}

// Tokens (evita magic strings con symbols)
const TOKENS = {
  DB:           Symbol('Database'),
  Mailer:       Symbol('Mailer'),
  Logger:       Symbol('Logger'),
  UserService:  Symbol('UserService'),
} as const;

// Composición root: un único lugar donde se arma todo
function buildContainer(env: 'prod' | 'test'): Container {
  const c = new Container();

  if (env === 'prod') {
    c.singleton(TOKENS.DB,     () => new PostgresDatabase());
    c.singleton(TOKENS.Mailer, () => new SendGridMailer());
    c.singleton(TOKENS.Logger, () => new WinstonLogger());
  } else {
    c.singleton(TOKENS.DB,     () => ({ query: async () => [] }));
    c.singleton(TOKENS.Mailer, () => ({ send: async () => {} }));
    c.singleton(TOKENS.Logger, () => ({ info: () => {}, error: () => {} }));
  }

  c.register(TOKENS.UserService, () => new UserService(
    c.resolve(TOKENS.DB),
    c.resolve(TOKENS.Mailer),
    c.resolve(TOKENS.Logger)
  ));

  return c;
}

const container   = buildContainer('prod');
const userService = container.resolve<UserService>(TOKENS.UserService);
```

---

## DI con decoradores (TypeScript + Reflect)

Frameworks como NestJS, InversifyJS o TSyringe usan decoradores para registrar y resolver dependencias automáticamente.

```typescript
// Con InversifyJS
import { injectable, inject, Container } from 'inversify';

@injectable()
class WinstonLogger implements Logger {
  info(msg: string)  { console.log(msg); }
  error(msg: string) { console.error(msg); }
}

@injectable()
class UserService {
  constructor(
    @inject('Logger') private logger: Logger,
    @inject('Database') private db: Database
  ) {}
}

const container = new Container();
container.bind<Logger>('Logger').to(WinstonLogger).inSingletonScope();
container.bind<UserService>(UserService).toSelf();
```

---

## NestJS: DI integrado

```typescript
// NestJS usa DI como ciudadano de primera clase
@Injectable()
export class UserService {
  constructor(
    @InjectRepository(User) private userRepo: Repository<User>,
    private mailer: MailService
  ) {}
}

@Module({
  imports:   [TypeOrmModule.forFeature([User])],
  providers: [UserService, MailService],
  controllers: [UserController],
})
export class UserModule {}
```

---

## Resumen

| Concepto | Descripción |
|---|---|
| **DI** | Las dependencias se pasan desde fuera, no se crean internamente |
| **IoC Container** | Gestiona el registro y resolución de dependencias |
| **Singleton** | Una instancia compartida por toda la app (DB pool, config, logger) |
| **Transient** | Nueva instancia en cada resolución |
| **Composition Root** | El único lugar donde se ensamblan todas las dependencias |
| **Beneficio principal** | Testabilidad — puedes inyectar mocks fácilmente |
