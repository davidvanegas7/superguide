---
title: "Blueprints y Estructura"
slug: "flask-blueprints-estructura"
description: "Aprende a organizar tu aplicación Flask en módulos reutilizables con Blueprints, el patrón Application Factory y configuración por entornos."
---

# Blueprints y Estructura

Cuando tu aplicación Flask crece más allá de un simple archivo `app.py`, necesitas una forma de organizar el código en módulos. Los **Blueprints** son el mecanismo que Flask proporciona para dividir tu aplicación en componentes reutilizables y mantenibles. Junto con el patrón **Application Factory** y una gestión adecuada de la configuración, tendrás una arquitectura profesional y escalable.

## ¿Qué es un Blueprint?

Un Blueprint es un objeto que funciona de manera similar a la aplicación Flask principal, pero **no es una aplicación en sí mismo**. Te permite definir rutas, plantillas, archivos estáticos y manejadores de errores de forma modular, y luego registrarlos en la aplicación principal.

Piensa en un Blueprint como un "plano" o "borrador" de una parte de tu aplicación que se activa al registrarlo.

## Crear un Blueprint Básico

```python
# blueprints/auth.py
from flask import Blueprint, render_template, request, redirect, url_for

# Crear el Blueprint
auth_bp = Blueprint('auth', __name__, 
                     template_folder='templates',
                     static_folder='static',
                     url_prefix='/auth')

@auth_bp.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        # Procesar login
        return redirect(url_for('main.inicio'))
    return render_template('auth/login.html')

@auth_bp.route('/registro', methods=['GET', 'POST'])
def registro():
    if request.method == 'POST':
        # Procesar registro
        return redirect(url_for('auth.login'))
    return render_template('auth/registro.html')

@auth_bp.route('/logout')
def logout():
    # Cerrar sesión
    return redirect(url_for('main.inicio'))
```

Los parámetros del constructor `Blueprint()`:

| Parámetro         | Descripción                                          |
|-------------------|------------------------------------------------------|
| `'auth'`          | Nombre del Blueprint (usado en `url_for`)            |
| `__name__`        | Módulo de importación (para localizar recursos)      |
| `template_folder` | Carpeta de plantillas específicas del Blueprint      |
| `static_folder`   | Carpeta de archivos estáticos del Blueprint          |
| `url_prefix`      | Prefijo de URL para todas las rutas del Blueprint    |

## Registrar un Blueprint

Para que un Blueprint funcione, debes registrarlo en la aplicación principal:

```python
# app.py
from flask import Flask
from blueprints.auth import auth_bp
from blueprints.main import main_bp
from blueprints.api import api_bp

app = Flask(__name__)

# Registrar Blueprints
app.register_blueprint(auth_bp)                         # /auth/login, /auth/registro
app.register_blueprint(main_bp)                         # /, /about
app.register_blueprint(api_bp, url_prefix='/api/v1')    # Puedes sobrescribir el prefijo

if __name__ == '__main__':
    app.run(debug=True)
```

## Estructura de Proyecto Recomendada

Para una aplicación Flask de tamaño mediano a grande, esta es la estructura recomendada:

```
mi_proyecto/
├── config.py
├── run.py
├── requirements.txt
├── app/
│   ├── __init__.py          # Application Factory
│   ├── extensions.py        # Extensiones (db, migrate, login_manager)
│   ├── models/
│   │   ├── __init__.py
│   │   ├── usuario.py
│   │   ├── articulo.py
│   │   └── comentario.py
│   ├── auth/
│   │   ├── __init__.py      # Blueprint de autenticación
│   │   ├── routes.py
│   │   ├── forms.py
│   │   └── templates/
│   │       └── auth/
│   │           ├── login.html
│   │           └── registro.html
│   ├── main/
│   │   ├── __init__.py      # Blueprint principal
│   │   ├── routes.py
│   │   └── templates/
│   │       └── main/
│   │           ├── inicio.html
│   │           └── about.html
│   ├── api/
│   │   ├── __init__.py      # Blueprint API
│   │   ├── routes.py
│   │   └── schemas.py
│   ├── templates/
│   │   └── base.html        # Plantilla base compartida
│   └── static/
│       ├── css/
│       ├── js/
│       └── img/
├── migrations/
└── tests/
```

### Ejemplo de Blueprint con Directorio Propio

```python
# app/auth/__init__.py
from flask import Blueprint

auth_bp = Blueprint('auth', __name__, template_folder='templates')

from app.auth import routes  # Importar rutas al final para evitar importaciones circulares
```

```python
# app/auth/routes.py
from flask import render_template, request, redirect, url_for, flash
from app.auth import auth_bp
from app.auth.forms import FormularioLogin, FormularioRegistro

@auth_bp.route('/login', methods=['GET', 'POST'])
def login():
    form = FormularioLogin()
    if form.validate_on_submit():
        # Lógica de autenticación
        flash('Sesión iniciada correctamente', 'success')
        return redirect(url_for('main.inicio'))
    return render_template('auth/login.html', form=form)
```

## Application Factory Pattern

El patrón **Application Factory** consiste en crear la aplicación dentro de una función en lugar de como una variable global. Esto permite:

- Crear múltiples instancias de la aplicación (para testing).
- Configurar la aplicación según el entorno.
- Evitar problemas con importaciones circulares.

