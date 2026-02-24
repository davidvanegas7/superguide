# Testing en Rails

En esta lección aprenderás a escribir tests en Rails para garantizar la calidad y estabilidad de tu aplicación. Cubriremos desde tests unitarios hasta tests de sistema.

---

## ¿Por qué testear?

Los tests son fundamentales en cualquier proyecto profesional. Permiten:

- Detectar bugs antes de que lleguen a producción
- Refactorizar con confianza
- Documentar el comportamiento esperado del código
- Facilitar la colaboración en equipo
- Automatizar la verificación de funcionalidades

Rails viene con un framework de testing integrado desde el primer momento.

---

## Minitest vs RSpec

Rails incluye **Minitest** por defecto. **RSpec** es una alternativa popular con una sintaxis más expresiva.

| Característica | Minitest | RSpec |
|---|---|---|
| Incluido en Rails | ✅ Sí | ❌ Gema externa |
| Sintaxis | Clásica (métodos) | DSL (describe/it) |
| Velocidad | Más rápido | Ligeramente más lento |
| Comunidad | Oficial Rails | Muy popular |
| Curva de aprendizaje | Menor | Mayor al inicio |

### Ejemplo Minitest:

```ruby
class UserTest < ActiveSupport::TestCase
  test "debe tener un nombre válido" do
    user = User.new(name: "", email: "test@test.com")
    assert_not user.valid?
    assert_includes user.errors[:name], "can't be blank"
  end
end
```

### Ejemplo RSpec:

```ruby
RSpec.describe User, type: :model do
  it "debe tener un nombre válido" do
    user = User.new(name: "", email: "test@test.com")
    expect(user).not_to be_valid
    expect(user.errors[:name]).to include("can't be blank")
  end
end
```

> **Nota:** En este curso usaremos Minitest por ser el estándar de Rails, pero los conceptos aplican a ambos frameworks.

---

## Estructura del directorio de tests

```
test/
├── controllers/          # Tests de controladores
│   └── articles_controller_test.rb
├── fixtures/             # Datos de prueba en YAML
│   ├── articles.yml
│   └── users.yml
├── helpers/              # Tests de helpers
├── integration/          # Tests de integración
│   └── user_flows_test.rb
├── mailers/              # Tests de mailers
│   └── user_mailer_test.rb
├── models/               # Tests de modelos
│   └── user_test.rb
├── system/               # Tests de sistema (navegador)
│   └── articles_test.rb
├── test_helper.rb        # Configuración global de tests
└── application_system_test_case.rb
```

---

## Tests de modelos

Los tests de modelos verifican validaciones, asociaciones, métodos y scopes:

```ruby
# test/models/user_test.rb
require "test_helper"

class UserTest < ActiveSupport::TestCase
  test "usuario válido con todos los campos" do
    user = User.new(
      name: "María López",
      email: "maria@example.com",
      password: "password123"
    )
    assert user.valid?
  end

  test "no es válido sin email" do
    user = User.new(name: "María", password: "password123")
    assert_not user.valid?
    assert_includes user.errors[:email], "can't be blank"
  end

  test "email debe ser único" do
    User.create!(name: "Ana", email: "ana@test.com", password: "pass123")
    duplicate = User.new(name: "Otra Ana", email: "ana@test.com", password: "pass456")
    assert_not duplicate.valid?
  end

  test "nombre completo combina nombre y apellido" do
    user = User.new(first_name: "Carlos", last_name: "García")
    assert_equal "Carlos García", user.full_name
  end

  test "scope activos retorna solo usuarios activos" do
    active_count = User.active.count
    assert active_count >= 0
  end

  test "puede tener muchos artículos" do
    user = users(:maria)
    assert_respond_to user, :articles
  end
end
```

Ejecutar tests de modelos:

```bash
bin/rails test test/models/

# Un archivo específico
bin/rails test test/models/user_test.rb

# Un test específico por línea
bin/rails test test/models/user_test.rb:10
```

---

## Fixtures

Las fixtures son datos de prueba definidos en archivos YAML:

```yaml
# test/fixtures/users.yml
maria:
  name: María López
  email: maria@example.com
  password_digest: <%= BCrypt::Password.create("password123") %>
  role: admin

carlos:
  name: Carlos García
  email: carlos@example.com
  password_digest: <%= BCrypt::Password.create("password123") %>
  role: user
```

