---
title: "Buenas Prácticas y Pythonic Code"
slug: "python-buenas-practicas"
description: "Aprende las convenciones, patrones y herramientas que hacen tu código Python profesional, legible y mantenible."
---

# Buenas Prácticas y Pythonic Code

Escribir código que funcione no es suficiente. El código **Pythonic** es legible, elegante y sigue las convenciones de la comunidad. En esta lección exploraremos las guías de estilo, los patrones idiomáticos de Python y las herramientas que te ayudarán a mantener un código profesional.

## PEP 8 — Guía de Estilo

PEP 8 es la guía de estilo oficial de Python. Algunas reglas clave:

```python
# ✅ Nombres correctos según PEP 8
mi_variable = 42                    # snake_case para variables y funciones
CONSTANTE_GLOBAL = 3.14159          # MAYÚSCULAS para constantes
class MiClase:                      # PascalCase para clases
    pass
_variable_privada = "interna"       # Prefijo _ para privado por convención

# ✅ Indentación: 4 espacios (nunca tabs)
if True:
    print("Indentado con 4 espacios")

# ✅ Longitud de línea: máximo 79-88 caracteres
# Se puede partir con paréntesis implícitos
resultado = (primera_variable
             + segunda_variable
             - tercera_variable)

# ✅ Espacios alrededor de operadores
x = 1 + 2
y = x * 3

# ❌ Evitar espacios innecesarios
# spam( jamón[ 1 ], { huevos: 2 } )  ← incorrecto
spam(jamón[1], {huevos: 2})           # ← correcto

# ✅ Imports en orden: stdlib, terceros, locales
import os                     # 1. Biblioteca estándar
import sys
from pathlib import Path

import requests               # 2. Paquetes de terceros
import pandas as pd

from mi_proyecto import utils  # 3. Imports locales
```

## PEP 20 — El Zen de Python

Los principios filosóficos de Python, accesibles con `import this`:

```python
import this

# Los más importantes en la práctica:
# 1. "Explícito es mejor que implícito"
# 2. "Simple es mejor que complejo"
# 3. "La legibilidad cuenta"
# 4. "Debería haber una, y preferiblemente solo una, manera obvia de hacerlo"
# 5. "Los errores nunca deben pasar en silencio"

# ✅ Explícito
def calcular_precio(base, iva=0.21):
    return base * (1 + iva)

# ❌ Implícito — ¿qué hace 1.21?
def calcular_precio(b):
    return b * 1.21
```

## EAFP vs LBYL

Python favorece **EAFP** (Es más fácil pedir perdón que permiso) sobre **LBYL** (Mira antes de saltar):

```python
# ❌ LBYL — Verificar antes de actuar (estilo C/Java)
def obtener_valor_lbyl(diccionario, clave):
    if clave in diccionario:
        valor = diccionario[clave]
        if isinstance(valor, str):
            return valor.upper()
    return None

# ✅ EAFP — Intentar y manejar excepciones (Pythonic)
def obtener_valor_eafp(diccionario, clave):
    try:
        return diccionario[clave].upper()
    except (KeyError, AttributeError):
        return None

# Otro ejemplo: abrir archivo
# ❌ LBYL
import os
if os.path.exists("archivo.txt"):
    with open("archivo.txt") as f:
        datos = f.read()

# ✅ EAFP
try:
    with open("archivo.txt") as f:
        datos = f.read()
except FileNotFoundError:
    datos = ""
```

## Walrus Operator `:=` (Python 3.8+)

El operador morsa permite asignar y evaluar en una misma expresión:

```python
# ❌ Sin walrus — lectura repetida
linea = input("Ingresa texto (vacío para salir): ")
while linea:
    print(f"Leído: {linea}")
    linea = input("Ingresa texto (vacío para salir): ")

# ✅ Con walrus — más conciso
while (linea := input("Ingresa texto (vacío para salir): ")):
    print(f"Leído: {linea}")

# Útil en comprehensions con filtro
import re
textos = ["precio: $100", "nota: hola", "precio: $250", "info: dato"]
precios = [
    int(m.group(1))
    for texto in textos
    if (m := re.search(r"\$(\d+)", texto))
]
print(precios)  # [100, 250]

# Útil al leer archivos en bloques
with open("datos.bin", "rb") as f:
    while (bloque := f.read(4096)):
        procesar(bloque)
```

