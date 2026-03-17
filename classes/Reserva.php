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
    
    // Cadastrar nova reserva
    public function cadastrar() {
        $conn = Conexao::getConn();
        
        $sql = "INSERT INTO RESERVA_SALA (USUARIO_ID, SALA_ID, DATA_RESERVA, HORA_INICIO, TEMPO_RESERVA, ESTADO) 
                VALUES (:usuarioId, :salaId, :dataReserva, :horaInicio, :tempoReserva, :estado)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':usuarioId', $this->usuarioId);
        $stmt->bindValue(':salaId', $this->salaId);
        $stmt->bindValue(':dataReserva', $this->dataReserva);
        $stmt->bindValue(':horaInicio', $this->horaInicio);
        $stmt->bindValue(':tempoReserva', $this->tempoReserva);
        $stmt->bindValue(':estado', $this->estado);
        
        return $stmt->execute();
    }
    
    // Listar todas as reservas
    public static function listarTodas() {
        $conn = Conexao::getConn();
        
        $sql = "SELECT r.*, u.USUARIO_NOME, s.NUM_SALA 
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
                AND ESTADO = 'Ativa'";
        
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
        
        $sql = "SELECT r.*, u.USUARIO_NOME, s.NUM_SALA 
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
                    ESTADO = :estado
                WHERE RESERVA_ID = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':usuarioId', $this->usuarioId);
        $stmt->bindValue(':salaId', $this->salaId);
        $stmt->bindValue(':dataReserva', $this->dataReserva);
        $stmt->bindValue(':horaInicio', $this->horaInicio);
        $stmt->bindValue(':tempoReserva', $this->tempoReserva);
        $stmt->bindValue(':estado', $this->estado);
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
}