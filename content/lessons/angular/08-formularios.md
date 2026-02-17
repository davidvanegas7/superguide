# Formularios en Angular

Angular ofrece dos enfoques para trabajar con formularios: **Template-driven** y **Reactive Forms**. Ambos tienen sus casos de uso. Los Reactive Forms son más potentes y testeables para formularios complejos.

---

## Template-driven Forms

Son más simples y usan directivas en el HTML. Ideales para formularios pequeños.

```typescript
import { Component } from '@angular/core';
import { FormsModule, NgForm } from '@angular/forms';

@Component({
  selector: 'app-contacto',
  standalone: true,
  imports: [FormsModule],
  template: `
    <form #contactoForm="ngForm" (ngSubmit)="onSubmit(contactoForm)">

      <!-- Campo de texto -->
      <div>
        <label>Nombre *</label>
        <input
          name="nombre"
          [(ngModel)]="datos.nombre"
          required
          minlength="3"
          #nombreCtrl="ngModel">

        <!-- Mensajes de error -->
        @if (nombreCtrl.invalid && nombreCtrl.touched) {
          @if (nombreCtrl.errors?.['required']) {
            <span class="error">El nombre es requerido</span>
          }
          @if (nombreCtrl.errors?.['minlength']) {
            <span class="error">Mínimo 3 caracteres</span>
          }
        }
      </div>

      <!-- Email -->
      <div>
        <label>Email *</label>
        <input
          name="email"
          type="email"
          [(ngModel)]="datos.email"
          required
          email
          #emailCtrl="ngModel">

        @if (emailCtrl.invalid && emailCtrl.touched) {
          <span class="error">Email inválido</span>
        }
      </div>

      <!-- Select -->
      <div>
        <label>País</label>
        <select name="pais" [(ngModel)]="datos.pais">
          <option value="">Selecciona un país</option>
          <option value="mx">México</option>
          <option value="co">Colombia</option>
          <option value="ar">Argentina</option>
        </select>
      </div>

      <!-- Textarea -->
      <div>
        <label>Mensaje *</label>
        <textarea
          name="mensaje"
          [(ngModel)]="datos.mensaje"
          required
          minlength="10"
          rows="4">
        </textarea>
      </div>

      <!-- Checkbox -->
      <div>
        <input type="checkbox" name="aceptaTerminos" [(ngModel)]="datos.aceptaTerminos" required>
        <label>Acepto los términos y condiciones</label>
      </div>

      <!-- Botón (deshabilitado si el form es inválido) -->
      <button type="submit" [disabled]="contactoForm.invalid">Enviar</button>

      <!-- Estado del formulario para debug -->
      <pre>{{ contactoForm.value | json }}</pre>
    </form>
  `
})
export class ContactoComponent {
  datos = {
    nombre: '',
    email: '',
    pais: '',
    mensaje: '',
    aceptaTerminos: false
  };

  onSubmit(form: NgForm): void {
    if (form.valid) {
      console.log('Datos:', form.value);
      // Enviar al servicio
      form.resetForm();  // Limpiar el formulario
    }
  }
}
```

---

## Reactive Forms

Los Reactive Forms se crean **programáticamente en TypeScript**, dando mayor control, validaciones personalizadas y facilidad para testear.

