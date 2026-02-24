---
title: "Modelos y ORM"
slug: "django-modelos-orm"
description: "Aprende a definir modelos, usar el ORM de Django para operaciones CRUD, migraciones y QuerySets."
---

# Modelos y ORM

El **ORM** (Object-Relational Mapping) de Django es una de sus características más poderosas. Te permite interactuar con la base de datos usando código Python en lugar de escribir SQL directamente. Cada modelo se mapea a una tabla en la base de datos, y cada instancia del modelo representa una fila.

## Definiendo modelos

Los modelos se definen en el archivo `models.py` de tu aplicación. Cada modelo es una clase que hereda de `django.db.models.Model`:

```python
# blog/models.py
from django.db import models
from django.utils import timezone

class Categoria(models.Model):
    nombre = models.CharField(max_length=100, unique=True)
    descripcion = models.TextField(blank=True)
    activa = models.BooleanField(default=True)

    class Meta:
        verbose_name_plural = "categorías"
        ordering = ['nombre']

    def __str__(self):
        return self.nombre


class Articulo(models.Model):
    titulo = models.CharField(max_length=200)
    slug = models.SlugField(max_length=200, unique=True)
    contenido = models.TextField()
    resumen = models.CharField(max_length=500, blank=True)
    categoria = models.ForeignKey(
        Categoria,
        on_delete=models.CASCADE,
        related_name='articulos'
    )
    publicado = models.BooleanField(default=False)
    fecha_creacion = models.DateTimeField(auto_now_add=True)
    fecha_actualizacion = models.DateTimeField(auto_now=True)
    fecha_publicacion = models.DateTimeField(null=True, blank=True)

    class Meta:
        ordering = ['-fecha_creacion']
        verbose_name_plural = "artículos"

    def __str__(self):
        return self.titulo

    def publicar(self):
        self.publicado = True
        self.fecha_publicacion = timezone.now()
        self.save()
```

## Tipos de campos (Field Types)

Django ofrece una gran variedad de campos para representar distintos tipos de datos:

| Campo | Descripción |
|-------|-------------|
| `CharField` | Texto corto, requiere `max_length` |
| `TextField` | Texto largo sin límite definido |
| `IntegerField` | Número entero |
| `FloatField` | Número decimal |
| `DecimalField` | Decimal con precisión fija |
| `BooleanField` | Verdadero o Falso |
| `DateField` | Solo fecha |
| `DateTimeField` | Fecha y hora |
| `EmailField` | Correo electrónico con validación |
| `URLField` | URL con validación |
| `SlugField` | Slug para URLs amigables |
| `FileField` | Archivo subido |
| `ImageField` | Imagen (requiere Pillow) |
| `JSONField` | Datos JSON |
| `UUIDField` | Identificador único universal |

### Opciones comunes de campos

```python
# Ejemplos de opciones frecuentes
nombre = models.CharField(
    max_length=100,
    blank=True,        # Permite valor vacío en formularios
    null=True,         # Permite NULL en la base de datos
    default='',        # Valor por defecto
    unique=True,       # Valor único en la tabla
    db_index=True,     # Crear índice en la BD
    help_text="Nombre completo del usuario",
    verbose_name="Nombre del usuario",
)
```

## Migraciones

Las migraciones son la forma en que Django propaga los cambios de tus modelos a la base de datos. Es un sistema de control de versiones para tu esquema.

```bash
# Paso 1: Crear las migraciones (detecta cambios en models.py)
python manage.py makemigrations

# Resultado:
# Migrations for 'blog':
#   blog/migrations/0001_initial.py
#     - Create model Categoria
#     - Create model Articulo

# Paso 2: Ver el SQL que generará la migración
python manage.py sqlmigrate blog 0001

# Paso 3: Aplicar las migraciones a la base de datos
python manage.py migrate

# Ver estado de las migraciones
python manage.py showmigrations
```

### Flujo de trabajo con migraciones

1. Modifica tu modelo en `models.py`.
2. Ejecuta `makemigrations` para generar el archivo de migración.
3. Revisa la migración generada.
4. Ejecuta `migrate` para aplicar los cambios.

```bash
# Revertir una migración específica
python manage.py migrate blog 0001

# Revertir todas las migraciones de una app
python manage.py migrate blog zero
```

## Operaciones CRUD con el ORM

### Crear (Create)

```python
# Método 1: Instanciar y guardar
categoria = Categoria(nombre="Tecnología", descripcion="Artículos de tech")
categoria.save()

# Método 2: Usar create() (crea y guarda en un solo paso)
categoria = Categoria.objects.create(
    nombre="Ciencia",
    descripcion="Artículos científicos"
)

# Crear un artículo asociado
articulo = Articulo.objects.create(
    titulo="Mi primer artículo",
    slug="mi-primer-articulo",
    contenido="Contenido del artículo...",
    categoria=categoria,
)
```

