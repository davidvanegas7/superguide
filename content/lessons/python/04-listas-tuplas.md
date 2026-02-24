---
title: "Listas y Tuplas"
slug: "listas-tuplas"
description: "Aprende a trabajar con listas (mutables) y tuplas (inmutables) en Python: métodos, slicing, list comprehensions y técnicas avanzadas."
---

# Listas y Tuplas

Las listas y las tuplas son las estructuras de datos secuenciales más importantes de Python. Ambas almacenan colecciones ordenadas de elementos, pero difieren en un aspecto fundamental: las listas son **mutables** (se pueden modificar) y las tuplas son **inmutables** (no se pueden cambiar después de creadas). En esta lección dominarás ambas estructuras.

## Listas: Colecciones Mutables

Una lista es una colección ordenada y modificable de elementos. Se define con corchetes `[]`:

```python
# Crear listas
numeros = [1, 2, 3, 4, 5]
nombres = ["Ana", "Luis", "Marta"]
mixta = [42, "hola", True, 3.14, None]  # Tipos mezclados
vacia = []

# Acceder a elementos por índice (empieza en 0)
print(nombres[0])    # "Ana"
print(nombres[1])    # "Luis"
print(nombres[-1])   # "Marta" (último elemento)
print(nombres[-2])   # "Luis"  (penúltimo)

# Modificar elementos
nombres[1] = "Carlos"
print(nombres)  # ["Ana", "Carlos", "Marta"]

# Longitud de una lista
print(len(numeros))  # 5
```

## Métodos de Listas

Las listas tienen numerosos métodos para manipular su contenido:

### Agregar Elementos

```python
frutas = ["manzana", "banana"]

# append(): agrega un elemento al final
frutas.append("cereza")
print(frutas)  # ["manzana", "banana", "cereza"]

# insert(): inserta en una posición específica
frutas.insert(1, "naranja")
print(frutas)  # ["manzana", "naranja", "banana", "cereza"]

# extend(): agrega múltiples elementos de otra lista
frutas.extend(["uva", "kiwi"])
print(frutas)  # ["manzana", "naranja", "banana", "cereza", "uva", "kiwi"]

# También se puede usar + para concatenar (crea nueva lista)
nueva = frutas + ["mango"]
```

### Eliminar Elementos

```python
colores = ["rojo", "verde", "azul", "verde", "amarillo"]

# remove(): elimina la primera aparición de un valor
colores.remove("verde")
print(colores)  # ["rojo", "azul", "verde", "amarillo"]

# pop(): elimina y devuelve un elemento por índice (último por defecto)
ultimo = colores.pop()
print(ultimo)    # "amarillo"
print(colores)   # ["rojo", "azul", "verde"]

segundo = colores.pop(1)
print(segundo)   # "azul"

# del: elimina por índice o un rango
numeros = [10, 20, 30, 40, 50]
del numeros[0]
print(numeros)   # [20, 30, 40, 50]

# clear(): vacía la lista
numeros.clear()
print(numeros)   # []
```

### Ordenar y Revertir

```python
numeros = [3, 1, 4, 1, 5, 9, 2, 6]

# sort(): ordena la lista IN PLACE (modifica la original)
numeros.sort()
print(numeros)  # [1, 1, 2, 3, 4, 5, 6, 9]

# Orden descendente
numeros.sort(reverse=True)
print(numeros)  # [9, 6, 5, 4, 3, 2, 1, 1]

# sorted(): devuelve una NUEVA lista ordenada (no modifica la original)
original = [3, 1, 4, 1, 5]
ordenada = sorted(original)
print(original)  # [3, 1, 4, 1, 5] (sin cambios)
print(ordenada)  # [1, 1, 3, 4, 5]

# reverse(): invierte el orden IN PLACE
letras = ["a", "b", "c", "d"]
letras.reverse()
print(letras)  # ["d", "c", "b", "a"]

# Ordenar con clave personalizada
palabras = ["Python", "es", "Genial", "y", "poderoso"]
palabras.sort(key=len)  # Ordenar por longitud
print(palabras)  # ["y", "es", "Python", "Genial", "poderoso"]

# Otros métodos útiles
numeros = [3, 1, 4, 1, 5, 9, 2, 6, 5]
print(numeros.count(5))   # 2 (¿cuántas veces aparece 5?)
print(numeros.index(4))   # 2 (¿en qué índice está 4?)
```

