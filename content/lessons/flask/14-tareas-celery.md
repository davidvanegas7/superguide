---
title: "Tareas Asíncronas con Celery"
slug: "flask-tareas-celery"
description: "Aprende a ejecutar tareas en segundo plano con Celery y Flask: procesamiento asíncrono, tareas periódicas y manejo de errores."
---

# Tareas Asíncronas con Celery

En aplicaciones web, hay operaciones que tardan demasiado para ejecutarse durante una petición HTTP: enviar correos, procesar imágenes, generar reportes o consultar APIs externas. **Celery** es un sistema de colas de tareas distribuido que permite ejecutar estas operaciones en segundo plano, devolviendo una respuesta inmediata al usuario.

## ¿Cómo Funciona Celery?

Celery sigue una arquitectura productor-consumidor:

1. **Productor (Flask)**: encola una tarea.
2. **Broker (Redis/RabbitMQ)**: almacena las tareas en una cola.
3. **Worker (Celery)**: consume y ejecuta las tareas de la cola.
4. **Backend (Redis)**: almacena los resultados de las tareas.

## Instalación y Configuración

```bash
# Instalar Celery y Redis como broker
pip install celery redis

# Asegúrate de tener Redis corriendo
# En Ubuntu: sudo apt install redis-server && sudo systemctl start redis
```

### Configuración con Application Factory

```python
# celery_app.py
from celery import Celery

def make_celery(app):
    """Crea una instancia de Celery integrada con Flask."""
    celery = Celery(
        app.import_name,
        broker=app.config['CELERY_BROKER_URL'],
        backend=app.config['CELERY_RESULT_BACKEND']
    )
    celery.conf.update(app.config)

    # Hacer que las tareas se ejecuten dentro del contexto de la app
    class ContextTask(celery.Task):
        def __call__(self, *args, **kwargs):
            with app.app_context():
                return self.run(*args, **kwargs)

    celery.Task = ContextTask
    return celery
```

```python
# app.py
from flask import Flask
from celery_app import make_celery

def create_app():
    app = Flask(__name__)
    app.config.update(
        CELERY_BROKER_URL='redis://localhost:6379/0',
        CELERY_RESULT_BACKEND='redis://localhost:6379/0',
        CELERY_TASK_SERIALIZER='json',
        CELERY_RESULT_SERIALIZER='json',
        CELERY_ACCEPT_CONTENT=['json'],
        CELERY_TIMEZONE='America/Mexico_City',
    )
    return app

app = create_app()
celery = make_celery(app)
```

## Definir Tareas

Las tareas se definen con el decorador `@celery.task`:

```python
# tasks.py
import time
from app import celery

@celery.task(name='enviar_correo')
def enviar_correo(destinatario, asunto, cuerpo):
    """Tarea para enviar un correo electrónico."""
    # Simulación de envío (en producción usarías Flask-Mail)
    time.sleep(3)  # Simula la latencia del servidor SMTP
    print(f"Correo enviado a {destinatario}: {asunto}")
    return {
        "destinatario": destinatario,
        "status": "enviado"
    }

@celery.task(name='procesar_imagen')
def procesar_imagen(ruta_imagen):
    """Tarea para redimensionar y optimizar una imagen."""
    from PIL import Image

    img = Image.open(ruta_imagen)

    # Crear diferentes tamaños
    tamaños = {
        'thumbnail': (150, 150),
        'medium': (800, 600),
        'large': (1920, 1080)
    }

    resultados = {}
    for nombre, tamaño in tamaños.items():
        copia = img.copy()
        copia.thumbnail(tamaño)
        ruta_salida = ruta_imagen.replace('.', f'_{nombre}.')
        copia.save(ruta_salida, optimize=True, quality=85)
        resultados[nombre] = ruta_salida

    return resultados

@celery.task(name='generar_reporte')
def generar_reporte(tipo, filtros):
    """Genera un reporte en PDF (operación lenta)."""
    time.sleep(10)  # Simula procesamiento pesado
    return {
        "tipo": tipo,
        "archivo": f"/reportes/reporte_{tipo}_{filtros.get('mes')}.pdf",
        "status": "completado"
    }
```

