---
title: "Testing con pytest"
slug: "python-testing-pytest"
description: "Aprende a escribir tests efectivos en Python con pytest, fixtures, parametrize, mocking y los fundamentos del TDD."
---

# Testing con pytest

El testing es una práctica esencial del desarrollo de software profesional. Python ofrece varias herramientas para testing, siendo **pytest** la más popular y poderosa. En esta lección aprenderás a escribir tests efectivos, usar fixtures, parametrizar pruebas, hacer mocking y seguir los principios del TDD.

## `unittest` vs `pytest`

Python incluye `unittest` en su biblioteca estándar, pero `pytest` se ha convertido en el estándar de facto por su simplicidad:

```python
# Con unittest (más verboso)
import unittest

class TestCalculadora(unittest.TestCase):
    def test_sumar(self):
        self.assertEqual(2 + 2, 4)
    
    def test_dividir(self):
        with self.assertRaises(ZeroDivisionError):
            1 / 0

# Con pytest (más simple y expresivo)
def test_sumar():
    assert 2 + 2 == 4

def test_dividir():
    import pytest
    with pytest.raises(ZeroDivisionError):
        1 / 0
```

Para instalar pytest:

```bash
pip install pytest
```

## Escribir Tests Básicos

Los tests en pytest son funciones que empiezan con `test_` en archivos que empiezan con `test_` o terminan con `_test.py`:

```python
# test_calculadora.py

def sumar(a, b):
    return a + b

def multiplicar(a, b):
    return a * b


# Tests
def test_sumar_enteros():
    assert sumar(2, 3) == 5

def test_sumar_flotantes():
    assert sumar(0.1, 0.2) == pytest.approx(0.3)  # Para flotantes

def test_sumar_negativos():
    assert sumar(-1, -1) == -2

def test_multiplicar():
    assert multiplicar(3, 4) == 12

def test_multiplicar_por_cero():
    assert multiplicar(5, 0) == 0
```

### Assert Enriquecido

pytest proporciona mensajes de error detallados automáticamente:

```python
def test_lista_contenido():
    resultado = [1, 2, 3, 4]
    esperado = [1, 2, 3, 5]
    assert resultado == esperado
    # FAILED: assert [1, 2, 3, 4] == [1, 2, 3, 5]
    #   At index 3: 4 != 5

def test_diccionario():
    usuario = {"nombre": "Ana", "edad": 28}
    assert "email" in usuario  # Falla con mensaje claro

def test_excepciones():
    import pytest
    with pytest.raises(ValueError, match="inválido"):
        raise ValueError("Valor inválido")
```

## Ejecutar Tests

```bash
# Ejecutar todos los tests
pytest

# Ejecutar un archivo específico
pytest test_calculadora.py

# Ejecutar un test específico
pytest test_calculadora.py::test_sumar_enteros

# Con salida detallada
pytest -v

# Mostrar prints
pytest -s

# Detener en el primer fallo
pytest -x

# Ejecutar los últimos tests fallidos
pytest --lf
```

## Fixtures

Las **fixtures** son funciones que preparan datos o recursos necesarios para los tests. Se inyectan automáticamente:

```python
import pytest

# Fixture básica
@pytest.fixture
def usuario():
    """Crea un usuario de prueba."""
    return {
        "nombre": "Ana García",
        "email": "ana@test.com",
        "activo": True,
    }

def test_usuario_nombre(usuario):
    assert usuario["nombre"] == "Ana García"

def test_usuario_activo(usuario):
    assert usuario["activo"] is True


# Fixture con setup y teardown
@pytest.fixture
def base_datos():
    """Configura y limpia la base de datos de prueba."""
    db = {"usuarios": [], "productos": []}
    db["usuarios"].append({"id": 1, "nombre": "Admin"})
    yield db  # El test se ejecuta aquí
    # Teardown: limpiar después del test
    db.clear()

def test_base_datos_tiene_admin(base_datos):
    assert len(base_datos["usuarios"]) == 1
    assert base_datos["usuarios"][0]["nombre"] == "Admin"


# Fixture con alcance (scope)
@pytest.fixture(scope="module")  # Se ejecuta una vez por módulo
def conexion_api():
    """Simula una conexión costosa a una API."""
    print("\nConectando a la API...")
    conexion = {"url": "https://api.test.com", "token": "abc123"}
    yield conexion
    print("\nDesconectando de la API...")
```

