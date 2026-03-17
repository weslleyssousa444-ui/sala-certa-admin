<?php

if (!defined('TEST_MODE')) {
    require_once __DIR__ . '/../config/conexao.php';
}

class Reserva {
    private $id;
    private $usuarioId;
    private $salaId;
    private $dataReserva;
    private $horaInicio;
    private $tempoReserva;
    private $estado;
    private $recorrencia;
    private $recorrenciaFim;
    private $reservaPaiId;

    // Getters e Setters
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    public function setUsuarioId($usuarioId) { $this->usuarioId = $usuarioId; }
    public function getUsuarioId() { return $this->usuarioId; }

    public function setSalaId($salaId) { $this->salaId = $salaId; }
    public function getSalaId() { return $this->salaId; }

    public function setDataReserva($dataReserva) { $this->dataReserva = $dataReserva; }
    public function getDataReserva() { return $this->dataReserva; }

    public function setHoraInicio($horaInicio) { $this->horaInicio = $horaInicio; }
    public function getHoraInicio() { return $this->horaInicio; }

    public function setTempoReserva($tempoReserva) { $this->tempoReserva = $tempoReserva; }
    public function getTempoReserva() { return $this->tempoReserva; }

    public function setEstado($estado) { $this->estado = $estado; }
    public function getEstado() { return $this->estado; }

    public function setRecorrencia($recorrencia) { $this->recorrencia = $recorrencia; }
    public function getRecorrencia() { return $this->recorrencia; }

    public function setRecorrenciaFim($recorrenciaFim) { $this->recorrenciaFim = $recorrenciaFim; }
    public function getRecorrenciaFim() { return $this->recorrenciaFim; }

    public function setReservaPaiId($reservaPaiId) { $this->reservaPaiId = $reservaPaiId; }
    public function getReservaPaiId() { return $this->reservaPaiId; }

    // Cadastrar nova reserva
    public function cadastrar() {
        $conn = Conexao::getConn();

        $sql = "INSERT INTO RESERVA_SALA (USUARIO_ID, SALA_ID, DATA_RESERVA, HORA_INICIO, TEMPO_RESERVA, ESTADO";
        $values = " VALUES (:usuarioId, :salaId, :dataReserva, :horaInicio, :tempoReserva, :estado";

        if (!empty($this->recorrencia)) {
            $sql .= ", RECORRENCIA";
            $values .= ", :recorrencia";
        }
        if (!empty($this->recorrenciaFim)) {
            $sql .= ", RECORRENCIA_FIM";
            $values .= ", :recorrenciaFim";
        }
        if (!empty($this->reservaPaiId)) {
            $sql .= ", RESERVA_PAI_ID";
            $values .= ", :reservaPaiId";
        }

        $sql .= ")" . $values . ")";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':usuarioId', $this->usuarioId);
        $stmt->bindValue(':salaId', $this->salaId);
        $stmt->bindValue(':dataReserva', $this->dataReserva);
        $stmt->bindValue(':horaInicio', $this->horaInicio);
        $stmt->bindValue(':tempoReserva', $this->tempoReserva);
        $stmt->bindValue(':estado', $this->estado);

        if (!empty($this->recorrencia)) {
            $stmt->bindValue(':recorrencia', $this->recorrencia);
        }
        if (!empty($this->recorrenciaFim)) {
            $stmt->bindValue(':recorrenciaFim', $this->recorrenciaFim);
        }
        if (!empty($this->reservaPaiId)) {
            $stmt->bindValue(':reservaPaiId', $this->reservaPaiId);
        }

