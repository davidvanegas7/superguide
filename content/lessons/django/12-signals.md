---
title: "Signals en Django"
slug: "django-signals"
description: "Domina el sistema de señales de Django: pre_save, post_save, pre_delete, post_delete, señales personalizadas y buenas prácticas"
---
# Signals en Django

Las signals (señales) de Django implementan el patrón Observer, permitiendo que ciertas acciones desencadenen automáticamente otras cuando ocurren eventos específicos. Son fundamentales para desacoplar componentes de tu aplicación, ejecutando código en respuesta a eventos del ORM como guardar, eliminar o modificar relaciones.

## ¿Qué son las Signals?

Una signal es una notificación que Django envía cuando ocurre una acción determinada. Los componentes interesados se "conectan" a la señal y ejecutan código cuando esta se dispara. Esto permite agregar funcionalidad sin modificar directamente el modelo o la vista.

```python
# Flujo básico:
# 1. Django ejecuta model.save()
# 2. Se dispara la señal pre_save ANTES de guardar
# 3. El objeto se guarda en la base de datos
# 4. Se dispara la señal post_save DESPUÉS de guardar
```

## Señales del ORM

### pre_save y post_save

Estas señales se disparan antes y después de que un modelo se guarde:

```python
from django.db.models.signals import pre_save, post_save
from django.dispatch import receiver
from django.utils.text import slugify
from .models import Articulo, Perfil

@receiver(pre_save, sender=Articulo)
def generar_slug(sender, instance, **kwargs):
    """Genera un slug automáticamente antes de guardar."""
    if not instance.slug:
        instance.slug = slugify(instance.titulo)

@receiver(post_save, sender=Articulo)
def notificar_nuevo_articulo(sender, instance, created, **kwargs):
    """Envía notificación cuando se crea un nuevo artículo."""
    if created:
        print(f"Nuevo artículo creado: {instance.titulo}")
        # Enviar email, crear notificación, etc.
```

El parámetro `created` en `post_save` es `True` cuando el objeto se acaba de crear (INSERT) y `False` cuando se actualiza (UPDATE).

### pre_delete y post_delete

Se disparan antes y después de eliminar un objeto:

```python
from django.db.models.signals import pre_delete, post_delete
from django.dispatch import receiver
from .models import Usuario, ArchivoAdjunto
import os

@receiver(pre_delete, sender=ArchivoAdjunto)
def eliminar_archivo_fisico(sender, instance, **kwargs):
    """Elimina el archivo del disco antes de borrar el registro."""
    if instance.archivo and os.path.isfile(instance.archivo.path):
        os.remove(instance.archivo.path)

@receiver(post_delete, sender=Usuario)
def registrar_eliminacion(sender, instance, **kwargs):
    """Registra la eliminación de un usuario."""
    import logging
    logger = logging.getLogger(__name__)
    logger.info(f"Usuario eliminado: {instance.email} (ID: {instance.pk})")
```

### m2m_changed

Esta señal se dispara cuando se modifica una relación ManyToMany:

```python
from django.db.models.signals import m2m_changed
from django.dispatch import receiver
from .models import Articulo

@receiver(m2m_changed, sender=Articulo.etiquetas.through)
def verificar_etiquetas(sender, instance, action, pk_set, **kwargs):
    """Controla las etiquetas asignadas a un artículo."""
    if action == 'pre_add':
        # Se ejecuta antes de agregar etiquetas
        if instance.etiquetas.count() + len(pk_set) > 5:
            raise ValueError("Un artículo no puede tener más de 5 etiquetas.")

    elif action == 'post_add':
        print(f"Se agregaron {len(pk_set)} etiquetas a '{instance.titulo}'")

    elif action == 'post_remove':
        print(f"Se removieron etiquetas de '{instance.titulo}'")

    elif action == 'post_clear':
        print(f"Se eliminaron todas las etiquetas de '{instance.titulo}'")
```

Las acciones posibles son: `pre_add`, `post_add`, `pre_remove`, `post_remove`, `pre_clear` y `post_clear`.

## El Decorador @receiver

El decorador `@receiver` es la forma más limpia de conectar señales:

```python
from django.dispatch import receiver
from django.db.models.signals import post_save
from django.contrib.auth.models import User
from .models import Perfil

# Conectar a un solo sender
@receiver(post_save, sender=User)
def crear_perfil(sender, instance, created, **kwargs):
    if created:
        Perfil.objects.create(usuario=instance)

# Conectar a múltiples senders
@receiver(post_save, sender=User)
@receiver(post_save, sender=Perfil)
def log_cambios(sender, instance, **kwargs):
    print(f"Se modificó {sender.__name__}: {instance}")
```

También puedes conectar señales manualmente con `connect()`:

```python
def mi_handler(sender, instance, **kwargs):
    print(f"Guardado: {instance}")

post_save.connect(mi_handler, sender=Articulo)

# Desconectar una señal
post_save.disconnect(mi_handler, sender=Articulo)
```

## Señales Personalizadas

