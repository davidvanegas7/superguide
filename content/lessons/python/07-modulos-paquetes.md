---
title: "Módulos y Paquetes"
slug: "modulos-paquetes"
description: "Aprende a organizar tu código con módulos y paquetes, gestionar dependencias con pip y trabajar con entornos virtuales en Python."
---

# Módulos y Paquetes

A medida que un proyecto crece, mantener todo el código en un único archivo se vuelve insostenible. Python resuelve esto con **módulos** (archivos `.py` individuales) y **paquetes** (directorios con módulos). En esta lección aprenderás a organizar tu código de forma profesional, usar la biblioteca estándar y gestionar dependencias externas.

## ¿Qué es un Módulo?

Un módulo es simplemente un archivo `.py` que contiene definiciones de funciones, clases y variables. Cualquier archivo Python puede importarse como módulo desde otro archivo.

```python
# archivo: matematicas.py
"""Módulo con funciones matemáticas útiles."""

PI = 3.14159265

def area_circulo(radio):
    """Calcula el área de un círculo."""
    return PI * radio ** 2

def area_rectangulo(base, altura):
    """Calcula el área de un rectángulo."""
    return base * altura

def factorial(n):
    """Calcula el factorial de un número."""
    if n <= 1:
        return 1
    return n * factorial(n - 1)
```

## Importar Módulos

Existen varias formas de importar un módulo:

### import completo

```python
# Importar el módulo completo
import matematicas

area = matematicas.area_circulo(5)
print(f"Área: {area}")
print(f"PI: {matematicas.PI}")
```

### from ... import

```python
# Importar elementos específicos
from matematicas import area_circulo, PI

area = area_circulo(5)  # No necesitas el prefijo
print(f"Área: {area}")
print(f"PI: {PI}")
```

### Alias con as

```python
# Renombrar al importar
import matematicas as mat

area = mat.area_circulo(5)

# También con from...import
from matematicas import area_rectangulo as area_rect
resultado = area_rect(10, 5)
```

### Importar todo (NO recomendado)

```python
# Importa todo lo público del módulo
from matematicas import *  # ⚠️ Evitar en producción

# Problemas:
# - No sabes de dónde viene cada nombre
# - Puede sobreescribir nombres existentes
# - Dificulta la lectura del código
```

## La Biblioteca Estándar

Python incluye una extensa biblioteca estándar con módulos para todo tipo de tareas:

```python
# math: funciones matemáticas
import math
print(math.sqrt(16))     # 4.0
print(math.ceil(3.2))    # 4
print(math.floor(3.8))   # 3
print(math.log(100, 10)) # 2.0
print(math.pi)           # 3.141592653589793

# random: generación de números aleatorios
import random
print(random.randint(1, 100))       # Entero aleatorio entre 1 y 100
print(random.choice(["a", "b", "c"]))  # Elemento aleatorio
random.shuffle([1, 2, 3, 4, 5])    # Mezclar lista in-place
print(random.sample(range(100), 5)) # 5 elementos únicos aleatorios

# datetime: manejo de fechas y horas
from datetime import datetime, timedelta
ahora = datetime.now()
print(ahora.strftime("%d/%m/%Y %H:%M"))  # "23/02/2026 10:30"
manana = ahora + timedelta(days=1)
print(f"Mañana: {manana.strftime('%A %d de %B')}")

# os y pathlib: sistema de archivos
from pathlib import Path
ruta = Path("mi_proyecto")
print(ruta.exists())     # ¿Existe?
print(ruta.is_dir())     # ¿Es directorio?

import os
print(os.getcwd())       # Directorio actual
print(os.listdir("."))   # Listar archivos

# json: trabajar con JSON
import json
datos = {"nombre": "Ana", "edad": 25, "lenguajes": ["Python", "JS"]}

# Python dict → JSON string
json_str = json.dumps(datos, indent=2, ensure_ascii=False)
print(json_str)

# JSON string → Python dict
datos_recuperados = json.loads(json_str)
print(datos_recuperados["nombre"])  # "Ana"

# collections: estructuras de datos adicionales
from collections import Counter, defaultdict

# Counter: contar elementos
palabras = ["python", "java", "python", "go", "python", "java"]
conteo = Counter(palabras)
print(conteo)                    # Counter({"python": 3, "java": 2, "go": 1})
print(conteo.most_common(2))     # [("python", 3), ("java", 2)]

# defaultdict: diccionario con valor por defecto
grupos = defaultdict(list)
for nombre, grupo in [("Ana", "A"), ("Luis", "B"), ("Marta", "A")]:
    grupos[grupo].append(nombre)
print(dict(grupos))  # {"A": ["Ana", "Marta"], "B": ["Luis"]}
```