        return $stmt->execute();
    }

    // Listar todas as reservas
    public static function listarTodas() {
        $conn = Conexao::getConn();

        $sql = "SELECT r.*, r.RECORRENCIA, r.RECORRENCIA_FIM, r.RESERVA_PAI_ID, u.USUARIO_NOME, s.NUM_SALA
                FROM RESERVA_SALA r
                JOIN USUARIO u ON r.USUARIO_ID = u.USUARIO_ID
                JOIN SALA s ON r.SALA_ID = s.SALA_ID
                ORDER BY r.DATA_RESERVA DESC, r.HORA_INICIO DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Listar reservas por usuário
    public static function listarPorUsuario($usuarioId) {
        $conn = Conexao::getConn();

        $sql = "SELECT r.*, s.NUM_SALA
                FROM RESERVA_SALA r
                JOIN SALA s ON r.SALA_ID = s.SALA_ID
                WHERE r.USUARIO_ID = :usuarioId
                ORDER BY r.DATA_RESERVA DESC, r.HORA_INICIO DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Listar reservas por data
    public static function listarPorData($data) {
        $conn = Conexao::getConn();

        $sql = "SELECT r.*, u.USUARIO_NOME, s.NUM_SALA
                FROM RESERVA_SALA r
                JOIN USUARIO u ON r.USUARIO_ID = u.USUARIO_ID
                JOIN SALA s ON r.SALA_ID = s.SALA_ID
                WHERE r.DATA_RESERVA = :data
                ORDER BY r.HORA_INICIO ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':data', $data);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Verificar conflito de horário
    public static function verificarConflito($salaId, $dataReserva, $horaInicio, $tempoReserva, $reservaIdExcluir = null) {
        $conn = Conexao::getConn();

        $sql = "SELECT RESERVA_ID, HORA_INICIO, TEMPO_RESERVA
                FROM RESERVA_SALA
                WHERE SALA_ID = :salaId
                AND DATA_RESERVA = :dataReserva
                AND ESTADO IN ('Ativa', 'Reservado', 'Pendente')";

        if ($reservaIdExcluir) {
            $sql .= " AND RESERVA_ID != :reservaIdExcluir";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':salaId', $salaId);
        $stmt->bindValue(':dataReserva', $dataReserva);

        if ($reservaIdExcluir) {
            $stmt->bindValue(':reservaIdExcluir', $reservaIdExcluir);
        }

        $stmt->execute();
        $reservasExistentes = $stmt->fetchAll();

        $horaInicioTimestamp = strtotime($horaInicio);
        $tempoReservaTimestamp = strtotime($tempoReserva);
        $tempoReservaSegundos = $tempoReservaTimestamp - strtotime('00:00:00');
        $horaFimTimestamp = $horaInicioTimestamp + $tempoReservaSegundos;

        foreach ($reservasExistentes as $reservaExistente) {
            $inicioExistenteTimestamp = strtotime($reservaExistente['HORA_INICIO']);
            $tempoExistenteTimestamp = strtotime($reservaExistente['TEMPO_RESERVA']);
            $tempoExistenteSegundos = $tempoExistenteTimestamp - strtotime('00:00:00');
            $fimExistenteTimestamp = $inicioExistenteTimestamp + $tempoExistenteSegundos;

            if ($horaInicioTimestamp < $fimExistenteTimestamp && $horaFimTimestamp > $inicioExistenteTimestamp) {
                return [
                    'conflito' => true,
                    'reserva_conflitante' => $reservaExistente,
                    'horario_ocupado' => date('H:i', $inicioExistenteTimestamp) . ' - ' . date('H:i', $fimExistenteTimestamp)
                ];
            }
        }

        return ['conflito' => false];
    }

    // Buscar horários disponíveis
    public static function buscarHorariosDisponiveis($salaId, $dataReserva) {
        $conn = Conexao::getConn();

        $sql = "SELECT HORA_INICIO, TEMPO_RESERVA
                FROM RESERVA_SALA
                WHERE SALA_ID = :salaId
                AND DATA_RESERVA = :dataReserva
                AND ESTADO = 'Ativa'
                ORDER BY HORA_INICIO ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':salaId', $salaId);
        $stmt->bindValue(':dataReserva', $dataReserva);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Buscar por ID
    public static function buscarPorId($id) {
        $conn = Conexao::getConn();

        $sql = "SELECT r.*, r.RECORRENCIA, r.RECORRENCIA_FIM, r.RESERVA_PAI_ID, u.USUARIO_NOME, s.NUM_SALA
                FROM RESERVA_SALA r
                JOIN USUARIO u ON r.USUARIO_ID = u.USUARIO_ID
                JOIN SALA s ON r.SALA_ID = s.SALA_ID
                WHERE r.RESERVA_ID = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    // Atualizar reserva
    public function atualizar() {
        $conn = Conexao::getConn();

        $sql = "UPDATE RESERVA_SALA
                SET USUARIO_ID = :usuarioId,
                    SALA_ID = :salaId,
                    DATA_RESERVA = :dataReserva,
                    HORA_INICIO = :horaInicio,
                    TEMPO_RESERVA = :tempoReserva,
                    ESTADO = :estado,
                    RECORRENCIA = :recorrencia,
                    RECORRENCIA_FIM = :recorrenciaFim,
                    RESERVA_PAI_ID = :reservaPaiId
                WHERE RESERVA_ID = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':usuarioId', $this->usuarioId);
        $stmt->bindValue(':salaId', $this->salaId);
        $stmt->bindValue(':dataReserva', $this->dataReserva);
        $stmt->bindValue(':horaInicio', $this->horaInicio);
        $stmt->bindValue(':tempoReserva', $this->tempoReserva);
        $stmt->bindValue(':estado', $this->estado);
        $stmt->bindValue(':recorrencia', $this->recorrencia);
        $stmt->bindValue(':recorrenciaFim', $this->recorrenciaFim);
        $stmt->bindValue(':reservaPaiId', $this->reservaPaiId);
        $stmt->bindValue(':id', $this->id);

        return $stmt->execute();
    }

    // Cancelar reserva
    public function cancelar() {
        $conn = Conexao::getConn();

        $sql = "UPDATE RESERVA_SALA SET ESTADO = 'Cancelada' WHERE RESERVA_ID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $this->id);

        return $stmt->execute();
    }

    // Excluir reserva
    public static function excluir($id) {
        $conn = Conexao::getConn();

        $sql = "DELETE FROM RESERVA_SALA WHERE RESERVA_ID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }

    // Criar reservas recorrentes
    public static function criarRecorrente($salaId, $dataInicio, $recorrencia, $recorrenciaFim, $horaInicio, $tempoReserva, $usuarioId) {
        $conn = Conexao::getConn();

        $datas = [];
        $dataAtual = new DateTime($dataInicio);
        $dataFim = new DateTime($recorrenciaFim);
        $limiteMaximo = new DateTime($dataInicio);
        $limiteMaximo->modify('+6 months');

        if ($dataFim > $limiteMaximo) {
            $dataFim = $limiteMaximo;
        }

        // Gera todas as datas da série
        while ($dataAtual <= $dataFim) {
            $datas[] = $dataAtual->format('Y-m-d');

            switch ($recorrencia) {
                case 'semanal':
                    $dataAtual->modify('+7 days');
                    break;
                case 'quinzenal':
                    $dataAtual->modify('+14 days');
                    break;
                case 'mensal':
                    $dataAtual->modify('+1 month');
                    break;
                default:
                    $dataAtual->modify('+7 days');
                    break;
            }
        }

        // Verifica conflitos em cada data
        $conflitos = [];
        $livres = [];

        foreach ($datas as $data) {
            $resultado = self::verificarConflito($salaId, $data, $horaInicio, $tempoReserva);
            if ($resultado['conflito']) {
                $conflitos[] = $data;
            } else {
                $livres[] = $data;
            }
        }

        $total = count($datas);

        // Se há conflitos, retorna informações para o chamador decidir
        if (!empty($conflitos)) {
            return [
                'conflitos' => $conflitos,
                'total' => $total,
                'livres' => count($livres),
                'datas_livres' => $livres
            ];
        }

        // Sem conflitos: cria todas as reservas
        $reservaPaiId = null;

        foreach ($livres as $index => $data) {
            $reserva = new Reserva();
            $reserva->setUsuarioId($usuarioId);
            $reserva->setSalaId($salaId);
            $reserva->setDataReserva($data);
            $reserva->setHoraInicio($horaInicio);
            $reserva->setTempoReserva($tempoReserva);
            $reserva->setEstado('Ativa');

            if ($index === 0) {
                // Primeira reserva é o pai
                $reserva->setRecorrencia($recorrencia);
                $reserva->setRecorrenciaFim($recorrenciaFim);
                $reserva->setReservaPaiId(null);
                $reserva->cadastrar();
                $reservaPaiId = $conn->lastInsertId();
            } else {
                // Demais referenciam o pai
                $reserva->setReservaPaiId($reservaPaiId);
                $reserva->cadastrar();
            }
        }

        return [
            'conflitos' => [],
            'total' => $total,
            'livres' => count($livres),
            'datas_livres' => $livres
        ];
    }

    // Cancelar série de reservas recorrentes (futuras)
    public static function cancelarSerie($reservaPaiId) {
        $conn = Conexao::getConn();

        $hoje = date('Y-m-d');

        $sql = "UPDATE RESERVA_SALA
                SET ESTADO = 'Cancelada'
                WHERE RESERVA_PAI_ID = :reservaPaiId
                AND DATA_RESERVA >= :hoje";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':reservaPaiId', $reservaPaiId);
        $stmt->bindValue(':hoje', $hoje);

        return $stmt->execute();
    }

    // Buscar série de reservas recorrentes
    public static function buscarSerie($reservaPaiId) {
        $conn = Conexao::getConn();

        $sql = "SELECT r.*, r.RECORRENCIA, r.RECORRENCIA_FIM, r.RESERVA_PAI_ID, u.USUARIO_NOME, s.NUM_SALA
                FROM RESERVA_SALA r
                JOIN USUARIO u ON r.USUARIO_ID = u.USUARIO_ID
                JOIN SALA s ON r.SALA_ID = s.SALA_ID
                WHERE r.RESERVA_PAI_ID = :reservaPaiId
                ORDER BY r.DATA_RESERVA ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':reservaPaiId', $reservaPaiId);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
