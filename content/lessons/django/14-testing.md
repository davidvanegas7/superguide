---
title: "Testing en Django"
slug: "django-testing"
description: "Aprende a escribir tests completos en Django: TestCase, Client, factory_boy, testing de modelos, vistas y formularios, y cobertura"
---
# Testing en Django

El testing es una práctica esencial en el desarrollo profesional con Django. Un buen conjunto de tests te permite refactorizar con confianza, detectar regresiones temprano y documentar el comportamiento esperado de tu código. Django incluye un framework de testing robusto basado en `unittest` de Python, con herramientas específicas para probar modelos, vistas, formularios y más.

## TestCase de Django

`TestCase` es la clase base para los tests en Django. Cada test se ejecuta dentro de una transacción que se revierte al finalizar, manteniendo la base de datos limpia:

```python
# tests/test_models.py
from django.test import TestCase
from django.utils import timezone
from .models import Articulo, Categoria

class ArticuloModelTest(TestCase):

    def setUp(self):
        """Se ejecuta antes de cada test."""
        self.categoria = Categoria.objects.create(
            nombre='Python',
            slug='python'
        )
        self.articulo = Articulo.objects.create(
            titulo='Introducción a Django',
            contenido='Contenido del artículo...',
            categoria=self.categoria,
            fecha_publicacion=timezone.now()
        )

    def tearDown(self):
        """Se ejecuta después de cada test (opcional con TestCase)."""
        # La limpieza de la BD es automática con TestCase
        pass

    def test_str_representation(self):
        self.assertEqual(str(self.articulo), 'Introducción a Django')

    def test_slug_generado_automaticamente(self):
        self.assertEqual(self.articulo.slug, 'introduccion-a-django')

    def test_articulo_pertenece_a_categoria(self):
        self.assertEqual(self.articulo.categoria.nombre, 'Python')

    def test_articulos_recientes(self):
        recientes = Articulo.objects.recientes()
        self.assertIn(self.articulo, recientes)

    def test_fecha_publicacion_no_futura(self):
        self.assertLessEqual(self.articulo.fecha_publicacion, timezone.now())
```

### setUpTestData — Optimización

```python
class ArticuloModelTest(TestCase):

    @classmethod
    def setUpTestData(cls):
        """Se ejecuta UNA sola vez para toda la clase. Más eficiente."""
        cls.categoria = Categoria.objects.create(nombre='Django', slug='django')
        cls.articulo = Articulo.objects.create(
            titulo='Testing en Django',
            contenido='Contenido...',
            categoria=cls.categoria,
        )

    def test_titulo(self):
        self.assertEqual(self.articulo.titulo, 'Testing en Django')
```

## Client: Testing de Vistas

Django ofrece un `Client` de pruebas para simular peticiones HTTP sin necesidad de un servidor:

```python
from django.test import TestCase, Client
from django.urls import reverse
from django.contrib.auth.models import User

class VistaArticulosTest(TestCase):

    def setUp(self):
        self.client = Client()
        self.user = User.objects.create_user(
            username='testuser',
            password='testpass123'
        )
        self.articulo = Articulo.objects.create(
            titulo='Test Article',
            contenido='Contenido...',
            autor=self.user,
            publicado=True,
        )

    def test_lista_articulos_status_200(self):
        response = self.client.get(reverse('articulo-lista'))
        self.assertEqual(response.status_code, 200)

    def test_lista_articulos_template(self):
        response = self.client.get(reverse('articulo-lista'))
        self.assertTemplateUsed(response, 'articulos/lista.html')

    def test_lista_contiene_articulo(self):
        response = self.client.get(reverse('articulo-lista'))
        self.assertContains(response, 'Test Article')
        self.assertNotContains(response, 'Artículo inexistente')

    def test_detalle_articulo(self):
        response = self.client.get(
            reverse('articulo-detalle', kwargs={'pk': self.articulo.pk})
        )
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.context['articulo'], self.articulo)

    def test_crear_articulo_requiere_login(self):
        response = self.client.get(reverse('articulo-crear'))
        self.assertRedirects(response, '/accounts/login/?next=/articulos/crear/')

    def test_crear_articulo_post(self):
        self.client.login(username='testuser', password='testpass123')
        response = self.client.post(reverse('articulo-crear'), {
            'titulo': 'Nuevo Artículo',
            'contenido': 'Contenido del nuevo artículo',
            'publicado': True,
        })
        self.assertRedirects(response, reverse('articulo-lista'))
        self.assertTrue(Articulo.objects.filter(titulo='Nuevo Artículo').exists())

    def test_articulo_no_encontrado(self):
        response = self.client.get(
            reverse('articulo-detalle', kwargs={'pk': 9999})
        )
        self.assertEqual(response.status_code, 404)
```

## factory_boy: Fábricas de Datos

`factory_boy` simplifica la creación de datos de prueba, evitando repetir código en `setUp`:

```bash
pip install factory_boy
```

