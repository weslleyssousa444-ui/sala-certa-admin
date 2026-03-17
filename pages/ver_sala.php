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
}
.sala-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #c9a84c;
    line-height: 1;
    margin-bottom: 4px;
}
.stat-box {
    text-align: center;
    padding: 16px;
    background: rgba(201, 168, 76, 0.08);
    border: 1px solid rgba(201, 168, 76, 0.2);
    border-radius: 8px;
}
.stat-box .stat-number {
    font-size: 1.75rem;
    font-weight: 700;
    color: #c9a84c;
    line-height: 1;
}
.stat-box .stat-label {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 4px;
}
</style>

<div class="page-header">
    <h2>Detalhes da Sala</h2>
    <div class="page-actions">
        <a href="salas.php" class="btn-gray"><i class="fas fa-arrow-left me-2"></i>Voltar</a>
        <a href="editar_sala.php?id=<?= $sala['SALA_ID'] ?>" class="btn-gold-outline"><i class="fas fa-edit me-2"></i>Editar</a>
        <a href="nova_reserva.php" class="btn-gold-outline"><i class="fas fa-plus me-2"></i>Nova Reserva</a>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 2fr; gap:24px; align-items:start">

    <!-- Left: Room Info Card -->
    <div class="sc-card">
        <div class="sc-card-header">
            <div>
                <h5 class="sc-card-title">Informações da Sala</h5>
            </div>
        </div>

        <div style="text-align:center; padding:16px 0 24px">
            <div class="sala-number"><?= htmlspecialchars($sala['NUM_SALA']) ?></div>
            <div style="font-size:0.875rem; color:#6b7280">Número da Sala</div>
        </div>

        <div class="detail-item">
            <span class="detail-label">Tipo</span>
            <span class="detail-value"><?= !empty($sala['TIPO_SALA']) ? htmlspecialchars($sala['TIPO_SALA']) : '—' ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Capacidade</span>
            <span class="detail-value"><?= $sala['QTD_PESSOAS'] ?> pessoas</span>
        </div>
        <?php if (!empty($sala['SETOR_RESPONSAVEL'])): ?>
        <div class="detail-item">
            <span class="detail-label">Setor</span>
            <span class="detail-value"><?= htmlspecialchars($sala['SETOR_RESPONSAVEL']) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($sala['DESCRICAO'])): ?>
        <div style="margin-top:16px">
            <div style="font-size:0.875rem; color:#6b7280; margin-bottom:6px">Descrição</div>
            <div style="font-size:0.875rem; line-height:1.6"><?= htmlspecialchars($sala['DESCRICAO']) ?></div>
        </div>
        <?php endif; ?>

        <div style="margin-top:24px">
            <div class="stat-box">
                <div class="stat-number"><?= $total_reservas ?></div>
                <div class="stat-label">Total de Reservas</div>
            </div>
        </div>
    </div>

    <!-- Right: Upcoming Reservations -->
    <div class="sc-card sc-card-flush">
        <div class="sc-card-header" style="padding:24px 24px 16px">
            <div>
                <h5 class="sc-card-title">Próximas Reservas</h5>
                <div class="sc-card-subtitle"><?= count($proximas_reservas) ?> reserva(s) ativas a partir de hoje</div>
            </div>
        </div>

        <?php if (count($proximas_reservas) > 0): ?>
        <div class="sc-table-wrapper" style="overflow-x:auto">
            <table class="sc-table">
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
                        <td><?= date('d/m/Y', strtotime($reserva['DATA_RESERVA'])) ?></td>
                        <td><?= date('H:i', strtotime($reserva['HORA_INICIO'])) ?></td>
                        <td><?= date('H:i', strtotime($reserva['TEMPO_RESERVA'])) ?>h</td>
                        <td><?= htmlspecialchars($reserva['USUARIO_NOME']) ?></td>
                        <td>
                            <a href="ver_reserva.php?id=<?= $reserva['RESERVA_ID'] ?>" class="btn-gold-outline" style="padding:4px 12px; font-size:0.75rem">
                                <i class="fas fa-eye"></i> Ver
                            </a>
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
                <h4 class="empty-state-title">Nenhuma reserva futura</h4>
                <p class="empty-state-text">Esta sala não possui reservas ativas a partir de hoje.</p>
                <a href="nova_reserva.php" class="btn-gold-outline">
                    <i class="fas fa-plus me-2"></i>Nova Reserva
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
