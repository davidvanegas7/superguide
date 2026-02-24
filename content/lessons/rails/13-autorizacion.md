# Autorización en Rails 8

La autorización determina **qué puede hacer** un usuario autenticado. Mientras que la autenticación verifica la identidad ("¿quién eres?"), la autorización controla los permisos ("¿qué puedes hacer?"). En este capítulo aprenderás a implementar autorización desde cero y con la gema Pundit.

---

## Autenticación vs Autorización

Es fundamental entender la diferencia:

| Concepto          | Pregunta que responde  | Ejemplo                             |
|-------------------|------------------------|--------------------------------------|
| **Autenticación** | ¿Quién eres?           | Login con email y contraseña         |
| **Autorización**  | ¿Qué puedes hacer?     | Solo admins pueden eliminar cursos   |

```ruby
# Autenticación: verificar identidad
user = User.authenticate_by(email_address: "ana@example.com", password: "secreto123")

# Autorización: verificar permisos
if user.admin?
  # Puede eliminar cursos
else
  # No tiene permiso
end
```

---

## Roles de Usuario con Enum

La forma más directa de implementar autorización es asignar roles a los usuarios.

### Migración

```bash
bin/rails generate migration AddRoleToUsers role:integer
```

```ruby
# db/migrate/XXXX_add_role_to_users.rb
class AddRoleToUsers < ActiveRecord::Migration[8.0]
  def change
    add_column :users, :role, :integer, default: 0, null: false
    add_index :users, :role
  end
end
```

```bash
bin/rails db:migrate
```

### Definir roles con enum

```ruby
# app/models/user.rb
class User < ApplicationRecord
  has_secure_password

  # Definir roles como enum
  enum :role, {
    student: 0,      # Rol por defecto
    instructor: 1,
    moderator: 2,
    admin: 3
  }

  # Métodos de conveniencia generados automáticamente:
  # user.student?      → true/false
  # user.admin?        → true/false
  # user.instructor!   → cambia el rol a instructor
  # User.admin         → scope que filtra admins
  # User.roles         → { "student" => 0, "instructor" => 1, ... }
end
```

```ruby
# Uso en la consola
user = User.first
user.student?       # => true
user.admin!         # Cambia el rol a admin
user.role            # => "admin"

# Buscar por rol
User.admin           # Todos los admins
User.instructor      # Todos los instructores
```

---

## Autorización con `before_action`

La forma más simple de autorizar es usar `before_action` en los controladores.

### Verificaciones básicas

```ruby
# app/controllers/application_controller.rb
class ApplicationController < ActionController::Base
  include Authentication

  private

  def require_admin
    unless Current.user&.admin?
      redirect_to root_path, alert: "No tienes permiso para acceder a esta sección."
    end
  end

  def require_instructor_or_admin
    unless Current.user&.instructor? || Current.user&.admin?
      redirect_to root_path, alert: "Acceso restringido a instructores."
    end
  end

  def require_owner_or_admin(record)
    unless record.user == Current.user || Current.user&.admin?
      redirect_to root_path, alert: "No tienes permiso para realizar esta acción."
    end
  end
end
```

### Aplicar en controladores

```ruby
# app/controllers/admin/dashboard_controller.rb
module Admin
  class DashboardController < ApplicationController
    before_action :require_admin

    def index
      @users_count = User.count
      @courses_count = Course.count
      @recent_users = User.order(created_at: :desc).limit(10)
    end
  end
end
```

```ruby
# app/controllers/courses_controller.rb
class CoursesController < ApplicationController
  allow_unauthenticated_access only: [:index, :show]
  before_action :require_instructor_or_admin, only: [:new, :create]
  before_action :set_course, only: [:show, :edit, :update, :destroy]
  before_action :authorize_course_owner, only: [:edit, :update, :destroy]

  def index
    @courses = Course.published
  end

  def show
  end

  def new
    @course = Course.new
  end

  def create
    @course = Current.user.courses.build(course_params)

    if @course.save
      redirect_to @course, notice: "Curso creado exitosamente."
    else
      render :new, status: :unprocessable_entity
    end
  end

  def edit
  end

  def update
    if @course.update(course_params)
      redirect_to @course, notice: "Curso actualizado."
    else
      render :edit, status: :unprocessable_entity
    end
  end

  def destroy
    @course.destroy
    redirect_to courses_path, notice: "Curso eliminado."
  end

  private

  def set_course
    @course = Course.find(params[:id])
  end

  def authorize_course_owner
    unless @course.user == Current.user || Current.user.admin?
      redirect_to courses_path, alert: "No puedes modificar este curso."
    end
  end

  def course_params
    params.require(:course).permit(:name, :description, :level, :published)
  end
end
```

