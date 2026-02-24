---
title: "WebSockets con Flask-SocketIO"
slug: "flask-websockets-socketio"
description: "Implementa comunicación en tiempo real con WebSockets usando Flask-SocketIO: chat, notificaciones push y eventos personalizados."
---

# WebSockets con Flask-SocketIO

HTTP es un protocolo de petición-respuesta: el cliente pide y el servidor contesta. Pero, ¿qué pasa cuando necesitas actualizaciones en tiempo real? Los **WebSockets** abren un canal de comunicación bidireccional y persistente entre cliente y servidor. **Flask-SocketIO** integra esta tecnología en Flask de forma elegante.

## ¿Qué son los WebSockets?

A diferencia de HTTP, un WebSocket mantiene la conexión abierta. Esto permite que tanto el cliente como el servidor envíen mensajes en cualquier momento, sin necesidad de realizar nuevas peticiones. Es ideal para:

- Chats en tiempo real
- Notificaciones instantáneas
- Paneles de monitoreo en vivo
- Juegos multijugador
- Edición colaborativa

## Instalación y Configuración

```bash
pip install flask-socketio eventlet
```

```python
# app.py
from flask import Flask, render_template
from flask_socketio import SocketIO

app = Flask(__name__)
app.config['SECRET_KEY'] = 'clave-secreta-websockets'

# Inicializar SocketIO con la app
socketio = SocketIO(app, cors_allowed_origins="*")

@app.route('/')
def index():
    return render_template('chat.html')

if __name__ == '__main__':
    # Usar socketio.run() en lugar de app.run()
    socketio.run(app, debug=True, host='0.0.0.0', port=5000)
```

## Eventos Básicos: emit y send

La comunicación con SocketIO se basa en **eventos**. Puedes emitir y escuchar eventos personalizados.

```python
from flask_socketio import emit, send

# Evento de conexión
@socketio.on('connect')
def manejar_conexion():
    """Se ejecuta cuando un cliente se conecta."""
    print('Cliente conectado')
    # Enviar un mensaje solo al cliente que se conectó
    emit('bienvenida', {'mensaje': '¡Bienvenido al servidor!'})

# Evento de desconexión
@socketio.on('disconnect')
def manejar_desconexion():
    print('Cliente desconectado')

# Evento personalizado: recibir un mensaje
@socketio.on('mensaje_chat')
def manejar_mensaje(data):
    """Recibe un mensaje del cliente y lo reenvía a todos."""
    print(f"Mensaje recibido: {data}")
    # send() envía un evento 'message' genérico
    # emit() envía un evento con nombre personalizado
    emit('nuevo_mensaje', {
        'usuario': data['usuario'],
        'texto': data['texto'],
        'hora': data.get('hora', 'ahora')
    }, broadcast=True)  # broadcast=True envía a TODOS los clientes
```

## Cliente JavaScript

```html
<!-- templates/chat.html -->
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Chat en Tiempo Real</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.7.2/socket.io.js"></script>
</head>
<body>
    <h1>Chat Flask-SocketIO</h1>
    <div id="mensajes" style="height:300px; overflow-y:scroll; border:1px solid #ccc; padding:10px;"></div>
    <input type="text" id="nombre" placeholder="Tu nombre" value="Anónimo">
    <input type="text" id="mensaje" placeholder="Escribe un mensaje...">
    <button onclick="enviarMensaje()">Enviar</button>

    <script>
        // Conectar al servidor SocketIO
        const socket = io();

        // Escuchar evento de bienvenida
        socket.on('bienvenida', (data) => {
            console.log(data.mensaje);
        });

        // Escuchar nuevos mensajes
        socket.on('nuevo_mensaje', (data) => {
            const div = document.getElementById('mensajes');
            div.innerHTML += `<p><strong>${data.usuario}:</strong> ${data.texto}</p>`;
            div.scrollTop = div.scrollHeight;
        });

        // Enviar mensaje
        function enviarMensaje() {
            const nombre = document.getElementById('nombre').value;
            const mensaje = document.getElementById('mensaje').value;

            socket.emit('mensaje_chat', {
                usuario: nombre,
                texto: mensaje,
                hora: new Date().toLocaleTimeString()
            });

            document.getElementById('mensaje').value = '';
        }

        // Enviar con Enter
        document.getElementById('mensaje').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') enviarMensaje();
        });
    </script>
</body>
</html>
```

## Rooms (Salas)

Las **rooms** permiten agrupar clientes para enviar mensajes solo a un subconjunto de conexiones.

