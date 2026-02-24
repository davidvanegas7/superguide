---
title: "Concurrencia y Paralelismo"
slug: "python-concurrencia-paralelismo"
description: "Comprende threading, multiprocessing y asyncio en Python: cuándo usar cada modelo de concurrencia y cómo aprovechar el paralelismo."
---

# Concurrencia y Paralelismo

La **concurrencia** permite que múltiples tareas progresen durante un mismo período de tiempo, mientras que el **paralelismo** ejecuta múltiples tareas simultáneamente en diferentes núcleos del procesador. Python ofrece tres enfoques principales: `threading`, `multiprocessing` y `asyncio`. Entender cuándo y cómo usar cada uno es clave para escribir programas eficientes.

## El GIL (Global Interpreter Lock)

El **GIL** es un mecanismo del intérprete CPython que permite que solo un hilo ejecute código Python a la vez. Esto significa que `threading` no proporciona paralelismo real para tareas intensivas en CPU, pero sí funciona bien para tareas de **I/O** (red, disco, etc.):

```python
# El GIL afecta a tareas CPU-bound
# Para CPU-bound → usar multiprocessing
# Para I/O-bound → usar threading o asyncio
```

| Tipo de Tarea | Solución Recomendada |
|---------------|---------------------|
| I/O (red, disco) | `threading` o `asyncio` |
| CPU (cálculos) | `multiprocessing` |
| Mixto | Combinar ambos |

## Threading

El módulo `threading` permite ejecutar tareas concurrentes en hilos separados. Ideal para operaciones de I/O:

```python
import threading
import time

def descargar_pagina(url):
    """Simula la descarga de una página web."""
    print(f"Descargando {url}...")
    time.sleep(2)  # Simula espera de red
    print(f"Completado {url}")

# Sin hilos — ejecución secuencial (~6 segundos)
urls = ["https://sitio1.com", "https://sitio2.com", "https://sitio3.com"]

# Con hilos — ejecución concurrente (~2 segundos)
hilos = []
for url in urls:
    hilo = threading.Thread(target=descargar_pagina, args=(url,))
    hilos.append(hilo)
    hilo.start()

# Esperar a que todos los hilos terminen
for hilo in hilos:
    hilo.join()

print("Todas las descargas completadas")
```

### Sincronización con Lock

Cuando múltiples hilos acceden a datos compartidos, necesitas sincronización:

```python
import threading

contador = 0
lock = threading.Lock()

def incrementar(n):
    global contador
    for _ in range(n):
        with lock:  # Solo un hilo a la vez modifica el contador
            contador += 1

hilos = [threading.Thread(target=incrementar, args=(100_000,)) for _ in range(5)]
for h in hilos:
    h.start()
for h in hilos:
    h.join()

print(f"Contador: {contador}")  # 500000 (sin lock podría ser menor)
```

## Multiprocessing

El módulo `multiprocessing` crea procesos separados, cada uno con su propio GIL. Ideal para tareas intensivas en **CPU**:

```python
import multiprocessing
import time

def calcular_factorial(n):
    """Calcula el factorial de un número grande."""
    resultado = 1
    for i in range(1, n + 1):
        resultado *= i
    return n, len(str(resultado))  # Número y cantidad de dígitos

# Ejecución en paralelo
if __name__ == "__main__":
    numeros = [50000, 60000, 70000, 80000]

    inicio = time.perf_counter()

    with multiprocessing.Pool(processes=4) as pool:
        resultados = pool.map(calcular_factorial, numeros)

    fin = time.perf_counter()
    
    for n, digitos in resultados:
        print(f"{n}! tiene {digitos} dígitos")
    print(f"Tiempo: {fin - inicio:.2f} segundos")
```

### Comunicación entre Procesos

```python
import multiprocessing

def productor(cola):
    """Produce datos y los pone en la cola."""
    for i in range(5):
        cola.put(f"Dato {i}")
    cola.put(None)  # Señal de fin

def consumidor(cola):
    """Consume datos de la cola."""
    while True:
        dato = cola.get()
        if dato is None:
            break
        print(f"Procesando: {dato}")

if __name__ == "__main__":
    cola = multiprocessing.Queue()
    
    proc_prod = multiprocessing.Process(target=productor, args=(cola,))
    proc_cons = multiprocessing.Process(target=consumidor, args=(cola,))
    
    proc_prod.start()
    proc_cons.start()
    
    proc_prod.join()
    proc_cons.join()
```

