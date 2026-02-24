# Stimulus en Rails 8

Stimulus es el framework JavaScript ligero de Hotwire que te permite añadir comportamiento interactivo a tu HTML existente. En lugar de construir interfaces completas en JavaScript, Stimulus se conecta al HTML renderizado por el servidor mediante atributos `data-*`.

---

## ¿Qué es Stimulus?

Stimulus sigue una filosofía opuesta a los frameworks SPA: en lugar de que JavaScript controle el HTML, es el HTML quien declara qué JavaScript necesita. Stimulus no renderiza HTML — eso lo hace el servidor. Stimulus solo añade **comportamiento**.

### Principios fundamentales

- El HTML es la fuente de verdad.
- Los controladores se conectan automáticamente al DOM.
- No necesitas gestionar estado complejo en el cliente.
- Ideal para aplicaciones server-rendered con Turbo.

```bash
# Stimulus ya viene preinstalado en Rails 8
# Verificar la instalación
bin/rails stimulus:manifest:update
```

---

## Controladores

Un controlador Stimulus es una clase JavaScript que se conecta a un elemento HTML mediante el atributo `data-controller`.

### Crear un controlador

```bash
# Generar un controlador con el generador de Rails
bin/rails generate stimulus greeting
# Crea: app/javascript/controllers/greeting_controller.js
```

```javascript
// app/javascript/controllers/greeting_controller.js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  greet() {
    console.log("¡Hola desde Stimulus!")
  }
}
```

### Conectar al HTML

```html
<div data-controller="greeting">
  <button data-action="click->greeting#greet">Saludar</button>
</div>
```

Cuando el `div` aparece en el DOM, Stimulus automáticamente instancia el controlador `greeting` y lo conecta.

### Convenciones de nombres

```javascript
// El nombre del archivo determina el nombre del controlador
// app/javascript/controllers/
//   greeting_controller.js       → data-controller="greeting"
//   slide_show_controller.js     → data-controller="slide-show"
//   users/list_controller.js     → data-controller="users--list"
```

---

## Actions (Acciones)

Las acciones conectan eventos del DOM con métodos del controlador usando el atributo `data-action`.

### Sintaxis

```html
<!-- Formato: evento->controlador#método -->
<button data-action="click->greeting#greet">Saludar</button>

<!-- Múltiples acciones en un elemento -->
<input data-action="input->search#filter keydown.enter->search#submit">

<!-- Eventos por defecto (se puede omitir el evento) -->
<!-- click para botones, submit para forms, input para campos -->
<button data-action="greeting#greet">Saludar</button>
<form data-action="search#submit">
<input data-action="search#filter">
```

### Ejemplo: contador interactivo

```javascript
// app/javascript/controllers/counter_controller.js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static targets = ["count"]

  connect() {
    this.counter = 0
  }

  increment() {
    this.counter++
    this.countTarget.textContent = this.counter
  }

  decrement() {
    if (this.counter > 0) {
      this.counter--
      this.countTarget.textContent = this.counter
    }
  }
}
```

```html
<div data-controller="counter">
  <button data-action="counter#decrement">-</button>
  <span data-counter-target="count">0</span>
  <button data-action="counter#increment">+</button>
</div>
```

### Opciones de acción

```html
<!-- Prevenir comportamiento por defecto -->
<form data-action="submit->form#handleSubmit:prevent">

<!-- Ejecutar solo una vez -->
<button data-action="click->analytics#track:once">Rastrear</button>

<!-- Escuchar en window o document -->
<div data-controller="shortcut"
     data-action="keydown.ctrl+k@window->shortcut#open">
</div>
```

---

## Targets (Objetivos)

Los targets son referencias a elementos del DOM dentro del controlador, declarados con `data-<controller>-target`.

### Declarar y usar targets

```javascript
// app/javascript/controllers/form_validator_controller.js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static targets = ["email", "password", "submit", "error"]

  validate() {
    const email = this.emailTarget.value
    const password = this.passwordTarget.value
    let errors = []

    if (!email.includes("@")) {
      errors.push("El email no es válido")
    }

    if (password.length < 8) {
      errors.push("La contraseña debe tener al menos 8 caracteres")
    }

    if (errors.length > 0) {
      this.errorTarget.innerHTML = errors.map(e => `<p class="text-red-500">${e}</p>`).join("")
      this.submitTarget.disabled = true
    } else {
      this.errorTarget.innerHTML = ""
      this.submitTarget.disabled = false
    }
  }
}
```

```html
<div data-controller="form-validator">
  <div data-form-validator-target="error"></div>

  <label>Email</label>
  <input type="email"
         data-form-validator-target="email"
         data-action="input->form-validator#validate">

  <label>Contraseña</label>
  <input type="password"
         data-form-validator-target="password"
         data-action="input->form-validator#validate">

  <button data-form-validator-target="submit" disabled>
    Registrarse
  </button>
</div>
```

