<?php
require_once '../config/config.php';
require_once '../classes/Reserva.php';
require_once '../includes/alert.php';

// Verificar se está logado
requireLogin();

// Data padrão (hoje)
$dataBusca = date('Y-m-d');

// Se foi enviada uma data via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataPost = filter_input(INPUT_POST, 'data_busca', FILTER_SANITIZE_SPECIAL_CHARS);
    if (!empty($dataPost)) {
        $dataBusca = date('Y-m-d', strtotime(str_replace('/', '-', $dataPost)));
    }
}

// Se foi enviada uma data via GET
if (isset($_GET['data'])) {
    $dataGet = filter_input(INPUT_GET, 'data', FILTER_SANITIZE_SPECIAL_CHARS);
    if (!empty($dataGet)) {
        $dataBusca = date('Y-m-d', strtotime($dataGet));
    }
}

// Buscar reservas da data
$reservas = Reserva::listarPorData($dataBusca);

$pageTitle = 'Reservas por Data';
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h2>Reservas por Data</h2>
    <div class="page-actions">
        <a href="nova_reserva.php" class="btn-gold"><i class="fas fa-plus me-2"></i>Nova Reserva</a>
    </div>
</div>

<!-- Date Filter -->
<div class="sc-filters">
    <form method="post" class="sc-search">
        <div class="sc-search-group">
            <label for="data_busca" class="sc-search-label">Filtrar por Data</label>
            <input type="text" class="sc-search-input datepicker date-mask" id="data_busca" name="data_busca"
                   value="<?= date('d/m/Y', strtotime($dataBusca)) ?>" placeholder="dd/mm/aaaa">
        </div>
        <div class="sc-search-actions">
            <button type="submit" class="btn-gold">
                <i class="fas fa-search me-2"></i>Buscar
            </button>
            <a href="reservas_por_data.php" class="btn-outline-gold">
                <i class="fas fa-redo me-2"></i>Hoje
            </a>
        </div>
    </form>
</div>

<!-- Result count header -->
<div class="page-header" style="margin-top:1.5rem;">
    <h3 style="font-size:1rem;font-weight:600;">
        Reservas do dia <?= date('d/m/Y', strtotime($dataBusca)) ?>
        <span class="badge-status badge-ativa" style="margin-left:.5rem;"><?= count($reservas) ?></span>
    </h3>
</div>

<!-- Desktop Table -->
<div class="sc-table-responsive">
    <div class="sc-table-wrapper">
        <table class="sc-table" id="dataTable">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Sala</th>
                    <th>Usuário</th>
                    <th>Duração</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $r): ?>
                <tr>
                    <td><strong><?= date('H:i', strtotime($r['HORA_INICIO'])) ?></strong></td>
                    <td>Sala <?= htmlspecialchars($r['NUM_SALA']) ?></td>
                    <td><?= htmlspecialchars($r['USUARIO_NOME']) ?></td>
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
            <span class="mobile-card-label">Hora</span>
            <span class="mobile-card-value"><strong><?= date('H:i', strtotime($r['HORA_INICIO'])) ?></strong></span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Sala</span>
            <span class="mobile-card-value">Sala <?= htmlspecialchars($r['NUM_SALA']) ?></span>
        </div>
        <div class="mobile-card-row">
            <span class="mobile-card-label">Usuário</span>
            <span class="mobile-card-value"><?= htmlspecialchars($r['USUARIO_NOME']) ?></span>
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
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Empty State -->
<?php if (empty($reservas)): ?>
<div class="empty-state">
    <div class="empty-state-icon"><i class="fas fa-calendar-day"></i></div>
    <div class="empty-state-title">Nenhum registro encontrado</div>
    <a href="nova_reserva.php" class="btn-gold">Criar nova reserva</a>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar jQuery Mask Plugin
    if (typeof $.fn.mask !== 'undefined') {
        $('.date-mask').mask('00/00/0000');
    }

    // Inicializar datepicker
    if (typeof $.fn.datepicker !== 'undefined') {
        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
            language: 'pt-BR',
            autoclose: true,
            todayHighlight: true
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
