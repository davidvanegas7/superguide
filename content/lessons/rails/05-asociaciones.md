# Asociaciones en Active Record

Las asociaciones permiten definir relaciones entre modelos. En lugar de escribir JOINs manualmente, Rails te ofrece macros declarativas que generan los métodos necesarios para navegar entre registros relacionados.

---

## belongs_to

Indica que un modelo pertenece a otro. La tabla debe tener una columna de clave foránea:

```ruby
# Un comentario pertenece a un artículo
# La tabla comments tiene una columna article_id

class Comment < ApplicationRecord
  belongs_to :article
end
```

```ruby
# Migración para crear la tabla comments
class CreateComments < ActiveRecord::Migration[8.0]
  def change
    create_table :comments do |t|
      t.text       :body, null: false
      t.references :article, null: false, foreign_key: true
      t.timestamps
    end
  end
end
```

Métodos generados:

```ruby
comment = Comment.first

comment.article          # Obtiene el artículo asociado
comment.article = article # Asigna un artículo
comment.build_article     # Construye un artículo sin guardar
comment.create_article    # Crea y guarda un artículo
```

> 💡 En Rails 8, `belongs_to` es obligatorio por defecto. Si quieres que sea opcional, debes especificar `belongs_to :article, optional: true`.

---

## has_many

Indica que un modelo tiene muchos registros de otro modelo:

```ruby
class Article < ApplicationRecord
  has_many :comments
end
```

Métodos generados:

```ruby
article = Article.first

article.comments              # Colección de comentarios
article.comments.count        # Cantidad de comentarios
article.comments.create(body: "Genial!") # Crear y asociar
article.comments.build(body: "Borrador") # Construir sin guardar
article.comments.where(approved: true)   # Filtrar
article.comments << comment              # Agregar a la colección
article.comments.empty?                  # ¿No tiene comentarios?
```

---

## has_one

Similar a `has_many` pero espera un solo registro relacionado:

```ruby
class User < ApplicationRecord
  has_one :profile
end

class Profile < ApplicationRecord
  belongs_to :user
end
```

```ruby
# Migración
class CreateProfiles < ActiveRecord::Migration[8.0]
  def change
    create_table :profiles do |t|
      t.string     :bio
      t.string     :avatar_url
      t.references :user, null: false, foreign_key: true
      t.timestamps
    end
  end
end
```

```ruby
user = User.first

user.profile                  # Obtiene el perfil
user.build_profile(bio: "Hola") # Construir sin guardar
user.create_profile(bio: "Hola") # Crear y guardar
```

---

## has_many :through

Crea una relación muchos-a-muchos a través de un modelo intermedio. Esta es la forma **recomendada** para relaciones muchos-a-muchos porque el modelo intermedio puede tener atributos propios:

```ruby
class Article < ApplicationRecord
  has_many :article_tags
  has_many :tags, through: :article_tags
end

class Tag < ApplicationRecord
  has_many :article_tags
  has_many :articles, through: :article_tags
end

class ArticleTag < ApplicationRecord
  belongs_to :article
  belongs_to :tag
end
```

```ruby
# Migraciones
class CreateTags < ActiveRecord::Migration[8.0]
  def change
    create_table :tags do |t|
      t.string :name, null: false
      t.timestamps
    end
  end
end

class CreateArticleTags < ActiveRecord::Migration[8.0]
  def change
    create_table :article_tags do |t|
      t.references :article, null: false, foreign_key: true
      t.references :tag,     null: false, foreign_key: true
      t.timestamps
    end

    add_index :article_tags, [:article_id, :tag_id], unique: true
  end
end
```

Uso:

```ruby
article = Article.first
tag = Tag.find_by(name: "Ruby")

# Agregar tags a un artículo
article.tags << tag
article.tags.create(name: "Rails")

# Obtener artículos de un tag
tag.articles

# Verificar si tiene un tag
article.tags.include?(tag)
article.tags.exists?(name: "Ruby")
```

---

## has_and_belongs_to_many (HABTM)

Relación muchos-a-muchos **sin modelo intermedio**. Es más simple pero menos flexible:

```ruby
class Article < ApplicationRecord
  has_and_belongs_to_many :categories
end

class Category < ApplicationRecord
  has_and_belongs_to_many :articles
end
```

```ruby
# La tabla intermedia debe llamarse articles_categories (orden alfabético)
class CreateArticlesCategories < ActiveRecord::Migration[8.0]
  def change
    create_join_table :articles, :categories do |t|
      t.index [:article_id, :category_id]
      t.index [:category_id, :article_id]
    end
  end
end
```

> 💡 Prefiere `has_many :through` sobre HABTM. Es más flexible y te permite agregar atributos a la tabla intermedia en el futuro.

---

## Asociaciones polimórficas

Permiten que un modelo pertenezca a más de un tipo de modelo usando una sola asociación:

```ruby
class Comment < ApplicationRecord
  belongs_to :commentable, polymorphic: true
end

class Article < ApplicationRecord
  has_many :comments, as: :commentable
end

class Video < ApplicationRecord
  has_many :comments, as: :commentable
end
```

```ruby
# Migración
class CreateComments < ActiveRecord::Migration[8.0]
  def change
    create_table :comments do |t|
      t.text    :body
      t.string  :commentable_type  # "Article" o "Video"
      t.bigint  :commentable_id    # ID del artículo o video
      t.timestamps
    end

    add_index :comments, [:commentable_type, :commentable_id]
  end
end

# O más conciso:
class CreateComments < ActiveRecord::Migration[8.0]
  def change
    create_table :comments do |t|
      t.text       :body
      t.references :commentable, polymorphic: true, null: false
      t.timestamps
    end
  end
end
```

