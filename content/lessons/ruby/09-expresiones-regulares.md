# Expresiones Regulares en Ruby

Ruby tiene soporte de primera clase para expresiones regulares (Regex), integrado directamente en la sintaxis del lenguaje.

---

## Creación y matching

```ruby
# Literal
patron = /ruby/i    # i = case insensitive

# Con Regexp.new
patron = Regexp.new("ruby", Regexp::IGNORECASE)

# Match
"Hola Ruby" =~ /ruby/i      # 5 (posición del match)
"Hola Python" =~ /ruby/i    # nil (no hay match)

# match method
resultado = "Hola Ruby 3.3".match(/ruby (\d+\.\d+)/i)
puts resultado[0]    # "Ruby 3.3" (match completo)
puts resultado[1]    # "3.3" (primer grupo)

# match? (solo true/false, más eficiente)
"Hola Ruby".match?(/ruby/i)    # true

# === (usado en case/when)
case "user@example.com"
when /\A[\w.]+@gmail\.com\z/
  "Gmail"
when /\A[\w.]+@[\w.]+\z/
  "Email válido"
else
  "No es email"
end
```

---

## Variables especiales

```ruby
"Hola Mundo Ruby" =~ /(\w+) (\w+)/

puts $~       # MatchData: "Hola Mundo"
puts $&       # Match completo: "Hola Mundo"
puts $1       # Grupo 1: "Hola"
puts $2       # Grupo 2: "Mundo"
puts $`       # Antes del match: ""
puts $'       # Después del match: " Ruby"
```

---

## Patrones comunes

```ruby
# Caracteres
/[abc]/          # a, b, o c
/[^abc]/         # cualquier cosa excepto a, b, c
/[a-z]/          # letras minúsculas
/[A-Za-z0-9]/    # alfanumérico

# Clases predefinidas
/\d/    # dígito [0-9]
/\D/    # no dígito
/\w/    # word [a-zA-Z0-9_]
/\W/    # no word
/\s/    # whitespace
/\S/    # no whitespace
/./     # cualquier carácter excepto newline

# Cuantificadores
/a*/     # 0 o más
/a+/     # 1 o más
/a?/     # 0 o 1
/a{3}/   # exactamente 3
/a{2,5}/ # entre 2 y 5
/a{3,}/  # 3 o más

# Lazy (no-greedy)
/".*?"/  # match mínimo entre comillas

# Anclas
/\Ahola/    # inicio de string (\A más seguro que ^)
/mundo\z/   # fin de string (\z más seguro que $)
/\bhola\b/  # word boundary
```

---

## Grupos y capturas

```ruby
# Grupos con nombre
patron = /(?<año>\d{4})-(?<mes>\d{2})-(?<dia>\d{2})/
match = "2024-03-15".match(patron)

puts match[:año]    # "2024"
puts match[:mes]    # "03"
puts match[:dia]    # "15"

# Grupo sin captura
/(?:https?|ftp):\/\//

# Alternación
/gato|perro/     # "gato" o "perro"

# Backreference
/(\w+) \1/       # palabra repetida: "hola hola"

# Lookahead y lookbehind
/\d+(?= euros)/     # dígitos seguidos de " euros" (positive lookahead)
/\d+(?! euros)/     # dígitos NO seguidos de " euros" (negative lookahead)
/(?<=\$)\d+/        # dígitos precedidos de "$" (positive lookbehind)
/(?<!\$)\d+/        # dígitos NO precedidos de "$" (negative lookbehind)
```

---

## Métodos con Regex

### String#scan

```ruby
"uno 2 tres 4 cinco 6".scan(/\d+/)
# ["2", "4", "6"]

"Hola Mundo Ruby".scan(/\w+/)
# ["Hola", "Mundo", "Ruby"]

# Con grupos
"2024-03-15 2024-12-25".scan(/(\d{4})-(\d{2})-(\d{2})/)
# [["2024", "03", "15"], ["2024", "12", "25"]]
```

### String#sub y String#gsub

```ruby
# sub — reemplaza la primera ocurrencia
"Hola Mundo".sub(/mundo/i, "Ruby")     # "Hola Ruby"

# gsub — reemplaza todas
"aaa bbb aaa".gsub(/aaa/, "xxx")       # "xxx bbb xxx"

# Con bloque
"hola mundo".gsub(/\w+/) { |m| m.capitalize }
# "Hola Mundo"

# Con hash
"cat and dog".gsub(/cat|dog/, "cat" => "gato", "dog" => "perro")
# "gato and perro"

# Con backreference
"John Smith".gsub(/(\w+) (\w+)/, '\2, \1')   # "Smith, John"
```

### String#split

```ruby
"uno, dos, tres".split(/,\s*/)     # ["uno", "dos", "tres"]
"camelCaseWord".split(/(?=[A-Z])/) # ["camel", "Case", "Word"]
```

---

## Validaciones prácticas

```ruby
module Validaciones
  # Email
  EMAIL = /\A[\w+\-.]+@[a-z\d\-.]+\.[a-z]+\z/i

  # Teléfono (formato español)
  TELEFONO = /\A(\+34)?[6789]\d{8}\z/

  # URL
  URL = /\Ahttps?:\/\/[\w\-.]+(:\d+)?(\/[\w\-._~:\/?#\[\]@!$&'()*+,;=%]*)?\z/

  # Contraseña fuerte
  PASSWORD = /\A(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}\z/

  # IPv4
  IPV4 = /\A(\d{1,3}\.){3}\d{1,3}\z/

  def self.validar(valor, patron, nombre)
    if valor.match?(patron)
      "✅ #{nombre} válido"
    else
      "❌ #{nombre} inválido"
    end
  end
end

puts Validaciones.validar("user@test.com", Validaciones::EMAIL, "Email")
puts Validaciones.validar("+34612345678", Validaciones::TELEFONO, "Teléfono")
puts Validaciones.validar("Ab1!abcd", Validaciones::PASSWORD, "Password")
```

---

## Flags (modificadores)

```ruby
/patron/i    # IGNORECASE — insensible a mayúsculas
/patron/m    # MULTILINE — . también matchea newlines
/patron/x    # EXTENDED — permite comentarios y whitespace

# Modo extendido para regex complejas
EMAIL_REGEX = /
  \A
  [\w+\-.]+      # usuario
  @               # arroba
  [a-z\d\-.]+     # dominio
  \.              # punto
  [a-z]+          # TLD
  \z
/xi
```

---

## Resumen

| Método | Descripción |
|---|---|
| `=~` | Retorna posición del match o nil |
| `match` | Retorna MatchData |
| `match?` | Solo true/false |
| `scan` | Encuentra todas las ocurrencias |
| `sub` | Reemplaza primera ocurrencia |
| `gsub` | Reemplaza todas las ocurrencias |
| `split` | Divide por patrón |
| `(?<name>...)` | Grupo con nombre |
| `(?=...)` | Positive lookahead |
| `(?<=...)` | Positive lookbehind |
