{{-- Datos del ejercicio como JSON (sin problemas de escaping en Blade) --}}
<script type="application/json" id="sg-exercise-data">
{!! json_encode(['starterCode' => $exercise->starter_code, 'language' => $exercise->language]) !!}
</script>

{{-- ── Panel del ejercicio ──────────────────────────────────────────────────── --}}
<div class="mt-12 border-t-2 border-dashed border-indigo-200 pt-10" id="ejercicio-practica">

    {{-- Cabecera --}}
    <div class="flex items-center gap-3 mb-5">
        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white text-xl shrink-0">⌨</div>
        <div>
            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-0.5">Ejercicio de práctica</p>
            <h2 class="text-lg font-bold text-gray-900 leading-tight">{{ $exercise->title }}</h2>
        </div>
    </div>

    {{-- Descripción --}}
    <div class="prose prose-sm max-w-none mb-5 p-4 bg-indigo-50 rounded-xl border border-indigo-100 text-gray-800">
        {!! $exercise->description_html !!}
    </div>

    {{-- Editor Monaco --}}
    <div class="rounded-xl overflow-hidden border border-gray-700 shadow-xl" style="height:430px;">
        <div id="sg-code-editor" style="width:100%;height:100%;"></div>
    </div>

    {{-- Controles --}}
    <div class="flex items-center gap-3 mt-3 flex-wrap">
        <button onclick="sgRunCode()"
                class="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-semibold rounded-lg transition-all text-sm shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Ejecutar
        </button>
        <button onclick="sgResetCode()"
                class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:border-gray-400 text-gray-600 font-medium rounded-lg transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Resetear
        </button>
        <span id="sg-run-status" class="text-sm ml-auto"></span>
    </div>

    {{-- Panel de output --}}
    <div id="sg-output" class="hidden mt-4 rounded-xl overflow-hidden border border-gray-700 shadow-inner">
        <div class="bg-gray-800 px-4 py-2 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                <span class="w-2.5 h-2.5 rounded-full bg-yellow-500"></span>
                <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                <span class="text-xs font-mono text-gray-400 ml-2 uppercase tracking-wider">Output</span>
            </div>
            <button onclick="document.getElementById('sg-output').classList.add('hidden')"
                    class="text-gray-500 hover:text-gray-300 text-sm transition-colors">✕</button>
        </div>
        <div class="bg-gray-950 p-4 font-mono text-sm min-h-20 max-h-80 overflow-y-auto">
            <pre id="sg-output-text" class="whitespace-pre-wrap text-sm leading-relaxed m-0"></pre>
        </div>
    </div>

</div>

@push('styles')
<link rel="stylesheet"
      data-name="vs/editor/editor.main"
      href="https://cdn.jsdelivr.net/npm/monaco-editor@0.52.0/min/vs/editor/editor.main.css">
@endpush

