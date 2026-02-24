---
title: "Iteradores y Generadores"
slug: "python-iteradores-generadores"
description: "Domina el protocolo iterador, generadores con yield y las herramientas de itertools para procesar datos de forma eficiente en Python."
---

# Iteradores y Generadores

Los iteradores y generadores son conceptos fundamentales en Python que permiten recorrer secuencias de datos de forma eficiente, sin necesidad de cargar todos los elementos en memoria. Comprender estos mecanismos te dará un control mucho más fino sobre cómo se procesan colecciones grandes de datos y te permitirá escribir código más elegante y performante.

## El Protocolo Iterador

En Python, un objeto es **iterable** si implementa el método `__iter__()`, que debe devolver un **iterador**. Un iterador, a su vez, implementa dos métodos especiales:

- `__iter__()`: devuelve el propio objeto iterador.
- `__next__()`: devuelve el siguiente elemento de la secuencia o lanza `StopIteration` cuando no hay más elementos.

```python
# Veamos cómo funciona internamente un for loop
numeros = [10, 20, 30]
iterador = iter(numeros)  # Llama a numeros.__iter__()

print(next(iterador))  # 10 — Llama a iterador.__next__()
print(next(iterador))  # 20
print(next(iterador))  # 30
# next(iterador)  # StopIteration
```

Cuando escribimos `for x in coleccion`, Python internamente obtiene un iterador con `iter()` y llama a `next()` repetidamente hasta que se lanza `StopIteration`.

## Crear Iteradores Personalizados

Puedes crear tus propios iteradores implementando `__iter__` y `__next__` en una clase:

```python
class Contador:
    """Iterador que cuenta desde inicio hasta fin (exclusivo)."""

    def __init__(self, inicio, fin):
        self.actual = inicio
        self.fin = fin

    def __iter__(self):
        return self  # El iterador se devuelve a sí mismo

    def __next__(self):
        if self.actual >= self.fin:
            raise StopIteration
        valor = self.actual
        self.actual += 1
        return valor


# Uso con for
for n in Contador(1, 5):
    print(n)  # 1, 2, 3, 4

# Convertir a lista
lista = list(Contador(10, 15))
print(lista)  # [10, 11, 12, 13, 14]
```

Otro ejemplo más práctico: un iterador que lee un archivo línea por línea en bloques:

```python
class LectorBloques:
    """Lee un archivo devolviendo bloques de N líneas."""

    def __init__(self, ruta, tamano_bloque=3):
        self.ruta = ruta
        self.tamano = tamano_bloque
        self.archivo = open(ruta, "r", encoding="utf-8")

    def __iter__(self):
        return self

    def __next__(self):
        lineas = []
        for _ in range(self.tamano):
            linea = self.archivo.readline()
            if not linea:
                break
            lineas.append(linea.rstrip("\n"))
        if not lineas:
            self.archivo.close()
            raise StopIteration
        return lineas
```

## Generadores con `yield`

Los **generadores** son una forma mucho más sencilla de crear iteradores. En lugar de definir una clase con `__iter__` y `__next__`, simplemente escribes una función que usa `yield`:

```python
def contador(inicio, fin):
    """Generador que cuenta desde inicio hasta fin (exclusivo)."""
    actual = inicio
    while actual < fin:
        yield actual  # Pausa la ejecución y devuelve el valor
        actual += 1


# Uso idéntico al iterador de clase
for n in contador(1, 5):
    print(n)  # 1, 2, 3, 4
```

Cuando Python encuentra `yield`, la función se convierte en un generador. Cada llamada a `next()` ejecuta el código hasta el siguiente `yield`, devuelve el valor y **congela** el estado de la función.

```python
def fibonacci(limite):
    """Genera números de Fibonacci hasta el límite dado."""
    a, b = 0, 1
    while a <= limite:
        yield a
        a, b = b, a + b


# Los generadores son perezosos: solo calculan cuando se pide
fib = fibonacci(100)
print(next(fib))  # 0
print(next(fib))  # 1
print(next(fib))  # 1
print(list(fib))  # [2, 3, 5, 8, 13, 21, 34, 55, 89] — resto de valores
```

## Expresiones Generadoras (Generator Expressions)

Así como existen list comprehensions, Python ofrece **generator expressions** con paréntesis en lugar de corchetes. Son ideales para procesar grandes volúmenes de datos sin crear listas intermedias:

