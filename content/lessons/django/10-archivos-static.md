---
title: "Archivos Estáticos y Media"
slug: "django-archivos-static"
description: "Configura y gestiona archivos estáticos (CSS, JS, imágenes) y archivos subidos por usuarios en Django."
---

# Archivos Estáticos y Media

En Django, los archivos se dividen en dos categorías: **estáticos** (CSS, JavaScript, imágenes del sitio) y **media** (archivos subidos por los usuarios). Cada tipo tiene su configuración, almacenamiento y forma de servir. Entender esta distinción es fundamental para desplegar correctamente tu aplicación.

## Archivos estáticos

Los archivos estáticos son los recursos que forman parte del código fuente de tu aplicación: hojas de estilo, scripts, iconos, fuentes e imágenes de diseño.

### Configuración básica

```python
# settings.py

# URL pública para acceder a los archivos estáticos
STATIC_URL = '/static/'

# Directorios adicionales donde Django buscará archivos estáticos
STATICFILES_DIRS = [
    BASE_DIR / 'static',          # Directorio global del proyecto
    BASE_DIR / 'assets',          # Otro directorio (opcional)
]

# Directorio donde collectstatic recopilará todos los archivos
# para servir en producción
STATIC_ROOT = BASE_DIR / 'staticfiles'

# Finders: motores de búsqueda de archivos estáticos
STATICFILES_FINDERS = [
    'django.contrib.staticfiles.finders.FileSystemFinder',   # Busca en STATICFILES_DIRS
    'django.contrib.staticfiles.finders.AppDirectoriesFinder', # Busca en app/static/
]
```

### Estructura de directorios

```
mi_proyecto/
├── static/                    # Archivos estáticos globales
│   ├── css/
│   │   └── estilos.css
│   ├── js/
│   │   └── main.js
│   └── img/
│       └── logo.png
├── blog/
│   └── static/
│       └── blog/              # Namespace para evitar conflictos
│           ├── css/
│           │   └── blog.css
│           └── js/
│               └── blog.js
└── tienda/
    └── static/
        └── tienda/
            └── css/
                └── tienda.css
```

Es crucial usar el namespace (ejemplo: `blog/static/blog/`) para evitar que archivos con el mismo nombre de diferentes aplicaciones se sobreescriban.

### Usar archivos estáticos en templates

```html
{% load static %}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Sitio</title>

    <!-- CSS -->
    <link rel="stylesheet" href="{% static 'css/estilos.css' %}">
    <link rel="stylesheet" href="{% static 'blog/css/blog.css' %}">
</head>
<body>
    <!-- Imágenes -->
    <img src="{% static 'img/logo.png' %}" alt="Logo">

    <!-- Favicon -->
    <link rel="icon" href="{% static 'img/favicon.ico' %}">

    <!-- JavaScript -->
    <script src="{% static 'js/main.js' %}"></script>

    <!-- Archivos de una app específica -->
    <script src="{% static 'blog/js/blog.js' %}"></script>
</body>
</html>
```

### Variables en CSS con archivos estáticos

```html
<!-- Si necesitas URLs dinámicas en CSS inline -->
<style>
    .hero {
        background-image: url('{% static "img/hero-bg.jpg" %}');
    }
</style>
```

## collectstatic

En producción, Django no sirve archivos estáticos directamente. El comando `collectstatic` recopila todos los archivos estáticos en un solo directorio:

```bash
# Recopilar archivos estáticos
python manage.py collectstatic

# Resultado:
# 120 static files copied to '/ruta/al/proyecto/staticfiles'.
```

Este comando:
1. Busca en todos los directorios de `STATICFILES_DIRS`.
2. Busca en las carpetas `static/` de cada app instalada.
3. Copia todo al directorio definido en `STATIC_ROOT`.

```bash
# Opciones útiles
python manage.py collectstatic --noinput     # Sin confirmación
python manage.py collectstatic --clear        # Limpiar antes de copiar
python manage.py findstatic css/estilos.css  # Encontrar un archivo específico
```

### Servir archivos estáticos en producción

En producción, usa un servidor web como **Nginx** o **WhiteNoise**:

```python
# Opción 1: WhiteNoise (simple, integrado en Django)
# pip install whitenoise

MIDDLEWARE = [
    'django.middleware.security.SecurityMiddleware',
    'whitenoise.middleware.WhiteNoiseMiddleware',  # Después de SecurityMiddleware
    # ... resto del middleware
]

# Compresión y caché automática
STATICFILES_STORAGE = 'whitenoise.storage.CompressedManifestStaticFilesStorage'
```

```nginx
# Opción 2: Configuración de Nginx
server {
    listen 80;
    server_name mi-sitio.com;

    location /static/ {
        alias /ruta/al/proyecto/staticfiles/;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location /media/ {
        alias /ruta/al/proyecto/media/;
    }

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

## Archivos media (subidos por usuarios)

Los archivos media son los que suben los usuarios: fotos de perfil, documentos, imágenes de productos, etc.

### Configuración

```python
# settings.py

# URL pública para acceder a los archivos media
MEDIA_URL = '/media/'

# Directorio donde se almacenarán los archivos subidos
MEDIA_ROOT = BASE_DIR / 'media'
```

### Servir archivos media en desarrollo

En desarrollo, necesitas agregar las URLs de media manualmente:

```python
# mi_proyecto/urls.py
from django.conf import settings
from django.conf.urls.static import static
from django.contrib import admin
from django.urls import path, include

urlpatterns = [
    path('admin/', admin.site.urls),
    path('', include('blog.urls')),
]

# Solo en modo DEBUG
if settings.DEBUG:
    urlpatterns += static(
        settings.MEDIA_URL,
        document_root=settings.MEDIA_ROOT,
    )
```

## FileField e ImageField

### FileField

Para manejar archivos genéricos:

```python
# blog/models.py
from django.db import models

class Documento(models.Model):
    titulo = models.CharField(max_length=200)
    archivo = models.FileField(
        upload_to='documentos/%Y/%m/',    # Organización por fecha
        max_length=255,
    )
    fecha_subida = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        return self.titulo

    @property
    def nombre_archivo(self):
        import os
        return os.path.basename(self.archivo.name)

    @property
    def tamaño_mb(self):
        try:
            return self.archivo.size / (1024 * 1024)
        except FileNotFoundError:
            return 0
```

### ImageField

Para imágenes (requiere la librería **Pillow**):

```bash
pip install Pillow
```

```python
class Producto(models.Model):
    nombre = models.CharField(max_length=200)
    precio = models.DecimalField(max_digits=10, decimal_places=2)
    imagen_principal = models.ImageField(
        upload_to='productos/',
        blank=True,
        null=True,
    )

    def __str__(self):
        return self.nombre


class ImagenProducto(models.Model):
    producto = models.ForeignKey(
        Producto,
        on_delete=models.CASCADE,
        related_name='imagenes',
    )
    imagen = models.ImageField(upload_to='productos/galeria/')
    titulo = models.CharField(max_length=100, blank=True)
    orden = models.PositiveIntegerField(default=0)

    class Meta:
        ordering = ['orden']

    def __str__(self):
        return f'Imagen de {self.producto.nombre}'
```

### upload_to dinámico

```python
def ruta_avatar(instance, filename):
    """Genera una ruta personalizada para cada usuario."""
    import os
    extension = os.path.splitext(filename)[1]
    return f'avatares/usuario_{instance.usuario.id}{extension}'


class Perfil(models.Model):
    usuario = models.OneToOneField('auth.User', on_delete=models.CASCADE)
    avatar = models.ImageField(
        upload_to=ruta_avatar,
        blank=True,
    )
```

### Formulario con archivos

```python
# blog/forms.py
class DocumentoForm(forms.ModelForm):
    class Meta:
        model = Documento
        fields = ['titulo', 'archivo']

    def clean_archivo(self):
        archivo = self.cleaned_data.get('archivo')
        if archivo:
            # Validar tamaño (máximo 10 MB)
            if archivo.size > 10 * 1024 * 1024:
                raise forms.ValidationError(
                    'El archivo no puede superar los 10 MB.'
                )
            # Validar extensión
            extensiones_permitidas = ['.pdf', '.docx', '.xlsx']
            import os
            ext = os.path.splitext(archivo.name)[1].lower()
            if ext not in extensiones_permitidas:
                raise forms.ValidationError(
                    f'Extensión no permitida. Usa: {", ".join(extensiones_permitidas)}'
                )
        return archivo
