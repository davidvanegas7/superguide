---
title: "Seguridad en Django"
slug: "django-seguridad"
description: "Protege tu aplicación Django contra CSRF, XSS, inyección SQL, clickjacking y configura HTTPS, CSP y rate limiting"
---
# Seguridad en Django

Django incluye protecciones de seguridad integradas contra las vulnerabilidades web más comunes. Sin embargo, entender cómo funcionan y cómo configurarlas correctamente es esencial para construir aplicaciones realmente seguras. Esta lección cubre las principales amenazas y las defensas que Django proporciona.

## Protección CSRF (Cross-Site Request Forgery)

CSRF es un ataque donde un sitio malicioso envía peticiones en nombre de un usuario autenticado. Django protege contra esto automáticamente usando tokens CSRF.

### Cómo Funciona

```html
<!-- Django incluye un token único en cada formulario -->
<form method="post">
    {% csrf_token %}
    <!-- Genera: <input type="hidden" name="csrfmiddleware_token" value="..."> -->
    <input type="text" name="titulo">
    <button type="submit">Enviar</button>
</form>
```

```python
# settings.py - El middleware CSRF está activo por defecto
MIDDLEWARE = [
    'django.middleware.csrf.CsrfViewMiddleware',
    ...
]

# Configuración CSRF
CSRF_COOKIE_SECURE = True       # Solo enviar cookie CSRF por HTTPS
CSRF_COOKIE_HTTPONLY = True      # Evitar acceso desde JavaScript
CSRF_TRUSTED_ORIGINS = [
    'https://midominio.com',
    'https://www.midominio.com',
]
```

### CSRF en APIs y AJAX

```python
# Para vistas que no necesitan CSRF (APIs con autenticación por token)
from django.views.decorators.csrf import csrf_exempt

@csrf_exempt
def api_webhook(request):
    # Endpoints que reciben datos de servicios externos
    pass

# En JavaScript, enviar el token CSRF con AJAX
# fetch('/api/datos/', {
#     method: 'POST',
#     headers: {
#         'X-CSRFToken': getCookie('csrftoken'),
#         'Content-Type': 'application/json',
#     },
#     body: JSON.stringify(data)
# });
```

## Protección XSS (Cross-Site Scripting)

XSS ocurre cuando un atacante inyecta scripts maliciosos en páginas vistas por otros usuarios. Django escapa automáticamente las variables en los templates.

```html
<!-- Django escapa automáticamente el HTML -->
{{ variable }}  <!-- <script>alert('xss')</script> se muestra como texto -->

<!-- ⚠️ PELIGRO: |safe desactiva el escape -->
{{ variable|safe }}  <!-- Solo usar con contenido confiable -->

<!-- ✅ Marcar como seguro desde Python cuando es necesario -->
```

```python
from django.utils.html import escape, strip_tags
from django.utils.safestring import mark_safe

# Escapar HTML manualmente
texto_seguro = escape(texto_usuario)  # Convierte < > & en entidades HTML

# Eliminar todas las etiquetas HTML
texto_limpio = strip_tags(texto_usuario)

# Solo marcar como seguro contenido que TÚ generas
html_seguro = mark_safe(f'<strong>{escape(nombre)}</strong>')

# Validar en formularios
from django import forms

class ComentarioForm(forms.ModelForm):
    def clean_contenido(self):
        contenido = self.cleaned_data['contenido']
        # Eliminar etiquetas HTML peligrosas
        return strip_tags(contenido)
```

## Prevención de Inyección SQL

Django ORM previene automáticamente la inyección SQL usando consultas parametrizadas:

```python
# ✅ SEGURO: El ORM parametriza automáticamente
Articulo.objects.filter(titulo=titulo_usuario)
Articulo.objects.filter(categoria__nombre__icontains=busqueda)

# ✅ SEGURO: Raw queries con parámetros
Articulo.objects.raw(
    'SELECT * FROM articulos WHERE titulo = %s',
    [titulo_usuario]
)

# ❌ PELIGROSO: Nunca interpolar variables directamente
# Articulo.objects.raw(f"SELECT * FROM articulos WHERE titulo = '{titulo_usuario}'")

# ✅ SEGURO: Si necesitas queries complejas
from django.db import connection

with connection.cursor() as cursor:
    cursor.execute(
        "SELECT * FROM articulos WHERE categoria_id = %s AND publicado = %s",
        [categoria_id, True]
    )
    resultados = cursor.fetchall()

# ✅ SEGURO: extra() con params
Articulo.objects.extra(
    where=['titulo LIKE %s'],
    params=['%django%']
)
```

## Protección Contra Clickjacking

El clickjacking engaña al usuario para que haga clic en elementos ocultos. Django usa el header `X-Frame-Options`:

```python
# settings.py
MIDDLEWARE = [
    'django.middleware.clickjacking.XFrameOptionsMiddleware',
    ...
]

# Opciones:
X_FRAME_OPTIONS = 'DENY'        # No permitir en ningún iframe
X_FRAME_OPTIONS = 'SAMEORIGIN'  # Solo permitir desde el mismo dominio

# Excepción para vistas específicas
from django.views.decorators.clickjacking import xframe_options_exempt

@xframe_options_exempt
def pagina_embebible(request):
    """Vista que puede mostrarse en un iframe."""
    return render(request, 'embebible.html')
```

## HTTPS y Configuración SECURE_*

En producción, es fundamental forzar HTTPS y configurar headers de seguridad:

