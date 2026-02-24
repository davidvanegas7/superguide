---
title: "Preguntas de Entrevista: Flask"
slug: "flask-preguntas-entrevista"
description: "Prepárate para entrevistas técnicas con las preguntas más comunes sobre Flask, su arquitectura, patrones y mejores prácticas."
---

# Preguntas de Entrevista: Flask

Las entrevistas técnicas para desarrolladores Python frecuentemente incluyen preguntas sobre Flask. No basta con saber usar el framework; debes comprender cómo funciona internamente, sus patrones de diseño y cómo se compara con otras opciones. En esta lección revisamos las preguntas más frecuentes con respuestas detalladas y ejemplos de código.

## 1. ¿Cuál es la diferencia entre Flask y Django?

Esta es probablemente la pregunta más común. La respuesta clave está en la filosofía:

| Aspecto | Flask | Django |
|---------|-------|--------|
| Filosofía | Micro-framework | Framework completo (batteries-included) |
| ORM | No incluido (usa SQLAlchemy) | Incluido (Django ORM) |
| Admin | No incluido | Panel de admin incluido |
| Formularios | Flask-WTF (extensión) | Django Forms (integrado) |
| Autenticación | Extensiones externas | Sistema auth incluido |
| Templates | Jinja2 | DTL (Django Template Language) |
| Flexibilidad | Muy alta | Más opinionado |
| Curva de aprendizaje | Más baja | Más alta inicialmente |

**Cuándo usar Flask**: APIs, microservicios, proyectos personalizados, aprendizaje.
**Cuándo usar Django**: Aplicaciones grandes, CMS, proyectos con admin, trabajo en equipo.

```python
# Flask: tú decides la estructura
from flask import Flask
app = Flask(__name__)

@app.route('/')
def hello():
    return 'Hello World'

# Django: estructura predefinida
# urls.py, views.py, models.py, settings.py...
```

## 2. ¿Qué es el Application Factory Pattern y por qué se usa?

El Application Factory es un patrón donde la aplicación se crea dentro de una función en lugar de como una variable global. Se usa para:

- **Testing**: crear instancias con diferentes configuraciones.
- **Múltiples instancias**: ejecutar varias versiones de la app.
- **Evitar imports circulares**: las extensiones se registran dentro de la función.

```python
# SIN factory (problemático)
app = Flask(__name__)
db = SQLAlchemy(app)  # Acoplamiento global

# CON factory (recomendado)
from flask import Flask
from flask_sqlalchemy import SQLAlchemy

db = SQLAlchemy()  # Sin app

def create_app(config_name='default'):
    app = Flask(__name__)
    app.config.from_object(config[config_name])

    # Inicializar extensiones
    db.init_app(app)

    # Registrar blueprints
    from .auth import auth_bp
    app.register_blueprint(auth_bp, url_prefix='/auth')

    return app
```

## 3. ¿Qué es el Application Context vs Request Context?

Flask tiene dos contextos que gestionan el estado de la aplicación:

**Application Context** (`app_context`): contiene datos a nivel de aplicación.
- Variables: `current_app`, `g`
- Disponible siempre que la aplicación esté activa.

**Request Context** (`request_context`): contiene datos específicos de una petición HTTP.
- Variables: `request`, `session`
- Solo disponible durante el procesamiento de una petición.

```python
from flask import current_app, g, request, session

# Fuera de una petición, necesitas crear el contexto manualmente
with app.app_context():
    print(current_app.config['DATABASE_URL'])
    db.create_all()

# Durante una petición, ambos contextos están disponibles automáticamente
@app.route('/ejemplo')
def ejemplo():
    # Request context
    usuario = request.args.get('usuario')
    session['ultimo_acceso'] = 'ahora'

    # Application context
    app_name = current_app.name
    g.db = get_db_connection()  # Objeto g temporal

    return f'App: {app_name}, Usuario: {usuario}'
```

## 4. ¿Qué es el objeto `g` y para qué se usa?

El objeto `g` (global) es un espacio de almacenamiento temporal que dura **una sola petición**. Se usa para compartir datos entre funciones durante el procesamiento de una petición sin pasarlos como argumentos.