## Comprehensions vs Loops

Las comprehensions son más Pythonic para transformar y filtrar datos:

```python
# ✅ List comprehension (preferido para transformaciones simples)
cuadrados = [x ** 2 for x in range(10)]

# ❌ Equivalente con loop (más verboso)
cuadrados = []
for x in range(10):
    cuadrados.append(x ** 2)

# ✅ Dict comprehension
precios_usd = {"laptop": 999, "mouse": 25, "teclado": 75}
precios_eur = {k: v * 0.92 for k, v in precios_usd.items()}

# ✅ Set comprehension
unicos = {palabra.lower() for palabra in ["Hola", "hola", "HOLA", "Mundo"]}
# {'hola', 'mundo'}

# ⚠️ Pero NO abuses — si es complejo, usa un loop
# ❌ Ilegible
resultado = [
    transformar(x)
    for grupo in datos
    for x in grupo.items
    if x.activo and x.valor > 0
    if x.tipo in tipos_validos
]

# ✅ Más legible como función con loop
def filtrar_y_transformar(datos, tipos_validos):
    resultado = []
    for grupo in datos:
        for x in grupo.items:
            if x.activo and x.valor > 0 and x.tipo in tipos_validos:
                resultado.append(transformar(x))
    return resultado
```

## F-strings Avanzados

```python
nombre = "Ana"
precio = 1234.5678
fecha = "2026-02-23"

# Formateo básico
print(f"Hola, {nombre}")

# Expresiones dentro de f-strings
print(f"2 + 3 = {2 + 3}")
print(f"{'Hola'.upper()}")

# Formato numérico
print(f"Precio: ${precio:.2f}")        # $1234.57
print(f"Precio: ${precio:,.2f}")       # $1,234.57
print(f"Entero: {42:05d}")             # 00042
print(f"Porcentaje: {0.856:.1%}")      # 85.6%
print(f"Binario: {255:08b}")           # 11111111
print(f"Hex: {255:#06x}")             # 0x00ff

# Alineación
print(f"{'Izquierda':<20}|")     # Izquierda           |
print(f"{'Derecha':>20}|")       #             Derecha|
print(f"{'Centro':^20}|")        #        Centro       |

# Debug con = (Python 3.8+)
x = 42
print(f"{x = }")          # x = 42
print(f"{x * 2 = }")      # x * 2 = 84
print(f"{nombre = !r}")    # nombre = 'Ana'

# Multiline f-strings
mensaje = (
    f"Usuario: {nombre}\n"
    f"Precio: ${precio:.2f}\n"
    f"Fecha: {fecha}"
)
```

## Logging Profesional

```python
import logging

# Configuración básica
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S",
)

logger = logging.getLogger(__name__)

# Niveles de logging
logger.debug("Mensaje de depuración")      # No se muestra con INFO
logger.info("Aplicación iniciada")
logger.warning("Disco casi lleno")
logger.error("No se pudo conectar a la BD")
logger.critical("Sistema caído")

# ✅ Usar lazy formatting (más eficiente)
usuario_id = 42
logger.info("Usuario %d conectado", usuario_id)

# ❌ Evitar f-strings en logging (se evalúan siempre)
logger.debug(f"Datos: {datos_enormes}")  # Se evalúa aunque no se muestre

# Logging con excepciones
try:
    resultado = 1 / 0
except ZeroDivisionError:
    logger.exception("Error en el cálculo")  # Incluye traceback
```

### Logging Estructurado con `structlog`

```python
# pip install structlog
import structlog

logger = structlog.get_logger()

# Logs estructurados con contexto
logger.info("usuario_creado", nombre="Ana", email="ana@test.com")
# 2026-02-23 10:30:00 [info] usuario_creado nombre=Ana email=ana@test.com

# Enlazar contexto permanente
log = logger.bind(request_id="abc-123")
log.info("procesando_pedido", pedido_id=456)
log.info("pedido_completado", total=99.99)
```

