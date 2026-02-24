---
title: "Base de Datos con SQLAlchemy"
slug: "flask-base-datos-sqlalchemy"
description: "Aprende a integrar bases de datos en Flask usando Flask-SQLAlchemy: modelos, operaciones CRUD, consultas y relaciones."
---

# Base de Datos con SQLAlchemy

Toda aplicación web significativa necesita persistir datos. **Flask-SQLAlchemy** es la extensión más popular para integrar bases de datos relacionales en Flask. Proporciona un ORM (Object-Relational Mapping) que te permite interactuar con la base de datos usando clases y objetos Python en lugar de escribir SQL directamente.

## Instalación y Configuración

```bash
pip install flask-sqlalchemy
```

Configura Flask-SQLAlchemy en tu aplicación:

```python
from flask import Flask
from flask_sqlalchemy import SQLAlchemy

app = Flask(__name__)

# Configuración de la base de datos
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///mi_app.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

# Inicializar la extensión
db = SQLAlchemy(app)
```

### URIs de Conexión Comunes

```python
# SQLite (archivo local)
'sqlite:///mi_app.db'

# PostgreSQL
'postgresql://usuario:password@localhost:5432/mi_base'

# MySQL
'mysql+pymysql://usuario:password@localhost:3306/mi_base'

# PostgreSQL con driver psycopg2
'postgresql+psycopg2://usuario:password@localhost/mi_base'
```

## Definir Modelos

Los modelos son clases Python que representan las tablas de la base de datos:

```python
from datetime import datetime

class Usuario(db.Model):
    __tablename__ = 'usuarios'  # Nombre de la tabla (opcional, se infiere del nombre de la clase)
    
    id = db.Column(db.Integer, primary_key=True)
    nombre = db.Column(db.String(100), nullable=False)
    email = db.Column(db.String(120), unique=True, nullable=False)
    password_hash = db.Column(db.String(256), nullable=False)
    activo = db.Column(db.Boolean, default=True)
    fecha_registro = db.Column(db.DateTime, default=datetime.utcnow)
    bio = db.Column(db.Text, nullable=True)
    
    def __repr__(self):
        return f'<Usuario {self.nombre}>'
```

### Tipos de Columna Disponibles

| Tipo SQLAlchemy       | Tipo SQL         | Descripción                  |
|-----------------------|------------------|------------------------------|
| `db.Integer`          | INTEGER          | Entero                       |
| `db.String(n)`        | VARCHAR(n)       | Cadena de longitud máxima n  |
| `db.Text`             | TEXT             | Texto largo                  |
| `db.Float`            | FLOAT            | Número decimal               |
| `db.Boolean`          | BOOLEAN          | Verdadero/Falso              |
| `db.DateTime`         | DATETIME         | Fecha y hora                 |
| `db.Date`             | DATE             | Solo fecha                   |
| `db.LargeBinary`      | BLOB             | Datos binarios               |

### Opciones de Columna

```python
db.Column(db.String(100), 
    primary_key=True,     # Clave primaria
    unique=True,          # Valor único
    nullable=False,       # No permite NULL
    default='valor',      # Valor por defecto
    index=True,           # Crear índice
    server_default='0',   # Valor por defecto en SQL
)
```

## Crear las Tablas

```python
# Dentro del contexto de la aplicación
with app.app_context():
    db.create_all()  # Crea todas las tablas definidas en los modelos
```

## Operaciones CRUD

### Create (Crear)

```python
@app.route('/crear-usuario', methods=['POST'])
def crear_usuario():
    nuevo_usuario = Usuario(
        nombre='Ana García',
        email='ana@example.com',
        password_hash='hash_seguro_aqui'
    )
    
    db.session.add(nuevo_usuario)         # Agregar a la sesión
    db.session.commit()                   # Guardar en la base de datos
    
    return f'Usuario {nuevo_usuario.nombre} creado con ID {nuevo_usuario.id}'
```

Para crear múltiples registros a la vez:

```python
usuarios = [
    Usuario(nombre='Carlos', email='carlos@example.com', password_hash='hash1'),
    Usuario(nombre='María', email='maria@example.com', password_hash='hash2'),
    Usuario(nombre='Luis', email='luis@example.com', password_hash='hash3'),
]

db.session.add_all(usuarios)
db.session.commit()
```

### Read (Leer)

Flask-SQLAlchemy ofrece varias formas de consultar datos:

```python
# Obtener todos los registros
todos = Usuario.query.all()

# Obtener por clave primaria
usuario = db.session.get(Usuario, 1)    # Flask-SQLAlchemy 3.x
# usuario = Usuario.query.get(1)        # Versión legacy

# Primer resultado que coincida
usuario = Usuario.query.filter_by(email='ana@example.com').first()

# first_or_404: retorna 404 si no se encuentra
usuario = Usuario.query.filter_by(email='ana@example.com').first_or_404(
    description='Usuario no encontrado'
)

# Filtros avanzados
activos = Usuario.query.filter(Usuario.activo == True).all()

# Múltiples condiciones
resultado = Usuario.query.filter(
    Usuario.activo == True,
    Usuario.nombre.like('%Ana%')
).all()

# Ordenar resultados
recientes = Usuario.query.order_by(Usuario.fecha_registro.desc()).all()

# Limitar resultados
primeros_cinco = Usuario.query.limit(5).all()

# Paginación
pagina = Usuario.query.paginate(page=1, per_page=10, error_out=False)
usuarios = pagina.items       # Lista de usuarios en la página
pagina.total                  # Total de registros
pagina.pages                  # Total de páginas
pagina.has_next               # ¿Hay página siguiente?
pagina.has_prev               # ¿Hay página anterior?
```