## `asyncio` — Programación Asíncrona

`asyncio` es el framework de programación asíncrona de Python. Usa un **event loop** con un solo hilo, alternando entre tareas cuando una espera I/O:

```python
import asyncio

async def descargar(url, segundos):
    """Simula una descarga asíncrona."""
    print(f"Iniciando descarga de {url}")
    await asyncio.sleep(segundos)  # No bloquea el event loop
    print(f"Completada descarga de {url}")
    return f"Datos de {url}"

async def main():
    # Ejecutar tareas concurrentemente
    resultados = await asyncio.gather(
        descargar("sitio1.com", 2),
        descargar("sitio2.com", 3),
        descargar("sitio3.com", 1),
    )
    print(f"Resultados: {resultados}")

# Ejecutar
asyncio.run(main())
# Tiempo total: ~3 segundos (no 6)
```

### async/await en Detalle

```python
import asyncio

# Función asíncrona (corrutina)
async def obtener_datos(id):
    print(f"Consultando datos para ID={id}")
    await asyncio.sleep(1)  # Simula llamada a BD
    return {"id": id, "nombre": f"Usuario_{id}"}

# Crear y manejar tareas
async def main():
    # Crear tareas explícitamente
    tarea1 = asyncio.create_task(obtener_datos(1))
    tarea2 = asyncio.create_task(obtener_datos(2))

    # Ambas tareas se ejecutan concurrentemente
    dato1 = await tarea1
    dato2 = await tarea2
    print(dato1, dato2)

    # Esperar con timeout
    try:
        resultado = await asyncio.wait_for(obtener_datos(3), timeout=0.5)
    except asyncio.TimeoutError:
        print("Tiempo agotado")

    # Ejecutar tareas y recoger resultados según terminan
    tareas = [obtener_datos(i) for i in range(5)]
    for corrutina in asyncio.as_completed(tareas):
        resultado = await corrutina
        print(f"Completado: {resultado}")

asyncio.run(main())
```

### Ejemplo con `aiohttp`

Para realizar solicitudes HTTP asíncronas de verdad:

```python
import asyncio
import aiohttp

async def obtener_pagina(session, url):
    """Descarga una página web de forma asíncrona."""
    async with session.get(url) as response:
        contenido = await response.text()
        print(f"{url}: {len(contenido)} caracteres")
        return contenido

async def main():
    urls = [
        "https://python.org",
        "https://docs.python.org",
        "https://pypi.org",
    ]

    async with aiohttp.ClientSession() as session:
        tareas = [obtener_pagina(session, url) for url in urls]
        resultados = await asyncio.gather(*tareas)
        print(f"Total páginas descargadas: {len(resultados)}")

# pip install aiohttp
asyncio.run(main())
```

## `concurrent.futures` — Interfaz Unificada

Este módulo proporciona una interfaz de alto nivel para ejecutar tareas con hilos o procesos:

### ThreadPoolExecutor

```python
from concurrent.futures import ThreadPoolExecutor, as_completed
import time

def descargar(url):
    """Simula descarga."""
    time.sleep(2)
    return f"Contenido de {url}"

urls = [f"https://sitio{i}.com" for i in range(10)]

# Usar pool de hilos
with ThreadPoolExecutor(max_workers=5) as executor:
    # submit: enviar tareas individuales
    futuros = {executor.submit(descargar, url): url for url in urls}

    for futuro in as_completed(futuros):
        url = futuros[futuro]
        try:
            resultado = futuro.result()
            print(f"{url}: {resultado}")
        except Exception as e:
            print(f"{url} falló: {e}")

# map: aplicar función a todos los elementos
with ThreadPoolExecutor(max_workers=5) as executor:
    resultados = list(executor.map(descargar, urls))
    print(f"Descargados: {len(resultados)} sitios")
```

