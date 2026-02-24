# Active Record: Modelos y Migraciones

Active Record es el ORM (Object-Relational Mapping) de Rails. Implementa el patrón Active Record, donde cada clase modelo representa una tabla de la base de datos y cada instancia representa una fila.

---

## ¿Qué es el patrón Active Record?

El patrón Active Record conecta objetos de tu aplicación con tablas de la base de datos:

```ruby
# El modelo Article representa la tabla "articles"
# Cada instancia de Article es una fila de esa tabla
article = Article.new(title: "Hola", body: "Contenido")
article.save  # INSERT INTO articles (title, body) VALUES ('Hola', 'Contenido')

article.title = "Nuevo título"
article.save  # UPDATE articles SET title = 'Nuevo título' WHERE id = 1
```

No necesitas escribir SQL manualmente. Active Record traduce métodos Ruby a consultas SQL.

---

## Crear un modelo

```bash
# Generar un modelo con sus columnas
rails g model Article title:string body:text published:boolean views_count:integer

# Esto crea:
# - app/models/article.rb          → el modelo
# - db/migrate/xxxx_create_articles.rb → la migración
# - test/models/article_test.rb    → los tests
```

El modelo generado es muy simple:

```ruby
# app/models/article.rb
class Article < ApplicationRecord
end
```

> 💡 No necesitas declarar las columnas en el modelo. Active Record las detecta automáticamente desde la base de datos.

---

## Migraciones

Las migraciones son archivos Ruby que describen cambios en la estructura de la base de datos. Son como un sistema de control de versiones para tu esquema.

### Crear una tabla

```ruby
# db/migrate/20260223000001_create_articles.rb
class CreateArticles < ActiveRecord::Migration[8.0]
  def change
    create_table :articles do |t|
      t.string  :title, null: false
      t.text    :body
      t.boolean :published, default: false
      t.integer :views_count, default: 0
      t.timestamps  # crea created_at y updated_at
    end

    add_index :articles, :title
    add_index :articles, :published
  end
end
```

### Agregar columnas

```bash
rails g migration AddCategoryToArticles category:string position:integer
```

```ruby
# db/migrate/20260223000002_add_category_to_articles.rb
class AddCategoryToArticles < ActiveRecord::Migration[8.0]
  def change
    add_column :articles, :category, :string, default: "general"
    add_column :articles, :position, :integer
    add_index  :articles, :category
  end
end
```

### Modificar columnas

```ruby
class ChangeArticlesTitleLimit < ActiveRecord::Migration[8.0]
  def change
    change_column :articles, :title, :string, limit: 200
  end
end
```

### Eliminar columnas

```bash
rails g migration RemovePositionFromArticles position:integer
```

```ruby
class RemovePositionFromArticles < ActiveRecord::Migration[8.0]
  def change
    remove_column :articles, :position, :integer
  end
end
```

### Renombrar columnas y tablas

```ruby
class RenameArticlesCategory < ActiveRecord::Migration[8.0]
  def change
    rename_column :articles, :category, :section
    # rename_table :articles, :posts
  end
end
```

---

## Tipos de datos disponibles

| Tipo Ruby        | SQL (PostgreSQL)  | Ejemplo de uso                |
|------------------|-------------------|-------------------------------|
| `:string`        | `varchar(255)`    | Nombres, títulos cortos       |
| `:text`          | `text`            | Contenido largo, descripciones|
| `:integer`       | `integer`         | Contadores, IDs               |
| `:bigint`        | `bigint`          | IDs grandes, timestamps       |
| `:float`         | `float`           | Números decimales aproximados |
| `:decimal`       | `decimal`         | Dinero, valores exactos       |
| `:boolean`       | `boolean`         | Verdadero/falso               |
| `:date`          | `date`            | Fechas sin hora               |
| `:datetime`      | `timestamp`       | Fechas con hora               |
| `:time`          | `time`            | Solo hora                     |
| `:binary`        | `bytea`           | Archivos binarios             |
| `:json`          | `json`            | Datos JSON                    |
| `:jsonb`         | `jsonb`           | JSON indexable (PostgreSQL)   |

---

## Ejecutar migraciones

```bash
# Ejecutar todas las migraciones pendientes
rails db:migrate

# Ver el estado de las migraciones
rails db:migrate:status

# Ejecutar una migración específica
rails db:migrate VERSION=20260223000001

# Revertir la última migración
rails db:rollback

# Revertir las últimas N migraciones
rails db:rollback STEP=3

# Rehacer la última migración (rollback + migrate)
rails db:migrate:redo

# Resetear la base de datos completa (¡cuidado en producción!)
rails db:reset     # drop + create + migrate + seed
rails db:setup     # create + migrate + seed
```

---

## El archivo schema.rb

