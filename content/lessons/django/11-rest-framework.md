---
title: "Django REST Framework"
slug: "django-rest-framework"
description: "Aprende a construir APIs RESTful con Django REST Framework: serializers, ViewSets, routers, permisos, autenticación y paginación"
---
# Django REST Framework

Django REST Framework (DRF) es la biblioteca más popular para construir APIs RESTful con Django. Proporciona herramientas poderosas como serializers, ViewSets, autenticación, permisos y mucho más, permitiendo crear APIs robustas y bien documentadas en poco tiempo.

## Instalación y Configuración

Para comenzar, instala DRF y agrégalo a tu proyecto:

```bash
pip install djangorestframework
```

```python
# settings.py
INSTALLED_APPS = [
    ...
    'rest_framework',
]

REST_FRAMEWORK = {
    'DEFAULT_PAGINATION_CLASS': 'rest_framework.pagination.PageNumberPagination',
    'PAGE_SIZE': 10,
    'DEFAULT_AUTHENTICATION_CLASSES': [
        'rest_framework.authentication.TokenAuthentication',
        'rest_framework.authentication.SessionAuthentication',
    ],
    'DEFAULT_PERMISSION_CLASSES': [
        'rest_framework.permissions.IsAuthenticated',
    ],
}
```

## Serializers

Los serializers convierten datos complejos (como instancias de modelos) en tipos de datos nativos de Python que pueden renderizarse en JSON, XML u otros formatos.

### Serializer Básico

```python
from rest_framework import serializers

class ArticuloSerializer(serializers.Serializer):
    id = serializers.IntegerField(read_only=True)
    titulo = serializers.CharField(max_length=200)
    contenido = serializers.CharField()
    fecha_publicacion = serializers.DateTimeField(read_only=True)

    def create(self, validated_data):
        return Articulo.objects.create(**validated_data)

    def update(self, instance, validated_data):
        instance.titulo = validated_data.get('titulo', instance.titulo)
        instance.contenido = validated_data.get('contenido', instance.contenido)
        instance.save()
        return instance
```

### ModelSerializer

`ModelSerializer` genera automáticamente campos y los métodos `create()`/`update()` basándose en el modelo:

```python
from rest_framework import serializers
from .models import Articulo, Categoria

class CategoriaSerializer(serializers.ModelSerializer):
    class Meta:
        model = Categoria
        fields = ['id', 'nombre', 'slug']

class ArticuloSerializer(serializers.ModelSerializer):
    categoria = CategoriaSerializer(read_only=True)
    categoria_id = serializers.PrimaryKeyRelatedField(
        queryset=Categoria.objects.all(),
        source='categoria',
        write_only=True
    )
    autor_nombre = serializers.SerializerMethodField()

    class Meta:
        model = Articulo
        fields = ['id', 'titulo', 'contenido', 'categoria', 'categoria_id',
                  'autor', 'autor_nombre', 'fecha_publicacion']
        read_only_fields = ['autor', 'fecha_publicacion']

    def get_autor_nombre(self, obj):
        return obj.autor.get_full_name()

    def validate_titulo(self, value):
        if len(value) < 5:
            raise serializers.ValidationError("El título debe tener al menos 5 caracteres.")
        return value
```

## ViewSets y Routers

Los ViewSets combinan la lógica de varias vistas relacionadas en una sola clase. Los routers generan automáticamente las URLs.

### ViewSets

```python
from rest_framework import viewsets, status
from rest_framework.decorators import action
from rest_framework.response import Response
from .models import Articulo
from .serializers import ArticuloSerializer

class ArticuloViewSet(viewsets.ModelViewSet):
    queryset = Articulo.objects.all()
    serializer_class = ArticuloSerializer

    def perform_create(self, serializer):
        serializer.save(autor=self.request.user)

    def get_queryset(self):
        queryset = Articulo.objects.select_related('autor', 'categoria')
        categoria = self.request.query_params.get('categoria')
        if categoria:
            queryset = queryset.filter(categoria__slug=categoria)
        return queryset

    @action(detail=True, methods=['post'])
    def publicar(self, request, pk=None):
        articulo = self.get_object()
        articulo.publicado = True
        articulo.save()
        return Response({'status': 'Artículo publicado'})

    @action(detail=False, methods=['get'])
    def recientes(self, request):
        recientes = Articulo.objects.order_by('-fecha_publicacion')[:5]
        serializer = self.get_serializer(recientes, many=True)
        return Response(serializer.data)
```

### Routers

```python
# urls.py
from rest_framework.routers import DefaultRouter
from .views import ArticuloViewSet

router = DefaultRouter()
router.register(r'articulos', ArticuloViewSet)

urlpatterns = [
    path('api/', include(router.urls)),
]
# Genera: /api/articulos/, /api/articulos/{pk}/,
#          /api/articulos/recientes/, /api/articulos/{pk}/publicar/
```

## Permisos

