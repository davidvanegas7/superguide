<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DjangoExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'django-fullstack')->first();

        if (! $course) {
            $this->command->warn('Django course not found. Run CourseSeeder + DjangoLessonSeeder first.');
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

        $this->command->info('Django exercises seeded: ' . count($exercises) . ' exercises.');
    }

    private function exercises(\Illuminate\Support\Collection $lessons): array
    {
        $ex = [];

        // ── 1 · Introducción a Django ──────────────────────────────
        if ($l = $lessons->get(1)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Estructura de proyecto Django',
            'language'     => 'python',
            'description'  => <<<'MD'
Simula la estructura y configuración de un proyecto Django.

```python
def project_structure() -> dict:
    """Retorna dict con la estructura principal de un proyecto Django.
    Claves: 'manage.py', 'settings.py', 'urls.py', 'wsgi.py', 'asgi.py'
    Valores: descripción breve de cada archivo."""

def parse_settings(settings_str: str) -> dict:
    """Parsea un string de settings tipo 'DEBUG=True\nALLOWED_HOSTS=*\nDB=sqlite3'
    Retorna dict: {'DEBUG': True, 'ALLOWED_HOSTS': '*', 'DB': 'sqlite3'}.
    Convierte 'True'/'False' a bool."""

def manage_command(command: str) -> str:
    """Simula manage.py: recibe comando y retorna descripción.
    Comandos: runserver, migrate, makemigrations, createsuperuser,
    startapp, collectstatic, shell.
    Si no se reconoce: 'Comando desconocido: {command}'."""
```
MD,
            'starter_code' => <<<'PYTHON'
def project_structure() -> dict:
    # Retorna dict con archivos principales y su descripción
    pass

def parse_settings(settings_str: str) -> dict:
    # Parsea string de configuración a dict
    pass

def manage_command(command: str) -> str:
    # Simula manage.py commands
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
def project_structure() -> dict:
    return {
        'manage.py': 'CLI para gestionar el proyecto Django',
        'settings.py': 'Configuración del proyecto: DB, apps, middleware',
        'urls.py': 'Definición de rutas URL del proyecto',
        'wsgi.py': 'Punto de entrada WSGI para servidores de producción',
        'asgi.py': 'Punto de entrada ASGI para soporte asíncrono',
    }

def parse_settings(settings_str: str) -> dict:
    result = {}
    for line in settings_str.strip().split('\n'):
        line = line.strip()
        if not line or '=' not in line:
            continue
        key, value = line.split('=', 1)
        key, value = key.strip(), value.strip()
        if value == 'True':
            value = True
        elif value == 'False':
            value = False
        result[key] = value
    return result

def manage_command(command: str) -> str:
    commands = {
        'runserver': 'Inicia servidor de desarrollo en http://127.0.0.1:8000',
        'migrate': 'Aplica migraciones pendientes a la base de datos',
        'makemigrations': 'Genera archivos de migración a partir de cambios en modelos',
        'createsuperuser': 'Crea un usuario administrador interactivamente',
        'startapp': 'Crea una nueva aplicación Django',
        'collectstatic': 'Recopila archivos estáticos en STATIC_ROOT',
        'shell': 'Inicia shell interactivo de Python con contexto Django',
    }
    return commands.get(command, f'Comando desconocido: {command}')
PYTHON,
        ];

        // ── 2 · Modelos y ORM ──────────────────────────────────────
        if ($l = $lessons->get(2)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Modelos y QuerySets simulados',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un mini-ORM que simule modelos y QuerySets de Django.

```python
class MiniModel:
    """Modelo base con almacenamiento en memoria."""
    _records: dict = {}
    _next_id: int = 1

    @classmethod
    def create(cls, **kwargs) -> 'MiniModel':
        """Crea un registro con id auto-incremental."""

    @classmethod
    def get(cls, pk: int) -> 'MiniModel':
        """Retorna instancia por pk. Lanza ValueError si no existe."""

    @classmethod
    def filter(cls, **kwargs) -> list:
        """Filtra registros por atributos. Retorna lista de instancias."""

    @classmethod
    def all(cls) -> list:
        """Retorna todos los registros como instancias."""

    def save(self) -> None:
        """Guarda/actualiza el registro en storage."""

    def delete(self) -> bool:
        """Elimina el registro. Retorna True."""

class Article(MiniModel):
    _records = {}
    _next_id = 1
    def __init__(self, title='', body='', published=False):
        self.id = None
        self.title = title
        self.body = body
        self.published = published
```
MD,
            'starter_code' => <<<'PYTHON'
class MiniModel:
    _records = {}
    _next_id = 1

    @classmethod
    def create(cls, **kwargs):
        pass

    @classmethod
    def get(cls, pk: int):
        pass

    @classmethod
    def filter(cls, **kwargs) -> list:
        pass

    @classmethod
    def all(cls) -> list:
        pass

    def save(self):
        pass

    def delete(self) -> bool:
        pass


class Article(MiniModel):
    _records = {}
    _next_id = 1
    def __init__(self, title='', body='', published=False):
        self.id = None
        self.title = title
        self.body = body
        self.published = published
PYTHON,
            'solution_code' => <<<'PYTHON'
class MiniModel:
    _records = {}
    _next_id = 1

    @classmethod
    def create(cls, **kwargs):
        instance = cls(**kwargs)
        instance.id = cls._next_id
        cls._next_id += 1
        cls._records[instance.id] = instance
        return instance

    @classmethod
    def get(cls, pk: int):
        if pk not in cls._records:
            raise ValueError(f'{cls.__name__} con pk={pk} no existe')
        return cls._records[pk]

    @classmethod
    def filter(cls, **kwargs) -> list:
        results = []
        for record in cls._records.values():
            match = all(getattr(record, k, None) == v for k, v in kwargs.items())
            if match:
                results.append(record)
        return results

    @classmethod
    def all(cls) -> list:
        return list(cls._records.values())

    def save(self):
        if self.id is None:
            self.id = self.__class__._next_id
            self.__class__._next_id += 1
        self.__class__._records[self.id] = self

    def delete(self) -> bool:
        if self.id in self.__class__._records:
            del self.__class__._records[self.id]
            return True
        return False


class Article(MiniModel):
    _records = {}
    _next_id = 1
    def __init__(self, title='', body='', published=False):
        self.id = None
        self.title = title
        self.body = body
        self.published = published
PYTHON,
        ];

        // ── 3 · Vistas y URLs ──────────────────────────────────────
        if ($l = $lessons->get(3)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Router y vistas de Django',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un sistema de URL routing similar a Django.

```python
class URLRouter:
    """Mini router que simula el sistema de URLs de Django."""

    def __init__(self):
        self.patterns = []

    def path(self, route: str, view, name: str = None):
        """Registra una ruta. route puede contener <type:param>."""

    def include(self, prefix: str, router: 'URLRouter'):
        """Incluye otro router bajo un prefijo."""

    def resolve(self, url: str) -> dict | None:
        """Resuelve URL: retorna {'view': fn, 'kwargs': {params}, 'name': str} o None."""

    def reverse(self, name: str, **kwargs) -> str:
        """Genera URL a partir del nombre y parámetros."""

def json_response(data: dict, status: int = 200) -> dict:
    """Simula JsonResponse: retorna {'status': status, 'content_type': 'application/json', 'data': data}."""

def redirect_response(url: str, permanent: bool = False) -> dict:
    """Simula redirect: {'status': 301|302, 'location': url}."""
```
MD,
            'starter_code' => <<<'PYTHON'
class URLRouter:
    def __init__(self):
        self.patterns = []

    def path(self, route: str, view, name: str = None):
        pass

    def include(self, prefix: str, router: 'URLRouter'):
        pass

    def resolve(self, url: str):
        pass

    def reverse(self, name: str, **kwargs) -> str:
        pass


def json_response(data: dict, status: int = 200) -> dict:
    pass

def redirect_response(url: str, permanent: bool = False) -> dict:
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import re


class URLRouter:
    def __init__(self):
        self.patterns = []

    def path(self, route: str, view, name: str = None):
        pattern = route
        regex = re.sub(r'<int:(\w+)>', r'(?P<\1>\\d+)', pattern)
        regex = re.sub(r'<str:(\w+)>', r'(?P<\1>[^/]+)', regex)
        regex = re.sub(r'<(\w+)>', r'(?P<\1>[^/]+)', regex)
        self.patterns.append({
            'route': route,
            'regex': f'^{regex}$',
            'view': view,
            'name': name,
        })

    def include(self, prefix: str, router: 'URLRouter'):
        for p in router.patterns:
            combined_route = prefix.rstrip('/') + '/' + p['route'].lstrip('/')
            combined_regex = re.sub(r'<int:(\w+)>', r'(?P<\1>\\d+)', combined_route)
            combined_regex = re.sub(r'<str:(\w+)>', r'(?P<\1>[^/]+)', combined_regex)
            combined_regex = re.sub(r'<(\w+)>', r'(?P<\1>[^/]+)', combined_regex)
            self.patterns.append({
                'route': combined_route,
                'regex': f'^{combined_regex}$',
                'view': p['view'],
                'name': p['name'],
            })

    def resolve(self, url: str):
        for p in self.patterns:
            match = re.match(p['regex'], url.strip('/') if url != '/' else '')
            if not match and url == '/':
                match = re.match(p['regex'], '')
            if match:
                kwargs = {}
                for k, v in match.groupdict().items():
                    kwargs[k] = int(v) if v.isdigit() else v
                return {'view': p['view'], 'kwargs': kwargs, 'name': p['name']}
        return None

    def reverse(self, name: str, **kwargs) -> str:
        for p in self.patterns:
            if p['name'] == name:
                route = p['route']
                for k, v in kwargs.items():
                    route = re.sub(rf'<\w+:{k}>', str(v), route)
                    route = re.sub(rf'<{k}>', str(v), route)
                return '/' + route.strip('/')
        raise ValueError(f'No URL pattern named: {name}')


def json_response(data: dict, status: int = 200) -> dict:
    return {'status': status, 'content_type': 'application/json', 'data': data}


def redirect_response(url: str, permanent: bool = False) -> dict:
    return {'status': 301 if permanent else 302, 'location': url}
PYTHON,
        ];

        // ── 4 · Templates ──────────────────────────────────────────
        if ($l = $lessons->get(4)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mini motor de templates Django',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un motor de templates simplificado al estilo DTL.

```python
class MiniTemplate:
    """Motor de templates con variables, filtros y tags."""

    def __init__(self, template: str):
        self.template = template

    def render(self, context: dict = {}) -> str:
        """Renderiza el template con el contexto dado.
        Soporta:
        - {{ variable }} → reemplaza con valor del contexto (escapando HTML)
        - {{ variable|upper }} → filtro upper
        - {{ variable|lower }} → filtro lower
        - {{ variable|default:'valor' }} → valor por defecto si variable es None/vacía
        - {% if cond %} ... {% else %} ... {% endif %}
        - {% for item in items %} ... {% endfor %}
        - {% block name %} ... {% endblock %}
        """

    @staticmethod
    def apply_filter(value, filter_expr: str):
        """Aplica un filtro al valor."""

def extends_template(child: str, parent: str, context: dict) -> str:
    """Simula herencia: reemplaza {% block %} del parent con contenido del child."""
```
MD,
            'starter_code' => <<<'PYTHON'
import re
from html import escape


class MiniTemplate:
    def __init__(self, template: str):
        self.template = template

    def render(self, context: dict = {}) -> str:
        pass

    @staticmethod
    def apply_filter(value, filter_expr: str):
        pass


def extends_template(child: str, parent: str, context: dict) -> str:
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import re
from html import escape


class MiniTemplate:
    def __init__(self, template: str):
        self.template = template

    def render(self, context: dict = {}) -> str:
        result = self.template

        # {% for item in items %} ... {% endfor %}
        def replace_for(m):
            var_name = m.group(1)
            list_name = m.group(2)
            body = m.group(3)
            items = context.get(list_name, [])
            output = ''
            for item in items:
                local_ctx = {**context, var_name: item}
                t = MiniTemplate(body)
                output += t.render(local_ctx)
            return output

        result = re.sub(
            r'\{%\s*for\s+(\w+)\s+in\s+(\w+)\s*%\}(.*?)\{%\s*endfor\s*%\}',
            replace_for, result, flags=re.DOTALL
        )

        # {% if cond %} ... {% else %} ... {% endif %}
        def replace_if(m):
            cond = m.group(1).strip()
            true_block = m.group(2)
            false_block = m.group(4) or ''
            val = context.get(cond)
            return true_block.strip() if val else false_block.strip()

        result = re.sub(
            r'\{%\s*if\s+(\w+)\s*%\}(.*?)(\{%\s*else\s*%\}(.*?))?\{%\s*endif\s*%\}',
            replace_if, result, flags=re.DOTALL
        )

        # {{ variable|filter }} y {{ variable }}
        def replace_var(m):
            expr = m.group(1).strip()
            if '|' in expr:
                var_name, filter_expr = expr.split('|', 1)
                value = context.get(var_name.strip())
                return str(self.apply_filter(value, filter_expr.strip()))
            value = context.get(expr)
            return escape(str(value)) if value is not None else ''

        result = re.sub(r'\{\{\s*(.*?)\s*\}\}', replace_var, result)
        return result

    @staticmethod
    def apply_filter(value, filter_expr: str):
        if filter_expr == 'upper':
            return str(value).upper() if value else ''
        elif filter_expr == 'lower':
            return str(value).lower() if value else ''
        elif filter_expr.startswith("default:"):
            default = filter_expr.split(':', 1)[1].strip("'\"")
            return value if value else default
        return value if value is not None else ''


def extends_template(child: str, parent: str, context: dict) -> str:
    blocks = dict(re.findall(
        r'\{%\s*block\s+(\w+)\s*%\}(.*?)\{%\s*endblock\s*%\}',
        child, re.DOTALL
    ))
    def replace_block(m):
        name = m.group(1)
        return blocks.get(name, m.group(2)).strip()

    result = re.sub(
        r'\{%\s*block\s+(\w+)\s*%\}(.*?)\{%\s*endblock\s*%\}',
        replace_block, parent, flags=re.DOTALL
    )
    return MiniTemplate(result).render(context)
PYTHON,
        ];

        // ── 5 · Formularios ────────────────────────────────────────
        if ($l = $lessons->get(5)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Formularios y validación Django',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un sistema de formularios con validación al estilo Django.

```python
class Field:
    """Campo base con validación."""
    def __init__(self, required=True, max_length=None, min_length=None):
        ...
    def validate(self, value) -> list[str]:
        """Retorna lista de errores (vacía si válido)."""

class CharField(Field): ...
class EmailField(Field): ...
class IntegerField(Field): ...
class BooleanField(Field): ...

class MiniForm:
    """Formulario con campos definidos como atributos de clase."""
    def __init__(self, data: dict = {}):
        ...
    def is_valid(self) -> bool:
        """Valida todos los campos. Almacena errores."""
    @property
    def errors(self) -> dict:
        """Dict de {campo: [errores]}."""
    @property
    def cleaned_data(self) -> dict:
        """Dict con datos validados y limpios."""
```
MD,
            'starter_code' => <<<'PYTHON'
import re


class Field:
    def __init__(self, required=True, max_length=None, min_length=None):
        self.required = required
        self.max_length = max_length
        self.min_length = min_length

    def validate(self, value) -> list:
        pass


class CharField(Field):
    def validate(self, value) -> list:
        pass


class EmailField(Field):
    def validate(self, value) -> list:
        pass


class IntegerField(Field):
    def validate(self, value) -> list:
        pass


class BooleanField(Field):
    def validate(self, value) -> list:
        pass


class MiniForm:
    def __init__(self, data: dict = {}):
        self.data = data
        self._errors = {}
        self._cleaned = {}

    def is_valid(self) -> bool:
        pass

    @property
    def errors(self) -> dict:
        return self._errors

    @property
    def cleaned_data(self) -> dict:
        return self._cleaned
PYTHON,
            'solution_code' => <<<'PYTHON'
import re


class Field:
    def __init__(self, required=True, max_length=None, min_length=None):
        self.required = required
        self.max_length = max_length
        self.min_length = min_length

    def validate(self, value) -> list:
        errors = []
        if self.required and (value is None or value == ''):
            errors.append('Este campo es obligatorio.')
        if value and self.max_length and len(str(value)) > self.max_length:
            errors.append(f'Máximo {self.max_length} caracteres.')
        if value and self.min_length and len(str(value)) < self.min_length:
            errors.append(f'Mínimo {self.min_length} caracteres.')
        return errors


class CharField(Field):
    def validate(self, value) -> list:
        errors = super().validate(value)
        if value is not None and not isinstance(value, str):
            errors.append('Debe ser texto.')
        return errors


class EmailField(Field):
    def validate(self, value) -> list:
        errors = super().validate(value)
        if value and not re.match(r'^[^@]+@[^@]+\.[^@]+$', str(value)):
            errors.append('Email inválido.')
        return errors


class IntegerField(Field):
    def validate(self, value) -> list:
        errors = []
        if self.required and (value is None or value == ''):
            errors.append('Este campo es obligatorio.')
        if value is not None and value != '':
            try:
                int(value)
            except (ValueError, TypeError):
                errors.append('Debe ser un número entero.')
        return errors


class BooleanField(Field):
    def validate(self, value) -> list:
        errors = []
        if self.required and value is None:
            errors.append('Este campo es obligatorio.')
        return errors


class MiniForm:
    def __init__(self, data: dict = {}):
        self.data = data
        self._errors = {}
        self._cleaned = {}

    def _get_fields(self):
        fields = {}
        for cls in type(self).__mro__:
            for k, v in vars(cls).items():
                if isinstance(v, Field) and k not in fields:
                    fields[k] = v
        return fields

    def is_valid(self) -> bool:
        self._errors = {}
        self._cleaned = {}
        for name, field in self._get_fields().items():
            value = self.data.get(name)
            errs = field.validate(value)
            if errs:
                self._errors[name] = errs
            else:
                if isinstance(field, IntegerField) and value is not None and value != '':
                    self._cleaned[name] = int(value)
                elif isinstance(field, BooleanField):
                    self._cleaned[name] = bool(value)
                else:
                    self._cleaned[name] = value
        return len(self._errors) == 0

    @property
    def errors(self) -> dict:
        return self._errors

    @property
    def cleaned_data(self) -> dict:
        return self._cleaned
PYTHON,
        ];

        // ── 6 · Panel de Administración ────────────────────────────
        if ($l = $lessons->get(6)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mini panel de administración',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un registro de modelos para un panel admin simplificado.

```python
class ModelAdmin:
    """Configuración de un modelo en el admin."""
    list_display: list = ['__str__']
    search_fields: list = []
    list_filter: list = []

    def __init__(self, model_class):
        self.model = model_class
        self.records = []

    def register_records(self, records: list[dict]):
        """Registra registros."""

    def get_list(self, search: str = None, filters: dict = None) -> list[dict]:
        """Lista filtrada y buscada de registros."""

    def get_display_row(self, record: dict) -> dict:
        """Retorna solo los campos de list_display."""

class AdminSite:
    """Sitio de administración con modelos registrados."""
    def __init__(self):
        self._registry = {}

    def register(self, model_name: str, admin_class: ModelAdmin):
        """Registra un modelo con su configuración admin."""

    def get_models(self) -> list[str]:
        """Retorna nombres de modelos registrados."""

    def get_admin(self, model_name: str) -> ModelAdmin:
        """Retorna el ModelAdmin registrado."""
```
MD,
            'starter_code' => <<<'PYTHON'
class ModelAdmin:
    list_display = ['__str__']
    search_fields = []
    list_filter = []

    def __init__(self, model_class=None):
        self.model = model_class
        self.records = []

    def register_records(self, records: list):
        pass

    def get_list(self, search: str = None, filters: dict = None) -> list:
        pass

    def get_display_row(self, record: dict) -> dict:
        pass


class AdminSite:
    def __init__(self):
        self._registry = {}

    def register(self, model_name: str, admin_class):
        pass

    def get_models(self) -> list:
        pass

    def get_admin(self, model_name: str):
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class ModelAdmin:
    list_display = ['__str__']
    search_fields = []
    list_filter = []

    def __init__(self, model_class=None):
        self.model = model_class
        self.records = []

    def register_records(self, records: list):
        self.records.extend(records)

    def get_list(self, search: str = None, filters: dict = None) -> list:
        results = self.records[:]
        if search and self.search_fields:
            search_lower = search.lower()
            results = [
                r for r in results
                if any(search_lower in str(r.get(f, '')).lower() for f in self.search_fields)
            ]
        if filters:
            for key, value in filters.items():
                if key in self.list_filter:
                    results = [r for r in results if r.get(key) == value]
        return results

    def get_display_row(self, record: dict) -> dict:
        if self.list_display == ['__str__']:
            return record
        return {k: record.get(k) for k in self.list_display if k in record}


class AdminSite:
    def __init__(self):
        self._registry = {}

    def register(self, model_name: str, admin_class):
        self._registry[model_name] = admin_class

    def get_models(self) -> list:
        return sorted(self._registry.keys())

    def get_admin(self, model_name: str):
        if model_name not in self._registry:
            raise ValueError(f'Modelo {model_name} no registrado')
        return self._registry[model_name]
PYTHON,
        ];

        // ── 7 · Relaciones y Queries Avanzados ─────────────────────
        if ($l = $lessons->get(7)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Relaciones y queries avanzados',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa relaciones entre modelos y queries avanzados estilo Django ORM.

```python
class RelationDB:
    """Base de datos relacional en memoria."""
    _tables: dict = {}

    @classmethod
    def insert(cls, table: str, record: dict) -> dict: ...
    @classmethod
    def select(cls, table: str, **filters) -> list: ...
    @classmethod
    def reset(cls): ...

def select_related(table: str, fk_field: str, related_table: str) -> list[dict]:
    """Simula select_related: JOIN FK en una query (agrega campo 'related_<fk>'). """

def prefetch_related(table: str, related_table: str, fk_field: str) -> list[dict]:
    """Simula prefetch_related: 2 queries, agrega lista de related al parent."""

def aggregate(table: str, field: str, func: str) -> float | int:
    """Funciones de agregación: count, sum, avg, max, min sobre un campo."""

def q_filter(table: str, conditions: list[dict], operator: str = 'AND') -> list:
    """Simula Q objects: filtra con AND/OR entre condiciones [{field: value}]."""
```
MD,
            'starter_code' => <<<'PYTHON'
class RelationDB:
    _tables = {}

    @classmethod
    def insert(cls, table, record):
        if table not in cls._tables:
            cls._tables[table] = []
        record['id'] = len(cls._tables[table]) + 1
        cls._tables[table].append(record)
        return record

    @classmethod
    def select(cls, table, **filters):
        rows = cls._tables.get(table, [])
        return [r for r in rows if all(r.get(k) == v for k, v in filters.items())]

    @classmethod
    def reset(cls):
        cls._tables = {}


def select_related(table, fk_field, related_table):
    pass

def prefetch_related(table, related_table, fk_field):
    pass

def aggregate(table, field, func):
    pass

def q_filter(table, conditions, operator='AND'):
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class RelationDB:
    _tables = {}

    @classmethod
    def insert(cls, table, record):
        if table not in cls._tables:
            cls._tables[table] = []
        record['id'] = len(cls._tables[table]) + 1
        cls._tables[table].append(record)
        return record

    @classmethod
    def select(cls, table, **filters):
        rows = cls._tables.get(table, [])
        return [r for r in rows if all(r.get(k) == v for k, v in filters.items())]

    @classmethod
    def reset(cls):
        cls._tables = {}


def select_related(table, fk_field, related_table):
    rows = RelationDB.select(table)
    related_rows = RelationDB.select(related_table)
    related_map = {r['id']: r for r in related_rows}
    results = []
    for row in rows:
        row_copy = dict(row)
        fk_val = row.get(fk_field)
        row_copy[f'related_{fk_field}'] = related_map.get(fk_val)
        results.append(row_copy)
    return results


def prefetch_related(table, related_table, fk_field):
    parents = RelationDB.select(table)
    children = RelationDB.select(related_table)
    parent_map = {}
    for child in children:
        pid = child.get(fk_field)
        parent_map.setdefault(pid, []).append(child)
    results = []
    for parent in parents:
        p = dict(parent)
        p[related_table] = parent_map.get(parent['id'], [])
        results.append(p)
    return results


def aggregate(table, field, func):
    rows = RelationDB.select(table)
    values = [r[field] for r in rows if field in r and r[field] is not None]
    if not values:
        return 0
    if func == 'count':
        return len(values)
    elif func == 'sum':
        return sum(values)
    elif func == 'avg':
        return sum(values) / len(values)
    elif func == 'max':
        return max(values)
    elif func == 'min':
        return min(values)
    return 0


def q_filter(table, conditions, operator='AND'):
    rows = RelationDB.select(table)
    results = []
    for row in rows:
        matches = []
        for cond in conditions:
            match = all(row.get(k) == v for k, v in cond.items())
            matches.append(match)
        if operator == 'AND' and all(matches):
            results.append(row)
        elif operator == 'OR' and any(matches):
            results.append(row)
    return results
PYTHON,
        ];

        // ── 8 · Autenticación y Autorización ──────────────────────
        if ($l = $lessons->get(8)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema auth de Django',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un sistema de autenticación y permisos al estilo Django.

```python
import hashlib, secrets

class UserManager:
    def __init__(self):
        self.users = {}
        self.groups = {}

    def create_user(self, username, email, password, is_staff=False, is_superuser=False) -> dict: ...
    def authenticate(self, username, password) -> dict | None: ...
    def set_password(self, username, new_password) -> bool: ...
    def create_group(self, name, permissions: list[str]) -> dict: ...
    def add_to_group(self, username, group_name) -> bool: ...
    def has_perm(self, username, permission) -> bool: ...
    def get_all_permissions(self, username) -> set: ...

def login_required(user: dict | None) -> dict:
    """Simula @login_required: retorna {'allowed': bool, 'redirect': '/login' o None}."""

def permission_required(user: dict | None, perm: str, manager: UserManager) -> dict:
    """Simula @permission_required."""
```
MD,
            'starter_code' => <<<'PYTHON'
import hashlib
import secrets


class UserManager:
    def __init__(self):
        self.users = {}
        self.groups = {}

    def create_user(self, username, email, password, is_staff=False, is_superuser=False):
        pass

    def authenticate(self, username, password):
        pass

    def set_password(self, username, new_password):
        pass

    def create_group(self, name, permissions):
        pass

    def add_to_group(self, username, group_name):
        pass

    def has_perm(self, username, permission):
        pass

    def get_all_permissions(self, username):
        pass


def login_required(user):
    pass

def permission_required(user, perm, manager):
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import hashlib
import secrets


class UserManager:
    def __init__(self):
        self.users = {}
        self.groups = {}

    def _hash(self, password, salt=None):
        if salt is None:
            salt = secrets.token_hex(8)
        h = hashlib.sha256(f'{salt}{password}'.encode()).hexdigest()
        return f'{salt}${h}'

    def create_user(self, username, email, password, is_staff=False, is_superuser=False):
        if username in self.users:
            raise ValueError(f'Usuario {username} ya existe')
        user = {
            'username': username,
            'email': email,
            'password_hash': self._hash(password),
            'is_staff': is_staff,
            'is_superuser': is_superuser,
            'groups': [],
            'user_permissions': [],
        }
        self.users[username] = user
        return {k: v for k, v in user.items() if k != 'password_hash'}

    def authenticate(self, username, password):
        user = self.users.get(username)
        if not user:
            return None
        salt = user['password_hash'].split('$')[0]
        if self._hash(password, salt) == user['password_hash']:
            return {k: v for k, v in user.items() if k != 'password_hash'}
        return None

    def set_password(self, username, new_password):
        if username not in self.users:
            return False
        self.users[username]['password_hash'] = self._hash(new_password)
        return True

    def create_group(self, name, permissions):
        self.groups[name] = {'name': name, 'permissions': set(permissions)}
        return self.groups[name]

    def add_to_group(self, username, group_name):
        if username not in self.users or group_name not in self.groups:
            return False
        if group_name not in self.users[username]['groups']:
            self.users[username]['groups'].append(group_name)
        return True

    def has_perm(self, username, permission):
        user = self.users.get(username)
        if not user:
            return False
        if user['is_superuser']:
            return True
        if permission in user.get('user_permissions', []):
            return True
        for g in user['groups']:
            if g in self.groups and permission in self.groups[g]['permissions']:
                return True
        return False

    def get_all_permissions(self, username):
        user = self.users.get(username)
        if not user:
            return set()
        perms = set(user.get('user_permissions', []))
        for g in user['groups']:
            if g in self.groups:
                perms |= self.groups[g]['permissions']
        return perms


def login_required(user):
    if user and user.get('username'):
        return {'allowed': True, 'redirect': None}
    return {'allowed': False, 'redirect': '/login'}


def permission_required(user, perm, manager):
    if not user or not user.get('username'):
        return {'allowed': False, 'redirect': '/login'}
    if manager.has_perm(user['username'], perm):
        return {'allowed': True, 'redirect': None}
    return {'allowed': False, 'redirect': '/403'}
PYTHON,
        ];

        // ── 9 · Middleware ─────────────────────────────────────────
        if ($l = $lessons->get(9)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Pipeline de middleware Django',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un pipeline de middleware al estilo Django.

```python
class DjangoMiddleware:
    """Middleware base con process_request y process_response."""
    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request: dict) -> dict:
        """Ejecuta process_request, luego get_response, luego process_response."""

    def process_request(self, request: dict) -> dict | None:
        """Pre-procesamiento. Retorna response para cortocircuitar o None para continuar."""
        return None

    def process_response(self, request: dict, response: dict) -> dict:
        """Post-procesamiento. Retorna response modificada."""
        return response

class SecurityMiddleware(DjangoMiddleware): ...
class CorsMiddleware(DjangoMiddleware): ...
class AuthMiddleware(DjangoMiddleware): ...

def build_middleware_chain(middlewares: list, view) -> callable:
    """Construye la cadena de middleware onion alrededor de la vista."""
```
MD,
            'starter_code' => <<<'PYTHON'
class DjangoMiddleware:
    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        pass

    def process_request(self, request):
        return None

    def process_response(self, request, response):
        return response


class SecurityMiddleware(DjangoMiddleware):
    def process_response(self, request, response):
        pass


class CorsMiddleware(DjangoMiddleware):
    def process_response(self, request, response):
        pass


class AuthMiddleware(DjangoMiddleware):
    def process_request(self, request):
        pass


def build_middleware_chain(middlewares, view):
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class DjangoMiddleware:
    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        early = self.process_request(request)
        if early is not None:
            return early
        response = self.get_response(request)
        return self.process_response(request, response)

    def process_request(self, request):
        return None

    def process_response(self, request, response):
        return response


class SecurityMiddleware(DjangoMiddleware):
    def process_response(self, request, response):
        response['X-Content-Type-Options'] = 'nosniff'
        response['X-Frame-Options'] = 'DENY'
        response['Referrer-Policy'] = 'same-origin'
        return response


class CorsMiddleware(DjangoMiddleware):
    def process_response(self, request, response):
        response['Access-Control-Allow-Origin'] = '*'
        response['Access-Control-Allow-Methods'] = 'GET, POST, PUT, DELETE'
        return response


class AuthMiddleware(DjangoMiddleware):
    def process_request(self, request):
        if not request.get('user'):
            return {'status': 401, 'body': 'Unauthorized'}
        return None


def build_middleware_chain(middlewares, view):
    handler = view
    for mw_class in reversed(middlewares):
        handler = mw_class(handler)
    return handler
PYTHON,
        ];

        // ── 10 · Archivos Estáticos y Media ───────────────────────
        if ($l = $lessons->get(10)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Gestión de archivos estáticos y media',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa utilidades para manejar archivos estáticos y media.

```python
class StaticFilesManager:
    """Gestiona archivos estáticos (CSS, JS, imágenes)."""
    def __init__(self, static_root: str, static_url: str = '/static/'):
        ...

    def add_app_static(self, app_name: str, files: list[str]):
        """Registra archivos estáticos de una app."""

    def collectstatic(self) -> list[str]:
        """Simula collectstatic: retorna lista de todos los paths destino."""

    def url(self, path: str) -> str:
        """Genera URL pública para un archivo estático."""

class MediaManager:
    """Gestiona archivos subidos por usuarios."""
    def __init__(self, media_root: str, media_url: str = '/media/'):
        ...

    def upload(self, field_name: str, filename: str, size_bytes: int) -> dict:
        """Simula upload: valida extensión, retorna {path, url, size}."""

    def delete(self, path: str) -> bool:
        """Elimina un archivo media."""

    def validate_file(self, filename: str, max_size: int, allowed_ext: list) -> list[str]:
        """Retorna lista de errores de validación."""
```
MD,
            'starter_code' => <<<'PYTHON'
import os


class StaticFilesManager:
    def __init__(self, static_root, static_url='/static/'):
        self.static_root = static_root
        self.static_url = static_url
        self.app_files = {}

    def add_app_static(self, app_name, files):
        pass

    def collectstatic(self):
        pass

    def url(self, path):
        pass


class MediaManager:
    def __init__(self, media_root, media_url='/media/'):
        self.media_root = media_root
        self.media_url = media_url
        self.files = {}

    def upload(self, field_name, filename, size_bytes):
        pass

    def delete(self, path):
        pass

    def validate_file(self, filename, max_size, allowed_ext):
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import os


class StaticFilesManager:
    def __init__(self, static_root, static_url='/static/'):
        self.static_root = static_root
        self.static_url = static_url
        self.app_files = {}

    def add_app_static(self, app_name, files):
        self.app_files[app_name] = files

    def collectstatic(self):
        collected = []
        for app, files in self.app_files.items():
            for f in files:
                dest = os.path.join(self.static_root, app, f)
                collected.append(dest)
        return collected

    def url(self, path):
        return self.static_url + path


class MediaManager:
    def __init__(self, media_root, media_url='/media/'):
        self.media_root = media_root
        self.media_url = media_url
        self.files = {}

    def upload(self, field_name, filename, size_bytes):
        path = os.path.join(self.media_root, field_name, filename)
        url = self.media_url + field_name + '/' + filename
        info = {'path': path, 'url': url, 'size': size_bytes}
        self.files[path] = info
        return info

    def delete(self, path):
        if path in self.files:
            del self.files[path]
            return True
        return False

    def validate_file(self, filename, max_size, allowed_ext):
        errors = []
        ext = os.path.splitext(filename)[1].lower()
        if ext not in allowed_ext:
            errors.append(f'Extensión {ext} no permitida. Permitidas: {allowed_ext}')
        if max_size and max_size > 0:
            pass  # size se valida al subir
        return errors
PYTHON,
        ];

        // ── 11 · Django REST Framework ────────────────────────────
        if ($l = $lessons->get(11)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Serializers y ViewSets DRF',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa serializers y viewsets simplificados al estilo DRF.

```python
class Serializer:
    """Serializer base con validación y serialización."""
    fields: list = []
    read_only: list = []

    def __init__(self, data: dict = None, instance: dict = None):
        ...
    def is_valid(self) -> bool: ...
    @property
    def errors(self) -> dict: ...
    @property
    def validated_data(self) -> dict: ...
    def serialize(self) -> dict: ...

class ModelSerializer(Serializer):
    """Serializer que genera campos automáticamente desde un modelo (dict)."""
    model_fields: dict = {}  # {field_name: {type, required, max_length...}}
    ...

class ViewSet:
    """ViewSet con acciones CRUD."""
    def __init__(self, serializer_class, storage: list = None): ...
    def list(self, filters: dict = None) -> list: ...
    def retrieve(self, pk: int) -> dict | None: ...
    def create(self, data: dict) -> dict: ...
    def update(self, pk: int, data: dict) -> dict | None: ...
    def destroy(self, pk: int) -> bool: ...

def paginate(items: list, page: int = 1, page_size: int = 10) -> dict:
    """Retorna {'count': N, 'results': [...], 'next': bool, 'previous': bool}."""
```
MD,
            'starter_code' => <<<'PYTHON'
class Serializer:
    fields = []
    read_only = []

    def __init__(self, data=None, instance=None):
        self.data = data or {}
        self.instance = instance
        self._errors = {}
        self._validated = {}

    def is_valid(self):
        pass

    @property
    def errors(self):
        return self._errors

    @property
    def validated_data(self):
        return self._validated

    def serialize(self):
        pass


class ViewSet:
    def __init__(self, serializer_class, storage=None):
        self.serializer_class = serializer_class
        self.storage = storage if storage is not None else []

    def list(self, filters=None):
        pass

    def retrieve(self, pk):
        pass

    def create(self, data):
        pass

    def update(self, pk, data):
        pass

    def destroy(self, pk):
        pass


def paginate(items, page=1, page_size=10):
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class Serializer:
    fields = []
    read_only = []

    def __init__(self, data=None, instance=None):
        self.data = data or {}
        self.instance = instance
        self._errors = {}
        self._validated = {}

    def is_valid(self):
        self._errors = {}
        self._validated = {}
        for field in self.fields:
            if field in self.read_only:
                continue
            value = self.data.get(field)
            if value is None or value == '':
                self._errors[field] = ['Este campo es obligatorio.']
            else:
                self._validated[field] = value
        return len(self._errors) == 0

    @property
    def errors(self):
        return self._errors

    @property
    def validated_data(self):
        return self._validated

    def serialize(self):
        source = self.instance or self.data
        return {f: source.get(f) for f in self.fields if f in source}


class ViewSet:
    def __init__(self, serializer_class, storage=None):
        self.serializer_class = serializer_class
        self.storage = storage if storage is not None else []
        self._next_id = max((r.get('id', 0) for r in self.storage), default=0) + 1

    def list(self, filters=None):
        results = self.storage
        if filters:
            results = [
                r for r in results
                if all(r.get(k) == v for k, v in filters.items())
            ]
        return [self.serializer_class(instance=r).serialize() for r in results]

    def retrieve(self, pk):
        for r in self.storage:
            if r.get('id') == pk:
                return self.serializer_class(instance=r).serialize()
        return None

    def create(self, data):
        s = self.serializer_class(data=data)
        if not s.is_valid():
            return {'errors': s.errors}
        record = {**s.validated_data, 'id': self._next_id}
        self._next_id += 1
        self.storage.append(record)
        return self.serializer_class(instance=record).serialize()

    def update(self, pk, data):
        for r in self.storage:
            if r.get('id') == pk:
                s = self.serializer_class(data=data)
                if not s.is_valid():
                    return {'errors': s.errors}
                r.update(s.validated_data)
                return self.serializer_class(instance=r).serialize()
        return None

    def destroy(self, pk):
        for i, r in enumerate(self.storage):
            if r.get('id') == pk:
                self.storage.pop(i)
                return True
        return False


def paginate(items, page=1, page_size=10):
    total = len(items)
    start = (page - 1) * page_size
    end = start + page_size
    return {
        'count': total,
        'results': items[start:end],
        'next': end < total,
        'previous': page > 1,
    }
PYTHON,
        ];

        // ── 12 · Signals ──────────────────────────────────────────
        if ($l = $lessons->get(12)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema de signals Django',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un sistema de signals al estilo Django.

```python
class Signal:
    """Signal de Django: conecta emisores con receptores."""
    def __init__(self, name: str = ''):
        ...

    def connect(self, receiver: callable, sender=None):
        """Conecta un receiver. Si sender se especifica, solo responde a ese sender."""

    def disconnect(self, receiver: callable, sender=None):
        """Desconecta un receiver."""

    def send(self, sender, **kwargs) -> list:
        """Emite la señal. Retorna lista de (receiver, resultado)."""

# Signals predefinidas
pre_save = Signal('pre_save')
post_save = Signal('post_save')
pre_delete = Signal('pre_delete')
post_delete = Signal('post_delete')

def receiver(signal, sender=None):
    """Decorador para conectar un handler a una signal."""

class ModelSignalMixin:
    """Mixin que emite signals al save/delete."""
    def save(self): ...
    def delete_instance(self): ...
```
MD,
            'starter_code' => <<<'PYTHON'
class Signal:
    def __init__(self, name=''):
        self.name = name
        self.receivers = []

    def connect(self, receiver_fn, sender=None):
        pass

    def disconnect(self, receiver_fn, sender=None):
        pass

    def send(self, sender, **kwargs):
        pass


pre_save = Signal('pre_save')
post_save = Signal('post_save')
pre_delete = Signal('pre_delete')
post_delete = Signal('post_delete')


def receiver(signal, sender=None):
    pass


class ModelSignalMixin:
    def save(self):
        pass

    def delete_instance(self):
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class Signal:
    def __init__(self, name=''):
        self.name = name
        self.receivers = []

    def connect(self, receiver_fn, sender=None):
        self.receivers.append({'fn': receiver_fn, 'sender': sender})

    def disconnect(self, receiver_fn, sender=None):
        self.receivers = [
            r for r in self.receivers
            if not (r['fn'] == receiver_fn and r['sender'] == sender)
        ]

    def send(self, sender, **kwargs):
        results = []
        for r in self.receivers:
            if r['sender'] is None or r['sender'] == sender:
                result = r['fn'](sender=sender, **kwargs)
                results.append((r['fn'], result))
        return results


pre_save = Signal('pre_save')
post_save = Signal('post_save')
pre_delete = Signal('pre_delete')
post_delete = Signal('post_delete')


def receiver(signal, sender=None):
    def decorator(fn):
        signal.connect(fn, sender=sender)
        return fn
    return decorator


class ModelSignalMixin:
    _storage = {}
    _next_id = 1

    def save(self):
        is_new = not hasattr(self, 'id') or self.id is None
        pre_save.send(sender=type(self), instance=self, created=is_new)
        if is_new:
            self.id = ModelSignalMixin._next_id
            ModelSignalMixin._next_id += 1
        ModelSignalMixin._storage[self.id] = self
        post_save.send(sender=type(self), instance=self, created=is_new)

    def delete_instance(self):
        pre_delete.send(sender=type(self), instance=self)
        if hasattr(self, 'id') and self.id in ModelSignalMixin._storage:
            del ModelSignalMixin._storage[self.id]
        post_delete.send(sender=type(self), instance=self)
PYTHON,
        ];

        // ── 13 · Tareas con Celery ──────────────────────────────

        if ($l = $lessons->get(13)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema de tareas asíncronas Celery',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un sistema de tareas asíncronas simplificado.

```python
class TaskResult:
    """Resultado de una tarea."""
    def __init__(self, task_id: str, status: str = 'PENDING', result=None): ...

class MiniCelery:
    """Simulador de Celery con cola de tareas."""
    def __init__(self):
        self.tasks = {}
        self.queue = []
        self.results = {}

    def shared_task(self, func):
        """Decorador que registra una función como task. Agrega .delay() y .apply_async()."""

    def delay(self, task_name: str, *args, **kwargs) -> TaskResult:
        """Encola tarea con args. Retorna TaskResult con status PENDING."""

    def apply_async(self, task_name: str, args=(), kwargs={}, countdown=0, eta=None) -> TaskResult:
        """Encola con opciones avanzadas."""

    def process_next(self) -> TaskResult | None:
        """Procesa la siguiente tarea de la cola. Actualiza resultado."""

    def process_all(self) -> list[TaskResult]:
        """Procesa todas las tareas pendientes."""

    def get_result(self, task_id: str) -> TaskResult | None:
        """Obtiene resultado por task_id."""

class PeriodicTask:
    """Tarea periódica."""
    def __init__(self, task_name: str, interval_seconds: int): ...
    def should_run(self, current_time: float) -> bool: ...
    def mark_run(self, current_time: float): ...
```
MD,
            'starter_code' => <<<'PYTHON'
import uuid
import time


class TaskResult:
    def __init__(self, task_id, status='PENDING', result=None):
        self.task_id = task_id
        self.status = status
        self.result = result


class MiniCelery:
    def __init__(self):
        self.tasks = {}
        self.queue = []
        self.results = {}

    def shared_task(self, func):
        pass

    def delay(self, task_name, *args, **kwargs):
        pass

    def apply_async(self, task_name, args=(), kwargs={}, countdown=0, eta=None):
        pass

    def process_next(self):
        pass

    def process_all(self):
        pass

    def get_result(self, task_id):
        pass


class PeriodicTask:
    def __init__(self, task_name, interval_seconds):
        self.task_name = task_name
        self.interval = interval_seconds
        self.last_run = 0

    def should_run(self, current_time):
        pass

    def mark_run(self, current_time):
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import uuid
import time


class TaskResult:
    def __init__(self, task_id, status='PENDING', result=None):
        self.task_id = task_id
        self.status = status
        self.result = result


class MiniCelery:
    def __init__(self):
        self.tasks = {}
        self.queue = []
        self.results = {}

    def shared_task(self, func):
        self.tasks[func.__name__] = func
        def delay(*args, **kwargs):
            return self.delay(func.__name__, *args, **kwargs)
        def apply_async(args=(), kwargs={}, countdown=0, eta=None):
            return self.apply_async(func.__name__, args, kwargs, countdown, eta)
        func.delay = delay
        func.apply_async = apply_async
        return func

    def delay(self, task_name, *args, **kwargs):
        task_id = str(uuid.uuid4())
        result = TaskResult(task_id)
        self.results[task_id] = result
        self.queue.append({
            'task_id': task_id,
            'name': task_name,
            'args': args,
            'kwargs': kwargs,
        })
        return result

    def apply_async(self, task_name, args=(), kwargs={}, countdown=0, eta=None):
        task_id = str(uuid.uuid4())
        result = TaskResult(task_id)
        self.results[task_id] = result
        self.queue.append({
            'task_id': task_id,
            'name': task_name,
            'args': args,
            'kwargs': kwargs,
            'countdown': countdown,
            'eta': eta,
        })
        return result

    def process_next(self):
        if not self.queue:
            return None
        task = self.queue.pop(0)
        result = self.results[task['task_id']]
        try:
            fn = self.tasks[task['name']]
            ret = fn(*task['args'], **task['kwargs'])
            result.status = 'SUCCESS'
            result.result = ret
        except Exception as e:
            result.status = 'FAILURE'
            result.result = str(e)
        return result

    def process_all(self):
        results = []
        while self.queue:
            results.append(self.process_next())
        return results

    def get_result(self, task_id):
        return self.results.get(task_id)


class PeriodicTask:
    def __init__(self, task_name, interval_seconds):
        self.task_name = task_name
        self.interval = interval_seconds
        self.last_run = 0

    def should_run(self, current_time):
        return (current_time - self.last_run) >= self.interval

    def mark_run(self, current_time):
        self.last_run = current_time
PYTHON,
        ];

        // ── 14 · Testing ──────────────────────────────────────────
        if ($l = $lessons->get(14)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Framework de testing Django',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un mini framework de testing al estilo Django TestCase.

```python
class TestCase:
    """TestCase con setUp, tearDown, y assertions estilo Django."""
    def setUp(self): ...
    def tearDown(self): ...

    def assertEqual(self, a, b, msg=''): ...
    def assertNotEqual(self, a, b, msg=''): ...
    def assertTrue(self, expr, msg=''): ...
    def assertFalse(self, expr, msg=''): ...
    def assertIn(self, member, container, msg=''): ...
    def assertRaises(self, exc_type, callable_fn, *args): ...
    def assertContains(self, response: dict, text: str): ...
    def assertStatusCode(self, response: dict, code: int): ...

class TestClient:
    """Cliente HTTP de testing."""
    def __init__(self, routes: dict = None):
        ...
    def get(self, path: str, data: dict = None) -> dict: ...
    def post(self, path: str, data: dict = None) -> dict: ...
    def put(self, path: str, data: dict = None) -> dict: ...
    def delete(self, path: str) -> dict: ...

class TestRunner:
    """Ejecuta tests y reporta resultados."""
    def run(self, test_classes: list) -> dict:
        """Retorna {'total': N, 'passed': N, 'failed': N, 'errors': [...]}"""
```
MD,
            'starter_code' => <<<'PYTHON'
class TestCase:
    def setUp(self):
        pass

    def tearDown(self):
        pass

    def assertEqual(self, a, b, msg=''):
        pass

    def assertNotEqual(self, a, b, msg=''):
        pass

    def assertTrue(self, expr, msg=''):
        pass

    def assertFalse(self, expr, msg=''):
        pass

    def assertIn(self, member, container, msg=''):
        pass

    def assertRaises(self, exc_type, callable_fn, *args):
        pass

    def assertContains(self, response, text):
        pass

    def assertStatusCode(self, response, code):
        pass


class TestClient:
    def __init__(self, routes=None):
        self.routes = routes or {}

    def get(self, path, data=None):
        pass

    def post(self, path, data=None):
        pass

    def put(self, path, data=None):
        pass

    def delete(self, path):
        pass


class TestRunner:
    def run(self, test_classes):
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class AssertionError(Exception):
    pass


class TestCase:
    def setUp(self):
        pass

    def tearDown(self):
        pass

    def assertEqual(self, a, b, msg=''):
        if a != b:
            raise AssertionError(msg or f'{a!r} != {b!r}')

    def assertNotEqual(self, a, b, msg=''):
        if a == b:
            raise AssertionError(msg or f'{a!r} == {b!r}')

    def assertTrue(self, expr, msg=''):
        if not expr:
            raise AssertionError(msg or f'{expr!r} is not True')

    def assertFalse(self, expr, msg=''):
        if expr:
            raise AssertionError(msg or f'{expr!r} is not False')

    def assertIn(self, member, container, msg=''):
        if member not in container:
            raise AssertionError(msg or f'{member!r} not in {container!r}')

    def assertRaises(self, exc_type, callable_fn, *args):
        try:
            callable_fn(*args)
        except exc_type:
            return
        except Exception as e:
            raise AssertionError(f'Expected {exc_type.__name__}, got {type(e).__name__}')
        raise AssertionError(f'{exc_type.__name__} not raised')

    def assertContains(self, response, text):
        body = response.get('body', '')
        if text not in body:
            raise AssertionError(f'{text!r} not found in response body')

    def assertStatusCode(self, response, code):
        status = response.get('status_code', 0)
        if status != code:
            raise AssertionError(f'Expected {code}, got {status}')


class TestClient:
    def __init__(self, routes=None):
        self.routes = routes or {}

    def _dispatch(self, method, path, data=None):
        handler = self.routes.get(path)
        if handler is None:
            return {'status_code': 404, 'body': 'Not Found'}
        try:
            result = handler(method=method, data=data)
            if isinstance(result, dict):
                result.setdefault('status_code', 200)
                return result
            return {'status_code': 200, 'body': str(result)}
        except Exception as e:
            return {'status_code': 500, 'body': str(e)}

    def get(self, path, data=None):
        return self._dispatch('GET', path, data)

    def post(self, path, data=None):
        return self._dispatch('POST', path, data)

    def put(self, path, data=None):
        return self._dispatch('PUT', path, data)

    def delete(self, path):
        return self._dispatch('DELETE', path)


class TestRunner:
    def run(self, test_classes):
        total = 0
        passed = 0
        failed = 0
        errors = []
        for cls in test_classes:
            instance = cls()
            methods = [m for m in dir(instance) if m.startswith('test')]
            for method_name in methods:
                total += 1
                instance.setUp()
                try:
                    getattr(instance, method_name)()
                    passed += 1
                except Exception as e:
                    failed += 1
                    errors.append(f'{cls.__name__}.{method_name}: {e}')
                finally:
                    instance.tearDown()
        return {'total': total, 'passed': passed, 'failed': failed, 'errors': errors}
PYTHON,
        ];

        // ── 15 · Caché y Performance ─────────────────────────────
        if ($l = $lessons->get(15)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema de caché Django',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un sistema de caché al estilo Django.

```python
class CacheBackend:
    """Backend de caché con TTL."""
    def __init__(self, default_ttl: int = 300): ...
    def set(self, key: str, value, ttl: int = None): ...
    def get(self, key: str, default=None): ...
    def delete(self, key: str) -> bool: ...
    def clear(self): ...
    def has_key(self, key: str) -> bool: ...
    def get_or_set(self, key: str, default_fn: callable, ttl: int = None): ...
    def incr(self, key: str, delta: int = 1) -> int: ...
    def decr(self, key: str, delta: int = 1) -> int: ...

def cache_page(ttl: int = 300):
    """Decorador que cachea el resultado de una función-vista."""

class CacheMiddleware:
    """Middleware que cachea respuestas GET."""
    def __init__(self, cache: CacheBackend, ttl: int = 300): ...
    def process_request(self, request: dict) -> dict | None: ...
    def process_response(self, request: dict, response: dict): ...

def cached_property(func):
    """Decorador que convierte un método en propiedad cacheada."""
```
MD,
            'starter_code' => <<<'PYTHON'
import time


class CacheBackend:
    def __init__(self, default_ttl=300):
        self.default_ttl = default_ttl
        self._cache = {}

    def set(self, key, value, ttl=None):
        pass

    def get(self, key, default=None):
        pass

    def delete(self, key):
        pass

    def clear(self):
        pass

    def has_key(self, key):
        pass

    def get_or_set(self, key, default_fn, ttl=None):
        pass

    def incr(self, key, delta=1):
        pass

    def decr(self, key, delta=1):
        pass


def cache_page(ttl=300):
    pass


class CacheMiddleware:
    def __init__(self, cache, ttl=300):
        self.cache = cache
        self.ttl = ttl

    def process_request(self, request):
        pass

    def process_response(self, request, response):
        pass


def cached_property(func):
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import time


class CacheBackend:
    def __init__(self, default_ttl=300):
        self.default_ttl = default_ttl
        self._cache = {}

    def _is_expired(self, key):
        if key not in self._cache:
            return True
        entry = self._cache[key]
        if entry['expires'] is not None and time.time() > entry['expires']:
            del self._cache[key]
            return True
        return False

    def set(self, key, value, ttl=None):
        t = ttl if ttl is not None else self.default_ttl
        expires = time.time() + t if t > 0 else None
        self._cache[key] = {'value': value, 'expires': expires}

    def get(self, key, default=None):
        if self._is_expired(key):
            return default
        return self._cache[key]['value']

    def delete(self, key):
        if key in self._cache:
            del self._cache[key]
            return True
        return False

    def clear(self):
        self._cache.clear()

    def has_key(self, key):
        return not self._is_expired(key)

    def get_or_set(self, key, default_fn, ttl=None):
        value = self.get(key)
        if value is None:
            value = default_fn()
            self.set(key, value, ttl)
        return value

    def incr(self, key, delta=1):
        val = self.get(key, 0)
        val += delta
        self.set(key, val)
        return val

    def decr(self, key, delta=1):
        return self.incr(key, -delta)


_page_cache = CacheBackend()

def cache_page(ttl=300):
    def decorator(view_fn):
        def wrapper(*args, **kwargs):
            cache_key = f'page:{view_fn.__name__}:{args}:{kwargs}'
            cached = _page_cache.get(cache_key)
            if cached is not None:
                return cached
            result = view_fn(*args, **kwargs)
            _page_cache.set(cache_key, result, ttl)
            return result
        return wrapper
    return decorator


class CacheMiddleware:
    def __init__(self, cache, ttl=300):
        self.cache = cache
        self.ttl = ttl

    def process_request(self, request):
        if request.get('method', 'GET') != 'GET':
            return None
        key = f"mw:{request.get('path', '/')}"
        return self.cache.get(key)

    def process_response(self, request, response):
        if request.get('method', 'GET') == 'GET':
            key = f"mw:{request.get('path', '/')}"
            self.cache.set(key, response, self.ttl)
        return response


def cached_property(func):
    attr_name = f'_cached_{func.__name__}'
    @property
    def wrapper(self):
        if not hasattr(self, attr_name):
            setattr(self, attr_name, func(self))
        return getattr(self, attr_name)
    return wrapper
PYTHON,
        ];

        // ── 16 · Seguridad ───────────────────────────────────────
        if ($l = $lessons->get(16)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Seguridad web Django',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa utilidades de seguridad al estilo Django.

```python
import hashlib, hmac, secrets, html, re

def generate_csrf_token() -> str:
    """Genera un token CSRF aleatorio de 64 caracteres hex."""

def validate_csrf(session_token: str, request_token: str) -> bool:
    """Compara tokens de forma segura (timing-safe)."""

def escape_html(text: str) -> str:
    """Escapa caracteres HTML peligrosos: & < > " '."""

def sanitize_sql(value: str) -> str:
    """Escapa comillas simples para prevenir inyección SQL básica."""

def hash_password(password: str, salt: str = None) -> dict:
    """Hashea con SHA-256 + salt. Retorna {'hash': str, 'salt': str}."""

def verify_password(password: str, stored_hash: str, salt: str) -> bool:
    """Verifica password contra hash almacenado."""

class SecurityMiddleware:
    """Middleware que agrega headers de seguridad a la respuesta."""
    SECURITY_HEADERS = {
        'X-Content-Type-Options': 'nosniff',
        'X-Frame-Options': 'DENY',
        'X-XSS-Protection': '1; mode=block',
        'Strict-Transport-Security': 'max-age=31536000; includeSubDomains',
    }
    def process_response(self, response: dict) -> dict: ...

class RateLimiter:
    """Limita peticiones por IP."""
    def __init__(self, max_requests: int = 100, window: int = 3600): ...
    def is_allowed(self, ip: str) -> bool: ...
    def reset(self, ip: str): ...
```
MD,
            'starter_code' => <<<'PYTHON'
import hashlib
import hmac
import secrets
import html
import time


def generate_csrf_token():
    pass

def validate_csrf(session_token, request_token):
    pass

def escape_html(text):
    pass

def sanitize_sql(value):
    pass

def hash_password(password, salt=None):
    pass

def verify_password(password, stored_hash, salt):
    pass


class SecurityMiddleware:
    SECURITY_HEADERS = {
        'X-Content-Type-Options': 'nosniff',
        'X-Frame-Options': 'DENY',
        'X-XSS-Protection': '1; mode=block',
        'Strict-Transport-Security': 'max-age=31536000; includeSubDomains',
    }

    def process_response(self, response):
        pass


class RateLimiter:
    def __init__(self, max_requests=100, window=3600):
        self.max_requests = max_requests
        self.window = window
        self._requests = {}

    def is_allowed(self, ip):
        pass

    def reset(self, ip):
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import hashlib
import hmac
import secrets
import html
import time


def generate_csrf_token():
    return secrets.token_hex(32)


def validate_csrf(session_token, request_token):
    return hmac.compare_digest(session_token, request_token)


def escape_html(text):
    return html.escape(text, quote=True)


def sanitize_sql(value):
    return value.replace("'", "''")


def hash_password(password, salt=None):
    if salt is None:
        salt = secrets.token_hex(16)
    hashed = hashlib.sha256(f'{salt}{password}'.encode()).hexdigest()
    return {'hash': hashed, 'salt': salt}


def verify_password(password, stored_hash, salt):
    result = hash_password(password, salt)
    return hmac.compare_digest(result['hash'], stored_hash)


class SecurityMiddleware:
    SECURITY_HEADERS = {
        'X-Content-Type-Options': 'nosniff',
        'X-Frame-Options': 'DENY',
        'X-XSS-Protection': '1; mode=block',
        'Strict-Transport-Security': 'max-age=31536000; includeSubDomains',
    }

    def process_response(self, response):
        if 'headers' not in response:
            response['headers'] = {}
        for key, value in self.SECURITY_HEADERS.items():
            response['headers'][key] = value
        return response


class RateLimiter:
    def __init__(self, max_requests=100, window=3600):
        self.max_requests = max_requests
        self.window = window
        self._requests = {}

    def is_allowed(self, ip):
        now = time.time()
        if ip not in self._requests:
            self._requests[ip] = []
        self._requests[ip] = [
            t for t in self._requests[ip] if now - t < self.window
        ]
        if len(self._requests[ip]) >= self.max_requests:
            return False
        self._requests[ip].append(now)
        return True

    def reset(self, ip):
        self._requests.pop(ip, None)
PYTHON,
        ];

        // ── 17 · Channels y WebSockets ────────────────────────────
        if ($l = $lessons->get(17)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'WebSockets con Channels',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa un sistema de WebSockets simplificado al estilo Django Channels.

```python
class WebSocketConsumer:
    """Consumer WebSocket base."""
    def __init__(self):
        self.channel_name = ''
        self.groups = []
        self.connected = False

    def connect(self): ...
    def disconnect(self, code: int = 1000): ...
    def receive(self, text_data: str = None, bytes_data: bytes = None): ...
    def send(self, text_data: str = None, bytes_data: bytes = None): ...

class JsonWebSocketConsumer(WebSocketConsumer):
    """Consumer que serializa/deserializa JSON automáticamente."""
    def receive_json(self, content: dict): ...
    def send_json(self, content: dict): ...

class ChannelLayer:
    """Capa de canales para comunicación entre consumers."""
    def __init__(self): ...
    def add(self, group: str, channel: str): ...
    def discard(self, group: str, channel: str): ...
    def group_send(self, group: str, message: dict) -> int: ...
    def send(self, channel: str, message: dict) -> bool: ...

class ChatConsumer(JsonWebSocketConsumer):
    """Consumer de chat que gestiona rooms."""
    def __init__(self, channel_layer: ChannelLayer): ...
    def connect(self, room_name: str): ...
    def disconnect(self, code=1000): ...
    def receive_json(self, content: dict): ...
    def get_messages(self) -> list: ...
```
MD,
            'starter_code' => <<<'PYTHON'
import json
import uuid


class WebSocketConsumer:
    def __init__(self):
        self.channel_name = str(uuid.uuid4())[:8]
        self.groups = []
        self.connected = False
        self._sent = []

    def connect(self):
        pass

    def disconnect(self, code=1000):
        pass

    def receive(self, text_data=None, bytes_data=None):
        pass

    def send(self, text_data=None, bytes_data=None):
        pass


class JsonWebSocketConsumer(WebSocketConsumer):
    def receive_json(self, content):
        pass

    def send_json(self, content):
        pass


class ChannelLayer:
    def __init__(self):
        self._groups = {}
        self._channels = {}

    def add(self, group, channel):
        pass

    def discard(self, group, channel):
        pass

    def group_send(self, group, message):
        pass

    def send(self, channel, message):
        pass


class ChatConsumer(JsonWebSocketConsumer):
    def __init__(self, channel_layer):
        super().__init__()
        self.channel_layer = channel_layer
        self.room_name = None
        self._messages = []

    def connect(self, room_name):
        pass

    def disconnect(self, code=1000):
        pass

    def receive_json(self, content):
        pass

    def get_messages(self):
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import json
import uuid


class WebSocketConsumer:
    def __init__(self):
        self.channel_name = str(uuid.uuid4())[:8]
        self.groups = []
        self.connected = False
        self._sent = []

    def connect(self):
        self.connected = True

    def disconnect(self, code=1000):
        self.connected = False
        self.groups.clear()

    def receive(self, text_data=None, bytes_data=None):
        return text_data or bytes_data

    def send(self, text_data=None, bytes_data=None):
        if self.connected:
            self._sent.append(text_data or bytes_data)


class JsonWebSocketConsumer(WebSocketConsumer):
    def receive(self, text_data=None, bytes_data=None):
        if text_data:
            return self.receive_json(json.loads(text_data))
        return super().receive(text_data, bytes_data)

    def receive_json(self, content):
        return content

    def send_json(self, content):
        self.send(text_data=json.dumps(content))


class ChannelLayer:
    def __init__(self):
        self._groups = {}
        self._channels = {}

    def add(self, group, channel):
        if group not in self._groups:
            self._groups[group] = set()
        self._groups[group].add(channel)

    def discard(self, group, channel):
        if group in self._groups:
            self._groups[group].discard(channel)
            if not self._groups[group]:
                del self._groups[group]

    def group_send(self, group, message):
        channels = self._groups.get(group, set())
        for ch in channels:
            self.send(ch, message)
        return len(channels)

    def send(self, channel, message):
        if channel not in self._channels:
            self._channels[channel] = []
        self._channels[channel].append(message)
        return True


class ChatConsumer(JsonWebSocketConsumer):
    def __init__(self, channel_layer):
        super().__init__()
        self.channel_layer = channel_layer
        self.room_name = None
        self._messages = []

    def connect(self, room_name):
        super().connect()
        self.room_name = room_name
        self.channel_layer.add(room_name, self.channel_name)

    def disconnect(self, code=1000):
        if self.room_name:
            self.channel_layer.discard(self.room_name, self.channel_name)
        super().disconnect(code)

    def receive_json(self, content):
        message = {
            'type': 'chat_message',
            'sender': self.channel_name,
            'message': content.get('message', ''),
            'room': self.room_name,
        }
        self._messages.append(message)
        self.channel_layer.group_send(self.room_name, message)
        return message

    def get_messages(self):
        return list(self._messages)
PYTHON,
        ];

        // ── 18 · Deploy y Producción ─────────────────────────────
        if ($l = $lessons->get(18)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Configuración deploy Django',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa utilidades para configurar deploy de Django en producción.

```python
class Settings:
    """Gestiona settings de Django por entorno."""
    def __init__(self, base_settings: dict = None):
        self.settings = base_settings or {}

    def configure(self, env: str = 'development') -> dict:
        """Aplica configuración según entorno (development/staging/production)."""
    def validate_production(self) -> list:
        """Retorna lista de warnings si hay config insegura para producción."""
    def get(self, key: str, default=None): ...
    def set(self, key: str, value): ...

class GunicornConfig:
    """Genera configuración de Gunicorn."""
    def __init__(self, app_name: str, **kwargs): ...
    def get_config(self) -> dict: ...
    def to_command(self) -> str: ...

class DockerConfig:
    """Genera Dockerfile y docker-compose para Django."""
    def __init__(self, project_name: str, python_version: str = '3.12'): ...
    def generate_dockerfile(self) -> str: ...
    def generate_compose(self, services: list = None) -> str: ...

class HealthCheck:
    """Sistema de health checks."""
    def __init__(self): self.checks = {}
    def register(self, name: str, check_fn: callable): ...
    def run_all(self) -> dict: ...
    def is_healthy(self) -> bool: ...
```
MD,
            'starter_code' => <<<'PYTHON'
class Settings:
    def __init__(self, base_settings=None):
        self.settings = base_settings or {}

    def configure(self, env='development'):
        pass

    def validate_production(self):
        pass

    def get(self, key, default=None):
        pass

    def set(self, key, value):
        pass


class GunicornConfig:
    def __init__(self, app_name, **kwargs):
        self.app_name = app_name
        self.options = kwargs

    def get_config(self):
        pass

    def to_command(self):
        pass


class DockerConfig:
    def __init__(self, project_name, python_version='3.12'):
        self.project_name = project_name
        self.python_version = python_version

    def generate_dockerfile(self):
        pass

    def generate_compose(self, services=None):
        pass


class HealthCheck:
    def __init__(self):
        self.checks = {}

    def register(self, name, check_fn):
        pass

    def run_all(self):
        pass

    def is_healthy(self):
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class Settings:
    DEFAULTS = {
        'development': {
            'DEBUG': True,
            'ALLOWED_HOSTS': ['*'],
            'SECURE_SSL_REDIRECT': False,
            'SESSION_COOKIE_SECURE': False,
            'CSRF_COOKIE_SECURE': False,
        },
        'staging': {
            'DEBUG': False,
            'ALLOWED_HOSTS': [],
            'SECURE_SSL_REDIRECT': True,
            'SESSION_COOKIE_SECURE': True,
            'CSRF_COOKIE_SECURE': True,
        },
        'production': {
            'DEBUG': False,
            'ALLOWED_HOSTS': [],
            'SECURE_SSL_REDIRECT': True,
            'SESSION_COOKIE_SECURE': True,
            'CSRF_COOKIE_SECURE': True,
            'SECURE_HSTS_SECONDS': 31536000,
            'SECURE_CONTENT_TYPE_NOSNIFF': True,
        },
    }

    def __init__(self, base_settings=None):
        self.settings = base_settings or {}

    def configure(self, env='development'):
        env_defaults = self.DEFAULTS.get(env, {})
        merged = {**env_defaults, **self.settings}
        self.settings = merged
        return dict(merged)

    def validate_production(self):
        warnings = []
        if self.settings.get('DEBUG', False):
            warnings.append('DEBUG must be False in production')
        if not self.settings.get('ALLOWED_HOSTS'):
            warnings.append('ALLOWED_HOSTS must not be empty')
        if not self.settings.get('SECRET_KEY'):
            warnings.append('SECRET_KEY is required')
        if not self.settings.get('SECURE_SSL_REDIRECT', False):
            warnings.append('SECURE_SSL_REDIRECT should be True')
        if not self.settings.get('SESSION_COOKIE_SECURE', False):
            warnings.append('SESSION_COOKIE_SECURE should be True')
        return warnings

    def get(self, key, default=None):
        return self.settings.get(key, default)

    def set(self, key, value):
        self.settings[key] = value


class GunicornConfig:
    def __init__(self, app_name, **kwargs):
        self.app_name = app_name
        self.options = {
            'bind': '0.0.0.0:8000',
            'workers': 4,
            'worker_class': 'gthread',
            'threads': 2,
            'timeout': 120,
            'accesslog': '-',
            'errorlog': '-',
        }
        self.options.update(kwargs)

    def get_config(self):
        return {**self.options, 'app': f'{self.app_name}.wsgi:application'}

    def to_command(self):
        parts = [f'gunicorn {self.app_name}.wsgi:application']
        for key, val in self.options.items():
            parts.append(f'--{key} {val}')
        return ' '.join(parts)


class DockerConfig:
    def __init__(self, project_name, python_version='3.12'):
        self.project_name = project_name
        self.python_version = python_version

    def generate_dockerfile(self):
        return f"""FROM python:{self.python_version}-slim
WORKDIR /app
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt
COPY . .
RUN python manage.py collectstatic --noinput
EXPOSE 8000
CMD ["gunicorn", "{self.project_name}.wsgi:application", "--bind", "0.0.0.0:8000"]"""

    def generate_compose(self, services=None):
        svcs = services or ['web', 'db']
        lines = ['version: "3.8"', 'services:']
        if 'db' in svcs:
            lines.append('  db:')
            lines.append('    image: postgres:15')
            lines.append('    environment:')
            lines.append('      POSTGRES_DB: ' + self.project_name)
            lines.append('      POSTGRES_PASSWORD: changeme')
            lines.append('    volumes:')
            lines.append('      - pgdata:/var/lib/postgresql/data')
        if 'web' in svcs:
            lines.append('  web:')
            lines.append('    build: .')
            lines.append('    ports:')
            lines.append('      - "8000:8000"')
            if 'db' in svcs:
                lines.append('    depends_on:')
                lines.append('      - db')
        if 'redis' in svcs:
            lines.append('  redis:')
            lines.append('    image: redis:7-alpine')
        if 'db' in svcs:
            lines.append('volumes:')
            lines.append('  pgdata:')
        return '\n'.join(lines)


class HealthCheck:
    def __init__(self):
        self.checks = {}

    def register(self, name, check_fn):
        self.checks[name] = check_fn

    def run_all(self):
        results = {}
        for name, fn in self.checks.items():
            try:
                fn()
                results[name] = {'status': 'ok'}
            except Exception as e:
                results[name] = {'status': 'error', 'message': str(e)}
        return results

    def is_healthy(self):
        results = self.run_all()
        return all(r['status'] == 'ok' for r in results.values())
PYTHON,
        ];

        // ── 19 · Preguntas de Entrevista ─────────────────────────
        if ($l = $lessons->get(19)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Preguntas de entrevista Django',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa funciones que demuestren conocimiento de Django para entrevistas.

```python
def explain_mvt() -> dict:
    """Retorna explicación del patrón MVT con keys: model, view, template, flow."""

def orm_vs_raw() -> dict:
    """Compara ORM vs SQL raw. Keys: orm_pros, orm_cons, raw_pros, raw_cons, recommendation."""

def solve_n_plus_one(queries: list[dict]) -> dict:
    """Dado queries=[{'model': 'Author', 'related': 'books'},...],
       retorna {'original_count': N, 'optimized_count': N, 'technique': str, 'code': str}."""

def middleware_order() -> list[dict]:
    """Retorna lista ordenada de middleware Django con name y purpose."""

def optimize_queryset(queryset_desc: dict) -> dict:
    """Dado {'model': str, 'operations': [str], 'filters': dict},
       retorna {'suggestions': [...], 'optimized_code': str, 'indexes': [...]}."""

class DjangoQuiz:
    """Quiz de Django con preguntas y evaluación."""
    def __init__(self): ...
    def get_questions(self) -> list[dict]: ...
    def answer(self, question_id: int, answer: str) -> bool: ...
    def score(self) -> dict: ...
```
MD,
            'starter_code' => <<<'PYTHON'
def explain_mvt():
    pass

def orm_vs_raw():
    pass

def solve_n_plus_one(queries):
    pass

def middleware_order():
    pass

def optimize_queryset(queryset_desc):
    pass


class DjangoQuiz:
    def __init__(self):
        self.questions = []
        self.answers = {}
        self._build_questions()

    def _build_questions(self):
        pass

    def get_questions(self):
        pass

    def answer(self, question_id, user_answer):
        pass

    def score(self):
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
def explain_mvt():
    return {
        'model': 'Capa de datos: define estructura, validaciones y lógica de negocio. Interactúa con la BD vía ORM.',
        'view': 'Capa de lógica: recibe requests, procesa datos con modelos y retorna responses.',
        'template': 'Capa de presentación: genera HTML dinámico con el Django Template Language.',
        'flow': 'URL → View → Model (datos) → Template (render) → Response',
    }


def orm_vs_raw():
    return {
        'orm_pros': ['Seguridad contra SQL injection', 'Portabilidad entre BDs', 'Código Pythónico legible', 'Migraciones automáticas'],
        'orm_cons': ['Overhead de rendimiento', 'Queries complejas difíciles', 'Curva de aprendizaje'],
        'raw_pros': ['Máximo rendimiento', 'Queries complejas directas', 'Control total'],
        'raw_cons': ['Riesgo de SQL injection', 'Sin portabilidad', 'Mantenimiento difícil'],
        'recommendation': 'Usar ORM por defecto; SQL raw solo para queries críticas de rendimiento con .raw() o connection.cursor().',
    }


def solve_n_plus_one(queries):
    original = 1 + len(queries)
    models = [q['model'] for q in queries]
    related = [q['related'] for q in queries]
    code_parts = []
    for q in queries:
        code_parts.append(f".select_related('{q['related']}')")
    return {
        'original_count': original,
        'optimized_count': 2,
        'technique': 'select_related / prefetch_related',
        'code': f"{queries[0]['model']}.objects.all(){''.join(code_parts)}",
    }


def middleware_order():
    return [
        {'name': 'SecurityMiddleware', 'purpose': 'Headers de seguridad y HTTPS redirect'},
        {'name': 'SessionMiddleware', 'purpose': 'Gestión de sesiones'},
        {'name': 'CommonMiddleware', 'purpose': 'URL rewriting, Content-Length'},
        {'name': 'CsrfViewMiddleware', 'purpose': 'Protección CSRF'},
        {'name': 'AuthenticationMiddleware', 'purpose': 'Asocia usuario a request'},
        {'name': 'MessageMiddleware', 'purpose': 'Soporte de mensajes flash'},
        {'name': 'XFrameOptionsMiddleware', 'purpose': 'Protección clickjacking'},
    ]


def optimize_queryset(queryset_desc):
    model = queryset_desc.get('model', 'Model')
    operations = queryset_desc.get('operations', [])
    filters = queryset_desc.get('filters', {})
    suggestions = []
    indexes = []
    optimized_parts = [f'{model}.objects']
    if 'select_related' in operations or any('fk' in op for op in operations):
        suggestions.append('Usar select_related() para ForeignKey')
    if 'prefetch_related' in operations or any('m2m' in op for op in operations):
        suggestions.append('Usar prefetch_related() para ManyToMany')
    if filters:
        filter_str = ', '.join(f'{k}={v!r}' for k, v in filters.items())
        optimized_parts.append(f'.filter({filter_str})')
        for field in filters:
            indexes.append(f'db_index=True en {field}')
            suggestions.append(f'Agregar índice a {field}')
    if 'only' in operations:
        suggestions.append('Usar .only() o .defer() para limitar campos')
        optimized_parts.append('.only("id", "name")')
    if 'count' in operations:
        suggestions.append('Usar .count() en lugar de len(queryset)')
    optimized_parts.append('.all()')
    return {
        'suggestions': suggestions,
        'optimized_code': ''.join(optimized_parts),
        'indexes': indexes,
    }


class DjangoQuiz:
    def __init__(self):
        self.questions = []
        self.answers = {}
        self._build_questions()

    def _build_questions(self):
        self.questions = [
            {'id': 1, 'question': '¿Qué patrón arquitectónico usa Django?', 'correct': 'MVT'},
            {'id': 2, 'question': '¿Qué método previene N+1 para ForeignKey?', 'correct': 'select_related'},
            {'id': 3, 'question': '¿Qué archivo define las rutas URL?', 'correct': 'urls.py'},
            {'id': 4, 'question': '¿Qué comando crea migraciones?', 'correct': 'makemigrations'},
            {'id': 5, 'question': '¿Qué middleware protege contra CSRF?', 'correct': 'CsrfViewMiddleware'},
        ]

    def get_questions(self):
        return [{'id': q['id'], 'question': q['question']} for q in self.questions]

    def answer(self, question_id, user_answer):
        for q in self.questions:
            if q['id'] == question_id:
                correct = user_answer.lower().strip() == q['correct'].lower().strip()
                self.answers[question_id] = correct
                return correct
        return False

    def score(self):
        total = len(self.questions)
        answered = len(self.answers)
        correct = sum(1 for v in self.answers.values() if v)
        return {
            'total': total,
            'answered': answered,
            'correct': correct,
            'percentage': round((correct / total) * 100, 1) if total > 0 else 0,
        }
PYTHON,
        ];

        return $ex;
    }
}
