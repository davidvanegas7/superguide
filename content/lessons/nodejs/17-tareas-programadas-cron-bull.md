# Tareas programadas con node-cron y colas con BullMQ

## ¿Cuándo usar cron jobs y cuándo colas?

| Necesidad | Solución |
|---|---|
| Ejecutar algo cada hora/día/semana | Cron job (node-cron) |
| Ejecutar una tarea en background sin bloquear | Cola (BullMQ) |
| Reintentar tareas fallidas automáticamente | Cola (BullMQ) |
| Procesar miles de tareas en paralelo | Cola (BullMQ) con concurrencia |
| Limpiar datos expirados cada noche | Cron job |
| Enviar email de bienvenida tras registro | Cola |

---

## node-cron: tareas programadas

```bash
npm install node-cron
npm install -D @types/node-cron
```

### Expresiones cron

```
┌───────────── segundo (0-59, opcional)
│ ┌─────────── minuto (0-59)
│ │ ┌───────── hora (0-23)
│ │ │ ┌─────── día del mes (1-31)
│ │ │ │ ┌───── mes (1-12)
│ │ │ │ │ ┌─── día de la semana (0-7, 0 y 7 = domingo)
│ │ │ │ │ │
* * * * * *
```

Ejemplos:
- `* * * * *` → cada minuto
- `0 * * * *` → cada hora en punto
- `0 9 * * 1-5` → lunes a viernes a las 9:00
- `0 0 * * 0` → cada domingo a medianoche
- `*/15 * * * *` → cada 15 minutos
- `0 2 1 * *` → el primer día de cada mes a las 2:00

---

## Definir tareas cron

```typescript
// src/jobs/cron.ts
import cron from 'node-cron';
import { prisma } from '../lib/prisma';
import { logger } from '../lib/logger';

// Limpiar refresh tokens expirados — cada día a las 3:00
export function scheduleTokenCleanup() {
  cron.schedule('0 3 * * *', async () => {
    const jobLogger = logger.child({ job: 'token-cleanup' });
    jobLogger.info('Starting token cleanup');

    try {
      const sevenDaysAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
      const { count } = await prisma.tokenBlacklist.deleteMany({
        where: { createdAt: { lt: sevenDaysAgo } },
      });
      jobLogger.info({ count }, 'Token cleanup completed');
    } catch (err) {
      jobLogger.error({ err }, 'Token cleanup failed');
    }
  });
}

// Generar reporte diario de actividad — cada día a las 6:00
export function scheduleActivityReport() {
  cron.schedule('0 6 * * *', async () => {
    const jobLogger = logger.child({ job: 'activity-report' });
    jobLogger.info('Generating activity report');

    try {
      const yesterday = new Date();
      yesterday.setDate(yesterday.getDate() - 1);
      yesterday.setHours(0, 0, 0, 0);
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      const stats = await prisma.user.count({
        where: { createdAt: { gte: yesterday, lt: today } },
      });

      jobLogger.info({ newUsers: stats }, 'Activity report generated');
      // Aquí podrías enviar el reporte por email
    } catch (err) {
      jobLogger.error({ err }, 'Activity report failed');
    }
  });
}

export function initCronJobs() {
  scheduleTokenCleanup();
  scheduleActivityReport();
  logger.info('Cron jobs initialized');
}
```

```typescript
// src/server.ts
import { initCronJobs } from './jobs/cron';

// Inicializar cron jobs al arrancar
initCronJobs();
```

---

## BullMQ: colas de trabajo con Redis

BullMQ es la librería de colas más robusta para Node.js. Requiere Redis como backend.

```bash
npm install bullmq ioredis
```

### Arquitectura de BullMQ

```
Producer (tu app)
    │
    │  job.add({ data })
    ▼
  Queue (Redis)
    │
    │  job = await queue.getNextJob()
    ▼
Worker (proceso separado o mismo proceso)
    │
    │  process job
    ▼
  Completed / Failed
```

### Configuración del cliente Redis

```typescript
// src/lib/redis.ts
import IORedis from 'ioredis';
import { env } from '../config/env';

export const redisConnection = new IORedis(env.REDIS_URL, {
  maxRetriesPerRequest: null, // Requerido por BullMQ
  enableReadyCheck: false,
});
```

---

## Definir una cola y sus tipos

```typescript
// src/queues/email.queue.ts
import { Queue, Worker, QueueEvents, Job } from 'bullmq';
import { redisConnection } from '../lib/redis';
import { logger } from '../lib/logger';

// Tipos para los distintos jobs de email
export type EmailJobData =
  | { type: 'welcome'; userId: string; email: string; name: string }
  | { type: 'password-reset'; email: string; token: string }
  | { type: 'newsletter'; email: string; campaignId: string };

// Crear la cola
export const emailQueue = new Queue<EmailJobData>('emails', {
  connection: redisConnection,
  defaultJobOptions: {
    attempts: 3,                          // Reintentar hasta 3 veces
    backoff: { type: 'exponential', delay: 1000 }, // 1s, 2s, 4s
    removeOnComplete: { count: 100 },     // Guardar solo los últimos 100 completados
    removeOnFail: { count: 200 },         // Guardar los últimos 200 fallidos
  },
});
```

