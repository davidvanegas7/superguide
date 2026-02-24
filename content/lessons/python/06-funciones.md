---
title: "Funciones"
slug: "funciones"
description: "Aprende a definir y usar funciones en Python: parámetros, retorno, scope, *args/**kwargs, lambdas y funciones como objetos de primera clase."
---

# Funciones

Las funciones son bloques de código reutilizables que realizan una tarea específica. Son el pilar de la programación modular y permiten organizar el código, evitar repeticiones y crear abstracciones claras. Python ofrece un sistema de funciones flexible y poderoso.

## Definir y Llamar Funciones

Una función se define con la palabra clave `def`:

```python
# Definir una función
def saludar():
    print("¡Hola, mundo!")

# Llamar (ejecutar) la función
saludar()  # ¡Hola, mundo!

# Función con parámetro
def saludar_a(nombre):
    print(f"¡Hola, {nombre}!")

saludar_a("Ana")    # ¡Hola, Ana!
saludar_a("Carlos") # ¡Hola, Carlos!
```

## return: Devolver Valores

Una función puede devolver un resultado con `return`:

```python
def sumar(a, b):
    return a + b

resultado = sumar(3, 5)
print(resultado)  # 8

# Retornar múltiples valores (se devuelven como tupla)
def dividir(a, b):
    cociente = a // b
    resto = a % b
    return cociente, resto

c, r = dividir(17, 5)
print(f"17 ÷ 5 = {c} con resto {r}")  # 17 ÷ 5 = 3 con resto 2

# return sin valor (o sin return) devuelve None
def solo_imprime(mensaje):
    print(mensaje)
    # return implícito: None

resultado = solo_imprime("Hola")
print(resultado)  # None

# return temprano para salir de la función
def es_par(numero):
    if numero % 2 == 0:
        return True
    return False
```

## Parámetros Posicionales y Keyword

Python soporta diferentes formas de pasar argumentos:

```python
# Parámetros posicionales (el orden importa)
def presentar(nombre, edad, ciudad):
    print(f"{nombre}, {edad} años, de {ciudad}")

presentar("Ana", 25, "Madrid")  # Posicional: por orden

# Argumentos keyword (por nombre, el orden NO importa)
presentar(ciudad="Lima", nombre="Luis", edad=30)

# Mezclar: posicionales primero, luego keyword
presentar("Marta", ciudad="Bogotá", edad=28)
```

### Valores por Defecto

```python
def crear_usuario(nombre, rol="usuario", activo=True):
    return {
        "nombre": nombre,
        "rol": rol,
        "activo": activo
    }

# Usar valores por defecto
usuario1 = crear_usuario("Ana")
print(usuario1)  # {"nombre": "Ana", "rol": "usuario", "activo": True}

# Sobreescribir valores por defecto
admin = crear_usuario("Carlos", rol="admin")
print(admin)  # {"nombre": "Carlos", "rol": "admin", "activo": True}

# ⚠️ CUIDADO: No usar objetos mutables como valor por defecto
# INCORRECTO:
def agregar_item_mal(item, lista=[]):
    lista.append(item)
    return lista

print(agregar_item_mal("a"))  # ["a"]
print(agregar_item_mal("b"))  # ["a", "b"] ¡La lista se comparte!

# CORRECTO:
def agregar_item(item, lista=None):
    if lista is None:
        lista = []
    lista.append(item)
    return lista

print(agregar_item("a"))  # ["a"]
print(agregar_item("b"))  # ["b"] ✓
```

## *args y **kwargs

Permiten recibir un número variable de argumentos:

```python
# *args: captura argumentos posicionales extras como una tupla
def sumar_todos(*numeros):
    print(f"Tipo: {type(numeros)}")  # <class 'tuple'>
    return sum(numeros)

print(sumar_todos(1, 2, 3))        # 6
print(sumar_todos(10, 20, 30, 40)) # 100

# **kwargs: captura argumentos keyword extras como un diccionario
def crear_perfil(nombre, **datos):
    perfil = {"nombre": nombre}
    perfil.update(datos)
    return perfil

perfil = crear_perfil(
    "Ana",
    edad=25,
    ciudad="Madrid",
    profesion="Ingeniera"
)
print(perfil)
# {"nombre": "Ana", "edad": 25, "ciudad": "Madrid", "profesion": "Ingeniera"}

# Combinar ambos
def funcion_flexible(*args, **kwargs):
    print(f"Posicionales: {args}")
    print(f"Con nombre: {kwargs}")

funcion_flexible(1, 2, 3, nombre="Ana", edad=25)
# Posicionales: (1, 2, 3)
# Con nombre: {"nombre": "Ana", "edad": 25}

# Desempaquetar al llamar funciones
def calcular(a, b, c):
    return a + b * c

valores = [2, 3, 4]
print(calcular(*valores))  # 14 (2 + 3*4)

config = {"a": 10, "b": 5, "c": 2}
print(calcular(**config))  # 20 (10 + 5*2)
```

## Docstrings

Los docstrings documentan qué hace una función, sus parámetros y su valor de retorno:

```python
def calcular_imc(peso, altura):
    """
    Calcula el Índice de Masa Corporal (IMC).
    
    Args:
        peso (float): Peso en kilogramos.
        altura (float): Altura en metros.
    
    Returns:
        float: El IMC calculado.
    
    Raises:
        ValueError: Si el peso o la altura son negativos.
    
    Example:
        >>> calcular_imc(70, 1.75)
        22.86
    """
    if peso <= 0 or altura <= 0:
        raise ValueError("Peso y altura deben ser positivos")
    return round(peso / altura ** 2, 2)

# Acceder al docstring
print(calcular_imc.__doc__)
help(calcular_imc)  # Muestra la documentación formateada
```

