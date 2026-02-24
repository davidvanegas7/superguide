---
title: "Formularios y Request"
slug: "flask-formularios-request"
description: "Aprende a manejar formularios, datos de solicitud, subida de archivos y protección CSRF con Flask-WTF."
---

# Formularios y Request

La interacción con el usuario a través de formularios es fundamental en cualquier aplicación web. Flask proporciona herramientas integradas para acceder a los datos enviados por el cliente, y la extensión **Flask-WTF** añade validación robusta y protección contra ataques CSRF. En esta lección dominarás el manejo completo del objeto `request` y los formularios en Flask.

## El Objeto `request`

Flask expone el objeto `request` que contiene toda la información de la solicitud HTTP actual. Para usarlo, impórtalo desde `flask`:

```python
from flask import Flask, request

app = Flask(__name__)
```

### `request.form` — Datos de Formulario

Cuando un formulario HTML se envía con `method="POST"`, los datos están disponibles en `request.form`:

```python
@app.route('/registro', methods=['GET', 'POST'])
def registro():
    if request.method == 'POST':
        nombre = request.form['nombre']           # Lanza KeyError si no existe
        email = request.form.get('email')          # Retorna None si no existe
        edad = request.form.get('edad', 0, type=int)  # Valor por defecto con tipo
        
        return f'Registrado: {nombre} ({email}), {edad} años'
    
    return '''
    <form method="POST">
        <input name="nombre" placeholder="Nombre" required>
        <input name="email" type="email" placeholder="Email">
        <input name="edad" type="number" placeholder="Edad">
        <button type="submit">Registrar</button>
    </form>
    '''
```

### `request.args` — Parámetros de URL (Query String)

Los parámetros de la URL (después del `?`) se acceden con `request.args`:

```python
@app.route('/buscar')
def buscar():
    # URL: /buscar?q=flask&pagina=2&orden=reciente
    consulta = request.args.get('q', '')
    pagina = request.args.get('pagina', 1, type=int)
    orden = request.args.get('orden', 'reciente')
    
    return f'Buscando: "{consulta}", página {pagina}, orden: {orden}'
```

### `request.json` — Datos JSON

Para solicitudes que envían datos JSON (común en APIs):

```python
@app.route('/api/usuario', methods=['POST'])
def crear_usuario_api():
    # El cliente envía: {"nombre": "Ana", "email": "ana@example.com"}
    datos = request.json  # Retorna None si no es JSON válido
    
    if datos is None:
        return {'error': 'Se requiere JSON'}, 400
    
    nombre = datos.get('nombre')
    email = datos.get('email')
    
    return {'mensaje': f'Usuario {nombre} creado', 'email': email}, 201
```

También puedes usar `request.get_json()` con opciones adicionales:

```python
datos = request.get_json(force=True)    # Ignora Content-Type
datos = request.get_json(silent=True)   # No lanza error si falla
```

### Otros Atributos Útiles del `request`

```python
@app.route('/info')
def info_request():
    info = {
        'metodo': request.method,                # GET, POST, etc.
        'url': request.url,                      # URL completa
        'host': request.host,                    # Dominio + puerto
        'ruta': request.path,                    # Solo la ruta
        'ip': request.remote_addr,               # IP del cliente
        'user_agent': str(request.user_agent),   # Navegador del cliente
        'content_type': request.content_type,    # Tipo de contenido
        'cookies': dict(request.cookies),        # Cookies enviadas
        'headers': dict(request.headers),        # Todos los headers
    }
    return info
```

## Subida de Archivos

Flask permite manejar archivos subidos desde formularios HTML con `request.files`:

```python
import os
from flask import Flask, request
from werkzeug.utils import secure_filename

app = Flask(__name__)
app.config['UPLOAD_FOLDER'] = 'uploads'
app.config['MAX_CONTENT_LENGTH'] = 16 * 1024 * 1024  # 16 MB máximo

EXTENSIONES_PERMITIDAS = {'png', 'jpg', 'jpeg', 'gif', 'pdf'}

def archivo_permitido(nombre):
    return '.' in nombre and \
           nombre.rsplit('.', 1)[1].lower() in EXTENSIONES_PERMITIDAS

@app.route('/subir', methods=['GET', 'POST'])
def subir_archivo():
    if request.method == 'POST':
        # Verificar que el archivo existe en la solicitud
        if 'archivo' not in request.files:
            return 'No se seleccionó ningún archivo', 400
        
        archivo = request.files['archivo']
        
        # Verificar que se seleccionó un archivo
        if archivo.filename == '':
            return 'Nombre de archivo vacío', 400
        
        if archivo and archivo_permitido(archivo.filename):
            # Sanitizar el nombre del archivo
            nombre_seguro = secure_filename(archivo.filename)
            ruta = os.path.join(app.config['UPLOAD_FOLDER'], nombre_seguro)
            archivo.save(ruta)
            return f'Archivo "{nombre_seguro}" subido exitosamente'
        
        return 'Tipo de archivo no permitido', 400
    
    return '''
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="archivo">
        <button type="submit">Subir</button>
    </form>
    '''
```

Para subir **múltiples archivos**:

```html
<input type="file" name="archivos" multiple>
```

```python
archivos = request.files.getlist('archivos')
for archivo in archivos:
    if archivo and archivo_permitido(archivo.filename):
        nombre = secure_filename(archivo.filename)
        archivo.save(os.path.join(app.config['UPLOAD_FOLDER'], nombre))
```

## Flask-WTF: Formularios Avanzados

**Flask-WTF** integra WTForms con Flask, proporcionando validación de formularios y protección CSRF de forma sencilla.

### Instalación

```bash
pip install flask-wtf
```

### Configuración

```python
app = Flask(__name__)
app.config['SECRET_KEY'] = 'tu-clave-secreta-muy-larga-y-aleatoria'
```

### Definir un Formulario

```python
from flask_wtf import FlaskForm
from wtforms import StringField, PasswordField, EmailField, IntegerField, TextAreaField, SelectField, BooleanField
from wtforms.validators import DataRequired, Email, Length, NumberRange, EqualTo

class FormularioRegistro(FlaskForm):
    nombre = StringField('Nombre', validators=[
        DataRequired(message='El nombre es obligatorio'),
        Length(min=2, max=50, message='El nombre debe tener entre 2 y 50 caracteres')
    ])
    email = EmailField('Correo electrónico', validators=[
        DataRequired(),
        Email(message='Ingresa un correo válido')
    ])
    password = PasswordField('Contraseña', validators=[
        DataRequired(),
        Length(min=8, message='La contraseña debe tener al menos 8 caracteres')
    ])
    confirmar_password = PasswordField('Confirmar contraseña', validators=[
        DataRequired(),
        EqualTo('password', message='Las contraseñas no coinciden')
    ])
    edad = IntegerField('Edad', validators=[
        NumberRange(min=18, max=120, message='Debes ser mayor de edad')
    ])
    pais = SelectField('País', choices=[
        ('mx', 'México'), ('es', 'España'), ('ar', 'Argentina'), ('co', 'Colombia')
    ])
    acepta_terminos = BooleanField('Acepto los términos', validators=[
        DataRequired(message='Debes aceptar los términos')
    ])
```

### Usar el Formulario en la Vista

```python
@app.route('/registro', methods=['GET', 'POST'])
def registro():
    form = FormularioRegistro()
    
    if form.validate_on_submit():  # Valida solo en POST
        nombre = form.nombre.data
        email = form.email.data
        password = form.password.data
        # Procesar registro...
        return redirect(url_for('inicio'))
    
    return render_template('registro.html', form=form)
```

### Renderizar el Formulario en la Plantilla

