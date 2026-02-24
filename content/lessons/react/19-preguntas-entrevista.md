# Preguntas de Entrevista: React

Recopilación de las preguntas más frecuentes en entrevistas técnicas sobre React, con respuestas claras y directas.

---

## 1. ¿Qué es React y en qué se diferencia de un framework?

React es una **biblioteca** de JavaScript para construir interfaces de usuario. A diferencia de frameworks como Angular, React solo se encarga de la capa de vista. No incluye routing, gestión de estado global ni un módulo HTTP por defecto — eso se resuelve con librerías del ecosistema (React Router, Redux/Zustand, TanStack Query).

---

## 2. ¿Qué es el Virtual DOM?

El Virtual DOM es una representación en memoria del DOM real. Cuando el estado cambia, React crea un nuevo árbol virtual, lo compara con el anterior (**diffing**) y aplica solo los cambios mínimos al DOM real (**reconciliation**). Esto evita manipulaciones innecesarias del DOM, que son costosas.

---

## 3. ¿Qué es JSX?

JSX es una extensión de sintaxis para JavaScript que permite escribir HTML dentro de JavaScript. Se transpila a llamadas `React.createElement()`. No es HTML: usa `className` en vez de `class`, `htmlFor` en vez de `for`, y las expresiones van entre `{}`.

---

## 4. ¿Cuál es la diferencia entre componentes funcionales y de clase?

Los **componentes funcionales** son funciones que reciben props y retornan JSX. Los **componentes de clase** extienden `React.Component` y usan un método `render()`. Desde React 16.8 (Hooks), los componentes funcionales pueden manejar estado y efectos secundarios, por lo que los componentes de clase se consideran **legacy**.

---

## 5. ¿Qué son las props?

Las props son **datos que un componente padre pasa a un hijo**. Son de solo lectura (inmutables). Se acceden como parámetros de la función del componente. Permiten que los componentes sean reutilizables y configurables.

---

## 6. ¿Qué son los Hooks? Nombra los más importantes.

Los Hooks son funciones que permiten "engancharse" al estado y ciclo de vida de React desde componentes funcionales:

- **useState**: Estado local
- **useEffect**: Efectos secundarios (fetch, subscripciones, DOM)
- **useContext**: Consumir un Context
- **useRef**: Referencia mutable que persiste entre renders
- **useMemo**: Memorizar un valor calculado
- **useCallback**: Memorizar una función
- **useReducer**: Estado complejo con patrón reducer

---

## 7. ¿Cuáles son las reglas de los Hooks?

1. **Solo llamar Hooks en el nivel superior** — nunca dentro de loops, condiciones o funciones anidadas.
2. **Solo llamar Hooks desde componentes funcionales o custom hooks** — nunca desde funciones regulares de JavaScript.

Esto permite que React asocie correctamente cada Hook con su estado.

---

## 8. ¿Qué es useEffect y cuándo se ejecuta?

`useEffect` ejecuta efectos secundarios después del render. Su segundo argumento (array de dependencias) controla cuándo se re-ejecuta:

- `useEffect(() => {}, [])` — Solo al montar
- `useEffect(() => {}, [a, b])` — Al montar y cuando `a` o `b` cambien
- `useEffect(() => {})` — Después de cada render (raramente deseado)

La función de retorno es el **cleanup**: se ejecuta al desmontar o antes de re-ejecutar el efecto.

---

## 9. ¿Qué es el estado (state) en React?

El estado es **datos internos** de un componente que pueden cambiar con el tiempo. Cuando el estado cambia, React re-renderiza el componente. Se maneja con `useState` (simples) o `useReducer` (complejos). El estado es **inmutable**: nunca se modifica directamente, se reemplaza con un nuevo valor.

---

## 10. ¿Cuál es la diferencia entre estado controlado y no controlado?

- **Controlado**: React controla el valor del input a través del state. `value={state}` + `onChange={handler}`.
- **No controlado**: El DOM controla el valor. Se accede mediante `useRef`. Útil para integración con librerías de terceros.

---

## 11. ¿Qué es el Context API y cuándo usarlo?

Context permite pasar datos a través del árbol de componentes **sin prop drilling**. Se crea con `createContext`, se provee con `<Context.Provider>`, y se consume con `useContext`. Es ideal para datos "globales" como tema, idioma o autenticación. **No** es un reemplazo de estado global complejo — para eso usa Redux, Zustand o similar.

---

## 12. ¿Qué es React.memo?

`React.memo` es un HOC que memoriza un componente funcional. Solo se re-renderiza si sus props cambian (shallow comparison). Es útil para componentes que reciben las mismas props frecuentemente:

```tsx
const ExpensiveList = React.memo(function ExpensiveList({ items }: { items: Item[] }) {
  return items.map(item => <ItemCard key={item.id} item={item} />)
})
```

---

## 13. ¿Cuál es la diferencia entre useMemo y useCallback?

- **useMemo**: Memoriza un **valor** calculado. `useMemo(() => compute(a, b), [a, b])`
- **useCallback**: Memoriza una **función**. `useCallback((x) => doSomething(x, a), [a])`

`useCallback(fn, deps)` es equivalente a `useMemo(() => fn, deps)`.

---

