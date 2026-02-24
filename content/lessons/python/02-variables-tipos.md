---
title: "Variables y Tipos de Datos"
slug: "variables-tipos-datos"
description: "Aprende a declarar variables en Python y domina los tipos de datos fundamentales: enteros, flotantes, strings, booleanos y None."
---

# Variables y Tipos de Datos

En Python, las variables son contenedores que almacenan datos. A diferencia de lenguajes como Java o C, Python no requiere declarar el tipo de una variable: el intérprete lo infiere automáticamente. Esta característica hace que programar sea más rápido y fluido, pero también exige que comprendas bien cómo funcionan los tipos de datos.

## Variables en Python

Una variable se crea simplemente asignando un valor con el operador `=`:

```python
# Crear variables (no se declara el tipo)
nombre = "Carlos"
edad = 30
altura = 1.78
es_estudiante = True

# Python infiere el tipo automáticamente
print(nombre)        # Carlos
print(edad)          # 30
print(altura)        # 1.78
print(es_estudiante) # True
```

### Reglas para Nombres de Variables

- Deben comenzar con una letra o guion bajo (`_`), nunca con un número.
- Solo contienen letras, números y guiones bajos.
- Son **case-sensitive**: `nombre`, `Nombre` y `NOMBRE` son variables distintas.
- No pueden ser palabras reservadas (`if`, `for`, `class`, `return`, etc.).

```python
# Nombres válidos
mi_variable = 10
_privada = "secreto"
camelCase = "también válido"
CONSTANTE = 3.14159  # Convención para constantes

# Nombres inválidos (generan error)
# 2nombre = "error"     # Empieza con número
# mi-variable = "error" # Contiene guion medio
# class = "error"       # Palabra reservada
```

La convención en Python (PEP 8) es usar **snake_case** para variables y funciones: `mi_variable`, `nombre_completo`, `calcular_total`.

## Tipos de Datos Fundamentales

### int (Enteros)

Los enteros son números sin parte decimal. En Python, los enteros no tienen límite de tamaño.

```python
edad = 25
temperatura = -5
poblacion_mundial = 8_000_000_000  # Guiones bajos como separadores

# Operaciones con enteros
suma = 10 + 3       # 13
resta = 10 - 3      # 7
producto = 10 * 3   # 30
potencia = 2 ** 10  # 1024
division_entera = 10 // 3  # 3 (descarta decimales)
modulo = 10 % 3     # 1 (resto de la división)

# Los enteros en Python son de precisión arbitraria
numero_grande = 10 ** 100  # ¡Un googol! Sin problema
print(numero_grande)
```

### float (Decimales)

Los flotantes representan números con parte decimal.

```python
pi = 3.14159
precio = 29.99
temperatura = -10.5

# Notación científica
distancia_sol = 1.496e11  # 1.496 × 10^11 metros
particula = 1.6e-19       # 1.6 × 10^-19

# Cuidado con la precisión de punto flotante
print(0.1 + 0.2)  # 0.30000000000000004 (¡no es exactamente 0.3!)

# Para cálculos financieros precisos, usa decimal
from decimal import Decimal
precio = Decimal("19.99")
impuesto = Decimal("0.21")
total = precio * (1 + impuesto)
print(total)  # 24.1879
```

### str (Cadenas de Texto)

Los strings son secuencias de caracteres, inmutables en Python.

```python
# Crear strings con comillas simples o dobles
nombre = "María"
apellido = 'González'

# Strings multilínea con triple comilla
mensaje = """Este es un texto
que ocupa varias
líneas."""

# Caracteres de escape
tabulado = "Columna1\tColumna2"
nueva_linea = "Línea 1\nLínea 2"
comilla = "Ella dijo: \"Hola\""

# Operaciones con strings
saludo = "Hola" + " " + "Mundo"  # Concatenación
risa = "ja" * 3                  # Repetición: "jajaja"
longitud = len(nombre)           # Longitud: 5

# Acceso por índice (empieza en 0)
primera = nombre[0]    # "M"
ultima = nombre[-1]    # "a"

# Métodos de string (no modifican el original, devuelven uno nuevo)
print("python".upper())        # "PYTHON"
print("PYTHON".lower())        # "python"
print("  hola  ".strip())      # "hola" (quita espacios)
print("hola mundo".title())    # "Hola Mundo"
print("hola mundo".replace("mundo", "Python"))  # "hola Python"
print("a,b,c".split(","))      # ["a", "b", "c"]
print("hola".startswith("ho")) # True
print("datos.csv".endswith(".csv"))  # True
```

### bool (Booleanos)

Los booleanos representan valores de verdad: `True` o `False`.

```python
es_mayor = True
tiene_permiso = False

# Resultados de comparaciones
print(10 > 5)      # True
print(10 == 5)     # False
print("a" in "hola")  # False
print("o" in "hola")  # True

# Valores "falsy" (se evalúan como False)
print(bool(0))       # False
print(bool(0.0))     # False
print(bool(""))      # False (string vacío)
print(bool([]))      # False (lista vacía)
print(bool(None))    # False

# Valores "truthy" (se evalúan como True)
print(bool(1))       # True
print(bool(-1))      # True (cualquier número distinto de 0)
print(bool("hola"))  # True (string no vacío)
print(bool([1, 2]))  # True (lista no vacía)
```