```python
from flask_socketio import join_room, leave_room, rooms

@socketio.on('unirse_sala')
def unirse_a_sala(data):
    """Un cliente se une a una sala específica."""
    sala = data['sala']
    usuario = data['usuario']

    join_room(sala)  # Agregar al cliente a la sala

    # Notificar a la sala que alguien se unió
    emit('notificacion', {
        'mensaje': f'{usuario} se ha unido a la sala {sala}'
    }, to=sala)  # 'to' envía solo a la sala especificada

@socketio.on('salir_sala')
def salir_de_sala(data):
    """Un cliente sale de una sala."""
    sala = data['sala']
    usuario = data['usuario']

    leave_room(sala)

    emit('notificacion', {
        'mensaje': f'{usuario} ha salido de la sala {sala}'
    }, to=sala)

@socketio.on('mensaje_sala')
def mensaje_en_sala(data):
    """Envía un mensaje solo a una sala específica."""
    emit('nuevo_mensaje', {
        'usuario': data['usuario'],
        'texto': data['texto']
    }, to=data['sala'])
```

## Namespaces

Los **namespaces** permiten separar la lógica de SocketIO en canales independientes, como diferentes secciones de la aplicación.

```python
# Namespace para el chat
@socketio.on('mensaje', namespace='/chat')
def mensaje_chat(data):
    emit('respuesta', {'texto': data['texto']}, namespace='/chat')

# Namespace para notificaciones
@socketio.on('suscribir', namespace='/notificaciones')
def suscribirse(data):
    join_room(data['canal'], namespace='/notificaciones')
    emit('info', {'msg': f"Suscrito al canal {data['canal']}"}, namespace='/notificaciones')

@socketio.on('nueva_notificacion', namespace='/notificaciones')
def notificar(data):
    emit('notificacion', {
        'titulo': data['titulo'],
        'cuerpo': data['cuerpo'],
        'tipo': data.get('tipo', 'info')
    }, to=data['canal'], namespace='/notificaciones')
```

En el cliente se conectan por separado:

```javascript
// Conectar a namespaces diferentes
const chatSocket = io('/chat');
const notiSocket = io('/notificaciones');

chatSocket.on('respuesta', (data) => {
    console.log('Chat:', data.texto);
});

notiSocket.on('notificacion', (data) => {
    mostrarNotificacion(data.titulo, data.cuerpo);
});
```

## Sistema de Notificaciones Push

Ejemplo completo de notificaciones en tiempo real:

```python
# Almacén de usuarios conectados
usuarios_conectados = {}

@socketio.on('registrar_usuario')
def registrar(data):
    """Registra un usuario con su ID de sesión."""
    user_id = data['user_id']
    usuarios_conectados[user_id] = request.sid  # sid = session ID de SocketIO
    emit('registrado', {'status': 'ok'})

def enviar_notificacion_a_usuario(user_id, notificacion):
    """Envía una notificación a un usuario específico."""
    sid = usuarios_conectados.get(user_id)
    if sid:
        socketio.emit('notificacion', notificacion, to=sid)
        return True
    return False

# Ejemplo: notificar cuando alguien comenta en un post
@app.route('/api/comentarios', methods=['POST'])
def crear_comentario():
    data = request.get_json()
    # ... guardar comentario en BD ...

    # Notificar al autor del post
    enviar_notificacion_a_usuario(data['autor_post_id'], {
        'tipo': 'comentario',
        'titulo': 'Nuevo comentario',
        'cuerpo': f"{data['usuario']} comentó en tu publicación",
        'url': f"/post/{data['post_id']}"
    })

    return jsonify({"ok": True}), 201
```

## Manejo de Errores

```python
@socketio.on_error()
def manejar_error(e):
    """Maneja errores en eventos de SocketIO."""
    print(f'Error en SocketIO: {e}')

@socketio.on_error_default
def manejar_error_default(e):
    """Maneja errores no capturados."""
    print(f'Error no manejado: {e}')
    emit('error', {'mensaje': 'Ocurrió un error en el servidor'})
```

## Ejercicio Práctico

Construye un sistema de chat con salas usando Flask-SocketIO:

1. **Página principal** con un formulario para ingresar nombre de usuario y seleccionar/crear una sala.
2. **Evento `unirse`**: al unirse, notifica a todos en la sala y muestra la lista de usuarios activos.
3. **Evento `mensaje`**: envía mensajes solo a la sala correspondiente con nombre, texto y hora.
4. **Evento `escribiendo`**: muestra "X está escribiendo..." cuando un usuario tipea.
5. **Evento `salir`**: notifica cuando alguien sale y actualiza la lista de usuarios.
6. **Historial**: guarda los últimos 50 mensajes por sala y envíalos al nuevo usuario que se une.
7. Agrega un **namespace** separado `/admin` que muestre estadísticas: usuarios conectados, mensajes por sala, etc.

## Resumen

- **WebSockets** permiten comunicación bidireccional en tiempo real entre cliente y servidor.
- **Flask-SocketIO** integra WebSockets en Flask con soporte para eventos, rooms y namespaces.
- `emit()` envía eventos con nombre personalizado; `broadcast=True` envía a todos.
- Las **rooms** agrupan clientes para enviar mensajes a subconjuntos de usuarios.
- Los **namespaces** separan la lógica de SocketIO en canales independientes.
- Usa `socketio.run()` en lugar de `app.run()` para iniciar el servidor.
- Los eventos `connect` y `disconnect` manejan el ciclo de vida de las conexiones.
