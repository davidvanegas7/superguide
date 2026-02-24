---
title: "Tareas Asíncronas con Celery"
slug: "django-celery-tareas"
description: "Configura Celery con Django para ejecutar tareas asíncronas, tareas periódicas con celery-beat, monitoreo y manejo de errores"
---
# Tareas Asíncronas con Celery

Celery es un sistema de colas de tareas distribuido que permite ejecutar operaciones pesadas en segundo plano. Cuando una petición web necesita enviar emails, procesar imágenes o generar reportes, Celery se encarga de estas tareas sin bloquear la respuesta al usuario, mejorando drásticamente la experiencia y el rendimiento de tu aplicación.

## Setup: Celery + Django

### Instalación

```bash
pip install celery redis django-celery-beat
```

### Configuración del Proyecto

```python
# proyecto/celery.py
import os
from celery import Celery

os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'proyecto.settings')

app = Celery('proyecto')
app.config_from_object('django.conf:settings', namespace='CELERY')
app.autodiscover_tasks()

@app.task(bind=True, ignore_result=True)
def debug_task(self):
    print(f'Request: {self.request!r}')
```

```python
# proyecto/__init__.py
from .celery import app as celery_app

__all__ = ('celery_app',)
```

```python
# settings.py
CELERY_BROKER_URL = 'redis://localhost:6379/0'
CELERY_RESULT_BACKEND = 'redis://localhost:6379/0'
CELERY_ACCEPT_CONTENT = ['json']
CELERY_TASK_SERIALIZER = 'json'
CELERY_RESULT_SERIALIZER = 'json'
CELERY_TIMEZONE = 'America/Mexico_City'

# Configuración adicional
CELERY_TASK_TRACK_STARTED = True
CELERY_TASK_TIME_LIMIT = 30 * 60  # 30 minutos máximo
CELERY_WORKER_MAX_TASKS_PER_CHILD = 1000
```

## shared_task: Creando Tareas

El decorador `@shared_task` es la forma recomendada de definir tareas reutilizables entre aplicaciones:

```python
# app/tasks.py
from celery import shared_task
from django.core.mail import send_mail
from django.template.loader import render_to_string
from .models import Usuario, Reporte

@shared_task
def enviar_email_bienvenida(usuario_id):
    """Envía email de bienvenida al usuario registrado."""
    usuario = Usuario.objects.get(id=usuario_id)
    html_mensaje = render_to_string('emails/bienvenida.html', {
        'nombre': usuario.nombre,
    })
    send_mail(
        subject='¡Bienvenido a nuestra plataforma!',
        message='',
        html_message=html_mensaje,
        from_email='noreply@app.com',
        recipient_list=[usuario.email],
    )

@shared_task
def generar_reporte_ventas(mes, anio):
    """Genera un reporte PDF de ventas del mes."""
    from .services import ReporteService
    reporte = ReporteService.generar_pdf(mes, anio)
    Reporte.objects.create(
        archivo=reporte,
        tipo='ventas',
        periodo=f"{mes}/{anio}"
    )
    return f"Reporte generado: {mes}/{anio}"

@shared_task
def procesar_imagen(imagen_id):
    """Redimensiona y optimiza una imagen subida."""
    from PIL import Image
    from .models import ImagenProducto

    img_obj = ImagenProducto.objects.get(id=imagen_id)
    img = Image.open(img_obj.archivo.path)

    # Crear thumbnail
    img.thumbnail((800, 600))
    img.save(img_obj.archivo.path, quality=85, optimize=True)

    img_obj.procesada = True
    img_obj.save()
```

## delay() y apply_async()

### delay() — Invocación Simple

```python
# views.py
from .tasks import enviar_email_bienvenida, generar_reporte_ventas

def registrar_usuario(request):
    usuario = Usuario.objects.create(...)

    # Ejecutar tarea en segundo plano
    enviar_email_bienvenida.delay(usuario.id)

    return JsonResponse({'mensaje': 'Usuario creado exitosamente'})
```

### apply_async() — Control Avanzado

```python
from datetime import timedelta
from django.utils import timezone

# Ejecutar con un retraso de 10 minutos
enviar_email_bienvenida.apply_async(
    args=[usuario.id],
    countdown=60 * 10
)

# Ejecutar en una fecha/hora específica
generar_reporte_ventas.apply_async(
    args=[12, 2025],
    eta=timezone.now() + timedelta(hours=2)
)

# Establecer cola específica y tiempo de expiración
procesar_imagen.apply_async(
    args=[imagen.id],
    queue='imagenes',
    expires=3600  # La tarea expira después de 1 hora
)

# Reintentar en caso de fallo
enviar_email_bienvenida.apply_async(
    args=[usuario.id],
    retry=True,
    retry_policy={
        'max_retries': 3,
        'interval_start': 10,
        'interval_step': 30,
        'interval_max': 120,
    }
)
```

## Verificar el Estado de una Tarea

```python
from celery.result import AsyncResult

def estado_tarea(request, task_id):
    resultado = AsyncResult(task_id)

    respuesta = {
        'task_id': task_id,
        'estado': resultado.state,     # PENDING, STARTED, SUCCESS, FAILURE
        'listo': resultado.ready(),
        'exitoso': resultado.successful(),
    }

    if resultado.ready():
        respuesta['resultado'] = resultado.get(timeout=1)
    elif resultado.state == 'FAILURE':
        respuesta['error'] = str(resultado.result)

    return JsonResponse(respuesta)
```

