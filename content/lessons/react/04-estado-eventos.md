# Estado y Eventos

El **estado** (`state`) es la memoria interna de un componente — datos que pueden cambiar con el tiempo y provocan un re-renderizado automático. Los **eventos** son las acciones del usuario (clics, tecleo, envío de formularios) que disparan esos cambios de estado.

---

## useState: el hook de estado

`useState` es el hook más básico de React. Declara una variable de estado y una función para actualizarla:

```tsx
import { useState } from 'react'

function Contador() {
  const [count, setCount] = useState(0)
  //     ^state  ^setter       ^valor inicial

  return (
    <div>
      <p>Contador: {count}</p>
      <button onClick={() => setCount(count + 1)}>+1</button>
      <button onClick={() => setCount(count - 1)}>-1</button>
      <button onClick={() => setCount(0)}>Reset</button>
    </div>
  )
}
```

### Reglas importantes

1. **Nunca mutes el estado directamente**: React no detectará el cambio.
2. **Las actualizaciones son asíncronas**: React las agrupa (batching).
3. **Cada render tiene su propio estado**: es una "foto" del estado en ese momento.

```tsx
// ❌ Esto NO actualiza el estado
count = count + 1   // React no se entera

// ✅ Usa siempre el setter
setCount(count + 1)
```

---

## Actualizaciones funcionales

Cuando el nuevo estado depende del anterior, usa la **forma funcional** del setter:

```tsx
// ❌ Problema: si llamas setCount tres veces seguidas, usan el mismo snapshot
setCount(count + 1)  // 0 + 1 = 1
setCount(count + 1)  // 0 + 1 = 1 (¡no 2!)
setCount(count + 1)  // 0 + 1 = 1 (¡no 3!)

// ✅ Forma funcional: recibe el estado previo REAL
setCount(prev => prev + 1)  // 0 → 1
setCount(prev => prev + 1)  // 1 → 2
setCount(prev => prev + 1)  // 2 → 3
```

---

## Estado con objetos

Con objetos, debes crear una **copia nueva** (inmutabilidad):

```tsx
interface User {
  name: string
  email: string
  age: number
}

function Profile() {
  const [user, setUser] = useState<User>({
    name: 'Ana',
    email: 'ana@mail.com',
    age: 28,
  })

  const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    // ✅ Spread operator para crear objeto nuevo
    setUser({ ...user, name: e.target.value })
    // o con forma funcional:
    setUser(prev => ({ ...prev, name: e.target.value }))
  }

  // ❌ NUNCA hagas esto — muta directamente
  // user.name = 'Otro'
  // setUser(user)  ← React ve la misma referencia y NO re-renderiza

  return (
    <div>
      <input value={user.name} onChange={handleNameChange} />
      <p>{user.name} — {user.email}</p>
    </div>
  )
}
```

---

## Estado con arrays

```tsx
function TodoApp() {
  const [todos, setTodos] = useState<string[]>([])
  const [input, setInput] = useState('')

  // Agregar
  const addTodo = () => {
    if (!input.trim()) return
    setTodos(prev => [...prev, input])
    setInput('')
  }

  // Eliminar por índice
  const removeTodo = (index: number) => {
    setTodos(prev => prev.filter((_, i) => i !== index))
  }

  // Actualizar un elemento
  const updateTodo = (index: number, newText: string) => {
    setTodos(prev => prev.map((todo, i) => i === index ? newText : todo))
  }

  return (
    <div>
      <input value={input} onChange={e => setInput(e.target.value)} />
      <button onClick={addTodo}>Agregar</button>
      <ul>
        {todos.map((todo, i) => (
          <li key={i}>
            {todo}
            <button onClick={() => removeTodo(i)}>🗑️</button>
          </li>
        ))}
      </ul>
    </div>
  )
}
```

### Patrón inmutable para arrays (resumen)

| Operación | Inmutable (✅) | Mutable (❌) |
|---|---|---|
| Agregar | `[...arr, nuevo]` | `arr.push(nuevo)` |
| Eliminar | `arr.filter(x => x.id !== id)` | `arr.splice(i, 1)` |
| Actualizar | `arr.map(x => x.id === id ? {...x, done: true} : x)` | `arr[i].done = true` |
| Reemplazar | `setArr(nuevoArray)` | `arr = nuevoArray` |

---

## Eventos en React

### Eventos más comunes

