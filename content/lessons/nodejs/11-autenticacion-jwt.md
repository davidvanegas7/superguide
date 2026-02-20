# Autenticación: bcrypt, JWT y refresh tokens

## ¿Por qué la autenticación importa en el backend?

La autenticación responde a la pregunta: **¿quién eres?** Antes de ejecutar cualquier lógica de negocio, necesitamos verificar la identidad del cliente. En APIs REST stateless, no hay sesiones en el servidor; en su lugar utilizamos **tokens firmados** que el cliente envía en cada petición.

El flujo más común en producción combina tres piezas:
1. **bcrypt** para almacenar contraseñas de forma segura (hashing irreversible)
2. **JWT** (JSON Web Token) como access token de corta duración
3. **Refresh token** de larga duración para obtener nuevos access tokens sin re-autenticar

---

## Hashing de contraseñas con bcrypt

Nunca almacenes contraseñas en texto plano. `bcryptjs` (o `bcrypt`) aplica un algoritmo de hashing adaptativo con un **salt** aleatorio.

```bash
npm install bcryptjs
npm install -D @types/bcryptjs
```

```typescript
// src/utils/password.ts
import bcrypt from 'bcryptjs';

const SALT_ROUNDS = 12; // entre 10-14 es razonable en producción

export async function hashPassword(plainText: string): Promise<string> {
  return bcrypt.hash(plainText, SALT_ROUNDS);
}

export async function verifyPassword(
  plainText: string,
  hash: string
): Promise<boolean> {
  return bcrypt.compare(plainText, hash);
}
```

> **¿Por qué 12 rounds?** Cada incremento duplica el tiempo de cómputo. Con 12 rounds, hashear tarda ~300ms en hardware moderno — suficiente para ralentizar ataques de fuerza bruta sin afectar la UX.

---

## JWT: estructura y firma

Un JWT tiene tres partes separadas por puntos: `header.payload.signature`

```typescript
// src/utils/token.ts
import jwt from 'jsonwebtoken';
import { env } from '../config/env';

export interface TokenPayload {
  sub: string;   // subject: user id
  email: string;
  role: string;
  iat?: number;  // issued at (lo agrega jwt automáticamente)
  exp?: number;  // expiration
}

export function signAccessToken(payload: Omit<TokenPayload, 'iat' | 'exp'>): string {
  return jwt.sign(payload, env.JWT_SECRET, { expiresIn: '15m' });
}

export function signRefreshToken(userId: string): string {
  return jwt.sign({ sub: userId }, env.JWT_REFRESH_SECRET, { expiresIn: '7d' });
}

export function verifyAccessToken(token: string): TokenPayload {
  return jwt.verify(token, env.JWT_SECRET) as TokenPayload;
}

export function verifyRefreshToken(token: string): { sub: string } {
  return jwt.verify(token, env.JWT_REFRESH_SECRET) as { sub: string };
}
```

Instalación:
```bash
npm install jsonwebtoken
npm install -D @types/jsonwebtoken
```

---

## AuthService: registro, login, refresh y logout

```typescript
// src/services/AuthService.ts
import { prisma } from '../lib/prisma';
import { hashPassword, verifyPassword } from '../utils/password';
import {
  signAccessToken,
  signRefreshToken,
  verifyRefreshToken,
} from '../utils/token';
import { AppError } from '../errors/AppError';

interface RegisterInput {
  name: string;
  email: string;
  password: string;
}

interface LoginInput {
  email: string;
  password: string;
}

interface AuthTokens {
  accessToken: string;
  refreshToken: string;
}

export class AuthService {
  async register(input: RegisterInput): Promise<AuthTokens> {
    const existing = await prisma.user.findUnique({
      where: { email: input.email },
    });
    if (existing) {
      throw new AppError('El email ya está registrado', 409);
    }

    const passwordHash = await hashPassword(input.password);
    const user = await prisma.user.create({
      data: {
        name: input.name,
        email: input.email,
        passwordHash,
        role: 'USER',
      },
    });

    return this.generateTokens(user.id, user.email, user.role);
  }

  async login(input: LoginInput): Promise<AuthTokens> {
    const user = await prisma.user.findUnique({
      where: { email: input.email },
    });
    if (!user) {
      throw new AppError('Credenciales inválidas', 401);
    }

    const valid = await verifyPassword(input.password, user.passwordHash);
    if (!valid) {
      throw new AppError('Credenciales inválidas', 401);
    }

    return this.generateTokens(user.id, user.email, user.role);
  }

  async refreshTokens(refreshToken: string): Promise<AuthTokens> {
    let payload: { sub: string };
    try {
      payload = verifyRefreshToken(refreshToken);
    } catch {
      throw new AppError('Refresh token inválido o expirado', 401);
    }

    // Verificar que el refresh token no está en la blacklist
    const blacklisted = await prisma.tokenBlacklist.findUnique({
      where: { token: refreshToken },
    });
    if (blacklisted) {
      throw new AppError('Refresh token revocado', 401);
    }

    const user = await prisma.user.findUniqueOrThrow({
      where: { id: payload.sub },
    });

    // Rotar el refresh token: revocar el anterior, emitir uno nuevo
    await prisma.tokenBlacklist.create({ data: { token: refreshToken } });

    return this.generateTokens(user.id, user.email, user.role);
  }

  async logout(refreshToken: string): Promise<void> {
    await prisma.tokenBlacklist.upsert({
      where: { token: refreshToken },
      create: { token: refreshToken },
      update: {},
    });
  }

  private generateTokens(
    userId: string,
    email: string,
    role: string
  ): AuthTokens {
    const accessToken = signAccessToken({ sub: userId, email, role });
    const refreshToken = signRefreshToken(userId);
    return { accessToken, refreshToken };
  }
}

export const authService = new AuthService();
```

