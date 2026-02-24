---
title: "Seguridad en Flask"
slug: "flask-seguridad"
description: "Protege tu aplicación Flask contra vulnerabilidades comunes: CSRF, XSS, SQL injection, CORS, rate limiting y más."
---

# Seguridad en Flask

La seguridad no es un complemento opcional, es una responsabilidad fundamental del desarrollador. Una aplicación Flask mal protegida puede exponer datos sensibles, permitir ejecución de código malicioso o ser víctima de ataques de denegación de servicio. En esta lección aprenderás a defenderte contra las vulnerabilidades más comunes.

## Protección contra CSRF (Cross-Site Request Forgery)

CSRF es un ataque donde un sitio malicioso engaña al navegador del usuario para que ejecute acciones no deseadas en tu aplicación. **Flask-WTF** proporciona protección automatizada.

```python
from flask import Flask, render_template, request
from flask_wtf import FlaskForm, CSRFProtect
from wtforms import StringField, SubmitField
from wtforms.validators import DataRequired

app = Flask(__name__)
app.config['SECRET_KEY'] = 'clave-mega-secreta-produccion'

# Activar CSRF globalmente
csrf = CSRFProtect(app)

class FormularioPerfil(FlaskForm):
    nombre = StringField('Nombre', validators=[DataRequired()])
    enviar = SubmitField('Guardar')

@app.route('/perfil', methods=['GET', 'POST'])
def perfil():
    formulario = FormularioPerfil()
    if formulario.validate_on_submit():
        # El token CSRF se valida automáticamente
        nombre = formulario.nombre.data
        return f'Perfil actualizado: {nombre}'
    return render_template('perfil.html', form=formulario)
```

En el template, incluye el token CSRF:

```html
<form method="POST">
    {{ form.hidden_tag() }}  <!-- Incluye el token CSRF -->
    {{ form.nombre.label }} {{ form.nombre() }}
    {{ form.enviar() }}
</form>

<!-- Para formularios sin Flask-WTF -->
<form method="POST">
    <input type="hidden" name="csrf_token" value="{{ csrf_token() }}"/>
    <input type="text" name="comentario">
    <button type="submit">Enviar</button>
</form>
```

Para APIs que usan JSON, puedes enviar el token en un header:

```python
# Excluir una ruta de la protección CSRF (solo si usas otro mecanismo como JWT)
@csrf.exempt
@app.route('/api/webhook', methods=['POST'])
def webhook():
    return 'OK'
```

## Prevención de XSS (Cross-Site Scripting)

XSS ocurre cuando un atacante inyecta JavaScript malicioso que se ejecuta en el navegador de otros usuarios. Jinja2 escapa HTML automáticamente, pero debes tener cuidado.

```python
# Jinja2 escapa automáticamente las variables
# Entrada del usuario: <script>alert('hackeado')</script>
# Se renderiza como: &lt;script&gt;alert('hackeado')&lt;/script&gt;

# PELIGROSO: Usar |safe desactiva el escape
{{ variable_no_confiable | safe }}  # ¡NUNCA hagas esto con datos del usuario!

# SEGURO: Usar markupsafe para contenido que necesites marcar como seguro
from markupsafe import Markup, escape

# Escapar manualmente datos del usuario
entrada_usuario = "<script>alert('xss')</script>"
texto_seguro = escape(entrada_usuario)
# Resultado: &lt;script&gt;alert(&#39;xss&#39;)&lt;/script&gt;
```

### Headers de seguridad contra XSS

```python
@app.after_request
def agregar_headers_seguridad(response):
    """Agrega headers de seguridad a todas las respuestas."""
    # Prevenir que el navegador adivine el Content-Type
    response.headers['X-Content-Type-Options'] = 'nosniff'
    # Activar protección XSS del navegador
    response.headers['X-XSS-Protection'] = '1; mode=block'
    # Prevenir que la página se muestre en iframes (clickjacking)
    response.headers['X-Frame-Options'] = 'SAMEORIGIN'
    # Referrer Policy
    response.headers['Referrer-Policy'] = 'strict-origin-when-cross-origin'
    return response
```

## Prevención de SQL Injection

SQL Injection ocurre cuando datos del usuario se insertan directamente en consultas SQL. Siempre usa consultas parametrizadas o un ORM.

```python
# ¡VULNERABLE! — Nunca concatenar datos del usuario en SQL
@app.route('/buscar')
def buscar_vulnerable():
    nombre = request.args.get('nombre')
    # Un atacante podría enviar: ' OR 1=1 --
    query = f"SELECT * FROM usuarios WHERE nombre = '{nombre}'"
    resultado = db.engine.execute(query)  # ¡PELIGROSO!
    return str(resultado)

# SEGURO — Usar parámetros
@app.route('/buscar')
def buscar_seguro():
    nombre = request.args.get('nombre')
    # Los parámetros se escapan automáticamente
    resultado = db.session.execute(
        text("SELECT * FROM usuarios WHERE nombre = :nombre"),
        {"nombre": nombre}
    )
    return jsonify([dict(row) for row in resultado])

# MEJOR — Usar el ORM (SQLAlchemy)
@app.route('/buscar')
def buscar_orm():
    nombre = request.args.get('nombre')
    usuarios = Usuario.query.filter_by(nombre=nombre).all()
    return jsonify([u.to_dict() for u in usuarios])
```

## CORS (Cross-Origin Resource Sharing)

CORS controla qué dominios pueden acceder a tu API desde el navegador.

