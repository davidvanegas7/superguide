---
title: "Panel de Administración"
slug: "django-admin"
description: "Configura y personaliza el panel de administración de Django con ModelAdmin, filtros, búsqueda y acciones personalizadas."
---

# Panel de Administración

El panel de administración es una de las características más destacadas de Django. Proporciona una interfaz web completa para gestionar los datos de tu aplicación sin escribir código de frontend. Se genera automáticamente a partir de tus modelos y es altamente personalizable.

## Configuración inicial

El admin viene activado por defecto. Solo necesitas crear un superusuario y registrar tus modelos:

```bash
# Crear superusuario
python manage.py createsuperuser
# Te pedirá: nombre de usuario, email y contraseña

# Aplicar migraciones si no lo has hecho
python manage.py migrate

# Ejecutar el servidor
python manage.py runserver
```

Accede al panel en `http://127.0.0.1:8000/admin/` e ingresa con las credenciales del superusuario.

## Registrar modelos con admin.site.register

La forma más básica de registrar un modelo:

```python
# blog/admin.py
from django.contrib import admin
from .models import Categoria, Articulo, Autor

# Registro simple
admin.site.register(Categoria)
admin.site.register(Autor)
```

Esto crea una interfaz CRUD automática para cada modelo. Sin embargo, la verdadera potencia está en la personalización con `ModelAdmin`.

## Personalización con ModelAdmin

`ModelAdmin` te da control total sobre cómo se muestra y comporta cada modelo en el admin:

```python
# blog/admin.py
from django.contrib import admin
from .models import Categoria, Articulo, Autor

@admin.register(Articulo)
class ArticuloAdmin(admin.ModelAdmin):
    # Columnas en la lista
    list_display = [
        'titulo',
        'categoria',
        'publicado',
        'fecha_creacion',
        'palabras_count',
    ]

    # Campos en los que se puede buscar
    search_fields = ['titulo', 'contenido', 'categoria__nombre']

    # Filtros laterales
    list_filter = ['publicado', 'categoria', 'fecha_creacion']

    # Campos editables directamente en la lista
    list_editable = ['publicado']

    # Ordenamiento por defecto
    ordering = ['-fecha_creacion']

    # Número de elementos por página
    list_per_page = 25

    # Paginación selectiva
    list_max_show_all = 200

    # Pre-cargar claves foráneas
    list_select_related = ['categoria']

    # Navegación por fechas
    date_hierarchy = 'fecha_creacion'

    # Auto-completar el slug
    prepopulated_fields = {'slug': ('titulo',)}

    # Método personalizado para mostrar en list_display
    @admin.display(description='Palabras', ordering='contenido')
    def palabras_count(self, obj):
        return len(obj.contenido.split())
```

## Organización del formulario de edición

Puedes organizar los campos del formulario de edición con `fields`, `fieldsets` y `readonly_fields`:

```python
@admin.register(Articulo)
class ArticuloAdmin(admin.ModelAdmin):
    # Campos de solo lectura
    readonly_fields = ['fecha_creacion', 'fecha_actualizacion']

    # Organizar en secciones con fieldsets
    fieldsets = (
        ('Información básica', {
            'fields': ('titulo', 'slug', 'categoria'),
        }),
        ('Contenido', {
            'fields': ('resumen', 'contenido'),
            'classes': ('wide',),  # Clase CSS para ancho completo
        }),
        ('Publicación', {
            'fields': ('publicado', 'fecha_publicacion'),
            'classes': ('collapse',),  # Sección colapsable
            'description': 'Configure las opciones de publicación.',
        }),
        ('Metadatos', {
            'fields': ('fecha_creacion', 'fecha_actualizacion'),
            'classes': ('collapse',),
        }),
    )

    prepopulated_fields = {'slug': ('titulo',)}
```

## search_fields avanzado

Los campos de búsqueda soportan lookups especiales:

```python
@admin.register(Articulo)
class ArticuloAdmin(admin.ModelAdmin):
    search_fields = [
        'titulo',                    # icontains por defecto
        '=slug',                     # Coincidencia exacta
        '^titulo',                   # Empieza con (startswith)
        'categoria__nombre',         # Búsqueda a través de relaciones
        'autor__nombre',
    ]
```

## list_filter personalizado

Puedes crear filtros personalizados:

```python
class DecadaPublicacionFilter(admin.SimpleListFilter):
    title = 'década de publicación'
    parameter_name = 'decada'

    def lookups(self, request, model_admin):
        return [
            ('2020', 'Años 2020'),
            ('2010', 'Años 2010'),
            ('2000', 'Años 2000'),
        ]

    def queryset(self, request, queryset):
        if self.value():
            inicio = int(self.value())
            return queryset.filter(
                fecha_publicacion__year__gte=inicio,
                fecha_publicacion__year__lt=inicio + 10,
            )
        return queryset


@admin.register(Articulo)
class ArticuloAdmin(admin.ModelAdmin):
    list_filter = [
        'publicado',
        'categoria',
        DecadaPublicacionFilter,
    ]
```

