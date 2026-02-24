---
title: "Manejo de Excepciones"
slug: "manejo-excepciones"
description: "Aprende a manejar errores en Python con try/except, crear excepciones personalizadas y usar context managers para gestionar recursos."
---

# Manejo de Excepciones

Los errores son inevitables en cualquier programa. Python utiliza un sistema de **excepciones** para señalar y manejar errores de forma elegante, sin que el programa se detenga abruptamente. Además, los **context managers** permiten gestionar recursos (archivos, conexiones, etc.) de forma segura. En esta lección dominarás ambas herramientas.

## ¿Qué es una Excepción?

Una excepción es un evento que ocurre durante la ejecución de un programa y altera su flujo normal. Si no se maneja, el programa termina con un mensaje de error (traceback):

```python
# Ejemplos de excepciones comunes
# print(10 / 0)             # ZeroDivisionError
# int("abc")                # ValueError
# lista = [1, 2]; lista[5]  # IndexError
# diccionario = {}; diccionario["clave"]  # KeyError
# archivo = open("noexiste.txt")  # FileNotFoundError
# "hola" + 5                # TypeError
```

## try / except

La estructura `try/except` permite capturar y manejar excepciones:

```python
# Manejar una excepción específica
try:
    numero = int(input("Ingresa un número: "))
    resultado = 100 / numero
    print(f"Resultado: {resultado}")
except ValueError:
    print("Error: debes ingresar un número válido")
except ZeroDivisionError:
    print("Error: no puedes dividir entre cero")
```

### Capturar Múltiples Excepciones

```python
# Varias excepciones en un mismo except
try:
    datos = [10, 20, 30]
    indice = int(input("Índice: "))
    valor = datos[indice]
    resultado = 100 / valor
except (ValueError, IndexError) as e:
    print(f"Error de entrada: {e}")
except ZeroDivisionError:
    print("División entre cero")

# Capturar CUALQUIER excepción (usar con precaución)
try:
    # código riesgoso
    resultado = operacion_compleja()
except Exception as e:
    print(f"Error inesperado: {type(e).__name__}: {e}")
```

### Acceder a la Información del Error

```python
try:
    archivo = open("datos.csv")
except FileNotFoundError as error:
    print(f"Tipo: {type(error).__name__}")  # FileNotFoundError
    print(f"Mensaje: {error}")               # [Errno 2] No such file or directory
    print(f"Args: {error.args}")             # (2, 'No such file or directory')
```

## try / except / else / finally

La estructura completa de manejo de excepciones tiene cuatro bloques:

```python
try:
    # Código que puede generar excepción
    archivo = open("datos.txt", "r")
    contenido = archivo.read()
except FileNotFoundError:
    # Se ejecuta SOLO si hay excepción del tipo indicado
    print("El archivo no existe")
    contenido = None
except PermissionError:
    print("No tienes permisos para leer el archivo")
    contenido = None
else:
    # Se ejecuta SOLO si NO hubo excepción
    print(f"Archivo leído correctamente ({len(contenido)} caracteres)")
finally:
    # Se ejecuta SIEMPRE, haya o no excepción
    print("Proceso de lectura finalizado")
    # Ideal para liberar recursos
    try:
        archivo.close()
    except NameError:
        pass  # Si 'archivo' nunca se creó
```

### Ejemplo Práctico Completo

```python
def dividir_seguro(a, b):
    """División con manejo completo de excepciones."""
    try:
        resultado = a / b
    except ZeroDivisionError:
        print("⚠️ No se puede dividir entre cero")
        return None
    except TypeError as e:
        print(f"⚠️ Tipos incompatibles: {e}")
        return None
    else:
        print(f"✅ {a} / {b} = {resultado}")
        return resultado
    finally:
        print("📋 Operación de división procesada")

dividir_seguro(10, 3)    # ✅ 10 / 3 = 3.333...  → 📋
dividir_seguro(10, 0)    # ⚠️ No se puede dividir entre cero → 📋
dividir_seguro("a", 5)   # ⚠️ Tipos incompatibles: ... → 📋
```

## Jerarquía de Excepciones

Las excepciones en Python forman una jerarquía de herencia. Las más comunes son:

```
BaseException
├── SystemExit
├── KeyboardInterrupt
├── GeneratorExit
└── Exception
    ├── ArithmeticError
    │   ├── ZeroDivisionError
    │   └── OverflowError
    ├── AttributeError
    ├── EOFError
    ├── LookupError
    │   ├── IndexError
    │   └── KeyError
    ├── NameError
    ├── OSError
    │   └── FileNotFoundError
    ├── TypeError
    ├── ValueError
    └── RuntimeError
```

```python
# Capturar por clase base captura todas las subclases
try:
    datos = {}
    valor = datos["clave"]
except LookupError:
    # Captura tanto KeyError como IndexError
    print("Error de búsqueda")

# Por eso capturar Exception captura casi todo
# pero NO captura SystemExit, KeyboardInterrupt, etc.
```

## raise: Lanzar Excepciones

Puedes lanzar excepciones manualmente con `raise`:

```python
def establecer_edad(edad):
    if not isinstance(edad, int):
        raise TypeError(f"La edad debe ser un entero, no {type(edad).__name__}")
    if edad < 0:
        raise ValueError("La edad no puede ser negativa")
    if edad > 150:
        raise ValueError(f"Edad poco realista: {edad}")
    return edad

# Usar la función
try:
    establecer_edad(-5)
except ValueError as e:
    print(f"Error: {e}")  # "Error: La edad no puede ser negativa"

# Re-lanzar una excepción (propagar después de registrar)
try:
    resultado = operacion_critica()
except Exception as e:
    print(f"Error registrado: {e}")
    raise  # Re-lanza la misma excepción sin modificarla

# Encadenar excepciones con 'from'
try:
    valor = int("abc")
except ValueError as original:
    raise RuntimeError("Error al procesar configuración") from original
# RuntimeError: Error al procesar configuración
# La excepción anterior fue: ValueError: invalid literal for int()
```

## Excepciones Personalizadas

Crear tus propias excepciones te permite señalar errores específicos de tu aplicación:

```python
# Excepción base de la aplicación
class AppError(Exception):
    """Excepción base para la aplicación."""
    pass

# Excepciones específicas
class ValidacionError(AppError):
    """Error de validación de datos."""
    def __init__(self, campo, mensaje):
        self.campo = campo
        self.mensaje = mensaje
        super().__init__(f"Error en '{campo}': {mensaje}")

class SaldoInsuficienteError(AppError):
    """Error cuando no hay saldo suficiente."""
    def __init__(self, saldo_actual, monto_solicitado):
        self.saldo_actual = saldo_actual
        self.monto_solicitado = monto_solicitado
        self.diferencia = monto_solicitado - saldo_actual
        super().__init__(
            f"Saldo insuficiente: tienes ${saldo_actual:,.2f}, "
            f"necesitas ${monto_solicitado:,.2f} "
            f"(faltan ${self.diferencia:,.2f})"
        )

class UsuarioNoEncontradoError(AppError):
    """Error cuando no se encuentra un usuario."""
    def __init__(self, identificador):
        self.identificador = identificador
        super().__init__(f"Usuario no encontrado: {identificador}")

# Usar las excepciones personalizadas
class CuentaBancaria:
    def __init__(self, titular, saldo=0):
        if not titular or not titular.strip():
            raise ValidacionError("titular", "No puede estar vacío")
        self.titular = titular
        self._saldo = saldo
    
    def retirar(self, monto):
        if monto <= 0:
            raise ValidacionError("monto", "Debe ser positivo")
        if monto > self._saldo:
            raise SaldoInsuficienteError(self._saldo, monto)
        self._saldo -= monto
        return self._saldo

# Uso
try:
    cuenta = CuentaBancaria("Ana", 1000)
    cuenta.retirar(1500)
except SaldoInsuficienteError as e:
    print(f"❌ {e}")
    print(f"   Te faltan: ${e.diferencia:,.2f}")
except ValidacionError as e:
    print(f"⚠️ Campo '{e.campo}': {e.mensaje}")
except AppError as e:
    print(f"Error de aplicación: {e}")
```

## Context Managers (with)

Los **context managers** gestionan recursos de forma segura, garantizando que se liberen correctamente incluso si ocurre una excepción:

```python
# Sin context manager (propenso a errores)
archivo = open("datos.txt", "w")
try:
    archivo.write("Contenido importante")
finally:
    archivo.close()  # Hay que recordar cerrar

# Con context manager (forma recomendada)
with open("datos.txt", "w") as archivo:
    archivo.write("Contenido importante")
# El archivo se cierra AUTOMÁTICAMENTE al salir del bloque

# Múltiples context managers
with open("entrada.txt") as entrada, open("salida.txt", "w") as salida:
    for linea in entrada:
        salida.write(linea.upper())

# Ejemplo con manejo de excepciones
try:
    with open("config.json") as f:
        import json
        config = json.load(f)
except FileNotFoundError:
    print("Archivo de configuración no encontrado, usando valores por defecto")
    config = {"debug": False, "puerto": 8080}
except json.JSONDecodeError as e:
    print(f"Error en el formato JSON: {e}")
    config = {}
```

## Crear Context Managers Propios

### Con __enter__ y __exit__

```python
class Temporizador:
    """Context manager que mide el tiempo de ejecución."""
    
    def __enter__(self):
        import time
        self.inicio = time.time()
        print("⏱️ Cronómetro iniciado")
        return self  # Lo que se asigna a la variable después de 'as'
    
    def __exit__(self, tipo_exc, valor_exc, traceback):
        import time
        self.duracion = time.time() - self.inicio
        print(f"⏱️ Tiempo transcurrido: {self.duracion:.4f} segundos")
        # Retornar False (o None) → la excepción se propaga
        # Retornar True → la excepción se suprime
        return False

# Uso
with Temporizador() as t:
    # Operación que queremos medir
    suma = sum(range(1_000_000))
    print(f"Resultado: {suma}")

print(f"Duración registrada: {t.duracion:.4f}s")
```