## Scope: Alcance de Variables (LEGB)

Python sigue la regla **LEGB** para buscar variables:

- **L**ocal: dentro de la función actual.
- **E**nclosing: en funciones envolventes (closures).
- **G**lobal: a nivel del módulo.
- **B**uilt-in: funciones y nombres predefinidos de Python.

```python
# Variable global
x = "global"

def externa():
    # Variable enclosing
    x = "enclosing"
    
    def interna():
        # Variable local
        x = "local"
        print(f"Interna: {x}")
    
    interna()
    print(f"Externa: {x}")

externa()
print(f"Global: {x}")
# Interna: local
# Externa: enclosing
# Global: global

# Modificar variable global (usar con moderación)
contador = 0

def incrementar():
    global contador
    contador += 1

incrementar()
incrementar()
print(contador)  # 2

# Modificar variable enclosing
def crear_contador():
    cuenta = 0
    
    def incrementar():
        nonlocal cuenta
        cuenta += 1
        return cuenta
    
    return incrementar

contador = crear_contador()
print(contador())  # 1
print(contador())  # 2
print(contador())  # 3
```

## Funciones como Objetos de Primera Clase

En Python, las funciones son objetos. Puedes asignarlas a variables, pasarlas como argumentos y devolverlas desde otras funciones:

```python
# Asignar función a variable
def gritar(texto):
    return texto.upper() + "!!!"

def susurrar(texto):
    return texto.lower() + "..."

# Seleccionar función dinámicamente
comunicar = gritar
print(comunicar("hola"))  # "HOLA!!!"

comunicar = susurrar
print(comunicar("hola"))  # "hola..."

# Pasar funciones como argumento
def aplicar(funcion, valor):
    return funcion(valor)

print(aplicar(gritar, "python"))    # "PYTHON!!!"
print(aplicar(susurrar, "python"))  # "python..."
print(aplicar(len, "python"))       # 6

# Funciones que devuelven funciones (closures)
def crear_multiplicador(factor):
    def multiplicar(numero):
        return numero * factor
    return multiplicar

doble = crear_multiplicador(2)
triple = crear_multiplicador(3)
print(doble(5))   # 10
print(triple(5))  # 15

# Funciones de orden superior integradas
numeros = [1, -2, 3, -4, 5, -6]

# map(): aplicar función a cada elemento
cuadrados = list(map(lambda x: x**2, numeros))
print(cuadrados)  # [1, 4, 9, 16, 25, 36]

# filter(): filtrar elementos
positivos = list(filter(lambda x: x > 0, numeros))
print(positivos)  # [1, 3, 5]

# sorted() con key
palabras = ["Python", "es", "un", "gran", "lenguaje"]
por_longitud = sorted(palabras, key=len)
print(por_longitud)  # ["es", "un", "gran", "Python", "lenguaje"]
```

## Funciones Lambda

Las funciones **lambda** son funciones anónimas de una sola expresión:

```python
# Sintaxis: lambda parametros: expresion

# Equivalentes:
def cuadrado(x):
    return x ** 2

cuadrado_lambda = lambda x: x ** 2

print(cuadrado(5))         # 25
print(cuadrado_lambda(5))  # 25

# Lambda con múltiples parámetros
suma = lambda a, b: a + b
print(suma(3, 4))  # 7

# Uso principal: funciones cortas como argumento
estudiantes = [
    {"nombre": "Ana", "nota": 85},
    {"nombre": "Luis", "nota": 92},
    {"nombre": "Marta", "nota": 78},
]

# Ordenar por nota (descendente)
por_nota = sorted(estudiantes, key=lambda e: e["nota"], reverse=True)
for e in por_nota:
    print(f"{e['nombre']}: {e['nota']}")
# Luis: 92
# Ana: 85
# Marta: 78

# Lambda en diccionarios de funciones
operaciones = {
    "sumar": lambda a, b: a + b,
    "restar": lambda a, b: a - b,
    "multiplicar": lambda a, b: a * b,
    "dividir": lambda a, b: a / b if b != 0 else "Error",
}

op = "multiplicar"
print(operaciones[op](6, 7))  # 42
```

## Ejercicio Práctico

Crea un archivo `analizador_texto.py` con las siguientes funciones:

1. `contar_palabras(texto)` → devuelve el número total de palabras.
2. `palabra_mas_frecuente(texto)` → devuelve la palabra que más se repite y cuántas veces.
3. `filtrar_palabras(texto, longitud_minima)` → devuelve una lista con las palabras que tienen al menos `longitud_minima` caracteres.
4. `estadisticas(texto)` → usa las funciones anteriores y devuelve un diccionario con todas las estadísticas.

Prueba con:

```python
texto = """Python es un lenguaje de programación muy popular.
Python se usa en inteligencia artificial y Python se usa en web.
La programación en Python es divertida y poderosa."""

stats = estadisticas(texto)
print(stats)
# {
#   "total_palabras": 27,
#   "palabra_frecuente": ("python", 4),
#   "palabras_largas": ["lenguaje", "programación", "popular", ...]
# }
```

## Resumen

- Las funciones se definen con `def` y devuelven valores con `return`.
- Python soporta **parámetros posicionales**, **keyword**, y **valores por defecto**.
- `*args` captura argumentos extra como tupla; `**kwargs` como diccionario.
- Los **docstrings** documentan funciones siguiendo convenciones estándar.
- El scope sigue la regla **LEGB**: Local → Enclosing → Global → Built-in.
- Las funciones son **objetos de primera clase**: se asignan, pasan y retornan.
- Las **lambdas** (`lambda x: expr`) son funciones anónimas de una expresión.
- Funciones como `map()`, `filter()` y `sorted(key=...)` aprovechan funciones como argumentos.
