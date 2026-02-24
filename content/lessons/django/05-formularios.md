---
title: "Formularios"
slug: "django-formularios"
description: "Crea y valida formularios con Form, ModelForm, widgets, CSRF y formsets en Django."
---

# Formularios

Los formularios son esenciales en cualquier aplicación web. Django proporciona un sistema robusto para crear, renderizar y validar formularios de manera segura. El framework se encarga de la generación de HTML, la validación de datos y la protección contra ataques CSRF.

## La clase Form

La forma más básica de crear formularios en Django es usando la clase `Form`:

```python
# blog/forms.py
from django import forms

class ContactoForm(forms.Form):
    nombre = forms.CharField(
        max_length=100,
        label='Tu nombre',
        help_text='Ingresa tu nombre completo'
    )
    email = forms.EmailField(
        label='Correo electrónico'
    )
    asunto = forms.CharField(max_length=200)
    mensaje = forms.CharField(
        widget=forms.Textarea(attrs={'rows': 5}),
        label='Tu mensaje'
    )
    prioridad = forms.ChoiceField(
        choices=[
            ('baja', 'Baja'),
            ('media', 'Media'),
            ('alta', 'Alta'),
        ],
        initial='media'
    )
    acepta_terminos = forms.BooleanField(
        required=True,
        label='Acepto los términos y condiciones'
    )
```

### Tipos de campos comunes

```python
# Texto
forms.CharField(max_length=100)
forms.CharField(widget=forms.Textarea)
forms.EmailField()
forms.URLField()
forms.SlugField()

# Números
forms.IntegerField(min_value=0, max_value=100)
forms.FloatField()
forms.DecimalField(max_digits=10, decimal_places=2)

# Booleanos y selección
forms.BooleanField()
forms.ChoiceField(choices=[...])
forms.MultipleChoiceField(choices=[...])
forms.TypedChoiceField(choices=[...], coerce=int)

# Fecha y hora
forms.DateField(widget=forms.DateInput(attrs={'type': 'date'}))
forms.DateTimeField()
forms.TimeField()

# Archivos
forms.FileField()
forms.ImageField()
```

## Procesamiento en la vista

```python
# blog/views.py
from django.shortcuts import render, redirect
from django.contrib import messages
from .forms import ContactoForm

def contacto(request):
    if request.method == 'POST':
        form = ContactoForm(request.POST)
        if form.is_valid():
            # Acceder a los datos validados
            nombre = form.cleaned_data['nombre']
            email = form.cleaned_data['email']
            mensaje = form.cleaned_data['mensaje']

            # Procesar los datos (enviar email, guardar en BD, etc.)
            enviar_email_contacto(nombre, email, mensaje)

            messages.success(request, '¡Mensaje enviado correctamente!')
            return redirect('contacto_exito')
    else:
        form = ContactoForm()

    return render(request, 'blog/contacto.html', {'form': form})
```

## Renderización en templates

Django ofrece varias formas de renderizar formularios:

```html
<!-- blog/templates/blog/contacto.html -->
{% extends "base.html" %}

{% block contenido %}
<h1>Contacto</h1>

<form method="post" novalidate>
    {% csrf_token %}

    <!-- Opción 1: Renderizar todo el formulario automáticamente -->
    {{ form.as_p }}

    <!-- Opción 2: Como tabla -->
    <table>{{ form.as_table }}</table>

    <!-- Opción 3: Como lista -->
    <ul>{{ form.as_ul }}</ul>

    <!-- Opción 4: Renderizado manual (mayor control) -->
    {% for field in form %}
    <div class="form-group {% if field.errors %}has-error{% endif %}">
        <label for="{{ field.id_for_label }}">
            {{ field.label }}
            {% if field.field.required %}<span class="required">*</span>{% endif %}
        </label>
        {{ field }}
        {% if field.help_text %}
            <small class="help-text">{{ field.help_text }}</small>
        {% endif %}
        {% for error in field.errors %}
            <span class="error">{{ error }}</span>
        {% endfor %}
    </div>
    {% endfor %}

    <!-- Errores no asociados a campos específicos -->
    {% if form.non_field_errors %}
    <div class="alert alert-danger">
        {% for error in form.non_field_errors %}
            <p>{{ error }}</p>
        {% endfor %}
    </div>
    {% endif %}

    <button type="submit">Enviar</button>
</form>
{% endblock %}
```