## __name__ == '__main__'

Cuando ejecutas un archivo Python directamente, la variable especial `__name__` vale `'__main__'`. Cuando se importa como módulo, `__name__` vale el nombre del módulo:

```python
# archivo: utilidades.py

def saludar(nombre):
    return f"¡Hola, {nombre}!"

def despedir(nombre):
    return f"¡Adiós, {nombre}!"

# Este bloque SOLO se ejecuta si corres el archivo directamente
# NO se ejecuta cuando se importa como módulo
if __name__ == '__main__':
    # Código de prueba / demostración
    print(saludar("Carlos"))
    print(despedir("Carlos"))
    print("Todo funciona correctamente ✓")
```

```bash
# Ejecutar directamente → __name__ == '__main__'
python3 utilidades.py
# ¡Hola, Carlos!
# ¡Adiós, Carlos!
# Todo funciona correctamente ✓
```

```python
# Importar desde otro archivo → __name__ == 'utilidades'
import utilidades
print(utilidades.saludar("Ana"))  # ¡Hola, Ana!
# El bloque if __name__ NO se ejecuta
```

Este patrón es fundamental en Python profesional. Permite que un archivo sirva como módulo importable y como script ejecutable.

## Crear Tus Propios Paquetes

Un **paquete** es un directorio que contiene módulos Python y un archivo `__init__.py`:

```
mi_proyecto/
├── main.py
└── mi_paquete/
    ├── __init__.py      # Hace que el directorio sea un paquete
    ├── calculos.py
    ├── validaciones.py
    └── utilidades/       # Sub-paquete
        ├── __init__.py
        └── formato.py
```

```python
# mi_paquete/__init__.py
"""Mi paquete de utilidades."""

# Puedes definir qué se exporta con __all__
__all__ = ["calculos", "validaciones"]

# O importar elementos para acceso directo
from .calculos import sumar, restar
```

```python
# mi_paquete/calculos.py
def sumar(a, b):
    return a + b

def restar(a, b):
    return a - b
```

```python
# mi_paquete/validaciones.py
def es_email_valido(email):
    return "@" in email and "." in email.split("@")[1]

def es_positivo(numero):
    return numero > 0
```

```python
# main.py
# Importar desde el paquete
from mi_paquete import sumar, restar  # Gracias al __init__.py
print(sumar(3, 5))  # 8

# Importar un módulo específico del paquete
from mi_paquete.validaciones import es_email_valido
print(es_email_valido("ana@mail.com"))  # True

# Importar sub-paquete
from mi_paquete.utilidades.formato import formatear_moneda
```

## pip: Gestor de Paquetes

