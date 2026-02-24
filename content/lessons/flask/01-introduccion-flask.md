---
title: "Introducción a Flask"
slug: "introduccion-flask"
description: "Aprende qué es Flask, cómo instalarlo y crear tu primera aplicación web con este microframework de Python."
---

# Introducción a Flask

Flask es uno de los frameworks web más populares de Python. A diferencia de frameworks más grandes como Django, Flask se define como un **microframework**: proporciona lo esencial para construir aplicaciones web sin imponer una estructura rígida ni incluir componentes que quizás no necesites. Esta filosofía lo convierte en una herramienta ideal tanto para principiantes como para desarrolladores experimentados que buscan flexibilidad total.

En esta lección aprenderás qué es Flask, cómo instalarlo, cómo crear una aplicación mínima y cuál es la estructura básica recomendada para un proyecto.

## ¿Qué es Flask?

Flask fue creado por Armin Ronacher en 2010 y está construido sobre dos bibliotecas fundamentales:

- **Werkzeug**: una biblioteca WSGI (Web Server Gateway Interface) que maneja las solicitudes y respuestas HTTP.
- **Jinja2**: un motor de plantillas que permite generar HTML dinámico.

A diferencia de Django, que sigue la filosofía de "baterías incluidas", Flask te da libertad para elegir tus propias herramientas: base de datos, sistema de autenticación, formularios, etc. Esto significa que tú decides qué extensiones usar según las necesidades de tu proyecto.

### Ventajas de Flask

- **Ligero y rápido**: solo incluye lo necesario.
- **Flexible**: sin estructura impuesta, tú defines la arquitectura.
- **Extensible**: miles de extensiones disponibles (Flask-SQLAlchemy, Flask-Login, Flask-WTF, etc.).
- **Documentación excelente**: una de las mejores documentadas en el ecosistema Python.
- **Fácil de aprender**: puedes tener una app funcionando en minutos.

## Instalación de Flask

Antes de instalar Flask, es recomendable crear un **entorno virtual** para aislar las dependencias de tu proyecto.

```python
# Crear un entorno virtual
python3 -m venv venv

# Activar el entorno virtual (Linux/Mac)
source venv/bin/activate

# Activar el entorno virtual (Windows)
venv\Scripts\activate

# Instalar Flask
pip install flask

# Verificar la instalación
pip show flask
```

Al instalar Flask, también se instalan automáticamente sus dependencias: Werkzeug, Jinja2, MarkupSafe, ItsDangerous y Click.

## Tu Primera Aplicación Flask

Crear una aplicación mínima en Flask es sorprendentemente sencillo. Crea un archivo llamado `app.py`:

```python
# app.py
from flask import Flask

# Crear la instancia de la aplicación
app = Flask(__name__)

# Definir una ruta
@app.route('/')
def inicio():
    return '¡Hola, mundo! Bienvenido a Flask.'

# Ejecutar la aplicación
if __name__ == '__main__':
    app.run()
```

Analicemos cada parte:

1. **`Flask(__name__)`**: crea una instancia de la aplicación. El argumento `__name__` le dice a Flask dónde buscar recursos como plantillas y archivos estáticos.
2. **`@app.route('/')`**: es un decorador que asocia la URL `/` con la función `inicio()`.
3. **`app.run()`**: inicia el servidor de desarrollo integrado.

## Ejecutar la Aplicación

Hay dos formas principales de ejecutar una aplicación Flask:

### Método 1: Usando `flask run`

```bash
# Definir la variable de entorno
export FLASK_APP=app.py

# Ejecutar el servidor
flask run

# El servidor estará disponible en http://127.0.0.1:5000
```

### Método 2: Ejecutando el archivo directamente

```bash
python app.py
```

Ambos métodos inician el servidor de desarrollo en `http://127.0.0.1:5000`.

## Modo Debug

Durante el desarrollo, el **modo debug** es esencial. Ofrece dos características fundamentales:

- **Recarga automática**: el servidor se reinicia automáticamente cuando detecta cambios en el código.
- **Depurador interactivo**: muestra información detallada de errores directamente en el navegador.