```python
from flask import g

@app.before_request
def cargar_usuario():
    """Se ejecuta antes de cada petición."""
    token = request.headers.get('Authorization')
    if token:
        g.usuario = Usuario.query.filter_by(token=token).first()
    else:
        g.usuario = None

@app.route('/perfil')
def perfil():
    # g.usuario está disponible aquí
    if g.usuario is None:
        return jsonify({"error": "No autenticado"}), 401
    return jsonify(g.usuario.to_dict())

@app.teardown_appcontext
def cerrar_conexion(exception):
    """Se ejecuta al final de cada petición."""
    db = g.pop('db', None)
    if db is not None:
        db.close()
```

**Importante**: `g` se reinicia en cada petición. No lo uses para persistir datos entre peticiones.

## 5. ¿Qué son los Blueprints y cuándo los usarías?

Los **Blueprints** organizan una aplicación Flask en módulos independientes y reutilizables. Son esenciales cuando la aplicación crece más allá de un solo archivo.

```python
# auth/routes.py
from flask import Blueprint

auth_bp = Blueprint('auth', __name__, url_prefix='/auth')

@auth_bp.route('/login', methods=['POST'])
def login():
    return jsonify({"token": "..."})

@auth_bp.route('/registro', methods=['POST'])
def registro():
    return jsonify({"mensaje": "Usuario creado"})

# productos/routes.py
productos_bp = Blueprint('productos', __name__, url_prefix='/api/productos')

@productos_bp.route('/')
def listar():
    return jsonify([])

# app.py - Registrar blueprints
def create_app():
    app = Flask(__name__)
    from auth.routes import auth_bp
    from productos.routes import productos_bp

    app.register_blueprint(auth_bp)
    app.register_blueprint(productos_bp)

    return app
```

Ventajas: separación de responsabilidades, mejor organización, posibilidad de reutilizar módulos entre proyectos.

## 6. ¿Qué son las Signals (señales) en Flask?

Las **signals** permiten que componentes de la aplicación reaccionen a eventos sin acoplamiento directo. Flask usa la librería Blinker.

```python
from flask import template_rendered, request_started, request_finished
from blinker import Namespace

# Señales integradas de Flask
@template_rendered.connect_via(app)
def cuando_template_renderizado(sender, template, context, **extra):
    print(f'Template renderizado: {template.name}')

# Señales personalizadas
mis_signals = Namespace()
usuario_registrado = mis_signals.signal('usuario-registrado')

# Emitir señal
@app.route('/registro', methods=['POST'])
def registrar():
    usuario = crear_usuario(request.get_json())
    usuario_registrado.send(app, usuario=usuario)
    return jsonify({"ok": True})

# Suscribirse a la señal
@usuario_registrado.connect_via(app)
def enviar_bienvenida(sender, usuario, **kwargs):
    enviar_correo_bienvenida(usuario.email)

@usuario_registrado.connect_via(app)
def registrar_analiticas(sender, usuario, **kwargs):
    analytics.track('registro', usuario_id=usuario.id)
```

## 7. ¿Flask es thread-safe?

Flask en sí es thread-safe gracias a los **Local Proxies** y el **contexto por thread**. Cada petición se procesa en su propio thread con su propio contexto (`request`, `session`, `g`).

Sin embargo, debes tener cuidado con:

```python
# PELIGROSO: variable global compartida entre threads
contador = 0  # ¡No es thread-safe!

@app.route('/incrementar')
def incrementar():
    global contador
    contador += 1  # Race condition posible
    return str(contador)

# SEGURO: usar herramientas thread-safe
import threading
lock = threading.Lock()

@app.route('/incrementar-seguro')
def incrementar_seguro():
    global contador
    with lock:
        contador += 1
    return str(contador)

# MEJOR: usar la base de datos o Redis para estado compartido
@app.route('/incrementar-mejor')
def incrementar_mejor():
    redis_client.incr('contador')
    return str(redis_client.get('contador'))
```

## 8. ¿Cómo maneja Flask las conexiones a la base de datos?

Flask-SQLAlchemy usa un **pool de conexiones** y el patrón **scoped session**: cada thread/petición obtiene su propia sesión.

