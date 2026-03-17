 Localização: tests/README.mdpowershell$utf8NoBom = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllText("$PWD\tests\README.md", @'
# 🧪 Documentação de Testes - Sistema Sala Certa

## 📋 Índice

- [Visão Geral](#visão-geral)
- [Estrutura](#estrutura)
- [Configuração](#configuração)
- [Executando Testes](#executando-testes)
- [Tipos de Testes](#tipos-de-testes)
- [Cobertura](#cobertura)
- [CI/CD](#cicd)
- [Troubleshooting](#troubleshooting)

---

## 🎯 Visão Geral

Este projeto utiliza **PHPUnit 9.6** para testes automatizados. Atualmente temos **23 testes unitários** cobrindo as principais classes do sistema.

### Estatísticas Atuais:
- ✅ **23 testes**
- ✅ **33 assertions**
- ✅ **100% de sucesso**
- ⏱️ **Tempo médio: ~0.1s**

---

## 📁 Estruturatests/
├── README.md              # Este arquivo
├── bootstrap.php          # Inicialização dos testes
├── Unit/                  # Testes unitários
│   ├── UsuarioTest.php   # 8 testes - Classe Usuario
│   ├── SalaTest.php      # 8 testes - Classe Sala
│   └── ReservaTest.php   # 7 testes - Classe Reserva
└── Integration/           # Testes de integração (futuro)

---

## ⚙️ Configuração

### Requisitos:
- PHP 8.2+
- Composer
- PHPUnit 9.6+

### Instalação:
```bash1. Instalar dependências
composer install2. Verificar instalação
vendor/bin/phpunit --version

### Arquivos de Configuração:

**phpunit.xml** (raiz do projeto):
```xml<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php"
      colors="true"
      verbose="true">
<testsuites>
<testsuite name="Unit Tests">
<directory>tests/Unit</directory>
</testsuite>
</testsuites>
</phpunit>

**composer.json**:
```json{
"require-dev": {
"phpunit/phpunit": "^9.5"
},
"autoload": {
"classmap": ["classes/"]
}
}

---

## 🚀 Executando Testes

### Comandos Básicos:
```bashExecutar todos os testes
vendor/bin/phpunitFormato legível (testdox)
vendor/bin/phpunit --testdoxCom cores
vendor/bin/phpunit --testdox --colors=alwaysApenas um arquivo
vendor/bin/phpunit tests/Unit/UsuarioTest.phpCom informações detalhadas
vendor/bin/phpunit --testdox --verbose

### PowerShell (Windows):
```powershellExecutar todos os testes
vendor\bin\phpunit.batFormato legível
vendor\bin\phpunit.bat --testdox --colors=alwaysApenas UsuarioTest
vendor\bin\phpunit.bat tests\Unit\UsuarioTest.php --testdox

---

## 🧩 Tipos de Testes

### 1. Testes Unitários (Unit/)

Testam classes isoladamente, sem dependências externas.

#### **UsuarioTest.php**
```php✅ Instância da classe
✅ Getters e Setters (nome, email, CPF)
✅ Criptografia de senha (bcrypt)
✅ Tipos de usuário (admin, comum, professor, aluno)
✅ Setores (TI, Acadêmico, RH)
✅ Validação de emails
✅ Configuração completa de usuário

**Exemplo de teste:**
```php/** @test */
public function deve_criptografar_senha()
{
    $senhaPlana = 'senha123';
    this−>usuario−>setSenha(this->usuario->setSenha(
this−>usuario−>setSenha(senhaPlana);
    $senhaHash = $this->usuario->getSenha();
$this->assertNotEquals($senhaPlana, $senhaHash);
$this->assertTrue(password_verify($senhaPlana, $senhaHash));
}

#### **SalaTest.php**
```php✅ Instância da classe
✅ Números de sala (T01, 101, 201, LAB01)
✅ Capacidades (0 a 500+ pessoas)
✅ Tipos de sala (Comum, Laboratório, Auditório)
✅ Setores responsáveis
✅ Descrições e recursos
✅ Configuração completa de sala

#### **ReservaTest.php**
```php✅ Instância da classe
✅ IDs (reserva, usuário, sala)
✅ Datas de reserva
✅ Horários (matutino, vespertino, noturno)
✅ Tempo de reserva (30min a 8h)
✅ Estados (Ativa, Cancelada, Concluída)
✅ Configuração completa de reserva

### 2. Testes de Integração (Integration/)

**Status:** Planejado para futuras versões

Irão testar:
- Cadastro no banco de dados
- Consultas SQL
- Relacionamentos entre tabelas
- Transações

---

## 📊 Cobertura de Testes

### Classes Testadas:

| Classe | Testes | Cobertura | Status |
|--------|--------|-----------|--------|
| Usuario | 8 | ~80% | ✅ Alta |
| Sala | 8 | ~80% | ✅ Alta |
| Reserva | 7 | ~75% | ✅ Alta |

### Métodos Cobertos:

**Usuario:**
- ✅ Getters/Setters
- ✅ setSenha() - criptografia
- ⚠️ login() - necessita integração
- ⚠️ cadastrar() - necessita integração

**Sala:**
- ✅ Getters/Setters
- ⚠️ cadastrar() - necessita integração
- ⚠️ listarTodas() - necessita integração

**Reserva:**
- ✅ Getters/Setters
- ⚠️ cadastrar() - necessita integração
- ⚠️ listar() - necessita integração

---

## 🔄 CI/CD

### GitHub Actions

O projeto inclui workflow automático que:
1. Roda testes a cada push
2. Verifica qualidade do código
3. Gera relatórios de cobertura

**Arquivo:** `.github/workflows/tests.yml`

### Script de Deploy

Antes de fazer deploy, rode:
```bash./scripts/pre-deploy.sh

Este script:
1. Executa todos os testes
2. Verifica se há erros
3. Só permite deploy se 100% passar

---

## 🐛 Troubleshooting

### Problema: "Module openssl already loaded"

**Causa:** Duplicação da extensão no php.ini

**Solução:**
```bashEditar php.ini e remover linha duplicada de openssl

### Problema: "Session cannot be started"

**Causa:** Config.php sendo carregado nos testes

**Solução:** As classes já estão protegidas com `TEST_MODE`:
```phpif (!defined('TEST_MODE')) {
require_once 'config/conexao.php';
}

### Problema: "Class not found"

**Causa:** Autoload não configurado

**Solução:**
```bashcomposer dump-autoload

### Problema: Testes lentos

**Causa:** Testes de senha usam bcrypt (CPU intensivo)

**Solução:** Normal. Testes de criptografia levam ~100ms cada.

---

## 📖 Boas Práticas

### Nomenclatura de Testes:

✅ **BOM:**
```php/** @test */
public function deve_criptografar_senha()
public function deve_aceitar_email_valido()
public function deve_rejeitar_cpf_invalido()

❌ **RUIM:**
```phppublic function test1()
public function testSenha()
public function myTest()

### Estrutura de um Teste:
```php/** @test */
public function deve_fazer_algo()
{
// 1. ARRANGE (Preparar)
$valor = 'teste';// 2. ACT (Executar)
$this->objeto->setValor($valor);// 3. ASSERT (Verificar)
$this->assertEquals($valor, $this->objeto->getValor());
}

### Assertions Comuns:
```php// Igualdade
this−>assertEquals(this->assertEquals(
this−>assertEquals(esperado, $atual);
this−>assertNotEquals(this->assertNotEquals(
this−>assertNotEquals(esperado, $atual);
// Verdadeiro/Falso
this−>assertTrue(this->assertTrue(
this−>assertTrue(condicao);
this−>assertFalse(this->assertFalse(
this−>assertFalse(condicao);
// Tipo/Instância
$this->assertInstanceOf(Usuario::class, $obj);
this−>assertIsString(this->assertIsString(
this−>assertIsString(variavel);
this−>assertIsInt(this->assertIsInt(
this−>assertIsInt(variavel);
// Vazio/Null
this−>assertEmpty(this->assertEmpty(
this−>assertEmpty(array);
this−>assertNull(this->assertNull(
this−>assertNull(variavel);


---

## 🎯 Roadmap

### Próximas Implementações:

**Fase 2 - Testes de Integração:**
- [ ] Testes com banco de dados
- [ ] Mocks de PDO
- [ ] Testes de transações

**Fase 3 - Validações:**
- [ ] Validação de CPF
- [ ] Validação de email
- [ ] Regras de negócio (conflitos de reserva)

**Fase 4 - Performance:**
- [ ] Testes de carga
- [ ] Benchmarks
- [ ] Otimizações

**Fase 5 - E2E:**
- [ ] Testes de interface (Selenium)
- [ ] Testes de API
- [ ] Testes de fluxo completo

---

## 🤝 Contribuindo

### Adicionando Novos Testes:

1. Crie arquivo em `tests/Unit/`
2. Use namespace `Tests\Unit`
3. Extenda `PHPUnit\Framework\TestCase`
4. Use anotação `@test`
5. Nomeie métodos: `deve_fazer_algo()`

**Exemplo:**
```php<?php
namespace Tests\Unit;use PHPUnit\Framework\TestCase;class MinhaClasseTest extends TestCase
{
private $objeto;protected function setUp(): void
{
    parent::setUp();
    $this->objeto = new MinhaClasse();
}/** @test */
public function deve_fazer_algo()
{
    // Seu teste aqui
}
}

---

## 📞 Suporte

- **Documentação PHPUnit:** https://phpunit.de/documentation.html
- **Issues:** Reporte bugs no GitHub
- **Email:** suporte@salacerta.online

---

## 📄 Licença

Este projeto é parte do Sistema Sala Certa © 2025

---

**Última atualização:** 22/10/2025  
**Versão dos testes:** 1.0.0  
**PHPUnit:** 9.6.29  
**PHP:** 8.2.12
'@, $utf8NoBom)

Write-Host "✅ README.md criado em tests/README.md" -ForegroundColor Green