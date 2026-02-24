---
title: "Introducción a Django"
slug: "django-introduccion"
description: "Primeros pasos con Django: instalación, creación de proyectos, estructura de archivos y ejecución del servidor de desarrollo."
---

# Introducción a Django

Django es un framework web de alto nivel escrito en Python que fomenta el desarrollo rápido y el diseño limpio y pragmático. Fue creado por desarrolladores experimentados y se encarga de gran parte de la complejidad del desarrollo web, permitiéndote concentrarte en escribir tu aplicación sin tener que reinventar la rueda. Es gratuito, de código abierto y cuenta con una comunidad activa a nivel mundial.

## ¿Por qué Django?

Django sigue el patrón de diseño **MTV** (Model-Template-View), una variación del conocido MVC. Entre sus principales ventajas encontramos:

- **Baterías incluidas**: ORM, sistema de plantillas, autenticación, panel de administración y más, todo listo para usar.
- **Seguridad**: protección integrada contra inyección SQL, XSS, CSRF y clickjacking.
- **Escalabilidad**: utilizado por Instagram, Pinterest, Disqus y Mozilla.
- **Documentación excelente**: una de las mejores documentaciones de cualquier framework.
- **Comunidad activa**: miles de paquetes de terceros disponibles en PyPI.

## Instalación

Antes de comenzar, necesitas Python 3.10 o superior instalado. Se recomienda usar un entorno virtual para aislar las dependencias de cada proyecto.

```bash
# Crear un entorno virtual
python3 -m venv mi_entorno

# Activar el entorno virtual
# En Linux/macOS:
source mi_entorno/bin/activate
# En Windows:
mi_entorno\Scripts\activate

# Instalar Django
pip install django

# Verificar la instalación
python -m django --version
```

## Crear un proyecto con django-admin

Django incluye la herramienta de línea de comandos `django-admin` para tareas administrativas. Para crear un nuevo proyecto:

```bash
django-admin startproject mi_proyecto
```

Esto genera la siguiente estructura de archivos:

```
mi_proyecto/
├── manage.py
└── mi_proyecto/
    ├── __init__.py
    ├── settings.py
    ├── urls.py
    ├── asgi.py
    └── wsgi.py
```

### Descripción de cada archivo

- **`manage.py`**: script de utilidad que permite interactuar con el proyecto Django. Es un envoltorio de `django-admin` que configura automáticamente la variable de entorno `DJANGO_SETTINGS_MODULE`.
- **`__init__.py`**: indica a Python que este directorio debe tratarse como un paquete.
- **`settings.py`**: configuración central del proyecto (base de datos, apps instaladas, middleware, etc.).
- **`urls.py`**: define las rutas URL del proyecto. Es el "índice" de tu sitio.
- **`asgi.py`**: punto de entrada para servidores compatibles con ASGI (soporte asíncrono).
- **`wsgi.py`**: punto de entrada para servidores compatibles con WSGI (despliegue en producción).

## Entendiendo settings.py

El archivo `settings.py` es el corazón de la configuración. Veamos las opciones más importantes:

```python
# settings.py

# Clave secreta para firmas criptográficas. ¡Nunca la compartas!
SECRET_KEY = 'django-insecure-cambia-esto-en-produccion'

# Modo de depuración. NUNCA True en producción.
DEBUG = True

# Hosts permitidos para servir el proyecto
ALLOWED_HOSTS = []

# Aplicaciones instaladas
INSTALLED_APPS = [
    'django.contrib.admin',        # Panel de administración
    'django.contrib.auth',         # Sistema de autenticación
    'django.contrib.contenttypes', # Framework de tipos de contenido
    'django.contrib.sessions',     # Framework de sesiones
    'django.contrib.messages',     # Framework de mensajes
    'django.contrib.staticfiles',  # Gestión de archivos estáticos
]

# Configuración de la base de datos (SQLite por defecto)
DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.sqlite3',
        'NAME': BASE_DIR / 'db.sqlite3',
    }
}

# Idioma y zona horaria
LANGUAGE_CODE = 'es-es'
TIME_ZONE = 'America/Mexico_City'
USE_I18N = True
USE_TZ = True
```

## Crear una aplicación

En Django, un proyecto se compone de múltiples **aplicaciones**. Cada aplicación encapsula una funcionalidad específica y es reutilizable.

```bash
# Dentro del directorio del proyecto
python manage.py startapp blog
```

Esto genera:

```
blog/
├── __init__.py
├── admin.py
├── apps.py
├── migrations/
│   └── __init__.py
├── models.py
├── tests.py
└── views.py
```

Después de crear la aplicación, debes registrarla en `settings.py`:

```python
INSTALLED_APPS = [
    # ... apps de Django
    'blog',  # Tu nueva aplicación
]
```

## El archivo manage.py

`manage.py` es tu herramienta principal para interactuar con el proyecto. Algunos comandos esenciales:

```bash
# Ejecutar el servidor de desarrollo
python manage.py runserver

# Ejecutar en un puerto específico
python manage.py runserver 8080

# Crear migraciones de base de datos
python manage.py makemigrations

# Aplicar migraciones
python manage.py migrate

# Crear un superusuario para el admin
python manage.py createsuperuser

# Abrir la shell interactiva de Django
python manage.py shell

# Recopilar archivos estáticos
python manage.py collectstatic
```

## Ejecutar el servidor de desarrollo

Para ver tu proyecto en funcionamiento:

```bash
python manage.py runserver
```

Verás una salida similar a:

```
Watching for file changes with StatReloader
Performing system checks...

System check identified no issues (0 silenced).
February 23, 2026 - 10:00:00
Django version 5.1, using settings 'mi_proyecto.settings'
Starting development server at http://127.0.0.1:8000/
Quit the server with CONTROL-C.
```

Abre tu navegador en `http://127.0.0.1:8000/` y verás la página de bienvenida de Django.

## Tu primera vista

Creemos una vista simple para verificar que todo funciona:

```python
# blog/views.py
from django.http import HttpResponse

def inicio(request):
    return HttpResponse("<h1>¡Bienvenido a mi blog con Django!</h1>")
```

```python
# blog/urls.py (crea este archivo)
from django.urls import path
from . import views

urlpatterns = [
    path('', views.inicio, name='inicio'),
]
```

```python
# mi_proyecto/urls.py
from django.contrib import admin
from django.urls import path, include

urlpatterns = [
    path('admin/', admin.site.urls),
    path('', include('blog.urls')),
]
```

Ahora al visitar `http://127.0.0.1:8000/` verás tu mensaje de bienvenida.

## Ejercicio Práctico

1. Crea un entorno virtual e instala Django.
2. Crea un proyecto llamado `tienda_online`.
3. Dentro del proyecto, crea una aplicación llamada `productos`.
4. Registra la aplicación en `settings.py`.
5. Crea una vista que devuelva un saludo con el nombre de tu tienda.
6. Configura las URLs para que la vista se muestre en la ruta raíz (`/`).
7. Cambia el idioma a español (`es-es`) y la zona horaria a tu zona local.
8. Ejecuta el servidor y verifica que todo funcione correctamente.

## Resumen

En esta lección aprendiste los fundamentos de Django: qué es, por qué usarlo, cómo instalarlo y cómo crear un proyecto desde cero. Exploraste la estructura de archivos generada por `django-admin startproject`, entendiste el rol de `settings.py` y `manage.py`, y creaste tu primera vista funcional. Estos conceptos son la base sobre la que construirás aplicaciones web completas con Django en las siguientes lecciones.
