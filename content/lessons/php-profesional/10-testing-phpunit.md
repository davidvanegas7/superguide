# Testing con PHPUnit

Las pruebas automatizadas son esenciales en el desarrollo profesional de PHP. PHPUnit es el framework de testing más utilizado en el ecosistema PHP y te permite verificar que tu código funciona correctamente de forma repetible y automática.

---

## Instalación de PHPUnit

```bash
# Instalar como dependencia de desarrollo
composer require --dev phpunit/phpunit

# Verificar la instalación
./vendor/bin/phpunit --version
```

---

## Configuración con phpunit.xml

Crea un archivo `phpunit.xml` en la raíz del proyecto:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         cacheDirectory=".phpunit.cache"
         executionOrder="depends,defects"
         requireCoverageMetadata="false"
         beStrictAboutOutputDuringTests="true"
         failOnRisky="true"
         failOnWarning="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>src</directory>
        </include>
        <exclude>
            <directory>src/Excepciones</directory>
        </exclude>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_DATABASE" value="testing"/>
    </php>
</phpunit>
```

---

## Estructura de un Test

Los tests en PHPUnit son clases que extienden `TestCase`:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Calculadora;

class CalculadoraTest extends TestCase
{
    private Calculadora $calculadora;

    // Se ejecuta ANTES de cada test
    protected function setUp(): void
    {
        parent::setUp();
        $this->calculadora = new Calculadora();
    }

    // Se ejecuta DESPUÉS de cada test
    protected function tearDown(): void
    {
        parent::tearDown();
        // Limpiar recursos si es necesario
    }

    public function test_puede_sumar_dos_numeros(): void
    {
        $resultado = $this->calculadora->sumar(2, 3);

        $this->assertEquals(5, $resultado);
    }

    public function test_puede_restar_dos_numeros(): void
    {
        $resultado = $this->calculadora->restar(10, 4);

        $this->assertEquals(6, $resultado);
    }

    public function test_division_entre_cero_lanza_excepcion(): void
    {
        $this->expectException(\DivisionByZeroError::class);
        $this->expectExceptionMessage('No se puede dividir entre cero');

        $this->calculadora->dividir(10, 0);
    }
}
```

### Convenciones de nomenclatura

- Los archivos de test terminan en `Test.php`: `CalculadoraTest.php`
- Los métodos de test empiezan con `test_` o usan el atributo `#[Test]`
- Un test por comportamiento esperado

```php
use PHPUnit\Framework\Attributes\Test;

class UsuarioTest extends TestCase
{
    #[Test]
    public function puede_crear_usuario_con_datos_validos(): void
    {
        $usuario = new Usuario('Ana', 'ana@ejemplo.com');

        $this->assertEquals('Ana', $usuario->nombre);
        $this->assertEquals('ana@ejemplo.com', $usuario->email);
    }
}
```

---

## Assertions (Aserciones)

PHPUnit ofrece una gran variedad de aserciones:

```php
class AssercionesTest extends TestCase
{
    public function test_aserciones_basicas(): void
    {
        // Igualdad
        $this->assertEquals(4, 2 + 2);          // Comparación flexible
        $this->assertSame(4, 2 + 2);            // Comparación estricta (===)
        $this->assertNotEquals(5, 2 + 2);

        // Booleanos
        $this->assertTrue(1 === 1);
        $this->assertFalse(1 === 2);

        // Null
        $this->assertNull(null);
        $this->assertNotNull('valor');

        // Tipos
        $this->assertIsInt(42);
        $this->assertIsString('hola');
        $this->assertIsArray([1, 2, 3]);
        $this->assertIsFloat(3.14);
        $this->assertIsBool(true);
        $this->assertInstanceOf(DateTime::class, new DateTime());

        // Strings
        $this->assertStringContainsString('mundo', 'hola mundo');
        $this->assertStringStartsWith('hola', 'hola mundo');
        $this->assertStringEndsWith('mundo', 'hola mundo');
        $this->assertMatchesRegularExpression('/^\d{3}-\d{4}$/', '123-4567');

        // Arrays
        $this->assertCount(3, [1, 2, 3]);
        $this->assertContains(2, [1, 2, 3]);
        $this->assertArrayHasKey('nombre', ['nombre' => 'Ana']);
        $this->assertEmpty([]);
        $this->assertNotEmpty([1]);

        // Numéricos
        $this->assertGreaterThan(3, 5);
        $this->assertLessThan(10, 5);
        $this->assertGreaterThanOrEqual(5, 5);
        $this->assertEqualsWithDelta(3.14, 3.141592, 0.01);
    }

    public function test_aserciones_de_excepciones(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El email es inválido');
        $this->expectExceptionCode(422);

        new Usuario('Ana', 'email-invalido');
    }
}
```

---

## Data Providers (Proveedores de Datos)

