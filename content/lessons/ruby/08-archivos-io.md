# Archivos, I/O y Serialización

Ruby facilita la lectura, escritura y manipulación de archivos con una API intuitiva.

---

## Lectura de archivos

```ruby
# Leer todo el contenido
contenido = File.read("datos.txt")
puts contenido

# Leer línea por línea (eficiente en memoria)
File.foreach("datos.txt") do |linea|
  puts linea.chomp
end

# Leer como array de líneas
lineas = File.readlines("datos.txt", chomp: true)
puts lineas.length

# Leer con encoding
contenido = File.read("datos.txt", encoding: "UTF-8")

# Con bloque (cierra automáticamente)
File.open("datos.txt", "r") do |file|
  while linea = file.gets
    puts linea.chomp
  end
end
```

---

## Escritura de archivos

```ruby
# Escribir (sobrescribe)
File.write("salida.txt", "Hola Mundo\n")

# Append
File.write("log.txt", "Nueva entrada\n", mode: "a")

# Con bloque
File.open("salida.txt", "w") do |file|
  file.puts "Línea 1"       # con newline
  file.print "Línea 2"      # sin newline
  file.write "\nLínea 3\n"  # raw
end

# Modos de apertura
# "r"  — solo lectura (default)
# "w"  — escritura (sobrescribe)
# "a"  — append
# "r+" — lectura + escritura
# "w+" — lectura + escritura (sobrescribe)
# "b"  — binario (combinable: "rb", "wb")
```

---

## Operaciones con archivos y directorios

```ruby
# Información del archivo
puts File.exist?("datos.txt")          # true/false
puts File.size("datos.txt")            # bytes
puts File.extname("foto.jpg")          # ".jpg"
puts File.basename("/path/to/file.rb") # "file.rb"
puts File.dirname("/path/to/file.rb")  # "/path/to"
puts File.expand_path("~/docs")        # "/home/user/docs"

# Tipo
puts File.file?("datos.txt")           # true
puts File.directory?("/tmp")           # true
puts File.symlink?("link.txt")         # true/false

# Timestamps
puts File.mtime("datos.txt")     # última modificación
puts File.ctime("datos.txt")     # creación

# Manipulación
File.rename("viejo.txt", "nuevo.txt")
File.delete("temporal.txt")
FileUtils.cp("origen.txt", "destino.txt")
FileUtils.mv("origen.txt", "destino.txt")
```

### Directorios

```ruby
require 'fileutils'

# Listar contenido
Dir.entries(".")                    # [".", "..", "file1.rb", ...]
Dir.glob("*.rb")                    # ["main.rb", "test.rb"]
Dir.glob("**/*.rb")                 # recursivo
Dir.glob("app/**/*.{rb,yml}")       # múltiples extensiones

# Crear y eliminar
Dir.mkdir("nueva_carpeta")
FileUtils.mkdir_p("a/b/c")         # crea intermedios
FileUtils.rm_rf("carpeta")         # elimina recursivo

# Directorio actual
puts Dir.pwd
Dir.chdir("/tmp") do
  puts Dir.pwd    # "/tmp"
end
puts Dir.pwd      # directorio original

# Directorio temporal
require 'tmpdir'
Dir.mktmpdir do |dir|
  File.write("#{dir}/temp.txt", "datos temporales")
  # el directorio se elimina automáticamente al salir del bloque
end
```

---

## Pathname (API moderna)

```ruby
require 'pathname'

path = Pathname.new("/home/user/project/src/main.rb")

puts path.basename     # "main.rb"
puts path.dirname      # "/home/user/project/src"
puts path.extname      # ".rb"
puts path.parent       # "/home/user/project/src"
puts path.exist?       # true/false

# Construir paths
base = Pathname.new("/home/user")
archivo = base / "project" / "src" / "main.rb"
puts archivo   # "/home/user/project/src/main.rb"

# Listas
Pathname.glob("/home/user/**/*.rb").each do |p|
  puts p
end
```

---

## JSON

```ruby
require 'json'

# Ruby Hash → JSON string
datos = { nombre: "Ana", edad: 28, hobbies: ["leer", "programar"] }
json_string = JSON.generate(datos)
json_pretty = JSON.pretty_generate(datos)

puts json_string
# {"nombre":"Ana","edad":28,"hobbies":["leer","programar"]}

# JSON string → Ruby Hash
parsed = JSON.parse(json_string)
puts parsed["nombre"]    # "Ana" (keys como strings)

# Con symbolize_names
parsed = JSON.parse(json_string, symbolize_names: true)
puts parsed[:nombre]     # "Ana" (keys como symbols)

# Leer/escribir JSON a archivo
File.write("datos.json", JSON.pretty_generate(datos))
datos_leidos = JSON.parse(File.read("datos.json"), symbolize_names: true)
```

---

## YAML

```ruby
require 'yaml'

# Ruby → YAML
config = {
  database: {
    host: "localhost",
    port: 5432,
    name: "myapp"
  },
  redis: {
    url: "redis://localhost:6379"
  }
}

yaml_string = config.to_yaml
File.write("config.yml", yaml_string)

# YAML → Ruby
loaded = YAML.safe_load_file("config.yml", permitted_classes: [Symbol])
puts loaded["database"]["host"]   # "localhost"
```

---

## CSV

```ruby
require 'csv'

# Escribir CSV
CSV.open("usuarios.csv", "w") do |csv|
  csv << ["nombre", "email", "edad"]
  csv << ["Ana", "ana@test.com", 28]
  csv << ["Bob", "bob@test.com", 35]
end

# Leer CSV
CSV.foreach("usuarios.csv", headers: true) do |row|
  puts "#{row['nombre']} - #{row['email']}"
end

# Parsear string CSV
datos = CSV.parse("a,b,c\n1,2,3\n4,5,6", headers: true)
datos.each { |row| puts row["a"] }
```

---

## StringIO (I/O en memoria)

```ruby
require 'stringio'

io = StringIO.new
io.puts "Línea 1"
io.puts "Línea 2"

io.rewind
puts io.read   # "Línea 1\nLínea 2\n"

# Útil para testing
def generar_reporte(output = $stdout)
  output.puts "Reporte"
  output.puts "======="
end

# En producción
generar_reporte

# En tests
buffer = StringIO.new
generar_reporte(buffer)
buffer.rewind
assert_equal "Reporte\n=======\n", buffer.read
```

---

## Resumen

| Operación | Método |
|---|---|
| Leer archivo | `File.read`, `File.foreach` |
| Escribir archivo | `File.write`, `File.open` |
| Verificar existencia | `File.exist?` |
| Listar directorio | `Dir.glob`, `Dir.entries` |
| Path operations | `Pathname` |
| JSON | `JSON.parse`, `JSON.generate` |
| YAML | `YAML.safe_load_file` |
| CSV | `CSV.foreach`, `CSV.open` |