### Fixture `conftest.py`

Las fixtures definidas en `conftest.py` están disponibles para todos los tests del directorio:

```python
# conftest.py
import pytest

@pytest.fixture
def datos_ejemplo():
    return [1, 2, 3, 4, 5]

@pytest.fixture
def usuario_admin():
    return {"rol": "admin", "permisos": ["leer", "escribir", "eliminar"]}
```

## Parametrize

`@pytest.mark.parametrize` permite ejecutar el mismo test con diferentes datos:

```python
import pytest

@pytest.mark.parametrize("entrada, esperado", [
    (2, 4),
    (3, 9),
    (4, 16),
    (-2, 4),
    (0, 0),
])
def test_cuadrado(entrada, esperado):
    assert entrada ** 2 == esperado

@pytest.mark.parametrize("email, valido", [
    ("user@example.com", True),
    ("user@.com", False),
    ("@example.com", False),
    ("user@example", False),
    ("user.name@example.co.uk", True),
])
def test_validar_email(email, valido):
    import re
    patron = r"^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
    assert bool(re.match(patron, email)) == valido
```

## Mocking

El **mocking** permite simular objetos y funciones para aislar el código que se está probando:

```python
from unittest.mock import Mock, patch, MagicMock
import pytest


# Ejemplo: función que llama a una API
def obtener_usuario_api(user_id):
    import requests
    response = requests.get(f"https://api.example.com/users/{user_id}")
    response.raise_for_status()
    return response.json()


# Test con patch — reemplazar requests.get temporalmente
@patch("requests.get")
def test_obtener_usuario(mock_get):
    # Configurar el mock
    mock_response = Mock()
    mock_response.json.return_value = {"id": 1, "nombre": "Ana"}
    mock_response.raise_for_status.return_value = None
    mock_get.return_value = mock_response

    # Ejecutar la función
    resultado = obtener_usuario_api(1)

    # Verificar
    assert resultado["nombre"] == "Ana"
    mock_get.assert_called_once_with("https://api.example.com/users/1")


# Mock como fixture
@pytest.fixture
def mock_db():
    db = MagicMock()
    db.consultar.return_value = [{"id": 1}, {"id": 2}]
    db.insertar.return_value = True
    return db

def test_consultar_db(mock_db):
    resultado = mock_db.consultar("SELECT * FROM usuarios")
    assert len(resultado) == 2
    mock_db.consultar.assert_called_once()


# patch como administrador de contexto
def test_hora_actual():
    from datetime import datetime
    with patch("builtins.__import__") as mock_import:
        # Configurar mock de datetime
        pass

# patch.object para métodos específicos
class ServicioEmail:
    def enviar(self, destinatario, asunto, cuerpo):
        # Lógica real de envío
        pass

def test_enviar_email():
    servicio = ServicioEmail()
    with patch.object(servicio, "enviar", return_value=True) as mock_enviar:
        resultado = servicio.enviar("test@test.com", "Hola", "Cuerpo")
        assert resultado is True
        mock_enviar.assert_called_once()
```

## Coverage (Cobertura)

Mide qué porcentaje de tu código está cubierto por tests:

```bash
# Instalar
pip install pytest-cov

# Ejecutar con cobertura
pytest --cov=mi_modulo --cov-report=html

# Ver reporte en terminal
pytest --cov=mi_modulo --cov-report=term-missing
```

```
# Ejemplo de salida
Name                    Stmts   Miss  Cover   Missing
-----------------------------------------------------
mi_modulo/__init__.py       5      0   100%
mi_modulo/calculadora.py   20      3    85%   15-17
mi_modulo/utils.py         30     10    67%   22-31
-----------------------------------------------------
TOTAL                      55     13    76%
```

## Fundamentos de TDD

**Test-Driven Development** sigue el ciclo **Rojo-Verde-Refactor**:

1. **Rojo:** Escribe un test que falle.
2. **Verde:** Escribe el código mínimo para que el test pase.
3. **Refactor:** Mejora el código manteniendo los tests verdes.

