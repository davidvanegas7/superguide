---
title: "Caché y Rendimiento"
slug: "flask-cache-rendimiento"
description: "Optimiza el rendimiento de tu aplicación Flask con estrategias de caché, compresión de respuestas, connection pooling y profiling."
---

# Caché y Rendimiento

A medida que tu aplicación crece, el rendimiento se convierte en una prioridad. Las consultas a la base de datos, las llamadas a APIs externas y los cálculos complejos pueden ralentizar la experiencia del usuario. El **caché** almacena resultados previamente calculados para servirlos instantáneamente. En esta lección aprenderás a implementar estrategias de caché y optimización en Flask.

## Flask-Caching: Configuración

**Flask-Caching** es la extensión estándar para caché en Flask. Soporta múltiples backends.

```python
from flask import Flask
from flask_caching import Cache

app = Flask(__name__)

# Configuración con Redis (recomendado para producción)
app.config['CACHE_TYPE'] = 'RedisCache'
app.config['CACHE_REDIS_HOST'] = 'localhost'
app.config['CACHE_REDIS_PORT'] = 6379
app.config['CACHE_REDIS_DB'] = 0
app.config['CACHE_DEFAULT_TIMEOUT'] = 300  # 5 minutos por defecto

cache = Cache(app)

# Alternativas de configuración:
# Para desarrollo (en memoria)
# app.config['CACHE_TYPE'] = 'SimpleCache'

# Para Memcached
# app.config['CACHE_TYPE'] = 'MemcachedCache'
# app.config['CACHE_MEMCACHED_SERVERS'] = ['127.0.0.1:11211']

# Para filesystem
# app.config['CACHE_TYPE'] = 'FileSystemCache'
# app.config['CACHE_DIR'] = '/tmp/flask-cache'
```

## Decorador @cache.cached

El decorador `@cache.cached` almacena la respuesta completa de una ruta.

```python
@app.route('/productos')
@cache.cached(timeout=120)  # Cache por 2 minutos
def listar_productos():
    """Esta consulta pesada solo se ejecuta cada 2 minutos."""
    productos = Producto.query.join(Categoria).all()
    return jsonify([p.to_dict() for p in productos])

@app.route('/producto/<int:producto_id>')
@cache.cached(timeout=60)
def obtener_producto(producto_id):
    """Cache individual por cada producto (la URL es la clave)."""
    producto = Producto.query.get_or_404(producto_id)
    return jsonify(producto.to_dict())
```

### Cache con clave personalizada

```python
def clave_por_usuario():
    """Genera una clave de caché basada en el usuario y los query params."""
    user_id = get_current_user_id() or 'anonimo'
    args = request.args.to_dict()
    return f"vista_{user_id}_{hash(frozenset(args.items()))}"

@app.route('/dashboard')
@cache.cached(timeout=300, key_prefix=clave_por_usuario)
def dashboard():
    """Caché diferente para cada usuario."""
    datos = calcular_estadisticas(current_user.id)
    return jsonify(datos)
```

## @cache.memoize para Funciones

`memoize` cachea el resultado de una función basándose en sus argumentos:

```python
@cache.memoize(timeout=600)  # Cache por 10 minutos
def obtener_estadisticas(user_id, periodo='mensual'):
    """Cachea el resultado según los argumentos recibidos."""
    # Consulta costosa a la BD
    total_ventas = db.session.query(
        func.sum(Venta.monto)
    ).filter(
        Venta.usuario_id == user_id,
        Venta.fecha >= calcular_inicio_periodo(periodo)
    ).scalar()

    total_pedidos = Pedido.query.filter_by(
        usuario_id=user_id
    ).count()

    return {
        'ventas': float(total_ventas or 0),
        'pedidos': total_pedidos,
        'promedio': float(total_ventas or 0) / max(total_pedidos, 1)
    }

# Uso en una ruta
@app.route('/api/estadisticas/<int:user_id>')
def api_estadisticas(user_id):
    periodo = request.args.get('periodo', 'mensual')
    datos = obtener_estadisticas(user_id, periodo)
    return jsonify(datos)

# Invalidar el caché cuando los datos cambian
@app.route('/api/ventas', methods=['POST'])
def registrar_venta():
    data = request.get_json()
    # ... guardar venta ...

    # Borrar el caché de estadísticas del usuario
    cache.delete_memoized(obtener_estadisticas, data['user_id'])

    return jsonify({"ok": True}), 201
```

## Invalidación del Caché

Una de las partes más importantes de una estrategia de caché es saber cuándo invalidar los datos.

```python
# Eliminar una clave específica
cache.delete('vista_/productos')

# Eliminar todas las versiones memoizadas de una función
cache.delete_memoized(obtener_estadisticas)

# Eliminar memoize con argumentos específicos
cache.delete_memoized(obtener_estadisticas, user_id=42)

# Limpiar todo el caché
cache.clear()

# Patrón: invalidar caché al modificar datos
def invalidar_cache_producto(producto_id):
    """Invalida todos los cachés relacionados con un producto."""
    cache.delete(f'vista_/producto/{producto_id}')
    cache.delete('vista_/productos')
    cache.delete_memoized(obtener_estadisticas)

@app.route('/api/productos/<int:pid>', methods=['PUT'])
def actualizar_producto(pid):
    # ... actualizar producto ...
    invalidar_cache_producto(pid)
    return jsonify({"actualizado": True})
```

## Compresión de Respuestas

Comprimir las respuestas reduce el tamaño de la transferencia significativamente.

