# Testing con RSpec y Minitest

Ruby tiene una cultura de testing muy fuerte. Los dos frameworks principales son Minitest (incluido en Ruby) y RSpec.

---

## Minitest (incluido en Ruby)

```ruby
# test_calculadora.rb
require 'minitest/autorun'

class Calculadora
  def sumar(a, b) = a + b
  def dividir(a, b)
    raise ArgumentError, "No se puede dividir por cero" if b == 0
    a.to_f / b
  end
end

class TestCalculadora < Minitest::Test
  def setup
    @calc = Calculadora.new
  end

  def test_sumar
    assert_equal 5, @calc.sumar(2, 3)
  end

  def test_sumar_negativos
    assert_equal -1, @calc.sumar(2, -3)
  end

  def test_dividir
    assert_in_delta 3.33, @calc.dividir(10, 3), 0.01
  end

  def test_dividir_por_cero
    assert_raises(ArgumentError) do
      @calc.dividir(10, 0)
    end
  end
end
```

```bash
ruby test_calculadora.rb
```

---

## RSpec — Setup

```bash
gem install rspec
rspec --init
```

```
.rspec
spec/
├── spec_helper.rb
└── calculadora_spec.rb
```

```ruby
# .rspec
--format documentation
--color
--require spec_helper
```

---

## RSpec — Sintaxis describe/it

```ruby
# spec/calculadora_spec.rb
require_relative '../lib/calculadora'

RSpec.describe Calculadora do
  subject(:calc) { described_class.new }

  describe '#sumar' do
    it 'suma dos números positivos' do
      expect(calc.sumar(2, 3)).to eq(5)
    end

    it 'suma números negativos' do
      expect(calc.sumar(-2, -3)).to eq(-5)
    end

    it 'suma con cero' do
      expect(calc.sumar(5, 0)).to eq(5)
    end
  end

  describe '#dividir' do
    it 'divide dos números' do
      expect(calc.dividir(10, 2)).to eq(5.0)
    end

    it 'lanza error al dividir por cero' do
      expect { calc.dividir(10, 0) }.to raise_error(ArgumentError, /cero/)
    end
  end
end
```

```bash
rspec
rspec spec/calculadora_spec.rb
rspec spec/calculadora_spec.rb:10   # línea específica
```

---

## Matchers de RSpec

```ruby
# Igualdad
expect(5).to eq(5)           # ==
expect(5).to eql(5)          # eql?
expect(obj).to equal(obj)    # same object (equal?)
expect(5).to be(5)           # equal?

# Comparación
expect(10).to be > 5
expect(10).to be >= 10
expect(5).to be < 10
expect(5).to be_between(1, 10)

# Truthiness
expect(true).to be_truthy
expect(nil).to be_falsy
expect(nil).to be_nil

# Tipos
expect("hola").to be_a(String)
expect(42).to be_an(Integer)
expect([]).to be_an(Array)

# Strings
expect("Hola Mundo").to include("Mundo")
expect("Hola").to start_with("Ho")
expect("Hola").to end_with("la")
expect("Hola").to match(/\AH\w+\z/)

# Arrays
expect([1, 2, 3]).to include(2)
expect([1, 2, 3]).to contain_exactly(3, 1, 2)  # cualquier orden
expect([1, 2, 3]).to match_array([3, 2, 1])
expect([]).to be_empty
expect([1, 2, 3]).to have_attributes(length: 3)
expect([1, 2, 3]).to all(be_an(Integer))

# Hashes
expect({ a: 1 }).to include(a: 1)
expect({ a: 1, b: 2 }).to include(:a, :b)

# Cambios
expect { array.push(1) }.to change(array, :length).by(1)
expect { x += 5 }.to change { x }.from(0).to(5)

# Excepciones
expect { raise "Error" }.to raise_error(RuntimeError)
expect { raise "Error" }.to raise_error("Error")
expect { raise "Error" }.to raise_error(/Err/)

# Output
expect { puts "hola" }.to output("hola\n").to_stdout

# Compuestos
expect(10).to be > 5 .and be < 20
expect("hola").to start_with("ho").and end_with("la")
```

---

## Contextos y let

```ruby
RSpec.describe User do
  # let — evaluación lazy (se ejecuta al usar la variable)
  let(:user) { User.new(nombre: "Ana", rol: :user) }
  let(:admin) { User.new(nombre: "Bob", rol: :admin) }

  # let! — evaluación eager (se ejecuta siempre)
  let!(:timestamp) { Time.now }

  describe '#admin?' do
    context 'cuando el usuario es admin' do
      subject { admin }

      it { is_expected.to be_admin }
    end

    context 'cuando el usuario no es admin' do
      subject { user }

      it { is_expected.not_to be_admin }
    end
  end

  describe '#nombre_completo' do
    context 'con apellido' do
      let(:user) { User.new(nombre: "Ana", apellido: "García") }

      it 'retorna nombre y apellido' do
        expect(user.nombre_completo).to eq("Ana García")
      end
    end

    context 'sin apellido' do
      it 'retorna solo el nombre' do
        expect(user.nombre_completo).to eq("Ana")
      end
    end
  end
end
```

---

## Hooks (before/after)

```ruby
RSpec.describe Database do
  before(:all) do
    # Una vez antes de TODOS los tests del describe
    @connection = Database.connect
  end

  before(:each) do
    # Antes de CADA test
    @connection.begin_transaction
  end

  after(:each) do
    # Después de CADA test
    @connection.rollback
  end

  after(:all) do
    # Una vez después de TODOS los tests
    @connection.close
  end
end
```

---

## Mocks y Stubs

```ruby
RSpec.describe OrderService do
  describe '#create_order' do
    let(:payment_gateway) { instance_double(PaymentGateway) }
    let(:mailer) { instance_double(OrderMailer) }
    let(:service) { OrderService.new(payment_gateway, mailer) }

    it 'procesa el pago y envía email' do
      # Stub — define retorno
      allow(payment_gateway).to receive(:charge)
        .with(100)
        .and_return({ success: true, id: "pay_123" })

      allow(mailer).to receive(:send_confirmation)

      # Act
      result = service.create_order(amount: 100)

      # Mock — verifica que se llamó
      expect(payment_gateway).to have_received(:charge).with(100)
      expect(mailer).to have_received(:send_confirmation).once
      expect(result).to be_success
    end

    it 'no envía email si el pago falla' do
      allow(payment_gateway).to receive(:charge)
        .and_return({ success: false })

      service.create_order(amount: 100)

      expect(mailer).not_to have_received(:send_confirmation)
    end
  end
end
```

---

## Shared examples

```ruby
RSpec.shared_examples "colección" do
  it 'empieza vacía' do
    expect(subject).to be_empty
  end

  it 'puede añadir elementos' do
    subject.add("item")
    expect(subject).not_to be_empty
  end

  it 'puede contar elementos' do
    3.times { |i| subject.add("item_#{i}") }
    expect(subject.count).to eq(3)
  end
end

RSpec.describe Stack do
  it_behaves_like "colección"
end

RSpec.describe Queue do
  it_behaves_like "colección"
end
```

---

## Resumen

| Concepto | Minitest | RSpec |
|---|---|---|
| Assertion/Expect | `assert_equal` | `expect().to eq()` |
| Setup | `def setup` | `before`, `let` |
| Test method | `def test_xxx` | `it 'xxx'` |
| Grouping | Class | `describe`, `context` |
| Mocks | `Minitest::Mock` | `instance_double` |
| Run | `ruby test_file.rb` | `rspec` |
