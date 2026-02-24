---
title: "Middleware"
slug: "django-middleware"
description: "Comprende la cadena de middleware de Django, crea middleware personalizado y configura seguridad y CORS."
---

# Middleware

El middleware en Django es un sistema de hooks que procesa las solicitudes y respuestas de forma global. Cada middleware es un componente que se ejecuta en cada solicitud entrante y cada respuesta saliente, formando una cadena ordenada. Es ideal para funcionalidades transversales como autenticación, seguridad, logging y procesamiento de cabeceras.

## Cómo funciona la cadena de middleware

El middleware de Django funciona como una "cebolla": cada capa envuelve a la siguiente. Las solicitudes pasan de arriba hacia abajo, y las respuestas de abajo hacia arriba:

```
Solicitud → Middleware 1 → Middleware 2 → Middleware 3 → Vista
Respuesta ← Middleware 1 ← Middleware 2 ← Middleware 3 ← Vista
```

La configuración se define en `settings.py`:

```python
# settings.py
MIDDLEWARE = [
    'django.middleware.security.SecurityMiddleware',       # Seguridad HTTPS
    'django.contrib.sessions.middleware.SessionMiddleware', # Sesiones
    'django.middleware.common.CommonMiddleware',            # Funcionalidad común
    'django.middleware.csrf.CsrfViewMiddleware',           # Protección CSRF
    'django.contrib.auth.middleware.AuthenticationMiddleware', # Autenticación
    'django.contrib.messages.middleware.MessageMiddleware', # Mensajes flash
    'django.middleware.clickjacking.XFrameOptionsMiddleware', # Protección clickjacking
]
```

**El orden importa.** Cada middleware puede depender de los anteriores. Por ejemplo, `AuthenticationMiddleware` necesita `SessionMiddleware` antes.

## Middleware incluido en Django

### SecurityMiddleware

Gestiona cabeceras de seguridad y redirecciones HTTPS:

```python
# settings.py

# Redirigir HTTP a HTTPS
SECURE_SSL_REDIRECT = True

# Cabecera HSTS (HTTP Strict Transport Security)
SECURE_HSTS_SECONDS = 31536000        # 1 año
SECURE_HSTS_INCLUDE_SUBDOMAINS = True
SECURE_HSTS_PRELOAD = True

# Prevenir sniffing de tipo de contenido
SECURE_CONTENT_TYPE_NOSNIFF = True

# Cookies seguras
SESSION_COOKIE_SECURE = True
CSRF_COOKIE_SECURE = True
```

### SessionMiddleware

Habilita el sistema de sesiones:

```python
# En vistas, acceder a la sesión
def mi_vista(request):
    # Guardar datos en sesión
    request.session['carrito'] = [1, 2, 3]
    request.session['idioma'] = 'es'

    # Leer datos de sesión
    carrito = request.session.get('carrito', [])

    # Eliminar un valor
    del request.session['carrito']

    # Limpiar toda la sesión
    request.session.flush()
```

### CsrfViewMiddleware

Verifica que las solicitudes POST incluyan un token CSRF válido. Protege contra ataques Cross-Site Request Forgery.

### CommonMiddleware

Funcionalidades generales como:
- Agregar barras finales a URLs (`APPEND_SLASH = True`).
- Enviar cabecera `Content-Length`.
- Manejar URLs prohibidas (`DISALLOWED_USER_AGENTS`).

## Crear middleware personalizado

### Sintaxis moderna (callable)

La forma recomendada de crear middleware en Django moderno:

```python
# mi_app/middleware.py

class RequestTimingMiddleware:
    """Middleware que mide el tiempo de procesamiento de cada solicitud."""

    def __init__(self, get_response):
        self.get_response = get_response
        # Código de inicialización (se ejecuta una sola vez al arrancar)

    def __call__(self, request):
        # Código que se ejecuta ANTES de la vista (y antes de otros middleware)
        import time
        inicio = time.time()

        # Pasar la solicitud al siguiente middleware o vista
        response = self.get_response(request)

        # Código que se ejecuta DESPUÉS de la vista
        duracion = time.time() - inicio
        response['X-Request-Duration'] = f'{duracion:.4f}s'

        if duracion > 1.0:
            import logging
            logger = logging.getLogger('django')
            logger.warning(
                f'Solicitud lenta: {request.path} tardó {duracion:.2f}s'
            )

        return response
```

### Middleware con process_request y process_response

Usando hooks específicos para mayor control:

```python
class MantenimientoMiddleware:
    """Middleware que muestra página de mantenimiento."""

    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        response = self.get_response(request)
        return response

    def process_request(self, request):
        """Se ejecuta antes de que Django determine qué vista usar."""
        from django.conf import settings
        from django.http import HttpResponse

        if getattr(settings, 'MODO_MANTENIMIENTO', False):
            # Permitir acceso al admin
            if request.path.startswith('/admin/'):
                return None

            return HttpResponse(
                '<h1>Sitio en mantenimiento</h1>'
                '<p>Volveremos pronto.</p>',
                status=503,
            )
        return None

    def process_response(self, request, response):
        """Se ejecuta después de que la vista genera la respuesta."""
        return response
```

### Middleware de logging