### Filtros Avanzados

```python
from sqlalchemy import or_, and_, func

# OR
resultados = Usuario.query.filter(
    or_(Usuario.nombre == 'Ana', Usuario.nombre == 'Carlos')
).all()

# Conteo
total = Usuario.query.filter_by(activo=True).count()

# Contiene (LIKE)
resultados = Usuario.query.filter(Usuario.email.contains('gmail')).all()

# Empieza/termina con
resultados = Usuario.query.filter(Usuario.nombre.startswith('A')).all()
resultados = Usuario.query.filter(Usuario.email.endswith('.com')).all()

# IN
resultados = Usuario.query.filter(Usuario.id.in_([1, 2, 3])).all()

# BETWEEN
resultados = Usuario.query.filter(Usuario.edad.between(18, 30)).all()

# Funciones de agregación
promedio = db.session.query(func.avg(Producto.precio)).scalar()
```

### Update (Actualizar)

```python
@app.route('/actualizar/<int:id>', methods=['POST'])
def actualizar_usuario(id):
    usuario = db.session.get(Usuario, id)
    if usuario is None:
        abort(404)
    
    usuario.nombre = 'Nuevo Nombre'
    usuario.email = 'nuevo@email.com'
    db.session.commit()
    
    return f'Usuario {usuario.id} actualizado'
```

Actualización masiva:

```python
Usuario.query.filter_by(activo=False).update({'activo': True})
db.session.commit()
```

### Delete (Eliminar)

```python
@app.route('/eliminar/<int:id>', methods=['DELETE'])
def eliminar_usuario(id):
    usuario = db.session.get(Usuario, id)
    if usuario is None:
        abort(404)
    
    db.session.delete(usuario)
    db.session.commit()
    
    return f'Usuario {id} eliminado'
```

### Manejo de Errores

Siempre envuelve las operaciones de base de datos en bloques `try/except`:

```python
from sqlalchemy.exc import IntegrityError

@app.route('/registro', methods=['POST'])
def registrar():
    try:
        usuario = Usuario(
            nombre=request.form['nombre'],
            email=request.form['email'],
            password_hash='hash'
        )
        db.session.add(usuario)
        db.session.commit()
        return 'Registrado exitosamente', 201
    except IntegrityError:
        db.session.rollback()
        return 'El email ya está registrado', 409
```

## Relaciones entre Modelos

### Uno a Muchos (One-to-Many)

```python
class Autor(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    nombre = db.Column(db.String(100), nullable=False)
    # Relación: un autor tiene muchos artículos
    articulos = db.relationship('Articulo', backref='autor', lazy=True, cascade='all, delete-orphan')

class Articulo(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    titulo = db.Column(db.String(200), nullable=False)
    contenido = db.Column(db.Text, nullable=False)
    # Clave foránea
    autor_id = db.Column(db.Integer, db.ForeignKey('autor.id'), nullable=False)
```

Uso:

```python
# Crear autor con artículos
autor = Autor(nombre='Ana')
articulo = Articulo(titulo='Mi Post', contenido='Contenido...', autor=autor)
db.session.add(autor)
db.session.commit()

# Acceder a los artículos de un autor
print(autor.articulos)  # [<Articulo 'Mi Post'>]

# Acceder al autor de un artículo
print(articulo.autor.nombre)  # 'Ana'
```

### Muchos a Muchos (Many-to-Many)

```python
# Tabla intermedia
etiquetas_articulos = db.Table('etiquetas_articulos',
    db.Column('articulo_id', db.Integer, db.ForeignKey('articulo.id'), primary_key=True),
    db.Column('etiqueta_id', db.Integer, db.ForeignKey('etiqueta.id'), primary_key=True)
)

class Etiqueta(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    nombre = db.Column(db.String(50), unique=True)
    articulos = db.relationship('Articulo', secondary=etiquetas_articulos,
                                 backref=db.backref('etiquetas', lazy=True))
```

## Ejercicio Práctico

Crea una aplicación Flask con una base de datos SQLite que gestione una **biblioteca de libros**:

1. Define dos modelos: `Categoria` (id, nombre) y `Libro` (id, titulo, autor_nombre, año, isbn, categoria_id).
2. Implementa rutas para las cuatro operaciones CRUD sobre libros.
3. Agrega paginación (5 libros por página) en la vista de listado.
4. Incluye manejo de errores para duplicados de ISBN.
5. Implementa una ruta de búsqueda por título o autor.

```python
@app.route('/libros')
def listar_libros():
    pagina = request.args.get('pagina', 1, type=int)
    paginacion = Libro.query.order_by(Libro.titulo).paginate(
        page=pagina, per_page=5, error_out=False
    )
    return render_template('libros.html', paginacion=paginacion)
```

## Resumen

Flask-SQLAlchemy facilita enormemente la interacción con bases de datos en Flask. Aprendiste a configurar la conexión, definir modelos con diferentes tipos de columna, ejecutar operaciones CRUD completas, realizar consultas avanzadas con filtros y paginación, y establecer relaciones uno-a-muchos y muchos-a-muchos entre modelos. Estos conocimientos te permiten construir aplicaciones Flask con persistencia de datos robusta y eficiente.
