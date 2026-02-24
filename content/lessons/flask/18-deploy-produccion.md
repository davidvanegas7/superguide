---
title: "Deploy y Producción"
slug: "flask-deploy-produccion"
description: "Despliega tu aplicación Flask en producción con Gunicorn, Nginx, Docker y servicios en la nube como Heroku, Railway y Render."
---

# Deploy y Producción

Desarrollar una aplicación Flask localmente es solo la mitad del trabajo. Llevarla a producción requiere un servidor WSGI adecuado, un proxy reverso, variables de entorno, logging y una estrategia de despliegue automatizada. En esta lección aprenderás todo lo necesario para poner tu aplicación en producción de forma profesional.

## ¿Por qué no usar app.run() en producción?

El servidor de desarrollo de Flask (`app.run()`) no está diseñado para producción:

- Maneja una sola petición a la vez.
- No es eficiente ni seguro.
- No tiene balanceo de carga ni soporte para procesos múltiples.

En producción se usa un servidor **WSGI** como **Gunicorn** o **uWSGI**.

## Gunicorn: Servidor WSGI

Gunicorn es el servidor WSGI más popular para aplicaciones Python.

```bash
# Instalar Gunicorn
pip install gunicorn
```

### Archivo WSGI de entrada

```python
# wsgi.py
from app import create_app

app = create_app()

if __name__ == '__main__':
    app.run()
```

### Ejecutar con Gunicorn

```bash
# Básico: 4 workers
gunicorn wsgi:app --workers 4 --bind 0.0.0.0:8000

# Con configuración avanzada
gunicorn wsgi:app \
    --workers 4 \                  # Número de procesos worker
    --threads 2 \                  # Threads por worker
    --bind 0.0.0.0:8000 \         # Dirección y puerto
    --timeout 120 \                # Timeout por petición
    --access-logfile /var/log/gunicorn/access.log \
    --error-logfile /var/log/gunicorn/error.log \
    --log-level info \
    --max-requests 1000 \          # Reiniciar worker después de N peticiones
    --max-requests-jitter 50 \     # Jitter para evitar reinicio simultáneo
    --preload                      # Cargar la app antes de fork
```

### Archivo de configuración de Gunicorn

```python
# gunicorn.conf.py
import multiprocessing

# Servidor
bind = "0.0.0.0:8000"
workers = multiprocessing.cpu_count() * 2 + 1  # Fórmula recomendada
threads = 2
worker_class = "gthread"  # O "gevent" para async

# Timeouts
timeout = 120
graceful_timeout = 30
keepalive = 5

# Logging
accesslog = "/var/log/gunicorn/access.log"
errorlog = "/var/log/gunicorn/error.log"
loglevel = "info"

# Reinicio de workers
max_requests = 1000
max_requests_jitter = 50

# Seguridad
limit_request_line = 8190
limit_request_fields = 100
```

```bash
# Usar el archivo de configuración
gunicorn wsgi:app -c gunicorn.conf.py
```

## Nginx como Reverse Proxy

Nginx se coloca delante de Gunicorn para manejar archivos estáticos, SSL, compresión y balanceo de carga.

```nginx
# /etc/nginx/sites-available/miapp
server {
    listen 80;
    server_name midominio.com www.midominio.com;

    # Redirigir HTTP a HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name midominio.com www.midominio.com;

    # Certificado SSL (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/midominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/midominio.com/privkey.pem;

    # Archivos estáticos servidos directamente por Nginx
    location /static/ {
        alias /home/ubuntu/miapp/static/;
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }

    # Archivos de uploads
    location /uploads/ {
        alias /home/ubuntu/miapp/uploads/;
        expires 7d;
    }

    # Proxy a Gunicorn
    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_redirect off;

        # Timeouts
        proxy_connect_timeout 60s;
        proxy_read_timeout 120s;
    }

    # Límite de tamaño de upload
    client_max_body_size 16M;

    # Compresión gzip
    gzip on;
    gzip_types text/plain text/css application/json application/javascript;
    gzip_min_length 1000;
}
```

```bash
# Activar el sitio y reiniciar Nginx
sudo ln -s /etc/nginx/sites-available/miapp /etc/nginx/sites-enabled/
sudo nginx -t  # Verificar configuración
sudo systemctl restart nginx

# Instalar certificado SSL con Let's Encrypt
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d midominio.com -d www.midominio.com
```

## Docker y Docker Compose

Docker empaqueta tu aplicación con todas sus dependencias para despliegues consistentes.

### Dockerfile

```dockerfile
# Dockerfile
FROM python:3.12-slim

# Variables de entorno
ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

# Directorio de trabajo
WORKDIR /app

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y --no-install-recommends \
    gcc libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Copiar e instalar dependencias de Python
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copiar el código de la aplicación
COPY . .

# Crear usuario no-root por seguridad
RUN adduser --disabled-password --no-create-home appuser
USER appuser

# Puerto expuesto
EXPOSE 8000

# Comando de inicio
CMD ["gunicorn", "wsgi:app", "-c", "gunicorn.conf.py"]
```

### Docker Compose

