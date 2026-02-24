---
title: "Rutas y Vistas"
slug: "flask-rutas-vistas"
description: "Domina el sistema de enrutamiento de Flask: decoradores, métodos HTTP, variables en rutas, redirecciones y manejo de errores."
---

# Rutas y Vistas

El sistema de enrutamiento es el corazón de cualquier aplicación web. En Flask, las **rutas** definen qué URLs están disponibles y las **vistas** (funciones de vista) determinan qué ocurre cuando un usuario accede a cada URL. En esta lección exploraremos en profundidad cómo Flask gestiona las rutas y cómo puedes crear un enrutamiento sólido y flexible.

## El Decorador `@app.route()`

El decorador `@app.route()` es la forma principal de asociar una URL con una función en Flask:

```python
from flask import Flask

app = Flask(__name__)

@app.route('/')
def inicio():
    return 'Página principal'

@app.route('/productos')
def productos():
    return 'Lista de productos'
```

También puedes registrar rutas usando `app.add_url_rule()`, que es el equivalente programático del decorador:

```python
def contacto():
    return 'Página de contacto'

app.add_url_rule('/contacto', 'contacto', contacto)
```

## Métodos HTTP

Por defecto, una ruta solo responde a solicitudes **GET**. Para aceptar otros métodos HTTP, usa el parámetro `methods`:

```python
from flask import request

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        usuario = request.form.get('usuario')
        password = request.form.get('password')
        # Procesar autenticación
        return f'Intentando login como {usuario}'
    # Mostrar formulario para GET
    return '''
    <form method="POST">
        <input name="usuario" placeholder="Usuario">
        <input name="password" type="password" placeholder="Contraseña">
        <button type="submit">Entrar</button>
    </form>
    '''
```

Los métodos HTTP más comunes son:

| Método   | Propósito                     |
|----------|-------------------------------|
| `GET`    | Obtener un recurso            |
| `POST`   | Crear un recurso              |
| `PUT`    | Actualizar un recurso completo|
| `PATCH`  | Actualizar parcialmente       |
| `DELETE` | Eliminar un recurso           |

A partir de Flask 2.0, también puedes usar decoradores específicos por método:

```python
@app.get('/usuarios')
def listar_usuarios():
    return 'Lista de usuarios'

@app.post('/usuarios')
def crear_usuario():
    return 'Usuario creado'

@app.delete('/usuarios/<int:id>')
def eliminar_usuario(id):
    return f'Usuario {id} eliminado'
```

## Variables en las Rutas

Flask permite capturar segmentos dinámicos de la URL usando **variables de ruta** con la sintaxis `<variable>`:

```python
@app.route('/usuario/<nombre>')
def perfil_usuario(nombre):
    return f'Perfil de {nombre}'

# Visitar /usuario/ana → "Perfil de ana"
```

### Convertidores de Tipo

Puedes especificar el tipo de la variable con **convertidores**:

```python
@app.route('/articulo/<int:id>')
def ver_articulo(id):
    return f'Artículo #{id}'

@app.route('/precio/<float:valor>')
def mostrar_precio(valor):
    return f'Precio: ${valor:.2f}'

@app.route('/ruta/<path:subruta>')
def mostrar_ruta(subruta):
    return f'Subruta: {subruta}'
```

Los convertidores disponibles son:

| Convertidor | Descripción                                  |
|-------------|----------------------------------------------|
| `string`    | Texto sin barras (por defecto)               |
| `int`       | Enteros positivos                            |
| `float`     | Números de punto flotante                    |
| `path`      | Como `string`, pero acepta barras `/`        |
| `uuid`      | Cadenas UUID                                 |

### Múltiples Variables

Puedes usar varias variables en una misma ruta:

```python
@app.route('/curso/<categoria>/<int:leccion_id>')
def ver_leccion(categoria, leccion_id):
    return f'Categoría: {categoria}, Lección: {leccion_id}'

# /curso/python/5 → "Categoría: python, Lección: 5"
```

## Trailing Slash (Barra Final)

Flask diferencia entre rutas con y sin barra final:

```python
@app.route('/about/')
def about():
    # Accesible como /about y /about/
    # Flask redirige /about → /about/
    return 'Acerca de'

@app.route('/info')
def info():
    # Solo accesible como /info
    # /info/ devuelve un error 404
    return 'Información'
```

La convención es usar barra final (`/about/`) para páginas y sin barra (`/api/datos`) para endpoints API.

## Generación de URLs con `url_for()`

En lugar de escribir URLs manualmente, usa `url_for()` para generarlas dinámicamente a partir del nombre de la función de vista:

```python
from flask import url_for

@app.route('/')
def inicio():
    # Genera la URL para la función 'perfil_usuario' con nombre='juan'
    url_perfil = url_for('perfil_usuario', nombre='juan')
    # url_perfil = '/usuario/juan'
    return f'<a href="{url_perfil}">Ver perfil de Juan</a>'

@app.route('/usuario/<nombre>')
def perfil_usuario(nombre):
    url_inicio = url_for('inicio')
    return f'Perfil de {nombre} | <a href="{url_inicio}">Volver</a>'
```

Ventajas de `url_for()`:
- Si cambias la URL de una ruta, los enlaces se actualizan automáticamente.
- Maneja correctamente caracteres especiales.
- Genera URLs absolutas cuando se necesita: `url_for('inicio', _external=True)`.

Parámetros adicionales que no correspondan a variables de ruta se añaden como query string:

```python
url_for('buscar', q='flask', pagina=2)
# Resultado: /buscar?q=flask&pagina=2
```

## Redirecciones

