# Introducción a JavaScript

JavaScript es el único lenguaje de programación que los navegadores web entienden de forma nativa. Es esencial para crear experiencias interactivas en la web.

## ¿Qué puedes hacer con JavaScript?

- Manipular el contenido de una página (DOM)
- Responder a eventos del usuario (clics, teclado, etc.)
- Hacer peticiones a servidores (AJAX / Fetch)
- Crear aplicaciones completas con frameworks como React, Vue o Angular

## Declaración de variables

En JavaScript moderno (ES6+) usamos `const` y `let`:

```javascript
// const — no puede reasignarse
const nombre = "Carlos";
const PI     = 3.14159;

// let — puede reasignarse
let contador = 0;
contador = 1; // ✅ válido

// var — evítalo (tiene scope raro)
var antiguo = "no recomendado";
```

## Tipos de datos

```javascript
const texto    = "Hola mundo";        // string
const numero   = 42;                  // number
const decimal  = 3.14;               // number (no hay float separado)
const activo   = true;               // boolean
const nada     = null;               // null
const sinValor = undefined;          // undefined
const lista    = [1, 2, 3];          // array
const persona  = { nombre: "Ana" };  // object
```

## Funciones

```javascript
// Función tradicional
function saludar(nombre) {
    return `Hola, ${nombre}!`;
}

// Arrow function (ES6+)
const saludar = (nombre) => `Hola, ${nombre}!`;

// Con múltiples líneas
const sumar = (a, b) => {
    const resultado = a + b;
    return resultado;
};

console.log(saludar("Mundo")); // Hola, Mundo!
console.log(sumar(3, 4));      // 7
```

## Template Literals

Las comillas invertidas permiten interpolar variables:

```javascript
const nombre = "Sofía";
const edad   = 28;

console.log(`Me llamo ${nombre} y tengo ${edad} años.`);
// Me llamo Sofía y tengo 28 años.
```

## Condicionales

```javascript
const hora = 14;

if (hora < 12) {
    console.log("Buenos días");
} else if (hora < 18) {
    console.log("Buenas tardes");
} else {
    console.log("Buenas noches");
}

// Operador ternario
const saludo = hora < 12 ? "Mañana" : "Tarde";
```

## Resumen

- JavaScript es el lenguaje del navegador
- `const` y `let` para declarar variables
- Funciones tradicionales y arrow functions
- Template literals con backticks
