---
title: "Preguntas de Entrevista: Python"
slug: "python-preguntas-entrevista"
description: "Prepárate para entrevistas técnicas de Python con las preguntas más frecuentes, explicaciones detalladas y ejemplos de código."
---

# Preguntas de Entrevista: Python

Las entrevistas técnicas de Python evalúan tanto tu conocimiento del lenguaje como tu capacidad para razonar sobre conceptos fundamentales. Esta lección cubre las preguntas más frecuentes con explicaciones claras, ejemplos de código y los matices que los entrevistadores buscan.

## 1. Mutable vs Inmutable

**Pregunta:** ¿Cuál es la diferencia entre objetos mutables e inmutables en Python?

Los objetos **inmutables** no se pueden modificar después de su creación. Los **mutables** sí pueden cambiar su contenido:

| Inmutables | Mutables |
|-----------|----------|
| `int`, `float`, `str` | `list`, `dict`, `set` |
| `tuple`, `frozenset` | `bytearray` |
| `bool`, `bytes` | Objetos personalizados |

```python
# Inmutable: str
texto = "hola"
texto_upper = texto.upper()  # Crea un NUEVO string
print(texto)        # "hola" — no cambió
print(id(texto))    # ID original

# Mutable: list
lista = [1, 2, 3]
lista.append(4)     # Modifica la MISMA lista
print(lista)        # [1, 2, 3, 4]

# ⚠️ Trampa en valores por defecto
def agregar(elemento, lista=[]):  # ❌ ¡La lista se comparte!
    lista.append(elemento)
    return lista

print(agregar(1))  # [1]
print(agregar(2))  # [1, 2] — ¡No [2]!

# ✅ Solución correcta
def agregar(elemento, lista=None):
    if lista is None:
        lista = []
    lista.append(elemento)
    return lista
```

## 2. Shallow Copy vs Deep Copy

**Pregunta:** ¿Qué diferencia hay entre copia superficial y copia profunda?

```python
import copy

original = [[1, 2], [3, 4], [5, 6]]

# Shallow copy: copia la estructura externa, pero comparte los objetos internos
shallow = copy.copy(original)  # También: original.copy(), list(original), original[:]
shallow[0].append(99)
print(original[0])  # [1, 2, 99] — ¡Se modificó el original!
print(shallow[0])   # [1, 2, 99]

# Deep copy: copia TODO recursivamente
original2 = [[1, 2], [3, 4]]
deep = copy.deepcopy(original2)
deep[0].append(99)
print(original2[0])  # [1, 2] — No se modificó
print(deep[0])       # [1, 2, 99]

# Cuándo importa:
# - Shallow: cuando los elementos internos son inmutables (ints, strings)
# - Deep: cuando los elementos internos son mutables y no deben compartirse
```

## 3. El GIL (Global Interpreter Lock)

**Pregunta:** ¿Qué es el GIL y cómo afecta al rendimiento?

El GIL es un mutex en CPython que permite que solo un hilo ejecute bytecode Python a la vez, incluso en máquinas con múltiples núcleos.

```python
# El GIL afecta a tareas CPU-bound con threading
import threading
import time

def contar(n):
    while n > 0:
        n -= 1

# Con threading — NO es más rápido (GIL)
inicio = time.perf_counter()
t1 = threading.Thread(target=contar, args=(50_000_000,))
t2 = threading.Thread(target=contar, args=(50_000_000,))
t1.start(); t2.start()
t1.join(); t2.join()
print(f"Threading: {time.perf_counter() - inicio:.2f}s")

# Soluciones:
# 1. multiprocessing para CPU-bound (procesos separados)
# 2. asyncio para I/O-bound (no necesita múltiples hilos)
# 3. Librerías en C (NumPy, etc.) liberan el GIL
# 4. Python 3.13+ ofrece modo experimental "free-threaded" (sin GIL)
```

## 4. Decoradores

**Pregunta:** ¿Qué es un decorador y cómo funciona internamente?

```python
from functools import wraps

# Un decorador es una función que recibe una función y devuelve otra
def mi_decorador(func):
    @wraps(func)
    def wrapper(*args, **kwargs):
        print("Antes")
        resultado = func(*args, **kwargs)
        print("Después")
        return resultado
    return wrapper

@mi_decorador
def saludar(nombre):
    return f"Hola, {nombre}"

# @mi_decorador es azúcar sintáctica para:
# saludar = mi_decorador(saludar)

# El entrevistador puede preguntar:
# - ¿Por qué usar @wraps? → Preserva __name__, __doc__
# - ¿Decorador con argumentos? → Triple anidamiento
# - ¿Soporta *args/**kwargs? → Para ser genérico
```

## 5. Generadores

**Pregunta:** ¿Qué son los generadores y cuándo usarlos?

