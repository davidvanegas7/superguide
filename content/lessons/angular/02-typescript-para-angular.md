# TypeScript para Angular

Angular está escrito completamente en **TypeScript**, y para usarlo de forma efectiva necesitas comprender los conceptos clave del lenguaje. Esta lección cubre todo lo que necesitas saber antes de escribir tu primera línea de Angular.

---

## ¿Qué es TypeScript?

TypeScript es un **superset de JavaScript** desarrollado por Microsoft. Todo código JavaScript válido es TypeScript válido, pero TypeScript agrega:

- **Tipado estático** — Declaras qué tipo tiene cada variable
- **Interfaces** — Contratos que definen la forma de los objetos
- **Clases mejoradas** — Con modificadores de acceso (`public`, `private`, `protected`)
- **Decoradores** — Metadatos que Angular usa extensivamente (`@Component`, `@Injectable`, etc.)
- **Genéricos** — Código reutilizable con tipos flexibles

TypeScript se **transpila** a JavaScript estándar para que los navegadores lo entiendan.

---

## Tipos básicos

```typescript
// Tipos primitivos
let nombre: string = 'Angular';
let version: number = 17;
let activo: boolean = true;

// Arrays
let lenguajes: string[] = ['Angular', 'React', 'Vue'];
let numeros: Array<number> = [1, 2, 3];

// Tuplas (array con tipos fijos por posición)
let persona: [string, number] = ['Juan', 30];

// Any (evita el tipado — úsalo con cuidado)
let cualquierCosa: any = 'texto';
cualquierCosa = 42;  // válido, pero pierde seguridad

// Unknown (más seguro que any)
let valor: unknown = 'hola';
if (typeof valor === 'string') {
  console.log(valor.toUpperCase());  // TS sabe que es string aquí
}

// Void (funciones que no retornan valor)
function saludar(): void {
  console.log('Hola');
}

// Never (funciones que nunca retornan)
function lanzarError(mensaje: string): never {
  throw new Error(mensaje);
}

// Null y Undefined
let sinValor: null = null;
let indefinido: undefined = undefined;
```

---

## Union Types e Intersection Types

```typescript
// Union Type: puede ser uno u otro
let id: string | number = 'abc123';
id = 42;  // también válido

function mostrarId(id: string | number): void {
  if (typeof id === 'string') {
    console.log(`ID string: ${id.toUpperCase()}`);
  } else {
    console.log(`ID número: ${id}`);
  }
}

// Intersection Type: combina tipos
type Empleado = { nombre: string; empresa: string };
type Desarrollador = { lenguaje: string };
type DevEmpleado = Empleado & Desarrollador;

const dev: DevEmpleado = {
  nombre: 'Ana',
  empresa: 'TechCorp',
  lenguaje: 'Angular'
};
```

---

## Interfaces

Las interfaces definen la **forma** de un objeto. Son el contrato entre partes de tu código:

```typescript
interface Usuario {
  id: number;
  nombre: string;
  email: string;
  rol?: string;  // ← propiedad opcional (el ? indica que puede no estar)
  readonly createdAt: Date;  // ← no se puede modificar después de la asignación
}

// Uso de la interfaz
const usuario: Usuario = {
  id: 1,
  nombre: 'María',
  email: 'maria@ejemplo.com',
  createdAt: new Date()
};

// Las interfaces también pueden definir métodos
interface Autenticable {
  login(email: string, password: string): Promise<boolean>;
  logout(): void;
}

// Extensión de interfaces
interface Admin extends Usuario {
  permisos: string[];
  nivel: number;
}
```

---

## Type Aliases

Similar a las interfaces pero más flexibles para tipos complejos:

```typescript
// Alias de tipo simple
type ID = string | number;

// Alias para objetos (parecido a interface)
type Producto = {
  id: ID;
  nombre: string;
  precio: number;
};

// Tipos literales (solo puede ser uno de estos valores)
type Estado = 'activo' | 'inactivo' | 'pendiente';
type Rol = 'admin' | 'editor' | 'viewer';

let estadoUsuario: Estado = 'activo';
// estadoUsuario = 'eliminado';  // ❌ Error: no es un valor válido

// Tipos de función
type Callback = (datos: string, error?: Error) => void;
type Transformador<T, U> = (entrada: T) => U;
```

---

## Clases

Las clases en TypeScript tienen modificadores de acceso, esenciales para los servicios de Angular:

```typescript
class Animal {
  // Propiedades con modificadores de acceso
  public nombre: string;
  private edad: number;
  protected especie: string;
  readonly id: number;

  // Constructor con shorthand (declara y asigna a la vez)
  constructor(
    public raza: string,      // ← público automáticamente
    private peso: number,     // ← privado automáticamente
    nombre: string
  ) {
    this.nombre = nombre;
    this.edad = 0;
    this.especie = 'desconocida';
    this.id = Math.random();
  }

  // Método público
  public describir(): string {
    return `${this.nombre} es un ${this.raza}`;
  }

  // Getter
  get info(): string {
    return `${this.nombre} (${this.edad} años)`;
  }

  // Setter
  set edadAnimal(valor: number) {
    if (valor >= 0) {
      this.edad = valor;
    }
  }

  // Método estático
  static crearDesconocido(): Animal {
    return new Animal('mestizo', 0, 'Sin nombre');
  }
}

// Herencia
class Perro extends Animal {
  constructor(nombre: string, raza: string) {
    super(raza, 0, nombre);  // llama al constructor padre
    this.especie = 'Canis lupus familiaris';
  }

  ladrar(): void {
    console.log(`${this.nombre} dice: ¡Guau!`);
  }
}

const perro = new Perro('Rex', 'Labrador');
perro.ladrar();                    // Rex dice: ¡Guau!
console.log(perro.describir());    // Rex es un Labrador
```

