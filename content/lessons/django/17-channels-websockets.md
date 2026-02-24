---
title: "Django Channels y WebSockets"
slug: "django-channels-websockets"
description: "Implementa comunicación en tiempo real con Django Channels: ASGI, consumers, routing, Channel layers con Redis, y aplicaciones prácticas"
---
# Django Channels y WebSockets

Las aplicaciones web modernas requieren comunicación en tiempo real: chats, notificaciones en vivo, actualizaciones de datos en tiempo real. Django Channels extiende Django más allá del ciclo HTTP tradicional, añadiendo soporte para WebSockets y otros protocolos asíncronos mediante ASGI, permitiendo construir experiencias interactivas y reactivas.

## ¿Qué es ASGI?

ASGI (Asynchronous Server Gateway Interface) es la evolución asíncrona de WSGI. Mientras WSGI maneja una petición a la vez por proceso, ASGI permite manejar múltiples conexiones concurrentes, incluyendo WebSockets y conexiones de larga duración.

```python
# En Django tradicional (WSGI):
# Petición HTTP → Respuesta → Cierre de conexión

# Con Channels (ASGI):
# WebSocket → Conexión persistente → Mensajes bidireccionales
```

## Instalación y Configuración

```bash
pip install channels channels-redis
```

```python
# settings.py
INSTALLED_APPS = [
    'daphne',  # Servidor ASGI - debe ir primero
    'django.contrib.admin',
    ...
    'channels',
    'chat',  # tu app
]

# Configurar ASGI
ASGI_APPLICATION = 'proyecto.asgi.application'

# Channel Layers con Redis
CHANNEL_LAYERS = {
    'default': {
        'BACKEND': 'channels_redis.core.RedisChannelLayer',
        'CONFIG': {
            'hosts': [('127.0.0.1', 6379)],
        },
    },
}
```

```python
# proyecto/asgi.py
import os
from django.core.asgi import get_asgi_application
from channels.routing import ProtocolTypeRouter, URLRouter
from channels.auth import AuthMiddlewareStack

os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'proyecto.settings')

django_asgi_app = get_asgi_application()

from chat.routing import websocket_urlpatterns

application = ProtocolTypeRouter({
    'http': django_asgi_app,
    'websocket': AuthMiddlewareStack(
        URLRouter(websocket_urlpatterns)
    ),
})
```

## Consumers: El Corazón de Channels

Un consumer es el equivalente a una vista, pero para WebSockets. Maneja la conexión, recepción y envío de mensajes:

### Consumer Síncrono

```python
# chat/consumers.py
import json
from channels.generic.websocket import WebsocketConsumer
from asgiref.sync import async_to_sync

class ChatConsumer(WebsocketConsumer):

    def connect(self):
        """Se ejecuta cuando un cliente abre la conexión WebSocket."""
        self.room_name = self.scope['url_route']['kwargs']['room_name']
        self.room_group_name = f'chat_{self.room_name}'

        # Unirse al grupo de la sala
        async_to_sync(self.channel_layer.group_add)(
            self.room_group_name,
            self.channel_name
        )

        # Aceptar la conexión
        self.accept()

        # Enviar mensaje de bienvenida
        self.send(text_data=json.dumps({
            'tipo': 'sistema',
            'mensaje': f'Te has unido a la sala {self.room_name}'
        }))

    def disconnect(self, close_code):
        """Se ejecuta cuando el cliente cierra la conexión."""
        async_to_sync(self.channel_layer.group_discard)(
            self.room_group_name,
            self.channel_name
        )

    def receive(self, text_data):
        """Se ejecuta cuando se recibe un mensaje del cliente."""
        data = json.loads(text_data)
        mensaje = data['mensaje']
        usuario = self.scope['user'].username

        # Enviar mensaje a todo el grupo
        async_to_sync(self.channel_layer.group_send)(
            self.room_group_name,
            {
                'type': 'chat_message',
                'mensaje': mensaje,
                'usuario': usuario,
            }
        )

    def chat_message(self, event):
        """Maneja mensajes enviados al grupo."""
        self.send(text_data=json.dumps({
            'tipo': 'mensaje',
            'mensaje': event['mensaje'],
            'usuario': event['usuario'],
        }))
```

### Consumer Asíncrono

Para mejor rendimiento, usa consumers asíncronos:

```python
from channels.generic.websocket import AsyncWebsocketConsumer
import json

class ChatAsyncConsumer(AsyncWebsocketConsumer):

    async def connect(self):
        self.room_name = self.scope['url_route']['kwargs']['room_name']
        self.room_group_name = f'chat_{self.room_name}'

        await self.channel_layer.group_add(
            self.room_group_name,
            self.channel_name
        )
        await self.accept()

    async def disconnect(self, close_code):
        await self.channel_layer.group_discard(
            self.room_group_name,
            self.channel_name
        )

    async def receive(self, text_data):
        data = json.loads(text_data)
        mensaje = data['mensaje']
        usuario = self.scope['user'].username

        # Guardar mensaje en la base de datos (usando sync_to_async)
        from channels.db import database_sync_to_async

        @database_sync_to_async
        def guardar_mensaje(sala, user, msg):
            from .models import Mensaje
            return Mensaje.objects.create(
                sala=sala, usuario=user, contenido=msg
            )

        await guardar_mensaje(self.room_name, self.scope['user'], mensaje)

        await self.channel_layer.group_send(
            self.room_group_name,
            {
                'type': 'chat_message',
                'mensaje': mensaje,
                'usuario': usuario,
            }
        )

    async def chat_message(self, event):
        await self.send(text_data=json.dumps({
            'tipo': 'mensaje',
            'mensaje': event['mensaje'],
            'usuario': event['usuario'],
        }))
```

