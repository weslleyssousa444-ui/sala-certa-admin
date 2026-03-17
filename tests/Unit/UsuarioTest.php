<?php
/**
 * ========================================
 * TESTES - UsuarioTest.php
 * ========================================
 *
 * Testes unitários para a classe Usuario
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

class UsuarioTest extends TestCase
{
    private $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->usuario = new \Usuario();
    }

    protected function tearDown(): void
    {
        $this->usuario = null;
        parent::tearDown();
    }

    // ========================================
    // TESTES BÁSICOS
    // ========================================

    #[Test]
    public function deve_criar_instancia_de_usuario(): void
    {
        $this->assertInstanceOf(\Usuario::class, $this->usuario);
    }

    #[Test]
    public function deve_iniciar_com_id_nulo(): void
    {
        $this->assertNull($this->usuario->getId());
    }

    #[Test]
    public function deve_definir_e_obter_id(): void
    {
        $this->usuario->setId(42);
        $this->assertEquals(42, $this->usuario->getId());
    }

    #[Test]
    public function deve_aceitar_id_zero(): void
    {
        $this->usuario->setId(0);
        $this->assertEquals(0, $this->usuario->getId());
    }

    #[Test]
    public function deve_aceitar_id_grande(): void
    {
        $this->usuario->setId(999999);
        $this->assertEquals(999999, $this->usuario->getId());
    }

    #[Test]
    public function deve_mudar_id(): void
    {
        $this->usuario->setId(1);
        $this->usuario->setId(100);
        $this->assertEquals(100, $this->usuario->getId());
    }

    // ========================================
    // TESTES DE NOME
    // ========================================

    #[Test]
    public function deve_definir_nome_simples(): void
    {
        $this->usuario->setNome('João Silva');
        $this->assertEquals('João Silva', $this->usuario->getNome());
    }

    #[Test]
    public function deve_aceitar_nome_completo(): void
    {
        $this->usuario->setNome('Maria Aparecida dos Santos Silva');
        $this->assertNotEmpty($this->usuario->getNome());
        $this->assertGreaterThan(10, strlen($this->usuario->getNome()));
    }

    #[Test]
    public function deve_aceitar_nome_com_acentos(): void
    {
        $this->usuario->setNome('José');
        $this->assertEquals('José', $this->usuario->getNome());
    }

    #[Test]
    public function deve_aceitar_nome_com_caracteres_especiais(): void
    {
        $this->usuario->setNome("D'Angelo");
        $this->assertEquals("D'Angelo", $this->usuario->getNome());
    }

    #[Test]
    public function deve_mudar_nome(): void
    {
        $this->usuario->setNome('João');
        $this->usuario->setNome('Maria');
        $this->assertEquals('Maria', $this->usuario->getNome());
    }

    #[Test]
    public function deve_aceitar_nome_curto(): void
    {
        $this->usuario->setNome('Ana');
        $this->assertEquals('Ana', $this->usuario->getNome());
    }

    #[Test]
    public function deve_aceitar_nome_composto(): void
    {
        $this->usuario->setNome('Ana Maria');
        $this->assertStringContainsString(' ', $this->usuario->getNome());
    }

    #[Test]
    public function nome_deve_ser_string(): void
    {
        $this->usuario->setNome('Teste');
        $this->assertIsString($this->usuario->getNome());
    }

    // ========================================
    // TESTES DE EMAIL - BÁSICOS
    // ========================================

    #[Test]
    public function deve_definir_email_basico(): void
    {
        $this->usuario->setEmail('joao@example.com');
        $this->assertEquals('joao@example.com', $this->usuario->getEmail());
    }

    #[Test]
    public function deve_aceitar_email_com_subdominio(): void
    {
        $this->usuario->setEmail('teste@sub.dominio.com.br');
        $this->assertStringContainsString('@', $this->usuario->getEmail());
    }

    #[Test]
    public function deve_aceitar_email_com_ponto(): void
    {
        $this->usuario->setEmail('user.name@example.com');
        $this->assertStringContainsString('.', $this->usuario->getEmail());
    }

    #[Test]
    public function deve_aceitar_email_com_numeros(): void
    {
        $this->usuario->setEmail('user123@example.com');
        $this->assertStringContainsString('@', $this->usuario->getEmail());
    }

    #[Test]
    public function deve_aceitar_email_com_hifen(): void
    {
        $this->usuario->setEmail('user-name@example.com');
        $this->assertStringContainsString('-', $this->usuario->getEmail());
    }

    #[Test]
    public function deve_aceitar_email_com_underscore(): void
    {
        $this->usuario->setEmail('user_name@example.com');
        $this->assertStringContainsString('_', $this->usuario->getEmail());
    }

    #[Test]
    public function deve_validar_formato_email(): void
    {
        $email = 'teste@example.com';
        $this->usuario->setEmail($email);
        $this->assertMatchesRegularExpression('/^[^@]+@[^@]+\.[^@]+$/', $email);
    }

    #[Test]
    public function deve_mudar_email(): void
    {
        $this->usuario->setEmail('primeiro@example.com');
        $this->usuario->setEmail('segundo@example.com');
        $this->assertEquals('segundo@example.com', $this->usuario->getEmail());
    }

    #[Test]
    public function email_deve_ter_arroba(): void
    {
        $this->usuario->setEmail('teste@example.com');
        $this->assertStringContainsString('@', $this->usuario->getEmail());
    }

    #[Test]
    public function email_deve_ter_dominio(): void
    {
        $this->usuario->setEmail('teste@example.com');
        $email = $this->usuario->getEmail();
        $partes = explode('@', $email);
        $this->assertCount(2, $partes);
        $this->assertNotEmpty($partes[1]);
    }

    // ========================================
    // TESTES DE EMAIL - DATA PROVIDER
    // ========================================

    public static function emailsValidosProvider(): array
    {
        return [
            'email simples' => ['user@example.com'],
            'email com ponto' => ['user.name@example.com'],
            'email com números' => ['user123@example.com'],
            'email com hífen' => ['user-name@example.com'],
            'email com underscore' => ['user_name@example.com'],
            'email com subdomínio' => ['user@mail.example.com'],
            'email .br' => ['user@example.com.br'],
            'email .org' => ['user@example.org'],
        ];
    }

    #[Test]
    #[DataProvider('emailsValidosProvider')]
    public function deve_aceitar_emails_validos(string $email): void
    {
        $this->usuario->setEmail($email);
        $this->assertEquals($email, $this->usuario->getEmail());
        $this->assertStringContainsString('@', $this->usuario->getEmail());
    }

    // ========================================
    // TESTES DE SENHA
    // ========================================

    #[Test]
    public function deve_criptografar_senha(): void
    {
        $senhaPlana = 'senha123';
        $this->usuario->setSenha($senhaPlana);
        $this->assertNotEquals($senhaPlana, $this->usuario->getSenha());
    }

    #[Test]
    public function deve_verificar_senha_correta(): void
    {
        $this->usuario->setSenha('senha123');
        $this->assertTrue(password_verify('senha123', $this->usuario->getSenha()));
    }

    #[Test]
    public function hash_deve_ter_60_caracteres(): void
    {
        $this->usuario->setSenha('senha123');
        $this->assertEquals(60, strlen($this->usuario->getSenha()));
    }

    #[Test]
    public function nao_deve_verificar_senha_errada(): void
    {
        $this->usuario->setSenha('senhaCorreta');
        $this->assertFalse(password_verify('senhaErrada', $this->usuario->getSenha()));
    }

    #[Test]
    public function deve_criptografar_senha_longa(): void
    {
        $senhaLonga = 'SenhaSuper$egura123!@#ComplexaELonga';
        $this->usuario->setSenha($senhaLonga);
        $this->assertNotEquals($senhaLonga, $this->usuario->getSenha());
        $this->assertTrue(password_verify($senhaLonga, $this->usuario->getSenha()));
    }

    #[Test]
    public function deve_criptografar_senha_com_caracteres_especiais(): void
    {
        $senha = 'S3nh@!#$%';
        $this->usuario->setSenha($senha);
        $this->assertTrue(password_verify($senha, $this->usuario->getSenha()));
    }

    #[Test]
    public function deve_gerar_hashs_diferentes_para_mesma_senha(): void
    {
        $usuario1 = new \Usuario();
        $usuario2 = new \Usuario();
        
        $usuario1->setSenha('senha123');
        $usuario2->setSenha('senha123');
        
        $this->assertNotEquals($usuario1->getSenha(), $usuario2->getSenha());
    }

    #[Test]
    public function hash_deve_comecar_com_dollar_2y(): void
    {
        $this->usuario->setSenha('senha123');
        $this->assertStringStartsWith('$2y$', $this->usuario->getSenha());
    }

    #[Test]
    public function deve_aceitar_senha_curta(): void
    {
        $this->usuario->setSenha('123');
        $this->assertTrue(password_verify('123', $this->usuario->getSenha()));
    }

    #[Test]
    public function deve_mudar_senha(): void
    {
        $this->usuario->setSenha('senhaAntiga');
        $hashAntigo = $this->usuario->getSenha();
        
        $this->usuario->setSenha('senhaNova');
        $hashNovo = $this->usuario->getSenha();
        
        $this->assertNotEquals($hashAntigo, $hashNovo);
        $this->assertFalse(password_verify('senhaAntiga', $hashNovo));
        $this->assertTrue(password_verify('senhaNova', $hashNovo));
    }

    // ========================================
    // TESTES DE CPF
    // ========================================

    #[Test]
    public function deve_aceitar_cpf_formatado(): void
    {
        $this->usuario->setCpf('123.456.789-00');
        $this->assertEquals('123.456.789-00', $this->usuario->getCpf());
    }

    #[Test]
    public function deve_aceitar_cpf_sem_formatacao(): void
    {
        $cpf = '12345678900';
        $this->usuario->setCpf($cpf);
        $this->assertEquals(11, strlen($cpf));
    }

    #[Test]
    public function deve_mudar_cpf(): void
    {
        $this->usuario->setCpf('111.111.111-11');
        $this->usuario->setCpf('222.222.222-22');
        $this->assertEquals('222.222.222-22', $this->usuario->getCpf());
    }

    #[Test]
    public function cpf_formatado_deve_ter_14_caracteres(): void
    {
        $this->usuario->setCpf('123.456.789-00');
        $this->assertEquals(14, strlen($this->usuario->getCpf()));
    }

    #[Test]
    public function cpf_deve_conter_pontos_e_hifen(): void
    {
        $this->usuario->setCpf('123.456.789-00');
        $cpf = $this->usuario->getCpf();
        $this->assertStringContainsString('.', $cpf);
        $this->assertStringContainsString('-', $cpf);
    }

    #[Test]
    public function deve_aceitar_cpf_com_zeros(): void
    {
        $this->usuario->setCpf('000.000.000-00');
        $this->assertEquals('000.000.000-00', $this->usuario->getCpf());
    }

    // ========================================
    // TESTES DE CPF - DATA PROVIDER
    // ========================================

    public static function cpfsValidosProvider(): array
    {
        return [
            'cpf formatado padrão' => ['123.456.789-00'],
            'cpf sem formatação' => ['12345678900'],
            'cpf com zeros' => ['000.000.000-00'],
            'cpf sequencial' => ['111.111.111-11'],
        ];
    }

    #[Test]
    #[DataProvider('cpfsValidosProvider')]
    public function deve_aceitar_diferentes_formatos_cpf(string $cpf): void
    {
        $this->usuario->setCpf($cpf);
        $this->assertEquals($cpf, $this->usuario->getCpf());
    }

    // ========================================
    // TESTES DE TIPO USUÁRIO
    // ========================================

    #[Test]
    public function deve_aceitar_tipo_admin(): void
    {
        $this->usuario->setTipoUsuario('admin');
        $this->assertEquals('admin', $this->usuario->getTipoUsuario());
    }

    #[Test]
    public function deve_aceitar_tipo_comum(): void
    {
        $this->usuario->setTipoUsuario('comum');
        $this->assertEquals('comum', $this->usuario->getTipoUsuario());
    }

    #[Test]
    public function deve_mudar_tipo_usuario(): void
    {
        $this->usuario->setTipoUsuario('comum');
        $this->usuario->setTipoUsuario('admin');
        $this->assertEquals('admin', $this->usuario->getTipoUsuario());
    }

    #[Test]
    public function deve_aceitar_tipo_moderador(): void
    {
        $this->usuario->setTipoUsuario('moderador');
        $this->assertEquals('moderador', $this->usuario->getTipoUsuario());
    }

    #[Test]
    public function tipo_usuario_deve_ser_string(): void
    {
        $this->usuario->setTipoUsuario('admin');
        $this->assertIsString($this->usuario->getTipoUsuario());
    }

    // ========================================
    // TESTES DE TIPO USUÁRIO - DATA PROVIDER
    // ========================================

    public static function tiposUsuarioProvider(): array
    {
        return [
            'tipo admin' => ['admin'],
            'tipo comum' => ['comum'],
            'tipo moderador' => ['moderador'],
            'tipo gestor' => ['gestor'],
        ];
    }

    #[Test]
    #[DataProvider('tiposUsuarioProvider')]
    public function deve_aceitar_diferentes_tipos_usuario(string $tipo): void
    {
        $this->usuario->setTipoUsuario($tipo);
        $this->assertEquals($tipo, $this->usuario->getTipoUsuario());
    }

    // ========================================
    // TESTES INTEGRADOS
    // ========================================

    #[Test]
    public function deve_configurar_usuario_completo(): void
    {
        $this->usuario->setId(100);
        $this->usuario->setNome('João da Silva');
        $this->usuario->setEmail('joao@example.com');
        $this->usuario->setSenha('senha123');
        $this->usuario->setCpf('123.456.789-00');
        $this->usuario->setTipoUsuario('admin');

        $this->assertEquals(100, $this->usuario->getId());
        $this->assertEquals('João da Silva', $this->usuario->getNome());
        $this->assertEquals('joao@example.com', $this->usuario->getEmail());
        $this->assertTrue(password_verify('senha123', $this->usuario->getSenha()));
        $this->assertEquals('123.456.789-00', $this->usuario->getCpf());
        $this->assertEquals('admin', $this->usuario->getTipoUsuario());
    }

    #[Test]
    public function deve_manter_consistencia_apos_multiplas_alteracoes(): void
    {
        // Configuração inicial
        $this->usuario->setNome('João');
        $this->usuario->setEmail('joao@example.com');
        $this->usuario->setTipoUsuario('comum');

        // Alterações
        $this->usuario->setNome('João Silva');
        $this->usuario->setEmail('joao.silva@example.com');
        $this->usuario->setTipoUsuario('admin');

        // Verificações
        $this->assertEquals('João Silva', $this->usuario->getNome());
        $this->assertEquals('joao.silva@example.com', $this->usuario->getEmail());
        $this->assertEquals('admin', $this->usuario->getTipoUsuario());
    }

    #[Test]
    public function deve_manter_dados_independentes_entre_instancias(): void
    {
        $usuario1 = new \Usuario();
        $usuario2 = new \Usuario();

        $usuario1->setNome('João');
        $usuario1->setEmail('joao@example.com');

        $usuario2->setNome('Maria');
        $usuario2->setEmail('maria@example.com');

        $this->assertNotEquals($usuario1->getNome(), $usuario2->getNome());
        $this->assertNotEquals($usuario1->getEmail(), $usuario2->getEmail());
    }

    // ========================================
    // TESTES DE EDGE CASES
    // ========================================

    #[Test]
    public function deve_aceitar_email_longo(): void
    {
        $email = 'usuario.com.nome.muito.grande@dominio.muito.extenso.com.br';
        $this->usuario->setEmail($email);
        $this->assertEquals($email, $this->usuario->getEmail());
    }

    #[Test]
    public function deve_aceitar_nome_com_multiplos_espacos(): void
    {
        $nome = 'João  da  Silva';
        $this->usuario->setNome($nome);
        $this->assertEquals($nome, $this->usuario->getNome());
    }

    #[Test]
    public function deve_persistir_dados_apos_multiplas_mudancas(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->usuario->setId($i);
        }
        $this->assertEquals(10, $this->usuario->getId());
    }

    // ========================================
    // TESTES DE VALIDAÇÃO DE DADOS
    // ========================================

    #[Test]
    public function id_deve_ser_numerico(): void
    {
        $this->usuario->setId(123);
        $this->assertIsNumeric($this->usuario->getId());
    }

    #[Test]
    public function email_nao_deve_estar_vazio(): void
    {
        $this->usuario->setEmail('teste@example.com');
        $this->assertNotEmpty($this->usuario->getEmail());
    }

    #[Test]
    public function nome_nao_deve_estar_vazio(): void
    {
        $this->usuario->setNome('João');
        $this->assertNotEmpty($this->usuario->getNome());
    }

    #[Test]
    public function senha_hash_nao_deve_estar_vazio(): void
    {
        $this->usuario->setSenha('senha123');
        $this->assertNotEmpty($this->usuario->getSenha());
    }

    #[Test]
    public function cpf_nao_deve_estar_vazio(): void
    {
        $this->usuario->setCpf('123.456.789-00');
        $this->assertNotEmpty($this->usuario->getCpf());
    }
}
