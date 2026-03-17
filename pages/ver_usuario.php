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

// Contar reservas ativas e canceladas
$reservas_ativas = 0;
$reservas_canceladas = 0;

foreach ($reservas as $reserva) {
    if ($reserva['ESTADO'] == 'Ativa') {
        $reservas_ativas++;
    } else {
        $reservas_canceladas++;
    }
}

// Generate avatar initials from name
$nomePartes = explode(' ', trim($usuario['USUARIO_NOME']));
$iniciais = strtoupper(substr($nomePartes[0], 0, 1));
if (count($nomePartes) > 1) {
    $iniciais .= strtoupper(substr(end($nomePartes), 0, 1));
}

$pageTitle = 'Detalhes do Usuário';
include '../includes/header.php';
?>

<style>
.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(209, 213, 219, 0.3);
}
.detail-item:last-child {
    border-bottom: none;
}
.detail-label {
    font-size: 0.875rem;
    color: #6b7280;
}
.detail-value {
    font-weight: 600;
    text-align: right;
    max-width: 60%;
    word-break: break-word;
}
.user-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #c9a84c, #a07830);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 auto 16px;
    border: 3px solid rgba(201, 168, 76, 0.4);
    flex-shrink: 0;
}
.stat-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 12px;
    margin-top: 20px;
}
.stat-box {
    text-align: center;
    padding: 14px 8px;
    background: rgba(201, 168, 76, 0.06);
    border: 1px solid rgba(201, 168, 76, 0.15);
    border-radius: 8px;
}
.stat-box .stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #c9a84c;
    line-height: 1;
}
.stat-box .stat-label {
    font-size: 0.7rem;
    color: #6b7280;
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.stat-box.stat-success .stat-number { color: #10b981; }
.stat-box.stat-danger .stat-number  { color: #ef4444; }
</style>

<div class="page-header">
    <h2>Detalhes do Usuário</h2>
    <div class="page-actions">
        <a href="usuarios.php" class="btn-gray"><i class="fas fa-arrow-left me-2"></i>Voltar</a>
        <a href="editar_usuario.php?id=<?= $usuario['USUARIO_ID'] ?>" class="btn-gold-outline"><i class="fas fa-edit me-2"></i>Editar</a>
        <a href="nova_reserva.php" class="btn-gold-outline"><i class="fas fa-plus me-2"></i>Nova Reserva</a>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 2fr; gap:24px; align-items:start">

    <!-- Left: User Info Card -->
    <div class="sc-card">
        <div style="text-align:center; padding:8px 0 20px">
            <div class="user-avatar-large"><?= $iniciais ?></div>
            <h4 style="margin:0; font-size:1.1rem; font-weight:700"><?= htmlspecialchars($usuario['USUARIO_NOME']) ?></h4>
            <?php if (!empty($usuario['USUARIO_CARGO'])): ?>
            <div style="font-size:0.8rem; color:#6b7280; margin-top:4px"><?= htmlspecialchars($usuario['USUARIO_CARGO']) ?></div>
            <?php endif; ?>
            <?php
            $tipo = $usuario['TIPO_USUARIO'] ?? 'comum';
            $badgeClass = $tipo === 'admin' ? 'badge-gold' : 'badge-light';
            ?>
            <span class="badge <?= $badgeClass ?>" style="margin-top:8px; text-transform:capitalize"><?= htmlspecialchars($tipo) ?></span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Email</span>
            <span class="detail-value" style="font-size:0.8rem"><?= htmlspecialchars($usuario['USUARIO_EMAIL']) ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">CPF</span>
            <span class="detail-value"><?= htmlspecialchars($usuario['USUARIO_CPF']) ?></span>
        </div>
        <?php if (!empty($usuario['USUARIO_DEPARTAMENTO'])): ?>
        <div class="detail-item">
            <span class="detail-label">Departamento</span>
            <span class="detail-value"><?= htmlspecialchars($usuario['USUARIO_DEPARTAMENTO']) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($usuario['USUARIO_CARGO'])): ?>
        <div class="detail-item">
            <span class="detail-label">Cargo</span>
            <span class="detail-value"><?= htmlspecialchars($usuario['USUARIO_CARGO']) ?></span>
        </div>
        <?php endif; ?>

        <div class="stat-row">
            <div class="stat-box">
                <div class="stat-number"><?= count($reservas) ?></div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-box stat-success">
                <div class="stat-number"><?= $reservas_ativas ?></div>
                <div class="stat-label">Ativas</div>
            </div>
            <div class="stat-box stat-danger">
                <div class="stat-number"><?= $reservas_canceladas ?></div>
                <div class="stat-label">Canceladas</div>
            </div>
        </div>
    </div>

    <!-- Right: Reservation History -->
    <div class="sc-card sc-card-flush">
        <div class="sc-card-header" style="padding:24px 24px 16px">
            <div>
                <h5 class="sc-card-title">Histórico de Reservas</h5>
                <div class="sc-card-subtitle"><?= count($reservas) ?> reserva(s) no total</div>
            </div>
        </div>

        <?php if (count($reservas) > 0): ?>
        <div style="overflow-x:auto">
            <table class="sc-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sala</th>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservas as $reserva): ?>
                    <tr>
                        <td style="color:#6b7280; font-size:0.8rem">#<?= $reserva['RESERVA_ID'] ?></td>
                        <td>Sala <?= htmlspecialchars($reserva['NUM_SALA']) ?></td>
                        <td><?= date('d/m/Y', strtotime($reserva['DATA_RESERVA'])) ?></td>
                        <td><?= date('H:i', strtotime($reserva['HORA_INICIO'])) ?></td>
                        <td>
                            <span class="badge-status badge-<?= strtolower($reserva['ESTADO']) ?>">
                                <?= $reserva['ESTADO'] ?>
                            </span>
                        </td>
                        <td>
                            <a href="ver_reserva.php?id=<?= $reserva['RESERVA_ID'] ?>" class="btn-gold-outline" style="padding:4px 12px; font-size:0.75rem">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <?php if ($reserva['ESTADO'] == 'Ativa'): ?>
                            <a href="editar_reserva.php?id=<?= $reserva['RESERVA_ID'] ?>" class="btn-gold-outline" style="padding:4px 12px; font-size:0.75rem; margin-left:4px">
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
        <div style="padding:24px">
            <div class="empty-state">
                <i class="fas fa-calendar-times empty-state-icon"></i>
                <h4 class="empty-state-title">Nenhuma reserva encontrada</h4>
                <p class="empty-state-text">Este usuário ainda não possui reservas registradas.</p>
                <a href="nova_reserva.php" class="btn-gold-outline">
                    <i class="fas fa-plus me-2"></i>Nova Reserva
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