```python
# app/__init__.py
from flask import Flask
from app.extensions import db, migrate, login_manager

def create_app(config_name='default'):
    app = Flask(__name__)
    
    # Cargar configuración
    app.config.from_object(config[config_name])
    
    # Inicializar extensiones
    db.init_app(app)
    migrate.init_app(app, db)
    login_manager.init_app(app)
    
    # Registrar Blueprints
    from app.auth import auth_bp
    app.register_blueprint(auth_bp, url_prefix='/auth')
    
    from app.main import main_bp
    app.register_blueprint(main_bp)
    
    from app.api import api_bp
    app.register_blueprint(api_bp, url_prefix='/api/v1')
    
    # Registrar manejadores de error
    registrar_errores(app)
    
    return app

def registrar_errores(app):
    @app.errorhandler(404)
    def not_found(error):
        return render_template('errores/404.html'), 404
    
    @app.errorhandler(500)
    def internal_error(error):
        db.session.rollback()
        return render_template('errores/500.html'), 500
```

### Extensiones Separadas

```python
# app/extensions.py
from flask_sqlalchemy import SQLAlchemy
from flask_migrate import Migrate
from flask_login import LoginManager

db = SQLAlchemy()
migrate = Migrate()
login_manager = LoginManager()
login_manager.login_view = 'auth.login'
login_manager.login_message = 'Debes iniciar sesión para acceder.'
```

### Archivo de Ejecución

```python
# run.py
from app import create_app

app = create_app('development')

if __name__ == '__main__':
    app.run()
```

## Gestión de Configuración

Usa clases para organizar la configuración por entornos:

```python
# config.py
import os

class Config:
    """Configuración base."""
    SECRET_KEY = os.environ.get('SECRET_KEY', 'clave-por-defecto-cambiar')
    SQLALCHEMY_TRACK_MODIFICATIONS = False
    
    # Configuraciones comunes
    ITEMS_POR_PAGINA = 10

class DevelopmentConfig(Config):
    """Configuración de desarrollo."""
    DEBUG = True
    SQLALCHEMY_DATABASE_URI = os.environ.get('DEV_DATABASE_URL',
                                              'sqlite:///dev.db')
    SQLALCHEMY_ECHO = True  # Mostrar consultas SQL

class TestingConfig(Config):
    """Configuración de pruebas."""
    TESTING = True
    SQLALCHEMY_DATABASE_URI = 'sqlite:///:memory:'
    WTF_CSRF_ENABLED = False  # Desactivar CSRF en tests

class ProductionConfig(Config):
    """Configuración de producción."""
    SQLALCHEMY_DATABASE_URI = os.environ.get('DATABASE_URL')
    
    @classmethod
    def init_app(cls, app):
        # Configuraciones específicas de producción
        import logging
        handler = logging.StreamHandler()
        handler.setLevel(logging.WARNING)
        app.logger.addHandler(handler)

config = {
    'development': DevelopmentConfig,
    'testing': TestingConfig,
    'production': ProductionConfig,
    'default': DevelopmentConfig,
}
```

### Variables de Entorno con `.env`

Usa `python-dotenv` para cargar variables de entorno:

```bash
pip install python-dotenv
```

```
# .env
FLASK_APP=run.py
FLASK_DEBUG=1
SECRET_KEY=tu-clave-ultra-secreta
DATABASE_URL=postgresql://user:pass@localhost/mi_app
```

Flask carga automáticamente `.env` si `python-dotenv` está instalado.

## `url_for()` con Blueprints

Cuando usas Blueprints, debes incluir el nombre del Blueprint en `url_for()`:

```python
# Dentro del mismo Blueprint
url_for('.login')             # Blueprint actual + endpoint 'login'

# Entre Blueprints diferentes
url_for('auth.login')         # Blueprint 'auth', endpoint 'login'
url_for('main.inicio')        # Blueprint 'main', endpoint 'inicio'
url_for('api.listar_usuarios') # Blueprint 'api', endpoint 'listar_usuarios'
```

En las plantillas:

```html
<a href="{{ url_for('auth.login') }}">Iniciar sesión</a>
<a href="{{ url_for('main.inicio') }}">Inicio</a>
```

## Ejercicio Práctico

Refactoriza una aplicación Flask monolítica en una estructura modular:

1. Crea la estructura de directorios con tres Blueprints: `main` (páginas públicas), `auth` (autenticación) y `admin` (panel de administración).
2. Implementa el **Application Factory Pattern** en `app/__init__.py`.
3. Separa las extensiones en `app/extensions.py`.
4. Crea un archivo `config.py` con configuraciones para desarrollo, testing y producción.
5. Registra los tres Blueprints con prefijos URL adecuados:
   - `main`: sin prefijo (raíz `/`)
   - `auth`: prefijo `/auth`
   - `admin`: prefijo `/admin`
6. Cada Blueprint debe tener al menos dos rutas funcionales.

```python
# Estructura mínima de cada Blueprint
# app/admin/__init__.py
from flask import Blueprint

admin_bp = Blueprint('admin', __name__, template_folder='templates')

from app.admin import routes

# app/admin/routes.py
from flask import render_template
from app.admin import admin_bp

@admin_bp.route('/dashboard')
def dashboard():
    return render_template('admin/dashboard.html')

@admin_bp.route('/usuarios')
def gestionar_usuarios():
    return render_template('admin/usuarios.html')
```

## Resumen

Los Blueprints son la clave para escalar aplicaciones Flask de manera organizada. Aprendiste a crear y registrar Blueprints, organizar tu proyecto en una estructura modular profesional, implementar el patrón Application Factory para mayor flexibilidad, y gestionar la configuración por entornos. Esta arquitectura te prepara para construir aplicaciones Flask de cualquier tamaño manteniendo un código limpio y mantenible.
