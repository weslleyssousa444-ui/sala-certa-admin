<?php
require_once '../config/config.php';
require_once '../classes/Usuario.php';
require_once '../includes/alert.php';

// Verificar se está logado
requireLogin();

// Excluir usuário
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id = $_GET['excluir'];

    // Não permitir excluir o próprio usuário
    if ($id == $_SESSION['usuario_id']) {
        $errorMsg = "Você não pode excluir seu próprio usuário.";
    } else {
        if (Usuario::excluir($id)) {
            $successMsg = "Usuário excluído com sucesso!";
        } else {
            $errorMsg = "Erro ao excluir o usuário.";
        }
    }
}

// Listar todos os usuários
$usuarios = Usuario::listarTodos();

$pageTitle = 'Usuários';
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h2>Gerenciar Usuários</h2>
    <div class="page-actions">
        <a href="novo_usuario.php" class="btn-gold"><i class="fas fa-user-plus me-2"></i>Novo Usuário</a>
    </div>
</div>

<?php if (isset($successMsg)): ?>
    <?php showAlert($successMsg, 'success'); ?>
<?php endif; ?>

<?php if (isset($errorMsg)): ?>
    <?php showAlert($errorMsg, 'danger'); ?>
<?php endif; ?>

<!-- Desktop Table -->
<div class="sc-table-responsive">
    <div class="sc-table-wrapper">
        <table class="sc-table" id="dataTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Departamento</th>
                    <th>Cargo</th>
                    <th>Tipo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                <?php
                    $isSubfolder = (basename(dirname($_SERVER['SCRIPT_FILENAME'])) === 'pages');
                    $baseUrl = $isSubfolder ? '..' : '.';
                    $tipo = $usuario['TIPO_USUARIO'] ?? 'comum';
                ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['USUARIO_ID']) ?></td>
                    <td>
                        <?php if (!empty($usuario['FOTO_PERFIL']) && file_exists($usuario['FOTO_PERFIL'])): ?>
                            <img src="<?= htmlspecialchars($usuario['FOTO_PERFIL']) ?>" alt="" class="user-avatar-sm" style="width:28px;height:28px;border-radius:50%;object-fit:cover;margin-right:6px;vertical-align:middle;">
                        <?php endif; ?>
                        <?= htmlspecialchars($usuario['USUARIO_NOME']) ?>
                    </td>
                    <td><?= htmlspecialchars($usuario['USUARIO_EMAIL']) ?></td>
                    <td><?= !empty($usuario['USUARIO_DEPARTAMENTO']) ? htmlspecialchars($usuario['USUARIO_DEPARTAMENTO']) : '<span class="text-muted">—</span>' ?></td>
                    <td><?= !empty($usuario['USUARIO_CARGO']) ? htmlspecialchars($usuario['USUARIO_CARGO']) : '<span class="text-muted">—</span>' ?></td>
                    <td>
                        <span class="badge-status <?= $tipo === 'admin' ? 'badge-ativa' : 'badge-pendente' ?>">
                            <?= ucfirst(htmlspecialchars($tipo)) ?>
                        </span>
                    </td>
                    <td>
                        <div class="actions-cell">
                            <a href="ver_usuario.php?id=<?= $usuario['USUARIO_ID'] ?>" class="btn-action btn-action-view" title="Ver"><i class="fas fa-eye"></i></a>
                            <a href="editar_usuario.php?id=<?= $usuario['USUARIO_ID'] ?>" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-pencil-alt"></i></a>
                            <?php if ($usuario['USUARIO_ID'] != $_SESSION['usuario_id']): ?>
                            <a href="?excluir=<?= $usuario['USUARIO_ID'] ?>" class="btn-action btn-action-delete btn-delete" title="Excluir"><i class="fas fa-trash"></i></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Mobile Cards -->
<div class="sc-mobile-cards">
    <?php foreach ($usuarios as $usuario): ?>
    <?php $tipo = $usuario['TIPO_USUARIO'] ?? 'comum'; ?>
    <div class="mobile-card">
        <div class="mobile-card-row">
            <span class="mobile-card-label">ID</span>
            <span class="mobile-card-value"><?= htmlspecialchars($usuario['USUARIO_ID']) ?></span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Nome</span>
            <span class="mobile-card-value">
                <?php if (!empty($usuario['FOTO_PERFIL']) && file_exists($usuario['FOTO_PERFIL'])): ?>
                    <img src="<?= htmlspecialchars($usuario['FOTO_PERFIL']) ?>" alt="" style="width:24px;height:24px;border-radius:50%;object-fit:cover;margin-right:5px;vertical-align:middle;">
                <?php endif; ?>
                <?= htmlspecialchars($usuario['USUARIO_NOME']) ?>
            </span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Email</span>
            <span class="mobile-card-value"><?= htmlspecialchars($usuario['USUARIO_EMAIL']) ?></span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Departamento</span>
            <span class="mobile-card-value"><?= !empty($usuario['USUARIO_DEPARTAMENTO']) ? htmlspecialchars($usuario['USUARIO_DEPARTAMENTO']) : '—' ?></span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Cargo</span>
            <span class="mobile-card-value"><?= !empty($usuario['USUARIO_CARGO']) ? htmlspecialchars($usuario['USUARIO_CARGO']) : '—' ?></span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Tipo</span>
            <span class="mobile-card-value">
                <span class="badge-status <?= $tipo === 'admin' ? 'badge-ativa' : 'badge-pendente' ?>">
                    <?= ucfirst(htmlspecialchars($tipo)) ?>
                </span>
            </span>
        </div>
        <div class="mobile-card-actions">
            <a href="ver_usuario.php?id=<?= $usuario['USUARIO_ID'] ?>" class="btn-action btn-action-view" title="Ver"><i class="fas fa-eye"></i></a>
            <a href="editar_usuario.php?id=<?= $usuario['USUARIO_ID'] ?>" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-pencil-alt"></i></a>
            <?php if ($usuario['USUARIO_ID'] != $_SESSION['usuario_id']): ?>
            <a href="?excluir=<?= $usuario['USUARIO_ID'] ?>" class="btn-action btn-action-delete btn-delete" title="Excluir"><i class="fas fa-trash"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Empty State -->
<?php if (empty($usuarios)): ?>
<div class="empty-state">
    <div class="empty-state-icon"><i class="fas fa-users"></i></div>
    <div class="empty-state-title">Nenhum registro encontrado</div>
    <a href="novo_usuario.php" class="btn-gold">Criar novo usuário</a>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