### Autorización en las vistas

```ruby
# app/views/courses/show.html.erb
<h1><%= @course.name %></h1>
<p><%= @course.description %></p>

<% if Current.user&.admin? || @course.user == Current.user %>
  <div class="actions">
    <%= link_to "Editar", edit_course_path(@course), class: "btn" %>
    <%= button_to "Eliminar", course_path(@course),
        method: :delete,
        class: "btn btn-danger",
        data: { turbo_confirm: "¿Estás seguro?" } %>
  </div>
<% end %>

<% if Current.user&.admin? %>
  <div class="admin-panel">
    <h3>Panel de Administrador</h3>
    <p>Creado por: <%= @course.user.name %></p>
    <p>Fecha: <%= l @course.created_at, format: :long %></p>
  </div>
<% end %>
```

---

## Controller Concerns para Autorización

Cuando la lógica de autorización se repite en varios controladores, usa concerns:

```ruby
# app/controllers/concerns/authorizable.rb
module Authorizable
  extend ActiveSupport::Concern

  class NotAuthorizedError < StandardError; end

  included do
    rescue_from NotAuthorizedError, with: :handle_unauthorized
  end

  private

  def authorize!(action, record = nil)
    unless can?(action, record)
      raise NotAuthorizedError, "No autorizado para #{action}"
    end
  end

  def can?(action, record = nil)
    case action
    when :manage_courses
      Current.user&.instructor? || Current.user&.admin?
    when :edit_course
      record&.user == Current.user || Current.user&.admin?
    when :delete_course
      Current.user&.admin?
    when :manage_users
      Current.user&.admin?
    when :view_admin
      Current.user&.admin? || Current.user&.moderator?
    else
      false
    end
  end
  helper_method :can?

  def handle_unauthorized
    respond_to do |format|
      format.html { redirect_to root_path, alert: "No tienes permiso para realizar esta acción." }
      format.turbo_stream { head :forbidden }
      format.json { render json: { error: "No autorizado" }, status: :forbidden }
    end
  end
end
```

```ruby
# app/controllers/application_controller.rb
class ApplicationController < ActionController::Base
  include Authentication
  include Authorizable
end
```

```ruby
# Uso en controladores
class CoursesController < ApplicationController
  def create
    authorize! :manage_courses
    @course = Current.user.courses.build(course_params)
    # ...
  end

  def destroy
    @course = Course.find(params[:id])
    authorize! :delete_course, @course
    @course.destroy
    # ...
  end
end

# Uso en vistas con el helper can?
<% if can?(:edit_course, @course) %>
  <%= link_to "Editar", edit_course_path(@course) %>
<% end %>
```

---

## Pundit: Autorización con Policies

Pundit es la gema más popular para autorización en Rails. Organiza las reglas en clases **Policy**, una por modelo.

### Instalación

```bash
bundle add pundit
```

```ruby
# app/controllers/application_controller.rb
class ApplicationController < ActionController::Base
  include Authentication
  include Pundit::Authorization

  # Lanzar error si olvidamos autorizar en alguna acción
  after_action :verify_authorized, except: :index
  after_action :verify_policy_scoped, only: :index

  rescue_from Pundit::NotAuthorizedError, with: :user_not_authorized

  private

  # Pundit necesita un método current_user (o pundit_user)
  def pundit_user
    Current.user
  end

  def user_not_authorized
    redirect_to root_path, alert: "No tienes permiso para realizar esta acción."
  end
end
```

### Generar una policy

```bash
bin/rails generate pundit:install  # Crea ApplicationPolicy base
bin/rails generate pundit:policy Course
# Crea: app/policies/course_policy.rb
```

### Definir la policy

