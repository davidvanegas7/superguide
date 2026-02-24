---
title: "Programación Orientada a Objetos: Fundamentos"
slug: "poo-basica"
description: "Aprende los fundamentos de la Programación Orientada a Objetos en Python: clases, objetos, atributos, métodos, propiedades y herencia."
---

# Programación Orientada a Objetos: Fundamentos

La Programación Orientada a Objetos (POO) es un paradigma que organiza el código en torno a **objetos** que combinan datos (atributos) y comportamiento (métodos). Python es un lenguaje multiparadigma, pero ofrece un soporte robusto y elegante para POO. En esta lección aprenderás a crear y usar clases desde cero.

## Clases y Objetos

Una **clase** es un molde o plantilla que define la estructura y comportamiento de un tipo de objeto. Un **objeto** es una instancia concreta de esa clase.

```python
# Definir una clase
class Perro:
    pass  # Clase vacía por ahora

# Crear objetos (instancias)
mi_perro = Perro()
otro_perro = Perro()

print(type(mi_perro))    # <class '__main__.Perro'>
print(mi_perro)           # <__main__.Perro object at 0x...>

# Cada instancia es un objeto independiente
print(mi_perro is otro_perro)  # False
```

## El Método __init__ y self

`__init__` es el **constructor** de la clase. Se ejecuta automáticamente al crear una nueva instancia. `self` es la referencia al objeto actual:

```python
class Persona:
    def __init__(self, nombre, edad):
        # self.atributo = valor → atributos de instancia
        self.nombre = nombre
        self.edad = edad
    
    def presentarse(self):
        return f"Hola, soy {self.nombre} y tengo {self.edad} años"

# Crear instancias
ana = Persona("Ana", 25)
luis = Persona("Luis", 30)

# Acceder a atributos
print(ana.nombre)  # "Ana"
print(luis.edad)   # 30

# Llamar métodos
print(ana.presentarse())   # "Hola, soy Ana y tengo 25 años"
print(luis.presentarse())  # "Hola, soy Luis y tengo 30 años"

# Modificar atributos
ana.edad = 26
print(ana.edad)  # 26
```

### ¿Qué es self?

`self` es simplemente la convención para referirse al objeto actual. Cuando llamas `ana.presentarse()`, Python traduce internamente a `Persona.presentarse(ana)`:

```python
# Estos son equivalentes:
ana.presentarse()           # Forma habitual
Persona.presentarse(ana)    # Lo que Python hace internamente
```

## Atributos de Instancia vs Atributos de Clase

```python
class Empleado:
    # Atributo de CLASE: compartido por todas las instancias
    empresa = "TechCorp"
    cantidad_empleados = 0
    
    def __init__(self, nombre, salario):
        # Atributos de INSTANCIA: únicos para cada objeto
        self.nombre = nombre
        self.salario = salario
        # Modificar atributo de clase
        Empleado.cantidad_empleados += 1
    
    def info(self):
        return f"{self.nombre} trabaja en {self.empresa} (${self.salario:,})"

# Crear empleados
e1 = Empleado("Ana", 50000)
e2 = Empleado("Luis", 60000)

print(e1.info())  # "Ana trabaja en TechCorp ($50,000)"
print(e2.info())  # "Luis trabaja en TechCorp ($60,000)"

# Los atributos de clase se comparten
print(Empleado.cantidad_empleados)  # 2
print(e1.empresa)    # "TechCorp"
print(e2.empresa)    # "TechCorp"

# Si modificas a nivel de clase, afecta a todos
Empleado.empresa = "NewTech"
print(e1.empresa)    # "NewTech"
print(e2.empresa)    # "NewTech"

# Si modificas en una instancia, crea un atributo de instancia (sombra)
e1.empresa = "SoloCorp"
print(e1.empresa)    # "SoloCorp" (atributo de instancia)
print(e2.empresa)    # "NewTech"  (sigue usando el de clase)
```

## Métodos

Las clases pueden tener diferentes tipos de métodos:

```python
class Calculadora:
    # Atributo de clase
    historial = []
    
    def __init__(self, marca="Genérica"):
        self.marca = marca
    
    # Método de instancia: recibe self, opera con la instancia
    def sumar(self, a, b):
        resultado = a + b
        self.historial.append(f"{a} + {b} = {resultado}")
        return resultado
    
    # Método de clase: recibe cls, opera con la clase
    @classmethod
    def limpiar_historial(cls):
        cls.historial.clear()
        print("Historial limpiado")
    
    # Método estático: no recibe ni self ni cls
    @staticmethod
    def es_par(numero):
        return numero % 2 == 0

# Usar los distintos métodos
calc = Calculadora("Casio")

# Método de instancia
print(calc.sumar(3, 5))  # 8

# Método de clase
Calculadora.limpiar_historial()  # "Historial limpiado"

# Método estático
print(Calculadora.es_par(4))  # True
print(calc.es_par(7))         # False (también se puede llamar desde instancia)
```

## Propiedades con @property

Las propiedades permiten definir **getters**, **setters** y **deleters** para controlar el acceso a los atributos:

```python
class CuentaBancaria:
    def __init__(self, titular, saldo_inicial=0):
        self.titular = titular
        self._saldo = saldo_inicial  # Convención: _ indica "protegido"
    
    @property
    def saldo(self):
        """Getter: se ejecuta al leer self.saldo"""
        return self._saldo
    
    @saldo.setter
    def saldo(self, valor):
        """Setter: se ejecuta al asignar self.saldo = x"""
        if valor < 0:
            raise ValueError("El saldo no puede ser negativo")
        self._saldo = valor
    
    def depositar(self, cantidad):
        if cantidad <= 0:
            raise ValueError("La cantidad debe ser positiva")
        self._saldo += cantidad
        return f"Depósito de ${cantidad:,.2f}. Saldo: ${self._saldo:,.2f}"
    
    def retirar(self, cantidad):
        if cantidad > self._saldo:
            raise ValueError("Fondos insuficientes")
        self._saldo -= cantidad
        return f"Retiro de ${cantidad:,.2f}. Saldo: ${self._saldo:,.2f}"

# Uso
cuenta = CuentaBancaria("Ana", 1000)

# El @property permite acceder como atributo, pero ejecuta el getter
print(cuenta.saldo)  # 1000 (llama al getter)

# El setter valida los datos
cuenta.saldo = 5000  # OK
# cuenta.saldo = -100  # ValueError: El saldo no puede ser negativo

print(cuenta.depositar(500))   # "Depósito de $500.00. Saldo: $5,500.00"
print(cuenta.retirar(200))     # "Retiro de $200.00. Saldo: $5,300.00"
```

### Propiedades calculadas

```python
class Rectangulo:
    def __init__(self, ancho, alto):
        self.ancho = ancho
        self.alto = alto
    
    @property
    def area(self):
        """Propiedad calculada: no almacena dato, lo calcula."""
        return self.ancho * self.alto
    
    @property
    def perimetro(self):
        return 2 * (self.ancho + self.alto)
    
    @property
    def es_cuadrado(self):
        return self.ancho == self.alto

rect = Rectangulo(10, 5)
print(rect.area)        # 50 (se accede sin paréntesis)
print(rect.perimetro)   # 30
print(rect.es_cuadrado) # False
```

## Herencia

La herencia permite crear clases nuevas basadas en clases existentes, reutilizando su código:

```python
# Clase base (padre/superclase)
class Animal:
    def __init__(self, nombre, especie):
        self.nombre = nombre
        self.especie = especie
    
    def hablar(self):
        return "..."
    
    def info(self):
        return f"{self.nombre} ({self.especie})"

# Clase derivada (hija/subclase)
class Perro(Animal):
    def __init__(self, nombre, raza):
        # Llamar al constructor de la clase padre
        super().__init__(nombre, especie="Canino")
        self.raza = raza  # Atributo propio de Perro
    
    # Sobreescribir método del padre
    def hablar(self):
        return "¡Guau!"
    
    def buscar(self, objeto):
        return f"{self.nombre} busca el {objeto}"

class Gato(Animal):
    def __init__(self, nombre, es_domestico=True):
        super().__init__(nombre, especie="Felino")
        self.es_domestico = es_domestico
    
    def hablar(self):
        return "¡Miau!"
    
    def ronronear(self):
        return f"{self.nombre} ronronea..."

# Usar las clases
rex = Perro("Rex", "Pastor Alemán")
misi = Gato("Misi")

print(rex.info())      # "Rex (Canino)" → heredado de Animal
print(rex.hablar())    # "¡Guau!" → sobreescrito en Perro
print(rex.buscar("palo"))  # "Rex busca el palo" → propio de Perro
print(rex.raza)        # "Pastor Alemán"

print(misi.info())     # "Misi (Felino)"
print(misi.hablar())   # "¡Miau!"
print(misi.ronronear())# "Misi ronronea..."
```

