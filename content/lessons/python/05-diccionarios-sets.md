---
title: "Diccionarios y Sets"
slug: "diccionarios-sets"
description: "Domina los diccionarios (pares clave-valor) y los sets (conjuntos sin duplicados) en Python, con sus métodos y operaciones fundamentales."
---

# Diccionarios y Sets

Los diccionarios y los sets son estructuras de datos fundamentales en Python que complementan a las listas y tuplas. Los diccionarios almacenan pares clave-valor y los sets almacenan elementos únicos sin orden definido. Ambas estructuras ofrecen búsquedas extremadamente rápidas gracias a su implementación basada en tablas hash.

## Diccionarios (dict)

Un diccionario es una colección **no ordenada** (desde Python 3.7 mantiene el orden de inserción) de pares **clave: valor**. Se define con llaves `{}`:

```python
# Crear diccionarios
persona = {
    "nombre": "Carlos",
    "edad": 30,
    "ciudad": "Madrid",
    "profesion": "Desarrollador"
}

# Diccionario vacío
vacio = {}
# o también:
vacio = dict()

# Crear a partir de listas con dict() y zip()
claves = ["nombre", "edad", "ciudad"]
valores = ["Ana", 25, "Lima"]
persona2 = dict(zip(claves, valores))
print(persona2)  # {"nombre": "Ana", "edad": 25, "ciudad": "Lima"}
```

### Acceder a Valores

```python
persona = {"nombre": "Carlos", "edad": 30, "ciudad": "Madrid"}

# Con corchetes (lanza KeyError si la clave no existe)
print(persona["nombre"])  # "Carlos"
# print(persona["email"])  # KeyError: 'email'

# Con get() (devuelve None o un valor por defecto si no existe)
print(persona.get("nombre"))      # "Carlos"
print(persona.get("email"))       # None
print(persona.get("email", "No disponible"))  # "No disponible"

# Verificar si una clave existe
print("nombre" in persona)  # True
print("email" in persona)   # False
```

### Modificar Diccionarios

```python
persona = {"nombre": "Carlos", "edad": 30}

# Agregar o modificar un par clave-valor
persona["email"] = "carlos@example.com"  # Agregar nueva clave
persona["edad"] = 31                      # Modificar valor existente
print(persona)
# {"nombre": "Carlos", "edad": 31, "email": "carlos@example.com"}

# update(): actualizar con otro diccionario
persona.update({"ciudad": "Barcelona", "edad": 32})
print(persona["ciudad"])  # "Barcelona"
print(persona["edad"])    # 32

# setdefault(): asignar solo si la clave NO existe
persona.setdefault("pais", "España")  # Agrega "pais": "España"
persona.setdefault("nombre", "Otro")  # NO cambia, "nombre" ya existe
print(persona["pais"])    # "España"
print(persona["nombre"])  # "Carlos"

# Eliminar pares
del persona["email"]                    # Elimina la clave (KeyError si no existe)
edad = persona.pop("edad")              # Elimina y devuelve el valor
print(edad)  # 32
ultimo = persona.popitem()              # Elimina y devuelve el último par
persona.clear()                         # Vacía el diccionario
```

### Recorrer Diccionarios

```python
producto = {"nombre": "Laptop", "precio": 999.99, "stock": 15}

# Iterar sobre las claves (por defecto)
for clave in producto:
    print(clave)
# nombre, precio, stock

# Iterar sobre los valores
for valor in producto.values():
    print(valor)
# Laptop, 999.99, 15

# Iterar sobre claves y valores (lo más común)
for clave, valor in producto.items():
    print(f"{clave}: {valor}")
# nombre: Laptop
# precio: 999.99
# stock: 15

# keys(), values(), items() devuelven vistas
claves = producto.keys()
valores = producto.values()
pares = producto.items()
print(list(claves))   # ["nombre", "precio", "stock"]
print(list(valores))  # ["Laptop", 999.99, 15]
print(list(pares))    # [("nombre", "Laptop"), ("precio", 999.99), ("stock", 15)]
```

### Diccionarios Anidados

