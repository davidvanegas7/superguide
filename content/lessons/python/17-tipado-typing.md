---
title: "Tipado Estático con typing"
slug: "python-tipado-typing"
description: "Aprende a usar type hints y el módulo typing de Python para escribir código más robusto, documentado y verificable con mypy."
---

# Tipado Estático con typing

Python es un lenguaje de tipado dinámico, pero desde la versión 3.5 ofrece **type hints** (anotaciones de tipo) que permiten declarar los tipos esperados de variables, parámetros y valores de retorno. Aunque no afectan la ejecución, mejoran enormemente la documentación, la detección de errores y la experiencia de desarrollo con herramientas como **mypy**.

## Type Hints Básicos

```python
# Variables con tipo
nombre: str = "Ana"
edad: int = 28
precio: float = 19.99
activo: bool = True

# Funciones con tipos
def saludar(nombre: str) -> str:
    return f"¡Hola, {nombre}!"

def sumar(a: int, b: int) -> int:
    return a + b

# Función que no retorna nada
def imprimir_info(mensaje: str) -> None:
    print(mensaje)

# Python NO rechaza tipos incorrectos en ejecución
resultado = sumar("hola", "mundo")  # Funciona, pero mypy detectará el error
```

## El Módulo `typing`

El módulo `typing` proporciona tipos genéricos y utilidades avanzadas:

### Colecciones Genéricas

```python
from typing import List, Dict, Set, Tuple

# Listas tipadas
nombres: List[str] = ["Ana", "Carlos", "Lucía"]
numeros: list[int] = [1, 2, 3]  # Python 3.9+ (sin importar)

# Diccionarios tipados
edades: Dict[str, int] = {"Ana": 28, "Carlos": 34}
config: dict[str, str] = {"host": "localhost"}  # Python 3.9+

# Conjuntos
tags: Set[str] = {"python", "tutorial", "avanzado"}

# Tuplas (tamaño y tipos fijos)
coordenada: Tuple[float, float] = (40.4168, -3.7038)
registro: Tuple[str, int, bool] = ("Ana", 28, True)

# Tupla de longitud variable
valores: Tuple[int, ...] = (1, 2, 3, 4, 5)
```

### `Optional` y `Union`

```python
from typing import Optional, Union

# Optional: puede ser el tipo indicado o None
def buscar_usuario(id: int) -> Optional[dict]:
    """Retorna un usuario o None si no existe."""
    usuarios = {1: {"nombre": "Ana"}, 2: {"nombre": "Carlos"}}
    return usuarios.get(id)  # Puede retornar None

# Union: puede ser uno de varios tipos
def procesar(dato: Union[str, int]) -> str:
    """Acepta string o int."""
    return str(dato)

# Python 3.10+ — Sintaxis simplificada con |
def procesar_v2(dato: str | int) -> str:
    return str(dato)

def buscar_v2(id: int) -> dict | None:
    return None
```

### `Any`

```python
from typing import Any

def guardar_en_cache(clave: str, valor: Any) -> None:
    """Acepta cualquier tipo de valor."""
    cache = {}
    cache[clave] = valor

# Any desactiva la verificación de tipos para ese valor
dato: Any = 42
dato = "ahora soy string"  # OK con mypy
dato.metodo_inexistente()   # mypy no detectará este error
```

## `TypeVar` y `Generic`

Permiten crear funciones y clases genéricas, que funcionan con múltiples tipos manteniendo la coherencia:

```python
from typing import TypeVar, Generic, List

# TypeVar para funciones genéricas
T = TypeVar("T")

def primero(lista: List[T]) -> T:
    """Retorna el primer elemento manteniendo el tipo."""
    return lista[0]

# mypy infiere el tipo correcto
nombre = primero(["Ana", "Carlos"])  # tipo: str
numero = primero([1, 2, 3])          # tipo: int


# TypeVar con restricciones
Numero = TypeVar("Numero", int, float)

def maximo(a: Numero, b: Numero) -> Numero:
    return a if a > b else b

# Bound: limitar a subclases
from typing import TypeVar

class Animal:
    nombre: str

class Perro(Animal):
    pass

A = TypeVar("A", bound=Animal)

def obtener_nombre(animal: A) -> str:
    return animal.nombre
```

