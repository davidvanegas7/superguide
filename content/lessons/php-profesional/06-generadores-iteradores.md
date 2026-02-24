# Generadores e Iteradores en PHP

Los generadores e iteradores son herramientas poderosas para trabajar con secuencias de datos de forma eficiente en memoria. En lugar de cargar todo en un array, procesan los elementos uno a uno.

---

## ¿Qué es un Generador?

Un generador es una función que usa `yield` para producir valores uno a uno, pausando su ejecución entre cada valor. Esto permite trabajar con conjuntos de datos enormes sin consumir memoria excesiva.

```php
function numeros(int $inicio, int $fin): Generator
{
    for ($i = $inicio; $i <= $fin; $i++) {
        yield $i;
    }
}

foreach (numeros(1, 5) as $numero) {
    echo $numero . ' '; // 1 2 3 4 5
}
```

### Comparación de memoria

```php
// SIN generador: carga todo en memoria
function rangoArray(int $inicio, int $fin): array
{
    $resultado = [];
    for ($i = $inicio; $i <= $fin; $i++) {
        $resultado[] = $i;
    }
    return $resultado; // Array de 1 millón de elementos en memoria
}

// CON generador: un elemento a la vez en memoria
function rangoGenerador(int $inicio, int $fin): Generator
{
    for ($i = $inicio; $i <= $fin; $i++) {
        yield $i; // Solo un valor en memoria a la vez
    }
}

// Uso: ambos funcionan igual en un foreach
foreach (rangoGenerador(1, 1_000_000) as $n) {
    // Procesa millones de elementos con memoria mínima
}
```

> **Tip:** Usa generadores cuando trabajes con archivos grandes, resultados de base de datos o cualquier secuencia que sea costosa en memoria.

---

## yield con Claves

Puedes generar pares clave-valor:

```php
function parsearCsv(string $archivo): Generator
{
    $handle = fopen($archivo, 'r');
    $encabezados = fgetcsv($handle);

    $linea = 1;
    while (($datos = fgetcsv($handle)) !== false) {
        yield $linea => array_combine($encabezados, $datos);
        $linea++;
    }

    fclose($handle);
}

foreach (parsearCsv('usuarios.csv') as $numero => $fila) {
    echo "Línea {$numero}: {$fila['nombre']}\n";
}
```

---

## yield from: Delegación de Generadores

`yield from` permite que un generador delegue a otro generador, array o iterable:

```php
function fibonacci(): Generator
{
    $a = 0;
    $b = 1;
    while (true) {
        yield $a;
        [$a, $b] = [$b, $a + $b];
    }
}

function primerosFibonacci(int $n): Generator
{
    $contador = 0;
    foreach (fibonacci() as $valor) {
        if ($contador >= $n) return;
        yield $valor;
        $contador++;
    }
}

// Combinar generadores con yield from
function numerosCompuestos(): Generator
{
    yield from [1, 2, 3];              // Delegar desde array
    yield from range(10, 13);          // Delegar desde array
    yield from primerosFibonacci(5);   // Delegar desde generador
}

foreach (numerosCompuestos() as $n) {
    echo $n . ' '; // 1 2 3 10 11 12 13 0 1 1 2 3
}
```

### Ejemplo práctico: combinar fuentes de datos

```php
function usuariosDeBase(): Generator
{
    // Simula consulta paginada
    for ($pagina = 1; $pagina <= 3; $pagina++) {
        $resultados = consultarPagina($pagina);
        yield from $resultados;
    }
}

function todosLosUsuarios(): Generator
{
    yield from usuariosDeBase();
    yield from usuariosDeApi();
    yield from usuariosDeArchivo();
}
```

---

## El Método send()

Los generadores son bidireccionales: puedes enviarles valores con `send()`:

```php
function acumulador(): Generator
{
    $total = 0;
    while (true) {
        $valor = yield $total; // Recibe valor y devuelve el total actual
        if ($valor === null) break;
        $total += $valor;
    }
}

$gen = acumulador();
$gen->current();         // Inicializa el generador (total = 0)
echo $gen->send(10);     // 10
echo $gen->send(20);     // 30
echo $gen->send(5);      // 35
```

### Pipeline con send()

```php
function transformador(): Generator
{
    while (true) {
        $entrada = yield;
        if ($entrada === null) return;
        echo strtoupper(trim($entrada)) . "\n";
    }
}

$t = transformador();
$t->current(); // Inicializar
$t->send('  hola mundo  ');  // HOLA MUNDO
$t->send('  php rocks  ');   // PHP ROCKS
```

---

## Métodos de la Clase Generator

```php
function miGenerador(): Generator
{
    yield 'a';
    yield 'b';
    yield 'c';
    return 'valor final'; // Valor de retorno del generador
}

$gen = miGenerador();

echo $gen->current();  // 'a' - valor actual sin avanzar
$gen->next();          // Avanza al siguiente yield
echo $gen->current();  // 'b'
echo $gen->key();      // 1 (índice actual)

$gen->next();
$gen->next();          // Termina el generador

echo $gen->valid();    // false (ya no hay más valores)
echo $gen->getReturn(); // 'valor final'
```

---

