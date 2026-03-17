<?php
require_once '../config/config.php';
require_once '../classes/Reserva.php';
require_once '../includes/alert.php';

// Verificar se está logado
requireLogin();

// Verificar se ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: reservas.php');
    exit;
}

$id = $_GET['id'];
$reserva = Reserva::buscarPorId($id);

// Verificar se a reserva existe
if (!$reserva) {
    header('Location: reservas.php');
    exit;
}

// Cancelar reserva
$success = '';
$error = '';

if (isset($_GET['cancelar']) && $_GET['cancelar'] == 'true') {
    $reservaObj = new Reserva();
    $reservaObj->setId($id);

    if ($reservaObj->cancelar()) {
        $success = 'Reserva cancelada com sucesso!';
        // Recarregar a reserva
        $reserva = Reserva::buscarPorId($id);
    } else {
        $error = 'Erro ao cancelar a reserva.';
    }
}

$pageTitle = 'Detalhes da Reserva';
include '../includes/header.php';
?>

<style>
.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(209, 213, 219, 0.3);
}
.detail-item:last-child {
    border-bottom: none;
}
.detail-label {
    font-size: 0.875rem;
    color: #6b7280;
}
.detail-value {
    font-weight: 600;
}
</style>

<div class="page-header">
    <h2>Detalhes da Reserva</h2>
    <div class="page-actions">
        <a href="reservas.php" class="btn-gray"><i class="fas fa-arrow-left me-2"></i>Voltar</a>
        <?php if ($reserva['ESTADO'] == 'Ativa'): ?>
            <a href="editar_reserva.php?id=<?= $reserva['RESERVA_ID'] ?>" class="btn-gold-outline"><i class="fas fa-edit me-2"></i>Editar</a>
            <a href="ver_reserva.php?id=<?= $reserva['RESERVA_ID'] ?>&cancelar=true" class="btn-danger-outline btn-delete"><i class="fas fa-times-circle me-2"></i>Cancelar</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($success): ?>
    <?php showAlert($success, 'success'); ?>
<?php endif; ?>

<?php if ($error): ?>
    <?php showAlert($error, 'danger'); ?>
<?php endif; ?>

<div class="sc-card">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px">
        <!-- Left: Reservation Info -->
        <div>
            <h5 style="color:#c9a84c; margin-bottom:16px">Informações da Reserva</h5>
            <div class="detail-item">
                <span class="detail-label">ID</span>
                <span class="detail-value">#<?= $reserva['RESERVA_ID'] ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Usuário</span>
                <span class="detail-value"><?= htmlspecialchars($reserva['USUARIO_NOME']) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Data</span>
                <span class="detail-value"><?= date('d/m/Y', strtotime($reserva['DATA_RESERVA'])) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Hora Início</span>
                <span class="detail-value"><?= date('H:i', strtotime($reserva['HORA_INICIO'])) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Duração</span>
                <span class="detail-value"><?= date('H:i', strtotime($reserva['TEMPO_RESERVA'])) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Status</span>
                <span class="badge-status badge-<?= strtolower($reserva['ESTADO']) ?>"><?= $reserva['ESTADO'] ?></span>
            </div>
            <?php if (!empty($reserva['RESERVA_PAI_ID'])): ?>
            <div class="detail-item">
                <span class="detail-label">Recorrência</span>
                <span class="badge-recorrente"><i class="fas fa-sync-alt"></i> Recorrente</span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: Room Info -->
        <div>
            <h5 style="color:#c9a84c; margin-bottom:16px">Informações da Sala</h5>
            <div class="detail-item">
                <span class="detail-label">Sala</span>
                <span class="detail-value"><?= htmlspecialchars($reserva['NUM_SALA']) ?></span>
            </div>
        </div>
    </div>

    <!-- End time calculation -->
    <?php
    $horaInicio = new DateTime($reserva['HORA_INICIO']);
    $tempoReserva = new DateTime($reserva['TEMPO_RESERVA']);
    $horaFim = clone $horaInicio;
    $horaFim->add(new DateInterval('PT' . $tempoReserva->format('H') . 'H' . $tempoReserva->format('i') . 'M'));
    ?>
    <div class="sc-alert sc-alert-info" style="margin-top:24px">
        <i class="fas fa-info-circle"></i>
        <span><strong>Término:</strong> <?= $horaFim->format('H:i') ?> | <strong>Duração:</strong> <?= $tempoReserva->format('H:i') ?>h</span>
    </div>

    <!-- If recurrent, show series link -->
    <?php if (!empty($reserva['RESERVA_PAI_ID'])): ?>
    <div style="margin-top:16px">
        <a href="reservas.php?serie=<?= $reserva['RESERVA_PAI_ID'] ?>" class="btn-gold-outline">
            <i class="fas fa-list me-2"></i>Ver todas da série
        </a>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
