<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RailsExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'rails-8-fullstack')->first();

        if (! $course) {
            $this->command->warn('Rails 8 course not found. Run CourseSeeder + RailsLessonSeeder first.');
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

        $this->command->info('Rails exercises seeded: ' . count($exercises) . ' exercises.');
    }

    private function exercises(\Illuminate\Support\Collection $lessons): array
    {
        $ex = [];

        // ── Lección 1: Introducción a Rails 8 ─────────────────────────
        if ($l = $lessons->get(1)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Generadores y estructura de un proyecto Rails',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Practica con los generadores de Rails y comprende la estructura del framework.

Implementa una clase `ProjectScaffolder` que simule la generación de archivos:
1. `initialize(app_name)` — guarda el nombre del proyecto.
2. `generate_structure` — retorna un hash con las claves `:app`, `:models`, `:controllers`, `:views`, cada una con su path relativo (`"#{app_name}/app/models"`, etc.).
3. `rails_command(cmd)` — recibe un string como `"generate model User name:string"` y retorna un hash `{ type: "model", name: "User", fields: ["name:string"] }`.
MD,
            'starter_code' => <<<'RUBY'
class ProjectScaffolder
  attr_reader :app_name

  def initialize(app_name)
    # Tu código aquí
  end

  def generate_structure
    # Retorna hash con :app, :models, :controllers, :views
  end

  def rails_command(cmd)
    # Parsea "generate model User name:string email:string"
    # Retorna { type: "model", name: "User", fields: [...] }
  end
end
RUBY,
            'solution_code' => <<<'RUBY'
class ProjectScaffolder
  attr_reader :app_name

  def initialize(app_name)
    @app_name = app_name
  end

  def generate_structure
    base = "#{app_name}/app"
    {
      app:         base,
      models:      "#{base}/models",
      controllers: "#{base}/controllers",
      views:       "#{base}/views"
    }
  end

  def rails_command(cmd)
    parts = cmd.split
    parts.shift # remove "generate"
    type   = parts.shift
    name   = parts.shift
    fields = parts
    { type: type, name: name, fields: fields }
  end
end
RUBY,
        ];

        // ── Lección 2: Arquitectura MVC ───────────────────────────────
        if ($l = $lessons->get(2)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulador de flujo MVC',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Simula el flujo MVC de Rails con clases Ruby puras.

1. `MiniModel` — clase con `attr_accessor :attributes` (hash). Método de clase `create(attrs)` que retorna instancia con atributos. Método `valid?` que retorna `true` si `:name` está presente.
2. `MiniController` — `initialize` recibe la clase del modelo. Método `index` retorna todos los registros (array de clase). Método `create(params)` crea y retorna instancia si `valid?`, o `nil`.
3. `MiniView` — Método `render(data)` que retorna `"<ul>#{items}</ul>"` donde items son `<li>` con el `:name` de cada registro.
MD,
            'starter_code' => <<<'RUBY'
class MiniModel
  attr_accessor :attributes
  @@records = []

  def self.create(attrs)
    # Crea instancia y la guarda en @@records
  end

  def self.all
    @@records
  end

  def valid?
    # true si :name está presente y no vacío
  end
end

class MiniController
  def initialize(model_class)
    # Tu código aquí
  end

  def index
    # Retorna todos los registros
  end

  def create(params)
    # Crea registro si es válido
  end
end

class MiniView
  def render(records)
    # Retorna HTML "<ul><li>...</li></ul>"
  end
end
RUBY,
            'solution_code' => <<<'RUBY'
class MiniModel
  attr_accessor :attributes
  @@records = []

  def initialize(attrs = {})
    @attributes = attrs
  end

  def self.create(attrs)
    instance = new(attrs)
    @@records << instance if instance.valid?
    instance
  end

  def self.all
    @@records
  end

  def self.reset!
    @@records = []
  end

  def valid?
    attributes[:name].is_a?(String) && !attributes[:name].strip.empty?
  end
end

class MiniController
  def initialize(model_class)
    @model = model_class
  end

  def index
    @model.all
  end

  def create(params)
    record = @model.create(params)
    record.valid? ? record : nil
  end
end

class MiniView
  def render(records)
    items = records.map { |r| "<li>#{r.attributes[:name]}</li>" }.join
    "<ul>#{items}</ul>"
  end
end
RUBY,
        ];

        // ── Lección 3: Sistema de Rutas ──────────────────────────────
        if ($l = $lessons->get(3)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mini router REST',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Construye un mini router que simule el sistema de rutas RESTful de Rails.

Implementa la clase `MiniRouter`:
1. `resources(name)` — genera las 7 rutas RESTful estándar para un recurso: index, show, new, create, edit, update, destroy.
2. `routes` — retorna array de hashes `{ method:, path:, action: }`. Ej: `{ method: "GET", path: "/posts", action: "posts#index" }`.
3. `match(method, path)` — busca la ruta que coincida y retorna el hash, o `nil`.
4. `namespace(prefix, &block)` — permite anidar recursos bajo un prefijo. Ej: `namespace("admin") { resources("users") }` genera `/admin/users`.
MD,
            'starter_code' => <<<'RUBY'
class MiniRouter
  attr_reader :routes

  def initialize
    @routes  = []
    @prefix  = ""
  end

  def resources(name)
    # Genera 7 rutas RESTful
  end

  def match(method, path)
    # Busca la ruta que coincida
  end

  def namespace(prefix, &block)
    # Ejecuta bloque con prefijo
  end
end
RUBY,
            'solution_code' => <<<'RUBY'
class MiniRouter
  attr_reader :routes

  def initialize
    @routes  = []
    @prefix  = ""
  end

  def resources(name)
    base = "#{@prefix}/#{name}"
    ctrl = name

    @routes.push(
      { method: "GET",    path: base,              action: "#{ctrl}#index"   },
      { method: "GET",    path: "#{base}/new",     action: "#{ctrl}#new"     },
      { method: "POST",   path: base,              action: "#{ctrl}#create"  },
      { method: "GET",    path: "#{base}/:id",     action: "#{ctrl}#show"    },
      { method: "GET",    path: "#{base}/:id/edit",action: "#{ctrl}#edit"    },
      { method: "PATCH",  path: "#{base}/:id",     action: "#{ctrl}#update"  },
      { method: "DELETE", path: "#{base}/:id",     action: "#{ctrl}#destroy" }
    )
  end

  def match(method, path)
    @routes.find do |r|
      r[:method] == method && path_match?(r[:path], path)
    end
  end

  def namespace(prefix, &block)
    old_prefix = @prefix
    @prefix = "#{@prefix}/#{prefix}"
    instance_eval(&block)
    @prefix = old_prefix
  end

  private

  def path_match?(pattern, actual)
    pattern_parts = pattern.split("/")
    actual_parts  = actual.split("/")
    return false unless pattern_parts.size == actual_parts.size

    pattern_parts.zip(actual_parts).all? do |p, a|
      p.start_with?(":") || p == a
    end
  end
end
RUBY,
        ];

        // ── Lección 4: Active Record ─────────────────────────────────
        if ($l = $lessons->get(4)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulador de migraciones y esquema',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Simula el sistema de migraciones de Active Record.

Implementa `SchemaMigrator`:
1. `initialize` — inicia con un hash de tablas vacío.
2. `create_table(name, &block)` — acepta un bloque con columnas. Retorna la tabla creada.
3. `add_column(table, name, type)` — agrega columna a tabla existente.
4. `remove_column(table, name)` — elimina columna.
5. `schema` — retorna hash completo `{ table_name: { col: type, ... } }`.
6. `TableBuilder` — clase auxiliar; métodos `string(name)`, `integer(name)`, `boolean(name)`, `timestamps`.
MD,
            'starter_code' => <<<'RUBY'
class SchemaMigrator
  attr_reader :schema

  def initialize
    @schema = {}
  end

  def create_table(name, &block)
    # Crea tabla usando TableBuilder
  end

  def add_column(table, name, type)
    # Agrega columna
  end

  def remove_column(table, name)
    # Elimina columna
  end
end

class TableBuilder
  attr_reader :columns

  def initialize
    @columns = {}
  end

  def string(name)   ; end
  def integer(name)  ; end
  def boolean(name)  ; end
  def timestamps     ; end
end
RUBY,
            'solution_code' => <<<'RUBY'
class TableBuilder
  attr_reader :columns

  def initialize
    @columns = { id: :integer }
  end

  def string(name)
    @columns[name.to_sym] = :string
  end

  def integer(name)
    @columns[name.to_sym] = :integer
  end

  def boolean(name)
    @columns[name.to_sym] = :boolean
  end

  def timestamps
    @columns[:created_at] = :datetime
    @columns[:updated_at] = :datetime
  end
end

class SchemaMigrator
  attr_reader :schema

  def initialize
    @schema = {}
  end

  def create_table(name, &block)
    builder = TableBuilder.new
    block.call(builder) if block
    @schema[name.to_sym] = builder.columns
    builder.columns
  end

  def add_column(table, name, type)
    @schema[table.to_sym][name.to_sym] = type.to_sym if @schema[table.to_sym]
  end

  def remove_column(table, name)
    @schema[table.to_sym]&.delete(name.to_sym)
  end
end
RUBY,
        ];

        // ── Lección 5: Asociaciones ──────────────────────────────────
        if ($l = $lessons->get(5)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema de asociaciones con eager loading',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Simula las asociaciones de Active Record.

Implementa un mini ORM:
1. `MiniRecord` — clase base con `attr_accessor :id, :attributes`. Método de clase `has_many(name, class_name:)` que define getter. Método de clase `belongs_to(name, class_name:)` que define getter usando `*_id`.
2. `User < MiniRecord` con `has_many :posts, class_name: "Post"`.
3. `Post < MiniRecord` con `belongs_to :user, class_name: "User"`.
4. `MiniRecord.store` — hash global que guarda todos los registros por clase.
5. `includes_simulation(records, association)` — función que pre-carga asociaciones (simula eager loading).
MD,
            'starter_code' => <<<'RUBY'
class MiniRecord
  attr_accessor :id, :attributes
  @@store = Hash.new { |h, k| h[k] = [] }

  def initialize(attrs = {})
    @attributes = attrs
    @id = attrs[:id]
  end

  def self.create(attrs)
    # Crea instancia y la añade al store
  end

  def self.all
    @@store[name]
  end

  def self.has_many(assoc, class_name:)
    # Define método que filtra registros asociados
  end

  def self.belongs_to(assoc, class_name:)
    # Define método que busca registro padre
  end
end

def includes_simulation(records, association)
  # Pre-carga asociaciones
end
RUBY,
            'solution_code' => <<<'RUBY'
class MiniRecord
  attr_accessor :id, :attributes
  @@store = Hash.new { |h, k| h[k] = [] }

  def initialize(attrs = {})
    @attributes = attrs
    @id = attrs[:id]
  end

  def self.create(attrs)
    instance = new(attrs)
    @@store[name] << instance
    instance
  end

  def self.all
    @@store[name]
  end

  def self.reset!
    @@store.clear
  end

  def self.find(id)
    @@store[name].find { |r| r.id == id }
  end

  def self.has_many(assoc, class_name:)
    define_method(assoc) do
      klass = Object.const_get(class_name)
      fk = :"#{self.class.name.downcase}_id"
      klass.all.select { |r| r.attributes[fk] == id }
    end
  end

  def self.belongs_to(assoc, class_name:)
    define_method(assoc) do
      klass = Object.const_get(class_name)
      fk = :"#{assoc}_id"
      klass.find(attributes[fk])
    end
  end
end

class User < MiniRecord
  has_many :posts, class_name: "Post"
end

class Post < MiniRecord
  belongs_to :user, class_name: "User"
end

def includes_simulation(records, association)
  records.each do |r|
    r.instance_variable_set(:"@#{association}", r.send(association))
  end
  records
end
RUBY,
        ];

        // ── Lección 6: Validaciones y Callbacks ─────────────────────
        if ($l = $lessons->get(6)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Validaciones y callbacks personalizados',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Crea un sistema de validaciones y callbacks al estilo Active Record.

Implementa `Validatable` (módulo):
1. `validates(field, **options)` — soporta `presence: true`, `length: { minimum:, maximum: }`, `format: { with: regex }`.
2. `before_save(method_name)` — registra callback que se ejecuta antes de `save`.
3. Al incluirse, añade `valid?`, `errors` (hash) y `save` a la instancia.

Luego crea `Article` que incluya `Validatable`:
- Valida `title` con presence y length (min 5, max 100).
- Valida `email` con format (regex de email simple).
- `before_save :normalize_title` que convierte título a titlecase.
MD,
            'starter_code' => <<<'RUBY'
module Validatable
  def self.included(base)
    base.extend(ClassMethods)
    base.instance_variable_set(:@validations, [])
    base.instance_variable_set(:@callbacks, { before_save: [] })
  end

  module ClassMethods
    def validates(field, **options)
      # Registra validación
    end

    def before_save(method_name)
      # Registra callback
    end
  end

  def valid?
    # Ejecuta todas las validaciones
  end

  def errors
    @errors ||= {}
  end

  def save
    # Ejecuta callbacks y valida
  end
end

class Article
  include Validatable
  attr_accessor :title, :email

  # Define validaciones y callbacks
end
RUBY,
            'solution_code' => <<<'RUBY'
module Validatable
  def self.included(base)
    base.extend(ClassMethods)
    base.instance_variable_set(:@validations, [])
    base.instance_variable_set(:@callbacks, { before_save: [] })
  end

  module ClassMethods
    def validations
      @validations
    end

    def callbacks
      @callbacks
    end

    def validates(field, **options)
      @validations << { field: field, options: options }
    end

    def before_save(method_name)
      @callbacks[:before_save] << method_name
    end
  end

  def valid?
    @errors = {}
    self.class.validations.each do |v|
      field = v[:field]
      value = send(field)
      opts  = v[:options]

      if opts[:presence] && (value.nil? || value.to_s.strip.empty?)
        (@errors[field] ||= []) << "can't be blank"
      end

      if opts[:length]
        len = value.to_s.length
        min = opts[:length][:minimum]
        max = opts[:length][:maximum]
        (@errors[field] ||= []) << "is too short (min #{min})" if min && len < min
        (@errors[field] ||= []) << "is too long (max #{max})"  if max && len > max
      end

      if opts[:format] && value
        (@errors[field] ||= []) << "is invalid" unless value.match?(opts[:format][:with])
      end
    end
    @errors.empty?
  end

  def errors
    @errors ||= {}
  end

  def save
    self.class.callbacks[:before_save].each { |cb| send(cb) }
    valid?
  end
end

class Article
  include Validatable
  attr_accessor :title, :email

  validates :title, presence: true, length: { minimum: 5, maximum: 100 }
  validates :email, format: { with: /\A[\w+\-.]+@[a-z\d\-]+(\.[a-z]+)+\z/i }

  def normalize_title
    self.title = title.split.map(&:capitalize).join(" ") if title
  end

  before_save :normalize_title
end
RUBY,
        ];

        // ── Lección 7: Controllers y Acciones ──────────────────────
        if ($l = $lessons->get(7)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mini controller con strong parameters',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Implementa un mini controlador Rails con strong parameters.

1. `StrongParams` — clase que recibe un hash. Método `require(key)` retorna nuevo `StrongParams` con ese sub-hash o lanza error. Método `permit(*keys)` retorna hash filtrado solo con las keys permitidas.
2. `ArticlesController` — `create(params)` usa StrongParams para requerir `:article` y permitir `:title, :body`. Retorna el hash permitido. `update(params)` también permite `:published`.
3. Ambos métodos retornan `nil` si los params no son válidos.
MD,
            'starter_code' => <<<'RUBY'
class StrongParams
  def initialize(hash)
    @hash = hash
  end

  def require(key)
    # Retorna StrongParams del sub-hash
  end

  def permit(*keys)
    # Retorna hash filtrado
  end
end

class ArticlesController
  def create(raw_params)
    # Usa StrongParams
  end

  def update(raw_params)
    # Usa StrongParams
  end
end
RUBY,
            'solution_code' => <<<'RUBY'
class StrongParams
  def initialize(hash)
    @hash = hash
  end

  def require(key)
    value = @hash[key]
    raise ArgumentError, "param #{key} is required" unless value.is_a?(Hash)
    StrongParams.new(value)
  end

  def permit(*keys)
    @hash.select { |k, _| keys.include?(k) }
  end
end

class ArticlesController
  def create(raw_params)
    params = StrongParams.new(raw_params)
    params.require(:article).permit(:title, :body)
  rescue ArgumentError
    nil
  end

  def update(raw_params)
    params = StrongParams.new(raw_params)
    params.require(:article).permit(:title, :body, :published)
  rescue ArgumentError
    nil
  end
end
RUBY,
        ];

        // ── Lección 8: Vistas, Layouts y Partials ───────────────────
        if ($l = $lessons->get(8)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mini motor de templates ERB',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Crea un mini motor de templates que simule ERB de Rails.

Implementa `MiniTemplate`:
1. `initialize(template)` — recibe el template string.
2. `render(locals = {})` — reemplaza `{{ variable }}` por el valor de `locals[:variable]`.
3. `render_collection(partial, collection, as:)` — dado un template parcial y un array, renderiza cada item.
4. `layout(layout_template, &block)` — envuelve el contenido del bloque en un layout. `{{ yield }}` en el layout se reemplaza por el contenido.
MD,
            'starter_code' => <<<'RUBY'
class MiniTemplate
  def initialize(template)
    @template = template
  end

  def render(locals = {})
    # Reemplaza {{ key }} por valor
  end

  def render_collection(partial, collection, as:)
    # Renderiza parcial para cada item
  end

  def self.layout(layout_template, &block)
    # Envuelve contenido en layout
  end
end
RUBY,
            'solution_code' => <<<'RUBY'
class MiniTemplate
  def initialize(template)
    @template = template
  end

  def render(locals = {})
    result = @template.dup
    locals.each do |key, value|
      result.gsub!("{{ #{key} }}", value.to_s)
    end
    result
  end

  def render_collection(partial, collection, as:)
    collection.map do |item|
      tmpl = MiniTemplate.new(partial)
      tmpl.render({ as => item })
    end.join
  end

  def self.layout(layout_template, &block)
    content = block.call
    layout_template.gsub("{{ yield }}", content)
  end
end
RUBY,
        ];

        // ── Lección 9: Hotwire y Turbo ──────────────────────────────
        if ($l = $lessons->get(9)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulador de Turbo Streams',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Simula los Turbo Streams de Rails.

Implementa `TurboStream`:
1. `append(target, content)` — retorna `<turbo-stream action="append" target="TARGET"><template>CONTENT</template></turbo-stream>`.
2. `prepend(target, content)` — igual con action="prepend".
3. `replace(target, content)` — action="replace".
4. `update(target, content)` — action="update".
5. `remove(target)` — action="remove" sin template.
6. `broadcast(channel, streams)` — retorna hash `{ channel:, streams: [...] }` donde streams es array de strings HTML.
MD,
            'starter_code' => <<<'RUBY'
class TurboStream
  def self.append(target, content)
    # Retorna turbo-stream tag
  end

  def self.prepend(target, content)
  end

  def self.replace(target, content)
  end

  def self.update(target, content)
  end

  def self.remove(target)
  end

  def self.broadcast(channel, streams)
    # Retorna hash con channel y streams
  end
end
RUBY,
            'solution_code' => <<<'RUBY'
class TurboStream
  def self.append(target, content)
    wrap("append", target, content)
  end

  def self.prepend(target, content)
    wrap("prepend", target, content)
  end

  def self.replace(target, content)
    wrap("replace", target, content)
  end

  def self.update(target, content)
    wrap("update", target, content)
  end

  def self.remove(target)
    "<turbo-stream action=\"remove\" target=\"#{target}\"></turbo-stream>"
  end

  def self.broadcast(channel, streams)
    { channel: channel, streams: streams }
  end

  private

  def self.wrap(action, target, content)
    "<turbo-stream action=\"#{action}\" target=\"#{target}\">" \
    "<template>#{content}</template>" \
    "</turbo-stream>"
  end
end
RUBY,
        ];

        // ── Lección 10: Stimulus ────────────────────────────────────
        if ($l = $lessons->get(10)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulador de Stimulus controllers',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Simula el sistema de controllers de Stimulus en Ruby.

Implementa `StimulusController`:
1. `targets` (class method) — define targets disponibles. Genera un getter `*_target` que busca en `@dom`.
2. `values` (class method) — define values con tipo. Genera getter y setter con conversión de tipo.
3. `connect` — callback al "conectar" el controller. Por defecto no hace nada.
4. `dispatch(event, detail: {})` — retorna hash `{ event:, detail: }`.

Luego crea `ToggleController < StimulusController`:
- Targets: `[:content]`
- Values: `{ open: :boolean }`
- `toggle` alterna el value `open`.
MD,
            'starter_code' => <<<'RUBY'
class StimulusController
  attr_reader :dom

  def initialize(dom = {})
    @dom = dom
  end

  def self.targets(list)
    # Define getter para cada target
  end

  def self.values(hash)
    # Define getter/setter para cada value
  end

  def connect; end

  def dispatch(event, detail: {})
    # Retorna hash del evento
  end
end

class ToggleController < StimulusController
  # Define targets y values
  # Método toggle
end
RUBY,
            'solution_code' => <<<'RUBY'
class StimulusController
  attr_reader :dom

  def initialize(dom = {})
    @dom = dom
    @values_store = {}
    connect
  end

  def self.targets(list)
    list.each do |t|
      define_method(:"#{t}_target") { @dom[t] }
      define_method(:"has_#{t}_target?") { @dom.key?(t) }
    end
  end

  def self.values(hash)
    hash.each do |name, type|
      define_method(:"#{name}_value") { @values_store[name] }
      define_method(:"#{name}_value=") do |val|
        @values_store[name] = case type
          when :boolean then !!val
          when :integer then val.to_i
          when :string  then val.to_s
          else val
        end
      end
    end
  end

  def connect; end

  def dispatch(event, detail: {})
    { event: event, detail: detail }
  end
end

class ToggleController < StimulusController
  targets [:content]
  values open: :boolean

  def connect
    self.open_value = false
  end

  def toggle
    self.open_value = !open_value
  end
end
RUBY,
        ];

        // ── Lección 11: Action Cable ────────────────────────────────
        if ($l = $lessons->get(11)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mini Action Cable: pub/sub en tiempo real',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Simula el sistema de channels de Action Cable.

Implementa:
1. `CableServer` — mantiene hash de channels. Método `subscribe(channel_name, &callback)` registra callback. Método `broadcast(channel_name, data)` ejecuta todos los callbacks de ese canal. Método `unsubscribe(channel_name, callback_id)`.
2. `ChatChannel` — hereda de `BaseChannel`. `subscribed` se conecta a "chat_#{room}". `speak(message)` broadcast al canal con `{ user:, message:, timestamp: }`. `unsubscribed` se desconecta.
3. `BaseChannel` — `stream_from(channel)` suscribe al `CableServer`. `broadcast_to(channel, data)`.
MD,
            'starter_code' => <<<'RUBY'
class CableServer
  attr_reader :channels

  def initialize
    @channels = Hash.new { |h, k| h[k] = {} }
    @next_id  = 0
  end

  def subscribe(channel_name, &callback)
    # Registra callback, retorna ID
  end

  def broadcast(channel_name, data)
    # Ejecuta todos los callbacks
  end

  def unsubscribe(channel_name, callback_id)
    # Elimina callback
  end
end

class BaseChannel
  def initialize(server, params = {})
    @server = server
    @params = params
  end

  def stream_from(channel)
  end

  def broadcast_to(channel, data)
  end
end

class ChatChannel < BaseChannel
  def subscribed
  end

  def speak(message)
  end

  def unsubscribed
  end
end
RUBY,
            'solution_code' => <<<'RUBY'
class CableServer
  attr_reader :channels

  def initialize
    @channels = Hash.new { |h, k| h[k] = {} }
    @next_id  = 0
  end

  def subscribe(channel_name, &callback)
    id = @next_id += 1
    @channels[channel_name][id] = callback
    id
  end

  def broadcast(channel_name, data)
    @channels[channel_name].each_value { |cb| cb.call(data) }
  end

  def unsubscribe(channel_name, callback_id)
    @channels[channel_name].delete(callback_id)
  end
end

class BaseChannel
  attr_reader :stream_name

  def initialize(server, params = {})
    @server = server
    @params = params
    @subscription_ids = []
  end

  def stream_from(channel)
    @stream_name = channel
  end

  def broadcast_to(channel, data)
    @server.broadcast(channel, data)
  end
end

class ChatChannel < BaseChannel
  def subscribed
    stream_from("chat_#{@params[:room]}")
  end

  def speak(message)
    broadcast_to(stream_name, {
      user:      @params[:user],
      message:   message,
      timestamp: Time.now.to_s
    })
  end

  def unsubscribed
    @server.channels.delete(stream_name)
  end
end
RUBY,
        ];

        // ── Lección 12: Autenticación ───────────────────────────────
        if ($l = $lessons->get(12)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema de autenticación con has_secure_password',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Simula `has_secure_password` de Rails.

Implementa:
1. `SecurePassword` (módulo) — al incluirse, agrega:
   - `password=(raw)` que hashea con SHA256 + salt y guarda en `@password_digest`.
   - `authenticate(raw)` que compara digests y retorna `self` o `false`.
2. `SessionManager` — `login(user, password)` intenta autenticar. Si ok, genera token (SecureRandom.hex) y guarda `{ token:, user_id: }`. Retorna token o nil. `current_user(token)` retorna user o nil. `logout(token)`.
3. `User` — incluye `SecurePassword`, tiene `attr_accessor :id, :email`.
MD,
            'starter_code' => <<<'RUBY'
require 'digest'
require 'securerandom'

module SecurePassword
  def password=(raw)
    # Hashea con SHA256 + salt
  end

  def authenticate(raw)
    # Compara y retorna self o false
  end
end

class User
  include SecurePassword
  attr_accessor :id, :email

  def initialize(id:, email:)
    @id = id
    @email = email
  end
end

class SessionManager
  def initialize
    @sessions = {}
  end

  def login(user, password)
  end

  def current_user(token)
  end

  def logout(token)
  end
end
RUBY,
            'solution_code' => <<<'RUBY'
require 'digest'
require 'securerandom'

module SecurePassword
  def password=(raw)
    @salt = SecureRandom.hex(16)
    @password_digest = Digest::SHA256.hexdigest("#{@salt}:#{raw}")
  end

  def authenticate(raw)
    digest = Digest::SHA256.hexdigest("#{@salt}:#{raw}")
    digest == @password_digest ? self : false
  end
end

class User
  include SecurePassword
  attr_accessor :id, :email

  def initialize(id:, email:)
    @id = id
    @email = email
  end
end

class SessionManager
  def initialize
    @sessions = {}
    @users    = {}
  end

  def login(user, password)
    result = user.authenticate(password)
    return nil unless result

    token = SecureRandom.hex(32)
    @sessions[token] = { token: token, user_id: user.id }
    @users[user.id] = user
    token
  end

  def current_user(token)
    session = @sessions[token]
    return nil unless session
    @users[session[:user_id]]
  end

  def logout(token)
    @sessions.delete(token)
  end
end
RUBY,
        ];

        // ── Lección 13: Autorización ────────────────────────────────
        if ($l = $lessons->get(13)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mini Pundit: sistema de policies',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Implementa un sistema de autorización basado en policies al estilo Pundit.

1. `PolicyFinder` — método de clase `policy_for(user, record)` que busca la policy correspondiente: `"#{record.class}Policy"`. Retorna instancia de la policy con user y record.
2. `BasePolicy` — `initialize(user, record)`. Métodos base `index?`, `show?`, `create?`, `update?`, `destroy?` todos retornan `false`.
3. `ArticlePolicy < BasePolicy` — `show?` siempre true. `create?` true si user.role es `:admin` o `:editor`. `update?` true si user es admin o es el autor. `destroy?` solo admin.
4. `authorize(user, record, action)` — función que busca policy y verifica permiso, lanza error si no autorizado.
MD,
            'starter_code' => <<<'RUBY'
class BasePolicy
  attr_reader :user, :record

  def initialize(user, record)
    @user   = user
    @record = record
  end

  def index?;   false; end
  def show?;    false; end
  def create?;  false; end
  def update?;  false; end
  def destroy?; false; end
end

class ArticlePolicy < BasePolicy
  # Define permisos
end

module PolicyFinder
  def self.policy_for(user, record)
    # Busca y retorna instancia de policy
  end
end

def authorize(user, record, action)
  # Verifica autorización
end
RUBY,
            'solution_code' => <<<'RUBY'
class BasePolicy
  attr_reader :user, :record

  def initialize(user, record)
    @user   = user
    @record = record
  end

  def index?;   false; end
  def show?;    false; end
  def create?;  false; end
  def update?;  false; end
  def destroy?; false; end
end

class ArticlePolicy < BasePolicy
  def show?
    true
  end

  def create?
    [:admin, :editor].include?(user.role)
  end

  def update?
    user.role == :admin || record.author_id == user.id
  end

  def destroy?
    user.role == :admin
  end
end

module PolicyFinder
  def self.policy_for(user, record)
    policy_class = Object.const_get("#{record.class}Policy")
    policy_class.new(user, record)
  end
end

def authorize(user, record, action)
  policy = PolicyFinder.policy_for(user, record)
  raise "Not authorized to #{action}" unless policy.send(:"#{action}?")
  true
end
RUBY,
        ];

        // ── Lección 14: Rails como API ──────────────────────────────
        if ($l = $lessons->get(14)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'API Serializer y versionado',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Crea un serializer JSON para una API REST versionada.

1. `BaseSerializer` — `initialize(record)`. Método de clase `attributes(*attrs)` define qué campos incluir. Método `as_json` retorna hash con los atributos. Método `to_json` convierte a JSON string.
2. `UserSerializer < BaseSerializer` — Atributos: `:id, :name, :email`. Método custom `full_name` que combina first_name + last_name del record.
3. `CollectionSerializer` — `initialize(records, serializer_class)`. `as_json` retorna `{ data: [...], meta: { count: N } }`.
4. `ApiVersionRouter` — `register(version, routes_hash)`. `resolve(version, path)` retorna el handler o nil.
MD,
            'starter_code' => <<<'RUBY'
require 'json'

class BaseSerializer
  def initialize(record)
    @record = record
  end

  def self.attributes(*attrs)
    # Registra atributos
  end

  def as_json
    # Retorna hash de atributos
  end

  def to_json
    as_json.to_json
  end
end

class UserSerializer < BaseSerializer
  # Define attributes y full_name
end

class CollectionSerializer
  def initialize(records, serializer_class)
    @records = records
    @serializer = serializer_class
  end

  def as_json
    # Retorna { data: [...], meta: { count: } }
  end
end

class ApiVersionRouter
  def initialize
    @versions = {}
  end

  def register(version, routes)
  end

  def resolve(version, path)
  end
end
RUBY,
            'solution_code' => <<<'RUBY'
require 'json'

class BaseSerializer
  def initialize(record)
    @record = record
  end

  def self.attributes(*attrs)
    @attrs = attrs
  end

  def self.defined_attributes
    @attrs || []
  end

  def as_json
    self.class.defined_attributes.each_with_object({}) do |attr, hash|
      hash[attr] = respond_to?(attr) ? send(attr) : @record.send(attr)
    end
  end

  def to_json
    as_json.to_json
  end
end

class UserSerializer < BaseSerializer
  attributes :id, :name, :email, :full_name

  def full_name
    "#{@record.first_name} #{@record.last_name}".strip
  end

  def name
    @record.name
  end

  def id
    @record.id
  end

  def email
    @record.email
  end
end

class CollectionSerializer
  def initialize(records, serializer_class)
    @records    = records
    @serializer = serializer_class
  end

  def as_json
    {
      data: @records.map { |r| @serializer.new(r).as_json },
      meta: { count: @records.size }
    }
  end
end

class ApiVersionRouter
  def initialize
    @versions = {}
  end

  def register(version, routes)
    @versions[version] = routes
  end

  def resolve(version, path)
    @versions.dig(version, path)
  end
end
RUBY,
        ];

        // ── Lección 15: Active Job y Action Mailer ──────────────────
        if ($l = $lessons->get(15)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Cola de jobs y sistema de mailers',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Simula Active Job y Action Mailer.

1. `JobQueue` — cola FIFO. `enqueue(job)` agrega job. `perform_all` ejecuta todos los jobs en orden (llama `perform`). `size` retorna cantidad pendiente. `clear` vacía la cola.
2. `BaseJob` — `perform_later(*args)` (class method) crea instancia y la encola en `JobQueue.instance`. Subclases implementan `perform(*args)`.
3. `MiniMailer` — `mail(to:, subject:, body:)` retorna hash con esos campos. `deliver_later` encola un `MailJob` con los datos.
4. `WelcomeMailer < MiniMailer` — `welcome(user)` genera email de bienvenida.
MD,
            'starter_code' => <<<'RUBY'
class JobQueue
  @@instance = nil

  def self.instance
    @@instance ||= new
  end

  def initialize
    @queue = []
  end

  def enqueue(job)  ; end
  def perform_all   ; end
  def size          ; end
  def clear         ; end
end

class BaseJob
  def self.perform_later(*args)
  end

  def perform(*args)
    raise NotImplementedError
  end
end

class MiniMailer
  def mail(to:, subject:, body:)
  end

  def deliver_later
  end
end

class WelcomeMailer < MiniMailer
  def welcome(user)
  end
end
RUBY,
            'solution_code' => <<<'RUBY'
class JobQueue
  @@instance = nil

  def self.instance
    @@instance ||= new
  end

  def self.reset!
    @@instance = new
  end

  def initialize
    @queue = []
  end

  def enqueue(job)
    @queue << job
  end

  def perform_all
    results = []
    while (job = @queue.shift)
      results << job.perform
    end
    results
  end

  def size
    @queue.size
  end

  def clear
    @queue.clear
  end
end

class BaseJob
  attr_reader :args

  def self.perform_later(*args)
    job = new(*args)
    JobQueue.instance.enqueue(job)
    job
  end

  def perform
    raise NotImplementedError
  end
end

class MailJob < BaseJob
  def initialize(mail_data)
    @mail_data = mail_data
  end

  def perform
    @mail_data.merge(delivered: true)
  end
end

class MiniMailer
  def mail(to:, subject:, body:)
    @mail_data = { to: to, subject: subject, body: body }
  end

  def deliver_later
    MailJob.perform_later(@mail_data)
  end
end

class WelcomeMailer < MiniMailer
  def welcome(user)
    mail(
      to:      user[:email],
      subject: "¡Bienvenido #{user[:name]}!",
      body:    "Hola #{user[:name]}, gracias por registrarte."
    )
    self
  end
end
RUBY,
        ];

        // ── Lección 16: Testing en Rails ────────────────────────────
        if ($l = $lessons->get(16)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mini framework de testing al estilo RSpec',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Crea un mini framework de testing inspirado en RSpec.

1. `MiniSpec` — DSL con `describe(name, &block)` y `it(name, &block)`. Ejecuta los tests y recolecta resultados.
2. `expect(value)` — retorna `Expectation`. Métodos: `to_eq(expected)`, `to_be_truthy`, `to_include(item)`, `to_raise(error_class, &block)`.
3. `let(name, &block)` — define helper lazy evaluado.
4. `before_each(&block)` — ejecuta antes de cada test.
5. `results` — retorna `{ passed: N, failed: N, errors: [...] }`.
MD,
            'starter_code' => <<<'RUBY'
class Expectation
  def initialize(value)
    @value = value
  end

  def to_eq(expected)   ; end
  def to_be_truthy      ; end
  def to_include(item)  ; end
end

class MiniSpec
  attr_reader :results

  def initialize
    @results = { passed: 0, failed: 0, errors: [] }
  end

  def describe(name, &block)  ; end
  def it(name, &block)        ; end
  def expect(value)           ; end
  def let(name, &block)       ; end
  def before_each(&block)     ; end
end
RUBY,
            'solution_code' => <<<'RUBY'
class Expectation
  def initialize(value)
    @value = value
  end

  def to_eq(expected)
    @value == expected or raise "Expected #{expected.inspect}, got #{@value.inspect}"
  end

  def to_be_truthy
    @value or raise "Expected truthy, got #{@value.inspect}"
  end

  def to_include(item)
    @value.include?(item) or raise "Expected #{@value.inspect} to include #{item.inspect}"
  end
end

class MiniSpec
  attr_reader :results

  def initialize
    @results     = { passed: 0, failed: 0, errors: [] }
    @lets        = {}
    @before_each = nil
  end

  def describe(name, &block)
    instance_eval(&block)
  end

  def it(name, &block)
    @before_each&.call
    instance_eval(&block)
    @results[:passed] += 1
  rescue => e
    @results[:failed] += 1
    @results[:errors] << "#{name}: #{e.message}"
  end

  def expect(value)
    Expectation.new(value)
  end

  def let(name, &block)
    @lets[name] = block
    define_singleton_method(name) do
      instance_variable_get(:"@let_#{name}") ||
        instance_variable_set(:"@let_#{name}", @lets[name].call)
    end
  end

  def before_each(&block)
    @before_each = block
  end
end
RUBY,
        ];

        // ── Lección 17: Deployment ──────────────────────────────────
        if ($l = $lessons->get(17)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulador de configuración de deploy',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Simula la configuración de deploy de una app Rails con Kamal.

1. `DeployConfig` — DSL para configurar deploy:
   - `service(name)`, `image(name)`, `servers(*list)` — setean valores.
   - `env(**vars)` — merge de variables de entorno.
   - `healthcheck(path:, interval:)` — configura healthcheck.
   - `to_hash` — retorna toda la configuración como hash.
2. `DeployConfig.define(&block)` — evalúa bloque en contexto de nueva instancia.
3. `CredentialStore` — `set(key, value)` cifra en Base64 (simulado). `get(key)` descifra. `credentials` retorna hash con keys.
MD,
            'starter_code' => <<<'RUBY'
require 'base64'

class DeployConfig
  def self.define(&block)
    # Evalúa bloque y retorna config
  end

  def initialize
    @config = {}
  end

  def service(name)    ; end
  def image(name)      ; end
  def servers(*list)   ; end
  def env(**vars)      ; end
  def healthcheck(path:, interval:) ; end
  def to_hash          ; end
end

class CredentialStore
  def initialize
    @store = {}
  end

  def set(key, value) ; end
  def get(key)        ; end
  def credentials     ; end
end
RUBY,
            'solution_code' => <<<'RUBY'
require 'base64'

class DeployConfig
  def self.define(&block)
    config = new
    config.instance_eval(&block)
    config
  end

  def initialize
    @config = { env: {} }
  end

  def service(name)
    @config[:service] = name
  end

  def image(name)
    @config[:image] = name
  end

  def servers(*list)
    @config[:servers] = list.flatten
  end

  def env(**vars)
    @config[:env].merge!(vars)
  end

  def healthcheck(path:, interval:)
    @config[:healthcheck] = { path: path, interval: interval }
  end

  def to_hash
    @config
  end
end

class CredentialStore
  def initialize
    @store = {}
  end

  def set(key, value)
    @store[key] = Base64.strict_encode64(value.to_s)
  end

  def get(key)
    encoded = @store[key]
    return nil unless encoded
    Base64.strict_decode64(encoded)
  end

  def credentials
    @store.transform_values { |v| Base64.strict_decode64(v) }
  end
end
RUBY,
        ];

        // ── Lección 18: Novedades Rails 8 ───────────────────────────
        if ($l = $lessons->get(18)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Solid Trifecta: Cache, Queue y Cable adapters',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Simula los Solid adapters de Rails 8 (Solid Cache, Solid Queue, Solid Cable).

1. `SolidCache` — `read(key)` y `write(key, value, expires_in: nil)`. `fetch(key, expires_in: nil, &block)` lee o ejecuta bloque y guarda. `delete(key)`. `clear`. Respeta expiración.
2. `SolidQueue` — `enqueue(job_class, *args, priority: 0)`. `process_next` ejecuta el job con mayor prioridad (menor número). `size`. `scheduled(at:, job_class:, args:)` encola para futuro.
3. `SolidCable` — `subscribe(channel, &callback)`. `broadcast(channel, message)`. `unsubscribe(channel, id)`. Similar al ejercicio de Action Cable pero in-memory.
MD,
            'starter_code' => <<<'RUBY'
class SolidCache
  def initialize
    @store = {}
  end

  def read(key)       ; end
  def write(key, value, expires_in: nil) ; end
  def fetch(key, expires_in: nil, &block); end
  def delete(key)     ; end
  def clear            ; end
end

class SolidQueue
  def initialize
    @queue = []
  end

  def enqueue(job_class, *args, priority: 0) ; end
  def process_next    ; end
  def size             ; end
end

class SolidCable
  def initialize
    @channels = Hash.new { |h, k| h[k] = {} }
    @next_id  = 0
  end

  def subscribe(channel, &callback) ; end
  def broadcast(channel, message)   ; end
  def unsubscribe(channel, id)      ; end
end
RUBY,
            'solution_code' => <<<'RUBY'
class SolidCache
  def initialize
    @store = {}
  end

  def read(key)
    entry = @store[key]
    return nil unless entry
    if entry[:expires_at] && Time.now > entry[:expires_at]
      @store.delete(key)
      return nil
    end
    entry[:value]
  end

  def write(key, value, expires_in: nil)
    expires_at = expires_in ? Time.now + expires_in : nil
    @store[key] = { value: value, expires_at: expires_at }
  end

  def fetch(key, expires_in: nil, &block)
    cached = read(key)
    return cached unless cached.nil?
    value = block.call
    write(key, value, expires_in: expires_in)
    value
  end

  def delete(key)
    @store.delete(key)
  end

  def clear
    @store.clear
  end
end

class SolidQueue
  def initialize
    @queue = []
  end

  def enqueue(job_class, *args, priority: 0)
    @queue << { job_class: job_class, args: args, priority: priority }
    @queue.sort_by! { |j| j[:priority] }
  end

  def process_next
    job = @queue.shift
    return nil unless job
    job[:job_class].new.perform(*job[:args])
  end

  def size
    @queue.size
  end
end

class SolidCable
  def initialize
    @channels = Hash.new { |h, k| h[k] = {} }
    @next_id  = 0
  end

  def subscribe(channel, &callback)
    id = @next_id += 1
    @channels[channel][id] = callback
    id
  end

  def broadcast(channel, message)
    @channels[channel].each_value { |cb| cb.call(message) }
  end

  def unsubscribe(channel, id)
    @channels[channel].delete(id)
  end
end
RUBY,
        ];

        // ── Lección 19: Preguntas de Entrevista ─────────────────────
        if ($l = $lessons->get(19)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Algoritmos clásicos de entrevista Rails',
            'language'     => 'ruby',
            'description'  => <<<'MD'
Resuelve problemas comunes de entrevistas sobre Rails.

Implementa:
1. `n_plus_one_fix(posts_with_comments)` — recibe array de hashes `[{ post:, comments: [...] }]` y retorna hash `{ posts: N, total_comments: N, avg_comments: Float }` en una sola pasada (sin queries anidados).
2. `scope_chain(records, filters)` — recibe array de hashes y array de lambdas/procs filtradoras. Aplica cada filtro secuencialmente. Retorna records filtrados.
3. `concern_modules(base_class, *concerns)` — simula concerns: recibe clase y módulos, incluye todos y retorna los métodos añadidos.
4. `optimize_query(items, strategy:)` — si strategy es `:batch`, divide items en grupos de 100. Si `:cache`, retorna `{ cached: items, key: sha256_hex }`.
MD,
            'starter_code' => <<<'RUBY'
require 'digest'

def n_plus_one_fix(posts_with_comments)
  # Calcula stats en una sola pasada
end

def scope_chain(records, filters)
  # Aplica filtros secuencialmente
end

def concern_modules(base_class, *concerns)
  # Incluye concerns y retorna métodos añadidos
end

def optimize_query(items, strategy:)
  # Optimiza según estrategia
end
RUBY,
            'solution_code' => <<<'RUBY'
require 'digest'

def n_plus_one_fix(posts_with_comments)
  total = 0
  posts_with_comments.each { |p| total += p[:comments].size }
  count = posts_with_comments.size
  {
    posts:          count,
    total_comments: total,
    avg_comments:   count.zero? ? 0.0 : total.to_f / count
  }
end

def scope_chain(records, filters)
  filters.reduce(records) { |acc, filter| acc.select(&filter) }
end

def concern_modules(base_class, *concerns)
  original = base_class.instance_methods(false)
  concerns.each { |c| base_class.include(c) }
  base_class.instance_methods(false) - original
end

def optimize_query(items, strategy:)
  case strategy
  when :batch
    items.each_slice(100).to_a
  when :cache
    key = Digest::SHA256.hexdigest(items.to_s)
    { cached: items, key: key }
  else
    items
  end
end
RUBY,
        ];

        return $ex;
    }
}