## Inlines

Los inlines permiten editar modelos relacionados dentro del formulario del modelo padre:

```python
from .models import Articulo, Imagen, Comentario

class ImagenInline(admin.TabularInline):
    """Muestra las imágenes en formato de tabla horizontal."""
    model = Imagen
    extra = 1                    # Formularios vacíos extra
    max_num = 5                  # Máximo de formularios
    min_num = 0                  # Mínimo de formularios
    fields = ['imagen', 'titulo', 'orden']


class ComentarioInline(admin.StackedInline):
    """Muestra los comentarios apilados verticalmente."""
    model = Comentario
    extra = 0
    readonly_fields = ['autor', 'fecha_creacion']
    fields = ['autor', 'contenido', 'aprobado', 'fecha_creacion']


@admin.register(Articulo)
class ArticuloAdmin(admin.ModelAdmin):
    list_display = ['titulo', 'categoria', 'publicado']
    inlines = [ImagenInline, ComentarioInline]
```

### Diferencias entre TabularInline y StackedInline

- **TabularInline**: muestra cada registro en una fila de tabla, ideal para modelos con pocos campos.
- **StackedInline**: apila los campos verticalmente, mejor para modelos con muchos campos.

## Acciones personalizadas (Custom Actions)

Las acciones permiten ejecutar operaciones sobre múltiples objetos seleccionados:

```python
@admin.register(Articulo)
class ArticuloAdmin(admin.ModelAdmin):
    list_display = ['titulo', 'publicado', 'categoria']
    actions = [
        'publicar_articulos',
        'despublicar_articulos',
        'asignar_categoria_general',
    ]

    @admin.action(description='Publicar artículos seleccionados')
    def publicar_articulos(self, request, queryset):
        cantidad = queryset.update(publicado=True)
        self.message_user(
            request,
            f'{cantidad} artículo(s) publicado(s) exitosamente.',
        )

    @admin.action(description='Despublicar artículos seleccionados')
    def despublicar_articulos(self, request, queryset):
        cantidad = queryset.update(publicado=False)
        self.message_user(
            request,
            f'{cantidad} artículo(s) despublicado(s).',
        )

    @admin.action(description='Asignar categoría "General"')
    def asignar_categoria_general(self, request, queryset):
        categoria, _ = Categoria.objects.get_or_create(nombre='General')
        queryset.update(categoria=categoria)
        self.message_user(request, 'Categoría actualizada.')
```

## Personalización global del sitio admin

```python
# blog/admin.py (o en un archivo admin.py del proyecto)
admin.site.site_header = 'Panel de Mi Blog'
admin.site.site_title = 'Admin - Mi Blog'
admin.site.index_title = 'Gestión del contenido'
```

## Sobreescribir métodos del ModelAdmin

```python
@admin.register(Articulo)
class ArticuloAdmin(admin.ModelAdmin):

    def save_model(self, request, obj, form, change):
        """Se ejecuta al guardar un objeto."""
        if not change:  # Si es un nuevo objeto
            obj.autor = request.user
        super().save_model(request, obj, form, change)

    def get_queryset(self, request):
        """Personalizar el queryset base."""
        qs = super().get_queryset(request)
        if not request.user.is_superuser:
            return qs.filter(autor=request.user)
        return qs

    def has_delete_permission(self, request, obj=None):
        """Controlar permisos de eliminación."""
        if obj and obj.publicado:
            return False  # No permitir eliminar artículos publicados
        return super().has_delete_permission(request, obj)

    def get_readonly_fields(self, request, obj=None):
        """Campos de solo lectura dinámicos."""
        if obj and obj.publicado:
            return ['titulo', 'slug']
        return []
```

## Ejercicio Práctico

1. Registra los modelos `Producto`, `Categoria` y `Pedido` en el admin.
2. Para `Producto`, configura: `list_display` con nombre, precio, stock y categoría; `search_fields` por nombre y descripción; `list_filter` por categoría y rango de precios.
3. Usa `prepopulated_fields` para generar el slug a partir del nombre.
4. Crea un `TabularInline` para gestionar imágenes de productos.
5. Implementa una acción personalizada para marcar productos como "Agotado" (stock = 0).
6. Personaliza el encabezado del admin con el nombre de tu tienda.
7. Sobreescribe `get_queryset` para que los usuarios no superusuarios solo vean sus propios productos.

## Resumen

El panel de administración de Django es una herramienta poderosa que genera automáticamente interfaces CRUD. Aprendiste a registrar modelos con `admin.site.register`, personalizar la vista de lista con `list_display`, `search_fields` y `list_filter`, organizar formularios con `fieldsets`, editar modelos relacionados con inlines, y crear acciones personalizadas para operaciones masivas. Todo esto sin escribir una sola línea de HTML o JavaScript.
