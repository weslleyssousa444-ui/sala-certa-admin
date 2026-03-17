<?php
if (!defined('TEST_MODE')) {
    if (!defined('TEST_MODE')) {
    require_once __DIR__ . '/../config/conexao.php';
}
}
class Sala {
    private $id;
    private $numSala;
    private $qtdPessoas;
    private $descricao;
    private $tipoSala;
    private $setorResponsavel;
    
    // Getters e Setters
    public function getId() { return $this->id; }
    public function getNumSala() { return $this->numSala; }
    public function getQtdPessoas() { return $this->qtdPessoas; }
    public function getDescricao() { return $this->descricao; }
    public function getTipoSala() { return $this->tipoSala; }
    public function getSetorResponsavel() { return $this->setorResponsavel; }
    
    public function setId($id) { $this->id = $id; }
    public function setNumSala($numSala) { $this->numSala = $numSala; }
    public function setQtdPessoas($qtdPessoas) { $this->qtdPessoas = $qtdPessoas; }
    public function setDescricao($descricao) { $this->descricao = $descricao; }
    public function setTipoSala($tipoSala) { $this->tipoSala = $tipoSala; }
    public function setSetorResponsavel($setorResponsavel) { $this->setorResponsavel = $setorResponsavel; }
    
    // Cadastrar sala
    public function cadastrar() {
        $conn = Conexao::getConn();
        
        // Verificar se o número da sala já existe
        $sqlCheck = "SELECT COUNT(*) as total FROM SALA WHERE NUM_SALA = :numSala";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindValue(':numSala', $this->numSala);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->fetch();
        
        if ($resultCheck['total'] > 0) {
            return false; // Número de sala já existe
        }
        
        $sql = "INSERT INTO SALA (NUM_SALA, QTD_PESSOAS, DESCRICAO, TIPO_SALA, SETOR_RESPONSAVEL) 
                VALUES (:numSala, :qtdPessoas, :descricao, :tipoSala, :setorResponsavel)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':numSala', $this->numSala);
        $stmt->bindValue(':qtdPessoas', $this->qtdPessoas);
        $stmt->bindValue(':descricao', $this->descricao);
        $stmt->bindValue(':tipoSala', $this->tipoSala ?? '');
        $stmt->bindValue(':setorResponsavel', $this->setorResponsavel ?? '');
        
        if ($stmt->execute()) {
            $this->id = $conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Listar todas as salas
    public static function listarTodas() {
        $conn = Conexao::getConn();
        
        $sql = "SELECT * FROM SALA ORDER BY NUM_SALA";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Buscar por ID
    public static function buscarPorId($id) {
        $conn = Conexao::getConn();
        
        $sql = "SELECT * FROM SALA WHERE SALA_ID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // Atualizar sala
    public function atualizar() {
        $conn = Conexao::getConn();
        
        // Verificar se o número da sala já existe em outra sala
        $sqlCheck = "SELECT COUNT(*) as total FROM SALA WHERE NUM_SALA = :numSala AND SALA_ID != :id";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindValue(':numSala', $this->numSala);
        $stmtCheck->bindValue(':id', $this->id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->fetch();
        
        if ($resultCheck['total'] > 0) {
            return false; // Número de sala já existe em outra sala
        }
        
        $sql = "UPDATE SALA 
                SET NUM_SALA = :numSala, 
                    QTD_PESSOAS = :qtdPessoas, 
                    DESCRICAO = :descricao,
                    TIPO_SALA = :tipoSala,
                    SETOR_RESPONSAVEL = :setorResponsavel
                WHERE SALA_ID = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':numSala', $this->numSala);
        $stmt->bindValue(':qtdPessoas', $this->qtdPessoas);
        $stmt->bindValue(':descricao', $this->descricao);
        $stmt->bindValue(':tipoSala', $this->tipoSala ?? '');
        $stmt->bindValue(':setorResponsavel', $this->setorResponsavel ?? '');
        $stmt->bindValue(':id', $this->id);
        
        return $stmt->execute();
    }
    
    // Excluir sala (apenas se não tiver reservas)
    public static function excluir($id) {
        $conn = Conexao::getConn();
        
        // Verificar se a sala tem reservas
        $sqlCheck = "SELECT COUNT(*) as total FROM RESERVA_SALA WHERE SALA_ID = :id";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindValue(':id', $id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->fetch();
        
        if ($resultCheck['total'] > 0) {
            return false; // Sala tem reservas, não pode ser excluída
        }
        
        $sql = "DELETE FROM SALA WHERE SALA_ID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        
        return $stmt->execute();
    }
}
