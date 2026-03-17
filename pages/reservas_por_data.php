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

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Reservas por Data</h2>
            <a href="nova_reserva.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nova Reserva
            </a>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Filtrar por Data</h5>
            </div>
            <div class="card-body">
                <form method="post" class="row g-3">
                    <div class="col-md-4">
                        <label for="data_busca" class="form-label">Data</label>
                        <input type="text" class="form-control datepicker date-mask" id="data_busca" name="data_busca" value="<?php echo date('d/m/Y', strtotime($dataBusca)); ?>">
                    </div>
                    <div class="col-md-8 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                        <a href="reservas_por_data.php" class="btn btn-secondary">
                            <i class="fas fa-redo me-2"></i>Hoje
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    Reservas do dia <?php echo date('d/m/Y', strtotime($dataBusca)); ?>
                    <span class="badge bg-primary ms-2"><?php echo count($reservas); ?></span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (count($reservas) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Sala</th>
                                <th>Usuário</th>
                                <th>Duração</th>
                                <th>Estado</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $reserva): ?>
                            <tr>
                                <td><strong><?php echo date('H:i', strtotime($reserva['HORA_INICIO'])); ?></strong></td>
                                <td>Sala <?php echo $reserva['NUM_SALA']; ?></td>
                                <td><?php echo $reserva['USUARIO_NOME']; ?></td>
                                <td><?php echo date('H:i', strtotime($reserva['TEMPO_RESERVA'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $reserva['ESTADO'] == 'Ativa' ? 'success' : 'danger'; ?>">
                                        <?php echo $reserva['ESTADO']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="ver_reserva.php?id=<?php echo $reserva['RESERVA_ID']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Ver detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="editar_reserva.php?id=<?php echo $reserva['RESERVA_ID']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Não há reservas para esta data.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

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

