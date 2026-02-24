# Seguridad en Backend

La seguridad no es una característica que se añade al final — debe diseñarse desde el principio. El proyecto OWASP (Open Web Application Security Project) mantiene la lista de los riesgos más críticos.

---

## OWASP Top 10 más comunes en backend

### 1. Inyección (SQL Injection)

```typescript
// ❌ Vulnerable: concatena input del usuario en SQL
const email = req.body.email;  // "' OR '1'='1"
const query = `SELECT * FROM users WHERE email = '${email}'`;
// → SELECT * FROM users WHERE email = '' OR '1'='1'
// ¡Devuelve TODOS los usuarios!

// ✅ Parámetros preparados (siempre)
const { rows } = await db.query(
  'SELECT id, name FROM users WHERE email = $1',
  [req.body.email]                              // el driver lo escapa
);

// ✅ Con ORM: usa los métodos del ORM, no queryRaw con interpolación
const user = await prisma.user.findFirst({
  where: { email: req.body.email }  // seguro por diseño
});

// ❌ Incluso con ORM, nunca hagas esto:
await prisma.$queryRaw`SELECT * FROM users WHERE email = '${req.body.email}'`;
// ✅ Usa parámetros también en queryRaw:
await prisma.$queryRaw`SELECT * FROM users WHERE email = ${req.body.email}`;
```

### 2. Broken Authentication

```typescript
import bcrypt from 'bcrypt';
import jwt    from 'jsonwebtoken';

// ✅ Hashear contraseñas con bcrypt (nunca MD5, SHA1 o SHA256 a secas)
const SALT_ROUNDS = 12;

async function hashPassword(password: string): Promise<string> {
  return bcrypt.hash(password, SALT_ROUNDS);
}

async function verifyPassword(password: string, hash: string): Promise<boolean> {
  return bcrypt.compare(password, hash);
}

// ✅ JWT seguro
const JWT_SECRET  = process.env.JWT_SECRET!;  // al menos 256 bits
const JWT_EXPIRES = '15m';                     // corto → acceso

function signToken(userId: number, role: string): string {
  return jwt.sign({ sub: userId, role }, JWT_SECRET, {
    expiresIn:  JWT_EXPIRES,
    issuer:     'miapp.com',
    audience:   'miapp.com',
  });
}

function verifyToken(token: string): { sub: number; role: string } {
  return jwt.verify(token, JWT_SECRET, {
    issuer:   'miapp.com',
    audience: 'miapp.com',
  }) as { sub: number; role: string };
}

// ✅ Refresh tokens: larga duración, rotación
async function refreshAccessToken(refreshToken: string) {
  const payload = verifyToken(refreshToken);
  const stored  = await tokenStore.get(payload.sub);
  if (!stored || stored !== refreshToken) throw new Error('Token revocado');

  // Rotación: invalida el token usado, emite nuevo
  const newAccess  = signToken(payload.sub, payload.role);
  const newRefresh = signToken(payload.sub, payload.role);  // expiresIn: '7d'

  await tokenStore.set(payload.sub, newRefresh);
  return { access: newAccess, refresh: newRefresh };
}
```

### 3. Exposición de datos sensibles

```typescript
// ✅ Nunca devuelves la contraseña al cliente
interface UserPublic {
  id:    number;
  email: string;
  name:  string;
  role:  string;
}

function toPublicUser(user: UserWithPassword): UserPublic {
  const { password, ...rest } = user;  // desestructura eliminando password
  return rest;
}

// ✅ Variables de entorno, nunca hardcoded
// ❌ const secret = 'mi-secreto-123';
// ✅
const secret = process.env.JWT_SECRET;
if (!secret) throw new Error('JWT_SECRET no definida');

// ✅ Logs: nunca loguear datos sensibles
function login(email: string, password: string) {
  logger.info(`Login attempt for ${email}`);       // ✅ email ok
  // logger.debug(`Password: ${password}`);         // ❌ NUNCA
}
```

### 4. Validación y sanitización de entrada