Los Data Providers permiten ejecutar el mismo test con múltiples conjuntos de datos:

```php
use PHPUnit\Framework\Attributes\DataProvider;

class ValidadorEmailTest extends TestCase
{
    public static function emailsValidosProvider(): array
    {
        return [
            'email simple'     => ['usuario@ejemplo.com', true],
            'con subdominios'  => ['user@sub.dominio.com', true],
            'con números'      => ['user123@ejemplo.com', true],
            'con puntos'       => ['user.name@ejemplo.com', true],
            'con guiones'      => ['user-name@ejemplo.com', true],
        ];
    }

    public static function emailsInvalidosProvider(): array
    {
        return [
            'sin arroba'       => ['usuario.ejemplo.com'],
            'sin dominio'      => ['usuario@'],
            'sin usuario'      => ['@ejemplo.com'],
            'con espacios'     => ['usuario @ejemplo.com'],
            'vacío'            => [''],
        ];
    }

    #[DataProvider('emailsValidosProvider')]
    public function test_acepta_emails_validos(string $email, bool $esperado): void
    {
        $validador = new ValidadorEmail();

        $this->assertEquals($esperado, $validador->esValido($email));
    }

    #[DataProvider('emailsInvalidosProvider')]
    public function test_rechaza_emails_invalidos(string $email): void
    {
        $validador = new ValidadorEmail();

        $this->assertFalse($validador->esValido($email));
    }
}
```

### Data Provider con generador

```php
public static function numerosProvider(): \Generator
{
    yield 'positivo' => [5, true];
    yield 'negativo' => [-3, false];
    yield 'cero'     => [0, false];
}
```

---

## Mocks y Stubs

Los test doubles permiten aislar la unidad bajo prueba de sus dependencias.

### Stubs: devuelven valores predefinidos

```php
class ServicioNotificacionTest extends TestCase
{
    public function test_envia_notificacion_por_email(): void
    {
        // Crear un stub del Mailer
        $mailer = $this->createStub(MailerInterface::class);
        $mailer->method('enviar')
               ->willReturn(true);

        $servicio = new ServicioNotificacion($mailer);
        $resultado = $servicio->notificar('user@ejemplo.com', 'Hola');

        $this->assertTrue($resultado);
    }

    public function test_maneja_fallo_de_envio(): void
    {
        $mailer = $this->createStub(MailerInterface::class);
        $mailer->method('enviar')
               ->willThrowException(new \RuntimeException('Error de conexión'));

        $servicio = new ServicioNotificacion($mailer);

        $this->expectException(\RuntimeException::class);
        $servicio->notificar('user@ejemplo.com', 'Hola');
    }
}
```

### Mocks: verifican interacciones

```php
class ProcesadorPedidoTest extends TestCase
{
    public function test_procesar_pedido_envia_email_de_confirmacion(): void
    {
        // Crear mock: verifica que se llame al método con los parámetros correctos
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())                   // Se llama exactamente 1 vez
               ->method('enviar')
               ->with(
                   $this->equalTo('cliente@ejemplo.com'),  // Primer argumento
                   $this->stringContains('confirmación')   // Segundo argumento
               )
               ->willReturn(true);

        $repositorio = $this->createStub(RepositorioPedido::class);
        $repositorio->method('guardar')->willReturn(true);

        $procesador = new ProcesadorPedido($mailer, $repositorio);
        $procesador->procesar(new Pedido(
            cliente: 'cliente@ejemplo.com',
            total: 150.00
        ));
    }

    public function test_no_envia_email_si_el_pedido_falla(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->never()) // NUNCA se debe llamar
               ->method('enviar');

        $repositorio = $this->createStub(RepositorioPedido::class);
        $repositorio->method('guardar')
                    ->willThrowException(new \RuntimeException('Error BD'));

        $procesador = new ProcesadorPedido($mailer, $repositorio);

        $this->expectException(\RuntimeException::class);
        $procesador->procesar(new Pedido('cliente@ejemplo.com', 150.00));
    }
}
```

### Expectativas de llamadas

```php
$mock->expects($this->once());           // Exactamente 1 vez
$mock->expects($this->exactly(3));       // Exactamente 3 veces
$mock->expects($this->atLeastOnce());    // Al menos 1 vez
$mock->expects($this->never());          // Nunca
$mock->expects($this->atMost(5));        // Como máximo 5 veces
```

---

## setUp y tearDown

