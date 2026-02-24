---
title: "Expresiones Regulares"
slug: "python-expresiones-regulares"
description: "Domina el módulo re de Python para buscar, validar y transformar texto usando patrones de expresiones regulares."
---

# Expresiones Regulares

Las expresiones regulares (regex) son patrones de texto que permiten buscar, validar y transformar cadenas de forma potente y flexible. Python incluye el módulo `re` en su biblioteca estándar, proporcionando todas las herramientas necesarias para trabajar con regex de manera eficiente.

## Introducción al Módulo `re`

```python
import re

# Búsqueda básica
texto = "Mi número es 555-1234"
resultado = re.search(r"\d{3}-\d{4}", texto)

if resultado:
    print(resultado.group())  # 555-1234
    print(resultado.start())  # 14 (posición de inicio)
    print(resultado.end())    # 22 (posición de fin)
    print(resultado.span())   # (14, 22)
```

> **Nota:** Siempre usa cadenas crudas (`r"..."`) para patrones regex. Esto evita conflictos con las secuencias de escape de Python.

## Funciones Principales

### `re.match()` — Coincidencia al Inicio

Solo busca al **inicio** de la cadena:

```python
import re

# match busca SOLO al inicio
print(re.match(r"\d+", "123abc"))    # Match: '123'
print(re.match(r"\d+", "abc123"))    # None (no empieza con dígitos)
```

### `re.search()` — Primera Coincidencia

Busca la primera coincidencia en **cualquier posición**:

```python
import re

texto = "Hoy es 23 de febrero de 2026"
m = re.search(r"\d+", texto)
print(m.group())  # "23" — primera coincidencia
```

### `re.findall()` — Todas las Coincidencias

Devuelve una **lista** con todas las coincidencias:

```python
import re

texto = "Precios: $15.99, $23.50 y $8.00"
precios = re.findall(r"\$\d+\.\d{2}", texto)
print(precios)  # ['$15.99', '$23.50', '$8.00']

# Con grupos, devuelve tuplas
pares = re.findall(r"(\w+)=(\w+)", "color=rojo tamaño=grande")
print(pares)  # [('color', 'rojo'), ('tamaño', 'grande')]
```

### `re.finditer()` — Iterador de Coincidencias

Similar a `findall`, pero devuelve un **iterador de objetos Match**, dando acceso a más información:

```python
import re

texto = "Error en línea 42, otro error en línea 87"
for m in re.finditer(r"línea (\d+)", texto):
    print(f"Encontrado '{m.group()}' en posición {m.start()}")
    print(f"  Número de línea: {m.group(1)}")
# Encontrado 'línea 42' en posición 10
#   Número de línea: 42
# Encontrado 'línea 87' en posición 33
#   Número de línea: 87
```

### `re.sub()` — Reemplazar

Reemplaza todas las coincidencias del patrón:

```python
import re

# Reemplazo simple
texto = "Hola   mundo,   esto   tiene   espacios"
limpio = re.sub(r"\s+", " ", texto)
print(limpio)  # "Hola mundo, esto tiene espacios"

# Reemplazo con función
def censurar(match):
    return "*" * len(match.group())

texto = "Mi contraseña es abc123 y mi PIN es 9876"
censurado = re.sub(r"\b[a-zA-Z0-9]{4,}\b", censurar, texto)
print(censurado)  # "Mi ********** es ****** y mi PIN es ****"

# Reemplazo con referencia a grupos
texto = "García, Ana"
invertido = re.sub(r"(\w+), (\w+)", r"\2 \1", texto)
print(invertido)  # "Ana García"
```

### `re.split()` — Dividir por Patrón

```python
import re

# Dividir por múltiples delimitadores
texto = "uno;dos,tres:cuatro cinco"
partes = re.split(r"[;,:\s]+", texto)
print(partes)  # ['uno', 'dos', 'tres', 'cuatro', 'cinco']

# Limitar divisiones
partes = re.split(r"[;,:\s]+", texto, maxsplit=2)
print(partes)  # ['uno', 'dos', 'tres:cuatro cinco']
```

## Metacaracteres y Cuantificadores

### Metacaracteres Básicos

