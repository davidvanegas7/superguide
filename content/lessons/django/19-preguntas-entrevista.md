---
title: "Preguntas de Entrevista: Django"
slug: "django-preguntas-entrevista"
description: "Prepárate para entrevistas técnicas de Django: patrón MVT, ORM, signals, middleware, N+1, caching, Manager vs QuerySet y más"
---
# Preguntas de Entrevista: Django

Las entrevistas técnicas para posiciones de desarrollo con Django evalúan tanto el conocimiento teórico del framework como la capacidad de resolver problemas reales. Esta lección recopila las preguntas más frecuentes con respuestas detalladas y ejemplos de código para que te prepares de forma integral.

## 1. ¿Qué es el patrón MVT y cómo se diferencia de MVC?

Django sigue el patrón **MVT (Model-View-Template)**, una variación del clásico MVC:

| Componente | MVT (Django) | MVC Tradicional |
|-----------|-------------|-----------------|
| **Model** | Define la estructura de datos y la lógica de negocio | Igual |
| **View** | Maneja la lógica de la petición y retorna respuestas | Equivale al Controller |
| **Template** | Define la presentación HTML | Equivale a la View |

```python
# Model - Define la estructura de datos
class Producto(models.Model):
    nombre = models.CharField(max_length=200)
    precio = models.DecimalField(max_digits=10, decimal_places=2)

    def esta_en_oferta(self):
        return self.precio < self.precio_original * 0.8

# View - Maneja la lógica de la petición (equivale al Controller en MVC)
def lista_productos(request):
    productos = Producto.objects.filter(activo=True)
    return render(request, 'productos/lista.html', {'productos': productos})

# Template - Presentación (equivale a la View en MVC)
# productos/lista.html
```

**Respuesta clave:** En Django, la "View" actúa como controlador, y Django mismo maneja el routing (el "Controller" de MVC). La principal ventaja es la separación clara de responsabilidades.

## 2. ¿Cuál es la diferencia entre ORM y SQL directo? ¿Cuándo usar cada uno?

```python
# ORM de Django - Abstracto, seguro, portable
productos = Producto.objects.filter(
    precio__gte=100,
    categoria__nombre='Electrónica'
).select_related('categoria').order_by('-precio')[:10]

# SQL directo - Para queries complejas que el ORM no soporta bien
from django.db import connection

with connection.cursor() as cursor:
    cursor.execute("""
        SELECT p.nombre, COUNT(r.id) as total_reseñas,
               AVG(r.calificacion) as promedio
        FROM productos_producto p
        LEFT JOIN productos_reseña r ON p.id = r.producto_id
        GROUP BY p.id
        HAVING AVG(r.calificacion) > %s
        ORDER BY promedio DESC
    """, [4.0])
    resultados = cursor.fetchall()
```

**Cuándo usar SQL directo:**
- Queries con múltiples JOINs complejos.
- Operaciones de bulk que el ORM no optimiza.
- Funciones específicas de la base de datos.
- Cuando el rendimiento es crítico y el ORM genera queries subóptimos.

**Cuándo usar el ORM:**
- CRUD estándar y la mayoría de operaciones.
- Cuando necesitas portabilidad entre bases de datos.
- Para aprovechar la protección automática contra SQL injection.

## 3. ¿Qué son las Signals y cuándo deberías/no deberías usarlas?

Las signals implementan el patrón Observer para desacoplar componentes:

```python
from django.db.models.signals import post_save
from django.dispatch import receiver

@receiver(post_save, sender=User)
def crear_perfil(sender, instance, created, **kwargs):
    if created:
        Perfil.objects.create(usuario=instance)
```

**Cuándo usarlas:**
- Crear objetos relacionados automáticamente (perfil al crear usuario).
- Invalidar caché cuando cambian datos.
- Logging y auditoría.
- Integrar apps de terceros sin modificar su código.

**Cuándo NO usarlas:**
- Lógica de negocio compleja (difícil de depurar, flujo oculto).
- Cuando un método del modelo o un servicio sería más claro.
- Si generan efectos secundarios difíciles de rastrear.