---

## Middleware de autenticación

```typescript
// src/middlewares/authenticate.ts
import { Request, Response, NextFunction } from 'express';
import { verifyAccessToken, TokenPayload } from '../utils/token';
import { AppError } from '../errors/AppError';

// Extender el tipo de Request para incluir el usuario autenticado
declare global {
  namespace Express {
    interface Request {
      user?: TokenPayload;
    }
  }
}

export function authenticate(
  req: Request,
  _res: Response,
  next: NextFunction
): void {
  const authHeader = req.headers.authorization;
  if (!authHeader?.startsWith('Bearer ')) {
    return next(new AppError('Token no proporcionado', 401));
  }

  const token = authHeader.slice(7);
  try {
    req.user = verifyAccessToken(token);
    next();
  } catch {
    next(new AppError('Token inválido o expirado', 401));
  }
}
```

---

## Rutas de autenticación

```typescript
// src/routes/auth.routes.ts
import { Router, Request, Response, NextFunction } from 'express';
import { z } from 'zod';
import { authService } from '../services/AuthService';
import { authenticate } from '../middlewares/authenticate';

const router = Router();

const registerSchema = z.object({
  name: z.string().min(2),
  email: z.string().email(),
  password: z.string().min(8),
});

const loginSchema = z.object({
  email: z.string().email(),
  password: z.string(),
});

// POST /auth/register
router.post('/register', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const body = registerSchema.parse(req.body);
    const tokens = await authService.register(body);
    res.status(201).json(tokens);
  } catch (err) {
    next(err);
  }
});

// POST /auth/login
router.post('/login', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const body = loginSchema.parse(req.body);
    const tokens = await authService.login(body);
    res.json(tokens);
  } catch (err) {
    next(err);
  }
});

// POST /auth/refresh
router.post('/refresh', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const { refreshToken } = req.body;
    if (!refreshToken) throw new Error('refreshToken requerido');
    const tokens = await authService.refreshTokens(refreshToken);
    res.json(tokens);
  } catch (err) {
    next(err);
  }
});

// POST /auth/logout
router.post('/logout', authenticate, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const { refreshToken } = req.body;
    if (refreshToken) await authService.logout(refreshToken);
    res.status(204).send();
  } catch (err) {
    next(err);
  }
});

// GET /auth/me
router.get('/me', authenticate, (req: Request, res: Response) => {
  res.json({ user: req.user });
});

export default router;
```

---

## Modelo Prisma necesario

```prisma
model User {
  id           String   @id @default(cuid())
  name         String
  email        String   @unique
  passwordHash String
  role         String   @default("USER")
  createdAt    DateTime @default(now())
  updatedAt    DateTime @updatedAt
}

model TokenBlacklist {
  id        String   @id @default(cuid())
  token     String   @unique
  createdAt DateTime @default(now())
}
```

> **Limpieza de blacklist**: En producción, añade un cron job que elimine tokens de la blacklist cuya fecha de expiración ya pasó, para no crecer la tabla indefinidamente.

---

## Buenas prácticas de seguridad

| Práctica | ¿Por qué? |
|---|---|
| Access token corto (15min) | Minimiza ventana de exposición si se filtra |
| Refresh token largo (7d) con rotación | Detecta robo: si el antiguo se usa, revocar ambos |
| Almacenar refresh token en httpOnly cookie | Inaccesible desde JavaScript (XSS) |
| No incluir datos sensibles en JWT | El payload es decodificable sin la clave |
| Comparar contraseñas con `bcrypt.compare` | Resistente a timing attacks |
| Devolver siempre `401` (no `403`) en login fallido | No revela si el email existe |

---

## Almacenando el refresh token en cookie httpOnly

```typescript
// En el controlador de login/register:
res.cookie('refreshToken', tokens.refreshToken, {
  httpOnly: true,
  secure: process.env.NODE_ENV === 'production',
  sameSite: 'strict',
  maxAge: 7 * 24 * 60 * 60 * 1000, // 7 días en ms
});

res.json({ accessToken: tokens.accessToken });
```

---

## Resumen

- `bcrypt.hash()` con 12 rounds para almacenar contraseñas de forma segura
- JWT de 15 minutos como access token + refresh token de 7 días con rotación
- Blacklist en BD para invalidar refresh tokens en logout
- Middleware `authenticate` extrae y verifica el access token en cada petición protegida
- Refresh tokens en cookies httpOnly para prevenir XSS