## Ejecutar Tareas: delay() y apply_async()

Hay dos formas de encolar una tarea:

```python
from flask import request, jsonify
from tasks import enviar_correo, procesar_imagen, generar_reporte

@app.route('/registro', methods=['POST'])
def registrar_usuario():
    data = request.get_json()

    # ... crear usuario en la base de datos ...

    # delay() — forma simple, argumentos posicionales
    tarea = enviar_correo.delay(
        data['email'],
        'Bienvenido a nuestra plataforma',
        'Gracias por registrarte...'
    )

    return jsonify({
        "mensaje": "Usuario registrado",
        "tarea_correo_id": tarea.id  # ID para consultar el estado
    }), 201

@app.route('/procesar-foto', methods=['POST'])
def procesar_foto():
    # ... guardar la imagen subida ...

    # apply_async() — forma avanzada con opciones adicionales
    tarea = procesar_imagen.apply_async(
        args=['/uploads/foto.jpg'],
        countdown=5,            # Esperar 5 segundos antes de ejecutar
        expires=3600,           # La tarea expira en 1 hora
        retry=True,             # Reintentar en caso de fallo
        retry_policy={
            'max_retries': 3,
            'interval_start': 1,
            'interval_step': 2,
        }
    )

    return jsonify({"tarea_id": tarea.id}), 202
```

## Consultar Resultados con AsyncResult

Puedes verificar el estado y resultado de una tarea usando su ID:

```python
from celery.result import AsyncResult

@app.route('/tarea/<tarea_id>')
def estado_tarea(tarea_id):
    """Consulta el estado de una tarea en ejecución."""
    resultado = AsyncResult(tarea_id, app=celery)

    response = {
        "tarea_id": tarea_id,
        "estado": resultado.state,  # PENDING, STARTED, SUCCESS, FAILURE
    }

    if resultado.state == 'SUCCESS':
        response['resultado'] = resultado.result
    elif resultado.state == 'FAILURE':
        response['error'] = str(resultado.info)
    elif resultado.state == 'PROGRESS':
        response['progreso'] = resultado.info

    return jsonify(response)
```

### Reportar Progreso desde una Tarea

```python
@celery.task(bind=True, name='importar_datos')
def importar_datos(self, archivo_csv):
    """Importa datos desde un CSV con reporte de progreso."""
    import csv

    with open(archivo_csv, 'r') as f:
        lector = csv.reader(f)
        filas = list(lector)
        total = len(filas)

        for i, fila in enumerate(filas):
            # Procesar cada fila
            # ... lógica de importación ...

            # Actualizar progreso
            self.update_state(
                state='PROGRESS',
                meta={
                    'actual': i + 1,
                    'total': total,
                    'porcentaje': int((i + 1) / total * 100)
                }
            )

    return {'total_importados': total, 'status': 'completado'}
```

## Tareas Periódicas con Celery Beat

Celery Beat ejecuta tareas de forma programada, similar a cron:

```python
# Configuración de tareas periódicas
from celery.schedules import crontab

celery.conf.beat_schedule = {
    # Ejecutar cada 30 minutos
    'limpiar-sesiones-expiradas': {
        'task': 'limpiar_sesiones',
        'schedule': 1800.0,  # Cada 1800 segundos (30 min)
    },
    # Ejecutar todos los días a las 2:00 AM
    'backup-diario': {
        'task': 'backup_base_datos',
        'schedule': crontab(hour=2, minute=0),
    },
    # Ejecutar cada lunes a las 9:00 AM
    'reporte-semanal': {
        'task': 'generar_reporte',
        'schedule': crontab(hour=9, minute=0, day_of_week='monday'),
        'args': ('semanal', {'semana': 'actual'}),
    },
    # Ejecutar el día 1 de cada mes
    'facturacion-mensual': {
        'task': 'generar_facturas',
        'schedule': crontab(day_of_month=1, hour=6, minute=0),
    },
}

@celery.task(name='limpiar_sesiones')
def limpiar_sesiones():
    """Elimina sesiones expiradas de la base de datos."""
    # ... lógica de limpieza ...
    return {'eliminadas': 42}

@celery.task(name='backup_base_datos')
def backup_base_datos():
    """Realiza un backup de la base de datos."""
    import subprocess
    subprocess.run([
        'pg_dump', '-U', 'usuario', '-d', 'mi_app',
        '-f', f'/backups/backup_{time.strftime("%Y%m%d")}.sql'
    ])
    return {'status': 'backup completado'}
```

