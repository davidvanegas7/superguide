<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExcelIntermediateExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'excel-intermedio')->first();

        if (! $course) {
            $this->command->warn('Excel Intermedio course not found. Run CourseSeeder + ExcelIntermediateLessonSeeder first.');
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

        $this->command->info('Excel Intermedio exercises seeded: ' . count($exercises) . ' exercises.');
    }

    private function exercises(\Illuminate\Support\Collection $lessons): array
    {
        $ex = [];

        // ── Lección 1: BUSCARV (VLOOKUP) ─────────────────────────────────
        if ($l = $lessons->get(1)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Busca precios de productos con BUSCARV',
            'language'     => 'excel',
            'description'  => <<<'MD'
**BUSCARV** (VLOOKUP) es una de las funciones más utilizadas en Excel. Permite buscar un valor en la primera columna de una tabla y devolver un dato de otra columna de la misma fila.

Sintaxis: `=BUSCARV(valor_buscado, tabla, columna, [coincidencia])`

En este ejercicio tienes:
- Un **catálogo de productos** (rango F1:H6) con Código, Producto y Precio.
- Una **lista de pedidos** (columnas A-D) donde debes completar el Producto y Precio usando BUSCARV.

Tu tarea:
1. En **B2:B5** usa BUSCARV para obtener el nombre del producto desde el catálogo.
2. En **C2:C5** usa BUSCARV para obtener el precio unitario.
3. En **D2:D5** calcula el **Total** (Precio × Cantidad que está en A).

Usa coincidencia exacta (0 o FALSO).
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Usa BUSCARV para completar Producto (B2:B5) y Precio (C2:C5) desde el catálogo en F1:H6. Luego calcula Total = Precio × Cantidad en D2:D5.",
  "initialData": {
    "A1": "Código",
    "B1": "Producto",
    "C1": "Precio",
    "D1": "Total",
    "A2": "P003",
    "A3": "P001",
    "A4": "P005",
    "A5": "P002",
    "E1": "Cantidad",
    "E2": 4,
    "E3": 2,
    "E4": 1,
    "E5": 7,
    "F1": "Código",
    "G1": "Producto",
    "H1": "Precio",
    "F2": "P001",
    "G2": "Laptop",
    "H2": 15999,
    "F3": "P002",
    "G3": "Mouse",
    "H3": 349,
    "F4": "P003",
    "G4": "Teclado",
    "H4": 899,
    "F5": "P004",
    "G5": "Monitor",
    "H5": 5499,
    "F6": "P005",
    "G6": "Audífonos",
    "H6": 1250
  },
  "expectedFormulas": {
    "B2": "=BUSCARV(A2,$F$2:$H$6,2,0)",
    "B3": "=BUSCARV(A3,$F$2:$H$6,2,0)",
    "B4": "=BUSCARV(A4,$F$2:$H$6,2,0)",
    "B5": "=BUSCARV(A5,$F$2:$H$6,2,0)",
    "C2": "=BUSCARV(A2,$F$2:$H$6,3,0)",
    "C3": "=BUSCARV(A3,$F$2:$H$6,3,0)",
    "C4": "=BUSCARV(A4,$F$2:$H$6,3,0)",
    "C5": "=BUSCARV(A5,$F$2:$H$6,3,0)",
    "D2": "=C2*E2",
    "D3": "=C3*E3",
    "D4": "=C4*E4",
    "D5": "=C5*E5"
  },
  "validate": {
    "B2": "Teclado",
    "B3": "Laptop",
    "B4": "Audífonos",
    "B5": "Mouse",
    "C2": 899,
    "C3": 15999,
    "C4": 1250,
    "C5": 349,
    "D2": 3596,
    "D3": 31998,
    "D4": 1250,
    "D5": 2443
  }
}
EXCEL,
        ];

        // ── Lección 2: Funciones condicionales (SUMAR.SI, CONTAR.SI) ──────
        if ($l = $lessons->get(2)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Analiza ventas por región con SUMAR.SI y CONTAR.SI',
            'language'     => 'excel',
            'description'  => <<<'MD'
Las funciones **condicionales** permiten operar solo sobre celdas que cumplen un criterio.

- `=SUMAR.SI(rango_criterio, criterio, rango_suma)` — suma valores que cumplen la condición.
- `=CONTAR.SI(rango, criterio)` — cuenta celdas que cumplen la condición.
- `=PROMEDIO.SI(rango_criterio, criterio, rango_promedio)` — promedia valores filtrados.

Tienes un reporte de ventas con **Región**, **Categoría** y **Monto**. Tu tarea:

1. Calcula el **total de ventas** de la región "Norte" en **G2**.
2. Cuenta **cuántas ventas** hubo en la región "Sur" en **G3**.
3. Calcula el **promedio de ventas** de la categoría "Electrónica" en **G4**.
4. Suma las ventas de la categoría "Ropa" en **G5**.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Usa SUMAR.SI, CONTAR.SI y PROMEDIO.SI para analizar las ventas. G2: total Norte, G3: cantidad Sur, G4: promedio Electrónica, G5: total Ropa.",
  "initialData": {
    "A1": "Vendedor",
    "B1": "Región",
    "C1": "Categoría",
    "D1": "Monto",
    "A2": "Ana",
    "B2": "Norte",
    "C2": "Electrónica",
    "D2": 12500,
    "A3": "Luis",
    "B3": "Sur",
    "C3": "Ropa",
    "D3": 8400,
    "A4": "María",
    "B4": "Norte",
    "C4": "Ropa",
    "D4": 6300,
    "A5": "Carlos",
    "B5": "Centro",
    "C5": "Electrónica",
    "D5": 15000,
    "A6": "Sofía",
    "B6": "Sur",
    "C6": "Electrónica",
    "D6": 9200,
    "A7": "Pedro",
    "B7": "Norte",
    "C7": "Alimentos",
    "D7": 4500,
    "A8": "Laura",
    "B8": "Sur",
    "C8": "Ropa",
    "D8": 7100,
    "A9": "Jorge",
    "B9": "Centro",
    "C9": "Alimentos",
    "D9": 3800,
    "A10": "Diana",
    "B10": "Norte",
    "C10": "Electrónica",
    "D10": 11000,
    "F1": "Métrica",
    "G1": "Resultado",
    "F2": "Total Norte",
    "F3": "Cantidad Sur",
    "F4": "Promedio Electrónica",
    "F5": "Total Ropa"
  },
  "expectedFormulas": {
    "G2": "=SUMAR.SI(B2:B10,\"Norte\",D2:D10)",
    "G3": "=CONTAR.SI(B2:B10,\"Sur\")",
    "G4": "=PROMEDIO.SI(C2:C10,\"Electrónica\",D2:D10)",
    "G5": "=SUMAR.SI(C2:C10,\"Ropa\",D2:D10)"
  },
  "validate": {
    "G2": 34300,
    "G3": 3,
    "G4": 11925,
    "G5": 21800
  }
}
EXCEL,
        ];

        // ── Lección 3: Tablas dinámicas ───────────────────────────────────
        if ($l = $lessons->get(3)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Prepara datos para una tabla dinámica de ventas',
            'language'     => 'excel',
            'description'  => <<<'MD'
Las **tablas dinámicas** son herramientas poderosas para resumir grandes volúmenes de datos. Antes de crearlas, los datos deben estar correctamente estructurados.

Requisitos para una tabla dinámica:
- Cada columna debe tener un **encabezado único**.
- No debe haber **filas ni columnas vacías** dentro del rango.
- Los datos deben ser **consistentes** (mismo tipo por columna).

En este ejercicio:
1. Completa las fórmulas de resumen que **simulan** lo que una tabla dinámica calcularía.
2. En **G2:G4** calcula el total de ventas por trimestre usando SUMAR.SI.
3. En **H2:H4** calcula el número de transacciones por trimestre con CONTAR.SI.
4. En **I2:I4** calcula el promedio de ventas por trimestre con PROMEDIO.SI.

Esto te ayudará a entender la lógica detrás de las tablas dinámicas.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Calcula resúmenes por trimestre: G2:G4 total ventas (SUMAR.SI), H2:H4 cantidad (CONTAR.SI), I2:I4 promedio (PROMEDIO.SI).",
  "initialData": {
    "A1": "Fecha",
    "B1": "Vendedor",
    "C1": "Producto",
    "D1": "Trimestre",
    "E1": "Monto",
    "A2": "2026-01-15",
    "B2": "Ana",
    "C2": "Laptop",
    "D2": "T1",
    "E2": 15000,
    "A3": "2026-02-20",
    "B3": "Luis",
    "C3": "Monitor",
    "D3": "T1",
    "E3": 5500,
    "A4": "2026-03-10",
    "B4": "Ana",
    "C4": "Teclado",
    "D4": "T1",
    "E4": 900,
    "A5": "2026-04-05",
    "B5": "Carlos",
    "C5": "Laptop",
    "D5": "T2",
    "E5": 16000,
    "A6": "2026-05-18",
    "B6": "Luis",
    "C6": "Mouse",
    "D6": "T2",
    "E6": 350,
    "A7": "2026-06-22",
    "B7": "Ana",
    "C7": "Monitor",
    "D7": "T2",
    "E7": 5500,
    "A8": "2026-07-11",
    "B8": "Carlos",
    "C8": "Laptop",
    "D8": "T3",
    "E8": 15000,
    "A9": "2026-08-30",
    "B9": "Luis",
    "C9": "Teclado",
    "D9": "T3",
    "E9": 900,
    "F1": "Trimestre",
    "G1": "Total Ventas",
    "H1": "Transacciones",
    "I1": "Promedio",
    "F2": "T1",
    "F3": "T2",
    "F4": "T3"
  },
  "expectedFormulas": {
    "G2": "=SUMAR.SI(D2:D9,\"T1\",E2:E9)",
    "G3": "=SUMAR.SI(D2:D9,\"T2\",E2:E9)",
    "G4": "=SUMAR.SI(D2:D9,\"T3\",E2:E9)",
    "H2": "=CONTAR.SI(D2:D9,\"T1\")",
    "H3": "=CONTAR.SI(D2:D9,\"T2\")",
    "H4": "=CONTAR.SI(D2:D9,\"T3\")",
    "I2": "=PROMEDIO.SI(D2:D9,\"T1\",E2:E9)",
    "I3": "=PROMEDIO.SI(D2:D9,\"T2\",E2:E9)",
    "I4": "=PROMEDIO.SI(D2:D9,\"T3\",E2:E9)"
  },
  "validate": {
    "G2": 21400,
    "G3": 21850,
    "G4": 15900,
    "H2": 3,
    "H3": 3,
    "H4": 2,
    "I2": 7133.33,
    "I3": 7283.33,
    "I4": 7950
  }
}
EXCEL,
        ];

        // ── Lección 4: Formato condicional avanzado ───────────────────────
        if ($l = $lessons->get(4)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Clasifica el rendimiento de empleados con fórmulas',
            'language'     => 'excel',
            'description'  => <<<'MD'
El **formato condicional avanzado** usa fórmulas para determinar qué celdas resaltar. Aunque el formato visual se aplica en la interfaz, la lógica detrás se basa en fórmulas.

En este ejercicio practicarás la lógica que sustenta el formato condicional:

1. En **D2:D7** clasifica el rendimiento del empleado:
   - `"Excelente"` si Ventas ≥ 50000
   - `"Bueno"` si Ventas ≥ 30000
   - `"Regular"` si Ventas < 30000
2. En **E2:E7** calcula si el empleado alcanzó la **meta** (35000):
   - `"Sí"` o `"No"`
3. En **F2** calcula cuántos empleados tienen rendimiento "Excelente".

Estas fórmulas son las mismas que usarías en las reglas de formato condicional.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Clasifica rendimiento en D2:D7 (Excelente/Bueno/Regular), meta alcanzada en E2:E7 (Sí/No con meta=35000), y cuenta Excelentes en F2.",
  "initialData": {
    "A1": "Empleado",
    "B1": "Departamento",
    "C1": "Ventas",
    "D1": "Rendimiento",
    "E1": "Meta (35000)",
    "F1": "Total Excelentes",
    "A2": "Roberto",
    "B2": "Comercial",
    "C2": 52000,
    "A3": "Elena",
    "B3": "Marketing",
    "C3": 38000,
    "A4": "Fernando",
    "B4": "Comercial",
    "C4": 27000,
    "A5": "Claudia",
    "B5": "Ventas",
    "C5": 61000,
    "A6": "Miguel",
    "B6": "Marketing",
    "C6": 45000,
    "A7": "Patricia",
    "B7": "Ventas",
    "C7": 29500
  },
  "expectedFormulas": {
    "D2": "=SI(C2>=50000,\"Excelente\",SI(C2>=30000,\"Bueno\",\"Regular\"))",
    "D3": "=SI(C3>=50000,\"Excelente\",SI(C3>=30000,\"Bueno\",\"Regular\"))",
    "D4": "=SI(C4>=50000,\"Excelente\",SI(C4>=30000,\"Bueno\",\"Regular\"))",
    "D5": "=SI(C5>=50000,\"Excelente\",SI(C5>=30000,\"Bueno\",\"Regular\"))",
    "D6": "=SI(C6>=50000,\"Excelente\",SI(C6>=30000,\"Bueno\",\"Regular\"))",
    "D7": "=SI(C7>=50000,\"Excelente\",SI(C7>=30000,\"Bueno\",\"Regular\"))",
    "E2": "=SI(C2>=35000,\"Sí\",\"No\")",
    "E3": "=SI(C3>=35000,\"Sí\",\"No\")",
    "E4": "=SI(C4>=35000,\"Sí\",\"No\")",
    "E5": "=SI(C5>=35000,\"Sí\",\"No\")",
    "E6": "=SI(C6>=35000,\"Sí\",\"No\")",
    "E7": "=SI(C7>=35000,\"Sí\",\"No\")",
    "F2": "=CONTAR.SI(D2:D7,\"Excelente\")"
  },
  "validate": {
    "D2": "Excelente",
    "D3": "Bueno",
    "D4": "Regular",
    "D5": "Excelente",
    "D6": "Bueno",
    "D7": "Regular",
    "E2": "Sí",
    "E3": "Sí",
    "E4": "No",
    "E5": "Sí",
    "E6": "Sí",
    "E7": "No",
    "F2": 2
  }
}
EXCEL,
        ];

        // ── Lección 5: Lógica avanzada (Y, O, SI.CONJUNTO) ───────────────
        if ($l = $lessons->get(5)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Evalúa préstamos con SI anidado, Y, O y SI.CONJUNTO',
            'language'     => 'excel',
            'description'  => <<<'MD'
Las funciones lógicas avanzadas permiten evaluar **múltiples condiciones** simultáneamente:

- `Y(condición1, condición2, ...)` — VERDADERO si **todas** son verdaderas.
- `O(condición1, condición2, ...)` — VERDADERO si **al menos una** es verdadera.
- `SI.CONJUNTO(condición1, valor1, condición2, valor2, ...)` — devuelve el valor de la primera condición verdadera.

Tienes solicitudes de préstamo. Evalúa:

1. **E2:E6** — ¿Aprobado? Usa `Y()`: se aprueba si Ingreso ≥ 20000 **y** Score ≥ 650.
2. **F2:F6** — Nivel de riesgo con `SI.CONJUNTO`:
   - Score ≥ 750 → `"Bajo"`
   - Score ≥ 650 → `"Medio"`
   - Score < 650 → `"Alto"`
3. **G2:G6** — ¿Oferta especial? Usa `O()`: Sí si Ingreso ≥ 40000 **o** Score ≥ 800.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "E2:E6 aprobación con Y(Ingreso>=20000, Score>=650). F2:F6 riesgo con SI.CONJUNTO. G2:G6 oferta especial con O(Ingreso>=40000, Score>=800).",
  "initialData": {
    "A1": "Solicitante",
    "B1": "Ingreso",
    "C1": "Score",
    "D1": "Monto Solicitado",
    "E1": "Aprobado",
    "F1": "Riesgo",
    "G1": "Oferta Especial",
    "A2": "García",
    "B2": 35000,
    "C2": 720,
    "D2": 150000,
    "A3": "Martínez",
    "B3": 18000,
    "C3": 680,
    "D3": 80000,
    "A4": "López",
    "B4": 45000,
    "C4": 810,
    "D4": 300000,
    "A5": "Rodríguez",
    "B5": 22000,
    "C5": 590,
    "D5": 120000,
    "A6": "Hernández",
    "B6": 50000,
    "C6": 770,
    "D6": 250000
  },
  "expectedFormulas": {
    "E2": "=SI(Y(B2>=20000,C2>=650),\"Sí\",\"No\")",
    "E3": "=SI(Y(B3>=20000,C3>=650),\"Sí\",\"No\")",
    "E4": "=SI(Y(B4>=20000,C4>=650),\"Sí\",\"No\")",
    "E5": "=SI(Y(B5>=20000,C5>=650),\"Sí\",\"No\")",
    "E6": "=SI(Y(B6>=20000,C6>=650),\"Sí\",\"No\")",
    "F2": "=SI.CONJUNTO(C2>=750,\"Bajo\",C2>=650,\"Medio\",C2<650,\"Alto\")",
    "F3": "=SI.CONJUNTO(C3>=750,\"Bajo\",C3>=650,\"Medio\",C3<650,\"Alto\")",
    "F4": "=SI.CONJUNTO(C4>=750,\"Bajo\",C4>=650,\"Medio\",C4<650,\"Alto\")",
    "F5": "=SI.CONJUNTO(C5>=750,\"Bajo\",C5>=650,\"Medio\",C5<650,\"Alto\")",
    "F6": "=SI.CONJUNTO(C6>=750,\"Bajo\",C6>=650,\"Medio\",C6<650,\"Alto\")",
    "G2": "=SI(O(B2>=40000,C2>=800),\"Sí\",\"No\")",
    "G3": "=SI(O(B3>=40000,C3>=800),\"Sí\",\"No\")",
    "G4": "=SI(O(B4>=40000,C4>=800),\"Sí\",\"No\")",
    "G5": "=SI(O(B5>=40000,C5>=800),\"Sí\",\"No\")",
    "G6": "=SI(O(B6>=40000,C6>=800),\"Sí\",\"No\")"
  },
  "validate": {
    "E2": "Sí",
    "E3": "No",
    "E4": "Sí",
    "E5": "No",
    "E6": "Sí",
    "F2": "Medio",
    "F3": "Medio",
    "F4": "Bajo",
    "F5": "Alto",
    "F6": "Bajo",
    "G2": "No",
    "G3": "No",
    "G4": "Sí",
    "G5": "No",
    "G6": "Sí"
  }
}
EXCEL,
        ];

        // ── Lección 6: Gráficos avanzados ────────────────────────────────
        if ($l = $lessons->get(6)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Prepara datos para gráficos combinados de ingresos y gastos',
            'language'     => 'excel',
            'description'  => <<<'MD'
Los **gráficos avanzados** requieren datos bien organizados. Los gráficos combinados (barras + línea) muestran métricas diferentes en un mismo gráfico.

En este ejercicio prepararás los datos para un gráfico combinado:

1. Tienes ingresos y gastos mensuales del primer semestre.
2. En **D2:D7** calcula la **Utilidad** (Ingresos − Gastos).
3. En **E2:E7** calcula el **Margen %** (Utilidad / Ingresos × 100).
4. En **B8** calcula el **total de ingresos**.
5. En **C8** calcula el **total de gastos**.
6. En **D8** calcula la **utilidad total**.

Estos datos serían ideales para un gráfico de barras (Ingresos/Gastos) con una línea superpuesta (Margen %).
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Calcula Utilidad (D2:D7), Margen% (E2:E7), y totales en fila 8. Utilidad = Ingresos - Gastos, Margen = Utilidad/Ingresos*100.",
  "initialData": {
    "A1": "Mes",
    "B1": "Ingresos",
    "C1": "Gastos",
    "D1": "Utilidad",
    "E1": "Margen %",
    "A2": "Enero",
    "B2": 120000,
    "C2": 85000,
    "A3": "Febrero",
    "B3": 135000,
    "C3": 90000,
    "A4": "Marzo",
    "B4": 128000,
    "C4": 92000,
    "A5": "Abril",
    "B5": 142000,
    "C5": 88000,
    "A6": "Mayo",
    "B6": 155000,
    "C6": 95000,
    "A7": "Junio",
    "B7": 148000,
    "C7": 91000,
    "A8": "Total"
  },
  "expectedFormulas": {
    "D2": "=B2-C2",
    "D3": "=B3-C3",
    "D4": "=B4-C4",
    "D5": "=B5-C5",
    "D6": "=B6-C6",
    "D7": "=B7-C7",
    "E2": "=D2/B2*100",
    "E3": "=D3/B3*100",
    "E4": "=D4/B4*100",
    "E5": "=D5/B5*100",
    "E6": "=D6/B6*100",
    "E7": "=D7/B7*100",
    "B8": "=SUMA(B2:B7)",
    "C8": "=SUMA(C2:C7)",
    "D8": "=SUMA(D2:D7)"
  },
  "validate": {
    "D2": 35000,
    "D3": 45000,
    "D4": 36000,
    "D5": 54000,
    "D6": 60000,
    "D7": 57000,
    "E2": 29.17,
    "E3": 33.33,
    "E4": 28.13,
    "E5": 38.03,
    "E6": 38.71,
    "E7": 38.51,
    "B8": 828000,
    "C8": 541000,
    "D8": 287000
  }
}
EXCEL,
        ];

        // ── Lección 7: Validación avanzada ────────────────────────────────
        if ($l = $lessons->get(7)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Valida datos de inventario con fórmulas de control',
            'language'     => 'excel',
            'description'  => <<<'MD'
La **validación avanzada** de datos va más allá de listas desplegables. Se pueden usar fórmulas personalizadas para controlar lo que se ingresa.

En este ejercicio simularás validaciones sobre un inventario:

1. En **E2:E6** verifica si el **Stock** está por debajo del **Punto de Reorden**:
   - `"⚠ Reponer"` si Stock < Punto de Reorden, `"OK"` en caso contrario.
2. En **F2:F6** calcula el **Valor del inventario** (Stock × Precio Unitario).
3. En **G2:G6** valida si el precio es razonable:
   - `"Válido"` si Precio está entre 10 y 5000 (inclusive), `"Revisar"` si no.
4. En **F7** calcula el valor total del inventario.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "E2:E6 alerta de reorden (Stock < Punto Reorden). F2:F6 valor inventario (Stock × Precio). G2:G6 validación de precio (10-5000). F7 total.",
  "initialData": {
    "A1": "Producto",
    "B1": "Stock",
    "C1": "Pto. Reorden",
    "D1": "Precio Unit.",
    "E1": "Estado",
    "F1": "Valor Inv.",
    "G1": "Precio Válido",
    "A2": "Tornillos (caja)",
    "B2": 150,
    "C2": 200,
    "D2": 45,
    "A3": "Martillo",
    "B3": 80,
    "C3": 30,
    "D3": 189,
    "A4": "Pintura (litro)",
    "B4": 12,
    "C4": 25,
    "D4": 320,
    "A5": "Cinta métrica",
    "B5": 45,
    "C5": 20,
    "D5": 75,
    "A6": "Sierra eléctrica",
    "B6": 5,
    "C6": 10,
    "D6": 4800
  },
  "expectedFormulas": {
    "E2": "=SI(B2<C2,\"⚠ Reponer\",\"OK\")",
    "E3": "=SI(B3<C3,\"⚠ Reponer\",\"OK\")",
    "E4": "=SI(B4<C4,\"⚠ Reponer\",\"OK\")",
    "E5": "=SI(B5<C5,\"⚠ Reponer\",\"OK\")",
    "E6": "=SI(B6<C6,\"⚠ Reponer\",\"OK\")",
    "F2": "=B2*D2",
    "F3": "=B3*D3",
    "F4": "=B4*D4",
    "F5": "=B5*D5",
    "F6": "=B6*D6",
    "G2": "=SI(Y(D2>=10,D2<=5000),\"Válido\",\"Revisar\")",
    "G3": "=SI(Y(D3>=10,D3<=5000),\"Válido\",\"Revisar\")",
    "G4": "=SI(Y(D4>=10,D4<=5000),\"Válido\",\"Revisar\")",
    "G5": "=SI(Y(D5>=10,D5<=5000),\"Válido\",\"Revisar\")",
    "G6": "=SI(Y(D6>=10,D6<=5000),\"Válido\",\"Revisar\")",
    "F7": "=SUMA(F2:F6)"
  },
  "validate": {
    "E2": "⚠ Reponer",
    "E3": "OK",
    "E4": "⚠ Reponer",
    "E5": "OK",
    "E6": "⚠ Reponer",
    "F2": 6750,
    "F3": 15120,
    "F4": 3840,
    "F5": 3375,
    "F6": 24000,
    "G2": "Válido",
    "G3": "Válido",
    "G4": "Válido",
    "G5": "Válido",
    "G6": "Válido",
    "F7": 53085
  }
}
EXCEL,
        ];

        // ── Lección 8: INDICE+COINCIDIR ───────────────────────────────────
        if ($l = $lessons->get(8)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Búsquedas flexibles con INDICE y COINCIDIR',
            'language'     => 'excel',
            'description'  => <<<'MD'
**INDICE + COINCIDIR** es la combinación más potente para búsquedas en Excel, superando a BUSCARV porque:

- Puede buscar en **cualquier dirección** (izquierda, derecha).
- Permite búsquedas en **dos dimensiones** (fila y columna).

Sintaxis:
- `=INDICE(rango_resultado, COINCIDIR(valor, rango_búsqueda, 0))`

Tienes una tabla de **tarifas** con ciudades (filas) y tipos de servicio (columnas). Tu tarea:

1. En **B12:B14** usa INDICE+COINCIDIR para obtener la tarifa según la ciudad y servicio indicados en A12 y A13.
2. En **B15** busca el **nombre de la ciudad** que tiene la tarifa más alta en "Express" (búsqueda inversa).
3. En **B16** usa una búsqueda bidimensional para encontrar la tarifa de una ciudad y servicio dados.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Usa INDICE+COINCIDIR para buscar tarifas. B12: tarifa Guadalajara-Estándar. B13: tarifa Monterrey-Express. B14: tarifa CDMX-Premium. B15: ciudad con mayor tarifa Express. B16: tarifa bidimensional de Puebla-Estándar.",
  "initialData": {
    "A1": "Ciudad",
    "B1": "Estándar",
    "C1": "Express",
    "D1": "Premium",
    "A2": "CDMX",
    "B2": 150,
    "C2": 280,
    "D2": 450,
    "A3": "Guadalajara",
    "B3": 180,
    "C3": 320,
    "D3": 500,
    "A4": "Monterrey",
    "B4": 170,
    "C4": 350,
    "D4": 520,
    "A5": "Puebla",
    "B5": 130,
    "C5": 240,
    "D5": 380,
    "A6": "Querétaro",
    "B6": 140,
    "C6": 260,
    "D6": 410,
    "A8": "Consulta",
    "B8": "Resultado",
    "A9": "Ciudad",
    "A10": "Servicio",
    "A11": "---",
    "A12": "Guadalajara Estándar",
    "A13": "Monterrey Express",
    "A14": "CDMX Premium",
    "A15": "Mayor Express (ciudad)",
    "A16": "Puebla Estándar"
  },
  "expectedFormulas": {
    "B12": "=INDICE(B2:B6,COINCIDIR(\"Guadalajara\",A2:A6,0))",
    "B13": "=INDICE(C2:C6,COINCIDIR(\"Monterrey\",A2:A6,0))",
    "B14": "=INDICE(D2:D6,COINCIDIR(\"CDMX\",A2:A6,0))",
    "B15": "=INDICE(A2:A6,COINCIDIR(MAX(C2:C6),C2:C6,0))",
    "B16": "=INDICE(B2:D6,COINCIDIR(\"Puebla\",A2:A6,0),COINCIDIR(\"Estándar\",B1:D1,0))"
  },
  "validate": {
    "B12": 180,
    "B13": 350,
    "B14": 450,
    "B15": "Monterrey",
    "B16": 130
  }
}
EXCEL,
        ];

        // ── Lección 9: Funciones matemáticas y estadísticas ───────────────
        if ($l = $lessons->get(9)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Análisis estadístico de calificaciones con funciones avanzadas',
            'language'     => 'excel',
            'description'  => <<<'MD'
Excel ofrece un amplio conjunto de funciones **matemáticas y estadísticas** para análisis de datos:

| Función | Descripción |
|---------|------------|
| `MEDIANA` | Valor central de un conjunto |
| `MODA` | Valor más frecuente |
| `DESVEST` | Desviación estándar |
| `K.ESIMO.MAYOR` | Enésimo valor más grande |
| `K.ESIMO.MENOR` | Enésimo valor más pequeño |
| `REDONDEAR` | Redondea a N decimales |
| `CONTAR.SI` | Cuenta con criterio |

Tienes calificaciones de 10 estudiantes. Calcula:

1. **B13** — Promedio general.
2. **B14** — Mediana.
3. **B15** — Desviación estándar.
4. **B16** — Nota más alta (MAX o K.ESIMO.MAYOR).
5. **B17** — Nota más baja.
6. **B18** — Segunda nota más alta.
7. **B19** — Cantidad de aprobados (≥ 6).
8. **B20** — Porcentaje de aprobados.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Calcula estadísticas: B13 promedio, B14 mediana, B15 desviación estándar, B16 máximo, B17 mínimo, B18 segundo mayor, B19 aprobados (>=6), B20 % aprobados.",
  "initialData": {
    "A1": "Estudiante",
    "B1": "Calificación",
    "A2": "Alumno 1",
    "B2": 8.5,
    "A3": "Alumno 2",
    "B3": 6.0,
    "A4": "Alumno 3",
    "B4": 9.2,
    "A5": "Alumno 4",
    "B5": 5.5,
    "A6": "Alumno 5",
    "B6": 7.8,
    "A7": "Alumno 6",
    "B7": 4.3,
    "A8": "Alumno 7",
    "B8": 8.0,
    "A9": "Alumno 8",
    "B9": 6.5,
    "A10": "Alumno 9",
    "B10": 9.8,
    "A11": "Alumno 10",
    "B11": 7.0,
    "A13": "Promedio",
    "A14": "Mediana",
    "A15": "Desv. Estándar",
    "A16": "Nota Máxima",
    "A17": "Nota Mínima",
    "A18": "2da Más Alta",
    "A19": "Aprobados (≥6)",
    "A20": "% Aprobados"
  },
  "expectedFormulas": {
    "B13": "=PROMEDIO(B2:B11)",
    "B14": "=MEDIANA(B2:B11)",
    "B15": "=REDONDEAR(DESVEST(B2:B11),2)",
    "B16": "=MAX(B2:B11)",
    "B17": "=MIN(B2:B11)",
    "B18": "=K.ESIMO.MAYOR(B2:B11,2)",
    "B19": "=CONTAR.SI(B2:B11,\">=6\")",
    "B20": "=B19/CONTARA(B2:B11)*100"
  },
  "validate": {
    "B13": 7.26,
    "B14": 7.4,
    "B15": 1.72,
    "B16": 9.8,
    "B17": 4.3,
    "B18": 9.2,
    "B19": 8,
    "B20": 80
  }
}
EXCEL,
        ];

        // ── Lección 10: Arrays dinámicos (FILTRAR, ORDENAR, UNICOS) ──────
        if ($l = $lessons->get(10)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Filtra, ordena y extrae valores únicos con arrays dinámicos',
            'language'     => 'excel',
            'description'  => <<<'MD'
Los **arrays dinámicos** (Excel 365 / 2021+) permiten que una sola fórmula devuelva múltiples resultados que se desbordan automáticamente:

- `FILTRAR(array, condición)` — filtra filas que cumplen un criterio.
- `ORDENAR(array, columna, orden)` — ordena datos (1 = ascendente, -1 = descendente).
- `UNICOS(array)` — extrae valores únicos eliminando duplicados.

Tienes una tabla de empleados con Departamento y Salario. Tu tarea:

1. En **F2** usa `UNICOS` para listar los departamentos sin repetir.
2. En **G2** usa `FILTRAR` para mostrar empleados del departamento "Ventas".
3. En **H2** usa `ORDENAR` para listar todos los salarios de mayor a menor.
4. En **I2** usa `FILTRAR` con condición compuesta: empleados con salario > 30000.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "F2: departamentos únicos con UNICOS. G2: empleados de Ventas con FILTRAR. H2: salarios ordenados desc con ORDENAR. I2: empleados con salario>30000 con FILTRAR.",
  "initialData": {
    "A1": "Empleado",
    "B1": "Departamento",
    "C1": "Salario",
    "A2": "Ana Ruiz",
    "B2": "Ventas",
    "C2": 32000,
    "A3": "Pedro Gómez",
    "B3": "TI",
    "C3": 45000,
    "A4": "Laura Torres",
    "B4": "Ventas",
    "C4": 28000,
    "A5": "Miguel Díaz",
    "B5": "RRHH",
    "C5": 35000,
    "A6": "Sofía Luna",
    "B6": "TI",
    "C6": 48000,
    "A7": "Carlos Vega",
    "B7": "Ventas",
    "C7": 31000,
    "A8": "Diana Mora",
    "B8": "RRHH",
    "C8": 33000,
    "A9": "Roberto Salas",
    "B9": "Marketing",
    "C9": 29000,
    "A10": "Elena Cruz",
    "B10": "Marketing",
    "C10": 27500,
    "F1": "Dptos. Únicos",
    "G1": "Empleados Ventas",
    "H1": "Salarios (desc)",
    "I1": "Salario > 30000"
  },
  "expectedFormulas": {
    "F2": "=UNICOS(B2:B10)",
    "G2": "=FILTRAR(A2:A10,B2:B10=\"Ventas\")",
    "H2": "=ORDENAR(C2:C10,1,-1)",
    "I2": "=FILTRAR(A2:C10,C2:C10>30000)"
  },
  "validate": {
    "F2": "Ventas",
    "F3": "TI",
    "F4": "RRHH",
    "F5": "Marketing",
    "G2": "Ana Ruiz",
    "G3": "Laura Torres",
    "G4": "Carlos Vega",
    "H2": 48000,
    "H3": 45000,
    "H4": 35000
  }
}
EXCEL,
        ];

        // ── Lección 11: Rangos con nombre ─────────────────────────────────
        if ($l = $lessons->get(11)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simplifica fórmulas usando rangos con nombre',
            'language'     => 'excel',
            'description'  => <<<'MD'
Los **rangos con nombre** asignan un alias legible a un rango de celdas, haciendo las fórmulas más claras.

Por ejemplo, en vez de `=SUMA(B2:B10)` puedes escribir `=SUMA(Ventas)`.

En este ejercicio trabajarás con un presupuesto mensual. Los rangos con nombre ya están definidos conceptualmente:

- `Ingresos` → D2:D5 (montos de ingresos)
- `Gastos` → D7:D12 (montos de gastos)

Tu tarea:
1. **D6** — Total ingresos con `SUMA` sobre el rango Ingresos.
2. **D13** — Total gastos con `SUMA` sobre el rango Gastos.
3. **D15** — Balance (Total Ingresos − Total Gastos).
4. **D16** — Porcentaje de ahorro (Balance / Total Ingresos × 100).
5. **D17** — Gasto más alto usando `MAX`.
6. **D18** — Cantidad de gastos superiores a 3000.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "D6: SUMA ingresos (D2:D5). D13: SUMA gastos (D7:D12). D15: balance. D16: % ahorro. D17: gasto máximo. D18: gastos > 3000.",
  "initialData": {
    "A1": "=== INGRESOS ===",
    "C1": "Concepto",
    "D1": "Monto",
    "C2": "Salario",
    "D2": 35000,
    "C3": "Freelance",
    "D3": 8000,
    "C4": "Inversiones",
    "D4": 2500,
    "C5": "Otros",
    "D5": 1500,
    "C6": "Total Ingresos",
    "A7": "=== GASTOS ===",
    "C7": "Renta",
    "D7": 12000,
    "C8": "Alimentos",
    "D8": 6000,
    "C9": "Transporte",
    "D9": 3500,
    "C10": "Servicios",
    "D10": 2800,
    "C11": "Entretenimiento",
    "D11": 2000,
    "C12": "Ahorro forzado",
    "D12": 5000,
    "C13": "Total Gastos",
    "A15": "=== RESUMEN ===",
    "C15": "Balance",
    "C16": "% Ahorro",
    "C17": "Gasto Mayor",
    "C18": "Gastos > 3000"
  },
  "expectedFormulas": {
    "D6": "=SUMA(D2:D5)",
    "D13": "=SUMA(D7:D12)",
    "D15": "=D6-D13",
    "D16": "=D15/D6*100",
    "D17": "=MAX(D7:D12)",
    "D18": "=CONTAR.SI(D7:D12,\">3000\")"
  },
  "validate": {
    "D6": 47000,
    "D13": 31300,
    "D15": 15700,
    "D16": 33.4,
    "D17": 12000,
    "D18": 3
  }
}
EXCEL,
        ];

        // ── Lección 12: Funciones de texto avanzadas ──────────────────────
        if ($l = $lessons->get(12)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Manipula datos de texto con funciones avanzadas',
            'language'     => 'excel',
            'description'  => <<<'MD'
Las funciones de **texto avanzadas** permiten limpiar, transformar y extraer información de cadenas:

| Función | Descripción |
|---------|------------|
| `IZQUIERDA(texto, n)` | Primeros n caracteres |
| `DERECHA(texto, n)` | Últimos n caracteres |
| `EXTRAE(texto, inicio, n)` | Subcadena desde posición |
| `ENCONTRAR(buscar, texto)` | Posición de un texto |
| `CONCATENAR` o `&` | Une textos |
| `ESPACIOS` | Elimina espacios extra |
| `MAYUSC / MINUSC / NOMPROPIO` | Cambia mayúsculas |
| `LARGO` | Longitud del texto |
| `SUSTITUIR` | Reemplaza texto |

Tienes datos de clientes con formato inconsistente. Tu tarea:

1. **C2:C6** — Extrae el **nombre** (todo antes del primer espacio).
2. **D2:D6** — Extrae el **apellido** (todo después del primer espacio).
3. **E2:E6** — Genera un **email** formato: nombre.apellido@empresa.com (todo en minúsculas).
4. **F2:F6** — Extrae los **primeros 3 dígitos** del teléfono (código de área).
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "C2:C6 extraer nombre. D2:D6 extraer apellido. E2:E6 generar email (minúsculas, nombre.apellido@empresa.com). F2:F6 primeros 3 dígitos del teléfono.",
  "initialData": {
    "A1": "Nombre Completo",
    "B1": "Teléfono",
    "C1": "Nombre",
    "D1": "Apellido",
    "E1": "Email",
    "F1": "Cód. Área",
    "A2": "María García",
    "B2": "5551234567",
    "A3": "Juan López",
    "B3": "3339876543",
    "A4": "Ana Martínez",
    "B4": "8187654321",
    "A5": "Carlos Ruiz",
    "B5": "4421112233",
    "A6": "Sofía Hernández",
    "B6": "2281003050"
  },
  "expectedFormulas": {
    "C2": "=IZQUIERDA(A2,ENCONTRAR(\" \",A2)-1)",
    "C3": "=IZQUIERDA(A3,ENCONTRAR(\" \",A3)-1)",
    "C4": "=IZQUIERDA(A4,ENCONTRAR(\" \",A4)-1)",
    "C5": "=IZQUIERDA(A5,ENCONTRAR(\" \",A5)-1)",
    "C6": "=IZQUIERDA(A6,ENCONTRAR(\" \",A6)-1)",
    "D2": "=DERECHA(A2,LARGO(A2)-ENCONTRAR(\" \",A2))",
    "D3": "=DERECHA(A3,LARGO(A3)-ENCONTRAR(\" \",A3))",
    "D4": "=DERECHA(A4,LARGO(A4)-ENCONTRAR(\" \",A4))",
    "D5": "=DERECHA(A5,LARGO(A5)-ENCONTRAR(\" \",A5))",
    "D6": "=DERECHA(A6,LARGO(A6)-ENCONTRAR(\" \",A6))",
    "E2": "=MINUSC(C2&\".\"&D2&\"@empresa.com\")",
    "E3": "=MINUSC(C3&\".\"&D3&\"@empresa.com\")",
    "E4": "=MINUSC(C4&\".\"&D4&\"@empresa.com\")",
    "E5": "=MINUSC(C5&\".\"&D5&\"@empresa.com\")",
    "E6": "=MINUSC(C6&\".\"&D6&\"@empresa.com\")",
    "F2": "=IZQUIERDA(B2,3)",
    "F3": "=IZQUIERDA(B3,3)",
    "F4": "=IZQUIERDA(B4,3)",
    "F5": "=IZQUIERDA(B5,3)",
    "F6": "=IZQUIERDA(B6,3)"
  },
  "validate": {
    "C2": "María",
    "C3": "Juan",
    "C4": "Ana",
    "C5": "Carlos",
    "C6": "Sofía",
    "D2": "García",
    "D3": "López",
    "D4": "Martínez",
    "D5": "Ruiz",
    "D6": "Hernández",
    "E2": "maría.garcía@empresa.com",
    "E3": "juan.lópez@empresa.com",
    "E4": "ana.martínez@empresa.com",
    "E5": "carlos.ruiz@empresa.com",
    "E6": "sofía.hernández@empresa.com",
    "F2": "555",
    "F3": "333",
    "F4": "818",
    "F5": "442",
    "F6": "228"
  }
}
EXCEL,
        ];

        // ── Lección 13: Análisis de hipótesis ─────────────────────────────
        if ($l = $lessons->get(13)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simula escenarios financieros con análisis de hipótesis',
            'language'     => 'excel',
            'description'  => <<<'MD'
El **análisis de hipótesis** (What-If Analysis) permite evaluar cómo cambios en variables afectan el resultado. Aunque Excel tiene herramientas como Buscar Objetivo y Tablas de Datos, la base es construir un **modelo con fórmulas vinculadas**.

Tienes un modelo de negocio para un producto. Las **variables** están en las celdas B2:B5 y los **resultados** se calculan abajo.

Escenarios a evaluar:
1. **B8** — Ingresos (Precio × Unidades vendidas).
2. **B9** — Costo total (Costo unitario × Unidades + Costos fijos).
3. **B10** — Utilidad (Ingresos − Costo total).
4. **B11** — Margen de utilidad % (Utilidad / Ingresos × 100).
5. **B12** — Punto de equilibrio en unidades: Costos fijos / (Precio − Costo unitario).
6. **B13** — ¿Es rentable? ("Sí" si Utilidad > 0, "No" si no).

Al cambiar las variables en B2:B5, todo el modelo se recalcula automáticamente.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Construye el modelo financiero: B8 ingresos, B9 costo total, B10 utilidad, B11 margen%, B12 punto de equilibrio, B13 rentabilidad.",
  "initialData": {
    "A1": "=== VARIABLES ===",
    "A2": "Precio de venta",
    "B2": 250,
    "A3": "Costo unitario",
    "B3": 95,
    "A4": "Unidades vendidas",
    "B4": 500,
    "A5": "Costos fijos",
    "B5": 35000,
    "A7": "=== RESULTADOS ===",
    "A8": "Ingresos",
    "A9": "Costo Total",
    "A10": "Utilidad",
    "A11": "Margen %",
    "A12": "Punto Equilibrio (uds)",
    "A13": "¿Es Rentable?"
  },
  "expectedFormulas": {
    "B8": "=B2*B4",
    "B9": "=B3*B4+B5",
    "B10": "=B8-B9",
    "B11": "=B10/B8*100",
    "B12": "=REDONDEAR(B5/(B2-B3),0)",
    "B13": "=SI(B10>0,\"Sí\",\"No\")"
  },
  "validate": {
    "B8": 125000,
    "B9": 82500,
    "B10": 42500,
    "B11": 34,
    "B12": 226,
    "B13": "Sí"
  }
}
EXCEL,
        ];

        // ── Lección 14: Datos externos ────────────────────────────────────
        if ($l = $lessons->get(14)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Consolida datos de múltiples fuentes',
            'language'     => 'excel',
            'description'  => <<<'MD'
Al trabajar con **datos externos** (archivos CSV, bases de datos, APIs), es común necesitar consolidar información de distintas fuentes.

En este ejercicio simulas la consolidación de datos de ventas de **3 sucursales** que llegan en hojas separadas. Los datos ya están en una sola hoja para practicar.

Tu tarea:
1. En **F2:F4** calcula el **total de ventas** por sucursal usando SUMAR.SI.
2. En **G2:G4** calcula el **número de transacciones** por sucursal con CONTAR.SI.
3. En **H2:H4** calcula el **ticket promedio** (Total / Transacciones).
4. En **F5** calcula el **gran total** de todas las sucursales.
5. En **I2:I4** calcula la **participación %** de cada sucursal respecto al gran total.
6. En **F6** identifica la sucursal con **mayores ventas** (INDICE+COINCIDIR+MAX).
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "F2:F4 total por sucursal (SUMAR.SI). G2:G4 transacciones (CONTAR.SI). H2:H4 ticket promedio. F5 gran total. I2:I4 participación %. F6 sucursal líder.",
  "initialData": {
    "A1": "Sucursal",
    "B1": "Producto",
    "C1": "Monto",
    "A2": "Centro",
    "B2": "Laptop",
    "C2": 15000,
    "A3": "Norte",
    "B3": "Mouse",
    "C3": 350,
    "A4": "Centro",
    "B4": "Monitor",
    "C4": 5500,
    "A5": "Sur",
    "B5": "Teclado",
    "C5": 900,
    "A6": "Norte",
    "B6": "Laptop",
    "C6": 16000,
    "A7": "Sur",
    "B7": "Monitor",
    "C7": 5500,
    "A8": "Centro",
    "B8": "Teclado",
    "C8": 850,
    "A9": "Norte",
    "B9": "Audífonos",
    "C9": 1200,
    "A10": "Sur",
    "B10": "Laptop",
    "C10": 15000,
    "A11": "Centro",
    "B11": "Mouse",
    "C11": 350,
    "E1": "Sucursal",
    "F1": "Total Ventas",
    "G1": "Transacciones",
    "H1": "Ticket Prom.",
    "I1": "Participación %",
    "E2": "Centro",
    "E3": "Norte",
    "E4": "Sur",
    "E5": "Gran Total",
    "E6": "Líder"
  },
  "expectedFormulas": {
    "F2": "=SUMAR.SI(A2:A11,\"Centro\",C2:C11)",
    "F3": "=SUMAR.SI(A2:A11,\"Norte\",C2:C11)",
    "F4": "=SUMAR.SI(A2:A11,\"Sur\",C2:C11)",
    "G2": "=CONTAR.SI(A2:A11,\"Centro\")",
    "G3": "=CONTAR.SI(A2:A11,\"Norte\")",
    "G4": "=CONTAR.SI(A2:A11,\"Sur\")",
    "H2": "=F2/G2",
    "H3": "=F3/G3",
    "H4": "=F4/G4",
    "F5": "=SUMA(F2:F4)",
    "I2": "=F2/F5*100",
    "I3": "=F3/F5*100",
    "I4": "=F4/F5*100",
    "F6": "=INDICE(E2:E4,COINCIDIR(MAX(F2:F4),F2:F4,0))"
  },
  "validate": {
    "F2": 21700,
    "F3": 17550,
    "F4": 21400,
    "G2": 4,
    "G3": 3,
    "G4": 3,
    "H2": 5425,
    "H3": 5850,
    "H4": 7133.33,
    "F5": 60650,
    "I2": 35.78,
    "I3": 28.94,
    "I4": 35.28,
    "F6": "Centro"
  }
}
EXCEL,
        ];

        // ── Lección 15: Funciones de fecha avanzadas ──────────────────────
        if ($l = $lessons->get(15)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Gestiona plazos y antigüedad con funciones de fecha',
            'language'     => 'excel',
            'description'  => <<<'MD'
Las funciones de **fecha avanzadas** son esenciales para gestión de proyectos, RRHH y finanzas:

| Función | Descripción |
|---------|------------|
| `SIFECHA(inicio, fin, unidad)` | Diferencia entre fechas ("Y"=años, "M"=meses, "D"=días) |
| `DIAS.LAB(inicio, fin)` | Días laborables entre dos fechas |
| `FIN.MES(fecha, meses)` | Último día del mes N meses adelante/atrás |
| `DIA.LAB(inicio, días)` | Fecha tras N días laborables |
| `AÑO / MES / DIA` | Extraen componentes de fecha |
| `HOY()` | Fecha actual |

Tienes empleados con su fecha de ingreso. Calcula:

1. **C2:C6** — Antigüedad en **años** (usa SIFECHA con "Y" respecto a 2026-02-24).
2. **D2:D6** — Antigüedad en **meses** totales.
3. **E2:E6** — Días laborables trabajados desde su ingreso hasta 2026-02-24.
4. **F2:F6** — Fecha de **próximo aniversario** laboral (FIN.MES del mes de ingreso en 2026).
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "C2:C6 antigüedad en años (SIFECHA). D2:D6 meses totales. E2:E6 días laborables (DIAS.LAB). F2:F6 próximo aniversario (usar FECHA para construir la fecha del aniversario 2026).",
  "initialData": {
    "A1": "Empleado",
    "B1": "Fecha Ingreso",
    "C1": "Años",
    "D1": "Meses",
    "E1": "Días Lab.",
    "F1": "Aniversario 2026",
    "G1": "Fecha Ref.",
    "G2": "2026-02-24",
    "A2": "Carmen Soto",
    "B2": "2018-03-15",
    "A3": "Raúl Méndez",
    "B3": "2020-07-01",
    "A4": "Isabel Reyes",
    "B4": "2015-11-20",
    "A5": "Tomás Vargas",
    "B5": "2022-01-10",
    "A6": "Lucía Paredes",
    "B6": "2019-09-05"
  },
  "expectedFormulas": {
    "C2": "=SIFECHA(B2,$G$2,\"Y\")",
    "C3": "=SIFECHA(B3,$G$2,\"Y\")",
    "C4": "=SIFECHA(B4,$G$2,\"Y\")",
    "C5": "=SIFECHA(B5,$G$2,\"Y\")",
    "C6": "=SIFECHA(B6,$G$2,\"Y\")",
    "D2": "=SIFECHA(B2,$G$2,\"M\")",
    "D3": "=SIFECHA(B3,$G$2,\"M\")",
    "D4": "=SIFECHA(B4,$G$2,\"M\")",
    "D5": "=SIFECHA(B5,$G$2,\"M\")",
    "D6": "=SIFECHA(B6,$G$2,\"M\")",
    "E2": "=DIAS.LAB(B2,$G$2)",
    "E3": "=DIAS.LAB(B3,$G$2)",
    "E4": "=DIAS.LAB(B4,$G$2)",
    "E5": "=DIAS.LAB(B5,$G$2)",
    "E6": "=DIAS.LAB(B6,$G$2)",
    "F2": "=FECHA(2026,MES(B2),DIA(B2))",
    "F3": "=FECHA(2026,MES(B3),DIA(B3))",
    "F4": "=FECHA(2026,MES(B4),DIA(B4))",
    "F5": "=FECHA(2026,MES(B5),DIA(B5))",
    "F6": "=FECHA(2026,MES(B6),DIA(B6))"
  },
  "validate": {
    "C2": 7,
    "C3": 5,
    "C4": 10,
    "C5": 4,
    "C6": 6,
    "D2": 95,
    "D3": 67,
    "D4": 123,
    "D5": 49,
    "D6": 77,
    "E2": 2067,
    "E3": 1463,
    "E4": 2681,
    "E5": 1075,
    "E6": 1675,
    "F2": "2026-03-15",
    "F3": "2026-07-01",
    "F4": "2026-11-20",
    "F5": "2026-01-10",
    "F6": "2026-09-05"
  }
}
EXCEL,
        ];

        // ── Lección 16: Protección y auditoría ───────────────────────────
        if ($l = $lessons->get(16)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Audita fórmulas y detecta errores en una hoja financiera',
            'language'     => 'excel',
            'description'  => <<<'MD'
La **protección y auditoría** de hojas de cálculo es fundamental para mantener la integridad de los datos. Las funciones de auditoría ayudan a detectar y manejar errores:

| Función | Descripción |
|---------|------------|
| `ESERROR(valor)` | VERDADERO si hay cualquier error |
| `SI.ERROR(valor, alternativa)` | Devuelve alternativa si hay error |
| `SI.ND(valor, alternativa)` | Devuelve alternativa si el resultado es #N/A |
| `TIPO.DE.ERROR(valor)` | Número del tipo de error |
| `ESBLANCO(celda)` | VERDADERO si la celda está vacía |

Tienes una hoja financiera con datos que pueden generar errores. Tu tarea:

1. **C2:C5** — Calcula Precio/Unidades con `SI.ERROR` para manejar división por cero.
2. **D2:D5** — Busca la categoría con BUSCARV usando `SI.ND` para manejar #N/A.
3. **E2:E5** — Verifica si la celda A tiene datos: `SI(ESBLANCO(...),"Falta dato","OK")`.
4. **C6** — Cuenta cuántas celdas en C2:C5 contienen "Error".
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "C2:C5 división segura con SI.ERROR. D2:D5 BUSCARV seguro con SI.ND. E2:E5 verificar datos con ESBLANCO. C6 contar errores.",
  "initialData": {
    "A1": "Producto",
    "B1": "Unidades",
    "C1": "Precio/Unidad",
    "D1": "Categoría",
    "E1": "Dato OK",
    "A2": "Widget A",
    "B2": 50,
    "A3": "Widget B",
    "B3": 0,
    "A4": "",
    "B4": 30,
    "A5": "Widget D",
    "B5": 25,
    "F1": "Precio Total",
    "F2": 5000,
    "F3": 3000,
    "F4": 4500,
    "F5": 2000,
    "G1": "Código",
    "H1": "Categoría",
    "G2": "Widget A",
    "H2": "Electrónica",
    "G3": "Widget B",
    "H3": "Hogar",
    "G4": "Widget C",
    "H4": "Oficina",
    "G5": "Widget D",
    "H5": "Electrónica"
  },
  "expectedFormulas": {
    "C2": "=SI.ERROR(F2/B2,\"Error\")",
    "C3": "=SI.ERROR(F3/B3,\"Error\")",
    "C4": "=SI.ERROR(F4/B4,\"Error\")",
    "C5": "=SI.ERROR(F5/B5,\"Error\")",
    "D2": "=SI.ND(BUSCARV(A2,$G$2:$H$5,2,0),\"No encontrado\")",
    "D3": "=SI.ND(BUSCARV(A3,$G$2:$H$5,2,0),\"No encontrado\")",
    "D4": "=SI.ND(BUSCARV(A4,$G$2:$H$5,2,0),\"No encontrado\")",
    "D5": "=SI.ND(BUSCARV(A5,$G$2:$H$5,2,0),\"No encontrado\")",
    "E2": "=SI(ESBLANCO(A2),\"Falta dato\",\"OK\")",
    "E3": "=SI(ESBLANCO(A3),\"Falta dato\",\"OK\")",
    "E4": "=SI(ESBLANCO(A4),\"Falta dato\",\"OK\")",
    "E5": "=SI(ESBLANCO(A5),\"Falta dato\",\"OK\")",
    "C6": "=CONTAR.SI(C2:C5,\"Error\")"
  },
  "validate": {
    "C2": 100,
    "C3": "Error",
    "C4": 150,
    "C5": 80,
    "D2": "Electrónica",
    "D3": "Hogar",
    "D4": "No encontrado",
    "D5": "Electrónica",
    "E2": "OK",
    "E3": "OK",
    "E4": "Falta dato",
    "E5": "OK",
    "C6": 1
  }
}
EXCEL,
        ];

        // ── Lección 17: Funciones de base de datos (BDSUMA, BDCONTAR) ────
        if ($l = $lessons->get(17)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Consulta una base de datos con BDSUMA y BDCONTAR',
            'language'     => 'excel',
            'description'  => <<<'MD'
Las **funciones de base de datos** de Excel operan sobre tablas estructuradas usando un rango de criterios:

| Función | Descripción |
|---------|------------|
| `BDSUMA(base, campo, criterios)` | Suma valores que cumplen criterios |
| `BDCONTAR(base, campo, criterios)` | Cuenta registros numéricos |
| `BDCONTARA(base, campo, criterios)` | Cuenta registros no vacíos |
| `BDPROMEDIO(base, campo, criterios)` | Promedio con criterios |
| `BDMAX(base, campo, criterios)` | Valor máximo con criterios |
| `BDMIN(base, campo, criterios)` | Valor mínimo con criterios |

La **base** es el rango completo con encabezados. El **campo** es el nombre de la columna (entre comillas). Los **criterios** son un rango con encabezado + condición.

Tienes una base de datos de ventas. Los criterios ya están definidos en H1:I2, J1:J2, etc.

1. **H4** — BDSUMA de ventas donde Región = "Norte".
2. **H5** — BDCONTAR de ventas donde Región = "Norte".
3. **H6** — BDPROMEDIO de ventas donde Categoría = "Electrónica".
4. **H7** — BDMAX de ventas en toda la base.
5. **H8** — BDMIN de ventas en toda la base (Monto > 0).
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Usa funciones BD con los criterios definidos. H4: BDSUMA Norte. H5: BDCONTAR Norte. H6: BDPROMEDIO Electrónica. H7: BDMAX general. H8: BDMIN (monto>0).",
  "initialData": {
    "A1": "Vendedor",
    "B1": "Región",
    "C1": "Categoría",
    "D1": "Monto",
    "A2": "Ana",
    "B2": "Norte",
    "C2": "Electrónica",
    "D2": 12500,
    "A3": "Luis",
    "B3": "Sur",
    "C3": "Ropa",
    "D3": 8400,
    "A4": "María",
    "B4": "Norte",
    "C4": "Ropa",
    "D4": 6300,
    "A5": "Carlos",
    "B5": "Centro",
    "C5": "Electrónica",
    "D5": 15000,
    "A6": "Sofía",
    "B6": "Sur",
    "C6": "Electrónica",
    "D6": 9200,
    "A7": "Pedro",
    "B7": "Norte",
    "C7": "Alimentos",
    "D7": 4500,
    "A8": "Laura",
    "B8": "Sur",
    "C8": "Ropa",
    "D8": 7100,
    "F1": "--- Criterios ---",
    "G1": "Criterio",
    "H1": "Región",
    "H2": "Norte",
    "I1": "Categoría",
    "I2": "Electrónica",
    "J1": "Monto",
    "J2": ">0",
    "G4": "Total Norte",
    "G5": "Cantidad Norte",
    "G6": "Prom. Electrónica",
    "G7": "Venta Máxima",
    "G8": "Venta Mínima"
  },
  "expectedFormulas": {
    "H4": "=BDSUMA(A1:D8,\"Monto\",H1:H2)",
    "H5": "=BDCONTAR(A1:D8,\"Monto\",H1:H2)",
    "H6": "=BDPROMEDIO(A1:D8,\"Monto\",I1:I2)",
    "H7": "=BDMAX(A1:D8,\"Monto\",J1:J2)",
    "H8": "=BDMIN(A1:D8,\"Monto\",J1:J2)"
  },
  "validate": {
    "H4": 23300,
    "H5": 3,
    "H6": 12233.33,
    "H7": 15000,
    "H8": 4500
  }
}
EXCEL,
        ];

        // ── Lección 18: Introducción a macros ─────────────────────────────
        if ($l = $lessons->get(18)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Automatiza cálculos repetitivos con lógica de macros',
            'language'     => 'excel',
            'description'  => <<<'MD'
Las **macros** automatizan tareas repetitivas en Excel. Antes de escribir VBA, es importante entender la lógica que una macro ejecutaría.

En este ejercicio simularás lo que una macro haría: aplicar un **proceso de cierre mensual** a datos financieros.

La macro debería:
1. Calcular el **total de ingresos** y **total de gastos** del mes.
2. Calcular la **utilidad bruta** y el **margen**.
3. Aplicar el **impuesto** (30% sobre utilidad positiva).
4. Calcular la **utilidad neta**.
5. Determinar el **estado del mes** (Superávit/Déficit).

Construye las fórmulas que la macro calcularía automáticamente:

1. **B9** — Total ingresos (SUMA).
2. **B14** — Total gastos (SUMA).
3. **B16** — Utilidad bruta (Ingresos − Gastos).
4. **B17** — Impuesto (30% de utilidad si es positiva, 0 si no).
5. **B18** — Utilidad neta (Bruta − Impuesto).
6. **B19** — Estado: "Superávit" si neta > 0, "Déficit" si no.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Simula el cierre mensual: B9 total ingresos, B14 total gastos, B16 utilidad bruta, B17 impuesto (30% si positiva), B18 utilidad neta, B19 estado.",
  "initialData": {
    "A1": "=== INGRESOS DEL MES ===",
    "A2": "Ventas producto A",
    "B2": 85000,
    "A3": "Ventas producto B",
    "B3": 62000,
    "A4": "Ventas producto C",
    "B4": 41000,
    "A5": "Servicios",
    "B5": 28000,
    "A6": "Comisiones recibidas",
    "B6": 5500,
    "A7": "Intereses ganados",
    "B7": 1200,
    "A8": "Otros ingresos",
    "B8": 3000,
    "A9": "TOTAL INGRESOS",
    "A10": "=== GASTOS DEL MES ===",
    "A11": "Nómina",
    "B11": 95000,
    "A12": "Renta y servicios",
    "B12": 25000,
    "A13": "Materiales",
    "B13": 18000,
    "A14": "TOTAL GASTOS",
    "A16": "Utilidad Bruta",
    "A17": "Impuesto (30%)",
    "A18": "Utilidad Neta",
    "A19": "Estado"
  },
  "expectedFormulas": {
    "B9": "=SUMA(B2:B8)",
    "B14": "=SUMA(B11:B13)",
    "B16": "=B9-B14",
    "B17": "=SI(B16>0,B16*0.3,0)",
    "B18": "=B16-B17",
    "B19": "=SI(B18>0,\"Superávit\",\"Déficit\")"
  },
  "validate": {
    "B9": 225700,
    "B14": 138000,
    "B16": 87700,
    "B17": 26310,
    "B18": 61390,
    "B19": "Superávit"
  }
}
EXCEL,
        ];

        // ── Lección 19: Proyecto: Dashboard ──────────────────────────────
        if ($l = $lessons->get(19)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Construye un dashboard de ventas con KPIs',
            'language'     => 'excel',
            'description'  => <<<'MD'
En este proyecto final construirás un **Dashboard de Ventas** que consolida múltiples KPIs (Key Performance Indicators) usando las técnicas aprendidas en el curso.

El dashboard incluye:

### Datos fuente (A1:E13)
Ventas mensuales del año con Mes, Región, Producto, Unidades y Monto.

### KPIs a calcular
1. **H2** — Ventas totales del año.
2. **H3** — Número total de transacciones.
3. **H4** — Ticket promedio (Total / Transacciones).
4. **H5** — Venta máxima del periodo.
5. **H6** — Venta mínima del periodo.
6. **H9** — Ventas región Norte (SUMAR.SI).
7. **H10** — Ventas región Sur.
8. **H11** — Ventas región Centro.
9. **H12** — Región líder (INDICE+COINCIDIR sobre H9:H11).
10. **H15** — Ventas de "Laptop" (SUMAR.SI por producto).
11. **H16** — Ventas de "Software".
12. **H17** — Ventas de "Servicio".
13. **H18** — Producto estrella (mayor ventas).
14. **H20** — Meta anual fija (500000).
15. **H21** — % cumplimiento de meta.
16. **H22** — ¿Meta alcanzada? (Sí/No).

¡Aplica todo lo aprendido en un solo ejercicio integrador!
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Construye el dashboard completo: KPIs generales (H2:H6), por región (H9:H12), por producto (H15:H18), y meta (H20:H22).",
  "initialData": {
    "A1": "Mes",
    "B1": "Región",
    "C1": "Producto",
    "D1": "Unidades",
    "E1": "Monto",
    "A2": "Enero",
    "B2": "Norte",
    "C2": "Laptop",
    "D2": 10,
    "E2": 160000,
    "A3": "Febrero",
    "B3": "Sur",
    "C3": "Software",
    "D3": 25,
    "E3": 37500,
    "A4": "Marzo",
    "B4": "Centro",
    "C4": "Servicio",
    "D4": 8,
    "E4": 24000,
    "A5": "Abril",
    "B5": "Norte",
    "C5": "Software",
    "D5": 30,
    "E5": 45000,
    "A6": "Mayo",
    "B6": "Sur",
    "C6": "Laptop",
    "D6": 5,
    "E6": 80000,
    "A7": "Junio",
    "B7": "Centro",
    "C7": "Servicio",
    "D7": 12,
    "E7": 36000,
    "A8": "Julio",
    "B8": "Norte",
    "C8": "Laptop",
    "D8": 8,
    "E8": 128000,
    "A9": "Agosto",
    "B9": "Sur",
    "C9": "Software",
    "D9": 20,
    "E9": 30000,
    "A10": "Septiembre",
    "B10": "Centro",
    "C10": "Laptop",
    "D10": 6,
    "E10": 96000,
    "A11": "Octubre",
    "B11": "Norte",
    "C11": "Servicio",
    "D11": 15,
    "E11": 45000,
    "A12": "Noviembre",
    "B12": "Sur",
    "C12": "Servicio",
    "D12": 10,
    "E12": 30000,
    "A13": "Diciembre",
    "B13": "Centro",
    "C13": "Software",
    "D13": 35,
    "E13": 52500,
    "G1": "=== DASHBOARD DE VENTAS ===",
    "G2": "Ventas Totales",
    "G3": "Total Transacciones",
    "G4": "Ticket Promedio",
    "G5": "Venta Máxima",
    "G6": "Venta Mínima",
    "G8": "=== POR REGIÓN ===",
    "G9": "Norte",
    "G10": "Sur",
    "G11": "Centro",
    "G12": "Región Líder",
    "G14": "=== POR PRODUCTO ===",
    "G15": "Laptop",
    "G16": "Software",
    "G17": "Servicio",
    "G18": "Producto Estrella",
    "G19": "=== META ANUAL ===",
    "G20": "Meta",
    "G21": "% Cumplimiento",
    "G22": "¿Alcanzada?"
  },
  "expectedFormulas": {
    "H2": "=SUMA(E2:E13)",
    "H3": "=CONTARA(A2:A13)",
    "H4": "=H2/H3",
    "H5": "=MAX(E2:E13)",
    "H6": "=MIN(E2:E13)",
    "H9": "=SUMAR.SI(B2:B13,\"Norte\",E2:E13)",
    "H10": "=SUMAR.SI(B2:B13,\"Sur\",E2:E13)",
    "H11": "=SUMAR.SI(B2:B13,\"Centro\",E2:E13)",
    "H12": "=INDICE(G9:G11,COINCIDIR(MAX(H9:H11),H9:H11,0))",
    "H15": "=SUMAR.SI(C2:C13,\"Laptop\",E2:E13)",
    "H16": "=SUMAR.SI(C2:C13,\"Software\",E2:E13)",
    "H17": "=SUMAR.SI(C2:C13,\"Servicio\",E2:E13)",
    "H18": "=INDICE(G15:G17,COINCIDIR(MAX(H15:H17),H15:H17,0))",
    "H20": 500000,
    "H21": "=H2/H20*100",
    "H22": "=SI(H2>=H20,\"Sí\",\"No\")"
  },
  "validate": {
    "H2": 764000,
    "H3": 12,
    "H4": 63666.67,
    "H5": 160000,
    "H6": 24000,
    "H9": 378000,
    "H10": 177500,
    "H11": 208500,
    "H12": "Norte",
    "H15": 464000,
    "H16": 165000,
    "H17": 135000,
    "H18": "Laptop",
    "H20": 500000,
    "H21": 152.8,
    "H22": "Sí"
  }
}
EXCEL,
        ];

        return $ex;
    }
}
