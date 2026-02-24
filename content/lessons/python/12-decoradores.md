---
title: "Decoradores"
slug: "python-decoradores"
description: "Aprende a crear y utilizar decoradores en Python para extender funciones y clases de forma elegante y reutilizable."
---

# Decoradores

Los decoradores son una de las características más elegantes y poderosas de Python. Permiten modificar o extender el comportamiento de funciones y clases sin alterar su código fuente, siguiendo el principio abierto/cerrado de diseño de software. Son ampliamente utilizados en frameworks como Flask, Django y FastAPI.

## Funciones como Objetos de Primera Clase

En Python, las funciones son **objetos de primera clase**: se pueden asignar a variables, pasar como argumentos y devolver desde otras funciones.

```python
def saludar(nombre):
    return f"¡Hola, {nombre}!"

# Asignar función a variable
mi_funcion = saludar
print(mi_funcion("Ana"))  # ¡Hola, Ana!

# Pasar función como argumento
def ejecutar(func, valor):
    return func(valor)

print(ejecutar(saludar, "Carlos"))  # ¡Hola, Carlos!

# Devolver función desde otra función
def crear_multiplicador(factor):
    def multiplicar(x):
        return x * factor
    return multiplicar

doble = crear_multiplicador(2)
print(doble(5))  # 10
```

## Closures (Clausuras)

Un **closure** es una función interna que recuerda las variables del ámbito donde fue creada, incluso después de que ese ámbito haya terminado:

```python
def crear_contador():
    cuenta = 0
    def incrementar():
        nonlocal cuenta  # Acceder a la variable del ámbito exterior
        cuenta += 1
        return cuenta
    return incrementar

contador = crear_contador()
print(contador())  # 1
print(contador())  # 2
print(contador())  # 3
```

## Sintaxis de Decoradores

Un decorador es una función que **recibe una función** y **devuelve una nueva función** (o la misma, modificada):

```python
def mi_decorador(func):
    def wrapper(*args, **kwargs):
        print(f"Antes de llamar a {func.__name__}")
        resultado = func(*args, **kwargs)
        print(f"Después de llamar a {func.__name__}")
        return resultado
    return wrapper

# Aplicar con la sintaxis @
@mi_decorador
def sumar(a, b):
    return a + b

# Equivale a: sumar = mi_decorador(sumar)
resultado = sumar(3, 5)
# Antes de llamar a sumar
# Después de llamar a sumar
print(resultado)  # 8
```

## Preservar Metadatos con `functools.wraps`

Sin `functools.wraps`, la función decorada pierde su nombre y docstring originales:

```python
from functools import wraps

def mi_decorador(func):
    @wraps(func)  # Preserva __name__, __doc__, etc.
    def wrapper(*args, **kwargs):
        return func(*args, **kwargs)
    return wrapper

@mi_decorador
def calcular():
    """Realiza un cálculo importante."""
    pass

print(calcular.__name__)  # "calcular" (sin wraps sería "wrapper")
print(calcular.__doc__)   # "Realiza un cálculo importante."
```

## Decoradores con Argumentos

Para crear un decorador que acepte parámetros, necesitas un nivel adicional de anidamiento:

```python
from functools import wraps

def repetir(veces):
    """Decorador que ejecuta la función N veces."""
    def decorador(func):
        @wraps(func)
        def wrapper(*args, **kwargs):
            resultados = []
            for _ in range(veces):
                resultados.append(func(*args, **kwargs))
            return resultados
        return wrapper
    return decorador

@repetir(veces=3)
def saludar(nombre):
    print(f"¡Hola, {nombre}!")
    return nombre

resultados = saludar("María")
# ¡Hola, María! (se imprime 3 veces)
```

## Apilamiento de Decoradores (Stacking)

Se pueden aplicar múltiples decoradores a una misma función. Se ejecutan de abajo hacia arriba:

```python
from functools import wraps

def negrita(func):
    @wraps(func)
    def wrapper(*args, **kwargs):
        return f"<b>{func(*args, **kwargs)}</b>"
    return wrapper

def cursiva(func):
    @wraps(func)
    def wrapper(*args, **kwargs):
        return f"<i>{func(*args, **kwargs)}</i>"
    return wrapper

@negrita      # Se aplica segundo (exterior)
@cursiva      # Se aplica primero (interior)
def mensaje(texto):
    return texto

print(mensaje("Hola"))  # <b><i>Hola</i></b>
# Equivale a: mensaje = negrita(cursiva(mensaje))
```

## Decoradores de Clase

Los decoradores también pueden aplicarse a clases o pueden ser clases ellos mismos:

```python
from functools import wraps

# Decorador aplicado a una clase
def singleton(cls):
    """Asegura que solo exista una instancia de la clase."""
    instancias = {}

    @wraps(cls)
    def obtener_instancia(*args, **kwargs):
        if cls not in instancias:
            instancias[cls] = cls(*args, **kwargs)
        return instancias[cls]

    return obtener_instancia

@singleton
class BaseDatos:
    def __init__(self):
        print("Conectando a la base de datos...")
        self.conectado = True

db1 = BaseDatos()  # Conectando a la base de datos...
db2 = BaseDatos()  # No se imprime nada, reutiliza la instancia
print(db1 is db2)  # True
```

