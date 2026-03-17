<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Usuario.php';
require_once __DIR__ . '/../includes/alert.php';

requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'] ?? '';
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
    $tipoUsuario = filter_input(INPUT_POST, 'tipo_usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $departamento = filter_input(INPUT_POST, 'departamento', FILTER_SANITIZE_SPECIAL_CHARS);
    $cargo = filter_input(INPUT_POST, 'cargo', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($nome) || empty($email) || empty($senha) || empty($cpf)) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido.';
    } else if (strlen($cpf) != 11) {
        $error = 'CPF inválido. Informe apenas os 11 dígitos numéricos.';
    } else if (strlen($senha) < 6) {
        $error = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        $usuario = new Usuario();
        $usuario->setNome($nome);
        $usuario->setEmail($email);
        $usuario->setSenha($senha);
        $usuario->setCpf($cpf);
        $usuario->setTipoUsuario($tipoUsuario ?? 'comum');
        $usuario->setUsuarioDepartamento($departamento ?? '');
        $usuario->setUsuarioCargo($cargo ?? '');

        if ($usuario->cadastrar()) {
            $success = 'Usuário cadastrado com sucesso!';
            $nome = $email = $cpf = $tipoUsuario = $departamento = $cargo = '';
        } else {
            $error = 'Erro ao cadastrar usuário. Verifique se o e-mail já está em uso.';
        }
    }
}

$pageTitle = 'Novo Usuário';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h2>Cadastrar Novo Usuário</h2>
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
                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($nome ?? '') ?>" required>
            </div>
            <div>
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email ?? '') ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label class="form-label">Senha *</label>
                <input type="password" name="senha" class="form-control" required minlength="6" placeholder="Mínimo 6 caracteres">
            </div>
            <div>
                <label class="form-label">CPF *</label>
                <input type="text" name="cpf" id="cpf" class="form-control" value="<?= htmlspecialchars($cpf ?? '') ?>" required maxlength="11" placeholder="Apenas números (11 dígitos)">
            </div>
        </div>

        <div class="form-row">
            <div>
                <label class="form-label">Tipo de Usuário *</label>
                <select name="tipo_usuario" class="form-select" required>
                    <option value="">Selecione o tipo</option>
                    <option value="comum" <?= (isset($tipoUsuario) && $tipoUsuario == 'comum') ? 'selected' : '' ?>>Usuário Comum</option>
                    <option value="admin" <?= (isset($tipoUsuario) && $tipoUsuario == 'admin') ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>
            <div>
                <label class="form-label">Departamento</label>
                <input type="text" name="departamento" class="form-control" value="<?= htmlspecialchars($departamento ?? '') ?>" placeholder="Ex: Tecnologia da Informação">
            </div>
        </div>

        <div class="form-row">
            <div>
                <label class="form-label">Cargo</label>
                <input type="text" name="cargo" class="form-control" value="<?= htmlspecialchars($cargo ?? '') ?>" placeholder="Ex: Analista de Sistemas">
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
document.getElementById('cpf').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    e.target.value = value;
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