## super() en Detalle

`super()` permite llamar a métodos de la clase padre, lo que es esencial para extender comportamiento:

```python
class Vehiculo:
    def __init__(self, marca, modelo, año):
        self.marca = marca
        self.modelo = modelo
        self.año = año
        self.velocidad = 0
    
    def acelerar(self, incremento):
        self.velocidad += incremento
        return f"Velocidad: {self.velocidad} km/h"

class Electrico(Vehiculo):
    def __init__(self, marca, modelo, año, bateria_kwh):
        super().__init__(marca, modelo, año)  # Inicializar lo del padre
        self.bateria_kwh = bateria_kwh        # Agregar lo propio
        self.carga = 100  # porcentaje
    
    def acelerar(self, incremento):
        # Extender el método del padre
        if self.carga <= 0:
            return "¡Sin batería!"
        self.carga -= incremento * 0.5
        return super().acelerar(incremento) + f" (Carga: {self.carga:.0f}%)"

tesla = Electrico("Tesla", "Model 3", 2024, 75)
print(tesla.acelerar(20))  # "Velocidad: 20 km/h (Carga: 90%)"
print(tesla.acelerar(30))  # "Velocidad: 50 km/h (Carga: 75%)"
```

## isinstance e issubclass

```python
rex = Perro("Rex", "Labrador")

# isinstance: ¿es una instancia de esta clase (o de sus padres)?
print(isinstance(rex, Perro))    # True
print(isinstance(rex, Animal))   # True (Perro hereda de Animal)
print(isinstance(rex, Gato))     # False

# issubclass: ¿es una subclase?
print(issubclass(Perro, Animal))   # True
print(issubclass(Gato, Animal))    # True
print(issubclass(Perro, Gato))     # False
print(issubclass(Animal, object))  # True (toda clase hereda de object)
```

## Ejercicio Práctico

Crea un sistema de gestión de una biblioteca con las siguientes clases:

1. `Libro`: con atributos `titulo`, `autor`, `isbn`, `disponible` (bool). Métodos: `prestar()`, `devolver()`, `info()`. Usa `@property` para que `disponible` no se pueda modificar directamente.

2. `LibroDigital(Libro)`: hereda de Libro, agrega `formato` (PDF, EPUB) y `tamaño_mb`. Sobreescribe `info()` para incluir el formato.

3. `Biblioteca`: con un atributo `catalogo` (lista de libros). Métodos: `agregar_libro(libro)`, `buscar_por_titulo(titulo)`, `listar_disponibles()`, `prestar_libro(isbn)`.

Ejemplo de uso:

```python
biblio = Biblioteca("Biblioteca Central")
biblio.agregar_libro(Libro("Don Quijote", "Cervantes", "978-0"))
biblio.agregar_libro(LibroDigital("Python Crash Course", "Matthes", "978-1", "PDF", 15.5))

biblio.prestar_libro("978-0")
disponibles = biblio.listar_disponibles()
```

## Resumen

- Una **clase** define la estructura; un **objeto** es una instancia concreta.
- `__init__` es el constructor; `self` referencia al objeto actual.
- Los **atributos de instancia** son únicos por objeto; los **de clase** son compartidos.
- Los métodos pueden ser **de instancia** (`self`), **de clase** (`@classmethod`) o **estáticos** (`@staticmethod`).
- `@property` permite controlar el acceso a atributos con getters, setters y deleters.
- La **herencia** permite reutilizar código: la subclase hereda atributos y métodos del padre.
- `super()` llama a métodos de la clase padre para extender su comportamiento.
- `isinstance()` verifica si un objeto es de un tipo; `issubclass()` verifica herencia entre clases.
