<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReactExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'react-frontend')->first();

        if (! $course) {
            $this->command->warn('React course not found. Run CourseSeeder + ReactLessonSeeder first.');
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

        $this->command->info('React exercises seeded: ' . count($exercises) . ' exercises.');
    }

    private function exercises(\Illuminate\Support\Collection $lessons): array
    {
        $ex = [];

        // ── Lección 1: Introducción a React ───────────────────────────────
        if ($l = $lessons->get(1)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Crea tu primer componente React con Vite',
            'language'     => 'typescript',
            'description'  => <<<'MD'
React se basa en **componentes** que retornan JSX. En este ejercicio crearás funciones puras que simulan componentes React.
Implementa `greet` (retorna un saludo), `repeat` (repite un componente N veces) y `createElement` (simula React.createElement).
MD,
            'starter_code' => <<<'TS'
// ─── Tipos básicos para simular React ────────────────────────────────────
interface VNode {
  type: string;
  props: Record<string, unknown>;
  children: (VNode | string)[];
}

// ─── 1. greet ────────────────────────────────────────────────────────────
// Retorna un string con formato: "Hola, {name}! Bienvenido a React."
// Si no se pasa name, usa "Mundo".
function greet(name?: string): string {
  // Tu código aquí
  return '';
}

// ─── 2. repeat ───────────────────────────────────────────────────────────
// Dado un template string y un número N, retorna un array con N copias
// del string, cada una con su índice: `${template} #${i+1}`
function repeat(template: string, times: number): string[] {
  // Tu código aquí
  return [];
}

// ─── 3. createElement ────────────────────────────────────────────────────
// Simula React.createElement: crea un VNode con type, props y children.
// Si un child es string, se deja como está. Si es un VNode, se deja como está.
function createElement(
  type: string,
  props: Record<string, unknown> | null,
  ...children: (VNode | string)[]
): VNode {
  // Tu código aquí
  return {} as VNode;
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
console.log(greet());          // "Hola, Mundo! Bienvenido a React."
console.log(greet('Ana'));     // "Hola, Ana! Bienvenido a React."

console.log(repeat('Item', 3));
// ["Item #1", "Item #2", "Item #3"]

const vnode = createElement('div', { className: 'card' },
  createElement('h1', null, 'Título'),
  'Texto simple',
  createElement('button', { onClick: 'handleClick' }, 'Click me')
);
console.log(JSON.stringify(vnode, null, 2));
// Debe tener type:'div', props:{className:'card'}, children con 3 elementos
TS,
        ];

        // ── Lección 2: JSX y Rendering ────────────────────────────────────
        if ($l = $lessons->get(2)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Implementa un motor de renderizado condicional',
            'language'     => 'typescript',
            'description'  => <<<'MD'
JSX permite renderizar contenido condicionalmente y mapear listas. Implementa funciones que simulan estos patrones:
`conditionalRender`, `renderList` con keys, y `renderTemplate` con un mini motor de templates.
MD,
            'starter_code' => <<<'TS'
// ─── 1. conditionalRender ────────────────────────────────────────────────
// Simula renderizado condicional como en JSX.
// Si condition es true, retorna trueValue. Si no, retorna falseValue.
// Si falseValue no se provee, retorna null.
function conditionalRender<T>(
  condition: boolean,
  trueValue: T,
  falseValue?: T | null
): T | null {
  // Tu código aquí
  return null;
}

// ─── 2. renderList ───────────────────────────────────────────────────────
// Simula {items.map(item => <Component key={item.id} />)}
// Recibe un array de objetos con "id" y una función render.
// Retorna un array de { key, content } donde key es el id del item.
interface HasId { id: number | string; }

function renderList<T extends HasId>(
  items: T[],
  render: (item: T) => string
): { key: number | string; content: string }[] {
  // Tu código aquí
  return [];
}

// ─── 3. renderTemplate ──────────────────────────────────────────────────
// Mini motor de templates: reemplaza {{variable}} en el template
// con los valores del contexto. Si la variable no existe, deja "".
function renderTemplate(
  template: string,
  context: Record<string, string | number>
): string {
  // Tu código aquí (tip: usa replace con regex)
  return '';
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
console.log(conditionalRender(true, 'Visible', 'Oculto'));   // "Visible"
console.log(conditionalRender(false, 'Visible', 'Oculto'));  // "Oculto"
console.log(conditionalRender(false, 'Visible'));             // null

const users = [
  { id: 1, name: 'Ana' },
  { id: 2, name: 'Bob' },
  { id: 3, name: 'Carl' },
];
console.log(renderList(users, u => `<li>${u.name}</li>`));
// [{ key: 1, content: '<li>Ana</li>' }, ...]

console.log(renderTemplate(
  'Hola {{name}}, tienes {{age}} años. Tu email es {{email}}.',
  { name: 'Ana', age: 28 }
));
// "Hola Ana, tienes 28 años. Tu email es ."
TS,
        ];

        // ── Lección 3: Componentes y Props ────────────────────────────────
        if ($l = $lessons->get(3)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema de componentes con props y children',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Los componentes React reciben **props** (incluido `children`) y son composables.
Implementa un sistema de componentes simulado con `createComponent`, `withDefaults` y `compose`.
MD,
            'starter_code' => <<<'TS'
// ─── Tipos ───────────────────────────────────────────────────────────────
interface ComponentOutput {
  type: string;
  props: Record<string, unknown>;
  children: string[];
  rendered: string;
}

type ComponentFn = (props: Record<string, unknown>) => ComponentOutput;

// ─── 1. createComponent ──────────────────────────────────────────────────
// Crea una "fábrica" de componentes. Retorna una función que al llamarse
// con props produce un ComponentOutput con type = name y rendered =
// `<${name} ${propsStr}>${children.join('')}</${name}>`
// propsStr = cada prop como key="value" separadas por espacio (excluir children)
function createComponent(name: string): ComponentFn {
  // Tu código aquí
  return () => ({} as ComponentOutput);
}

// ─── 2. withDefaults ─────────────────────────────────────────────────────
// HOC: recibe un ComponentFn y un objeto de default props.
// Retorna un nuevo ComponentFn que usa los defaults si no se pasan.
function withDefaults(
  component: ComponentFn,
  defaults: Record<string, unknown>
): ComponentFn {
  // Tu código aquí
  return component;
}

// ─── 3. compose ──────────────────────────────────────────────────────────
// Compone componentes: recibe un parent ComponentFn y un array de
// children ComponentOutputs. Retorna el parent renderizado con los
// children.rendered como sus children.
function compose(
  parent: ComponentFn,
  parentProps: Record<string, unknown>,
  children: ComponentOutput[]
): ComponentOutput {
  // Tu código aquí
  return {} as ComponentOutput;
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
const Button = createComponent('Button');
const btn = Button({ variant: 'primary', children: ['Click me'] });
console.log(btn.rendered);
// <Button variant="primary">Click me</Button>

const DefaultButton = withDefaults(Button, { variant: 'secondary', size: 'md' });
const dbtn = DefaultButton({ children: ['Save'] });
console.log(dbtn.rendered);
// <Button variant="secondary" size="md">Save</Button>

const dbtn2 = DefaultButton({ variant: 'danger', children: ['Delete'] });
console.log(dbtn2.rendered);
// <Button variant="danger" size="md">Delete</Button>

const Card = createComponent('Card');
const result = compose(Card, { className: 'card' }, [
  Button({ variant: 'primary', children: ['OK'] }),
  Button({ variant: 'secondary', children: ['Cancel'] }),
]);
console.log(result.rendered);
// <Card className="card"><Button variant="primary">OK</Button><Button variant="secondary">Cancel</Button></Card>
TS,
        ];

        // ── Lección 4: Estado y Eventos ────────────────────────────────────
        if ($l = $lessons->get(4)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Implementa gestión de estado inmutable',
            'language'     => 'typescript',
            'description'  => <<<'MD'
En React el estado es **inmutable**: nunca se modifica directamente, se reemplaza. 
Implementa funciones que gestionan estado de forma inmutable: `updateItem`, `toggleItem` y `createStore` (un mini store con listeners).
MD,
            'starter_code' => <<<'TS'
// ─── Tipos ───────────────────────────────────────────────────────────────
interface Todo {
  id: number;
  text: string;
  completed: boolean;
}

// ─── 1. updateItem ───────────────────────────────────────────────────────
// Actualiza un item en un array de forma inmutable.
// Retorna un NUEVO array donde el item con el id dado tiene las props actualizadas.
// Si el id no existe, retorna el array sin cambios.
function updateItem(
  items: Todo[],
  id: number,
  updates: Partial<Omit<Todo, 'id'>>
): Todo[] {
  // Tu código aquí (NO usar splice, push ni modificar el original)
  return [];
}

// ─── 2. toggleItem ───────────────────────────────────────────────────────
// Alterna el campo "completed" del item con el id dado.
// Retorna un NUEVO array.
function toggleItem(items: Todo[], id: number): Todo[] {
  // Tu código aquí (usa updateItem o map)
  return [];
}

// ─── 3. createStore ──────────────────────────────────────────────────────
// Mini implementación de un store reactivo (como useState simplificado).
// - getState() retorna el estado actual
// - setState(newState) reemplaza el estado y notifica a los listeners
// - subscribe(listener) registra un listener que se llama en cada setState
//   y retorna una función unsubscribe
interface Store<T> {
  getState: () => T;
  setState: (newState: T) => void;
  subscribe: (listener: (state: T) => void) => () => void;
}

function createStore<T>(initialState: T): Store<T> {
  // Tu código aquí
  return {} as Store<T>;
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
const todos: Todo[] = [
  { id: 1, text: 'Aprender React', completed: false },
  { id: 2, text: 'Crear componente', completed: false },
  { id: 3, text: 'Escribir tests', completed: true },
];

const updated = updateItem(todos, 2, { text: 'Crear componente con props' });
console.log(updated[1].text);        // "Crear componente con props"
console.log(todos[1].text);          // "Aprender React" (original sin cambios)
console.log(todos !== updated);      // true (nueva referencia)

const toggled = toggleItem(todos, 1);
console.log(toggled[0].completed);   // true
console.log(todos[0].completed);     // false (original sin cambios)

// Store
const store = createStore({ count: 0 });
const logs: string[] = [];
const unsub = store.subscribe(s => logs.push(`count: ${s.count}`));

store.setState({ count: 1 });
store.setState({ count: 2 });
console.log(logs);                   // ["count: 1", "count: 2"]
console.log(store.getState());       // { count: 2 }

unsub();
store.setState({ count: 3 });
console.log(logs.length);            // 2 (ya no recibe notificaciones)
TS,
        ];

        // ── Lección 5: Hooks Fundamentales ─────────────────────────────────
        if ($l = $lessons->get(5)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simula useState y useEffect',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Los hooks son el corazón de React. Implementa simulaciones de `useState` y `useEffect` para entender cómo funcionan internamente:
cómo React asocia estado a cada llamada y cómo gestiona el array de dependencias.
MD,
            'starter_code' => <<<'TS'
// ─── Mini framework de hooks (simulación) ────────────────────────────────
let hookIndex = 0;
const hookStates: unknown[] = [];
const hookEffects: { deps: unknown[] | undefined; cleanup?: () => void }[] = [];

// ─── 1. useState ─────────────────────────────────────────────────────────
// Simula React.useState:
// - En la primera llamada, inicializa con initialValue
// - En llamadas siguientes, retorna el valor almacenado
// - setter actualiza el valor en hookStates
function useState<T>(initialValue: T): [T, (newValue: T) => void] {
  // Tu código aquí
  // Tip: usa hookIndex para saber qué posición del array corresponde
  // a esta llamada. Incrementa hookIndex al final.
  return [initialValue, () => {}];
}

// ─── 2. useEffect ────────────────────────────────────────────────────────
// Simula React.useEffect:
// - Si no hay deps previos (primer render), ejecuta el efecto
// - Si deps cambió respecto al render anterior, ejecuta cleanup del
//   efecto anterior (si existe) y luego el nuevo efecto
// - Si deps no cambió, no hace nada
// La función effect puede retornar un cleanup
function useEffect(
  effect: () => void | (() => void),
  deps?: unknown[]
): void {
  // Tu código aquí
  // Tip: compara deps con los anteriores usando depsChanged()
}

// Helper: compara dos arrays de dependencias
function depsChanged(prev: unknown[] | undefined, next: unknown[] | undefined): boolean {
  if (!prev || !next) return true;
  if (prev.length !== next.length) return true;
  return prev.some((val, i) => !Object.is(val, next[i]));
}

// ─── 3. Función para resetear hooks entre "renders" ──────────────────────
function resetHookIndex() {
  hookIndex = 0;
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
// Simula un componente que usa hooks
function MyComponent() {
  const [count, setCount] = useState(0);
  const [name, setName] = useState('React');

  const logs: string[] = [];
  useEffect(() => {
    logs.push(`Effect: count is ${count}`);
    return () => logs.push(`Cleanup: count was ${count}`);
  }, [count]);

  return { count, name, setCount, setName, logs };
}

// Render 1
resetHookIndex();
const r1 = MyComponent();
console.log(r1.count, r1.name);  // 0, "React"
console.log(r1.logs);            // ["Effect: count is 0"]

// Simula setState
r1.setCount(5);

// Render 2
resetHookIndex();
const r2 = MyComponent();
console.log(r2.count);           // 5
console.log(r2.logs);            // ["Cleanup: count was 0", "Effect: count is 5"]

// Render 3 sin cambio de count
resetHookIndex();
const r3 = MyComponent();
console.log(r3.logs);            // [] (deps no cambió)
TS,
        ];

        // ── Lección 6: Hooks Avanzados ────────────────────────────────────
        if ($l = $lessons->get(6)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Implementa useReducer y un sistema de Context',
            'language'     => 'typescript',
            'description'  => <<<'MD'
`useReducer` gestiona estado complejo con el patrón reducer. `useContext` permite compartir estado sin prop drilling.
Implementa `useReducer` (con dispatch tipado), y un mini sistema de `createContext` / `useContext`.
MD,
            'starter_code' => <<<'TS'
// ─── 1. useReducer ───────────────────────────────────────────────────────
// Simula React.useReducer. Recibe un reducer y un estado inicial.
// Retorna [state, dispatch] donde dispatch ejecuta el reducer y actualiza el estado.
type Reducer<S, A> = (state: S, action: A) => S;

function useReducer<S, A>(
  reducer: Reducer<S, A>,
  initialState: S
): [() => S, (action: A) => void] {
  let state = initialState;
  // Retorna un getter de estado y un dispatch
  // Tu código aquí
  return [() => state, (_a: A) => {}];
}

// ─── 2. createContext / useContext ────────────────────────────────────────
// Mini sistema de Context:
// - createContext(defaultValue) retorna un objeto Context
// - Context tiene un Provider que acepta un value
// - useContext(Context) retorna el value del Provider más cercano,
//   o el defaultValue si no hay Provider
interface Context<T> {
  _defaultValue: T;
  _currentValue: T | undefined;
  Provider: (value: T, fn: () => void) => void;
}

function createContext<T>(defaultValue: T): Context<T> {
  // Tu código aquí
  return {} as Context<T>;
}

function useContext<T>(context: Context<T>): T {
  // Tu código aquí
  return context._defaultValue;
}

// ─── 3. Combina useReducer + Context ─────────────────────────────────────
// Crea un TodoContext usando createContext y useReducer para gestionar
// una lista de todos.

interface Todo { id: number; text: string; done: boolean; }

type TodoAction =
  | { type: 'ADD'; text: string }
  | { type: 'TOGGLE'; id: number }
  | { type: 'REMOVE'; id: number };

function todoReducer(state: Todo[], action: TodoAction): Todo[] {
  // Tu código aquí
  switch (action.type) {
    case 'ADD':
      return state; // añade un nuevo todo con id = state.length + 1
    case 'TOGGLE':
      return state; // alterna done del todo con ese id
    case 'REMOVE':
      return state; // elimina el todo con ese id
    default:
      return state;
  }
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
// useReducer
const [getState, dispatch] = useReducer(todoReducer, []);

dispatch({ type: 'ADD', text: 'Aprender React' });
dispatch({ type: 'ADD', text: 'Crear proyecto' });
console.log(getState());
// [{ id: 1, text: 'Aprender React', done: false }, { id: 2, text: 'Crear proyecto', done: false }]

dispatch({ type: 'TOGGLE', id: 1 });
console.log(getState()[0].done); // true

dispatch({ type: 'REMOVE', id: 2 });
console.log(getState().length);  // 1

// Context
const ThemeContext = createContext('light');
console.log(useContext(ThemeContext)); // "light" (default)

ThemeContext.Provider('dark', () => {
  console.log(useContext(ThemeContext)); // "dark"
});

console.log(useContext(ThemeContext)); // "light" (fuera del provider)
TS,
        ];

        // ── Lección 7: Formularios ────────────────────────────────────────
        if ($l = $lessons->get(7)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Validador de formularios con TypeScript',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Los formularios en React requieren validación de datos. Implementa un sistema de validación tipado:
`createValidator` con reglas encadenables, `validateForm` para múltiples campos y `formatErrors` para mensajes legibles.
MD,
            'starter_code' => <<<'TS'
// ─── Tipos ───────────────────────────────────────────────────────────────
type ValidationRule = (value: unknown) => string | null; // null = válido

interface FieldValidation {
  field: string;
  rules: ValidationRule[];
}

interface ValidationResult {
  valid: boolean;
  errors: Record<string, string[]>;
}

// ─── 1. Reglas de validación ─────────────────────────────────────────────
// Cada función retorna una ValidationRule

function required(msg = 'Campo requerido'): ValidationRule {
  // Falla si el valor es null, undefined o string vacío
  return (value) => null; // Tu código aquí
}

function minLength(min: number, msg?: string): ValidationRule {
  // Falla si el string tiene menos de `min` caracteres
  return (value) => null; // Tu código aquí
}

function maxLength(max: number, msg?: string): ValidationRule {
  return (value) => null; // Tu código aquí
}

function isEmail(msg = 'Email inválido'): ValidationRule {
  // Valida formato básico con regex: algo@algo.algo
  return (value) => null; // Tu código aquí
}

function matches(pattern: RegExp, msg = 'Formato inválido'): ValidationRule {
  return (value) => null; // Tu código aquí
}

// ─── 2. validateForm ─────────────────────────────────────────────────────
// Valida un objeto de datos contra un array de FieldValidation.
// Ejecuta todas las reglas de cada campo y recopila los errores.
function validateForm(
  data: Record<string, unknown>,
  validations: FieldValidation[]
): ValidationResult {
  // Tu código aquí
  return { valid: true, errors: {} };
}

// ─── 3. formatErrors ─────────────────────────────────────────────────────
// Convierte ValidationResult.errors en un array de strings legibles:
// "campo: error1, error2"
function formatErrors(result: ValidationResult): string[] {
  // Tu código aquí
  return [];
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
const formData = {
  name: '',
  email: 'invalid-email',
  password: '123',
};

const validations: FieldValidation[] = [
  { field: 'name',     rules: [required(), minLength(2)] },
  { field: 'email',    rules: [required(), isEmail()] },
  { field: 'password', rules: [required(), minLength(8, 'Mínimo 8 caracteres'), matches(/[A-Z]/, 'Debe contener mayúscula')] },
];

const result = validateForm(formData, validations);
console.log(result.valid);    // false
console.log(result.errors);
// { name: ['Campo requerido'], email: ['Email inválido'], password: ['Mínimo 8 caracteres', 'Debe contener mayúscula'] }

console.log(formatErrors(result));
// ['name: Campo requerido', 'email: Email inválido', 'password: Mínimo 8 caracteres, Debe contener mayúscula']

// Datos válidos
const validData = { name: 'Ana', email: 'ana@test.com', password: 'Secret123' };
const validResult = validateForm(validData, validations);
console.log(validResult.valid);  // true
console.log(validResult.errors); // {}
TS,
        ];

        // ── Lección 8: React Router ───────────────────────────────────────
        if ($l = $lessons->get(8)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Implementa un mini router con rutas dinámicas',
            'language'     => 'typescript',
            'description'  => <<<'MD'
React Router mapea URLs a componentes. Implementa un mini router que soporte rutas estáticas, dinámicas (`:param`) y anidadas.
Incluye `matchRoute`, `extractParams` y un `Router` con navegación programática.
MD,
            'starter_code' => <<<'TS'
// ─── Tipos ───────────────────────────────────────────────────────────────
interface Route {
  path: string;           // ej: "/users/:id/posts"
  component: string;      // nombre del componente
  children?: Route[];     // rutas anidadas
}

interface MatchResult {
  matched: boolean;
  component: string;
  params: Record<string, string>;
  childMatch?: MatchResult;
}

// ─── 1. matchRoute ───────────────────────────────────────────────────────
// Compara un path real contra un patrón de ruta.
// Los segmentos que empiezan con ":" son parámetros dinámicos.
// "/users/42" contra "/users/:id" → match con { id: "42" }
function matchRoute(pattern: string, actualPath: string): { matched: boolean; params: Record<string, string> } {
  // Tu código aquí
  // Tip: split por "/" y compara segmento a segmento
  return { matched: false, params: {} };
}

// ─── 2. findRoute ────────────────────────────────────────────────────────
// Busca la primera ruta que coincida con el path en un array de rutas.
// Soporta rutas anidadas: si una ruta padre matchea, intenta matchear
// los children con el resto del path.
function findRoute(routes: Route[], path: string): MatchResult | null {
  // Tu código aquí
  return null;
}

// ─── 3. Router ───────────────────────────────────────────────────────────
// Mini router con historial de navegación.
class Router {
  private routes: Route[];
  private history: string[] = [];
  private currentIndex = -1;

  constructor(routes: Route[]) {
    this.routes = routes;
  }

  // Navega a un path y lo añade al historial
  navigate(path: string): MatchResult | null {
    // Tu código aquí
    return null;
  }

  // Retrocede en el historial
  back(): MatchResult | null {
    // Tu código aquí
    return null;
  }

  // Avanza en el historial
  forward(): MatchResult | null {
    // Tu código aquí
    return null;
  }

  getCurrentPath(): string {
    return this.history[this.currentIndex] ?? '/';
  }
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
// matchRoute
console.log(matchRoute('/users/:id', '/users/42'));
// { matched: true, params: { id: '42' } }

console.log(matchRoute('/users/:id/posts/:postId', '/users/1/posts/99'));
// { matched: true, params: { id: '1', postId: '99' } }

console.log(matchRoute('/about', '/contact'));
// { matched: false, params: {} }

// findRoute con rutas
const routes: Route[] = [
  { path: '/', component: 'Home' },
  { path: '/about', component: 'About' },
  { path: '/users/:id', component: 'UserProfile' },
];

const found = findRoute(routes, '/users/5');
console.log(found?.component, found?.params);
// "UserProfile" { id: "5" }

// Router con historial
const router = new Router(routes);
router.navigate('/');
router.navigate('/about');
router.navigate('/users/7');

console.log(router.getCurrentPath());  // "/users/7"
router.back();
console.log(router.getCurrentPath());  // "/about"
router.forward();
console.log(router.getCurrentPath());  // "/users/7"
TS,
        ];

        // ── Lección 9: Fetching de datos ──────────────────────────────────
        if ($l = $lessons->get(9)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Implementa un sistema de data fetching con caché',
            'language'     => 'typescript',
            'description'  => <<<'MD'
En React usamos hooks como `useFetch` o TanStack Query para gestionar datos del servidor.
Implementa un `QueryClient` con caché, invalidación y deduplicación de peticiones.
MD,
            'starter_code' => <<<'TS'
// ─── Helpers ─────────────────────────────────────────────────────────────
const delay = (ms: number) => new Promise<void>(r => setTimeout(r, ms));

// Simula un API
let fetchCount = 0;
async function fakeFetch(url: string): Promise<{ data: string; fetchNumber: number }> {
  fetchCount++;
  const n = fetchCount;
  await delay(50);
  return { data: `Response from ${url}`, fetchNumber: n };
}

// ─── 1. QueryClient ──────────────────────────────────────────────────────
// Mini implementación de TanStack Query:
// - query(key, fetcher): si hay caché válido, retorna de caché. Si no, ejecuta fetcher.
// - invalidate(key): marca la caché como inválida para ese key.
// - staleTime: tiempo en ms que la caché es válida.

interface CacheEntry<T> {
  data: T;
  timestamp: number;
}

class QueryClient {
  private cache = new Map<string, CacheEntry<unknown>>();
  private staleTime: number;
  private inFlight = new Map<string, Promise<unknown>>();

  constructor(options: { staleTime: number }) {
    this.staleTime = options.staleTime;
  }

  async query<T>(key: string, fetcher: () => Promise<T>): Promise<T> {
    // 1. Si hay caché y no está stale, retorna de caché
    // 2. Si ya hay una petición en vuelo para este key, espera esa misma (deduplicación)
    // 3. Si no, ejecuta fetcher, guarda en caché y retorna
    // Tu código aquí
    return {} as T;
  }

  invalidate(key: string): void {
    // Elimina la entrada de caché para forzar un refetch
    // Tu código aquí
  }

  invalidateAll(): void {
    // Limpia toda la caché
    // Tu código aquí
  }

  getCacheSize(): number {
    return this.cache.size;
  }
}

// ─── 2. retry wrapper ────────────────────────────────────────────────────
// Envuelve un fetcher para reintentar en caso de error, con backoff.
async function withRetry<T>(
  fn: () => Promise<T>,
  maxRetries: number,
  baseDelay = 100
): Promise<T> {
  // Tu código aquí
  return fn();
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
async function main() {
  const client = new QueryClient({ staleTime: 200 });

  // Primera query → ejecuta fetcher
  const r1 = await client.query('users', () => fakeFetch('/users'));
  console.log('r1:', r1.data, 'fetch#', r1.fetchNumber); // "Response from /users", fetch# 1

  // Segunda query con misma key → caché (si staleTime no ha pasado)
  const r2 = await client.query('users', () => fakeFetch('/users'));
  console.log('r2:', r2.fetchNumber); // 1 (de caché, no hizo nuevo fetch)

  // Invalidar y re-fetch
  client.invalidate('users');
  const r3 = await client.query('users', () => fakeFetch('/users'));
  console.log('r3:', r3.fetchNumber); // 2 (nuevo fetch)

  // Deduplicación: dos queries simultáneas con misma key = un solo fetch
  fetchCount = 0;
  client.invalidateAll();
  const [r4, r5] = await Promise.all([
    client.query('posts', () => fakeFetch('/posts')),
    client.query('posts', () => fakeFetch('/posts')),
  ]);
  console.log('dedup:', r4.fetchNumber, r5.fetchNumber); // 1, 1 (mismo fetch)

  // Retry
  let attempt = 0;
  const unreliable = async () => {
    attempt++;
    if (attempt < 3) throw new Error(`Fail #${attempt}`);
    return 'success';
  };
  const retryResult = await withRetry(unreliable, 5, 10);
  console.log('retry result:', retryResult); // "success"
}

main();
TS,
        ];

        // ── Lección 10: Context API ───────────────────────────────────────
        if ($l = $lessons->get(10)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Implementa un sistema de theme y auth con Context',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Context API evita el prop drilling. Implementa un sistema multi-context que combine **ThemeContext** y **AuthContext**,
con un `ContextManager` que gestione múltiples providers.
MD,
            'starter_code' => <<<'TS'
// ─── Mini Context System ─────────────────────────────────────────────────
interface MiniContext<T> {
  defaultValue: T;
  stack: T[];
}

function createCtx<T>(defaultValue: T): MiniContext<T> {
  return { defaultValue, stack: [] };
}

function provide<T>(ctx: MiniContext<T>, value: T, fn: () => void): void {
  ctx.stack.push(value);
  fn();
  ctx.stack.pop();
}

function consume<T>(ctx: MiniContext<T>): T {
  return ctx.stack.length > 0 ? ctx.stack[ctx.stack.length - 1] : ctx.defaultValue;
}

// ─── 1. ThemeContext ─────────────────────────────────────────────────────
interface Theme {
  mode: 'light' | 'dark';
  primary: string;
  bg: string;
  text: string;
}

const lightTheme: Theme = { mode: 'light', primary: '#3b82f6', bg: '#ffffff', text: '#1a1a1a' };
const darkTheme: Theme  = { mode: 'dark',  primary: '#60a5fa', bg: '#1a1a1a', text: '#f5f5f5' };

const ThemeContext = createCtx<Theme>(lightTheme);

function useTheme(): Theme & { toggle: () => Theme } {
  const theme = consume(ThemeContext);
  // toggle retorna el tema opuesto
  // Tu código aquí
  return { ...theme, toggle: () => theme.mode === 'light' ? darkTheme : lightTheme };
}

// ─── 2. AuthContext ──────────────────────────────────────────────────────
interface User { id: number; name: string; role: 'admin' | 'user'; }
interface AuthState {
  user: User | null;
  isAuthenticated: boolean;
}

const AuthContext = createCtx<AuthState>({ user: null, isAuthenticated: false });

function useAuth() {
  const auth = consume(AuthContext);
  return {
    ...auth,
    // hasRole: verifica si el usuario tiene un rol específico
    hasRole: (role: string): boolean => {
      // Tu código aquí
      return false;
    },
  };
}

// ─── 3. ContextManager ──────────────────────────────────────────────────
// Combina múltiples providers (como composeProviders en React).
// Recibe un array de [context, value] y ejecuta fn con todos activos.
function withProviders(
  providers: Array<[MiniContext<any>, any]>,
  fn: () => void
): void {
  // Tu código aquí
  // Tip: aplica recursivamente provide para cada par [ctx, value]
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
// Theme
provide(ThemeContext, darkTheme, () => {
  const theme = useTheme();
  console.log('mode:', theme.mode);       // "dark"
  console.log('bg:', theme.bg);           // "#1a1a1a"
  const toggled = theme.toggle();
  console.log('toggled:', toggled.mode);  // "light"
});

// Auth
const adminUser: User = { id: 1, name: 'Admin', role: 'admin' };
provide(AuthContext, { user: adminUser, isAuthenticated: true }, () => {
  const auth = useAuth();
  console.log('user:', auth.user?.name);          // "Admin"
  console.log('isAdmin:', auth.hasRole('admin'));  // true
  console.log('isUser:', auth.hasRole('user'));    // false
});

// Fuera del provider → defaults
console.log('default theme:', useTheme().mode);        // "light"
console.log('default auth:', useAuth().isAuthenticated); // false

// ContextManager: múltiples providers a la vez
withProviders(
  [
    [ThemeContext, darkTheme],
    [AuthContext, { user: adminUser, isAuthenticated: true }],
  ],
  () => {
    console.log('combined theme:', useTheme().mode);       // "dark"
    console.log('combined auth:', useAuth().user?.name);   // "Admin"
  }
);
TS,
        ];

        // ── Lección 11: Redux Toolkit ─────────────────────────────────────
        if ($l = $lessons->get(11)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Implementa un mini Redux con slices y middleware',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Redux Toolkit usa `createSlice` para reducers y `configureStore` para el store.
Implementa `createSlice`, `configureStore` con middleware de logging, y un selector factory.
MD,
            'starter_code' => <<<'TS'
// ─── Tipos ───────────────────────────────────────────────────────────────
interface Action { type: string; payload?: unknown; }
type ReducerFn<S> = (state: S, action: Action) => S;
type Middleware = (action: Action, state: unknown) => void;

interface Slice<S> {
  name: string;
  reducer: ReducerFn<S>;
  actions: Record<string, (payload?: unknown) => Action>;
}

// ─── 1. createSlice ──────────────────────────────────────────────────────
// Crea un slice con nombre, estado inicial y reducers.
// Genera automáticamente action creators con type = "name/reducerName".
function createSlice<S>(config: {
  name: string;
  initialState: S;
  reducers: Record<string, (state: S, payload?: any) => S>;
}): Slice<S> {
  // Tu código aquí
  // Genera actions: { reducerName: (payload) => ({ type: "name/reducerName", payload }) }
  // Genera reducer: switch por action.type que llama al reducer correspondiente
  return {} as Slice<S>;
}

// ─── 2. configureStore ───────────────────────────────────────────────────
// Combina múltiples slices en un store unificado.
// Soporta middleware (funciones que se ejecutan en cada dispatch).
interface Store {
  getState: () => Record<string, unknown>;
  dispatch: (action: Action) => void;
  subscribe: (listener: () => void) => () => void;
}

function configureStore(config: {
  slices: Slice<any>[];
  middleware?: Middleware[];
}): Store {
  // Tu código aquí
  return {} as Store;
}

// ─── 3. createSelector ───────────────────────────────────────────────────
// Selector memoizado: recalcula solo si el input cambia.
function createSelector<S, R>(
  inputFn: (state: S) => unknown,
  resultFn: (input: unknown) => R
): (state: S) => R {
  // Tu código aquí (memoiza basándote en el resultado de inputFn)
  return (state) => resultFn(inputFn(state));
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
// Slice de counter
const counterSlice = createSlice({
  name: 'counter',
  initialState: { value: 0 },
  reducers: {
    increment: (state) => ({ ...state, value: state.value + 1 }),
    decrement: (state) => ({ ...state, value: state.value - 1 }),
    addBy: (state, amount: number) => ({ ...state, value: state.value + amount }),
  },
});

// Slice de todos
const todosSlice = createSlice({
  name: 'todos',
  initialState: [] as string[],
  reducers: {
    add: (state, text: string) => [...state, text],
    remove: (state, index: number) => state.filter((_: string, i: number) => i !== index),
  },
});

// Actions auto-generadas
console.log(counterSlice.actions.increment());
// { type: "counter/increment" }
console.log(counterSlice.actions.addBy(5));
// { type: "counter/addBy", payload: 5 }

// Store con middleware de logging
const logs: string[] = [];
const logger: Middleware = (action) => logs.push(action.type);

const store = configureStore({
  slices: [counterSlice, todosSlice],
  middleware: [logger],
});

store.dispatch(counterSlice.actions.increment());
store.dispatch(counterSlice.actions.addBy(10));
store.dispatch(todosSlice.actions.add('Learn Redux'));

console.log(store.getState());
// { counter: { value: 11 }, todos: ['Learn Redux'] }

console.log(logs);
// ['counter/increment', 'counter/addBy', 'todos/add']
TS,
        ];

        // ── Lección 12: Testing ───────────────────────────────────────────
        if ($l = $lessons->get(12)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Implementa un mini framework de testing',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Para hacer testing en React usamos Vitest y RTL. Implementa tu propio mini framework de testing
con `describe`, `it`, `expect` (matchers) y un runner que reporte resultados.
MD,
            'starter_code' => <<<'TS'
// ─── Tipos ───────────────────────────────────────────────────────────────
interface TestResult {
  name: string;
  passed: boolean;
  error?: string;
}

interface SuiteResult {
  name: string;
  tests: TestResult[];
  passed: number;
  failed: number;
}

// ─── 1. expect + matchers ────────────────────────────────────────────────
// Implementa una función expect que retorna un objeto con matchers.
// Si un matcher falla, lanza un Error con mensaje descriptivo.
function expect(actual: unknown) {
  return {
    toBe(expected: unknown): void {
      // Comparación estricta (===)
      // Tu código aquí
    },
    toEqual(expected: unknown): void {
      // Comparación profunda (deep equality)
      // Tu código aquí (tip: JSON.stringify para simplificar)
    },
    toBeTruthy(): void {
      // Tu código aquí
    },
    toBeFalsy(): void {
      // Tu código aquí
    },
    toContain(item: unknown): void {
      // Para arrays: incluye el item. Para strings: incluye el substring.
      // Tu código aquí
    },
    toThrow(expectedMsg?: string): void {
      // actual debe ser una función que al ejecutarse lanza un error
      // Si se pasa expectedMsg, el mensaje del error debe incluirlo
      // Tu código aquí
    },
    toHaveLength(length: number): void {
      // Tu código aquí
    },
  };
}

// ─── 2. describe / it ────────────────────────────────────────────────────
const suites: SuiteResult[] = [];
let currentSuite: SuiteResult | null = null;

function describe(name: string, fn: () => void): void {
  // Crea un nuevo SuiteResult, ejecuta fn (que contiene it()),
  // y guarda el suite en suites.
  // Tu código aquí
}

function it(name: string, fn: () => void): void {
  // Ejecuta fn dentro de un try/catch.
  // Si no lanza → passed. Si lanza → failed con el mensaje del error.
  // Añade el TestResult al currentSuite.
  // Tu código aquí
}

// ─── 3. runTests (runner) ────────────────────────────────────────────────
// Ejecuta todos los suites y muestra un reporte en consola.
function runTests(): { total: number; passed: number; failed: number } {
  let totalPassed = 0;
  let totalFailed = 0;

  for (const suite of suites) {
    console.log(`\n  ${suite.name}`);
    for (const test of suite.tests) {
      const icon = test.passed ? '✓' : '✗';
      console.log(`    ${icon} ${test.name}${test.error ? ' — ' + test.error : ''}`);
      if (test.passed) totalPassed++; else totalFailed++;
    }
  }

  const total = totalPassed + totalFailed;
  console.log(`\n  ${totalPassed}/${total} passed`);
  return { total, passed: totalPassed, failed: totalFailed };
}

// ─── Pruebas (usa tu propio framework para probarse a sí mismo) ──────────
describe('expect matchers', () => {
  it('toBe compara con ===', () => {
    expect(1 + 1).toBe(2);
    expect('hello').toBe('hello');
  });

  it('toEqual compara profundamente', () => {
    expect({ a: 1, b: [2, 3] }).toEqual({ a: 1, b: [2, 3] });
  });

  it('toBeTruthy y toBeFalsy', () => {
    expect(1).toBeTruthy();
    expect('').toBeFalsy();
    expect(null).toBeFalsy();
  });

  it('toContain', () => {
    expect([1, 2, 3]).toContain(2);
    expect('Hello World').toContain('World');
  });

  it('toThrow', () => {
    expect(() => { throw new Error('¡boom!'); }).toThrow('boom');
  });

  it('toHaveLength', () => {
    expect([1, 2, 3]).toHaveLength(3);
    expect('abc').toHaveLength(3);
  });

  it('fallo intencional para ver el reporte', () => {
    expect(1).toBe(2); // Esto debe fallar
  });
});

const report = runTests();
console.log('Total:', report.total, 'Passed:', report.passed, 'Failed:', report.failed);
TS,
        ];

        // ── Lección 13: Performance ───────────────────────────────────────
        if ($l = $lessons->get(13)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Implementa memoización y virtualización',
            'language'     => 'typescript',
            'description'  => <<<'MD'
La optimización de rendimiento en React se basa en **memoización** (evitar recalcular) y **virtualización** (renderizar solo lo visible).
Implementa `memo`, `useMemoized`, y un `VirtualList` que solo procesa los items visibles en el viewport.
MD,
            'starter_code' => <<<'TS'
// ─── 1. memo (simula React.memo) ─────────────────────────────────────────
// Recibe una función render y retorna una versión memoizada que solo
// re-ejecuta si los argumentos cambiaron (shallow comparison).
function memo<TArgs extends unknown[], TResult>(
  fn: (...args: TArgs) => TResult
): (...args: TArgs) => TResult {
  let lastArgs: TArgs | null = null;
  let lastResult: TResult;

  // Tu código aquí
  // Tip: compara cada arg con === (shallow)
  return fn;
}

// ─── 2. useMemoized (simula useMemo) ─────────────────────────────────────
// Memoiza el resultado de un cálculo costoso basándose en deps.
// Solo recalcula si alguna dep cambió.
function useMemoized<T>(
  factory: () => T,
  deps: unknown[]
): { getValue: () => T; update: (newDeps: unknown[]) => T } {
  let cachedValue = factory();
  let cachedDeps = [...deps];

  return {
    getValue: () => cachedValue,
    update: (newDeps: unknown[]) => {
      // Si deps cambiaron, recalcula. Si no, retorna el cacheado.
      // Tu código aquí
      return cachedValue;
    },
  };
}

// ─── 3. VirtualList ──────────────────────────────────────────────────────
// Dado un array grande, calcula qué items serían visibles
// dado un viewport (scrollTop + viewportHeight) y un itemHeight fijo.
interface VirtualItem<T> {
  index: number;
  item: T;
  offsetTop: number;
}

interface VirtualListResult<T> {
  visibleItems: VirtualItem<T>[];
  totalHeight: number;
  startIndex: number;
  endIndex: number;
}

function virtualizeList<T>(
  items: T[],
  itemHeight: number,
  viewportHeight: number,
  scrollTop: number
): VirtualListResult<T> {
  // Calcula qué items son visibles en el viewport actual
  // Incluye 1 item extra arriba y abajo como buffer (overscan)
  // Tu código aquí
  return { visibleItems: [], totalHeight: 0, startIndex: 0, endIndex: 0 };
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
// memo
let renderCount = 0;
const render = memo((name: string, age: number) => {
  renderCount++;
  return `${name} (${age})`;
});

console.log(render('Ana', 25));   // "Ana (25)" — renderCount = 1
console.log(render('Ana', 25));   // "Ana (25)" — renderCount = 1 (cached)
console.log(render('Bob', 30));   // "Bob (30)" — renderCount = 2 (args changed)
console.log('render calls:', renderCount); // 2

// useMemoized
let calcCount = 0;
const expensive = useMemoized(() => { calcCount++; return 42 * 42; }, [42]);
console.log(expensive.getValue());    // 1764, calcCount = 1
expensive.update([42]);               // deps no cambiaron
console.log('calc calls:', calcCount); // 1
expensive.update([99]);               // deps cambiaron
console.log('calc calls:', calcCount); // 2

// VirtualList: 1000 items, cada uno 50px, viewport 200px, scroll 500px
const bigList = Array.from({ length: 1000 }, (_, i) => `Item ${i}`);
const result = virtualizeList(bigList, 50, 200, 500);
console.log('totalHeight:', result.totalHeight);     // 50000
console.log('startIndex:', result.startIndex);        // 9 (con overscan)
console.log('endIndex:', result.endIndex);            // 14 (con overscan)
console.log('visible count:', result.visibleItems.length); // ~6
console.log('first visible:', result.visibleItems[0]?.item); // "Item 9"
TS,
        ];

        // ── Lección 14: Custom Hooks ──────────────────────────────────────
        if ($l = $lessons->get(14)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Crea custom hooks reutilizables',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Los custom hooks encapsulan lógica reutilizable. Implementa versiones funcionales de
`useLocalStorage`, `useDebounce` y `usePagination` sin dependencia de React.
MD,
            'starter_code' => <<<'TS'
// ─── 1. createLocalStorage ───────────────────────────────────────────────
// Simula useLocalStorage: lee/escribe de un store en memoria (simulando localStorage).
// Soporta serialización JSON y valor por defecto.
const memoryStore = new Map<string, string>();

function createLocalStorage<T>(key: string, defaultValue: T) {
  // Inicializa: si existe en memoryStore, parsea JSON. Si no, usa defaultValue.
  // Tu código aquí

  return {
    get: (): T => {
      // Lee de memoryStore, parsea y retorna. Si no existe, retorna defaultValue.
      // Tu código aquí
      return defaultValue;
    },
    set: (value: T): void => {
      // Serializa y guarda en memoryStore.
      // Tu código aquí
    },
    remove: (): void => {
      // Elimina del memoryStore.
      // Tu código aquí
    },
  };
}

// ─── 2. createDebounce ───────────────────────────────────────────────────
// Simula useDebounce: retarda la ejecución de una función hasta que
// pasen `delay` ms sin ser llamada.
function createDebounce<TArgs extends unknown[]>(
  fn: (...args: TArgs) => void,
  delay: number
): { call: (...args: TArgs) => void; cancel: () => void; flush: () => void } {
  let timerId: ReturnType<typeof setTimeout> | null = null;
  let lastArgs: TArgs | null = null;

  return {
    call: (...args: TArgs) => {
      // Cancela el timer anterior, guarda args y programa la ejecución.
      // Tu código aquí
    },
    cancel: () => {
      // Cancela el timer pendiente.
      // Tu código aquí
    },
    flush: () => {
      // Ejecuta inmediatamente si hay un timer pendiente.
      // Tu código aquí
    },
  };
}

// ─── 3. createPagination ─────────────────────────────────────────────────
// Simula usePagination: gestiona el estado de paginación.
interface PaginationState<T> {
  currentPage: number;
  pageSize: number;
  totalItems: number;
  totalPages: number;
  items: T[];              // items de la página actual
  hasNext: boolean;
  hasPrev: boolean;
}

function createPagination<T>(allItems: T[], pageSize: number) {
  let currentPage = 1;

  function getState(): PaginationState<T> {
    // Calcula todos los campos basándose en currentPage y pageSize
    // Tu código aquí
    return {} as PaginationState<T>;
  }

  return {
    getState,
    nextPage: (): PaginationState<T> => {
      // Avanza una página si es posible
      // Tu código aquí
      return getState();
    },
    prevPage: (): PaginationState<T> => {
      // Retrocede una página si es posible
      // Tu código aquí
      return getState();
    },
    goToPage: (page: number): PaginationState<T> => {
      // Va a una página específica (clamped entre 1 y totalPages)
      // Tu código aquí
      return getState();
    },
    setPageSize: (newSize: number): PaginationState<T> => {
      // Cambia el tamaño de página y resetea a página 1
      // Tu código aquí
      return getState();
    },
  };
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
// LocalStorage
const storage = createLocalStorage('user', { name: 'Guest', theme: 'light' });
console.log(storage.get());           // { name: 'Guest', theme: 'light' }
storage.set({ name: 'Ana', theme: 'dark' });
console.log(storage.get());           // { name: 'Ana', theme: 'dark' }

// Nuevo accessor para la misma key lee el dato guardado
const storage2 = createLocalStorage('user', { name: 'Guest', theme: 'light' });
console.log(storage2.get());          // { name: 'Ana', theme: 'dark' }

storage.remove();
console.log(storage.get());           // { name: 'Guest', theme: 'light' }

// Debounce
const calls: number[] = [];
const debounced = createDebounce((n: number) => calls.push(n), 100);
debounced.call(1);
debounced.call(2);
debounced.call(3);
// Solo el último se ejecutará después de 100ms
debounced.flush();
console.log('debounce calls:', calls); // [3]

// Pagination
const items = Array.from({ length: 23 }, (_, i) => `Item ${i + 1}`);
const pagination = createPagination(items, 5);

let state = pagination.getState();
console.log('page:', state.currentPage, 'items:', state.items.length); // 1, 5
console.log('totalPages:', state.totalPages);  // 5
console.log('hasNext:', state.hasNext);         // true
console.log('hasPrev:', state.hasPrev);         // false

state = pagination.nextPage();
console.log('page:', state.currentPage, 'first:', state.items[0]); // 2, "Item 6"

state = pagination.goToPage(5);
console.log('page:', state.currentPage, 'items:', state.items.length); // 5, 3
console.log('hasNext:', state.hasNext); // false
TS,
        ];

        // ── Lección 15: TypeScript con React ─────────────────────────────
        if ($l = $lessons->get(15)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema de tipos avanzado para componentes React',
            'language'     => 'typescript',
            'description'  => <<<'MD'
TypeScript con React va más allá de tipar props. Implementa tipos avanzados:
**discriminated unions** para componentes polimórficos, **generic components** tipados y un **event system** type-safe.
MD,
            'starter_code' => <<<'TS'
// ─── 1. Discriminated Union para notificaciones ─────────────────────────
// Crea un tipo Notification que use discriminated unions:
// - type: "success" → tiene message: string
// - type: "error" → tiene message: string y code: number
// - type: "loading" → tiene progress: number (0-100)
// Implementa renderNotification que maneje cada caso.

type Notification =
  | { type: 'success'; message: string }
  // Completa los otros casos aquí
  ;

function renderNotification(notification: Notification): string {
  // Usa switch/case con exhaustive check
  // success → "✓ {message}"
  // error → "✗ [{code}] {message}"
  // loading → "⏳ {progress}%"
  // Tu código aquí
  return '';
}

// ─── 2. Generic Container ────────────────────────────────────────────────
// Implementa una clase Container genérica que simula un componente
// que puede contener datos de cualquier tipo con transformaciones tipadas.
class Container<T> {
  constructor(private value: T) {}

  // map: transforma el valor interno con una función tipada
  map<U>(fn: (value: T) => U): Container<U> {
    // Tu código aquí
    return new Container(fn(this.value));
  }

  // flatMap: como map pero para funciones que retornan Container
  flatMap<U>(fn: (value: T) => Container<U>): Container<U> {
    // Tu código aquí
    return fn(this.value);
  }

  // filter: retorna el Container o null si no cumple el predicado
  filter(predicate: (value: T) => boolean): Container<T> | null {
    // Tu código aquí
    return null;
  }

  // getValue: extrae el valor
  getValue(): T {
    return this.value;
  }
}

// ─── 3. Type-safe Event Emitter ──────────────────────────────────────────
// Un sistema de eventos donde los tipos de payload están ligados a los nombres de evento.
// Usa un type parameter que mapea nombre de evento → tipo de payload.

interface EventMap {
  click: { x: number; y: number };
  change: { value: string; previousValue: string };
  submit: { data: Record<string, unknown> };
  resize: { width: number; height: number };
}

class TypedEmitter<T extends Record<string, unknown>> {
  private listeners = new Map<keyof T, Set<(payload: any) => void>>();

  on<K extends keyof T>(event: K, handler: (payload: T[K]) => void): () => void {
    // Registra el handler y retorna una función para des-suscribirse
    // Tu código aquí
    return () => {};
  }

  emit<K extends keyof T>(event: K, payload: T[K]): void {
    // Ejecuta todos los handlers registrados para ese evento
    // Tu código aquí
  }

  off<K extends keyof T>(event: K): void {
    // Elimina todos los handlers de un evento
    // Tu código aquí
  }
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
// Discriminated unions
console.log(renderNotification({ type: 'success', message: 'Guardado' }));
// "✓ Guardado"
console.log(renderNotification({ type: 'error', message: 'Falló', code: 404 }));
// "✗ [404] Falló"
console.log(renderNotification({ type: 'loading', progress: 75 }));
// "⏳ 75%"

// Generic Container
const result = new Container(5)
  .map(x => x * 2)
  .map(x => `Value: ${x}`)
  .getValue();
console.log(result); // "Value: 10"

const filtered = new Container(10)
  .filter(x => x > 5);
console.log(filtered?.getValue()); // 10

const filteredOut = new Container(3)
  .filter(x => x > 5);
console.log(filteredOut); // null

// Chain con flatMap
const nested = new Container(5)
  .flatMap(x => new Container(x * 3))
  .map(x => x + 1)
  .getValue();
console.log(nested); // 16

// Type-safe Event Emitter
const emitter = new TypedEmitter<EventMap>();
const clickLogs: string[] = [];
const unsub = emitter.on('click', ({ x, y }) => clickLogs.push(`${x},${y}`));

emitter.emit('click', { x: 10, y: 20 });
emitter.emit('click', { x: 30, y: 40 });
console.log(clickLogs); // ["10,20", "30,40"]

unsub(); // des-suscribir
emitter.emit('click', { x: 50, y: 60 });
console.log(clickLogs.length); // 2 (no recibe más)

// Type-safety: esto daría error en compilación (ejemplo comentado):
// emitter.emit('click', { value: 'wrong' }); // TS Error!
TS,
        ];

        // ── Lección 16: Estilos ───────────────────────────────────────────
        if ($l = $lessons->get(16)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Implementa un sistema de estilos con utility classes',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Tailwind CSS usa utility classes para estilar componentes. Implementa un mini sistema similar:
una función `cx` para clases condicionales, un `StyleEngine` que genera CSS, y un generador de `theme tokens`.
MD,
            'starter_code' => <<<'TS'
// ─── 1. cx — class name builder ──────────────────────────────────────────
// Simula clsx/classnames: combina clases condicionales.
// Acepta strings, objetos {clase: boolean}, arrays y valores falsy.
type CxInput = string | undefined | null | false | Record<string, boolean> | CxInput[];

function cx(...inputs: CxInput[]): string {
  // Tu código aquí
  // "btn", {active: true, disabled: false}, ["rounded", null]
  // → "btn active rounded"
  return '';
}

// ─── 2. StyleEngine ──────────────────────────────────────────────────────
// Genera utilidades CSS a partir de definiciones.
// Simula cómo Tailwind genera sus clases.
interface StyleRule {
  property: string;
  values: Record<string, string>;
}

class StyleEngine {
  private rules: StyleRule[] = [];

  addRule(rule: StyleRule): this {
    this.rules.push(rule);
    return this;
  }

  // Genera un objeto que mapea className → CSS string
  // Ej: { "p-1": "padding: 4px;", "p-2": "padding: 8px;" }
  generate(): Record<string, string> {
    // Tu código aquí
    return {};
  }

  // Dado un array de classNames usados, retorna solo el CSS necesario (purge)
  purge(usedClasses: string[]): string {
    // Tu código aquí
    // Retorna CSS puro: ".p-1 { padding: 4px; }\n.p-2 { padding: 8px; }"
    return '';
  }
}

// ─── 3. createTheme ──────────────────────────────────────────────────────
// Genera design tokens consistentes a partir de una configuración base.
interface ThemeConfig {
  colors: Record<string, string>;
  spacing: number[];        // valores en px: [0, 4, 8, 16, 24, 32, 48, 64]
  fontSizes: number[];      // valores en px
  breakpoints: Record<string, number>;  // { sm: 640, md: 768, lg: 1024 }
}

interface Theme {
  color: (name: string) => string;
  space: (index: number) => string;
  fontSize: (index: number) => string;
  mediaQuery: (breakpoint: string) => string;
  css: (styles: Record<string, string>) => string;
}

function createTheme(config: ThemeConfig): Theme {
  // Tu código aquí
  return {} as Theme;
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
// cx
console.log(cx('btn', 'primary'));
// "btn primary"
console.log(cx('btn', { active: true, disabled: false }));
// "btn active"
console.log(cx('btn', undefined, null, false, ['rounded', 'shadow']));
// "btn rounded shadow"
console.log(cx({ flex: true, hidden: false }, ['p-4', { 'mt-2': true }]));
// "flex p-4 mt-2"

// StyleEngine
const engine = new StyleEngine()
  .addRule({ property: 'padding', values: { '1': '4px', '2': '8px', '4': '16px' } })
  .addRule({ property: 'margin', values: { '1': '4px', '2': '8px' } })
  .addRule({ property: 'font-size', values: { 'sm': '14px', 'base': '16px', 'lg': '18px' } });

const allClasses = engine.generate();
console.log(allClasses['p-1']);        // "padding: 4px;"
console.log(allClasses['m-2']);        // "margin: 8px;"
console.log(allClasses['text-lg']);    // "font-size: 18px;"

const css = engine.purge(['p-2', 'm-1', 'text-base']);
console.log(css);
// ".p-2 { padding: 8px; }\n.m-1 { margin: 4px; }\n.text-base { font-size: 16px; }"

// createTheme
const theme = createTheme({
  colors: { primary: '#3b82f6', danger: '#dc2626' },
  spacing: [0, 4, 8, 16, 24, 32],
  fontSizes: [12, 14, 16, 18, 24, 32],
  breakpoints: { sm: 640, md: 768, lg: 1024 },
});

console.log(theme.color('primary'));     // "#3b82f6"
console.log(theme.space(3));             // "16px"
console.log(theme.fontSize(2));          // "16px"
console.log(theme.mediaQuery('md'));     // "@media (min-width: 768px)"
console.log(theme.css({ color: theme.color('danger'), padding: theme.space(2) }));
// "color: #dc2626; padding: 8px;"
TS,
        ];

        // ── Lección 17: SSR / Next.js ─────────────────────────────────────
        if ($l = $lessons->get(17)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simula SSR: renderizado en servidor y hidratación',
            'language'     => 'typescript',
            'description'  => <<<'MD'
En SSR, el servidor genera HTML y el cliente lo "hidrata" con interactividad.
Implementa `renderToString` (genera HTML desde VNodes), `hydrate` (asocia handlers) y un sistema de `ServerComponent` con data fetching.
MD,
            'starter_code' => <<<'TS'
// ─── Tipos ───────────────────────────────────────────────────────────────
interface VNode {
  tag: string;
  props: Record<string, unknown>;
  children: (VNode | string)[];
}

function h(tag: string, props: Record<string, unknown>, ...children: (VNode | string)[]): VNode {
  return { tag, props, children };
}

// ─── 1. renderToString ───────────────────────────────────────────────────
// Convierte un árbol de VNodes a HTML string (como ReactDOMServer.renderToString).
// Reglas:
// - Self-closing tags: img, br, hr, input
// - Props: se convierten en atributos HTML (className → class, htmlFor → for)
// - Ignora props que empiezan con "on" (event handlers no van en SSR)
// - Los children string se escapan (< → &lt;, > → &gt;, & → &amp;)
const SELF_CLOSING = new Set(['img', 'br', 'hr', 'input']);

function renderToString(node: VNode | string): string {
  // Tu código aquí
  return '';
}

// ─── 2. hydrate ──────────────────────────────────────────────────────────
// Simula la hidratación: extrae todos los event handlers del árbol de VNodes.
// Retorna un mapa de { "tag[prop=value]": handlerList } para asociar handlers.
interface HydrationMap {
  selector: string;
  events: Record<string, Function>;
}

function hydrate(node: VNode): HydrationMap[] {
  // Recorre el árbol y extrae props que empiezan con "on"
  // El selector se construye como: "tag.className" o "tag#id" si tiene id
  // Tu código aquí
  return [];
}

// ─── 3. ServerComponent ──────────────────────────────────────────────────
// Simula un Server Component que ejecuta un fetcher asíncrono
// y retorna el HTML resultante. Como en Next.js, el componente
// NO se envía al cliente — solo el HTML generado.
interface ServerComponentResult {
  html: string;
  data: unknown;
  fetchedAt: string;  // ISO timestamp
}

async function renderServerComponent<T>(
  fetcher: () => Promise<T>,
  template: (data: T) => VNode
): Promise<ServerComponentResult> {
  // 1. Ejecuta el fetcher
  // 2. Pasa el resultado al template para obtener un VNode
  // 3. Renderiza a string
  // Tu código aquí
  return { html: '', data: null, fetchedAt: '' };
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
// renderToString
const tree = h('div', { className: 'card', id: 'main' },
  h('h1', {}, 'Hello <World>'),
  h('p', { className: 'desc' }, 'A ', h('strong', {}, 'bold'), ' text'),
  h('img', { src: '/photo.jpg', alt: 'Photo' }),
  h('button', { onClick: () => alert('hi'), className: 'btn' }, 'Click'),
);

const html = renderToString(tree);
console.log(html);
// <div class="card" id="main"><h1>Hello &lt;World&gt;</h1><p class="desc">A <strong>bold</strong> text</p><img src="/photo.jpg" alt="Photo" /><button class="btn">Click</button></div>

// hydrate
const handlers = hydrate(tree);
console.log(handlers.length);  // 1 (solo el button tiene onClick)
console.log(handlers[0]?.selector); // "button.btn"
console.log(Object.keys(handlers[0]?.events ?? {})); // ["onClick"]

// ServerComponent
async function main() {
  const result = await renderServerComponent(
    async () => {
      return [
        { id: 1, title: 'Post 1' },
        { id: 2, title: 'Post 2' },
      ];
    },
    (posts) => h('ul', {},
      ...posts.map(p => h('li', { key: String(p.id) }, p.title))
    )
  );

  console.log(result.html);
  // "<ul><li>Post 1</li><li>Post 2</li></ul>"
  console.log(result.data);
  // [{ id: 1, title: 'Post 1' }, { id: 2, title: 'Post 2' }]
  console.log(typeof result.fetchedAt); // "string" (ISO date)
}

main();
TS,
        ];

        // ── Lección 18: Patrones avanzados ────────────────────────────────
        if ($l = $lessons->get(18)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Implementa Compound Components y un State Machine',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Los patrones avanzados resuelven problemas de composición y flujos complejos.
Implementa un sistema de **Compound Components** (parent + children con estado compartido) y un **State Machine** para un flujo de checkout.
MD,
            'starter_code' => <<<'TS'
// ─── 1. Compound Component: Accordion ────────────────────────────────────
// Implementa un Accordion donde solo un panel puede estar abierto a la vez.
// Es un patrón donde el padre mantiene el estado y los hijos lo consumen.

interface AccordionItem {
  id: string;
  title: string;
  content: string;
}

class Accordion {
  private items: AccordionItem[] = [];
  private activeId: string | null = null;

  addItem(item: AccordionItem): this {
    this.items.push(item);
    return this;
  }

  toggle(id: string): void {
    // Si el id es el activo, lo cierra. Si no, lo abre (cierra el anterior).
    // Tu código aquí
  }

  getActiveItem(): AccordionItem | null {
    // Retorna el item activo o null si ninguno está abierto.
    // Tu código aquí
    return null;
  }

  // Renderiza como texto: muestra el título de cada item.
  // Si está activo, muestra también el contenido debajo.
  render(): string {
    // Formato:
    // ▸ Título cerrado
    // ▾ Título abierto
    //   Contenido del item abierto
    // Tu código aquí
    return '';
  }
}

// ─── 2. State Machine para Checkout ──────────────────────────────────────
// Define un flujo de checkout con transiciones válidas.
// Estados: idle → cart → shipping → payment → confirmation → completed
// Algunas transiciones no son válidas (ej: de idle a payment directamente).

type CheckoutState = 'idle' | 'cart' | 'shipping' | 'payment' | 'confirmation' | 'completed';

type CheckoutEvent =
  | { type: 'ADD_ITEM' }
  | { type: 'CHECKOUT' }
  | { type: 'SET_ADDRESS'; address: string }
  | { type: 'SET_PAYMENT'; method: string }
  | { type: 'CONFIRM' }
  | { type: 'BACK' }
  | { type: 'RESET' };

interface MachineContext {
  items: string[];
  address: string;
  paymentMethod: string;
}

class CheckoutMachine {
  private state: CheckoutState = 'idle';
  private context: MachineContext = { items: [], address: '', paymentMethod: '' };
  private history: CheckoutState[] = [];

  getState(): CheckoutState { return this.state; }
  getContext(): MachineContext { return { ...this.context }; }

  // Procesa un evento y transiciona al siguiente estado si es válido.
  // Retorna true si la transición fue exitosa, false si no es válida.
  send(event: CheckoutEvent): boolean {
    // Define las transiciones válidas para cada estado+evento
    // Tu código aquí
    // RESET siempre vuelve a 'idle' y limpia el contexto
    // BACK vuelve al estado anterior si hay historial
    return false;
  }

  // Retorna los eventos válidos desde el estado actual
  getAvailableEvents(): string[] {
    // Tu código aquí
    return [];
  }
}

// ─── 3. Pipe (composición funcional) ─────────────────────────────────────
// Implementa pipe para componer funciones de izquierda a derecha.
// pipe(f, g, h)(x) === h(g(f(x)))
function pipe<T>(...fns: Array<(arg: T) => T>): (input: T) => T {
  // Tu código aquí
  return (input) => input;
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
// Accordion
const accordion = new Accordion()
  .addItem({ id: 'a', title: 'React', content: 'Biblioteca para UIs' })
  .addItem({ id: 'b', title: 'Vue', content: 'Framework progresivo' })
  .addItem({ id: 'c', title: 'Angular', content: 'Framework completo' });

console.log(accordion.getActiveItem()); // null
accordion.toggle('a');
console.log(accordion.getActiveItem()?.title); // "React"
console.log(accordion.render());
// "▾ React\n  Biblioteca para UIs\n▸ Vue\n▸ Angular"

accordion.toggle('b');
console.log(accordion.getActiveItem()?.title); // "Vue"
accordion.toggle('b'); // toggle off
console.log(accordion.getActiveItem()); // null

// Checkout Machine
const machine = new CheckoutMachine();
console.log(machine.getState()); // "idle"

console.log(machine.send({ type: 'SET_PAYMENT', method: 'card' })); // false (no válido desde idle)
console.log(machine.send({ type: 'ADD_ITEM' }));  // true → "cart"
console.log(machine.getState());                    // "cart"

console.log(machine.send({ type: 'CHECKOUT' }));   // true → "shipping"
console.log(machine.send({ type: 'SET_ADDRESS', address: 'Calle 123' })); // true → "payment"
console.log(machine.getContext().address);           // "Calle 123"

console.log(machine.send({ type: 'SET_PAYMENT', method: 'card' })); // true → "confirmation"
console.log(machine.send({ type: 'BACK' }));        // true → "payment"
console.log(machine.send({ type: 'SET_PAYMENT', method: 'paypal' })); // true → "confirmation"
console.log(machine.send({ type: 'CONFIRM' }));     // true → "completed"

console.log(machine.send({ type: 'RESET' }));       // true → "idle"
console.log(machine.getState());                     // "idle"

// Pipe
const transform = pipe(
  (s: string) => s.trim(),
  (s: string) => s.toLowerCase(),
  (s: string) => s.replace(/\s+/g, '-'),
);
console.log(transform('  Hello World  ')); // "hello-world"

const calc = pipe(
  (n: number) => n * 2,
  (n: number) => n + 10,
  (n: number) => n / 3,
);
console.log(calc(5)); // (5*2 + 10) / 3 = 20/3 ≈ 6.666...
TS,
        ];

        // ── Lección 19: Preguntas de entrevista ───────────────────────────
        if ($l = $lessons->get(19)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Desafíos técnicos de entrevista React',
            'language'     => 'typescript',
            'description'  => <<<'MD'
Ejercicios comunes en entrevistas técnicas de React. Implementa: un **deep equality checker**,
un **event bus** desacoplado para comunicación entre componentes, y un hook `useAsync` que gestiona estados loading/error/data.
MD,
            'starter_code' => <<<'TS'
// ─── 1. deepEqual ────────────────────────────────────────────────────────
// Compara dos valores con igualdad profunda (deep equality).
// Soporta: primitivos, arrays, objetos planos, null, undefined, NaN.
// No necesita soportar: Date, Map, Set, RegExp, funciones.
function deepEqual(a: unknown, b: unknown): boolean {
  // Tu código aquí
  // Tip: maneja los casos base primero, luego recurse
  return false;
}

// ─── 2. EventBus ─────────────────────────────────────────────────────────
// Patrón pub/sub para comunicación desacoplada entre componentes.
// Similar a un EventEmitter pero con wildcards: "user.*" matchea "user.login", "user.logout".
class EventBus {
  private handlers = new Map<string, Set<(data: unknown) => void>>();

  on(pattern: string, handler: (data: unknown) => void): () => void {
    // Si pattern contiene "*", matchea con cualquier segmento
    // Retorna función de des-suscripción
    // Tu código aquí
    return () => {};
  }

  emit(event: string, data?: unknown): void {
    // Ejecuta handlers que matcheen el evento (exacto o por wildcard)
    // Tu código aquí
  }

  // Matchea un evento contra un patrón con wildcards
  private matches(pattern: string, event: string): boolean {
    // "user.*" matchea "user.login" pero no "user.profile.edit"
    // "**" matchea todo
    // "user.**" matchea "user.login" y "user.profile.edit"
    // Tu código aquí
    return false;
  }

  listenerCount(pattern?: string): number {
    if (!pattern) {
      let count = 0;
      this.handlers.forEach(set => count += set.size);
      return count;
    }
    return this.handlers.get(pattern)?.size ?? 0;
  }
}

// ─── 3. useAsync (simulated) ─────────────────────────────────────────────
// Gestiona el ciclo de vida de una operación async:
// idle → loading → success/error
// Incluye refetch y reset.
interface AsyncState<T> {
  status: 'idle' | 'loading' | 'success' | 'error';
  data: T | null;
  error: Error | null;
  isLoading: boolean;
}

function createAsync<T>(asyncFn: () => Promise<T>) {
  let state: AsyncState<T> = {
    status: 'idle',
    data: null,
    error: null,
    isLoading: false,
  };

  const listeners = new Set<(state: AsyncState<T>) => void>();
  function notify() { listeners.forEach(fn => fn(state)); }

  return {
    getState: () => ({ ...state }),

    execute: async (): Promise<AsyncState<T>> => {
      // Pasa a loading, ejecuta asyncFn, pasa a success o error
      // Tu código aquí
      return state;
    },

    reset: (): void => {
      // Vuelve a idle
      // Tu código aquí
    },

    subscribe: (fn: (state: AsyncState<T>) => void): (() => void) => {
      listeners.add(fn);
      return () => listeners.delete(fn);
    },
  };
}

// ─── Pruebas ─────────────────────────────────────────────────────────────
// deepEqual
console.log(deepEqual(1, 1));                           // true
console.log(deepEqual('a', 'b'));                       // false
console.log(deepEqual(NaN, NaN));                       // true
console.log(deepEqual(null, undefined));                // false
console.log(deepEqual([1, [2, 3]], [1, [2, 3]]));      // true
console.log(deepEqual([1, [2, 3]], [1, [2, 4]]));      // false
console.log(deepEqual(
  { a: 1, b: { c: [1, 2] } },
  { a: 1, b: { c: [1, 2] } }
)); // true
console.log(deepEqual(
  { a: 1, b: { c: [1, 2] } },
  { a: 1, b: { c: [1, 3] } }
)); // false

// EventBus
const bus = new EventBus();
const received: string[] = [];

bus.on('user.login', (data) => received.push(`login: ${data}`));
bus.on('user.*', (data) => received.push(`user wildcard: ${data}`));
const unsubAll = bus.on('**', (data) => received.push(`global: ${data}`));

bus.emit('user.login', 'Ana');
bus.emit('user.logout', 'Bob');
bus.emit('system.start');

console.log(received);
// ["login: Ana", "user wildcard: Ana", "global: Ana",
//  "user wildcard: Bob", "global: Bob",
//  "global: undefined"]

// useAsync
async function testAsync() {
  let attempts = 0;
  const fetchData = createAsync(async () => {
    attempts++;
    if (attempts === 1) throw new Error('Network error');
    return { id: 1, name: 'Ana' };
  });

  const states: string[] = [];
  fetchData.subscribe(s => states.push(s.status));

  // Primera ejecución → error
  const r1 = await fetchData.execute();
  console.log(r1.status);   // "error"
  console.log(r1.error?.message); // "Network error"

  // Segunda ejecución → success
  const r2 = await fetchData.execute();
  console.log(r2.status);  // "success"
  console.log(r2.data);    // { id: 1, name: "Ana" }

  console.log(states);
  // ["loading", "error", "loading", "success"]

  fetchData.reset();
  console.log(fetchData.getState().status); // "idle"
}

testAsync();
TS,
        ];

        return $ex;
    }
}
