# Patrones de Diseño Creacionales

Los **patrones creacionales** resuelven el problema de **cómo crear objetos** de forma flexible, ocultando la lógica de instanciación y permitiendo que el sistema sea independiente de cómo se crean sus objetos.

---

## Factory Method

Define una interfaz para crear un objeto, pero deja que las **subclases** decidan qué clase instanciar.

```typescript
interface Logger {
  log(level: 'info' | 'warn' | 'error', msg: string): void;
}

class ConsoleLogger implements Logger {
  log(level: string, msg: string): void {
    const prefix = { info: '💡', warn: '⚠️', error: '❌' }[level] ?? '•';
    console.log(`${prefix} [${level.toUpperCase()}] ${msg}`);
  }
}

class FileLogger implements Logger {
  constructor(private filename: string) {}
  log(level: string, msg: string): void {
    // En producción: escribiría al archivo
    console.log(`[FILE:${this.filename}] ${level}: ${msg}`);
  }
}

class JsonLogger implements Logger {
  log(level: string, msg: string): void {
    console.log(JSON.stringify({ timestamp: new Date().toISOString(), level, msg }));
  }
}

// Factory: centraliza la decisión de qué instanciar
class LoggerFactory {
  static create(type: 'console' | 'file' | 'json', options?: { filename?: string }): Logger {
    switch (type) {
      case 'console': return new ConsoleLogger();
      case 'file':    return new FileLogger(options?.filename ?? 'app.log');
      case 'json':    return new JsonLogger();
      default:        throw new Error(`Logger desconocido: ${type}`);
    }
  }
}

// El cliente no sabe (ni necesita saber) qué clase concreta usa
const logger = LoggerFactory.create(process.env.LOG_FORMAT as any ?? 'console');
logger.log('info', 'Servidor iniciado en el puerto 3000');
logger.log('warn', 'Token a punto de expirar');
```

---

## Abstract Factory

Proporciona una interfaz para crear **familias de objetos relacionados** sin especificar sus clases concretas.

```typescript
// Familias de componentes de base de datos

interface DatabaseConnection {
  connect(): void;
  query(sql: string): unknown[];
  disconnect(): void;
}

interface QueryBuilder {
  select(table: string, fields: string[]): string;
  insert(table: string, data: Record<string, unknown>): string;
  update(table: string, data: Record<string, unknown>, where: string): string;
}

// Familia PostgreSQL
class PostgresConnection implements DatabaseConnection {
  connect():    void    { console.log('Conectando a PostgreSQL...'); }
  query(sql: string): unknown[] { console.log(`[PG] ${sql}`); return []; }
  disconnect(): void    { console.log('Desconectando PostgreSQL'); }
}

class PostgresQueryBuilder implements QueryBuilder {
  select(table: string, fields: string[]): string {
    return `SELECT ${fields.join(', ')} FROM "${table}"`;
  }
  insert(table: string, data: Record<string, unknown>): string {
    const keys = Object.keys(data);
    const vals = keys.map((_, i) => `$${i + 1}`);
    return `INSERT INTO "${table}" (${keys.join(', ')}) VALUES (${vals.join(', ')})`;
  }
  update(table: string, data: Record<string, unknown>, where: string): string {
    const sets = Object.keys(data).map((k, i) => `${k} = $${i + 1}`);
    return `UPDATE "${table}" SET ${sets.join(', ')} WHERE ${where}`;
  }
}

// Familia SQLite
class SQLiteConnection implements DatabaseConnection {
  connect():    void    { console.log('Abriendo SQLite...'); }
  query(sql: string): unknown[] { console.log(`[SQLite] ${sql}`); return []; }
  disconnect(): void    { console.log('Cerrando SQLite'); }
}

class SQLiteQueryBuilder implements QueryBuilder {
  select(table: string, fields: string[]): string {
    return `SELECT ${fields.join(', ')} FROM \`${table}\``;
  }
  insert(table: string, data: Record<string, unknown>): string {
    const keys = Object.keys(data);
    const vals = keys.map(() => '?');
    return `INSERT INTO \`${table}\` (${keys.join(', ')}) VALUES (${vals.join(', ')})`;
  }
  update(table: string, data: Record<string, unknown>, where: string): string {
    const sets = Object.keys(data).map(k => `${k} = ?`);
    return `UPDATE \`${table}\` SET ${sets.join(', ')} WHERE ${where}`;
  }
}

// Abstract Factory
interface DatabaseFactory {
  createConnection(): DatabaseConnection;
  createQueryBuilder(): QueryBuilder;
}

class PostgresFactory implements DatabaseFactory {
  createConnection():  DatabaseConnection { return new PostgresConnection(); }
  createQueryBuilder(): QueryBuilder      { return new PostgresQueryBuilder(); }
}

class SQLiteFactory implements DatabaseFactory {
  createConnection():  DatabaseConnection { return new SQLiteConnection(); }
  createQueryBuilder(): QueryBuilder      { return new SQLiteQueryBuilder(); }
}

// El cliente usa la fábrica sin conocer las clases concretas
function setupDatabase(factory: DatabaseFactory): void {
  const conn = factory.createConnection();
  const qb   = factory.createQueryBuilder();

  conn.connect();
  const q = qb.select('users', ['id', 'name', 'email']);
  console.log('Query generada:', q);
  conn.disconnect();
}

const factory = process.env.NODE_ENV === 'test'
  ? new SQLiteFactory()
  : new PostgresFactory();

