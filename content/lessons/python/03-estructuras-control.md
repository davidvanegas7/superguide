---
title: "Estructuras de Control"
slug: "estructuras-control"
description: "Domina las estructuras condicionales y los bucles en Python: if/elif/else, while, for, range y el moderno match-case."
---

# Estructuras de Control

Las estructuras de control determinan el flujo de ejecución de un programa. Con ellas puedes tomar decisiones, repetir acciones y reaccionar a distintas situaciones. Python ofrece una sintaxis limpia y expresiva para todas estas operaciones.

## Condicionales: if / elif / else

La estructura `if` evalúa una condición y ejecuta un bloque de código si es verdadera:

```python
edad = 20

if edad >= 18:
    print("Eres mayor de edad")
```

Con `else` puedes definir qué ocurre cuando la condición es falsa:

```python
edad = 15

if edad >= 18:
    print("Eres mayor de edad")
else:
    print("Eres menor de edad")
```

Usa `elif` (abreviatura de *else if*) para evaluar múltiples condiciones:

```python
nota = 85

if nota >= 90:
    calificacion = "Sobresaliente"
elif nota >= 80:
    calificacion = "Notable"
elif nota >= 70:
    calificacion = "Aprobado"
elif nota >= 60:
    calificacion = "Suficiente"
else:
    calificacion = "Suspenso"

print(f"Tu calificación es: {calificacion}")
# Salida: Tu calificación es: Notable
```

### Condicional en una línea (ternario)

Python permite expresiones condicionales en una sola línea:

```python
edad = 20
estado = "mayor" if edad >= 18 else "menor"
print(f"Eres {estado} de edad")

# Útil para asignaciones rápidas
descuento = 0.10 if es_miembro else 0.0
```

## Operadores de Comparación

Los operadores de comparación devuelven valores booleanos (`True` o `False`):

```python
a = 10
b = 20

print(a == b)   # False  (igual a)
print(a != b)   # True   (distinto de)
print(a < b)    # True   (menor que)
print(a > b)    # False  (mayor que)
print(a <= b)   # True   (menor o igual)
print(a >= b)   # False  (mayor o igual)

# Comparaciones encadenadas (exclusivo de Python)
x = 15
print(10 < x < 20)    # True  (10 < 15 < 20)
print(10 < x < 12)    # False (15 no es menor que 12)

# Comparación de identidad
a = [1, 2, 3]
b = [1, 2, 3]
print(a == b)   # True  (mismo contenido)
print(a is b)   # False (distinto objeto en memoria)

c = a
print(a is c)   # True  (mismo objeto)

# Pertenencia
print(3 in [1, 2, 3])       # True
print("a" in "hola")        # True
print("z" not in "hola")    # True
```

## Operadores Lógicos

Los operadores `and`, `or` y `not` combinan expresiones booleanas:

```python
edad = 25
tiene_licencia = True

# and: ambas condiciones deben ser True
if edad >= 18 and tiene_licencia:
    print("Puedes conducir")

# or: al menos una condición debe ser True
es_festivo = False
es_fin_de_semana = True
if es_festivo or es_fin_de_semana:
    print("¡Día libre!")

# not: invierte el valor booleano
esta_lloviendo = False
if not esta_lloviendo:
    print("Puedes salir sin paraguas")

# Combinaciones complejas
edad = 30
ingresos = 50000
buen_historial = True

if (edad >= 21 and ingresos >= 30000) or buen_historial:
    print("Préstamo aprobado")
```

### Evaluación de cortocircuito

Python evalúa los operadores lógicos de izquierda a derecha y se detiene tan pronto como conoce el resultado:

```python
# Con 'and': si el primero es False, no evalúa el segundo
x = 0
if x != 0 and 10 / x > 2:  # No da error: 10/x nunca se evalúa
    print("Válido")

# Con 'or': si el primero es True, no evalúa el segundo
nombre = "" 
nombre_mostrar = nombre or "Anónimo"  # "Anónimo" (porque "" es falsy)
print(nombre_mostrar)
```

## Bucle while

El bucle `while` repite un bloque de código mientras la condición sea verdadera:

```python
# Contador básico
contador = 1
while contador <= 5:
    print(f"Iteración {contador}")
    contador += 1

# Solicitar entrada hasta que sea válida
while True:
    entrada = input("Ingresa un número positivo: ")
    if entrada.isdigit() and int(entrada) > 0:
        numero = int(entrada)
        break  # Sale del bucle
    print("Entrada inválida, intenta de nuevo")

print(f"Ingresaste: {numero}")
```

**Cuidado con los bucles infinitos:** asegúrate de que la condición eventualmente se vuelva `False`, o usa `break` para salir.

```python
# Ejemplo: menú interactivo
while True:
    print("\n--- MENÚ ---")
    print("1. Saludar")
    print("2. Despedirse")
    print("3. Salir")
    
    opcion = input("Elige una opción: ")
    
    if opcion == "1":
        print("¡Hola, bienvenido!")
    elif opcion == "2":
        print("¡Hasta luego!")
    elif opcion == "3":
        print("Cerrando programa...")
        break
    else:
        print("Opción no válida")
```

## Bucle for

El bucle `for` itera sobre los elementos de una secuencia (lista, string, rango, etc.):

