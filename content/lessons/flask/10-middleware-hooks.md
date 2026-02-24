---
title: "Middleware y Hooks"
slug: "flask-middleware-hooks"
description: "Aprende a usar hooks de solicitud, el objeto g, middleware WSGI y los contextos de aplicación y solicitud en Flask."
---

# Middleware y Hooks

Flask proporciona un sistema de **hooks** (ganchos) que te permiten ejecutar código en diferentes momentos del ciclo de vida de una solicitud HTTP. Estos ganchos, junto con el objeto `g` y la comprensión de los contextos de Flask, te dan control total sobre cómo tu aplicación procesa cada petición. En esta lección exploraremos estas herramientas esenciales para construir aplicaciones Flask robustas y bien estructuradas.

## El Ciclo de Vida de una Solicitud

Cuando Flask recibe una solicitud HTTP, sigue este flujo:

```
Solicitud llega
    → before_request()
        → Función de vista (route handler)
            → after_request()
                → teardown_request()
                    → Respuesta enviada
```

Entender este flujo es fundamental para saber en qué momento intervenir con cada hook.

## `before_request`

El decorador `@app.before_request` registra una función que se ejecuta **antes** de cada solicitud, sin importar a qué ruta se dirija:

```python
from flask import Flask, g, request, redirect, url_for
import time

app = Flask(__name__)

@app.before_request
def antes_de_cada_solicitud():
    g.inicio_solicitud = time.time()
    print(f'[{request.method}] {request.path} - Solicitud iniciada')
```

### Casos de Uso Comunes

#### Verificación de Autenticación Global

```python
@app.before_request
def verificar_autenticacion():
    rutas_publicas = ['login', 'registro', 'static']
    
    if request.endpoint and request.endpoint not in rutas_publicas:
        if not current_user.is_authenticated:
            return redirect(url_for('login'))
```

#### Mantenimiento del Sitio

```python
MODO_MANTENIMIENTO = False

@app.before_request
def verificar_mantenimiento():
    if MODO_MANTENIMIENTO and request.endpoint != 'static':
        return '<h1>Sitio en mantenimiento</h1><p>Volvemos pronto.</p>', 503
```

#### Forzar HTTPS

```python
@app.before_request
def forzar_https():
    if not request.is_secure and app.config.get('FORCE_HTTPS'):
        url = request.url.replace('http://', 'https://', 1)
        return redirect(url, code=301)
```

> **Nota**: Si `before_request` retorna un valor (como un Response, un string o una tupla), Flask **interrumpe** el flujo y usa ese valor como la respuesta, sin ejecutar la función de vista.

## `after_request`

El decorador `@app.after_request` ejecuta código **después** de que la función de vista haya generado la respuesta, pero **antes** de enviarla al cliente. Recibe el objeto `response` y debe retornarlo:

```python
@app.after_request
def despues_de_cada_solicitud(response):
    # Calcular tiempo de respuesta
    if hasattr(g, 'inicio_solicitud'):
        duracion = time.time() - g.inicio_solicitud
        response.headers['X-Tiempo-Respuesta'] = f'{duracion:.4f}s'
    
    return response  # ¡Siempre debes retornar la respuesta!
```

### Casos de Uso Comunes

#### Agregar Headers de Seguridad

```python
@app.after_request
def agregar_headers_seguridad(response):
    response.headers['X-Content-Type-Options'] = 'nosniff'
    response.headers['X-Frame-Options'] = 'SAMEORIGIN'
    response.headers['X-XSS-Protection'] = '1; mode=block'
    response.headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains'
    return response
```

#### Control de Caché

```python
@app.after_request
def configurar_cache(response):
    if request.path.startswith('/api/'):
        response.headers['Cache-Control'] = 'no-store'
    elif request.path.startswith('/static/'):
        response.headers['Cache-Control'] = 'public, max-age=31536000'
    return response
```

#### Logging de Respuestas

```python
import logging

logger = logging.getLogger(__name__)

@app.after_request
def log_respuesta(response):
    logger.info(f'{request.method} {request.path} → {response.status_code}')
    return response
```

## `teardown_request`

