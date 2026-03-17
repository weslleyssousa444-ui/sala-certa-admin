<?php
require_once '../config/config.php';
require_once '../classes/Usuario.php';
require_once '../classes/Reserva.php';
require_once '../includes/alert.php';

// Verificar se está logado
requireLogin();

// Verificar se ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: usuarios.php');
    exit;
}

$id = $_GET['id'];
$usuario = Usuario::buscarPorId($id);

// Verificar se o usuário existe
if (!$usuario) {
    header('Location: usuarios.php');
    exit;
}

// Buscar as reservas deste usuário
$reservas = Reserva::listarPorUsuario($id);

// Contar reservas ativas
$reservas_ativas = 0;
$reservas_canceladas = 0;

foreach ($reservas as $reserva) {
    if ($reserva['ESTADO'] == 'Ativa') {
        $reservas_ativas++;
    } else {
        $reservas_canceladas++;
    }
}

$pageTitle = 'Detalhes do Usuário';
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Detalhes do Usuário</h2>
            <div>
                <a href="usuarios.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
                <a href="editar_usuario.php?id=<?php echo $usuario['USUARIO_ID']; ?>" class="btn btn-warning ms-2">
                    <i class="fas fa-edit me-2"></i>Editar
                </a>
                <a href="nova_reserva.php" class="btn btn-primary ms-2">
                    <i class="fas fa-plus me-2"></i>Nova Reserva
                </a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informações do Usuário</h5>
                    </div>
                    <div class="card-body">
                        <h2 class="mb-3 text-primary"><?php echo $usuario['USUARIO_NOME']; ?></h2>
                        
                        <div class="mb-3">
                            <h6 class="text-primary">Email</h6>
                            <p><?php echo $usuario['USUARIO_EMAIL']; ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-primary">CPF</h6>
                            <p><?php echo $usuario['USUARIO_CPF']; ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-primary">Curso</h6>
                            <p><?php echo $usuario['USUARIO_CURSO']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Estatísticas</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <h6>Total de Reservas:</h6>
                            <span class="badge bg-primary fs-6"><?php echo count($reservas); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <h6>Reservas Ativas:</h6>
                            <span class="badge bg-success fs-6"><?php echo $reservas_ativas; ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <h6>Reservas Canceladas:</h6>
                            <span class="badge bg-danger fs-6"><?php echo $reservas_canceladas; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Reservas do Usuário</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($reservas) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Sala</th>
                                        <th>Data</th>
                                        <th>Horário</th>
                                        <th>Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservas as $reserva): ?>
                                    <tr>
                                        <td><?php echo $reserva['RESERVA_ID']; ?></td>
                                        <td>Sala <?php echo $reserva['NUM_SALA']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($reserva['DATA_RESERVA'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($reserva['HORA_INICIO'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $reserva['ESTADO'] == 'Ativa' ? 'success' : 'danger'; ?>">
                                                <?php echo $reserva['ESTADO']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="ver_reserva.php?id=<?php echo $reserva['RESERVA_ID']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Ver detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($reserva['ESTADO'] == 'Ativa'): ?>
                                            <a href="editar_reserva.php?id=<?php echo $reserva['RESERVA_ID']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Este usuário não possui reservas.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