### None (Valor Nulo)

`None` representa la ausencia de valor. Es el equivalente de `null` en otros lenguajes.

```python
resultado = None

# Comparar con None usando 'is' (no ==)
if resultado is None:
    print("No hay resultado todavía")

# Las funciones sin return explícito devuelven None
def saludar(nombre):
    print(f"Hola, {nombre}")

valor = saludar("Ana")  # Imprime "Hola, Ana"
print(valor)             # None
```

## La Función type()

`type()` te permite verificar el tipo de cualquier valor o variable:

```python
print(type(42))          # <class 'int'>
print(type(3.14))        # <class 'float'>
print(type("hola"))      # <class 'str'>
print(type(True))        # <class 'bool'>
print(type(None))        # <class 'NoneType'>
print(type([1, 2, 3]))   # <class 'list'>

# También puedes usar isinstance() para verificar tipos
edad = 25
print(isinstance(edad, int))    # True
print(isinstance(edad, float))  # False
print(isinstance(edad, (int, float)))  # True (acepta tupla de tipos)
```

## Conversiones de Tipo (Casting)

Python permite convertir entre tipos de datos:

```python
# String a número
edad_str = "25"
edad_int = int(edad_str)       # 25 (entero)
precio_str = "19.99"
precio_float = float(precio_str)  # 19.99 (flotante)

# Número a string
numero = 42
texto = str(numero)  # "42"

# Float a int (trunca, no redondea)
pi = 3.99
entero = int(pi)     # 3 (no 4)

# Para redondear
redondeado = round(3.7)     # 4
redondeado2 = round(3.14159, 2)  # 3.14 (2 decimales)

# Conversiones que fallan generan ValueError
# int("hola")  # ValueError: invalid literal for int()
# int("3.14")  # ValueError: debe pasar primero por float

# Conversión segura
texto = "abc"
if texto.isdigit():
    numero = int(texto)
else:
    print(f"'{texto}' no es un número válido")
```

## f-strings (Formateo de Cadenas)

Las **f-strings** (Python 3.6+) son la forma más moderna y recomendada de formatear texto:

```python
nombre = "Laura"
edad = 28
altura = 1.65

# f-string básico
print(f"Me llamo {nombre} y tengo {edad} años")

# Expresiones dentro de las llaves
print(f"El próximo año tendré {edad + 1} años")
print(f"Nombre en mayúsculas: {nombre.upper()}")

# Formateo de números
pi = 3.14159265
print(f"Pi con 2 decimales: {pi:.2f}")      # 3.14
print(f"Pi con 4 decimales: {pi:.4f}")      # 3.1416

precio = 1234567.89
print(f"Precio: ${precio:,.2f}")  # Precio: $1,234,567.89

# Alineación y relleno
for producto, precio in [("Café", 2.5), ("Pan", 1.2), ("Leche", 3.0)]:
    print(f"{producto:<10} ${precio:>6.2f}")
# Café       $  2.50
# Pan        $  1.20
# Leche      $  3.00

# f-strings multilínea
ficha = (
    f"Nombre: {nombre}\n"
    f"Edad: {edad}\n"
    f"Altura: {altura}m"
)
print(ficha)
```

## La Función input()

`input()` permite recibir datos del usuario desde la terminal. **Siempre devuelve un string.**

```python
# Solicitar datos al usuario
nombre = input("¿Cuál es tu nombre? ")
print(f"¡Hola, {nombre}!")

# Convertir la entrada a número
edad = int(input("¿Cuántos años tienes? "))
print(f"En 10 años tendrás {edad + 10} años")

# Entrada con validación básica
try:
    peso = float(input("¿Cuánto pesas en kg? "))
    print(f"Tu peso es {peso} kg")
except ValueError:
    print("Error: debes ingresar un número válido")
```

## Ejercicio Práctico

Crea un programa llamado `calculadora_imc.py` que:

1. Solicite al usuario su nombre, peso (en kg) y altura (en metros).
2. Calcule el Índice de Masa Corporal (IMC): $IMC = \frac{peso}{altura^2}$
3. Muestre el resultado formateado con 1 decimal.
4. Clasifique el resultado: Bajo peso (<18.5), Normal (18.5–24.9), Sobrepeso (25–29.9), Obesidad (≥30).

Ejemplo de salida:

```
Nombre: Carlos
Peso (kg): 75
Altura (m): 1.78

Carlos, tu IMC es: 23.7
Clasificación: Normal
```

**Pista:** Usa `float(input(...))` para leer los números y f-strings para mostrar el resultado.

## Resumen

- Las **variables** en Python no requieren declaración de tipo; se crean al asignar un valor.
- Los tipos fundamentales son: `int`, `float`, `str`, `bool` y `None`.
- Usa `type()` para inspeccionar tipos e `isinstance()` para verificar.
- Las **conversiones** entre tipos se hacen con `int()`, `float()`, `str()`, `bool()`.
- Las **f-strings** (`f"texto {variable}"`) son la forma moderna de formatear cadenas.
- `input()` lee datos del usuario como string; convierte el resultado si necesitas números.
- La convención de nombres es **snake_case** según PEP 8.
