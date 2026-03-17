<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Reserva.php';
require_once __DIR__ . '/../classes/Sala.php';
require_once __DIR__ . '/../classes/Usuario.php';
require_once __DIR__ . '/../includes/alert.php';

requireLogin();

$error = '';
$success = '';

$usuarios = Usuario::listarTodos();
$todasSalas = Sala::listarTodas();

// Agrupar salas por andar
$salasPorAndar = [];
foreach ($todasSalas as $sala) {
    $numSala = $sala['NUM_SALA'];
    if (strpos($numSala, 'T') === 0) {
        $andar = 'Térreo';
    } else if (strpos($numSala, '1') === 0) {
        $andar = '1º Andar';
    } else if (strpos($numSala, '2') === 0) {
        $andar = '2º Andar';
    } else {
        $andar = 'Outros';
    }
    
    if (!isset($salasPorAndar[$andar])) {
        $salasPorAndar[$andar] = [];
    }
    $salasPorAndar[$andar][] = $sala;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioId = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
    $salaId = filter_input(INPUT_POST, 'sala_id', FILTER_VALIDATE_INT);
    $dataReserva = filter_input(INPUT_POST, 'data_reserva', FILTER_SANITIZE_SPECIAL_CHARS);
    $horaInicio = filter_input(INPUT_POST, 'hora_inicio', FILTER_SANITIZE_SPECIAL_CHARS);
    $tempoReserva = filter_input(INPUT_POST, 'tempo_reserva', FILTER_SANITIZE_SPECIAL_CHARS);
    $estado = 'Ativa';
    
    $dataFormatada = date('Y-m-d', strtotime(str_replace('/', '-', $dataReserva)));
    
    if (empty($usuarioId) || empty($salaId) || empty($dataReserva) || empty($horaInicio) || empty($tempoReserva)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        $resultadoConflito = Reserva::verificarConflito($salaId, $dataFormatada, $horaInicio, $tempoReserva);
        
        if ($resultadoConflito['conflito']) {
            $horarioOcupado = $resultadoConflito['horario_ocupado'];
            $error = "Não é possível criar a reserva. A sala já está reservada no horário: <strong>{$horarioOcupado}</strong>. Por favor, escolha outro horário disponível.";
        } else {
            $reserva = new Reserva();
            $reserva->setUsuarioId($usuarioId);
            $reserva->setSalaId($salaId);
            $reserva->setDataReserva($dataFormatada);
            $reserva->setHoraInicio($horaInicio);
            $reserva->setTempoReserva($tempoReserva);
            $reserva->setEstado($estado);
            
            if ($reserva->cadastrar()) {
                $success = 'Reserva cadastrada com sucesso!';
                $usuarioId = $salaId = $dataReserva = $horaInicio = $tempoReserva = '';
            } else {
                $error = 'Erro ao cadastrar reserva.';
            }
        }
    }
}

$pageTitle = 'Nova Reserva';
include __DIR__ . '/../includes/header.php';
?>

<style>
.sala-card {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s;
}

.sala-card:hover {
    border-color: #37D0C0;
    background-color: #f8f9fa;
    transform: translateY(-2px);
}

.sala-card.selected {
    border-color: #37D0C0;
    background-color: #e8f8f5;
}

.horario-item {
    background: #f8f9fa;
    border-left: 4px solid #dc3545;
    padding: 10px;
    margin-bottom: 8px;
    border-radius: 4px;
}

.horario-item.disponivel {
    border-left-color: #28a745;
}

.andar-badge {
    font-size: 0.85rem;
    padding: 5px 12px;
}
</style>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Nova Reserva</h2>
            <a href="reservas.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
        
        <?php if ($error): ?>
            <?php showAlert($error, 'danger'); ?>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <?php showAlert($success, 'success'); ?>
        <?php endif; ?>
    </div>
</div>

<!-- Card com salas disponíveis por andar -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-door-open me-2"></i>Salas Disponíveis por Andar
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($salasPorAndar as $andar => $salas): ?>
                    <div class="col-md-4 mb-3">
                        <h6 class="text-primary">
                            <i class="fas fa-building me-2"></i><?php echo $andar; ?>
                        </h6>
                        <hr>
                        <?php foreach ($salas as $sala): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-start border-4 border-info">
                            <div>
                                <strong>Sala <?php echo $sala['NUM_SALA']; ?></strong>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-users"></i> <?php echo $sala['QTD_PESSOAS']; ?> pessoas
                                </small>
                            </div>
                            <span class="badge bg-<?php 
                                $tipo = $sala['TIPO_SALA'] ?? 'aula';
                                echo ($tipo == 'aula') ? 'primary' : (($tipo == 'laboratorio') ? 'warning' : 'success'); 
                            ?>">
                                <?php 
                                    $tipos = ['aula' => 'Aula', 'laboratorio' => 'Lab', 'reuniao' => 'Reunião', 'auditorio' => 'Auditório'];
                                    echo $tipos[$tipo] ?? 'Aula';
                                ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card de horários ocupados -->
