---
title: "APIs REST"
slug: "flask-api-rest"
description: "Construye APIs RESTful con Flask: respuestas JSON, códigos de estado, serialización con Marshmallow, manejo de errores y CORS."
---

# APIs REST

Flask es una excelente opción para construir **APIs RESTful** gracias a su ligereza y flexibilidad. Una API REST permite que diferentes aplicaciones (frontends, apps móviles, otros servicios) se comuniquen con tu backend mediante solicitudes HTTP y respuestas JSON. En esta lección aprenderás a diseñar y construir APIs profesionales con Flask.

## Fundamentos de REST

REST (Representational State Transfer) es un estilo arquitectónico que define convenciones para las APIs web:

| Método HTTP | Ruta              | Acción                         |
|-------------|-------------------|--------------------------------|
| `GET`       | `/api/usuarios`   | Listar todos los usuarios      |
| `GET`       | `/api/usuarios/1` | Obtener usuario con id 1       |
| `POST`      | `/api/usuarios`   | Crear un nuevo usuario         |
| `PUT`       | `/api/usuarios/1` | Actualizar usuario completo    |
| `PATCH`     | `/api/usuarios/1` | Actualizar parcialmente        |
| `DELETE`    | `/api/usuarios/1` | Eliminar usuario               |

## Respuestas JSON con `jsonify`

Flask proporciona `jsonify()` para crear respuestas JSON correctamente formateadas:

```python
from flask import Flask, jsonify, request

app = Flask(__name__)

# Datos de ejemplo (normalmente vendrían de una base de datos)
tareas = [
    {'id': 1, 'titulo': 'Aprender Flask', 'completada': True},
    {'id': 2, 'titulo': 'Construir API', 'completada': False},
    {'id': 3, 'titulo': 'Desplegar aplicación', 'completada': False},
]

@app.route('/api/tareas', methods=['GET'])
def listar_tareas():
    return jsonify({
        'tareas': tareas,
        'total': len(tareas)
    })
```

`jsonify()` automáticamente:
- Serializa diccionarios y listas a JSON.
- Establece el `Content-Type` a `application/json`.
- Retorna un objeto `Response`.

A partir de Flask 1.0, también puedes retornar diccionarios directamente:

```python
@app.route('/api/estado')
def estado():
    return {'status': 'ok', 'version': '1.0'}  # Se convierte a JSON automáticamente
```

## Códigos de Estado HTTP

Siempre devuelve el código de estado apropiado:

```python
@app.route('/api/tareas', methods=['POST'])
def crear_tarea():
    datos = request.json
    
    if not datos or 'titulo' not in datos:
        return jsonify({'error': 'El campo titulo es obligatorio'}), 400
    
    nueva_tarea = {
        'id': len(tareas) + 1,
        'titulo': datos['titulo'],
        'completada': datos.get('completada', False)
    }
    tareas.append(nueva_tarea)
    
    return jsonify(nueva_tarea), 201  # 201 Created
```

Códigos de estado comunes en APIs:

| Código | Significado          | Uso                                      |
|--------|----------------------|------------------------------------------|
| `200`  | OK                   | Solicitud exitosa                        |
| `201`  | Created              | Recurso creado exitosamente              |
| `204`  | No Content           | Éxito sin contenido (ej. DELETE)         |
| `400`  | Bad Request          | Datos inválidos del cliente              |
| `401`  | Unauthorized         | No autenticado                           |
| `403`  | Forbidden            | Sin permisos                             |
| `404`  | Not Found            | Recurso no encontrado                    |
| `409`  | Conflict             | Conflicto (ej. duplicado)                |
| `422`  | Unprocessable Entity | Error de validación                      |
| `500`  | Internal Server Error| Error del servidor                       |

## API CRUD Completa