## 4. ¿Cómo funciona el pipeline de Middleware en Django?

El middleware procesa peticiones y respuestas en un orden específico (como capas de una cebolla):

```
Petición → Middleware1 → Middleware2 → ... → View
                                               ↓
Respuesta ← Middleware1 ← Middleware2 ← ... ← View
```

```python
# Middleware personalizado
class RequestTimingMiddleware:
    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        import time
        start = time.time()

        # Código ANTES de la vista
        response = self.get_response(request)

        # Código DESPUÉS de la vista
        duration = time.time() - start
        response['X-Request-Duration'] = f'{duration:.3f}s'
        return response

    def process_exception(self, request, exception):
        """Se ejecuta si la vista lanza una excepción."""
        import logging
        logging.error(f"Error en {request.path}: {exception}")
        return None  # Propaga la excepción
```

**Orden importante:** Los middleware se ejecutan de arriba a abajo en la petición y de abajo a arriba en la respuesta.

## 5. ¿Qué es el problema N+1 y cómo se resuelve?

El problema N+1 ocurre cuando se ejecuta 1 query para obtener objetos y luego N queries adicionales para acceder a sus relaciones:

```python
# ❌ Problema N+1: 1 + N queries
articulos = Articulo.objects.all()  # 1 query
for art in articulos:
    print(art.autor.nombre)  # N queries (una por artículo)
    print(art.categoria.nombre)  # N queries más

# ✅ Solución con select_related (ForeignKey, OneToOne): 1 query con JOIN
articulos = Articulo.objects.select_related('autor', 'categoria').all()

# ✅ Solución con prefetch_related (ManyToMany, reverse FK): 2-3 queries
articulos = Articulo.objects.prefetch_related('etiquetas', 'comentarios').all()

# ✅ Combinando ambos
articulos = (Articulo.objects
    .select_related('autor', 'categoria')
    .prefetch_related('etiquetas')
    .all())
```

**Herramientas de detección:** Django Debug Toolbar, `assertNumQueries` en tests, `django-query-inspector`.

## 6. ¿Cuáles son las estrategias de Caching en Django?

```python
# 1. Caché de vista completa
@cache_page(60 * 15)
def lista_productos(request):
    return render(request, 'productos.html')

# 2. Caché de bajo nivel
from django.core.cache import cache
productos = cache.get_or_set('productos_populares', lambda: list(
    Producto.objects.order_by('-ventas')[:10]
), 600)

# 3. Caché de template
# {% cache 300 sidebar %}...{% endcache %}

# 4. Caché de QuerySet (django-cacheops)
# 5. Caché per-site (middleware)
```

**Estrategias de invalidación:** TTL (expiración temporal), invalidación manual al modificar datos, cache versioning.

## 7. Django vs Flask: ¿Cuáles son las diferencias principales?

| Aspecto | Django | Flask |
|---------|--------|-------|
| Filosofía | "Batteries included" | Micro-framework minimalista |
| ORM | Incluido (Django ORM) | No incluido (usa SQLAlchemy) |
| Admin | Panel admin automático | No incluido |
| Autenticación | Sistema completo incluido | Extensiones (Flask-Login) |
| Formularios | django.forms | WTForms (extensión) |
| Curva | Más pronunciada | Más simple al inicio |
| Ideal para | Proyectos medianos/grandes | APIs pequeñas, microservicios |

**Respuesta clave:** Django es ideal cuando necesitas una solución completa con ORM, admin, auth y formularios. Flask es mejor para microservicios o cuando quieres control total sobre cada componente.

## 8. ¿Cuál es la diferencia entre Manager y QuerySet?

