---
title: "JWT y OAuth2"
slug: "flask-jwt-oauth2"
description: "Implementa autenticación con JSON Web Tokens y OAuth2 en Flask: tokens de acceso, refresh tokens, blacklisting y login con terceros."
---

# JWT y OAuth2

La autenticación basada en tokens es el estándar moderno para APIs y aplicaciones SPA (Single Page Application). **JWT (JSON Web Tokens)** permite crear tokens firmados que verifican la identidad del usuario sin necesidad de sesiones en el servidor. **OAuth2** permite a los usuarios iniciar sesión con sus cuentas de Google, GitHub u otros proveedores. En esta lección aprenderás a implementar ambos.

## ¿Qué es un JWT?

Un JWT es un token codificado en Base64 que contiene tres partes separadas por puntos:

```
eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ1c2VyMSJ9.firma_digital
|_____ Header _____|.|_____ Payload _____|.|____ Firma ____|
```

- **Header**: algoritmo de firma y tipo de token.
- **Payload**: datos del usuario (claims).
- **Firma**: garantiza que el token no ha sido alterado.

## Configuración de Flask-JWT-Extended

```bash
pip install flask-jwt-extended
```

```python
from flask import Flask, jsonify, request
from flask_jwt_extended import (
    JWTManager, create_access_token, create_refresh_token,
    jwt_required, get_jwt_identity, get_jwt
)
from datetime import timedelta

app = Flask(__name__)

# Configuración de JWT
app.config['JWT_SECRET_KEY'] = 'super-secret-key-cambiar-en-produccion'
app.config['JWT_ACCESS_TOKEN_EXPIRES'] = timedelta(hours=1)
app.config['JWT_REFRESH_TOKEN_EXPIRES'] = timedelta(days=30)
app.config['JWT_TOKEN_LOCATION'] = ['headers']  # También: 'cookies', 'query_string'
app.config['JWT_HEADER_NAME'] = 'Authorization'
app.config['JWT_HEADER_TYPE'] = 'Bearer'

jwt = JWTManager(app)
```

## Login y Generación de Tokens

```python
from werkzeug.security import check_password_hash

@app.route('/auth/login', methods=['POST'])
def login():
    """Autentica al usuario y devuelve tokens JWT."""
    data = request.get_json()
    email = data.get('email')
    password = data.get('password')

    # Buscar usuario en la base de datos
    usuario = Usuario.query.filter_by(email=email).first()

    if not usuario or not check_password_hash(usuario.password_hash, password):
        return jsonify({"error": "Credenciales inválidas"}), 401

    # Crear tokens
    access_token = create_access_token(
        identity=str(usuario.id),
        additional_claims={
            'email': usuario.email,
            'rol': usuario.rol,
            'nombre': usuario.nombre
        }
    )
    refresh_token = create_refresh_token(identity=str(usuario.id))

    return jsonify({
        "access_token": access_token,
        "refresh_token": refresh_token,
        "usuario": {
            "id": usuario.id,
            "nombre": usuario.nombre,
            "email": usuario.email
        }
    }), 200
```

## Proteger Rutas con @jwt_required

```python
@app.route('/api/perfil')
@jwt_required()  # Requiere un access_token válido
def obtener_perfil():
    """Devuelve el perfil del usuario autenticado."""
    user_id = get_jwt_identity()  # Obtiene el 'identity' del token
    claims = get_jwt()  # Obtiene todos los claims del token

    usuario = Usuario.query.get(user_id)
    return jsonify({
        "id": usuario.id,
        "nombre": usuario.nombre,
        "email": usuario.email,
        "rol": claims.get('rol')
    })

@app.route('/api/admin/usuarios')
@jwt_required()
def listar_usuarios():
    """Solo accesible para administradores."""
    claims = get_jwt()
    if claims.get('rol') != 'admin':
        return jsonify({"error": "No tienes permisos de administrador"}), 403

    usuarios = Usuario.query.all()
    return jsonify([u.to_dict() for u in usuarios])

# Ruta con token opcional
@app.route('/api/articulos')
@jwt_required(optional=True)
def listar_articulos():
    """Accesible sin token, pero muestra más datos con token."""
    user_id = get_jwt_identity()
    articulos = Articulo.query.all()

    if user_id:
        # Usuario autenticado: incluir datos adicionales
        return jsonify([a.to_dict(incluir_privados=True) for a in articulos])
    else:
        # Usuario anónimo: solo datos públicos
        return jsonify([a.to_dict() for a in articulos])
```