<div class="row mb-4" id="horariosCard" style="display: none;">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock me-2"></i>Horários da Sala
                </h5>
            </div>
            <div class="card-body" id="horariosConteudo">
                <!-- Será preenchido via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Formulário -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Dados da Reserva</h5>
            </div>
            <div class="card-body">
                <form method="post" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="usuario_id" class="form-label">Usuário *</label>
                            <select class="form-select" id="usuario_id" name="usuario_id" required>
                                <option value="">Selecione um usuário</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?php echo $usuario['USUARIO_ID']; ?>">
                                    <?php echo $usuario['USUARIO_NOME']; ?> (<?php echo $usuario['USUARIO_EMAIL']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Por favor, selecione um usuário.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="data_reserva" class="form-label">Data da Reserva *</label>
                            <input type="date" class="form-control" id="data_reserva" name="data_reserva" required min="<?php echo date('Y-m-d'); ?>">
                            <div class="invalid-feedback">
                                Por favor, informe a data da reserva.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="andar_filtro" class="form-label">Filtrar por Andar *</label>
                            <select class="form-select" id="andar_filtro" required>
                                <option value="">Selecione o andar</option>
                                <option value="terreo">Térreo</option>
                                <option value="1">1º Andar</option>
                                <option value="2">2º Andar</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="sala_id" class="form-label">Sala *</label>
                            <select class="form-select" id="sala_id" name="sala_id" required disabled>
                                <option value="">Primeiro selecione o andar</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor, selecione uma sala.
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="hora_inicio" class="form-label">Hora de Início *</label>
                            <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" required>
                            <div class="invalid-feedback">
                                Por favor, informe a hora de início.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tempo_reserva" class="form-label">Duração (Máx: 3h) *</label>
                            <select class="form-select" id="tempo_reserva" name="tempo_reserva" required>
                                <option value="">Selecione a duração</option>
                                <option value="00:30:00">30 minutos</option>
                                <option value="01:00:00">1 hora</option>
                                <option value="01:30:00">1 hora e 30 minutos</option>
                                <option value="02:00:00">2 horas</option>
                                <option value="02:30:00">2 horas e 30 minutos</option>
                                <option value="03:00:00">3 horas</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor, selecione a duração.
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Cadastrar Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Filtrar salas por andar
document.getElementById('andar_filtro').addEventListener('change', function() {
    const andar = this.value;
    const salaSelect = document.getElementById('sala_id');
    
    salaSelect.innerHTML = '<option value="">Carregando...</option>';
    salaSelect.disabled = true;
    
    if (!andar) {
        salaSelect.innerHTML = '<option value="">Primeiro selecione o andar</option>';
        return;
    }
    
    const todasSalas = <?php echo json_encode($todasSalas); ?>;
    const salasFiltradas = todasSalas.filter(sala => {
        const num = sala.NUM_SALA;
        if (andar === 'terreo') return num.startsWith('T');
        if (andar === '1') return num.startsWith('1') && !num.startsWith('T');
        if (andar === '2') return num.startsWith('2');
        return false;
    });
    
    salaSelect.innerHTML = '<option value="">Selecione uma sala</option>';
    salasFiltradas.forEach(sala => {
        const option = document.createElement('option');
        option.value = sala.SALA_ID;
        option.textContent = `Sala ${sala.NUM_SALA} (${sala.QTD_PESSOAS} pessoas)`;
        salaSelect.appendChild(option);
    });
    
    salaSelect.disabled = false;
});

// Buscar horários quando selecionar sala e data
function buscarHorarios() {
    const salaId = document.getElementById('sala_id').value;
    const dataReserva = document.getElementById('data_reserva').value;
    
    if (!salaId || !dataReserva) {
        document.getElementById('horariosCard').style.display = 'none';
        return;
    }
    
    const container = document.getElementById('horariosConteudo');
    container.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2">Carregando horários...</p></div>';
    document.getElementById('horariosCard').style.display = 'block';
    
    fetch(`buscar_horarios.php?sala_id=${salaId}&data=${dataReserva}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao buscar horários');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                container.innerHTML = `<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>${data.error}</div>`;
                return;
            }
            
            if (!data.horarios || data.horarios.length === 0) {
                container.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Sala completamente disponível nesta data!</div>';
            } else {
                let html = '<h6 class="text-danger mb-3"><i class="fas fa-exclamation-circle me-2"></i>Horários já reservados:</h6>';
                data.horarios.forEach(h => {
                    html += `<div class="horario-item">
                        <i class="fas fa-clock me-2"></i>
                        <strong>${h.inicio}</strong> até <strong>${h.fim}</strong>
                    </div>`;
                });
                container.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            container.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Erro ao carregar horários. Tente novamente.</div>';
        });
}

document.getElementById('sala_id').addEventListener('change', buscarHorarios);
document.getElementById('data_reserva').addEventListener('change', buscarHorarios);

// Validação do formulário
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>