## 14. ¿Qué es el prop drilling y cómo evitarlo?

Prop drilling es pasar props a través de múltiples niveles de componentes que no los necesitan, solo para llegar al componente que sí los usa. Soluciones:

- **Context API** — para datos de baja frecuencia de cambio
- **Redux / Zustand** — para estado global complejo
- **Composición** — reestructurar componentes para que los hijos tengan acceso directo

---

## 15. ¿Qué es React Router y cómo funciona?

React Router es la librería estándar de routing para SPA con React. Componentes principales:

- `<BrowserRouter>` — proveedor de routing
- `<Routes>` y `<Route>` — definen las rutas
- `<Link>` y `<NavLink>` — navegación sin recarga
- `useParams`, `useNavigate`, `useLocation` — hooks de navegación
- `<Outlet>` — punto de inserción para rutas anidadas

---

## 16. ¿Qué son las keys y por qué son importantes?

Las keys ayudan a React a **identificar qué elementos han cambiado** en una lista. Deben ser estables, únicas y predecibles. **Nunca uses el índice como key** si la lista puede reordenarse o filtrarse, porque causa problemas de rendimiento y bugs en el estado de los componentes.

---

## 17. ¿Qué son los Server Components?

Los Server Components (React 18+, Next.js) son componentes que se renderizan **exclusivamente en el servidor**. Su código JavaScript no se envía al cliente. Pueden acceder directamente a bases de datos y APIs del servidor. No pueden usar hooks ni event handlers. Reducen significativamente el bundle size del cliente.

---

## 18. ¿Cuál es la diferencia entre SSR, SSG e ISR?

- **SSR (Server-Side Rendering)**: HTML generado en cada request. Siempre actualizado, pero más lento.
- **SSG (Static Site Generation)**: HTML generado en build time. Rápido y cacheable, pero estático.
- **ISR (Incremental Static Regeneration)**: Páginas estáticas que se regeneran después de un intervalo configurable. Balance entre performance y frescura.

---

## 19. ¿Qué es Redux y cuándo es necesario?

Redux es una librería de gestión de estado predecible. Usa un **store centralizado**, **actions** para describir cambios, y **reducers** puros para calcular el nuevo estado. **Redux Toolkit** simplifica mucho su uso.

Es necesario cuando:
- Múltiples componentes no relacionados comparten estado
- El estado tiene lógica de actualización compleja
- Necesitas debugging avanzado (time-travel)
- El equipo necesita patrones estandarizados

**No** lo necesitas para estado local simple o datos de servidor (usa TanStack Query para eso).

---

## 20. ¿Cuáles son las principales diferencias entre React y Angular?

| Aspecto | React | Angular |
|---|---|---|
| Tipo | Biblioteca | Framework completo |
| Lenguaje | JavaScript/TypeScript | TypeScript obligatorio |
| DOM | Virtual DOM | Real DOM + Change Detection |
| State management | Hooks + librería externa | Servicios + RxJS |
| Routing | React Router (externo) | Angular Router (integrado) |
| Curva de aprendizaje | Menor al inicio | Mayor, más conceptos |
| Flexibilidad | Alta (elige tus librerías) | Menor (opinionado) |
| Mobile | React Native | Ionic/NativeScript |

---

## 21. ¿Qué es un Custom Hook?

Un Custom Hook es una función que empieza con `use` y utiliza otros Hooks dentro. Permite **extraer y reutilizar lógica stateful** entre componentes sin cambiar la jerarquía. Por ejemplo, `useForm`, `useAuth`, `useFetch`.

---

## 22. ¿Cómo manejas errores en React?

- **Error Boundaries** (componentes de clase con `componentDidCatch`): capturan errores de rendering en el árbol de componentes hijo.
- **try/catch** en event handlers y funciones async.
- **Suspense + Error Boundary** (React 18+) para data fetching.
- **Libraries**: `react-error-boundary` proporciona un componente funcional para error boundaries.

---

## 23. ¿Qué es la reconciliación (reconciliation)?

Es el proceso mediante el cual React compara el Virtual DOM anterior con el nuevo para determinar los cambios mínimos necesarios. React usa un algoritmo de diffing con complejidad O(n) basado en dos heurísticas:
1. Elementos de diferente tipo producen árboles diferentes.
2. Las keys identifican qué elementos son estables entre renders.

---

## 24. ¿Qué es Suspense?

`<Suspense>` permite declarar un fallback mientras un componente hijo está "suspendido" (cargando datos o código). Se usa con:
- `React.lazy()` — code splitting de componentes
- Data fetching frameworks (TanStack Query, Next.js)
- Futuro: cualquier fuente de datos async

```tsx
<Suspense fallback={<Spinner />}>
  <LazyComponent />
</Suspense>
```

---

## 25. ¿Cómo optimizas el rendimiento de una app React?

1. **React.memo** para evitar re-renders innecesarios
2. **useMemo/useCallback** para estabilizar valores y funciones
3. **Code splitting** con `React.lazy` y `Suspense`
4. **Virtualización** para listas grandes (react-window)
5. **Keys estables** en listas
6. **Debounce** en inputs de búsqueda
7. **React DevTools Profiler** para identificar bottlenecks
8. **useTransition/useDeferredValue** para updates no urgentes
