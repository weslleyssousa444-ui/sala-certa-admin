<?php
require_once '../config/config.php';
require_once '../classes/Reserva.php';
require_once '../includes/alert.php';

// Verificar se está logado
requireLogin();

// Excluir reserva
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id = $_GET['excluir'];

    if (Reserva::excluir($id)) {
        $successMsg = "Reserva excluída com sucesso!";
    } else {
        $errorMsg = "Erro ao excluir a reserva.";
    }
}

// Listar todas as reservas
$reservas = Reserva::listarTodas();

$pageTitle = 'Reservas';
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h2>Gerenciar Reservas</h2>
    <div class="page-actions">
        <a href="nova_reserva.php" class="btn-gold"><i class="fas fa-plus me-2"></i>Nova Reserva</a>
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
                    <th>Usuário</th>
                    <th>Sala</th>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Duração</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['RESERVA_ID']) ?></td>
                    <td><?= htmlspecialchars($r['USUARIO_NOME']) ?></td>
                    <td>Sala <?= htmlspecialchars($r['NUM_SALA']) ?></td>
                    <td><?= date('d/m/Y', strtotime($r['DATA_RESERVA'])) ?></td>
                    <td><?= date('H:i', strtotime($r['HORA_INICIO'])) ?></td>
                    <td><?= date('H:i', strtotime($r['TEMPO_RESERVA'])) ?></td>
                    <td>
                        <span class="badge-status badge-<?= strtolower($r['ESTADO']) ?>"><?= htmlspecialchars($r['ESTADO']) ?></span>
                        <?php if (!empty($r['RESERVA_PAI_ID'])): ?>
                            <span class="badge-recorrente"><i class="fas fa-sync-alt"></i> Recorrente</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="actions-cell">
                            <a href="ver_reserva.php?id=<?= $r['RESERVA_ID'] ?>" class="btn-action btn-action-view" title="Ver"><i class="fas fa-eye"></i></a>
                            <a href="editar_reserva.php?id=<?= $r['RESERVA_ID'] ?>" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-pencil-alt"></i></a>
                            <a href="?excluir=<?= $r['RESERVA_ID'] ?>" class="btn-action btn-action-delete btn-delete" title="Excluir"><i class="fas fa-trash"></i></a>
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
    <?php foreach ($reservas as $r): ?>
    <div class="mobile-card">
        <div class="mobile-card-row">
            <span class="mobile-card-label">ID</span>
            <span class="mobile-card-value"><?= htmlspecialchars($r['RESERVA_ID']) ?></span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Usuário</span>
            <span class="mobile-card-value"><?= htmlspecialchars($r['USUARIO_NOME']) ?></span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Sala</span>
            <span class="mobile-card-value">Sala <?= htmlspecialchars($r['NUM_SALA']) ?></span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Data</span>
            <span class="mobile-card-value"><?= date('d/m/Y', strtotime($r['DATA_RESERVA'])) ?></span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Hora</span>
            <span class="mobile-card-value"><?= date('H:i', strtotime($r['HORA_INICIO'])) ?></span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Duração</span>
            <span class="mobile-card-value"><?= date('H:i', strtotime($r['TEMPO_RESERVA'])) ?></span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Status</span>
            <span class="mobile-card-value">
                <span class="badge-status badge-<?= strtolower($r['ESTADO']) ?>"><?= htmlspecialchars($r['ESTADO']) ?></span>
                <?php if (!empty($r['RESERVA_PAI_ID'])): ?>
                    <span class="badge-recorrente"><i class="fas fa-sync-alt"></i> Recorrente</span>
                <?php endif; ?>
            </span>
        </div>
        <div class="mobile-card-actions">
            <a href="ver_reserva.php?id=<?= $r['RESERVA_ID'] ?>" class="btn-action btn-action-view" title="Ver"><i class="fas fa-eye"></i></a>
            <a href="editar_reserva.php?id=<?= $r['RESERVA_ID'] ?>" class="btn-action btn-action-edit" title="Editar"><i class="fas fa-pencil-alt"></i></a>
            <a href="?excluir=<?= $r['RESERVA_ID'] ?>" class="btn-action btn-action-delete btn-delete" title="Excluir"><i class="fas fa-trash"></i></a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Empty State -->
<?php if (empty($reservas)): ?>
<div class="empty-state">
    <div class="empty-state-icon"><i class="fas fa-calendar-times"></i></div>
    <div class="empty-state-title">Nenhum registro encontrado</div>
    <a href="nova_reserva.php" class="btn-gold">Criar nova reserva</a>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