@push('scripts')
<script>
(function () {
    // ── Cargar Monaco ─────────────────────────────────────────────────────
    const loaderScript = document.createElement('script');
    loaderScript.src = 'https://cdn.jsdelivr.net/npm/monaco-editor@0.52.0/min/vs/loader.js';
    loaderScript.onload = function () {
        require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.52.0/min/vs' } });
        require(['vs/editor/editor.main'], function () {
            // ── Tema Darcula ───────────────────────────────────────────────
            monaco.editor.defineTheme('darcula', {
                base: 'vs-dark',
                inherit: true,
                rules: [
                    { token: '',                   foreground: 'A9B7C6' },
                    { token: 'comment',            foreground: '808080', fontStyle: 'italic' },
                    { token: 'comment.doc',        foreground: '629755', fontStyle: 'italic' },
                    { token: 'keyword',            foreground: 'CC7832' },
                    { token: 'keyword.operator',   foreground: 'CC7832' },
                    { token: 'keyword.control',    foreground: 'CC7832' },
                    { token: 'string',             foreground: '6A8759' },
                    { token: 'string.escape',      foreground: 'CC7832' },
                    { token: 'number',             foreground: '6897BB' },
                    { token: 'number.float',       foreground: '6897BB' },
                    { token: 'regexp',             foreground: '6A8759' },
                    { token: 'type',               foreground: 'FFC66D' },
                    { token: 'class',              foreground: 'FFC66D' },
                    { token: 'function',           foreground: 'FFC66D' },
                    { token: 'identifier',         foreground: 'A9B7C6' },
                    { token: 'variable',           foreground: 'A9B7C6' },
                    { token: 'variable.parameter', foreground: 'A9B7C6' },
                    { token: 'constant',           foreground: '9876AA' },
                    { token: 'delimiter',          foreground: 'A9B7C6' },
                    { token: 'delimiter.square',   foreground: 'A9B7C6' },
                    { token: 'delimiter.bracket',  foreground: 'A9B7C6' },
                    { token: 'tag',                foreground: 'E8BF6A' },
                    { token: 'attribute.name',     foreground: 'BABABA' },
                    { token: 'attribute.value',    foreground: '6A8759' },
                    { token: 'metatag',            foreground: 'BBB529' },
                    { token: 'annotation',         foreground: 'BBB529' },
                ],
                colors: {
                    'editor.background':                  '#2B2B2B',
                    'editor.foreground':                  '#A9B7C6',
                    'editorLineNumber.foreground':        '#606366',
                    'editorLineNumber.activeForeground':  '#A4A3A3',
                    'editorCursor.foreground':            '#BBBBBB',
                    'editor.selectionBackground':         '#214283',
                    'editor.inactiveSelectionBackground': '#21428366',
                    'editor.lineHighlightBackground':     '#323232',
                    'editorIndentGuide.background1':      '#3D3D3D',
                    'editorWhitespace.foreground':        '#404040',
                    'editorBracketMatch.background':      '#3B514D',
                    'editorBracketMatch.border':          '#4B6F4E',
                    'scrollbarSlider.background':         '#4C4C4C80',
                    'scrollbarSlider.hoverBackground':    '#6464648C',
                    'scrollbarSlider.activeBackground':   '#7474748C',
                    'editorWidget.background':            '#3C3F41',
                    'editorWidget.border':                '#555555',
                    'input.background':                   '#45494A',
                    'input.border':                       '#646464',
                    'focusBorder':                        '#6E6E6E',
                    'list.hoverBackground':               '#4B4B4B',
                    'list.activeSelectionBackground':     '#4B6EAF',
                },
            });

            // ── Inicializar editor ─────────────────────────────────────────
            const data = JSON.parse(document.getElementById('sg-exercise-data').textContent);
            window.sgStarterCode = data.starterCode;

            window.sgEditor = monaco.editor.create(document.getElementById('sg-code-editor'), {
                value:                   data.starterCode,
                language:                data.language,
                theme:                   'darcula',
                fontSize:                14,
                fontFamily:              "'JetBrains Mono', 'Fira Code', 'Cascadia Code', monospace",
                fontLigatures:           true,
                lineHeight:              22,
                minimap:                 { enabled: false },
                scrollBeyondLastLine:    false,
                automaticLayout:         true,
                roundedSelection:        true,
                cursorStyle:             'line',
                cursorBlinking:          'smooth',
                wordWrap:                'on',
                tabSize:                 2,
                insertSpaces:            true,
                renderWhitespace:        'none',
                smoothScrolling:         true,
                contextmenu:             true,
                suggestOnTriggerCharacters: true,
                quickSuggestions:        { other: true, comments: false, strings: false },
                lineNumbers:             'on',
                glyphMargin:             false,
                folding:                 true,
                renderLineHighlight:     'line',
                padding:                 { top: 12, bottom: 12 },
            });
        });
    };
    document.head.appendChild(loaderScript);
})();

// ── Ejecutar código ────────────────────────────────────────────────────────
window.sgRunCode = function () {
    if (!window.sgEditor) return;

    const code      = window.sgEditor.getValue();
    const outputDiv = document.getElementById('sg-output');
    const outputEl  = document.getElementById('sg-output-text');
    const statusEl  = document.getElementById('sg-run-status');

    outputDiv.classList.remove('hidden');
    outputEl.innerHTML = '<span style="color:#808080;font-style:italic">Compilando…</span>';
    statusEl.textContent = '';

    const hasImports = /^\s*import\s+/m.test(code);

    if (!window.ts) {
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/typescript@5.7.3/lib/typescript.js';
        s.onload = () => _sgTranspileAndRun(code, hasImports, outputEl, statusEl);
        document.head.appendChild(s);
    } else {
        _sgTranspileAndRun(code, hasImports, outputEl, statusEl);
    }
};