```python
# Clase como decorador (usando __call__)
class ContarLlamadas:
    """Cuenta cuántas veces se llama una función."""

    def __init__(self, func):
        self.func = func
        self.llamadas = 0

    def __call__(self, *args, **kwargs):
        self.llamadas += 1
        print(f"{self.func.__name__} llamada {self.llamadas} veces")
        return self.func(*args, **kwargs)

@ContarLlamadas
def procesar(dato):
    return dato.upper()

procesar("hola")   # procesar llamada 1 veces
procesar("mundo")  # procesar llamada 2 veces
```

## Ejemplos Prácticos

### Decorador de Timing

```python
import time
from functools import wraps

def timing(func):
    """Mide el tiempo de ejecución de una función."""
    @wraps(func)
    def wrapper(*args, **kwargs):
        inicio = time.perf_counter()
        resultado = func(*args, **kwargs)
        fin = time.perf_counter()
        print(f"{func.__name__} tardó {fin - inicio:.4f} segundos")
        return resultado
    return wrapper

@timing
def proceso_pesado():
    time.sleep(1)
    return "Listo"

proceso_pesado()  # proceso_pesado tardó 1.0012 segundos
```

### Decorador de Logging

```python
import logging
from functools import wraps

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

def log_llamada(func):
    """Registra cada llamada a la función con sus argumentos."""
    @wraps(func)
    def wrapper(*args, **kwargs):
        args_repr = [repr(a) for a in args]
        kwargs_repr = [f"{k}={v!r}" for k, v in kwargs.items()]
        firma = ", ".join(args_repr + kwargs_repr)
        logger.info(f"Llamando {func.__name__}({firma})")
        try:
            resultado = func(*args, **kwargs)
            logger.info(f"{func.__name__} retornó {resultado!r}")
            return resultado
        except Exception as e:
            logger.exception(f"{func.__name__} lanzó {type(e).__name__}: {e}")
            raise
    return wrapper

@log_llamada
def dividir(a, b):
    return a / b

dividir(10, 2)    # INFO: Llamando dividir(10, 2) → retornó 5.0
# dividir(10, 0)  # INFO: Lanzó ZeroDivisionError
```

### Decorador de Memoización (Caché)

```python
from functools import wraps

def memoize(func):
    """Cachea resultados de llamadas previas."""
    cache = {}

    @wraps(func)
    def wrapper(*args):
        if args in cache:
            print(f"  (cache hit para {args})")
            return cache[args]
        resultado = func(*args)
        cache[args] = resultado
        return resultado

    wrapper.cache = cache  # Exponer el caché
    return wrapper

@memoize
def fibonacci(n):
    if n < 2:
        return n
    return fibonacci(n - 1) + fibonacci(n - 2)

print(fibonacci(30))  # 832040 — calculado eficientemente
print(fibonacci(30))  # (cache hit para (30,))

# Nota: Python incluye @functools.lru_cache para esto
from functools import lru_cache

@lru_cache(maxsize=128)
def fibonacci_v2(n):
    if n < 2:
        return n
    return fibonacci_v2(n - 1) + fibonacci_v2(n - 2)
```

## Ejercicio Práctico

Crea los siguientes decoradores y aplícalos a funciones de ejemplo:

```python
from functools import wraps

# 1. Decorador que valida tipos de argumentos
def validar_tipos(*tipos):
    def decorador(func):
        @wraps(func)
        def wrapper(*args):
            for arg, tipo in zip(args, tipos):
                if not isinstance(arg, tipo):
                    raise TypeError(
                        f"Se esperaba {tipo.__name__}, "
                        f"se recibió {type(arg).__name__}"
                    )
            return func(*args)
        return wrapper
    return decorador

@validar_tipos(str, int)
def crear_usuario(nombre, edad):
    return {"nombre": nombre, "edad": edad}

print(crear_usuario("Ana", 25))  # {'nombre': 'Ana', 'edad': 25}
# crear_usuario("Ana", "25")    # TypeError

# 2. Decorador de reintentos
def reintentar(intentos=3, excepciones=(Exception,)):
    def decorador(func):
        @wraps(func)
        def wrapper(*args, **kwargs):
            for i in range(intentos):
                try:
                    return func(*args, **kwargs)
                except excepciones as e:
                    print(f"Intento {i+1} falló: {e}")
                    if i == intentos - 1:
                        raise
        return wrapper
    return decorador

@reintentar(intentos=3, excepciones=(ConnectionError,))
def conectar_api():
    # Simular conexión que puede fallar
    import random
    if random.random() < 0.7:
        raise ConnectionError("Servidor no disponible")
    return {"status": "ok"}
```

**Reto:** Crea un decorador `@requiere_autenticacion` que verifique si un usuario tiene permisos antes de ejecutar la función.

## Resumen

- Las **funciones son objetos de primera clase** en Python: se pueden asignar, pasar y devolver.
- Los **closures** permiten que funciones internas recuerden el ámbito donde fueron creadas.
- Un **decorador** recibe una función y devuelve otra, extendiendo su comportamiento.
- Usa `@functools.wraps` siempre para preservar los metadatos de la función original.
- Los decoradores **con argumentos** requieren un nivel adicional de anidamiento.
- Se pueden **apilar** múltiples decoradores; se aplican de abajo hacia arriba.
- Los decoradores de **clase** usan `__call__` y son útiles para mantener estado.
- Casos de uso comunes: timing, logging, caché, validación y control de acceso.