## Manejo de Errores y Reintentos

```python
@celery.task(
    bind=True,
    name='llamar_api_externa',
    max_retries=5,
    default_retry_delay=60  # Esperar 60 segundos entre reintentos
)
def llamar_api_externa(self, url, datos):
    """Llama a una API externa con reintentos automáticos."""
    import requests

    try:
        response = requests.post(url, json=datos, timeout=30)
        response.raise_for_status()
        return response.json()

    except requests.exceptions.ConnectionError as exc:
        # Reintentar con backoff exponencial
        raise self.retry(
            exc=exc,
            countdown=2 ** self.request.retries  # 1, 2, 4, 8, 16 seg
        )

    except requests.exceptions.HTTPError as exc:
        if exc.response.status_code >= 500:
            raise self.retry(exc=exc)  # Reintentar errores del servidor
        raise  # No reintentar errores 4xx (del cliente)

@celery.task(name='tarea_con_callback')
def tarea_con_callback():
    pass

# Callbacks: ejecutar una tarea al completarse otra
from celery import chain

# Encadenar tareas: una después de otra
cadena = chain(
    procesar_imagen.s('/uploads/foto.jpg'),
    enviar_correo.s('admin@site.com', 'Imagen procesada', 'Listo')
)
cadena.apply_async()
```

## Ejecutar Workers y Beat

```bash
# Iniciar un worker de Celery
celery -A app.celery worker --loglevel=info --concurrency=4

# Iniciar Celery Beat (tareas periódicas)
celery -A app.celery beat --loglevel=info

# Worker + Beat juntos (solo para desarrollo)
celery -A app.celery worker --beat --loglevel=info

# Monitorear tareas con Flower (dashboard web)
pip install flower
celery -A app.celery flower --port=5555
```

## Ejercicio Práctico

Implementa un sistema de procesamiento de pedidos con Celery:

1. **Tarea `procesar_pedido(pedido_id)`**: simula el procesamiento de un pedido con 3 pasos (validar pago, preparar envío, notificar cliente), reportando progreso en cada paso.
2. **Ruta POST /pedidos**: crea un pedido y encola la tarea de procesamiento.
3. **Ruta GET /pedidos/<id>/estado**: devuelve el estado actual de la tarea (progreso, completado, error).
4. **Tarea periódica**: cada hora, verifica pedidos pendientes por más de 24 horas y envía alertas.
5. Implementa reintentos con backoff exponencial para la tarea de notificación.
6. Encadena las tareas: `validar_pago → preparar_envio → notificar_cliente`.
7. Monitorea las tareas con Flower.

## Resumen

- **Celery** ejecuta tareas pesadas en segundo plano usando colas de mensajes.
- Usa **Redis** o **RabbitMQ** como broker para las colas de tareas.
- `delay()` encola tareas de forma simple; `apply_async()` ofrece opciones avanzadas.
- **AsyncResult** permite consultar el estado y resultado de tareas en ejecución.
- `self.update_state()` reporta progreso durante la ejecución de una tarea.
- **Celery Beat** programa tareas periódicas con crontab.
- Implementa **reintentos** con backoff exponencial para tareas que pueden fallar.
- `chain()` permite encadenar tareas que dependen del resultado de la anterior.
