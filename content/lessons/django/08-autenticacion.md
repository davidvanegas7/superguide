---
title: "Autenticación y Autorización"
slug: "django-autenticacion"
description: "Implementa login, logout, registro, permisos, grupos y un modelo de usuario personalizado en Django."
---

# Autenticación y Autorización

Django incluye un sistema de autenticación completo y robusto que maneja cuentas de usuario, grupos, permisos y sesiones basadas en cookies. Este sistema se ocupa tanto de la **autenticación** (verificar quién es el usuario) como de la **autorización** (determinar qué puede hacer).

## El modelo User por defecto

Django proporciona un modelo `User` integrado con los siguientes campos:

```python
from django.contrib.auth.models import User

# Campos principales del User:
# username, password, email, first_name, last_name
# is_active, is_staff, is_superuser
# date_joined, last_login
```

### Crear usuarios

```python
from django.contrib.auth.models import User

# Crear usuario normal
usuario = User.objects.create_user(
    username='maria',
    email='maria@ejemplo.com',
    password='contraseña_segura123',
    first_name='María',
    last_name='García',
)

# Crear superusuario
admin = User.objects.create_superuser(
    username='admin',
    email='admin@ejemplo.com',
    password='admin_seguro123',
)

# Cambiar contraseña
usuario.set_password('nueva_contraseña')
usuario.save()

# Verificar contraseña
usuario.check_password('nueva_contraseña')  # True
```

## Vistas de login y logout

Django incluye vistas predefinidas para autenticación:

```python
# mi_proyecto/urls.py
from django.contrib import admin
from django.urls import path, include

urlpatterns = [
    path('admin/', admin.site.urls),
    path('cuentas/', include('django.contrib.auth.urls')),
    # Incluye automáticamente:
    # cuentas/login/
    # cuentas/logout/
    # cuentas/password_change/
    # cuentas/password_change/done/
    # cuentas/password_reset/
    # cuentas/password_reset/done/
    # cuentas/reset/<uidb64>/<token>/
    # cuentas/reset/done/
]
```

### Template de login

```html
<!-- templates/registration/login.html -->
{% extends "base.html" %}

{% block contenido %}
<div class="login-container">
    <h2>Iniciar Sesión</h2>

    {% if form.errors %}
    <div class="alert alert-danger">
        <p>Usuario o contraseña incorrectos. Inténtalo de nuevo.</p>
    </div>
    {% endif %}

    <form method="post">
        {% csrf_token %}

        <div class="form-group">
            <label for="id_username">Usuario:</label>
            {{ form.username }}
        </div>

        <div class="form-group">
            <label for="id_password">Contraseña:</label>
            {{ form.password }}
        </div>

        <button type="submit">Entrar</button>
        <input type="hidden" name="next" value="{{ next }}">
    </form>

    <p>
        <a href="{% url 'password_reset' %}">¿Olvidaste tu contraseña?</a>
    </p>
</div>
{% endblock %}
```

### Configurar redirecciones

```python
# settings.py
LOGIN_URL = '/cuentas/login/'                  # URL de login
LOGIN_REDIRECT_URL = '/'                       # Después de login exitoso
LOGOUT_REDIRECT_URL = '/'                      # Después de logout
```

## Vista de registro personalizada

Django no incluye una vista de registro, pero es fácil crearla:

```python
# cuentas/forms.py
from django import forms
from django.contrib.auth.models import User
from django.contrib.auth.forms import UserCreationForm

class RegistroForm(UserCreationForm):
    email = forms.EmailField(required=True)
    first_name = forms.CharField(max_length=50, label='Nombre')
    last_name = forms.CharField(max_length=50, label='Apellido')

    class Meta:
        model = User
        fields = [
            'username', 'email', 'first_name',
            'last_name', 'password1', 'password2',
        ]

    def clean_email(self):
        email = self.cleaned_data['email']
        if User.objects.filter(email=email).exists():
            raise forms.ValidationError('Este correo ya está registrado.')
        return email
```

```python
# cuentas/views.py
from django.shortcuts import render, redirect
from django.contrib.auth import login
from django.contrib import messages
from .forms import RegistroForm

def registro(request):
    if request.method == 'POST':
        form = RegistroForm(request.POST)
        if form.is_valid():
            usuario = form.save()
            login(request, usuario)  # Iniciar sesión automáticamente
            messages.success(request, '¡Cuenta creada exitosamente!')
            return redirect('inicio')
    else:
        form = RegistroForm()

    return render(request, 'registration/registro.html', {'form': form})
```

## Autenticación manual en vistas

```python
from django.contrib.auth import authenticate, login, logout

def mi_login(request):
    if request.method == 'POST':
        username = request.POST['username']
        password = request.POST['password']

        usuario = authenticate(request, username=username, password=password)

        if usuario is not None:
            if usuario.is_active:
                login(request, usuario)
                # Redirigir a la página solicitada o al inicio
                siguiente = request.GET.get('next', '/')
                return redirect(siguiente)
            else:
                messages.error(request, 'Tu cuenta está desactivada.')
        else:
            messages.error(request, 'Credenciales inválidas.')

    return render(request, 'registration/login.html')


def mi_logout(request):
    logout(request)
    messages.info(request, 'Has cerrado sesión.')
    return redirect('inicio')
```

## @login_required

El decorador `@login_required` protege vistas que requieren autenticación:

