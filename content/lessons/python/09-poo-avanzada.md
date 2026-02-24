---
title: "POO Avanzada"
slug: "poo-avanzada"
description: "Explora conceptos avanzados de POO en Python: herencia múltiple, MRO, clases abstractas, dunder methods, dataclasses y __slots__."
---

# POO Avanzada

Una vez dominados los fundamentos de la Programación Orientada a Objetos, Python te ofrece herramientas avanzadas que elevan la expresividad y potencia de tus diseños. En esta lección exploraremos herencia múltiple, clases abstractas, métodos mágicos (dunder methods), dataclasses y optimización con `__slots__`.

## Herencia Múltiple

Python permite que una clase herede de **múltiples clases padre** simultáneamente:

```python
class Volador:
    def volar(self):
        return f"{self.nombre} está volando"

class Nadador:
    def nadar(self):
        return f"{self.nombre} está nadando"

class Caminante:
    def caminar(self):
        return f"{self.nombre} está caminando"

# Herencia múltiple
class Pato(Caminante, Volador, Nadador):
    def __init__(self, nombre):
        self.nombre = nombre

donald = Pato("Donald")
print(donald.caminar())  # "Donald está caminando"
print(donald.volar())    # "Donald está volando"
print(donald.nadar())    # "Donald está nadando"
```

### MRO (Method Resolution Order)

Cuando múltiples clases padre definen el mismo método, Python sigue el **MRO** para decidir cuál ejecutar. El MRO utiliza el algoritmo **C3 linearization**:

```python
class A:
    def metodo(self):
        return "A"

class B(A):
    def metodo(self):
        return "B"

class C(A):
    def metodo(self):
        return "C"

class D(B, C):
    pass

d = D()
print(d.metodo())  # "B" (primero busca en B, luego C, luego A)

# Ver el MRO completo
print(D.__mro__)
# (<class 'D'>, <class 'B'>, <class 'C'>, <class 'A'>, <class 'object'>)

# También con .mro()
for cls in D.mro():
    print(cls.__name__, end=" → ")
# D → B → C → A → object →
```

### Mixins

Un **mixin** es una clase diseñada para agregar funcionalidad específica sin ser usada de forma independiente. Es un patrón común con herencia múltiple:

```python
class SerializableMixin:
    """Agrega capacidad de serialización a cualquier clase."""
    def to_dict(self):
        return {k: v for k, v in self.__dict__.items() if not k.startswith('_')}
    
    def to_json(self):
        import json
        return json.dumps(self.to_dict(), ensure_ascii=False, indent=2)

class ValidableMixin:
    """Agrega validación básica."""
    def validar(self):
        for attr, valor in self.__dict__.items():
            if valor is None:
                raise ValueError(f"El atributo '{attr}' no puede ser None")
        return True

class TimestampMixin:
    """Agrega marcas de tiempo."""
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        from datetime import datetime
        self.creado_en = datetime.now().isoformat()

# Combinar mixins con una clase base
class Usuario(TimestampMixin, SerializableMixin, ValidableMixin):
    def __init__(self, nombre, email):
        self.nombre = nombre
        self.email = email
        super().__init__()

user = Usuario("Ana", "ana@mail.com")
print(user.to_json())
# {
#   "nombre": "Ana",
#   "email": "ana@mail.com",
#   "creado_en": "2026-02-23T10:30:45.123456"
# }

user.validar()  # True (no hay valores None)
```

## Clases Abstractas (ABC)

Las clases abstractas definen una **interfaz** que las subclases deben implementar. No se pueden instanciar directamente:

```python
from abc import ABC, abstractmethod

class Forma(ABC):
    """Clase abstracta: define métodos que las subclases DEBEN implementar."""
    
    def __init__(self, color="negro"):
        self.color = color
    
    @abstractmethod
    def area(self):
        """Cada forma debe calcular su propia área."""
        pass
    
    @abstractmethod
    def perimetro(self):
        """Cada forma debe calcular su propio perímetro."""
        pass
    
    # Métodos concretos (no abstractos): se heredan normalmente
    def descripcion(self):
        return f"Forma de color {self.color}, área={self.area():.2f}"

# No se puede instanciar una clase abstracta
# forma = Forma()  # TypeError: Can't instantiate abstract class

class Circulo(Forma):
    def __init__(self, radio, color="rojo"):
        super().__init__(color)
        self.radio = radio
    
    def area(self):
        import math
        return math.pi * self.radio ** 2
    
    def perimetro(self):
        import math
        return 2 * math.pi * self.radio

class Rectangulo(Forma):
    def __init__(self, ancho, alto, color="azul"):
        super().__init__(color)
        self.ancho = ancho
        self.alto = alto
    
    def area(self):
        return self.ancho * self.alto
    
    def perimetro(self):
        return 2 * (self.ancho + self.alto)

# Ahora sí se pueden instanciar
circulo = Circulo(5)
rect = Rectangulo(10, 4)

print(circulo.descripcion())  # "Forma de color rojo, área=78.54"
print(rect.descripcion())     # "Forma de color azul, área=40.00"

# Polimorfismo: tratar diferentes formas de manera uniforme
formas = [Circulo(3), Rectangulo(5, 8), Circulo(10)]
area_total = sum(f.area() for f in formas)
print(f"Área total: {area_total:.2f}")
```

