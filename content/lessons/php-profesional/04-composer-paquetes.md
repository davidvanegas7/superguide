# Composer y Gestión de Paquetes en PHP

Composer es el gestor de dependencias estándar de PHP. Permite instalar librerías externas, gestionar versiones y configurar el autoloading de tu proyecto de manera profesional.

---

## Instalación de Composer

```bash
# Descarga e instalación global (Linux/macOS)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Verificar instalación
composer --version
```

---

## Iniciar un Proyecto con Composer

```bash
# Crear un nuevo proyecto con composer.json interactivo
composer init

# O clonar un proyecto y luego:
composer install
```

---

## Estructura de composer.json

El archivo `composer.json` es el corazón de todo proyecto PHP moderno:

```json
{
    "name": "miempresa/mi-proyecto",
    "description": "Un proyecto PHP profesional",
    "type": "project",
    "license": "MIT",
    "authors": [
        {
            "name": "Tu Nombre",
            "email": "tu@email.com"
        }
    ],
    "minimum-stability": "stable",
    "require": {
        "php": "^8.1",
        "guzzlehttp/guzzle": "^7.0",
        "monolog/monolog": "^3.0",
        "vlucas/phpdotenv": "^5.5"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "phpstan/phpstan": "^1.10",
        "friendsofphp/php-cs-fixer": "^3.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "test": "phpunit",
        "lint": "php-cs-fixer fix --dry-run",
        "analyse": "phpstan analyse src"
    }
}
```

---

## require vs require-dev

- **require**: Dependencias necesarias en **producción** (ORM, HTTP client, etc.)
- **require-dev**: Dependencias solo para **desarrollo** (testing, linters, etc.)

```bash
# Instalar una dependencia de producción
composer require guzzlehttp/guzzle

# Instalar una dependencia de desarrollo
composer require --dev phpunit/phpunit

# Instalar solo dependencias de producción (en servidor)
composer install --no-dev
```

---

## Versionado Semántico

Composer usa versionado semántico (SemVer): `MAJOR.MINOR.PATCH`

```
1.0.0 → versión inicial
1.1.0 → nueva funcionalidad (retrocompatible)
1.1.1 → corrección de bugs
2.0.0 → cambios que rompen compatibilidad
```

### Operadores de Versión

```json
{
    "require": {
        "vendor/paquete1": "^1.2",
        "vendor/paquete2": "~1.2.3",
        "vendor/paquete3": ">=1.0 <2.0",
        "vendor/paquete4": "1.0.*",
        "vendor/paquete5": "1.2.3"
    }
}
```

| Operador    | Significado                           | Rango permitido       |
|-------------|---------------------------------------|-----------------------|
| `^1.2`      | Compatible con 1.x                    | >=1.2.0, <2.0.0       |
| `^1.2.3`    | Compatible con 1.x                    | >=1.2.3, <2.0.0       |
| `~1.2.3`    | Siguiente versión significativa       | >=1.2.3, <1.3.0       |
| `~1.2`      | Siguiente versión significativa       | >=1.2.0, <2.0.0       |
| `1.0.*`     | Cualquier patch de 1.0                | >=1.0.0, <1.1.0       |
| `1.2.3`     | Versión exacta                        | Solo 1.2.3            |

> **Tip:** El operador `^` es el más utilizado y recomendado. Permite actualizaciones compatibles automáticamente.

---

## composer.lock

El archivo `composer.lock` registra las versiones **exactas** instaladas de cada paquete:

```bash
# Instalar las versiones exactas del lock file
composer install

# Actualizar dependencias a las últimas versiones permitidas
composer update

# Actualizar solo un paquete específico
composer update monolog/monolog

# Ver qué paquetes tienen actualizaciones disponibles
composer outdated
```

### Reglas importantes

- **Siempre** incluye `composer.lock` en el control de versiones para proyectos.
- Usa `composer install` en CI/CD y servidores (respeta el lock).
- Usa `composer update` solo en desarrollo cuando quieras actualizar.

---

## Scripts de Composer

Los scripts permiten definir comandos reutilizables:

```json
{
    "scripts": {
        "test": "phpunit --colors=always",
        "test:coverage": "phpunit --coverage-html coverage",
        "lint": "php-cs-fixer fix --dry-run --diff",
        "lint:fix": "php-cs-fixer fix",
        "analyse": "phpstan analyse src --level=8",
        "check": [
            "@lint",
            "@analyse",
            "@test"
        ],
        "post-install-cmd": [
            "@php artisan key:generate --ansi"
        ],
        "post-update-cmd": [
            "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
        ]
    }
}
```

```bash
# Ejecutar scripts
composer test
composer lint:fix
composer check  # Ejecuta lint, analyse y test en secuencia
```

---

## Comandos Útiles de Composer

```bash
# Ver todas las dependencias instaladas
composer show

# Ver información de un paquete específico
composer show monolog/monolog

# Buscar paquetes en Packagist
composer search http client

# Validar composer.json
composer validate

# Ver árbol de dependencias
composer depends monolog/monolog

# Ver por qué un paquete fue instalado
composer why psr/log

# Limpiar caché
composer clear-cache

# Ver configuración global
composer config --list
```

---

## Repositorios Personalizados

Puedes usar repositorios alternativos a Packagist:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/miempresa/paquete-privado"
        },
        {
            "type": "path",
            "url": "../mi-paquete-local"
        },
        {
            "type": "composer",
            "url": "https://packages.miempresa.com"
        }
    ],
    "require": {
        "miempresa/paquete-privado": "^1.0",
        "miempresa/paquete-local": "*"
    }
}
```

---

## Crear Tu Propio Paquete

### Estructura del paquete

```
mi-paquete/
├── composer.json
├── src/
│   └── MiClase.php
├── tests/
│   └── MiClaseTest.php
├── README.md
├── LICENSE
└── .gitignore
```

### composer.json del paquete

```json
{
    "name": "miusuario/mi-paquete",
    "description": "Descripción de mi paquete",
    "type": "library",
    "license": "MIT",
    "autoload": {
        "psr-4": {
            "MiUsuario\\MiPaquete\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "MiUsuario\\MiPaquete\\Tests\\": "tests/"
        }
    },
    "require": {
        "php": "^8.1"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0"
    }
}
```

### Código del paquete

```php
<?php
// src/MiClase.php

namespace MiUsuario\MiPaquete;

class Calculadora
{
    public function sumar(float $a, float $b): float
    {
        return $a + $b;
    }

    public function dividir(float $a, float $b): float
    {
        if ($b == 0) {
            throw new \DivisionByZeroError('No se puede dividir entre cero');
        }
        return $a / $b;
    }
}
```

---

## Publicar en Packagist

1. Sube tu paquete a GitHub/GitLab.
2. Crea una cuenta en [packagist.org](https://packagist.org).
3. Envía la URL del repositorio.
4. Configura un webhook para actualizaciones automáticas.

```bash
# Crear un tag de versión
git tag v1.0.0
git push origin v1.0.0
```

Una vez publicado, cualquier persona puede instalarlo:

```bash
composer require miusuario/mi-paquete
```

---

## Platform Requirements

Puedes especificar requisitos de plataforma:

```json
{
    "require": {
        "php": "^8.1",
        "ext-mbstring": "*",
        "ext-pdo": "*",
        "ext-json": "*",
        "lib-openssl": ">=1.1"
    }
}
```

Esto asegura que el entorno tenga las extensiones PHP necesarias antes de instalar.

```bash
# Ver extensiones de plataforma disponibles
composer show --platform
```

---

## Resumen

- **Composer** es el gestor de dependencias estándar de PHP.
- Usa `require` para producción y `require-dev` para desarrollo.
- El operador `^` permite actualizaciones compatibles con SemVer.
- Siempre versiona `composer.lock` en tus proyectos.
- Los **scripts** automatizan tareas repetitivas del proyecto.
- Puedes crear y **publicar paquetes propios** en Packagist.
- Usa `repositories` para paquetes privados o locales.
- Ejecuta `composer install --no-dev` en producción.