```

### Vista para subir archivos

```python
from django.shortcuts import render, redirect
from .forms import DocumentoForm

def subir_documento(request):
    if request.method == 'POST':
        form = DocumentoForm(request.POST, request.FILES)  # ¡No olvides request.FILES!
        if form.is_valid():
            documento = form.save()
            return redirect('documento_detalle', pk=documento.pk)
    else:
        form = DocumentoForm()

    return render(request, 'blog/subir_documento.html', {'form': form})
```

### Template con enctype

```html
<form method="post" enctype="multipart/form-data">
    {% csrf_token %}
    {{ form.as_p }}
    <button type="submit">Subir</button>
</form>
```

### Mostrar archivos en templates

```html
<!-- Imagen -->
{% if producto.imagen_principal %}
    <img src="{{ producto.imagen_principal.url }}"
         alt="{{ producto.nombre }}"
         width="300">
{% else %}
    <img src="{% static 'img/sin-imagen.png' %}" alt="Sin imagen">
{% endif %}

<!-- Enlace a documento -->
{% if documento.archivo %}
    <a href="{{ documento.archivo.url }}" download>
        Descargar {{ documento.nombre_archivo }}
        ({{ documento.tamaño_mb|floatformat:2 }} MB)
    </a>
{% endif %}

<!-- Galería de imágenes -->
{% for img in producto.imagenes.all %}
    <img src="{{ img.imagen.url }}"
         alt="{{ img.titulo|default:'Imagen del producto' }}"
         class="galeria-img">
{% endfor %}
```

### Eliminar archivos al borrar objetos

```python
import os
from django.db.models.signals import post_delete, pre_save
from django.dispatch import receiver

@receiver(post_delete, sender=Producto)
def eliminar_imagen_producto(sender, instance, **kwargs):
    """Elimina el archivo físico cuando se borra el objeto."""
    if instance.imagen_principal:
        if os.path.isfile(instance.imagen_principal.path):
            os.remove(instance.imagen_principal.path)

@receiver(pre_save, sender=Producto)
def eliminar_imagen_anterior(sender, instance, **kwargs):
    """Elimina la imagen anterior cuando se sube una nueva."""
    if not instance.pk:
        return
    try:
        anterior = Producto.objects.get(pk=instance.pk)
    except Producto.DoesNotExist:
        return
    if anterior.imagen_principal and anterior.imagen_principal != instance.imagen_principal:
        if os.path.isfile(anterior.imagen_principal.path):
            os.remove(anterior.imagen_principal.path)
```

## Ejercicio Práctico

1. Configura `STATIC_URL`, `STATICFILES_DIRS` y `STATIC_ROOT` en tu proyecto.
2. Crea una estructura de archivos estáticos con CSS, JS e imágenes.
3. Carga los archivos estáticos en un template usando `{% static %}`.
4. Configura `MEDIA_URL` y `MEDIA_ROOT`.
5. Crea un modelo `Galeria` con un `ImageField` que use `upload_to` dinámico.
6. Implementa un formulario para subir imágenes con validación de tamaño máximo (5 MB) y extensiones permitidas (jpg, png, webp).
7. Ejecuta `collectstatic` y verifica que los archivos se recopilen correctamente.
8. Configura WhiteNoise para servir archivos estáticos en producción.

## Resumen

En esta lección aprendiste a gestionar los dos tipos de archivos en Django. Para archivos estáticos, configuraste `STATIC_URL`, `STATICFILES_DIRS` y el comando `collectstatic` para producción. Para archivos media, configuraste `MEDIA_URL` y `MEDIA_ROOT`, y usaste `FileField` e `ImageField` con funciones `upload_to` personalizadas. También aprendiste a validar archivos subidos, mostrarlos en templates y limpiar archivos huérfanos con señales. Estos conocimientos son esenciales para cualquier aplicación web que maneje recursos y contenido multimedia.