```python
# Un generador es una función que usa yield para producir valores bajo demanda
def fibonacci():
    a, b = 0, 1
    while True:
        yield a
        a, b = b, a + b

# Ventajas:
# 1. Eficientes en memoria (no cargan todo a la vez)
# 2. Representan secuencias infinitas
# 3. Evaluación perezosa (lazy evaluation)

# Generator expression vs list comprehension
gen = (x ** 2 for x in range(1_000_000))   # Casi no usa memoria
lst = [x ** 2 for x in range(1_000_000)]   # ~8 MB en memoria

# Diferencia clave: un generador solo se puede recorrer UNA vez
numeros = (x for x in [1, 2, 3])
print(list(numeros))  # [1, 2, 3]
print(list(numeros))  # [] — ¡Agotado!
```

## 6. `*args` y `**kwargs`

**Pregunta:** ¿Para qué sirven `*args` y `**kwargs`?

```python
# *args — empaqueta argumentos posicionales extras en una tupla
def sumar(*args):
    return sum(args)

print(sumar(1, 2, 3))      # 6
print(sumar(1, 2, 3, 4, 5))  # 15

# **kwargs — empaqueta argumentos con nombre extras en un diccionario
def crear_perfil(**kwargs):
    return kwargs

print(crear_perfil(nombre="Ana", edad=28))
# {'nombre': 'Ana', 'edad': 28}

# Combinados
def funcion(a, b, *args, **kwargs):
    print(f"a={a}, b={b}")
    print(f"args={args}")
    print(f"kwargs={kwargs}")

funcion(1, 2, 3, 4, clave="valor")
# a=1, b=2
# args=(3, 4)
# kwargs={'clave': 'valor'}

# Desempaquetado
def saludar(nombre, edad):
    print(f"{nombre} tiene {edad} años")

datos = {"nombre": "Ana", "edad": 28}
saludar(**datos)  # Ana tiene 28 años

nums = [3, 5]
print(sumar(*nums))  # 8
```

## 7. `is` vs `==`

**Pregunta:** ¿Cuál es la diferencia entre `is` y `==`?

```python
# == compara VALORES (igualdad)
# is compara IDENTIDAD (mismo objeto en memoria)

a = [1, 2, 3]
b = [1, 2, 3]
c = a

print(a == b)   # True — mismos valores
print(a is b)   # False — objetos diferentes
print(a is c)   # True — misma referencia

# ⚠️ Caso especial: integer caching (-5 a 256)
x = 256
y = 256
print(x is y)   # True — Python cachea estos enteros

x = 257
y = 257
print(x is y)   # False — fuera del rango de caché (puede variar)

# ✅ Cuándo usar is:
# - Comparar con None: if x is None
# - Comparar con True/False: if x is True (raro, generalmente if x:)
# - Comparar singletons

# ✅ Cuándo usar ==:
# - Comparar valores: if x == 42
# - Comparar strings: if nombre == "Ana"
```

## 8. `@classmethod` vs `@staticmethod`

**Pregunta:** ¿Cuál es la diferencia entre método de clase y método estático?

```python
class Fecha:
    def __init__(self, dia, mes, año):
        self.dia = dia
        self.mes = mes
        self.año = año

    # Método de instancia: accede a self
    def formatear(self):
        return f"{self.dia:02d}/{self.mes:02d}/{self.año}"

    # Método de clase: recibe la clase (cls), no la instancia
    @classmethod
    def desde_string(cls, fecha_str):
        """Constructor alternativo."""
        dia, mes, año = map(int, fecha_str.split("-"))
        return cls(dia, mes, año)  # Crea una nueva instancia

    # Método estático: no recibe ni self ni cls
    @staticmethod
    def es_bisiesto(año):
        """Utilidad que no necesita acceso a la clase ni instancia."""
        return año % 4 == 0 and (año % 100 != 0 or año % 400 == 0)


fecha = Fecha.desde_string("23-02-2026")  # classmethod como factory
print(fecha.formatear())                   # 23/02/2026
print(Fecha.es_bisiesto(2024))             # True

# Diferencia clave:
# @classmethod → herencia funciona correctamente (cls es la subclase)
# @staticmethod → es solo una función namespaceada en la clase
```

## 9. `__new__` vs `__init__`

**Pregunta:** ¿Cuál es la diferencia entre `__new__` e `__init__`?

```python
class MiClase:
    def __new__(cls, *args, **kwargs):
        """Crea y RETORNA la nueva instancia."""
        print(f"1. __new__ creando instancia de {cls.__name__}")
        instancia = super().__new__(cls)
        return instancia

    def __init__(self, valor):
        """Inicializa la instancia ya creada."""
        print(f"2. __init__ inicializando con {valor}")
        self.valor = valor

obj = MiClase(42)
# 1. __new__ creando instancia de MiClase
# 2. __init__ inicializando con 42

# Uso práctico de __new__: Singleton
class Singleton:
    _instancia = None

    def __new__(cls):
        if cls._instancia is None:
            cls._instancia = super().__new__(cls)
        return cls._instancia

s1 = Singleton()
s2 = Singleton()
print(s1 is s2)  # True

# __new__ también se usa con tipos inmutables (no se puede modificar en __init__)
class MiEntero(int):
    def __new__(cls, valor):
        return super().__new__(cls, abs(valor))  # Siempre positivo

print(MiEntero(-42))  # 42
```