```python
# Iterar sobre una lista
frutas = ["manzana", "banana", "cereza"]
for fruta in frutas:
    print(f"Me gusta la {fruta}")

# Iterar sobre un string
for letra in "Python":
    print(letra, end=" ")
# P y t h o n

# Iterar sobre un diccionario
edades = {"Ana": 25, "Luis": 30, "Marta": 28}
for nombre, edad in edades.items():
    print(f"{nombre} tiene {edad} años")
```

## La Función range()

`range()` genera una secuencia de números y es fundamental para bucles `for`:

```python
# range(fin) → de 0 a fin-1
for i in range(5):
    print(i, end=" ")  # 0 1 2 3 4

print()

# range(inicio, fin) → de inicio a fin-1
for i in range(1, 6):
    print(i, end=" ")  # 1 2 3 4 5

print()

# range(inicio, fin, paso)
for i in range(0, 20, 3):
    print(i, end=" ")  # 0 3 6 9 12 15 18

print()

# Contar hacia atrás
for i in range(10, 0, -1):
    print(i, end=" ")  # 10 9 8 7 6 5 4 3 2 1

print()

# Ejemplo práctico: tabla de multiplicar
numero = 7
print(f"\nTabla del {numero}:")
for i in range(1, 11):
    print(f"{numero} x {i} = {numero * i}")
```

## break y continue

Estas sentencias controlan el flujo dentro de un bucle:

```python
# break: termina el bucle completamente
print("Buscando el número 5:")
for i in range(1, 11):
    if i == 5:
        print(f"¡Encontrado: {i}!")
        break
    print(f"  Revisando {i}...")

# continue: salta a la siguiente iteración
print("\nNúmeros impares del 1 al 10:")
for i in range(1, 11):
    if i % 2 == 0:
        continue  # Salta los pares
    print(i, end=" ")  # 1 3 5 7 9

print()

# else en bucles (se ejecuta si el bucle NO se interrumpió con break)
numeros = [2, 4, 6, 8, 10]
for n in numeros:
    if n % 2 != 0:
        print(f"Se encontró un impar: {n}")
        break
else:
    print("Todos los números son pares")
# Salida: Todos los números son pares
```

## match-case (Python 3.10+)

El **pattern matching** es una alternativa moderna y poderosa al encadenamiento de `if/elif`:

```python
# Ejemplo básico
comando = "salir"

match comando:
    case "iniciar":
        print("Iniciando sistema...")
    case "pausar":
        print("Pausando...")
    case "salir" | "exit" | "quit":  # Múltiples patrones
        print("¡Hasta luego!")
    case _:  # Caso por defecto (como 'default' en switch)
        print(f"Comando desconocido: {comando}")

# Pattern matching con desestructuración
punto = (3, 5)

match punto:
    case (0, 0):
        print("Origen")
    case (x, 0):
        print(f"En el eje X, posición {x}")
    case (0, y):
        print(f"En el eje Y, posición {y}")
    case (x, y):
        print(f"Punto en ({x}, {y})")

# Con guardas (condiciones adicionales)
edad = 25

match edad:
    case n if n < 0:
        print("Edad inválida")
    case n if n < 13:
        print("Niño")
    case n if n < 18:
        print("Adolescente")
    case n if n < 65:
        print("Adulto")
    case _:
        print("Adulto mayor")
```

## Bucles Anidados

Puedes colocar un bucle dentro de otro:

```python
# Tabla de multiplicar completa
for i in range(1, 6):
    for j in range(1, 6):
        print(f"{i*j:4d}", end="")
    print()  # Nueva línea después de cada fila

# Salida:
#    1   2   3   4   5
#    2   4   6   8  10
#    3   6   9  12  15
#    4   8  12  16  20
#    5  10  15  20  25

# Buscar en una matriz
matriz = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9]
]

buscado = 5
for fila_idx, fila in enumerate(matriz):
    for col_idx, valor in enumerate(fila):
        if valor == buscado:
            print(f"Encontrado {buscado} en fila {fila_idx}, columna {col_idx}")
```

## Ejercicio Práctico

Crea un programa llamado `adivina_numero.py` que:

1. Genere un número aleatorio entre 1 y 100 (`import random; secreto = random.randint(1, 100)`).
2. Permita al usuario adivinar el número con un máximo de 7 intentos.
3. Después de cada intento, indique si el número secreto es mayor o menor.
4. Muestre el número de intentos usados al acertar.
5. Si se agotan los intentos, revele el número secreto.

Ejemplo de sesión:

```
🎯 Adivina el número (1-100). Tienes 7 intentos.

Intento 1/7: 50
🔼 El número es MAYOR que 50

Intento 2/7: 75
🔽 El número es MENOR que 75

Intento 3/7: 63
🎉 ¡Correcto! Adivinaste en 3 intentos.
```

**Pista:** Usa un `while` con un contador de intentos, `break` cuando acierte y `else` en el `while` para el caso de agotar intentos.

## Resumen

- **if/elif/else** permite tomar decisiones basadas en condiciones.
- Los **operadores de comparación** (`==`, `!=`, `<`, `>`, `<=`, `>=`) devuelven booleanos.
- Los **operadores lógicos** (`and`, `or`, `not`) combinan condiciones.
- **while** repite código mientras una condición sea verdadera.
- **for** itera sobre secuencias; `range()` genera secuencias numéricas.
- **break** termina un bucle; **continue** salta a la siguiente iteración.
- **match-case** (Python 3.10+) ofrece pattern matching potente y expresivo.