### ProcessPoolExecutor

```python
from concurrent.futures import ProcessPoolExecutor
import math

def es_primo(n):
    """Verifica si un número es primo."""
    if n < 2:
        return False
    for i in range(2, int(math.sqrt(n)) + 1):
        if n % i == 0:
            return False
    return True

def contar_primos(rango):
    """Cuenta los primos en un rango."""
    inicio, fin = rango
    return sum(1 for n in range(inicio, fin) if es_primo(n))

if __name__ == "__main__":
    # Dividir el trabajo en rangos
    rangos = [(i, i + 250_000) for i in range(0, 1_000_000, 250_000)]

    with ProcessPoolExecutor(max_workers=4) as executor:
        resultados = list(executor.map(contar_primos, rangos))

    total = sum(resultados)
    print(f"Primos encontrados: {total}")
```

## ¿Cuándo Usar Cada Uno?

```python
# Guía de decisión rápida:

# 1. THREADING — Múltiples operaciones I/O simultáneas
#    Ejemplos: descargar archivos, consultas a APIs, leer de disco
#    Ventaja: ligero, memoria compartida
#    Limitación: GIL impide paralelismo CPU real

# 2. MULTIPROCESSING — Cálculo intensivo en CPU
#    Ejemplos: procesamiento de imágenes, cálculos numéricos
#    Ventaja: verdadero paralelismo, sin GIL
#    Limitación: mayor consumo de memoria, overhead de IPC

# 3. ASYNCIO — Miles de conexiones I/O simultáneas
#    Ejemplos: servidores web, websockets, crawlers
#    Ventaja: muy eficiente con muchas conexiones, un solo hilo
#    Limitación: requiere librerías async, no apto para CPU-bound

# 4. CONCURRENT.FUTURES — Interfaz simple para threading/multiprocessing
#    Uso: cuando quieres cambiar fácilmente entre hilos y procesos
```

## Ejercicio Práctico

Crea un descargador concurrente que descargue múltiples URLs y las guarde en disco:

```python
import asyncio
import aiohttp
from pathlib import Path
import time

async def descargar_y_guardar(session, url, directorio):
    """Descarga una URL y guarda el contenido en un archivo."""
    try:
        async with session.get(url, timeout=aiohttp.ClientTimeout(total=10)) as resp:
            contenido = await resp.text()
            nombre = url.split("//")[1].replace("/", "_") + ".html"
            ruta = directorio / nombre
            ruta.write_text(contenido, encoding="utf-8")
            print(f"OK: {url} ({len(contenido)} chars)")
            return url, True
    except Exception as e:
        print(f"ERROR: {url} — {e}")
        return url, False

async def main():
    urls = [
        "https://python.org",
        "https://docs.python.org",
        "https://pypi.org",
    ]

    directorio = Path("descargas")
    directorio.mkdir(exist_ok=True)

    inicio = time.perf_counter()

    async with aiohttp.ClientSession() as session:
        tareas = [descargar_y_guardar(session, url, directorio) for url in urls]
        resultados = await asyncio.gather(*tareas)

    exitos = sum(1 for _, ok in resultados if ok)
    print(f"\n{exitos}/{len(urls)} descargados en {time.perf_counter()-inicio:.2f}s")

asyncio.run(main())
```

**Reto:** Modifica el ejercicio para usar `ProcessPoolExecutor` sobre los archivos descargados y contar las palabras de cada uno en paralelo.

## Resumen

- El **GIL** limita el paralelismo de hilos en CPython para tareas de CPU.
- **`threading`** es ideal para I/O concurrente: descargas, consultas de red y disco.
- **`multiprocessing`** permite paralelismo real en múltiples núcleos para tareas de CPU.
- **`asyncio`** (async/await) es eficiente para manejar miles de operaciones I/O con un solo hilo.
- **`concurrent.futures`** proporciona `ThreadPoolExecutor` y `ProcessPoolExecutor` con una interfaz unificada y limpia.
- Usa `threading` o `asyncio` para I/O, `multiprocessing` para CPU, y `concurrent.futures` para abstracción de alto nivel.
