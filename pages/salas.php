<?php
require_once '../config/config.php';
require_once '../classes/Sala.php';
require_once '../includes/alert.php';

// Verificar se está logado
requireLogin();

// Excluir sala
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id = $_GET['excluir'];

    if (Sala::excluir($id)) {
        $successMsg = "Sala excluída com sucesso!";
    } else {
        $errorMsg = "Erro ao excluir a sala.";
    }
}

// Listar todas as salas
$salas = Sala::listarTodas();

// Helper: derivar andar a partir do número da sala
function getAndar($numSala) {
    $num = strtoupper((string)$numSala);
    if (str_starts_with($num, 'T')) return 'Térreo';
    if (str_starts_with($num, '1')) return '1º Andar';
    if (str_starts_with($num, '2')) return '2º Andar';
    return 'Outros';
}

// Helper: label do tipo de sala
function getTipoLabel($tipo) {
    $labels = [
        'aula'        => 'Sala de Aula',
        'laboratorio' => 'Laboratório',
        'reuniao'     => 'Sala de Reunião',
        'auditorio'   => 'Auditório',
    ];
    return $labels[$tipo] ?? 'Não definido';
}

$pageTitle = 'Salas';
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h2>Gerenciar Salas</h2>
    <div class="page-actions">
        <a href="nova_sala.php" class="btn-gold"><i class="fas fa-plus me-2"></i>Nova Sala</a>
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
                    <th>Número</th>
                    <th>Tipo</th>
                    <th>Capacidade</th>
                    <th>Andar</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salas as $sala): ?>
                <tr>
                    <td><?= htmlspecialchars($sala['SALA_ID']) ?></td>
                    <td>Sala <?= htmlspecialchars($sala['NUM_SALA']) ?></td>
                    <td><?= htmlspecialchars(getTipoLabel($sala['TIPO_SALA'] ?? '')) ?></td>
                    <td><?= htmlspecialchars($sala['QTD_PESSOAS']) ?> pessoas</td>
                    <td><?= getAndar($sala['NUM_SALA']) ?></td>
                    <td>
                        <div class="actions-cell">
                            <a href="ver_sala.php?id=<?= $sala['SALA_ID'] ?>" class="btn-action btn-action-view" title="Ver"><i class="fas fa-eye"></i></a>
                            <a href="editar_sala.php?id=<?= $sala['SALA_ID'] ?>" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-pencil-alt"></i></a>
                            <a href="?excluir=<?= $sala['SALA_ID'] ?>" class="btn-action btn-action-delete btn-delete" title="Excluir"><i class="fas fa-trash"></i></a>
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
    <?php foreach ($salas as $sala): ?>
    <div class="mobile-card">
        <div class="mobile-card-row">
            <span class="mobile-card-label">ID</span>
            <span class="mobile-card-value"><?= htmlspecialchars($sala['SALA_ID']) ?></span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Número</span>
            <span class="mobile-card-value">Sala <?= htmlspecialchars($sala['NUM_SALA']) ?></span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Tipo</span>
            <span class="mobile-card-value"><?= htmlspecialchars(getTipoLabel($sala['TIPO_SALA'] ?? '')) ?></span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Capacidade</span>
            <span class="mobile-card-value"><?= htmlspecialchars($sala['QTD_PESSOAS']) ?> pessoas</span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Andar</span>
            <span class="mobile-card-value"><?= getAndar($sala['NUM_SALA']) ?></span>
        </div>
        <div class="mobile-card-actions">
            <a href="ver_sala.php?id=<?= $sala['SALA_ID'] ?>" class="btn-action btn-action-view" title="Ver"><i class="fas fa-eye"></i></a>
            <a href="editar_sala.php?id=<?= $sala['SALA_ID'] ?>" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-pencil-alt"></i></a>
            <a href="?excluir=<?= $sala['SALA_ID'] ?>" class="btn-action btn-action-delete btn-delete" title="Excluir"><i class="fas fa-trash"></i></a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Empty State -->
<?php if (empty($salas)): ?>
<div class="empty-state">
    <div class="empty-state-icon"><i class="fas fa-door-open"></i></div>
    <div class="empty-state-title">Nenhum registro encontrado</div>
    <a href="nova_sala.php" class="btn-gold">Criar nova sala</a>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