| Patrón | Significado |
|--------|-------------|
| `.` | Cualquier carácter excepto `\n` |
| `^` | Inicio de cadena (o línea con `MULTILINE`) |
| `$` | Fin de cadena (o línea con `MULTILINE`) |
| `\d` | Dígito `[0-9]` |
| `\D` | No dígito `[^0-9]` |
| `\w` | Alfanumérico `[a-zA-Z0-9_]` |
| `\W` | No alfanumérico |
| `\s` | Espacio en blanco `[ \t\n\r\f\v]` |
| `\S` | No espacio en blanco |
| `\b` | Límite de palabra |

### Cuantificadores

| Patrón | Significado |
|--------|-------------|
| `*` | 0 o más (greedy) |
| `+` | 1 o más (greedy) |
| `?` | 0 o 1 |
| `{n}` | Exactamente n |
| `{n,m}` | Entre n y m |
| `*?`, `+?` | Versiones no-greedy (lazy) |

```python
import re

# Greedy vs Lazy
html = "<b>texto</b> y <i>otro</i>"
print(re.findall(r"<.+>", html))   # ['<b>texto</b> y <i>otro</i>'] (greedy)
print(re.findall(r"<.+?>", html))  # ['<b>', '</b>', '<i>', '</i>'] (lazy)
```

## Grupos

### Grupos de Captura

Los paréntesis `()` crean **grupos de captura** que extraen partes del match:

```python
import re

fecha = "Fecha: 2026-02-23"
m = re.search(r"(\d{4})-(\d{2})-(\d{2})", fecha)

print(m.group())   # "2026-02-23" (match completo)
print(m.group(1))  # "2026" (primer grupo)
print(m.group(2))  # "02"
print(m.group(3))  # "23"
print(m.groups())  # ('2026', '02', '23')
```

### Grupos con Nombre (Named Groups)

Más legibles que los grupos numéricos:

```python
import re

patron = r"(?P<año>\d{4})-(?P<mes>\d{2})-(?P<dia>\d{2})"
m = re.search(patron, "Hoy es 2026-02-23")

print(m.group("año"))  # "2026"
print(m.group("mes"))  # "02"
print(m.group("dia"))  # "23"
print(m.groupdict())   # {'año': '2026', 'mes': '02', 'dia': '23'}
```

### Grupos No Capturantes

Cuando necesitas agrupar sin capturar, usa `(?:...)`:

```python
import re

# Sin capturar el grupo
urls = re.findall(r"https?://(?:www\.)?(\w+\.\w+)", 
                  "Visita https://www.python.org o http://docs.python.org")
print(urls)  # ['python.org', 'python.org']
```

## Flags (Banderas)

Las flags modifican el comportamiento del motor regex:

```python
import re

# IGNORECASE — ignorar mayúsculas/minúsculas
m = re.search(r"python", "Me gusta PYTHON", re.IGNORECASE)
print(m.group())  # "PYTHON"

# MULTILINE — ^ y $ coinciden con inicio/fin de cada línea
texto = """Primera línea
Segunda línea
Tercera línea"""
inicios = re.findall(r"^\w+", texto, re.MULTILINE)
print(inicios)  # ['Primera', 'Segunda', 'Tercera']

# DOTALL — el punto (.) también coincide con \n
texto_ml = "Inicio\nContenido\nFin"
m = re.search(r"Inicio(.+)Fin", texto_ml, re.DOTALL)
print(m.group(1))  # "\nContenido\n"

# Combinar flags con |
m = re.search(r"^inicio(.+)fin$", "INICIO\ntexto\nFIN",
              re.IGNORECASE | re.DOTALL | re.MULTILINE)

# VERBOSE — permitir comentarios y espacios (mejora legibilidad)
patron_email = re.compile(r"""
    ^                   # Inicio de la cadena
    [a-zA-Z0-9._%+-]+   # Usuario (letras, dígitos, puntos, etc.)
    @                   # Arroba
    [a-zA-Z0-9.-]+      # Dominio
    \.[a-zA-Z]{2,}      # Extensión (.com, .org, etc.)
    $                   # Fin de la cadena
""", re.VERBOSE)
```

## Compilar Patrones

Si usas un patrón repetidamente, compílalo para mejorar el rendimiento:

```python
import re

# Compilar patrón (recomendado para uso repetido)
patron_telefono = re.compile(r"\+?\d{1,3}[\s-]?\d{3}[\s-]?\d{3}[\s-]?\d{3}")

textos = [
    "Llama al +34 600 123 456",
    "Mi número: 600-789-012",
    "Contacto: +1 555 867 5309",
]

for texto in textos:
    m = patron_telefono.search(texto)
    if m:
        print(f"Teléfono encontrado: {m.group()}")
```

## Ejemplos Prácticos

### Validar un Email

```python
import re

def validar_email(email):
    patron = r"^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
    return bool(re.match(patron, email))

print(validar_email("usuario@ejemplo.com"))   # True
print(validar_email("invalido@.com"))          # False
```

### Extraer URLs de un Texto

```python
import re

def extraer_urls(texto):
    patron = r"https?://[^\s<>\"']+|www\.[^\s<>\"']+"
    return re.findall(patron, texto)

texto = """
Visita https://python.org para más info.
Documentación en https://docs.python.org/3/library/re.html
"""
print(extraer_urls(texto))
```

### Limpiar y Normalizar Texto

```python
import re

def normalizar_texto(texto):
    """Limpia y normaliza un texto."""
    texto = re.sub(r"<[^>]+>", "", texto)         # Eliminar HTML
    texto = re.sub(r"[^\w\sáéíóúñÁÉÍÓÚÑ]", "", texto)  # Solo alfanuméricos
    texto = re.sub(r"\s+", " ", texto)             # Espacios múltiples
    return texto.strip().lower()

html = "<p>¡Hola   <b>Mundo</b>!  Esto es una   prueba.</p>"
print(normalizar_texto(html))  # "hola mundo esto es una prueba"
```

## Ejercicio Práctico

Crea un validador de formularios usando expresiones regulares:

```python
import re

def validar_formulario(datos):
    """Valida un diccionario de datos de formulario."""
    errores = []
    
    # Validar nombre (solo letras y espacios, 2-50 caracteres)
    if not re.match(r"^[a-zA-ZáéíóúñÁÉÍÓÚÑ\s]{2,50}$", datos.get("nombre", "")):
        errores.append("Nombre inválido")

    # Validar email
    if not re.match(r"^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$",
                    datos.get("email", "")):
        errores.append("Email inválido")

    # Validar teléfono (formato español)
    if not re.match(r"^\+?34?\s?\d{3}\s?\d{3}\s?\d{3}$",
                    datos.get("telefono", "")):
        errores.append("Teléfono inválido")

    # Validar contraseña (mínimo 8 chars, mayúscula, minúscula, dígito)
    pwd = datos.get("password", "")
    if len(pwd) < 8:
        errores.append("Contraseña muy corta")
    elif not re.search(r"[A-Z]", pwd):
        errores.append("Contraseña sin mayúscula")
    elif not re.search(r"[a-z]", pwd):
        errores.append("Contraseña sin minúscula")
    elif not re.search(r"\d", pwd):
        errores.append("Contraseña sin dígito")

    return errores if errores else "Formulario válido"


# Prueba
datos = {
    "nombre": "Ana García",
    "email": "ana@ejemplo.com",
    "telefono": "+34 600 123 456",
    "password": "MiClave123",
}
print(validar_formulario(datos))  # "Formulario válido"
```

**Reto:** Crea un parser de logs que extraiga la fecha, nivel (INFO/ERROR/WARNING), y mensaje de líneas con formato `[2026-02-23 14:30:00] ERROR: Mensaje aquí`.

## Resumen

- El módulo **`re`** proporciona `match`, `search`, `findall`, `finditer`, `sub` y `split`.
- Los **metacaracteres** (`\d`, `\w`, `\s`, `.`, `^`, `$`) y **cuantificadores** (`*`, `+`, `?`, `{n,m}`) forman los bloques de los patrones.
- Los **grupos** `()` capturan partes del match; los **named groups** `(?P<nombre>...)` mejoran la legibilidad.
- Las **flags** como `IGNORECASE`, `MULTILINE`, `DOTALL` y `VERBOSE` modifican el comportamiento del patrón.
- **Compila** patrones que uses repetidamente con `re.compile()` para mejor rendimiento.
- Usa siempre **cadenas raw** (`r"..."`) al definir patrones regex.
