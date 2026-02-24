---
title: "Templates con Jinja2"
slug: "flask-templates-jinja2"
description: "Aprende a usar el motor de plantillas Jinja2 para generar HTML dinámico con herencia de templates, filtros y macros."
---

# Templates con Jinja2

Retornar HTML directamente desde funciones de vista es poco práctico y difícil de mantener. Flask utiliza **Jinja2** como motor de plantillas para separar la lógica de negocio de la presentación. Jinja2 permite crear archivos HTML dinámicos con expresiones, estructuras de control, herencia de plantillas y mucho más.

## Configuración Básica

Por defecto, Flask busca las plantillas en una carpeta llamada `templates/` dentro del directorio raíz de tu proyecto:

```
mi_proyecto/
├── app.py
├── templates/
│   ├── base.html
│   ├── inicio.html
│   └── perfil.html
```

## `render_template()`

Para renderizar una plantilla, usa la función `render_template()`:

```python
from flask import Flask, render_template

app = Flask(__name__)

@app.route('/')
def inicio():
    return render_template('inicio.html', titulo='Bienvenido', usuario='Ana')
```

Los argumentos con nombre después del nombre del archivo se pasan como **variables** a la plantilla. En `inicio.html`:

```html
<!DOCTYPE html>
<html>
<head>
    <title>{{ titulo }}</title>
</head>
<body>
    <h1>Hola, {{ usuario }}</h1>
</body>
</html>
```

## Sintaxis de Jinja2

Jinja2 utiliza delimitadores especiales para distinguir el código de plantilla del HTML estático:

| Delimitador       | Propósito                            |
|-------------------|--------------------------------------|
| `{{ }}`           | Expresiones (imprimir valores)       |
| `{% %}`           | Sentencias (lógica de control)       |
| `{# #}`           | Comentarios (no se renderizan)       |

### Expresiones `{{ }}`

Las expresiones evalúan y muestran valores en la salida:

```html
<p>Nombre: {{ usuario.nombre }}</p>
<p>Total: {{ precio * cantidad }}</p>
<p>Saludo: {{ "Hola, " + nombre }}</p>
<p>Tipo: {{ tipo|default("Sin tipo") }}</p>
```

Jinja2 **escapa automáticamente** el HTML para prevenir ataques XSS. Si necesitas insertar HTML sin escapar (¡con cuidado!):

```html
{{ contenido_html|safe }}
```

### Condicionales `{% if %}`

```html
{% if usuario %}
    <h1>Bienvenido, {{ usuario.nombre }}</h1>
    {% if usuario.es_admin %}
        <a href="/admin">Panel de administración</a>
    {% endif %}
{% else %}
    <h1>Bienvenido, visitante</h1>
    <a href="/login">Iniciar sesión</a>
{% endif %}
```

### Bucles `{% for %}`

```html
<h2>Productos Disponibles</h2>
<ul>
{% for producto in productos %}
    <li>
        {{ producto.nombre }} - ${{ producto.precio }}
        {% if producto.en_oferta %}
            <span class="oferta">¡En oferta!</span>
        {% endif %}
    </li>
{% else %}
    <li>No hay productos disponibles.</li>
{% endfor %}
</ul>
```

Dentro de un bucle `for`, Jinja2 proporciona la variable especial `loop`:

```html
{% for item in items %}
    <p>{{ loop.index }}. {{ item }}</p>       {# Índice desde 1 #}
    <p>{{ loop.index0 }}. {{ item }}</p>      {# Índice desde 0 #}
    {% if loop.first %}(Primero){% endif %}
    {% if loop.last %}(Último){% endif %}
    <p>Total: {{ loop.length }}</p>
{% endfor %}
```

## Filtros

Los filtros transforman valores usando la sintaxis `valor|filtro`. Flask y Jinja2 incluyen muchos filtros predefinidos:

```html
{# Transformación de texto #}
<p>{{ nombre|upper }}</p>          {# MAYÚSCULAS #}
<p>{{ nombre|lower }}</p>          {# minúsculas #}
<p>{{ nombre|capitalize }}</p>     {# Primera letra en mayúscula #}
<p>{{ nombre|title }}</p>          {# Cada Palabra Capitalizada #}

{# Números y formato #}
<p>{{ precio|round(2) }}</p>       {# Redondear a 2 decimales #}
<p>{{ lista|length }}</p>          {# Longitud de lista #}

{# Valores por defecto #}
<p>{{ bio|default("Sin biografía") }}</p>

{# Listas #}
<p>{{ tags|join(", ") }}</p>       {# Unir elementos #}
<p>{{ numeros|sort }}</p>          {# Ordenar #}
<p>{{ items|first }}</p>           {# Primer elemento #}
<p>{{ items|last }}</p>            {# Último elemento #}

{# Truncar texto #}
<p>{{ descripcion|truncate(100) }}</p>
```

### Encadenar Filtros

Puedes encadenar múltiples filtros:

```html
<p>{{ nombre|lower|capitalize }}</p>
<p>{{ descripcion|striptags|truncate(50) }}</p>
```

### Filtros Personalizados

Puedes crear tus propios filtros en Python:

```python
@app.template_filter('moneda')
def filtro_moneda(valor):
    return f"${valor:,.2f}"

@app.template_filter('fecha_es')
def filtro_fecha(fecha):
    meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
             'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre']
    return f"{fecha.day} de {meses[fecha.month - 1]} de {fecha.year}"
```

