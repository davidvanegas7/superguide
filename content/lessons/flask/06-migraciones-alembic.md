---
title: "Migraciones con Alembic"
slug: "flask-migraciones-alembic"
description: "Aprende a gestionar cambios en el esquema de tu base de datos de forma controlada con Flask-Migrate y Alembic."
---

# Migraciones con Alembic

Cuando desarrollas una aplicación web, el esquema de la base de datos evoluciona constantemente: agregas columnas, creas tablas nuevas o modificas relaciones. Usar `db.create_all()` no es suficiente porque no puede modificar tablas existentes. Las **migraciones** resuelven este problema al mantener un historial versionado de cambios en la base de datos. **Flask-Migrate** integra **Alembic** (la herramienta de migraciones de SQLAlchemy) con Flask de manera transparente.

## ¿Qué son las Migraciones?

Las migraciones son scripts que describen cambios incrementales en el esquema de la base de datos. Cada migración contiene:

- **`upgrade()`**: aplica el cambio (ej. agregar una columna).
- **`downgrade()`**: revierte el cambio (ej. eliminar esa columna).

Esto te permite:
- Versionar los cambios del esquema junto con tu código.
- Colaborar en equipo sin conflictos en la base de datos.
- Revertir cambios si algo sale mal.
- Reproducir la base de datos completa desde cero.

## Instalación

```bash
pip install flask-migrate
```

## Configuración Inicial

Integra Flask-Migrate en tu aplicación:

```python
from flask import Flask
from flask_sqlalchemy import SQLAlchemy
from flask_migrate import Migrate

app = Flask(__name__)
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///mi_app.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

db = SQLAlchemy(app)
migrate = Migrate(app, db)
```

Si usas el **Application Factory Pattern**:

```python
# extensions.py
from flask_sqlalchemy import SQLAlchemy
from flask_migrate import Migrate

db = SQLAlchemy()
migrate = Migrate()

# __init__.py
def create_app():
    app = Flask(__name__)
    app.config.from_object('config.Config')
    
    db.init_app(app)
    migrate.init_app(app, db)
    
    return app
```

## Inicializar Migraciones: `flask db init`

El primer paso es crear el directorio de migraciones:

```bash
flask db init
```

Esto genera un directorio `migrations/` con la siguiente estructura:

```
migrations/
├── alembic.ini
├── env.py
├── README
├── script.py.mako
└── versions/
    └── (aquí se guardarán las migraciones)
```

- **`versions/`**: contiene los scripts de migración individuales.
- **`env.py`**: configuración del entorno de Alembic.
- **`script.py.mako`**: plantilla para generar nuevas migraciones.

> **Nota**: Solo ejecutas `flask db init` una vez por proyecto. El directorio `migrations/` se incluye en el control de versiones.

## Crear una Migración: `flask db migrate`

Supongamos que defines tu primer modelo:

```python
class Usuario(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    nombre = db.Column(db.String(100), nullable=False)
    email = db.Column(db.String(120), unique=True, nullable=False)
```

Para generar una migración que refleje este modelo:

```bash
flask db migrate -m "crear tabla usuarios"
```

El flag `-m` agrega un mensaje descriptivo (similar a un commit de Git). Alembic compara automáticamente el estado actual de tus modelos con el estado de la base de datos y genera el script de migración apropiado.

El archivo generado en `migrations/versions/` se verá así:

```python
"""crear tabla usuarios

Revision ID: a1b2c3d4e5f6
Revises: 
Create Date: 2026-02-23 10:30:00.000000
"""
from alembic import op
import sqlalchemy as sa

# Identificadores de revisión
revision = 'a1b2c3d4e5f6'
down_revision = None
branch_labels = None
depends_on = None

def upgrade():
    op.create_table('usuario',
        sa.Column('id', sa.Integer(), nullable=False),
        sa.Column('nombre', sa.String(length=100), nullable=False),
        sa.Column('email', sa.String(length=120), nullable=False),
        sa.PrimaryKeyConstraint('id'),
        sa.UniqueConstraint('email')
    )

def downgrade():
    op.drop_table('usuario')
```

> **Importante**: Siempre revisa el script de migración generado antes de aplicarlo. Alembic no siempre detecta todos los cambios correctamente (especialmente renombrado de columnas o tablas).

## Aplicar Migraciones: `flask db upgrade`

Para ejecutar las migraciones pendientes y actualizar la base de datos:

```bash
flask db upgrade
```

Esto ejecuta la función `upgrade()` de cada migración no aplicada, en orden cronológico.

Para aplicar hasta una revisión específica:

```bash
flask db upgrade a1b2c3d4e5f6
```

## Flujo de Trabajo Completo

Veamos un flujo de trabajo práctico paso a paso:

### Paso 1: Agregar un Campo Nuevo

Modificas el modelo `Usuario` para agregar una columna `bio`:

```python
class Usuario(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    nombre = db.Column(db.String(100), nullable=False)
    email = db.Column(db.String(120), unique=True, nullable=False)
    bio = db.Column(db.Text, nullable=True)  # ← Nueva columna
```

### Paso 2: Generar la Migración

```bash
flask db migrate -m "agregar campo bio a usuarios"
```

Migración generada:

```python
def upgrade():
    op.add_column('usuario', sa.Column('bio', sa.Text(), nullable=True))

def downgrade():
    op.drop_column('usuario', 'bio')
```

### Paso 3: Aplicar

```bash
flask db upgrade
```

### Paso 4: Agregar un Nuevo Modelo

```python
class Articulo(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    titulo = db.Column(db.String(200), nullable=False)
    contenido = db.Column(db.Text, nullable=False)
    fecha = db.Column(db.DateTime, default=datetime.utcnow)
    autor_id = db.Column(db.Integer, db.ForeignKey('usuario.id'), nullable=False)
```

```bash
flask db migrate -m "crear tabla articulos"
flask db upgrade
```

## Revertir Migraciones: `flask db downgrade`

Para revertir la última migración aplicada:

```bash
flask db downgrade
```

Para revertir hasta una revisión específica:

```bash
flask db downgrade a1b2c3d4e5f6
```

Para revertir todas las migraciones (volver al estado inicial):

```bash
flask db downgrade base
```

## Comandos Útiles

```bash
# Ver el historial de migraciones
flask db history

# Ver la revisión actual de la base de datos
flask db current

# Ver las migraciones pendientes
flask db heads

# Mostrar las diferencias entre modelos y base de datos
flask db check

# Generar un script SQL sin ejecutar (para revisión)
flask db upgrade --sql
```

## Operaciones Comunes en Migraciones

A veces necesitas escribir o editar migraciones manualmente:

```python
from alembic import op
import sqlalchemy as sa

def upgrade():
    # Agregar columna
    op.add_column('usuarios', sa.Column('telefono', sa.String(20)))
    
    # Eliminar columna
    op.drop_column('usuarios', 'campo_viejo')
    
    # Renombrar columna
    op.alter_column('usuarios', 'nombre_viejo', new_column_name='nombre_nuevo')
    
    # Crear índice
    op.create_index('ix_usuarios_email', 'usuarios', ['email'], unique=True)
    
    # Eliminar índice
    op.drop_index('ix_usuarios_email', 'usuarios')
    
    # Renombrar tabla
    op.rename_table('nombre_viejo', 'nombre_nuevo')
    
    # Agregar clave foránea
    op.create_foreign_key('fk_articulo_autor', 'articulos', 'usuarios',
                          ['autor_id'], ['id'])

def downgrade():
    # Revertir en orden inverso
    op.drop_constraint('fk_articulo_autor', 'articulos', type_='foreignkey')
    op.rename_table('nombre_nuevo', 'nombre_viejo')
    op.create_index('ix_usuarios_email', 'usuarios', ['email'], unique=True)
    op.drop_column('usuarios', 'telefono')
```

## Migraciones con Datos (Data Migrations)

A veces necesitas no solo cambiar el esquema sino también migrar datos:

```python
from alembic import op
import sqlalchemy as sa
from sqlalchemy.sql import table, column

def upgrade():
    # Agregar columna 'rol' con valor por defecto
    op.add_column('usuarios', sa.Column('rol', sa.String(20), server_default='usuario'))
    
    # Migrar datos: poner 'admin' a usuarios específicos
    usuarios = table('usuarios',
        column('id', sa.Integer),
        column('rol', sa.String)
    )
    op.execute(
        usuarios.update().where(usuarios.c.id == 1).values(rol='admin')
    )

def downgrade():
    op.drop_column('usuarios', 'rol')
```

## Buenas Prácticas

1. **Mensajes descriptivos**: Usa `-m` con mensajes claros como en Git.
2. **Revisar siempre**: Examina cada migración generada antes de aplicarla.
3. **No editar migraciones aplicadas**: Si ya se ejecutó en producción, crea una nueva.
4. **Incluir en Git**: El directorio `migrations/` forma parte del código fuente.
5. **Probar downgrade**: Verifica que la reversión funcione correctamente.
6. **Entornos separados**: Usa variables de entorno para las URIs de base de datos.

## Ejercicio Práctico

Simula la evolución de una base de datos para un blog:

1. **Migración 1**: Crea los modelos `Usuario` (id, nombre, email) y `Post` (id, titulo, contenido, fecha, autor_id como FK a Usuario).
2. **Migración 2**: Agrega el campo `avatar_url` (String, nullable) al modelo `Usuario`.
3. **Migración 3**: Crea el modelo `Comentario` (id, texto, fecha, post_id, usuario_id).
4. **Migración 4**: Agrega el campo `publicado` (Boolean, default False) al modelo `Post`.

Para cada paso:
- Modifica el modelo en Python.
- Ejecuta `flask db migrate -m "descripción"`.
- Revisa la migración generada.
- Aplica con `flask db upgrade`.
- Verifica con `flask db current`.

Luego practica revertir a la migración 2 con `flask db downgrade` y volver a aplicar todas.

## Resumen

Flask-Migrate y Alembic te dan control total sobre la evolución del esquema de tu base de datos. Aprendiste a inicializar el sistema de migraciones, generar migraciones automáticas a partir de cambios en tus modelos, aplicarlas y revertirlas, y a realizar operaciones manuales como renombrar columnas o migrar datos. Dominar las migraciones es esencial para mantener la integridad de tus datos a lo largo del ciclo de vida de tu aplicación.
