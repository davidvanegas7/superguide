<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RubyExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'ruby-desde-cero')->first();

        if (! $course) {
            $this->command->warn('Ruby course not found. Run CourseSeeder + RubyLessonSeeder first.');
            return;
        }

        /** @var \Illuminate\Support\Collection<int,Lesson> $lessons */
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

        $this->command->info('Ruby exercises seeded: ' . count($exercises) . ' exercises.');
    }

    private function exercises(\Illuminate\Support\Collection $lessons): array
    {
        $ex = [];

        // ── Lección 1: Introducción a Ruby ─────────────────────────────
        if ($l = $lessons->get(1)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Primeros pasos con Ruby: strings, números y conversiones',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Practica los fundamentos de Ruby: interpolación de strings, métodos numéricos y conversiones de tipo.

Implementa:
1. `presentar(nombre, edad)` — retorna `"Hola, soy {nombre} y tengo {edad} años"` usando interpolación.
2. `info_numero(n)` — retorna un hash con las claves `:par`, `:positivo` y `:absoluto`.
3. `invertir_palabras(frase)` — invierte cada palabra pero mantiene el orden. Ej: `"hola mundo"` → `"aloh odnum"`.
MD,
            'starter_code' => <<<'RUBY'
# 1. presentar(nombre, edad) → String
def presentar(nombre, edad)
  # Tu código aquí
end

# 2. info_numero(n) → Hash
# Retorna { par: true/false, positivo: true/false, absoluto: valor }
def info_numero(n)
  # Tu código aquí
end

# 3. invertir_palabras(frase) → String
# "hola mundo" → "aloh odnum"
def invertir_palabras(frase)
  # Tu código aquí
end

# ── Tests ─────────────────────────────────────────────
puts presentar("Ana", 28) == "Hola, soy Ana y tengo 28 años"
puts info_numero(-5) == { par: false, positivo: false, absoluto: 5 }
puts info_numero(4) == { par: true, positivo: true, absoluto: 4 }
puts invertir_palabras("hola mundo") == "aloh odnum"
puts invertir_palabras("Ruby es genial") == "ybuR se laineg"
RUBY,
        ];

        // ── Lección 2: Tipos de datos y operadores ─────────────────────
        if ($l = $lessons->get(2)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Domina Arrays, Hashes y Symbols',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Practica la manipulación de las estructuras de datos fundamentales de Ruby.

Implementa:
1. `estadisticas(numeros)` — recibe un array y retorna un hash con `:min`, `:max`, `:suma` y `:promedio`.
2. `contar_caracteres(texto)` — retorna un hash con cada carácter (en lowercase) y su frecuencia, ignorando espacios.
3. `merge_profundo(h1, h2)` — fusiona dos hashes recursivamente (si ambos valores son hashes, se fusionan).
MD,
            'starter_code' => <<<'RUBY'
# 1. estadisticas(numeros) → Hash
def estadisticas(numeros)
  # Tu código aquí
end

# 2. contar_caracteres(texto) → Hash
# "Hola Mundo" → {"h"=>1, "o"=>2, "l"=>1, "a"=>1, "m"=>1, "u"=>1, "n"=>1, "d"=>1}
def contar_caracteres(texto)
  # Tu código aquí
end

# 3. merge_profundo(h1, h2) → Hash
# merge_profundo({a: {b: 1}}, {a: {c: 2}}) → {a: {b: 1, c: 2}}
def merge_profundo(h1, h2)
  # Tu código aquí
end

# ── Tests ─────────────────────────────────────────────
puts estadisticas([10, 5, 20, 15]) == { min: 5, max: 20, suma: 50, promedio: 12.5 }
puts contar_caracteres("Hola") == {"h"=>1, "o"=>1, "l"=>1, "a"=>1}
puts merge_profundo({a: {b: 1, c: 2}}, {a: {c: 3, d: 4}}) == {a: {b: 1, c: 3, d: 4}}
RUBY,
        ];

        // ── Lección 3: Estructuras de control ─────────────────────────
        if ($l = $lessons->get(3)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Iteradores, filtros y transformaciones',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Practica el uso de iteradores y estructuras de control con colecciones.

Implementa:
1. `fizzbuzz(n)` — retorna un array del 1 al n: "Fizz" si múltiplo de 3, "Buzz" de 5, "FizzBuzz" de ambos, el número como string en otro caso.
2. `aplanar(array)` — aplana un array anidado arbitrariamente sin usar `.flatten`. Usa recursión.
3. `agrupar_consecutivos(array)` — agrupa elementos consecutivos iguales. Ej: `[1,1,2,2,2,3,1]` → `[[1,1],[2,2,2],[3],[1]]`.
MD,
            'starter_code' => <<<'RUBY'
# 1. fizzbuzz(n) → Array
def fizzbuzz(n)
  # Tu código aquí
end

# 2. aplanar(array) → Array (sin usar .flatten)
def aplanar(array)
  # Tu código aquí
end

# 3. agrupar_consecutivos(array) → Array de Arrays
def agrupar_consecutivos(array)
  # Tu código aquí
end

# ── Tests ─────────────────────────────────────────────
puts fizzbuzz(15).last(3) == ["13", "14", "FizzBuzz"]
puts fizzbuzz(5) == ["1", "2", "Fizz", "4", "Buzz"]
puts aplanar([1, [2, [3, [4]]], 5]) == [1, 2, 3, 4, 5]
puts agrupar_consecutivos([1, 1, 2, 2, 2, 3, 1]) == [[1, 1], [2, 2, 2], [3], [1]]
RUBY,
        ];

        // ── Lección 4: Métodos y Bloques ──────────────────────────────
        if ($l = $lessons->get(4)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Bloques, yield y closures',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Practica el uso de bloques, yield y closures en Ruby.

Implementa:
1. `medir` — un método que recibe un bloque, lo ejecuta, y retorna un hash con `:resultado` y `:tiempo` (en segundos).
2. `mi_map(array)` — reimplementa `map` usando `yield`. No uses `.map`.
3. `crear_acumulador(inicial)` — retorna un lambda que acumula valores. Cada llamada suma al total y retorna el acumulado.
MD,
            'starter_code' => <<<'RUBY'
# 1. medir — ejecuta el bloque y retorna { resultado: ..., tiempo: ... }
def medir
  # Tu código aquí (usa Time.now)
end

# 2. mi_map(array) — reimplementa map con yield
def mi_map(array)
  # Tu código aquí
end

# 3. crear_acumulador(inicial) → Lambda
def crear_acumulador(inicial)
  # Tu código aquí
end

# ── Tests ─────────────────────────────────────────────
r = medir { sleep(0.1); 42 }
puts r[:resultado] == 42
puts r[:tiempo] >= 0.1

puts mi_map([1, 2, 3]) { |n| n * 2 } == [2, 4, 6]
puts mi_map(["a", "b"]) { |s| s.upcase } == ["A", "B"]

acc = crear_acumulador(10)
puts acc.call(5) == 15
puts acc.call(3) == 18
puts acc.call(2) == 20
RUBY,
        ];

        // ── Lección 5: POO ─────────────────────────────────────────────
        if ($l = $lessons->get(5)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Clases, herencia y polimorfismo',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Construye una jerarquía de clases para una biblioteca.

Implementa:
1. `Libro` — con `titulo`, `autor`, `paginas` (attr_reader). Método `to_s` que retorna `"titulo - autor (N págs)"`.
2. `Ebook < Libro` — añade `formato` (pdf/epub). Override `to_s` para incluir el formato.
3. `Biblioteca` — con `agregar(libro)`, `buscar(titulo)` (busca parcial, case-insensitive), `por_autor(autor)` y `to_s` mostrando el total.
MD,
            'starter_code' => <<<'RUBY'
class Libro
  # Tu código aquí
end

class Ebook < Libro
  # Tu código aquí
end

class Biblioteca
  # Tu código aquí
end

# ── Tests ─────────────────────────────────────────────
libro = Libro.new("Ruby Programming", "Matz", 350)
puts libro.to_s == "Ruby Programming - Matz (350 págs)"

ebook = Ebook.new("Rails Way", "Obie", 900, "pdf")
puts ebook.to_s == "Rails Way - Obie (900 págs) [pdf]"

bib = Biblioteca.new
bib.agregar(libro)
bib.agregar(ebook)
puts bib.buscar("ruby").first == libro
puts bib.por_autor("Matz").length == 1
RUBY,
        ];

        // ── Lección 6: Herencia y módulos avanzados ────────────────────
        if ($l = $lessons->get(6)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mixins, hooks y composición',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Practica el uso de módulos como mixins para componer comportamiento.

Implementa:
1. `Validatable` — módulo con método `validate!` que verifica que las variables de instancia marcadas con `validates` (método de clase) no sean nil. Lanza `RuntimeError` si alguna es nil.
2. `Serializable` — módulo con `to_h` que convierte todas las variables de instancia a un hash (key sin @).
3. `Producto` — clase que incluye ambos módulos, con `nombre`, `precio` y `stock`.
MD,
            'starter_code' => <<<'RUBY'
module Validatable
  def self.included(base)
    base.extend(ClassMethods)
    base.instance_variable_set(:@_validations, [])
  end

  module ClassMethods
    def validates(*fields)
      @_validations.concat(fields)
    end

    def validations
      @_validations
    end
  end

  def validate!
    # Tu código aquí: verifica que cada field de self.class.validations
    # no sea nil en esta instancia. Lanza RuntimeError si lo es.
  end
end

module Serializable
  def to_h
    # Tu código aquí: convierte instance_variables a hash
  end
end

class Producto
  include Validatable
  include Serializable

  attr_accessor :nombre, :precio, :stock

  validates :nombre, :precio

  def initialize(nombre: nil, precio: nil, stock: 0)
    @nombre = nombre
    @precio = precio
    @stock = stock
  end
end

# ── Tests ─────────────────────────────────────────────
p = Producto.new(nombre: "Laptop", precio: 999, stock: 5)
p.validate!   # no lanza error
puts p.to_h == { nombre: "Laptop", precio: 999, stock: 5 }

p2 = Producto.new(nombre: "Mouse")
begin
  p2.validate!
  puts false   # no debería llegar aquí
rescue RuntimeError
  puts true    # correcto: precio es nil
end
RUBY,
        ];

        // ── Lección 7: Manejo de errores ──────────────────────────────
        if ($l = $lessons->get(7)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Excepciones custom y Result pattern',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Practica el manejo de errores con excepciones personalizadas y el patrón Result.

Implementa:
1. `ValidationError < StandardError` — con atributo `field` y `code`.
2. `validar_usuario(datos)` — valida que un hash tenga `nombre` (string no vacío) y `email` (que contenga @). Lanza `ValidationError` apropiado.
3. `safe_execute` — ejecuta un bloque y retorna `{ ok: resultado }` o `{ error: mensaje }`.
MD,
            'starter_code' => <<<'RUBY'
class ValidationError < StandardError
  attr_reader :field, :code

  def initialize(message, field:, code:)
    # Tu código aquí
  end
end

def validar_usuario(datos)
  # Tu código aquí
  # Lanza ValidationError si el nombre está vacío o el email no contiene @
end

def safe_execute
  # Tu código aquí: ejecuta yield, retorna { ok: resultado } o { error: mensaje }
end

# ── Tests ─────────────────────────────────────────────
begin
  validar_usuario({ nombre: "", email: "test@mail.com" })
  puts false
rescue ValidationError => e
  puts e.field == :nombre
  puts e.code == :blank
end

r1 = safe_execute { 10 / 2 }
puts r1 == { ok: 5 }

r2 = safe_execute { 10 / 0 }
puts r2[:error].is_a?(String)
RUBY,
        ];

        // ── Lección 8: Archivos e I/O ──────────────────────────────────
        if ($l = $lessons->get(8)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Procesamiento de archivos y JSON',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Practica la lectura, escritura y procesamiento de datos.

Implementa (usando solo strings, sin necesidad de archivos reales):
1. `parsear_csv(csv_string)` — convierte un CSV string (con headers) a un array de hashes.
2. `a_tabla(datos)` — convierte un array de hashes a una tabla formateada con headers alineados.
3. `transformar_json(json_string)` — parsea JSON, agrupa por una clave y retorna JSON.
MD,
            'starter_code' => <<<'RUBY'
require 'json'

# 1. parsear_csv(csv_string) → Array de Hashes
# "nombre,edad\nAna,28\nBob,35" → [{"nombre"=>"Ana","edad"=>"28"}, ...]
def parsear_csv(csv_string)
  # Tu código aquí
end

# 2. a_tabla(datos) → String
# Recibe array de hashes y retorna tabla formateada
def a_tabla(datos)
  # Tu código aquí
end

# 3. transformar_json(json_string, agrupar_por:) → String (JSON)
# Parsea, agrupa por la clave indicada y retorna JSON
def transformar_json(json_string, agrupar_por:)
  # Tu código aquí
end

# ── Tests ─────────────────────────────────────────────
csv = "nombre,edad,ciudad\nAna,28,Madrid\nBob,35,Barcelona"
resultado = parsear_csv(csv)
puts resultado.length == 2
puts resultado.first["nombre"] == "Ana"

datos = [{ nombre: "Ana", edad: 28 }, { nombre: "Bob", edad: 35 }]
tabla = a_tabla(datos)
puts tabla.include?("nombre")
puts tabla.include?("Ana")

json = '[{"dept":"IT","name":"Ana"},{"dept":"HR","name":"Bob"},{"dept":"IT","name":"Carlos"}]'
agrupado = JSON.parse(transformar_json(json, agrupar_por: "dept"))
puts agrupado["IT"].length == 2
RUBY,
        ];

        // ── Lección 9: Expresiones regulares ───────────────────────────
        if ($l = $lessons->get(9)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Regex: validación y extracción',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Practica el uso de expresiones regulares para validar y extraer datos.

Implementa:
1. `extraer_emails(texto)` — extrae todos los emails de un texto.
2. `parsear_log(linea)` — parsea una línea de log con formato `"[NIVEL] YYYY-MM-DD mensaje"` y retorna un hash con `:nivel`, `:fecha` y `:mensaje`.
3. `convertir_camel_a_snake(texto)` — convierte camelCase a snake_case. Ej: `"miVariableNueva"` → `"mi_variable_nueva"`.
MD,
            'starter_code' => <<<'RUBY'
# 1. extraer_emails(texto) → Array de Strings
def extraer_emails(texto)
  # Tu código aquí
end

# 2. parsear_log(linea) → Hash
# "[ERROR] 2024-03-15 Connection failed" → { nivel: "ERROR", fecha: "2024-03-15", mensaje: "Connection failed" }
def parsear_log(linea)
  # Tu código aquí
end

# 3. convertir_camel_a_snake(texto) → String
# "miVariableNueva" → "mi_variable_nueva"
def convertir_camel_a_snake(texto)
  # Tu código aquí
end

# ── Tests ─────────────────────────────────────────────
emails = extraer_emails("Contacta a ana@test.com o bob@mail.org para info")
puts emails == ["ana@test.com", "bob@mail.org"]

log = parsear_log("[ERROR] 2024-03-15 Connection failed")
puts log == { nivel: "ERROR", fecha: "2024-03-15", mensaje: "Connection failed" }

puts convertir_camel_a_snake("miVariableNueva") == "mi_variable_nueva"
puts convertir_camel_a_snake("XMLParser") == "xml_parser"
RUBY,
        ];

        // ── Lección 10: Enumerables y colecciones ──────────────────────
        if ($l = $lessons->get(10)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Enumerable: transformaciones avanzadas',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Domina los métodos de Enumerable con ejercicios prácticos.

Implementa:
1. `top_palabras(texto, n)` — retorna las N palabras más frecuentes como array de pares `[palabra, conteo]`, ordenadas por frecuencia descendente.
2. `pipeline(datos, *operaciones)` — aplica una serie de lambdas/procs secuencialmente sobre los datos.
3. `mi_group_by(array)` — reimplementa `group_by` sin usar el método original. Recibe un bloque.
MD,
            'starter_code' => <<<'RUBY'
# 1. top_palabras(texto, n) → Array de [palabra, conteo]
def top_palabras(texto, n)
  # Tu código aquí
end

# 2. pipeline(datos, *operaciones) → resultado
def pipeline(datos, *operaciones)
  # Tu código aquí
end

# 3. mi_group_by(array, &block) → Hash
def mi_group_by(array)
  # Tu código aquí (usa yield)
end

# ── Tests ─────────────────────────────────────────────
texto = "ruby es genial ruby es rápido ruby"
puts top_palabras(texto, 2) == [["ruby", 3], ["es", 2]]

doble = ->(arr) { arr.map { |n| n * 2 } }
filtrar = ->(arr) { arr.select(&:even?) }
sumar = ->(arr) { arr.sum }

puts pipeline([1, 2, 3, 4, 5], doble, filtrar, sumar) == 30

resultado = mi_group_by([1, 2, 3, 4, 5]) { |n| n.even? ? :par : :impar }
puts resultado == { impar: [1, 3, 5], par: [2, 4] }
RUBY,
        ];

        // ── Lección 11: Programación funcional ─────────────────────────
        if ($l = $lessons->get(11)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Composición, curry y programación funcional',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Practica conceptos de programación funcional en Ruby.

Implementa:
1. `componer(*funciones)` — retorna un lambda que aplica las funciones de derecha a izquierda (como en matemáticas: f∘g).
2. `memoize(funcion)` — retorna un lambda que cachea los resultados por argumentos.
3. `mi_reduce(array, inicial)` — reimplementa `reduce` sin usar el método original. Recibe un bloque.
MD,
            'starter_code' => <<<'RUBY'
# 1. componer(*funciones) → Lambda
def componer(*funciones)
  # Tu código aquí
end

# 2. memoize(funcion) → Lambda
def memoize(funcion)
  # Tu código aquí
end

# 3. mi_reduce(array, inicial, &block) → resultado
def mi_reduce(array, inicial)
  # Tu código aquí
end

# ── Tests ─────────────────────────────────────────────
doble = ->(n) { n * 2 }
inc = ->(n) { n + 1 }
cuadrado = ->(n) { n ** 2 }

f = componer(cuadrado, inc, doble)  # cuadrado(inc(doble(3)))
puts f.call(3) == 49  # (3*2=6, 6+1=7, 7²=49)

llamadas = 0
factorial = ->(n) { llamadas += 1; n <= 1 ? 1 : n * factorial.call(n - 1) }
memo_fact = memoize(factorial)
memo_fact.call(5)
antes = llamadas
memo_fact.call(5)
puts llamadas == antes  # no incrementó (cacheado)

puts mi_reduce([1, 2, 3, 4], 0) { |acc, n| acc + n } == 10
puts mi_reduce(["a", "b", "c"], "") { |acc, s| acc + s } == "abc"
RUBY,
        ];

        // ── Lección 12: Metaprogramación ───────────────────────────────
        if ($l = $lessons->get(12)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'define_method y DSLs',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Practica técnicas de metaprogramación para crear código dinámico.

Implementa:
1. `attr_with_default` — método de clase que genera un accessor con valor por defecto. Uso: `attr_with_default :nombre, "Sin nombre"`.
2. `Schema` — una clase DSL para definir esquemas de datos. `Schema.define { string :nombre; integer :edad }` que genera los tipos esperados.
MD,
            'starter_code' => <<<'RUBY'
module AttrWithDefault
  def attr_with_default(name, default_value)
    # Tu código aquí: define getter y setter con valor por defecto
  end
end

class Persona
  extend AttrWithDefault

  attr_with_default :nombre, "Sin nombre"
  attr_with_default :edad, 0
  attr_with_default :activo, true
end

class Schema
  attr_reader :fields

  def initialize
    @fields = {}
  end

  # Tu código aquí: implementa self.define con instance_eval
  # y métodos string, integer, boolean que registren los fields

  def self.define(&block)
    # Tu código aquí
  end

  def validate(data)
    # Tu código aquí: verifica que cada field del data tenga el tipo correcto
  end
end

# ── Tests ─────────────────────────────────────────────
p = Persona.new
puts p.nombre == "Sin nombre"
puts p.edad == 0
p.nombre = "Ana"
puts p.nombre == "Ana"

schema = Schema.define do
  string :nombre
  integer :edad
end

puts schema.fields == { nombre: String, edad: Integer }
puts schema.validate({ nombre: "Ana", edad: 28 }) == true
puts schema.validate({ nombre: "Ana", edad: "28" }) == false
RUBY,
        ];

        // ── Lección 13: Gemas y Bundler ─────────────────────────────
        if ($l = $lessons->get(13)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Crea un Gemspec y Rakefile',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Practica la estructura de una gema Ruby y tareas con Rake.

Implementa:
1. `GemSpec` — clase que genera la estructura de un archivo .gemspec como string.
2. `TaskRunner` — sistema simple de tareas con dependencias (como Rake). Soporta `define(nombre, deps, &block)` y `run(nombre)`.
MD,
            'starter_code' => <<<'RUBY'
class GemSpec
  attr_accessor :name, :version, :summary, :authors, :dependencies

  def initialize
    @dependencies = []
    yield self if block_given?
  end

  def add_dependency(name, version)
    @dependencies << { name: name, version: version }
  end

  def to_s
    # Tu código aquí: genera un string con formato gemspec
    # Gem::Specification.new do |s|
    #   s.name = "..."
    #   ...
    # end
  end
end

class TaskRunner
  def initialize
    @tasks = {}
  end

  def define(name, deps = [], &block)
    # Tu código aquí
  end

  def run(name)
    # Tu código aquí: ejecuta dependencias primero, luego la tarea
  end
end

# ── Tests ─────────────────────────────────────────────
spec = GemSpec.new do |s|
  s.name = "mi_gema"
  s.version = "1.0.0"
  s.summary = "Una gema genial"
  s.authors = ["Ana"]
  s.add_dependency("httparty", "~> 0.21")
end
puts spec.to_s.include?("mi_gema")
puts spec.to_s.include?("1.0.0")

runner = TaskRunner.new
log = []
runner.define(:clean) { log << :clean }
runner.define(:build, [:clean]) { log << :build }
runner.define(:deploy, [:build]) { log << :deploy }
runner.run(:deploy)
puts log == [:clean, :build, :deploy]
RUBY,
        ];

        // ── Lección 14: Testing ────────────────────────────────────────
        if ($l = $lessons->get(14)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Implementa un mini framework de testing',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Crea un mini framework de testing inspirado en RSpec.

Implementa:
1. `describe(nombre)` — recibe un bloque que agrupa tests.
2. `it(descripcion)` — define un test individual.
3. `expect(valor)` — retorna un objeto con matchers: `to_eq`, `to_be_truthy`, `to_include`, `to_raise_error`.
MD,
            'starter_code' => <<<'RUBY'
class Expectation
  def initialize(value)
    @value = value
  end

  def to_eq(expected)
    # Tu código aquí
  end

  def to_be_truthy
    # Tu código aquí
  end

  def to_include(item)
    # Tu código aquí
  end
end

class ExpectBlock
  def initialize(&block)
    @block = block
  end

  def to_raise_error(error_class)
    # Tu código aquí
  end
end

$test_results = { passed: 0, failed: 0, errors: [] }

def expect(value = nil, &block)
  block ? ExpectBlock.new(&block) : Expectation.new(value)
end

def describe(name, &block)
  # Tu código aquí
end

def it(description, &block)
  # Tu código aquí
end

# ── Tests ─────────────────────────────────────────────
describe "Calculadora" do
  it "suma correctamente" do
    expect(2 + 3).to_eq(5)
  end

  it "verifica truthy" do
    expect(1).to_be_truthy
  end

  it "verifica include" do
    expect([1, 2, 3]).to_include(2)
  end

  it "captura errores" do
    expect { raise ArgumentError }.to_raise_error(ArgumentError)
  end
end

puts $test_results[:passed] >= 4
puts $test_results[:failed] == 0
RUBY,
        ];

        // ── Lección 15: Concurrencia ───────────────────────────────────
        if ($l = $lessons->get(15)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Threads, Mutex y producer-consumer',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Practica concurrencia segura en Ruby.

Implementa:
1. `parallel_map(array)` — ejecuta el bloque en un Thread por elemento y retorna los resultados en orden.
2. `ThreadSafeCounter` — clase thread-safe con `increment`, `decrement` y `value` protegidos con Mutex.
3. `bounded_queue(tamaño)` — retorna un hash con lambdas `:push` y `:pop` que operan sobre una cola con tamaño máximo (bloquea si está llena/vacía).
MD,
            'starter_code' => <<<'RUBY'
# 1. parallel_map(array, &block) → Array
def parallel_map(array)
  # Tu código aquí: un Thread por elemento, .value para recoger resultados
end

# 2. ThreadSafeCounter
class ThreadSafeCounter
  def initialize(initial = 0)
    # Tu código aquí
  end

  def increment
    # Tu código aquí
  end

  def decrement
    # Tu código aquí
  end

  def value
    # Tu código aquí
  end
end

# ── Tests ─────────────────────────────────────────────
resultados = parallel_map([1, 2, 3, 4, 5]) { |n| n ** 2 }
puts resultados == [1, 4, 9, 16, 25]

counter = ThreadSafeCounter.new(0)
threads = 10.times.map { Thread.new { 100.times { counter.increment } } }
threads.each(&:join)
puts counter.value == 1000
RUBY,
        ];

        // ── Lección 16: Patrones de diseño ─────────────────────────────
        if ($l = $lessons->get(16)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Observer y Builder pattern',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Implementa patrones de diseño clásicos en Ruby.

1. `EventEmitter` — módulo con `on(evento, &callback)`, `emit(evento, *args)` y `off(evento)`.
2. `HtmlBuilder` — builder para generar HTML con method chaining. Soporta `tag(nombre, attrs, &bloque)` anidado.
MD,
            'starter_code' => <<<'RUBY'
module EventEmitter
  def on(event, &callback)
    # Tu código aquí
  end

  def emit(event, *args)
    # Tu código aquí
  end

  def off(event)
    # Tu código aquí
  end
end

class App
  include EventEmitter
end

class HtmlBuilder
  def initialize
    @elements = []
  end

  def tag(name, attrs = {}, &block)
    # Tu código aquí
    self
  end

  def text(content)
    # Tu código aquí
    self
  end

  def to_s
    # Tu código aquí
  end
end

# ── Tests ─────────────────────────────────────────────
app = App.new
logs = []
app.on(:click) { |x| logs << "click: #{x}" }
app.on(:hover) { |x| logs << "hover: #{x}" }
app.emit(:click, "botón")
app.emit(:hover, "imagen")
puts logs == ["click: botón", "hover: imagen"]
app.off(:click)
app.emit(:click, "otro")
puts logs.length == 2   # no cambió

html = HtmlBuilder.new
  .tag(:div, class: "container") {
    tag(:h1) { text("Hola") }
    tag(:p) { text("Mundo") }
  }
  .to_s
puts html.include?("<div")
puts html.include?("Hola")
RUBY,
        ];

        // ── Lección 17: HTTP y APIs ────────────────────────────────────
        if ($l = $lessons->get(17)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Router HTTP y middleware',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Construye un mini-router HTTP inspirado en Sinatra.

Implementa:
1. `MiniRouter` — clase con `get(path, &handler)`, `post(path, &handler)` y `handle(method, path)` que ejecuta el handler.
2. Soporte para parámetros en la URL: `/users/:id` matchea `/users/42` y pasa `{ id: "42" }`.
3. `use(middleware)` — registra middleware que se ejecuta antes de cada request.
MD,
            'starter_code' => <<<'RUBY'
class MiniRouter
  def initialize
    @routes = []
    @middlewares = []
  end

  def get(path, &handler)
    # Tu código aquí
  end

  def post(path, &handler)
    # Tu código aquí
  end

  def use(&middleware)
    # Tu código aquí
  end

  def handle(method, path)
    # Tu código aquí: busca la ruta, extrae params, ejecuta middlewares y handler
  end

  private

  def match_route(pattern, path)
    # Tu código aquí: convierte "/users/:id" a regex y extrae params
  end
end

# ── Tests ─────────────────────────────────────────────
router = MiniRouter.new

log = []
router.use { |req| log << "middleware: #{req[:method]} #{req[:path]}" }

router.get("/") { { status: 200, body: "Home" } }
router.get("/users/:id") { |params| { status: 200, body: "User #{params[:id]}" } }
router.post("/users") { { status: 201, body: "Created" } }

r1 = router.handle("GET", "/")
puts r1[:body] == "Home"

r2 = router.handle("GET", "/users/42")
puts r2[:body] == "User 42"

r3 = router.handle("POST", "/users")
puts r3[:status] == 201

puts log.length == 3
RUBY,
        ];

        // ── Lección 18: Ruby moderno ───────────────────────────────────
        if ($l = $lessons->get(18)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Pattern matching y Data class',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Practica las features modernas de Ruby 3.x.

Implementa:
1. `clasificar_respuesta(response)` — usa pattern matching para clasificar un hash con `:status` y `:body`. Retorna strings descriptivos según el status (2xx, 4xx, 5xx).
2. `Punto` — usando `Data.define` (o Struct si Ruby < 3.2) con `:x`, `:y` y métodos `distancia(otro)` y `+(otro)`.
3. `procesar_datos(datos)` — recibe un array de hashes mixtos y usa pattern matching para extraer/transformar según el tipo.
MD,
            'starter_code' => <<<'RUBY'
# 1. clasificar_respuesta(response) → String
def clasificar_respuesta(response)
  # Tu código aquí: usa case/in con pattern matching
  # { status: 200, body: ... } → "OK: {body}"
  # { status: 404 } → "No encontrado"
  # { status: (500..) } → "Error del servidor"
  # otro → "Respuesta desconocida"
end

# 2. Punto — value object con Data.define o Struct
Punto = Struct.new(:x, :y) do
  def distancia(otro)
    # Tu código aquí
  end

  def +(otro)
    # Tu código aquí: retorna un nuevo Punto
  end

  def to_s
    "(#{x}, #{y})"
  end
end

# 3. procesar_datos(datos) → Array de strings
def procesar_datos(datos)
  # Tu código aquí: usa pattern matching para cada elemento
  # { type: "user", name: String } → "Usuario: {name}"
  # { type: "order", total: Numeric } → "Pedido: ${total}"  
  # otro → "Desconocido"
end

# ── Tests ─────────────────────────────────────────────
puts clasificar_respuesta({ status: 200, body: "OK" }) == "OK: OK"
puts clasificar_respuesta({ status: 404 }) == "No encontrado"
puts clasificar_respuesta({ status: 500 }) == "Error del servidor"

p1 = Punto.new(0, 0)
p2 = Punto.new(3, 4)
puts p1.distancia(p2) == 5.0
p3 = p1 + p2
puts p3.to_s == "(3, 4)"

datos = [
  { type: "user", name: "Ana" },
  { type: "order", total: 99.99 },
  { type: "unknown" }
]
resultado = procesar_datos(datos)
puts resultado == ["Usuario: Ana", "Pedido: $99.99", "Desconocido"]
RUBY,
        ];

        // ── Lección 19: Preguntas de entrevista ────────────────────────
        if ($l = $lessons->get(19)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Desafío de entrevista: algoritmos en Ruby',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Resuelve problemas clásicos de entrevistas técnicas usando Ruby idiomático.

Implementa:
1. `dos_suma(nums, target)` — retorna los índices de dos números que sumen target. Usa un hash para O(n).
2. `balancear_parentesis(str)` — verifica que los paréntesis `(){}[]` estén balanceados.
3. `anagramas(str1, str2)` — verifica si dos strings son anagramas (ignora espacios y mayúsculas).
4. `fibonacci_memo(n)` — retorna el n-ésimo Fibonacci con memoización.
MD,
            'starter_code' => <<<'RUBY'
# 1. dos_suma(nums, target) → [indice1, indice2]
def dos_suma(nums, target)
  # Tu código aquí (usa hash para O(n))
end

# 2. balancear_parentesis(str) → true/false
def balancear_parentesis(str)
  # Tu código aquí (usa un stack/array)
end

# 3. anagramas(str1, str2) → true/false
def anagramas(str1, str2)
  # Tu código aquí
end

# 4. fibonacci_memo(n) → Integer
def fibonacci_memo(n, memo = {})
  # Tu código aquí
end

# ── Tests ─────────────────────────────────────────────
puts dos_suma([2, 7, 11, 15], 9) == [0, 1]
puts dos_suma([3, 2, 4], 6) == [1, 2]

puts balancear_parentesis("({[]})") == true
puts balancear_parentesis("({[})") == false
puts balancear_parentesis("") == true

puts anagramas("listen", "silent") == true
puts anagramas("Hello World", "World Hello") == true
puts anagramas("abc", "abd") == false

puts fibonacci_memo(0) == 0
puts fibonacci_memo(10) == 55
puts fibonacci_memo(50) == 12586269025
RUBY,
        ];

        return $ex;
    }
}
