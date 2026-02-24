---
title: "Introducción a Python"
slug: "introduccion-python"
description: "Descubre qué es Python, su historia, cómo instalarlo y escribe tu primer programa. Aprende los fundamentos del lenguaje más popular del mundo."
---

# Introducción a Python

Python es uno de los lenguajes de programación más populares y versátiles del mundo. Diseñado con la filosofía de que el código debe ser legible y elegante, Python se ha convertido en la herramienta preferida para desarrollo web, ciencia de datos, inteligencia artificial, automatización y mucho más. En esta lección daremos los primeros pasos para entender qué es Python, cómo instalarlo y cómo escribir nuestro primer programa.

## Breve Historia de Python

Python fue creado por **Guido van Rossum** a finales de los años 80 y su primera versión pública (0.9.0) se lanzó en 1991. El nombre no proviene de la serpiente, sino del grupo de comedia británico **Monty Python**, del cual Guido era fanático.

A lo largo de los años, Python ha evolucionado significativamente:

- **Python 1.0 (1994):** primera versión estable con características funcionales como `lambda`, `map` y `filter`.
- **Python 2.0 (2000):** introdujo list comprehensions y recolección de basura por ciclos.
- **Python 3.0 (2008):** una revisión mayor que rompió compatibilidad con Python 2, pero mejoró la consistencia del lenguaje.
- **Python 3.10+ (2021):** estructuras modernas como `match-case` (pattern matching).

Hoy en día, Python 2 ya no recibe soporte y todo desarrollo nuevo debe realizarse en **Python 3**.

## ¿Por Qué Aprender Python?

Existen muchas razones para elegir Python como tu lenguaje de programación:

1. **Sintaxis clara y legible:** Python usa indentación en lugar de llaves, lo que fuerza un código limpio.
2. **Versatilidad:** sirve para web, datos, IA, scripting, videojuegos, IoT y más.
3. **Gran comunidad:** millones de desarrolladores y miles de bibliotecas disponibles.
4. **Alta demanda laboral:** Python es uno de los lenguajes más solicitados en el mercado.
5. **Curva de aprendizaje suave:** ideal para principiantes sin sacrificar potencia.

## Instalación de Python

### En Linux (Ubuntu/Debian)

Python suele venir preinstalado. Verifica con:

```bash
python3 --version
```

Si necesitas instalarlo:

```bash
sudo apt update
sudo apt install python3 python3-pip
```

### En macOS

Puedes usar Homebrew:

```bash
brew install python3
```

### En Windows