## Refresh Tokens

Los refresh tokens permiten obtener nuevos access tokens sin pedir las credenciales de nuevo.

```python
@app.route('/auth/refresh', methods=['POST'])
@jwt_required(refresh=True)  # Requiere un refresh_token
def refrescar_token():
    """Genera un nuevo access_token usando el refresh_token."""
    user_id = get_jwt_identity()
    nuevo_access_token = create_access_token(identity=user_id)

    return jsonify({
        "access_token": nuevo_access_token
    }), 200
```

El flujo completo es:

1. Login → recibe access_token (1 hora) + refresh_token (30 días).
2. Usa access_token para acceder a la API.
3. Cuando el access_token expire (401), envía el refresh_token a `/auth/refresh`.
4. Recibe un nuevo access_token y continúa.

## Token Blacklisting (Revocación)

Para invalidar tokens antes de su expiración (logout, cambio de contraseña):

```python
from datetime import datetime

# Almacén de tokens revocados (en producción usa Redis)
tokens_revocados = set()

# En producción, usa Redis para el blacklist:
# import redis
# redis_client = redis.Redis(host='localhost', port=6379, db=1)

@jwt.token_in_blocklist_loader
def verificar_si_token_revocado(jwt_header, jwt_payload):
    """Verifica si un token está en la lista negra."""
    jti = jwt_payload['jti']  # JTI = JWT ID (identificador único)
    return jti in tokens_revocados
    # Con Redis: return redis_client.sismember('tokens_revocados', jti)

@app.route('/auth/logout', methods=['POST'])
@jwt_required()
def logout():
    """Revoca el token actual."""
    jti = get_jwt()['jti']
    tokens_revocados.add(jti)
    # Con Redis: redis_client.sadd('tokens_revocados', jti)
    return jsonify({"mensaje": "Sesión cerrada exitosamente"}), 200
```

## Custom Claims y Decoradores de Rol

```python
from functools import wraps

def rol_requerido(rol):
    """Decorador que verifica el rol del usuario."""
    def decorator(fn):
        @wraps(fn)
        @jwt_required()
        def wrapper(*args, **kwargs):
            claims = get_jwt()
            if claims.get('rol') != rol:
                return jsonify({
                    "error": f"Se requiere el rol '{rol}'"
                }), 403
            return fn(*args, **kwargs)
        return wrapper
    return decorator

@app.route('/admin/panel')
@rol_requerido('admin')
def panel_admin():
    return jsonify({"panel": "datos de administración"})

@app.route('/editor/articulos')
@rol_requerido('editor')
def panel_editor():
    return jsonify({"articulos": "lista de artículos"})
```

## Manejo de Errores JWT

```python
@jwt.expired_token_loader
def token_expirado(jwt_header, jwt_payload):
    return jsonify({
        "error": "Token expirado",
        "codigo": "TOKEN_EXPIRED"
    }), 401

@jwt.invalid_token_loader
def token_invalido(error):
    return jsonify({
        "error": "Token inválido",
        "detalle": str(error)
    }), 422

@jwt.unauthorized_loader
def sin_token(error):
    return jsonify({
        "error": "Token de autorización requerido",
        "codigo": "TOKEN_MISSING"
    }), 401

@jwt.revoked_token_loader
def token_revocado(jwt_header, jwt_payload):
    return jsonify({
        "error": "Token revocado",
        "codigo": "TOKEN_REVOKED"
    }), 401
```

## OAuth2: Login con Terceros

OAuth2 permite que los usuarios se autentiquen con Google, GitHub, etc. usando **Authlib**:

```bash
pip install authlib
```