| Evento | Uso | Tipo TypeScript |
|---|---|---|
| `onClick` | Clic en un elemento | `React.MouseEvent` |
| `onChange` | Cambio en input/select/textarea | `React.ChangeEvent` |
| `onSubmit` | Envío de formulario | `React.FormEvent` |
| `onKeyDown` | Presionar tecla | `React.KeyboardEvent` |
| `onFocus` / `onBlur` | Foco entra/sale | `React.FocusEvent` |
| `onMouseEnter` / `onMouseLeave` | Hover | `React.MouseEvent` |

### Eventos sintéticos

React envuelve los eventos nativos del navegador en **SyntheticEvent** para garantizar comportamiento consistente entre navegadores:

```tsx
function HandleClick() {
  const handleClick = (e: React.MouseEvent<HTMLButtonElement>) => {
    e.preventDefault()  // Prevenir comportamiento por defecto
    e.stopPropagation() // Detener propagación del evento
    console.log('Posición:', e.clientX, e.clientY)
  }

  return <button onClick={handleClick}>Clic aquí</button>
}
```

---

## Pasar argumentos a handlers

```tsx
function Lista() {
  const items = ['React', 'Angular', 'Vue']

  // ✅ Arrow function inline
  return (
    <ul>
      {items.map((item, i) => (
        <li key={i}>
          <button onClick={() => handleSelect(item)}>
            {item}
          </button>
        </li>
      ))}
    </ul>
  )

  function handleSelect(item: string) {
    console.log('Seleccionado:', item)
  }
}
```

---

## Ejemplo: Formulario de registro con estado

```tsx
interface FormData {
  name: string
  email: string
  password: string
  acceptTerms: boolean
}

function RegistrationForm() {
  const [form, setForm] = useState<FormData>({
    name: '',
    email: '',
    password: '',
    acceptTerms: false,
  })

  const [errors, setErrors] = useState<Partial<Record<keyof FormData, string>>>({})

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value, type, checked } = e.target
    setForm(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
    }))
  }

  const validate = (): boolean => {
    const newErrors: typeof errors = {}
    if (!form.name) newErrors.name = 'El nombre es requerido'
    if (!form.email.includes('@')) newErrors.email = 'Email inválido'
    if (form.password.length < 8) newErrors.password = 'Mínimo 8 caracteres'
    if (!form.acceptTerms) newErrors.acceptTerms = 'Debes aceptar los términos'
    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    if (validate()) {
      console.log('Datos válidos:', form)
    }
  }

  return (
    <form onSubmit={handleSubmit}>
      <div>
        <input name="name" value={form.name} onChange={handleChange} placeholder="Nombre" />
        {errors.name && <span className="error">{errors.name}</span>}
      </div>

      <div>
        <input name="email" value={form.email} onChange={handleChange} placeholder="Email" />
        {errors.email && <span className="error">{errors.email}</span>}
      </div>

      <div>
        <input name="password" type="password" value={form.password} onChange={handleChange} placeholder="Contraseña" />
        {errors.password && <span className="error">{errors.password}</span>}
      </div>

      <label>
        <input name="acceptTerms" type="checkbox" checked={form.acceptTerms} onChange={handleChange} />
        Acepto los términos
      </label>
      {errors.acceptTerms && <span className="error">{errors.acceptTerms}</span>}

      <button type="submit">Registrarse</button>
    </form>
  )
}
```

---

## Levantar el estado (Lifting State Up)

Cuando dos componentes hermanos necesitan compartir datos, el estado se "levanta" al padre más cercano:

```tsx
function App() {
  // El estado vive en el padre
  const [temperature, setTemperature] = useState(20)

  return (
    <div>
      <TemperatureInput
        label="Celsius"
        value={temperature}
        onChange={setTemperature}
      />
      <TemperatureDisplay celsius={temperature} />
    </div>
  )
}

function TemperatureInput({ label, value, onChange }: {
  label: string
  value: number
  onChange: (val: number) => void
}) {
  return (
    <label>
      {label}:
      <input
        type="number"
        value={value}
        onChange={e => onChange(Number(e.target.value))}
      />
    </label>
  )
}

function TemperatureDisplay({ celsius }: { celsius: number }) {
  const fahrenheit = (celsius * 9) / 5 + 32
  return <p>{celsius}°C = {fahrenheit.toFixed(1)}°F</p>
}
```

---

## Resumen

| Concepto | Descripción |
|---|---|
| `useState(initial)` | Declara estado local, retorna `[value, setter]` |
| **Inmutabilidad** | Siempre crear copias nuevas de objetos/arrays |
| **Forma funcional** | `setX(prev => ...)` cuando dependes del estado anterior |
| **SyntheticEvent** | React envuelve eventos nativos para consistencia |
| **Lifting State Up** | Mover estado al padre cuando hermanos lo necesitan |
| **Batching** | React agrupa múltiples `setState` en un solo re-render |