```html
{% extends "base.html" %}
{% block contenido %}
<h1>Registro</h1>
<form method="POST" novalidate>
    {{ form.hidden_tag() }}   {# Incluye el token CSRF #}
    
    <div class="campo">
        {{ form.nombre.label }}
        {{ form.nombre(class="form-control", placeholder="Tu nombre") }}
        {% for error in form.nombre.errors %}
            <span class="error">{{ error }}</span>
        {% endfor %}
    </div>
    
    <div class="campo">
        {{ form.email.label }}
        {{ form.email(class="form-control") }}
        {% for error in form.email.errors %}
            <span class="error">{{ error }}</span>
        {% endfor %}
    </div>
    
    <div class="campo">
        {{ form.password.label }}
        {{ form.password(class="form-control") }}
        {% for error in form.password.errors %}
            <span class="error">{{ error }}</span>
        {% endfor %}
    </div>
    
    <div class="campo">
        {{ form.acepta_terminos() }} {{ form.acepta_terminos.label }}
    </div>
    
    <button type="submit" class="btn">Registrarse</button>
</form>
{% endblock %}
```

## Protección CSRF

**CSRF** (Cross-Site Request Forgery) es un ataque donde un sitio malicioso envía solicitudes a tu aplicación en nombre del usuario. Flask-WTF protege automáticamente contra esto generando un token único por sesión.

Al usar `{{ form.hidden_tag() }}` o `{{ form.csrf_token }}`, Flask-WTF incluye un campo oculto con el token CSRF que se valida en cada envío del formulario.

Si necesitas protección CSRF en formularios manuales (sin FlaskForm):

```html
<form method="POST">
    <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
    <input name="dato" placeholder="Un dato">
    <button>Enviar</button>
</form>
```

## Mensajes Flash

Flask incluye un sistema de **mensajes flash** para mostrar notificaciones al usuario:

```python
from flask import flash, redirect, url_for

@app.route('/registro', methods=['GET', 'POST'])
def registro():
    form = FormularioRegistro()
    if form.validate_on_submit():
        # Procesar registro...
        flash('¡Registro exitoso! Bienvenido.', 'success')
        return redirect(url_for('inicio'))
    return render_template('registro.html', form=form)
```

En la plantilla:

```html
{% with mensajes = get_flashed_messages(with_categories=true) %}
    {% for categoria, mensaje in mensajes %}
        <div class="alerta alerta-{{ categoria }}">{{ mensaje }}</div>
    {% endfor %}
{% endwith %}
```

## Ejercicio Práctico

Crea una aplicación con un formulario de contacto que incluya:

1. **Campos**: nombre (obligatorio, 3-50 caracteres), email (obligatorio, formato válido), asunto (selección: consulta, soporte, sugerencia) y mensaje (obligatorio, mínimo 20 caracteres).
2. **Validación** con Flask-WTF y mensajes de error en español.
3. **Protección CSRF**.
4. **Mensaje flash** al enviar exitosamente.
5. **Subida de archivo** adjunto opcional (solo PDF o imágenes, máximo 5 MB).

```python
from flask_wtf import FlaskForm
from flask_wtf.file import FileField, FileAllowed
from wtforms import StringField, SelectField, TextAreaField
from wtforms.validators import DataRequired, Email, Length

class FormularioContacto(FlaskForm):
    nombre = StringField('Nombre', validators=[
        DataRequired(message='El nombre es obligatorio'),
        Length(min=3, max=50)
    ])
    email = EmailField('Email', validators=[DataRequired(), Email()])
    asunto = SelectField('Asunto', choices=[
        ('consulta', 'Consulta general'),
        ('soporte', 'Soporte técnico'),
        ('sugerencia', 'Sugerencia'),
    ])
    mensaje = TextAreaField('Mensaje', validators=[
        DataRequired(), Length(min=20, message='El mensaje debe tener al menos 20 caracteres')
    ])
    adjunto = FileField('Archivo adjunto', validators=[
        FileAllowed(['pdf', 'png', 'jpg'], 'Solo PDF o imágenes')
    ])
```

## Resumen

En esta lección aprendiste a trabajar con el objeto `request` de Flask para acceder a datos de formularios, parámetros de URL, datos JSON y archivos subidos. También descubriste cómo Flask-WTF simplifica la validación de formularios y proporciona protección CSRF automática. Estas herramientas son esenciales para construir aplicaciones web interactivas y seguras.
