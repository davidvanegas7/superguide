# Gemas y Bundler

Las gemas son paquetes de Ruby y Bundler es el gestor de dependencias estándar.

---

## RubyGems

```bash
# Instalar una gema
gem install httparty
gem install rails -v 8.0.0

# Listar gemas instaladas
gem list

# Información de una gema
gem info httparty

# Buscar gemas
gem search json

# Desinstalar
gem uninstall httparty

# Actualizar
gem update httparty
```

### Usar gemas en código

```ruby
require 'httparty'
require 'json'

response = HTTParty.get('https://api.github.com/users/octocat')
puts response['name']     # "The Octocat"
puts response['location'] # "San Francisco"
```

---

## Bundler

Bundler gestiona las dependencias de un proyecto Ruby.

### Gemfile

```ruby
# Gemfile
source 'https://rubygems.org'

# Versión de Ruby
ruby '3.3.0'

# Gemas principales
gem 'rails', '~> 8.0'
gem 'pg', '~> 1.5'
gem 'puma', '>= 6.0'
gem 'redis', '~> 5.0'

# Solo en desarrollo y test
group :development, :test do
  gem 'rspec-rails', '~> 7.0'
  gem 'rubocop', '~> 1.60'
  gem 'debug', '>= 1.0'
end

# Solo en desarrollo
group :development do
  gem 'solargraph'
  gem 'web-console'
end

# Solo en producción
group :production do
  gem 'aws-sdk-s3'
end
```

### Operadores de versión

```ruby
gem 'rails', '8.0.0'       # exacta
gem 'rails', '>= 8.0'      # mayor o igual
gem 'rails', '~> 8.0'      # >= 8.0, < 9.0 (pessimistic)
gem 'rails', '~> 8.0.1'    # >= 8.0.1, < 8.1 (patch level)
gem 'rails', '>= 7.0', '< 9.0'  # rango
```

### Comandos de Bundler

```bash
# Instalar dependencias
bundle install

# Actualizar gemas
bundle update              # todas
bundle update rails        # solo una

# Ejecutar con el entorno de bundle
bundle exec rake db:migrate
bundle exec rspec

# Ver gemas instaladas
bundle list

# Ver gemas desactualizadas
bundle outdated

# Añadir gema
bundle add httparty

# Crear Gemfile.lock
bundle lock

# Abrir código fuente de una gema
bundle open rails
```

---

## Crear tu propia gema

### Estructura

```bash
bundle gem mi_gema
```

```
mi_gema/
├── Gemfile
├── README.md
├── Rakefile
├── mi_gema.gemspec
├── lib/
│   ├── mi_gema.rb
│   └── mi_gema/
│       └── version.rb
├── spec/
│   ├── mi_gema_spec.rb
│   └── spec_helper.rb
└── sig/
    └── mi_gema.rbs
```

### Gemspec

```ruby
# mi_gema.gemspec
Gem::Specification.new do |spec|
  spec.name          = "mi_gema"
  spec.version       = MiGema::VERSION
  spec.authors       = ["Tu Nombre"]
  spec.email         = ["tu@email.com"]
  spec.summary       = "Descripción corta"
  spec.description   = "Descripción larga de la gema"
  spec.homepage      = "https://github.com/user/mi_gema"
  spec.license       = "MIT"

  spec.required_ruby_version = ">= 3.0"

  spec.files = Dir["lib/**/*", "README.md", "LICENSE"]
  spec.require_paths = ["lib"]

  spec.add_dependency "httparty", "~> 0.21"
  spec.add_development_dependency "rspec", "~> 3.12"
end
```

### Código de la gema

```ruby
# lib/mi_gema.rb
require_relative "mi_gema/version"

module MiGema
  class Error < StandardError; end

  class Client
    def initialize(api_key:)
      @api_key = api_key
    end

    def fetch(endpoint)
      # lógica
    end
  end
end

# lib/mi_gema/version.rb
module MiGema
  VERSION = "0.1.0"
end
```

### Publicar

```bash
# Construir
gem build mi_gema.gemspec

# Publicar en RubyGems
gem push mi_gema-0.1.0.gem

# O instalar localmente
gem install mi_gema-0.1.0.gem
```

---

## Rake (tareas)

```ruby
# Rakefile
require 'rake'

desc "Ejecutar las pruebas"
task :test do
  sh 'bundle exec rspec'
end

desc "Lint del código"
task :lint do
  sh 'bundle exec rubocop'
end

namespace :db do
  desc "Crear la base de datos"
  task :create do
    puts "Creando base de datos..."
  end

  desc "Migrar la base de datos"
  task :migrate do
    puts "Ejecutando migraciones..."
  end

  desc "Seed de datos"
  task seed: :migrate do   # depende de migrate
    puts "Insertando datos..."
  end
end

task default: [:lint, :test]
```

```bash
rake              # ejecuta default
rake test         # ejecuta test
rake db:migrate   # ejecuta la tarea dentro del namespace
rake -T           # lista todas las tareas
```

---

## Gestión de versiones de Ruby

### rbenv

```bash
rbenv install --list         # versiones disponibles
rbenv install 3.3.0          # instalar
rbenv global 3.3.0           # establecer global
rbenv local 3.2.0            # establecer por proyecto (.ruby-version)
rbenv versions               # listar instaladas
```

### .ruby-version

```
3.3.0
```

---

## Resumen

| Concepto | Descripción |
|---|---|
| `gem install` | Instalar gema individual |
| `Gemfile` | Declarar dependencias del proyecto |
| `bundle install` | Instalar dependencias |
| `bundle exec` | Ejecutar en contexto |
| `~>` | Version pessimistic (minor/patch) |
| `.gemspec` | Especificación de gema |
| `Rakefile` | Tareas automatizadas |
| `rbenv` | Gestor de versiones de Ruby |
