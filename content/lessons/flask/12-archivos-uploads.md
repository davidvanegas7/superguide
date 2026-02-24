---
title: "Archivos y Uploads"
slug: "flask-archivos-uploads"
description: "Aprende a manejar la subida de archivos en Flask, validar extensiones, servir archivos y conectar con almacenamiento en la nube."
---

# Archivos y Uploads

La subida y gestión de archivos es una funcionalidad común en aplicaciones web: imágenes de perfil, documentos PDF, hojas de cálculo, etc. Flask proporciona herramientas sencillas para manejar uploads de forma segura. En esta lección aprenderás desde la configuración básica hasta la integración con servicios de almacenamiento en la nube.

## Configuración Básica del Upload

Primero, configura la carpeta donde se almacenarán los archivos y el tamaño máximo permitido.

```python
import os
from flask import Flask

app = Flask(__name__)

# Configuración de uploads
app.config['UPLOAD_FOLDER'] = os.path.join(app.root_path, 'uploads')
app.config['MAX_CONTENT_LENGTH'] = 16 * 1024 * 1024  # 16 MB máximo

# Crear la carpeta si no existe
os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)
```

## Subida de Archivos con request.files

Flask recibe archivos a través del objeto `request.files`. Cada archivo es un objeto `FileStorage`.

```python
from flask import request, jsonify
from werkzeug.utils import secure_filename

# Extensiones permitidas
EXTENSIONES_PERMITIDAS = {'png', 'jpg', 'jpeg', 'gif', 'pdf', 'docx'}

def archivo_permitido(nombre_archivo):
    """Verifica que la extensión del archivo esté permitida."""
    return '.' in nombre_archivo and \
           nombre_archivo.rsplit('.', 1)[1].lower() in EXTENSIONES_PERMITIDAS

@app.route('/subir', methods=['POST'])
def subir_archivo():
    # Verificar que se envió un archivo
    if 'archivo' not in request.files:
        return jsonify({"error": "No se envió ningún archivo"}), 400

    archivo = request.files['archivo']

    # Verificar que el archivo tenga nombre
    if archivo.filename == '':
        return jsonify({"error": "No se seleccionó ningún archivo"}), 400

    # Validar la extensión
    if not archivo_permitido(archivo.filename):
        return jsonify({"error": "Extensión no permitida"}), 400

    # Asegurar el nombre del archivo (prevenir ataques de path traversal)
    nombre_seguro = secure_filename(archivo.filename)

    # Guardar el archivo
    ruta_destino = os.path.join(app.config['UPLOAD_FOLDER'], nombre_seguro)
    archivo.save(ruta_destino)

    return jsonify({
        "mensaje": "Archivo subido exitosamente",
        "nombre": nombre_seguro,
        "tamaño": os.path.getsize(ruta_destino)
    }), 201
```

## La Importancia de secure_filename

La función `secure_filename` de Werkzeug protege contra nombres de archivo maliciosos:

```python
from werkzeug.utils import secure_filename

# Ejemplos de sanitización
secure_filename("mi archivo.pdf")          # "mi_archivo.pdf"
secure_filename("../../../etc/passwd")      # "etc_passwd"
secure_filename("foto<script>.jpg")         # "fotoscript.jpg"
secure_filename("Ñoño café.png")            # "Nono_cafe.png"
```

Para conservar nombres únicos y evitar colisiones, puedes agregar un identificador:

```python
import uuid

def generar_nombre_unico(nombre_original):
    """Genera un nombre de archivo único conservando la extensión."""
    extension = nombre_original.rsplit('.', 1)[1].lower()
    nombre_unico = f"{uuid.uuid4().hex}.{extension}"
    return nombre_unico

# Resultado: "a3f2b1c4d5e6f7890123456789abcdef.jpg"
```

## Subida Múltiple de Archivos

Para subir varios archivos a la vez, usa `request.files.getlist()`:

```python
@app.route('/subir-multiple', methods=['POST'])
def subir_multiples():
    """Permite subir varios archivos a la vez."""
    archivos = request.files.getlist('archivos')

    if not archivos:
        return jsonify({"error": "No se enviaron archivos"}), 400

    resultados = []
    errores = []

    for archivo in archivos:
        if archivo.filename == '':
            continue

        if not archivo_permitido(archivo.filename):
            errores.append(f"{archivo.filename}: extensión no permitida")
            continue

        nombre = generar_nombre_unico(archivo.filename)
        ruta = os.path.join(app.config['UPLOAD_FOLDER'], nombre)
        archivo.save(ruta)

        resultados.append({
            "original": archivo.filename,
            "guardado_como": nombre,
            "tamaño": os.path.getsize(ruta)
        })

    return jsonify({
        "subidos": resultados,
        "errores": errores,
        "total": len(resultados)
    }), 201
```

## Servir Archivos con send_from_directory

Para permitir la descarga de archivos almacenados, usa `send_from_directory`:

```python
from flask import send_from_directory, abort

@app.route('/archivos/<nombre_archivo>')
def descargar_archivo(nombre_archivo):
    """Sirve un archivo desde la carpeta de uploads."""
    try:
        return send_from_directory(
            app.config['UPLOAD_FOLDER'],
            nombre_archivo,
            as_attachment=False  # True para forzar descarga
        )
    except FileNotFoundError:
        abort(404)

@app.route('/descargar/<nombre_archivo>')
def forzar_descarga(nombre_archivo):
    """Fuerza la descarga del archivo."""
    return send_from_directory(
        app.config['UPLOAD_FOLDER'],
        nombre_archivo,
        as_attachment=True,
        download_name=f"descarga_{nombre_archivo}"  # Nombre personalizado
    )
```

## Validación Avanzada de Archivos

Verificar solo la extensión no es suficiente. Un usuario podría renombrar un ejecutable malicioso como `.jpg`. Usa validación basada en el contenido real:

```python
import imghdr

def validar_imagen(stream):
    """Verifica que el archivo sea realmente una imagen válida."""
    header = stream.read(512)
    stream.seek(0)  # Resetear el puntero del stream
    formato = imghdr.what(None, header)
    if formato:
        return '.' + formato
    return None

@app.route('/subir-imagen', methods=['POST'])
def subir_imagen():
    archivo = request.files.get('imagen')
    if not archivo:
        return jsonify({"error": "No se envió imagen"}), 400

    # Validar que sea una imagen real
    extension_real = validar_imagen(archivo.stream)
    if extension_real not in ['.jpg', '.jpeg', '.png', '.gif']:
        return jsonify({"error": "El archivo no es una imagen válida"}), 400

    nombre = generar_nombre_unico(archivo.filename)
    archivo.save(os.path.join(app.config['UPLOAD_FOLDER'], nombre))

    return jsonify({"imagen": nombre}), 201
```

## Generación de Thumbnails

Para imágenes, es común generar miniaturas para mejorar el rendimiento de carga:

```python
from PIL import Image

def crear_thumbnail(ruta_imagen, tamaño=(150, 150)):
    """Crea una miniatura de la imagen especificada."""
    directorio = os.path.dirname(ruta_imagen)
    nombre = os.path.basename(ruta_imagen)

    # Abrir la imagen original
    imagen = Image.open(ruta_imagen)

    # Crear thumbnail manteniendo proporciones
    imagen.thumbnail(tamaño, Image.Resampling.LANCZOS)

    # Guardar thumbnail
    ruta_thumb = os.path.join(directorio, 'thumbs', f"thumb_{nombre}")
    os.makedirs(os.path.join(directorio, 'thumbs'), exist_ok=True)
    imagen.save(ruta_thumb, optimize=True, quality=85)

    return ruta_thumb

@app.route('/subir-con-thumbnail', methods=['POST'])
def subir_con_thumbnail():
    archivo = request.files.get('imagen')
    if not archivo or not archivo_permitido(archivo.filename):
        return jsonify({"error": "Imagen no válida"}), 400

    nombre = generar_nombre_unico(archivo.filename)
    ruta = os.path.join(app.config['UPLOAD_FOLDER'], nombre)
    archivo.save(ruta)

    # Generar thumbnail
    ruta_thumb = crear_thumbnail(ruta)

    return jsonify({
        "imagen": nombre,
        "thumbnail": os.path.basename(ruta_thumb)
    }), 201
```

