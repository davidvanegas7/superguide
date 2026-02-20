# Validación de datos con Zod

## ¿Por qué Zod?

Los `interface` y `type` de TypeScript solo existen en tiempo de compilación. En runtime, cualquier dato que entra desde HTTP, archivos o bases de datos es `unknown`. Zod cierra esa brecha:

```typescript
// Sin Zod — el compilador confía en ti, pero el dato puede ser cualquier cosa
const body = req.body as CreateUserDto; // ← mentira en tiempo de ejecución

// Con Zod — validación real en runtime + inferencia de tipos
const result = createUserSchema.safeParse(req.body);
// result.data está garantizado con el tipo correcto
```

```bash
pnpm add zod
```

---

## Tipos básicos

```typescript
import { z } from 'zod';

const stringSchema  = z.string();
const numberSchema  = z.number();
const booleanSchema = z.boolean();
const dateSchema    = z.date();
const nullSchema    = z.null();
const undefinedSchema = z.undefined();
const unknownSchema = z.unknown(); // acepta cualquier valor sin transformar
const neverSchema   = z.never();   // rechaza todo (útil en switch exhaustivo)

// Literales
const adminRole = z.literal('admin');

// Validar con parse (lanza ZodError si falla)
const name = z.string().parse("Ana"); // "Ana"
z.string().parse(42);                 // ❌ lanza ZodError

// Validar con safeParse (nunca lanza)
const result = z.string().safeParse(42);
if (!result.success) {
  console.log(result.error.issues); // array de errores
} else {
  console.log(result.data); // string garantizado
}
```

---

## Strings con restricciones

```typescript
const emailSchema = z
  .string()
  .min(1, 'El email es requerido')
  .max(255, 'El email es demasiado largo')
  .email('Formato de email inválido')
  .toLowerCase()        // transformar: convierte a minúsculas
  .trim();              // transformar: elimina espacios

const passwordSchema = z
  .string()
  .min(8, 'Mínimo 8 caracteres')
  .max(100)
  .regex(/[A-Z]/, 'Debe tener al menos una mayúscula')
  .regex(/[0-9]/, 'Debe tener al menos un número');

const slugSchema = z
  .string()
  .regex(/^[a-z0-9-]+$/, 'Solo letras minúsculas, números y guiones');

const urlSchema = z.string().url('URL inválida');

const uuidSchema = z.string().uuid('UUID inválido');
```

---

## Números y fechas

```typescript
const priceSchema = z
  .number()
  .positive('El precio debe ser positivo')
  .multipleOf(0.01, 'Máximo 2 decimales');

const ageSchema = z
  .number()
  .int('Debe ser entero')
  .min(18, 'Debes ser mayor de edad')
  .max(120);

// Coerción: útil para query params (siempre vienen como string)
const pageSchema = z.coerce.number().int().positive().default(1);
// z.coerce.number().parse("42") → 42 (convierte el string a número)

const futureDateSchema = z
  .date()
  .min(new Date(), 'La fecha debe ser futura');

// Coercionar string ISO a Date
const dateStringSchema = z.coerce.date();
// dateStringSchema.parse("2024-12-31") → Date object
```

---

## Objetos (el uso más común en APIs)

```typescript
const createUserSchema = z.object({
  name:     z.string().min(2).max(100).trim(),
  email:    z.string().email().toLowerCase(),
  password: z.string().min(8),
  role:     z.enum(['admin', 'editor', 'viewer']).default('viewer'),
  age:      z.number().int().min(18).optional(),
  metadata: z.record(z.string(), z.unknown()).optional(), // objeto con claves dinámicas
});

// Inferir tipo TypeScript automáticamente — fuente única de verdad
type CreateUserDto = z.infer<typeof createUserSchema>;
// {
//   name: string;
//   email: string;
//   password: string;
//   role: "admin" | "editor" | "viewer";
//   age?: number;
//   metadata?: Record<string, unknown>;
// }

// Para update: todos los campos opcionales
const updateUserSchema = createUserSchema
  .omit({ password: true })  // excluir password del update
  .partial();                // hacer todos opcionales
type UpdateUserDto = z.infer<typeof updateUserSchema>;
```

### Métodos clave de objetos

```typescript
const schema = z.object({ a: z.string(), b: z.number(), c: z.boolean() });

schema.pick({ a: true, b: true });    // solo a y b
schema.omit({ c: true });            // todo menos c
schema.partial();                     // todos opcionales
schema.required();                    // todos requeridos
schema.extend({ d: z.date() });       // añadir campos
schema.merge(otroSchema);             // combinar schemas

// Por defecto Zod elimina campos extra (strip)
// Para rechazarlos:
schema.strict();
// Para pasarlos tal cual:
schema.passthrough();
```

---

## Arrays y uniones