```python
from flask_cors import CORS

app = Flask(__name__)

# Permitir todos los orígenes (solo para desarrollo)
CORS(app)

# Configuración específica para producción
CORS(app, resources={
    r"/api/*": {
        "origins": ["https://midominio.com", "https://app.midominio.com"],
        "methods": ["GET", "POST", "PUT", "DELETE"],
        "allow_headers": ["Content-Type", "Authorization"],
        "max_age": 3600,  # Cache de preflight por 1 hora
        "supports_credentials": True
    }
})

# O aplicar CORS solo a rutas específicas
from flask_cors import cross_origin

@app.route('/api/publico')
@cross_origin(origins='*')
def api_publica():
    return jsonify({"data": "accesible desde cualquier origen"})
```

## Rate Limiting con Flask-Limiter

Protege tu aplicación contra abuso y ataques de fuerza bruta limitando las peticiones por IP.

```python
from flask_limiter import Limiter
from flask_limiter.util import get_remote_address

limiter = Limiter(
    app=app,
    key_func=get_remote_address,  # Limitar por IP del cliente
    default_limits=["200 per day", "50 per hour"],  # Límites globales
    storage_uri="redis://localhost:6379/1"  # Almacenar contadores en Redis
)

# Límites personalizados por ruta
@app.route('/login', methods=['POST'])
@limiter.limit("5 per minute")  # Solo 5 intentos de login por minuto
def login():
    # ... lógica de login ...
    return jsonify({"token": "abc123"})

@app.route('/api/datos')
@limiter.limit("30 per minute")
def obtener_datos():
    return jsonify({"datos": "..."})

# Excluir rutas del rate limiting
@app.route('/health')
@limiter.exempt
def health_check():
    return jsonify({"status": "ok"})

# Límite dinámico basado en el tipo de usuario
def limite_por_plan():
    user = get_current_user()
    if user and user.plan == 'premium':
        return "1000 per hour"
    return "100 per hour"

@app.route('/api/premium')
@limiter.limit(limite_por_plan)
def api_premium():
    return jsonify({"data": "..."})
```

## Content Security Policy (CSP)

CSP restringe qué recursos puede cargar tu página, previniendo XSS e inyecciones de código.

```python
from flask_talisman import Talisman

# Configurar CSP
csp = {
    'default-src': "'self'",
    'script-src': ["'self'", 'cdnjs.cloudflare.com'],
    'style-src': ["'self'", "'unsafe-inline'", 'fonts.googleapis.com'],
    'font-src': ["'self'", 'fonts.gstatic.com'],
    'img-src': ["'self'", 'data:', '*.amazonaws.com'],
}

# Talisman agrega múltiples headers de seguridad automáticamente
Talisman(
    app,
    content_security_policy=csp,
    force_https=True,              # Redirigir HTTP a HTTPS
    strict_transport_security=True, # HSTS
    session_cookie_secure=True,     # Cookies solo por HTTPS
    session_cookie_httponly=True,    # Cookies no accesibles por JavaScript
)
```

## Gestión Segura de Secrets

Nunca incluyas credenciales en el código fuente.

```python
import os
from dotenv import load_dotenv

# Cargar variables de entorno desde .env
load_dotenv()

app.config.update(
    SECRET_KEY=os.environ.get('SECRET_KEY'),
    DATABASE_URL=os.environ.get('DATABASE_URL'),
    MAIL_PASSWORD=os.environ.get('MAIL_PASSWORD'),
    API_KEY_STRIPE=os.environ.get('STRIPE_API_KEY'),
)

# Verificar que las variables críticas existen
variables_requeridas = ['SECRET_KEY', 'DATABASE_URL']
for var in variables_requeridas:
    if not os.environ.get(var):
        raise RuntimeError(f"Variable de entorno {var} no configurada")
```

Archivo `.env` (añadir a `.gitignore`):

```bash
# .env — NUNCA subir a git
SECRET_KEY=tu-clave-secreta-muy-larga-y-aleatoria
DATABASE_URL=postgresql://user:pass@localhost/db
MAIL_PASSWORD=contraseña-smtp
STRIPE_API_KEY=sk_live_xxx
```

## HTTPS

```python
# Forzar HTTPS en producción
@app.before_request
def forzar_https():
    if not request.is_secure and app.env != 'development':
        url = request.url.replace('http://', 'https://', 1)
        return redirect(url, code=301)
```

## Ejercicio Práctico

Asegura una aplicación Flask existente implementando:

1. **CSRF**: activa la protección global y agrega tokens a todos los formularios.
2. **Rate limiting**: limita el endpoint `/login` a 5 intentos por minuto y `/api/*` a 100 peticiones por hora.
3. **Headers de seguridad**: implementa `X-Content-Type-Options`, `X-Frame-Options`, CSP y HSTS.
4. **CORS**: permite solo solicitudes desde `https://miapp.com`.
5. **Secrets**: mueve todas las credenciales a variables de entorno y valida su existencia al iniciar.
6. **Sanitización**: asegúrate de que todas las consultas a la BD usen parámetros o el ORM.
7. Escribe tests que verifiquen que los headers de seguridad están presentes en las respuestas.

## Resumen

- **CSRF**: usa Flask-WTF o CSRFProtect para generar y validar tokens en formularios.
- **XSS**: Jinja2 escapa HTML por defecto; nunca uses `|safe` con datos del usuario.
- **SQL Injection**: siempre usa consultas parametrizadas o el ORM de SQLAlchemy.
- **CORS**: configura orígenes permitidos explícitamente en producción con Flask-CORS.
- **Rate Limiting**: Flask-Limiter previene abuso y ataques de fuerza bruta.
- **CSP**: Flask-Talisman agrega Content Security Policy y otros headers de seguridad.
- **Secrets**: usa variables de entorno y `.env` para credenciales; nunca las pongas en el código.
- **HTTPS**: fuerza conexiones seguras en producción con redirecciones y HSTS.