```python
import logging
import json
from datetime import datetime

logger = logging.getLogger('solicitudes')


class LoggingMiddleware:
    """Registra información detallada de cada solicitud."""

    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        # Registrar solicitud entrante
        datos_solicitud = {
            'timestamp': datetime.now().isoformat(),
            'metodo': request.method,
            'ruta': request.path,
            'usuario': str(request.user) if hasattr(request, 'user') else 'anónimo',
            'ip': self.obtener_ip(request),
            'user_agent': request.META.get('HTTP_USER_AGENT', ''),
        }

        response = self.get_response(request)

        # Registrar respuesta
        datos_solicitud['status_code'] = response.status_code
        logger.info(json.dumps(datos_solicitud))

        return response

    def obtener_ip(self, request):
        x_forwarded = request.META.get('HTTP_X_FORWARDED_FOR')
        if x_forwarded:
            return x_forwarded.split(',')[0].strip()
        return request.META.get('REMOTE_ADDR')
```

### Middleware de control de acceso por IP

```python
from django.http import HttpResponseForbidden

class RestriccionIPMiddleware:
    """Bloquea solicitudes de IPs no permitidas para rutas sensibles."""

    IPS_PERMITIDAS = ['127.0.0.1', '10.0.0.1']
    RUTAS_PROTEGIDAS = ['/admin/', '/api/interno/']

    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        ip_cliente = request.META.get('REMOTE_ADDR')

        for ruta in self.RUTAS_PROTEGIDAS:
            if request.path.startswith(ruta):
                if ip_cliente not in self.IPS_PERMITIDAS:
                    return HttpResponseForbidden(
                        'Acceso denegado desde tu dirección IP.'
                    )

        return self.get_response(request)
```

## Registrar middleware personalizado

```python
# settings.py
MIDDLEWARE = [
    'django.middleware.security.SecurityMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',

    # Middleware personalizado
    'mi_app.middleware.RequestTimingMiddleware',
    'mi_app.middleware.LoggingMiddleware',

    'django.middleware.common.CommonMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
    'django.middleware.clickjacking.XFrameOptionsMiddleware',

    # Middleware que necesita ir al final
    'mi_app.middleware.MantenimientoMiddleware',
]
```

## Middleware de excepciones

```python
class ManejadorExcepcionesMiddleware:
    """Captura excepciones no manejadas y envía alertas."""

    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        return self.get_response(request)

    def process_exception(self, request, exception):
        """Se ejecuta cuando una vista lanza una excepción no capturada."""
        import logging
        logger = logging.getLogger('errores')

        logger.error(
            f'Excepción no manejada en {request.path}: {exception}',
            exc_info=True,
            extra={
                'request': request,
                'usuario': str(request.user),
            }
        )

        # Retornar None para que Django maneje la excepción normalmente
        # O retornar un HttpResponse personalizado
        return None
```

## CORS (Cross-Origin Resource Sharing)

Para permitir solicitudes desde otros dominios (necesario para APIs), usa `django-cors-headers`:

```bash
pip install django-cors-headers
```

```python
# settings.py
INSTALLED_APPS = [
    # ...
    'corsheaders',
]

MIDDLEWARE = [
    # Debe ir lo más arriba posible
    'corsheaders.middleware.CorsMiddleware',
    'django.middleware.common.CommonMiddleware',
    # ...
]

# Permitir todos los orígenes (solo para desarrollo)
CORS_ALLOW_ALL_ORIGINS = True

# O especificar orígenes permitidos (producción)
CORS_ALLOWED_ORIGINS = [
    'https://mi-frontend.com',
    'https://app.mi-dominio.com',
    'http://localhost:3000',
]

# Permitir credenciales (cookies, headers de autenticación)
CORS_ALLOW_CREDENTIALS = True

# Cabeceras adicionales permitidas
CORS_ALLOW_HEADERS = [
    'accept',
    'authorization',
    'content-type',
    'x-csrftoken',
    'x-requested-with',
]

# Métodos HTTP permitidos
CORS_ALLOW_METHODS = [
    'DELETE',
    'GET',
    'OPTIONS',
    'PATCH',
    'POST',
    'PUT',
]
```

## Ejercicio Práctico

1. Crea un middleware `VisitasMiddleware` que cuente las visitas totales a cada ruta y las almacene en un diccionario en memoria.
2. Crea un middleware `IdiomaMiddleware` que detecte el idioma preferido del navegador desde la cabecera `Accept-Language` y lo almacene en `request.idioma`.
3. Implementa un middleware que limite las solicitudes a 100 por minuto por IP (rate limiting básico).
4. Instala y configura `django-cors-headers` para permitir solicitudes desde `http://localhost:3000`.
5. Crea un middleware que agregue cabeceras de seguridad personalizadas a todas las respuestas.

## Resumen

El middleware de Django es un mecanismo potente para procesar solicitudes y respuestas de forma global. Aprendiste cómo funciona la cadena de middleware, exploraste los middleware integrados para seguridad y sesiones, creaste middleware personalizado para timing, logging y control de acceso, y configuraste CORS para permitir solicitudes cross-origin. El middleware es esencial para implementar funcionalidades transversales sin modificar cada vista individualmente.
