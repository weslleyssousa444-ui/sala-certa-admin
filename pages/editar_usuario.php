<?php
require_once '../config/config.php';
require_once '../classes/Usuario.php';
require_once '../includes/alert.php';

// Verificar se está logado
requireLogin();

// Verificar se ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: usuarios.php');
    exit;
}

$id = $_GET['id'];
$usuario_dados = Usuario::buscarPorId($id);

// Verificar se o usuário existe
if (!$usuario_dados) {
    header('Location: usuarios.php');
    exit;
}

$error = '';
$success = '';

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
    $curso = filter_input(INPUT_POST, 'curso', FILTER_SANITIZE_STRING);
    $senha = $_POST['senha'] ?? '';
    
    // Validar dados
    if (empty($nome) || empty($email) || empty($cpf) || empty($curso)) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } else if (strlen($cpf) != 11) {
        $error = 'CPF inválido.';
    } else if (!empty($senha) && strlen($senha) < 6) {
        $error = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        // Atualizar usuário
        $usuario = new Usuario();
        $usuario->setId($id);
        $usuario->setNome($nome);
        $usuario->setEmail($email);
        $usuario->setCpf($cpf);
        $usuario->setCurso($curso);
        
        // Se uma nova senha foi fornecida, atualizá-la
        if (!empty($senha)) {
            $usuario->setSenha($senha);
            
            // Atualizar a senha separadamente
            $conn = Conexao::getConn();
            $sql = "UPDATE USUARIO SET USUARIO_SENHA = :senha WHERE USUARIO_ID = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':senha', $usuario->getSenha());
            $stmt->bindValue(':id', $id);
            $stmt->execute();
        }
        
        if ($usuario->atualizar()) {
            $success = 'Usuário atualizado com sucesso!';
            // Recarregar os dados do usuário
            $usuario_dados = Usuario::buscarPorId($id);
        } else {
            $error = 'Erro ao atualizar usuário.';
        }
    }
}

$pageTitle = 'Editar Usuário';
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Editar Usuário</h2>
            <a href="usuarios.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
        
        <?php if ($error): ?>
            <?php showAlert($error, 'danger'); ?>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <?php showAlert($success, 'success'); ?>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Formulário de Edição</h5>
            </div>
            <div class="card-body">
                <form method="post" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nome" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $usuario_dados['USUARIO_NOME']; ?>" required>
                            <div class="invalid-feedback">
                                Por favor, informe o nome completo.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $usuario_dados['USUARIO_EMAIL']; ?>" required>
                            <div class="invalid-feedback">
                                Por favor, informe um email válido.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="senha" class="form-label">Nova Senha (deixe em branco para manter a atual)</label>
                            <input type="password" class="form-control" id="senha" name="senha" minlength="6">
                            <div class="invalid-feedback">
                                A senha deve ter pelo menos 6 caracteres.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="cpf" class="form-label">CPF</label>
                            <input type="text" class="form-control cpf-mask" id="cpf" name="cpf" value="<?php echo $usuario_dados['USUARIO_CPF']; ?>" required>
                            <div class="invalid-feedback">
                                Por favor, informe um CPF válido.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="curso" class="form-label">Curso</label>
                        <input type="text" class="form-control" id="curso" name="curso" value="<?php echo $usuario_dados['USUARIO_CURSO']; ?>" required>
                        <div class="invalid-feedback">
                            Por favor, informe o curso.
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Atualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $.fn.mask !== 'undefined') {
        $('.cpf-mask').mask('000.000.000-00', {reverse: true});
    }
});
</script>

<?php include '../includes/footer.php'; ?>


