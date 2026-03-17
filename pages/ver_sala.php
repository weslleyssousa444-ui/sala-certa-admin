<?php
require_once '../config/config.php';
require_once '../classes/Sala.php';
require_once '../classes/Reserva.php';
require_once '../includes/alert.php';

// Verificar se está logado
requireLogin();

// Verificar se ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: salas.php');
    exit;
}

$id = $_GET['id'];
$sala = Sala::buscarPorId($id);

// Verificar se a sala existe
if (!$sala) {
    header('Location: salas.php');
    exit;
}

// Buscar as próximas reservas desta sala
$conn = Conexao::getConn();
$hoje = date('Y-m-d');

$sql = "SELECT r.*, u.USUARIO_NOME 
        FROM RESERVA_SALA r
        JOIN USUARIO u ON r.USUARIO_ID = u.USUARIO_ID
        WHERE r.SALA_ID = :sala_id 
        AND (r.DATA_RESERVA >= :hoje OR (r.DATA_RESERVA = :hoje AND ADDTIME(r.HORA_INICIO, r.TEMPO_RESERVA) >= CURTIME()))
        AND r.ESTADO = 'Ativa'
        ORDER BY r.DATA_RESERVA, r.HORA_INICIO
        LIMIT 10";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':sala_id', $id);
$stmt->bindValue(':hoje', $hoje);
$stmt->execute();

$proximas_reservas = $stmt->fetchAll();

// Contar total de reservas desta sala
$sql = "SELECT COUNT(*) as total FROM RESERVA_SALA WHERE SALA_ID = :sala_id";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':sala_id', $id);
$stmt->execute();
$resultado = $stmt->fetch();
$total_reservas = $resultado['total'];

$pageTitle = 'Detalhes da Sala';
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Detalhes da Sala</h2>
            <div>
                <a href="salas.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
                <a href="editar_sala.php?id=<?php echo $sala['SALA_ID']; ?>" class="btn btn-warning ms-2">
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
                        <h5 class="card-title mb-0">Informações da Sala</h5>
                    </div>
                    <div class="card-body">
                        <h2 class="mb-3 text-primary">Sala <?php echo $sala['NUM_SALA']; ?></h2>
                        
                        <div class="mb-3">
                            <h6 class="text-primary">Capacidade</h6>
                            <p><?php echo $sala['QTD_PESSOAS']; ?> pessoas</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-primary">Descrição</h6>
                            <p><?php echo $sala['DESCRICAO']; ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-primary">Estatísticas</h6>
                            <p class="mb-0">Total de reservas: <?php echo $total_reservas; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Próximas Reservas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($proximas_reservas) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Horário</th>
                                        <th>Duração</th>
                                        <th>Usuário</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($proximas_reservas as $reserva): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($reserva['DATA_RESERVA'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($reserva['HORA_INICIO'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($reserva['TEMPO_RESERVA'])); ?></td>
                                        <td><?php echo $reserva['USUARIO_NOME']; ?></td>
                                        <td>
                                            <a href="ver_reserva.php?id=<?php echo $reserva['RESERVA_ID']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Ver detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Não há reservas futuras para esta sala.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


