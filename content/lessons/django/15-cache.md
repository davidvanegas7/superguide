---
title: "Caché y Performance en Django"
slug: "django-cache-performance"
description: "Domina el sistema de caché de Django, optimización de queries con select_related/prefetch_related, y estrategias de rendimiento"
---
# Caché y Performance en Django

El rendimiento es crucial en aplicaciones web modernas. Django ofrece un framework de caché flexible que reduce la carga en la base de datos y acelera los tiempos de respuesta. Combinado con técnicas de optimización de consultas como `select_related` y `prefetch_related`, puedes construir aplicaciones que escalen eficientemente bajo carga alta.

## Framework de Caché de Django

Django soporta múltiples backends de caché, cada uno con diferentes características de rendimiento:

### Configuración de Backends

```python
# settings.py

# Redis (recomendado para producción)
CACHES = {
    'default': {
        'BACKEND': 'django.core.cache.backends.redis.RedisCache',
        'LOCATION': 'redis://127.0.0.1:6379/1',
        'OPTIONS': {
            'db': '1',
        },
        'TIMEOUT': 300,  # 5 minutos por defecto
        'KEY_PREFIX': 'miapp',
    }
}

# Memcached
CACHES = {
    'default': {
        'BACKEND': 'django.core.cache.backends.memcached.PyMemcacheCache',
        'LOCATION': '127.0.0.1:11211',
    }
}

# Base de datos (para sitios pequeños)
CACHES = {
    'default': {
        'BACKEND': 'django.core.cache.backends.db.DatabaseCache',
        'LOCATION': 'cache_table',
    }
}
# Ejecutar: python manage.py createcachetable

# Filesystem
CACHES = {
    'default': {
        'BACKEND': 'django.core.cache.backends.filebased.FileBasedCache',
        'LOCATION': '/var/tmp/django_cache',
    }
}
```

## @cache_page: Caché de Vistas Completas

La forma más sencilla de agregar caché es decorar vistas enteras:

```python
from django.views.decorators.cache import cache_page
from django.utils.decorators import method_decorator

# Vista basada en función
@cache_page(60 * 15)  # Caché por 15 minutos
def lista_articulos(request):
    articulos = Articulo.objects.filter(publicado=True)
    return render(request, 'articulos/lista.html', {'articulos': articulos})

# Vista basada en clase
@method_decorator(cache_page(60 * 15), name='dispatch')
class ArticuloListView(ListView):
    model = Articulo
    template_name = 'articulos/lista.html'

# En urls.py
from django.views.decorators.cache import cache_page

urlpatterns = [
    path('articulos/', cache_page(60 * 15)(lista_articulos)),
]
```

### Caché Condicional con vary_on

```python
from django.views.decorators.vary import vary_on_headers, vary_on_cookie

@cache_page(60 * 15)
@vary_on_cookie  # Caché diferente por usuario
def mi_perfil(request):
    return render(request, 'perfil.html')

@cache_page(60 * 15)
@vary_on_headers('Accept-Language')  # Caché por idioma
def pagina_inicio(request):
    return render(request, 'inicio.html')
```

## API de Caché: cache.get() y cache.set()

Para un control más granular, usa la API de bajo nivel:

```python
from django.core.cache import cache

# Operaciones básicas
cache.set('mi_clave', 'mi_valor', timeout=300)  # 5 minutos
valor = cache.get('mi_clave')  # Retorna None si no existe
valor = cache.get('mi_clave', 'valor_por_defecto')

# get_or_set: obtener o establecer si no existe
def obtener_estadisticas():
    return {
        'usuarios': User.objects.count(),
        'articulos': Articulo.objects.count(),
    }

stats = cache.get_or_set('estadisticas', obtener_estadisticas, 600)

# Operaciones con múltiples claves
cache.set_many({'clave1': 'val1', 'clave2': 'val2'}, timeout=300)
valores = cache.get_many(['clave1', 'clave2'])

# Eliminar del caché
cache.delete('mi_clave')
cache.delete_many(['clave1', 'clave2'])
cache.clear()  # ¡Elimina TODO el caché!

# Incrementar/Decrementar
cache.set('visitas', 0)
cache.incr('visitas')       # 1
cache.incr('visitas', 5)    # 6
cache.decr('visitas')       # 5
```

### Patrón Común: Caché en Modelos

```python
from django.core.cache import cache

class Articulo(models.Model):
    titulo = models.CharField(max_length=200)
    contenido = models.TextField()
    publicado = models.BooleanField(default=False)

    @classmethod
    def obtener_populares(cls, limite=10):
        cache_key = f'articulos_populares_{limite}'
        articulos = cache.get(cache_key)

        if articulos is None:
            articulos = list(
                cls.objects.filter(publicado=True)
                .order_by('-vistas')[:limite]
                .values('id', 'titulo', 'vistas')
            )
            cache.set(cache_key, articulos, 60 * 30)  # 30 minutos

        return articulos

    def save(self, *args, **kwargs):
        super().save(*args, **kwargs)
        # Invalidar caché cuando se modifica un artículo
        cache.delete_many([
            'articulos_populares_10',
            f'articulo_detalle_{self.pk}',
        ])
```