## Validación

Django ejecuta la validación en tres etapas: validación de campo, `clean_<campo>()` y `clean()`:

```python
class RegistroForm(forms.Form):
    username = forms.CharField(max_length=50)
    email = forms.EmailField()
    password = forms.CharField(widget=forms.PasswordInput)
    confirmar_password = forms.CharField(widget=forms.PasswordInput)
    edad = forms.IntegerField(min_value=13)

    def clean_username(self):
        """Validación de un campo específico."""
        username = self.cleaned_data['username']
        if ' ' in username:
            raise forms.ValidationError(
                'El nombre de usuario no puede contener espacios.'
            )
        if User.objects.filter(username=username).exists():
            raise forms.ValidationError(
                'Este nombre de usuario ya está en uso.'
            )
        return username.lower()

    def clean_email(self):
        email = self.cleaned_data['email']
        dominio = email.split('@')[1]
        dominios_prohibidos = ['mailinator.com', 'tempmail.com']
        if dominio in dominios_prohibidos:
            raise forms.ValidationError(
                'No se permiten correos temporales.'
            )
        return email

    def clean(self):
        """Validación que involucra múltiples campos."""
        cleaned_data = super().clean()
        password = cleaned_data.get('password')
        confirmar = cleaned_data.get('confirmar_password')

        if password and confirmar and password != confirmar:
            raise forms.ValidationError(
                'Las contraseñas no coinciden.'
            )
        return cleaned_data
```

## Widgets

Los widgets controlan cómo se renderiza un campo en HTML:

```python
class ArticuloForm(forms.Form):
    titulo = forms.CharField(
        widget=forms.TextInput(attrs={
            'class': 'form-control',
            'placeholder': 'Escribe el título...',
            'id': 'campo-titulo',
        })
    )
    contenido = forms.CharField(
        widget=forms.Textarea(attrs={
            'class': 'form-control',
            'rows': 10,
            'placeholder': 'Escribe el contenido...',
        })
    )
    categoria = forms.ChoiceField(
        widget=forms.Select(attrs={'class': 'form-select'})
    )
    tags = forms.MultipleChoiceField(
        widget=forms.CheckboxSelectMultiple,
        choices=[('python', 'Python'), ('django', 'Django'), ('web', 'Web')]
    )
    fecha = forms.DateField(
        widget=forms.DateInput(attrs={
            'type': 'date',
            'class': 'form-control'
        })
    )
    activo = forms.BooleanField(
        widget=forms.CheckboxInput(attrs={'class': 'form-check-input'})
    )
```

## ModelForm

`ModelForm` genera automáticamente un formulario a partir de un modelo, evitando duplicar la definición de campos:

```python
# blog/forms.py
from django import forms
from .models import Articulo, Categoria

class ArticuloModelForm(forms.ModelForm):
    class Meta:
        model = Articulo
        fields = ['titulo', 'slug', 'contenido', 'categoria', 'publicado']
        # O excluir campos:
        # exclude = ['fecha_creacion', 'fecha_actualizacion']

        labels = {
            'titulo': 'Título del artículo',
            'contenido': 'Contenido principal',
        }
        widgets = {
            'titulo': forms.TextInput(attrs={'class': 'form-control'}),
            'contenido': forms.Textarea(attrs={
                'class': 'form-control',
                'rows': 8,
            }),
            'categoria': forms.Select(attrs={'class': 'form-select'}),
        }
        help_texts = {
            'slug': 'Se genera automáticamente a partir del título.',
        }

    def clean_titulo(self):
        titulo = self.cleaned_data['titulo']
        if len(titulo) < 10:
            raise forms.ValidationError(
                'El título debe tener al menos 10 caracteres.'
            )
        return titulo
```