setupDatabase(factory);
```

---

## Builder

Separa la **construcción** de un objeto complejo de su **representación**, permitiendo el mismo proceso de construcción para crear distintas representaciones.

```typescript
interface EmailOptions {
  from:        string;
  to:          string[];
  cc?:         string[];
  bcc?:        string[];
  subject:     string;
  textBody?:   string;
  htmlBody?:   string;
  replyTo?:    string;
  attachments: Array<{ filename: string; content: Buffer }>;
  priority:    'high' | 'normal' | 'low';
}

class EmailBuilder {
  private options: Partial<EmailOptions> = {
    to:          [],
    cc:          [],
    bcc:         [],
    attachments: [],
    priority:    'normal',
  };

  from(address: string): this {
    this.options.from = address;
    return this;
  }

  to(...addresses: string[]): this {
    this.options.to!.push(...addresses);
    return this;
  }

  cc(...addresses: string[]): this {
    this.options.cc!.push(...addresses);
    return this;
  }

  subject(text: string): this {
    this.options.subject = text;
    return this;
  }

  textBody(text: string): this {
    this.options.textBody = text;
    return this;
  }

  htmlBody(html: string): this {
    this.options.htmlBody = html;
    return this;
  }

  priority(p: 'high' | 'normal' | 'low'): this {
    this.options.priority = p;
    return this;
  }

  attach(filename: string, content: Buffer): this {
    this.options.attachments!.push({ filename, content });
    return this;
  }

  build(): EmailOptions {
    if (!this.options.from)           throw new Error('El campo "from" es requerido');
    if (!this.options.to?.length)     throw new Error('Se necesita al menos un destinatario');
    if (!this.options.subject)        throw new Error('El asunto es requerido');
    if (!this.options.textBody && !this.options.htmlBody) {
      throw new Error('Se necesita cuerpo de texto o HTML');
    }

    return this.options as EmailOptions;
  }
}

// Uso: API fluida, legible y con validación al final
const email = new EmailBuilder()
  .from('noreply@tienda.com')
  .to('ana@example.com', 'bob@example.com')
  .cc('manager@tienda.com')
  .subject('Tu pedido #1234 fue enviado')
  .htmlBody('<h1>¡Tu pedido está en camino!</h1>')
  .textBody('Tu pedido está en camino.')
  .priority('high')
  .build();

console.log('Email listo:', email.subject, '→', email.to);
```

---

## Singleton

Garantiza que una clase tiene **una única instancia** y proporciona un punto de acceso global a ella.

```typescript
class AppConfig {
  private static instance: AppConfig | null = null;

  private constructor(
    public readonly dbUrl:      string,
    public readonly port:       number,
    public readonly jwtSecret:  string,
    public readonly environment: string
  ) {}

  static getInstance(): AppConfig {
    if (!AppConfig.instance) {
      AppConfig.instance = new AppConfig(
        process.env.DATABASE_URL ?? 'postgresql://localhost/dev',
        parseInt(process.env.PORT ?? '3000'),
        process.env.JWT_SECRET ?? 'dev-secret',
        process.env.NODE_ENV ?? 'development'
      );
    }
    return AppConfig.instance;
  }

  // Solo para tests: permite resetear la instancia
  static reset(): void {
    AppConfig.instance = null;
  }

  isProduction(): boolean { return this.environment === 'production'; }
}

const config1 = AppConfig.getInstance();
const config2 = AppConfig.getInstance();
console.log(config1 === config2); // true — misma instancia
console.log(config1.port);        // 3000
```

> **Cuidado con Singleton**: dificulta los tests porque introduce estado global. En aplicaciones modernas se prefiere **inyección de dependencias** con un contenedor IoC que gestione el ciclo de vida (singleton cuando conviene).

---

## Object Pool

Un patrón relacionado: mantiene un conjunto de objetos reutilizables para evitar el coste de creación frecuente.

```typescript
class DatabaseConnectionPool {
  private available: string[] = [];  // simplificado: IDs de conexión
  private inUse     = new Set<string>();
  private readonly maxSize: number;

  constructor(maxSize: number) {
    this.maxSize = maxSize;
    // Pre-crea las conexiones
    for (let i = 0; i < maxSize; i++) {
      this.available.push(`conn-${i}`);
    }
  }

  acquire(): string {
    const conn = this.available.pop();
    if (!conn) throw new Error('Pool agotado, espera a que se libere una conexión');
    this.inUse.add(conn);
    return conn;
  }

  release(conn: string): void {
    if (!this.inUse.has(conn)) throw new Error('Conexión no pertenece al pool');
    this.inUse.delete(conn);
    this.available.push(conn);
  }

  get stats() {
    return { available: this.available.length, inUse: this.inUse.size };
  }
}

const pool = new DatabaseConnectionPool(3);
const c1   = pool.acquire();
const c2   = pool.acquire();
console.log(pool.stats); // { available: 1, inUse: 2 }
pool.release(c1);
console.log(pool.stats); // { available: 2, inUse: 1 }
```

---

## Cuándo usar cada patrón

| Patrón | Úsalo cuando… |
|---|---|
| **Factory Method** | La creación de objetos cambia según contexto o configuración |
| **Abstract Factory** | Necesitas familias de objetos relacionados intercambiables |
| **Builder** | Un objeto tiene muchos parámetros opcionales o pasos de construcción |
| **Singleton** | Necesitas una única instancia compartida (config, pool, cache global) |
| **Object Pool** | La creación de objetos es costosa y se reutilizan frecuentemente |
