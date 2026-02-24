---
title: "Autenticación y Sesiones"
slug: "flask-autenticacion-sesiones"
description: "Implementa un sistema completo de autenticación en Flask con sesiones, Flask-Login, hashing de contraseñas y protección de rutas."
---

# Autenticación y Sesiones

La autenticación es uno de los componentes más críticos de cualquier aplicación web. En esta lección aprenderás a implementar un sistema completo de autenticación en Flask usando **sesiones**, la extensión **Flask-Login** para gestionar el estado del usuario y **hashing de contraseñas** para almacenarlas de forma segura.

## Sesiones en Flask

Las sesiones permiten almacenar información del usuario entre diferentes solicitudes HTTP. Flask usa cookies firmadas criptográficamente para implementar sesiones del lado del cliente.

### Configuración

```python
from flask import Flask, session

app = Flask(__name__)
app.config['SECRET_KEY'] = 'tu-clave-secreta-muy-segura'  # ¡Obligatorio para sesiones!
app.config['PERMANENT_SESSION_LIFETIME'] = 1800  # 30 minutos en segundos
```

### Uso Básico de Sesiones

```python
from flask import session, redirect, url_for

@app.route('/configurar')
def configurar_sesion():
    session['idioma'] = 'es'
    session['tema'] = 'oscuro'
    session.permanent = True  # Usa PERMANENT_SESSION_LIFETIME
    return 'Preferencias guardadas'

@app.route('/leer')
def leer_sesion():
    idioma = session.get('idioma', 'en')
    tema = session.get('tema', 'claro')
    return f'Idioma: {idioma}, Tema: {tema}'

@app.route('/limpiar')
def limpiar_sesion():
    session.clear()  # Elimina toda la sesión
    return 'Sesión limpiada'

@app.route('/eliminar-clave')
def eliminar_clave():
    session.pop('tema', None)  # Elimina una clave específica
    return 'Tema eliminado de la sesión'
```

> **Nota**: Las sesiones de Flask almacenan los datos en una cookie del navegador, firmada pero no encriptada. No guardes información sensible directamente en la sesión. Para sesiones del lado del servidor, usa extensiones como `Flask-Session`.

## Hashing de Contraseñas

**Nunca** almacenes contraseñas en texto plano. Usa funciones de hashing para generar un resumen irreversible de la contraseña. Werkzeug (incluido con Flask) proporciona funciones seguras:

```python
from werkzeug.security import generate_password_hash, check_password_hash

# Generar el hash (al registrar un usuario)
password_hash = generate_password_hash('mi_contraseña_segura')
# Resultado: 'scrypt:32768:8:1$salt$hash...'

# Verificar la contraseña (al hacer login)
es_correcta = check_password_hash(password_hash, 'mi_contraseña_segura')
# True

es_incorrecta = check_password_hash(password_hash, 'contraseña_incorrecta')
# False
```

### Integración con el Modelo de Usuario

```python
from werkzeug.security import generate_password_hash, check_password_hash

class Usuario(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    nombre = db.Column(db.String(100), nullable=False)
    email = db.Column(db.String(120), unique=True, nullable=False)
    password_hash = db.Column(db.String(256), nullable=False)
    
    def set_password(self, password):
        """Genera y almacena el hash de la contraseña."""
        self.password_hash = generate_password_hash(password)
    
    def check_password(self, password):
        """Verifica si la contraseña proporcionada es correcta."""
        return check_password_hash(self.password_hash, password)
    
    def __repr__(self):
        return f'<Usuario {self.email}>'
```

Uso:

```python
# Registrar un usuario
usuario = Usuario(nombre='Ana', email='ana@example.com')
usuario.set_password('contraseña_segura_123')
db.session.add(usuario)
db.session.commit()

# Verificar contraseña en login
usuario = Usuario.query.filter_by(email='ana@example.com').first()
if usuario and usuario.check_password('contraseña_segura_123'):
    print('¡Login exitoso!')
```

## Flask-Login

**Flask-Login** es la extensión estándar para manejar la autenticación de usuarios. Gestiona las sesiones de usuario, protege rutas y proporciona utilidades como "recordar sesión".

### Instalación

```bash
pip install flask-login
```

### Configuración

```python
from flask_login import LoginManager

login_manager = LoginManager()
login_manager.init_app(app)
login_manager.login_view = 'auth.login'  # Redirigir aquí si no autenticado
login_manager.login_message = 'Por favor, inicia sesión para acceder a esta página.'
login_manager.login_message_category = 'warning'
```

### Preparar el Modelo de Usuario

Flask-Login requiere que tu modelo de usuario implemente ciertos métodos. La forma más fácil es heredar de `UserMixin`:

```python
from flask_login import UserMixin

class Usuario(UserMixin, db.Model):
    id = db.Column(db.Integer, primary_key=True)
    nombre = db.Column(db.String(100), nullable=False)
    email = db.Column(db.String(120), unique=True, nullable=False)
    password_hash = db.Column(db.String(256), nullable=False)
    activo = db.Column(db.Boolean, default=True)
    
    def set_password(self, password):
        self.password_hash = generate_password_hash(password)
    
    def check_password(self, password):
        return check_password_hash(self.password_hash, password)
    
    @property
    def is_active(self):
        """Flask-Login usa esto para verificar si el usuario está activo."""
        return self.activo
```

`UserMixin` proporciona implementaciones por defecto de:
- `is_authenticated`: siempre retorna `True`.
- `is_active`: siempre retorna `True` (la sobrescribimos arriba).
- `is_anonymous`: siempre retorna `False`.
- `get_id()`: retorna el `id` como string.

### User Loader

Flask-Login necesita saber cómo cargar un usuario desde la base de datos:

```python
@login_manager.user_loader
def cargar_usuario(user_id):
    return db.session.get(Usuario, int(user_id))
```

## Implementar Login y Logout

### `login_user()`

```python
from flask_login import login_user, logout_user, login_required, current_user

@auth_bp.route('/login', methods=['GET', 'POST'])
def login():
    if current_user.is_authenticated:
        return redirect(url_for('main.inicio'))
    
    form = FormularioLogin()
    if form.validate_on_submit():
        usuario = Usuario.query.filter_by(email=form.email.data).first()
        
        if usuario is None or not usuario.check_password(form.password.data):
            flash('Email o contraseña incorrectos', 'danger')
            return redirect(url_for('auth.login'))
        
        login_user(usuario, remember=form.recordar.data)
        
        # Redirigir a la página que el usuario intentaba acceder
        pagina_siguiente = request.args.get('next')
        if pagina_siguiente and url_parse(pagina_siguiente).netloc == '':
            return redirect(pagina_siguiente)
        
        flash(f'¡Bienvenido, {usuario.nombre}!', 'success')
        return redirect(url_for('main.inicio'))
    
    return render_template('auth/login.html', form=form)
```

### `logout_user()`

```python
@auth_bp.route('/logout')
@login_required
def logout():
    logout_user()
    flash('Sesión cerrada correctamente', 'info')
    return redirect(url_for('main.inicio'))
```

### Formulario de Login

```python
from flask_wtf import FlaskForm
from wtforms import StringField, PasswordField, BooleanField
from wtforms.validators import DataRequired, Email

class FormularioLogin(FlaskForm):
    email = StringField('Email', validators=[DataRequired(), Email()])
    password = PasswordField('Contraseña', validators=[DataRequired()])
    recordar = BooleanField('Recordar sesión')
```

## Proteger Rutas con `@login_required`

El decorador `@login_required` restringe el acceso a usuarios autenticados:

```python
@main_bp.route('/dashboard')
@login_required
def dashboard():
    return render_template('dashboard.html')

@main_bp.route('/perfil')
@login_required
def perfil():
    return render_template('perfil.html', usuario=current_user)

@admin_bp.route('/admin')
@login_required
def panel_admin():
    if not current_user.es_admin:
        abort(403)
    return render_template('admin/panel.html')
```

Si un usuario no autenticado intenta acceder a una ruta protegida, Flask-Login lo redirige automáticamente a `login_view`.

## `current_user`

`current_user` es un proxy disponible en vistas y templates que representa al usuario actual:

```python
# En una vista
@app.route('/mi-cuenta')
@login_required
def mi_cuenta():
    return f'Hola, {current_user.nombre}. Tu email es {current_user.email}'
```

```html
<!-- En una plantilla -->
{% if current_user.is_authenticated %}
    <span>Hola, {{ current_user.nombre }}</span>
    <a href="{{ url_for('auth.logout') }}">Cerrar sesión</a>
{% else %}
    <a href="{{ url_for('auth.login') }}">Iniciar sesión</a>
    <a href="{{ url_for('auth.registro') }}">Registrarse</a>
{% endif %}
```

## Decorador Personalizado: Requerir Rol

```python
from functools import wraps
from flask_login import current_user
from flask import abort

def admin_required(f):
    @wraps(f)
    @login_required
    def decorated_function(*args, **kwargs):
        if not current_user.es_admin:
            abort(403)
        return f(*args, **kwargs)
    return decorated_function

# Uso
@admin_bp.route('/usuarios')
@admin_required
def gestionar_usuarios():
    usuarios = Usuario.query.all()
    return render_template('admin/usuarios.html', usuarios=usuarios)
```

## Registro de Usuarios

```python
@auth_bp.route('/registro', methods=['GET', 'POST'])
def registro():
    if current_user.is_authenticated:
        return redirect(url_for('main.inicio'))
    
    form = FormularioRegistro()
    if form.validate_on_submit():
        usuario = Usuario(
            nombre=form.nombre.data,
            email=form.email.data
        )
        usuario.set_password(form.password.data)
        
        try:
            db.session.add(usuario)
            db.session.commit()
            flash('¡Cuenta creada exitosamente! Ya puedes iniciar sesión.', 'success')
            return redirect(url_for('auth.login'))
        except IntegrityError:
            db.session.rollback()
            flash('El email ya está registrado.', 'danger')
    
    return render_template('auth/registro.html', form=form)
```

## Ejercicio Práctico

Implementa un sistema completo de autenticación para una aplicación Flask:

1. **Modelo `Usuario`** con campos: id, nombre, email (único), password_hash, es_admin (boolean), fecha_registro. Incluye métodos `set_password()` y `check_password()`.
2. **Registro** con validación: nombre (3-50 chars), email válido, contraseña (mín. 8 chars, confirmación).
3. **Login** con opción "recordar sesión" y redirección a la página previa.
4. **Logout** protegido con `@login_required`.
5. **Página de perfil** donde el usuario puede ver y editar su nombre.
6. **Ruta `/admin`** protegida con un decorador personalizado `@admin_required`.
7. **Navegación dinámica** que muestre opciones según si el usuario está autenticado o no.

## Resumen

En esta lección implementaste un sistema de autenticación completo en Flask. Aprendiste a usar sesiones para almacenar datos entre solicitudes, hashear contraseñas de forma segura con Werkzeug, gestionar el ciclo de vida de la autenticación con Flask-Login (`login_user`, `logout_user`, `current_user`), proteger rutas con `@login_required` y crear decoradores personalizados para control de acceso basado en roles. Estos conocimientos son fundamentales para cualquier aplicación web que requiera gestión de usuarios.