`@app.teardown_request` se ejecuta **siempre** al final de la solicitud, incluso si ocurrió una excepción. Es ideal para tareas de limpieza:

```python
@app.teardown_request
def limpiar_al_final(excepcion=None):
    if excepcion:
        print(f'Error durante la solicitud: {excepcion}')
    
    # Cerrar conexiones, liberar recursos
    db_connection = getattr(g, 'db_connection', None)
    if db_connection is not None:
        db_connection.close()
        print('Conexión a la base de datos cerrada')
```

A diferencia de `after_request`, `teardown_request`:
- Recibe la excepción como argumento (o `None` si no hubo error).
- **No** recibe ni retorna la respuesta.
- Se ejecuta incluso si `after_request` falla.

## `teardown_appcontext`

Similar a `teardown_request`, pero se ejecuta al final del **contexto de la aplicación**:

```python
@app.teardown_appcontext
def cerrar_conexion_db(excepcion=None):
    db = g.pop('db', None)
    if db is not None:
        db.close()
```

## El Objeto `g`

El objeto `g` (de "global") es un **almacén temporal** por solicitud. Los datos almacenados en `g` están disponibles durante toda la solicitud actual y se destruyen automáticamente al finalizar:

```python
from flask import g

@app.before_request
def cargar_datos_solicitud():
    g.usuario = obtener_usuario_actual()
    g.idioma = request.headers.get('Accept-Language', 'es')
    g.inicio = time.time()

@app.route('/perfil')
def perfil():
    # g.usuario está disponible aquí
    return f'Perfil de {g.usuario.nombre}'

@app.after_request
def medir_tiempo(response):
    if hasattr(g, 'inicio'):
        duracion = time.time() - g.inicio
        app.logger.info(f'Solicitud procesada en {duracion:.3f}s')
    return response
```

### Patrón de Conexión Lazy

Un uso clásico de `g` es crear conexiones a recursos de forma perezosa:

```python
import sqlite3

def get_db():
    """Obtiene la conexión a la BD, creándola si no existe en esta solicitud."""
    if 'db' not in g:
        g.db = sqlite3.connect(app.config['DATABASE'])
        g.db.row_factory = sqlite3.Row
    return g.db

@app.teardown_appcontext
def cerrar_db(excepcion=None):
    db = g.pop('db', None)
    if db is not None:
        db.close()

@app.route('/usuarios')
def listar_usuarios():
    db = get_db()
    usuarios = db.execute('SELECT * FROM usuarios').fetchall()
    return render_template('usuarios.html', usuarios=usuarios)
```

## Contexto de Aplicación vs. Contexto de Solicitud

Flask maneja dos tipos de contextos que es crucial entender:

### Contexto de Solicitud (Request Context)

Se crea automáticamente cuando Flask recibe una solicitud HTTP. Proporciona acceso a:

- **`request`**: datos de la solicitud actual.
- **`session`**: datos de la sesión del usuario.

```python
@app.route('/info')
def info():
    # Dentro de una ruta, el contexto de solicitud existe automáticamente
    return f'Método: {request.method}, IP: {request.remote_addr}'
```

### Contexto de Aplicación (Application Context)

Proporciona acceso a:

- **`current_app`**: la instancia de la aplicación Flask.
- **`g`**: el almacén temporal por solicitud.

```python
from flask import current_app

@app.route('/config')
def ver_config():
    return f'Debug: {current_app.debug}, Secret: {current_app.config["SECRET_KEY"][:5]}...'
```

### Crear Contextos Manualmente

Fuera de una solicitud (scripts, tests, tareas), necesitas crear los contextos manualmente:

```python
# Contexto de aplicación
with app.app_context():
    # current_app y g están disponibles
    db.create_all()
    print(current_app.config['SQLALCHEMY_DATABASE_URI'])

# Contexto de solicitud (incluye el de aplicación)
with app.test_request_context('/ruta', method='POST'):
    # request, session, current_app y g están disponibles
    print(request.path)    # /ruta
    print(request.method)  # POST
```

## Hooks en Blueprints

Los Blueprints pueden tener sus propios hooks que solo se aplican a sus rutas:

```python
from flask import Blueprint

api_bp = Blueprint('api', __name__, url_prefix='/api')

@api_bp.before_request
def antes_de_api():
    """Solo se ejecuta para rutas del Blueprint 'api'."""
    api_key = request.headers.get('X-API-Key')
    if not api_key or api_key != app.config['API_KEY']:
        return jsonify({'error': 'API key inválida'}), 401

@api_bp.after_request
def despues_de_api(response):
    """Solo para rutas del Blueprint 'api'."""
    response.headers['X-API-Version'] = '1.0'
    return response
```

Para hooks que afecten a **todas** las rutas desde un Blueprint:

```python
@api_bp.before_app_request
def antes_de_toda_la_app():
    """Se ejecuta antes de CADA solicitud de la aplicación."""
    g.request_id = str(uuid.uuid4())
```

## Middleware WSGI Personalizado

Para un control aún más bajo, puedes crear middleware WSGI:

```python
class LoggingMiddleware:
    def __init__(self, app):
        self.app = app
    
    def __call__(self, environ, start_response):
        path = environ.get('PATH_INFO', '/')
        method = environ.get('REQUEST_METHOD', 'GET')
        print(f'[WSGI] {method} {path}')
        return self.app(environ, start_response)

# Aplicar el middleware
app.wsgi_app = LoggingMiddleware(app.wsgi_app)
```

## Ejemplo Integrado

Un ejemplo que combina todos los hooks:

```python
from flask import Flask, g, request, jsonify
import time
import uuid
import logging

app = Flask(__name__)
logger = logging.getLogger(__name__)

@app.before_request
def preparar_solicitud():
    g.request_id = str(uuid.uuid4())[:8]
    g.inicio = time.time()
    logger.info(f'[{g.request_id}] {request.method} {request.path} - Inicio')

@app.after_request
def finalizar_solicitud(response):
    duracion = time.time() - g.get('inicio', time.time())
    response.headers['X-Request-ID'] = g.get('request_id', 'unknown')
    response.headers['X-Response-Time'] = f'{duracion:.4f}s'
    logger.info(f'[{g.request_id}] Respuesta {response.status_code} en {duracion:.4f}s')
    return response

@app.teardown_request
def limpiar_solicitud(excepcion=None):
    if excepcion:
        logger.error(f'[{g.get("request_id")}] Error: {excepcion}')

@app.route('/')
def inicio():
    return jsonify({'mensaje': 'Hola', 'request_id': g.request_id})
```

## Ejercicio Práctico

Construye una aplicación Flask que demuestre el uso completo de hooks y middleware:

1. **`before_request`**: Asigna un ID único a cada solicitud y registra la hora de inicio. Si la ruta empieza por `/admin`, verifica que exista un header `X-Admin-Token`.
2. **`after_request`**: Agrega headers de seguridad (X-Content-Type-Options, X-Frame-Options) y un header `X-Request-Duration` con el tiempo de procesamiento.
3. **`teardown_request`**: Registra si la solicitud terminó con error o exitosamente.
4. **Objeto `g`**: Implementa un patrón de conexión lazy a una base de datos SQLite usando `g`.
5. **Blueprint con hooks propios**: Crea un Blueprint `api` con un `before_request` que valide un API key en el header.
6. **Middleware WSGI**: Crea un middleware que registre todas las solicitudes en un archivo de log.

```python
# Estructura esperada
@app.before_request
def preparar():
    g.request_id = str(uuid.uuid4())[:8]
    g.start_time = time.time()
    if request.path.startswith('/admin'):
        token = request.headers.get('X-Admin-Token')
        if token != 'mi-token-secreto':
            return jsonify({'error': 'Acceso denegado'}), 403
```

## Resumen

En esta lección exploraste el sistema de hooks de Flask y cómo intervenir en el ciclo de vida de cada solicitud. Aprendiste a usar `before_request` para preparar datos o verificar permisos, `after_request` para modificar respuestas y agregar headers, `teardown_request` para limpieza garantizada, y el objeto `g` como almacén temporal por solicitud. También comprendiste la diferencia entre el contexto de aplicación y el de solicitud, y cómo crear middleware WSGI personalizado. Estos conceptos te dan control total sobre cómo tu aplicación Flask procesa cada interacción.