```typescript
import Joi   from 'joi';
import xss   from 'xss';
import DOMPurify from 'isomorphic-dompurify';

// ✅ Valida y sanitiza TODA entrada del usuario
const registerSchema = Joi.object({
  email: Joi.string().email().max(255).required(),
  name:  Joi.string().alphanum().min(2).max(100).required(),
  password: Joi.string()
    .min(8)
    .max(128)
    .pattern(/[A-Z]/, 'uppercase')
    .pattern(/[0-9]/, 'number')
    .required(),
});

app.post('/register', async (req, res) => {
  const { error, value } = registerSchema.validate(req.body, { abortEarly: false });
  if (error) {
    const details = error.details.reduce((acc, d) => {
      const field = d.path.join('.');
      return { ...acc, [field]: [...(acc[field] ?? []), d.message] };
    }, {} as Record<string, string[]>);
    return res.status(422).json({ error: { code: 'VALIDATION_ERROR', details } });
  }

  // Sanitiza contenido que podría mostrarse como HTML
  const safeName = xss(value.name);
  // ...
});
```

---

## CORS (Cross-Origin Resource Sharing)

```typescript
import cors from 'cors';

const ALLOWED_ORIGINS = [
  'https://miapp.com',
  'https://admin.miapp.com',
  ...(process.env.NODE_ENV !== 'production' ? ['http://localhost:3000'] : []),
];

app.use(cors({
  origin: (origin, callback) => {
    if (!origin || ALLOWED_ORIGINS.includes(origin)) {
      callback(null, true);
    } else {
      callback(new Error('No permitido por CORS'));
    }
  },
  methods:          ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
  allowedHeaders:   ['Content-Type', 'Authorization', 'X-Trace-Id'],
  exposedHeaders:   ['X-RateLimit-Remaining', 'X-Total-Count'],
  credentials:      true,   // ¡Solo si realmente necesitas cookies cross-origin!
  maxAge:           86400,  // Cache preflight 24h
}));
```

---

## Headers de seguridad con Helmet

```typescript
import helmet from 'helmet';

app.use(helmet({
  contentSecurityPolicy: {
    directives: {
      defaultSrc: ["'self'"],
      scriptSrc:  ["'self'"],
      styleSrc:   ["'self'", "'unsafe-inline'"],
      imgSrc:     ["'self'", 'data:', 'https:'],
    },
  },
  hsts: {
    maxAge:            31536000,  // 1 año en segundos
    includeSubDomains: true,
    preload:           true,
  },
  // Activa automáticamente:
  // X-Frame-Options: DENY
  // X-Content-Type-Options: nosniff
  // Referrer-Policy: no-referrer
  // X-XSS-Protection: 0 (moderno: confiar en CSP)
}));
```

---

## Autorización basada en roles (RBAC)

```typescript
type Permission = 'users:read' | 'users:write' | 'users:delete'
                | 'orders:read' | 'orders:write' | 'admin:all';

const ROLE_PERMISSIONS: Record<string, Permission[]> = {
  admin: ['users:read', 'users:write', 'users:delete', 'orders:read', 'orders:write', 'admin:all'],
  staff: ['users:read', 'orders:read', 'orders:write'],
  user:  ['orders:read'],
};

function hasPermission(role: string, permission: Permission): boolean {
  return ROLE_PERMISSIONS[role]?.includes(permission) ?? false;
}

// Middleware de autorización
function require(permission: Permission) {
  return (req: Request, res: Response, next: NextFunction) => {
    const user = req.user;  // añadido por el middleware de autenticación

    if (!user) {
      return res.status(401).json({ error: { code: 'UNAUTHORIZED' } });
    }

    if (!hasPermission(user.role, permission)) {
      return res.status(403).json({ error: { code: 'FORBIDDEN' } });
    }

    next();
  };
}

// Uso
app.get('/admin/users',    require('users:read'),   userController.list);
app.delete('/admin/users/:id', require('users:delete'), userController.delete);
```

---

## Checklist de seguridad backend

| ✅ | Práctica |
|---|---|
| ☐ | Parámetros preparados en todas las queries SQL |
| ☐ | Contraseñas con bcrypt/argon2 (mínimo 12 rounds) |
| ☐ | Tokens JWT con expiración corta + refresh tokens |
| ☐ | Validar y sanitizar toda entrada del usuario |
| ☐ | HTTPS en todos los endpoints (redirigir HTTP) |
| ☐ | Headers de seguridad con Helmet |
| ☐ | CORS configurado estrictamente |
| ☐ | Rate limiting en endpoints de autenticación |
| ☐ | No exponer detalles de errores en producción |
| ☐ | Secretos en variables de entorno, nunca en código |
| ☐ | Logs de auditoría para acciones críticas |
| ☐ | Dependencias actualizadas (`npm audit` regular) |