Descarga el instalador desde [python.org](https://www.python.org/downloads/). Asegúrate de marcar la opción **"Add Python to PATH"** durante la instalación.

## El REPL: Tu Laboratorio Interactivo

El **REPL** (Read-Eval-Print Loop) es un entorno interactivo donde puedes escribir código Python y ver los resultados inmediatamente. Para abrirlo, escribe en tu terminal:

```bash
python3
```

Verás algo como:

```
Python 3.12.0 (main, Oct  2 2023, 00:00:00)
>>> 
```

Ahora puedes escribir expresiones directamente:

```python
>>> 2 + 3
5
>>> "Hola" + " Mundo"
'Hola Mundo'
>>> 10 / 3
3.3333333333333335
```

Para salir del REPL, escribe `exit()` o presiona `Ctrl + D`.

## Tu Primer Script en Python

Crea un archivo llamado `hola.py` con tu editor de texto favorito:

```python
# Mi primer programa en Python
# Archivo: hola.py

print("¡Hola, mundo!")
print("Bienvenido a Python")
```

Ejecútalo desde la terminal:

```bash
python3 hola.py
```

Salida:

```
¡Hola, mundo!
Bienvenido a Python
```

## La Función print()

`print()` es la función más básica para mostrar información en la consola. Acepta múltiples argumentos y tiene parámetros útiles:

```python
# Imprimir varios valores separados por espacio (por defecto)
print("Python", "es", "genial")
# Salida: Python es genial

# Cambiar el separador
print("2025", "02", "17", sep="-")
# Salida: 2025-02-17

# Cambiar el final de línea (por defecto es \n)
print("Cargando", end="...")
print("Listo")
# Salida: Cargando...Listo

# Imprimir números y mezclar tipos
print("Tengo", 25, "años y mido", 1.75, "metros")
# Salida: Tengo 25 años y mido 1.75 metros
```

## Comentarios

Los comentarios son texto que Python ignora completamente. Sirven para documentar tu código:

```python
# Esto es un comentario de una línea

nombre = "Ana"  # Comentario al final de una línea

# Python NO tiene comentarios multilínea con /* */
# pero puedes usar varias líneas con #
# como estas tres líneas

"""
Esto es un string multilínea.
Técnicamente no es un comentario, pero se usa
a veces como documentación (docstring).
"""
```

## La Indentación: El Alma de Python

A diferencia de otros lenguajes que usan llaves `{}` para delimitar bloques de código, Python utiliza la **indentación** (espacios al inicio de línea). Esto no es opcional, es parte de la sintaxis:

```python
# Correcto: indentación consistente con 4 espacios
edad = 18
if edad >= 18:
    print("Eres mayor de edad")
    print("Puedes votar")
else:
    print("Eres menor de edad")

# INCORRECTO: esto genera un IndentationError
# if edad >= 18:
# print("Error")  # Falta la indentación
```

La convención estándar es usar **4 espacios** por nivel de indentación. Evita mezclar tabuladores con espacios.

## El Zen de Python

Python tiene una filosofía de diseño resumida en **El Zen de Python**, escrito por Tim Peters. Puedes verlo ejecutando en el REPL:

```python
>>> import this
```

Algunos principios clave:

- **Bello es mejor que feo.** Escribe código estético.
- **Explícito es mejor que implícito.** Sé claro en tus intenciones.
- **Simple es mejor que complejo.** No sobrediseñes.
- **La legibilidad cuenta.** Tu código será leído más veces de las que será escrito.
- **Los errores nunca deberían pasar en silencio.** Maneja las excepciones.
- **Debería haber una, y preferiblemente solo una, manera obvia de hacerlo.**

Estos principios guían la forma en que la comunidad Python escribe y diseña software.

## Un Programa Más Completo

Combinemos lo aprendido en un pequeño programa:

```python
# programa: saludo_personalizado.py
# Descripción: Un programa que saluda al usuario

# Solicitar el nombre al usuario
nombre = input("¿Cómo te llamas? ")

# Mostrar un saludo personalizado
print("=" * 40)
print("¡Hola,", nombre + "!")
print("Bienvenido al curso de Python")
print("=" * 40)

# Mostrar información del sistema
import sys
print("Versión de Python:", sys.version)
```

## Ejercicio Práctico

Crea un archivo llamado `tarjeta.py` que haga lo siguiente:

1. Solicite al usuario su nombre, edad y ciudad usando `input()`.
2. Muestre una tarjeta de presentación formateada en consola.

Ejemplo de salida esperada:

```
+=============================+
|   TARJETA DE PRESENTACIÓN   |
+=============================+
| Nombre: María               |
| Edad: 28 años               |
| Ciudad: Madrid              |
+=============================+
```

**Pista:** Usa `print()` con multiplicación de strings (`"=" * 30`) para crear las líneas decorativas.

## Resumen

- **Python** es un lenguaje de alto nivel, legible y versátil creado por Guido van Rossum.
- Se instala fácilmente en cualquier sistema operativo y hoy se usa exclusivamente **Python 3**.
- El **REPL** permite experimentar con código de forma interactiva.
- `print()` muestra información en la consola y acepta parámetros como `sep` y `end`.
- Los **comentarios** (`#`) documentan el código y Python los ignora.
- La **indentación** (4 espacios) es obligatoria y define la estructura del código.
- El **Zen de Python** resume la filosofía del lenguaje: simplicidad, legibilidad y claridad.
