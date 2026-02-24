# Formularios en React

El manejo de formularios es una de las tareas más frecuentes en aplicaciones web. React ofrece dos enfoques: **componentes controlados** (la norma) y **componentes no controlados** (con refs).

---

## Componentes controlados

En un componente controlado, React es la **fuente de verdad** del valor del input. Cada cambio pasa por el estado:

```tsx
function LoginForm() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    console.log({ email, password })
  }

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="email"
        value={email}             // Valor controlado por el estado
        onChange={e => setEmail(e.target.value)}  // Actualizar estado en cada tecla
      />
      <input
        type="password"
        value={password}
        onChange={e => setPassword(e.target.value)}
      />
      <button type="submit">Entrar</button>
    </form>
  )
}
```

### Ventajas de controlados

- Validación en tiempo real
- Control total sobre el valor (formateo, limitar caracteres)
- Estado siempre sincronizado con la UI
- Fácil de testear

---

## Formulario con estado unificado

Para formularios con muchos campos, usa un solo objeto de estado:

```tsx
interface FormData {
  name: string
  email: string
  role: string
  bio: string
  newsletter: boolean
}

function ProfileForm() {
  const [form, setForm] = useState<FormData>({
    name: '',
    email: '',
    role: 'developer',
    bio: '',
    newsletter: false,
  })

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>
  ) => {
    const { name, value, type } = e.target
    const checked = (e.target as HTMLInputElement).checked

    setForm(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
    }))
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    console.log(form)
  }

  return (
    <form onSubmit={handleSubmit}>
      <input name="name" value={form.name} onChange={handleChange} placeholder="Nombre" />

      <input name="email" type="email" value={form.email} onChange={handleChange} placeholder="Email" />

      <select name="role" value={form.role} onChange={handleChange}>
        <option value="developer">Developer</option>
        <option value="designer">Designer</option>
        <option value="manager">Manager</option>
      </select>

      <textarea name="bio" value={form.bio} onChange={handleChange} rows={4} />

      <label>
        <input name="newsletter" type="checkbox" checked={form.newsletter} onChange={handleChange} />
        Suscribirme al newsletter
      </label>

      <button type="submit">Guardar</button>
    </form>
  )
}
```

---

## Validación manual

```tsx
interface Errors {
  name?: string
  email?: string
  password?: string
}

function RegisterForm() {
  const [form, setForm] = useState({ name: '', email: '', password: '' })
  const [errors, setErrors] = useState<Errors>({})
  const [submitted, setSubmitted] = useState(false)

  const validate = (data: typeof form): Errors => {
    const errs: Errors = {}
    if (!data.name.trim()) errs.name = 'El nombre es requerido'
    if (!data.email.includes('@')) errs.email = 'Email inválido'
    if (data.password.length < 8) errs.password = 'Mínimo 8 caracteres'
    return errs
  }

  // Validación en tiempo real (después del primer submit)
  useEffect(() => {
    if (submitted) {
      setErrors(validate(form))
    }
  }, [form, submitted])

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    setSubmitted(true)
    const errs = validate(form)
    setErrors(errs)
    if (Object.keys(errs).length === 0) {
      console.log('Form válido:', form)
    }
  }

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setForm(prev => ({ ...prev, [e.target.name]: e.target.value }))
  }

  return (
    <form onSubmit={handleSubmit} noValidate>
      <div>
        <input name="name" value={form.name} onChange={handleChange} />
        {errors.name && <span className="error">{errors.name}</span>}
      </div>

      <div>
        <input name="email" type="email" value={form.email} onChange={handleChange} />
        {errors.email && <span className="error">{errors.email}</span>}
      </div>

      <div>
        <input name="password" type="password" value={form.password} onChange={handleChange} />
        {errors.password && <span className="error">{errors.password}</span>}
      </div>

      <button type="submit">Registrarse</button>
    </form>
  )
}
```

---

## useReducer para formularios complejos

Para formularios con lógica de validación compleja, `useReducer` brilla:

```tsx
interface FormState {
  values: { name: string; email: string; password: string }
  errors: Partial<Record<string, string>>
  touched: Partial<Record<string, boolean>>
  isSubmitting: boolean
}

type FormAction =
  | { type: 'SET_FIELD'; field: string; value: string }
  | { type: 'SET_TOUCHED'; field: string }
  | { type: 'SET_ERRORS'; errors: FormState['errors'] }
  | { type: 'SUBMIT_START' }
  | { type: 'SUBMIT_END' }
  | { type: 'RESET' }

const initialState: FormState = {
  values: { name: '', email: '', password: '' },
  errors: {},
  touched: {},
  isSubmitting: false,
}

function formReducer(state: FormState, action: FormAction): FormState {
  switch (action.type) {
    case 'SET_FIELD':
      return { ...state, values: { ...state.values, [action.field]: action.value } }
    case 'SET_TOUCHED':
      return { ...state, touched: { ...state.touched, [action.field]: true } }
    case 'SET_ERRORS':
      return { ...state, errors: action.errors }
    case 'SUBMIT_START':
      return { ...state, isSubmitting: true }
    case 'SUBMIT_END':
      return { ...state, isSubmitting: false }
    case 'RESET':
      return initialState
    default:
      return state
  }
}
```

---

## Componentes no controlados (useRef)

En un componente no controlado, el DOM mantiene el estado y accedes al valor con `ref`:

```tsx
function UncontrolledForm() {
  const nameRef = useRef<HTMLInputElement>(null)
  const emailRef = useRef<HTMLInputElement>(null)

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    console.log({
      name: nameRef.current?.value,
      email: emailRef.current?.value,
    })
  }

  return (
    <form onSubmit={handleSubmit}>
      <input ref={nameRef} defaultValue="" />
      <input ref={emailRef} type="email" defaultValue="" />
      <button type="submit">Enviar</button>
    </form>
  )
}
```

> **¿Cuándo usar no controlados?** Integración con código no-React, formularios simples donde no necesitas validación en tiempo real, o cuando la performance es crítica (muchos campos).

---

## React Hook Form (librería popular)

Para formularios complejos en producción, [React Hook Form](https://react-hook-form.com/) es la opción más popular:

```tsx
import { useForm } from 'react-hook-form'

interface FormData {
  name: string
  email: string
  password: string
}

function HookFormExample() {
  const {
    register,      // Conecta un input al formulario
    handleSubmit,   // Envuelve tu onSubmit
    formState: { errors, isSubmitting },
  } = useForm<FormData>()

  const onSubmit = async (data: FormData) => {
    await submitToAPI(data)
  }

  return (
    <form onSubmit={handleSubmit(onSubmit)}>
      <input {...register('name', { required: 'Nombre es requerido' })} />
      {errors.name && <span>{errors.name.message}</span>}

      <input
        type="email"
        {...register('email', {
          required: 'Email es requerido',
          pattern: { value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: 'Email inválido' }
        })}
      />
      {errors.email && <span>{errors.email.message}</span>}

      <input
        type="password"
        {...register('password', { required: true, minLength: { value: 8, message: 'Mínimo 8 caracteres' } })}
      />
      {errors.password && <span>{errors.password.message}</span>}

      <button disabled={isSubmitting}>
        {isSubmitting ? 'Enviando...' : 'Registrarse'}
      </button>
    </form>
  )
}
```

### Validación con Zod + React Hook Form

```tsx
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'

const schema = z.object({
  name: z.string().min(2, 'Mínimo 2 caracteres'),
  email: z.string().email('Email inválido'),
  password: z.string().min(8, 'Mínimo 8 caracteres'),
})

type FormData = z.infer<typeof schema>

function ZodForm() {
  const { register, handleSubmit, formState: { errors } } = useForm<FormData>({
    resolver: zodResolver(schema),
  })

  return (
    <form onSubmit={handleSubmit(data => console.log(data))}>
      <input {...register('name')} />
      {errors.name && <span>{errors.name.message}</span>}
      {/* ... */}
    </form>
  )
}
```

---

## Resumen

| Enfoque | Fuente de verdad | Uso |
|---|---|---|
| **Controlado** | Estado React (`useState`) | Validación en tiempo real, control total |
| **No controlado** | DOM (`useRef`) | Formularios simples, integración con no-React |
| **React Hook Form** | Librería (refs + estado) | Producción, performance, validación con Zod |
| **useReducer** | Reducer + dispatch | Lógica de validación compleja |
