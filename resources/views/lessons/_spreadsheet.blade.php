{{-- ══════════════════════════════════════════════════════════════════════════
     _spreadsheet.blade.php — Hoja de cálculo interactiva estilo Excel
     Se activa automáticamente cuando exercise->language === 'excel'
   ══════════════════════════════════════════════════════════════════════════ --}}

{{-- Datos del ejercicio como JSON --}}
<script type="application/json" id="sg-spreadsheet-data">
{!! json_encode([
    'starterCode' => $exercise->starter_code,
    'title'       => $exercise->title,
]) !!}
</script>

<div class="mt-12 border-t-2 border-dashed border-emerald-200 pt-10" id="ejercicio-practica">

    {{-- Cabecera --}}
    <div class="flex items-center gap-3 mb-5">
        <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white text-xl shrink-0">📊</div>
        <div>
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-0.5">Ejercicio interactivo — Hoja de cálculo</p>
            <h2 class="text-lg font-bold text-gray-900 leading-tight">{{ $exercise->title }}</h2>
        </div>
    </div>

    {{-- Descripción --}}
    <div class="prose prose-sm max-w-none mb-5 p-4 bg-emerald-50 rounded-xl border border-emerald-100 text-gray-800">
        {!! $exercise->description_html !!}
    </div>

    {{-- Barra de fórmulas --}}
    <div class="flex items-center bg-white border border-gray-300 rounded-t-xl overflow-hidden">
        <div id="sg-cell-ref" class="w-20 text-center text-xs font-mono font-bold text-gray-600 border-r border-gray-300 py-2 bg-gray-50 select-none">A1</div>
        <div class="px-2 text-gray-400 text-sm font-bold select-none">ƒ</div>
        <input id="sg-formula-bar" type="text"
               class="flex-1 px-2 py-2 text-sm font-mono outline-none border-none focus:ring-0"
               placeholder="Escribe un valor o fórmula (ej: =SUMA(A1:A5))" />
    </div>

    {{-- Hoja de cálculo --}}
    <div class="border border-t-0 border-gray-300 rounded-b-xl overflow-auto shadow-lg bg-white" style="max-height: 500px;">
        <table id="sg-spreadsheet" class="border-collapse w-full" style="min-width: 600px;">
            <thead id="sg-ss-head"></thead>
            <tbody id="sg-ss-body"></tbody>
        </table>
    </div>

    {{-- Instrucciones --}}
    <div id="sg-instructions" class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800 flex items-start gap-2">
        <span class="text-lg leading-none">💡</span>
        <span id="sg-instructions-text"></span>
    </div>

    {{-- Controles --}}
    <div class="flex items-center gap-3 mt-4 flex-wrap">
        <button onclick="sgVerifySpreadsheet()"
                class="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-semibold rounded-lg transition-all text-sm shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Verificar
        </button>
        <button onclick="sgResetSpreadsheet()"
                class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:border-gray-400 text-gray-600 font-medium rounded-lg transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Resetear
        </button>
        <span id="sg-ss-status" class="text-sm ml-auto font-medium"></span>
    </div>

    {{-- Resultado --}}
    <div id="sg-ss-result" class="hidden mt-4 rounded-xl overflow-hidden border shadow-inner"></div>
</div>

