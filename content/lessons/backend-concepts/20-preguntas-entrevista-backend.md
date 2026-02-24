# Preguntas de Entrevista: Backend

Esta lección recopila las preguntas más frecuentes en entrevistas técnicas para posiciones de backend. No se trata de memorizar respuestas, sino de entender los conceptos para poder explicarlos con tus propias palabras.

---

## POO y Diseño

### ¿Cuáles son los 4 pilares de la POO?

1. **Encapsulamiento**: ocultar el estado interno y exponer solo lo necesario a través de una interfaz pública. Previene modificaciones no controladas.
2. **Herencia**: una clase puede heredar comportamiento de otra, evitando duplicación de código. Se prefiere composición sobre herencia cuando es posible.
3. **Polimorfismo**: diferentes clases pueden responder al mismo mensaje de formas distintas. Permite escribir código genérico que funciona con cualquier implementación.
4. **Abstracción**: modelar solo los aspectos relevantes del mundo real, ocultando la complejidad interna. Se implementa con interfaces y clases abstractas.

### ¿Qué es SOLID?

- **S** — Single Responsibility: cada clase tiene una sola razón para cambiar
- **O** — Open/Closed: abierto para extensión, cerrado para modificación
- **L** — Liskov Substitution: una subclase puede usarse donde se espera la clase base sin romper el programa
- **I** — Interface Segregation: interfaces pequeñas y específicas, no grandes e "todo en uno"
- **D** — Dependency Inversion: depender de abstracciones, no de implementaciones concretas

### Composición vs Herencia — ¿cuándo usar cada una?

**Herencia** ("es un"): úsala cuando existe una relación taxonómica real y quieres reutilizar implementación. Crea acoplamiento fuerte.

**Composición** ("tiene un"): úsala cuando quieres reutilizar comportamiento de forma flexible. Permite cambiar comportamiento en tiempo de ejecución.

```typescript
// ❌ Herencia forzada
class JSONLogger extends ConsoleLogger { ... }

// ✅ Composición
class OrderService {
  constructor(private logger: Logger) {}  // inyectado, intercambiable
}
```

---

## Bases de datos

### ¿Qué es ACID?

- **Atomicidad**: una transacción es todo o nada
- **Consistencia**: la transacción lleva la BD de un estado válido a otro válido
- **Aislamiento**: las transacciones concurrentes no se ven entre sí (según el nivel configurado)
- **Durabilidad**: una vez confirmada, la transacción persiste aunque el sistema falle

### ¿Cuál es la diferencia entre SQL y NoSQL?

| | SQL (Relacional) | NoSQL |
|---|---|---|
| Estructura | Tablas con esquema fijo | Flexible (documentos, grafos, clave-valor...) |
| Relaciones | JOINs, FK | Desnormalización, referencias manuales |
| Transacciones | ACID completo | Eventual consistency (en general) |
| Escala | Vertical (+ hardware) | Horizontal (+ nodos) |
| Ideal para | Datos relacionales, transacciones | Alta escritura, datos no estructurados |

### ¿Qué es el problema N+1?

Ocurre cuando para una lista de N registros, se hace 1 query inicial más N queries adicionales (una por registro) para obtener datos relacionados. Se soluciona con JOIN, eager loading, o el patrón "IN list + group in memory".

### ¿Qué es un índice y cuándo usarlo?

Un índice es una estructura de datos auxiliar (árbol B+ habitualmente) que acelera las búsquedas. Úsalo en columnas que aparecen en `WHERE`, `JOIN ON`, `ORDER BY`. El coste es mayor tiempo en escrituras y espacio en disco.

---

## Arquitectura y patrones

### ¿Qué es REST y cuáles son sus principios?

REST es un estilo arquitectónico con 6 restricciones: stateless, client-server, cacheable, uniform interface, layered system, code on demand (opcional). Una API RESTful usa recursos (URIs) + verbos HTTP semánticamente correctos + códigos de estado apropiados.

### ¿Cuál es la diferencia entre autenticación y autorización?

- **Autenticación** (*¿quién eres?*): verifica la identidad. Ej: login con usuario/contraseña, JWT.
- **Autorización** (*¿qué puedes hacer?*): verifica permisos. Ej: el usuario puede leer pedidos pero no eliminarlos.

### ¿Qué es un JWT y cuáles son sus partes?

Un JSON Web Token tiene 3 partes separadas por puntos:
1. **Header**: algoritmo de firma (ej: `{ "alg": "HS256", "typ": "JWT" }`)
2. **Payload**: claims (datos: sub, exp, role...) — **no cifrado, solo codificado en Base64**
3. **Signature**: HMAC o RSA del header + payload con el secreto