```ruby
# Uso
article = Article.first
article.comments.create(body: "Comentario en artículo")

video = Video.first
video.comments.create(body: "Comentario en video")

comment = Comment.first
comment.commentable  # Retorna el Article o Video asociado
```

---

## inverse_of

Optimiza la carga de asociaciones bidireccionales, evitando consultas duplicadas:

```ruby
class Article < ApplicationRecord
  has_many :comments, inverse_of: :article
end

class Comment < ApplicationRecord
  belongs_to :article, inverse_of: :comments
end
```

```ruby
# Sin inverse_of:
article = Article.first
comment = article.comments.first
comment.article  # ¡Hace otra consulta a la BD!

# Con inverse_of:
article = Article.first
comment = article.comments.first
comment.article  # Usa el mismo objeto en memoria, sin consulta extra
```

> 💡 Rails infiere `inverse_of` automáticamente en la mayoría de los casos, pero es buena práctica declararlo explícitamente en asociaciones complejas.

---

## dependent: opciones de eliminación

Controla qué pasa con los registros asociados cuando se elimina el padre:

```ruby
class Article < ApplicationRecord
  # Elimina los comentarios cuando se elimina el artículo
  has_many :comments, dependent: :destroy

  # Otras opciones:
  # has_many :comments, dependent: :delete_all   # SQL DELETE directo (sin callbacks)
  # has_many :comments, dependent: :nullify       # Pone article_id = NULL
  # has_many :comments, dependent: :restrict_with_error # Impide eliminar si tiene hijos
  # has_many :comments, dependent: :restrict_with_exception # Lanza excepción
end
```

```ruby
article = Article.find(1)
article.comments.count  # => 5

article.destroy
# Con dependent: :destroy → elimina los 5 comentarios (ejecuta callbacks de cada uno)
# Con dependent: :delete_all → elimina los 5 con un solo DELETE SQL
# Con dependent: :nullify → los 5 comentarios quedan con article_id = NULL
```

---

## Eager Loading: evitar consultas N+1

El problema N+1 ocurre cuando cargas una colección y luego accedes a la asociación de cada elemento:

```ruby
# ❌ Problema N+1: 1 consulta para artículos + N consultas para comentarios
articles = Article.all
articles.each do |article|
  puts article.comments.count  # Una consulta por cada artículo
end
```

### includes

Carga las asociaciones por adelantado con una o dos consultas:

```ruby
# ✅ Carga artículos y comentarios en 2 consultas
articles = Article.includes(:comments).all

articles.each do |article|
  puts article.comments.count  # Sin consulta adicional
end
```

### preload

Fuerza la carga con consultas separadas:

```ruby
# Siempre hace 2 consultas separadas
articles = Article.preload(:comments).all
# SELECT * FROM articles
# SELECT * FROM comments WHERE article_id IN (1, 2, 3, ...)
```

### eager_load

Fuerza la carga con un solo LEFT JOIN:

```ruby
# Hace 1 consulta con LEFT JOIN
articles = Article.eager_load(:comments).all
# SELECT articles.*, comments.*
# FROM articles
# LEFT OUTER JOIN comments ON comments.article_id = articles.id
```

### ¿Cuándo usar cada uno?

```ruby
# includes — Rails decide automáticamente (recomendado en general)
Article.includes(:comments)

# preload — cuando NO necesitas filtrar por la asociación
Article.preload(:comments)

# eager_load — cuando necesitas filtrar con WHERE en la asociación
Article.eager_load(:comments).where(comments: { approved: true })

# Cargar múltiples asociaciones
Article.includes(:comments, :tags, :author)

# Cargar asociaciones anidadas
Article.includes(comments: :user)
```

---

## Scopes con asociaciones

```ruby
class Article < ApplicationRecord
  has_many :comments
  has_many :approved_comments, -> { where(approved: true) }, class_name: "Comment"
  has_many :recent_comments, -> { order(created_at: :desc).limit(5) }, class_name: "Comment"
end

# Uso
article.approved_comments
article.recent_comments
```

---

## Contador de caché

Evita contar registros cada vez con `counter_cache`:

```ruby
class Comment < ApplicationRecord
  belongs_to :article, counter_cache: true
end

# Requiere agregar la columna en articles:
class AddCommentsCountToArticles < ActiveRecord::Migration[8.0]
  def change
    add_column :articles, :comments_count, :integer, default: 0
  end
end
```

```ruby
# Ahora article.comments.count no hace consulta SQL
# Lee directamente de la columna comments_count
article.comments_count  # => 42
```

---

## Resumen

En esta lección aprendiste:

- Las asociaciones fundamentales: `belongs_to`, `has_many`, `has_one`
- Relaciones muchos-a-muchos con `has_many :through` y HABTM
- Asociaciones polimórficas para modelos flexibles
- Cómo `inverse_of` optimiza la memoria
- Las opciones de `dependent` para controlar eliminaciones en cascada
- Cómo evitar el problema N+1 con `includes`, `preload` y `eager_load`
- Scopes y contador de caché en asociaciones

En la siguiente lección aprenderemos sobre **validaciones y callbacks** para proteger la integridad de los datos.