### Producer: encolar trabajos

```typescript
// src/services/EmailService.ts
import { emailQueue, EmailJobData } from '../queues/email.queue';

export class EmailService {
  async sendWelcomeEmail(userId: string, email: string, name: string): Promise<void> {
    await emailQueue.add('send-email', {
      type: 'welcome',
      userId,
      email,
      name,
    });
  }

  async sendPasswordReset(email: string, token: string): Promise<void> {
    await emailQueue.add(
      'send-email',
      { type: 'password-reset', email, token },
      { priority: 1 } // Alta prioridad
    );
  }

  async scheduleNewsletter(email: string, campaignId: string, sendAt: Date): Promise<void> {
    const delay = sendAt.getTime() - Date.now();
    await emailQueue.add(
      'send-email',
      { type: 'newsletter', email, campaignId },
      { delay } // Job diferido
    );
  }
}
```

---

## Worker: procesar trabajos

```typescript
// src/workers/email.worker.ts
import { Worker, Job } from 'bullmq';
import { redisConnection } from '../lib/redis';
import { EmailJobData } from '../queues/email.queue';
import { logger } from '../lib/logger';
import nodemailer from 'nodemailer';

const transporter = nodemailer.createTransport({ /* config SMTP */ });

async function processEmailJob(job: Job<EmailJobData>): Promise<void> {
  const jobLogger = logger.child({ jobId: job.id, jobType: job.data.type });
  jobLogger.info('Processing email job');

  switch (job.data.type) {
    case 'welcome':
      await transporter.sendMail({
        to: job.data.email,
        subject: `¡Bienvenido, ${job.data.name}!`,
        html: `<h1>Hola ${job.data.name}</h1><p>Gracias por registrarte.</p>`,
      });
      break;

    case 'password-reset':
      await transporter.sendMail({
        to: job.data.email,
        subject: 'Restablece tu contraseña',
        html: `<p>Tu token: <strong>${job.data.token}</strong> (válido 1 hora)</p>`,
      });
      break;

    case 'newsletter':
      // Obtener contenido de campaña y enviar
      jobLogger.info({ campaignId: job.data.campaignId }, 'Sending newsletter');
      break;
  }

  jobLogger.info('Email job completed');
}

export const emailWorker = new Worker<EmailJobData>(
  'emails',
  processEmailJob,
  {
    connection: redisConnection,
    concurrency: 5, // Procesar hasta 5 emails en paralelo
  }
);

emailWorker.on('completed', (job) => {
  logger.info({ jobId: job.id }, 'Job completed');
});

emailWorker.on('failed', (job, err) => {
  logger.error({ jobId: job?.id, err: err.message }, 'Job failed');
});
```

---

## Dead Letter Queue (DLQ)

Los jobs que fallan todas sus tentativas se pueden redirigir a una cola de "muertos" para análisis:

```typescript
// src/queues/dlq.ts
import { Queue, Worker, UnrecoverableError } from 'bullmq';
import { redisConnection } from '../lib/redis';

export const dlq = new Queue('dead-letter', { connection: redisConnection });

// En el worker principal, capturar errores no recuperables
async function processWithDLQ(job: Job<EmailJobData>): Promise<void> {
  try {
    await processEmailJob(job);
  } catch (err) {
    if (isNonRetryableError(err)) {
      // Mover a DLQ inmediatamente sin reintentos
      await dlq.add('failed-job', { originalJob: job.name, data: job.data, error: String(err) });
      throw new UnrecoverableError('Job moved to DLQ'); // Evitar reintentos
    }
    throw err; // Dejar que BullMQ reintente
  }
}

function isNonRetryableError(err: unknown): boolean {
  // Errores de BD de datos inválidos, emails inválidos, etc.
  const msg = String(err).toLowerCase();
  return msg.includes('invalid email') || msg.includes('not found');
}
```

---

## Bull Board: dashboard de monitoreo

```bash
npm install @bull-board/express @bull-board/api
```

```typescript
// src/lib/bullBoard.ts
import { createBullBoard } from '@bull-board/api';
import { BullMQAdapter } from '@bull-board/api/bullMQAdapter';
import { ExpressAdapter } from '@bull-board/express';
import { emailQueue } from '../queues/email.queue';

export function setupBullBoard(app: Express) {
  const serverAdapter = new ExpressAdapter();
  serverAdapter.setBasePath('/admin/queues');

  createBullBoard({
    queues: [new BullMQAdapter(emailQueue)],
    serverAdapter,
  });

  app.use('/admin/queues', serverAdapter.getRouter());
}
```

---

## Resumen

| Herramienta | Uso |
|---|---|
| `node-cron` | Tareas recurrentes (limpiezas, reportes) |
| BullMQ `Queue` | Encolar trabajos desde la app |
| BullMQ `Worker` | Procesar trabajos con concurrencia |
| `attempts + backoff` | Reintentos exponenciales automáticos |
| `UnrecoverableError` | Mover a DLQ sin reintentos |
| Bull Board | Dashboard web para monitorear colas |
| `delay` en job options | Programar jobs diferidos |
