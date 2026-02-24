# Introducción a Ruby on Rails 8

Ruby on Rails (o simplemente Rails) es un framework de desarrollo web escrito en Ruby que te permite crear aplicaciones web completas de forma rápida y elegante. Fue creado por David Heinemeier Hansson (DHH) en 2004 y desde entonces ha impulsado aplicaciones como GitHub, Shopify, Basecamp y Airbnb.

---

## ¿Por qué aprender Rails?

- **Productividad extrema**: Rails genera código por ti y sigue convenciones que eliminan decisiones repetitivas.
- **Ecosistema maduro**: miles de gemas (librerías) disponibles para cualquier necesidad.
- **Comunidad activa**: documentación abundante, conferencias y soporte constante.
- **Full-stack**: maneja desde la base de datos hasta la interfaz de usuario en un solo framework.
- **Rails 8** trae mejoras significativas en rendimiento, seguridad y herramientas para desarrollo moderno.

---

## Filosofía de Rails

Rails se basa en tres principios fundamentales:

### Convention over Configuration (CoC)

Rails toma decisiones por ti. Si sigues las convenciones, no necesitas configurar casi nada:

```ruby
# Si tu modelo se llama Article, Rails espera:
# - Tabla en la base de datos: articles
# - Archivo del modelo: app/models/article.rb
# - Controlador: app/controllers/articles_controller.rb
# - Vistas en: app/views/articles/
```

### DRY — Don't Repeat Yourself

Cada pieza de conocimiento debe tener una única representación en el sistema. Rails te anima a reutilizar código con helpers, partials, concerns y herencia.

### MVC — Model-View-Controller

Rails organiza tu código en tres capas:

| Capa        | Responsabilidad                       | Directorio          |
|-------------|---------------------------------------|----------------------|
| **Model**   | Datos y lógica de negocio             | `app/models/`       |
| **View**    | Presentación al usuario               | `app/views/`        |
| **Controller** | Coordina modelos y vistas          | `app/controllers/`  |

---

## Requisitos previos

Antes de instalar Rails necesitas:

- **Ruby** 3.2 o superior (Rails 8 requiere Ruby moderno)
- **Bundler** (gestor de gemas)
- **Node.js** y **Yarn** (para assets, aunque Rails 8 puede prescindir con Import Maps)
- **SQLite3** (base de datos por defecto) o PostgreSQL / MySQL

```bash
# Verificar versiones instaladas
ruby -v        # ruby 3.3.0 o superior
gem -v         # gestor de gemas
node -v        # v18+ recomendado
```

---

## Instalación de Rails 8

```bash
# Instalar la última versión de Rails
gem install rails

# Verificar la versión
rails -v
# Rails 8.0.0
```

> 💡 Si usas `rbenv` o `rvm` para gestionar versiones de Ruby, asegúrate de tener la versión correcta activa antes de instalar Rails.

---

## Crear tu primer proyecto

```bash
# Crear un nuevo proyecto con la configuración por defecto
rails new mi_app

# Crear proyecto con PostgreSQL en lugar de SQLite
rails new mi_app --database=postgresql

# Crear proyecto sin assets de JavaScript (usando Import Maps)
rails new mi_app --skip-javascript

# Crear proyecto como API (sin vistas)
rails new mi_app --api
```

Rails generará toda la estructura de archivos y ejecutará `bundle install` automáticamente.

---

## Estructura de directorios

Al crear un proyecto, Rails genera esta estructura:

```
mi_app/
├── app/                    # Código principal de la aplicación
│   ├── controllers/        # Controladores
│   ├── models/             # Modelos (Active Record)
│   ├── views/              # Vistas (ERB, HTML)
│   ├── helpers/            # Métodos auxiliares para vistas
│   ├── jobs/               # Tareas en segundo plano
│   ├── mailers/            # Envío de correos
│   └── channels/           # WebSockets (Action Cable)
├── bin/                    # Scripts ejecutables (rails, rake, etc.)
├── config/                 # Configuración de la aplicación
│   ├── routes.rb           # Definición de rutas
│   ├── database.yml        # Configuración de base de datos
│   └── environments/       # Configuración por entorno
├── db/                     # Migraciones y esquema de base de datos
│   ├── migrate/            # Archivos de migración
│   ├── schema.rb           # Esquema actual
│   └── seeds.rb            # Datos iniciales
├── lib/                    # Código reutilizable propio
├── log/                    # Archivos de log
├── public/                 # Archivos estáticos públicos
├── test/                   # Tests (Minitest por defecto)
├── storage/                # Archivos subidos (Active Storage)
├── Gemfile                 # Dependencias del proyecto
└── Gemfile.lock            # Versiones exactas de gemas
```

