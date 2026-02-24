---
title: "Relaciones y Queries Avanzados"
slug: "django-relaciones-queries"
description: "Domina ForeignKey, ManyToMany, OneToOne, select_related, prefetch_related, Q objects, F expressions y aggregates."
---

# Relaciones y Queries Avanzados

Django ORM soporta todos los tipos de relaciones entre tablas y proporciona herramientas avanzadas para construir consultas complejas y optimizadas. En esta lección exploraremos los tres tipos de relaciones y las técnicas más potentes para consultar datos.

## ForeignKey (Muchos a Uno)

Una clave foránea establece una relación donde muchos registros de un modelo se asocian a uno de otro:

```python
# tienda/models.py
from django.db import models
from django.conf import settings

class Categoria(models.Model):
    nombre = models.CharField(max_length=100)
    descripcion = models.TextField(blank=True)

    def __str__(self):
        return self.nombre


class Producto(models.Model):
    nombre = models.CharField(max_length=200)
    precio = models.DecimalField(max_digits=10, decimal_places=2)
    categoria = models.ForeignKey(
        Categoria,
        on_delete=models.CASCADE,       # Eliminar productos si se borra la categoría
        related_name='productos',        # Nombre para acceso inverso
    )
    vendedor = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.SET_NULL,       # Poner NULL si se elimina el usuario
        null=True,
        related_name='productos_vendidos',
    )

    def __str__(self):
        return self.nombre
```

### Opciones de on_delete

| Opción | Comportamiento |
|--------|---------------|
| `CASCADE` | Elimina los registros relacionados |
| `PROTECT` | Impide la eliminación (lanza `ProtectedError`) |
| `SET_NULL` | Establece NULL (requiere `null=True`) |
| `SET_DEFAULT` | Establece el valor por defecto |
| `SET(valor)` | Establece un valor específico o callable |
| `DO_NOTHING` | No hace nada (puede romper integridad) |

### Consultas con ForeignKey

```python
# Acceso directo (de hijo a padre)
producto = Producto.objects.get(pk=1)
print(producto.categoria.nombre)

# Acceso inverso (de padre a hijos) usando related_name
categoria = Categoria.objects.get(pk=1)
productos = categoria.productos.all()

# Filtrar a través de relaciones (doble guion bajo)
Producto.objects.filter(categoria__nombre="Electrónica")
Producto.objects.filter(vendedor__username="admin")

# Crear con relación
cat = Categoria.objects.get(nombre="Ropa")
Producto.objects.create(nombre="Camiseta", precio=29.99, categoria=cat)
```

## ManyToManyField (Muchos a Muchos)

Relaciones donde múltiples registros se asocian con múltiples registros del otro modelo:

```python
class Etiqueta(models.Model):
    nombre = models.CharField(max_length=50, unique=True)

    def __str__(self):
        return self.nombre


class Producto(models.Model):
    nombre = models.CharField(max_length=200)
    precio = models.DecimalField(max_digits=10, decimal_places=2)
    etiquetas = models.ManyToManyField(
        Etiqueta,
        related_name='productos',
        blank=True,
    )

    def __str__(self):
        return self.nombre
```

### Operaciones con ManyToMany

```python
producto = Producto.objects.get(pk=1)
etiqueta = Etiqueta.objects.get(nombre="oferta")

# Agregar relaciones
producto.etiquetas.add(etiqueta)
producto.etiquetas.add(et1, et2, et3)       # Agregar múltiples

# Eliminar relaciones
producto.etiquetas.remove(etiqueta)

# Reemplazar todas las relaciones
producto.etiquetas.set([et1, et2])

# Limpiar todas las relaciones
producto.etiquetas.clear()

# Consultar
producto.etiquetas.all()                     # Etiquetas del producto
etiqueta.productos.all()                     # Productos con esta etiqueta
producto.etiquetas.count()                   # Cantidad

# Filtrar
Producto.objects.filter(etiquetas__nombre="oferta")
Producto.objects.filter(etiquetas__in=[et1, et2]).distinct()
```

### ManyToMany con modelo intermedio (through)