```yaml
# docker-compose.yml
version: '3.8'

services:
  web:
    build: .
    ports:
      - "8000:8000"
    environment:
      - FLASK_ENV=production
      - DATABASE_URL=postgresql://postgres:password@db:5432/miapp
      - REDIS_URL=redis://redis:6379/0
      - SECRET_KEY=${SECRET_KEY}
    depends_on:
      db:
        condition: service_healthy
      redis:
        condition: service_started
    volumes:
      - uploads:/app/uploads
    restart: unless-stopped

  db:
    image: postgres:16-alpine
    environment:
      - POSTGRES_DB=miapp
      - POSTGRES_USER=postgres
      - POSTGRES_PASSWORD=password
    volumes:
      - postgres_data:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U postgres"]
      interval: 5s
      timeout: 5s
      retries: 5

  redis:
    image: redis:7-alpine
    volumes:
      - redis_data:/data

  celery:
    build: .
    command: celery -A app.celery worker --loglevel=info
    environment:
      - DATABASE_URL=postgresql://postgres:password@db:5432/miapp
      - REDIS_URL=redis://redis:6379/0
    depends_on:
      - db
      - redis

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
      - ./static:/usr/share/nginx/static
    depends_on:
      - web

volumes:
  postgres_data:
  redis_data:
  uploads:
```

```bash
# Construir y ejecutar
docker-compose up --build -d

# Ver logs
docker-compose logs -f web

# Ejecutar migraciones
docker-compose exec web flask db upgrade

# Parar todo
docker-compose down
```

## Variables de Entorno y Configuración

```python
# config.py
import os

class Config:
    """Configuración base."""
    SECRET_KEY = os.environ.get('SECRET_KEY', 'dev-key-no-usar-en-prod')
    SQLALCHEMY_TRACK_MODIFICATIONS = False

class ProductionConfig(Config):
    """Configuración de producción."""
    DEBUG = False
    SQLALCHEMY_DATABASE_URI = os.environ.get('DATABASE_URL')
    REDIS_URL = os.environ.get('REDIS_URL')

class DevelopmentConfig(Config):
    """Configuración de desarrollo."""
    DEBUG = True
    SQLALCHEMY_DATABASE_URI = 'sqlite:///dev.db'

class TestingConfig(Config):
    """Configuración de tests."""
    TESTING = True
    SQLALCHEMY_DATABASE_URI = 'sqlite:///:memory:'

# Seleccionar configuración según el entorno
config = {
    'production': ProductionConfig,
    'development': DevelopmentConfig,
    'testing': TestingConfig,
    'default': DevelopmentConfig
}
```

## Logging en Producción

```python
import logging
from logging.handlers import RotatingFileHandler

def configurar_logging(app):
    """Configura logging para producción."""
    if not app.debug:
        # Archivo de log rotativo (10MB máximo, 10 archivos)
        handler = RotatingFileHandler(
            'logs/app.log',
            maxBytes=10 * 1024 * 1024,
            backupCount=10
        )
        handler.setFormatter(logging.Formatter(
            '%(asctime)s %(levelname)s: %(message)s '
            '[en %(pathname)s:%(lineno)d]'
        ))
        handler.setLevel(logging.INFO)
        app.logger.addHandler(handler)
        app.logger.setLevel(logging.INFO)
        app.logger.info('Aplicación iniciada')
```

## Health Checks

```python
@app.route('/health')
def health_check():
    """Endpoint de salud para monitoreo y load balancers."""
    checks = {
        'app': 'ok',
        'database': 'ok',
        'redis': 'ok'
    }

    # Verificar base de datos
    try:
        db.session.execute(text('SELECT 1'))
    except Exception as e:
        checks['database'] = str(e)

    # Verificar Redis
    try:
        redis_client.ping()
    except Exception as e:
        checks['redis'] = str(e)

    status = 200 if all(v == 'ok' for v in checks.values()) else 503
    return jsonify(checks), status
```

## Deploy en Plataformas Cloud

### Render

```yaml
# render.yaml
services:
  - type: web
    name: mi-flask-app
    env: python
    buildCommand: pip install -r requirements.txt
    startCommand: gunicorn wsgi:app
    envVars:
      - key: SECRET_KEY
        generateValue: true
      - key: DATABASE_URL
        fromDatabase:
          name: mi-db
          property: connectionString

databases:
  - name: mi-db
    plan: free
```

### Heroku

```
# Procfile
web: gunicorn wsgi:app --workers 4 --threads 2
worker: celery -A app.celery worker --loglevel=info
```

```bash
# Deploy en Heroku
heroku create mi-flask-app
heroku addons:create heroku-postgresql:mini
heroku config:set SECRET_KEY=$(python -c 'import secrets; print(secrets.token_hex(32))')
git push heroku main
heroku run flask db upgrade
```

## Ejercicio Práctico

Despliega tu aplicación Flask en producción:

1. Crea un `Dockerfile` optimizado con multi-stage build y usuario no-root.
2. Configura `docker-compose.yml` con Flask, PostgreSQL, Redis y Nginx.
3. Implementa configuración por entornos (dev/staging/production) con `config.py`.
4. Configura Gunicorn con workers basados en CPU y logging a archivo.
5. Agrega un endpoint `/health` que verifique la BD y Redis.
6. Configura Nginx con SSL, compresión y servicio de archivos estáticos.
7. Despliega en Render o Railway con variables de entorno.
8. Implementa logging con rotación de archivos.

## Resumen

- **Nunca uses `app.run()`** en producción; usa **Gunicorn** como servidor WSGI.
- **Nginx** actúa como reverse proxy para SSL, archivos estáticos y balanceo de carga.
- **Docker** empaqueta tu app con sus dependencias para despliegues consistentes.
- Usa **variables de entorno** para configuración sensible; nunca credenciales en el código.
- Implementa **health checks** para que los load balancers monitoreen tu servicio.
- El **logging** con rotación de archivos ayuda a diagnosticar problemas en producción.
- Plataformas como **Render**, **Railway** y **Heroku** simplifican el despliegue.
- Configura diferentes **entornos** (development, testing, production) con clases de configuración.
