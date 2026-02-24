---
title: "Archivos y E/S"
slug: "python-archivos-io"
description: "Aprende a leer, escribir y manipular archivos en Python usando open, pathlib, y los módulos csv, json y pickle."
---

# Archivos y E/S

El manejo de archivos es una habilidad esencial en cualquier lenguaje de programación. Python ofrece herramientas potentes y sencillas para leer, escribir y manipular archivos de texto, binarios y en formatos estructurados como CSV y JSON. En esta lección exploraremos desde las operaciones básicas hasta el uso avanzado de `pathlib` y módulos especializados.

## Abrir y Cerrar Archivos con `open()`

La función `open()` es la puerta de entrada al sistema de archivos:

```python
# Abrir un archivo para lectura
archivo = open("datos.txt", "r", encoding="utf-8")
contenido = archivo.read()
archivo.close()  # ¡Siempre cerrar el archivo!

print(contenido)
```

### Modos de Apertura

| Modo | Descripción |
|------|-------------|
| `"r"` | Lectura (por defecto). Error si no existe. |
| `"w"` | Escritura. Crea el archivo o **sobrescribe** si existe. |
| `"a"` | Añadir al final. Crea el archivo si no existe. |
| `"x"` | Creación exclusiva. Error si el archivo ya existe. |
| `"b"` | Modo binario (se combina: `"rb"`, `"wb"`). |
| `"t"` | Modo texto (por defecto, se combina: `"rt"`). |
| `"+"` | Lectura y escritura (se combina: `"r+"`, `"w+"`). |

## El Administrador de Contexto `with`

La forma recomendada de trabajar con archivos es usando `with`, que garantiza el cierre automático del archivo incluso si ocurre un error:

```python
# Lectura segura con with
with open("datos.txt", "r", encoding="utf-8") as f:
    contenido = f.read()
    # El archivo se cierra automáticamente al salir del bloque

# Métodos de lectura
with open("datos.txt", "r", encoding="utf-8") as f:
    todo = f.read()             # Leer todo como string
    # f.seek(0)                 # Volver al inicio
    # linea = f.readline()     # Leer una línea
    # lineas = f.readlines()   # Lista de todas las líneas

# Lectura línea por línea (eficiente en memoria)
with open("datos.txt", "r", encoding="utf-8") as f:
    for linea in f:  # El archivo es un iterador
        print(linea.strip())
```

## Escritura de Archivos

```python
# Escribir texto (sobrescribe el archivo)
with open("salida.txt", "w", encoding="utf-8") as f:
    f.write("Primera línea\n")
    f.write("Segunda línea\n")

# Añadir al final
with open("salida.txt", "a", encoding="utf-8") as f:
    f.write("Línea añadida\n")

# Escribir múltiples líneas
lineas = ["Uno\n", "Dos\n", "Tres\n"]
with open("salida.txt", "w", encoding="utf-8") as f:
    f.writelines(lineas)

# Escribir con print
with open("reporte.txt", "w", encoding="utf-8") as f:
    print("Reporte de ventas", file=f)
    print(f"Total: ${1500.50:.2f}", file=f)
```

## Archivos Binarios

```python
# Leer archivo binario (imagen, PDF, etc.)
with open("imagen.png", "rb") as f:
    datos = f.read()
    print(f"Tamaño: {len(datos)} bytes")

# Copiar archivo binario
with open("original.png", "rb") as origen:
    with open("copia.png", "wb") as destino:
        destino.write(origen.read())

# Lectura por bloques (para archivos grandes)
TAMANO_BLOQUE = 4096
with open("archivo_grande.bin", "rb") as f:
    while bloque := f.read(TAMANO_BLOQUE):
        # Procesar bloque
        pass
```

## `pathlib`: Rutas Modernas

El módulo `pathlib` (Python 3.4+) ofrece una interfaz orientada a objetos para trabajar con rutas de archivos:

```python
from pathlib import Path

# Crear objetos Path
ruta = Path("documentos/reporte.txt")
home = Path.home()                  # /home/usuario
actual = Path.cwd()                 # Directorio actual

# Componentes de la ruta
print(ruta.name)       # "reporte.txt"
print(ruta.stem)       # "reporte"
print(ruta.suffix)     # ".txt"
print(ruta.parent)     # "documentos"
print(ruta.resolve())  # Ruta absoluta

# Construir rutas con /
proyecto = Path("/home/usuario/proyecto")
config = proyecto / "config" / "settings.json"
print(config)  # /home/usuario/proyecto/config/settings.json

# Verificar existencia y tipo
print(ruta.exists())      # True/False
print(ruta.is_file())     # True/False
print(ruta.is_dir())      # True/False

# Crear directorios
Path("datos/csv").mkdir(parents=True, exist_ok=True)

# Leer y escribir directamente
ruta.write_text("Contenido del archivo", encoding="utf-8")
texto = ruta.read_text(encoding="utf-8")

datos = ruta.read_bytes()   # Lectura binaria
ruta.write_bytes(datos)     # Escritura binaria
```

### Buscar Archivos con `glob`

```python
from pathlib import Path

directorio = Path("proyecto")

# Buscar archivos Python en el directorio
for py in directorio.glob("*.py"):
    print(py)

# Búsqueda recursiva con **
for md in directorio.rglob("*.md"):
    print(md)

# Listar contenido de un directorio
for item in directorio.iterdir():
    tipo = "DIR" if item.is_dir() else "FILE"
    print(f"[{tipo}] {item.name}")
```

## `os.path`: Rutas Clásicas

Aunque `pathlib` es preferido, `os.path` sigue siendo útil:

```python
import os

# Operaciones comunes
print(os.path.exists("archivo.txt"))
print(os.path.join("datos", "csv", "ventas.csv"))
print(os.path.basename("/ruta/a/archivo.txt"))  # "archivo.txt"
print(os.path.dirname("/ruta/a/archivo.txt"))   # "/ruta/a"
print(os.path.splitext("foto.jpg"))             # ('foto', '.jpg')
print(os.path.getsize("archivo.txt"))           # Tamaño en bytes

# Listar directorio
for nombre in os.listdir("."):
    print(nombre)
```

## Módulo `csv`

Para leer y escribir archivos CSV de forma robusta:

```python
import csv

# Escribir CSV
datos = [
    ["nombre", "edad", "ciudad"],
    ["Ana", 28, "Madrid"],
    ["Carlos", 34, "Barcelona"],
    ["Lucía", 22, "Sevilla"],
]

with open("personas.csv", "w", newline="", encoding="utf-8") as f:
    escritor = csv.writer(f)
    escritor.writerows(datos)

# Leer CSV como listas
with open("personas.csv", "r", encoding="utf-8") as f:
    lector = csv.reader(f)
    encabezados = next(lector)  # Primera fila
    for fila in lector:
        print(f"{fila[0]} tiene {fila[1]} años")

# Leer CSV como diccionarios (más legible)
with open("personas.csv", "r", encoding="utf-8") as f:
    lector = csv.DictReader(f)
    for fila in lector:
        print(f"{fila['nombre']} vive en {fila['ciudad']}")

# Escribir desde diccionarios
campos = ["nombre", "edad", "ciudad"]
registros = [
    {"nombre": "Pedro", "edad": 30, "ciudad": "Valencia"},
]

with open("personas.csv", "a", newline="", encoding="utf-8") as f:
    escritor = csv.DictWriter(f, fieldnames=campos)
    # escritor.writeheader()  # Solo si el archivo es nuevo
    escritor.writerows(registros)
```

## Módulo `json`

Para trabajar con datos JSON, el formato estándar de intercambio:

```python
import json

# Python a JSON (serializar)
datos = {
    "nombre": "Ana",
    "edad": 28,
    "habilidades": ["Python", "SQL", "Docker"],
    "activo": True,
}

# Escribir a archivo
with open("usuario.json", "w", encoding="utf-8") as f:
    json.dump(datos, f, indent=2, ensure_ascii=False)

# Leer desde archivo
with open("usuario.json", "r", encoding="utf-8") as f:
    usuario = json.load(f)
    print(usuario["nombre"])  # Ana

# Convertir a/desde string
json_str = json.dumps(datos, indent=2, ensure_ascii=False)
datos_recuperados = json.loads(json_str)
```

## Módulo `pickle`

`pickle` serializa objetos Python a formato binario. Es útil para guardar estado, pero **nunca cargues pickle de fuentes no confiables** (riesgo de seguridad):

```python
import pickle

# Serializar objeto complejo
class Modelo:
    def __init__(self, nombre, pesos):
        self.nombre = nombre
        self.pesos = pesos

modelo = Modelo("red_neuronal", [0.5, 0.3, 0.8])

# Guardar con pickle
with open("modelo.pkl", "wb") as f:
    pickle.dump(modelo, f)

# Cargar con pickle
with open("modelo.pkl", "rb") as f:
    modelo_cargado = pickle.load(f)
    print(modelo_cargado.nombre)  # red_neuronal
    print(modelo_cargado.pesos)   # [0.5, 0.3, 0.8]
```

## Ejercicio Práctico

Crea un sistema de gestión de contactos que almacene datos en JSON:

```python
import json
from pathlib import Path

ARCHIVO = Path("contactos.json")


def cargar_contactos():
    """Carga contactos desde el archivo JSON."""
    if ARCHIVO.exists():
        return json.loads(ARCHIVO.read_text(encoding="utf-8"))
    return []


def guardar_contactos(contactos):
    """Guarda la lista de contactos en JSON."""
    ARCHIVO.write_text(
        json.dumps(contactos, indent=2, ensure_ascii=False),
        encoding="utf-8",
    )


def agregar_contacto(nombre, email, telefono):
    """Agrega un nuevo contacto."""
    contactos = cargar_contactos()
    contactos.append({
        "nombre": nombre,
        "email": email,
        "telefono": telefono,
    })
    guardar_contactos(contactos)
    print(f"Contacto '{nombre}' agregado.")


def buscar_contacto(termino):
    """Busca contactos por nombre o email."""
    contactos = cargar_contactos()
    resultados = [
        c for c in contactos
        if termino.lower() in c["nombre"].lower()
        or termino.lower() in c["email"].lower()
    ]
    return resultados


# Uso
agregar_contacto("Ana García", "ana@email.com", "+34 600 123 456")
print(buscar_contacto("ana"))
```

**Reto:** Añade funcionalidad para exportar los contactos a CSV e importar desde CSV.

## Resumen

- Usa `with open(...)` siempre para garantizar el cierre automático de archivos.
- Los modos `r`, `w`, `a`, `b` controlan cómo se abre el archivo.
- **`pathlib.Path`** es la forma moderna y recomendada de manejar rutas de archivos.
- `glob` y `rglob` permiten buscar archivos por patrones de forma recursiva.
- El módulo **`csv`** maneja archivos CSV con `reader`/`writer` y `DictReader`/`DictWriter`.
- **`json`** serializa datos entre Python y el formato JSON universal.
- **`pickle`** permite serializar cualquier objeto Python, pero solo úsalo con datos de confianza.