Cuando necesitas almacenar datos adicionales en la relación:

```python
class Pedido(models.Model):
    cliente = models.ForeignKey(settings.AUTH_USER_MODEL, on_delete=models.CASCADE)
    productos = models.ManyToManyField(Producto, through='DetallePedido')
    fecha = models.DateTimeField(auto_now_add=True)


class DetallePedido(models.Model):
    pedido = models.ForeignKey(Pedido, on_delete=models.CASCADE)
    producto = models.ForeignKey(Producto, on_delete=models.CASCADE)
    cantidad = models.PositiveIntegerField(default=1)
    precio_unitario = models.DecimalField(max_digits=10, decimal_places=2)

    class Meta:
        unique_together = ['pedido', 'producto']
```

```python
# Crear con through
pedido = Pedido.objects.create(cliente=usuario)
DetallePedido.objects.create(
    pedido=pedido,
    producto=producto,
    cantidad=3,
    precio_unitario=producto.precio,
)
```

## OneToOneField (Uno a Uno)

Relación exclusiva uno a uno, frecuente para extender modelos:

```python
class Perfil(models.Model):
    usuario = models.OneToOneField(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name='perfil',
    )
    bio = models.TextField(blank=True)
    avatar = models.ImageField(upload_to='avatares/', blank=True)
    fecha_nacimiento = models.DateField(null=True, blank=True)
    sitio_web = models.URLField(blank=True)

    def __str__(self):
        return f'Perfil de {self.usuario.username}'
```

```python
# Acceso directo e inverso
perfil = Perfil.objects.get(usuario=usuario)
perfil = usuario.perfil  # Acceso inverso (sin .all(), es un solo objeto)

# Crear automáticamente con señales
from django.db.models.signals import post_save
from django.dispatch import receiver

@receiver(post_save, sender=settings.AUTH_USER_MODEL)
def crear_perfil(sender, instance, created, **kwargs):
    if created:
        Perfil.objects.create(usuario=instance)
```

## Optimización: select_related y prefetch_related

### select_related (JOIN en SQL)

Optimiza consultas de relaciones ForeignKey y OneToOne cargando los datos relacionados en una sola consulta SQL con JOIN:

```python
# SIN select_related: N+1 consultas
productos = Producto.objects.all()
for p in productos:
    print(p.categoria.nombre)  # Cada acceso = una consulta SQL extra

# CON select_related: 1 sola consulta con JOIN
productos = Producto.objects.select_related('categoria', 'vendedor').all()
for p in productos:
    print(p.categoria.nombre)  # Sin consulta extra

# Encadenar relaciones
pedidos = Pedido.objects.select_related('cliente__perfil')
```

### prefetch_related (consultas separadas)

Optimiza relaciones ManyToMany y relaciones inversas con consultas separadas eficientes:

```python
# SIN prefetch_related: N+1 consultas
productos = Producto.objects.all()
for p in productos:
    print(p.etiquetas.all())  # Consulta extra por cada producto

# CON prefetch_related: 2 consultas (productos + etiquetas)
productos = Producto.objects.prefetch_related('etiquetas').all()
for p in productos:
    print(p.etiquetas.all())  # Sin consulta extra

# Prefetch personalizado
from django.db.models import Prefetch

categorias = Categoria.objects.prefetch_related(
    Prefetch(
        'productos',
        queryset=Producto.objects.filter(precio__lte=100).order_by('precio'),
        to_attr='productos_economicos',
    )
)
for cat in categorias:
    print(cat.productos_economicos)  # Lista, no QuerySet
```

## Q Objects (consultas complejas)

Los objetos `Q` permiten construir condiciones OR, NOT y combinaciones complejas:

```python
from django.db.models import Q

# OR: productos baratos O en oferta
Producto.objects.filter(
    Q(precio__lt=50) | Q(etiquetas__nombre="oferta")
)

# AND explícito
Producto.objects.filter(
    Q(precio__gte=10) & Q(precio__lte=100)
)

# NOT: productos que NO son de electrónica
Producto.objects.filter(
    ~Q(categoria__nombre="Electrónica")
)

# Combinaciones complejas
Producto.objects.filter(
    (Q(precio__lt=50) | Q(en_oferta=True)) &
    Q(stock__gt=0) &
    ~Q(categoria__nombre="Descontinuado")
)

# Q dinámico
filtros = Q()
if nombre_busqueda:
    filtros &= Q(nombre__icontains=nombre_busqueda)
if precio_max:
    filtros &= Q(precio__lte=precio_max)
if categoria_id:
    filtros &= Q(categoria_id=categoria_id)

resultados = Producto.objects.filter(filtros)
```

## F Expressions (referencias a campos)

Las expresiones `F` permiten referenciar valores de campos en consultas sin cargarlos en Python:

```python
from django.db.models import F

# Comparar campos entre sí
Producto.objects.filter(stock__lt=F('stock_minimo'))

# Operaciones aritméticas
Producto.objects.filter(precio__lt=F('precio_original') * 0.5)

# Actualización eficiente (operación en la BD, sin race conditions)
Producto.objects.filter(pk=1).update(stock=F('stock') - 1)
Producto.objects.filter(pk=1).update(visitas=F('visitas') + 1)

# Actualización masiva: aplicar 10% de descuento
Producto.objects.filter(
    categoria__nombre="Ofertas"
).update(
    precio=F('precio') * 0.9
)

# Anotar con expresiones F
from django.db.models import ExpressionWrapper, DecimalField

Producto.objects.annotate(
    ganancia=ExpressionWrapper(
        F('precio') - F('costo'),
        output_field=DecimalField()
    )
).filter(ganancia__gt=10)
```

## Aggregates y Annotations

Funciones de agregación para cálculos sobre conjuntos de datos:

```python
from django.db.models import Count, Sum, Avg, Max, Min

# Aggregate: devuelve un diccionario con resultados
Producto.objects.aggregate(
    total_productos=Count('id'),
    precio_promedio=Avg('precio'),
    precio_maximo=Max('precio'),
    precio_minimo=Min('precio'),
    valor_inventario=Sum(F('precio') * F('stock')),
)
# {'total_productos': 150, 'precio_promedio': 45.50, ...}

# Annotate: agrega un campo calculado a cada objeto
categorias = Categoria.objects.annotate(
    num_productos=Count('productos'),
    precio_promedio=Avg('productos__precio'),
).order_by('-num_productos')

for cat in categorias:
    print(f"{cat.nombre}: {cat.num_productos} productos, promedio ${cat.precio_promedio:.2f}")

# Filtrar sobre anotaciones
categorias_populares = Categoria.objects.annotate(
    num_productos=Count('productos')
).filter(num_productos__gte=10)

# Agrupar con values + annotate (GROUP BY)
ventas_por_mes = Pedido.objects.values(
    'fecha__month'
).annotate(
    total=Sum('total'),
    cantidad=Count('id'),
).order_by('fecha__month')
```

## Ejercicio Práctico

1. Crea los modelos: `Autor` (nombre, bio), `Libro` (título, precio, autor FK), `Editorial` (nombre), `Genero` (nombre) con relación M2M a Libro.
2. Usa `select_related` para listar libros con su autor en una sola consulta.
3. Usa `prefetch_related` para listar libros con sus géneros.
4. Con `Q` objects, busca libros cuyo precio sea menor a $20 O que pertenezcan al género "Ficción".
5. Con `F` expressions, aplica un 15% de descuento a todos los libros con precio mayor a $50.
6. Usa `aggregate` para obtener el precio promedio, máximo y mínimo de todos los libros.
7. Usa `annotate` para obtener el número de libros por autor, ordenado de mayor a menor.

## Resumen

En esta lección profundizaste en las relaciones de Django (ForeignKey, ManyToManyField, OneToOneField) y en técnicas avanzadas de consulta. Aprendiste a optimizar con `select_related` y `prefetch_related`, a construir condiciones complejas con `Q` objects, a referenciar campos con `F` expressions y a realizar cálculos con funciones de agregación. Estas herramientas te permiten escribir consultas eficientes y expresivas sin recurrir a SQL crudo.
