---
title: "Testing en Flask"
slug: "flask-testing"
description: "Aprende a escribir tests unitarios y de integración para aplicaciones Flask usando pytest, fixtures y el test client."
---

# Testing en Flask

Escribir tests es una práctica esencial en el desarrollo de software profesional. Flask proporciona herramientas integradas para probar tu aplicación de forma sencilla, y combinado con **pytest**, puedes construir suites de tests robustas y mantenibles. En esta lección aprenderás a testear rutas, APIs, interacciones con base de datos y más.

## El Test Client de Flask

Flask incluye un cliente de pruebas que simula peticiones HTTP sin necesidad de levantar un servidor real. Se accede mediante `app.test_client()`.

```python
# app.py
from flask import Flask, jsonify

def create_app():
    app = Flask(__name__)

    @app.route('/')
    def index():
        return jsonify({"mensaje": "Hola Mundo"})

    @app.route('/usuarios/<int:user_id>')
    def obtener_usuario(user_id):
        # Simulamos un usuario
        return jsonify({"id": user_id, "nombre": "Ana"})

    return app
```

```python
# test_app.py
from app import create_app

def test_index():
    """Verifica que la ruta principal responda correctamente."""
    app = create_app()
    client = app.test_client()

    response = client.get('/')
    data = response.get_json()

    assert response.status_code == 200
    assert data['mensaje'] == 'Hola Mundo'

def test_obtener_usuario():
    """Verifica que se devuelva un usuario válido."""
    app = create_app()
    client = app.test_client()

    response = client.get('/usuarios/1')
    data = response.get_json()

    assert response.status_code == 200
    assert data['id'] == 1
    assert data['nombre'] == 'Ana'
```

## Configuración con pytest y conftest.py

El archivo `conftest.py` permite definir **fixtures** reutilizables en todos tus tests. Con fixtures evitas repetir la creación de la app y el client en cada test.

```python
# conftest.py
import pytest
from app import create_app
from extensions import db

@pytest.fixture()
def app():
    """Crea una instancia de la app configurada para testing."""
    app = create_app()
    app.config.update({
        "TESTING": True,
        "SQLALCHEMY_DATABASE_URI": "sqlite:///:memory:",
        "WTF_CSRF_ENABLED": False,  # Desactivar CSRF en tests
    })

    # Crear las tablas en la base de datos en memoria
    with app.app_context():
        db.create_all()
        yield app
        db.session.remove()
        db.drop_all()

@pytest.fixture()
def client(app):
    """Proporciona un test client de Flask."""
    return app.test_client()

@pytest.fixture()
def runner(app):
    """Proporciona un CLI test runner."""
    return app.test_cli_runner()
```

Ahora los tests son más limpios:

```python
# test_rutas.py
def test_pagina_principal(client):
    """El fixture 'client' se inyecta automáticamente."""
    response = client.get('/')
    assert response.status_code == 200

def test_pagina_no_encontrada(client):
    """Verifica que rutas inexistentes devuelvan 404."""
    response = client.get('/ruta-inexistente')
    assert response.status_code == 404
```

## Testing de Rutas con Métodos HTTP

Puedes simular cualquier método HTTP: GET, POST, PUT, DELETE, etc.

```python
def test_crear_producto(client):
    """Prueba la creación de un producto vía POST."""
    datos = {
        "nombre": "Laptop",
        "precio": 999.99,
        "categoria": "Electrónica"
    }

    response = client.post(
        '/api/productos',
        json=datos,  # Envía JSON automáticamente
        headers={"Authorization": "Bearer token123"}
    )
    data = response.get_json()

    assert response.status_code == 201
    assert data['nombre'] == 'Laptop'
    assert data['precio'] == 999.99

def test_actualizar_producto(client):
    """Prueba la actualización parcial de un producto."""
    response = client.put(
        '/api/productos/1',
        json={"precio": 899.99}
    )

    assert response.status_code == 200
    assert response.get_json()['precio'] == 899.99

def test_eliminar_producto(client):
    """Prueba la eliminación de un producto."""
    response = client.delete('/api/productos/1')
    assert response.status_code == 204
```

## Testing con Base de Datos

Para probar operaciones con base de datos, usa una base de datos en memoria (SQLite) y fixtures que preparen datos iniciales.

