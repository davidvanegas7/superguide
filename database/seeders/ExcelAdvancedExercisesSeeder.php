<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExcelAdvancedExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'excel-avanzado')->first();

        if (! $course) {
            $this->command->warn('Excel Avanzado course not found.');
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

        $this->command->info('Excel Avanzado exercises seeded: ' . count($exercises) . ' exercises.');
    }

    private function exercises(\Illuminate\Support\Collection $lessons): array
    {
        $ex = [];

        // ── Lección 1: Power Query ──────────────────────────────────────────
        if ($l = $lessons->get(1)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Transformaciones ETL con Power Query',
            'language'     => 'excel',
            'description'  => <<<'MD'
Simula transformaciones de **Power Query** sobre datos de ventas sucios.
Aplica pasos ETL: eliminar filas vacías, dividir columnas, cambiar tipos y crear columnas calculadas.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Los datos de ventas tienen problemas de calidad. Crea fórmulas que simulen las transformaciones de Power Query: 1) En D2:D6 extrae el código de país de la columna C (primeros 2 caracteres). 2) En E2:E6 calcula el total (Cantidad * Precio). 3) En E7 calcula el gran total.",
  "initialData": {
    "A1": "Producto", "B1": "Cantidad", "C1": "País-Ciudad", "D1": "CódigoPaís", "E1": "Total",
    "A2": "Laptop",    "B2": 5,  "C2": "MX-CDMX",
    "A3": "Monitor",   "B3": 10, "C3": "CO-Bogotá",
    "A4": "Teclado",   "B4": 25, "C4": "AR-Buenos Aires",
    "A5": "Mouse",     "B5": 50, "C5": "MX-Monterrey",
    "A6": "Webcam",    "B6": 15, "C6": "CL-Santiago",
    "F1": "Precio",
    "F2": 800, "F3": 350, "F4": 45, "F5": 15, "F6": 60
  },
  "expectedFormulas": {
    "D2": "=IZQUIERDA(C2,2)",
    "E2": "=B2*F2",
    "E7": "=SUMA(E2:E6)"
  },
  "validate": {
    "D2": "MX", "D3": "CO", "D4": "AR",
    "E2": 4000, "E3": 3500, "E4": 1125, "E5": 750, "E6": 900,
    "E7": 10275
  }
}
EXCEL,
        ];

        // ── Lección 2: Power Pivot ──────────────────────────────────────────
        if ($l = $lessons->get(2)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Modelo de datos con relaciones',
            'language'     => 'excel',
            'description'  => <<<'MD'
Simula un modelo **Power Pivot** con tabla de hechos (Ventas) y dimensión (Productos).
Usa BUSCARV para traer datos de la dimensión y crea medidas agregadas como en un modelo relacional.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Tienes ventas (A:C) y catálogo de productos (F:H). 1) En D2:D5 usa BUSCARV para traer la Categoría del producto. 2) En E2:E5 calcula Ingreso = Cantidad * PrecioUnitario (tráelo con BUSCARV). 3) En H2 calcula el ingreso total de la categoría 'Electrónica' con SUMAR.SI.",
  "initialData": {
    "A1": "IdProducto", "B1": "Cantidad", "C1": "Fecha", "D1": "Categoría", "E1": "Ingreso",
    "A2": "P001", "B2": 3,  "C2": "2024-01-15",
    "A3": "P002", "B3": 5,  "C3": "2024-01-16",
    "A4": "P003", "B4": 2,  "C4": "2024-01-17",
    "A5": "P001", "B5": 1,  "C5": "2024-01-18",
    "F1": "IdProducto", "G1": "Precio", "H1": "Categoría",
    "F2": "P001", "G2": 500, "H2": "Electrónica",
    "F3": "P002", "G3": 25,  "H3": "Oficina",
    "F4": "P003", "G4": 1200,"H4": "Electrónica"
  },
  "expectedFormulas": {
    "D2": "=BUSCARV(A2,$F$2:$H$4,3,FALSO)",
    "E2": "=B2*BUSCARV(A2,$F$2:$H$4,2,FALSO)"
  },
  "validate": {
    "D2": "Electrónica", "D3": "Oficina", "D4": "Electrónica", "D5": "Electrónica",
    "E2": 1500, "E3": 125, "E4": 2400, "E5": 500
  }
}
EXCEL,
        ];

        // ── Lección 3: DAX ──────────────────────────────────────────────────
        if ($l = $lessons->get(3)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Medidas DAX simuladas en Excel',
            'language'     => 'excel',
            'description'  => <<<'MD'
Simula medidas **DAX** usando funciones de Excel. Crea KPIs típicos de un modelo de datos:
Total Ventas, Ventas Año Anterior, Crecimiento % y Acumulado YTD.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Datos de ventas mensuales para 2023 y 2024. 1) En C2:C13 calcula la diferencia mes a mes (2024 - 2023). 2) En D2:D13 calcula el % de crecimiento ((2024-2023)/2023). 3) En E2:E13 calcula el acumulado YTD de 2024 (suma desde enero hasta el mes actual).",
  "initialData": {
    "A1": "Mes", "B1": "Ventas2023", "C1": "Ventas2024", "D1": "Diferencia", "E1": "Crecimiento%", "F1": "YTD 2024",
    "A2": "Ene", "B2": 45000, "C2": 52000,
    "A3": "Feb", "B3": 38000, "C3": 41000,
    "A4": "Mar", "B4": 52000, "C4": 58000,
    "A5": "Abr", "B5": 41000, "C5": 47000,
    "A6": "May", "B6": 55000, "C6": 62000,
    "A7": "Jun", "B7": 48000, "C7": 55000,
    "A8": "Jul", "B8": 60000, "C8": 68000,
    "A9": "Ago", "B9": 53000, "C9": 59000,
    "A10": "Sep", "B10": 47000, "C10": 54000,
    "A11": "Oct", "B11": 58000, "C11": 65000,
    "A12": "Nov", "B12": 62000, "C12": 71000,
    "A13": "Dic", "B13": 70000, "C13": 80000
  },
  "expectedFormulas": {
    "D2": "=C2-B2",
    "E2": "=(C2-B2)/B2",
    "F2": "=SUMA(C$2:C2)"
  },
  "validate": {
    "D2": 7000, "D3": 3000,
    "E2": 0.1556, "E3": 0.0789,
    "F2": 52000, "F3": 93000, "F4": 151000
  }
}
EXCEL,
        ];

        // ── Lección 4: VBA Fundamentos ──────────────────────────────────────
        if ($l = $lessons->get(4)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Lógica de programación aplicada a Excel',
            'language'     => 'excel',
            'description'  => <<<'MD'
Simula la lógica de un macro VBA usando fórmulas. Implementa un **clasificador automático**
que asigne categorías según rangos de valores, como haría un Sub con If-ElseIf en VBA.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Clasifica empleados según su puntuación de desempeño. 1) En C2:C8 asigna la categoría: >=90 'Excelente', >=75 'Bueno', >=60 'Aceptable', <60 'Necesita mejora'. 2) En D2:D8 calcula el bono: Excelente=20%, Bueno=10%, Aceptable=5%, otro=0%. 3) En E2:E8 calcula el monto del bono (Salario * Porcentaje).",
  "initialData": {
    "A1": "Empleado", "B1": "Puntuación", "C1": "Categoría", "D1": "% Bono", "E1": "Monto Bono", "F1": "Salario",
    "A2": "Ana García",    "B2": 95, "F2": 45000,
    "A3": "Carlos López",  "B3": 82, "F3": 38000,
    "A4": "Diana Ruiz",    "B4": 67, "F4": 35000,
    "A5": "Eduardo Soto",  "B5": 91, "F5": 52000,
    "A6": "Fernanda Gil",  "B6": 55, "F6": 32000,
    "A7": "Gabriel Mora",  "B7": 78, "F7": 41000,
    "A8": "Helena Cruz",   "B8": 88, "F8": 47000
  },
  "expectedFormulas": {
    "C2": "=SI(B2>=90,\"Excelente\",SI(B2>=75,\"Bueno\",SI(B2>=60,\"Aceptable\",\"Necesita mejora\")))",
    "D2": "=SI(B2>=90,0.2,SI(B2>=75,0.1,SI(B2>=60,0.05,0)))",
    "E2": "=F2*D2"
  },
  "validate": {
    "C2": "Excelente", "C3": "Bueno", "C4": "Aceptable", "C6": "Necesita mejora",
    "D2": 0.2, "D3": 0.1, "D4": 0.05, "D6": 0,
    "E2": 9000, "E3": 3800
  }
}
EXCEL,
        ];

        // ── Lección 5: VBA Manejo de datos ──────────────────────────────────
        if ($l = $lessons->get(5)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Procesamiento masivo de datos',
            'language'     => 'excel',
            'description'  => <<<'MD'
Simula operaciones de procesamiento masivo de datos como las que haría un macro VBA:
limpieza, transformación y resumen de un dataset de transacciones.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Procesa las transacciones bancarias. 1) En D2:D8 calcula el saldo acumulado partiendo de un saldo inicial de 5000 en D1 (saldo anterior + monto). 2) En E2:E8 clasifica cada transacción: monto>0 es 'Ingreso', monto<0 es 'Gasto'. 3) En G2 calcula total ingresos y en G3 total gastos con SUMAR.SI.",
  "initialData": {
    "A1": "Fecha", "B1": "Concepto", "C1": "Monto", "D1": 5000, "E1": "Tipo",
    "A2": "01/03", "B2": "Nómina",        "C2": 3500,
    "A3": "03/03", "B3": "Supermercado",   "C3": -450,
    "A4": "05/03", "B4": "Gasolina",       "C4": -80,
    "A5": "10/03", "B5": "Freelance",      "C5": 1200,
    "A6": "15/03", "B6": "Renta",          "C6": -2000,
    "A7": "20/03", "B7": "Venta MKT",      "C7": 800,
    "A8": "28/03", "B8": "Servicios",      "C8": -350,
    "F1": "Resumen", "F2": "Ingresos", "F3": "Gastos", "F4": "Saldo Final",
    "G1": ""
  },
  "expectedFormulas": {
    "D2": "=D1+C2",
    "E2": "=SI(C2>0,\"Ingreso\",\"Gasto\")",
    "G2": "=SUMAR.SI(C2:C8,\">0\")",
    "G3": "=SUMAR.SI(C2:C8,\"<0\")",
    "G4": "=D8"
  },
  "validate": {
    "D2": 8500, "D3": 8050, "D8": 7620,
    "E2": "Ingreso", "E3": "Gasto",
    "G2": 5500, "G3": -2880
  }
}
EXCEL,
        ];

        // ── Lección 6: UserForms ────────────────────────────────────────────
        if ($l = $lessons->get(6)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Formulario de registro con validación',
            'language'     => 'excel',
            'description'  => <<<'MD'
Simula la lógica de un **UserForm** de VBA: validación de campos antes de registrar datos.
Crea fórmulas que validen nombre, email, edad y teléfono como lo haría un formulario.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Valida los datos de un formulario de registro. 1) En D2:D6 valida el nombre (LARGO>2). 2) En E2:E6 valida email (debe contener '@' y '.'). 3) En F2:F6 valida edad (entre 18 y 99). 4) En G2:G6 muestra 'VÁLIDO' solo si las 3 validaciones son verdaderas, 'INVÁLIDO' si no.",
  "initialData": {
    "A1": "Nombre", "B1": "Email", "C1": "Edad", "D1": "Nombre OK", "E1": "Email OK", "F1": "Edad OK", "G1": "Estado",
    "A2": "Ana",         "B2": "ana@mail.com",  "C2": 25,
    "A3": "Bo",          "B3": "bo@test.net",   "C3": 30,
    "A4": "Carlos Ruiz", "B4": "carlos",        "C4": 45,
    "A5": "Diana",       "B5": "d@corp.io",     "C5": 15,
    "A6": "Eduardo",     "B6": "edu@mail.com",  "C6": 35
  },
  "expectedFormulas": {
    "D2": "=LARGO(A2)>2",
    "E2": "=Y(ESNUMERO(HALLAR(\"@\",B2)),ESNUMERO(HALLAR(\".\",B2)))",
    "F2": "=Y(C2>=18,C2<=99)",
    "G2": "=SI(Y(D2,E2,F2),\"VÁLIDO\",\"INVÁLIDO\")"
  },
  "validate": {
    "D2": true, "D3": false,
    "E4": false,
    "F5": false,
    "G2": "VÁLIDO", "G3": "INVÁLIDO", "G4": "INVÁLIDO", "G5": "INVÁLIDO", "G6": "VÁLIDO"
  }
}
EXCEL,
        ];

        // ── Lección 7: LAMBDA y Arrays ──────────────────────────────────────
        if ($l = $lessons->get(7)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Funciones LAMBDA y operaciones con arrays',
            'language'     => 'excel',
            'description'  => <<<'MD'
Trabaja con fórmulas que simulan **LAMBDA** y operaciones de arrays dinámicos.
Crea funciones reutilizables para cálculo de impuestos, descuentos y totales.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Calcula precios finales con impuestos y descuentos. 1) En C2:C6 calcula el subtotal (Cantidad * PrecioUnitario). 2) En D2:D6 aplica el IVA del 16% (=Subtotal*0.16). 3) En E2:E6 aplica descuento según cantidad: >=100 → 15%, >=50 → 10%, >=10 → 5%, otro → 0%. 4) En F2:F6 calcula el total final (Subtotal + IVA - Descuento).",
  "initialData": {
    "A1": "Cantidad", "B1": "PrecioUnit", "C1": "Subtotal", "D1": "IVA", "E1": "Descuento", "F1": "Total",
    "A2": 150, "B2": 25,
    "A3": 75,  "B3": 40,
    "A4": 30,  "B4": 100,
    "A5": 5,   "B5": 500,
    "A6": 200, "B6": 10
  },
  "expectedFormulas": {
    "C2": "=A2*B2",
    "D2": "=C2*0.16",
    "E2": "=C2*SI(A2>=100,0.15,SI(A2>=50,0.1,SI(A2>=10,0.05,0)))",
    "F2": "=C2+D2-E2"
  },
  "validate": {
    "C2": 3750, "C3": 3000,
    "D2": 600, "D3": 480,
    "E2": 562.5, "E3": 300,
    "F2": 3787.5, "F3": 3180
  }
}
EXCEL,
        ];

        // ── Lección 8: Dashboards profesionales ─────────────────────────────
        if ($l = $lessons->get(8)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'KPIs para dashboard ejecutivo',
            'language'     => 'excel',
            'description'  => <<<'MD'
Construye los **KPIs** de un dashboard ejecutivo: ingresos totales, ticket promedio,
tasa de conversión, producto estrella y comparativa con meta.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Calcula KPIs de ventas. 1) H2: Total ingresos (SUMA). 2) H3: Ticket promedio (PROMEDIO de E). 3) H4: Venta máxima (MAX). 4) H5: Productos vendidos (CONTARA de A, sin encabezado). 5) H6: % cumplimiento de meta (Total/Meta, meta=50000). 6) H7: Producto más vendido (INDICE+COINCIDIR con MAX de D).",
  "initialData": {
    "A1": "Producto", "B1": "Categoría", "C1": "PrecioUnit", "D1": "Cantidad", "E1": "Ingreso",
    "A2": "Laptop Pro",   "B2": "Tech",    "C2": 1200, "D2": 15, "E2": 18000,
    "A3": "Mouse Inal.",  "B3": "Tech",    "C3": 25,   "D3": 120,"E3": 3000,
    "A4": "Silla Ergo",   "B4": "Oficina", "C4": 350,  "D4": 30, "E4": 10500,
    "A5": "Monitor 27\"", "B5": "Tech",    "C5": 450,  "D5": 25, "E5": 11250,
    "A6": "Teclado Mec.", "B6": "Tech",    "C6": 80,   "D6": 60, "E6": 4800,
    "A7": "Escritorio",   "B7": "Oficina", "C7": 500,  "D7": 12, "E7": 6000,
    "G1": "KPI",           "H1": "Valor",
    "G2": "Total Ingresos","G3": "Ticket Promedio","G4": "Venta Máxima",
    "G5": "# Productos","G6": "% Meta","G7": "Top Producto",
    "G8": "Meta", "H8": 50000
  },
  "expectedFormulas": {
    "H2": "=SUMA(E2:E7)",
    "H3": "=PROMEDIO(E2:E7)",
    "H4": "=MAX(E2:E7)",
    "H5": "=CONTARA(A2:A7)",
    "H6": "=H2/H8"
  },
  "validate": {
    "H2": 53550, "H3": 8925, "H4": 18000, "H5": 6, "H6": 1.071
  }
}
EXCEL,
        ];

        // ── Lección 9: VBA Eventos ──────────────────────────────────────────
        if ($l = $lessons->get(9)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulación de eventos automáticos',
            'language'     => 'excel',
            'description'  => <<<'MD'
Simula el comportamiento de **eventos VBA** (Worksheet_Change) con fórmulas:
crea campos que se actualizan automáticamente según los valores de otras celdas.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Simula un evento Worksheet_Change: cuando cambia el estado de un pedido, se actualizan automáticamente otros campos. 1) En D2:D6 calcula los días transcurridos (HOY()-C2, usa valores fijos: asume HOY=45678). 2) En E2:E6 muestra un semáforo: estado='Entregado' → '✅', días>5 → '🔴', días>2 → '🟡', otro → '🟢'. 3) En F2:F6 indica si necesita seguimiento: 'Sí' si no está entregado Y tiene más de 3 días.",
  "initialData": {
    "A1": "Pedido", "B1": "Estado", "C1": "FechaPedido", "D1": "Días", "E1": "Semáforo", "F1": "Seguimiento",
    "A2": "PED-001", "B2": "Entregado",   "C2": 45670,
    "A3": "PED-002", "B3": "En tránsito", "C3": 45672,
    "A4": "PED-003", "B4": "Pendiente",   "C4": 45668,
    "A5": "PED-004", "B5": "Entregado",   "C5": 45660,
    "A6": "PED-005", "B6": "Pendiente",   "C6": 45676,
    "G1": "Hoy", "G2": 45678
  },
  "expectedFormulas": {
    "D2": "=$G$2-C2",
    "E2": "=SI(B2=\"Entregado\",\"✅\",SI(D2>5,\"🔴\",SI(D2>2,\"🟡\",\"🟢\")))",
    "F2": "=SI(Y(B2<>\"Entregado\",D2>3),\"Sí\",\"No\")"
  },
  "validate": {
    "D2": 8, "D3": 6, "D6": 2,
    "E2": "✅", "E3": "🔴", "E6": "🟢",
    "F2": "No", "F3": "Sí", "F4": "Sí", "F5": "No", "F6": "No"
  }
}
EXCEL,
        ];

        // ── Lección 10: Lenguaje M ──────────────────────────────────────────
        if ($l = $lessons->get(10)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Transformaciones de texto estilo M',
            'language'     => 'excel',
            'description'  => <<<'MD'
Simula transformaciones del **lenguaje M** de Power Query con fórmulas de Excel:
limpieza de texto, extracción de patrones y normalización de datos.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Limpia datos sucios como haría el lenguaje M. 1) En C2:C6 extrae el nombre sin espacios extra (ESPACIOS). 2) En D2:D6 extrae el dominio del email (todo después de '@'). 3) En E2:E6 normaliza el teléfono: extrae solo los últimos 10 dígitos. 4) En F2:F6 crea un username: primera letra del nombre + apellido, todo en minúsculas.",
  "initialData": {
    "A1": "NombreCompleto", "B1": "Email", "C1": "NombreLimpio", "D1": "Dominio", "E1": "Tel Normalizado", "F1": "Username",
    "A2": "  Ana  García  ",  "B2": "ana@gmail.com",
    "A3": " Carlos  López ",  "B3": "carlos.l@empresa.mx",
    "A4": "Diana Ruiz",       "B4": "diana@outlook.com",
    "A5": " Eduardo  Soto  ", "B5": "edu@corp.io",
    "A6": "  Fernanda Gil ",  "B6": "fer@mail.net"
  },
  "expectedFormulas": {
    "C2": "=ESPACIOS(A2)",
    "D2": "=DERECHA(B2,LARGO(B2)-HALLAR(\"@\",B2))"
  },
  "validate": {
    "C2": "Ana García",
    "C3": "Carlos López",
    "D2": "gmail.com",
    "D3": "empresa.mx"
  }
}
EXCEL,
        ];

        // ── Lección 11: VBA Clases ──────────────────────────────────────────
        if ($l = $lessons->get(11)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Modelado orientado a objetos en hoja',
            'language'     => 'excel',
            'description'  => <<<'MD'
Simula un **modelo de clases VBA** usando la hoja como base de datos.
Implementa propiedades calculadas y métodos como lo haría una Class Module.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Modela una clase 'CuentaBancaria' en la hoja. 1) En E2:E5 calcula el saldo actual: SaldoInicial + SumaDepósitos - SumaRetiros. 2) En F2:F5 asigna el tipo de cuenta: saldo>=10000 'Premium', saldo>=1000 'Estándar', otro 'Básica'. 3) En G2:G5 calcula el interés anual: Premium=5%, Estándar=3%, Básica=1%.",
  "initialData": {
    "A1": "Titular", "B1": "SaldoInicial", "C1": "Depósitos", "D1": "Retiros", "E1": "SaldoActual", "F1": "Tipo", "G1": "InterésAnual",
    "A2": "Ana García",    "B2": 15000, "C2": 3000,  "D2": 2000,
    "A3": "Carlos López",  "B3": 800,   "C3": 500,   "D3": 200,
    "A4": "Diana Ruiz",    "B4": 5000,  "C4": 7000,  "D4": 1500,
    "A5": "Eduardo Soto",  "B5": 25000, "C5": 10000, "D5": 5000
  },
  "expectedFormulas": {
    "E2": "=B2+C2-D2",
    "F2": "=SI(E2>=10000,\"Premium\",SI(E2>=1000,\"Estándar\",\"Básica\"))",
    "G2": "=E2*SI(F2=\"Premium\",0.05,SI(F2=\"Estándar\",0.03,0.01))"
  },
  "validate": {
    "E2": 16000, "E3": 1100, "E4": 10500, "E5": 30000,
    "F2": "Premium", "F3": "Estándar", "F4": "Premium", "F5": "Premium",
    "G2": 800, "G3": 33
  }
}
EXCEL,
        ];

        // ── Lección 12: Add-ins ─────────────────────────────────────────────
        if ($l = $lessons->get(12)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Funciones personalizadas reutilizables',
            'language'     => 'excel',
            'description'  => <<<'MD'
Crea funciones personalizadas simulando lo que haría un **Add-in de Excel**:
conversiones de unidades, cálculos financieros y utilidades de texto.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Crea funciones reutilizables como un add-in. 1) En B2:B5 convierte USD a MXN (tipo de cambio en G1=17.5). 2) En D2:D5 calcula el pago mensual de un préstamo: =PAGO(tasa/12, plazo, -monto). Tasa anual en G2. 3) En F2:F5 crea códigos de producto: primeras 3 letras de categoría (mayúsc) + '-' + id con ceros a la izquierda (4 dígitos).",
  "initialData": {
    "A1": "MontoUSD", "B1": "MontoMXN",
    "A2": 100, "A3": 500, "A4": 1250, "A5": 3000,
    "C1": "Préstamo", "D1": "PagoMensual", "E1": "Plazo",
    "C2": 50000,  "E2": 12,
    "C3": 150000, "E3": 24,
    "C4": 300000, "E4": 36,
    "C5": 500000, "E5": 48,
    "F1": "Categoría", "G1": 17.5, "H1": "Id", "I1": "Código",
    "F2": "Electrónica", "H2": 1,
    "F3": "Oficina",     "H3": 25,
    "F4": "Ropa",        "H4": 130,
    "F5": "Alimentos",   "H5": 7,
    "G2": 0.12
  },
  "expectedFormulas": {
    "B2": "=A2*$G$1",
    "D2": "=PAGO($G$2/12,E2,-C2)",
    "I2": "=MAYUSC(IZQUIERDA(F2,3))&\"-\"&TEXTO(H2,\"0000\")"
  },
  "validate": {
    "B2": 1750, "B3": 8750,
    "I2": "ELE-0001", "I3": "OFI-0025", "I4": "ROP-0130"
  }
}
EXCEL,
        ];

        // ── Lección 13: Optimización ────────────────────────────────────────
        if ($l = $lessons->get(13)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Optimización de fórmulas para rendimiento',
            'language'     => 'excel',
            'description'  => <<<'MD'
Practica técnicas de **optimización** de fórmulas: evitar volátiles,
reducir cálculos redundantes y usar fórmulas eficientes.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Optimiza las fórmulas. 1) En D2:D8 calcula el ranking de ventas usando JERARQUIA. 2) En E2:E8 calcula el % que cada vendedor aporta al total (usa referencia absoluta al total para eficiencia). 3) En F2:F8 calcula el acumulado del % ordenado (suma de E desde la fila 2 hasta la actual). 4) En G2:G8 clasifica en Pareto: acumulado<=80% → 'A', <=95% → 'B', otro → 'C'.",
  "initialData": {
    "A1": "Vendedor", "B1": "Región", "C1": "Ventas", "D1": "Ranking", "E1": "% Total", "F1": "% Acum", "G1": "Clase",
    "A2": "María",   "B2": "Norte", "C2": 85000,
    "A3": "Pedro",   "B3": "Sur",   "C3": 42000,
    "A4": "Lucía",   "B4": "Centro","C4": 67000,
    "A5": "Jorge",   "B5": "Norte", "C5": 93000,
    "A6": "Rosa",    "B6": "Este",  "C6": 31000,
    "A7": "Andrés",  "B7": "Oeste", "C7": 55000,
    "A8": "Sofía",   "B8": "Sur",   "C8": 78000,
    "H1": "Total", "H2": "=SUMA(C2:C8)"
  },
  "expectedFormulas": {
    "D2": "=JERARQUIA(C2,$C$2:$C$8)",
    "E2": "=C2/$H$2"
  },
  "validate": {
    "D2": 2, "D5": 1, "D6": 7
  }
}
EXCEL,
        ];

        // ── Lección 14: APIs ────────────────────────────────────────────────
        if ($l = $lessons->get(14)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Procesamiento de datos JSON',
            'language'     => 'excel',
            'description'  => <<<'MD'
Simula el procesamiento de datos provenientes de una **API REST**.
Trabaja con datos tabulares como los que devolvería una consulta a un endpoint.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Procesa datos de una API de clima. 1) En D2:D7 convierte la temperatura de Fahrenheit a Celsius: =(F-32)*5/9. 2) En E2:E7 clasifica el clima: >=30°C 'Caluroso', >=20°C 'Templado', >=10°C 'Fresco', <10°C 'Frío'. 3) En F2:F7 crea una alerta: humedad>80% Y temp>30°C → 'ALERTA', humedad>80% → 'HÚMEDO', otro → 'OK'. 4) En H2 calcula la temperatura promedio en Celsius.",
  "initialData": {
    "A1": "Ciudad", "B1": "País", "C1": "Temp_F", "D1": "Temp_C", "E1": "Clima", "F1": "Alerta", "G1": "Humedad%",
    "A2": "CDMX",      "B2": "MX", "C2": 75, "G2": 55,
    "A3": "Bogotá",    "B3": "CO", "C3": 60, "G3": 82,
    "A4": "Lima",      "B4": "PE", "C4": 68, "G4": 78,
    "A5": "Santiago",  "B5": "CL", "C5": 50, "G5": 45,
    "A6": "Buenos Aires","B6": "AR","C6": 90, "G6": 85,
    "A7": "Cancún",    "B7": "MX", "C7": 95, "G7": 90,
    "H1": "Prom °C"
  },
  "expectedFormulas": {
    "D2": "=(C2-32)*5/9",
    "E2": "=SI(D2>=30,\"Caluroso\",SI(D2>=20,\"Templado\",SI(D2>=10,\"Fresco\",\"Frío\")))",
    "F2": "=SI(Y(G2>80,D2>30),\"ALERTA\",SI(G2>80,\"HÚMEDO\",\"OK\"))",
    "H2": "=PROMEDIO(D2:D7)"
  },
  "validate": {
    "D2": 23.89,
    "E2": "Templado", "E6": "Caluroso", "E5": "Fresco",
    "F6": "ALERTA", "F7": "ALERTA", "F3": "HÚMEDO", "F2": "OK"
  }
}
EXCEL,
        ];

        // ── Lección 15: Depuración ──────────────────────────────────────────
        if ($l = $lessons->get(15)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Diagnóstico y corrección de errores',
            'language'     => 'excel',
            'description'  => <<<'MD'
Practica la **depuración de fórmulas**: identifica y maneja errores comunes
como #DIV/0!, #N/A, #VALOR! usando funciones de control de errores.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Crea fórmulas robustas que manejen errores. 1) En C2:C6 calcula el ratio A/B, pero si B=0 muestra 'N/A' (usa SI para evitar #DIV/0!). 2) En F2:F6 usa BUSCARV pero envuelve en SI.ERROR para mostrar 'No encontrado' si falla. 3) En G2:G6 valida que el valor en A sea numérico: si es número calcula A*2, si no muestra 'Error: no es número'.",
  "initialData": {
    "A1": "ValorA", "B1": "ValorB", "C1": "Ratio", "D1": "Código", "E1": "Nombre",
    "A2": 100,     "B2": 25,  "D2": "P01",
    "A3": 200,     "B3": 0,   "D3": "P02",
    "A4": 150,     "B4": 30,  "D4": "P99",
    "A5": "texto", "B5": 10,  "D5": "P03",
    "A6": 300,     "B6": 0,   "D6": "P01",
    "F1": "Búsqueda", "G1": "Validación",
    "H1": "Código", "I1": "Nombre",
    "H2": "P01", "I2": "Laptop",
    "H3": "P02", "I3": "Mouse",
    "H4": "P03", "I4": "Teclado"
  },
  "expectedFormulas": {
    "C2": "=SI(B2=0,\"N/A\",A2/B2)",
    "F2": "=SI.ERROR(BUSCARV(D2,$H$2:$I$4,2,FALSO),\"No encontrado\")",
    "G2": "=SI(ESNUMERO(A2),A2*2,\"Error: no es número\")"
  },
  "validate": {
    "C2": 4, "C3": "N/A", "C4": 5, "C6": "N/A",
    "F2": "Laptop", "F3": "Mouse", "F4": "No encontrado", "F5": "Teclado",
    "G2": 200, "G4": 300, "G5": "Error: no es número"
  }
}
EXCEL,
        ];

        // ── Lección 16: Estadísticas ────────────────────────────────────────
        if ($l = $lessons->get(16)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Análisis estadístico avanzado',
            'language'     => 'excel',
            'description'  => <<<'MD'
Realiza un **análisis estadístico** completo: medidas de tendencia central,
dispersión, percentiles y detección de outliers.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Analiza las calificaciones de un examen. 1) En G2: promedio. 2) En G3: mediana. 3) En G4: moda. 4) En G5: desviación estándar (DESVEST). 5) En G6: mínimo. 6) En G7: máximo. 7) En G8: rango (max-min). 8) En G9: percentil 25 (PERCENTIL). 9) En G10: percentil 75. 10) En C2:C15 clasifica: >=G10 'Superior', >=G2 'Promedio+', otro 'Bajo promedio'.",
  "initialData": {
    "A1": "Alumno", "B1": "Nota", "C1": "Clasificación",
    "A2": "Alumno1",  "B2": 85,
    "A3": "Alumno2",  "B3": 92,
    "A4": "Alumno3",  "B4": 67,
    "A5": "Alumno4",  "B5": 78,
    "A6": "Alumno5",  "B6": 95,
    "A7": "Alumno6",  "B7": 88,
    "A8": "Alumno7",  "B8": 72,
    "A9": "Alumno8",  "B9": 81,
    "A10": "Alumno9", "B10": 90,
    "A11": "Alumno10","B11": 63,
    "A12": "Alumno11","B12": 77,
    "A13": "Alumno12","B13": 84,
    "A14": "Alumno13","B14": 91,
    "A15": "Alumno14","B15": 70,
    "F1": "Estadística", "G1": "Valor",
    "F2": "Promedio", "F3": "Mediana", "F4": "Moda", "F5": "Desv.Est",
    "F6": "Mínimo", "F7": "Máximo", "F8": "Rango", "F9": "P25", "F10": "P75"
  },
  "expectedFormulas": {
    "G2": "=PROMEDIO(B2:B15)",
    "G3": "=MEDIANA(B2:B15)",
    "G4": "=MODA(B2:B15)",
    "G5": "=DESVEST(B2:B15)",
    "G6": "=MIN(B2:B15)",
    "G7": "=MAX(B2:B15)",
    "G8": "=G7-G6",
    "G9": "=PERCENTIL(B2:B15,0.25)",
    "G10": "=PERCENTIL(B2:B15,0.75)"
  },
  "validate": {
    "G2": 80.93, "G3": 82.5, "G6": 63, "G7": 95, "G8": 32
  }
}
EXCEL,
        ];

        // ── Lección 17: Colaboración en la nube ─────────────────────────────
        if ($l = $lessons->get(17)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Gestión de permisos y control de versiones',
            'language'     => 'excel',
            'description'  => <<<'MD'
Crea un sistema de **control de acceso** simulado: asigna permisos según roles,
rastrea cambios y gestiona versiones de un documento compartido.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Gestiona permisos de un archivo compartido. 1) En D2:D7 asigna permisos según rol: 'Admin' → 'Editar+Compartir', 'Editor' → 'Editar', 'Visor' → 'Solo lectura'. 2) En E2:E7 calcula los días desde el último acceso (usa G1 como fecha actual). 3) En F2:F7 marca como 'Inactivo' si no accedió en 30+ días, 'Activo' si no.",
  "initialData": {
    "A1": "Usuario", "B1": "Email", "C1": "Rol", "D1": "Permisos", "E1": "Días sin acceso", "F1": "Estado",
    "A2": "Ana",     "B2": "ana@corp.com",    "C2": "Admin",
    "A3": "Carlos",  "B3": "carlos@corp.com", "C3": "Editor",
    "A4": "Diana",   "B4": "diana@ext.com",   "C4": "Visor",
    "A5": "Eduardo", "B5": "edu@corp.com",    "C5": "Editor",
    "A6": "Fernanda","B6": "fer@ext.com",     "C6": "Visor",
    "A7": "Gabriel", "B7": "gab@corp.com",    "C7": "Admin",
    "G1": 45678,
    "H1": "ÚltimoAcceso",
    "H2": 45675, "H3": 45640, "H4": 45670, "H5": 45600, "H6": 45678, "H7": 45650
  },
  "expectedFormulas": {
    "D2": "=SI(C2=\"Admin\",\"Editar+Compartir\",SI(C2=\"Editor\",\"Editar\",\"Solo lectura\"))",
    "E2": "=$G$1-H2",
    "F2": "=SI(E2>=30,\"Inactivo\",\"Activo\")"
  },
  "validate": {
    "D2": "Editar+Compartir", "D3": "Editar", "D4": "Solo lectura",
    "E2": 3, "E3": 38, "E5": 78,
    "F2": "Activo", "F3": "Inactivo", "F5": "Inactivo", "F6": "Activo"
  }
}
EXCEL,
        ];

        // ── Lección 18: Python + Excel ──────────────────────────────────────
        if ($l = $lessons->get(18)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Análisis de datos estilo Python/Pandas',
            'language'     => 'excel',
            'description'  => <<<'MD'
Simula operaciones comunes de **pandas** usando fórmulas de Excel:
filtrado, agrupación, transformación y análisis exploratorio de datos.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Realiza un análisis exploratorio de datos de ventas como lo harías con pandas. 1) En F2 calcula las ventas totales. 2) En F3 calcula el promedio de ventas por transacción. 3) En F4 cuenta las transacciones de la categoría 'Electrónica' (CONTAR.SI). 4) En F5 calcula la suma de ventas solo de 'Electrónica' (SUMAR.SI). 5) En F6 calcula la desviación estándar. 6) En G2:G10 crea una columna 'Normalizado': (valor - mínimo) / (máximo - mínimo).",
  "initialData": {
    "A1": "Id", "B1": "Categoría", "C1": "Producto", "D1": "Ventas",
    "A2": 1, "B2": "Electrónica", "C2": "Laptop",   "D2": 15000,
    "A3": 2, "B3": "Ropa",        "C3": "Camisa",    "D3": 450,
    "A4": 3, "B4": "Electrónica", "C4": "Teléfono",  "D4": 8000,
    "A5": 4, "B5": "Hogar",       "C5": "Silla",     "D5": 2500,
    "A6": 5, "B6": "Electrónica", "C6": "Tablet",    "D6": 5500,
    "A7": 6, "B7": "Ropa",        "C7": "Pantalón",  "D7": 600,
    "A8": 7, "B8": "Hogar",       "C8": "Mesa",      "D8": 3200,
    "A9": 8, "B9": "Electrónica", "C9": "Monitor",   "D9": 4500,
    "A10":9, "B10":"Ropa",        "C10":"Zapatos",    "D10": 1200,
    "E1": "Métrica", "F1": "Valor", "G1": "Normalizado",
    "E2": "Total", "E3": "Promedio", "E4": "#Electrónica", "E5": "Σ Electrónica", "E6": "Desv.Est"
  },
  "expectedFormulas": {
    "F2": "=SUMA(D2:D10)",
    "F3": "=PROMEDIO(D2:D10)",
    "F4": "=CONTAR.SI(B2:B10,\"Electrónica\")",
    "F5": "=SUMAR.SI(B2:B10,\"Electrónica\",D2:D10)",
    "F6": "=DESVEST(D2:D10)",
    "G2": "=(D2-MIN($D$2:$D$10))/(MAX($D$2:$D$10)-MIN($D$2:$D$10))"
  },
  "validate": {
    "F2": 40950, "F3": 4550, "F4": 4, "F5": 33000,
    "G2": 1, "G3": 0
  }
}
EXCEL,
        ];

        // ── Lección 19: Proyecto final ──────────────────────────────────────
        if ($l = $lessons->get(19)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema empresarial integral',
            'language'     => 'excel',
            'description'  => <<<'MD'
Construye un **sistema empresarial** completo: inventario con valorización,
análisis de rentabilidad, alertas automáticas e indicadores financieros clave.
MD,
            'starter_code' => <<<'EXCEL'
{
  "instructions": "Construye un sistema de gestión empresarial. 1) En E2:E8 calcula el valor del inventario (Stock * CostoUnit). 2) En F2:F8 calcula el margen bruto % ((Precio-Costo)/Precio). 3) En G2:G8 crea alertas de stock: stock<=PuntoReorden → '🔴 Reordenar', stock<=PuntoReorden*2 → '🟡 Bajo', otro → '🟢 OK'. 4) En J2 calcula el valor total del inventario. 5) En J3 calcula el margen promedio ponderado por ventas. 6) En J4 cuenta productos que necesitan reorden. 7) En J5 identifica el producto más rentable (mayor margen).",
  "initialData": {
    "A1": "Producto", "B1": "Stock", "C1": "CostoUnit", "D1": "PrecioVenta", "E1": "ValorInv", "F1": "Margen%", "G1": "AlertaStock", "H1": "PuntoReorden",
    "A2": "Laptop Pro",    "B2": 45,  "C2": 800,  "D2": 1299, "H2": 20,
    "A3": "Mouse BT",      "B3": 200, "C3": 8,    "D3": 29,   "H3": 50,
    "A4": "Monitor 4K",    "B4": 18,  "C4": 280,  "D4": 499,  "H4": 15,
    "A5": "Teclado Mec.",  "B5": 85,  "C5": 35,   "D5": 89,   "H5": 30,
    "A6": "Webcam HD",     "B6": 12,  "C6": 25,   "D6": 69,   "H6": 25,
    "A7": "Hub USB-C",     "B7": 150, "C7": 12,   "D7": 45,   "H7": 40,
    "A8": "SSD 1TB",       "B8": 30,  "C8": 55,   "D8": 119,  "H8": 20,
    "I1": "KPI", "J1": "Valor",
    "I2": "Valor Total Inventario", "I3": "Margen Prom.", "I4": "# Reorden", "I5": "Más Rentable"
  },
  "expectedFormulas": {
    "E2": "=B2*C2",
    "F2": "=(D2-C2)/D2",
    "G2": "=SI(B2<=H2,\"🔴 Reordenar\",SI(B2<=H2*2,\"🟡 Bajo\",\"🟢 OK\"))",
    "J2": "=SUMA(E2:E8)",
    "J4": "=CONTAR.SI(G2:G8,\"🔴*\")"
  },
  "validate": {
    "E2": 36000, "E3": 1600, "E6": 300,
    "F2": 0.384, "F3": 0.724,
    "G2": "🟢 OK", "G4": "🟡 Bajo", "G6": "🔴 Reordenar"
  }
}
EXCEL,
        ];

        return $ex;
    }
}
