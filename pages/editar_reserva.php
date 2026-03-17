<?php
require_once '../config/config.php';
require_once '../classes/Reserva.php';
require_once '../classes/Sala.php';
require_once '../classes/Usuario.php';
require_once '../includes/alert.php';

requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: reservas.php');
    exit;
}

$id = $_GET['id'];
$reserva_dados = Reserva::buscarPorId($id);

if (!$reserva_dados) {
    header('Location: reservas.php');
    exit;
}

$salas = Sala::listarTodas();
$usuarios = Usuario::listarTodos();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioId = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
    $salaId = filter_input(INPUT_POST, 'sala_id', FILTER_VALIDATE_INT);
    $dataReserva = htmlspecialchars($_POST['data_reserva'] ?? '');
    $horaInicio = htmlspecialchars($_POST['hora_inicio'] ?? '');
    $tempoReserva = htmlspecialchars($_POST['tempo_reserva'] ?? '');
    $estado = htmlspecialchars($_POST['estado'] ?? '');

    $dataFormatada = date('Y-m-d', strtotime(str_replace('/', '-', $dataReserva)));

    if (empty($usuarioId) || empty($salaId) || empty($dataReserva) || empty($horaInicio) || empty($tempoReserva) || empty($estado)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        $resultadoConflito = Reserva::verificarConflito($salaId, $dataFormatada, $horaInicio, $tempoReserva, $id);

        if ($resultadoConflito['conflito']) {
            $horarioOcupado = $resultadoConflito['horario_ocupado'];
            $error = "Não é possível alterar a reserva. A sala já está reservada no horário: <strong>{$horarioOcupado}</strong>. Por favor, escolha outro horário que não esteja ocupado.";
        } else {
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

<div class="page-header">
    <h2>Editar Reserva</h2>
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
                <label class="form-label">Usuário</label>
                <select name="usuario_id" class="form-select" required>
                    <option value="">Selecione um usuário</option>
                    <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= $usuario['USUARIO_ID'] ?>" <?= ($reserva_dados['USUARIO_ID'] == $usuario['USUARIO_ID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($usuario['USUARIO_NOME']) ?> (<?= htmlspecialchars($usuario['USUARIO_EMAIL']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Sala</label>
                <select name="sala_id" class="form-select" required>
                    <option value="">Selecione uma sala</option>
                    <?php foreach ($salas as $sala): ?>
                    <option value="<?= $sala['SALA_ID'] ?>" <?= ($reserva_dados['SALA_ID'] == $sala['SALA_ID']) ? 'selected' : '' ?>>
                        Sala <?= htmlspecialchars($sala['NUM_SALA']) ?> (<?= $sala['QTD_PESSOAS'] ?> pessoas)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label class="form-label">Data da Reserva</label>
                <input type="text" name="data_reserva" class="form-control date-mask" value="<?= date('d/m/Y', strtotime($reserva_dados['DATA_RESERVA'])) ?>" required placeholder="DD/MM/AAAA">
            </div>
            <div>
                <label class="form-label">Hora de Início</label>
                <input type="text" name="hora_inicio" class="form-control time-mask" value="<?= date('H:i', strtotime($reserva_dados['HORA_INICIO'])) ?>" required placeholder="00:00">
            </div>
        </div>

        <div class="form-row">
            <div>
                <label class="form-label">Duração</label>
                <input type="text" name="tempo_reserva" class="form-control time-mask" value="<?= date('H:i', strtotime($reserva_dados['TEMPO_RESERVA'])) ?>" required placeholder="00:00">
            </div>
            <div>
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select" required>
                    <option value="">Selecione o estado</option>
                    <option value="Ativa" <?= ($reserva_dados['ESTADO'] == 'Ativa') ? 'selected' : '' ?>>Ativa</option>
                    <option value="Cancelada" <?= ($reserva_dados['ESTADO'] == 'Cancelada') ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <a href="reservas.php" class="btn-gray">Cancelar</a>
            <button type="submit" class="btn-gold"><i class="fas fa-save me-2"></i>Salvar</button>
        </div>
    </form>
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
});
</script>

<?php include '../includes/footer.php'; ?>
