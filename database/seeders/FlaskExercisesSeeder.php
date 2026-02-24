<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FlaskExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'flask-backend')->first();

        if (! $course) {
            $this->command->warn('Flask course not found. Run CourseSeeder + FlaskLessonSeeder first.');
            return;
        }

        $lessons = Lesson::where('course_id', $course->id)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('sort_order');

        $exercises = $this->exercises($lessons);
        $now = now();

        foreach ($exercises as $ex) {
            DB::table('lesson_exercises')->updateOrInsert(
                ['lesson_id' => $ex['lesson_id']],
                array_merge($ex, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        $this->command->info('Flask exercises seeded: ' . count($exercises) . ' exercises.');
    }

    private function exercises(\Illuminate\Support\Collection $lessons): array
    {
        $ex = [];

        // ── 1 · Introducción a Flask ────────────────────────────────
        if ($l = $lessons->get(1)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Primera aplicación Flask',
            'language'     => 'python',
            'description'  => <<<'MD'
Simula la creación de una app Flask básica.

```python
def create_app_config() -> dict:
    """Retorna dict de configuración:
    {'DEBUG': True, 'SECRET_KEY': 'dev-secret', 'TESTING': False}."""

def parse_route(route_str: str) -> dict:
    """Parsea string de ruta tipo '/users/<int:id>/posts'.
    Retorna {'path': '/users/<int:id>/posts',
             'params': [{'name': 'id', 'type': 'int'}],
             'segments': ['users', '<int:id>', 'posts']}."""

def http_response(status: int, body: str, content_type: str = 'text/html') -> dict:
    """Construye un dict de respuesta HTTP:
    {'status': status, 'body': body, 'headers': {'Content-Type': content_type}}."""
```
MD,
            'starter_code' => <<<'PYTHON'
def create_app_config() -> dict:
    """Retorna dict de configuración Flask."""
    pass


def parse_route(route_str: str) -> dict:
    """Parsea string de ruta con parámetros."""
    pass


def http_response(status: int, body: str, content_type: str = 'text/html') -> dict:
    """Construye dict de respuesta HTTP."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import re


def create_app_config() -> dict:
    """Retorna dict de configuración Flask."""
    return {'DEBUG': True, 'SECRET_KEY': 'dev-secret', 'TESTING': False}


def parse_route(route_str: str) -> dict:
    """Parsea string de ruta con parámetros."""
    segments = [s for s in route_str.strip('/').split('/') if s]
    params = []
    for seg in segments:
        match = re.match(r'<(\w+):(\w+)>', seg)
        if match:
            params.append({'name': match.group(2), 'type': match.group(1)})
        else:
            match = re.match(r'<(\w+)>', seg)
            if match:
                params.append({'name': match.group(1), 'type': 'string'})
    return {'path': route_str, 'params': params, 'segments': segments}


def http_response(status: int, body: str, content_type: str = 'text/html') -> dict:
    """Construye dict de respuesta HTTP."""
    return {
        'status': status,
        'body': body,
        'headers': {'Content-Type': content_type},
    }
PYTHON,
        ];

        // ── 2 · Rutas y Vistas ──────────────────────────────────────
        if ($l = $lessons->get(2)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mini sistema de rutas',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un mini enrutador estilo Flask.

```python
class FlaskRouter:
    def __init__(self):
        self.routes = {}

    def route(self, path: str, methods: list[str] = None):
        """Decorador que registra una función para path y methods.
        Por defecto methods=['GET']."""

    def resolve(self, path: str, method: str = 'GET') -> dict | None:
        """Busca ruta. Retorna {'handler': nombre_func, 'method': method} o None."""

    def list_routes(self) -> list[dict]:
        """Retorna lista de {'path': str, 'methods': list[str], 'handler': str}."""
```
MD,
            'starter_code' => <<<'PYTHON'
class FlaskRouter:
    def __init__(self):
        self.routes = {}

    def route(self, path: str, methods: list[str] = None):
        """Decorador que registra una función para path y methods."""
        pass

    def resolve(self, path: str, method: str = 'GET') -> dict | None:
        """Busca ruta registrada."""
        pass

    def list_routes(self) -> list[dict]:
        """Lista todas las rutas."""
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class FlaskRouter:
    def __init__(self):
        self.routes = {}

    def route(self, path: str, methods: list[str] = None):
        """Decorador que registra una función para path y methods."""
        if methods is None:
            methods = ['GET']

        def decorator(func):
            self.routes[path] = {
                'handler': func.__name__,
                'methods': [m.upper() for m in methods],
                'func': func,
            }
            return func
        return decorator

    def resolve(self, path: str, method: str = 'GET') -> dict | None:
        """Busca ruta registrada."""
        route = self.routes.get(path)
        if route and method.upper() in route['methods']:
            return {'handler': route['handler'], 'method': method.upper()}
        return None

    def list_routes(self) -> list[dict]:
        """Lista todas las rutas."""
        return [
            {'path': path, 'methods': info['methods'], 'handler': info['handler']}
            for path, info in self.routes.items()
        ]
PYTHON,
        ];

        // ── 3 · Templates Jinja2 ───────────────────────────────────
        if ($l = $lessons->get(3)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Motor de plantillas simple',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un mini motor de plantillas.

```python
def render_template(template: str, context: dict) -> str:
    """Reemplaza {{ variable }} con valores del contexto.
    Variables no encontradas se dejan como están."""

def render_list(template: str, items: list[dict]) -> str:
    """Renderiza template para cada item y los une con '\\n'."""

def build_html_page(title: str, body: str, css_files: list[str] = None) -> str:
    """Genera HTML básico con <!DOCTYPE html>, <head> con title y links CSS,
    y <body> con el contenido."""
```
MD,
            'starter_code' => <<<'PYTHON'
def render_template(template: str, context: dict) -> str:
    """Reemplaza {{ variable }} con valores del contexto."""
    pass


def render_list(template: str, items: list[dict]) -> str:
    """Renderiza template para cada item, une con newline."""
    pass


def build_html_page(title: str, body: str, css_files: list[str] = None) -> str:
    """Genera HTML básico con head y body."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import re


def render_template(template: str, context: dict) -> str:
    """Reemplaza {{ variable }} con valores del contexto."""
    def replacer(match):
        key = match.group(1).strip()
        return str(context.get(key, match.group(0)))
    return re.sub(r'\{\{\s*(\w+)\s*\}\}', replacer, template)


def render_list(template: str, items: list[dict]) -> str:
    """Renderiza template para cada item, une con newline."""
    return '\n'.join(render_template(template, item) for item in items)


def build_html_page(title: str, body: str, css_files: list[str] = None) -> str:
    """Genera HTML básico con head y body."""
    css_links = ''
    if css_files:
        css_links = '\n'.join(
            f'<link rel="stylesheet" href="{f}">' for f in css_files
        )
        css_links = '\n' + css_links
    return f"""<!DOCTYPE html>
<html>
<head>
<title>{title}</title>{css_links}
</head>
<body>
{body}
</body>
</html>"""
PYTHON,
        ];

        // ── 4 · Formularios y Request ───────────────────────────────
        if ($l = $lessons->get(4)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Validación de formularios',
            'language'     => 'python',
            'description'  => <<<'MD'
Simula validación de formularios al estilo Flask.

```python
class FormValidator:
    def __init__(self, rules: dict[str, list[str]]):
        """rules: {'field': ['required', 'min:3', 'max:50', 'email']}"""

    def validate(self, data: dict) -> dict:
        """Retorna {'valid': bool, 'errors': {field: [mensajes]}}."""

def parse_query_string(qs: str) -> dict[str, str]:
    """Parsea 'key1=val1&key2=val2' en dict. Decodifica %20 como espacio."""

def parse_multipart_field(field: str) -> dict:
    """Parsea 'name="file"; filename="photo.jpg"' y retorna
    {'name': 'file', 'filename': 'photo.jpg'}."""
```
MD,
            'starter_code' => <<<'PYTHON'
class FormValidator:
    def __init__(self, rules: dict[str, list[str]]):
        """rules: {'field': ['required', 'min:3', 'max:50', 'email']}"""
        pass

    def validate(self, data: dict) -> dict:
        """Retorna {'valid': bool, 'errors': {field: [msgs]}}."""
        pass


def parse_query_string(qs: str) -> dict[str, str]:
    """Parsea query string en dict."""
    pass


def parse_multipart_field(field: str) -> dict:
    """Parsea campo multipart en dict."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import re


class FormValidator:
    def __init__(self, rules: dict[str, list[str]]):
        """rules: {'field': ['required', 'min:3', 'max:50', 'email']}"""
        self.rules = rules

    def validate(self, data: dict) -> dict:
        """Retorna {'valid': bool, 'errors': {field: [msgs]}}."""
        errors = {}
        for field, rules in self.rules.items():
            field_errors = []
            value = data.get(field, '')
            for rule in rules:
                if rule == 'required' and not value:
                    field_errors.append(f'{field} es requerido')
                elif rule.startswith('min:'):
                    min_len = int(rule.split(':')[1])
                    if value and len(str(value)) < min_len:
                        field_errors.append(f'{field} mínimo {min_len} caracteres')
                elif rule.startswith('max:'):
                    max_len = int(rule.split(':')[1])
                    if value and len(str(value)) > max_len:
                        field_errors.append(f'{field} máximo {max_len} caracteres')
                elif rule == 'email':
                    if value and not re.match(r'^[\w.+-]+@[\w.-]+\.\w+$', str(value)):
                        field_errors.append(f'{field} no es un email válido')
            if field_errors:
                errors[field] = field_errors
        return {'valid': len(errors) == 0, 'errors': errors}


def parse_query_string(qs: str) -> dict[str, str]:
    """Parsea query string en dict."""
    if not qs:
        return {}
    result = {}
    for pair in qs.split('&'):
        if '=' in pair:
            key, value = pair.split('=', 1)
            result[key] = value.replace('%20', ' ')
    return result


def parse_multipart_field(field: str) -> dict:
    """Parsea campo multipart en dict."""
    result = {}
    parts = field.split(';')
    for part in parts:
        part = part.strip()
        if '=' in part:
            key, value = part.split('=', 1)
            result[key.strip()] = value.strip().strip('"')
    return result
PYTHON,
        ];

        // ── 5 · SQLAlchemy con Flask ────────────────────────────────
        if ($l = $lessons->get(5)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulador de ORM',
            'language'     => 'python',
            'description'  => <<<'MD'
Simula operaciones ORM estilo SQLAlchemy.

```python
class MiniORM:
    def __init__(self):
        self.tables = {}  # {'users': [{'id': 1, ...}, ...]}

    def create_table(self, name: str, columns: list[str]) -> None:
        """Crea tabla con columnas especificadas. 'id' se agrega automáticamente."""

    def insert(self, table: str, **data) -> dict:
        """Inserta registro, auto-genera id. Retorna registro con id."""

    def select(self, table: str, **filters) -> list[dict]:
        """Filtra registros. Sin filtros retorna todos."""

    def update(self, table: str, record_id: int, **data) -> dict | None:
        """Actualiza registro por id. Retorna record actualizado o None."""

    def delete(self, table: str, record_id: int) -> bool:
        """Elimina registro por id. Retorna True si se eliminó."""
```
MD,
            'starter_code' => <<<'PYTHON'
class MiniORM:
    def __init__(self):
        self.tables = {}

    def create_table(self, name: str, columns: list[str]) -> None:
        pass

    def insert(self, table: str, **data) -> dict:
        pass

    def select(self, table: str, **filters) -> list[dict]:
        pass

    def update(self, table: str, record_id: int, **data) -> dict | None:
        pass

    def delete(self, table: str, record_id: int) -> bool:
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class MiniORM:
    def __init__(self):
        self.tables = {}
        self._counters = {}

    def create_table(self, name: str, columns: list[str]) -> None:
        if name not in self.tables:
            self.tables[name] = []
            self._counters[name] = 0
            if 'id' not in columns:
                columns.insert(0, 'id')

    def insert(self, table: str, **data) -> dict:
        self._counters[table] = self._counters.get(table, 0) + 1
        record = {'id': self._counters[table], **data}
        self.tables[table].append(record)
        return record

    def select(self, table: str, **filters) -> list[dict]:
        records = self.tables.get(table, [])
        if not filters:
            return list(records)
        return [
            r for r in records
            if all(r.get(k) == v for k, v in filters.items())
        ]

    def update(self, table: str, record_id: int, **data) -> dict | None:
        for record in self.tables.get(table, []):
            if record['id'] == record_id:
                record.update(data)
                return record
        return None

    def delete(self, table: str, record_id: int) -> bool:
        records = self.tables.get(table, [])
        for i, record in enumerate(records):
            if record['id'] == record_id:
                records.pop(i)
                return True
        return False
PYTHON,
        ];

        // ── 6 · Migraciones con Alembic ────────────────────────────
        if ($l = $lessons->get(6)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulador de migraciones',
            'language'     => 'python',
            'description'  => <<<'MD'
Simula un sistema de migraciones de base de datos.

```python
class MigrationManager:
    def __init__(self):
        self.migrations = []  # Lista de migraciones registradas
        self.applied = []     # Lista de IDs aplicados

    def register(self, migration_id: str, up_description: str, down_description: str) -> None:
        """Registra migración con id, descripción up y down."""

    def migrate(self, target_id: str = None) -> list[str]:
        """Aplica migraciones pendientes hasta target_id (inclusive).
        Sin target aplica todas. Retorna lista de IDs aplicados."""

    def rollback(self, steps: int = 1) -> list[str]:
        """Revierte las últimas N migraciones. Retorna IDs revertidos."""

    def status(self) -> list[dict]:
        """Retorna lista de {'id': str, 'applied': bool} para cada migración."""
```
MD,
            'starter_code' => <<<'PYTHON'
class MigrationManager:
    def __init__(self):
        self.migrations = []
        self.applied = []

    def register(self, migration_id: str, up_description: str, down_description: str) -> None:
        pass

    def migrate(self, target_id: str = None) -> list[str]:
        pass

    def rollback(self, steps: int = 1) -> list[str]:
        pass

    def status(self) -> list[dict]:
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class MigrationManager:
    def __init__(self):
        self.migrations = []
        self.applied = []

    def register(self, migration_id: str, up_description: str, down_description: str) -> None:
        self.migrations.append({
            'id': migration_id,
            'up': up_description,
            'down': down_description,
        })

    def migrate(self, target_id: str = None) -> list[str]:
        applied_now = []
        for m in self.migrations:
            if m['id'] in self.applied:
                continue
            self.applied.append(m['id'])
            applied_now.append(m['id'])
            if target_id and m['id'] == target_id:
                break
        return applied_now

    def rollback(self, steps: int = 1) -> list[str]:
        rolled_back = []
        for _ in range(min(steps, len(self.applied))):
            mid = self.applied.pop()
            rolled_back.append(mid)
        return rolled_back

    def status(self) -> list[dict]:
        return [
            {'id': m['id'], 'applied': m['id'] in self.applied}
            for m in self.migrations
        ]
PYTHON,
        ];

        // ── 7 · Blueprints ─────────────────────────────────────────
        if ($l = $lessons->get(7)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulador de Blueprints',
            'language'     => 'python',
            'description'  => <<<'MD'
Simula el sistema de Blueprints de Flask.

```python
class Blueprint:
    def __init__(self, name: str, url_prefix: str = ''):
        self.name = name
        self.url_prefix = url_prefix
        self.routes = []

    def route(self, path: str, methods: list[str] = None):
        """Decorador que registra ruta en el blueprint."""

class FlaskApp:
    def __init__(self):
        self.blueprints = {}
        self.routes = {}

    def register_blueprint(self, bp: Blueprint) -> None:
        """Registra un blueprint y sus rutas con el prefijo."""

    def resolve(self, path: str, method: str = 'GET') -> dict | None:
        """Resuelve path+method. Retorna {'blueprint': name, 'handler': name, 'method': str} o None."""

    def list_all_routes(self) -> list[dict]:
        """Lista todas las rutas registradas de todos los blueprints."""
```
MD,
            'starter_code' => <<<'PYTHON'
class Blueprint:
    def __init__(self, name: str, url_prefix: str = ''):
        self.name = name
        self.url_prefix = url_prefix
        self.routes = []

    def route(self, path: str, methods: list[str] = None):
        pass


class FlaskApp:
    def __init__(self):
        self.blueprints = {}
        self.routes = {}

    def register_blueprint(self, bp: Blueprint) -> None:
        pass

    def resolve(self, path: str, method: str = 'GET') -> dict | None:
        pass

    def list_all_routes(self) -> list[dict]:
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class Blueprint:
    def __init__(self, name: str, url_prefix: str = ''):
        self.name = name
        self.url_prefix = url_prefix
        self.routes = []

    def route(self, path: str, methods: list[str] = None):
        if methods is None:
            methods = ['GET']

        def decorator(func):
            self.routes.append({
                'path': path,
                'methods': [m.upper() for m in methods],
                'handler': func.__name__,
            })
            return func
        return decorator


class FlaskApp:
    def __init__(self):
        self.blueprints = {}
        self.routes = {}

    def register_blueprint(self, bp: Blueprint) -> None:
        self.blueprints[bp.name] = bp
        for route in bp.routes:
            full_path = bp.url_prefix.rstrip('/') + '/' + route['path'].lstrip('/')
            if full_path != '/':
                full_path = full_path.rstrip('/')
            self.routes[full_path] = {
                'blueprint': bp.name,
                'handler': route['handler'],
                'methods': route['methods'],
            }

    def resolve(self, path: str, method: str = 'GET') -> dict | None:
        route = self.routes.get(path)
        if route and method.upper() in route['methods']:
            return {
                'blueprint': route['blueprint'],
                'handler': route['handler'],
                'method': method.upper(),
            }
        return None

    def list_all_routes(self) -> list[dict]:
        return [
            {'path': path, 'blueprint': info['blueprint'],
             'handler': info['handler'], 'methods': info['methods']}
            for path, info in self.routes.items()
        ]
PYTHON,
        ];

        // ── 8 · Autenticación ───────────────────────────────────────
        if ($l = $lessons->get(8)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema de autenticación',
            'language'     => 'python',
            'description'  => <<<'MD'
Simula autenticación con hashing y sesiones.

```python
import hashlib

class AuthManager:
    def __init__(self):
        self.users = {}      # {username: {'password_hash': str, 'role': str}}
        self.sessions = {}   # {token: username}

    def register(self, username: str, password: str, role: str = 'user') -> bool:
        """Registra usuario. Guarda hash SHA-256 del password. Retorna False si ya existe."""

    def login(self, username: str, password: str) -> str | None:
        """Verifica credenciales. Retorna token (hash del username+timestamp simulado) o None."""

    def get_user(self, token: str) -> dict | None:
        """Retorna {'username': str, 'role': str} del usuario autenticado o None."""

    def logout(self, token: str) -> bool:
        """Elimina sesión. Retorna True si existía."""
```
MD,
            'starter_code' => <<<'PYTHON'
import hashlib


class AuthManager:
    def __init__(self):
        self.users = {}
        self.sessions = {}

    def register(self, username: str, password: str, role: str = 'user') -> bool:
        pass

    def login(self, username: str, password: str) -> str | None:
        pass

    def get_user(self, token: str) -> dict | None:
        pass

    def logout(self, token: str) -> bool:
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import hashlib


class AuthManager:
    def __init__(self):
        self.users = {}
        self.sessions = {}
        self._token_counter = 0

    def register(self, username: str, password: str, role: str = 'user') -> bool:
        if username in self.users:
            return False
        pw_hash = hashlib.sha256(password.encode()).hexdigest()
        self.users[username] = {'password_hash': pw_hash, 'role': role}
        return True

    def login(self, username: str, password: str) -> str | None:
        user = self.users.get(username)
        if not user:
            return None
        pw_hash = hashlib.sha256(password.encode()).hexdigest()
        if user['password_hash'] != pw_hash:
            return None
        self._token_counter += 1
        token = hashlib.sha256(f"{username}:{self._token_counter}".encode()).hexdigest()
        self.sessions[token] = username
        return token

    def get_user(self, token: str) -> dict | None:
        username = self.sessions.get(token)
        if not username or username not in self.users:
            return None
        return {'username': username, 'role': self.users[username]['role']}

    def logout(self, token: str) -> bool:
        if token in self.sessions:
            del self.sessions[token]
            return True
        return False
PYTHON,
        ];

        // ── 9 · API REST ───────────────────────────────────────────
        if ($l = $lessons->get(9)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulador de API RESTful',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un controlador REST completo.

```python
import json

class RESTController:
    def __init__(self):
        self.items = []
        self._next_id = 1

    def index(self) -> tuple[int, str]:
        """GET /items — Retorna (200, json_array_de_items)."""

    def show(self, item_id: int) -> tuple[int, str]:
        """GET /items/:id — Retorna (200, json_item) o (404, json_error)."""

    def create(self, data: dict) -> tuple[int, str]:
        """POST /items — Crea item con auto-id. Retorna (201, json_item)."""

    def update(self, item_id: int, data: dict) -> tuple[int, str]:
        """PUT /items/:id — Actualiza. Retorna (200, json_item) o (404, json_error)."""

    def delete(self, item_id: int) -> tuple[int, str]:
        """DELETE /items/:id — Elimina. Retorna (204, '') o (404, json_error)."""
```
MD,
            'starter_code' => <<<'PYTHON'
import json


class RESTController:
    def __init__(self):
        self.items = []
        self._next_id = 1

    def index(self) -> tuple[int, str]:
        pass

    def show(self, item_id: int) -> tuple[int, str]:
        pass

    def create(self, data: dict) -> tuple[int, str]:
        pass

    def update(self, item_id: int, data: dict) -> tuple[int, str]:
        pass

    def delete(self, item_id: int) -> tuple[int, str]:
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import json


class RESTController:
    def __init__(self):
        self.items = []
        self._next_id = 1

    def _find(self, item_id: int):
        for i, item in enumerate(self.items):
            if item['id'] == item_id:
                return i, item
        return None, None

    def index(self) -> tuple[int, str]:
        return (200, json.dumps(self.items))

    def show(self, item_id: int) -> tuple[int, str]:
        _, item = self._find(item_id)
        if item:
            return (200, json.dumps(item))
        return (404, json.dumps({'error': 'Not found'}))

    def create(self, data: dict) -> tuple[int, str]:
        item = {'id': self._next_id, **data}
        self._next_id += 1
        self.items.append(item)
        return (201, json.dumps(item))

    def update(self, item_id: int, data: dict) -> tuple[int, str]:
        idx, item = self._find(item_id)
        if item is None:
            return (404, json.dumps({'error': 'Not found'}))
        self.items[idx].update(data)
        return (200, json.dumps(self.items[idx]))

    def delete(self, item_id: int) -> tuple[int, str]:
        idx, item = self._find(item_id)
        if item is None:
            return (404, json.dumps({'error': 'Not found'}))
        self.items.pop(idx)
        return (204, '')
PYTHON,
        ];

        // ── 10 · Middleware y Hooks ─────────────────────────────────
        if ($l = $lessons->get(10)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Pipeline de middleware',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un sistema de middleware.

```python
class MiddlewarePipeline:
    def __init__(self):
        self.middlewares = []

    def use(self, middleware_fn) -> None:
        """Agrega middleware. Cada middleware recibe (request, next_fn) y llama next_fn(request)."""

    def execute(self, request: dict) -> dict:
        """Ejecuta la cadena de middlewares. Retorna el request final modificado."""

def logging_middleware(request: dict, next_fn) -> dict:
    """Middleware que agrega 'logged': True al request."""

def auth_middleware(request: dict, next_fn) -> dict:
    """Si request tiene 'token', agrega 'authenticated': True y continúa.
    Si no, retorna {'error': 'Unauthorized', 'status': 401}."""
```
MD,
            'starter_code' => <<<'PYTHON'
class MiddlewarePipeline:
    def __init__(self):
        self.middlewares = []

    def use(self, middleware_fn) -> None:
        pass

    def execute(self, request: dict) -> dict:
        pass


def logging_middleware(request: dict, next_fn) -> dict:
    pass


def auth_middleware(request: dict, next_fn) -> dict:
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class MiddlewarePipeline:
    def __init__(self):
        self.middlewares = []

    def use(self, middleware_fn) -> None:
        self.middlewares.append(middleware_fn)

    def execute(self, request: dict) -> dict:
        def create_chain(index):
            if index >= len(self.middlewares):
                return lambda req: req
            return lambda req: self.middlewares[index](req, create_chain(index + 1))
        if not self.middlewares:
            return request
        return create_chain(0)(request)


def logging_middleware(request: dict, next_fn) -> dict:
    request['logged'] = True
    return next_fn(request)


def auth_middleware(request: dict, next_fn) -> dict:
    if 'token' in request:
        request['authenticated'] = True
        return next_fn(request)
    return {'error': 'Unauthorized', 'status': 401}
PYTHON,
        ];

        // ── 11 · Testing en Flask ───────────────────────────────────
        if ($l = $lessons->get(11)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Tests para una mini API',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa funciones testables y sus tests.

```python
def validate_json_body(body: str) -> tuple[bool, dict | str]:
    """Valida JSON. Retorna (True, parsed_dict) o (False, error_message)."""

def paginate(items: list, page: int, per_page: int = 10) -> dict:
    """Retorna {'items': [...], 'page': int, 'per_page': int,
    'total': int, 'pages': int}."""

def test_validate_json_body():
    """Al menos 4 asserts: JSON válido, inválido, vacío, nested."""

def test_paginate():
    """Al menos 4 asserts: primera página, última, fuera de rango, per_page custom."""
```
MD,
            'starter_code' => <<<'PYTHON'
import json


def validate_json_body(body: str) -> tuple[bool, dict | str]:
    """Valida JSON. Retorna (True, dict) o (False, error)."""
    pass


def paginate(items: list, page: int, per_page: int = 10) -> dict:
    """Pagina lista de items."""
    pass


def test_validate_json_body():
    """Tests para validate_json_body."""
    pass


def test_paginate():
    """Tests para paginate."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import json
import math


def validate_json_body(body: str) -> tuple[bool, dict | str]:
    """Valida JSON. Retorna (True, dict) o (False, error)."""
    try:
        data = json.loads(body)
        if not isinstance(data, dict):
            return (False, 'Expected JSON object')
        return (True, data)
    except json.JSONDecodeError as e:
        return (False, str(e))


def paginate(items: list, page: int, per_page: int = 10) -> dict:
    """Pagina lista de items."""
    total = len(items)
    pages = math.ceil(total / per_page) if per_page > 0 else 0
    start = (page - 1) * per_page
    end = start + per_page
    return {
        'items': items[start:end],
        'page': page,
        'per_page': per_page,
        'total': total,
        'pages': pages,
    }


def test_validate_json_body():
    """Tests para validate_json_body."""
    ok, data = validate_json_body('{"name": "test"}')
    assert ok is True
    assert data == {'name': 'test'}

    ok, err = validate_json_body('invalid')
    assert ok is False

    ok, err = validate_json_body('')
    assert ok is False

    ok, data = validate_json_body('{"a": {"b": 1}}')
    assert ok is True
    assert data['a']['b'] == 1


def test_paginate():
    """Tests para paginate."""
    items = list(range(25))
    result = paginate(items, 1, 10)
    assert len(result['items']) == 10
    assert result['pages'] == 3

    result = paginate(items, 3, 10)
    assert len(result['items']) == 5

    result = paginate(items, 5, 10)
    assert len(result['items']) == 0

    result = paginate(items, 1, 5)
    assert result['pages'] == 5
PYTHON,
        ];

        // ── 12 · Archivos y Uploads ─────────────────────────────────
        if ($l = $lessons->get(12)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Gestor de archivos',
            'language'     => 'python',
            'description'  => <<<'MD'
Simula gestión de archivos y uploads.

```python
class FileManager:
    ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'gif', 'pdf'}
    MAX_SIZE_MB = 5

    def validate_upload(self, filename: str, size_bytes: int) -> dict:
        """Retorna {'valid': bool, 'errors': [str]}. Valida extensión y tamaño."""

    def generate_safe_filename(self, filename: str) -> str:
        """Retorna filename seguro: minúsculas, espacios por guiones bajos,
        solo alfanuméricos/guiones/puntos."""

    def organize_by_date(self, files: list[dict]) -> dict[str, list[str]]:
        """Recibe [{'name': str, 'date': 'YYYY-MM-DD'}].
        Retorna {date: [nombres]} agrupados por fecha, ordenados."""
```
MD,
            'starter_code' => <<<'PYTHON'
class FileManager:
    ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'gif', 'pdf'}
    MAX_SIZE_MB = 5

    def validate_upload(self, filename: str, size_bytes: int) -> dict:
        pass

    def generate_safe_filename(self, filename: str) -> str:
        pass

    def organize_by_date(self, files: list[dict]) -> dict[str, list[str]]:
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import re


class FileManager:
    ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'gif', 'pdf'}
    MAX_SIZE_MB = 5

    def validate_upload(self, filename: str, size_bytes: int) -> dict:
        errors = []
        ext = filename.rsplit('.', 1)[-1].lower() if '.' in filename else ''
        if ext not in self.ALLOWED_EXTENSIONS:
            errors.append(f'Extensión .{ext} no permitida')
        size_mb = size_bytes / (1024 * 1024)
        if size_mb > self.MAX_SIZE_MB:
            errors.append(f'Archivo excede {self.MAX_SIZE_MB}MB')
        return {'valid': len(errors) == 0, 'errors': errors}

    def generate_safe_filename(self, filename: str) -> str:
        name = filename.lower().strip()
        name = name.replace(' ', '_')
        name = re.sub(r'[^a-z0-9._-]', '', name)
        return name

    def organize_by_date(self, files: list[dict]) -> dict[str, list[str]]:
        grouped = {}
        for f in files:
            date = f['date']
            grouped.setdefault(date, []).append(f['name'])
        return dict(sorted(grouped.items()))
PYTHON,
        ];

        // ── 13 · WebSockets ────────────────────────────────────────
        if ($l = $lessons->get(13)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulador de WebSocket',
            'language'     => 'python',
            'description'  => <<<'MD'
Simula comunicación WebSocket.

```python
class WebSocketRoom:
    def __init__(self, name: str):
        self.name = name
        self.clients = {}   # {client_id: str (username)}
        self.messages = []

    def join(self, client_id: str, username: str) -> dict:
        """Agrega cliente. Retorna {'event': 'join', 'user': username, 'room': name}."""

    def leave(self, client_id: str) -> dict | None:
        """Remueve cliente. Retorna {'event': 'leave', 'user': username, 'room': name} o None."""

    def broadcast(self, client_id: str, message: str) -> dict | None:
        """Envía mensaje. Retorna {'event': 'message', 'user': username,
        'message': message, 'room': name} o None si el cliente no existe."""

    def get_history(self, limit: int = 10) -> list[dict]:
        """Retorna últimos N mensajes."""
```
MD,
            'starter_code' => <<<'PYTHON'
class WebSocketRoom:
    def __init__(self, name: str):
        self.name = name
        self.clients = {}
        self.messages = []

    def join(self, client_id: str, username: str) -> dict:
        pass

    def leave(self, client_id: str) -> dict | None:
        pass

    def broadcast(self, client_id: str, message: str) -> dict | None:
        pass

    def get_history(self, limit: int = 10) -> list[dict]:
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class WebSocketRoom:
    def __init__(self, name: str):
        self.name = name
        self.clients = {}
        self.messages = []

    def join(self, client_id: str, username: str) -> dict:
        self.clients[client_id] = username
        return {'event': 'join', 'user': username, 'room': self.name}

    def leave(self, client_id: str) -> dict | None:
        username = self.clients.pop(client_id, None)
        if username is None:
            return None
        return {'event': 'leave', 'user': username, 'room': self.name}

    def broadcast(self, client_id: str, message: str) -> dict | None:
        username = self.clients.get(client_id)
        if username is None:
            return None
        msg = {
            'event': 'message',
            'user': username,
            'message': message,
            'room': self.name,
        }
        self.messages.append(msg)
        return msg

    def get_history(self, limit: int = 10) -> list[dict]:
        return self.messages[-limit:]
PYTHON,
        ];

        // ── 14 · Celery y Tareas ───────────────────────────────────
        if ($l = $lessons->get(14)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulador de cola de tareas',
            'language'     => 'python',
            'description'  => <<<'MD'
Simula un sistema de tareas asíncronas estilo Celery.

```python
from enum import Enum

class TaskStatus(Enum):
    PENDING = 'pending'
    RUNNING = 'running'
    SUCCESS = 'success'
    FAILURE = 'failure'

class TaskQueue:
    def __init__(self):
        self.tasks = {}
        self._counter = 0

    def enqueue(self, func_name: str, *args) -> str:
        """Encola tarea. Retorna task_id string."""

    def process_next(self) -> dict | None:
        """Procesa la siguiente tarea PENDING: cambia a RUNNING, ejecuta (simula éxito),
        cambia a SUCCESS. Retorna info de la tarea o None si no hay pendientes."""

    def get_status(self, task_id: str) -> dict | None:
        """Retorna {'task_id': str, 'func': str, 'status': str, 'args': tuple}."""

    def get_stats(self) -> dict:
        """Retorna conteo por status: {'pending': N, 'running': N, 'success': N, 'failure': N}."""
```
MD,
            'starter_code' => <<<'PYTHON'
from enum import Enum


class TaskStatus(Enum):
    PENDING = 'pending'
    RUNNING = 'running'
    SUCCESS = 'success'
    FAILURE = 'failure'


class TaskQueue:
    def __init__(self):
        self.tasks = {}
        self._counter = 0

    def enqueue(self, func_name: str, *args) -> str:
        pass

    def process_next(self) -> dict | None:
        pass

    def get_status(self, task_id: str) -> dict | None:
        pass

    def get_stats(self) -> dict:
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
from enum import Enum


class TaskStatus(Enum):
    PENDING = 'pending'
    RUNNING = 'running'
    SUCCESS = 'success'
    FAILURE = 'failure'


class TaskQueue:
    def __init__(self):
        self.tasks = {}
        self._counter = 0

    def enqueue(self, func_name: str, *args) -> str:
        self._counter += 1
        task_id = f"task_{self._counter}"
        self.tasks[task_id] = {
            'task_id': task_id,
            'func': func_name,
            'args': args,
            'status': TaskStatus.PENDING,
        }
        return task_id

    def process_next(self) -> dict | None:
        for task_id, task in self.tasks.items():
            if task['status'] == TaskStatus.PENDING:
                task['status'] = TaskStatus.RUNNING
                task['status'] = TaskStatus.SUCCESS
                return {
                    'task_id': task_id,
                    'func': task['func'],
                    'status': task['status'].value,
                }
        return None

    def get_status(self, task_id: str) -> dict | None:
        task = self.tasks.get(task_id)
        if not task:
            return None
        return {
            'task_id': task['task_id'],
            'func': task['func'],
            'status': task['status'].value,
            'args': task['args'],
        }

    def get_stats(self) -> dict:
        stats = {s.value: 0 for s in TaskStatus}
        for task in self.tasks.values():
            stats[task['status'].value] += 1
        return stats
PYTHON,
        ];

        // ── 15 · Seguridad en Flask ─────────────────────────────────
        if ($l = $lessons->get(15)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Utilidades de seguridad',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa funciones de seguridad web.

```python
import hashlib, hmac, secrets

def generate_csrf_token(secret: str, session_id: str) -> str:
    """Genera token CSRF usando HMAC-SHA256 de session_id con secret."""

def verify_csrf_token(token: str, secret: str, session_id: str) -> bool:
    """Verifica que el token coincida con el esperado."""

def sanitize_html(text: str) -> str:
    """Escapa caracteres peligrosos: < > & \" ' """

def check_password_strength(password: str) -> dict:
    """Retorna {'score': int (0-4), 'feedback': [str]}.
    +1 por longitud >= 8, +1 por mayúscula, +1 por dígito, +1 por carácter especial."""
```
MD,
            'starter_code' => <<<'PYTHON'
import hashlib
import hmac
import secrets


def generate_csrf_token(secret: str, session_id: str) -> str:
    pass


def verify_csrf_token(token: str, secret: str, session_id: str) -> bool:
    pass


def sanitize_html(text: str) -> str:
    pass


def check_password_strength(password: str) -> dict:
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import hashlib
import hmac
import secrets
import re


def generate_csrf_token(secret: str, session_id: str) -> str:
    return hmac.new(secret.encode(), session_id.encode(), hashlib.sha256).hexdigest()


def verify_csrf_token(token: str, secret: str, session_id: str) -> bool:
    expected = generate_csrf_token(secret, session_id)
    return hmac.compare_digest(token, expected)


def sanitize_html(text: str) -> str:
    replacements = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#x27;',
    }
    for char, entity in replacements.items():
        text = text.replace(char, entity)
    return text


def check_password_strength(password: str) -> dict:
    score = 0
    feedback = []
    if len(password) >= 8:
        score += 1
    else:
        feedback.append('Mínimo 8 caracteres')
    if re.search(r'[A-Z]', password):
        score += 1
    else:
        feedback.append('Incluir al menos una mayúscula')
    if re.search(r'\d', password):
        score += 1
    else:
        feedback.append('Incluir al menos un dígito')
    if re.search(r'[!@#$%^&*(),.?":{}|<>]', password):
        score += 1
    else:
        feedback.append('Incluir al menos un carácter especial')
    return {'score': score, 'feedback': feedback}
PYTHON,
        ];

        // ── 16 · Caché y Performance ───────────────────────────────
        if ($l = $lessons->get(16)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema de caché',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un sistema de caché con TTL.

```python
import time

class Cache:
    def __init__(self, default_ttl: int = 300):
        self.store = {}
        self.default_ttl = default_ttl

    def set(self, key: str, value, ttl: int = None) -> None:
        """Guarda valor con TTL en segundos (usa default_ttl si None)."""

    def get(self, key: str, default=None):
        """Retorna valor si existe y no expiró, sino default."""

    def delete(self, key: str) -> bool:
        """Elimina clave. Retorna True si existía."""

    def clear_expired(self) -> int:
        """Elimina entries expirados. Retorna cantidad eliminada."""

    def stats(self) -> dict:
        """Retorna {'total': int, 'active': int, 'expired': int}."""
```
MD,
            'starter_code' => <<<'PYTHON'
import time


class Cache:
    def __init__(self, default_ttl: int = 300):
        self.store = {}
        self.default_ttl = default_ttl

    def set(self, key: str, value, ttl: int = None) -> None:
        pass

    def get(self, key: str, default=None):
        pass

    def delete(self, key: str) -> bool:
        pass

    def clear_expired(self) -> int:
        pass

    def stats(self) -> dict:
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import time


class Cache:
    def __init__(self, default_ttl: int = 300):
        self.store = {}
        self.default_ttl = default_ttl

    def set(self, key: str, value, ttl: int = None) -> None:
        if ttl is None:
            ttl = self.default_ttl
        self.store[key] = {
            'value': value,
            'expires_at': time.time() + ttl,
        }

    def get(self, key: str, default=None):
        entry = self.store.get(key)
        if entry is None:
            return default
        if time.time() > entry['expires_at']:
            return default
        return entry['value']

    def delete(self, key: str) -> bool:
        if key in self.store:
            del self.store[key]
            return True
        return False

    def clear_expired(self) -> int:
        now = time.time()
        expired = [k for k, v in self.store.items() if now > v['expires_at']]
        for k in expired:
            del self.store[k]
        return len(expired)

    def stats(self) -> dict:
        now = time.time()
        total = len(self.store)
        expired = sum(1 for v in self.store.values() if now > v['expires_at'])
        return {'total': total, 'active': total - expired, 'expired': expired}
PYTHON,
        ];

        // ── 17 · JWT y OAuth ───────────────────────────────────────
        if ($l = $lessons->get(17)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulador JWT',
            'language'     => 'python',
            'description'  => <<<'MD'
Simula la creación y verificación de JWT.

```python
import json, hashlib, base64

def base64url_encode(data: bytes) -> str:
    """Codifica bytes a base64url (sin padding)."""

def create_jwt(payload: dict, secret: str) -> str:
    """Crea JWT con header {'alg': 'HS256', 'typ': 'JWT'}.
    Formato: base64url(header).base64url(payload).signature
    Firma: HMAC-SHA256 de 'header.payload' con secret."""

def decode_jwt(token: str, secret: str) -> dict | None:
    """Decodifica y verifica JWT. Retorna payload dict o None si firma inválida."""
```
MD,
            'starter_code' => <<<'PYTHON'
import json
import hashlib
import hmac
import base64


def base64url_encode(data: bytes) -> str:
    """Codifica bytes a base64url (sin padding)."""
    pass


def create_jwt(payload: dict, secret: str) -> str:
    """Crea JWT simplificado con HMAC-SHA256."""
    pass


def decode_jwt(token: str, secret: str) -> dict | None:
    """Decodifica y verifica JWT. None si firma inválida."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import json
import hashlib
import hmac
import base64


def base64url_encode(data: bytes) -> str:
    """Codifica bytes a base64url (sin padding)."""
    return base64.urlsafe_b64encode(data).rstrip(b'=').decode('ascii')


def base64url_decode(s: str) -> bytes:
    padding = 4 - len(s) % 4
    s += '=' * padding
    return base64.urlsafe_b64decode(s)


def create_jwt(payload: dict, secret: str) -> str:
    """Crea JWT simplificado con HMAC-SHA256."""
    header = {'alg': 'HS256', 'typ': 'JWT'}
    h = base64url_encode(json.dumps(header).encode())
    p = base64url_encode(json.dumps(payload).encode())
    signing_input = f"{h}.{p}"
    sig = hmac.new(secret.encode(), signing_input.encode(), hashlib.sha256).digest()
    return f"{signing_input}.{base64url_encode(sig)}"


def decode_jwt(token: str, secret: str) -> dict | None:
    """Decodifica y verifica JWT. None si firma inválida."""
    parts = token.split('.')
    if len(parts) != 3:
        return None
    signing_input = f"{parts[0]}.{parts[1]}"
    expected_sig = hmac.new(secret.encode(), signing_input.encode(), hashlib.sha256).digest()
    actual_sig = base64url_decode(parts[2])
    if not hmac.compare_digest(expected_sig, actual_sig):
        return None
    payload = json.loads(base64url_decode(parts[1]))
    return payload
PYTHON,
        ];

        // ── 18 · Deploy en Flask ───────────────────────────────────
        if ($l = $lessons->get(18)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Configuración de deploy',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa utilidades de configuración para deploy.

```python
class DeployConfig:
    ENVIRONMENTS = {'development', 'staging', 'production'}

    def __init__(self, env: str = 'development'):
        """Valida env contra ENVIRONMENTS. Default: 'development'."""

    def get_config(self) -> dict:
        """Retorna config según entorno:
        development: DEBUG=True, LOG_LEVEL='DEBUG', DB='sqlite:///dev.db'
        staging: DEBUG=False, LOG_LEVEL='INFO', DB='postgresql://staging/db'
        production: DEBUG=False, LOG_LEVEL='WARNING', DB='postgresql://prod/db'"""

    def get_gunicorn_args(self, workers: int = None) -> list[str]:
        """Retorna args para gunicorn: ['--workers', N, '--bind', '0.0.0.0:PORT'].
        PORT: dev=5000, staging=8000, prod=8000. Workers default: dev=1, staging=2, prod=4."""

def generate_dockerfile(app_name: str, python_version: str = '3.12') -> str:
    """Genera Dockerfile string: FROM python:{version}-slim, WORKDIR, COPY, pip install, EXPOSE 8000, CMD gunicorn."""
```
MD,
            'starter_code' => <<<'PYTHON'
class DeployConfig:
    ENVIRONMENTS = {'development', 'staging', 'production'}

    def __init__(self, env: str = 'development'):
        pass

    def get_config(self) -> dict:
        pass

    def get_gunicorn_args(self, workers: int = None) -> list[str]:
        pass


def generate_dockerfile(app_name: str, python_version: str = '3.12') -> str:
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class DeployConfig:
    ENVIRONMENTS = {'development', 'staging', 'production'}

    def __init__(self, env: str = 'development'):
        if env not in self.ENVIRONMENTS:
            env = 'development'
        self.env = env

    def get_config(self) -> dict:
        configs = {
            'development': {'DEBUG': True, 'LOG_LEVEL': 'DEBUG', 'DB': 'sqlite:///dev.db'},
            'staging': {'DEBUG': False, 'LOG_LEVEL': 'INFO', 'DB': 'postgresql://staging/db'},
            'production': {'DEBUG': False, 'LOG_LEVEL': 'WARNING', 'DB': 'postgresql://prod/db'},
        }
        return configs[self.env]

    def get_gunicorn_args(self, workers: int = None) -> list[str]:
        defaults = {'development': (1, 5000), 'staging': (2, 8000), 'production': (4, 8000)}
        w_default, port = defaults[self.env]
        w = workers if workers is not None else w_default
        return ['--workers', str(w), '--bind', f'0.0.0.0:{port}']


def generate_dockerfile(app_name: str, python_version: str = '3.12') -> str:
    return f"""FROM python:{python_version}-slim
WORKDIR /app
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt
COPY . .
EXPOSE 8000
CMD ["gunicorn", "{app_name}:app", "--bind", "0.0.0.0:8000"]"""
PYTHON,
        ];

        // ── 19 · Preguntas de Entrevista ───────────────────────────
        if ($l = $lessons->get(19)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Retos de entrevista Flask',
            'language'     => 'python',
            'description'  => <<<'MD'
Resuelve preguntas clásicas de entrevista sobre Flask.

```python
def explain_flask_vs_django() -> dict:
    """Retorna dict con 'flask' y 'django', cada uno con lista de al menos 3 características."""

def implement_rate_limiter(max_requests: int, window_seconds: int):
    """Retorna función check(client_id) -> bool.
    True si el cliente no ha excedido max_requests en la ventana de tiempo.
    Usa dict interno con timestamps."""

def build_error_handlers() -> dict[int, str]:
    """Retorna dict {status_code: mensaje} para 400, 401, 403, 404, 500.
    Los mensajes deben ser descriptivos en español."""
```
MD,
            'starter_code' => <<<'PYTHON'
import time


def explain_flask_vs_django() -> dict:
    """Retorna comparación Flask vs Django."""
    pass


def implement_rate_limiter(max_requests: int, window_seconds: int):
    """Retorna función check(client_id) que implementa rate limiting."""
    pass


def build_error_handlers() -> dict[int, str]:
    """Retorna dict de handlers de error HTTP."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import time


def explain_flask_vs_django() -> dict:
    """Retorna comparación Flask vs Django."""
    return {
        'flask': [
            'Microframework ligero y minimalista',
            'Flexibilidad total para elegir componentes',
            'Ideal para APIs y microservicios',
            'Curva de aprendizaje más suave',
        ],
        'django': [
            'Framework fullstack con baterías incluidas',
            'ORM, admin, auth y forms integrados',
            'Ideal para aplicaciones complejas',
            'Convenciones fuertes (MVT)',
        ],
    }


def implement_rate_limiter(max_requests: int, window_seconds: int):
    """Retorna función check(client_id) que implementa rate limiting."""
    requests = {}

    def check(client_id: str) -> bool:
        now = time.time()
        if client_id not in requests:
            requests[client_id] = []
        requests[client_id] = [
            t for t in requests[client_id] if now - t < window_seconds
        ]
        if len(requests[client_id]) >= max_requests:
            return False
        requests[client_id].append(now)
        return True

    return check


def build_error_handlers() -> dict[int, str]:
    """Retorna dict de handlers de error HTTP."""
    return {
        400: 'Solicitud incorrecta: los datos enviados no son válidos',
        401: 'No autorizado: se requiere autenticación',
        403: 'Prohibido: no tienes permisos para acceder a este recurso',
        404: 'No encontrado: el recurso solicitado no existe',
        500: 'Error interno del servidor: algo salió mal',
    }
PYTHON,
        ];

        return $ex;
    }
}
