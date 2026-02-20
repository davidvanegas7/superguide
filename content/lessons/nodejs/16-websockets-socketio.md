# WebSockets en tiempo real con Socket.io

## HTTP vs WebSocket

HTTP es **request-response**: el cliente pide, el servidor responde y la conexión cierra. Para actualizaciones en tiempo real (chats, notificaciones, precios en vivo) necesitamos una conexión **bidireccional persistente**.

**WebSocket** es un protocolo que convierte la conexión HTTP inicial (handshake) en un canal TCP persistente. Socket.io añade una capa encima con:
- Reconexión automática
- Namespaces y rooms
- Fallback a long-polling si WebSocket no está disponible
- Broadcasts tipados

---

## Instalación e integración con Express

```bash
npm install socket.io
npm install -D @types/node
```

```typescript
// src/server.ts
import { createServer } from 'node:http';
import { createApp } from './app';
import { createSocketServer } from './socket';

const app = createApp();
const httpServer = createServer(app);
const io = createSocketServer(httpServer);

httpServer.listen(3000, () => {
  console.log('Server + WS running on port 3000');
});

export { io };
```

```typescript
// src/socket/index.ts
import { Server } from 'socket.io';
import type { Server as HttpServer } from 'node:http';
import { registerChatHandlers } from './handlers/chat.handler';
import { socketAuthMiddleware } from './middleware/socketAuth';

export function createSocketServer(httpServer: HttpServer): Server {
  const io = new Server(httpServer, {
    cors: {
      origin: process.env.CLIENT_URL ?? 'http://localhost:5173',
      methods: ['GET', 'POST'],
      credentials: true,
    },
    pingTimeout: 60000,
    pingInterval: 25000,
  });

  // Middleware de autenticación para todos los sockets
  io.use(socketAuthMiddleware);

  // Registrar handlers
  io.on('connection', (socket) => {
    console.log(`Client connected: ${socket.id} — user: ${socket.data.userId}`);
    registerChatHandlers(io, socket);

    socket.on('disconnect', (reason) => {
      console.log(`Client disconnected: ${socket.id} — reason: ${reason}`);
    });
  });

  return io;
}
```

---

## Autenticación de sockets

Los sockets deben autenticarse antes de conectarse. El cliente envía el JWT en los `auth` handshake data:

```typescript
// src/socket/middleware/socketAuth.ts
import { Socket } from 'socket.io';
import { verifyAccessToken } from '../../utils/token';

export function socketAuthMiddleware(
  socket: Socket,
  next: (err?: Error) => void
): void {
  const token = socket.handshake.auth.token as string | undefined;

  if (!token) {
    return next(new Error('Token no proporcionado'));
  }

  try {
    const payload = verifyAccessToken(token);
    socket.data.userId = payload.sub;
    socket.data.email = payload.email;
    socket.data.role = payload.role;
    next();
  } catch {
    next(new Error('Token inválido'));
  }
}
```

Desde el cliente (JavaScript):
```javascript
import { io } from 'socket.io-client';

const socket = io('http://localhost:3000', {
  auth: { token: localStorage.getItem('accessToken') },
});
```

---

## Rooms y namespaces

**Room**: agrupación temporal de sockets dentro de un namespace. Un socket puede estar en múltiples rooms.

**Namespace**: canal con URL propia (`/chat`, `/notifications`). Los namespaces tienen su propio middleware y eventos.

```typescript
// src/socket/handlers/chat.handler.ts
import { Server, Socket } from 'socket.io';
import { prisma } from '../../lib/prisma';

interface MessagePayload {
  roomId: string;
  content: string;
}

interface JoinRoomPayload {
  roomId: string;
}

export function registerChatHandlers(io: Server, socket: Socket): void {
  const userId = socket.data.userId as string;

  // Unirse a una sala
  socket.on('chat:join', async ({ roomId }: JoinRoomPayload) => {
    // Verificar que el usuario tiene acceso a esta sala
    const member = await prisma.roomMember.findUnique({
      where: { userId_roomId: { userId, roomId } },
    });
    if (!member) {
      socket.emit('error', { message: 'No tienes acceso a esta sala' });
      return;
    }

    await socket.join(roomId);
    socket.emit('chat:joined', { roomId });

    // Notificar a los demás en la sala
    socket.to(roomId).emit('chat:userJoined', {
      userId,
      email: socket.data.email,
      timestamp: new Date().toISOString(),
    });

    // Enviar historial de mensajes recientes
    const history = await prisma.message.findMany({
      where: { roomId },
      orderBy: { createdAt: 'desc' },
      take: 50,
      include: { author: { select: { id: true, name: true } } },
    });
    socket.emit('chat:history', history.reverse());
  });

  // Enviar mensaje
  socket.on('chat:message', async ({ roomId, content }: MessagePayload) => {
    // Verificar que está en la sala
    const rooms = socket.rooms;
    if (!rooms.has(roomId)) {
      socket.emit('error', { message: 'No estás en esta sala' });
      return;
    }

    // Guardar en BD
    const message = await prisma.message.create({
      data: { roomId, content, authorId: userId },
      include: { author: { select: { id: true, name: true } } },
    });

    // Emitir a todos en la sala (incluido el emisor)
    io.to(roomId).emit('chat:message', {
      id: message.id,
      content: message.content,
      author: message.author,
      roomId,
      createdAt: message.createdAt.toISOString(),
    });
  });

  // Indicador de "está escribiendo"
  socket.on('chat:typing', ({ roomId }: JoinRoomPayload) => {
    socket.to(roomId).emit('chat:typing', {
      userId,
      email: socket.data.email,
    });
  });

  // Salir de una sala
  socket.on('chat:leave', ({ roomId }: JoinRoomPayload) => {
    socket.leave(roomId);
    socket.to(roomId).emit('chat:userLeft', { userId });
  });
}
```