## Caché de Fragmentos de Template

Almacena en caché secciones específicas de un template:

```html
{% load cache %}

{% cache 600 sidebar %}
    <div class="sidebar">
        {% for categoria in categorias %}
            <a href="{{ categoria.url }}">{{ categoria.nombre }}</a>
        {% endfor %}
    </div>
{% endcache %}

<!-- Caché por usuario -->
{% cache 300 perfil_sidebar request.user.id %}
    <div class="perfil">
        <p>{{ request.user.nombre }}</p>
        <p>Puntos: {{ request.user.perfil.puntos }}</p>
    </div>
{% endcache %}
```

## select_related y prefetch_related

Estas son las herramientas más importantes para evitar el problema N+1 en Django.

### select_related — Relaciones ForeignKey y OneToOne

Realiza un JOIN en la base de datos para obtener los objetos relacionados en una sola consulta:

```python
# ❌ Problema N+1: 1 query para artículos + N queries para autores
articulos = Articulo.objects.all()
for art in articulos:
    print(art.autor.nombre)  # Cada acceso genera un query adicional

# ✅ Con select_related: 1 sola query con JOIN
articulos = Articulo.objects.select_related('autor', 'categoria').all()
for art in articulos:
    print(art.autor.nombre)  # Sin queries adicionales
```

### prefetch_related — Relaciones ManyToMany y Reverse FK

Realiza queries separadas y luego une los resultados en Python:

```python
# ❌ N+1: 1 query para artículos + N queries para etiquetas
articulos = Articulo.objects.all()
for art in articulos:
    print(art.etiquetas.all())

# ✅ Con prefetch_related: 2 queries en total
articulos = Articulo.objects.prefetch_related('etiquetas').all()
for art in articulos:
    print(art.etiquetas.all())  # Sin queries adicionales

# Prefetch con filtro personalizado
from django.db.models import Prefetch

articulos = Articulo.objects.prefetch_related(
    Prefetch(
        'comentarios',
        queryset=Comentario.objects.filter(aprobado=True).order_by('-fecha'),
        to_attr='comentarios_aprobados'
    )
)
```

## Optimización de Queries

### only() y defer()

```python
# Solo cargar campos específicos
articulos = Articulo.objects.only('titulo', 'slug', 'fecha')

# Diferir campos pesados
articulos = Articulo.objects.defer('contenido')  # No carga 'contenido'
```

### values() y values_list()

```python
# Retorna diccionarios en vez de objetos completos
titulos = Articulo.objects.values('titulo', 'fecha')
# [{'titulo': 'Artículo 1', 'fecha': ...}, ...]

# Retorna tuplas
titulos = Articulo.objects.values_list('titulo', flat=True)
# ['Artículo 1', 'Artículo 2', ...]
```

### Debugging de Queries

```python
from django.db import connection, reset_queries
import logging

# Ver queries ejecutadas
reset_queries()
articulos = list(Articulo.objects.select_related('autor').all())
print(f"Queries ejecutadas: {len(connection.queries)}")
for q in connection.queries:
    print(f"  {q['time']}s - {q['sql'][:100]}")

# Django Debug Toolbar (recomendado)
# pip install django-debug-toolbar
```

## Ejercicio Práctico

Optimiza una aplicación de blog con los siguientes requisitos:

1. Configura Redis como backend de caché.
2. Implementa caché de la página de inicio por 15 minutos.
3. Crea un método `obtener_con_cache()` en el modelo `Articulo` que almacene artículos populares.
4. Implementa invalidación automática del caché cuando se crea o edita un artículo.
5. Optimiza las queries de la vista de lista usando `select_related` y `prefetch_related`.
6. Mide la mejora usando `assertNumQueries` en los tests.

```python
class ArticuloListView(ListView):
    def get_queryset(self):
        return (Articulo.objects
            .filter(publicado=True)
            .select_related('autor', 'categoria')
            .prefetch_related('etiquetas')
            .only('titulo', 'slug', 'fecha', 'autor__username', 'categoria__nombre')
            .order_by('-fecha'))
```

## Resumen

El **framework de caché** de Django soporta múltiples backends (Redis, Memcached, BD, filesystem). Usa **@cache_page** para cachear vistas completas y la **API cache.get/set** para control granular. El **caché de fragmentos de template** optimiza secciones específicas. Para consultas, **select_related** resuelve relaciones ForeignKey con JOINs mientras que **prefetch_related** optimiza ManyToMany con queries separadas. Combina estas técnicas con **only()/defer()** y **values()** para minimizar la transferencia de datos. Siempre mide antes de optimizar usando Django Debug Toolbar o `assertNumQueries`.