DRF ofrece un sistema de permisos flexible para controlar el acceso a la API:

```python
from rest_framework import permissions

class EsAutorOSoloLectura(permissions.BasePermission):
    """Solo el autor puede editar; los demás solo pueden leer."""

    def has_object_permission(self, request, view, obj):
        if request.method in permissions.SAFE_METHODS:
            return True
        return obj.autor == request.user

class ArticuloViewSet(viewsets.ModelViewSet):
    permission_classes = [permissions.IsAuthenticated, EsAutorOSoloLectura]
    queryset = Articulo.objects.all()
    serializer_class = ArticuloSerializer
```

## Autenticación: Token y JWT

### Token Authentication

```bash
pip install djangorestframework
```

```python
# settings.py
INSTALLED_APPS = [..., 'rest_framework.authtoken']

# urls.py
from rest_framework.authtoken.views import obtain_auth_token
urlpatterns = [
    path('api/token/', obtain_auth_token),
]
```

### JWT Authentication

```bash
pip install djangorestframework-simplejwt
```

```python
# settings.py
REST_FRAMEWORK = {
    'DEFAULT_AUTHENTICATION_CLASSES': [
        'rest_framework_simplejwt.authentication.JWTAuthentication',
    ],
}

from datetime import timedelta
SIMPLE_JWT = {
    'ACCESS_TOKEN_LIFETIME': timedelta(minutes=30),
    'REFRESH_TOKEN_LIFETIME': timedelta(days=1),
    'ROTATE_REFRESH_TOKENS': True,
}

# urls.py
from rest_framework_simplejwt.views import TokenObtainPairView, TokenRefreshView
urlpatterns = [
    path('api/token/', TokenObtainPairView.as_view()),
    path('api/token/refresh/', TokenRefreshView.as_view()),
]
```

## Paginación y Filtrado

### Paginación Personalizada

```python
from rest_framework.pagination import PageNumberPagination

class PaginacionArticulos(PageNumberPagination):
    page_size = 20
    page_size_query_param = 'page_size'
    max_page_size = 100

class ArticuloViewSet(viewsets.ModelViewSet):
    pagination_class = PaginacionArticulos
```

### Filtrado con django-filter

```bash
pip install django-filter
```

```python
# settings.py
INSTALLED_APPS = [..., 'django_filters']

REST_FRAMEWORK = {
    'DEFAULT_FILTER_BACKENDS': [
        'django_filters.rest_framework.DjangoFilterBackend',
        'rest_framework.filters.SearchFilter',
        'rest_framework.filters.OrderingFilter',
    ],
}

# views.py
import django_filters

class ArticuloFilter(django_filters.FilterSet):
    fecha_desde = django_filters.DateFilter(field_name='fecha_publicacion', lookup_expr='gte')
    fecha_hasta = django_filters.DateFilter(field_name='fecha_publicacion', lookup_expr='lte')

    class Meta:
        model = Articulo
        fields = ['categoria', 'publicado', 'fecha_desde', 'fecha_hasta']

class ArticuloViewSet(viewsets.ModelViewSet):
    filterset_class = ArticuloFilter
    search_fields = ['titulo', 'contenido']
    ordering_fields = ['fecha_publicacion', 'titulo']
    ordering = ['-fecha_publicacion']
```

## Ejercicio Práctico

Crea una API completa para una tienda en línea:

1. Define modelos `Producto` y `Pedido` con sus relaciones.
2. Crea serializers con validaciones personalizadas.
3. Implementa ViewSets con acciones personalizadas (`agregar_al_carrito`, `confirmar_pedido`).
4. Configura autenticación JWT y permisos para que solo usuarios autenticados puedan crear pedidos.
5. Agrega paginación y filtrado por categoría y rango de precios.

```python
class ProductoSerializer(serializers.ModelSerializer):
    class Meta:
        model = Producto
        fields = '__all__'

    def validate_precio(self, value):
        if value <= 0:
            raise serializers.ValidationError("El precio debe ser positivo.")
        return value

class PedidoViewSet(viewsets.ModelViewSet):
    permission_classes = [permissions.IsAuthenticated]

    def perform_create(self, serializer):
        serializer.save(usuario=self.request.user)

    @action(detail=True, methods=['post'])
    def confirmar(self, request, pk=None):
        pedido = self.get_object()
        pedido.estado = 'confirmado'
        pedido.save()
        return Response({'status': 'Pedido confirmado'})
```

## Resumen

Django REST Framework es una herramienta imprescindible para construir APIs en Django. Los **serializers** transforman datos entre objetos Python y JSON; los **ModelSerializer** automatizan este proceso. Los **ViewSets** y **routers** simplifican la creación de endpoints CRUD. El sistema de **permisos** y **autenticación** (Token/JWT) protege tus recursos. Finalmente, la **paginación** y el **filtrado** permiten manejar grandes volúmenes de datos eficientemente. Dominar DRF te permite crear APIs profesionales, seguras y escalables.