En la plantilla:

```html
<p>Precio: {{ 1499.9|moneda }}</p>       {# $1,499.90 #}
<p>Fecha: {{ fecha|fecha_es }}</p>       {# 15 de marzo de 2026 #}
```

## Herencia de Plantillas

La herencia es una de las características más poderosas de Jinja2. Permite definir una plantilla **base** con bloques que las plantillas hijas pueden sobrescribir.

### Plantilla Base (`base.html`)

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block titulo %}Mi Sitio{% endblock %}</title>
    <link rel="stylesheet" href="{{ url_for('static', filename='css/estilos.css') }}">
    {% block estilos_extra %}{% endblock %}
</head>
<body>
    <nav>
        <a href="{{ url_for('inicio') }}">Inicio</a>
        <a href="{{ url_for('productos') }}">Productos</a>
        <a href="{{ url_for('contacto') }}">Contacto</a>
    </nav>

    <main>
        {% block contenido %}{% endblock %}
    </main>

    <footer>
        <p>&copy; 2026 Mi Sitio. Todos los derechos reservados.</p>
    </footer>

    {% block scripts %}{% endblock %}
</body>
</html>
```

### Plantilla Hija (`productos.html`)

```html
{% extends "base.html" %}

{% block titulo %}Productos - Mi Sitio{% endblock %}

{% block contenido %}
<h1>Nuestros Productos</h1>
<div class="productos-grid">
    {% for producto in productos %}
    <div class="producto-card">
        <h3>{{ producto.nombre }}</h3>
        <p>{{ producto.descripcion|truncate(80) }}</p>
        <span class="precio">{{ producto.precio|moneda }}</span>
    </div>
    {% endfor %}
</div>
{% endblock %}

{% block scripts %}
<script src="{{ url_for('static', filename='js/productos.js') }}"></script>
{% endblock %}
```

### `{{ super() }}`

Si quieres **agregar** contenido a un bloque padre en lugar de reemplazarlo:

```html
{% block estilos_extra %}
    {{ super() }}
    <link rel="stylesheet" href="{{ url_for('static', filename='css/productos.css') }}">
{% endblock %}
```

## Macros

Las macros son como funciones reutilizables dentro de las plantillas. Son ideales para componentes que se repiten:

```html
{# macros/formularios.html #}
{% macro campo_texto(nombre, etiqueta, tipo="text", valor="") %}
<div class="campo-form">
    <label for="{{ nombre }}">{{ etiqueta }}</label>
    <input type="{{ tipo }}" id="{{ nombre }}" name="{{ nombre }}" value="{{ valor }}">
</div>
{% endmacro %}

{% macro boton(texto, tipo="submit", clase="btn-primary") %}
<button type="{{ tipo }}" class="{{ clase }}">{{ texto }}</button>
{% endmacro %}
```

Para usar las macros en otra plantilla:

```html
{% from "macros/formularios.html" import campo_texto, boton %}

<form method="POST">
    {{ campo_texto("nombre", "Nombre completo") }}
    {{ campo_texto("email", "Correo electrónico", tipo="email") }}
    {{ campo_texto("password", "Contraseña", tipo="password") }}
    {{ boton("Registrarse") }}
</form>
```

## Include

Puedes incluir fragmentos de plantilla con `{% include %}`:

```html
{% include "partials/header.html" %}

<main>{{ contenido }}</main>

{% include "partials/footer.html" %}
```

## Ejercicio Práctico

Crea un sistema de plantillas para un blog con las siguientes características:

1. **`base.html`** — Plantilla base con navegación, bloques para título, contenido y scripts.
2. **`inicio.html`** — Hereda de `base.html`, muestra una lista de posts pasados desde la vista.
3. **`post.html`** — Hereda de `base.html`, muestra el detalle de un post individual.
4. Crea una **macro** para renderizar la tarjeta de un post (título, autor, fecha, extracto).
5. Usa al menos tres **filtros** diferentes.

Vista en Python:

```python
from flask import Flask, render_template
from datetime import datetime

app = Flask(__name__)

POSTS = [
    {'id': 1, 'titulo': 'Aprendiendo Flask', 'autor': 'Ana',
     'fecha': datetime(2026, 1, 15), 'contenido': 'Flask es un microframework...'},
    {'id': 2, 'titulo': 'Templates con Jinja2', 'autor': 'Carlos',
     'fecha': datetime(2026, 2, 1), 'contenido': 'Jinja2 es el motor de plantillas...'},
]

@app.route('/')
def inicio():
    return render_template('inicio.html', posts=POSTS)

@app.route('/post/<int:id>')
def ver_post(id):
    post = next((p for p in POSTS if p['id'] == id), None)
    if not post:
        abort(404)
    return render_template('post.html', post=post)
```

## Resumen

Jinja2 transforma la forma en que construyes la interfaz de tus aplicaciones Flask. Aprendiste a renderizar plantillas con `render_template()`, usar expresiones y sentencias de control, aplicar filtros para transformar datos, crear estructuras reutilizables con herencia de plantillas y macros, e incluir fragmentos parciales. Dominar Jinja2 te permitirá crear interfaces web limpias, mantenibles y seguras.