```typescript
// Array de strings
const tagsSchema = z.array(z.string().min(1)).min(1).max(10);

// Tupla (array con longitud y tipos fijos)
const coordSchema = z.tuple([z.number(), z.number()]); // [lat, lng]

// Unión
const idSchema = z.union([z.string().uuid(), z.number().int().positive()]);
// Forma más corta:
const roleSchema = z.enum(['admin', 'editor', 'viewer']);

// Discriminated union (más eficiente que union para objetos)
const eventSchema = z.discriminatedUnion('type', [
  z.object({ type: z.literal('click'),   x: z.number(), y: z.number() }),
  z.object({ type: z.literal('keydown'), key: z.string() }),
  z.object({ type: z.literal('scroll'),  delta: z.number() }),
]);
type Event = z.infer<typeof eventSchema>;
```

---

## Transformaciones y refinements

```typescript
// transform — transforma el valor después de validar
const trimmedString = z.string().transform(s => s.trim());

const normalizedEmail = z
  .string()
  .email()
  .transform(s => s.toLowerCase().trim());

// Transformar string a objeto
const jsonStringSchema = z
  .string()
  .transform((str, ctx) => {
    try {
      return JSON.parse(str) as unknown;
    } catch {
      ctx.addIssue({ code: 'custom', message: 'JSON inválido' });
      return z.NEVER; // indicar que falló
    }
  });

// refine — validación personalizada compleja
const passwordConfirmSchema = z
  .object({
    password:        z.string().min(8),
    confirmPassword: z.string(),
  })
  .refine(data => data.password === data.confirmPassword, {
    message: 'Las contraseñas no coinciden',
    path:    ['confirmPassword'], // campo al que atribuir el error
  });

// superRefine — para múltiples errores personalizados
const ageRangeSchema = z
  .object({ min: z.number(), max: z.number() })
  .superRefine((data, ctx) => {
    if (data.min >= data.max) {
      ctx.addIssue({
        code:    z.ZodIssueCode.custom,
        message: 'min debe ser menor que max',
        path:    ['min'],
      });
    }
  });
```

---

## Validar variables de entorno

Una de las mejores aplicaciones de Zod: validar `process.env` al arrancar la app:

```typescript
// src/config/env.ts
import { z } from 'zod';

const envSchema = z.object({
  NODE_ENV:     z.enum(['development', 'test', 'production']).default('development'),
  PORT:         z.coerce.number().int().positive().default(3000),
  DATABASE_URL: z.string().url('DATABASE_URL debe ser una URL válida'),
  JWT_SECRET:   z.string().min(32, 'JWT_SECRET debe tener al menos 32 caracteres'),
  JWT_EXPIRES:  z.string().default('7d'),
  LOG_LEVEL:    z.enum(['trace','debug','info','warn','error','fatal']).default('info'),
  REDIS_URL:    z.string().url().optional(),
});

// Lanza un error descriptivo si falta alguna variable
const parsed = envSchema.safeParse(process.env);

if (!parsed.success) {
  console.error('❌ Variables de entorno inválidas:');
  parsed.error.issues.forEach(issue => {
    console.error(`  ${issue.path.join('.')}: ${issue.message}`);
  });
  process.exit(1);
}

export const env = parsed.data;
// env.PORT es number (no string), env.NODE_ENV tiene autocompletado
```

---

## Middleware de validación genérico

```typescript
// src/middleware/validate.ts
import { Request, Response, NextFunction } from 'express';
import { ZodSchema, ZodError } from 'zod';

type ValidateTarget = 'body' | 'query' | 'params';

export function validate(schema: ZodSchema, target: ValidateTarget = 'body') {
  return (req: Request, res: Response, next: NextFunction): void => {
    const result = schema.safeParse(req[target]);

    if (!result.success) {
      res.status(422).json({
        error:   'Datos inválidos',
        details: result.error.errors.map(e => ({
          field:   e.path.join('.'),
          message: e.message,
          code:    e.code,
        })),
      });
      return;
    }

    // Reemplazar con datos transformados/sanitizados por Zod
    (req as any)[target] = result.data;
    next();
  };
}

// Uso en rutas
import { createUserSchema, updateUserSchema } from '../schemas/user.schema.js';
import { validate } from '../middleware/validate.js';

router.post('/',    validate(createUserSchema),  createUser);
router.patch('/:id', validate(updateUserSchema), updateUser);
router.get('/',    validate(paginationSchema, 'query'), getUsers);
```

---

## Resumen

| Operación | Método |
|---|---|
| Validar (lanza error) | `schema.parse(data)` |
| Validar (resultado seguro) | `schema.safeParse(data)` |
| Inferir tipo TypeScript | `z.infer<typeof schema>` |
| Hacer campos opcionales | `schema.partial()` |
| Excluir campos | `schema.omit({ campo: true })` |
| Transformar valor | `.transform(fn)` |
| Validación personalizada | `.refine(fn, mensaje)` |
| Coercionar tipos | `z.coerce.number()`, `z.coerce.date()` |

En la siguiente lección aprendemos a manejar **variables de entorno** de forma robusta con múltiples entornos y el patrón de objeto de configuración centralizado.
