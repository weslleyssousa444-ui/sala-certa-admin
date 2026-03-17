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
    $recorrenciaAtiva = isset($_POST['recorrencia_ativa']);
    $recorrencia = filter_input(INPUT_POST, 'recorrencia', FILTER_SANITIZE_SPECIAL_CHARS);
    $recorrenciaFim = filter_input(INPUT_POST, 'recorrencia_fim', FILTER_SANITIZE_SPECIAL_CHARS);

    $dataFormatada = date('Y-m-d', strtotime(str_replace('/', '-', $dataReserva)));

    if (empty($usuarioId) || empty($salaId) || empty($dataReserva) || empty($horaInicio) || empty($tempoReserva)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        if ($recorrenciaAtiva && !empty($recorrencia) && !empty($recorrenciaFim)) {
            // Criar reservas recorrentes
            $resultado = Reserva::criarRecorrente($salaId, $dataFormatada, $recorrencia, $recorrenciaFim, $horaInicio, $tempoReserva, $usuarioId);

            if (!empty($resultado['conflitos'])) {
                $datasConflito = implode(', ', array_map(function($d) { return date('d/m/Y', strtotime($d)); }, $resultado['conflitos']));
                $error = "Existem conflitos de horário nas seguintes datas: <strong>{$datasConflito}</strong>. Nenhuma reserva foi criada. Por favor, escolha outro horário.";
            } else {
                $success = "Reservas recorrentes criadas com sucesso! Total: <strong>{$resultado['livres']}</strong> reserva(s) cadastrada(s).";
                $usuarioId = $salaId = $dataReserva = $horaInicio = $tempoReserva = '';
            }
        } else {
            // Reserva simples
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
}

$pageTitle = 'Nova Reserva';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h2>Nova Reserva</h2>
    <a href="reservas.php" class="btn-gray"><i class="fas fa-arrow-left me-2"></i>Voltar</a>
</div>

<?php if ($error): ?>
    <div class="sc-alert sc-alert-danger"><i class="fas fa-exclamation-circle"></i><span><?= $error ?></span></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="sc-alert sc-alert-success"><i class="fas fa-check-circle"></i><span><?= $success ?></span></div>
<?php endif; ?>

<div class="sc-card">
    <form method="post" class="sc-form">
        <div class="form-row">
            <div>
                <label class="form-label">Usuário *</label>
                <select name="usuario_id" class="form-select" required>
                    <option value="">Selecione um usuário</option>
                    <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= $usuario['USUARIO_ID'] ?>" <?= (isset($usuarioId) && $usuarioId == $usuario['USUARIO_ID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($usuario['USUARIO_NOME']) ?> (<?= htmlspecialchars($usuario['USUARIO_EMAIL']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Data da Reserva *</label>
                <input type="date" name="data_reserva" class="form-control" value="<?= htmlspecialchars($dataReserva ?? '') ?>" min="<?= date('Y-m-d') ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label class="form-label">Filtrar por Andar</label>
                <select id="andar_filtro" class="form-select">
                    <option value="">Selecione o andar</option>
                    <option value="terreo">Térreo</option>
                    <option value="1">1º Andar</option>
                    <option value="2">2º Andar</option>
                </select>
            </div>
            <div>
                <label class="form-label">Sala *</label>
                <select name="sala_id" id="sala_id" class="form-select" required disabled>
                    <option value="">Primeiro selecione o andar</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label class="form-label">Hora de Início *</label>
                <input type="time" name="hora_inicio" class="form-control" value="<?= htmlspecialchars($horaInicio ?? '') ?>" required>
            </div>
            <div>
                <label class="form-label">Duração (Máx: 3h) *</label>
                <select name="tempo_reserva" class="form-select" required>
                    <option value="">Selecione a duração</option>
                    <option value="00:30:00" <?= (isset($tempoReserva) && $tempoReserva == '00:30:00') ? 'selected' : '' ?>>30 minutos</option>
                    <option value="01:00:00" <?= (isset($tempoReserva) && $tempoReserva == '01:00:00') ? 'selected' : '' ?>>1 hora</option>
                    <option value="01:30:00" <?= (isset($tempoReserva) && $tempoReserva == '01:30:00') ? 'selected' : '' ?>>1 hora e 30 minutos</option>
                    <option value="02:00:00" <?= (isset($tempoReserva) && $tempoReserva == '02:00:00') ? 'selected' : '' ?>>2 horas</option>
                    <option value="02:30:00" <?= (isset($tempoReserva) && $tempoReserva == '02:30:00') ? 'selected' : '' ?>>2 horas e 30 minutos</option>
                    <option value="03:00:00" <?= (isset($tempoReserva) && $tempoReserva == '03:00:00') ? 'selected' : '' ?>>3 horas</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="recorrenciaToggle" name="recorrencia_ativa" <?= (isset($recorrenciaAtiva) && $recorrenciaAtiva) ? 'checked' : '' ?>>
                <label class="form-check-label" for="recorrenciaToggle">Reserva Recorrente</label>
            </div>
        </div>

        <div id="recorrenciaFields" style="display:none">
            <div class="form-row">
                <div>
                    <label class="form-label">Tipo de Recorrência</label>
                    <select name="recorrencia" class="form-select">
                        <option value="semanal" <?= (isset($recorrencia) && $recorrencia == 'semanal') ? 'selected' : '' ?>>Semanal</option>
                        <option value="quinzenal" <?= (isset($recorrencia) && $recorrencia == 'quinzenal') ? 'selected' : '' ?>>Quinzenal</option>
                        <option value="mensal" <?= (isset($recorrencia) && $recorrencia == 'mensal') ? 'selected' : '' ?>>Mensal</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Data Final</label>
                    <input type="date" name="recorrencia_fim" class="form-control" value="<?= htmlspecialchars($recorrenciaFim ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="reservas.php" class="btn-gray">Cancelar</a>
            <button type="submit" class="btn-gold"><i class="fas fa-save me-2"></i>Salvar</button>
        </div>
    </form>
</div>

<script>
// Mostrar/Ocultar campos de recorrência
document.getElementById('recorrenciaToggle')?.addEventListener('change', function() {
    document.getElementById('recorrenciaFields').style.display = this.checked ? 'block' : 'none';
});

// Restaurar estado do toggle se recorrência estava ativa
if (document.getElementById('recorrenciaToggle').checked) {
    document.getElementById('recorrenciaFields').style.display = 'block';
}

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
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