---

## Decoradores

Los decoradores son un concepto **fundamental en Angular**. Son funciones que añaden metadatos a clases, métodos y propiedades:

```typescript
// Decorador de clase (lo que Angular usa)
function Sellada(constructor: Function) {
  Object.seal(constructor);
  Object.seal(constructor.prototype);
}

@Sellada
class Config {
  readonly apiUrl = 'https://api.ejemplo.com';
}

// Angular usa decoradores así:
@Component({
  selector: 'app-root',
  template: '<h1>Hola</h1>'
})
class AppComponent {}

@Injectable({
  providedIn: 'root'
})
class MiServicio {}
```

> En Angular no necesitas crear decoradores propios, pero sí usarás los de Angular constantemente: `@Component`, `@Directive`, `@Pipe`, `@Injectable`, `@Input`, `@Output`, `@NgModule`, etc.

---

## Genéricos

Los genéricos permiten crear código reutilizable que funciona con cualquier tipo:

```typescript
// Función genérica
function identity<T>(valor: T): T {
  return valor;
}

identity<string>('hola');   // tipo explícito
identity(42);               // TypeScript infiere number

// Clase genérica — Angular usa esto internamente en HttpClient
class Repositorio<T> {
  private items: T[] = [];

  agregar(item: T): void {
    this.items.push(item);
  }

  obtener(indice: number): T {
    return this.items[indice];
  }

  obtenerTodos(): T[] {
    return [...this.items];
  }
}

interface Producto { id: number; nombre: string; }
const repo = new Repositorio<Producto>();
repo.agregar({ id: 1, nombre: 'Laptop' });
const productos = repo.obtenerTodos();  // Producto[]

// Restricciones en genéricos
function obtenerPropiedad<T, K extends keyof T>(obj: T, clave: K): T[K] {
  return obj[clave];
}

const usuario = { id: 1, nombre: 'Ana', email: 'ana@test.com' };
const nombre = obtenerPropiedad(usuario, 'nombre');  // string
// obtenerPropiedad(usuario, 'telefono');  // ❌ Error: no existe esa clave
```

---

## Async/Await y Promesas

En Angular harás muchas operaciones asíncronas. Es esencial entender las promesas y async/await:

```typescript
// Función que retorna una promesa
function obtenerUsuario(id: number): Promise<Usuario> {
  return fetch(`/api/usuarios/${id}`)
    .then(response => response.json());
}

// Con async/await (más legible)
async function cargarDatos(): Promise<void> {
  try {
    const usuario = await obtenerUsuario(1);
    console.log(usuario.nombre);
  } catch (error) {
    console.error('Error al cargar:', error);
  } finally {
    console.log('Operación terminada');
  }
}

// Promise.all — ejecutar múltiples promesas en paralelo
async function cargarTodo(): Promise<void> {
  const [usuarios, productos] = await Promise.all([
    obtenerUsuarios(),
    obtenerProductos()
  ]);
  // Ambas cargan al mismo tiempo — más eficiente
}
```

---

## Configuración de TypeScript (tsconfig.json)

Angular configura TypeScript con opciones estrictas por defecto:

```json
{
  "compilerOptions": {
    "target": "ES2022",           // Versión JS de destino
    "module": "ES2022",           // Sistema de módulos
    "lib": ["ES2022", "DOM"],     // Librerías incluidas
    "strict": true,               // Activa todas las verificaciones estrictas
    "strictNullChecks": true,     // null y undefined son tipos separados
    "noImplicitAny": true,        // No permite 'any' implícito
    "experimentalDecorators": true, // Habilita decoradores
    "emitDecoratorMetadata": true,  // Metadata de decoradores
    "paths": {
      "@app/*": ["src/app/*"],    // Alias de importación
      "@env/*": ["src/environments/*"]
    }
  }
}
```

Con `strict: true`, TypeScript es más exigente pero detecta más errores antes de ejecutar el código — algo muy valioso en proyectos grandes.

---

## Resumen

| Concepto | Uso en Angular |
|---|---|
| **Interfaces** | Definir modelos de datos (Usuario, Producto, etc.) |
| **Clases** | Componentes, Servicios, Guards, Interceptors |
| **Decoradores** | `@Component`, `@Injectable`, `@Input`, `@Output` |
| **Genéricos** | `HttpClient.get<T>()`, `Observable<T>`, `EventEmitter<T>` |
| **Union Types** | Props opcionales, estados posibles |
| **Async/Await** | Llamadas HTTP, resolvers de rutas |

En la siguiente lección veremos los **Componentes**, el bloque fundamental de cualquier app Angular.
