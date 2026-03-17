<?php
/**
 * ========================================
 * TESTES - SalaTest.php
 * ========================================
 *
 * Testes unitários para a classe Sala
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SalaTest extends TestCase
{
    private $sala;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sala = new \Sala();
    }

    protected function tearDown(): void
    {
        $this->sala = null;
        parent::tearDown();
    }

    // ========================================
    // TESTES DE INSTÂNCIA
    // ========================================

    #[Test]
    public function testDeveCriarInstanciaDeSala(): void
    {
        $this->assertInstanceOf(\Sala::class, $this->sala);
    }

    #[Test]
    public function testDeveIniciarComIdNulo(): void
    {
        $this->assertNull($this->sala->getId());
    }

    #[Test]
    public function testDeveDefinirEObterIdCorretamente(): void
    {
        $this->sala->setId(1);
        $this->assertEquals(1, $this->sala->getId());
        $this->assertIsInt($this->sala->getId());
    }

    #[Test]
    public function testDeveAceitarIdGrande(): void
    {
        $this->sala->setId(999999);
        $this->assertEquals(999999, $this->sala->getId());
    }

    // ========================================
    // TESTES DE NÚMERO DA SALA
    // ========================================

    #[Test]
    public function testDeveAceitarNumeroSala(): void
    {
        $this->sala->setNumSala('101');
        $this->assertEquals('101', $this->sala->getNumSala());
    }

    #[Test]
    public function testDeveAceitarNumeroSalaLaboratorio(): void
    {
        $this->sala->setNumSala('LAB01');
        $this->assertEquals('LAB01', $this->sala->getNumSala());
    }

    #[Test]
    public function testDeveAceitarNumeroSalaComLetrasENumeros(): void
    {
        $this->sala->setNumSala('SALA-A123');
        $this->assertStringContainsString('SALA', $this->sala->getNumSala());
        $this->assertStringContainsString('123', $this->sala->getNumSala());
    }

    #[Test]
    public function testDeveAceitarNumeroSalaAnfiteatro(): void
    {
        $this->sala->setNumSala('ANFITEATRO-1');
        $this->assertEquals('ANFITEATRO-1', $this->sala->getNumSala());
    }

    #[Test]
    public function testDeveAceitarNumeroSalaAuditorio(): void
    {
        $this->sala->setNumSala('AUDITÓRIO');
        $this->assertEquals('AUDITÓRIO', $this->sala->getNumSala());
    }

    #[Test]
    public function testDeveMudarNumeroSala(): void
    {
        $this->sala->setNumSala('101');
        $this->sala->setNumSala('102');
        $this->assertEquals('102', $this->sala->getNumSala());
    }

    #[Test]
    public function testNumeroSalaDeveSerString(): void
    {
        $this->sala->setNumSala('101');
        $this->assertIsString($this->sala->getNumSala());
    }

    // ========================================
    // TESTES DE CAPACIDADE
    // ========================================

    #[Test]
    public function testDeveAceitarCapacidade(): void
    {
        $this->sala->setQtdPessoas(30);
        $this->assertEquals(30, $this->sala->getQtdPessoas());
    }

    #[Test]
    public function testDeveAceitarCapacidadePequena(): void
    {
        $this->sala->setQtdPessoas(10);
        $this->assertEquals(10, $this->sala->getQtdPessoas());
    }

    #[Test]
    public function testDeveAceitarCapacidadeGrande(): void
    {
        $this->sala->setQtdPessoas(100);
        $this->assertEquals(100, $this->sala->getQtdPessoas());
    }

    #[Test]
    public function testDeveAceitarCapacidadeMinimaUmaPessoa(): void
    {
        $this->sala->setQtdPessoas(1);
        $this->assertEquals(1, $this->sala->getQtdPessoas());
        $this->assertGreaterThan(0, $this->sala->getQtdPessoas());
    }

    #[Test]
    public function testDeveAceitarCapacidadeMaximaAuditorio(): void
    {
        $this->sala->setQtdPessoas(500);
        $this->assertEquals(500, $this->sala->getQtdPessoas());
    }

    #[Test]
    public function testDeveMudarCapacidade(): void
    {
        $this->sala->setQtdPessoas(20);
        $this->sala->setQtdPessoas(40);
        $this->assertEquals(40, $this->sala->getQtdPessoas());
    }

    #[Test]
    public function testCapacidadeDeveSerInteiro(): void
    {
        $this->sala->setQtdPessoas(30);
        $this->assertIsInt($this->sala->getQtdPessoas());
    }

    #[Test]
    public function testCapacidadeDeveSerPositiva(): void
    {
        $this->sala->setQtdPessoas(25);
        $this->assertGreaterThan(0, $this->sala->getQtdPessoas());
    }

    // ========================================
    // TESTES DE DESCRIÇÃO
    // ========================================

    #[Test]
    public function testDeveAceitarDescricao(): void
    {
        $descricao = 'Sala com projetor';
        $this->sala->setDescricao($descricao);
        $this->assertEquals($descricao, $this->sala->getDescricao());
    }

    #[Test]
    public function testDeveAceitarDescricaoLonga(): void
    {
        $descricao = 'Sala equipada com projetor multimídia, ar-condicionado, quadro branco e capacidade para 40 pessoas';
        $this->sala->setDescricao($descricao);
        $this->assertGreaterThan(50, strlen($this->sala->getDescricao()));
    }

    #[Test]
    public function testDeveAceitarDescricaoComCaracteresEspeciais(): void
    {
        $descricao = 'Sala c/ ar-condicionado & projetor (Full HD)';
        $this->sala->setDescricao($descricao);
        $this->assertEquals($descricao, $this->sala->getDescricao());
    }

    #[Test]
    public function testDeveAceitarDescricaoVazia(): void
    {
        $this->sala->setDescricao('');
        $this->assertEmpty($this->sala->getDescricao());
    }

    #[Test]
    public function testDeveMudarDescricao(): void
    {
        $this->sala->setDescricao('Descrição antiga');
        $this->sala->setDescricao('Descrição nova');
        $this->assertEquals('Descrição nova', $this->sala->getDescricao());
    }

    #[Test]
    public function testDescricaoDeveSerString(): void
    {
        $this->sala->setDescricao('Teste');
        $this->assertIsString($this->sala->getDescricao());
    }

    // ========================================
    // TESTES DE TIPO DE SALA
    // ========================================

    #[Test]
    public function testDeveAceitarTipoLaboratorio(): void
    {
        $this->sala->setTipoSala('Laboratório');
        $this->assertEquals('Laboratório', $this->sala->getTipoSala());
    }

    #[Test]
    public function testDeveAceitarTipoConvencional(): void
    {
        $this->sala->setTipoSala('Convencional');
        $this->assertEquals('Convencional', $this->sala->getTipoSala());
    }

    #[Test]
    public function testDeveAceitarTipoAuditorio(): void
    {
        $this->sala->setTipoSala('Auditório');
        $this->assertEquals('Auditório', $this->sala->getTipoSala());
    }

    #[Test]
    public function testDeveAceitarTipoAnfiteatro(): void
    {
        $this->sala->setTipoSala('Anfiteatro');
        $this->assertEquals('Anfiteatro', $this->sala->getTipoSala());
    }

    #[Test]
    public function testDeveAceitarTipoMultimidia(): void
    {
        $this->sala->setTipoSala('Multimídia');
        $this->assertEquals('Multimídia', $this->sala->getTipoSala());
    }

    #[Test]
    public function testDeveMudarTipoSala(): void
    {
        $this->sala->setTipoSala('Convencional');
        $this->sala->setTipoSala('Laboratório');
        $this->assertEquals('Laboratório', $this->sala->getTipoSala());
    }

    #[Test]
    public function testTipoSalaDeveSerString(): void
    {
        $this->sala->setTipoSala('Laboratório');
        $this->assertIsString($this->sala->getTipoSala());
    }

    // ========================================
    // TESTES DE SETOR RESPONSÁVEL
    // ========================================

    #[Test]
    public function testDeveAceitarSetorTI(): void
    {
        $this->sala->setSetorResponsavel('TI');
        $this->assertEquals('TI', $this->sala->getSetorResponsavel());
    }

    #[Test]
    public function testDeveAceitarSetorManutencao(): void
    {
        $this->sala->setSetorResponsavel('Manutenção');
        $this->assertEquals('Manutenção', $this->sala->getSetorResponsavel());
    }

    #[Test]
    public function testDeveAceitarSetorAdministracao(): void
    {
        $this->sala->setSetorResponsavel('Administração');
        $this->assertEquals('Administração', $this->sala->getSetorResponsavel());
    }

    #[Test]
    public function testDeveAceitarSetorPatrimonio(): void
    {
        $this->sala->setSetorResponsavel('Patrimônio');
        $this->assertEquals('Patrimônio', $this->sala->getSetorResponsavel());
    }

    #[Test]
    public function testDeveMudarSetorResponsavel(): void
    {
        $this->sala->setSetorResponsavel('TI');
        $this->sala->setSetorResponsavel('Manutenção');
        $this->assertEquals('Manutenção', $this->sala->getSetorResponsavel());
    }

    #[Test]
    public function testSetorResponsavelDeveSerString(): void
    {
        $this->sala->setSetorResponsavel('TI');
        $this->assertIsString($this->sala->getSetorResponsavel());
    }

    // ========================================
    // TESTES DE CONFIGURAÇÃO COMPLETA
    // ========================================

    #[Test]
    public function testDeveConfigurarSalaCompleta(): void
    {
        $this->sala->setNumSala('LAB01');
        $this->sala->setQtdPessoas(20);
        $this->sala->setDescricao('Laboratório completo');
        $this->sala->setTipoSala('Laboratório');
        $this->sala->setSetorResponsavel('TI');

        $this->assertEquals('LAB01', $this->sala->getNumSala());
        $this->assertEquals(20, $this->sala->getQtdPessoas());
        $this->assertEquals('Laboratório completo', $this->sala->getDescricao());
        $this->assertEquals('Laboratório', $this->sala->getTipoSala());
        $this->assertEquals('TI', $this->sala->getSetorResponsavel());
    }

    #[Test]
    public function testDeveConfigurarSalaConvencionalCompleta(): void
    {
        $this->sala->setId(10);
        $this->sala->setNumSala('201');
        $this->sala->setQtdPessoas(35);
        $this->sala->setDescricao('Sala convencional com ar-condicionado');
        $this->sala->setTipoSala('Convencional');
        $this->sala->setSetorResponsavel('Administração');

        $this->assertNotNull($this->sala->getId());
        $this->assertNotEmpty($this->sala->getNumSala());
        $this->assertGreaterThan(0, $this->sala->getQtdPessoas());
    }

    #[Test]
    public function testDeveConfigurarAuditorioCompleto(): void
    {
        $this->sala->setNumSala('AUD-PRINCIPAL');
        $this->sala->setQtdPessoas(200);
        $this->sala->setDescricao('Auditório principal com sistema de som profissional');
        $this->sala->setTipoSala('Auditório');
        $this->sala->setSetorResponsavel('Patrimônio');

        $this->assertEquals('AUD-PRINCIPAL', $this->sala->getNumSala());
        $this->assertEquals(200, $this->sala->getQtdPessoas());
        $this->assertEquals('Auditório', $this->sala->getTipoSala());
    }

    // ========================================
    // TESTES DE MÚLTIPLAS INSTÂNCIAS
    // ========================================

    #[Test]
    public function testDevePermitirMultiplasInstancias(): void
    {
        $sala1 = new \Sala();
        $sala2 = new \Sala();

        $sala1->setNumSala('101');
        $sala2->setNumSala('102');

        $this->assertNotEquals($sala1->getNumSala(), $sala2->getNumSala());
    }

    #[Test]
    public function testInstanciasDevemSerIndependentes(): void
    {
        $sala1 = new \Sala();
        $sala1->setId(1);
        $sala1->setQtdPessoas(30);

        $sala2 = new \Sala();
        $this->assertNull($sala2->getId());
        $this->assertNotEquals(30, $sala2->getQtdPessoas());
    }

    // ========================================
    // TESTES DE CONSISTÊNCIA
    // ========================================

    #[Test]
    public function testDeveManterConsistenciaAposMultiplasAlteracoes(): void
    {
        $this->sala->setNumSala('101');
        $this->sala->setQtdPessoas(20);
        
        $this->sala->setNumSala('102');
        $this->sala->setQtdPessoas(30);
        
        $this->sala->setNumSala('103');
        
        $this->assertEquals('103', $this->sala->getNumSala());
        $this->assertEquals(30, $this->sala->getQtdPessoas());
    }

    #[Test]
    public function testDevePermitirModificacaoIndependenteDeAtributos(): void
    {
        $this->sala->setNumSala('LAB01');
        $this->assertEquals('LAB01', $this->sala->getNumSala());
        
        $this->sala->setQtdPessoas(25);
        $this->assertEquals('LAB01', $this->sala->getNumSala());
        $this->assertEquals(25, $this->sala->getQtdPessoas());
        
        $this->sala->setTipoSala('Laboratório');
        $this->assertEquals('LAB01', $this->sala->getNumSala());
        $this->assertEquals(25, $this->sala->getQtdPessoas());
        $this->assertEquals('Laboratório', $this->sala->getTipoSala());
    }

    // ========================================
    // TESTES DE CENÁRIOS REAIS
    // ========================================

    #[Test]
    public function testDeveCriarSalaDePequenasReunioes(): void
    {
        $this->sala->setNumSala('REUNIÃO-01');
        $this->sala->setQtdPessoas(8);
        $this->sala->setDescricao('Sala de reunião para pequenos grupos');
        $this->sala->setTipoSala('Convencional');

        $this->assertLessThan(10, $this->sala->getQtdPessoas());
        $this->assertStringContainsString('REUNIÃO', $this->sala->getNumSala());
    }

    #[Test]
    public function testDeveCriarLaboratorioDeInformatica(): void
    {
        $this->sala->setNumSala('LAB-INFO-01');
        $this->sala->setQtdPessoas(40);
        $this->sala->setDescricao('Laboratório de informática com 40 computadores');
        $this->sala->setTipoSala('Laboratório');
        $this->sala->setSetorResponsavel('TI');

        $this->assertEquals('Laboratório', $this->sala->getTipoSala());
        $this->assertEquals('TI', $this->sala->getSetorResponsavel());
        $this->assertEquals(40, $this->sala->getQtdPessoas());
    }

    #[Test]
    public function testDeveCriarSalaMultiuso(): void
    {
        $this->sala->setNumSala('MULTIUSO-A');
        $this->sala->setQtdPessoas(50);
        $this->sala->setDescricao('Sala multiuso para diversos eventos');
        $this->sala->setTipoSala('Multimídia');

        $this->assertStringContainsString('MULTIUSO', $this->sala->getNumSala());
        $this->assertGreaterThanOrEqual(50, $this->sala->getQtdPessoas());
    }

    // ========================================
    // TESTES DE VALIDAÇÃO
    // ========================================

    #[Test]
    public function testDeveValidarTodosOsAtributosDefinidos(): void
    {
        $this->sala->setId(1);
        $this->sala->setNumSala('101');
        $this->sala->setQtdPessoas(30);
        $this->sala->setDescricao('Teste');
        $this->sala->setTipoSala('Convencional');
        $this->sala->setSetorResponsavel('TI');

        $this->assertNotNull($this->sala->getId());
        $this->assertNotEmpty($this->sala->getNumSala());
        $this->assertGreaterThan(0, $this->sala->getQtdPessoas());
        $this->assertNotEmpty($this->sala->getDescricao());
        $this->assertNotEmpty($this->sala->getTipoSala());
        $this->assertNotEmpty($this->sala->getSetorResponsavel());
    }
}