Después de cada migración, Rails actualiza `db/schema.rb` con el esquema actual de la base de datos:

```ruby
# db/schema.rb — NUNCA edites este archivo manualmente
ActiveRecord::Schema[8.0].define(version: 2026_02_23_000002) do
  create_table "articles", force: :cascade do |t|
    t.string  "title", null: false
    t.text    "body"
    t.boolean "published", default: false
    t.integer "views_count", default: 0
    t.string  "category", default: "general"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["category"], name: "index_articles_on_category"
    t.index ["published"], name: "index_articles_on_published"
    t.index ["title"], name: "index_articles_on_title"
  end
end
```

> 💡 `schema.rb` es útil para crear la base de datos de un vistazo con `rails db:schema:load`, sin ejecutar todas las migraciones una por una.

---

## Seeds: datos iniciales

El archivo `db/seeds.rb` permite poblar la base de datos con datos de ejemplo:

```ruby
# db/seeds.rb
puts "Creando artículos..."

Article.create!([
  {
    title: "Introducción a Rails",
    body: "Rails es un framework web escrito en Ruby...",
    published: true,
    category: "tutorial"
  },
  {
    title: "Active Record para principiantes",
    body: "Active Record es el ORM de Rails...",
    published: true,
    category: "tutorial"
  },
  {
    title: "Borrador: Novedades Rails 8",
    body: "Rails 8 incluye muchas mejoras...",
    published: false,
    category: "noticias"
  }
])

puts "Se crearon #{Article.count} artículos."
```

```bash
# Ejecutar seeds
rails db:seed
```

---

## CRUD con Active Record

### Crear registros

```ruby
# Método 1: new + save
article = Article.new(title: "Mi artículo", body: "Contenido")
article.save  # retorna true/false

# Método 2: create (new + save en un paso)
article = Article.create(title: "Mi artículo", body: "Contenido")

# Método 3: create! (lanza excepción si falla)
article = Article.create!(title: "Mi artículo", body: "Contenido")
```

### Leer registros

```ruby
# Buscar por ID
article = Article.find(1)          # lanza excepción si no existe
article = Article.find_by(id: 1)   # retorna nil si no existe

# Buscar por atributo
article = Article.find_by(title: "Mi artículo")

# Todos los registros
articles = Article.all

# Filtrar con where
articles = Article.where(published: true)
articles = Article.where("views_count > ?", 100)
articles = Article.where(category: ["tutorial", "noticias"])

# Ordenar
articles = Article.order(created_at: :desc)
articles = Article.order(:title)

# Limitar resultados
articles = Article.limit(10).offset(20)

# Encadenar consultas
articles = Article.where(published: true)
                  .where(category: "tutorial")
                  .order(created_at: :desc)
                  .limit(5)

# Primero y último
Article.first
Article.last
```

### Actualizar registros

```ruby
article = Article.find(1)

# Método 1: asignar y guardar
article.title = "Nuevo título"
article.save

# Método 2: update (asigna y guarda en un paso)
article.update(title: "Nuevo título", published: true)

# Método 3: update! (lanza excepción si falla)
article.update!(title: "Nuevo título")

# Actualizar múltiples registros
Article.where(published: false).update_all(published: true)
```

### Eliminar registros

```ruby
article = Article.find(1)

# Eliminar un registro (ejecuta callbacks)
article.destroy

# Eliminar sin callbacks
article.delete

# Eliminar múltiples registros
Article.where(published: false).destroy_all
Article.where("created_at < ?", 1.year.ago).delete_all
```

---

## Métodos útiles de consulta

```ruby
# Contar
Article.count
Article.where(published: true).count

# Verificar existencia
Article.exists?(title: "Mi artículo")
Article.where(published: true).any?
Article.where(published: true).none?

# Agregaciones
Article.average(:views_count)
Article.maximum(:views_count)
Article.minimum(:views_count)
Article.sum(:views_count)

# Pluck: obtener un array de valores
Article.pluck(:title)         # => ["Título 1", "Título 2"]
Article.pluck(:id, :title)    # => [[1, "Título 1"], [2, "Título 2"]]

# Select y distinct
Article.select(:category).distinct
```

---

## Resumen

En esta lección aprendiste:

- Qué es el patrón Active Record y cómo Rails lo implementa
- Cómo crear modelos con el generador `rails g model`
- Cómo escribir migraciones para crear, modificar y eliminar tablas y columnas
- Los tipos de datos disponibles en migraciones
- Cómo ejecutar, revertir y gestionar migraciones
- El rol de `schema.rb` y `seeds.rb`
- Las operaciones CRUD completas con Active Record

En la siguiente lección veremos las **asociaciones** entre modelos: `belongs_to`, `has_many`, y más.