## Routing

El routing de WebSockets funciona de manera similar al de URLs:

```python
# chat/routing.py
from django.urls import re_path
from . import consumers

websocket_urlpatterns = [
    re_path(r'ws/chat/(?P<room_name>\w+)/$', consumers.ChatAsyncConsumer.as_asgi()),
    re_path(r'ws/notificaciones/$', consumers.NotificacionConsumer.as_asgi()),
]
```

## Channel Layers y Grupos

Los Channel layers permiten la comunicación entre diferentes instancias de consumers usando Redis como intermediario:

```python
from channels.layers import get_channel_layer
from asgiref.sync import async_to_sync

# Enviar mensaje a un grupo desde cualquier parte (vistas, signals, tasks)
def notificar_nuevo_pedido(pedido):
    channel_layer = get_channel_layer()
    async_to_sync(channel_layer.group_send)(
        f'pedidos_{pedido.tienda_id}',
        {
            'type': 'nuevo_pedido',
            'pedido_id': pedido.id,
            'total': str(pedido.total),
            'cliente': pedido.cliente.nombre,
        }
    )

# Enviar a un canal específico
async_to_sync(channel_layer.send)(
    'specific_channel_name',
    {
        'type': 'alerta',
        'mensaje': 'Mensaje directo al canal',
    }
)
```

## Sistema de Notificaciones en Tiempo Real

```python
# notifications/consumers.py
class NotificacionConsumer(AsyncWebsocketConsumer):

    async def connect(self):
        self.user = self.scope['user']
        if self.user.is_anonymous:
            await self.close()
            return

        self.group_name = f'notificaciones_{self.user.id}'

        await self.channel_layer.group_add(
            self.group_name,
            self.channel_name
        )
        await self.accept()

        # Enviar notificaciones no leídas al conectar
        from channels.db import database_sync_to_async

        @database_sync_to_async
        def obtener_no_leidas():
            from .models import Notificacion
            return list(
                Notificacion.objects.filter(
                    usuario=self.user, leida=False
                ).values('id', 'titulo', 'mensaje', 'fecha')[:20]
            )

        no_leidas = await obtener_no_leidas()
        await self.send(text_data=json.dumps({
            'tipo': 'no_leidas',
            'notificaciones': no_leidas,
        }))

    async def nueva_notificacion(self, event):
        await self.send(text_data=json.dumps({
            'tipo': 'nueva',
            'titulo': event['titulo'],
            'mensaje': event['mensaje'],
        }))
```

## Cliente JavaScript

```javascript
// Conectar al WebSocket
const roomName = 'general';
const wsUrl = `ws://${window.location.host}/ws/chat/${roomName}/`;
const socket = new WebSocket(wsUrl);

socket.onopen = function(e) {
    console.log('Conexión WebSocket establecida');
};

socket.onmessage = function(e) {
    const data = JSON.parse(e.data);
    if (data.tipo === 'mensaje') {
        agregarMensaje(data.usuario, data.mensaje);
    }
};

socket.onclose = function(e) {
    console.log('WebSocket cerrado. Reconectando...');
    setTimeout(() => conectar(), 3000);
};

// Enviar mensaje
function enviarMensaje(mensaje) {
    socket.send(JSON.stringify({
        'mensaje': mensaje
    }));
}
```

## Ejecutar el Servidor

```bash
# Desarrollo (Daphne se usa automáticamente)
python manage.py runserver

# Producción
daphne -b 0.0.0.0 -p 8000 proyecto.asgi:application
```

## Ejercicio Práctico

Construye un sistema de chat en tiempo real:

1. Crea un consumer que maneje salas de chat con conexión, desconexión y envío de mensajes.
2. Implementa un modelo `Mensaje` y guarda los mensajes en la base de datos.
3. Al conectarse, envía los últimos 50 mensajes de la sala.
4. Agrega un consumer de notificaciones para alertar a usuarios mencionados (`@usuario`).
5. Implementa reconexión automática en el cliente JavaScript.

```python
class ChatConsumer(AsyncWebsocketConsumer):
    async def connect(self):
        self.room_name = self.scope['url_route']['kwargs']['room_name']
        self.room_group = f'chat_{self.room_name}'
        await self.channel_layer.group_add(self.room_group, self.channel_name)
        await self.accept()
        # Enviar historial de mensajes
        mensajes = await self.obtener_historial()
        await self.send(text_data=json.dumps({
            'tipo': 'historial', 'mensajes': mensajes
        }))
```

## Resumen

**Django Channels** extiende Django con soporte para WebSockets y comunicación en tiempo real a través de **ASGI**. Los **consumers** manejan conexiones WebSocket de forma similar a las vistas, pudiendo ser síncronos o asíncronos. El **routing** dirige las conexiones WebSocket a los consumers correspondientes. Los **Channel layers** (Redis) permiten la comunicación entre consumers mediante **grupos**, habilitando funcionalidades como salas de chat y notificaciones. Usa **Daphne** como servidor ASGI en producción y implementa reconexión automática en el cliente para una experiencia robusta.