function _sgTranspileAndRun(code, hasImports, outputEl, statusEl) {
    try {
        const result = ts.transpileModule(code, {
            compilerOptions: {
                target:           ts.ScriptTarget.ES2020,
                module:           hasImports ? ts.ModuleKind.ESNext : ts.ModuleKind.None,
                strict:           false,
                esModuleInterop:  true,
                experimentalDecorators: true,
            },
        });

        const jsCode     = result.outputText;
        const scriptType = hasImports ? 'module' : 'text/javascript';

        const consolePatch = `
const __fmt = (...a) => a.map(x => {
    if (x === null) return 'null';
    if (x === undefined) return 'undefined';
    if (typeof x === 'object') { try { return JSON.stringify(x, null, 2); } catch(e) { return String(x); } }
    return String(x);
}).join(' ');
const __timers = {};
console.log   = (...a) => parent.postMessage({t:'log',  s:__fmt(...a)}, '*');
console.error = (...a) => parent.postMessage({t:'err',  s:__fmt(...a)}, '*');
console.warn  = (...a) => parent.postMessage({t:'warn', s:__fmt(...a)}, '*');
console.info  = (...a) => parent.postMessage({t:'log',  s:__fmt(...a)}, '*');
console.time    = l  => { __timers[l] = performance.now(); parent.postMessage({t:'log', s:'⏱ '+l+': [iniciado]'}, '*'); };
console.timeEnd = l  => { const ms = (__timers[l] ? (performance.now()-__timers[l]).toFixed(2) : '?'); parent.postMessage({t:'log', s:'⏱ '+l+': '+ms+'ms'}, '*'); };
window.onerror = (m,_s,_l,_c,e) => parent.postMessage({t:'err', s: e ? (e.stack||e.message) : m}, '*');
window.addEventListener('unhandledrejection', e => parent.postMessage({t:'err', s:String(e.reason?.message||e.reason)}, '*'));
`;

        const iframeHtml = `<!DOCTYPE html><html><head><script>${consolePatch}<\/script></head><body>
<script type="${scriptType}">
${jsCode}
${hasImports ? '' : "parent.postMessage({t:'done'}, '*');"}
<\/script>
${hasImports ? `<script type="module">parent.postMessage({t:'done'}, '*');<\/script>` : ''}
</body></html>`;

        const old = document.getElementById('sg-runner-iframe');
        if (old) old.remove();

        const iframe = document.createElement('iframe');
        iframe.id = 'sg-runner-iframe';
        iframe.style.cssText = 'position:fixed;left:-9999px;width:1px;height:1px;border:0;';
        iframe.setAttribute('sandbox', 'allow-scripts');
        document.body.appendChild(iframe);

        const lines = [];
        outputEl.innerHTML = '';

        const timeout = setTimeout(() => {
            window.removeEventListener('message', handler);
            if (lines.length === 0)
                outputEl.innerHTML = '<span style="color:#FFC66D">⏱ Sin respuesta (¿bucle infinito o import fallido?)</span>';
        }, 12000);

        function handler(ev) {
            if (ev.source !== iframe.contentWindow) return;
            const { t, s } = ev.data;
            if (t === 'log' || t === 'err' || t === 'warn') {
                lines.push({ t, s });
                _sgRenderLines(outputEl, lines);
            }
            if (t === 'done') {
                clearTimeout(timeout);
                window.removeEventListener('message', handler);
                if (lines.length === 0)
                    outputEl.innerHTML = '<span style="color:#606366;font-style:italic">(sin output — ¿olvidaste console.log?)</span>';
                statusEl.innerHTML = lines.some(l => l.t === 'err')
                    ? '<span style="color:#FF6B6B">❌ con errores</span>'
                    : '<span style="color:#6A8759">✅ ejecutado</span>';
            }
        }
        window.addEventListener('message', handler);
        iframe.srcdoc = iframeHtml;

    } catch (e) {
        outputEl.innerHTML = `<span style="color:#FF6B6B">❌ Error de compilación:\n${_sgEsc(e.message)}</span>`;
    }
}

function _sgRenderLines(el, lines) {
    el.innerHTML = lines.map(l => {
        if (l.t === 'err')  return `<span style="color:#FF6B6B">❌ ${_sgEsc(l.s)}</span>`;
        if (l.t === 'warn') return `<span style="color:#FFC66D">⚠ ${_sgEsc(l.s)}</span>`;
        return `<span style="color:#6A8759">${_sgEsc(l.s)}</span>`;
    }).join('\n');
}

function _sgEsc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

window.sgResetCode = function () {
    if (window.sgEditor) window.sgEditor.setValue(window.sgStarterCode);
    document.getElementById('sg-output').classList.add('hidden');
    document.getElementById('sg-run-status').textContent = '';
};
</script>
@endpush