```python
# settings.py - SOLO EN PRODUCCIÓN

# Forzar HTTPS
SECURE_SSL_REDIRECT = True                # Redirigir HTTP a HTTPS
SECURE_PROXY_SSL_HEADER = ('HTTP_X_FORWARDED_PROTO', 'https')

# Cookies seguras
SESSION_COOKIE_SECURE = True              # Cookies de sesión solo por HTTPS
CSRF_COOKIE_SECURE = True                 # Cookie CSRF solo por HTTPS
SESSION_COOKIE_HTTPONLY = True             # No accesible desde JavaScript
SESSION_COOKIE_AGE = 1209600              # 2 semanas

# HSTS (HTTP Strict Transport Security)
SECURE_HSTS_SECONDS = 31536000            # 1 año
SECURE_HSTS_INCLUDE_SUBDOMAINS = True     # Incluir subdominios
SECURE_HSTS_PRELOAD = True                # Precargar en navegadores

# Otros headers de seguridad
SECURE_CONTENT_TYPE_NOSNIFF = True        # Prevenir MIME type sniffing
SECURE_BROWSER_XSS_FILTER = True          # Activar filtro XSS del navegador
SECURE_REFERRER_POLICY = 'strict-origin-when-cross-origin'
```

## Content Security Policy (CSP)

CSP controla qué recursos puede cargar el navegador, previniendo XSS y otros ataques:

```bash
pip install django-csp
```

```python
# settings.py
MIDDLEWARE = [
    'csp.middleware.CSPMiddleware',
    ...
]

# Configuración CSP
CSP_DEFAULT_SRC = ("'self'",)
CSP_SCRIPT_SRC = ("'self'", "https://cdn.jsdelivr.net")
CSP_STYLE_SRC = ("'self'", "'unsafe-inline'", "https://fonts.googleapis.com")
CSP_FONT_SRC = ("'self'", "https://fonts.gstatic.com")
CSP_IMG_SRC = ("'self'", "data:", "https://images.miapp.com")
CSP_CONNECT_SRC = ("'self'", "https://api.miapp.com")
CSP_FRAME_ANCESTORS = ("'none'",)  # Equivalente a X-Frame-Options: DENY

# Modo solo reporte para testing
CSP_REPORT_ONLY = True  # No bloquea, solo reporta violaciones
CSP_REPORT_URI = '/csp-report/'
```

## Rate Limiting

Limitar la cantidad de peticiones previene ataques de fuerza bruta y abuso:

```bash
pip install django-ratelimit
```

```python
from django_ratelimit.decorators import ratelimit

@ratelimit(key='ip', rate='5/m', block=True)
def login_view(request):
    """Máximo 5 intentos de login por minuto por IP."""
    if request.method == 'POST':
        # Procesar login
        pass
    return render(request, 'login.html')

@ratelimit(key='user', rate='100/h', block=True)
def api_datos(request):
    """Máximo 100 peticiones por hora por usuario."""
    pass

@ratelimit(key='ip', rate='3/m', method='POST', block=True)
def registrar(request):
    """Limitar registros a 3 por minuto por IP."""
    pass
```

### Rate Limiting Manual

```python
from django.core.cache import cache
from django.http import JsonResponse

def rate_limit_check(user_id, accion, limite=10, periodo=3600):
    """Verificación manual de rate limiting."""
    cache_key = f'ratelimit:{accion}:{user_id}'
    intentos = cache.get(cache_key, 0)

    if intentos >= limite:
        return False

    cache.set(cache_key, intentos + 1, periodo)
    return True

def enviar_mensaje(request):
    if not rate_limit_check(request.user.id, 'mensaje', limite=20, periodo=3600):
        return JsonResponse(
            {'error': 'Has excedido el límite de mensajes por hora'},
            status=429
        )
    # Procesar mensaje...
```

## Checklist de Seguridad para Producción

```bash
# Django incluye un comando para verificar la seguridad
python manage.py check --deploy
```

```python
# settings.py de producción
DEBUG = False
ALLOWED_HOSTS = ['midominio.com', 'www.midominio.com']
SECRET_KEY = os.environ.get('DJANGO_SECRET_KEY')  # Nunca en el código

# Contraseñas seguras
AUTH_PASSWORD_VALIDATORS = [
    {'NAME': 'django.contrib.auth.password_validation.UserAttributeSimilarityValidator'},
    {'NAME': 'django.contrib.auth.password_validation.MinimumLengthValidator',
     'OPTIONS': {'min_length': 10}},
    {'NAME': 'django.contrib.auth.password_validation.CommonPasswordValidator'},
    {'NAME': 'django.contrib.auth.password_validation.NumericPasswordValidator'},
]
```

## Ejercicio Práctico

Asegura una aplicación Django existente:

1. Configura todas las opciones `SECURE_*` para producción.
2. Implementa CSP para permitir solo recursos de tu dominio y CDNs confiables.
3. Agrega rate limiting al endpoint de login (máximo 5 intentos/minuto).
4. Implementa validación de entrada en formularios para prevenir XSS.
5. Ejecuta `python manage.py check --deploy` y resuelve todas las advertencias.

```python
# Ejemplo de configuración segura completa
SECURE_SSL_REDIRECT = True
SECURE_HSTS_SECONDS = 31536000
SESSION_COOKIE_SECURE = True
CSRF_COOKIE_SECURE = True
X_FRAME_OPTIONS = 'DENY'
SECURE_CONTENT_TYPE_NOSNIFF = True
```

## Resumen

Django ofrece defensas integradas contra las principales vulnerabilidades web. La protección **CSRF** valida tokens en formularios POST. El escape automático en templates previene **XSS**. El ORM previene la **inyección SQL** con consultas parametrizadas. El middleware de **clickjacking** controla el uso de iframes. En producción, configura **HTTPS** con las opciones `SECURE_*`, implementa **CSP** para controlar recursos permitidos, y agrega **rate limiting** para prevenir abusos. Siempre ejecuta `check --deploy` antes de lanzar a producción y mantén Django actualizado.