```python
# Paso 1: ROJO — Test que falla
def test_pila_push_pop():
    pila = Pila()
    pila.push(1)
    pila.push(2)
    assert pila.pop() == 2
    assert pila.pop() == 1

def test_pila_vacia():
    pila = Pila()
    assert pila.esta_vacia() is True

def test_pila_pop_vacia():
    pila = Pila()
    with pytest.raises(IndexError):
        pila.pop()


# Paso 2: VERDE — Implementación mínima
class Pila:
    def __init__(self):
        self._elementos = []

    def push(self, elemento):
        self._elementos.append(elemento)

    def pop(self):
        if not self._elementos:
            raise IndexError("Pop de pila vacía")
        return self._elementos.pop()

    def esta_vacia(self):
        return len(self._elementos) == 0


# Paso 3: REFACTOR — Mejorar sin romper tests
class Pila:
    def __init__(self):
        self._elementos = []

    def push(self, elemento):
        self._elementos.append(elemento)

    def pop(self):
        if self.esta_vacia():
            raise IndexError("Pop de pila vacía")
        return self._elementos.pop()

    def peek(self):
        if self.esta_vacia():
            raise IndexError("Peek de pila vacía")
        return self._elementos[-1]

    def esta_vacia(self):
        return not self._elementos

    def __len__(self):
        return len(self._elementos)

    def __repr__(self):
        return f"Pila({self._elementos})"
```

## Organización de Tests

Estructura recomendada para un proyecto:

```
mi_proyecto/
├── src/
│   └── mi_modulo/
│       ├── __init__.py
│       ├── calculadora.py
│       └── utils.py
├── tests/
│   ├── conftest.py          # Fixtures compartidas
│   ├── test_calculadora.py
│   └── test_utils.py
├── pytest.ini               # Configuración de pytest
└── pyproject.toml
```

```ini
# pytest.ini
[pytest]
testpaths = tests
python_files = test_*.py
python_functions = test_*
addopts = -v --tb=short
markers =
    slow: tests que tardan mucho
    integration: tests de integración
```

```python
# Usar markers para categorizar tests
import pytest

@pytest.mark.slow
def test_proceso_largo():
    import time
    time.sleep(2)
    assert True

@pytest.mark.integration
def test_conexion_real():
    pass

# Ejecutar solo tests rápidos:
# pytest -m "not slow"
```

## Ejercicio Práctico

Implementa una clase `Carrito` de compras usando TDD:

```python
# test_carrito.py
import pytest
from carrito import Carrito, Producto

@pytest.fixture
def carrito():
    return Carrito()

@pytest.fixture
def producto_laptop():
    return Producto("Laptop", 999.99)

def test_carrito_vacio(carrito):
    assert carrito.total() == 0
    assert carrito.cantidad_items() == 0

def test_agregar_producto(carrito, producto_laptop):
    carrito.agregar(producto_laptop, cantidad=1)
    assert carrito.cantidad_items() == 1

def test_total_con_productos(carrito, producto_laptop):
    carrito.agregar(producto_laptop, cantidad=2)
    assert carrito.total() == pytest.approx(1999.98)

def test_eliminar_producto(carrito, producto_laptop):
    carrito.agregar(producto_laptop)
    carrito.eliminar("Laptop")
    assert carrito.cantidad_items() == 0

@pytest.mark.parametrize("cantidad", [0, -1, -5])
def test_cantidad_invalida(carrito, producto_laptop, cantidad):
    with pytest.raises(ValueError):
        carrito.agregar(producto_laptop, cantidad=cantidad)
```

**Reto:** Implementa la clase `Carrito` para que todos los tests pasen, y luego añade tests para descuentos y límites de stock.

## Resumen

- **pytest** es más simple y expresivo que `unittest`, usando simples `assert`.
- Las **fixtures** (`@pytest.fixture`) preparan datos y recursos con setup/teardown automático.
- `@pytest.mark.parametrize` permite ejecutar un test con múltiples conjuntos de datos.
- El **mocking** (`unittest.mock`) aísla el código de dependencias externas como APIs o bases de datos.
- **Coverage** mide qué porcentaje de código está cubierto por tests.
- **TDD** sigue el ciclo Rojo (test falla) → Verde (código mínimo) → Refactor (mejorar).
- Organiza los tests en un directorio `tests/` con `conftest.py` para fixtures compartidas.