```python
# Diccionarios dentro de diccionarios
empresa = {
    "nombre": "TechCorp",
    "empleados": {
        "dev01": {
            "nombre": "Ana",
            "rol": "Frontend",
            "lenguajes": ["JavaScript", "Python"]
        },
        "dev02": {
            "nombre": "Luis",
            "rol": "Backend",
            "lenguajes": ["Python", "Go"]
        }
    }
}

# Acceder a datos anidados
print(empresa["empleados"]["dev01"]["nombre"])  # "Ana"
print(empresa["empleados"]["dev02"]["lenguajes"][0])  # "Python"

# Acceso seguro con get() encadenado
rol = empresa.get("empleados", {}).get("dev03", {}).get("rol", "No encontrado")
print(rol)  # "No encontrado"
```

### Dict Comprehensions

Al igual que las list comprehensions, puedes crear diccionarios de forma concisa:

```python
# Crear un diccionario de cuadrados
cuadrados = {x: x**2 for x in range(1, 6)}
print(cuadrados)  # {1: 1, 2: 4, 3: 9, 4: 16, 5: 25}

# Filtrar un diccionario
precios = {"manzana": 1.5, "caviar": 150, "pan": 2, "trufa": 300}
economicos = {prod: precio for prod, precio in precios.items() if precio < 10}
print(economicos)  # {"manzana": 1.5, "pan": 2}

# Invertir claves y valores
original = {"a": 1, "b": 2, "c": 3}
invertido = {v: k for k, v in original.items()}
print(invertido)  # {1: "a", 2: "b", 3: "c"}

# Transformar valores
nombres = {"ana": 25, "luis": 30, "marta": 28}
mayusculas = {nombre.upper(): edad for nombre, edad in nombres.items()}
print(mayusculas)  # {"ANA": 25, "LUIS": 30, "MARTA": 28}

# Contar frecuencia de caracteres
texto = "programacion"
frecuencia = {}
for letra in texto:
    frecuencia[letra] = frecuencia.get(letra, 0) + 1
print(frecuencia)
# {"p": 1, "r": 2, "o": 2, "g": 1, "a": 2, "m": 1, "c": 1, "i": 1, "n": 1}
```

## Sets (Conjuntos)

Un set es una colección **no ordenada** de elementos **únicos**. Se define con llaves `{}` pero sin pares clave-valor:

```python
# Crear sets
numeros = {1, 2, 3, 4, 5}
letras = set("abracadabra")  # Elimina duplicados
print(letras)  # {"a", "b", "r", "c", "d"} (orden puede variar)

# Set vacío (NO usar {}, eso crea un dict vacío)
vacio = set()

# Eliminar duplicados de una lista
lista_con_duplicados = [1, 2, 2, 3, 3, 3, 4, 4, 4, 4]
unicos = list(set(lista_con_duplicados))
print(unicos)  # [1, 2, 3, 4]
```

### Métodos de Sets

```python
colores = {"rojo", "verde", "azul"}

# Agregar elementos
colores.add("amarillo")
print(colores)  # {"rojo", "verde", "azul", "amarillo"}

# Agregar un elemento que ya existe (no hace nada)
colores.add("rojo")
print(colores)  # Sin cambios, "rojo" ya estaba

# Eliminar elementos
colores.discard("verde")   # No lanza error si no existe
colores.remove("azul")     # Lanza KeyError si no existe
elemento = colores.pop()   # Elimina y devuelve un elemento arbitrario

# Verificar pertenencia (muy rápido: O(1))
print("rojo" in colores)   # True o False según el estado actual
```

### Operaciones de Conjuntos

Los sets implementan las operaciones matemáticas de conjuntos:

```python
frontend = {"HTML", "CSS", "JavaScript", "TypeScript"}
backend = {"Python", "JavaScript", "SQL", "Go"}

# Unión: elementos en A O en B (o ambos)
todos = frontend | backend
# o: frontend.union(backend)
print(todos)
# {"HTML", "CSS", "JavaScript", "TypeScript", "Python", "SQL", "Go"}

# Intersección: elementos en A Y en B
comunes = frontend & backend
# o: frontend.intersection(backend)
print(comunes)  # {"JavaScript"}

# Diferencia: elementos en A pero NO en B
solo_front = frontend - backend
# o: frontend.difference(backend)
print(solo_front)  # {"HTML", "CSS", "TypeScript"}

solo_back = backend - frontend
print(solo_back)  # {"Python", "SQL", "Go"}

# Diferencia simétrica: elementos en A o en B, pero NO en ambos
exclusivos = frontend ^ backend
# o: frontend.symmetric_difference(backend)
print(exclusivos)  # {"HTML", "CSS", "TypeScript", "Python", "SQL", "Go"}

# Subconjunto y superconjunto
web_basico = {"HTML", "CSS"}
print(web_basico <= frontend)   # True (es subconjunto)
print(web_basico.issubset(frontend))  # True
print(frontend >= web_basico)   # True (es superconjunto)
print(frontend.issuperset(web_basico))  # True

# Conjuntos disjuntos (sin elementos en común)
frutas = {"manzana", "banana"}
verduras = {"lechuga", "tomate"}
print(frutas.isdisjoint(verduras))  # True
```

