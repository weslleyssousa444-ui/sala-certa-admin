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

$pageTitle = 'Salas';
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gerenciar Salas</h2>
            <a href="nova_sala.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nova Sala
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
                <h5 class="card-title mb-0">Lista de Salas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Número</th>
                                <th>Capacidade</th>
                                <th>Tipo</th>
                                <th>Setor Responsável</th>
                                <th>Descrição</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salas as $sala): ?>
                            <tr>
                                <td><?php echo $sala['SALA_ID']; ?></td>
                                <td>Sala <?php echo $sala['NUM_SALA']; ?></td>
                                <td><?php echo $sala['QTD_PESSOAS']; ?> pessoas</td>
                                <td>
                                    <?php 
                                    $tipoSala = isset($sala['TIPO_SALA']) ? $sala['TIPO_SALA'] : '';
                                    switch($tipoSala) {
                                        case 'aula':
                                            echo '<span class="badge bg-primary">Sala de Aula</span>';
                                            break;
                                        case 'laboratorio':
                                            echo '<span class="badge bg-info">Laboratório</span>';
                                            break;
                                        case 'reuniao':
                                            echo '<span class="badge bg-success">Sala de Reunião</span>';
                                            break;
                                        case 'auditorio':
                                            echo '<span class="badge bg-warning">Auditório</span>';
                                            break;
                                        default:
                                            echo '<span class="text-muted">Não definido</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo isset($sala['SETOR_RESPONSAVEL']) ? ucfirst($sala['SETOR_RESPONSAVEL']) : '<span class="text-muted">Não definido</span>'; ?></td>
                                <td><?php echo $sala['DESCRICAO']; ?></td>
                                <td>
                                    <a href="editar_sala.php?id=<?php echo $sala['SALA_ID']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="salas.php?excluir=<?php echo $sala['SALA_ID']; ?>" class="btn btn-sm btn-danger btn-delete" data-bs-toggle="tooltip" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($salas)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Nenhuma sala encontrada</td>
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