## Slicing (Rebanado)

El slicing permite extraer sub-listas usando la sintaxis `[inicio:fin:paso]`:

```python
letras = ["a", "b", "c", "d", "e", "f", "g"]

# [inicio:fin] → desde inicio hasta fin-1
print(letras[1:4])    # ["b", "c", "d"]

# Omitir inicio → desde el principio
print(letras[:3])     # ["a", "b", "c"]

# Omitir fin → hasta el final
print(letras[4:])     # ["e", "f", "g"]

# Con paso
print(letras[::2])    # ["a", "c", "e", "g"] (cada 2 elementos)
print(letras[1::2])   # ["b", "d", "f"]

# Invertir con slicing
print(letras[::-1])   # ["g", "f", "e", "d", "c", "b", "a"]

# El slicing crea una COPIA superficial
copia = letras[:]
copia[0] = "Z"
print(letras[0])  # "a" (original no cambia)

# Slicing también funciona con strings
texto = "Programación"
print(texto[0:8])    # "Programa"
print(texto[::-1])   # "nóicamargorP"
```

## List Comprehensions

Las **list comprehensions** son una forma elegante y concisa de crear listas:

```python
# Forma tradicional
cuadrados = []
for x in range(10):
    cuadrados.append(x ** 2)

# Con list comprehension (equivalente y más Pythónico)
cuadrados = [x ** 2 for x in range(10)]
print(cuadrados)  # [0, 1, 4, 9, 16, 25, 36, 49, 64, 81]

# Con condición (filtro)
pares = [x for x in range(20) if x % 2 == 0]
print(pares)  # [0, 2, 4, 6, 8, 10, 12, 14, 16, 18]

# Con transformación y condición
mayores_de_edad = [
    nombre.upper() 
    for nombre, edad in [("ana", 25), ("luis", 16), ("marta", 30)]
    if edad >= 18
]
print(mayores_de_edad)  # ["ANA", "MARTA"]

# Comprehension anidada (aplanar lista de listas)
matriz = [[1, 2, 3], [4, 5, 6], [7, 8, 9]]
plana = [num for fila in matriz for num in fila]
print(plana)  # [1, 2, 3, 4, 5, 6, 7, 8, 9]

# Con if/else (nota: va ANTES del for)
numeros = [1, 2, 3, 4, 5, 6]
resultado = ["par" if n % 2 == 0 else "impar" for n in numeros]
print(resultado)  # ["impar", "par", "impar", "par", "impar", "par"]
```

## Tuplas: Colecciones Inmutables

Las tuplas son como listas, pero **no se pueden modificar** después de creadas. Se definen con paréntesis `()`:

```python
# Crear tuplas
coordenadas = (10, 20)
colores_rgb = (255, 128, 0)
solo_un_elemento = (42,)  # ¡La coma es necesaria!
vacia = ()

# También se pueden crear sin paréntesis
punto = 3, 5
print(type(punto))  # <class 'tuple'>

# Acceso por índice (igual que listas)
print(coordenadas[0])   # 10
print(coordenadas[-1])  # 20

# Slicing funciona igual
dias = ("lun", "mar", "mié", "jue", "vie", "sáb", "dom")
entre_semana = dias[:5]
print(entre_semana)  # ("lun", "mar", "mié", "jue", "vie")

# ¡INMUTABLES! No se pueden modificar
# coordenadas[0] = 99  # TypeError: 'tuple' object does not support item assignment
```

### ¿Cuándo usar tuplas en lugar de listas?

- Cuando los datos **no deben cambiar** (coordenadas, configuraciones fijas).
- Como **claves de diccionarios** (las listas no pueden ser claves).
- Para **devolver múltiples valores** de una función.
- Son más **eficientes en memoria** que las listas.

```python
# Tuplas como claves de diccionario
distancias = {
    ("Madrid", "Barcelona"): 621,
    ("Madrid", "Sevilla"): 534,
    ("Barcelona", "Valencia"): 349,
}
print(distancias[("Madrid", "Barcelona")])  # 621
```

## Unpacking (Desempaquetado)

El unpacking permite asignar los elementos de una secuencia a variables individuales:

```python
# Unpacking básico
coordenadas = (10, 20, 30)
x, y, z = coordenadas
print(x)  # 10
print(y)  # 20
print(z)  # 30

# Intercambiar variables (muy Pythónico)
a, b = 5, 10
a, b = b, a
print(a, b)  # 10, 5

# Unpacking con * (capturar el resto)
primero, *resto = [1, 2, 3, 4, 5]
print(primero)  # 1
print(resto)    # [2, 3, 4, 5]

*inicio, ultimo = [1, 2, 3, 4, 5]
print(inicio)  # [1, 2, 3, 4]
print(ultimo)  # 5

primero, *medio, ultimo = [1, 2, 3, 4, 5]
print(primero)  # 1
print(medio)    # [2, 3, 4]
print(ultimo)   # 5

# Ignorar valores con _
nombre, _, edad = ("Ana", "González", 25)
print(nombre, edad)  # Ana 25
```

## enumerate() y zip()

Dos funciones esenciales para iterar sobre secuencias:

```python
# enumerate(): obtener índice y valor simultáneamente
frutas = ["manzana", "banana", "cereza"]
for indice, fruta in enumerate(frutas):
    print(f"{indice}: {fruta}")
# 0: manzana
# 1: banana
# 2: cereza

# Empezar desde otro índice
for num, fruta in enumerate(frutas, start=1):
    print(f"{num}. {fruta}")
# 1. manzana
# 2. banana
# 3. cereza

# zip(): combinar varias secuencias en paralelo
nombres = ["Ana", "Luis", "Marta"]
edades = [25, 30, 28]
ciudades = ["Madrid", "Lima", "Bogotá"]

for nombre, edad, ciudad in zip(nombres, edades, ciudades):
    print(f"{nombre} ({edad}) vive en {ciudad}")
# Ana (25) vive en Madrid
# Luis (30) vive en Lima
# Marta (28) vive en Bogotá

# zip para crear diccionarios
datos = dict(zip(nombres, edades))
print(datos)  # {"Ana": 25, "Luis": 30, "Marta": 28}
```

## Funciones Útiles para Secuencias

```python
numeros = [3, 1, 4, 1, 5, 9, 2, 6]

print(len(numeros))    # 8 (longitud)
print(sum(numeros))    # 31 (suma)
print(min(numeros))    # 1 (mínimo)
print(max(numeros))    # 9 (máximo)
print(sorted(numeros)) # [1, 1, 2, 3, 4, 5, 6, 9] (nueva lista ordenada)

# any() y all()
notas = [85, 92, 40, 78, 95]
print(any(n < 50 for n in notas))  # True (¿alguna < 50?)
print(all(n >= 50 for n in notas)) # False (¿todas >= 50?)
```

## Ejercicio Práctico

Crea un programa llamado `gestor_tareas.py` que:

1. Mantenga una lista de tareas (strings).
2. Muestre un menú con las opciones: Agregar tarea, Marcar como completada (eliminar), Ver tareas, Salir.
3. Al ver tareas, use `enumerate()` para mostrar cada tarea con su número.
4. Use una lista para las tareas pendientes y otra para las completadas.
5. Al salir, muestre un resumen: total de tareas completadas y pendientes.

Ejemplo:

```
--- GESTOR DE TAREAS ---
1. Agregar tarea
2. Completar tarea
3. Ver tareas
4. Salir

Opción: 1
Nueva tarea: Estudiar Python
✅ Tarea agregada

Opción: 3
📋 Tareas pendientes:
  1. Estudiar Python
```

**Pista:** Usa `append()` para agregar, `pop(indice)` para mover a completadas, y `enumerate(tareas, 1)` para mostrar con numeración.

## Resumen

- Las **listas** (`[]`) son mutables: `append`, `insert`, `extend`, `pop`, `remove`, `sort`, `reverse`.
- El **slicing** (`[inicio:fin:paso]`) extrae sub-secuencias de listas, tuplas y strings.
- Las **list comprehensions** (`[expr for x in iterable if cond]`) crean listas de forma concisa.
- Las **tuplas** (`()`) son inmutables, más eficientes y pueden ser claves de diccionario.
- El **unpacking** asigna elementos de secuencias a variables individuales.
- `enumerate()` da índice y valor; `zip()` combina múltiples secuencias en paralelo.
- Funciones como `len()`, `sum()`, `min()`, `max()`, `sorted()`, `any()`, `all()` operan sobre secuencias.