## Linters y Formateadores

### Ruff — Linter ultrarrápido

```bash
# Instalar
pip install ruff

# Verificar código
ruff check mi_modulo.py

# Corregir automáticamente
ruff check --fix mi_modulo.py

# Formatear código (reemplaza black)
ruff format mi_modulo.py
```

### Black — Formateador inflexible

```bash
pip install black

# Formatear archivo
black mi_modulo.py

# Verificar sin modificar
black --check mi_modulo.py
```

### isort — Ordenar imports

```bash
pip install isort

# Ordenar imports
isort mi_modulo.py

# Perfil compatible con black
isort --profile black mi_modulo.py
```

### Configuración centralizada en `pyproject.toml`

```toml
[tool.ruff]
line-length = 88
target-version = "py311"

[tool.ruff.lint]
select = ["E", "F", "W", "I", "N", "UP"]

[tool.black]
line-length = 88
target-version = ["py311"]

[tool.isort]
profile = "black"

[tool.mypy]
python_version = "3.11"
strict = true
```

## Estructura de Proyecto

```
mi_proyecto/
├── pyproject.toml          # Configuración del proyecto
├── README.md
├── LICENSE
├── src/
│   └── mi_paquete/
│       ├── __init__.py
│       ├── modelos.py
│       ├── servicios.py
│       └── utils.py
├── tests/
│   ├── conftest.py
│   ├── test_modelos.py
│   └── test_servicios.py
├── docs/
│   └── guia.md
└── scripts/
    └── seed_data.py
```

```toml
# pyproject.toml moderno
[project]
name = "mi-proyecto"
version = "1.0.0"
description = "Mi proyecto Python"
requires-python = ">=3.11"
dependencies = [
    "requests>=2.28",
    "pydantic>=2.0",
]

[project.optional-dependencies]
dev = [
    "pytest>=7.0",
    "ruff>=0.1",
    "mypy>=1.0",
]
```

## Ejercicio Práctico

Refactoriza el siguiente código aplicando todas las buenas prácticas aprendidas:

```python
# ❌ Código ANTES (anti-Pythonic)
import os, sys, json
from typing import *

def getData(f):
  data=[]
  file=open(f,'r')
  for l in file:
    d=json.loads(l)
    if d['active']==True:
      if d['age']>=18:
        data.append({'Name':d['name'],'Age':d['age']})
  file.close()
  return data

# ✅ Código DESPUÉS (Pythonic)
import json
from pathlib import Path
from typing import Optional

def obtener_usuarios_activos(
    ruta: str | Path,
    edad_minima: int = 18,
) -> list[dict[str, str | int]]:
    """Lee usuarios activos mayores de edad desde un archivo JSON Lines."""
    ruta = Path(ruta)
    usuarios = []

    with ruta.open("r", encoding="utf-8") as f:
        for linea in f:
            try:
                dato = json.loads(linea)
            except json.JSONDecodeError:
                continue

            if dato.get("active") and dato.get("age", 0) >= edad_minima:
                usuarios.append({
                    "nombre": dato["name"],
                    "edad": dato["age"],
                })

    return usuarios
```

**Reto:** Toma un script propio o de un proyecto open source y aplica las buenas prácticas de esta lección: PEP 8, EAFP, comprehensions, f-strings, type hints y logging.

## Resumen

- **PEP 8** establece las convenciones de estilo: `snake_case`, 4 espacios, 79-88 caracteres por línea.
- **PEP 20** (Zen de Python) promueve la simplicidad, la legibilidad y lo explícito.
- **EAFP** (try/except) es más Pythonic que **LBYL** (if/else antes de actuar).
- El **walrus operator** (`:=`) asigna y evalúa en la misma expresión.
- Las **comprehensions** son preferidas sobre loops para transformaciones simples.
- Los **f-strings** ofrecen formateo potente con `:.2f`, `:,`, `=`, alineación y más.
- Usa **logging** (no `print`) en código de producción, con lazy formatting.
- **Ruff**, **Black** e **isort** mantienen tu código formateado y consistente automáticamente.
- Centraliza la configuración de herramientas en **`pyproject.toml`**.