## Tareas Periódicas con django-celery-beat

`django-celery-beat` permite programar tareas que se ejecutan automáticamente en intervalos regulares:

```python
# settings.py
INSTALLED_APPS = [
    ...
    'django_celery_beat',
]

# Definir tareas periódicas
from celery.schedules import crontab

CELERY_BEAT_SCHEDULE = {
    'limpiar-sesiones-expiradas': {
        'task': 'app.tasks.limpiar_sesiones',
        'schedule': crontab(hour=3, minute=0),  # Todos los días a las 3:00 AM
    },
    'resumen-diario': {
        'task': 'app.tasks.enviar_resumen_diario',
        'schedule': crontab(hour=8, minute=0, day_of_week='mon-fri'),
    },
    'verificar-suscripciones': {
        'task': 'app.tasks.verificar_suscripciones_vencidas',
        'schedule': timedelta(hours=6),  # Cada 6 horas
    },
    'reporte-mensual': {
        'task': 'app.tasks.generar_reporte_ventas',
        'schedule': crontab(day_of_month=1, hour=6),  # Primer día del mes
        'args': (),
    },
}
```

```python
# tasks.py
@shared_task
def limpiar_sesiones():
    from django.contrib.sessions.models import Session
    from django.utils import timezone
    eliminadas = Session.objects.filter(
        expire_date__lt=timezone.now()
    ).delete()
    return f"Sesiones eliminadas: {eliminadas[0]}"

@shared_task
def enviar_resumen_diario():
    from .models import Pedido
    from django.utils import timezone
    hoy = timezone.now().date()
    pedidos_hoy = Pedido.objects.filter(fecha__date=hoy).count()
    # Enviar resumen por email...
    return f"Resumen enviado: {pedidos_hoy} pedidos hoy"
```

### Iniciar Celery Beat

```bash
# Terminal 1: Worker
celery -A proyecto worker --loglevel=info

# Terminal 2: Beat (programador de tareas)
celery -A proyecto beat --loglevel=info --scheduler django_celery_beat.schedulers:DatabaseScheduler
```

## Manejo de Errores y Reintentos

```python
from celery import shared_task
from celery.exceptions import MaxRetriesExceededError

@shared_task(
    bind=True,
    max_retries=3,
    default_retry_delay=60,  # 60 segundos entre reintentos
    autoretry_for=(ConnectionError, TimeoutError),
    retry_backoff=True,       # Backoff exponencial
    retry_backoff_max=600,    # Máximo 10 minutos entre reintentos
)
def procesar_pago(self, pedido_id):
    try:
        pedido = Pedido.objects.get(id=pedido_id)
        resultado = pasarela_pago.cobrar(pedido.total, pedido.tarjeta_token)

        if resultado.exitoso:
            pedido.estado = 'pagado'
            pedido.save()
            return {'status': 'ok', 'transaccion': resultado.id}
        else:
            raise Exception(f"Pago rechazado: {resultado.mensaje}")

    except Pedido.DoesNotExist:
        return {'error': 'Pedido no encontrado'}

    except Exception as exc:
        try:
            self.retry(exc=exc)
        except MaxRetriesExceededError:
            pedido.estado = 'pago_fallido'
            pedido.save()
            notificar_admin.delay(f"Pago fallido para pedido #{pedido_id}")
```

## Monitoreo con Flower

```bash
pip install flower
celery -A proyecto flower --port=5555
# Abre http://localhost:5555 para ver el dashboard
```

## Ejercicio Práctico

Implementa un sistema de procesamiento de tareas para una plataforma de contenido:

1. Crea una tarea `procesar_video` que simule la conversión de un video subido (con reintentos automáticos).
2. Crea una tarea periódica que envíe un resumen semanal de actividad a los usuarios.
3. Implementa una vista que muestre el estado de procesamiento del video en tiempo real.
4. Configura manejo de errores con notificación al administrador cuando falla el procesamiento.

```python
@shared_task(bind=True, max_retries=3, retry_backoff=True)
def procesar_video(self, video_id):
    video = Video.objects.get(id=video_id)
    video.estado = 'procesando'
    video.save()
    try:
        # Simular procesamiento
        convertir_formato(video)
        generar_thumbnail(video)
        video.estado = 'completado'
        video.save()
    except Exception as exc:
        video.estado = 'error'
        video.save()
        self.retry(exc=exc)
```

## Resumen

**Celery** permite ejecutar tareas pesadas en segundo plano sin bloquear las peticiones web. La configuración requiere un **broker** (Redis), el archivo `celery.py` del proyecto y `@shared_task` para definir tareas. Usa **delay()** para invocaciones simples y **apply_async()** para control avanzado (retrasos, colas, expiración). Las **tareas periódicas** con django-celery-beat automatizan procesos recurrentes. El manejo de **errores con reintentos** y backoff exponencial garantiza la resiliencia, mientras que **Flower** proporciona monitoreo visual en tiempo real.