```python
from flask_compress import Compress

app = Flask(__name__)
app.config['COMPRESS_MIMETYPES'] = [
    'text/html', 'text/css', 'text/xml',
    'application/json', 'application/javascript'
]
app.config['COMPRESS_LEVEL'] = 6       # Nivel de compresión (1-9)
app.config['COMPRESS_MIN_SIZE'] = 500   # Mínimo 500 bytes para comprimir

Compress(app)
# Automáticamente comprime respuestas con gzip cuando el cliente lo soporta
```

## Lazy Loading y Paginación

Evita cargar todos los datos de una vez:

```python
@app.route('/api/articulos')
@cache.cached(timeout=120, query_string=True)  # Cache por query params
def listar_articulos():
    """Paginación eficiente con caché."""
    pagina = request.args.get('pagina', 1, type=int)
    por_pagina = request.args.get('por_pagina', 20, type=int)

    # Limitar el tamaño de página
    por_pagina = min(por_pagina, 100)

    # Query paginada (solo carga lo necesario)
    paginacion = Articulo.query.order_by(
        Articulo.fecha.desc()
    ).paginate(
        page=pagina,
        per_page=por_pagina,
        error_out=False
    )

    return jsonify({
        'articulos': [a.to_dict() for a in paginacion.items],
        'total': paginacion.total,
        'paginas': paginacion.pages,
        'pagina_actual': paginacion.page,
        'tiene_siguiente': paginacion.has_next,
        'tiene_anterior': paginacion.has_prev,
    })
```

## Connection Pooling

Reutilizar conexiones a la base de datos mejora el rendimiento drásticamente:

```python
from sqlalchemy import create_engine
from sqlalchemy.pool import QueuePool

# Configuración del pool de conexiones
app.config['SQLALCHEMY_ENGINE_OPTIONS'] = {
    'pool_size': 10,          # Número de conexiones permanentes
    'max_overflow': 20,       # Conexiones adicionales bajo carga
    'pool_timeout': 30,       # Espera máxima por una conexión (segundos)
    'pool_recycle': 1800,     # Reciclar conexiones cada 30 min
    'pool_pre_ping': True,    # Verificar conexión antes de usarla
}

# Monitorear el pool
@app.route('/admin/pool-stats')
def pool_stats():
    """Estadísticas del pool de conexiones."""
    pool = db.engine.pool
    return jsonify({
        'tamaño': pool.size(),
        'conexiones_activas': pool.checkedin(),
        'en_uso': pool.checkedout(),
        'overflow': pool.overflow(),
    })
```

## Profiling: Identificar Cuellos de Botella

Antes de optimizar, necesitas medir:

```python
# Middleware para medir tiempos de respuesta
import time

@app.before_request
def iniciar_temporizador():
    request._inicio = time.time()

@app.after_request
def registrar_tiempo(response):
    if hasattr(request, '_inicio'):
        duracion = time.time() - request._inicio
        response.headers['X-Response-Time'] = f"{duracion:.4f}s"

        # Registrar peticiones lentas
        if duracion > 1.0:  # Más de 1 segundo
            app.logger.warning(
                f"Petición lenta: {request.method} {request.path} "
                f"({duracion:.2f}s)"
            )
    return response
```

### Profiling con Flask-DebugToolbar (desarrollo)

```python
# Solo para desarrollo
from flask_debugtoolbar import DebugToolbarExtension

app.config['DEBUG_TB_INTERCEPT_REDIRECTS'] = False
app.config['DEBUG_TB_PROFILER_ENABLED'] = True
toolbar = DebugToolbarExtension(app)
```

### Profiling de Consultas SQL

```python
# Registrar consultas lentas de SQLAlchemy
app.config['SQLALCHEMY_RECORD_QUERIES'] = True

@app.after_request
def consultas_lentas(response):
    from flask_sqlalchemy import get_debug_queries
    for query in get_debug_queries():
        if query.duration >= 0.5:  # Más de 500ms
            app.logger.warning(
                f"Consulta lenta ({query.duration:.2f}s): "
                f"{query.statement}\n"
                f"Parámetros: {query.parameters}\n"
                f"Contexto: {query.context}"
            )
    return response
```

## Ejercicio Práctico

Optimiza una API de catálogo de productos:

1. **Configura Flask-Caching** con Redis como backend.
2. **Cachea la lista de productos** por 5 minutos; invalida al crear/editar/eliminar un producto.
3. **Cachea las estadísticas** de ventas con `@cache.memoize`, diferenciando por usuario y periodo.
4. **Implementa paginación** con caché que considere los parámetros de la URL.
5. **Configura connection pooling** con 10 conexiones permanentes y hasta 20 de overflow.
6. **Agrega compresión** de respuestas con Flask-Compress.
7. **Implementa el middleware de profiling** que registre peticiones que tarden más de 500ms.
8. Mide el tiempo de respuesta antes y después de las optimizaciones.

## Resumen

- **Flask-Caching** soporta múltiples backends: Redis, Memcached, memoria, filesystem.
- `@cache.cached` cachea respuestas de rutas completas por la URL.
- `@cache.memoize` cachea resultados de funciones basándose en sus argumentos.
- La **invalidación del caché** es crucial: borrar datos obsoletos al modificar la fuente.
- **Flask-Compress** reduce el tamaño de las respuestas con gzip automáticamente.
- El **connection pooling** reutiliza conexiones a la BD para evitar sobrecarga.
- Usa **profiling** para identificar cuellos de botella antes de optimizar.
- La **paginación** evita cargar grandes volúmenes de datos innecesariamente.