---

## Namespace de notificaciones

```typescript
// src/socket/namespaces/notifications.ts
import { Server } from 'socket.io';
import { socketAuthMiddleware } from '../middleware/socketAuth';

export function setupNotificationsNamespace(io: Server): void {
  const nsp = io.of('/notifications');
  nsp.use(socketAuthMiddleware);

  nsp.on('connection', (socket) => {
    const userId = socket.data.userId;

    // Cada usuario se une a su sala personal al conectarse
    socket.join(`user:${userId}`);
    console.log(`User ${userId} connected to notifications`);
  });
}

// Emitir notificación desde cualquier parte de la app:
export function sendNotificationToUser(io: Server, userId: string, notification: object): void {
  io.of('/notifications').to(`user:${userId}`).emit('notification', notification);
}
```

---

## Tipos compartidos (cliente y servidor)

```typescript
// src/socket/types.ts — Se puede compartir con el frontend
export interface ServerToClientEvents {
  'chat:message': (msg: ChatMessage) => void;
  'chat:history': (msgs: ChatMessage[]) => void;
  'chat:joined': (data: { roomId: string }) => void;
  'chat:userJoined': (data: { userId: string; email: string; timestamp: string }) => void;
  'chat:userLeft': (data: { userId: string }) => void;
  'chat:typing': (data: { userId: string; email: string }) => void;
  'error': (data: { message: string }) => void;
  'notification': (data: NotificationPayload) => void;
}

export interface ClientToServerEvents {
  'chat:join': (data: { roomId: string }) => void;
  'chat:leave': (data: { roomId: string }) => void;
  'chat:message': (data: { roomId: string; content: string }) => void;
  'chat:typing': (data: { roomId: string }) => void;
}

export interface ChatMessage {
  id: string;
  content: string;
  author: { id: string; name: string };
  roomId: string;
  createdAt: string;
}

export interface NotificationPayload {
  id: string;
  type: 'info' | 'warning' | 'success';
  title: string;
  message: string;
}
```

Servidor con tipos:

```typescript
import { Server } from 'socket.io';
import { ServerToClientEvents, ClientToServerEvents } from './types';

const io = new Server<ClientToServerEvents, ServerToClientEvents>(httpServer);
```

---

## Broadcast: patrones de emisión

```typescript
// Emitir al socket específico
socket.emit('event', data);

// Emitir a todos en una room EXCEPTO al emisor
socket.to('roomId').emit('event', data);

// Emitir a todos en una room (incluido el emisor)
io.to('roomId').emit('event', data);

// Emitir a todos los sockets conectados
io.emit('event', data);

// Emitir a todos EXCEPTO al socket específico
socket.broadcast.emit('event', data);

// Emitir a un namespace
io.of('/namespace').emit('event', data);
```

---

## Escalado horizontal con Redis Adapter

En producción con múltiples instancias del servidor, los sockets de diferentes instancias no se pueden comunicar entre sí sin un adaptador centralizado:

```bash
npm install @socket.io/redis-adapter ioredis
```

```typescript
import { createAdapter } from '@socket.io/redis-adapter';
import { createClient } from 'ioredis';

const pubClient = createClient({ host: 'localhost', port: 6379 });
const subClient = pubClient.duplicate();

io.adapter(createAdapter(pubClient, subClient));
```

---

## Resumen

| Concepto | Implementación |
|---|---|
| Setup básico | `new Server(httpServer, { cors })` |
| Autenticación | Middleware `socketAuthMiddleware` en `io.use()` |
| Rooms | `socket.join()`, `socket.to(room).emit()`, `io.to(room).emit()` |
| Namespaces | `io.of('/namespace')` con su propio middleware |
| Tipado | `Server<ClientToServer, ServerToClient>` |
| Escalado | Redis Adapter con pub/sub |