```python
class ConexionBD:
    """Simula una conexión a base de datos con context manager."""
    
    def __init__(self, host, base_datos):
        self.host = host
        self.base_datos = base_datos
        self.conectado = False
    
    def __enter__(self):
        print(f"🔌 Conectando a {self.base_datos}@{self.host}...")
        self.conectado = True
        return self
    
    def __exit__(self, tipo_exc, valor_exc, traceback):
        print(f"🔌 Cerrando conexión a {self.base_datos}")
        self.conectado = False
        if tipo_exc is not None:
            print(f"⚠️ Error durante la operación: {valor_exc}")
        return False  # No suprimir excepciones
    
    def ejecutar(self, consulta):
        if not self.conectado:
            raise RuntimeError("No hay conexión activa")
        print(f"  📝 Ejecutando: {consulta}")
        return {"status": "ok"}

# Uso
with ConexionBD("localhost", "mi_app") as db:
    db.ejecutar("SELECT * FROM usuarios")
    db.ejecutar("INSERT INTO logs VALUES ('acceso')")
# 🔌 Conectando a mi_app@localhost...
#   📝 Ejecutando: SELECT * FROM usuarios
#   📝 Ejecutando: INSERT INTO logs VALUES ('acceso')
# 🔌 Cerrando conexión a mi_app
```

### Con contextlib (Más Simple)

```python
from contextlib import contextmanager

@contextmanager
def temporizador(nombre="operación"):
    """Context manager con decorador (más conciso)."""
    import time
    inicio = time.time()
    print(f"⏱️ Iniciando '{nombre}'...")
    try:
        yield  # Aquí se ejecuta el bloque 'with'
    except Exception as e:
        print(f"❌ Error en '{nombre}': {e}")
        raise
    finally:
        duracion = time.time() - inicio
        print(f"⏱️ '{nombre}' completado en {duracion:.4f}s")

# Uso
with temporizador("procesamiento de datos"):
    datos = [x**2 for x in range(500_000)]
    print(f"  Procesados {len(datos)} elementos")

# Context manager para manejo temporal de directorio
@contextmanager
def directorio_temporal():
    """Crea un directorio temporal y lo limpia al terminar."""
    import tempfile, shutil, os
    ruta = tempfile.mkdtemp()
    print(f"📁 Directorio temporal: {ruta}")
    try:
        yield ruta
    finally:
        shutil.rmtree(ruta)
        print(f"🗑️ Directorio temporal eliminado")

with directorio_temporal() as tmp:
    # Trabajar con archivos temporales
    ruta_archivo = f"{tmp}/datos.txt"
    with open(ruta_archivo, "w") as f:
        f.write("Datos temporales")
# Al salir, el directorio se elimina automáticamente
```

## Buenas Prácticas

```python
# ✅ Capturar excepciones específicas
try:
    valor = int(texto)
except ValueError:
    valor = 0

# ❌ NUNCA capturar todo sin hacer nada
# try:
#     algo()
# except:
#     pass  # ¡Los errores pasan en silencio!

# ✅ Usar EAFP (Easier to Ask Forgiveness than Permission)
# Estilo Pythónico: intentar y manejar si falla
try:
    valor = diccionario["clave"]
except KeyError:
    valor = "por defecto"

# vs LBYL (Look Before You Leap) - menos Pythónico
if "clave" in diccionario:
    valor = diccionario["clave"]
else:
    valor = "por defecto"

# ✅ Registrar excepciones antes de suprimirlas
import logging

try:
    resultado = operacion_riesgosa()
except Exception as e:
    logging.error(f"Error en operación: {e}", exc_info=True)
    resultado = valor_por_defecto
```

## Ejercicio Práctico

Crea un programa `gestor_archivos.py` que:

1. Defina excepciones personalizadas: `ArchivoError`, `FormatoInvalidoError`, `ArchivoVacioError`.
2. Cree un context manager `GestorCSV` que abra un archivo CSV, lo lea al entrar y libere recursos al salir.
3. Implemente una función `procesar_csv(ruta)` que use el context manager y maneje todos los posibles errores.
4. Maneje: archivo no encontrado, formato inválido, archivo vacío, errores de permisos.

```python
# Uso esperado:
try:
    with GestorCSV("datos.csv") as gestor:
        for fila in gestor.filas:
            print(fila)
        print(f"Total: {gestor.total_filas} filas")
except ArchivoVacioError:
    print("El archivo está vacío")
except FormatoInvalidoError as e:
    print(f"Formato inválido: {e}")
```

## Resumen

- **try/except/else/finally** es la estructura completa para manejar excepciones.
- Captura excepciones **específicas**, no genéricas (`Exception`).
- Las excepciones siguen una **jerarquía de herencia**; capturar la clase base captura todas las subclases.
- **raise** lanza excepciones; puedes encadenarlas con `from`.
- Las **excepciones personalizadas** heredan de `Exception` y modelan errores de tu dominio.
- Los **context managers** (`with`) gestionan recursos de forma segura y automática.
- Crea context managers con `__enter__`/`__exit__` o con `@contextmanager` de `contextlib`.
- Sigue el principio **EAFP**: intenta la operación y maneja la excepción si falla.