```python
from authlib.integrations.flask_client import OAuth

oauth = OAuth(app)

# Registrar proveedor de Google
google = oauth.register(
    name='google',
    client_id=os.environ.get('GOOGLE_CLIENT_ID'),
    client_secret=os.environ.get('GOOGLE_CLIENT_SECRET'),
    server_metadata_url='https://accounts.google.com/.well-known/openid-configuration',
    client_kwargs={'scope': 'openid email profile'}
)

# Registrar proveedor de GitHub
github = oauth.register(
    name='github',
    client_id=os.environ.get('GITHUB_CLIENT_ID'),
    client_secret=os.environ.get('GITHUB_CLIENT_SECRET'),
    access_token_url='https://github.com/login/oauth/access_token',
    authorize_url='https://github.com/login/oauth/authorize',
    api_base_url='https://api.github.com/',
    client_kwargs={'scope': 'user:email'}
)

@app.route('/auth/google')
def login_google():
    """Redirige al usuario a Google para autenticarse."""
    redirect_uri = url_for('callback_google', _external=True)
    return google.authorize_redirect(redirect_uri)

@app.route('/auth/google/callback')
def callback_google():
    """Callback después de la autenticación con Google."""
    token = google.authorize_access_token()
    info_usuario = token.get('userinfo')

    if not info_usuario:
        return jsonify({"error": "No se pudo obtener información del usuario"}), 400

    # Buscar o crear usuario en la BD
    usuario = Usuario.query.filter_by(email=info_usuario['email']).first()
    if not usuario:
        usuario = Usuario(
            nombre=info_usuario['name'],
            email=info_usuario['email'],
            avatar=info_usuario.get('picture'),
            proveedor='google',
            proveedor_id=info_usuario['sub']
        )
        db.session.add(usuario)
        db.session.commit()

    # Generar JWT para el usuario
    access_token = create_access_token(identity=str(usuario.id))
    refresh_token = create_refresh_token(identity=str(usuario.id))

    # Redirigir al frontend con los tokens
    return redirect(
        f"https://miapp.com/auth/callback"
        f"?access_token={access_token}"
        f"&refresh_token={refresh_token}"
    )

@app.route('/auth/github')
def login_github():
    redirect_uri = url_for('callback_github', _external=True)
    return github.authorize_redirect(redirect_uri)

@app.route('/auth/github/callback')
def callback_github():
    token = github.authorize_access_token()
    resp = github.get('user')
    perfil = resp.json()

    # Obtener email (puede ser privado en GitHub)
    emails_resp = github.get('user/emails')
    emails = emails_resp.json()
    email_principal = next(
        (e['email'] for e in emails if e['primary']), None
    )

    # Buscar o crear usuario
    usuario = Usuario.query.filter_by(email=email_principal).first()
    if not usuario:
        usuario = Usuario(
            nombre=perfil['name'] or perfil['login'],
            email=email_principal,
            avatar=perfil['avatar_url'],
            proveedor='github',
            proveedor_id=str(perfil['id'])
        )
        db.session.add(usuario)
        db.session.commit()

    access_token = create_access_token(identity=str(usuario.id))
    return jsonify({"access_token": access_token})
```

## Ejercicio Práctico

Implementa un sistema de autenticación completo para una API REST:

1. **POST /auth/registro**: registra un usuario con email y contraseña (hash con bcrypt).
2. **POST /auth/login**: devuelve access_token y refresh_token.
3. **POST /auth/refresh**: genera un nuevo access_token.
4. **POST /auth/logout**: revoca el token actual usando Redis como blacklist.
5. **GET /api/perfil**: devuelve perfil del usuario (requiere token).
6. Implementa un decorador `@rol_requerido` para roles `admin`, `editor` y `usuario`.
7. Agrega login con **Google OAuth2** y crea automáticamente el usuario si no existe.
8. Maneja todos los errores de JWT con mensajes claros en español.

## Resumen

- **JWT** permite autenticación stateless con tokens firmados digitalmente.
- **Flask-JWT-Extended** facilita la creación, validación y gestión de tokens JWT.
- Los **access tokens** tienen vida corta (minutos/horas); los **refresh tokens** tienen vida larga (días/semanas).
- El **blacklisting** permite revocar tokens antes de su expiración (logout).
- `@jwt_required()` protege rutas; `get_jwt_identity()` obtiene el usuario autenticado.
- **OAuth2** con Authlib permite login con Google, GitHub y otros proveedores.
- Siempre guarda la `SECRET_KEY` en variables de entorno, nunca en el código.
- Implementa manejo de errores personalizado para todos los escenarios de JWT.
