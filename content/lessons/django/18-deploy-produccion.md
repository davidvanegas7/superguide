---
title: "Deploy y Producción"
slug: "django-deploy-produccion"
description: "Despliega Django en producción con Gunicorn, Nginx, Docker, variables de entorno, logging y CI/CD"
---
# Deploy y Producción

Llevar una aplicación Django de desarrollo a producción requiere configurar múltiples componentes: un servidor de aplicaciones como Gunicorn, un proxy inverso como Nginx, archivos estáticos, variables de entorno seguras, logging y automatización con CI/CD. Esta lección cubre todo el proceso para un deploy profesional y seguro.

## Arquitectura de Producción

```
Cliente → Nginx (proxy inverso + archivos estáticos)
              → Gunicorn (servidor WSGI/ASGI)
                    → Django (aplicación)
                         → PostgreSQL (base de datos)
                         → Redis (caché + Celery broker)
```

## Gunicorn: Servidor WSGI

Gunicorn es el servidor de aplicaciones más popular para Django en producción:

```bash
pip install gunicorn
```

```bash
# Ejecutar con Gunicorn
gunicorn proyecto.wsgi:application --bind 0.0.0.0:8000

# Configuración recomendada
gunicorn proyecto.wsgi:application \
    --bind 0.0.0.0:8000 \
    --workers 4 \
    --worker-class gthread \
    --threads 2 \
    --timeout 120 \
    --max-requests 1000 \
    --max-requests-jitter 50 \
    --access-logfile /var/log/gunicorn/access.log \
    --error-logfile /var/log/gunicorn/error.log \
    --log-level info
```

**Regla de workers:** Número de CPU × 2 + 1

### WSGI vs ASGI

```bash
# WSGI (peticiones HTTP síncronas)
gunicorn proyecto.wsgi:application

# ASGI (WebSockets, async) - usando Daphne o Uvicorn
pip install uvicorn
uvicorn proyecto.asgi:application --host 0.0.0.0 --port 8000 --workers 4

# O con Daphne
daphne -b 0.0.0.0 -p 8000 proyecto.asgi:application
```

### Systemd Service

```ini
# /etc/systemd/system/gunicorn.service
[Unit]
Description=Gunicorn Django Server
After=network.target

[Service]
User=deploy
Group=www-data
WorkingDirectory=/home/deploy/proyecto
Environment="DJANGO_SETTINGS_MODULE=proyecto.settings.production"
EnvironmentFile=/home/deploy/proyecto/.env
ExecStart=/home/deploy/proyecto/venv/bin/gunicorn \
    proyecto.wsgi:application \
    --bind unix:/run/gunicorn/gunicorn.sock \
    --workers 4 \
    --timeout 120
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl start gunicorn
sudo systemctl enable gunicorn
sudo systemctl status gunicorn
```

## Nginx: Proxy Inverso

```nginx
# /etc/nginx/sites-available/proyecto
server {
    listen 80;
    server_name midominio.com www.midominio.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name midominio.com www.midominio.com;

    ssl_certificate /etc/letsencrypt/live/midominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/midominio.com/privkey.pem;

    # Archivos estáticos
    location /static/ {
        alias /home/deploy/proyecto/staticfiles/;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Archivos de medios (subidos por usuarios)
    location /media/ {
        alias /home/deploy/proyecto/media/;
        expires 7d;
    }

    # Proxy a Gunicorn
    location / {
        proxy_pass http://unix:/run/gunicorn/gunicorn.sock;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_redirect off;

        # Timeouts
        proxy_connect_timeout 60s;
        proxy_read_timeout 120s;
    }

    # WebSockets (si usas Channels)
    location /ws/ {
        proxy_pass http://unix:/run/daphne/daphne.sock;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
    }

    # Seguridad
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    client_max_body_size 10M;
}
```

## Archivos Estáticos: collectstatic

```python
# settings/production.py
STATIC_URL = '/static/'
STATIC_ROOT = '/home/deploy/proyecto/staticfiles/'
STATICFILES_STORAGE = 'django.contrib.staticfiles.storage.ManifestStaticFilesStorage'

MEDIA_URL = '/media/'
MEDIA_ROOT = '/home/deploy/proyecto/media/'
```

```bash
python manage.py collectstatic --noinput
# Recopila todos los archivos estáticos en STATIC_ROOT
```

## Variables de Entorno

Nunca hardcodees configuración sensible. Usa variables de entorno:

```bash
pip install python-decouple
```

```python
# settings/production.py
from decouple import config, Csv

SECRET_KEY = config('DJANGO_SECRET_KEY')
DEBUG = config('DEBUG', default=False, cast=bool)
ALLOWED_HOSTS = config('ALLOWED_HOSTS', cast=Csv())

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.postgresql',
        'NAME': config('DB_NAME'),
        'USER': config('DB_USER'),
        'PASSWORD': config('DB_PASSWORD'),
        'HOST': config('DB_HOST', default='localhost'),
        'PORT': config('DB_PORT', default='5432'),
        'CONN_MAX_AGE': 600,
        'OPTIONS': {
            'connect_timeout': 10,
        },
    }
}

EMAIL_HOST = config('EMAIL_HOST', default='smtp.gmail.com')
EMAIL_PORT = config('EMAIL_PORT', default=587, cast=int)
EMAIL_HOST_USER = config('EMAIL_HOST_USER')
EMAIL_HOST_PASSWORD = config('EMAIL_HOST_PASSWORD')
EMAIL_USE_TLS = True
```

