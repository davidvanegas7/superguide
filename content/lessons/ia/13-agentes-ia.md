# Agentes de IA

Los agentes de IA son sistemas que usan LLMs para razonar, planificar y ejecutar acciones autónomamente. Representan la evolución de chatbots simples a asistentes que interactúan con el mundo real.

## ¿Qué es un agente de IA?

Un agente es un LLM con acceso a **herramientas** (tools) que puede:
1. Entender una tarea compleja
2. Planificar pasos para resolverla
3. Ejecutar acciones (buscar, calcular, escribir archivos)
4. Observar resultados y ajustar su plan
5. Iterar hasta completar la tarea

```
┌─────────────────────────────────────────────────┐
│                AGENTE DE IA                      │
│                                                  │
│  Usuario: "Busca los 3 restaurantes mejor       │
│           valorados cerca de mí y reserva uno"   │
│                                                  │
│  ┌──────────┐      ┌──────────────┐             │
│  │   LLM    │─────▶│ Planificar   │             │
│  │ (cerebro)│◀─────│ y Razonar    │             │
│  └──────────┘      └──────────────┘             │
│       │                                          │
│       ▼                                          │
│  ┌──────────────────────────────────┐           │
│  │         HERRAMIENTAS             │           │
│  │  🔍 Buscar restaurantes          │           │
│  │  📍 Obtener ubicación            │           │
│  │  ⭐ Leer reseñas                │           │
│  │  📅 Hacer reservación            │           │
│  │  📧 Enviar confirmación          │           │
│  └──────────────────────────────────┘           │
└─────────────────────────────────────────────────┘
```

## Function Calling (Tool Use)

La base de los agentes: el LLM puede invocar funciones definidas:

```python
from openai import OpenAI
import json

client = OpenAI()

# Definir herramientas disponibles
tools = [
    {
        "type": "function",
        "function": {
            "name": "get_weather",
            "description": "Obtiene el clima actual de una ciudad",
            "parameters": {
                "type": "object",
                "properties": {
                    "city": {"type": "string", "description": "Nombre de la ciudad"},
                    "unit": {"type": "string", "enum": ["celsius", "fahrenheit"]}
                },
                "required": ["city"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "search_web",
            "description": "Busca información actualizada en internet",
            "parameters": {
                "type": "object",
                "properties": {
                    "query": {"type": "string", "description": "Término de búsqueda"}
                },
                "required": ["query"]
            }
        }
    }
]

# El LLM decide cuándo y qué herramienta usar
response = client.chat.completions.create(
    model="gpt-4o",
    messages=[{"role": "user", "content": "¿Qué clima hace en Madrid?"}],
    tools=tools,
    tool_choice="auto",  # El modelo decide
)

# Procesar la llamada a herramienta
tool_call = response.choices[0].message.tool_calls[0]
print(f"Función: {tool_call.function.name}")
print(f"Args: {tool_call.function.arguments}")
# Función: get_weather
# Args: {"city": "Madrid", "unit": "celsius"}
```

### Loop de agente completo

```python
def run_agent(user_message, tools, available_functions):
    messages = [{"role": "user", "content": user_message}]

    while True:
        response = client.chat.completions.create(
            model="gpt-4o",
            messages=messages,
            tools=tools,
        )

        msg = response.choices[0].message
        messages.append(msg)

        # Si no hay tool calls, el agente terminó
        if not msg.tool_calls:
            return msg.content

        # Ejecutar cada herramienta solicitada
        for tool_call in msg.tool_calls:
            fn_name = tool_call.function.name
            fn_args = json.loads(tool_call.function.arguments)

            # Ejecutar la función real
            result = available_functions[fn_name](**fn_args)

            # Devolver resultado al LLM
            messages.append({
                "role": "tool",
                "tool_call_id": tool_call.id,
                "content": json.dumps(result),
            })

# Funciones reales
def get_weather(city, unit="celsius"):
    # En producción: llamar a una API de clima
    return {"city": city, "temp": 22, "unit": unit, "condition": "soleado"}

available_functions = {"get_weather": get_weather}

answer = run_agent("¿Necesito paraguas en Madrid hoy?", tools, available_functions)
print(answer)
```