**pip** es la herramienta estándar para instalar paquetes de terceros desde [PyPI](https://pypi.org/) (Python Package Index):

```bash
# Instalar un paquete
pip install requests

# Instalar una versión específica
pip install requests==2.31.0

# Instalar versión mínima
pip install "requests>=2.28"

# Actualizar un paquete
pip install --upgrade requests

# Desinstalar
pip uninstall requests

# Ver paquetes instalados
pip list

# Ver información de un paquete
pip show requests

# Buscar paquetes (en la terminal)
pip search "web framework"  # Nota: puede estar deshabilitado en PyPI
```

## Entornos Virtuales (venv)

Un entorno virtual es un directorio aislado con su propia instalación de Python y paquetes. Esto evita conflictos entre dependencias de diferentes proyectos.

```bash
# Crear un entorno virtual
python3 -m venv mi_entorno

# Activar el entorno virtual
# En Linux/macOS:
source mi_entorno/bin/activate

# En Windows:
# mi_entorno\Scripts\activate

# Tu prompt cambiará:
# (mi_entorno) usuario@maquina:~$

# Ahora pip instala SOLO dentro del entorno
pip install requests flask

# Ver lo instalado en este entorno
pip list

# Desactivar el entorno
deactivate
```

### requirements.txt

El archivo `requirements.txt` lista las dependencias de un proyecto:

```bash
# Generar requirements.txt con las versiones actuales
pip freeze > requirements.txt
```

```
# requirements.txt
requests==2.31.0
flask==3.0.0
sqlalchemy==2.0.23
python-dotenv==1.0.0
```

```bash
# Instalar todas las dependencias de un proyecto
pip install -r requirements.txt
```

### Flujo de Trabajo Profesional

```bash
# 1. Crear directorio del proyecto
mkdir mi_proyecto && cd mi_proyecto

# 2. Crear entorno virtual
python3 -m venv venv

# 3. Activar
source venv/bin/activate

# 4. Instalar dependencias
pip install requests flask

# 5. Guardar dependencias
pip freeze > requirements.txt

# 6. Añadir venv al .gitignore
echo "venv/" >> .gitignore

# 7. Cuando otro desarrollador clone el proyecto:
git clone <repo>
cd mi_proyecto
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
```

## Importaciones Relativas vs Absolutas

```python
# Absolutas (recomendadas): ruta completa desde la raíz del proyecto
from mi_paquete.calculos import sumar
from mi_paquete.utilidades.formato import formatear

# Relativas: ruta relativa al archivo actual (solo dentro de paquetes)
# Desde mi_paquete/validaciones.py:
from .calculos import sumar           # Mismo nivel (.)
from .utilidades.formato import formatear  # Sub-paquete
from ..otro_paquete import algo       # Nivel superior (..)
```

## Ejercicio Práctico

Crea un mini-paquete llamado `utils/` con la siguiente estructura:

```
utils/
├── __init__.py
├── texto.py       # funciones: contar_vocales(), invertir(), es_palindromo()
├── numeros.py     # funciones: es_primo(), fibonacci(n), mcd(a, b)
└── archivos.py    # funciones: leer_json(ruta), escribir_json(ruta, datos)
```

Luego crea un `main.py` que importe y use las funciones de cada módulo:

```python
from utils.texto import es_palindromo
from utils.numeros import fibonacci, es_primo
from utils.archivos import escribir_json, leer_json

# Probar funciones
print(es_palindromo("reconocer"))  # True
print(fibonacci(10))  # [0, 1, 1, 2, 3, 5, 8, 13, 21, 34]
print(es_primo(17))  # True

escribir_json("datos.json", {"curso": "Python", "leccion": 7})
datos = leer_json("datos.json")
print(datos)
```

## Resumen

- Un **módulo** es un archivo `.py`; un **paquete** es un directorio con `__init__.py`.
- Formas de importar: `import mod`, `from mod import func`, `import mod as alias`.
- `if __name__ == '__main__':` diferencia entre ejecución directa e importación.
- La **biblioteca estándar** incluye `math`, `random`, `datetime`, `json`, `os`, `collections` y muchos más.
- **pip** instala paquetes desde PyPI; **venv** crea entornos virtuales aislados.
- **requirements.txt** documenta y permite reproducir las dependencias de un proyecto.
- Usa **importaciones absolutas** en proyectos y **relativas** solo dentro de paquetes cuando sea necesario.