---

## Tu primer "Hello World"

Vamos a crear una página que muestre un saludo:

### Paso 1: Generar un controlador

```bash
rails generate controller Pages home
# Atajo: rails g controller Pages home
```

Esto crea:

- `app/controllers/pages_controller.rb`
- `app/views/pages/home.html.erb`
- Una ruta en `config/routes.rb`

### Paso 2: Editar el controlador

```ruby
# app/controllers/pages_controller.rb
class PagesController < ApplicationController
  def home
    @mensaje = "¡Hola, mundo desde Rails 8!"
  end
end
```

### Paso 3: Editar la vista

```erb
<!-- app/views/pages/home.html.erb -->
<h1><%= @mensaje %></h1>
<p>Bienvenido a tu primera aplicación Rails.</p>
```

### Paso 4: Configurar la ruta raíz

```ruby
# config/routes.rb
Rails.application.routes.draw do
  root "pages#home"
end
```

---

## Iniciar el servidor

```bash
# Iniciar el servidor de desarrollo
rails server
# Atajo: rails s

# Por defecto escucha en http://localhost:3000
# Para cambiar el puerto:
rails s -p 4000

# Para permitir conexiones externas:
rails s -b 0.0.0.0
```

Abre tu navegador en `http://localhost:3000` y verás tu mensaje de bienvenida.

---

## Generadores básicos

Rails incluye generadores que crean archivos con una estructura predefinida:

```bash
# Generar un modelo
rails g model Article title:string body:text published:boolean

# Generar un controlador con acciones
rails g controller Articles index show new create

# Generar un scaffold completo (modelo + controlador + vistas + rutas)
rails g scaffold Product name:string price:decimal description:text

# Generar una migración
rails g migration AddCategoryToArticles category:string

# Ver todos los generadores disponibles
rails g --help
```

> 💡 Los scaffolds son excelentes para prototipos rápidos, pero en producción es mejor generar cada componente por separado para tener más control.

---

## La consola de Rails

La consola interactiva te permite experimentar con tu aplicación:

```bash
# Abrir la consola
rails console
# Atajo: rails c

# Dentro de la consola puedes ejecutar cualquier código Ruby:
# > Article.count
# > Article.create(title: "Mi primer artículo", body: "Contenido aquí")
# > Article.first
```

---

## Comandos esenciales de Rails

| Comando                  | Descripción                              |
|--------------------------|------------------------------------------|
| `rails new nombre`       | Crear nuevo proyecto                     |
| `rails s`                | Iniciar servidor                         |
| `rails c`                | Consola interactiva                      |
| `rails g model ...`      | Generar modelo                           |
| `rails g controller ...` | Generar controlador                      |
| `rails db:migrate`       | Ejecutar migraciones pendientes          |
| `rails db:seed`          | Poblar base de datos con datos iniciales |
| `rails routes`           | Ver todas las rutas definidas            |
| `rails test`             | Ejecutar tests                           |

---

## Resumen

En esta lección aprendiste:

- Qué es Ruby on Rails y por qué es tan productivo
- Los principios fundamentales: CoC, DRY y MVC
- Cómo instalar Rails 8 y crear tu primer proyecto
- La estructura de directorios de una aplicación Rails
- Cómo crear tu primer "Hello World" con controlador, vista y ruta
- Los generadores y comandos básicos que usarás a diario

En la siguiente lección profundizaremos en la **arquitectura MVC** y cómo Rails organiza el flujo de una petición HTTP.