### Set Comprehensions

```python
# Crear pares del 0 al 20
pares = {x for x in range(21) if x % 2 == 0}
print(pares)  # {0, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20}

# Longitudes únicas de palabras
palabras = ["hola", "mundo", "python", "es", "genial", "hola"]
longitudes = {len(p) for p in palabras}
print(longitudes)  # {2, 4, 5, 6}
```

## frozenset: Sets Inmutables

Un `frozenset` es un set que no se puede modificar. Esto lo hace **hashable**, por lo que puede usarse como clave de diccionario o como elemento de otro set:

```python
# Crear un frozenset
inmutable = frozenset([1, 2, 3, 4, 5])

# Las operaciones de lectura funcionan
print(3 in inmutable)  # True
print(inmutable | {6, 7})  # frozenset({1, 2, 3, 4, 5, 6, 7})

# Pero NO se puede modificar
# inmutable.add(6)  # AttributeError

# Uso como clave de diccionario
permisos = {
    frozenset({"leer", "escribir"}): "Editor",
    frozenset({"leer"}): "Lector",
    frozenset({"leer", "escribir", "admin"}): "Administrador"
}

mis_permisos = frozenset({"leer", "escribir"})
print(permisos[mis_permisos])  # "Editor"
```

## Caso Práctico: Análisis de Texto

Combinemos diccionarios y sets en un ejemplo real:

```python
texto = """Python es un lenguaje de programación. Python es versátil.
La programación en Python es divertida."""

# Normalizar y separar en palabras
palabras = texto.lower().replace(".", "").split()

# Palabras únicas (set)
unicas = set(palabras)
print(f"Total de palabras: {len(palabras)}")
print(f"Palabras únicas: {len(unicas)}")

# Frecuencia de cada palabra (diccionario)
frecuencia = {}
for palabra in palabras:
    frecuencia[palabra] = frecuencia.get(palabra, 0) + 1

# Ordenar por frecuencia (descendente)
mas_frecuentes = sorted(frecuencia.items(), key=lambda x: x[1], reverse=True)
print("\nPalabras más frecuentes:")
for palabra, conteo in mas_frecuentes[:5]:
    print(f"  '{palabra}': {conteo} veces")
```

## Ejercicio Práctico

Crea un programa llamado `agenda_contactos.py` que:

1. Use un diccionario para almacenar contactos: `{nombre: {"telefono": ..., "email": ..., "tags": set()}}`.
2. Permita agregar contactos con nombre, teléfono, email y etiquetas (tags).
3. Permita buscar contactos por nombre o por etiqueta.
4. Muestre qué etiquetas son comunes entre dos contactos (intersección de sets).
5. Muestre todas las etiquetas únicas en la agenda (unión de sets).

Ejemplo:

```
Contacto agregado: Ana (tags: amiga, trabajo)
Contacto agregado: Luis (tags: trabajo, universidad)

Tags comunes entre Ana y Luis: {trabajo}
Todos los tags: {amiga, trabajo, universidad}
```

## Resumen

- Los **diccionarios** (`dict`) almacenan pares clave-valor con acceso rápido por clave.
- Métodos principales: `get()`, `setdefault()`, `update()`, `keys()`, `values()`, `items()`, `pop()`.
- Las **dict comprehensions** (`{k: v for k, v in ...}`) crean diccionarios de forma concisa.
- Los **sets** (`set`) almacenan elementos únicos y permiten operaciones de conjuntos.
- Operaciones de conjuntos: unión (`|`), intersección (`&`), diferencia (`-`), diferencia simétrica (`^`).
- `frozenset` es un set inmutable que puede usarse como clave de diccionario.
- Tanto diccionarios como sets usan tablas hash para búsquedas rápidas en tiempo $O(1)$.