Puedes crear tus propias señales para eventos específicos de tu aplicación:

```python
# signals.py
import django.dispatch

# Definir señales personalizadas
pedido_completado = django.dispatch.Signal()  # Django 4+
pago_procesado = django.dispatch.Signal()

# Disparar la señal desde una vista o servicio
# views.py
from .signals import pedido_completado

def completar_pedido(request, pedido_id):
    pedido = Pedido.objects.get(id=pedido_id)
    pedido.estado = 'completado'
    pedido.save()

    # Enviar la señal con datos adicionales
    pedido_completado.send(
        sender=Pedido,
        pedido=pedido,
        usuario=request.user
    )

# handlers.py - Receptores de la señal
from .signals import pedido_completado
from django.dispatch import receiver

@receiver(pedido_completado)
def enviar_email_confirmacion(sender, pedido, usuario, **kwargs):
    print(f"Enviando confirmación de pedido #{pedido.id} a {usuario.email}")

@receiver(pedido_completado)
def actualizar_inventario(sender, pedido, **kwargs):
    for item in pedido.items.all():
        item.producto.stock -= item.cantidad
        item.producto.save()
```

## Conectar Signals en AppConfig.ready()

La forma recomendada de registrar señales es dentro del método `ready()` de `AppConfig`:

```python
# apps.py
from django.apps import AppConfig

class TiendaConfig(AppConfig):
    default_auto_field = 'django.db.models.BigAutoField'
    name = 'tienda'

    def ready(self):
        import tienda.signals  # Importar las señales aquí
```

Crea un archivo `signals.py` dedicado para organizar todos los handlers:

```python
# tienda/signals.py
from django.db.models.signals import post_save, pre_delete
from django.dispatch import receiver
from django.contrib.auth.models import User
from .models import Perfil, Pedido

@receiver(post_save, sender=User)
def crear_perfil_usuario(sender, instance, created, **kwargs):
    if created:
        Perfil.objects.create(usuario=instance)

@receiver(post_save, sender=User)
def guardar_perfil_usuario(sender, instance, **kwargs):
    instance.perfil.save()
```

## Buenas Prácticas y Precauciones

```python
# ❌ MAL: Lógica compleja en signals
@receiver(post_save, sender=Pedido)
def procesar_pedido(sender, instance, created, **kwargs):
    # Demasiada lógica aquí puede ser difícil de depurar
    enviar_email(instance)
    actualizar_inventario(instance)
    generar_factura(instance)
    notificar_proveedor(instance)

# ✅ BIEN: Delegar a un servicio
@receiver(post_save, sender=Pedido)
def procesar_pedido(sender, instance, created, **kwargs):
    if created:
        from .services import PedidoService
        PedidoService.procesar_nuevo(instance)

# ⚠️ CUIDADO con bucles infinitos
@receiver(post_save, sender=Articulo)
def actualizar_contador(sender, instance, **kwargs):
    # Esto causa un bucle infinito porque save() dispara post_save de nuevo
    # instance.save()  # ❌ NO HACER ESTO

    # Usar update() para evitar señales
    Articulo.objects.filter(pk=instance.pk).update(
        contador_vistas=instance.contador_vistas + 1
    )
```

## Ejercicio Práctico

Implementa un sistema de auditoría usando signals:

1. Crea un modelo `RegistroAuditoria` con campos: modelo, objeto_id, acción, usuario, fecha, datos_anteriores.
2. Usa `pre_save` para capturar los datos antes de la modificación.
3. Usa `post_save` para registrar creaciones y actualizaciones.
4. Usa `post_delete` para registrar eliminaciones.
5. Crea una señal personalizada `accion_sospechosa` que se dispare cuando un usuario intenta múltiples eliminaciones.

```python
class RegistroAuditoria(models.Model):
    ACCIONES = [('crear', 'Crear'), ('actualizar', 'Actualizar'), ('eliminar', 'Eliminar')]
    modelo = models.CharField(max_length=100)
    objeto_id = models.IntegerField()
    accion = models.CharField(max_length=20, choices=ACCIONES)
    datos_anteriores = models.JSONField(null=True)
    fecha = models.DateTimeField(auto_now_add=True)

@receiver(post_save, sender=Articulo)
def auditar_guardado(sender, instance, created, **kwargs):
    RegistroAuditoria.objects.create(
        modelo=sender.__name__,
        objeto_id=instance.pk,
        accion='crear' if created else 'actualizar',
    )
```

## Resumen

Las **signals** de Django permiten desacoplar componentes ejecutando código en respuesta a eventos. Las señales más usadas son **pre_save/post_save** para operaciones de guardado, **pre_delete/post_delete** para eliminaciones, y **m2m_changed** para relaciones muchos a muchos. El decorador **@receiver** conecta funciones a señales de forma declarativa. También puedes crear **señales personalizadas** para eventos propios de tu aplicación. Siempre registra tus signals en **AppConfig.ready()** y mantén los handlers simples, delegando la lógica compleja a servicios dedicados.
