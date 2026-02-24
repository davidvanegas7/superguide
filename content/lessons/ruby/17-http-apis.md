# HTTP, APIs y Networking en Ruby

Ruby tiene herramientas potentes para trabajar con HTTP y construir/consumir APIs.

---

## Net::HTTP (stdlib)

```ruby
require 'net/http'
require 'json'
require 'uri'

# GET simple
uri = URI('https://jsonplaceholder.typicode.com/posts/1')
response = Net::HTTP.get_response(uri)
puts response.code          # "200"
puts response.content_type  # "application/json"

data = JSON.parse(response.body)
puts data['title']

# GET con parámetros
uri = URI('https://api.example.com/search')
uri.query = URI.encode_www_form(q: 'ruby', page: 1)
response = Net::HTTP.get_response(uri)

# POST con JSON
uri = URI('https://jsonplaceholder.typicode.com/posts')
http = Net::HTTP.new(uri.host, uri.port)
http.use_ssl = true

request = Net::HTTP::Post.new(uri.path)
request['Content-Type'] = 'application/json'
request['Authorization'] = 'Bearer token123'
request.body = { title: 'Hola', body: 'Contenido', userId: 1 }.to_json

response = http.request(request)
puts JSON.parse(response.body)

# PUT
request = Net::HTTP::Put.new(uri.path + '/1')
request['Content-Type'] = 'application/json'
request.body = { title: 'Actualizado' }.to_json

# DELETE
request = Net::HTTP::Delete.new(uri.path + '/1')
```

---

## HTTParty (gema popular)

```bash
gem install httparty
```

```ruby
require 'httparty'

# GET
response = HTTParty.get('https://api.github.com/users/octocat',
  headers: { 'User-Agent' => 'Ruby App' }
)
puts response['name']
puts response.code
puts response.headers['content-type']

# POST
response = HTTParty.post('https://jsonplaceholder.typicode.com/posts',
  body: { title: 'Hola', body: 'Contenido' }.to_json,
  headers: { 'Content-Type' => 'application/json' }
)

# Clase wrapper
class GitHubAPI
  include HTTParty
  base_uri 'https://api.github.com'
  headers 'User-Agent' => 'Ruby App'

  def user(username)
    self.class.get("/users/#{username}")
  end

  def repos(username)
    self.class.get("/users/#{username}/repos",
      query: { sort: 'updated', per_page: 5 }
    )
  end
end

api = GitHubAPI.new
user = api.user('octocat')
puts "#{user['name']} - #{user['public_repos']} repos"
```

---

## Faraday (gema flexible)

```ruby
require 'faraday'
require 'faraday/net_http'

# Cliente configurado
conn = Faraday.new(url: 'https://api.example.com') do |f|
  f.request :json                    # encode body como JSON
  f.response :json                   # parse response como JSON
  f.response :raise_error            # lanza en 4xx/5xx
  f.adapter Faraday.default_adapter
  f.headers['Authorization'] = 'Bearer token123'
end

# GET
response = conn.get('/users', page: 1, per_page: 10)
puts response.body   # ya parseado como Hash

# POST
response = conn.post('/users') do |req|
  req.body = { name: 'Ana', email: 'ana@test.com' }
end

# Middleware custom
class LoggingMiddleware < Faraday::Middleware
  def call(env)
    puts ">> #{env.method.upcase} #{env.url}"
    response = @app.call(env)
    puts "<< #{response.status}"
    response
  end
end
```

---

## Servidor HTTP con WEBrick

```ruby
require 'webrick'
require 'json'

server = WEBrick::HTTPServer.new(Port: 8080)

server.mount_proc '/' do |req, res|
  res.content_type = 'application/json'
  res.body = { message: 'Hola Mundo', time: Time.now }.to_json
end

server.mount_proc '/users' do |req, res|
  case req.request_method
  when 'GET'
    res.content_type = 'application/json'
    res.body = [{ id: 1, name: 'Ana' }].to_json
  when 'POST'
    body = JSON.parse(req.body)
    res.status = 201
    res.body = body.merge('id' => 2).to_json
  end
end

trap('INT') { server.shutdown }
server.start
```

---

## Sinatra (micro-framework)

```bash
gem install sinatra sinatra-contrib
```

```ruby
require 'sinatra'
require 'sinatra/json'

# Rutas
get '/' do
  json message: 'Hola Mundo'
end

get '/users/:id' do
  id = params[:id]
  json id: id, name: "User #{id}"
end

post '/users' do
  body = JSON.parse(request.body.read)
  status 201
  json body.merge('id' => rand(1000))
end

put '/users/:id' do
  body = JSON.parse(request.body.read)
  json body.merge('id' => params[:id])
end

delete '/users/:id' do
  status 204
end

# Middleware
before do
  content_type :json
  # Autenticación
  unless request.path == '/'
    token = request.env['HTTP_AUTHORIZATION']
    halt 401, json(error: 'No autorizado') unless token
  end
end

# Error handling
error 404 do
  json error: 'No encontrado'
end

not_found do
  json error: 'Ruta no encontrada'
end
```

---

## WebSockets

```ruby
require 'faye-websocket'
require 'eventmachine'

# Cliente WebSocket
EM.run do
  ws = Faye::WebSocket::Client.new('wss://echo.websocket.org')

  ws.on :open do
    puts 'Conectado'
    ws.send('Hola WebSocket!')
  end

  ws.on :message do |event|
    puts "Recibido: #{event.data}"
    ws.close
  end

  ws.on :close do
    puts 'Desconectado'
    EM.stop
  end
end
```

---

## TCP/UDP con Sockets

```ruby
require 'socket'

# Servidor TCP
server = TCPServer.new('localhost', 3000)
puts "Servidor escuchando en puerto 3000..."

loop do
  client = server.accept
  request = client.gets
  puts "Recibido: #{request}"

  client.puts "HTTP/1.1 200 OK\r\n"
  client.puts "Content-Type: text/plain\r\n"
  client.puts "\r\n"
  client.puts "Hola desde Ruby!"
  client.close
end

# Cliente TCP
require 'socket'
socket = TCPSocket.new('localhost', 3000)
socket.puts "GET / HTTP/1.1\r\n\r\n"
response = socket.read
puts response
socket.close
```

---

## Resumen

| Herramienta | Tipo | Uso |
|---|---|---|
| `Net::HTTP` | stdlib | HTTP básico |
| `HTTParty` | gema | HTTP simple y elegante |
| `Faraday` | gema | HTTP flexible con middleware |
| `WEBrick` | stdlib | Servidor HTTP básico |
| `Sinatra` | gema | Micro-framework web |
| `Socket` | stdlib | TCP/UDP raw |
| `Faye` | gema | WebSockets |