### Clases Genéricas

```python
from typing import TypeVar, Generic, Optional

T = TypeVar("T")

class Pila(Generic[T]):
    """Pila genérica tipada."""

    def __init__(self) -> None:
        self._elementos: list[T] = []

    def push(self, elemento: T) -> None:
        self._elementos.append(elemento)

    def pop(self) -> T:
        if not self._elementos:
            raise IndexError("Pila vacía")
        return self._elementos.pop()

    def peek(self) -> Optional[T]:
        return self._elementos[-1] if self._elementos else None

    def __len__(self) -> int:
        return len(self._elementos)


# Uso con tipo específico
pila_str: Pila[str] = Pila()
pila_str.push("Hola")
valor: str = pila_str.pop()  # mypy sabe que es str

pila_int: Pila[int] = Pila()
pila_int.push(42)
```

## `Protocol` — Tipado Estructural

`Protocol` define interfaces basadas en estructura (duck typing tipado):

```python
from typing import Protocol, runtime_checkable

class Dibujable(Protocol):
    """Cualquier objeto que tenga un método dibujar."""
    def dibujar(self) -> str: ...

class Circulo:
    def dibujar(self) -> str:
        return "○"

class Cuadrado:
    def dibujar(self) -> str:
        return "□"

# No necesita heredar de Dibujable — solo cumplir la interfaz
def renderizar(forma: Dibujable) -> None:
    print(forma.dibujar())

renderizar(Circulo())   # ○
renderizar(Cuadrado())  # □


# runtime_checkable permite usar isinstance
@runtime_checkable
class Serializable(Protocol):
    def to_json(self) -> str: ...

class Usuario:
    def to_json(self) -> str:
        return '{"nombre": "Ana"}'

print(isinstance(Usuario(), Serializable))  # True
```

## `TypeAlias` — Alias de Tipos

```python
from typing import TypeAlias

# Crear alias para tipos complejos
Coordenada: TypeAlias = tuple[float, float]
MatrizNum: TypeAlias = list[list[float]]
Callback: TypeAlias = callable  # Python 3.9+
JSON: TypeAlias = dict[str, "str | int | float | bool | list | dict | None"]

def distancia(punto_a: Coordenada, punto_b: Coordenada) -> float:
    """Calcula la distancia entre dos coordenadas."""
    dx = punto_a[0] - punto_b[0]
    dy = punto_a[1] - punto_b[1]
    return (dx ** 2 + dy ** 2) ** 0.5

# Python 3.12+ — Sintaxis nativa
type Punto = tuple[float, float]
type Matriz = list[list[float]]
```

## `Callable` — Funciones como Tipo

```python
from typing import Callable

# Función que acepta un callback
def aplicar(func: Callable[[int, int], int], a: int, b: int) -> int:
    """Aplica una función a dos argumentos."""
    return func(a, b)

resultado = aplicar(lambda x, y: x + y, 3, 5)  # 8

# Callback sin argumentos
def ejecutar_despues(callback: Callable[[], None], segundos: int) -> None:
    import time
    time.sleep(segundos)
    callback()

# Callable con argumentos variables
from typing import ParamSpec, Concatenate

P = ParamSpec("P")

def decorador(func: Callable[P, T]) -> Callable[P, T]:
    def wrapper(*args: P.args, **kwargs: P.kwargs) -> T:
        return func(*args, **kwargs)
    return wrapper
```

## Dataclasses con Tipos

Las `dataclasses` aprovechan naturalmente las anotaciones de tipo:

```python
from dataclasses import dataclass, field
from typing import Optional

@dataclass
class Producto:
    nombre: str
    precio: float
    stock: int = 0
    tags: list[str] = field(default_factory=list)
    descripcion: Optional[str] = None

    def precio_con_iva(self, tasa: float = 0.21) -> float:
        return self.precio * (1 + tasa)

    def esta_disponible(self) -> bool:
        return self.stock > 0


producto = Producto(
    nombre="Laptop",
    precio=999.99,
    stock=5,
    tags=["electrónica", "computación"],
)

print(producto.precio_con_iva())  # 1209.9879
print(producto.esta_disponible())  # True

# Dataclass inmutable
@dataclass(frozen=True)
class Coordenada:
    latitud: float
    longitud: float

punto = Coordenada(40.4168, -3.7038)
# punto.latitud = 0  # FrozenInstanceError
```

## Verificación con mypy

**mypy** es el verificador de tipos estático más popular para Python:

```bash
# Instalar
pip install mypy

# Verificar un archivo
mypy mi_modulo.py

# Verificar un paquete completo
mypy src/

# Modo estricto
mypy --strict mi_modulo.py
```

```python
# ejemplo.py
def saludar(nombre: str) -> str:
    return f"Hola, {nombre}"

# mypy detectará este error:
resultado: int = saludar("Ana")  # error: Incompatible types in assignment

# Ignorar una línea específica
dato_especial = obtener_dato()  # type: ignore

# Configuración en pyproject.toml
"""
[tool.mypy]
python_version = "3.11"
warn_return_any = true
warn_unused_configs = true
disallow_untyped_defs = true
"""
```

## Ejercicio Práctico

Implementa un sistema de repositorios genéricos con tipado completo:

```python
from typing import TypeVar, Generic, Optional, Protocol
from dataclasses import dataclass, field

# Protocolo para entidades con ID
class ConId(Protocol):
    id: int

T = TypeVar("T", bound=ConId)

@dataclass
class Usuario:
    id: int
    nombre: str
    email: str

@dataclass
class Producto:
    id: int
    nombre: str
    precio: float

class Repositorio(Generic[T]):
    """Repositorio genérico en memoria."""

    def __init__(self) -> None:
        self._datos: dict[int, T] = {}

    def guardar(self, entidad: T) -> None:
        self._datos[entidad.id] = entidad

    def obtener(self, id: int) -> Optional[T]:
        return self._datos.get(id)

    def listar(self) -> list[T]:
        return list(self._datos.values())

    def eliminar(self, id: int) -> bool:
        return self._datos.pop(id, None) is not None

    def contar(self) -> int:
        return len(self._datos)


# Uso con tipo específico
repo_usuarios: Repositorio[Usuario] = Repositorio()
repo_usuarios.guardar(Usuario(1, "Ana", "ana@test.com"))
repo_usuarios.guardar(Usuario(2, "Carlos", "carlos@test.com"))

usuario: Optional[Usuario] = repo_usuarios.obtener(1)
print(usuario)  # Usuario(id=1, nombre='Ana', email='ana@test.com')

repo_productos: Repositorio[Producto] = Repositorio()
repo_productos.guardar(Producto(1, "Laptop", 999.99))
```

**Reto:** Añade métodos `buscar(criterio: Callable[[T], bool]) -> list[T]` y `actualizar(id: int, datos: dict[str, Any]) -> Optional[T]` al repositorio.

## Resumen

- Los **type hints** documentan el código y permiten detección de errores sin afectar la ejecución.
- `Optional`, `Union` y `|` manejan tipos que pueden ser `None` o uno de varios tipos.
- **`TypeVar`** y **`Generic`** permiten crear funciones y clases genéricas con coherencia de tipos.
- **`Protocol`** implementa tipado estructural (duck typing con verificación).
- **`TypeAlias`** simplifica tipos complejos con alias legibles.
- Las **`dataclasses`** combinan naturalmente con anotaciones de tipo.
- **mypy** verifica los tipos estáticamente y detecta errores antes de la ejecución.