```yaml
# test/fixtures/articles.yml
primer_articulo:
  title: Introducción a Rails
  body: Rails es un framework web para Ruby...
  published: true
  user: maria

borrador:
  title: Artículo en borrador
  body: Este artículo aún no está publicado...
  published: false
  user: carlos
```

Accede a las fixtures en los tests:

```ruby
test "fixture maria existe y es admin" do
  user = users(:maria)
  assert_equal "María López", user.name
  assert_equal "admin", user.role
end
```

---

## Fixtures vs Factories (FactoryBot)

FactoryBot es una alternativa a las fixtures que permite crear objetos de prueba de forma más flexible:

```ruby
# Gemfile (grupo test)
gem "factory_bot_rails"
```

```ruby
# test/factories/users.rb
FactoryBot.define do
  factory :user do
    name { "Usuario de prueba" }
    sequence(:email) { |n| "user#{n}@test.com" }
    password { "password123" }

    trait :admin do
      role { "admin" }
    end

    trait :with_articles do
      after(:create) do |user|
        create_list(:article, 3, user: user)
      end
    end
  end
end
```

```ruby
# En los tests
test "crear usuario con factory" do
  user = create(:user)
  assert user.persisted?

  admin = create(:user, :admin)
  assert_equal "admin", admin.role

  user_con_articulos = create(:user, :with_articles)
  assert_equal 3, user_con_articulos.articles.count
end
```

> **Tip:** Las fixtures son más rápidas (se cargan una vez en transacción), pero las factories son más flexibles. Usa fixtures para datos base y factories para escenarios específicos.

---

## Tests de controladores

```ruby
# test/controllers/articles_controller_test.rb
require "test_helper"

class ArticlesControllerTest < ActionDispatch::IntegrationTest
  setup do
    @user = users(:maria)
    @article = articles(:primer_articulo)
  end

  test "debe obtener index" do
    get articles_url
    assert_response :success
  end

  test "debe mostrar artículo" do
    get article_url(@article)
    assert_response :success
    assert_select "h1", @article.title
  end

  test "debe crear artículo cuando está autenticado" do
    sign_in @user

    assert_difference("Article.count", 1) do
      post articles_url, params: {
        article: { title: "Nuevo artículo", body: "Contenido...", published: true }
      }
    end

    assert_redirected_to article_url(Article.last)
  end

  test "no debe crear artículo sin autenticación" do
    post articles_url, params: {
      article: { title: "Nuevo", body: "Contenido" }
    }
    assert_redirected_to login_url
  end

  test "debe actualizar artículo" do
    sign_in @user
    patch article_url(@article), params: {
      article: { title: "Título actualizado" }
    }
    assert_redirected_to article_url(@article)
    @article.reload
    assert_equal "Título actualizado", @article.title
  end

  test "debe eliminar artículo" do
    sign_in @user
    assert_difference("Article.count", -1) do
      delete article_url(@article)
    end
    assert_redirected_to articles_url
  end
end
```

---

## Tests de integración

Los tests de integración verifican flujos completos de usuario:

```ruby
# test/integration/user_registration_flow_test.rb
require "test_helper"

class UserRegistrationFlowTest < ActionDispatch::IntegrationTest
  test "flujo completo de registro y login" do
    # Visitar página de registro
    get signup_url
    assert_response :success

    # Registrarse
    assert_difference("User.count", 1) do
      post users_url, params: {
        user: {
          name: "Nuevo Usuario",
          email: "nuevo@test.com",
          password: "password123",
          password_confirmation: "password123"
        }
      }
    end
    assert_redirected_to root_url
    follow_redirect!
    assert_select ".flash-notice", "Cuenta creada exitosamente"

    # Cerrar sesión
    delete logout_url
    assert_redirected_to root_url

    # Iniciar sesión
    post login_url, params: { email: "nuevo@test.com", password: "password123" }
    assert_redirected_to dashboard_url
    follow_redirect!
    assert_select "h1", /Dashboard/
  end
end
```

---

## Tests de sistema (Capybara)

Los tests de sistema simulan interacciones reales del usuario en el navegador:

```ruby
# test/application_system_test_case.rb
require "test_helper"

class ApplicationSystemTestCase < ActionDispatch::SystemTestCase
  driven_by :selenium, using: :headless_chrome, screen_size: [1400, 900]
end
```

```ruby
# test/system/articles_test.rb
require "application_system_test_case"

class ArticlesTest < ApplicationSystemTestCase
  setup do
    @user = users(:maria)
  end

  test "crear un artículo nuevo" do
    visit login_url
    fill_in "Email", with: @user.email
    fill_in "Contraseña", with: "password123"
    click_on "Iniciar sesión"

    visit new_article_url
    fill_in "Título", with: "Mi artículo de prueba"
    fill_in "Contenido", with: "Este es el contenido del artículo."
    check "Publicado"
    click_on "Crear artículo"

    assert_text "Artículo creado exitosamente"
    assert_text "Mi artículo de prueba"
  end

  test "buscar artículos" do
    visit articles_url
    fill_in "Buscar", with: "Rails"
    click_on "Buscar"

    assert_selector ".article-card", minimum: 1
  end
end
```

Ejecutar tests de sistema:

```bash
bin/rails test:system
```

---

## Assertions comunes

```ruby
# Igualdad
assert_equal expected, actual
assert_not_equal unexpected, actual

# Verdadero / Falso
assert condition
assert_not condition

# Nil
assert_nil object
assert_not_nil object

# Inclusión
assert_includes collection, item

# Diferencia en base de datos
assert_difference("Model.count", 1) { create_action }
assert_no_difference("Model.count") { failed_action }

# Respuesta HTTP
assert_response :success      # 200
assert_response :redirect     # 3xx
assert_response :not_found    # 404

# Redirecciones
assert_redirected_to path

# Selectores HTML
assert_select "h1", "Texto esperado"
assert_select ".clase", count: 3

# Excepciones
assert_raises(ActiveRecord::RecordNotFound) { action }

# Emails
assert_emails 1 do
  UserMailer.welcome(user).deliver_now
end
```

---

## Test Helpers personalizados

```ruby
# test/test_helper.rb
ENV["RAILS_ENV"] ||= "test"
require_relative "../config/environment"
require "rails/test_help"

module ActiveSupport
  class TestCase
    parallelize(workers: :number_of_processors)
    fixtures :all

    # Helper para autenticación en tests
    def sign_in(user)
      post login_url, params: { email: user.email, password: "password123" }
    end

    # Helper para crear datos de prueba
    def create_published_article(user:, title: "Test Article")
      Article.create!(title: title, body: "Contenido", published: true, user: user)
    end
  end
end
```

---

## Configurar CI (Integración Continua)

```yaml
# .github/workflows/ci.yml
name: CI

on:
  pull_request:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_PASSWORD: postgres
        ports:
          - 5432:5432
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

    steps:
      - uses: actions/checkout@v4
      - uses: ruby/setup-ruby@v1
        with:
          bundler-cache: true

      - name: Configurar base de datos
        env:
          DATABASE_URL: postgres://postgres:postgres@localhost/test
          RAILS_ENV: test
        run: |
          bin/rails db:create
          bin/rails db:schema:load

      - name: Ejecutar tests
        env:
          DATABASE_URL: postgres://postgres:postgres@localhost/test
          RAILS_ENV: test
        run: bin/rails test

      - name: Ejecutar tests de sistema
        env:
          DATABASE_URL: postgres://postgres:postgres@localhost/test
          RAILS_ENV: test
        run: bin/rails test:system
```

---

## Resumen

- Rails incluye **Minitest** por defecto; **RSpec** es una alternativa popular.
- Organiza tus tests en carpetas: `models/`, `controllers/`, `integration/`, `system/`.
- Usa **fixtures** para datos base y **FactoryBot** para escenarios específicos.
- Los **tests de modelo** verifican validaciones, asociaciones y lógica de negocio.
- Los **tests de controlador** verifican respuestas HTTP y flujos de datos.
- Los **tests de sistema** con **Capybara** simulan interacciones del usuario en el navegador.
- Configura **CI** con GitHub Actions para ejecutar tests automáticamente en cada push.
- Ejecuta tests con `bin/rails test` y tests de sistema con `bin/rails test:system`.