```python
# Flask-SQLAlchemy maneja esto automáticamente:
# 1. Al inicio de la petición: obtiene una conexión del pool
# 2. Durante la petición: usa esa conexión para todas las queries
# 3. Al final: hace commit o rollback y devuelve la conexión al pool

@app.teardown_appcontext
def shutdown_session(exception=None):
    db.session.remove()  # Liberar la sesión al final de la petición

# Configurar el pool
app.config['SQLALCHEMY_ENGINE_OPTIONS'] = {
    'pool_size': 10,
    'pool_recycle': 300,
    'pool_pre_ping': True,
}
```

## 9. ¿Qué es WSGI y cómo se relaciona con Flask?

**WSGI (Web Server Gateway Interface)** es la especificación estándar de Python que define cómo un servidor web se comunica con una aplicación Python. Flask es una aplicación WSGI.

```python
# Una aplicación WSGI mínima (sin Flask)
def app(environ, start_response):
    status = '200 OK'
    headers = [('Content-Type', 'text/plain')]
    start_response(status, headers)
    return [b'Hola Mundo']

# Flask abstrae esto, pero internamente es lo mismo
# Werkzeug (base de Flask) maneja la conversión WSGI

# El objeto app de Flask es callable WSGI:
from flask import Flask
app = Flask(__name__)
# app(environ, start_response)  <-- Así lo llama Gunicorn
```

El flujo es: **Cliente → Nginx → Gunicorn (WSGI Server) → Flask (WSGI App) → Respuesta**

## 10. Preguntas Adicionales Frecuentes

**¿Cómo manejas los errores en Flask?**

```python
@app.errorhandler(404)
def pagina_no_encontrada(error):
    return jsonify({"error": "Recurso no encontrado"}), 404

@app.errorhandler(500)
def error_interno(error):
    db.session.rollback()  # Importante: revertir transacciones fallidas
    return jsonify({"error": "Error interno del servidor"}), 500
```

**¿Qué son los middleware en Flask?**

```python
# Middleware WSGI
class MiMiddleware:
    def __init__(self, app):
        self.app = app

    def __call__(self, environ, start_response):
        # Antes de la petición
        print(f"Request: {environ['REQUEST_METHOD']} {environ['PATH_INFO']}")
        return self.app(environ, start_response)

app.wsgi_app = MiMiddleware(app.wsgi_app)
```

**¿Cómo harías paginación en Flask?**

```python
@app.route('/api/items')
def listar_items():
    pagina = request.args.get('page', 1, type=int)
    resultado = Item.query.paginate(page=pagina, per_page=20)
    return jsonify({
        'items': [i.to_dict() for i in resultado.items],
        'total': resultado.total,
        'paginas': resultado.pages
    })
```

## Ejercicio Práctico

Prepárate para una entrevista técnica respondiendo estas preguntas:

1. Explica con código el patrón Application Factory y sus beneficios.
2. Crea un ejemplo que demuestre la diferencia entre `g`, `session` y `request`.
3. Implementa una aplicación con 3 blueprints y explica cómo organizas el código.
4. Escribe un middleware WSGI que registre el tiempo de cada petición.
5. Demuestra un caso donde `g` podría causar problemas si no se usa correctamente.
6. Explica con un diagrama el flujo completo: Nginx → Gunicorn → Flask → Response.
7. Implementa señales personalizadas para desacoplar la lógica de registro de usuario.
8. Compara las ventajas/desventajas de Flask vs Django para un proyecto de e-commerce.

## Resumen

- **Flask vs Django**: Flask es micro y flexible; Django es completo y opinionado.
- **Application Factory**: crea la app en una función para testing y modularidad.
- **Application Context vs Request Context**: alcance de aplicación vs alcance de petición.
- **Objeto `g`**: almacenamiento temporal por petición, se reinicia en cada request.
- **Blueprints**: módulos para organizar una aplicación grande en componentes.
- **Signals**: reaccionar a eventos sin acoplamiento directo entre componentes.
- **Thread safety**: Flask es thread-safe por diseño, pero cuidado con estado global.
- **Conexiones BD**: Flask-SQLAlchemy usa scoped sessions y connection pooling.
- **WSGI**: estándar de comunicación entre servidores web y aplicaciones Python.