```ruby
# app/policies/application_policy.rb
class ApplicationPolicy
  attr_reader :user, :record

  def initialize(user, record)
    @user = user
    @record = record
  end

  def index?
    true
  end

  def show?
    true
  end

  def create?
    false
  end

  def new?
    create?
  end

  def update?
    false
  end

  def edit?
    update?
  end

  def destroy?
    false
  end

  # Scope base para consultas
  class Scope
    def initialize(user, scope)
      @user = user
      @scope = scope
    end

    def resolve
      raise NoMethodError, "Debes definir #resolve en #{self.class}"
    end

    private

    attr_reader :user, :scope
  end
end
```

```ruby
# app/policies/course_policy.rb
class CoursePolicy < ApplicationPolicy
  def index?
    true  # Cualquiera puede ver la lista
  end

  def show?
    # Cursos publicados son visibles para todos
    # Borradores solo para el autor y admins
    record.published? || owner_or_admin?
  end

  def create?
    user&.instructor? || user&.admin?
  end

  def update?
    owner_or_admin?
  end

  def destroy?
    user&.admin?
  end

  def publish?
    owner_or_admin?
  end

  private

  def owner_or_admin?
    user&.admin? || record.user == user
  end

  # Scope: qué registros puede ver cada rol
  class Scope < ApplicationPolicy::Scope
    def resolve
      if user&.admin?
        scope.all
      elsif user&.instructor?
        scope.where(published: true).or(scope.where(user: user))
      else
        scope.where(published: true)
      end
    end
  end
end
```

### Usar en controladores

```ruby
# app/controllers/courses_controller.rb
class CoursesController < ApplicationController
  allow_unauthenticated_access only: [:index, :show]

  def index
    @courses = policy_scope(Course).order(created_at: :desc)
  end

  def show
    @course = Course.find(params[:id])
    authorize @course
  end

  def new
    @course = Course.new
    authorize @course
  end

  def create
    @course = Current.user.courses.build(course_params)
    authorize @course

    if @course.save
      redirect_to @course, notice: "Curso creado."
    else
      render :new, status: :unprocessable_entity
    end
  end

  def update
    @course = Course.find(params[:id])
    authorize @course

    if @course.update(course_params)
      redirect_to @course, notice: "Curso actualizado."
    else
      render :edit, status: :unprocessable_entity
    end
  end

  def destroy
    @course = Course.find(params[:id])
    authorize @course

    @course.destroy
    redirect_to courses_path, notice: "Curso eliminado."
  end

  def publish
    @course = Course.find(params[:id])
    authorize @course

    @course.update!(published: true)
    redirect_to @course, notice: "Curso publicado."
  end
end
```

### Usar en vistas

```ruby
# app/views/courses/show.html.erb
<h1><%= @course.name %></h1>

<% if policy(@course).update? %>
  <%= link_to "Editar", edit_course_path(@course), class: "btn" %>
<% end %>

<% if policy(@course).destroy? %>
  <%= button_to "Eliminar", course_path(@course),
      method: :delete, class: "btn btn-danger",
      data: { turbo_confirm: "¿Estás seguro?" } %>
<% end %>

<% if policy(@course).publish? && !@course.published? %>
  <%= button_to "Publicar", publish_course_path(@course),
      method: :patch, class: "btn btn-success" %>
<% end %>
```

---

## Policy para Usuarios

```ruby
# app/policies/user_policy.rb
class UserPolicy < ApplicationPolicy
  def index?
    user&.admin? || user&.moderator?
  end

  def show?
    user&.admin? || user&.moderator? || record == user
  end

  def update?
    user&.admin? || record == user
  end

  def destroy?
    user&.admin? && record != user  # Un admin no puede eliminarse a sí mismo
  end

  def change_role?
    user&.admin? && record != user
  end

  class Scope < ApplicationPolicy::Scope
    def resolve
      if user&.admin?
        scope.all
      else
        scope.where(id: user.id)
      end
    end
  end
end
```

---

## Testear Policies

Pundit facilita el testeo de las reglas de autorización:

```ruby
# test/policies/course_policy_test.rb
require "test_helper"

class CoursePolicyTest < ActiveSupport::TestCase
  setup do
    @admin = users(:admin)
    @instructor = users(:instructor)
    @student = users(:student)
    @course = courses(:rails_basics)
  end

  test "cualquiera puede ver cursos publicados" do
    @course.update!(published: true)

    assert CoursePolicy.new(@student, @course).show?
    assert CoursePolicy.new(@instructor, @course).show?
    assert CoursePolicy.new(nil, @course).show?
  end

  test "solo el autor y admins ven borradores" do
    @course.update!(published: false, user: @instructor)

    assert CoursePolicy.new(@instructor, @course).show?
    assert CoursePolicy.new(@admin, @course).show?
    refute CoursePolicy.new(@student, @course).show?
  end

  test "solo instructores y admins pueden crear cursos" do
    assert CoursePolicy.new(@instructor, Course.new).create?
    assert CoursePolicy.new(@admin, Course.new).create?
    refute CoursePolicy.new(@student, Course.new).create?
  end

  test "solo admins pueden eliminar cursos" do
    assert CoursePolicy.new(@admin, @course).destroy?
    refute CoursePolicy.new(@instructor, @course).destroy?
    refute CoursePolicy.new(@student, @course).destroy?
  end

  test "scope filtra cursos según el rol" do
    scope = CoursePolicy::Scope.new(@student, Course).resolve
    assert scope.where(published: false).empty?

    admin_scope = CoursePolicy::Scope.new(@admin, Course).resolve
    assert_equal Course.count, admin_scope.count
  end
end
```

---

## Autorización Personalizada sin Gemas

Si prefieres no usar Pundit, puedes implementar un patrón similar manualmente:

```ruby
# app/policies/base_policy.rb
class BasePolicy
  attr_reader :user, :record

  def initialize(user, record)
    @user = user
    @record = record
  end

  def admin?
    user&.admin?
  end

  def owner?
    record.respond_to?(:user) && record.user == user
  end

  def owner_or_admin?
    owner? || admin?
  end
end
```

```ruby
# app/policies/lesson_policy.rb
class LessonPolicy < BasePolicy
  def create?
    user&.instructor? || admin?
  end

  def update?
    owner_or_admin?
  end

  def destroy?
    admin?
  end
end
```

```ruby
# app/controllers/concerns/policy_enforcement.rb
module PolicyEnforcement
  extend ActiveSupport::Concern

  class NotAuthorizedError < StandardError; end

  included do
    rescue_from NotAuthorizedError, with: -> {
      redirect_to root_path, alert: "No autorizado."
    }
  end

  def authorize(record, action)
    policy_class = "#{record.class}Policy".constantize
    policy = policy_class.new(Current.user, record)

    unless policy.public_send("#{action}?")
      raise NotAuthorizedError
    end
  end

  def policy(record)
    policy_class = "#{record.class}Policy".constantize
    policy_class.new(Current.user, record)
  end
  helper_method :policy
end
```

---

## Consejos Prácticos

1. **Separa autenticación de autorización**: son responsabilidades distintas.
2. **Usa enums para roles simples**: suficiente para la mayoría de aplicaciones.
3. **Centraliza la lógica**: con Pundit o concerns, evita duplicar reglas.
4. **Testea las policies**: son lógica de negocio crítica, deben tener buena cobertura.
5. **Revisa las vistas**: no muestres botones o enlaces que el usuario no puede usar.
6. **Principio de menor privilegio**: da a cada rol solo los permisos mínimos necesarios.
7. **Audita acciones sensibles**: registra quién hizo qué y cuándo.

```ruby
# Ejemplo: auditar cambios de rol
class User < ApplicationRecord
  after_update :log_role_change, if: :saved_change_to_role?

  private

  def log_role_change
    Rails.logger.info(
      "AUDIT: Usuario #{id} (#{email_address}) cambió de rol " \
      "#{role_before_last_save} → #{role} " \
      "por #{Current.user&.email_address || 'sistema'}"
    )
  end
end
```

---

## Resumen

La autorización es esencial para controlar qué puede hacer cada usuario en tu aplicación:

- **Autenticación ≠ Autorización**: identidad vs permisos, son complementarias pero distintas.
- **Roles con enum** proporcionan una forma simple y eficiente de categorizar usuarios.
- **`before_action`** permite verificaciones rápidas en controladores.
- **Controller concerns** centralizan lógica de autorización reutilizable.
- **Pundit** organiza las reglas en policies por modelo, con scopes para consultas filtradas.
- **Testear policies** es fundamental para garantizar que los permisos funcionan correctamente.

Elige el enfoque adecuado según la complejidad de tu aplicación: `before_action` para proyectos simples, Pundit para aplicaciones con reglas de negocio complejas.
