# Introducción a Elixir

Elixir es un lenguaje de programación funcional, concurrente y de propósito general que se ejecuta sobre la máquina virtual de Erlang (BEAM). Fue creado por José Valim en 2011 con el objetivo de ofrecer una sintaxis moderna y productiva sin sacrificar la robustez y escalabilidad que caracterizan al ecosistema Erlang.

## ¿Qué es Elixir y por qué aprenderlo?

Elixir combina lo mejor de dos mundos: la productividad de lenguajes modernos como Ruby con la capacidad de construir sistemas distribuidos y tolerantes a fallos de Erlang. Empresas como Discord, Pinterest y WhatsApp utilizan la BEAM VM para manejar millones de conexiones simultáneas.

Las principales características de Elixir son:

- **Funcional**: los datos son inmutables y las funciones son ciudadanos de primera clase.
- **Concurrente**: los procesos ligeros permiten manejar miles de tareas simultáneas.
- **Tolerante a fallos**: el modelo de supervisión permite que los sistemas se recuperen automáticamente.
- **Escalable**: diseñado para distribuirse en múltiples nodos de forma transparente.

## La BEAM VM

La BEAM (Bogdan/Björn's Erlang Abstract Machine) es la máquina virtual que ejecuta el código Elixir. Fue diseñada originalmente para Erlang y ofrece:

- **Procesos ligeros**: cada proceso usa apenas unos pocos kilobytes de memoria.
- **Garbage collection por proceso**: no hay pausas globales de recolección de basura.
- **Preemptive scheduling**: el planificador asegura que ningún proceso monopolice la CPU.
- **Hot code swapping**: permite actualizar código en producción sin detener el sistema.

## Instalación de Elixir

En sistemas basados en Debian/Ubuntu puedes instalar Elixir con:

```elixir
# En Ubuntu/Debian
# sudo apt-get install elixir

# En macOS con Homebrew
# brew install elixir

# Verificar la instalación
# elixir --version
```

Una vez instalado, tendrás disponibles tres herramientas principales: `elixir` (compilador), `iex` (shell interactivo) y `mix` (herramienta de construcción).

## IEx: El Shell Interactivo

IEx (Interactive Elixir) es una herramienta fundamental para experimentar con el lenguaje. Puedes iniciarla escribiendo `iex` en tu terminal:

```elixir
iex> 2 + 3
5

iex> "Hola" <> " " <> "Mundo"
"Hola Mundo"

iex> String.upcase("elixir")
"ELIXIR"

iex> h String.split
# Muestra la documentación de la función
```

IEx incluye el helper `h/1` para consultar documentación directamente desde la consola, lo que facilita enormemente el aprendizaje.

## Mix: Herramienta de Construcción

Mix es la herramienta oficial para crear proyectos, gestionar dependencias, ejecutar tests y mucho más:

```elixir
# Crear un nuevo proyecto
# mix new mi_proyecto

# Estructura generada:
# mi_proyecto/
#   lib/
#     mi_proyecto.ex
#   test/
#     mi_proyecto_test.exs
#     test_helper.exs
#   mix.exs
#   README.md
```

El archivo `mix.exs` es el corazón de cualquier proyecto Elixir, donde se definen las dependencias y la configuración del proyecto.

## Tu Primer Programa

Creemos un módulo sencillo para entender la estructura básica de un programa en Elixir:

```elixir
defmodule Saludo do
  @moduledoc """
  Módulo que proporciona funciones de saludo.
  """

  @doc """
  Saluda a una persona por su nombre.
  """
  def hola(nombre) do
    "¡Hola, #{nombre}! Bienvenido a Elixir."
  end

  def despedida(nombre) do
    "¡Hasta luego, #{nombre}!"
  end
end

# Uso:
IO.puts(Saludo.hola("María"))
# => ¡Hola, María! Bienvenido a Elixir.
```

Observa el uso de `defmodule` para definir un módulo, `def` para funciones públicas y la interpolación de strings con `#{}`.

## Ejecutar Archivos Elixir

Puedes ejecutar archivos Elixir de varias formas:

```elixir
# Ejecutar un script (.exs)
# elixir mi_script.exs

# Compilar un archivo (.ex)
# elixirc mi_modulo.ex

# Ejecutar dentro de un proyecto Mix
# mix run -e "IO.puts(Saludo.hola(\"Mundo\"))"
```

Los archivos `.exs` son scripts que se interpretan directamente, mientras que los `.ex` se compilan a bytecode de la BEAM.

## Resumen

Elixir es un lenguaje funcional moderno que aprovecha décadas de ingeniería de la BEAM VM para ofrecer concurrencia, tolerancia a fallos y escalabilidad. Con herramientas como IEx para experimentación rápida y Mix para gestión de proyectos, el ecosistema proporciona todo lo necesario para desarrollar aplicaciones robustas. En las siguientes lecciones exploraremos en profundidad los tipos de datos, funciones y las características únicas que hacen de Elixir un lenguaje excepcional.