## 10. Duck Typing

**Pregunta:** ¿Qué es duck typing?

"Si camina como un pato y grazna como un pato, entonces es un pato." Python no verifica el tipo del objeto, sino que tenga los métodos/atributos necesarios:

```python
# No importa el tipo, importa el comportamiento
class Pato:
    def caminar(self):
        print("Caminando como pato")
    def graznar(self):
        print("¡Cuac!")

class Persona:
    def caminar(self):
        print("Caminando como persona")
    def graznar(self):
        print("¡Cuac! (imitando)")

def hacer_cosas(animal):
    # No verificamos el tipo — solo que tenga los métodos
    animal.caminar()
    animal.graznar()

hacer_cosas(Pato())     # Funciona
hacer_cosas(Persona())  # También funciona

# Ejemplo real: cualquier objeto con __len__ funciona con len()
class ColeccionCustom:
    def __len__(self):
        return 42

print(len(ColeccionCustom()))  # 42
```

## 11. Monkey Patching

**Pregunta:** ¿Qué es monkey patching y cuándo es apropiado?

```python
# Monkey patching: modificar clases o módulos en tiempo de ejecución
class Calculadora:
    def sumar(self, a, b):
        return a + b

# Añadir un método dinámicamente
Calculadora.multiplicar = lambda self, a, b: a * b

calc = Calculadora()
print(calc.multiplicar(3, 4))  # 12

# ✅ Uso apropiado: testing (mocking)
import unittest.mock

# ❌ Uso inapropiado: modificar librerías de terceros en producción
# Problemas: difícil de depurar, frágil ante actualizaciones

# Ejemplo de monkey patching para testing
def test_api():
    import requests
    original_get = requests.get

    def mock_get(url, **kwargs):
        class MockResponse:
            status_code = 200
            def json(self):
                return {"dato": "simulado"}
        return MockResponse()

    requests.get = mock_get  # Monkey patch
    # ... ejecutar test ...
    requests.get = original_get  # Restaurar
```

## Preguntas Adicionales Rápidas

```python
# ¿Qué imprime esto?
print(0.1 + 0.2 == 0.3)  # False (error de punto flotante)
# Solución: math.isclose(0.1 + 0.2, 0.3)

# ¿Diferencia entre append y extend?
a = [1, 2]
a.append([3, 4])   # [1, 2, [3, 4]]
b = [1, 2]
b.extend([3, 4])   # [1, 2, 3, 4]

# ¿Qué es __slots__?
class Punto:
    __slots__ = ("x", "y")  # Restringe atributos, ahorra memoria
    def __init__(self, x, y):
        self.x = x
        self.y = y

# ¿List vs tuple?
# Lista: mutable, para colecciones homogéneas
# Tupla: inmutable, para registros heterogéneos, hashable (sirve como key de dict)

# ¿Qué retorna range()?
# Un objeto range (iterable, no una lista). Es perezoso y eficiente.
```

## Ejercicio Práctico

Responde a estas preguntas sin ejecutar el código. Luego verifica tus respuestas:

```python
# 1. ¿Qué imprime?
a = [1, 2, 3]
b = a
b.append(4)
print(a)

# 2. ¿Qué imprime?
def f(x, lst=[]):
    lst.append(x)
    return lst
print(f(1))
print(f(2))
print(f(3))

# 3. ¿Qué imprime?
x = 10
def cambiar():
    x = 20
cambiar()
print(x)

# 4. ¿Qué imprime?
print([i for i in range(5) if i % 2 == 0])

# 5. ¿Es esto válido?
d = {[1, 2]: "valor"}
```

**Respuestas:**
1. `[1, 2, 3, 4]` — `b` es una referencia a la misma lista.
2. `[1]`, `[1, 2]`, `[1, 2, 3]` — El valor por defecto mutable se comparte.
3. `10` — `x` dentro de la función es una variable local.
4. `[0, 2, 4]` — Comprehension con filtro.
5. No. Las listas no son hashables; no pueden ser claves de diccionario.

## Resumen

- **Mutable vs inmutable** afecta la asignación, copia y valores por defecto.
- **Shallow copy** comparte objetos internos; **deep copy** clona todo recursivamente.
- El **GIL** impide paralelismo real con `threading` en tareas CPU-bound.
- Los **decoradores** wrappean funciones; los **generadores** producen valores bajo demanda.
- `*args` empaqueta posicionales; `**kwargs` empaqueta argumentos con nombre.
- `is` compara **identidad**; `==` compara **valor**.
- `@classmethod` recibe `cls`; `@staticmethod` no recibe ni `self` ni `cls`.
- `__new__` **crea** la instancia; `__init__` la **inicializa**.
- **Duck typing**: Python verifica comportamiento, no tipo.
- **Monkey patching**: modificar objetos en runtime — útil en testing, peligroso en producción.