```typescript
import { Component, OnInit, inject } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule, AbstractControl } from '@angular/forms';

@Component({
  selector: 'app-registro',
  standalone: true,
  imports: [ReactiveFormsModule],
  template: `
    <form [formGroup]="form" (ngSubmit)="onSubmit()">

      <!-- Nombre -->
      <div>
        <label>Nombre *</label>
        <input formControlName="nombre">
        @if (f['nombre'].invalid && f['nombre'].touched) {
          @if (f['nombre'].errors?.['required']) {
            <span class="error">Requerido</span>
          }
          @if (f['nombre'].errors?.['minlength']) {
            <span class="error">Mínimo 3 caracteres</span>
          }
        }
      </div>

      <!-- Email -->
      <div>
        <label>Email *</label>
        <input formControlName="email" type="email">
        @if (f['email'].invalid && f['email'].touched) {
          <span class="error">Email inválido</span>
        }
      </div>

      <!-- Contraseña con grupo -->
      <div formGroupName="contrasenas">
        <div>
          <label>Contraseña *</label>
          <input formControlName="password" type="password">
          @if (g['password'].errors?.['pattern'] && g['password'].touched) {
            <span class="error">Mínimo 8 chars, 1 mayúscula, 1 número</span>
          }
        </div>

        <div>
          <label>Confirmar contraseña *</label>
          <input formControlName="confirmPassword" type="password">
          @if (form.get('contrasenas')?.errors?.['noCoinciden'] && g['confirmPassword'].touched) {
            <span class="error">Las contraseñas no coinciden</span>
          }
        </div>
      </div>

      <!-- Radio buttons -->
      <div>
        <label>Rol</label>
        <label><input type="radio" formControlName="rol" value="viewer"> Viewer</label>
        <label><input type="radio" formControlName="rol" value="editor"> Editor</label>
        <label><input type="radio" formControlName="rol" value="admin"> Admin</label>
      </div>

      <!-- Estado del form -->
      <p>Formulario: {{ form.status }}</p>

      <button type="submit" [disabled]="form.invalid || enviando">
        {{ enviando ? 'Registrando...' : 'Registrarse' }}
      </button>
    </form>
  `
})
export class RegistroComponent implements OnInit {
  private fb = inject(FormBuilder);

  form!: FormGroup;
  enviando = false;

  // Getter para acceder fácilmente a los controles
  get f() { return this.form.controls; }
  get g() { return (this.form.get('contrasenas') as FormGroup).controls; }

  ngOnInit(): void {
    this.form = this.fb.group({
      nombre: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(50)]],
      email: ['', [Validators.required, Validators.email]],
      contrasenas: this.fb.group({
        password: ['', [
          Validators.required,
          Validators.pattern(/^(?=.*[A-Z])(?=.*\d).{8,}$/)
        ]],
        confirmPassword: ['', Validators.required]
      }, { validators: this.validarContrasenas }),
      rol: ['viewer', Validators.required],
      activo: [true]
    });
  }

  // Validador a nivel de grupo
  private validarContrasenas(grupo: AbstractControl) {
    const pass = grupo.get('password')?.value;
    const confirm = grupo.get('confirmPassword')?.value;

    return pass === confirm ? null : { noCoinciden: true };
  }

  onSubmit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();   // Mostrar errores de todos los campos
      return;
    }

    this.enviando = true;
    const datos = this.form.value;
    console.log('Registrando:', datos);

    // Simular llamada al servidor
    setTimeout(() => {
      this.enviando = false;
      this.form.reset({ rol: 'viewer', activo: true });
    }, 2000);
  }
}
```

---

## FormArray — Listas dinámicas

```typescript
import { Component, OnInit, inject } from '@angular/core';
import { FormBuilder, FormArray, Validators, ReactiveFormsModule } from '@angular/forms';

@Component({
  selector: 'app-experiencia',
  standalone: true,
  imports: [ReactiveFormsModule],
  template: `
    <form [formGroup]="form" (ngSubmit)="onSubmit()">
      <h2>Experiencia Laboral</h2>

      <div formArrayName="experiencias">
        @for (exp of experiencias.controls; let i = $index; track i) {
          <div [formGroupName]="i" class="experiencia-item">
            <h4>Experiencia {{ i + 1 }}</h4>
            <input formControlName="empresa" placeholder="Empresa">
            <input formControlName="cargo" placeholder="Cargo">
            <input formControlName="desde" type="date">
            <input formControlName="hasta" type="date">
            <button type="button" (click)="eliminarExperiencia(i)">🗑️ Eliminar</button>
          </div>
        }
      </div>

      <button type="button" (click)="agregarExperiencia()">+ Agregar experiencia</button>
      <button type="submit" [disabled]="form.invalid">Guardar</button>
    </form>
  `
})
export class ExperienciaComponent implements OnInit {
  private fb = inject(FormBuilder);
  form = this.fb.group({ experiencias: this.fb.array([]) });

  get experiencias(): FormArray {
    return this.form.get('experiencias') as FormArray;
  }

  ngOnInit(): void {
    this.agregarExperiencia();  // Empezar con un campo
  }

  agregarExperiencia(): void {
    this.experiencias.push(this.fb.group({
      empresa: ['', Validators.required],
      cargo: ['', Validators.required],
      desde: ['', Validators.required],
      hasta: ['']
    }));
  }

  eliminarExperiencia(indice: number): void {
    this.experiencias.removeAt(indice);
  }

  onSubmit(): void {
    console.log(this.form.value);
  }
}
```

