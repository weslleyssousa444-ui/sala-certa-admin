<?php
require_once '../config/config.php';
require_once '../classes/Reserva.php';
require_once '../classes/Sala.php';
require_once '../classes/Usuario.php';
require_once '../includes/alert.php';

// Verificar se está logado
requireLogin();

// Verificar se ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: reservas.php');
    exit;
}

$id = $_GET['id'];
$reserva_dados = Reserva::buscarPorId($id);

// Verificar se a reserva existe
if (!$reserva_dados) {
    header('Location: reservas.php');
    exit;
}

// Listar todas as salas para o select
$salas = Sala::listarTodas();

// Listar todos os usuários para o select
$usuarios = Usuario::listarTodos();

$error = '';
$success = '';

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioId = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
    $salaId = filter_input(INPUT_POST, 'sala_id', FILTER_VALIDATE_INT);
    $dataReserva = filter_input(INPUT_POST, 'data_reserva', FILTER_SANITIZE_STRING);
    $horaInicio = filter_input(INPUT_POST, 'hora_inicio', FILTER_SANITIZE_STRING);
    $tempoReserva = filter_input(INPUT_POST, 'tempo_reserva', FILTER_SANITIZE_STRING);
    $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);
    
    // Converter data para o formato do banco
    $dataFormatada = date('Y-m-d', strtotime(str_replace('/', '-', $dataReserva)));
    
    // Validar dados
    if (empty($usuarioId) || empty($salaId) || empty($dataReserva) || empty($horaInicio) || empty($tempoReserva) || empty($estado)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        // Verificar conflito de reserva (excluindo a própria reserva da verificação)
        $resultadoConflito = Reserva::verificarConflito($salaId, $dataFormatada, $horaInicio, $tempoReserva, $id);
        
        if ($resultadoConflito['conflito']) {
            $horarioOcupado = $resultadoConflito['horario_ocupado'];
            $error = "Não é possível alterar a reserva. A sala já está reservada no horário: <strong>{$horarioOcupado}</strong>. Por favor, escolha outro horário que não esteja ocupado.";
        } else {
            // Atualizar reserva
            $reserva = new Reserva();
            $reserva->setId($id);
            $reserva->setUsuarioId($usuarioId);
            $reserva->setSalaId($salaId);
            $reserva->setDataReserva($dataFormatada);
            $reserva->setHoraInicio($horaInicio);
            $reserva->setTempoReserva($tempoReserva);
            $reserva->setEstado($estado);
            
            if ($reserva->atualizar()) {
                $success = 'Reserva atualizada com sucesso!';
                // Recarregar os dados da reserva
                $reserva_dados = Reserva::buscarPorId($id);
            } else {
                $error = 'Erro ao atualizar reserva.';
            }
        }
    }
}

$pageTitle = 'Editar Reserva';
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Editar Reserva</h2>
            <a href="reservas.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Formulário de Edição</h5>
            </div>
            <div class="card-body">
                <form method="post" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="usuario_id" class="form-label">Usuário</label>
                            <select class="form-select" id="usuario_id" name="usuario_id" required>
                                <option value="">Selecione um usuário</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?php echo $usuario['USUARIO_ID']; ?>" <?php echo ($reserva_dados['USUARIO_ID'] == $usuario['USUARIO_ID']) ? 'selected' : ''; ?>>
                                    <?php echo $usuario['USUARIO_NOME']; ?> (<?php echo $usuario['USUARIO_EMAIL']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Por favor, selecione um usuário.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="sala_id" class="form-label">Sala</label>
                            <select class="form-select" id="sala_id" name="sala_id" required>
                                <option value="">Selecione uma sala</option>
                                <?php foreach ($salas as $sala): ?>
                                <option value="<?php echo $sala['SALA_ID']; ?>" <?php echo ($reserva_dados['SALA_ID'] == $sala['SALA_ID']) ? 'selected' : ''; ?>>
                                    Sala <?php echo $sala['NUM_SALA']; ?> (<?php echo $sala['QTD_PESSOAS']; ?> pessoas)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Por favor, selecione uma sala.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="data_reserva" class="form-label">Data da Reserva</label>
                            <input type="text" class="form-control datepicker date-mask" id="data_reserva" name="data_reserva" value="<?php echo date('d/m/Y', strtotime($reserva_dados['DATA_RESERVA'])); ?>" required>
                            <div class="invalid-feedback">
                                Por favor, informe a data da reserva.
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="hora_inicio" class="form-label">Hora de Início</label>
                            <input type="text" class="form-control time-mask" id="hora_inicio" name="hora_inicio" value="<?php echo date('H:i', strtotime($reserva_dados['HORA_INICIO'])); ?>" required placeholder="00:00">
                            <div class="invalid-feedback">
                                Por favor, informe a hora de início.
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="tempo_reserva" class="form-label">Duração</label>
                            <input type="text" class="form-control time-mask" id="tempo_reserva" name="tempo_reserva" value="<?php echo date('H:i', strtotime($reserva_dados['TEMPO_RESERVA'])); ?>" required placeholder="00:00">
                            <div class="invalid-feedback">
                                Por favor, informe a duração da reserva.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="">Selecione o estado</option>
                            <option value="Ativa" <?php echo ($reserva_dados['ESTADO'] == 'Ativa') ? 'selected' : ''; ?>>Ativa</option>
                            <option value="Cancelada" <?php echo ($reserva_dados['ESTADO'] == 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                        </select>
                        <div class="invalid-feedback">
                            Por favor, selecione o estado da reserva.
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Atenção:</strong> Ao alterar a data ou horário, o sistema verificará se não há conflitos com outras reservas ativas para a mesma sala.
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Atualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $.fn.mask === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js';
        document.head.appendChild(script);
        
        script.onload = function() {
            $('.date-mask').mask('00/00/0000');
            $('.time-mask').mask('00:00');
        }
    } else {
        $('.date-mask').mask('00/00/0000');
        $('.time-mask').mask('00:00');
    }
    
    if (typeof $.fn.datepicker === 'undefined') {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css';
        document.head.appendChild(link);
        
        var script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js';
        document.head.appendChild(script);
        
        script.onload = function() {
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                language: 'pt-BR',
                autoclose: true
            });
        }
    } else {
        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
            language: 'pt-BR',
            autoclose: true
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>