### Leer (Read)

```python
# Obtener todos los registros
todas = Categoria.objects.all()

# Obtener uno por clave primaria
cat = Categoria.objects.get(pk=1)

# Obtener uno por campo (lanza excepción si no existe o hay múltiples)
cat = Categoria.objects.get(nombre="Tecnología")

# Filtrar registros
publicados = Articulo.objects.filter(publicado=True)
recientes = Articulo.objects.filter(
    fecha_creacion__gte=timezone.now() - timezone.timedelta(days=7)
)

# Excluir registros
no_publicados = Articulo.objects.exclude(publicado=True)

# Obtener el primero / último
primero = Articulo.objects.first()
ultimo = Articulo.objects.last()

# Contar registros
total = Articulo.objects.count()

# Verificar existencia
existe = Articulo.objects.filter(slug="mi-articulo").exists()
```

### Actualizar (Update)

```python
# Actualizar un solo objeto
articulo = Articulo.objects.get(pk=1)
articulo.titulo = "Título actualizado"
articulo.save()

# Actualización masiva (más eficiente, no llama a save())
Articulo.objects.filter(publicado=False).update(publicado=True)
```

### Eliminar (Delete)

```python
# Eliminar un objeto
articulo = Articulo.objects.get(pk=1)
articulo.delete()

# Eliminación masiva
Articulo.objects.filter(publicado=False).delete()
```

## QuerySets

Los QuerySets son colecciones de consultas a la base de datos. Son **lazy** (perezosos): no ejecutan la consulta hasta que se evalúan.

```python
# Encadenar filtros (cada uno devuelve un nuevo QuerySet)
resultados = (
    Articulo.objects
    .filter(publicado=True)
    .filter(categoria__nombre="Tecnología")
    .exclude(titulo__contains="borrador")
    .order_by('-fecha_publicacion')
)

# Lookups de campo (field lookups)
Articulo.objects.filter(titulo__contains="Django")      # Contiene
Articulo.objects.filter(titulo__icontains="django")     # Contiene (sin importar mayúsculas)
Articulo.objects.filter(titulo__startswith="Intro")     # Empieza con
Articulo.objects.filter(fecha_creacion__year=2026)      # Por año
Articulo.objects.filter(categoria__nombre="Tech")       # A través de relación

# Valores específicos
Articulo.objects.values('titulo', 'fecha_creacion')     # Diccionarios
Articulo.objects.values_list('titulo', flat=True)       # Lista plana

# Slicing (LIMIT/OFFSET)
ultimos_cinco = Articulo.objects.all()[:5]
del_6_al_10 = Articulo.objects.all()[5:10]
```

## Managers personalizados

Un **Manager** es la interfaz a través de la cual se proporcionan las operaciones de consulta. Puedes crear managers personalizados:

```python
class ArticuloPublicadoManager(models.Manager):
    def get_queryset(self):
        return super().get_queryset().filter(publicado=True)

    def recientes(self):
        return self.get_queryset().order_by('-fecha_publicacion')[:5]


class Articulo(models.Model):
    # ... campos ...

    # Manager por defecto
    objects = models.Manager()
    # Manager personalizado
    publicados = ArticuloPublicadoManager()

# Uso:
Articulo.publicados.all()          # Solo artículos publicados
Articulo.publicados.recientes()    # Los 5 más recientes publicados
```

## Ejercicio Práctico

Crea una aplicación `biblioteca` con los siguientes modelos:

1. **Autor**: nombre, nacionalidad, fecha de nacimiento.
2. **Libro**: título, ISBN (único), número de páginas, fecha de publicación, autor (ForeignKey).
3. Ejecuta las migraciones.
4. Usando la shell de Django (`python manage.py shell`):
   - Crea 3 autores y 5 libros.
   - Filtra los libros con más de 300 páginas.
   - Obtén todos los libros de un autor específico.
   - Actualiza el título de un libro.
   - Elimina un libro.
5. Crea un Manager personalizado que devuelva solo libros con más de 200 páginas.

## Resumen

En esta lección exploraste el ORM de Django en profundidad. Aprendiste a definir modelos con distintos tipos de campos, a gestionar el esquema de la base de datos con migraciones, a realizar operaciones CRUD completas y a trabajar con QuerySets encadenables. También viste cómo crear Managers personalizados para encapsular consultas frecuentes. El ORM de Django te permite trabajar con la base de datos de forma segura y eficiente sin escribir SQL directamente.