```python
from django.contrib.auth.decorators import login_required

@login_required
def mi_perfil(request):
    """Solo usuarios autenticados pueden ver su perfil."""
    return render(request, 'cuentas/perfil.html', {
        'usuario': request.user,
    })

@login_required(login_url='/cuentas/login/')
def panel_control(request):
    """Redirige a una URL de login personalizada."""
    return render(request, 'cuentas/panel.html')
```

Para vistas basadas en clases, usa el mixin `LoginRequiredMixin`:

```python
from django.contrib.auth.mixins import LoginRequiredMixin
from django.views.generic import ListView

class MisArticulosView(LoginRequiredMixin, ListView):
    model = Articulo
    template_name = 'blog/mis_articulos.html'
    login_url = '/cuentas/login/'

    def get_queryset(self):
        return Articulo.objects.filter(autor=self.request.user)
```

## Permisos

Django genera automáticamente permisos CRUD para cada modelo: `add_`, `change_`, `delete_`, `view_`.

```python
# Verificar permisos en vistas
from django.contrib.auth.decorators import permission_required

@permission_required('blog.add_articulo', raise_exception=True)
def crear_articulo(request):
    pass

@permission_required('blog.change_articulo')
def editar_articulo(request, pk):
    pass

# Verificar permisos manualmente
if request.user.has_perm('blog.delete_articulo'):
    # Puede eliminar
    pass

# Verificar en templates
{% if perms.blog.add_articulo %}
    <a href="{% url 'blog:crear' %}">Nuevo artículo</a>
{% endif %}
```

### Permisos personalizados

```python
class Articulo(models.Model):
    titulo = models.CharField(max_length=200)
    # ...

    class Meta:
        permissions = [
            ('publicar_articulo', 'Puede publicar artículos'),
            ('destacar_articulo', 'Puede destacar artículos'),
        ]
```

```python
# Para CBV
from django.contrib.auth.mixins import PermissionRequiredMixin

class PublicarArticuloView(PermissionRequiredMixin, UpdateView):
    permission_required = 'blog.publicar_articulo'
    model = Articulo
    fields = ['publicado']
```

## Grupos

Los grupos agrupan permisos para asignarlos a múltiples usuarios:

```python
from django.contrib.auth.models import Group, Permission

# Crear grupo
editores = Group.objects.create(name='Editores')

# Agregar permisos al grupo
permiso_cambiar = Permission.objects.get(codename='change_articulo')
permiso_publicar = Permission.objects.get(codename='publicar_articulo')
editores.permissions.add(permiso_cambiar, permiso_publicar)

# Agregar usuario al grupo
usuario.groups.add(editores)

# Verificar grupo
if usuario.groups.filter(name='Editores').exists():
    print("Es editor")
```

## Modelo de usuario personalizado (AbstractUser)

Es una **práctica recomendada** crear un modelo de usuario personalizado al inicio del proyecto, incluso si no necesitas campos adicionales:

```python
# cuentas/models.py
from django.contrib.auth.models import AbstractUser
from django.db import models

class Usuario(AbstractUser):
    bio = models.TextField(blank=True, verbose_name='Biografía')
    avatar = models.ImageField(
        upload_to='avatares/',
        blank=True,
        null=True,
    )
    fecha_nacimiento = models.DateField(
        null=True,
        blank=True,
        verbose_name='Fecha de nacimiento',
    )
    telefono = models.CharField(max_length=20, blank=True)
    sitio_web = models.URLField(blank=True)

    class Meta:
        verbose_name = 'usuario'
        verbose_name_plural = 'usuarios'

    def __str__(self):
        return self.get_full_name() or self.username

    def get_nombre_corto(self):
        return self.first_name or self.username
```

### Configurar el modelo personalizado

```python
# settings.py
AUTH_USER_MODEL = 'cuentas.Usuario'
```

**Importante**: Define `AUTH_USER_MODEL` antes de ejecutar la primera migración. Cambiarlo después es complejo.

```python
# cuentas/admin.py
from django.contrib import admin
from django.contrib.auth.admin import UserAdmin
from .models import Usuario

@admin.register(Usuario)
class UsuarioAdmin(UserAdmin):
    fieldsets = UserAdmin.fieldsets + (
        ('Información adicional', {
            'fields': ('bio', 'avatar', 'fecha_nacimiento', 'telefono', 'sitio_web'),
        }),
    )
    list_display = ['username', 'email', 'first_name', 'is_active', 'date_joined']
```

### Referenciar el usuario en otros modelos

```python
from django.conf import settings

class Articulo(models.Model):
    autor = models.ForeignKey(
        settings.AUTH_USER_MODEL,  # Siempre usa esto, nunca User directamente
        on_delete=models.CASCADE,
    )
```

## Ejercicio Práctico

1. Crea un modelo de usuario personalizado (`CustomUser`) que extienda `AbstractUser` con campos: bio, avatar, ciudad y fecha de nacimiento.
2. Configura `AUTH_USER_MODEL` en settings.
3. Implementa vistas de registro, login y logout.
4. Crea una vista de perfil protegida con `@login_required`.
5. Define un permiso personalizado `puede_moderar` y un grupo `Moderadores`.
6. Agrega un template que muestre opciones diferentes según los permisos del usuario.

## Resumen

Django ofrece un sistema de autenticación y autorización completo. Aprendiste a usar el modelo User por defecto, a implementar login/logout con vistas integradas, a proteger vistas con `@login_required`, a gestionar permisos y grupos, y a crear un modelo de usuario personalizado con `AbstractUser`. Este sistema te da las herramientas necesarias para controlar el acceso a tu aplicación de manera segura y flexible.