```python
# List comprehension — crea toda la lista en memoria
cuadrados_lista = [x ** 2 for x in range(1_000_000)]

# Generator expression — genera valores bajo demanda
cuadrados_gen = (x ** 2 for x in range(1_000_000))

# Útil al pasar directamente a funciones
suma = sum(x ** 2 for x in range(1_000_000))
print(suma)  # 333333166666500000
```

## El Método `send()`

Los generadores pueden **recibir valores** desde el exterior con `send()`. Esto convierte al generador en una **corrutina** básica:

```python
def acumulador():
    """Generador que acumula valores enviados con send()."""
    total = 0
    while True:
        valor = yield total  # Recibe valor y devuelve total actual
        if valor is None:
            break
        total += valor


gen = acumulador()
next(gen)          # Inicializar — avanza hasta el primer yield
print(gen.send(10))  # 10
print(gen.send(20))  # 30
print(gen.send(5))   # 35
```

## El Módulo `itertools`

La biblioteca estándar incluye `itertools`, un módulo con herramientas altamente optimizadas para trabajar con iteradores:

```python
import itertools

# chain: encadena múltiples iterables
letras = itertools.chain("ABC", "DEF", "GHI")
print(list(letras))  # ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']

# islice: corta un iterable como un slice, pero sin crear lista
infinito = itertools.count(1)  # 1, 2, 3, ...
primeros_5 = itertools.islice(infinito, 5)
print(list(primeros_5))  # [1, 2, 3, 4, 5]

# product: producto cartesiano
colores = ["rojo", "azul"]
tallas = ["S", "M", "L"]
combinaciones = list(itertools.product(colores, tallas))
print(combinaciones)
# [('rojo', 'S'), ('rojo', 'M'), ('rojo', 'L'),
#  ('azul', 'S'), ('azul', 'M'), ('azul', 'L')]

# combinations: combinaciones sin repetición
equipos = ["A", "B", "C", "D"]
partidos = list(itertools.combinations(equipos, 2))
print(partidos)
# [('A', 'B'), ('A', 'C'), ('A', 'D'), ('B', 'C'), ('B', 'D'), ('C', 'D')]

# groupby: agrupar elementos consecutivos
datos = [("ventas", 100), ("ventas", 200), ("costos", 50), ("costos", 30)]
for clave, grupo in itertools.groupby(datos, key=lambda x: x[0]):
    valores = [v for _, v in grupo]
    print(f"{clave}: {valores}")
# ventas: [100, 200]
# costos: [50, 30]
```

## Ejercicio Práctico

Crea un generador llamado `leer_csv_perezoso` que lea un archivo CSV línea por línea y devuelva diccionarios. Luego usa `itertools` para procesar los datos:

```python
import csv
import itertools


def leer_csv_perezoso(ruta):
    """Lee un CSV y genera diccionarios fila por fila."""
    with open(ruta, "r", encoding="utf-8") as f:
        lector = csv.DictReader(f)
        for fila in lector:
            yield fila


# Uso: procesar solo las primeras 100 filas
primeras = itertools.islice(leer_csv_perezoso("datos.csv"), 100)

# Filtrar y transformar con generadores encadenados
def filtrar_activos(filas):
    for fila in filas:
        if fila.get("activo") == "true":
            yield fila

def calcular_total(filas):
    for fila in filas:
        fila["total"] = float(fila["precio"]) * int(fila["cantidad"])
        yield fila

# Pipeline de procesamiento perezoso
pipeline = calcular_total(filtrar_activos(leer_csv_perezoso("productos.csv")))

for producto in pipeline:
    print(f"{producto['nombre']}: ${producto['total']:.2f}")
```

**Reto adicional:** Implementa un generador infinito que produzca números primos usando la Criba de Eratóstenes de forma perezosa.

## Resumen

- El **protocolo iterador** se basa en `__iter__()` y `__next__()`, y es lo que hace funcionar los bucles `for`.
- Los **generadores** (`yield`) simplifican la creación de iteradores al congelar y reanudar el estado de la función.
- Las **expresiones generadoras** `(x for x in ...)` son la versión perezosa de las list comprehensions.
- `send()` permite enviar datos a un generador, habilitando comunicación bidireccional.
- **`itertools`** ofrece herramientas optimizadas como `chain`, `islice`, `product` y `combinations` para manipular iteradores de forma eficiente.
- Los generadores son ideales para procesar grandes volúmenes de datos sin consumir memoria excesiva.