## Secuencias Infinitas

Los generadores permiten representar secuencias infinitas de forma segura:

```php
function numerosNaturales(): Generator
{
    $n = 1;
    while (true) {
        yield $n++;
    }
}

function potenciasDeDos(): Generator
{
    $n = 1;
    while (true) {
        yield $n;
        $n *= 2;
    }
}

// Tomar solo los primeros N elementos
function tomar(Generator $gen, int $n): array
{
    $resultado = [];
    foreach ($gen as $valor) {
        $resultado[] = $valor;
        if (count($resultado) >= $n) break;
    }
    return $resultado;
}

$primeros10 = tomar(potenciasDeDos(), 10);
// [1, 2, 4, 8, 16, 32, 64, 128, 256, 512]
```

---

## La Interface Iterator

Para crear clases que se comporten como iterables, implementa la interface `Iterator`:

```php
class RangoNumerico implements Iterator
{
    private int $actual;

    public function __construct(
        private readonly int $inicio,
        private readonly int $fin,
        private readonly int $paso = 1
    ) {
        $this->actual = $inicio;
    }

    public function current(): int
    {
        return $this->actual;
    }

    public function key(): int
    {
        return ($this->actual - $this->inicio) / $this->paso;
    }

    public function next(): void
    {
        $this->actual += $this->paso;
    }

    public function rewind(): void
    {
        $this->actual = $this->inicio;
    }

    public function valid(): bool
    {
        return $this->actual <= $this->fin;
    }
}

$rango = new RangoNumerico(0, 20, 5);
foreach ($rango as $indice => $valor) {
    echo "{$indice}: {$valor}\n";
}
// 0: 0
// 1: 5
// 2: 10
// 3: 15
// 4: 20
```

---

## IteratorAggregate

`IteratorAggregate` es más simple que `Iterator`. Solo necesitas implementar `getIterator()`:

```php
class Coleccion implements IteratorAggregate, Countable
{
    private array $elementos = [];

    public function agregar(mixed $elemento): void
    {
        $this->elementos[] = $elemento;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->elementos);
    }

    public function count(): int
    {
        return count($this->elementos);
    }
}

$coleccion = new Coleccion();
$coleccion->agregar('PHP');
$coleccion->agregar('Python');
$coleccion->agregar('JavaScript');

foreach ($coleccion as $lenguaje) {
    echo $lenguaje . "\n";
}

echo count($coleccion); // 3
```

### IteratorAggregate con Generadores

Puedes combinar `IteratorAggregate` con generadores:

```php
class RegistrosBaseDatos implements IteratorAggregate
{
    public function __construct(
        private PDO $pdo,
        private string $tabla,
        private int $tamanioPagina = 100
    ) {}

    public function getIterator(): Generator
    {
        $offset = 0;
        while (true) {
            $sql = "SELECT * FROM {$this->tabla} LIMIT {$this->tamanioPagina} OFFSET {$offset}";
            $registros = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            if (empty($registros)) break;

            yield from $registros;
            $offset += $this->tamanioPagina;
        }
    }
}

// Itera sobre millones de registros sin cargar todo en memoria
$usuarios = new RegistrosBaseDatos($pdo, 'usuarios');
foreach ($usuarios as $usuario) {
    procesarUsuario($usuario);
}
```

---

## Lazy Evaluation: Procesamiento Perezoso

Los generadores habilitan la evaluación perezosa, donde los valores se calculan solo cuando se necesitan:

```php
function filtrar(iterable $datos, callable $predicado): Generator
{
    foreach ($datos as $clave => $valor) {
        if ($predicado($valor)) {
            yield $clave => $valor;
        }
    }
}

function mapear(iterable $datos, callable $transformacion): Generator
{
    foreach ($datos as $clave => $valor) {
        yield $clave => $transformacion($valor);
    }
}

function tomar2(iterable $datos, int $limite): Generator
{
    $contador = 0;
    foreach ($datos as $clave => $valor) {
        if ($contador >= $limite) return;
        yield $clave => $valor;
        $contador++;
    }
}

// Pipeline perezoso: nada se ejecuta hasta que se itera
$resultado = tomar2(
    mapear(
        filtrar(
            rangoGenerador(1, 1_000_000),
            fn($n) => $n % 2 === 0  // Solo pares
        ),
        fn($n) => $n ** 2           // Elevar al cuadrado
    ),
    5                                // Tomar solo 5
);

foreach ($resultado as $valor) {
    echo $valor . ' '; // 4 16 36 64 100
}
// Solo se procesaron los primeros 10 números, no el millón completo
```

---

## Resumen

- Los **generadores** con `yield` producen valores uno a uno, ahorrando memoria.
- `yield from` delega la generación a otro iterable o generador.
- `send()` permite comunicación bidireccional con un generador.
- Las **secuencias infinitas** son posibles y seguras con generadores.
- La interface `Iterator` da control completo sobre la iteración de objetos.
- `IteratorAggregate` es una alternativa más simple, ideal con generadores.
- La **evaluación perezosa** permite crear pipelines eficientes que solo computan lo necesario.
- Usa generadores para leer archivos grandes, paginar consultas y procesar streams.