### Monolito vs Microservicios — ¿cuándo elegir cada uno?

**Monolito**: ideal para equipos pequeños, dominios no muy grandes, cuando la velocidad de desarrollo importa más que la escala independiente. Más simple de desplegar, debuggear y mantener.

**Microservicios**: cuando necesitas escalar componentes independientemente, cuando equipos diferentes necesitan desplegar sin coordinarse, cuando el dominio es muy grande. Mayor complejidad operacional.

> Regla práctica: empieza con un monolito bien estructurado. Extrae microservicios cuando tengas un problema concreto que justifique la complejidad.

### ¿Qué es la inyección de dependencias?

Es pasar las dependencias de un objeto desde el exterior en lugar de crearlas internamente. Facilita el testing (puedes inyectar mocks), reduce el acoplamiento y sigue el principio DIP de SOLID.

---

## Performance y escalabilidad

### ¿Qué estrategias de caché conoces?

- **Cache-aside** (lazy loading): la app gestiona el caché manualmente
- **Write-through**: escribe simultáneamente en caché y BD
- **Write-behind**: escribe en caché primero, sincroniza la BD después
- **TTL**: expiración automática
- **Invalidación activa**: borrar la caché al actualizar los datos
- **Cache tags**: agrupar claves para invalidar por grupo

### ¿Qué es un race condition y cómo se previene?

Una race condition ocurre cuando el resultado depende del orden de ejecución no controlado de operaciones concurrentes. Soluciones:
- **UPDATE condicional** (`WHERE ... AND condition = expected_value`)
- **SELECT FOR UPDATE** (bloqueo pesimista)
- **Optimistic locking** (campo `version`)
- **Distributed locks** (Redis SET NX)
- **Idempotencia** (diseñar operaciones que se pueden ejecutar múltiples veces)

---

## Seguridad

### ¿Qué es SQL injection y cómo prevenirlo?

El atacante inyecta SQL malicioso en inputs del usuario. Prevención: **siempre usar parámetros preparados** (placeholders), nunca concatenar strings con datos del usuario.

### ¿Cómo se almacenan correctamente las contraseñas?

Con una función de hash lenta diseñada para contraseñas: **bcrypt**, **argon2** o **scrypt**. Nunca MD5, SHA1 ni SHA256 solos (son rápidos, son vulnerables a ataques de fuerza bruta). El hash incluye el "salt" automáticamente, así que no hay que guardar el salt por separado.

### ¿Qué es CORS?

Cross-Origin Resource Sharing: mecanismo de seguridad del navegador que bloquea peticiones desde un dominio distinto al del servidor. Se habilita selectivamente en el servidor con cabeceras `Access-Control-Allow-Origin` y otras. Los backends deben configurarlo explícitamente.

---

## Testing

### ¿Cuál es la diferencia entre unit test, integration test y e2e test?

- **Unit**: testa una unidad de lógica aislada con dependencias mockeadas. Rápido, muchos.
- **Integration**: testa que varios componentes funcionan juntos (ej: repositorio + BD real). Moderado.
- **E2E**: testa el sistema completo desde el punto de vista del usuario. Lento, pocos.

### ¿Qué es TDD?

Test-Driven Development: escribir primero el test (que falla), luego el código mínimo para que pase, luego refactorizar. Ciclo: **Red → Green → Refactor**. Beneficio: el código siempre es testeable por diseño.

---

## Preguntas de diseño de sistema (System Design)

### ¿Cómo diseñarías un sistema de acortador de URLs?

1. **API**: `POST /shorten` → genera código corto; `GET /:code` → redirige
2. **BD**: tabla `urls (id, code UNIQUE, original_url, user_id, created_at, clicks)`
3. **Generación del código**: hash + colisión detection, o autoincremental + base62
4. **Caché**: Redis para los códigos más accedidos (TTL + invalidación)
5. **Redirección**: 301 (permanente, el browser cachea) o 302 (temporal, siempre llega al server)
6. **Escalado**: múltiples instancias + load balancer; BD replicada para reads

### ¿Cómo implementarías un sistema de notificaciones?

1. **Event bus** para desacoplar el emisor de las notificaciones
2. **Preferencias del usuario**: qué canales acepta (email, push, SMS)
3. **Cola de mensajes** (BullMQ/RabbitMQ) para envío asíncrono y resiliente
4. **Reintentos** con backoff exponencial para fallos transitorios
5. **Deduplicación**: evitar enviar la misma notificación dos veces (idempotency key)
6. **Plantillas** de mensajes versionadas