### Usar ModelForm en la vista

```python
def crear_articulo(request):
    if request.method == 'POST':
        form = ArticuloModelForm(request.POST)
        if form.is_valid():
            articulo = form.save(commit=False)  # No guardar aún
            articulo.autor = request.user       # Asignar autor
            articulo.save()                     # Ahora sí guardar
            messages.success(request, 'Artículo creado exitosamente.')
            return redirect('blog:detalle', slug=articulo.slug)
    else:
        form = ArticuloModelForm()

    return render(request, 'blog/crear_articulo.html', {'form': form})


def editar_articulo(request, slug):
    articulo = get_object_or_404(Articulo, slug=slug)
    if request.method == 'POST':
        form = ArticuloModelForm(request.POST, instance=articulo)
        if form.is_valid():
            form.save()
            return redirect('blog:detalle', slug=articulo.slug)
    else:
        form = ArticuloModelForm(instance=articulo)

    return render(request, 'blog/editar_articulo.html', {'form': form})
```

## Protección CSRF

Django incluye protección contra ataques **Cross-Site Request Forgery** de manera integrada. Cada formulario POST debe incluir el token CSRF:

```html
<form method="post">
    {% csrf_token %}
    {{ form.as_p }}
    <button type="submit">Enviar</button>
</form>
```

El middleware `CsrfViewMiddleware` verifica automáticamente que cada solicitud POST incluya un token válido. Sin `{% csrf_token %}`, Django rechazará la solicitud con un error 403.

## Formsets

Los formsets permiten manejar múltiples instancias del mismo formulario:

```python
from django.forms import formset_factory, modelformset_factory

# Formset basado en Form
ImagenFormSet = formset_factory(
    ImagenForm,
    extra=3,        # Formularios vacíos adicionales
    max_num=10,     # Máximo de formularios
    can_delete=True # Permitir eliminar
)

# Formset basado en ModelForm
ArticuloFormSet = modelformset_factory(
    Articulo,
    fields=['titulo', 'publicado'],
    extra=2,
)

# En la vista
def gestionar_imagenes(request):
    if request.method == 'POST':
        formset = ImagenFormSet(request.POST, request.FILES)
        if formset.is_valid():
            for form in formset:
                if form.cleaned_data and not form.cleaned_data.get('DELETE'):
                    form.save()
            return redirect('galeria')
    else:
        formset = ImagenFormSet()

    return render(request, 'galeria/gestionar.html', {'formset': formset})
```

```html
<!-- En el template -->
<form method="post" enctype="multipart/form-data">
    {% csrf_token %}
    {{ formset.management_form }}
    {% for form in formset %}
        <div class="formset-row">
            {{ form.as_p }}
        </div>
    {% endfor %}
    <button type="submit">Guardar</button>
</form>
```

## Ejercicio Práctico

1. Crea un `ModelForm` para un modelo `Producto` con campos: nombre, descripción, precio, stock y categoría.
2. Implementa vistas para crear y editar productos.
3. Agrega validación personalizada: el precio debe ser mayor a 0, el nombre debe tener al menos 3 caracteres.
4. Personaliza los widgets con clases CSS de Bootstrap.
5. Renderiza el formulario manualmente mostrando errores individuales por campo.
6. Crea un formset para agregar múltiples imágenes a un producto.

## Resumen

Django ofrece un sistema de formularios completo y seguro. Aprendiste a crear formularios con `Form` y `ModelForm`, a validar datos en múltiples niveles, a personalizar la presentación con widgets, a proteger formularios con CSRF y a manejar múltiples formularios con formsets. Este sistema te permite recopilar y validar datos del usuario de forma eficiente y segura.