Para redirigir al usuario a otra página, usa `redirect()`:

```python
from flask import redirect, url_for

@app.route('/vieja-pagina')
def pagina_antigua():
    return redirect(url_for('inicio'))

@app.route('/admin')
def admin():
    usuario_autenticado = False
    if not usuario_autenticado:
        return redirect(url_for('login'))
    return 'Panel de Administración'
```

Por defecto, `redirect()` emite un código **302** (redirección temporal). Puedes cambiarlo:

```python
# Redirección permanente (301)
return redirect(url_for('inicio'), code=301)
```

## Manejo de Errores con `abort()`

Usa `abort()` para interrumpir una solicitud y devolver un código de error HTTP:

```python
from flask import abort

@app.route('/usuario/<int:id>')
def obtener_usuario(id):
    usuarios = {1: 'Ana', 2: 'Carlos', 3: 'María'}
    if id not in usuarios:
        abort(404)  # No encontrado
    return f'Usuario: {usuarios[id]}'
```

### Personalizar Páginas de Error

Puedes crear páginas de error personalizadas con `@app.errorhandler()`:

```python
@app.errorhandler(404)
def pagina_no_encontrada(error):
    return '<h1>404 - Página no encontrada</h1><p>Lo sentimos, la página que buscas no existe.</p>', 404

@app.errorhandler(500)
def error_interno(error):
    return '<h1>500 - Error interno</h1><p>Algo salió mal en el servidor.</p>', 500

@app.errorhandler(403)
def acceso_prohibido(error):
    return '<h1>403 - Acceso prohibido</h1><p>No tienes permiso para acceder.</p>', 403
```

## Respuestas Personalizadas

Flask permite personalizar las respuestas completamente usando `make_response()`:

```python
from flask import make_response

@app.route('/datos')
def datos():
    respuesta = make_response('Contenido de la respuesta')
    respuesta.headers['X-Custom-Header'] = 'MiValor'
    respuesta.status_code = 200
    respuesta.set_cookie('visita', 'true', max_age=3600)
    return respuesta
```

También puedes devolver tuplas con el código de estado y los encabezados:

```python
@app.route('/creado')
def recurso_creado():
    return 'Recurso creado exitosamente', 201, {'X-Info': 'nuevo'}
```

## Ejercicio Práctico

Crea una aplicación Flask que simule una tienda en línea con las siguientes rutas:

1. **`/`** — Página principal con enlaces a categorías.
2. **`/categoria/<nombre>`** — Muestra productos de una categoría. Solo acepta: `electronica`, `ropa`, `hogar`. Si la categoría no existe, devuelve un error 404 personalizado.
3. **`/producto/<int:id>`** — Detalle de un producto por ID (usa un diccionario simulado). Si el ID no existe, devuelve 404.
4. **`/buscar`** — Acepta el parámetro `q` en la query string y muestra qué se buscó.
5. **`/viejo-catalogo`** — Redirige a la página principal.

Requisitos:
- Usa `url_for()` para generar todos los enlaces.
- Personaliza la página de error 404.
- Acepta GET y POST en la ruta de búsqueda.

```python
from flask import Flask, redirect, url_for, abort, request

app = Flask(__name__)

CATEGORIAS = ['electronica', 'ropa', 'hogar']
PRODUCTOS = {
    1: {'nombre': 'Laptop', 'precio': 999.99, 'categoria': 'electronica'},
    2: {'nombre': 'Camiseta', 'precio': 19.99, 'categoria': 'ropa'},
    3: {'nombre': 'Lámpara', 'precio': 45.00, 'categoria': 'hogar'},
}

@app.route('/')
def inicio():
    enlaces = ''.join(
        f'<li><a href="{url_for("ver_categoria", nombre=c)}">{c.title()}</a></li>'
        for c in CATEGORIAS
    )
    return f'<h1>Tienda</h1><ul>{enlaces}</ul>'

@app.route('/categoria/<nombre>')
def ver_categoria(nombre):
    if nombre not in CATEGORIAS:
        abort(404)
    items = [p for p in PRODUCTOS.values() if p['categoria'] == nombre]
    lista = ''.join(f"<li>{p['nombre']} - ${p['precio']}</li>" for p in items)
    return f'<h1>{nombre.title()}</h1><ul>{lista}</ul>'

@app.route('/producto/<int:id>')
def ver_producto(id):
    if id not in PRODUCTOS:
        abort(404)
    p = PRODUCTOS[id]
    return f"<h1>{p['nombre']}</h1><p>Precio: ${p['precio']}</p>"

@app.route('/buscar', methods=['GET', 'POST'])
def buscar():
    q = request.args.get('q', '') if request.method == 'GET' else request.form.get('q', '')
    return f'<h1>Resultados para: {q}</h1>'

@app.route('/viejo-catalogo')
def viejo_catalogo():
    return redirect(url_for('inicio'), code=301)

@app.errorhandler(404)
def pagina_no_encontrada(e):
    return f'<h1>404</h1><p>No encontrado.</p><a href="{url_for("inicio")}">Volver</a>', 404

if __name__ == '__main__':
    app.run(debug=True)
```

## Resumen

En esta lección dominaste el sistema de enrutamiento de Flask: aprendiste a definir rutas con `@app.route()`, manejar diferentes métodos HTTP, capturar variables dinámicas con convertidores de tipo, generar URLs seguras con `url_for()`, redirigir usuarios con `redirect()` y manejar errores elegantemente con `abort()` y `@app.errorhandler()`. Estos conceptos son la base sobre la que construirás aplicaciones web robustas con Flask.