```bash
# Activar modo debug con flask run
export FLASK_DEBUG=1
flask run

# O directamente en el código
if __name__ == '__main__':
    app.run(debug=True)
```

También puedes especificar el host y el puerto:

```python
if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=8080)
```

> **Advertencia**: Nunca uses el modo debug en un servidor de producción. El depurador interactivo permite ejecutar código arbitrario en el servidor.

## Estructura Básica de un Proyecto

Para proyectos pequeños, un solo archivo puede ser suficiente. Sin embargo, a medida que tu aplicación crece, es importante organizar el código. Esta es la estructura recomendada:

```
mi_proyecto/
├── venv/                  # Entorno virtual
├── app/
│   ├── __init__.py        # Inicialización de la aplicación
│   ├── routes.py          # Definición de rutas
│   ├── models.py          # Modelos de base de datos
│   ├── templates/         # Plantillas HTML
│   │   ├── base.html
│   │   └── inicio.html
│   └── static/            # Archivos estáticos (CSS, JS, imágenes)
│       ├── css/
│       ├── js/
│       └── img/
├── config.py              # Configuración de la aplicación
├── requirements.txt       # Dependencias del proyecto
└── run.py                 # Punto de entrada
```

Veamos cómo se implementa esta estructura:

```python
# config.py
class Config:
    SECRET_KEY = 'mi-clave-secreta'
    DEBUG = True

# app/__init__.py
from flask import Flask
from config import Config

def create_app():
    app = Flask(__name__)
    app.config.from_object(Config)

    from app.routes import main
    app.register_blueprint(main)

    return app

# app/routes.py
from flask import Blueprint, render_template

main = Blueprint('main', __name__)

@main.route('/')
def inicio():
    return render_template('inicio.html')

# run.py
from app import create_app

app = create_app()

if __name__ == '__main__':
    app.run()
```

Para gestionar las dependencias del proyecto:

```bash
# Generar el archivo de dependencias
pip freeze > requirements.txt

# Instalar dependencias desde el archivo
pip install -r requirements.txt
```

## Ejercicio Práctico

Crea una aplicación Flask que tenga tres páginas:

1. **Página de inicio** (`/`): muestra un mensaje de bienvenida.
2. **Página "Acerca de"** (`/acerca`): muestra información sobre la aplicación.
3. **Página de contacto** (`/contacto`): muestra datos de contacto ficticios.

```python
# ejercicio.py
from flask import Flask

app = Flask(__name__)

@app.route('/')
def inicio():
    return '''
    <h1>Bienvenido a Mi Sitio Web</h1>
    <nav>
        <a href="/acerca">Acerca de</a> |
        <a href="/contacto">Contacto</a>
    </nav>
    <p>Esta es mi primera aplicación con Flask.</p>
    '''

@app.route('/acerca')
def acerca():
    return '''
    <h1>Acerca de Nosotros</h1>
    <nav>
        <a href="/">Inicio</a> |
        <a href="/contacto">Contacto</a>
    </nav>
    <p>Somos una empresa dedicada al desarrollo web con Python.</p>
    '''

@app.route('/contacto')
def contacto():
    return '''
    <h1>Contacto</h1>
    <nav>
        <a href="/">Inicio</a> |
        <a href="/acerca">Acerca de</a>
    </nav>
    <p>Email: info@ejemplo.com</p>
    <p>Teléfono: +34 600 123 456</p>
    '''

if __name__ == '__main__':
    app.run(debug=True)
```

Ejecuta la aplicación con `python ejercicio.py` y navega entre las tres páginas para verificar que todo funciona correctamente.

## Resumen

- **Flask** es un microframework web de Python basado en Werkzeug y Jinja2.
- Se instala fácilmente con `pip install flask` dentro de un entorno virtual.
- Una aplicación mínima requiere solo una instancia de `Flask` y al menos una ruta definida con `@app.route()`.
- El modo debug (`debug=True`) activa la recarga automática y el depurador interactivo durante el desarrollo.
- La estructura de proyecto recomendada separa rutas, modelos, plantillas y archivos estáticos en directorios organizados.
- Flask no impone una arquitectura rígida, lo que te da libertad total para organizar tu código como mejor convenga a tu proyecto.