```python
# Manager: interfaz para hacer queries desde el modelo
# QuerySet: representa una consulta a la base de datos

class ArticuloQuerySet(models.QuerySet):
    def publicados(self):
        return self.filter(publicado=True)

    def recientes(self):
        return self.order_by('-fecha')[:10]

    def por_categoria(self, categoria):
        return self.filter(categoria__slug=categoria)

class ArticuloManager(models.Manager):
    def get_queryset(self):
        return ArticuloQuerySet(self.model, using=self._db)

    def publicados(self):
        return self.get_queryset().publicados()

class Articulo(models.Model):
    objects = ArticuloManager()

# Uso
Articulo.objects.publicados()  # Manager → QuerySet
Articulo.objects.publicados().recientes()  # Encadenamiento
Articulo.objects.filter(activo=True).publicados()  # QuerySet directamente
```

**Manager:** Punto de entrada (`Model.objects`), puede tener métodos de tabla.  
**QuerySet:** Lazy, encadenable, representa una consulta SQL que se ejecuta solo cuando se evalúa.

## 9. ¿Qué es el Framework de ContentTypes?

El framework `contenttypes` permite crear relaciones genéricas con cualquier modelo:

```python
from django.contrib.contenttypes.fields import GenericForeignKey, GenericRelation
from django.contrib.contenttypes.models import ContentType

class Comentario(models.Model):
    # Relación genérica - se puede asociar a cualquier modelo
    content_type = models.ForeignKey(ContentType, on_delete=models.CASCADE)
    object_id = models.PositiveIntegerField()
    content_object = GenericForeignKey('content_type', 'object_id')

    texto = models.TextField()
    fecha = models.DateTimeField(auto_now_add=True)

class Articulo(models.Model):
    titulo = models.CharField(max_length=200)
    comentarios = GenericRelation(Comentario)

class Foto(models.Model):
    imagen = models.ImageField()
    comentarios = GenericRelation(Comentario)

# Uso
articulo = Articulo.objects.first()
Comentario.objects.create(content_object=articulo, texto="Gran artículo")

foto = Foto.objects.first()
Comentario.objects.create(content_object=foto, texto="Bella foto")
```

**Casos de uso:** Sistemas de comentarios, likes, etiquetas, historial de actividad aplicables a múltiples modelos.

## 10. Preguntas Adicionales Frecuentes

### ¿Qué diferencia hay entre `null=True` y `blank=True`?
- `null=True`: Permite `NULL` en la base de datos (nivel DB).
- `blank=True`: Permite campo vacío en formularios (nivel validación).

### ¿Qué es `select_for_update()`?
Bloquea las filas seleccionadas hasta que termine la transacción, previniendo race conditions:

```python
with transaction.atomic():
    producto = Producto.objects.select_for_update().get(id=1)
    producto.stock -= 1
    producto.save()
```

### ¿Cómo manejas migraciones en equipo?
- Nunca editar migraciones ya aplicadas en producción.
- Usar `squashmigrations` para reducir el número.
- Resolver conflictos con `makemigrations --merge`.

## Ejercicio Práctico

Prepárate para una entrevista simulada:

1. Explica el ciclo de vida completo de una petición HTTP en Django (URL → middleware → view → template → response).
2. Implementa un Manager personalizado con métodos encadenables.
3. Escribe código que demuestre el problema N+1 y su solución.
4. Diseña un sistema de auditoría usando ContentTypes.
5. Compara tres enfoques de caché para un dashboard con datos en tiempo real.

```python
# Ejemplo: Ciclo de vida de una petición
# 1. URL matching → urls.py
# 2. Middleware (request) → SecurityMiddleware, SessionMiddleware...
# 3. View → procesa lógica
# 4. Template → renderiza HTML
# 5. Middleware (response) → agrega headers
# 6. Respuesta al cliente
```

## Resumen

Las entrevistas de Django evalúan conceptos fundamentales: el patrón **MVT** y su diferencia con MVC, el uso del **ORM** vs SQL directo, las **signals** y cuándo evitarlas, el **pipeline de middleware**, la resolución del problema **N+1** con select/prefetch_related, las estrategias de **caching**, las diferencias entre **Django y Flask**, **Manager vs QuerySet** para consultas personalizadas, y el **framework ContentTypes** para relaciones genéricas. Dominar estos conceptos demuestra comprensión profunda de Django y preparación para roles de desarrollo backend profesional.