```php
class RepositorioUsuarioTest extends TestCase
{
    private PDO $pdo;
    private RepositorioUsuario $repositorio;

    // Se ejecuta UNA vez antes de todos los tests de la clase
    public static function setUpBeforeClass(): void
    {
        // Crear base de datos de testing, etc.
    }

    // Se ejecuta ANTES de cada test individual
    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->exec('CREATE TABLE usuarios (id INTEGER PRIMARY KEY, nombre TEXT, email TEXT)');
        $this->repositorio = new RepositorioUsuario($this->pdo);
    }

    // Se ejecuta DESPUÉS de cada test individual
    protected function tearDown(): void
    {
        $this->pdo->exec('DROP TABLE IF EXISTS usuarios');
        parent::tearDown();
    }

    // Se ejecuta UNA vez después de todos los tests de la clase
    public static function tearDownAfterClass(): void
    {
        // Limpiar recursos globales
    }

    public function test_puede_guardar_y_recuperar_usuario(): void
    {
        $usuario = new Usuario(nombre: 'Ana', email: 'ana@ejemplo.com');
        $this->repositorio->guardar($usuario);

        $encontrado = $this->repositorio->buscarPorEmail('ana@ejemplo.com');

        $this->assertNotNull($encontrado);
        $this->assertEquals('Ana', $encontrado->nombre);
    }

    public function test_devuelve_null_si_no_encuentra_usuario(): void
    {
        $encontrado = $this->repositorio->buscarPorEmail('noexiste@ejemplo.com');

        $this->assertNull($encontrado);
    }
}
```

---

## Code Coverage (Cobertura de Código)

La cobertura de código mide qué porcentaje de tu código se ejecuta durante los tests:

```bash
# Requiere Xdebug o PCOV
# Con Xdebug
php -d xdebug.mode=coverage ./vendor/bin/phpunit --coverage-text

# Generar reporte HTML
php -d xdebug.mode=coverage ./vendor/bin/phpunit --coverage-html coverage/

# Generar reporte Clover (para CI/CD)
php -d xdebug.mode=coverage ./vendor/bin/phpunit --coverage-clover coverage.xml
```

### Requerir cobertura mínima

En `phpunit.xml`:

```xml
<coverage>
    <report>
        <html outputDirectory="coverage"/>
    </report>
</coverage>
```

> **Tip:** Una cobertura del 80% es un buen objetivo. No persigas el 100%: enfócate en cubrir la lógica de negocio crítica.

---

## Ejecutar Tests

```bash
# Ejecutar todos los tests
./vendor/bin/phpunit

# Ejecutar una suite específica
./vendor/bin/phpunit --testsuite Unit

# Ejecutar un archivo específico
./vendor/bin/phpunit tests/Unit/CalculadoraTest.php

# Ejecutar un test específico
./vendor/bin/phpunit --filter test_puede_sumar_dos_numeros

# Ejecutar con salida detallada
./vendor/bin/phpunit --testdox

# Detener al primer fallo
./vendor/bin/phpunit --stop-on-failure

# Ejecutar solo tests que fallaron en la última ejecución
./vendor/bin/phpunit --order-by=defects --stop-on-defect
```

### Salida con --testdox

```
Calculadora
 ✓ Puede sumar dos numeros
 ✓ Puede restar dos numeros
 ✓ Division entre cero lanza excepcion

Validador Email
 ✓ Acepta emails validos with email simple
 ✓ Acepta emails validos with con subdominios
 ✗ Rechaza emails invalidos with vacío
```

---

## Buenas Prácticas de Testing

```php
class BuenasPracticasTest extends TestCase
{
    // 1. Un test debe verificar UNA sola cosa
    public function test_el_nombre_no_puede_estar_vacio(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Usuario('', 'email@ejemplo.com');
    }

    // 2. Usa nombres descriptivos
    public function test_usuario_premium_tiene_descuento_del_20_porciento(): void
    {
        $usuario = new Usuario('Ana', 'ana@e.com', tipoPlan: 'premium');
        $precio = $usuario->calcularPrecio(100);

        $this->assertEquals(80.0, $precio);
    }

    // 3. Sigue el patrón AAA: Arrange, Act, Assert
    public function test_puede_agregar_producto_al_carrito(): void
    {
        // Arrange (Preparar)
        $carrito = new Carrito();
        $producto = new Producto('Laptop', 999.99);

        // Act (Actuar)
        $carrito->agregar($producto);

        // Assert (Verificar)
        $this->assertCount(1, $carrito->productos());
        $this->assertEquals(999.99, $carrito->total());
    }
}
```

---

## Resumen

- Instala PHPUnit con `composer require --dev phpunit/phpunit`.
- Configura suites y opciones en `phpunit.xml`.
- Los tests extienden `TestCase` y sus métodos empiezan con `test_` o usan `#[Test]`.
- Usa **aserciones** para verificar resultados: `assertEquals`, `assertTrue`, `assertCount`, etc.
- Los **Data Providers** ejecutan el mismo test con múltiples datos.
- Los **stubs** simulan valores de retorno; los **mocks** verifican interacciones.
- `setUp()` y `tearDown()` preparan y limpian el estado para cada test.
- Mide la **cobertura de código** para identificar código sin testear.
- Sigue el patrón **AAA** (Arrange, Act, Assert) para tests claros y mantenibles.
