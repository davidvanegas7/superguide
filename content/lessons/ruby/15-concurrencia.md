# Concurrencia y Paralelismo en Ruby

Ruby ofrece varias herramientas para manejar código concurrente y paralelo.

---

## Threads

```ruby
# Crear un thread
thread = Thread.new do
  5.times do |i|
    puts "Thread: #{i}"
    sleep(0.1)
  end
end

5.times do |i|
  puts "Main: #{i}"
  sleep(0.1)
end

thread.join   # esperar a que termine

# Múltiples threads
threads = 5.times.map do |i|
  Thread.new(i) do |num|
    sleep(rand(0.1..0.5))
    puts "Thread #{num} terminado"
    num * 2   # valor de retorno
  end
end

resultados = threads.map(&:value)   # espera y obtiene valores
puts resultados.inspect             # [0, 2, 4, 6, 8]
```

### Thread safety

```ruby
# ❌ No thread-safe — race condition
counter = 0
threads = 10.times.map do
  Thread.new do
    1000.times { counter += 1 }
  end
end
threads.each(&:join)
puts counter   # ¡Puede ser < 10000!

# ✅ Thread-safe con Mutex
mutex = Mutex.new
counter = 0
threads = 10.times.map do
  Thread.new do
    1000.times do
      mutex.synchronize { counter += 1 }
    end
  end
end
threads.each(&:join)
puts counter   # Siempre 10000
```

### Thread-safe collections

```ruby
require 'thread'

# Queue thread-safe
queue = Queue.new

# Productor
producer = Thread.new do
  10.times do |i|
    queue.push("Item #{i}")
    sleep(0.1)
  end
  queue.push(:done)
end

# Consumidor
consumer = Thread.new do
  loop do
    item = queue.pop
    break if item == :done
    puts "Procesando: #{item}"
  end
end

producer.join
consumer.join

# SizedQueue — con límite
buffer = SizedQueue.new(5)   # máximo 5 elementos
```

---

## Fibers (coroutines)

```ruby
# Fibers — concurrencia cooperativa (no preemptiva)
fiber = Fiber.new do
  puts "Paso 1"
  Fiber.yield
  puts "Paso 2"
  Fiber.yield
  puts "Paso 3"
end

fiber.resume   # "Paso 1"
puts "Entre pasos"
fiber.resume   # "Paso 2"
fiber.resume   # "Paso 3"

# Fiber como generador
def fibonacci
  Fiber.new do
    a, b = 0, 1
    loop do
      Fiber.yield a
      a, b = b, a + b
    end
  end
end

fib = fibonacci
10.times { puts fib.resume }   # 0, 1, 1, 2, 3, 5, 8, 13, 21, 34
```

---

## Ractor (Ruby 3.0+) — Paralelismo real

```ruby
# Ractors ejecutan en paralelo (sin GVL)
r1 = Ractor.new do
  # Calcula algo pesado
  (1..10_000_000).sum
end

r2 = Ractor.new do
  (10_000_001..20_000_000).sum
end

total = r1.take + r2.take
puts total

# Comunicación entre Ractors
pipe = Ractor.new do
  loop do
    msg = Ractor.receive
    Ractor.yield msg.upcase
  end
end

pipe.send("hola")
puts pipe.take    # "HOLA"

# Pool de workers
workers = 4.times.map do
  Ractor.new do
    loop do
      n = Ractor.receive
      Ractor.yield [n, n ** 2]
    end
  end
end

(1..8).each_with_index do |n, i|
  workers[i % 4].send(n)
end

8.times do
  _, (n, resultado) = Ractor.select(*workers)
  puts "#{n}² = #{resultado}"
end
```

---

## Async (gem) — I/O concurrente moderno

```bash
gem install async
```

```ruby
require 'async'

# Tareas concurrentes con async
Async do |task|
  # Ejecutar en paralelo
  t1 = task.async do
    sleep(1)
    "Resultado 1"
  end

  t2 = task.async do
    sleep(1)
    "Resultado 2"
  end

  puts t1.wait   # "Resultado 1"
  puts t2.wait   # "Resultado 2"
  # Total: ~1 segundo (no 2)
end

# HTTP concurrente con async
require 'async'
require 'async/http/internet'

Async do
  internet = Async::HTTP::Internet.new

  urls = [
    "https://httpbin.org/delay/1",
    "https://httpbin.org/delay/1",
    "https://httpbin.org/delay/1"
  ]

  tasks = urls.map do |url|
    Async do
      response = internet.get(url)
      response.read
    end
  end

  results = tasks.map(&:wait)
  puts "#{results.length} respuestas recibidas"
ensure
  internet&.close
end
```

---

## Process — fork (Unix)

```ruby
# fork crea un proceso hijo (copia del padre)
pid = fork do
  puts "Hijo: PID #{Process.pid}"
  sleep(2)
  puts "Hijo terminado"
end

puts "Padre: PID #{Process.pid}, hijo: #{pid}"
Process.wait(pid)   # esperar al hijo
puts "Padre: hijo terminado"

# Parallel processing con fork
def parallel_map(array, &block)
  read_pipes = array.map do |item|
    r, w = IO.pipe

    fork do
      r.close
      resultado = block.call(item)
      Marshal.dump(resultado, w)
      w.close
    end

    w.close
    r
  end

  read_pipes.map do |r|
    resultado = Marshal.load(r)
    r.close
    resultado
  end
ensure
  Process.waitall
end

# Uso
resultados = parallel_map([1, 2, 3, 4]) { |n| n ** 2 }
puts resultados.inspect   # [1, 4, 9, 16]
```

---

## GVL (Global VM Lock)

```ruby
# Ruby (CRuby/MRI) tiene un GVL que previene
# que múltiples threads ejecuten Ruby code en paralelo.

# Threads SÍ son útiles para I/O:
# - Peticiones HTTP
# - Lectura de archivos
# - Consultas a base de datos
# - Sleep / esperas

# Para CPU-bound, usar:
# - Ractors (Ruby 3.0+)
# - fork/Process
# - Gemas como Parallel
```

---

## Resumen

| Mecanismo | Paralelismo | Uso ideal |
|---|---|---|
| Threads | Concurrente (GVL) | I/O bound |
| Fibers | Cooperativo | Generadores, coroutines |
| Ractors | Paralelo real | CPU bound (Ruby 3+) |
| fork/Process | Paralelo real | CPU bound (Unix) |
| Async gem | Concurrente | I/O moderno |
| Mutex | N/A | Proteger datos compartidos |