---

## Validadores personalizados

```typescript
import { AbstractControl, ValidationErrors, ValidatorFn } from '@angular/forms';

// Validador de función pura
export function sinEspacios(): ValidatorFn {
  return (control: AbstractControl): ValidationErrors | null => {
    if (!control.value) return null;
    const tieneEspacios = /\s/.test(control.value);
    return tieneEspacios ? { sinEspacios: true } : null;
  };
}

// Validador que recibe parámetros
export function mayorDe(edad: number): ValidatorFn {
  return (control: AbstractControl): ValidationErrors | null => {
    const valor = Number(control.value);
    return valor >= edad ? null : { mayorDe: { requerido: edad, actual: valor } };
  };
}

// Validador asíncrono (por ejemplo, verificar si el email ya existe)
export function emailDisponible(usuariosService: UsuariosService): AsyncValidatorFn {
  return (control: AbstractControl): Observable<ValidationErrors | null> => {
    return timer(500).pipe(   // debounce de 500ms
      switchMap(() => usuariosService.verificarEmail(control.value)),
      map(disponible => disponible ? null : { emailOcupado: true })
    );
  };
}

// Uso en el formulario
this.form = this.fb.group({
  username: ['', [Validators.required, sinEspacios()]],
  edad: ['', [Validators.required, Validators.min(0), mayorDe(18)]],
  email: ['', {
    validators: [Validators.required, Validators.email],
    asyncValidators: [emailDisponible(this.usuariosService)],
    updateOn: 'blur'   // Validar al perder el foco (no al escribir)
  }]
});
```

---

## Escuchar cambios en el formulario

```typescript
ngOnInit(): void {
  this.form = this.fb.group({
    categoria: [''],
    subcategoria: ['']
  });

  // Escuchar cambios en un control específico
  this.form.get('categoria')!.valueChanges.subscribe(categoria => {
    console.log('Categoría cambiada:', categoria);
    // Limpiar y actualizar subcategorías
    this.form.get('subcategoria')!.setValue('');
    this.cargarSubcategorias(categoria);
  });

  // Escuchar cambios en todo el formulario
  this.form.valueChanges.subscribe(valores => {
    console.log('Formulario actualizado:', valores);
    this.guardadoAutomatico(valores);
  });

  // Escuchar cambios de estado
  this.form.statusChanges.subscribe(estado => {
    console.log('Estado del formulario:', estado); // 'VALID', 'INVALID', 'PENDING'
  });
}
```

---

## Comparación: Template-driven vs Reactive

| Aspecto | Template-driven | Reactive |
|---|---|---|
| **Configuración** | En el HTML | En TypeScript |
| **Validación** | Atributos HTML | Funciones TypeScript |
| **Formularios dinámicos** | Difícil | Fácil con FormArray |
| **Testing** | Más complejo | Más simple |
| **Cambios en tiempo real** | `valueChanges` limitado | `valueChanges` Observable completo |
| **Ideal para** | Formularios simples | Formularios complejos |

---

## Gestión de estado del formulario

```typescript
// Métodos útiles del FormGroup y FormControl

// Resetear
this.form.reset();
this.form.reset({ nombre: '', rol: 'viewer' });  // con valores por defecto

// Patch values (actualiza solo los campos indicados)
this.form.patchValue({ nombre: 'Ana', email: 'ana@test.com' });

// Set values (todos los campos)
this.form.setValue({ nombre: 'Ana', email: 'ana@test.com', rol: 'editor' });

// Deshabilitar/habilitar
this.form.get('email')!.disable();
this.form.get('email')!.enable();

// Marcar como tocado (para mostrar errores)
this.form.markAllAsTouched();
this.form.get('nombre')!.markAsTouched();

// Obtener errores
const errores = this.form.get('email')!.errors;

// Estado
console.log(this.form.valid);    // boolean
console.log(this.form.dirty);    // true si el usuario modificó algo
console.log(this.form.touched);  // true si el usuario tocó algún campo
console.log(this.form.pristine); // opuesto a dirty
```
