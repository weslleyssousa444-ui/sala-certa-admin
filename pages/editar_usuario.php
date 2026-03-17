<?php
require_once '../config/config.php';
require_once '../classes/Usuario.php';
require_once '../includes/alert.php';

requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: usuarios.php');
    exit;
}

$id = $_GET['id'];
$usuario_dados = Usuario::buscarPorId($id);

if (!$usuario_dados) {
    header('Location: usuarios.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
    $tipoUsuario = filter_input(INPUT_POST, 'tipo_usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $departamento = filter_input(INPUT_POST, 'departamento', FILTER_SANITIZE_SPECIAL_CHARS);
    $cargo = filter_input(INPUT_POST, 'cargo', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha = $_POST['senha'] ?? '';

    if (empty($nome) || empty($email) || empty($cpf)) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } else if (strlen($cpf) != 11) {
        $error = 'CPF inválido.';
    } else if (!empty($senha) && strlen($senha) < 6) {
        $error = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        $usuario = new Usuario();
        $usuario->setId($id);
        $usuario->setNome($nome);
        $usuario->setEmail($email);
        $usuario->setCpf($cpf);
        $usuario->setTipoUsuario($tipoUsuario ?? $usuario_dados['TIPO_USUARIO'] ?? 'comum');
        $usuario->setUsuarioDepartamento($departamento ?? '');
        $usuario->setUsuarioCargo($cargo ?? '');

        // Se uma nova senha foi fornecida, atualizá-la
        if (!empty($senha)) {
            $usuario->setSenha($senha);

            $conn = Conexao::getConn();
            $sql = "UPDATE USUARIO SET USUARIO_SENHA = :senha WHERE USUARIO_ID = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':senha', $usuario->getSenha());
            $stmt->bindValue(':id', $id);
            $stmt->execute();
        }

        if ($usuario->atualizar()) {
            $success = 'Usuário atualizado com sucesso!';
            $usuario_dados = Usuario::buscarPorId($id);
        } else {
            $error = 'Erro ao atualizar usuário.';
        }
    }
}

$pageTitle = 'Editar Usuário';
include '../includes/header.php';
?>

<div class="page-header">
    <h2>Editar Usuário</h2>
    <a href="usuarios.php" class="btn-gray"><i class="fas fa-arrow-left me-2"></i>Voltar</a>
</div>

<?php if ($error): ?>
    <div class="sc-alert sc-alert-danger"><i class="fas fa-exclamation-circle"></i><span><?= $error ?></span></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="sc-alert sc-alert-success"><i class="fas fa-check-circle"></i><span><?= $success ?></span></div>
<?php endif; ?>

<div class="sc-card">
    <form method="post" class="sc-form">
        <div class="form-row">
            <div>
                <label class="form-label">Nome Completo *</label>
                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($usuario_dados['USUARIO_NOME']) ?>" required>
            </div>
            <div>
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario_dados['USUARIO_EMAIL']) ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label class="form-label">Nova Senha <small style="font-weight:400;">(deixe em branco para manter)</small></label>
                <input type="password" name="senha" class="form-control" minlength="6" placeholder="Mínimo 6 caracteres">
            </div>
            <div>
                <label class="form-label">CPF *</label>
                <input type="text" name="cpf" id="cpf" class="form-control cpf-mask" value="<?= htmlspecialchars($usuario_dados['USUARIO_CPF']) ?>" required maxlength="11" placeholder="Apenas números">
            </div>
        </div>

        <div class="form-row">
            <div>
                <label class="form-label">Tipo de Usuário</label>
                <select name="tipo_usuario" class="form-select">
                    <option value="comum" <?= ($usuario_dados['TIPO_USUARIO'] == 'comum') ? 'selected' : '' ?>>Usuário Comum</option>
                    <option value="admin" <?= ($usuario_dados['TIPO_USUARIO'] == 'admin') ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>
            <div>
                <label class="form-label">Departamento</label>
                <input type="text" name="departamento" class="form-control" value="<?= htmlspecialchars($usuario_dados['USUARIO_DEPARTAMENTO'] ?? '') ?>" placeholder="Ex: Tecnologia da Informação">
            </div>
        </div>

        <div class="form-row">
            <div>
                <label class="form-label">Cargo</label>
                <input type="text" name="cargo" class="form-control" value="<?= htmlspecialchars($usuario_dados['USUARIO_CARGO'] ?? '') ?>" placeholder="Ex: Analista de Sistemas">
            </div>
            <div></div>
        </div>

        <div class="form-actions">
            <a href="usuarios.php" class="btn-gray">Cancelar</a>
            <button type="submit" class="btn-gold"><i class="fas fa-save me-2"></i>Salvar</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('cpf').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });
});
</script>

<?php include '../includes/footer.php'; ?>
