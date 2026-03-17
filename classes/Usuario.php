<?php
if (!defined('TEST_MODE')) { require_once __DIR__ . '/../config/conexao.php'; }

class Usuario {
    private $id;
    private $nome;
    private $email;
    private $senha;
    private $cpf;
    private $cargo;
    private $tipoUsuario;
    private $usuarioDepartamento;

    // Getters e Setters
    public function getId() { return $this->id; }
    public function getNome() { return $this->nome; }
    public function getEmail() { return $this->email; }
    public function getSenha() { return $this->senha; }
    public function getCpf() { return $this->cpf; }
    public function getUsuarioCargo() { return $this->cargo; }
    public function getTipoUsuario() { return $this->tipoUsuario; }
    public function getUsuarioDepartamento() { return $this->usuarioDepartamento; }

    public function setId($id) { $this->id = $id; }
    public function setNome($nome) { $this->nome = $nome; }
    public function setEmail($email) { $this->email = $email; }
    public function setSenha($senha) { $this->senha = password_hash($senha, PASSWORD_DEFAULT); }
    public function setCpf($cpf) { $this->cpf = $cpf; }
    public function setUsuarioCargo($cargo) { $this->cargo = $cargo; }
    public function setTipoUsuario($tipoUsuario) { $this->tipoUsuario = $tipoUsuario; }
    public function setUsuarioDepartamento($usuarioDepartamento) { $this->usuarioDepartamento = $usuarioDepartamento; }

    // Método para login
    public function login($email, $senha) {
        $conn = Conexao::getConn();

        $sql = "SELECT * FROM USUARIO WHERE USUARIO_EMAIL = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $resultado = $stmt->fetch();

            // Verificar senha (suporta tanto hash quanto texto puro para migração)
            $senhaValida = false;
            if (password_verify($senha, $resultado['USUARIO_SENHA'])) {
                $senhaValida = true;
            } elseif ($resultado['USUARIO_SENHA'] === $senha) {
                // Senha em texto puro (para compatibilidade com sistema antigo)
                $senhaValida = true;
            }

            if ($senhaValida) {
                $this->id = $resultado['USUARIO_ID'];
                $this->nome = $resultado['USUARIO_NOME'];
                $this->email = $resultado['USUARIO_EMAIL'];
                $this->cpf = $resultado['USUARIO_CPF'];
                $this->cargo = $resultado['USUARIO_CARGO'] ?? '';
                $this->tipoUsuario = $resultado['TIPO_USUARIO'] ?? 'comum';
                $this->usuarioDepartamento = $resultado['USUARIO_DEPARTAMENTO'] ?? '';

                return true;
            }
        }

        return false;
    }

    // Método para cadastro
    public function cadastrar() {
        $conn = Conexao::getConn();

        // Verificar se o email já existe
        $sqlCheck = "SELECT COUNT(*) as total FROM USUARIO WHERE USUARIO_EMAIL = :email";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindValue(':email', $this->email);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->fetch();

        if ($resultCheck['total'] > 0) {
            return false; // Email já cadastrado
        }

        $sql = "INSERT INTO USUARIO (USUARIO_NOME, USUARIO_EMAIL, USUARIO_SENHA, USUARIO_CPF, USUARIO_CARGO, TIPO_USUARIO, USUARIO_DEPARTAMENTO)
                VALUES (:nome, :email, :senha, :cpf, :cargo, :tipoUsuario, :usuarioDepartamento)";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':nome', $this->nome);
        $stmt->bindValue(':email', $this->email);
        $stmt->bindValue(':senha', $this->senha);
        $stmt->bindValue(':cpf', $this->cpf);
        $stmt->bindValue(':cargo', $this->cargo);
        $stmt->bindValue(':tipoUsuario', $this->tipoUsuario ?? 'comum');
        $stmt->bindValue(':usuarioDepartamento', $this->usuarioDepartamento ?? '');

        if ($stmt->execute()) {
            $this->id = $conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Listar todos usuários
    public static function listarTodos() {
        $conn = Conexao::getConn();

        $sql = "SELECT * FROM USUARIO ORDER BY USUARIO_NOME";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Buscar por ID
    public static function buscarPorId($id) {
        $conn = Conexao::getConn();

        $sql = "SELECT * FROM USUARIO WHERE USUARIO_ID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    // Atualizar usuário
    public function atualizar() {
        $conn = Conexao::getConn();

        // Verificar se o email já existe em outro usuário
        $sqlCheck = "SELECT COUNT(*) as total FROM USUARIO WHERE USUARIO_EMAIL = :email AND USUARIO_ID != :id";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindValue(':email', $this->email);
        $stmtCheck->bindValue(':id', $this->id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->fetch();

        if ($resultCheck['total'] > 0) {
            return false; // Email já cadastrado para outro usuário
        }

        $sql = "UPDATE USUARIO
                SET USUARIO_NOME = :nome,
                    USUARIO_EMAIL = :email,
                    USUARIO_CPF = :cpf,
                    USUARIO_CARGO = :cargo
                WHERE USUARIO_ID = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':nome', $this->nome);
        $stmt->bindValue(':email', $this->email);
        $stmt->bindValue(':cpf', $this->cpf);
        $stmt->bindValue(':cargo', $this->cargo);
        $stmt->bindValue(':id', $this->id);

        return $stmt->execute();
    }

    // Excluir usuário (apenas se não tiver reservas)
    public static function excluir($id) {
        $conn = Conexao::getConn();

        // Verificar se o usuário tem reservas
        $sqlCheck = "SELECT COUNT(*) as total FROM RESERVA_SALA WHERE USUARIO_ID = :id";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindValue(':id', $id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->fetch();

        if ($resultCheck['total'] > 0) {
            return false; // Usuário tem reservas, não pode ser excluído
        }

        $sql = "DELETE FROM USUARIO WHERE USUARIO_ID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }
}
