<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExcelBeginnerExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'excel-principiante')->first();

        if (! $course) {
            $this->command->warn('Excel Principiante course not found. Run CourseSeeder + ExcelBeginnerLessonSeeder first.');
            return;
        }

        /** @var \Illuminate\Support\Collection<int,Lesson> $lessons */
        $lessons = Lesson::where('course_id', $course->id)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('sort_order');

        $exercises = $this->exercises($lessons);
        $now = now();

        foreach ($exercises as $ex) {
            DB::table('lesson_exercises')->updateOrInsert(
                ['lesson_id' => $ex['lesson_id']],
                array_merge($ex, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        $this->command->info('Excel Principiante exercises seeded: ' . count($exercises) . ' exercises.');
    }

    private function exercises(\Illuminate\Support\Collection $lessons): array
    {
        $ex = [];

        // ── Lección 1: Introducción a Excel ───────────────────────────────
        if ($l = $lessons->get(1)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Primeros pasos: ingresa datos y crea tu primera suma',
            'language'     => 'excel',
            'description'  => <<<'MD'
En este ejercicio darás tus **primeros pasos** en Excel. Aprenderás a:

- Ingresar texto y números en celdas.
- Escribir tu primera **fórmula de suma**.
- Entender la diferencia entre un **valor literal** y una **fórmula**.

Se te presenta una pequeña tabla de productos con sus precios. Tu tarea es completar la celda **B5** con una fórmula que sume todos los precios (B2:B4) para obtener el total.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Ingresa la fórmula en B5 para sumar los precios de los tres productos (B2, B3 y B4).",
  "initialData": {
    "A1": "Producto",
    "B1": "Precio",
    "A2": "Cuaderno",
    "B2": 45,
    "A3": "Lápiz",
    "B3": 12,
    "A4": "Borrador",
    "B4": 8,
    "A5": "Total"
  },
  "expectedFormulas": {
    "B5": "=B2+B3+B4"
  },
  "validate": {
    "B5": 65
  }
}
EXCEL,
        ];

        // ── Lección 2: Formatos de celda ──────────────────────────────────
        if ($l = $lessons->get(2)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Aplica formatos de moneda, porcentaje y fecha',
            'language'     => 'excel',
            'description'  => <<<'MD'
El **formato de celda** permite mostrar los datos de forma legible sin cambiar su valor interno.

En este ejercicio trabajarás con una tabla de ventas donde debes:

- Calcular el **IVA** (16 %) de cada producto en la columna C.
- Calcular el **Total con IVA** en la columna D.
- Los formatos de moneda y porcentaje se aplicarán automáticamente al validar.

Practica cómo las fórmulas interactúan con los formatos numéricos.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Calcula el IVA (16%) en la columna C y el total con IVA en la columna D para cada producto. IVA = Precio * 0.16, Total = Precio + IVA.",
  "initialData": {
    "A1": "Producto",
    "B1": "Precio",
    "C1": "IVA (16%)",
    "D1": "Total",
    "A2": "Teclado",
    "B2": 350,
    "A3": "Mouse",
    "B3": 200,
    "A4": "Monitor",
    "B4": 4500
  },
  "expectedFormulas": {
    "C2": "=B2*0.16",
    "C3": "=B3*0.16",
    "C4": "=B4*0.16",
    "D2": "=B2+C2",
    "D3": "=B3+C3",
    "D4": "=B4+C4"
  },
  "validate": {
    "C2": 56,
    "C3": 32,
    "C4": 720,
    "D2": 406,
    "D3": 232,
    "D4": 5220
  }
}
EXCEL,
        ];

        // ── Lección 3: Fórmulas básicas ───────────────────────────────────
        if ($l = $lessons->get(3)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Operaciones aritméticas: suma, resta, multiplicación y división',
            'language'     => 'excel',
            'description'  => <<<'MD'
Las **fórmulas básicas** en Excel usan los operadores aritméticos:

| Operador | Significado    |
|----------|---------------|
| `+`      | Suma          |
| `-`      | Resta         |
| `*`      | Multiplicación|
| `/`      | División      |

Tienes una tienda con productos. Calcula:
- **Subtotal** (Precio × Cantidad)
- **Descuento** (Subtotal × % Descuento)
- **Total a pagar** (Subtotal − Descuento)
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Calcula el Subtotal (Precio × Cantidad) en D2:D4, el Descuento (Subtotal × %Desc) en E2:E4, y el Total (Subtotal - Descuento) en F2:F4.",
  "initialData": {
    "A1": "Producto",
    "B1": "Precio",
    "C1": "Cantidad",
    "D1": "Subtotal",
    "E1": "Descuento",
    "F1": "Total",
    "A2": "Camisa",
    "B2": 250,
    "C2": 3,
    "G1": "% Desc",
    "G2": 0.10,
    "A3": "Pantalón",
    "B3": 450,
    "C3": 2,
    "A4": "Zapatos",
    "B4": 800,
    "C4": 1
  },
  "expectedFormulas": {
    "D2": "=B2*C2",
    "D3": "=B3*C3",
    "D4": "=B4*C4",
    "E2": "=D2*$G$2",
    "E3": "=D3*$G$2",
    "E4": "=D4*$G$2",
    "F2": "=D2-E2",
    "F3": "=D3-E3",
    "F4": "=D4-E4"
  },
  "validate": {
    "D2": 750,
    "D3": 900,
    "D4": 800,
    "E2": 75,
    "E3": 90,
    "E4": 80,
    "F2": 675,
    "F3": 810,
    "F4": 720
  }
}
EXCEL,
        ];

        // ── Lección 4: Referencias de celda ───────────────────────────────
        if ($l = $lessons->get(4)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Referencias relativas, absolutas y mixtas',
            'language'     => 'excel',
            'description'  => <<<'MD'
Existen tres tipos de **referencias** en Excel:

- **Relativa** (`A1`): se ajusta al copiar la fórmula.
- **Absoluta** (`$A$1`): nunca cambia al copiar.
- **Mixta** (`$A1` o `A$1`): fija solo la columna o la fila.

En este ejercicio tienes una tabla de conversión de divisas. El **tipo de cambio** está en una celda fija (E1). Usa una referencia **absoluta** para que al copiar la fórmula hacia abajo, siempre apunte al tipo de cambio.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Convierte los montos en pesos (columna B) a dólares en la columna C usando el tipo de cambio en E1. Usa referencia absoluta ($E$1) para el tipo de cambio.",
  "initialData": {
    "A1": "Concepto",
    "B1": "Pesos MXN",
    "C1": "Dólares USD",
    "D1": "Tipo de cambio:",
    "E1": 17.5,
    "A2": "Renta",
    "B2": 15000,
    "A3": "Comida",
    "B3": 8000,
    "A4": "Transporte",
    "B4": 3500,
    "A5": "Entretenimiento",
    "B5": 2000,
    "A6": "Ahorro",
    "B6": 5000
  },
  "expectedFormulas": {
    "C2": "=B2/$E$1",
    "C3": "=B3/$E$1",
    "C4": "=B4/$E$1",
    "C5": "=B5/$E$1",
    "C6": "=B6/$E$1"
  },
  "validate": {
    "C2": 857.14,
    "C3": 457.14,
    "C4": 200,
    "C5": 114.29,
    "C6": 285.71
  }
}
EXCEL,
        ];

        // ── Lección 5: Funciones básicas (SUMA, PROMEDIO, etc.) ───────────
        if ($l = $lessons->get(5)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Usa SUMA, PROMEDIO, MAX, MIN y CONTAR',
            'language'     => 'excel',
            'description'  => <<<'MD'
Las **funciones estadísticas básicas** de Excel son esenciales:

| Función      | Descripción                        |
|-------------|-----------------------------------|
| `SUMA`      | Suma todos los valores del rango   |
| `PROMEDIO`  | Calcula la media aritmética        |
| `MAX`       | Devuelve el valor más alto         |
| `MIN`       | Devuelve el valor más bajo         |
| `CONTAR`    | Cuenta celdas con números          |

Tienes las calificaciones de 8 estudiantes. Calcula las estadísticas del grupo en las celdas correspondientes.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Calcula en la fila 11: B11=SUMA de calificaciones, B12=PROMEDIO, B13=Calificación más alta (MAX), B14=Calificación más baja (MIN), B15=Cantidad de alumnos (CONTAR).",
  "initialData": {
    "A1": "Alumno",
    "B1": "Calificación",
    "A2": "Ana García",
    "B2": 92,
    "A3": "Luis Pérez",
    "B3": 78,
    "A4": "María López",
    "B4": 95,
    "A5": "Carlos Ruiz",
    "B5": 64,
    "A6": "Sofía Torres",
    "B6": 88,
    "A7": "Diego Herrera",
    "B7": 71,
    "A8": "Valeria Cruz",
    "B8": 85,
    "A9": "Andrés Morales",
    "B9": 90,
    "A11": "Suma total",
    "A12": "Promedio",
    "A13": "Más alta",
    "A14": "Más baja",
    "A15": "Cantidad"
  },
  "expectedFormulas": {
    "B11": "=SUMA(B2:B9)",
    "B12": "=PROMEDIO(B2:B9)",
    "B13": "=MAX(B2:B9)",
    "B14": "=MIN(B2:B9)",
    "B15": "=CONTAR(B2:B9)"
  },
  "validate": {
    "B11": 663,
    "B12": 82.875,
    "B13": 95,
    "B14": 64,
    "B15": 8
  }
}
EXCEL,
        ];

        // ── Lección 6: Rangos ─────────────────────────────────────────────
        if ($l = $lessons->get(6)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Trabaja con rangos para inventario de almacén',
            'language'     => 'excel',
            'description'  => <<<'MD'
Un **rango** es un grupo de celdas contiguas, como `B2:B10` o `A1:D5`.

Los rangos son fundamentales para:
- Aplicar funciones a múltiples celdas.
- Seleccionar datos para gráficos.
- Definir áreas de impresión.

En este ejercicio gestionarás un **inventario de almacén**. Usa rangos para calcular el valor total de cada categoría y las estadísticas globales.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Calcula el Valor Total (Precio × Stock) en D2:D7. Luego calcula en las celdas indicadas: D8=Valor total del inventario (SUMA de D2:D7), D9=Valor promedio por producto, C10=Stock total (SUMA de C2:C7).",
  "initialData": {
    "A1": "Producto",
    "B1": "Precio Unit.",
    "C1": "Stock",
    "D1": "Valor Total",
    "A2": "Tornillos (caja)",
    "B2": 85,
    "C2": 150,
    "A3": "Martillo",
    "B3": 220,
    "C3": 45,
    "A4": "Pintura (litro)",
    "B4": 180,
    "C4": 80,
    "A5": "Cable (metro)",
    "B5": 35,
    "C5": 500,
    "A6": "Foco LED",
    "B6": 65,
    "C6": 200,
    "A7": "Cinta adhesiva",
    "B7": 42,
    "C7": 120,
    "A8": "Valor inventario",
    "A9": "Valor promedio",
    "A10": "Stock total"
  },
  "expectedFormulas": {
    "D2": "=B2*C2",
    "D3": "=B3*C3",
    "D4": "=B4*C4",
    "D5": "=B5*C5",
    "D6": "=B6*C6",
    "D7": "=B7*C7",
    "D8": "=SUMA(D2:D7)",
    "D9": "=PROMEDIO(D2:D7)",
    "C10": "=SUMA(C2:C7)"
  },
  "validate": {
    "D2": 12750,
    "D3": 9900,
    "D4": 14400,
    "D5": 17500,
    "D6": 13000,
    "D7": 5040,
    "D8": 72590,
    "D9": 12098.33,
    "C10": 1095
  }
}
EXCEL,
        ];

        // ── Lección 7: Ordenar y filtrar ──────────────────────────────────
        if ($l = $lessons->get(7)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Ordena y analiza datos de ventas mensuales',
            'language'     => 'excel',
            'description'  => <<<'MD'
**Ordenar y filtrar** son herramientas fundamentales para analizar datos:

- **Ordenar**: reorganiza los datos por una o más columnas (ascendente o descendente).
- **Filtrar**: muestra solo las filas que cumplen ciertos criterios.

En este ejercicio tienes datos de ventas de una tienda. Calcula los totales y responde preguntas usando funciones que simulan filtros, como `CONTAR.SI` y `SUMAR.SI`.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Calcula: E2:E8 = Ingreso (Precio × Unidades). En la sección de resumen: B11 = total de ingresos, B12 = cantidad de ventas de Electrónica (CONTAR.SI), B13 = ingresos totales de Electrónica (SUMAR.SI), B14 = promedio de unidades vendidas.",
  "initialData": {
    "A1": "Vendedor",
    "B1": "Categoría",
    "C1": "Precio",
    "D1": "Unidades",
    "E1": "Ingreso",
    "A2": "Pedro",
    "B2": "Electrónica",
    "C2": 1200,
    "D2": 5,
    "A3": "Laura",
    "B3": "Ropa",
    "C3": 350,
    "D3": 12,
    "A4": "Pedro",
    "B4": "Electrónica",
    "C4": 800,
    "D4": 8,
    "A5": "María",
    "B5": "Alimentos",
    "C5": 150,
    "D5": 30,
    "A6": "Laura",
    "B6": "Electrónica",
    "C6": 2500,
    "D6": 3,
    "A7": "María",
    "B7": "Ropa",
    "C7": 500,
    "D7": 15,
    "A8": "Pedro",
    "B8": "Alimentos",
    "C8": 200,
    "D8": 20,
    "A10": "Resumen",
    "A11": "Total ingresos",
    "A12": "Ventas Electrónica",
    "A13": "Ingresos Electrónica",
    "A14": "Promedio unidades"
  },
  "expectedFormulas": {
    "E2": "=C2*D2",
    "E3": "=C3*D3",
    "E4": "=C4*D4",
    "E5": "=C5*D5",
    "E6": "=C6*D6",
    "E7": "=C7*D7",
    "E8": "=C8*D8",
    "B11": "=SUMA(E2:E8)",
    "B12": "=CONTAR.SI(B2:B8,\"Electrónica\")",
    "B13": "=SUMAR.SI(B2:B8,\"Electrónica\",E2:E8)",
    "B14": "=PROMEDIO(D2:D8)"
  },
  "validate": {
    "E2": 6000,
    "E3": 4200,
    "E4": 6400,
    "E5": 4500,
    "E6": 7500,
    "E7": 7500,
    "E8": 4000,
    "B11": 40100,
    "B12": 3,
    "B13": 19900,
    "B14": 13.29
  }
}
EXCEL,
        ];

        // ── Lección 8: Gráficos básicos ───────────────────────────────────
        if ($l = $lessons->get(8)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Prepara datos para gráficos de ventas trimestrales',
            'language'     => 'excel',
            'description'  => <<<'MD'
Antes de crear un **gráfico** en Excel, los datos deben estar bien organizados.

Un buen gráfico necesita:
- **Categorías** claras (eje X).
- **Valores numéricos** precisos (eje Y).
- **Totales y porcentajes** para gráficos circulares.

Prepara los datos de ventas trimestrales calculando totales por producto y por trimestre, además de los porcentajes de participación de cada producto.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Calcula: F2:F4 = Total anual por producto (suma de los 4 trimestres). B5:E5 = Total por trimestre. F5 = Gran total. G2:G4 = Porcentaje de participación de cada producto (Total producto / Gran total).",
  "initialData": {
    "A1": "Producto",
    "B1": "T1",
    "C1": "T2",
    "D1": "T3",
    "E1": "T4",
    "F1": "Total",
    "G1": "% Participación",
    "A2": "Laptops",
    "B2": 45000,
    "C2": 52000,
    "D2": 48000,
    "E2": 61000,
    "A3": "Tablets",
    "B3": 22000,
    "C3": 28000,
    "D3": 31000,
    "E3": 35000,
    "A4": "Accesorios",
    "B4": 15000,
    "C4": 18000,
    "D4": 20000,
    "E4": 24000,
    "A5": "Total trimestre"
  },
  "expectedFormulas": {
    "F2": "=SUMA(B2:E2)",
    "F3": "=SUMA(B3:E3)",
    "F4": "=SUMA(B4:E4)",
    "B5": "=SUMA(B2:B4)",
    "C5": "=SUMA(C2:C4)",
    "D5": "=SUMA(D2:D4)",
    "E5": "=SUMA(E2:E4)",
    "F5": "=SUMA(F2:F4)",
    "G2": "=F2/$F$5",
    "G3": "=F3/$F$5",
    "G4": "=F4/$F$5"
  },
  "validate": {
    "F2": 206000,
    "F3": 116000,
    "F4": 77000,
    "B5": 82000,
    "C5": 98000,
    "D5": 99000,
    "E5": 120000,
    "F5": 399000,
    "G2": 0.5163,
    "G3": 0.2908,
    "G4": 0.1929
  }
}
EXCEL,
        ];

        // ── Lección 9: Función SI ─────────────────────────────────────────
        if ($l = $lessons->get(9)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Evalúa rendimiento de empleados con SI y SI anidado',
            'language'     => 'excel',
            'description'  => <<<'MD'
La función **SI** permite tomar decisiones en la hoja de cálculo:

```
=SI(condición, valor_si_verdadero, valor_si_falso)
```

También puedes **anidar** funciones SI para evaluar múltiples condiciones:

```
=SI(condición1, resultado1, SI(condición2, resultado2, resultado3))
```

Tienes una tabla de empleados con sus ventas mensuales. Determina:
- Si alcanzaron la **meta** (≥ 50000).
- Su **nivel de desempeño**: Excelente (≥80000), Bueno (≥50000), o Insuficiente (<50000).
- El **bono** correspondiente: 15% si Excelente, 8% si Bueno, 0% si Insuficiente.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Completa: C2:C6 = SI las ventas >= 50000 muestra 'Sí', sino 'No'. D2:D6 = SI anidado: >=80000 → 'Excelente', >=50000 → 'Bueno', sino 'Insuficiente'. E2:E6 = Bono: SI >=80000 → Ventas*0.15, SI >=50000 → Ventas*0.08, sino 0.",
  "initialData": {
    "A1": "Empleado",
    "B1": "Ventas",
    "C1": "¿Meta?",
    "D1": "Desempeño",
    "E1": "Bono",
    "A2": "Roberto Silva",
    "B2": 92000,
    "A3": "Ana Martínez",
    "B3": 55000,
    "A4": "Jorge Díaz",
    "B4": 38000,
    "A5": "Claudia Vera",
    "B5": 81000,
    "A6": "Miguel Soto",
    "B6": 47000
  },
  "expectedFormulas": {
    "C2": "=SI(B2>=50000,\"Sí\",\"No\")",
    "C3": "=SI(B3>=50000,\"Sí\",\"No\")",
    "C4": "=SI(B4>=50000,\"Sí\",\"No\")",
    "C5": "=SI(B5>=50000,\"Sí\",\"No\")",
    "C6": "=SI(B6>=50000,\"Sí\",\"No\")",
    "D2": "=SI(B2>=80000,\"Excelente\",SI(B2>=50000,\"Bueno\",\"Insuficiente\"))",
    "D3": "=SI(B3>=80000,\"Excelente\",SI(B3>=50000,\"Bueno\",\"Insuficiente\"))",
    "D4": "=SI(B4>=80000,\"Excelente\",SI(B4>=50000,\"Bueno\",\"Insuficiente\"))",
    "D5": "=SI(B5>=80000,\"Excelente\",SI(B5>=50000,\"Bueno\",\"Insuficiente\"))",
    "D6": "=SI(B6>=80000,\"Excelente\",SI(B6>=50000,\"Bueno\",\"Insuficiente\"))",
    "E2": "=SI(B2>=80000,B2*0.15,SI(B2>=50000,B2*0.08,0))",
    "E3": "=SI(B3>=80000,B3*0.15,SI(B3>=50000,B3*0.08,0))",
    "E4": "=SI(B4>=80000,B4*0.15,SI(B4>=50000,B4*0.08,0))",
    "E5": "=SI(B5>=80000,B5*0.15,SI(B5>=50000,B5*0.08,0))",
    "E6": "=SI(B6>=80000,B6*0.15,SI(B6>=50000,B6*0.08,0))"
  },
  "validate": {
    "C2": "Sí",
    "C3": "Sí",
    "C4": "No",
    "C5": "Sí",
    "C6": "No",
    "D2": "Excelente",
    "D3": "Bueno",
    "D4": "Insuficiente",
    "D5": "Excelente",
    "D6": "Insuficiente",
    "E2": 13800,
    "E3": 4400,
    "E4": 0,
    "E5": 12150,
    "E6": 0
  }
}
EXCEL,
        ];

        // ── Lección 10: Funciones de texto ────────────────────────────────
        if ($l = $lessons->get(10)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Manipula texto con IZQUIERDA, DERECHA, EXTRAE y CONCATENAR',
            'language'     => 'excel',
            'description'  => <<<'MD'
Las **funciones de texto** permiten manipular cadenas de caracteres:

| Función         | Descripción                               |
|----------------|------------------------------------------|
| `IZQUIERDA`    | Extrae caracteres desde la izquierda      |
| `DERECHA`      | Extrae caracteres desde la derecha        |
| `EXTRAE`       | Extrae caracteres desde una posición      |
| `CONCATENAR`   | Une varios textos en uno solo             |
| `LARGO`        | Devuelve la longitud del texto            |
| `MAYUSC`       | Convierte a mayúsculas                    |

Tienes una lista de códigos de empleados con formato `DEPT-NNNN-SEDE`. Extrae cada parte del código.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Extrae de los códigos en A2:A5: B = Departamento (primeros 3 caracteres con IZQUIERDA), C = Número de empleado (caracteres 5 al 8 con EXTRAE), D = Sede (últimos 3 caracteres con DERECHA). En E genera el nombre completo: CONCATENAR(Nombre, ' - ', Departamento).",
  "initialData": {
    "A1": "Código",
    "B1": "Departamento",
    "C1": "Nº Empleado",
    "D1": "Sede",
    "E1": "Nombre completo",
    "F1": "Nombre",
    "A2": "VEN-1042-MTY",
    "F2": "Roberto Silva",
    "A3": "MKT-2085-GDL",
    "F3": "Ana Martínez",
    "A4": "TEC-3021-CDM",
    "F4": "Jorge Díaz",
    "A5": "RHH-4510-MTY",
    "F5": "Claudia Vera"
  },
  "expectedFormulas": {
    "B2": "=IZQUIERDA(A2,3)",
    "B3": "=IZQUIERDA(A3,3)",
    "B4": "=IZQUIERDA(A4,3)",
    "B5": "=IZQUIERDA(A5,3)",
    "C2": "=EXTRAE(A2,5,4)",
    "C3": "=EXTRAE(A3,5,4)",
    "C4": "=EXTRAE(A4,5,4)",
    "C5": "=EXTRAE(A5,5,4)",
    "D2": "=DERECHA(A2,3)",
    "D3": "=DERECHA(A3,3)",
    "D4": "=DERECHA(A4,3)",
    "D5": "=DERECHA(A5,3)",
    "E2": "=CONCATENAR(F2,\" - \",B2)",
    "E3": "=CONCATENAR(F3,\" - \",B3)",
    "E4": "=CONCATENAR(F4,\" - \",B4)",
    "E5": "=CONCATENAR(F5,\" - \",B5)"
  },
  "validate": {
    "B2": "VEN",
    "B3": "MKT",
    "B4": "TEC",
    "B5": "RHH",
    "C2": "1042",
    "C3": "2085",
    "C4": "3021",
    "C5": "4510",
    "D2": "MTY",
    "D3": "GDL",
    "D4": "CDM",
    "D5": "MTY",
    "E2": "Roberto Silva - VEN",
    "E3": "Ana Martínez - MKT",
    "E4": "Jorge Díaz - TEC",
    "E5": "Claudia Vera - RHH"
  }
}
EXCEL,
        ];

        // ── Lección 11: Funciones de fecha ────────────────────────────────
        if ($l = $lessons->get(11)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Calcula antigüedad y vencimientos con funciones de fecha',
            'language'     => 'excel',
            'description'  => <<<'MD'
Las **funciones de fecha** en Excel permiten trabajar con fechas de forma dinámica:

| Función      | Descripción                          |
|-------------|-------------------------------------|
| `HOY`       | Fecha actual                         |
| `AÑO`       | Extrae el año de una fecha           |
| `MES`       | Extrae el mes de una fecha           |
| `DIA`       | Extrae el día de una fecha           |
| `DIAS`      | Días entre dos fechas                |
| `FECHA`     | Crea una fecha a partir de componentes |

Tienes una lista de contratos. Calcula la fecha de vencimiento, los días restantes y el año de inicio.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Calcula: C2:C5 = Fecha vencimiento (Fecha inicio + Duración en días, columna E). D2:D5 = Año de inicio (función AÑO). F2:F5 = Mes de inicio (función MES). La columna E tiene la duración de cada contrato en días.",
  "initialData": {
    "A1": "Contrato",
    "B1": "Fecha inicio",
    "C1": "Fecha vencimiento",
    "D1": "Año inicio",
    "E1": "Duración (días)",
    "F1": "Mes inicio",
    "A2": "Servicio A",
    "B2": "2026-01-15",
    "E2": 365,
    "A3": "Servicio B",
    "B3": "2025-06-01",
    "E3": 180,
    "A4": "Servicio C",
    "B4": "2026-03-10",
    "E4": 90,
    "A5": "Servicio D",
    "B5": "2025-11-20",
    "E5": 730
  },
  "expectedFormulas": {
    "C2": "=B2+E2",
    "C3": "=B3+E3",
    "C4": "=B4+E4",
    "C5": "=B5+E5",
    "D2": "=AÑO(B2)",
    "D3": "=AÑO(B3)",
    "D4": "=AÑO(B4)",
    "D5": "=AÑO(B5)",
    "F2": "=MES(B2)",
    "F3": "=MES(B3)",
    "F4": "=MES(B4)",
    "F5": "=MES(B5)"
  },
  "validate": {
    "D2": 2026,
    "D3": 2025,
    "D4": 2026,
    "D5": 2025,
    "F2": 1,
    "F3": 6,
    "F4": 3,
    "F5": 11
  }
}
EXCEL,
        ];

        // ── Lección 12: Tablas de Excel ───────────────────────────────────
        if ($l = $lessons->get(12)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Crea una tabla de datos de empleados con totales estructurados',
            'language'     => 'excel',
            'description'  => <<<'MD'
Las **Tablas de Excel** (Ctrl+T) convierten un rango en una estructura dinámica con ventajas:

- **Fila de totales** automática.
- **Referencias estructuradas** (ej. `[@Salario]`).
- **Autoexpansión** al agregar filas.
- **Estilos** alternados para mejor lectura.

En este ejercicio organizarás datos de empleados como si fuera una tabla formal. Calcula salario neto, deducciones y los resúmenes de la tabla.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Calcula: D2:D7 = Deducción (Salario bruto × Tasa deducción en F1). E2:E7 = Salario neto (Bruto - Deducción). Resumen: B9 = Total salarios brutos, D9 = Total deducciones, E9 = Total salarios netos. E10 = Promedio salario neto.",
  "initialData": {
    "A1": "Empleado",
    "B1": "Depto",
    "C1": "Salario bruto",
    "D1": "Deducción",
    "E1": "Salario neto",
    "F1": 0.12,
    "G1": "Tasa deducción",
    "A2": "Elena Ríos",
    "B2": "Ventas",
    "C2": 28000,
    "A3": "Tomás Franco",
    "B3": "TI",
    "C3": 42000,
    "A4": "Lucía Navarro",
    "B4": "RH",
    "C4": 31000,
    "A5": "Raúl Mendoza",
    "B5": "Ventas",
    "C5": 27000,
    "A6": "Patricia León",
    "B6": "TI",
    "C6": 45000,
    "A7": "Fernando Ruiz",
    "B7": "RH",
    "C7": 33000,
    "A9": "TOTALES",
    "A10": "PROMEDIO NETO"
  },
  "expectedFormulas": {
    "D2": "=C2*$F$1",
    "D3": "=C3*$F$1",
    "D4": "=C4*$F$1",
    "D5": "=C5*$F$1",
    "D6": "=C6*$F$1",
    "D7": "=C7*$F$1",
    "E2": "=C2-D2",
    "E3": "=C3-D3",
    "E4": "=C4-D4",
    "E5": "=C5-D5",
    "E6": "=C6-D6",
    "E7": "=C7-D7",
    "B9": "=SUMA(C2:C7)",
    "D9": "=SUMA(D2:D7)",
    "E9": "=SUMA(E2:E7)",
    "E10": "=PROMEDIO(E2:E7)"
  },
  "validate": {
    "D2": 3360,
    "D3": 5040,
    "D4": 3720,
    "D5": 3240,
    "D6": 5400,
    "D7": 3960,
    "E2": 24640,
    "E3": 36960,
    "E4": 27280,
    "E5": 23760,
    "E6": 39600,
    "E7": 29040,
    "B9": 206000,
    "D9": 24720,
    "E9": 181280,
    "E10": 30213.33
  }
}
EXCEL,
        ];

        // ── Lección 13: Impresión ─────────────────────────────────────────
        if ($l = $lessons->get(13)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Prepara un reporte de ventas listo para imprimir',
            'language'     => 'excel',
            'description'  => <<<'MD'
Antes de **imprimir** una hoja de cálculo, debes asegurarte de que los datos estén completos y bien organizados.

Consideraciones para impresión:
- **Encabezados** claros en la primera fila.
- **Totales** visibles al final.
- **Porcentajes** calculados para contexto.
- Datos **ordenados** lógicamente.

Prepara este reporte de ventas regionales calculando los totales y porcentajes de participación para que quede listo para presentar.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Completa el reporte: D2:D6 = Venta Total (Precio promedio × Unidades). D7 = Gran total de ventas. E2:E6 = Porcentaje de participación (Venta de la región / Gran total). E7 debe ser 1 (o 100% = suma de porcentajes).",
  "initialData": {
    "A1": "Región",
    "B1": "Precio Promedio",
    "C1": "Unidades",
    "D1": "Venta Total",
    "E1": "% Participación",
    "A2": "Norte",
    "B2": 520,
    "C2": 180,
    "A3": "Sur",
    "B3": 480,
    "C3": 220,
    "A4": "Centro",
    "B4": 610,
    "C4": 310,
    "A5": "Este",
    "B5": 390,
    "C5": 150,
    "A6": "Oeste",
    "B6": 550,
    "C6": 200,
    "A7": "TOTAL"
  },
  "expectedFormulas": {
    "D2": "=B2*C2",
    "D3": "=B3*C3",
    "D4": "=B4*C4",
    "D5": "=B5*C5",
    "D6": "=B6*C6",
    "D7": "=SUMA(D2:D6)",
    "E2": "=D2/$D$7",
    "E3": "=D3/$D$7",
    "E4": "=D4/$D$7",
    "E5": "=D5/$D$7",
    "E6": "=D6/$D$7",
    "E7": "=SUMA(E2:E6)"
  },
  "validate": {
    "D2": 93600,
    "D3": 105600,
    "D4": 189100,
    "D5": 58500,
    "D6": 110000,
    "D7": 556800,
    "E2": 0.1681,
    "E3": 0.1896,
    "E4": 0.3396,
    "E5": 0.1050,
    "E6": 0.1975,
    "E7": 1
  }
}
EXCEL,
        ];

        // ── Lección 14: Formato condicional ───────────────────────────────
        if ($l = $lessons->get(14)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Clasifica el rendimiento académico con formato condicional',
            'language'     => 'excel',
            'description'  => <<<'MD'
El **formato condicional** resalta celdas según reglas. Aunque el formato visual se configura en el menú, las fórmulas subyacentes son la base.

En este ejercicio crearás las fórmulas que clasifican el rendimiento de estudiantes:

- **Promedio** de tres exámenes.
- **Estado**: Aprobado (≥70) o Reprobado (<70).
- **Distinción**: "Con honores" si promedio ≥90, "Regular" si ≥70, "En riesgo" si <70.
- **Puntos extra**: 5 puntos si tuvo asistencia perfecta (columna F = "Sí").
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Calcula: E2:E7 = Promedio de los 3 exámenes (B, C, D). G2:G7 = Estado: SI(Promedio>=70, 'Aprobado', 'Reprobado'). H2:H7 = Distinción con SI anidado (>=90 → 'Con honores', >=70 → 'Regular', 'En riesgo'). I2:I7 = Puntos extra: SI(F='Sí', 5, 0).",
  "initialData": {
    "A1": "Alumno",
    "B1": "Examen 1",
    "C1": "Examen 2",
    "D1": "Examen 3",
    "E1": "Promedio",
    "F1": "Asistencia perfecta",
    "G1": "Estado",
    "H1": "Distinción",
    "I1": "Puntos extra",
    "A2": "Daniela Ortiz",
    "B2": 95,
    "C2": 88,
    "D2": 92,
    "F2": "Sí",
    "A3": "Marcos Luna",
    "B3": 72,
    "C3": 65,
    "D3": 78,
    "F3": "No",
    "A4": "Isabella Reyes",
    "B4": 55,
    "C4": 60,
    "D4": 58,
    "F4": "Sí",
    "A5": "Santiago Flores",
    "B5": 91,
    "C5": 94,
    "D5": 89,
    "F5": "Sí",
    "A6": "Camila Herrera",
    "B6": 80,
    "C6": 75,
    "D6": 70,
    "F6": "No",
    "A7": "Emilio Castro",
    "B7": 68,
    "C7": 72,
    "D7": 65,
    "F7": "No"
  },
  "expectedFormulas": {
    "E2": "=PROMEDIO(B2:D2)",
    "E3": "=PROMEDIO(B3:D3)",
    "E4": "=PROMEDIO(B4:D4)",
    "E5": "=PROMEDIO(B5:D5)",
    "E6": "=PROMEDIO(B6:D6)",
    "E7": "=PROMEDIO(B7:D7)",
    "G2": "=SI(E2>=70,\"Aprobado\",\"Reprobado\")",
    "G3": "=SI(E3>=70,\"Aprobado\",\"Reprobado\")",
    "G4": "=SI(E4>=70,\"Aprobado\",\"Reprobado\")",
    "G5": "=SI(E5>=70,\"Aprobado\",\"Reprobado\")",
    "G6": "=SI(E6>=70,\"Aprobado\",\"Reprobado\")",
    "G7": "=SI(E7>=70,\"Aprobado\",\"Reprobado\")",
    "H2": "=SI(E2>=90,\"Con honores\",SI(E2>=70,\"Regular\",\"En riesgo\"))",
    "H3": "=SI(E3>=90,\"Con honores\",SI(E3>=70,\"Regular\",\"En riesgo\"))",
    "H4": "=SI(E4>=90,\"Con honores\",SI(E4>=70,\"Regular\",\"En riesgo\"))",
    "H5": "=SI(E5>=90,\"Con honores\",SI(E5>=70,\"Regular\",\"En riesgo\"))",
    "H6": "=SI(E6>=90,\"Con honores\",SI(E6>=70,\"Regular\",\"En riesgo\"))",
    "H7": "=SI(E7>=90,\"Con honores\",SI(E7>=70,\"Regular\",\"En riesgo\"))",
    "I2": "=SI(F2=\"Sí\",5,0)",
    "I3": "=SI(F3=\"Sí\",5,0)",
    "I4": "=SI(F4=\"Sí\",5,0)",
    "I5": "=SI(F5=\"Sí\",5,0)",
    "I6": "=SI(F6=\"Sí\",5,0)",
    "I7": "=SI(F7=\"Sí\",5,0)"
  },
  "validate": {
    "E2": 91.67,
    "E3": 71.67,
    "E4": 57.67,
    "E5": 91.33,
    "E6": 75,
    "E7": 68.33,
    "G2": "Aprobado",
    "G3": "Aprobado",
    "G4": "Reprobado",
    "G5": "Aprobado",
    "G6": "Aprobado",
    "G7": "Reprobado",
    "H2": "Con honores",
    "H3": "Regular",
    "H4": "En riesgo",
    "H5": "Con honores",
    "H6": "Regular",
    "H7": "En riesgo",
    "I2": 5,
    "I3": 0,
    "I4": 5,
    "I5": 5,
    "I6": 0,
    "I7": 0
  }
}
EXCEL,
        ];

        // ── Lección 15: Hojas múltiples ───────────────────────────────────
        if ($l = $lessons->get(15)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Consolida datos de sucursales desde múltiples hojas',
            'language'     => 'excel',
            'description'  => <<<'MD'
Cuando trabajas con **hojas múltiples**, puedes hacer referencia a datos de otras hojas usando la sintaxis:

```
=NombreHoja!Celda
```

Por ejemplo: `=Enero!B5` trae el valor de B5 de la hoja "Enero".

En este ejercicio simularemos un resumen que consolida las ventas de 3 sucursales. Los datos de cada sucursal están representados en columnas separadas, y tú debes crear las fórmulas de consolidación como si estuvieras referenciando hojas diferentes.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Simula consolidación de hojas: E2:E5 = Suma de las 3 sucursales por producto (B+C+D). E6 = Gran total. F2:F5 = Porcentaje del producto sobre el gran total. B6:D6 = Total por sucursal. G2 = Sucursal con mayor venta de Laptops (SI anidado).",
  "initialData": {
    "A1": "Producto",
    "B1": "Sucursal Norte",
    "C1": "Sucursal Centro",
    "D1": "Sucursal Sur",
    "E1": "Total",
    "F1": "% del Total",
    "A2": "Laptops",
    "B2": 85000,
    "C2": 120000,
    "D2": 65000,
    "A3": "Teléfonos",
    "B3": 95000,
    "C3": 88000,
    "D3": 102000,
    "A4": "Tablets",
    "B4": 42000,
    "C4": 55000,
    "D4": 38000,
    "A5": "Accesorios",
    "B5": 28000,
    "C5": 35000,
    "D5": 22000,
    "A6": "Total sucursal"
  },
  "expectedFormulas": {
    "E2": "=B2+C2+D2",
    "E3": "=B3+C3+D3",
    "E4": "=B4+C4+D4",
    "E5": "=B5+C5+D5",
    "E6": "=SUMA(E2:E5)",
    "F2": "=E2/$E$6",
    "F3": "=E3/$E$6",
    "F4": "=E4/$E$6",
    "F5": "=E5/$E$6",
    "B6": "=SUMA(B2:B5)",
    "C6": "=SUMA(C2:C5)",
    "D6": "=SUMA(D2:D5)",
    "G2": "=SI(C2>=B2,SI(C2>=D2,\"Centro\",\"Sur\"),SI(B2>=D2,\"Norte\",\"Sur\"))"
  },
  "validate": {
    "E2": 270000,
    "E3": 285000,
    "E4": 135000,
    "E5": 85000,
    "E6": 775000,
    "F2": 0.3484,
    "F3": 0.3677,
    "F4": 0.1742,
    "F5": 0.1097,
    "B6": 250000,
    "C6": 298000,
    "D6": 227000,
    "G2": "Centro"
  }
}
EXCEL,
        ];

        // ── Lección 16: Validación de datos ───────────────────────────────
        if ($l = $lessons->get(16)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Valida entradas de un formulario de pedidos',
            'language'     => 'excel',
            'description'  => <<<'MD'
La **validación de datos** garantiza que las entradas sean correctas. En Excel puedes usar fórmulas para verificar valores.

Funciones útiles para validación:

| Función       | Descripción                                 |
|--------------|---------------------------------------------|
| `SI`         | Verifica una condición                       |
| `Y`          | Todas las condiciones deben ser verdaderas   |
| `O`          | Al menos una condición debe ser verdadera    |
| `ESNUMERO`   | Verifica si el valor es un número            |
| `LARGO`      | Verifica la longitud del texto               |

Crea fórmulas que validen un formulario de pedidos: cantidades positivas, códigos con formato correcto y fechas válidas.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Valida los pedidos: E2:E5 = Subtotal (Precio × Cantidad). F2:F5 = ¿Cantidad válida? SI(Y(C>0, C<=100), 'OK', 'Error'). G2:G5 = ¿Pedido completo? SI(Y(Cantidad válida='OK', Subtotal>0), 'Listo', 'Revisar'). H6 = Total de pedidos válidos (CONTAR.SI en F2:F5 que sean 'OK'). E6 = Suma total de subtotales.",
  "initialData": {
    "A1": "Código",
    "B1": "Producto",
    "C1": "Cantidad",
    "D1": "Precio",
    "E1": "Subtotal",
    "F1": "¿Cant. válida?",
    "G1": "Estado",
    "A2": "P-001",
    "B2": "Impresora",
    "C2": 5,
    "D2": 3500,
    "A3": "P-002",
    "B3": "Tóner",
    "C3": 50,
    "D3": 450,
    "A4": "P-003",
    "B4": "Papel (resma)",
    "C4": -3,
    "D4": 120,
    "A5": "P-004",
    "B5": "USB 32GB",
    "C5": 150,
    "D5": 180,
    "A6": "Totales",
    "H1": "Pedidos OK"
  },
  "expectedFormulas": {
    "E2": "=C2*D2",
    "E3": "=C3*D3",
    "E4": "=C4*D4",
    "E5": "=C5*D5",
    "F2": "=SI(Y(C2>0,C2<=100),\"OK\",\"Error\")",
    "F3": "=SI(Y(C3>0,C3<=100),\"OK\",\"Error\")",
    "F4": "=SI(Y(C4>0,C4<=100),\"OK\",\"Error\")",
    "F5": "=SI(Y(C5>0,C5<=100),\"OK\",\"Error\")",
    "G2": "=SI(Y(F2=\"OK\",E2>0),\"Listo\",\"Revisar\")",
    "G3": "=SI(Y(F3=\"OK\",E3>0),\"Listo\",\"Revisar\")",
    "G4": "=SI(Y(F4=\"OK\",E4>0),\"Listo\",\"Revisar\")",
    "G5": "=SI(Y(F5=\"OK\",E5>0),\"Listo\",\"Revisar\")",
    "H6": "=CONTAR.SI(F2:F5,\"OK\")",
    "E6": "=SUMA(E2:E5)"
  },
  "validate": {
    "E2": 17500,
    "E3": 22500,
    "E4": -360,
    "E5": 27000,
    "F2": "OK",
    "F3": "OK",
    "F4": "Error",
    "F5": "Error",
    "G2": "Listo",
    "G3": "Listo",
    "G4": "Revisar",
    "G5": "Revisar",
    "H6": 2,
    "E6": 66640
  }
}
EXCEL,
        ];

        // ── Lección 17: Atajos de teclado ─────────────────────────────────
        if ($l = $lessons->get(17)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Practica productividad con fórmulas rápidas',
            'language'     => 'excel',
            'description'  => <<<'MD'
Los **atajos de teclado** aceleran tu trabajo en Excel. Para practicarlos, resolverás un ejercicio que requiere velocidad y eficiencia.

Atajos esenciales:
- **Ctrl+C / Ctrl+V**: Copiar y pegar.
- **Ctrl+D**: Copiar celda de arriba.
- **F2**: Editar celda activa.
- **Ctrl+Shift+L**: Activar filtros.
- **Alt+=**: Autosuma rápida.
- **Ctrl+;**: Insertar fecha actual.
- **Tab**: Mover a la derecha.

Completa rápidamente esta tabla de gastos semanales usando las fórmulas que normalmente copiarías con atajos.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Calcula: F2:F6 = Total semanal por categoría (suma de Lunes a Viernes). B7:F7 = Total diario (suma de todas las categorías). G2:G6 = Promedio diario por categoría. G7 = Promedio general de los totales diarios.",
  "initialData": {
    "A1": "Categoría",
    "B1": "Lunes",
    "C1": "Martes",
    "D1": "Miércoles",
    "E1": "Jueves",
    "F1": "Total",
    "G1": "Promedio/día",
    "A2": "Comida",
    "B2": 120,
    "C2": 95,
    "D2": 110,
    "E2": 85,
    "A3": "Transporte",
    "B3": 50,
    "C3": 50,
    "D3": 65,
    "E3": 50,
    "A4": "Café",
    "B4": 45,
    "C4": 35,
    "D4": 45,
    "E4": 40,
    "A5": "Snacks",
    "B5": 30,
    "C5": 25,
    "D5": 35,
    "E5": 20,
    "A6": "Otros",
    "B6": 0,
    "C6": 80,
    "D6": 0,
    "E6": 150,
    "A7": "Total día"
  },
  "expectedFormulas": {
    "F2": "=SUMA(B2:E2)",
    "F3": "=SUMA(B3:E3)",
    "F4": "=SUMA(B4:E4)",
    "F5": "=SUMA(B5:E5)",
    "F6": "=SUMA(B6:E6)",
    "B7": "=SUMA(B2:B6)",
    "C7": "=SUMA(C2:C6)",
    "D7": "=SUMA(D2:D6)",
    "E7": "=SUMA(E2:E6)",
    "F7": "=SUMA(F2:F6)",
    "G2": "=PROMEDIO(B2:E2)",
    "G3": "=PROMEDIO(B3:E3)",
    "G4": "=PROMEDIO(B4:E4)",
    "G5": "=PROMEDIO(B5:E5)",
    "G6": "=PROMEDIO(B6:E6)",
    "G7": "=PROMEDIO(B7:E7)"
  },
  "validate": {
    "F2": 410,
    "F3": 215,
    "F4": 165,
    "F5": 110,
    "F6": 230,
    "B7": 245,
    "C7": 285,
    "D7": 255,
    "E7": 345,
    "F7": 1130,
    "G2": 102.5,
    "G3": 53.75,
    "G4": 41.25,
    "G5": 27.5,
    "G6": 57.5,
    "G7": 282.5
  }
}
EXCEL,
        ];

        // ── Lección 18: Protección ────────────────────────────────────────
        if ($l = $lessons->get(18)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Crea una cotización protegida con celdas de solo lectura',
            'language'     => 'excel',
            'description'  => <<<'MD'
La **protección** en Excel permite bloquear celdas para evitar cambios accidentales.

Conceptos clave:
- Las celdas con **fórmulas** generalmente se protegen.
- Las celdas de **entrada** se dejan desbloqueadas.
- La protección se activa en **Revisar → Proteger hoja**.

En este ejercicio crearás una **cotización profesional** con fórmulas protegidas. El usuario solo podrá modificar las cantidades y precios, mientras que los cálculos estarán bloqueados.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Crea la cotización: E2:E5 = Subtotal (Cantidad × Precio). E6 = Subtotal general (SUMA). E7 = IVA (Subtotal × 16%). E8 = Total (Subtotal + IVA). E9 = Descuento (SI el total > 10000, Total × 5%, sino 0). E10 = Total final (Total - Descuento).",
  "initialData": {
    "A1": "Servicio",
    "B1": "Descripción",
    "C1": "Cantidad",
    "D1": "Precio Unit.",
    "E1": "Subtotal",
    "A2": "Diseño web",
    "B2": "Página corporativa",
    "C2": 1,
    "D2": 8000,
    "A3": "Hosting",
    "B3": "Anual",
    "C3": 1,
    "D3": 2400,
    "A4": "Dominio",
    "B4": ".com.mx",
    "C4": 2,
    "D4": 350,
    "A5": "Soporte",
    "B5": "Mensual (3 meses)",
    "C5": 3,
    "D5": 1500,
    "A6": "Subtotal",
    "A7": "IVA (16%)",
    "A8": "Total",
    "A9": "Descuento (>10000)",
    "A10": "TOTAL FINAL"
  },
  "expectedFormulas": {
    "E2": "=C2*D2",
    "E3": "=C3*D3",
    "E4": "=C4*D4",
    "E5": "=C5*D5",
    "E6": "=SUMA(E2:E5)",
    "E7": "=E6*0.16",
    "E8": "=E6+E7",
    "E9": "=SI(E8>10000,E8*0.05,0)",
    "E10": "=E8-E9"
  },
  "validate": {
    "E2": 8000,
    "E3": 2400,
    "E4": 700,
    "E5": 4500,
    "E6": 15600,
    "E7": 2496,
    "E8": 18096,
    "E9": 904.8,
    "E10": 17191.2
  }
}
EXCEL,
        ];

        // ── Lección 19: Proyecto final (Presupuesto) ──────────────────────
        if ($l = $lessons->get(19)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Proyecto final: Presupuesto mensual personal completo',
            'language'     => 'excel',
            'description'  => <<<'MD'
## Proyecto Final: Presupuesto Mensual Personal

En este proyecto integrador aplicarás **todas las habilidades** aprendidas:

- **Fórmulas básicas** y funciones (SUMA, PROMEDIO, MAX, MIN).
- **Función SI** para alertas y clasificaciones.
- **Referencias absolutas** para porcentajes.
- **Funciones de texto** para etiquetas dinámicas.
- **Formato condicional** (fórmulas de clasificación).

Construirás un presupuesto mensual completo con:
1. **Ingresos** detallados.
2. **Gastos fijos** (renta, servicios, transporte).
3. **Gastos variables** (comida, entretenimiento, ropa).
4. **Resumen** con balance, porcentajes y alertas.
5. **Análisis** con clasificación del estado financiero.

¡Demuestra todo lo que aprendiste!
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Construye un presupuesto completo. INGRESOS: B3=Total ingresos (SUMA B4:B6). GASTOS FIJOS: B9=Total gastos fijos (SUMA B10:B14). GASTOS VARIABLES: B17=Total gastos variables (SUMA B18:B22). RESUMEN: B25=Total ingresos (=B3), B26=Total gastos (=B9+B17), B27=Balance (=B25-B26), B28=% Gastos fijos sobre ingresos (=B9/B25), B29=% Gastos variables sobre ingresos (=B17/B25), B30=% Ahorro (=B27/B25). ANÁLISIS: C27=SI(B27>0,'Superávit','Déficit'), C30=SI anidado: >=20% → 'Excelente', >=10% → 'Aceptable', 'Insuficiente'. D25=MAX gasto fijo (MAX B10:B14), D26=MIN gasto variable (MIN B18:B22), D27=Promedio gastos fijos (PROMEDIO B10:B14), D28=Cantidad de gastos fijos (CONTAR B10:B14).",
  "initialData": {
    "A1": "PRESUPUESTO MENSUAL",
    "A2": "═══ INGRESOS ═══",
    "A3": "Total Ingresos",
    "A4": "Salario",
    "B4": 25000,
    "A5": "Freelance",
    "B5": 8000,
    "A6": "Inversiones",
    "B6": 2000,
    "A8": "═══ GASTOS FIJOS ═══",
    "A9": "Total Gastos Fijos",
    "A10": "Renta",
    "B10": 7500,
    "A11": "Servicios (luz, agua, gas)",
    "B11": 2200,
    "A12": "Internet y teléfono",
    "B12": 800,
    "A13": "Transporte",
    "B13": 2500,
    "A14": "Seguro médico",
    "B14": 1800,
    "A16": "═══ GASTOS VARIABLES ═══",
    "A17": "Total Gastos Variables",
    "A18": "Alimentación",
    "B18": 5500,
    "A19": "Entretenimiento",
    "B19": 2000,
    "A20": "Ropa",
    "B20": 1500,
    "A21": "Educación",
    "B21": 1200,
    "A22": "Imprevistos",
    "B22": 800,
    "A24": "═══ RESUMEN ═══",
    "A25": "Total Ingresos",
    "A26": "Total Gastos",
    "A27": "Balance",
    "A28": "% Gastos fijos",
    "A29": "% Gastos variables",
    "A30": "% Ahorro",
    "C1": "Estado",
    "D1": "Análisis",
    "D24": "═══ ANÁLISIS ═══",
    "D25": "Mayor gasto fijo",
    "D26": "Menor gasto variable",
    "D27": "Promedio g. fijos",
    "D28": "Nº gastos fijos"
  },
  "expectedFormulas": {
    "B3": "=SUMA(B4:B6)",
    "B9": "=SUMA(B10:B14)",
    "B17": "=SUMA(B18:B22)",
    "B25": "=B3",
    "B26": "=B9+B17",
    "B27": "=B25-B26",
    "B28": "=B9/B25",
    "B29": "=B17/B25",
    "B30": "=B27/B25",
    "C27": "=SI(B27>0,\"Superávit\",\"Déficit\")",
    "C30": "=SI(B30>=0.2,\"Excelente\",SI(B30>=0.1,\"Aceptable\",\"Insuficiente\"))",
    "E25": "=MAX(B10:B14)",
    "E26": "=MIN(B18:B22)",
    "E27": "=PROMEDIO(B10:B14)",
    "E28": "=CONTAR(B10:B14)"
  },
  "validate": {
    "B3": 35000,
    "B9": 14800,
    "B17": 11000,
    "B25": 35000,
    "B26": 25800,
    "B27": 9200,
    "B28": 0.4229,
    "B29": 0.3143,
    "B30": 0.2629,
    "C27": "Superávit",
    "C30": "Excelente",
    "E25": 7500,
    "E26": 800,
    "E27": 2960,
    "E28": 5
  }
}
EXCEL,
        ];

        return $ex;
    }
}
