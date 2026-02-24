<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PythonExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'python-desde-cero')->first();

        if (! $course) {
            $this->command->warn('Python course not found. Run CourseSeeder + PythonLessonSeeder first.');
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

        $this->command->info('Python exercises seeded: ' . count($exercises) . ' exercises.');
    }

    private function exercises(\Illuminate\Support\Collection $lessons): array
    {
        $ex = [];

        // ── 1 · Introducción a Python ───────────────────────────────
        if ($l = $lessons->get(1)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Primeras funciones en Python',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa tres funciones básicas para familiarizarte con la sintaxis de Python.

```python
def greeting(name: str) -> str:
    """Retorna 'Hola, {name}! Bienvenido a Python.'"""

def calculator(a: float, b: float, op: str) -> float:
    """Calcula a op b. Operaciones: '+', '-', '*', '/'.
    Si op es '/' y b == 0, retorna 0.0.
    Si op no es válido, retorna 0.0."""

def type_name(value) -> str:
    """Retorna el nombre del tipo: 'int', 'float', 'str', 'bool', 'list', etc."""
```
MD,
            'starter_code' => <<<'PYTHON'
def greeting(name: str) -> str:
    """Retorna 'Hola, {name}! Bienvenido a Python.'"""
    pass


def calculator(a: float, b: float, op: str) -> float:
    """Calcula a op b. Operaciones: +, -, *, /. Div/0 y op inválido retornan 0.0."""
    pass


def type_name(value) -> str:
    """Retorna el nombre del tipo como string."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
def greeting(name: str) -> str:
    """Retorna 'Hola, {name}! Bienvenido a Python.'"""
    return f"Hola, {name}! Bienvenido a Python."


def calculator(a: float, b: float, op: str) -> float:
    """Calcula a op b. Operaciones: +, -, *, /. Div/0 y op inválido retornan 0.0."""
    if op == '+':
        return a + b
    elif op == '-':
        return a - b
    elif op == '*':
        return a * b
    elif op == '/':
        return a / b if b != 0 else 0.0
    return 0.0


def type_name(value) -> str:
    """Retorna el nombre del tipo como string."""
    return type(value).__name__
PYTHON,
        ];

        // ── 2 · Variables y Tipos de Datos ──────────────────────────
        if ($l = $lessons->get(2)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Manipulación de tipos y strings',
            'language'     => 'python',
            'description'  => <<<'MD'
Practica conversión de tipos y métodos de cadenas.

```python
def format_price(amount: float, currency: str = "USD") -> str:
    """Retorna el precio formateado: '$1,234.56 USD'. Usa coma como separador de miles."""

def safe_cast(value: str, target_type: str):
    """Convierte value al tipo indicado: 'int', 'float', 'bool'.
    Si falla, retorna None. Para 'bool': 'true'/'1' -> True, 'false'/'0' -> False."""

def title_slug(text: str) -> str:
    """Convierte texto a slug: minúsculas, espacios reemplazados por guiones,
    solo alfanuméricos y guiones. Ej: 'Hola Mundo!' -> 'hola-mundo'"""
```
MD,
            'starter_code' => <<<'PYTHON'
def format_price(amount: float, currency: str = "USD") -> str:
    """Retorna el precio formateado: '$1,234.56 USD'."""
    pass


def safe_cast(value: str, target_type: str):
    """Convierte value al tipo indicado. Retorna None si falla."""
    pass


def title_slug(text: str) -> str:
    """Convierte texto a slug: minúsculas, solo alfanuméricos y guiones."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
def format_price(amount: float, currency: str = "USD") -> str:
    """Retorna el precio formateado: '$1,234.56 USD'."""
    return f"${amount:,.2f} {currency}"


def safe_cast(value: str, target_type: str):
    """Convierte value al tipo indicado. Retorna None si falla."""
    try:
        if target_type == 'int':
            return int(value)
        elif target_type == 'float':
            return float(value)
        elif target_type == 'bool':
            if value.lower() in ('true', '1'):
                return True
            elif value.lower() in ('false', '0'):
                return False
            return None
    except (ValueError, TypeError):
        return None


def title_slug(text: str) -> str:
    """Convierte texto a slug: minúsculas, solo alfanuméricos y guiones."""
    import re
    text = text.lower().strip()
    text = re.sub(r'[^a-z0-9\s-]', '', text)
    text = re.sub(r'[\s]+', '-', text)
    text = re.sub(r'-+', '-', text)
    return text.strip('-')
PYTHON,
        ];

        // ── 3 · Estructuras de Control ──────────────────────────────
        if ($l = $lessons->get(3)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'FizzBuzz y clasificadores',
            'language'     => 'python',
            'description'  => <<<'MD'
Practica if/elif/else, bucles y range.

```python
def fizzbuzz(n: int) -> list[str]:
    """Retorna lista de 1 a n: 'Fizz' (mult 3), 'Buzz' (mult 5),
    'FizzBuzz' (ambos), o el número como string."""

def classify_number(n: int) -> str:
    """Retorna 'positivo par', 'positivo impar', 'negativo par',
    'negativo impar' o 'cero'."""

def factorial(n: int) -> int:
    """Calcula n! iterativamente. Si n < 0 retorna -1."""
```
MD,
            'starter_code' => <<<'PYTHON'
def fizzbuzz(n: int) -> list[str]:
    """Retorna lista FizzBuzz de 1 a n."""
    pass


def classify_number(n: int) -> str:
    """Clasifica un número: positivo/negativo par/impar o cero."""
    pass


def factorial(n: int) -> int:
    """Calcula n! iterativamente. n<0 retorna -1."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
def fizzbuzz(n: int) -> list[str]:
    """Retorna lista FizzBuzz de 1 a n."""
    result = []
    for i in range(1, n + 1):
        if i % 15 == 0:
            result.append("FizzBuzz")
        elif i % 3 == 0:
            result.append("Fizz")
        elif i % 5 == 0:
            result.append("Buzz")
        else:
            result.append(str(i))
    return result


def classify_number(n: int) -> str:
    """Clasifica un número: positivo/negativo par/impar o cero."""
    if n == 0:
        return "cero"
    sign = "positivo" if n > 0 else "negativo"
    parity = "par" if n % 2 == 0 else "impar"
    return f"{sign} {parity}"


def factorial(n: int) -> int:
    """Calcula n! iterativamente. n<0 retorna -1."""
    if n < 0:
        return -1
    result = 1
    for i in range(2, n + 1):
        result *= i
    return result
PYTHON,
        ];

        // ── 4 · Listas y Tuplas ─────────────────────────────────────
        if ($l = $lessons->get(4)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Operaciones con listas',
            'language'     => 'python',
            'description'  => <<<'MD'
Practica slicing, list comprehensions y operaciones con listas.

```python
def flatten(matrix: list[list]) -> list:
    """Aplana una lista de listas en una sola lista."""

def unique_sorted(items: list) -> list:
    """Retorna elementos únicos ordenados ascendentemente."""

def frequency(items: list) -> dict:
    """Retorna diccionario {elemento: cantidad} con la frecuencia de cada elemento."""
```
MD,
            'starter_code' => <<<'PYTHON'
def flatten(matrix: list[list]) -> list:
    """Aplana una lista de listas en una sola lista."""
    pass


def unique_sorted(items: list) -> list:
    """Retorna elementos únicos ordenados ascendentemente."""
    pass


def frequency(items: list) -> dict:
    """Retorna diccionario con la frecuencia de cada elemento."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
def flatten(matrix: list[list]) -> list:
    """Aplana una lista de listas en una sola lista."""
    return [item for row in matrix for item in row]


def unique_sorted(items: list) -> list:
    """Retorna elementos únicos ordenados ascendentemente."""
    return sorted(set(items))


def frequency(items: list) -> dict:
    """Retorna diccionario con la frecuencia de cada elemento."""
    freq = {}
    for item in items:
        freq[item] = freq.get(item, 0) + 1
    return freq
PYTHON,
        ];

        // ── 5 · Diccionarios y Sets ─────────────────────────────────
        if ($l = $lessons->get(5)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Diccionarios y conjuntos en acción',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa operaciones con diccionarios y sets.

```python
def word_count(text: str) -> dict[str, int]:
    """Cuenta la frecuencia de cada palabra (minúsculas, sin puntuación)."""

def merge_dicts(*dicts) -> dict:
    """Fusiona varios diccionarios. Si hay claves repetidas, el último valor gana."""

def set_operations(a: set, b: set) -> dict:
    """Retorna {'union': ..., 'intersection': ..., 'difference': ..., 'symmetric': ...}."""
```
MD,
            'starter_code' => <<<'PYTHON'
def word_count(text: str) -> dict[str, int]:
    """Cuenta frecuencia de cada palabra (minúsculas, sin puntuación)."""
    pass


def merge_dicts(*dicts) -> dict:
    """Fusiona varios diccionarios, último valor gana."""
    pass


def set_operations(a: set, b: set) -> dict:
    """Retorna dict con union, intersection, difference, symmetric."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import re


def word_count(text: str) -> dict[str, int]:
    """Cuenta frecuencia de cada palabra (minúsculas, sin puntuación)."""
    words = re.findall(r'[a-záéíóúñü]+', text.lower())
    counts = {}
    for w in words:
        counts[w] = counts.get(w, 0) + 1
    return counts


def merge_dicts(*dicts) -> dict:
    """Fusiona varios diccionarios, último valor gana."""
    result = {}
    for d in dicts:
        result.update(d)
    return result


def set_operations(a: set, b: set) -> dict:
    """Retorna dict con union, intersection, difference, symmetric."""
    return {
        'union': a | b,
        'intersection': a & b,
        'difference': a - b,
        'symmetric': a ^ b,
    }
PYTHON,
        ];

        // ── 6 · Funciones ───────────────────────────────────────────
        if ($l = $lessons->get(6)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Funciones de orden superior',
            'language'     => 'python',
            'description'  => <<<'MD'
Practica funciones como ciudadanos de primera clase.

```python
def apply_to_all(func, items: list) -> list:
    """Aplica func a cada elemento y retorna la nueva lista (como map)."""

def compose(*functions):
    """Retorna una función que es la composición de las funciones dadas.
    compose(f, g, h)(x) == f(g(h(x)))"""

def memoize(func):
    """Retorna una versión memoizada de func. Cachea resultados por argumentos."""
```
MD,
            'starter_code' => <<<'PYTHON'
def apply_to_all(func, items: list) -> list:
    """Aplica func a cada elemento y retorna la nueva lista."""
    pass


def compose(*functions):
    """Retorna composición de funciones: compose(f,g)(x) == f(g(x))."""
    pass


def memoize(func):
    """Retorna versión memoizada de func."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
from functools import reduce


def apply_to_all(func, items: list) -> list:
    """Aplica func a cada elemento y retorna la nueva lista."""
    return [func(item) for item in items]


def compose(*functions):
    """Retorna composición de funciones: compose(f,g)(x) == f(g(x))."""
    def composed(x):
        result = x
        for fn in reversed(functions):
            result = fn(result)
        return result
    return composed


def memoize(func):
    """Retorna versión memoizada de func."""
    cache = {}
    def wrapper(*args):
        if args not in cache:
            cache[args] = func(*args)
        return cache[args]
    return wrapper
PYTHON,
        ];

        // ── 7 · Módulos y Paquetes ──────────────────────────────────
        if ($l = $lessons->get(7)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulador de sistema de módulos',
            'language'     => 'python',
            'description'  => <<<'MD'
Simula un sistema de importación de módulos.

```python
class ModuleRegistry:
    """Registro simple de módulos."""

    def __init__(self):
        self.modules = {}

    def register(self, name: str, exports: dict) -> None:
        """Registra un módulo con sus exports."""

    def get(self, name: str) -> dict | None:
        """Retorna los exports del módulo o None si no existe."""

    def list_modules(self) -> list[str]:
        """Retorna lista de nombres de módulos registrados, ordenada."""

def resolve_import(path: str) -> dict:
    """Parsea 'package.subpackage.module' y retorna
    {'package': str, 'subpackages': list[str], 'module': str}.
    Si solo hay un componente, package y module son iguales, subpackages vacío."""
```
MD,
            'starter_code' => <<<'PYTHON'
class ModuleRegistry:
    """Registro simple de módulos."""

    def __init__(self):
        self.modules = {}

    def register(self, name: str, exports: dict) -> None:
        """Registra un módulo con sus exports."""
        pass

    def get(self, name: str) -> dict | None:
        """Retorna los exports del módulo o None."""
        pass

    def list_modules(self) -> list[str]:
        """Retorna lista ordenada de nombres de módulos."""
        pass


def resolve_import(path: str) -> dict:
    """Parsea 'package.subpackage.module' y retorna dict con package, subpackages, module."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class ModuleRegistry:
    """Registro simple de módulos."""

    def __init__(self):
        self.modules = {}

    def register(self, name: str, exports: dict) -> None:
        """Registra un módulo con sus exports."""
        self.modules[name] = exports

    def get(self, name: str) -> dict | None:
        """Retorna los exports del módulo o None."""
        return self.modules.get(name)

    def list_modules(self) -> list[str]:
        """Retorna lista ordenada de nombres de módulos."""
        return sorted(self.modules.keys())


def resolve_import(path: str) -> dict:
    """Parsea 'package.subpackage.module' y retorna dict con package, subpackages, module."""
    parts = path.split('.')
    if len(parts) == 1:
        return {'package': parts[0], 'subpackages': [], 'module': parts[0]}
    return {
        'package': parts[0],
        'subpackages': parts[1:-1],
        'module': parts[-1],
    }
PYTHON,
        ];

        // ── 8 · POO Básica ──────────────────────────────────────────
        if ($l = $lessons->get(8)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Clase BankAccount',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa una cuenta bancaria con POO.

```python
class BankAccount:
    def __init__(self, owner: str, balance: float = 0.0):
        """Inicializa con dueño y saldo. El saldo no puede ser negativo al crear."""

    @property
    def balance(self) -> float:
        """Retorna el saldo actual."""

    def deposit(self, amount: float) -> float:
        """Deposita amount (>0). Retorna nuevo saldo. Lanza ValueError si amount <= 0."""

    def withdraw(self, amount: float) -> float:
        """Retira amount. Retorna nuevo saldo.
        Lanza ValueError si amount <= 0 o si fondos insuficientes."""

    def transfer(self, other: 'BankAccount', amount: float) -> None:
        """Transfiere amount a otra cuenta. Usa withdraw y deposit internamente."""

    def __str__(self) -> str:
        """Retorna 'BankAccount({owner}: ${balance:.2f})'."""
```
MD,
            'starter_code' => <<<'PYTHON'
class BankAccount:
    def __init__(self, owner: str, balance: float = 0.0):
        """Inicializa con dueño y saldo (>= 0)."""
        pass

    @property
    def balance(self) -> float:
        """Retorna el saldo actual."""
        pass

    def deposit(self, amount: float) -> float:
        """Deposita amount (>0). Retorna nuevo saldo."""
        pass

    def withdraw(self, amount: float) -> float:
        """Retira amount. Retorna nuevo saldo."""
        pass

    def transfer(self, other: 'BankAccount', amount: float) -> None:
        """Transfiere amount a otra cuenta."""
        pass

    def __str__(self) -> str:
        """Retorna representación string."""
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class BankAccount:
    def __init__(self, owner: str, balance: float = 0.0):
        """Inicializa con dueño y saldo (>= 0)."""
        self._owner = owner
        self._balance = max(0.0, balance)

    @property
    def balance(self) -> float:
        """Retorna el saldo actual."""
        return self._balance

    def deposit(self, amount: float) -> float:
        """Deposita amount (>0). Retorna nuevo saldo."""
        if amount <= 0:
            raise ValueError("El monto debe ser positivo")
        self._balance += amount
        return self._balance

    def withdraw(self, amount: float) -> float:
        """Retira amount. Retorna nuevo saldo."""
        if amount <= 0:
            raise ValueError("El monto debe ser positivo")
        if amount > self._balance:
            raise ValueError("Fondos insuficientes")
        self._balance -= amount
        return self._balance

    def transfer(self, other: 'BankAccount', amount: float) -> None:
        """Transfiere amount a otra cuenta."""
        self.withdraw(amount)
        other.deposit(amount)

    def __str__(self) -> str:
        """Retorna representación string."""
        return f"BankAccount({self._owner}: ${self._balance:.2f})"
PYTHON,
        ];

        // ── 9 · POO Avanzada ────────────────────────────────────────
        if ($l = $lessons->get(9)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Jerarquía de figuras geométricas',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa una jerarquía de clases con herencia y métodos dunder.

```python
from abc import ABC, abstractmethod
import math

class Shape(ABC):
    @abstractmethod
    def area(self) -> float: ...
    @abstractmethod
    def perimeter(self) -> float: ...
    def __eq__(self, other) -> bool:
        """Dos figuras son iguales si tienen la misma área (con tolerancia 1e-9)."""
    def __lt__(self, other) -> bool:
        """Compara por área."""

class Circle(Shape):
    def __init__(self, radius: float): ...

class Rectangle(Shape):
    def __init__(self, width: float, height: float): ...

class Triangle(Shape):
    def __init__(self, a: float, b: float, c: float):
        """Triángulo por sus tres lados. Lanza ValueError si no forma triángulo válido."""
```
MD,
            'starter_code' => <<<'PYTHON'
from abc import ABC, abstractmethod
import math


class Shape(ABC):
    @abstractmethod
    def area(self) -> float: ...

    @abstractmethod
    def perimeter(self) -> float: ...

    def __eq__(self, other) -> bool:
        pass

    def __lt__(self, other) -> bool:
        pass


class Circle(Shape):
    def __init__(self, radius: float):
        pass

    def area(self) -> float:
        pass

    def perimeter(self) -> float:
        pass


class Rectangle(Shape):
    def __init__(self, width: float, height: float):
        pass

    def area(self) -> float:
        pass

    def perimeter(self) -> float:
        pass


class Triangle(Shape):
    def __init__(self, a: float, b: float, c: float):
        pass

    def area(self) -> float:
        pass

    def perimeter(self) -> float:
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
from abc import ABC, abstractmethod
import math


class Shape(ABC):
    @abstractmethod
    def area(self) -> float: ...

    @abstractmethod
    def perimeter(self) -> float: ...

    def __eq__(self, other) -> bool:
        if not isinstance(other, Shape):
            return NotImplemented
        return abs(self.area() - other.area()) < 1e-9

    def __lt__(self, other) -> bool:
        if not isinstance(other, Shape):
            return NotImplemented
        return self.area() < other.area()


class Circle(Shape):
    def __init__(self, radius: float):
        self.radius = radius

    def area(self) -> float:
        return math.pi * self.radius ** 2

    def perimeter(self) -> float:
        return 2 * math.pi * self.radius


class Rectangle(Shape):
    def __init__(self, width: float, height: float):
        self.width = width
        self.height = height

    def area(self) -> float:
        return self.width * self.height

    def perimeter(self) -> float:
        return 2 * (self.width + self.height)


class Triangle(Shape):
    def __init__(self, a: float, b: float, c: float):
        if a + b <= c or a + c <= b or b + c <= a:
            raise ValueError("No forma un triángulo válido")
        self.a = a
        self.b = b
        self.c = c

    def area(self) -> float:
        s = (self.a + self.b + self.c) / 2
        return math.sqrt(s * (s - self.a) * (s - self.b) * (s - self.c))

    def perimeter(self) -> float:
        return self.a + self.b + self.c
PYTHON,
        ];

        // ── 10 · Excepciones ────────────────────────────────────────
        if ($l = $lessons->get(10)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Manejo robusto de errores',
            'language'     => 'python',
            'description'  => <<<'MD'
Practica try/except y excepciones personalizadas.

```python
class ValidationError(Exception):
    def __init__(self, field: str, message: str):
        self.field = field
        self.message = message
        super().__init__(f"{field}: {message}")

def safe_divide(a: float, b: float) -> float | None:
    """Retorna a/b o None si b es 0."""

def validate_age(value) -> int:
    """Valida y retorna edad como int.
    Lanza ValidationError('age', ...) si no es convertible a int o si < 0 o > 150."""

def safe_chain(*functions):
    """Retorna función que aplica funciones en cadena.
    Si cualquier función lanza excepción, retorna ('error', str(excepcion)).
    Si todo va bien, retorna ('ok', resultado_final)."""
```
MD,
            'starter_code' => <<<'PYTHON'
class ValidationError(Exception):
    def __init__(self, field: str, message: str):
        self.field = field
        self.message = message
        super().__init__(f"{field}: {message}")


def safe_divide(a: float, b: float) -> float | None:
    """Retorna a/b o None si b es 0."""
    pass


def validate_age(value) -> int:
    """Valida y retorna edad como int."""
    pass


def safe_chain(*functions):
    """Retorna función que aplica funciones en cadena con manejo de errores."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
class ValidationError(Exception):
    def __init__(self, field: str, message: str):
        self.field = field
        self.message = message
        super().__init__(f"{field}: {message}")


def safe_divide(a: float, b: float) -> float | None:
    """Retorna a/b o None si b es 0."""
    try:
        return a / b
    except ZeroDivisionError:
        return None


def validate_age(value) -> int:
    """Valida y retorna edad como int."""
    try:
        age = int(value)
    except (ValueError, TypeError):
        raise ValidationError('age', 'No es un número válido')
    if age < 0:
        raise ValidationError('age', 'La edad no puede ser negativa')
    if age > 150:
        raise ValidationError('age', 'La edad no puede ser mayor a 150')
    return age


def safe_chain(*functions):
    """Retorna función que aplica funciones en cadena con manejo de errores."""
    def chained(value):
        result = value
        for fn in functions:
            try:
                result = fn(result)
            except Exception as e:
                return ('error', str(e))
        return ('ok', result)
    return chained
PYTHON,
        ];

        // ── 11 · Iteradores y Generadores ───────────────────────────
        if ($l = $lessons->get(11)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Generadores e iteradores personalizados',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa iteradores y generadores.

```python
def fibonacci(limit: int):
    """Generador que produce números de Fibonacci menores que limit."""

def chunked(iterable, size: int):
    """Generador que divide un iterable en listas de tamaño size.
    El último chunk puede ser menor."""

class InfiniteCounter:
    """Iterador infinito que cuenta desde start de step en step."""
    def __init__(self, start: int = 0, step: int = 1): ...
    def __iter__(self): ...
    def __next__(self) -> int: ...
```
MD,
            'starter_code' => <<<'PYTHON'
def fibonacci(limit: int):
    """Generador que produce números de Fibonacci menores que limit."""
    pass


def chunked(iterable, size: int):
    """Generador que divide un iterable en listas de tamaño size."""
    pass


class InfiniteCounter:
    """Iterador infinito que cuenta desde start de step en step."""

    def __init__(self, start: int = 0, step: int = 1):
        pass

    def __iter__(self):
        pass

    def __next__(self) -> int:
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
def fibonacci(limit: int):
    """Generador que produce números de Fibonacci menores que limit."""
    a, b = 0, 1
    while a < limit:
        yield a
        a, b = b, a + b


def chunked(iterable, size: int):
    """Generador que divide un iterable en listas de tamaño size."""
    chunk = []
    for item in iterable:
        chunk.append(item)
        if len(chunk) == size:
            yield chunk
            chunk = []
    if chunk:
        yield chunk


class InfiniteCounter:
    """Iterador infinito que cuenta desde start de step en step."""

    def __init__(self, start: int = 0, step: int = 1):
        self.current = start
        self.step = step

    def __iter__(self):
        return self

    def __next__(self) -> int:
        value = self.current
        self.current += self.step
        return value
PYTHON,
        ];

        // ── 12 · Decoradores ────────────────────────────────────────
        if ($l = $lessons->get(12)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Decoradores prácticos',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa decoradores útiles.

```python
import time
from functools import wraps

def timer(func):
    """Decorador que mide el tiempo de ejecución.
    Añade atributo 'last_elapsed' a la función con el tiempo en segundos."""

def retry(max_attempts: int = 3, delay: float = 0.1):
    """Decorador con parámetros. Reintenta la función hasta max_attempts veces.
    Espera delay segundos entre intentos. Lanza la última excepción si todos fallan."""

def validate_types(**expected):
    """Decorador que valida los tipos de los argumentos.
    Ej: @validate_types(x=int, y=str)
    Lanza TypeError si un argumento no coincide con el tipo esperado."""
```
MD,
            'starter_code' => <<<'PYTHON'
import time
from functools import wraps


def timer(func):
    """Decorador que mide el tiempo de ejecución."""
    pass


def retry(max_attempts: int = 3, delay: float = 0.1):
    """Decorador que reintenta la función hasta max_attempts veces."""
    pass


def validate_types(**expected):
    """Decorador que valida los tipos de los argumentos."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import time
from functools import wraps


def timer(func):
    """Decorador que mide el tiempo de ejecución."""
    @wraps(func)
    def wrapper(*args, **kwargs):
        start = time.perf_counter()
        result = func(*args, **kwargs)
        wrapper.last_elapsed = time.perf_counter() - start
        return result
    wrapper.last_elapsed = 0.0
    return wrapper


def retry(max_attempts: int = 3, delay: float = 0.1):
    """Decorador que reintenta la función hasta max_attempts veces."""
    def decorator(func):
        @wraps(func)
        def wrapper(*args, **kwargs):
            last_error = None
            for attempt in range(max_attempts):
                try:
                    return func(*args, **kwargs)
                except Exception as e:
                    last_error = e
                    if attempt < max_attempts - 1:
                        time.sleep(delay)
            raise last_error
        return wrapper
    return decorator


def validate_types(**expected):
    """Decorador que valida los tipos de los argumentos."""
    def decorator(func):
        @wraps(func)
        def wrapper(*args, **kwargs):
            import inspect
            sig = inspect.signature(func)
            bound = sig.bind(*args, **kwargs)
            bound.apply_defaults()
            for param, typ in expected.items():
                if param in bound.arguments:
                    if not isinstance(bound.arguments[param], typ):
                        raise TypeError(
                            f"Argumento '{param}' debe ser {typ.__name__}, "
                            f"recibido {type(bound.arguments[param]).__name__}"
                        )
            return func(*args, **kwargs)
        return wrapper
    return decorator
PYTHON,
        ];

        // ── 13 · Archivos e I/O ─────────────────────────────────────
        if ($l = $lessons->get(13)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Procesamiento de archivos',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa funciones de procesamiento de texto, CSV y JSON.

```python
def parse_csv(content: str, delimiter: str = ',') -> list[dict]:
    """Parsea contenido CSV (string). Primera línea son headers.
    Retorna lista de dicts con header como clave."""

def read_json_config(content: str) -> dict:
    """Parsea JSON string. Si el JSON es inválido, retorna {'error': str_del_error}."""

def analyze_log(log_content: str) -> dict:
    """Analiza log con formato 'LEVEL: mensaje' por línea.
    Retorna {'total': int, 'by_level': {'ERROR': int, 'WARNING': int, ...},
             'errors': [lista de mensajes de ERROR]}"""
```
MD,
            'starter_code' => <<<'PYTHON'
def parse_csv(content: str, delimiter: str = ',') -> list[dict]:
    """Parsea contenido CSV. Primera línea son headers."""
    pass


def read_json_config(content: str) -> dict:
    """Parsea JSON string. Retorna {'error': ...} si es inválido."""
    pass


def analyze_log(log_content: str) -> dict:
    """Analiza log 'LEVEL: mensaje'. Retorna total, by_level, errors."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import json


def parse_csv(content: str, delimiter: str = ',') -> list[dict]:
    """Parsea contenido CSV. Primera línea son headers."""
    lines = [line.strip() for line in content.strip().split('\n') if line.strip()]
    if not lines:
        return []
    headers = [h.strip() for h in lines[0].split(delimiter)]
    result = []
    for line in lines[1:]:
        values = [v.strip() for v in line.split(delimiter)]
        result.append(dict(zip(headers, values)))
    return result


def read_json_config(content: str) -> dict:
    """Parsea JSON string. Retorna {'error': ...} si es inválido."""
    try:
        return json.loads(content)
    except json.JSONDecodeError as e:
        return {'error': str(e)}


def analyze_log(log_content: str) -> dict:
    """Analiza log 'LEVEL: mensaje'. Retorna total, by_level, errors."""
    lines = [line.strip() for line in log_content.strip().split('\n') if line.strip()]
    by_level = {}
    errors = []
    for line in lines:
        if ': ' in line:
            level, message = line.split(': ', 1)
            level = level.strip()
            by_level[level] = by_level.get(level, 0) + 1
            if level == 'ERROR':
                errors.append(message)
    return {'total': len(lines), 'by_level': by_level, 'errors': errors}
PYTHON,
        ];

        // ── 14 · Expresiones Regulares ──────────────────────────────
        if ($l = $lessons->get(14)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Regex en acción',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa validadores y extractores con expresiones regulares.

```python
import re

def is_valid_email(email: str) -> bool:
    """Valida formato de email básico: usuario@dominio.extension"""

def extract_urls(text: str) -> list[str]:
    """Extrae todas las URLs (http:// o https://) del texto."""

def parse_log_line(line: str) -> dict | None:
    """Parsea línea con formato '[2024-01-15 10:30:45] LEVEL: mensaje'.
    Retorna {'date': str, 'time': str, 'level': str, 'message': str} o None."""
```
MD,
            'starter_code' => <<<'PYTHON'
import re


def is_valid_email(email: str) -> bool:
    """Valida formato de email básico."""
    pass


def extract_urls(text: str) -> list[str]:
    """Extrae todas las URLs http/https del texto."""
    pass


def parse_log_line(line: str) -> dict | None:
    """Parsea línea de log '[fecha hora] LEVEL: mensaje'."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import re


def is_valid_email(email: str) -> bool:
    """Valida formato de email básico."""
    pattern = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
    return bool(re.match(pattern, email))


def extract_urls(text: str) -> list[str]:
    """Extrae todas las URLs http/https del texto."""
    pattern = r'https?://[^\s<>"\')\]]+'
    return re.findall(pattern, text)


def parse_log_line(line: str) -> dict | None:
    """Parsea línea de log '[fecha hora] LEVEL: mensaje'."""
    pattern = r'\[(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2}:\d{2})\]\s+(\w+):\s+(.*)'
    match = re.match(pattern, line)
    if not match:
        return None
    return {
        'date': match.group(1),
        'time': match.group(2),
        'level': match.group(3),
        'message': match.group(4),
    }
PYTHON,
        ];

        // ── 15 · Testing con pytest ─────────────────────────────────
        if ($l = $lessons->get(15)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Funciones testeables y tests',
            'language'     => 'python',
            'description'  => <<<'MD'
Implementa funciones y sus tests.

```python
def is_palindrome(text: str) -> bool:
    """Retorna True si text es palíndromo (ignora mayúsculas, espacios y puntuación)."""

def caesar_cipher(text: str, shift: int) -> str:
    """Cifrado César: desplaza letras por shift posiciones. Mantiene mayúsculas/minúsculas.
    Solo afecta letras [a-zA-Z], otros caracteres se mantienen."""

def test_palindrome():
    """Test is_palindrome con al menos 5 asserts variados."""

def test_caesar():
    """Test caesar_cipher con al menos 5 asserts variados."""
```
MD,
            'starter_code' => <<<'PYTHON'
def is_palindrome(text: str) -> bool:
    """Retorna True si text es palíndromo (ignora mayúsculas, espacios y puntuación)."""
    pass


def caesar_cipher(text: str, shift: int) -> str:
    """Cifrado César: desplaza letras por shift posiciones."""
    pass


def test_palindrome():
    """Test is_palindrome con al menos 5 asserts."""
    pass


def test_caesar():
    """Test caesar_cipher con al menos 5 asserts."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import re


def is_palindrome(text: str) -> bool:
    """Retorna True si text es palíndromo (ignora mayúsculas, espacios y puntuación)."""
    cleaned = re.sub(r'[^a-zA-Z0-9]', '', text).lower()
    return cleaned == cleaned[::-1]


def caesar_cipher(text: str, shift: int) -> str:
    """Cifrado César: desplaza letras por shift posiciones."""
    result = []
    for ch in text:
        if ch.isalpha():
            base = ord('A') if ch.isupper() else ord('a')
            result.append(chr((ord(ch) - base + shift) % 26 + base))
        else:
            result.append(ch)
    return ''.join(result)


def test_palindrome():
    """Test is_palindrome con al menos 5 asserts."""
    assert is_palindrome("racecar") is True
    assert is_palindrome("A man a plan a canal Panama") is True
    assert is_palindrome("hello") is False
    assert is_palindrome("Was it a car or a cat I saw") is True
    assert is_palindrome("") is True
    assert is_palindrome("ab") is False


def test_caesar():
    """Test caesar_cipher con al menos 5 asserts."""
    assert caesar_cipher("abc", 1) == "bcd"
    assert caesar_cipher("xyz", 3) == "abc"
    assert caesar_cipher("ABC", 1) == "BCD"
    assert caesar_cipher("Hello, World!", 13) == "Uryyb, Jbeyq!"
    assert caesar_cipher("abc", 0) == "abc"
    assert caesar_cipher("abc", 26) == "abc"
PYTHON,
        ];

        // ── 16 · Concurrencia ───────────────────────────────────────
        if ($l = $lessons->get(16)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Concurrencia con asyncio y threads',
            'language'     => 'python',
            'description'  => <<<'MD'
Simula tareas concurrentes.

```python
import asyncio

async def fetch_all(urls: list[str]) -> list[dict]:
    """Simula fetch concurrente. Para cada url retorna
    {'url': url, 'status': 200, 'length': len(url)}.
    Usa asyncio.gather para simular concurrencia."""

def parallel_map(func, items: list, max_workers: int = 4) -> list:
    """Aplica func a cada item usando ThreadPoolExecutor.
    Retorna lista de resultados en el mismo orden."""

class TaskQueue:
    """Cola de tareas simple (no thread-safe, solo lógica)."""
    def __init__(self):
        self.tasks = []
        self.results = []

    def add(self, task_fn, *args): ...
    def run_all(self) -> list:
        """Ejecuta todas las tareas en orden y retorna resultados."""
```
MD,
            'starter_code' => <<<'PYTHON'
import asyncio
from concurrent.futures import ThreadPoolExecutor


async def fetch_all(urls: list[str]) -> list[dict]:
    """Simula fetch concurrente con asyncio.gather."""
    pass


def parallel_map(func, items: list, max_workers: int = 4) -> list:
    """Aplica func a cada item usando ThreadPoolExecutor."""
    pass


class TaskQueue:
    """Cola de tareas simple."""

    def __init__(self):
        self.tasks = []
        self.results = []

    def add(self, task_fn, *args):
        pass

    def run_all(self) -> list:
        """Ejecuta todas las tareas y retorna resultados."""
        pass
PYTHON,
            'solution_code' => <<<'PYTHON'
import asyncio
from concurrent.futures import ThreadPoolExecutor


async def fetch_all(urls: list[str]) -> list[dict]:
    """Simula fetch concurrente con asyncio.gather."""
    async def fetch_one(url: str) -> dict:
        await asyncio.sleep(0.01)
        return {'url': url, 'status': 200, 'length': len(url)}

    return await asyncio.gather(*[fetch_one(url) for url in urls])


def parallel_map(func, items: list, max_workers: int = 4) -> list:
    """Aplica func a cada item usando ThreadPoolExecutor."""
    with ThreadPoolExecutor(max_workers=max_workers) as executor:
        return list(executor.map(func, items))


class TaskQueue:
    """Cola de tareas simple."""

    def __init__(self):
        self.tasks = []
        self.results = []

    def add(self, task_fn, *args):
        self.tasks.append((task_fn, args))

    def run_all(self) -> list:
        """Ejecuta todas las tareas y retorna resultados."""
        self.results = []
        for fn, args in self.tasks:
            self.results.append(fn(*args))
        self.tasks.clear()
        return self.results
PYTHON,
        ];

        // ── 17 · Tipado y Typing ────────────────────────────────────
        if ($l = $lessons->get(17)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Estructuras tipadas',
            'language'     => 'python',
            'description'  => <<<'MD'
Practica el sistema de tipos de Python.

```python
from typing import TypeVar, Generic, Optional, Protocol

T = TypeVar('T')

class Stack(Generic[T]):
    """Pila genérica tipada."""
    def __init__(self): ...
    def push(self, item: T) -> None: ...
    def pop(self) -> Optional[T]: ...
    def peek(self) -> Optional[T]: ...
    def is_empty(self) -> bool: ...
    def size(self) -> int: ...

class Comparable(Protocol):
    def __lt__(self, other) -> bool: ...

def sorted_insert(items: list[T], value: T) -> list[T]:
    """Inserta value en la posición correcta asumiendo items está ordenada.
    Retorna nueva lista."""

def safe_get(data: dict[str, T], key: str, default: T) -> T:
    """Retorna data[key] si existe, sino default."""
```
MD,
            'starter_code' => <<<'PYTHON'
from typing import TypeVar, Generic, Optional

T = TypeVar('T')


class Stack(Generic[T]):
    """Pila genérica tipada."""

    def __init__(self):
        pass

    def push(self, item: T) -> None:
        pass

    def pop(self) -> Optional[T]:
        pass

    def peek(self) -> Optional[T]:
        pass

    def is_empty(self) -> bool:
        pass

    def size(self) -> int:
        pass


def sorted_insert(items: list, value, ) -> list:
    """Inserta value en posición correcta en lista ordenada."""
    pass


def safe_get(data: dict, key: str, default):
    """Retorna data[key] si existe, sino default."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
from typing import TypeVar, Generic, Optional

T = TypeVar('T')


class Stack(Generic[T]):
    """Pila genérica tipada."""

    def __init__(self):
        self._items: list[T] = []

    def push(self, item: T) -> None:
        self._items.append(item)

    def pop(self) -> Optional[T]:
        if self._items:
            return self._items.pop()
        return None

    def peek(self) -> Optional[T]:
        if self._items:
            return self._items[-1]
        return None

    def is_empty(self) -> bool:
        return len(self._items) == 0

    def size(self) -> int:
        return len(self._items)


def sorted_insert(items: list, value) -> list:
    """Inserta value en posición correcta en lista ordenada."""
    result = list(items)
    for i, item in enumerate(result):
        if value <= item:
            result.insert(i, value)
            return result
    result.append(value)
    return result


def safe_get(data: dict, key: str, default):
    """Retorna data[key] si existe, sino default."""
    return data.get(key, default)
PYTHON,
        ];

        // ── 18 · Buenas Prácticas ───────────────────────────────────
        if ($l = $lessons->get(18)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Refactorización y buenas prácticas',
            'language'     => 'python',
            'description'  => <<<'MD'
Refactoriza código para seguir las mejores prácticas de Python.

```python
def parse_config(config_str: str) -> dict:
    """Parsea config en formato 'key=value' por línea.
    Ignora líneas vacías y comentarios (comienzan con #).
    Los valores numéricos se convierten a int o float.
    Los valores 'true'/'false' se convierten a bool."""

def clean_data(records: list[dict], required_fields: list[str]) -> list[dict]:
    """Filtra registros que tienen todos los required_fields no vacíos.
    Retorna lista de dicts con solo los required_fields."""

def create_logger(name: str):
    """Retorna función log(level, message) que genera strings con formato:
    '[{name}] {LEVEL}: {message}'
    Levels válidos: 'debug', 'info', 'warning', 'error'.
    Level inválido se trata como 'info'."""
```
MD,
            'starter_code' => <<<'PYTHON'
def parse_config(config_str: str) -> dict:
    """Parsea config 'key=value' por línea. Convierte tipos automáticamente."""
    pass


def clean_data(records: list[dict], required_fields: list[str]) -> list[dict]:
    """Filtra registros con todos los campos requeridos no vacíos."""
    pass


def create_logger(name: str):
    """Retorna función log(level, message) con formato '[name] LEVEL: message'."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
def parse_config(config_str: str) -> dict:
    """Parsea config 'key=value' por línea. Convierte tipos automáticamente."""
    config = {}
    for line in config_str.strip().split('\n'):
        line = line.strip()
        if not line or line.startswith('#'):
            continue
        if '=' not in line:
            continue
        key, value = line.split('=', 1)
        key = key.strip()
        value = value.strip()

        if value.lower() in ('true', 'false'):
            config[key] = value.lower() == 'true'
        else:
            try:
                config[key] = int(value)
            except ValueError:
                try:
                    config[key] = float(value)
                except ValueError:
                    config[key] = value
    return config


def clean_data(records: list[dict], required_fields: list[str]) -> list[dict]:
    """Filtra registros con todos los campos requeridos no vacíos."""
    cleaned = []
    for record in records:
        if all(record.get(field) for field in required_fields):
            cleaned.append({field: record[field] for field in required_fields})
    return cleaned


def create_logger(name: str):
    """Retorna función log(level, message) con formato '[name] LEVEL: message'."""
    valid_levels = {'debug', 'info', 'warning', 'error'}

    def log(level: str, message: str) -> str:
        level = level.lower() if level.lower() in valid_levels else 'info'
        return f"[{name}] {level.upper()}: {message}"

    return log
PYTHON,
        ];

        // ── 19 · Preguntas de Entrevista ────────────────────────────
        if ($l = $lessons->get(19)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Retos clásicos de entrevista Python',
            'language'     => 'python',
            'description'  => <<<'MD'
Resuelve preguntas clásicas de entrevista.

```python
def two_sum(nums: list[int], target: int) -> tuple[int, int] | None:
    """Retorna tupla (i, j) donde nums[i] + nums[j] == target.
    Retorna la primera combinación encontrada o None."""

def is_anagram(s1: str, s2: str) -> bool:
    """Retorna True si s1 y s2 son anagramas (ignorando mayúsculas y espacios)."""

def max_subarray_sum(nums: list[int]) -> int:
    """Retorna la suma máxima de un subarray contiguo (algoritmo de Kadane).
    Si la lista está vacía, retorna 0."""
```
MD,
            'starter_code' => <<<'PYTHON'
def two_sum(nums: list[int], target: int) -> tuple[int, int] | None:
    """Retorna tupla (i, j) donde nums[i]+nums[j] == target, o None."""
    pass


def is_anagram(s1: str, s2: str) -> bool:
    """Retorna True si s1 y s2 son anagramas."""
    pass


def max_subarray_sum(nums: list[int]) -> int:
    """Suma máxima de subarray contiguo (Kadane). Lista vacía retorna 0."""
    pass
PYTHON,
            'solution_code' => <<<'PYTHON'
def two_sum(nums: list[int], target: int) -> tuple[int, int] | None:
    """Retorna tupla (i, j) donde nums[i]+nums[j] == target, o None."""
    seen = {}
    for i, num in enumerate(nums):
        complement = target - num
        if complement in seen:
            return (seen[complement], i)
        seen[num] = i
    return None


def is_anagram(s1: str, s2: str) -> bool:
    """Retorna True si s1 y s2 son anagramas."""
    clean1 = sorted(s1.lower().replace(' ', ''))
    clean2 = sorted(s2.lower().replace(' ', ''))
    return clean1 == clean2


def max_subarray_sum(nums: list[int]) -> int:
    """Suma máxima de subarray contiguo (Kadane). Lista vacía retorna 0."""
    if not nums:
        return 0
    max_sum = current = nums[0]
    for num in nums[1:]:
        current = max(num, current + num)
        max_sum = max(max_sum, current)
    return max_sum
PYTHON,
        ];

        return $ex;
    }
}