## Integración con Cloud Storage (AWS S3)

En producción, es mejor almacenar archivos en servicios de nube como AWS S3:

```python
import boto3
from botocore.exceptions import ClientError

# Configuración de AWS S3
app.config['S3_BUCKET'] = 'mi-bucket-flask'
app.config['S3_REGION'] = 'us-east-1'

s3_client = boto3.client(
    's3',
    region_name=app.config['S3_REGION'],
    aws_access_key_id=os.environ.get('AWS_ACCESS_KEY_ID'),
    aws_secret_access_key=os.environ.get('AWS_SECRET_ACCESS_KEY')
)

def subir_a_s3(archivo, nombre_archivo):
    """Sube un archivo a AWS S3 y devuelve la URL pública."""
    try:
        s3_client.upload_fileobj(
            archivo,
            app.config['S3_BUCKET'],
            nombre_archivo,
            ExtraArgs={
                'ContentType': archivo.content_type,
                'ACL': 'public-read'
            }
        )
        url = f"https://{app.config['S3_BUCKET']}.s3.amazonaws.com/{nombre_archivo}"
        return url
    except ClientError as e:
        app.logger.error(f"Error subiendo a S3: {e}")
        return None

@app.route('/subir-s3', methods=['POST'])
def subir_archivo_s3():
    """Sube un archivo directamente a S3."""
    archivo = request.files.get('archivo')
    if not archivo:
        return jsonify({"error": "No se envió archivo"}), 400

    nombre = generar_nombre_unico(archivo.filename)
    url = subir_a_s3(archivo, f"uploads/{nombre}")

    if url:
        return jsonify({"url": url, "nombre": nombre}), 201
    return jsonify({"error": "Error al subir el archivo"}), 500
```

## Formulario HTML para Uploads

El formulario debe usar `enctype="multipart/form-data"`:

```html
<form action="/subir" method="POST" enctype="multipart/form-data">
    <div>
        <label for="archivo">Selecciona un archivo:</label>
        <input type="file" name="archivo" id="archivo" accept=".jpg,.png,.pdf">
    </div>
    <div>
        <label for="multiples">O varios archivos:</label>
        <input type="file" name="archivos" id="multiples" multiple>
    </div>
    <button type="submit">Subir</button>
</form>
```

## Ejercicio Práctico

Construye un sistema completo de galería de imágenes con Flask:

1. **Ruta POST /galeria/subir**: acepta imágenes (JPG, PNG, GIF), valida extensión y contenido, genera un nombre único, crea un thumbnail de 200x200, y guarda ambos archivos.
2. **Ruta GET /galeria**: devuelve un JSON con la lista de todas las imágenes y sus thumbnails.
3. **Ruta GET /galeria/<nombre>**: sirve la imagen original.
4. **Ruta GET /galeria/thumb/<nombre>**: sirve la miniatura.
5. **Ruta DELETE /galeria/<nombre>**: elimina la imagen y su thumbnail.
6. Limita el tamaño máximo a 5 MB y maneja el error `413 Request Entity Too Large`.
7. Agrega un contador de descargas que se incremente cada vez que se acceda a una imagen.

## Resumen

- Usa `request.files` para recibir archivos enviados con `multipart/form-data`.
- **`secure_filename()`** sanitiza nombres de archivo para prevenir ataques de path traversal.
- Configura `MAX_CONTENT_LENGTH` para limitar el tamaño de las subidas.
- Valida tanto la extensión como el contenido real del archivo.
- **`send_from_directory()`** sirve archivos de forma segura desde una carpeta.
- Genera **thumbnails** con Pillow para optimizar la carga de imágenes.
- En producción, usa servicios como **AWS S3** en lugar de almacenamiento local.
- Siempre genera nombres únicos para evitar colisiones y sobrescrituras.