```python
# tests/factories.py
import factory
from django.contrib.auth.models import User
from .models import Articulo, Categoria, Comentario

class UserFactory(factory.django.DjangoModelFactory):
    class Meta:
        model = User

    username = factory.Sequence(lambda n: f'usuario{n}')
    email = factory.LazyAttribute(lambda obj: f'{obj.username}@test.com')
    password = factory.PostGenerationMethodCall('set_password', 'testpass123')

class CategoriaFactory(factory.django.DjangoModelFactory):
    class Meta:
        model = Categoria

    nombre = factory.Faker('word', locale='es_ES')
    slug = factory.LazyAttribute(lambda obj: obj.nombre.lower())

class ArticuloFactory(factory.django.DjangoModelFactory):
    class Meta:
        model = Articulo

    titulo = factory.Faker('sentence', locale='es_ES')
    contenido = factory.Faker('text', max_nb_chars=500, locale='es_ES')
    autor = factory.SubFactory(UserFactory)
    categoria = factory.SubFactory(CategoriaFactory)
    publicado = True

class ComentarioFactory(factory.django.DjangoModelFactory):
    class Meta:
        model = Comentario

    articulo = factory.SubFactory(ArticuloFactory)
    autor = factory.SubFactory(UserFactory)
    texto = factory.Faker('paragraph', locale='es_ES')
```

### Uso en Tests

```python
from .factories import ArticuloFactory, UserFactory

class ArticuloConFactoryTest(TestCase):

    def test_crear_articulo(self):
        articulo = ArticuloFactory()
        self.assertTrue(articulo.publicado)
        self.assertIsNotNone(articulo.autor)

    def test_articulo_borrador(self):
        articulo = ArticuloFactory(publicado=False)
        self.assertFalse(articulo.publicado)

    def test_multiples_articulos(self):
        ArticuloFactory.create_batch(10)
        self.assertEqual(Articulo.objects.count(), 10)

    def test_articulo_con_autor_especifico(self):
        autor = UserFactory(username='juan')
        articulo = ArticuloFactory(autor=autor)
        self.assertEqual(articulo.autor.username, 'juan')
```

## Testing de Formularios

```python
from django.test import TestCase
from .forms import ArticuloForm

class ArticuloFormTest(TestCase):

    def test_formulario_valido(self):
        form = ArticuloForm(data={
            'titulo': 'Un Título Válido',
            'contenido': 'Contenido del artículo',
            'publicado': True,
        })
        self.assertTrue(form.is_valid())

    def test_formulario_titulo_vacio(self):
        form = ArticuloForm(data={
            'titulo': '',
            'contenido': 'Contenido',
        })
        self.assertFalse(form.is_valid())
        self.assertIn('titulo', form.errors)

    def test_formulario_titulo_muy_largo(self):
        form = ArticuloForm(data={
            'titulo': 'x' * 201,  # Excede max_length
            'contenido': 'Contenido',
        })
        self.assertFalse(form.is_valid())

    def test_formulario_labels(self):
        form = ArticuloForm()
        self.assertEqual(form.fields['titulo'].label, 'Título del artículo')
```

## Asserts Específicos de Django

Django extiende los asserts de `unittest` con métodos especializados:

```python
class AssertsEspecificosTest(TestCase):

    def test_assert_contains(self):
        response = self.client.get('/')
        self.assertContains(response, 'Bienvenido', status_code=200)

    def test_assert_redirects(self):
        response = self.client.get('/perfil/')
        self.assertRedirects(response, '/login/?next=/perfil/')

    def test_assert_template_used(self):
        response = self.client.get('/')
        self.assertTemplateUsed(response, 'home.html')

    def test_assert_form_error(self):
        response = self.client.post('/crear/', {'titulo': ''})
        self.assertFormError(response.context['form'], 'titulo', 'Este campo es obligatorio.')

    def test_assert_queryset_equal(self):
        ArticuloFactory.create_batch(3, publicado=True)
        qs = Articulo.objects.filter(publicado=True).order_by('id')
        self.assertQuerySetEqual(
            qs,
            Articulo.objects.all().order_by('id')
        )

    def test_assert_num_queries(self):
        ArticuloFactory.create_batch(5)
        with self.assertNumQueries(1):
            list(Articulo.objects.all())
```

## Ejecutar Tests y Cobertura

```bash
# Ejecutar todos los tests
python manage.py test

# Tests de una app específica
python manage.py test app_name

# Un archivo o clase específica
python manage.py test app_name.tests.test_models.ArticuloModelTest

# Con verbosidad
python manage.py test --verbosity=2

# Cobertura de código
pip install coverage
coverage run --source='.' manage.py test
coverage report
coverage html  # Genera reporte HTML en htmlcov/
```

## Ejercicio Práctico

Crea un conjunto completo de tests para un blog:

1. Crea factories con `factory_boy` para `User`, `Post` y `Comentario`.
2. Escribe tests para el modelo `Post` (creación, slug automático, método `__str__`).
3. Escribe tests para la vista de lista (status 200, template correcto, contenido).
4. Escribe tests para la creación de posts (requiere login, form válido, redirect).
5. Verifica que la cobertura sea superior al 80%.

```python
class PostViewTest(TestCase):
    def setUp(self):
        self.user = UserFactory()
        self.client.login(username=self.user.username, password='testpass123')

    def test_crear_post_exitoso(self):
        response = self.client.post(reverse('post-crear'), {
            'titulo': 'Mi Post',
            'contenido': 'Contenido de prueba',
        })
        self.assertRedirects(response, reverse('post-lista'))
        self.assertEqual(Post.objects.count(), 1)
```

## Resumen

Django proporciona un framework de testing completo. **TestCase** ofrece aislamiento de base de datos con rollback automático, mientras que **setUpTestData** optimiza la creación de datos compartidos. El **Client** simula peticiones HTTP para probar vistas sin servidor. **factory_boy** simplifica la generación de datos de prueba con factories. Los **asserts específicos** de Django (assertContains, assertRedirects, assertFormError) facilitan las verificaciones comunes. Usa **coverage** para medir la cobertura y asegurar que tus tests cubren el código crítico de tu aplicación.
