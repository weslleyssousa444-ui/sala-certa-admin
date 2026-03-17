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

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gerenciar Reservas</h2>
            <a href="nova_reserva.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nova Reserva
            </a>
        </div>
        
        <?php if (isset($successMsg)): ?>
            <?php showAlert($successMsg, 'success'); ?>
        <?php endif; ?>
        
        <?php if (isset($errorMsg)): ?>
            <?php showAlert($errorMsg, 'danger'); ?>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Lista de Reservas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuário</th>
                                <th>Sala</th>
                                <th>Data</th>
                                <th>Hora Início</th>
                                <th>Duração</th>
                                <th>Estado</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $reserva): ?>
                            <tr>
                                <td><?php echo $reserva['RESERVA_ID']; ?></td>
                                <td><?php echo $reserva['USUARIO_NOME']; ?></td>
                                <td>Sala <?php echo $reserva['NUM_SALA']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($reserva['DATA_RESERVA'])); ?></td>
                                <td><?php echo date('H:i', strtotime($reserva['HORA_INICIO'])); ?></td>
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
                                    <a href="reservas.php?excluir=<?php echo $reserva['RESERVA_ID']; ?>" class="btn btn-sm btn-danger btn-delete" data-bs-toggle="tooltip" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($reservas)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Nenhuma reserva encontrada</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


