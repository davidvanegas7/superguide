---
title: "Vistas y URLs"
slug: "django-vistas-urls"
description: "Aprende a crear vistas basadas en funciones y clases, configurar URLs y usar namespaces en Django."
---

# Vistas y URLs

En Django, las **vistas** son funciones o clases que reciben una solicitud HTTP y devuelven una respuesta HTTP. Las **URLs** conectan las rutas del navegador con las vistas correspondientes. Juntas forman el sistema de enrutamiento que dirige el tráfico de tu aplicación.

## Vistas basadas en funciones (FBV)

Las vistas basadas en funciones son la forma más directa de manejar solicitudes. Reciben un objeto `request` y devuelven un objeto `HttpResponse`:

```python
# blog/views.py
from django.http import HttpResponse, JsonResponse, Http404
from django.shortcuts import render, get_object_or_404, redirect
from .models import Articulo, Categoria


def lista_articulos(request):
    """Lista todos los artículos publicados."""
    articulos = Articulo.objects.filter(publicado=True)
    contexto = {
        'articulos': articulos,
        'titulo': 'Todos los artículos',
    }
    return render(request, 'blog/lista_articulos.html', contexto)


def detalle_articulo(request, slug):
    """Muestra un artículo individual."""
    articulo = get_object_or_404(Articulo, slug=slug, publicado=True)
    return render(request, 'blog/detalle_articulo.html', {'articulo': articulo})


def articulos_por_categoria(request, categoria_id):
    """Filtra artículos por categoría."""
    categoria = get_object_or_404(Categoria, pk=categoria_id)
    articulos = categoria.articulos.filter(publicado=True)
    return render(request, 'blog/lista_articulos.html', {
        'articulos': articulos,
        'titulo': f'Categoría: {categoria.nombre}',
    })


def api_articulos(request):
    """Ejemplo de vista que devuelve JSON."""
    articulos = Articulo.objects.filter(publicado=True).values(
        'titulo', 'slug', 'fecha_publicacion'
    )
    return JsonResponse(list(articulos), safe=False)
```

### Manejar diferentes métodos HTTP

```python
def crear_articulo(request):
    if request.method == 'POST':
        # Procesar el formulario
        titulo = request.POST.get('titulo')
        contenido = request.POST.get('contenido')
        # ... crear artículo ...
        return redirect('blog:lista')
    else:
        # Mostrar formulario vacío
        return render(request, 'blog/crear_articulo.html')
```

## Configuración de URLs con path()

Las URLs se definen en archivos `urls.py` usando la función `path()`:

```python
# blog/urls.py
from django.urls import path
from . import views

app_name = 'blog'  # Namespace de la aplicación

urlpatterns = [
    # Ruta exacta
    path('', views.lista_articulos, name='lista'),

    # Ruta con parámetro string
    path('articulo/<slug:slug>/', views.detalle_articulo, name='detalle'),

    # Ruta con parámetro entero
    path('categoria/<int:categoria_id>/', views.articulos_por_categoria, name='por_categoria'),

    # Ruta para API
    path('api/articulos/', views.api_articulos, name='api_lista'),
]
```

### Convertidores de ruta

Django proporciona varios convertidores para capturar valores de la URL:

| Convertidor | Descripción | Ejemplo |
|-------------|-------------|---------|
| `str` | Cualquier cadena no vacía (excluye `/`) | `<str:nombre>` |
| `int` | Entero positivo | `<int:id>` |
| `slug` | Letras, números, guiones | `<slug:slug>` |
| `uuid` | UUID formateado | `<uuid:pk>` |
| `path` | Cadena incluyendo `/` | `<path:ruta>` |

### Incluir URLs con include()

El archivo `urls.py` principal del proyecto usa `include()` para delegar rutas a cada aplicación:

```python
# mi_proyecto/urls.py
from django.contrib import admin
from django.urls import path, include

urlpatterns = [
    path('admin/', admin.site.urls),
    path('blog/', include('blog.urls')),        # Todas las URLs de blog
    path('tienda/', include('tienda.urls')),     # Todas las URLs de tienda
    path('api/', include('api.urls')),           # Todas las URLs del API
]
```

Con esta configuración, las URLs del blog serán:
- `/blog/` → lista de artículos
- `/blog/articulo/mi-primer-post/` → detalle
- `/blog/categoria/1/` → artículos de una categoría

## URL Namespaces

Los namespaces evitan conflictos de nombres entre aplicaciones. Se definen con `app_name` en el `urls.py` de cada app:

```python
# En templates, usar el namespace:
# {% url 'blog:detalle' slug=articulo.slug %}

# En vistas, usar reverse():
from django.urls import reverse

def mi_vista(request):
    url = reverse('blog:detalle', kwargs={'slug': 'mi-articulo'})
    return redirect(url)
    # También puedes usar el shortcut:
    # return redirect('blog:detalle', slug='mi-articulo')
```

## Vistas basadas en clases (CBV)

Las CBV encapsulan la lógica en clases reutilizables. Django incluye vistas genéricas que cubren patrones comunes:

### TemplateView

Para vistas que solo renderizan un template:

```python
# blog/views.py
from django.views.generic import TemplateView

class PaginaInicioView(TemplateView):
    template_name = 'blog/inicio.html'

    def get_context_data(self, **kwargs):
        contexto = super().get_context_data(**kwargs)
        contexto['mensaje'] = '¡Bienvenido al blog!'
        contexto['articulos_recientes'] = Articulo.objects.filter(
            publicado=True
        )[:5]
        return contexto
```

### ListView

Para mostrar listas de objetos con paginación automática:

```python
from django.views.generic import ListView

class ArticuloListView(ListView):
    model = Articulo
    template_name = 'blog/lista_articulos.html'
    context_object_name = 'articulos'
    paginate_by = 10

    def get_queryset(self):
        return Articulo.objects.filter(publicado=True)

    def get_context_data(self, **kwargs):
        contexto = super().get_context_data(**kwargs)
        contexto['titulo'] = 'Artículos publicados'
        return contexto
```

### DetailView

Para mostrar el detalle de un solo objeto:

```python
from django.views.generic import DetailView

class ArticuloDetailView(DetailView):
    model = Articulo
    template_name = 'blog/detalle_articulo.html'
    context_object_name = 'articulo'
    slug_field = 'slug'
    slug_url_kwarg = 'slug'

    def get_queryset(self):
        return Articulo.objects.filter(publicado=True)
```

### Registrar CBV en urls.py

Las CBV se registran usando el método `.as_view()`:

```python
# blog/urls.py
from django.urls import path
from . import views

app_name = 'blog'

urlpatterns = [
    path('', views.ArticuloListView.as_view(), name='lista'),
    path('articulo/<slug:slug>/', views.ArticuloDetailView.as_view(), name='detalle'),
    path('inicio/', views.PaginaInicioView.as_view(), name='inicio'),
]
```

## Comparación FBV vs CBV

| Aspecto | FBV | CBV |
|---------|-----|-----|
| Simplicidad | Más simple y explícita | Requiere conocer las clases base |
| Reutilización | Mediante decoradores | Mediante herencia y mixins |
| Ideal para | Lógica personalizada | Patrones comunes (CRUD) |
| Métodos HTTP | Condicionales `if/elif` | Métodos separados (`get`, `post`) |

## Decoradores útiles para vistas

```python
from django.views.decorators.http import require_http_methods, require_GET, require_POST
from django.views.decorators.cache import cache_page

@require_GET
def solo_get(request):
    """Solo acepta solicitudes GET."""
    return render(request, 'pagina.html')

@require_POST
def solo_post(request):
    """Solo acepta solicitudes POST."""
    # procesar datos...
    return redirect('blog:lista')

@cache_page(60 * 15)  # Cachear por 15 minutos
def vista_cacheada(request):
    articulos = Articulo.objects.all()
    return render(request, 'blog/lista.html', {'articulos': articulos})
```

## Ejercicio Práctico

1. Crea una aplicación `recetas` con un modelo `Receta` (título, ingredientes, instrucciones, tiempo de preparación, dificultad).
2. Crea las siguientes vistas:
   - **FBV**: vista que liste todas las recetas.
   - **FBV**: vista que muestre el detalle de una receta por su slug.
   - **CBV (ListView)**: vista alternativa para listar recetas con paginación de 5 elementos.
   - **CBV (DetailView)**: vista alternativa para el detalle.
3. Configura las URLs con namespace `recetas`.
4. Agrega una vista que devuelva las recetas en formato JSON.
5. Usa `include()` para integrar las URLs en el proyecto principal.

## Resumen

En esta lección aprendiste a crear vistas tanto basadas en funciones como en clases. Configuraste URLs con `path()`, usaste convertidores de ruta para capturar parámetros, organizaste las rutas con `include()` y namespaces, y exploraste las vistas genéricas `TemplateView`, `ListView` y `DetailView`. Estas herramientas te permiten construir el sistema de enrutamiento completo de cualquier aplicación Django.