```python
from flask import Flask, jsonify, request, abort

app = Flask(__name__)

# Simulación de base de datos
libros = [
    {'id': 1, 'titulo': 'Don Quijote', 'autor': 'Cervantes', 'año': 1605},
    {'id': 2, 'titulo': 'Cien Años de Soledad', 'autor': 'García Márquez', 'año': 1967},
]
siguiente_id = 3

def buscar_libro(libro_id):
    return next((l for l in libros if l['id'] == libro_id), None)

# GET - Listar todos
@app.route('/api/libros', methods=['GET'])
def listar_libros():
    # Filtros opcionales
    autor = request.args.get('autor')
    if autor:
        filtrados = [l for l in libros if autor.lower() in l['autor'].lower()]
        return jsonify({'libros': filtrados, 'total': len(filtrados)})
    return jsonify({'libros': libros, 'total': len(libros)})

# GET - Obtener uno
@app.route('/api/libros/<int:id>', methods=['GET'])
def obtener_libro(id):
    libro = buscar_libro(id)
    if libro is None:
        return jsonify({'error': 'Libro no encontrado'}), 404
    return jsonify(libro)

# POST - Crear
@app.route('/api/libros', methods=['POST'])
def crear_libro():
    global siguiente_id
    datos = request.json
    
    if not datos:
        return jsonify({'error': 'Se requiere JSON'}), 400
    
    # Validación
    campos_requeridos = ['titulo', 'autor']
    for campo in campos_requeridos:
        if campo not in datos:
            return jsonify({'error': f'El campo {campo} es obligatorio'}), 422
    
    nuevo_libro = {
        'id': siguiente_id,
        'titulo': datos['titulo'],
        'autor': datos['autor'],
        'año': datos.get('año', None)
    }
    libros.append(nuevo_libro)
    siguiente_id += 1
    
    return jsonify(nuevo_libro), 201

# PUT - Actualizar completo
@app.route('/api/libros/<int:id>', methods=['PUT'])
def actualizar_libro(id):
    libro = buscar_libro(id)
    if libro is None:
        return jsonify({'error': 'Libro no encontrado'}), 404
    
    datos = request.json
    if not datos or 'titulo' not in datos or 'autor' not in datos:
        return jsonify({'error': 'Se requieren titulo y autor'}), 422
    
    libro['titulo'] = datos['titulo']
    libro['autor'] = datos['autor']
    libro['año'] = datos.get('año')
    
    return jsonify(libro)

# PATCH - Actualizar parcialmente
@app.route('/api/libros/<int:id>', methods=['PATCH'])
def actualizar_parcial_libro(id):
    libro = buscar_libro(id)
    if libro is None:
        return jsonify({'error': 'Libro no encontrado'}), 404
    
    datos = request.json
    if 'titulo' in datos:
        libro['titulo'] = datos['titulo']
    if 'autor' in datos:
        libro['autor'] = datos['autor']
    if 'año' in datos:
        libro['año'] = datos['año']
    
    return jsonify(libro)

# DELETE - Eliminar
@app.route('/api/libros/<int:id>', methods=['DELETE'])
def eliminar_libro(id):
    libro = buscar_libro(id)
    if libro is None:
        return jsonify({'error': 'Libro no encontrado'}), 404
    
    libros.remove(libro)
    return '', 204  # No Content
```

## Manejo de Errores Personalizado

Crea manejadores de errores que retornen JSON para tu API:

```python
@app.errorhandler(400)
def bad_request(error):
    return jsonify({'error': 'Solicitud incorrecta', 'codigo': 400}), 400

@app.errorhandler(404)
def not_found(error):
    return jsonify({'error': 'Recurso no encontrado', 'codigo': 404}), 404

@app.errorhandler(405)
def method_not_allowed(error):
    return jsonify({'error': 'Método no permitido', 'codigo': 405}), 405

@app.errorhandler(500)
def internal_error(error):
    return jsonify({'error': 'Error interno del servidor', 'codigo': 500}), 500
```

### Excepciones Personalizadas

```python
class APIError(Exception):
    def __init__(self, mensaje, codigo=400, detalles=None):
        self.mensaje = mensaje
        self.codigo = codigo
        self.detalles = detalles

@app.errorhandler(APIError)
def handle_api_error(error):
    respuesta = {'error': error.mensaje, 'codigo': error.codigo}
    if error.detalles:
        respuesta['detalles'] = error.detalles
    return jsonify(respuesta), error.codigo

# Uso
@app.route('/api/procesar', methods=['POST'])
def procesar():
    datos = request.json
    if not datos:
        raise APIError('Se requiere un cuerpo JSON', 400)
    if 'valor' not in datos:
        raise APIError('Campo faltante', 422, detalles={'campo': 'valor'})
    return jsonify({'resultado': datos['valor'] * 2})
```

## Serialización con Marshmallow

**Marshmallow** es una biblioteca poderosa para serialización, deserialización y validación de datos. **Flask-Marshmallow** integra Marshmallow con Flask y SQLAlchemy:

```bash
pip install flask-marshmallow marshmallow-sqlalchemy
```