@push('styles')
<style>
/* ── Estilos de la hoja de cálculo ──────────────────────────── */
#sg-spreadsheet th,
#sg-spreadsheet td {
    border: 1px solid #d1d5db;
    padding: 0;
    text-align: left;
    font-size: 13px;
    position: relative;
}
#sg-spreadsheet th {
    background: #f3f4f6;
    color: #6b7280;
    font-weight: 600;
    text-align: center;
    padding: 6px 8px;
    font-size: 11px;
    text-transform: uppercase;
    user-select: none;
    position: sticky;
    top: 0;
    z-index: 2;
}
#sg-spreadsheet th:first-child {
    position: sticky;
    left: 0;
    z-index: 3;
}
#sg-spreadsheet td:first-child {
    background: #f9fafb;
    color: #6b7280;
    font-weight: 600;
    text-align: center;
    width: 40px;
    min-width: 40px;
    font-size: 11px;
    user-select: none;
    position: sticky;
    left: 0;
    z-index: 1;
    padding: 6px 4px;
}
#sg-spreadsheet td:not(:first-child) {
    min-width: 100px;
    height: 30px;
}
.sg-cell-input {
    width: 100%;
    height: 100%;
    border: none;
    outline: none;
    padding: 4px 6px;
    font-size: 13px;
    font-family: inherit;
    background: transparent;
    box-sizing: border-box;
}
.sg-cell-input:focus {
    background: #eff6ff;
    box-shadow: inset 0 0 0 2px #3b82f6;
}
.sg-cell-readonly {
    background: #f9fafb;
    color: #1f2937;
    cursor: default;
    padding: 4px 6px;
    height: 100%;
    display: flex;
    align-items: center;
}
.sg-cell-editable {
    background: #fefce8;
}
.sg-cell-correct {
    background: #dcfce7 !important;
}
.sg-cell-wrong {
    background: #fee2e2 !important;
}
.sg-cell-formula {
    color: #1e40af;
    font-weight: 500;
}
.sg-cell-number { text-align: right; }
.sg-cell-header-val { font-weight: 600; color: #111827; }

/* ── Fill handle (esquinita de arrastre) ─────────────────────── */
.sg-cell-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
}
.sg-fill-handle {
    position: absolute;
    right: -4px;
    bottom: -4px;
    width: 8px;
    height: 8px;
    background: #2563eb;
    border: 1px solid #fff;
    cursor: crosshair;
    z-index: 5;
    display: none;
    border-radius: 1px;
}
.sg-cell-focused .sg-fill-handle {
    display: block;
}
.sg-fill-preview {
    background: #dbeafe !important;
    outline: 2px dashed #3b82f6;
    outline-offset: -2px;
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    'use strict';

    // ── Configuración ──────────────────────────────────────────
    const raw = document.getElementById('sg-spreadsheet-data');
    if (!raw) return;

    const payload  = JSON.parse(raw.textContent);
    let config;
    try { config = JSON.parse(payload.starterCode); } catch(e) { config = {}; }

    const initialData      = config.initialData || {};
    const expectedFormulas  = config.expectedFormulas || {};
    const validate          = config.validate || {};
    const instructions      = config.instructions || 'Completa las celdas resaltadas en amarillo con las fórmulas correctas.';

    document.getElementById('sg-instructions-text').textContent = instructions;

    // ── Estado ──────────────────────────────────────────────────
    let cellData   = {};  // {cellRef: {value, formula, readonly}}
    let activeCell = null;
    let fillDrag   = null; // {sourceRef, startR, startC, currentR, currentC}
    const ROWS = 15;
    const COLS = 8; // A-H

    function colLetter(i) { return String.fromCharCode(65 + i); }
    function cellRef(r, c) { return colLetter(c) + (r + 1); }
    function parseCellRef(ref) {
        const m = ref.match(/^([A-Z]+)(\d+)$/i);
        if (!m) return null;
        const c = m[1].toUpperCase().charCodeAt(0) - 65;
        const r = parseInt(m[2]) - 1;
        return { r, c };
    }

    // ── Motor de fórmulas ──────────────────────────────────────
    function evaluateFormula(formula) {
        if (!formula || formula[0] !== '=') return formula;

        const expr = formula.substring(1).toUpperCase();

        try {
            // Expandir funciones de Excel a JS
            let jsExpr = expr;

            // SUMA / SUM — soporta rangos y listas
            jsExpr = jsExpr.replace(/(?:SUMA|SUM)\(([^)]+)\)/gi, (_, args) => {
                const vals = expandArgs(args);
                return `(${vals.join('+')}||0)`;
            });

            // PROMEDIO / AVERAGE
            jsExpr = jsExpr.replace(/(?:PROMEDIO|AVERAGE)\(([^)]+)\)/gi, (_, args) => {
                const vals = expandArgs(args);
                return `((${vals.join('+')}||0)/${vals.length})`;
            });

            // MAX
            jsExpr = jsExpr.replace(/MAX\(([^)]+)\)/gi, (_, args) => {
                const vals = expandArgs(args);
                return `Math.max(${vals.join(',')})`;
            });

            // MIN
            jsExpr = jsExpr.replace(/MIN\(([^)]+)\)/gi, (_, args) => {
                const vals = expandArgs(args);
                return `Math.min(${vals.join(',')})`;
            });

            // CONTAR / COUNT
            jsExpr = jsExpr.replace(/(?:CONTAR|COUNT)\(([^)]+)\)/gi, (_, args) => {
                const vals = expandArgs(args);
                return `(${vals.map(v => `(typeof ${v}==='number'?1:0)`).join('+')})`;
            });

            // CONTARA / COUNTA
            jsExpr = jsExpr.replace(/(?:CONTARA|COUNTA)\(([^)]+)\)/gi, (_, args) => {
                const vals = expandArgs(args);
                return `(${vals.map(v => `(${v}!=null&&${v}!==''?1:0)`).join('+')})`;
            });

            // SI / IF
            jsExpr = jsExpr.replace(/(?:SI|IF)\(([^,]+),([^,]+),([^)]+)\)/gi, (_, cond, t, f) => {
                return `(${resolveValue(cond.trim())}?${resolveValue(t.trim())}:${resolveValue(f.trim())})`;
            });

            // CONCATENAR / CONCAT
            jsExpr = jsExpr.replace(/(?:CONCATENAR|CONCAT|CONCATENATE)\(([^)]+)\)/gi, (_, args) => {
                const parts = args.split(',').map(a => resolveValue(a.trim()));
                return `(String(${parts.join(')+String(')}))`;
            });

            // ABS
            jsExpr = jsExpr.replace(/ABS\(([^)]+)\)/gi, (_, a) => `Math.abs(${resolveValue(a.trim())})`);

            // REDONDEAR / ROUND
            jsExpr = jsExpr.replace(/(?:REDONDEAR|ROUND)\(([^,]+),([^)]+)\)/gi, (_, num, dec) => {
                return `(Math.round(${resolveValue(num.trim())}*Math.pow(10,${resolveValue(dec.trim())}))/Math.pow(10,${resolveValue(dec.trim())}))`;
            });

            // POTENCIA / POWER
            jsExpr = jsExpr.replace(/(?:POTENCIA|POWER)\(([^,]+),([^)]+)\)/gi, (_, base, exp) => {
                return `Math.pow(${resolveValue(base.trim())},${resolveValue(exp.trim())})`;
            });

            // RAIZ / SQRT
            jsExpr = jsExpr.replace(/(?:RAIZ|SQRT)\(([^)]+)\)/gi, (_, a) => `Math.sqrt(${resolveValue(a.trim())})`);

            // SUMAR.SI / SUMIF — SUMAR.SI(rango, criterio, rango_suma)
            jsExpr = jsExpr.replace(/(?:SUMAR\.SI|SUMIF)\(([^,]+),([^,]+),([^)]+)\)/gi, (_, range, criteria, sumRange) => {
                const rangeVals = expandRange(range.trim());
                const sumVals   = expandRange(sumRange.trim());
                const crit      = criteria.trim().replace(/"/g, '');
                let parts = [];
                for (let i = 0; i < rangeVals.length; i++) {
                    const sv = sumVals[i] || '0';
                    if (crit.startsWith('>')) {
                        parts.push(`(${rangeVals[i]}>${crit.substring(1)}?${sv}:0)`);
                    } else if (crit.startsWith('<')) {
                        parts.push(`(${rangeVals[i]}<${crit.substring(1)}?${sv}:0)`);
                    } else {
                        parts.push(`(${rangeVals[i]}==${JSON.stringify(crit)}?${sv}:0)`);
                    }
                }
                return `(${parts.join('+')})`;
            });

            // CONTAR.SI / COUNTIF
            jsExpr = jsExpr.replace(/(?:CONTAR\.SI|COUNTIF)\(([^,]+),([^)]+)\)/gi, (_, range, criteria) => {
                const vals = expandRange(range.trim());
                const crit = criteria.trim().replace(/"/g, '');
                let parts = [];
                for (const v of vals) {
                    if (crit.startsWith('>')) parts.push(`(${v}>${crit.substring(1)}?1:0)`);
                    else if (crit.startsWith('<')) parts.push(`(${v}<${crit.substring(1)}?1:0)`);
                    else parts.push(`(${v}==${JSON.stringify(crit)}?1:0)`);
                }
                return `(${parts.join('+')})`;
            });

            // BUSCARV / VLOOKUP simplificado
            jsExpr = jsExpr.replace(/(?:BUSCARV|VLOOKUP)\(([^,]+),([^,]+),([^,]+),([^)]*)\)/gi, (_, lookup, range, colIdx, approx) => {
                // Implementación simplificada - retorna el valor buscado
                return resolveValue(lookup.trim());
            });

            // IZQUIERDA / LEFT
            jsExpr = jsExpr.replace(/(?:IZQUIERDA|LEFT)\(([^,]+),([^)]+)\)/gi, (_, text, n) => {
                return `String(${resolveValue(text.trim())}).substring(0,${resolveValue(n.trim())})`;
            });

            // DERECHA / RIGHT
            jsExpr = jsExpr.replace(/(?:DERECHA|RIGHT)\(([^,]+),([^)]+)\)/gi, (_, text, n) => {
                return `String(${resolveValue(text.trim())}).slice(-${resolveValue(n.trim())})`;
            });

            // MAYUSC / UPPER
            jsExpr = jsExpr.replace(/(?:MAYUSC|UPPER)\(([^)]+)\)/gi, (_, a) => `String(${resolveValue(a.trim())}).toUpperCase()`);

            // MINUSC / LOWER
            jsExpr = jsExpr.replace(/(?:MINUSC|LOWER)\(([^)]+)\)/gi, (_, a) => `String(${resolveValue(a.trim())}).toLowerCase()`);

            // LARGO / LEN
            jsExpr = jsExpr.replace(/(?:LARGO|LEN)\(([^)]+)\)/gi, (_, a) => `String(${resolveValue(a.trim())}).length`);

            // Reemplazar referencias a celdas restantes
            jsExpr = jsExpr.replace(/\b([A-Z]+\d+)\b/g, (match) => {
                const val = getCellNumericValue(match);
                return val !== null ? val : '0';
            });

            // Evaluar
            const result = Function('"use strict"; return (' + jsExpr + ')')();
            return typeof result === 'number' ? Math.round(result * 1e10) / 1e10 : result;

        } catch (e) {
            console.warn('Formula error:', formula, e);
            return '#ERROR';
        }
    }

    function expandArgs(args) {
        const parts = args.split(',');
        let result = [];
        for (const p of parts) {
            const trimmed = p.trim();
            if (trimmed.includes(':')) {
                result.push(...expandRange(trimmed));
            } else {
                result.push(resolveValue(trimmed));
            }
        }
        return result;
    }

    function expandRange(rangeStr) {
        const [start, end] = rangeStr.split(':');
        const s = parseCellRef(start.trim());
        const e = parseCellRef(end.trim());
        if (!s || !e) return [];
        let refs = [];
        for (let r = s.r; r <= e.r; r++) {
            for (let c = s.c; c <= e.c; c++) {
                const ref = cellRef(r, c);
                const val = getCellNumericValue(ref);
                refs.push(val !== null ? String(val) : '0');
            }
        }
        return refs;
    }

    function resolveValue(v) {
        if (!v) return '0';
        // String literal
        if ((v.startsWith('"') && v.endsWith('"')) || (v.startsWith("'") && v.endsWith("'"))) {
            return v;
        }
        // Number
        if (!isNaN(v)) return v;
        // Cell ref
        if (/^[A-Z]+\d+$/i.test(v)) {
            const val = getCellNumericValue(v);
            return val !== null ? String(val) : '0';
        }
        return v;
    }

    function getCellNumericValue(ref) {
        ref = ref.toUpperCase();
        const cell = cellData[ref];
        if (!cell) return null;
        if (cell.formula) {
            const r = evaluateFormula(cell.formula);
            return typeof r === 'number' ? r : (isNaN(Number(r)) ? null : Number(r));
        }
        const n = Number(cell.value);
        return isNaN(n) ? null : n;
    }

    function getCellDisplayValue(ref) {
        ref = ref.toUpperCase();
        const cell = cellData[ref];
        if (!cell) return '';
        if (cell.formula) {
            const result = evaluateFormula(cell.formula);
            return result !== null && result !== undefined ? result : '';
        }
        return cell.value !== null && cell.value !== undefined ? cell.value : '';
    }

    // ── Shift de referencias para fill handle ─────────────────
    function shiftFormula(formula, dRow, dCol) {
        if (!formula || formula[0] !== '=') return formula;
        // Reemplazar referencias de celdas, respetando $ (absolutas)
        return '=' + formula.substring(1).replace(/(\$?)([A-Z]+)(\$?)(\d+)/gi, (match, abCol, col, abRow, row) => {
            let newCol = col.toUpperCase();
            let newRow = parseInt(row);
            // Si no tiene $, es relativa → la desplazamos
            if (!abCol) {
                const colIdx = newCol.charCodeAt(0) - 65 + dCol;
                if (colIdx < 0 || colIdx > 25) return match; // fuera de rango
                newCol = String.fromCharCode(65 + colIdx);
            }
            if (!abRow) {
                newRow = newRow + dRow;
                if (newRow < 1) return match;
            }
            return (abCol || '') + newCol + (abRow || '') + newRow;
        });
    }

    function shiftValue(value, dRow, dCol) {
        // Para valores numéricos: incrementar progresivamente
        if (typeof value === 'number') return value + dRow + dCol;
        // Para texto: copiar tal cual
        return value;
    }

    // ── Inicializar datos de celdas ────────────────────────────
    function initCellData() {
        cellData = {};
        for (const [ref, val] of Object.entries(initialData)) {
            const upper = ref.toUpperCase();
            if (typeof val === 'string' && val.startsWith('=')) {
                cellData[upper] = { formula: val, value: null, readonly: true };
            } else {
                cellData[upper] = { value: val, formula: null, readonly: true };
            }
        }
        // Celdas editables donde el usuario debe escribir fórmulas
        for (const ref of Object.keys(expectedFormulas)) {
            const upper = ref.toUpperCase();
            if (!cellData[upper]) {
                cellData[upper] = { value: '', formula: null, readonly: false };
            } else {
                cellData[upper].readonly = false;
            }
        }
    }

    // ── Renderizar tabla ───────────────────────────────────────
    function renderTable() {
        const thead = document.getElementById('sg-ss-head');
        const tbody = document.getElementById('sg-ss-body');

        // Calcular filas/columnas necesarias
        let maxRow = 0, maxCol = 0;
        const allRefs = [...Object.keys(initialData), ...Object.keys(expectedFormulas), ...Object.keys(validate)];
        for (const ref of allRefs) {
            const p = parseCellRef(ref);
            if (p) { maxRow = Math.max(maxRow, p.r); maxCol = Math.max(maxCol, p.c); }
        }
        const rows = Math.max(maxRow + 3, 8);
        const cols = Math.max(maxCol + 2, 5);

        // Header
        let headHtml = '<tr><th></th>';
        for (let c = 0; c < cols; c++) headHtml += `<th>${colLetter(c)}</th>`;
        headHtml += '</tr>';
        thead.innerHTML = headHtml;

        // Body
        let bodyHtml = '';
        for (let r = 0; r < rows; r++) {
            bodyHtml += `<tr><td>${r + 1}</td>`;
            for (let c = 0; c < cols; c++) {
                const ref = cellRef(r, c);
                const cell = cellData[ref];
                const isEditable = cell && !cell.readonly;
                const isExpected = expectedFormulas.hasOwnProperty(ref);
                const displayVal = getCellDisplayValue(ref);
                const isNum = typeof displayVal === 'number' || (!isNaN(Number(displayVal)) && displayVal !== '');
                const isHeader = (r === 0 || c === 0) && cell && cell.readonly && typeof cell.value === 'string';

                let classes = [];
                if (isEditable || isExpected) classes.push('sg-cell-editable');
                if (isNum) classes.push('sg-cell-number');
                if (isHeader) classes.push('sg-cell-header-val');

                if (cell && cell.readonly && !isExpected) {
                    bodyHtml += `<td><div class="sg-cell-readonly ${classes.join(' ')}">${displayVal}</div></td>`;
                } else {
                    const val = cell ? (cell.formula || cell.value || '') : '';
                    bodyHtml += `<td class="${classes.join(' ')}">
                        <div class="sg-cell-wrapper">
                            <input type="text" class="sg-cell-input ${isNum ? 'sg-cell-number' : ''}"
                                data-ref="${ref}" data-row="${r}" data-col="${c}"
                                value="${String(displayVal).replace(/"/g, '&quot;')}"
                                data-formula="${String(val).replace(/"/g, '&quot;')}"
                                onfocus="sgSelectCell('${ref}', this)"
                                onblur="sgBlurCell('${ref}', this)"
                                onkeydown="sgCellKeydown(event, ${r}, ${c})" />
                            <div class="sg-fill-handle" data-ref="${ref}" data-row="${r}" data-col="${c}"></div>
                        </div>
                    </td>`;
                }
            }
            bodyHtml += '</tr>';
        }
        tbody.innerHTML = bodyHtml;
    }

    // ── Interacción con celdas ─────────────────────────────────
    window.sgSelectCell = function(ref, input) {
        // Quitar clase focused anterior
        document.querySelectorAll('.sg-cell-focused').forEach(el => el.classList.remove('sg-cell-focused'));
        activeCell = ref;
        document.getElementById('sg-cell-ref').textContent = ref;
        const cell = cellData[ref];
        const formulaBar = document.getElementById('sg-formula-bar');
        if (cell && cell.formula) {
            formulaBar.value = cell.formula;
            input.value = cell.formula;
        } else if (cell) {
            formulaBar.value = cell.value || '';
        } else {
            formulaBar.value = '';
        }
        // Marcar wrapper como focused para mostrar fill handle
        const wrapper = input.closest('.sg-cell-wrapper');
        if (wrapper) wrapper.classList.add('sg-cell-focused');
    };

    window.sgBlurCell = function(ref, input) {
        // Si hay fill drag activo, no procesar el blur (el fill handle se encarga)
        if (fillDrag) return;

        const rawVal = input.value.trim();
        if (!cellData[ref]) cellData[ref] = { value: '', formula: null, readonly: false };
        const cell = cellData[ref];

        if (rawVal.startsWith('=')) {
            cell.formula = rawVal;
            cell.value = null;
            const result = evaluateFormula(rawVal);
            input.value = result;
            input.dataset.formula = rawVal;
            input.classList.add('sg-cell-formula');
        } else {
            // Si la celda ya tenía una fórmula y el rawVal es el resultado numérico, preservar la fórmula
            if (cell.formula) {
                const currentResult = String(evaluateFormula(cell.formula));
                if (rawVal === currentResult || rawVal === String(Math.round(Number(currentResult) * 1e10) / 1e10)) {
                    // El usuario no editó nada, solo perdió el foco → mostrar resultado, mantener fórmula
                    input.value = evaluateFormula(cell.formula);
                    recalcAll();
                    return;
                }
            }
            cell.formula = null;
            cell.value = rawVal === '' ? '' : (isNaN(Number(rawVal)) ? rawVal : Number(rawVal));
            input.classList.remove('sg-cell-formula');
        }

        // Recalcular todas las celdas que sean fórmulas
        recalcAll();
    };

    window.sgCellKeydown = function(e, row, col) {
        const cols = document.querySelectorAll('#sg-ss-head th').length - 1;
        let nextRef = null;

        if (e.key === 'Enter' || e.key === 'ArrowDown') {
            e.preventDefault();
            nextRef = cellRef(row + 1, col);
        } else if (e.key === 'Tab' || e.key === 'ArrowRight') {
            if (e.key === 'Tab') e.preventDefault();
            if (e.key === 'ArrowRight') return; // let cursor move inside input
            nextRef = cellRef(row, col + 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            nextRef = cellRef(row - 1, col);
        }

        if (nextRef) {
            const nextInput = document.querySelector(`input[data-ref="${nextRef}"]`);
            if (nextInput) nextInput.focus();
        }
    };

    // Sincronizar la barra de fórmulas
    document.getElementById('sg-formula-bar').addEventListener('input', function() {
        if (!activeCell) return;
        const input = document.querySelector(`input[data-ref="${activeCell}"]`);
        if (input) input.value = this.value;
    });

    document.getElementById('sg-formula-bar').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            if (!activeCell) return;
            const input = document.querySelector(`input[data-ref="${activeCell}"]`);
            if (input) {
                input.value = this.value;
                sgBlurCell(activeCell, input);
            }
        }
    });

    function recalcAll() {
        document.querySelectorAll('#sg-ss-body input.sg-cell-input').forEach(input => {
            const ref = input.dataset.ref;
            const cell = cellData[ref];
            if (cell && cell.formula) {
                const result = evaluateFormula(cell.formula);
                input.value = result;
            }
        });
    }

    // ── Verificar respuestas ───────────────────────────────────
    window.sgVerifySpreadsheet = function() {
        const resultDiv = document.getElementById('sg-ss-result');
        const status    = document.getElementById('sg-ss-status');
        let correct = 0;
        let total   = Object.keys(validate).length;
        let details = [];

        for (const [ref, expected] of Object.entries(validate)) {
            const input = document.querySelector(`input[data-ref="${ref.toUpperCase()}"]`);
            const cell  = cellData[ref.toUpperCase()];
            const actual = getCellDisplayValue(ref.toUpperCase());

            let isCorrect = false;
            if (typeof expected === 'number') {
                isCorrect = Math.abs(Number(actual) - expected) < 0.01;
            } else {
                isCorrect = String(actual).toLowerCase().trim() === String(expected).toLowerCase().trim();
            }

            if (isCorrect) {
                correct++;
                if (input) { input.parentElement.classList.add('sg-cell-correct'); input.parentElement.classList.remove('sg-cell-wrong'); }
                details.push({ ref, ok: true, expected, actual });
            } else {
                if (input) { input.parentElement.classList.add('sg-cell-wrong'); input.parentElement.classList.remove('sg-cell-correct'); }
                details.push({ ref, ok: false, expected, actual });
            }
        }

        const allCorrect = correct === total;

        // Construir resultado
        let html = `<div class="px-4 py-2 flex items-center gap-2 ${allCorrect ? 'bg-green-600' : 'bg-gray-800'}">
            <span class="text-xs font-mono uppercase tracking-wider ${allCorrect ? 'text-green-100' : 'text-gray-400'}">
                Resultado: ${correct}/${total} celdas correctas
            </span>
        </div>
        <div class="${allCorrect ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200'} p-4">`;

        if (allCorrect) {
            html += '<p class="text-green-800 font-semibold mb-2">🎉 ¡Excelente! Todas las celdas son correctas.</p>';
        } else {
            html += '<p class="text-gray-800 font-medium mb-3">Revisa las celdas marcadas en rojo:</p><ul class="space-y-1">';
            for (const d of details) {
                if (!d.ok) {
                    html += `<li class="text-sm text-red-700">• Celda <strong>${d.ref}</strong>: se esperaba <code class="bg-red-100 px-1 rounded">${d.expected}</code>, obtuviste <code class="bg-red-100 px-1 rounded">${d.actual || '(vacío)'}</code></li>`;
                }
            }
            html += '</ul>';
        }
        html += '</div>';

        resultDiv.innerHTML = html;
        resultDiv.classList.remove('hidden');
        resultDiv.className = `mt-4 rounded-xl overflow-hidden border shadow-inner ${allCorrect ? 'border-green-300' : 'border-gray-300'}`;

        status.textContent = allCorrect ? '✅ ¡Correcto!' : `⚠️ ${correct}/${total}`;
        status.className   = `text-sm ml-auto font-medium ${allCorrect ? 'text-green-600' : 'text-amber-600'}`;
    };

    // ── Resetear ───────────────────────────────────────────────
    window.sgResetSpreadsheet = function() {
        initCellData();
        renderTable();
        document.getElementById('sg-ss-result').classList.add('hidden');
        document.getElementById('sg-ss-status').textContent = '';
        document.getElementById('sg-cell-ref').textContent = 'A1';
        document.getElementById('sg-formula-bar').value = '';
        // Limpiar colores
        document.querySelectorAll('.sg-cell-correct, .sg-cell-wrong').forEach(el => {
            el.classList.remove('sg-cell-correct', 'sg-cell-wrong');
        });
    };

    // ── Fill Handle: Drag & Drop ───────────────────────────────
    (function initFillHandle() {
        const table = document.getElementById('sg-spreadsheet');
        if (!table) return;

        table.addEventListener('mousedown', function(e) {
            const handle = e.target.closest('.sg-fill-handle');
            if (!handle) return;
            e.preventDefault();

            const srcRef = handle.dataset.ref;
            const srcR   = parseInt(handle.dataset.row);
            const srcC   = parseInt(handle.dataset.col);
            const srcCell = cellData[srcRef];
            if (!srcCell && !srcRef) return;

            fillDrag = { sourceRef: srcRef, startR: srcR, startC: srcC, currentR: srcR, currentC: srcC };

            // Añadir listeners globales
            document.addEventListener('mousemove', onFillMove);
            document.addEventListener('mouseup', onFillUp);
        });

        function onFillMove(e) {
            if (!fillDrag) return;
            // Encontrar la celda bajo el cursor
            const el = document.elementFromPoint(e.clientX, e.clientY);
            if (!el) return;
            const input = el.closest('input[data-ref]');
            const handle = el.closest('.sg-fill-handle');
            const target = input || handle;
            if (!target) return;

            const ref = target.dataset.ref;
            const p = parseCellRef(ref);
            if (!p) return;

            fillDrag.currentR = p.r;
            fillDrag.currentC = p.c;

            // Limpiar preview anterior
            document.querySelectorAll('.sg-fill-preview').forEach(el => el.classList.remove('sg-fill-preview'));

            // Determinar dirección: vertical u horizontal
            const dR = fillDrag.currentR - fillDrag.startR;
            const dC = fillDrag.currentC - fillDrag.startC;

            if (Math.abs(dR) >= Math.abs(dC)) {
                // Vertical
                const minR = Math.min(fillDrag.startR, fillDrag.currentR);
                const maxR = Math.max(fillDrag.startR, fillDrag.currentR);
                for (let r = minR; r <= maxR; r++) {
                    if (r === fillDrag.startR) continue;
                    const cellInput = document.querySelector(`input[data-ref="${cellRef(r, fillDrag.startC)}"]`);
                    if (cellInput) cellInput.closest('td').classList.add('sg-fill-preview');
                }
            } else {
                // Horizontal
                const minC = Math.min(fillDrag.startC, fillDrag.currentC);
                const maxC = Math.max(fillDrag.startC, fillDrag.currentC);
                for (let c = minC; c <= maxC; c++) {
                    if (c === fillDrag.startC) continue;
                    const cellInput = document.querySelector(`input[data-ref="${cellRef(fillDrag.startR, c)}"]`);
                    if (cellInput) cellInput.closest('td').classList.add('sg-fill-preview');
                }
            }
        }

        function onFillUp(e) {
            if (!fillDrag) return;
            document.removeEventListener('mousemove', onFillMove);
            document.removeEventListener('mouseup', onFillUp);

            // Limpiar previews
            document.querySelectorAll('.sg-fill-preview').forEach(el => el.classList.remove('sg-fill-preview'));

            const srcRef  = fillDrag.sourceRef;
            const srcCell = cellData[srcRef];
            const dR = fillDrag.currentR - fillDrag.startR;
            const dC = fillDrag.currentC - fillDrag.startC;

            if (dR === 0 && dC === 0) { fillDrag = null; return; }

            // Determinar dirección principal
            if (Math.abs(dR) >= Math.abs(dC)) {
                // Arrastre vertical
                const step = dR > 0 ? 1 : -1;
                for (let r = fillDrag.startR + step; step > 0 ? r <= fillDrag.currentR : r >= fillDrag.currentR; r += step) {
                    const offset = r - fillDrag.startR;
                    const targetRef = cellRef(r, fillDrag.startC);
                    applyFill(srcRef, srcCell, targetRef, offset, 0);
                }
            } else {
                // Arrastre horizontal
                const step = dC > 0 ? 1 : -1;
                for (let c = fillDrag.startC + step; step > 0 ? c <= fillDrag.currentC : c >= fillDrag.currentC; c += step) {
                    const offset = c - fillDrag.startC;
                    const targetRef = cellRef(fillDrag.startR, c);
                    applyFill(srcRef, srcCell, targetRef, 0, offset);
                }
            }

            recalcAll();
            fillDrag = null;
        }

        function applyFill(srcRef, srcCell, targetRef, dRow, dCol) {
            if (!srcCell) return;
            const targetInput = document.querySelector(`input[data-ref="${targetRef}"]`);
            if (!targetInput) return;

            // No sobreescribir celdas readonly (datos iniciales fijos)
            const existing = cellData[targetRef];
            if (existing && existing.readonly) return;

            if (srcCell.formula) {
                // Fórmula → shift de referencias
                const newFormula = shiftFormula(srcCell.formula, dRow, dCol);
                if (!cellData[targetRef]) cellData[targetRef] = { value: '', formula: null, readonly: false };
                cellData[targetRef].formula = newFormula;
                cellData[targetRef].value = null;
                const result = evaluateFormula(newFormula);
                targetInput.value = result;
                targetInput.dataset.formula = newFormula;
                targetInput.classList.add('sg-cell-formula');
            } else {
                // Valor → copiar (con incremento numérico)
                const newVal = shiftValue(srcCell.value, dRow, dCol);
                if (!cellData[targetRef]) cellData[targetRef] = { value: '', formula: null, readonly: false };
                cellData[targetRef].formula = null;
                cellData[targetRef].value = newVal;
                targetInput.value = newVal;
                targetInput.classList.remove('sg-cell-formula');
            }
        }
    })();

    // ── Inicializar ────────────────────────────────────────────
    initCellData();
    renderTable();

})();
</script>
@endpush
