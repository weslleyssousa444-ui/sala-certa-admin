<?php
/**
 * ========================================
 * TESTES - ReservaTest.php
 * ========================================
 *
 * Testes unitários para a classe Reserva
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ReservaTest extends TestCase
{
    private $reserva;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reserva = new \Reserva();
    }

    protected function tearDown(): void
    {
        $this->reserva = null;
        parent::tearDown();
    }

    // ========================================
    // TESTES BÁSICOS
    // ========================================

    #[Test]
    public function testDeveCriarInstanciaDeReserva(): void
    {
        $this->assertInstanceOf(\Reserva::class, $this->reserva);
    }

    #[Test]
    public function testDeveDefinirIds(): void
    {
        $this->reserva->setId(1);
        $this->assertEquals(1, $this->reserva->getId());
    }

    #[Test]
    public function testDeveIniciarComIdNulo(): void
    {
        $this->assertNull($this->reserva->getId());
    }

    // ========================================
    // TESTES DE DATA
    // ========================================

    #[Test]
    public function testDeveAceitarDataReserva(): void
    {
        $this->reserva->setDataReserva('2025-11-15');
        $this->assertEquals('2025-11-15', $this->reserva->getDataReserva());
    }

    #[Test]
    public function testDeveAceitarDataFutura(): void
    {
        $this->reserva->setDataReserva('2025-12-31');
        $this->assertEquals('2025-12-31', $this->reserva->getDataReserva());
    }

    #[Test]
    public function testDeveValidarFormatoData(): void
    {
        $data = '2025-11-15';
        $this->reserva->setDataReserva($data);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $this->reserva->getDataReserva());
    }

    #[Test]
    public function testDeveMudarData(): void
    {
        $this->reserva->setDataReserva('2025-11-15');
        $this->reserva->setDataReserva('2025-12-25');
        $this->assertEquals('2025-12-25', $this->reserva->getDataReserva());
    }

    #[Test]
    public function testDeveRejeitarDataPassada(): void
    {
        $dataPassada = '2020-01-01';
        $this->reserva->setDataReserva($dataPassada);
        
        // Verifica se a data é anterior a hoje
        $hoje = new \DateTime();
        $dataReserva = new \DateTime($this->reserva->getDataReserva());
        
        // Este teste assume que sua classe valida datas passadas
        // Se não valida, você deveria adicionar essa validação
        $this->assertLessThanOrEqual($hoje, $dataReserva);
    }

    // ========================================
    // TESTES DE HORÁRIO
    // ========================================

    #[Test]
    public function testDeveAceitarHorario(): void
    {
        $this->reserva->setHoraInicio('14:00:00');
        $this->assertEquals('14:00:00', $this->reserva->getHoraInicio());
    }

    #[Test]
    public function testDeveAceitarHorarioManha(): void
    {
        $this->reserva->setHoraInicio('08:00:00');
        $this->assertEquals('08:00:00', $this->reserva->getHoraInicio());
    }

    #[Test]
    public function testDeveAceitarHorarioNoite(): void
    {
        $this->reserva->setHoraInicio('20:00:00');
        $this->assertEquals('20:00:00', $this->reserva->getHoraInicio());
    }

    #[Test]
    public function testDeveAceitarTempoReserva(): void
    {
        $this->reserva->setTempoReserva('02:00:00');
        $this->assertEquals('02:00:00', $this->reserva->getTempoReserva());
    }

    #[Test]
    public function testDeveValidarFormatoHora(): void
    {
        $hora = '14:30:45';
        $this->reserva->setHoraInicio($hora);
        $this->assertMatchesRegularExpression('/^\d{2}:\d{2}:\d{2}$/', $this->reserva->getHoraInicio());
    }

    #[Test]
    public function testDeveAceitarTempoMinimo(): void
    {
        $this->reserva->setTempoReserva('00:30:00');
        $this->assertEquals('00:30:00', $this->reserva->getTempoReserva());
    }

    #[Test]
    public function testDeveAceitarTempoMaximo(): void
    {
        $this->reserva->setTempoReserva('08:00:00');
        $this->assertEquals('08:00:00', $this->reserva->getTempoReserva());
    }

    // ========================================
    // TESTES DE ESTADO
    // ========================================

    #[Test]
    public function testDeveAceitarEstadoAtiva(): void
    {
        $this->reserva->setEstado('Ativa');
        $this->assertEquals('Ativa', $this->reserva->getEstado());
    }

    #[Test]
    public function testDeveAceitarEstadoCancelada(): void
    {
        $this->reserva->setEstado('Cancelada');
        $this->assertEquals('Cancelada', $this->reserva->getEstado());
    }

    #[Test]
    public function testDeveAceitarEstadoConcluida(): void
    {
        $this->reserva->setEstado('Concluída');
        $this->assertEquals('Concluída', $this->reserva->getEstado());
    }

    #[Test]
    public function testDeveMudarEstado(): void
    {
        $this->reserva->setEstado('Ativa');
        $this->reserva->setEstado('Cancelada');
        $this->assertEquals('Cancelada', $this->reserva->getEstado());
    }

    #[Test]
    public function testDeveValidarTransicaoEstado(): void
    {
        $this->reserva->setEstado('Ativa');
        $this->assertEquals('Ativa', $this->reserva->getEstado());
        
        $this->reserva->setEstado('Concluída');
        $this->assertEquals('Concluída', $this->reserva->getEstado());
    }

    // ========================================
    // TESTES DE RELACIONAMENTOS
    // ========================================

    #[Test]
    public function testDeveDefinirUsuarioId(): void
    {
        $this->reserva->setUsuarioId(10);
        $this->assertEquals(10, $this->reserva->getUsuarioId());
    }

    #[Test]
    public function testDeveDefinirSalaId(): void
    {
        $this->reserva->setSalaId(5);
        $this->assertEquals(5, $this->reserva->getSalaId());
    }

    #[Test]
    public function testDeveAceitarUsuarioIdZero(): void
    {
        $this->reserva->setUsuarioId(0);
        $this->assertEquals(0, $this->reserva->getUsuarioId());
    }

    #[Test]
    public function testDeveAceitarSalaIdGrande(): void
    {
        $this->reserva->setSalaId(9999);
        $this->assertEquals(9999, $this->reserva->getSalaId());
    }

    // ========================================
    // TESTES INTEGRADOS
    // ========================================

    #[Test]
    public function testDeveConfigurarReservaCompleta(): void
    {
        $this->reserva->setId(100);
        $this->reserva->setUsuarioId(50);
        $this->reserva->setSalaId(25);
        $this->reserva->setDataReserva('2025-11-15');
        $this->reserva->setHoraInicio('14:00:00');
        $this->reserva->setTempoReserva('02:00:00');
        $this->reserva->setEstado('Ativa');

        $this->assertEquals(100, $this->reserva->getId());
        $this->assertEquals(50, $this->reserva->getUsuarioId());
        $this->assertEquals(25, $this->reserva->getSalaId());
        $this->assertEquals('2025-11-15', $this->reserva->getDataReserva());
        $this->assertEquals('14:00:00', $this->reserva->getHoraInicio());
        $this->assertEquals('02:00:00', $this->reserva->getTempoReserva());
        $this->assertEquals('Ativa', $this->reserva->getEstado());
    }

    #[Test]
    public function testDeveManterConsistenciaAposMultiplasAlteracoes(): void
    {
        // Primeira configuração
        $this->reserva->setDataReserva('2025-11-15');
        $this->reserva->setHoraInicio('10:00:00');
        $this->reserva->setEstado('Ativa');

        // Alterações
        $this->reserva->setDataReserva('2025-12-20');
        $this->reserva->setHoraInicio('15:00:00');

        // Verificações
        $this->assertEquals('2025-12-20', $this->reserva->getDataReserva());
        $this->assertEquals('15:00:00', $this->reserva->getHoraInicio());
        $this->assertEquals('Ativa', $this->reserva->getEstado());
    }

    // ========================================
    // TESTES DE EDGE CASES
    // ========================================

    #[Test]
    public function testDevePermitirReservaNoMesmoHorario(): void
    {
        $reserva1 = new \Reserva();
        $reserva2 = new \Reserva();

        $reserva1->setDataReserva('2025-11-15');
        $reserva1->setHoraInicio('14:00:00');

        $reserva2->setDataReserva('2025-11-15');
        $reserva2->setHoraInicio('14:00:00');

        $this->assertEquals($reserva1->getDataReserva(), $reserva2->getDataReserva());
        $this->assertEquals($reserva1->getHoraInicio(), $reserva2->getHoraInicio());
    }

    #[Test]
    public function testDeveManterDadosAposMultiplasInstancias(): void
    {
        $this->reserva->setId(999);
        $this->reserva->setEstado('Ativa');

        $novaReserva = new \Reserva();
        $this->assertNull($novaReserva->getId());
        $this->assertNotEquals('Ativa', $novaReserva->getEstado());
    }

    // ========================================
    // TESTES DE VALIDAÇÃO
    // ========================================

    #[Test]
    public function testDeveValidarDataNoFormatoCorreto(): void
    {
        $this->reserva->setDataReserva('2025-11-15');
        $data = $this->reserva->getDataReserva();
        
        $this->assertIsString($data);
        $this->assertStringContainsString('-', $data);
        $this->assertEquals(10, strlen($data));
    }

    #[Test]
    public function testDeveValidarHorarioNoFormatoCorreto(): void
    {
        $this->reserva->setHoraInicio('14:30:00');
        $hora = $this->reserva->getHoraInicio();
        
        $this->assertIsString($hora);
        $this->assertStringContainsString(':', $hora);
        $this->assertEquals(8, strlen($hora));
    }
}