## MCP: Model Context Protocol (Anthropic, 2024-2025)

MCP es un protocolo abierto que estandariza cómo los LLMs se conectan con herramientas externas. Es como un "USB para IA":

```python
# MCP permite conectar herramientas de forma estándar
# En lugar de definir tools manualmente, MCP las descubre automáticamente

# Ejemplo conceptual de MCP server
class GitHubMCPServer:
    """Servidor MCP que expone operaciones de GitHub."""

    def list_tools(self):
        return [
            {"name": "search_repos", "description": "Buscar repositorios"},
            {"name": "create_issue", "description": "Crear un issue"},
            {"name": "read_file", "description": "Leer archivo del repo"},
        ]

    def call_tool(self, name, arguments):
        if name == "search_repos":
            return github_api.search(arguments["query"])
        elif name == "create_issue":
            return github_api.create_issue(**arguments)
```

Beneficios de MCP:
- **Estándar abierto**: funciona con cualquier LLM
- **Descubrimiento automático**: las herramientas se registran dinámicamente
- **Seguridad**: permisos granulares por herramienta
- **Ecosistema**: servidores MCP para GitHub, Slack, bases de datos, etc.

## Frameworks de agentes

### LangChain

```python
from langchain.agents import create_tool_calling_agent, AgentExecutor
from langchain_openai import ChatOpenAI
from langchain.tools import tool

@tool
def calculate(expression: str) -> str:
    """Calcula una expresión matemática."""
    return str(eval(expression))

@tool
def search(query: str) -> str:
    """Busca información en internet."""
    return f"Resultado para: {query}"

llm = ChatOpenAI(model="gpt-4o")
tools = [calculate, search]

agent = create_tool_calling_agent(llm, tools, prompt_template)
executor = AgentExecutor(agent=agent, tools=tools, verbose=True)

result = executor.invoke({"input": "¿Cuánto es 15% de 340 dólares?"})
```

### CrewAI: Equipos de agentes

```python
from crewai import Agent, Task, Crew

researcher = Agent(
    role="Investigador",
    goal="Encontrar información actualizada",
    backstory="Eres un investigador experto en tecnología.",
    tools=[search_tool],
)

writer = Agent(
    role="Escritor",
    goal="Escribir artículos claros y concisos",
    backstory="Eres un redactor técnico experimentado.",
)

task1 = Task(description="Investiga las tendencias de IA en 2025", agent=researcher)
task2 = Task(description="Escribe un artículo de 500 palabras", agent=writer)

crew = Crew(agents=[researcher, writer], tasks=[task1, task2])
result = crew.kickoff()
```

## Patrones de agentes

### ReAct (Reasoning + Acting)

```
Pensamiento: Necesito buscar el clima de Madrid
Acción: get_weather("Madrid")
Observación: 22°C, soleado
Pensamiento: Está soleado, no necesita paraguas
Respuesta: No necesitas paraguas, en Madrid está soleado a 22°C.
```

### Plan-and-Execute

```
Plan:
1. Buscar restaurantes italianos en la zona
2. Filtrar por rating > 4.5
3. Verificar disponibilidad para hoy
4. Reservar en el mejor disponible

Ejecutar paso 1: search_restaurants("italian", location)
Ejecutar paso 2: filter_results(results, min_rating=4.5)
...
```

## Resumen

- Los agentes combinan LLMs con herramientas para actuar en el mundo real.
- Function calling/tool use es la base técnica de los agentes.
- MCP estandariza la conexión entre LLMs y herramientas.
- Frameworks como LangChain y CrewAI simplifican la construcción.
- Patrones ReAct y Plan-and-Execute guían el razonamiento del agente.
