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

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Detalhes da Reserva</h2>
            <div>
                <a href="reservas.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
                <?php if ($reserva['ESTADO'] == 'Ativa'): ?>
                <a href="editar_reserva.php?id=<?php echo $reserva['RESERVA_ID']; ?>" class="btn btn-warning ms-2">
                    <i class="fas fa-edit me-2"></i>Editar
                </a>
                <a href="ver_reserva.php?id=<?php echo $reserva['RESERVA_ID']; ?>&cancelar=true" class="btn btn-danger ms-2 btn-delete" data-bs-toggle="tooltip" title="Cancelar Reserva">
                    <i class="fas fa-times-circle me-2"></i>Cancelar
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($success): ?>
            <?php showAlert($success, 'success'); ?>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <?php showAlert($error, 'danger'); ?>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informações da Reserva #<?php echo $reserva['RESERVA_ID']; ?></h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-primary">Usuário</h6>
                        <p class="mb-0"><strong>Nome:</strong> <?php echo $reserva['USUARIO_NOME']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Sala</h6>
                        <p class="mb-0"><strong>Número:</strong> <?php echo $reserva['NUM_SALA']; ?></p>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <h6 class="text-primary">Data da Reserva</h6>
                        <p class="mb-0"><?php echo date('d/m/Y', strtotime($reserva['DATA_RESERVA'])); ?></p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-primary">Horário de Início</h6>
                        <p class="mb-0"><?php echo date('H:i', strtotime($reserva['HORA_INICIO'])); ?></p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-primary">Duração</h6>
                        <p class="mb-0"><?php echo date('H:i', strtotime($reserva['TEMPO_RESERVA'])); ?></p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="text-primary">Estado</h6>
                        <p>
                            <span class="badge bg-<?php echo $reserva['ESTADO'] == 'Ativa' ? 'success' : 'danger'; ?> fs-6">
                                <?php echo $reserva['ESTADO']; ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <!-- Calculando horário de término -->
                <?php 
                $horaInicio = new DateTime($reserva['HORA_INICIO']);
                $tempoReserva = new DateTime($reserva['TEMPO_RESERVA']);
                $horaFim = clone $horaInicio;
                $horaFim->add(new DateInterval('PT' . $tempoReserva->format('H') . 'H' . $tempoReserva->format('i') . 'M'));
                ?>
                
                <div class="alert alert-info mt-4">
                    <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Informações Adicionais</h6>
                    <p class="mb-1"><strong>Hora de Término:</strong> <?php echo $horaFim->format('H:i'); ?></p>
                    <p class="mb-0"><strong>Duração Total:</strong> <?php echo $tempoReserva->format('H:i'); ?> horas</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