```python
# test_modelos.py
from models import Usuario

@pytest.fixture()
def usuario_ejemplo(app):
    """Fixture que crea un usuario de prueba en la BD."""
    with app.app_context():
        usuario = Usuario(
            nombre="Carlos",
            email="carlos@ejemplo.com"
        )
        usuario.set_password("clave_segura")
        db.session.add(usuario)
        db.session.commit()
        yield usuario

def test_crear_usuario(app):
    """Verifica que se pueda crear un usuario en la BD."""
    with app.app_context():
        usuario = Usuario(nombre="María", email="maria@test.com")
        usuario.set_password("123456")
        db.session.add(usuario)
        db.session.commit()

        guardado = Usuario.query.filter_by(email="maria@test.com").first()
        assert guardado is not None
        assert guardado.nombre == "María"

def test_login_exitoso(client, usuario_ejemplo):
    """Verifica el login con credenciales correctas."""
    response = client.post('/login', json={
        "email": "carlos@ejemplo.com",
        "password": "clave_segura"
    })

    assert response.status_code == 200
    assert 'token' in response.get_json()
```

## Mocking con unittest.mock

El **mocking** permite simular servicios externos (APIs, correos, etc.) para aislar tus tests.

```python
from unittest.mock import patch, MagicMock

def test_enviar_correo_bienvenida(client):
    """Verifica que se envíe un correo al registrar un usuario."""
    with patch('servicios.correo.enviar_email') as mock_enviar:
        mock_enviar.return_value = True

        response = client.post('/registro', json={
            "nombre": "Laura",
            "email": "laura@test.com",
            "password": "segura123"
        })

        assert response.status_code == 201
        # Verificar que se llamó la función de envío
        mock_enviar.assert_called_once_with(
            destinatario="laura@test.com",
            asunto="Bienvenida",
        )

def test_api_externa_fallida(client):
    """Simula un fallo en una API externa."""
    with patch('servicios.api_clima.obtener_clima') as mock_clima:
        mock_clima.side_effect = ConnectionError("Servicio no disponible")

        response = client.get('/clima/Madrid')

        assert response.status_code == 503
        assert 'error' in response.get_json()
```

## Testing de APIs REST Completo

```python
class TestAPIProductos:
    """Suite de tests para la API de productos."""

    def test_listar_productos_vacio(self, client):
        response = client.get('/api/productos')
        assert response.status_code == 200
        assert response.get_json() == []

    def test_ciclo_crud_completo(self, client):
        # Crear
        res = client.post('/api/productos', json={
            "nombre": "Teclado", "precio": 49.99
        })
        assert res.status_code == 201
        producto_id = res.get_json()['id']

        # Leer
        res = client.get(f'/api/productos/{producto_id}')
        assert res.status_code == 200
        assert res.get_json()['nombre'] == 'Teclado'

        # Actualizar
        res = client.put(f'/api/productos/{producto_id}', json={
            "precio": 39.99
        })
        assert res.status_code == 200

        # Eliminar
        res = client.delete(f'/api/productos/{producto_id}')
        assert res.status_code == 204

        # Verificar eliminación
        res = client.get(f'/api/productos/{producto_id}')
        assert res.status_code == 404
```

## Test Coverage

La cobertura de tests te dice qué porcentaje de tu código está siendo probado.

```bash
# Instalar pytest-cov
pip install pytest-cov

# Ejecutar tests con cobertura
pytest --cov=app --cov-report=html --cov-report=term-missing

# Resultado de ejemplo:
# Name          Stmts   Miss  Cover   Missing
# app.py           45      3    93%   22, 35, 41
# models.py        30      0   100%
# rutas.py         60      8    87%   15-18, 44-47
```

Puedes configurar la cobertura mínima aceptable en `pytest.ini`:

```ini
# pytest.ini
[pytest]
testpaths = tests
addopts = --cov=app --cov-fail-under=80
```

## Ejercicio Práctico

Crea una aplicación Flask con una API de tareas (TODO) y escribe los siguientes tests:

1. **Configura `conftest.py`** con fixtures para `app`, `client` y una tarea de ejemplo.
2. **Test GET /tareas**: verifica que devuelva una lista y status 200.
3. **Test POST /tareas**: crea una tarea y verifica que se guarde en la BD.
4. **Test PUT /tareas/<id>**: marca una tarea como completada.
5. **Test DELETE /tareas/<id>**: elimina una tarea y verifica que ya no exista.
6. **Test de validación**: envía datos incompletos y verifica que devuelva 400.
7. **Usa mocking** para simular el envío de una notificación al completar una tarea.
8. Ejecuta los tests con cobertura y asegúrate de tener al menos 85%.

## Resumen

- Flask proporciona `test_client()` para simular peticiones HTTP en tests.
- **pytest fixtures** y `conftest.py` permiten reutilizar configuración entre tests.
- Usa bases de datos en memoria (SQLite) para tests aislados y rápidos.
- El **mocking** permite simular servicios externos y aislar la lógica bajo prueba.
- Siempre mide la **cobertura de tests** para identificar código sin probar.
- Los tests deben cubrir casos exitosos, errores y validaciones.
- Configura `TESTING = True` para activar el modo de pruebas de Flask.