### Propiedades de targets

```javascript
// Para un target llamado "item"
this.itemTarget       // Primer elemento que coincide (error si no existe)
this.itemTargets      // Array de todos los elementos que coinciden
this.hasItemTarget    // Boolean: ¿existe al menos un elemento?
```

---

## Values (Valores)

Los values permiten almacenar datos tipados en atributos HTML y leerlos desde el controlador.

### Declarar y usar values

```javascript
// app/javascript/controllers/countdown_controller.js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static values = {
    seconds: { type: Number, default: 60 },
    autoStart: { type: Boolean, default: false },
    url: String,
    labels: Object
  }

  static targets = ["display"]

  connect() {
    this.remaining = this.secondsValue
    this.updateDisplay()

    if (this.autoStartValue) {
      this.start()
    }
  }

  start() {
    this.timer = setInterval(() => {
      this.remaining--
      this.updateDisplay()

      if (this.remaining <= 0) {
        this.stop()
        if (this.hasUrlValue) {
          window.location.href = this.urlValue
        }
      }
    }, 1000)
  }

  stop() {
    clearInterval(this.timer)
  }

  updateDisplay() {
    const minutes = Math.floor(this.remaining / 60)
    const seconds = this.remaining % 60
    this.displayTarget.textContent =
      `${minutes.toString().padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`
  }

  disconnect() {
    this.stop()
  }
}
```

```html
<div data-controller="countdown"
     data-countdown-seconds-value="300"
     data-countdown-auto-start-value="true"
     data-countdown-url-value="/quiz/timeup">
  <p>Tiempo restante: <span data-countdown-target="display"></span></p>
  <button data-action="countdown#start">Iniciar</button>
  <button data-action="countdown#stop">Pausar</button>
</div>
```

### Callbacks de cambio de value

```javascript
// Se ejecuta automáticamente cuando el value cambia
secondsValueChanged() {
  this.remaining = this.secondsValue
  this.updateDisplay()
}
```

---

## Lifecycle Callbacks

Stimulus proporciona callbacks del ciclo de vida que se ejecutan automáticamente.

```javascript
// app/javascript/controllers/sidebar_controller.js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  // Se ejecuta cuando el controlador se conecta al DOM
  connect() {
    console.log("Sidebar conectado al DOM")
    this.loadPreferences()
  }

  // Se ejecuta cuando el controlador se desconecta del DOM
  disconnect() {
    console.log("Sidebar desconectado")
    this.savePreferences()
  }

  // Callbacks para targets (connect/disconnect por cada target)
  itemTargetConnected(element) {
    console.log("Nuevo item añadido:", element)
    this.updateItemCount()
  }

  itemTargetDisconnected(element) {
    console.log("Item eliminado:", element)
    this.updateItemCount()
  }

  loadPreferences() {
    const collapsed = localStorage.getItem("sidebar_collapsed")
    if (collapsed === "true") {
      this.element.classList.add("collapsed")
    }
  }

  savePreferences() {
    const isCollapsed = this.element.classList.contains("collapsed")
    localStorage.setItem("sidebar_collapsed", isCollapsed)
  }
}
```

---

## Outlets

Los outlets permiten que un controlador haga referencia a otro controlador en el DOM, facilitando la comunicación entre controladores.

```javascript
// app/javascript/controllers/search_controller.js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static outlets = ["results"]
  static targets = ["input"]

  filter() {
    const query = this.inputTarget.value.toLowerCase()

    // Acceder al controlador "results" conectado
    if (this.hasResultsOutlet) {
      this.resultsOutlet.filterBy(query)
    }
  }
}
```

```javascript
// app/javascript/controllers/results_controller.js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static targets = ["item"]

  filterBy(query) {
    this.itemTargets.forEach(item => {
      const text = item.textContent.toLowerCase()
      item.style.display = text.includes(query) ? "" : "none"
    })
  }
}
```

```html
<div data-controller="search"
     data-search-results-outlet="#results-panel">
  <input data-search-target="input"
         data-action="input->search#filter"
         placeholder="Buscar lección...">
</div>

<div id="results-panel" data-controller="results">
  <div data-results-target="item">Lección 1: Introducción a Ruby</div>
  <div data-results-target="item">Lección 2: Variables y tipos</div>
  <div data-results-target="item">Lección 3: Estructuras de control</div>
</div>
```

### Propiedades de outlets

```javascript
this.resultsOutlet       // Primera instancia del controlador conectado
this.resultsOutlets      // Array de todas las instancias
this.hasResultsOutlet    // Boolean
```

---

## CSS Classes

Stimulus permite declarar clases CSS como configuración, evitando hardcodear nombres de clases en JavaScript.

```javascript
// app/javascript/controllers/toggle_controller.js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static classes = ["active", "hidden"]
  static targets = ["content"]

  toggle() {
    this.contentTarget.classList.toggle(this.activeClass)
    this.contentTarget.classList.toggle(this.hiddenClass)
  }
}
```

```html
<div data-controller="toggle"
     data-toggle-active-class="bg-blue-500 text-white"
     data-toggle-hidden-class="hidden">
  <button data-action="toggle#toggle">Mostrar/Ocultar</button>
  <div data-toggle-target="content" class="hidden">
    <p>Contenido que se muestra y oculta</p>
  </div>
</div>
```

---

## Ejemplos del Mundo Real

### Menú desplegable (Dropdown)

```javascript
// app/javascript/controllers/dropdown_controller.js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static targets = ["menu"]
  static classes = ["open"]

  toggle() {
    this.menuTarget.classList.toggle(this.openClass)
  }

  close(event) {
    if (!this.element.contains(event.target)) {
      this.menuTarget.classList.remove(this.openClass)
    }
  }

  connect() {
    this.boundClose = this.close.bind(this)
    document.addEventListener("click", this.boundClose)
  }

  disconnect() {
    document.removeEventListener("click", this.boundClose)
  }
}
```

```html
<div data-controller="dropdown" data-dropdown-open-class="show">
  <button data-action="dropdown#toggle">Mi Perfil ▾</button>
  <ul data-dropdown-target="menu" class="dropdown-menu">
    <li><a href="/profile">Ver perfil</a></li>
    <li><a href="/settings">Configuración</a></li>
    <li><a href="/logout">Cerrar sesión</a></li>
  </ul>
</div>
```

### Autoguardado de formularios

```javascript
// app/javascript/controllers/autosave_controller.js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
  static values = { delay: { type: Number, default: 1000 }, url: String }
  static targets = ["status"]

  connect() {
    this.timeout = null
  }

  save() {
    clearTimeout(this.timeout)
    this.statusTarget.textContent = "Guardando..."

    this.timeout = setTimeout(() => {
      const formData = new FormData(this.element)

      fetch(this.urlValue, {
        method: "PATCH",
        body: formData,
        headers: {
          "X-CSRF-Token": document.querySelector("[name='csrf-token']").content,
          "Accept": "text/vnd.turbo-stream.html"
        }
      }).then(response => {
        if (response.ok) {
          this.statusTarget.textContent = "Guardado ✓"
        } else {
          this.statusTarget.textContent = "Error al guardar"
        }
      })
    }, this.delayValue)
  }

  disconnect() {
    clearTimeout(this.timeout)
  }
}
```

```html
<form data-controller="autosave"
      data-autosave-url-value="<%= course_path(@course) %>"
      data-autosave-delay-value="1500">
  <textarea name="course[description]"
            data-action="input->autosave#save"><%= @course.description %></textarea>
  <span data-autosave-target="status" class="text-sm text-gray-500"></span>
</form>
```

---

## Consejos Prácticos

1. **Un controlador = una responsabilidad**: mantén los controladores pequeños y enfocados.
2. **Usa values para configuración**: no hardcodees URLs, tiempos o textos en JavaScript.
3. **Usa targets en lugar de querySelector**: son más declarativos y se actualizan automáticamente.
4. **Limpia en `disconnect()`**: remueve event listeners, clearInterval/clearTimeout.
5. **Combina con Turbo**: Stimulus maneja el comportamiento, Turbo maneja las actualizaciones del DOM.
6. **Usa `data-action` con modificadores**: `:prevent`, `:stop`, `:once` evitan código boilerplate.

```bash
# Actualizar el manifiesto después de crear controladores manualmente
bin/rails stimulus:manifest:update
```

---

## Resumen

Stimulus es el complemento perfecto para Turbo en el stack Hotwire:

- **Controladores** se conectan al DOM mediante `data-controller` y se instancian automáticamente.
- **Targets** proporcionan referencias tipo query selector declarativas (`data-<ctrl>-target`).
- **Values** almacenan datos tipados en atributos HTML con callbacks de cambio.
- **Actions** conectan eventos del DOM con métodos del controlador (`data-action`).
- **Lifecycle callbacks** (`connect`, `disconnect`) gestionan el ciclo de vida del controlador.
- **Outlets** permiten comunicación entre controladores.
- **CSS Classes** evitan hardcodear clases en JavaScript.

Con Stimulus escribes JavaScript mínimo, declarativo y conectado al HTML que el servidor ya genera. Es la pieza que completa la experiencia Hotwire en Rails 8.