```python
from flask_marshmallow import Marshmallow

ma = Marshmallow(app)

# Modelo SQLAlchemy
class Producto(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    nombre = db.Column(db.String(100), nullable=False)
    precio = db.Column(db.Float, nullable=False)
    descripcion = db.Column(db.Text)
    en_stock = db.Column(db.Boolean, default=True)

# Schema de Marshmallow
class ProductoSchema(ma.SQLAlchemyAutoSchema):
    class Meta:
        model = Producto
        load_instance = True  # Deserializa a instancias del modelo
        include_fk = True

# Instancias del schema
producto_schema = ProductoSchema()
productos_schema = ProductoSchema(many=True)

# Uso en las rutas
@app.route('/api/productos', methods=['GET'])
def listar_productos():
    productos = Producto.query.all()
    return jsonify(productos_schema.dump(productos))

@app.route('/api/productos/<int:id>', methods=['GET'])
def obtener_producto(id):
    producto = db.session.get(Producto, id)
    if not producto:
        return jsonify({'error': 'No encontrado'}), 404
    return jsonify(producto_schema.dump(producto))

@app.route('/api/productos', methods=['POST'])
def crear_producto():
    try:
        producto = producto_schema.load(request.json)
        db.session.add(producto)
        db.session.commit()
        return jsonify(producto_schema.dump(producto)), 201
    except ValidationError as err:
        return jsonify({'errores': err.messages}), 422
```

### Validación con Marshmallow

```python
from marshmallow import fields, validate, validates, ValidationError

class ProductoSchema(ma.Schema):
    id = fields.Int(dump_only=True)
    nombre = fields.Str(required=True, validate=validate.Length(min=2, max=100))
    precio = fields.Float(required=True, validate=validate.Range(min=0.01))
    descripcion = fields.Str(validate=validate.Length(max=500))
    en_stock = fields.Bool(load_default=True)
    
    @validates('nombre')
    def validar_nombre(self, value):
        if Producto.query.filter_by(nombre=value).first():
            raise ValidationError('Ya existe un producto con ese nombre.')
```

## CORS (Cross-Origin Resource Sharing)

Si tu API será consumida por un frontend en otro dominio, necesitas habilitar **CORS**:

```bash
pip install flask-cors
```

```python
from flask_cors import CORS

app = Flask(__name__)

# Habilitar CORS para toda la aplicación
CORS(app)

# O solo para rutas específicas
CORS(app, resources={r"/api/*": {"origins": ["http://localhost:3000", "https://mifrontend.com"]}})

# O para un Blueprint
api_bp = Blueprint('api', __name__)
CORS(api_bp)
```

También puedes configurar encabezados adicionales:

```python
CORS(app, 
     resources={r"/api/*": {"origins": "*"}},
     supports_credentials=True,
     allow_headers=["Content-Type", "Authorization"],
     methods=["GET", "POST", "PUT", "PATCH", "DELETE"])
```

## Ejercicio Práctico

Construye una API REST completa para gestionar una lista de **tareas (TODO)**:

1. **Modelo `Tarea`**: id, titulo (obligatorio), descripcion, completada (boolean), prioridad (alta/media/baja), fecha_creacion.
2. **Endpoints CRUD**: GET (listar con filtros por estado y prioridad), GET por ID, POST, PUT, PATCH, DELETE.
3. **Serialización** con Marshmallow incluyendo validación.
4. **Manejadores de error** personalizados que retornen JSON.
5. **Paginación**: acepta `page` y `per_page` como query params.
6. **CORS** habilitado para `http://localhost:3000`.

Prueba tu API con `curl`:

```bash
# Listar tareas
curl http://localhost:5000/api/tareas

# Crear tarea
curl -X POST http://localhost:5000/api/tareas \
  -H "Content-Type: application/json" \
  -d '{"titulo": "Mi tarea", "prioridad": "alta"}'

# Actualizar tarea
curl -X PATCH http://localhost:5000/api/tareas/1 \
  -H "Content-Type: application/json" \
  -d '{"completada": true}'

# Eliminar tarea
curl -X DELETE http://localhost:5000/api/tareas/1
```

## Resumen

En esta lección aprendiste a construir APIs RESTful profesionales con Flask. Dominaste el uso de `jsonify` para respuestas JSON, los códigos de estado HTTP adecuados para cada operación, el manejo de errores personalizado, la serialización y validación con Marshmallow, y la configuración de CORS para permitir peticiones desde otros orígenes. Estos conocimientos te permiten crear backends robustos que sirvan datos a cualquier tipo de cliente.
