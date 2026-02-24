---
title: "Sistema de Templates"
slug: "django-templates"
description: "Domina el Django Template Language (DTL): variables, tags, filtros, herencia de plantillas e includes."
---

# Sistema de Templates

El sistema de templates de Django permite separar la lógica de presentación del código Python. Usa el **Django Template Language (DTL)**, un lenguaje de plantillas diseñado para ser legible, seguro y extensible. Los templates generan HTML dinámico combinando contenido estático con datos del servidor.

## Configuración de templates

Primero, configura dónde Django buscará los templates:

```python
# settings.py
TEMPLATES = [
    {
        'BACKEND': 'django.template.backends.django.DjangoTemplates',
        'DIRS': [BASE_DIR / 'templates'],  # Templates globales del proyecto
        'APP_DIRS': True,  # Buscar en carpetas templates/ de cada app
        'OPTIONS': {
            'context_processors': [
                'django.template.context_processors.debug',
                'django.template.context_processors.request',
                'django.contrib.auth.context_processors.auth',
                'django.contrib.messages.context_processors.messages',
            ],
        },
    },
]
```

Estructura recomendada de directorios:

```
mi_proyecto/
├── templates/              # Templates globales
│   └── base.html
└── blog/
    └── templates/
        └── blog/           # Namespace por app
            ├── lista.html
            └── detalle.html
```

## Variables {{ }}

Las variables se renderizan usando llaves dobles. Django reemplaza las variables con los valores del contexto proporcionado por la vista:

```html
<!-- Acceso a variables simples -->
<h1>{{ titulo }}</h1>
<p>Autor: {{ articulo.autor }}</p>

<!-- Acceso a atributos de objetos -->
<p>{{ articulo.titulo }}</p>
<p>{{ articulo.categoria.nombre }}</p>

<!-- Acceso a métodos (sin paréntesis ni argumentos) -->
<p>{{ articulo.get_resumen }}</p>

<!-- Acceso a índices de listas -->
<p>Primer tag: {{ articulo.tags.0 }}</p>

<!-- Acceso a claves de diccionarios -->
<p>{{ datos.clave }}</p>
```

### Pasar contexto desde la vista

```python
# blog/views.py
def detalle(request, slug):
    articulo = get_object_or_404(Articulo, slug=slug)
    articulos_relacionados = Articulo.objects.filter(
        categoria=articulo.categoria
    ).exclude(pk=articulo.pk)[:3]

    contexto = {
        'articulo': articulo,
        'relacionados': articulos_relacionados,
        'mostrar_sidebar': True,
    }
    return render(request, 'blog/detalle.html', contexto)
```

## Tags {% %}

Los tags proporcionan lógica de control dentro de los templates. Se encierran entre `{% %}`:

### Condicionales

```html
{% if articulo.publicado %}
    <span class="badge badge-success">Publicado</span>
{% elif articulo.en_revision %}
    <span class="badge badge-warning">En revisión</span>
{% else %}
    <span class="badge badge-secondary">Borrador</span>
{% endif %}

<!-- Operadores lógicos -->
{% if usuario.is_authenticated and articulo.autor == usuario %}
    <a href="{% url 'blog:editar' slug=articulo.slug %}">Editar</a>
{% endif %}

{% if not errores %}
    <p>¡Todo correcto!</p>
{% endif %}
```

### Bucles

```html
<ul>
{% for articulo in articulos %}
    <li>
        <a href="{% url 'blog:detalle' slug=articulo.slug %}">
            {{ articulo.titulo }}
        </a>
        <small>{{ articulo.fecha_creacion }}</small>
    </li>
{% empty %}
    <li>No hay artículos disponibles.</li>
{% endfor %}
</ul>

<!-- Variables especiales del bucle for -->
{% for item in lista %}
    {{ forloop.counter }}      <!-- Índice desde 1 -->
    {{ forloop.counter0 }}     <!-- Índice desde 0 -->
    {{ forloop.revcounter }}   <!-- Cuenta regresiva -->
    {{ forloop.first }}        <!-- True si es el primero -->
    {{ forloop.last }}         <!-- True si es el último -->
{% endfor %}
```

### Otros tags útiles

```html
<!-- URLs dinámicas -->
<a href="{% url 'blog:detalle' slug=articulo.slug %}">Ver</a>

<!-- Cargar archivos estáticos -->
{% load static %}
<link rel="stylesheet" href="{% static 'css/estilos.css' %}">
<img src="{% static 'img/logo.png' %}" alt="Logo">

<!-- Comentarios -->
{% comment "Nota del desarrollador" %}
    Este bloque no se renderizará.
{% endcomment %}

<!-- Asignación de variables -->
{% with total=articulos.count %}
    <p>Total de artículos: {{ total }}</p>
{% endwith %}

<!-- Protección CSRF en formularios -->
<form method="post">
    {% csrf_token %}
    <!-- campos del formulario -->
</form>

<!-- Ciclo entre valores -->
{% for articulo in articulos %}
    <div class="{% cycle 'fila-par' 'fila-impar' %}">
        {{ articulo.titulo }}
    </div>
{% endfor %}
```

## Filtros

Los filtros transforman el valor de las variables. Se aplican con el carácter pipe `|`:

```html
<!-- Texto -->
{{ nombre|upper }}                    <!-- MAYÚSCULAS -->
{{ nombre|lower }}                    <!-- minúsculas -->
{{ nombre|title }}                    <!-- Capitalizar Palabras -->
{{ texto|truncatewords:30 }}          <!-- Limitar a 30 palabras -->
{{ texto|truncatechars:100 }}         <!-- Limitar a 100 caracteres -->
{{ texto|linebreaks }}                <!-- Convertir saltos en <p> y <br> -->
{{ texto|striptags }}                 <!-- Eliminar tags HTML -->
{{ texto|slugify }}                   <!-- Convertir a slug -->

<!-- Números y fechas -->
{{ precio|floatformat:2 }}            <!-- 29.99 -->
{{ fecha|date:"d/m/Y" }}             <!-- 23/02/2026 -->
{{ fecha|timesince }}                 <!-- "hace 3 días" -->
{{ fecha|timeuntil }}                 <!-- "en 2 semanas" -->

<!-- Listas -->
{{ lista|length }}                    <!-- Cantidad de elementos -->
{{ lista|join:", " }}                 <!-- Unir con separador -->
{{ lista|first }}                     <!-- Primer elemento -->
{{ lista|last }}                      <!-- Último elemento -->

<!-- Valores por defecto -->
{{ variable|default:"Sin valor" }}    <!-- Valor si es falsy -->
{{ variable|default_if_none:"N/A" }} <!-- Valor si es None -->

<!-- Seguridad -->
{{ html_content|safe }}               <!-- Marcar como HTML seguro -->
{{ texto|escape }}                    <!-- Escapar HTML -->

<!-- Encadenar filtros -->
{{ nombre|lower|truncatewords:5 }}
```

## Herencia de templates (extends/block)

La herencia es la característica más poderosa del sistema de templates. Permite definir un template base y extenderlo en templates hijos:

### Template base

```html
<!-- templates/base.html -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block titulo %}Mi Sitio{% endblock %}</title>
    {% load static %}
    <link rel="stylesheet" href="{% static 'css/estilos.css' %}">
    {% block css_extra %}{% endblock %}
</head>
<body>
    <header>
        {% block header %}
        <nav>
            <a href="{% url 'inicio' %}">Inicio</a>
            <a href="{% url 'blog:lista' %}">Blog</a>
            {% if user.is_authenticated %}
                <span>Hola, {{ user.username }}</span>
                <a href="{% url 'logout' %}">Salir</a>
            {% else %}
                <a href="{% url 'login' %}">Entrar</a>
            {% endif %}
        </nav>
        {% endblock %}
    </header>

    <main>
        {% block contenido %}{% endblock %}
    </main>

    <footer>
        {% block footer %}
        <p>&copy; 2026 Mi Sitio. Todos los derechos reservados.</p>
        {% endblock %}
    </footer>

    {% block scripts %}{% endblock %}
</body>
</html>
```

### Template hijo

```html
<!-- blog/templates/blog/lista.html -->
{% extends "base.html" %}

{% block titulo %}Blog - Artículos{% endblock %}

{% block contenido %}
<h1>{{ titulo }}</h1>

{% for articulo in articulos %}
    <article>
        <h2>
            <a href="{% url 'blog:detalle' slug=articulo.slug %}">
                {{ articulo.titulo }}
            </a>
        </h2>
        <p>{{ articulo.contenido|truncatewords:50 }}</p>
        <small>
            {{ articulo.fecha_creacion|date:"d M Y" }} |
            Categoría: {{ articulo.categoria.nombre }}
        </small>
    </article>
{% empty %}
    <p>No hay artículos disponibles.</p>
{% endfor %}
{% endblock %}

{% block scripts %}
<script src="{% static 'js/blog.js' %}"></script>
{% endblock %}
```

### Usar {{ block.super }}

Para conservar el contenido del bloque padre y agregar contenido adicional:

```html
{% block css_extra %}
    {{ block.super }}
    <link rel="stylesheet" href="{% static 'css/blog.css' %}">
{% endblock %}
```

## Includes

Los includes permiten reutilizar fragmentos de template:

```html
<!-- templates/componentes/tarjeta_articulo.html -->
<article class="tarjeta">
    <h3>{{ articulo.titulo }}</h3>
    <p>{{ articulo.resumen|default:"Sin resumen" }}</p>
    <a href="{% url 'blog:detalle' slug=articulo.slug %}">Leer más</a>
</article>
```

```html
<!-- Usar el include -->
{% for articulo in articulos %}
    {% include "componentes/tarjeta_articulo.html" with articulo=articulo %}
{% endfor %}

<!-- Include con contexto aislado (only) -->
{% include "componentes/tarjeta_articulo.html" with articulo=articulo only %}
```

## Ejercicio Práctico

1. Crea un template base (`base.html`) con: navegación, área de contenido y pie de página.
2. Crea un template `lista_productos.html` que extienda de `base.html` y muestre una lista de productos con nombre, precio y categoría.
3. Crea un template `detalle_producto.html` que muestre toda la información de un producto.
4. Usa filtros para: formatear precios con 2 decimales, truncar descripciones largas, formatear fechas en español.
5. Crea un componente reutilizable `tarjeta_producto.html` y úsalo con `{% include %}`.
6. Agrega lógica condicional para mostrar un badge de "Agotado" cuando el stock sea 0.

## Resumen

El sistema de templates de Django separa la presentación de la lógica de negocio de manera elegante. Aprendiste a usar variables, tags de control de flujo, filtros para transformar datos, herencia de plantillas con `extends`/`block` para evitar repetición, e includes para componentizar tu HTML. Dominar el DTL te permite crear interfaces dinámicas mantenibles y organizadas.