## Dunder Methods (Métodos Mágicos)

Los **dunder methods** (double underscore) permiten definir cómo se comportan los objetos con operadores y funciones integradas de Python:

```python
class Vector:
    def __init__(self, x, y):
        self.x = x
        self.y = y
    
    # Representación para el desarrollador (debug)
    def __repr__(self):
        return f"Vector({self.x}, {self.y})"
    
    # Representación para el usuario (print)
    def __str__(self):
        return f"({self.x}, {self.y})"
    
    # Longitud
    def __len__(self):
        # En este caso, retornamos la magnitud como entero
        return int((self.x**2 + self.y**2) ** 0.5)
    
    # Igualdad (==)
    def __eq__(self, otro):
        if not isinstance(otro, Vector):
            return NotImplemented
        return self.x == otro.x and self.y == otro.y
    
    # Menor que (<) → permite usar sorted()
    def __lt__(self, otro):
        if not isinstance(otro, Vector):
            return NotImplemented
        return (self.x**2 + self.y**2) < (otro.x**2 + otro.y**2)
    
    # Suma (+)
    def __add__(self, otro):
        if isinstance(otro, Vector):
            return Vector(self.x + otro.x, self.y + otro.y)
        return NotImplemented
    
    # Multiplicación por escalar (*)
    def __mul__(self, escalar):
        if isinstance(escalar, (int, float)):
            return Vector(self.x * escalar, self.y * escalar)
        return NotImplemented
    
    # Multiplicación reversa (escalar * vector)
    def __rmul__(self, escalar):
        return self.__mul__(escalar)
    
    # Valor absoluto (abs())
    def __abs__(self):
        return (self.x**2 + self.y**2) ** 0.5
    
    # Booleano (bool())
    def __bool__(self):
        return self.x != 0 or self.y != 0

# Usar los dunder methods
v1 = Vector(3, 4)
v2 = Vector(1, 2)

print(v1)            # (3, 4) → __str__
print(repr(v1))      # Vector(3, 4) → __repr__
print(v1 + v2)       # (4, 6) → __add__
print(v1 * 3)        # (9, 12) → __mul__
print(2 * v1)        # (6, 8) → __rmul__
print(abs(v1))       # 5.0 → __abs__
print(v1 == Vector(3, 4))  # True → __eq__
print(v2 < v1)       # True → __lt__

# Gracias a __lt__, podemos ordenar vectores
vectores = [Vector(5, 0), Vector(1, 1), Vector(3, 4)]
print(sorted(vectores))  # [Vector(1, 1), Vector(3, 4), Vector(5, 0)]
```

### Tabla de Dunder Methods Comunes

| Método | Operador/Función | Descripción |
|--------|-----------------|-------------|
| `__str__` | `str()`, `print()` | Representación legible |
| `__repr__` | `repr()` | Representación para debug |
| `__len__` | `len()` | Longitud del objeto |
| `__eq__` | `==` | Igualdad |
| `__lt__` | `<` | Menor que |
| `__le__` | `<=` | Menor o igual |
| `__add__` | `+` | Suma |
| `__sub__` | `-` | Resta |
| `__mul__` | `*` | Multiplicación |
| `__getitem__` | `obj[key]` | Acceso por índice |
| `__setitem__` | `obj[key] = val` | Asignación por índice |
| `__contains__` | `in` | Pertenencia |
| `__iter__` | `for x in obj` | Iteración |
| `__call__` | `obj()` | Llamar como función |
| `__hash__` | `hash()` | Crear hash del objeto |

## Dataclasses

Las **dataclasses** (Python 3.7+) reducen enormemente el código repetitivo al crear clases que principalmente almacenan datos:

```python
from dataclasses import dataclass, field

@dataclass
class Producto:
    nombre: str
    precio: float
    stock: int = 0  # Valor por defecto
    
    # Los dataclasses generan automáticamente:
    # __init__, __repr__, __eq__

# Se crea como una clase normal
laptop = Producto("Laptop", 999.99, 50)
mouse = Producto("Mouse", 29.99, 200)

print(laptop)  # Producto(nombre='Laptop', precio=999.99, stock=50)
print(laptop == Producto("Laptop", 999.99, 50))  # True

# Con campos más avanzados
@dataclass(order=True)  # Habilita <, >, <=, >=
class Estudiante:
    # sort_index se usa para comparaciones pero no en __init__
    sort_index: float = field(init=False, repr=False)
    nombre: str
    nota: float
    materias: list = field(default_factory=list)  # Mutable por defecto
    
    def __post_init__(self):
        """Se ejecuta después de __init__."""
        self.sort_index = self.nota  # Ordenar por nota

e1 = Estudiante("Ana", 9.5, ["Python", "SQL"])
e2 = Estudiante("Luis", 8.7, ["Java"])
e3 = Estudiante("Marta", 9.8)

print(e1)  # Estudiante(nombre='Ana', nota=9.5, materias=['Python', 'SQL'])

# Gracias a order=True y sort_index
estudiantes = sorted([e1, e2, e3], reverse=True)
for e in estudiantes:
    print(f"  {e.nombre}: {e.nota}")
# Marta: 9.8
# Ana: 9.5
# Luis: 8.7

# Frozen dataclass (inmutable, como tupla)
@dataclass(frozen=True)
class Punto:
    x: float
    y: float

p = Punto(3, 4)
# p.x = 5  # FrozenInstanceError: no se puede modificar
print(hash(p))  # Se puede usar como clave de dict o en sets
```

## __slots__: Optimización de Memoria

Por defecto, los atributos de instancia se almacenan en un diccionario (`__dict__`). Con `__slots__` puedes usar una estructura más eficiente:

```python
# Sin __slots__: usa __dict__ (flexible pero consume más memoria)
class PuntoNormal:
    def __init__(self, x, y):
        self.x = x
        self.y = y

# Con __slots__: estructura fija (menos memoria, más rápido)
class PuntoOptimizado:
    __slots__ = ('x', 'y')
    
    def __init__(self, x, y):
        self.x = x
        self.y = y

# Funcionalidad idéntica
p1 = PuntoOptimizado(3, 4)
print(p1.x, p1.y)  # 3 4

# Pero NO puedes añadir atributos nuevos
# p1.z = 5  # AttributeError: 'PuntoOptimizado' object has no attribute 'z'

# No tiene __dict__
# print(p1.__dict__)  # AttributeError

# Comparación de memoria (con muchos objetos)
import sys
normal = PuntoNormal(1, 2)
optimizado = PuntoOptimizado(1, 2)

print(sys.getsizeof(normal.__dict__))  # ~104 bytes (el dict)
# El objeto con __slots__ no tiene __dict__, ahorra memoria

# Útil cuando creas millones de objetos:
# puntos = [PuntoOptimizado(i, i*2) for i in range(1_000_000)]
```

## Ejercicio Práctico

Crea un sistema de calificaciones usando los conceptos avanzados de POO:

1. Crea una clase abstracta `EvaluacionABC` con métodos abstractos `calcular_nota()` y `es_aprobado()`.

2. Crea una `@dataclass` `Examen(EvaluacionABC)` con: `estudiante`, `materia`, `puntos_obtenidos`, `puntos_totales`.

3. Crea una `@dataclass` `Proyecto(EvaluacionABC)` con: `estudiante`, `titulo`, `calidad` (1-10), `complejidad` (1-10).

4. Implementa los dunder methods `__str__`, `__lt__` (para ordenar por nota) y `__eq__`.

5. Crea una lista de diferentes evaluaciones, ordénalas y muestra un reporte.

```python
# Uso esperado:
evaluaciones = [
    Examen("Ana", "Python", 85, 100),
    Proyecto("Luis", "API REST", 8, 9),
    Examen("Marta", "SQL", 92, 100),
]

for e in sorted(evaluaciones, reverse=True):
    estado = "✅" if e.es_aprobado() else "❌"
    print(f"{estado} {e} → Nota: {e.calcular_nota():.1f}")
```

## Resumen

- La **herencia múltiple** permite heredar de varias clases; el **MRO** resuelve conflictos.
- Los **mixins** son clases ligeras que agregan funcionalidad específica.
- Las **clases abstractas** (`ABC`) definen interfaces que las subclases deben implementar.
- Los **dunder methods** (`__str__`, `__add__`, `__eq__`, etc.) definen el comportamiento con operadores y funciones integradas.
- Las **dataclasses** (`@dataclass`) eliminan código repetitivo para clases de datos, generando automáticamente `__init__`, `__repr__` y `__eq__`.
- `__slots__` optimiza el uso de memoria al reemplazar el `__dict__` por una estructura fija.