```bash
# .env (NO incluir en git)
DJANGO_SECRET_KEY=tu-clave-secreta-muy-larga-aqui
DEBUG=False
ALLOWED_HOSTS=midominio.com,www.midominio.com
DB_NAME=proyecto_db
DB_USER=proyecto_user
DB_PASSWORD=contraseña_segura
DB_HOST=localhost
DB_PORT=5432
```

## Docker

```dockerfile
# Dockerfile
FROM python:3.12-slim

ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

WORKDIR /app

RUN apt-get update && apt-get install -y \
    libpq-dev gcc \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .
RUN python manage.py collectstatic --noinput

EXPOSE 8000

CMD ["gunicorn", "proyecto.wsgi:application", "--bind", "0.0.0.0:8000", "--workers", "4"]
```

```yaml
# docker-compose.yml
version: '3.8'

services:
  web:
    build: .
    ports:
      - "8000:8000"
    env_file:
      - .env
    depends_on:
      - db
      - redis
    volumes:
      - static_data:/app/staticfiles
      - media_data:/app/media

  db:
    image: postgres:16
    environment:
      POSTGRES_DB: ${DB_NAME}
      POSTGRES_USER: ${DB_USER}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"

  celery:
    build: .
    command: celery -A proyecto worker --loglevel=info
    env_file:
      - .env
    depends_on:
      - redis
      - db

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
      - static_data:/app/staticfiles
      - media_data:/app/media

volumes:
  postgres_data:
  static_data:
  media_data:
```

## Logging

```python
# settings/production.py
LOGGING = {
    'version': 1,
    'disable_existing_loggers': False,
    'formatters': {
        'verbose': {
            'format': '{levelname} {asctime} {module} {process:d} {thread:d} {message}',
            'style': '{',
        },
    },
    'handlers': {
        'file': {
            'level': 'WARNING',
            'class': 'logging.FileHandler',
            'filename': '/var/log/django/app.log',
            'formatter': 'verbose',
        },
        'console': {
            'level': 'INFO',
            'class': 'logging.StreamHandler',
            'formatter': 'verbose',
        },
        'mail_admins': {
            'level': 'ERROR',
            'class': 'django.utils.log.AdminEmailHandler',
        },
    },
    'loggers': {
        'django': {
            'handlers': ['file', 'console'],
            'level': 'WARNING',
            'propagate': True,
        },
        'app': {
            'handlers': ['file', 'console', 'mail_admins'],
            'level': 'INFO',
            'propagate': False,
        },
    },
}
```

## CI/CD con GitHub Actions

```yaml
# .github/workflows/deploy.yml
name: Deploy

on:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_DB: test_db
          POSTGRES_USER: test_user
          POSTGRES_PASSWORD: test_pass
        ports: ['5432:5432']
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-python@v5
        with:
          python-version: '3.12'
      - run: pip install -r requirements.txt
      - run: python manage.py test
        env:
          DB_NAME: test_db
          DB_USER: test_user
          DB_PASSWORD: test_pass

  deploy:
    needs: test
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Deploy al servidor
        run: |
          ssh deploy@servidor "cd /home/deploy/proyecto && \
            git pull origin main && \
            source venv/bin/activate && \
            pip install -r requirements.txt && \
            python manage.py migrate && \
            python manage.py collectstatic --noinput && \
            sudo systemctl restart gunicorn"
```

## Ejercicio Práctico

Prepara tu proyecto Django para producción:

1. Crea un `Dockerfile` y `docker-compose.yml` con Django, PostgreSQL, Redis y Nginx.
2. Configura todas las variables de entorno con `python-decouple`.
3. Configura archivos estáticos con `collectstatic` y Nginx.
4. Implementa logging con rotación de archivos.
5. Crea un pipeline CI/CD que ejecute tests y despliegue automáticamente.

```bash
# Comandos de deploy
docker-compose build
docker-compose up -d
docker-compose exec web python manage.py migrate
docker-compose exec web python manage.py collectstatic --noinput
docker-compose exec web python manage.py createsuperuser
```

## Resumen

Un deploy profesional de Django requiere múltiples componentes trabajando juntos. **Gunicorn** sirve la aplicación WSGI/ASGI, mientras **Nginx** actúa como proxy inverso sirviendo archivos estáticos. **Docker** containeriza toda la infraestructura para deployments reproducibles. Las **variables de entorno** protegen la configuración sensible. **collectstatic** recopila los archivos estáticos para servir eficientemente. El **logging** estructurado ayuda a diagnosticar problemas en producción. Finalmente, **CI/CD** con GitHub Actions automatiza tests y despliegues, asegurando calidad en cada release.
