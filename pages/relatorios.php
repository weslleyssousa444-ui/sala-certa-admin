<?php
require_once '../config/config.php';
require_once '../classes/Reserva.php';
require_once '../classes/Sala.php';
require_once '../classes/Usuario.php';

// Verificar se está logado
requireLogin();

// Obter dados para relatórios
$conn = Conexao::getConn();

// Estatísticas gerais
$totalUsuarios = count(Usuario::listarTodos());
$totalSalas = count(Sala::listarTodas());

$sql = "SELECT COUNT(*) as total FROM RESERVA_SALA";
$stmt = $conn->prepare($sql);
$stmt->execute();
$totalReservas = $stmt->fetch()['total'];

$sql = "SELECT COUNT(*) as total FROM RESERVA_SALA WHERE ESTADO = 'Ativa'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$reservasAtivas = $stmt->fetch()['total'];

$sql = "SELECT COUNT(*) as total FROM RESERVA_SALA WHERE ESTADO = 'Reservado'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$reservasReservado = $stmt->fetch()['total'];

$sql = "SELECT COUNT(*) as total FROM RESERVA_SALA WHERE ESTADO = 'Pendente'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$reservasPendente = $stmt->fetch()['total'];

$sql = "SELECT COUNT(*) as total FROM RESERVA_SALA WHERE ESTADO = 'Cancelada'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$reservasCanceladas = $stmt->fetch()['total'];

// Reservas por mês
$sql = "SELECT DATE_FORMAT(DATA_RESERVA, '%Y-%m') as mes, COUNT(*) as total
        FROM RESERVA_SALA
        GROUP BY DATE_FORMAT(DATA_RESERVA, '%Y-%m')
        ORDER BY mes DESC
        LIMIT 6";
$stmt = $conn->prepare($sql);
$stmt->execute();
$reservasPorMes = $stmt->fetchAll();

// Preparar dados para o gráfico de meses
$mesesLabels = [];
$mesesValues = [];
foreach ($reservasPorMes as $row) {
    $data = DateTime::createFromFormat('Y-m', $row['mes']);
    $mesesLabels[] = $data->format('M/Y');
    $mesesValues[] = $row['total'];
}
$mesesLabels = array_reverse($mesesLabels);
$mesesValues = array_reverse($mesesValues);

// Salas mais reservadas
$sql = "SELECT s.NUM_SALA, s.DESCRICAO, COUNT(r.RESERVA_ID) as total
        FROM SALA s
        LEFT JOIN RESERVA_SALA r ON s.SALA_ID = r.SALA_ID
        GROUP BY s.SALA_ID
        ORDER BY total DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->execute();
$salasMaisReservadas = $stmt->fetchAll();

// Usuários mais ativos
$sql = "SELECT u.USUARIO_NOME, u.USUARIO_EMAIL, u.USUARIO_DEPARTAMENTO, COUNT(r.RESERVA_ID) as total
        FROM USUARIO u
        LEFT JOIN RESERVA_SALA r ON u.USUARIO_ID = r.USUARIO_ID
        GROUP BY u.USUARIO_ID
        ORDER BY total DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->execute();
$usuariosMaisAtivos = $stmt->fetchAll();

// Reservas recentes
$sql = "SELECT r.*, u.USUARIO_NOME, s.NUM_SALA
        FROM RESERVA_SALA r
        JOIN USUARIO u ON r.USUARIO_ID = u.USUARIO_ID
        JOIN SALA s ON r.SALA_ID = s.SALA_ID
        ORDER BY r.DATA_RESERVA DESC, r.HORA_INICIO DESC
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->execute();
$reservasRecentes = $stmt->fetchAll();

// Preparar dados para gráficos de barras
$topSalasLabels = [];
$topSalasValues = [];
foreach ($salasMaisReservadas as $s) {
    $topSalasLabels[] = 'Sala ' . $s['NUM_SALA'];
    $topSalasValues[] = $s['total'];
}

$topUsuariosLabels = [];
$topUsuariosValues = [];
foreach ($usuariosMaisAtivos as $u) {
    $topUsuariosLabels[] = $u['USUARIO_NOME'];
    $topUsuariosValues[] = $u['total'];
}

$pageTitle = 'Relatórios';
include '../includes/header.php';
?>

<div class="reports-page">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h2 class="page-title">Relatórios</h2>
            <p class="page-subtitle">Gerado em <?php echo date('d/m/Y \à\s H:i'); ?></p>
        </div>
        <div class="page-header-right">
            <button class="btn-gold" onclick="window.print()">
                <i class="fas fa-file-pdf me-2"></i>Exportar PDF
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="reports-summary-row">
        <div class="summary-card">
            <div class="summary-label"><i class="fas fa-calendar-check me-1"></i> Total de Reservas</div>
            <div class="summary-value"><?php echo $totalReservas; ?></div>
        </div>
        <div class="summary-card summary-green">
            <div class="summary-label"><i class="fas fa-check-circle me-1"></i> Reservas Ativas</div>
            <div class="summary-value"><?php echo $reservasAtivas; ?></div>
        </div>
        <div class="summary-card">
            <div class="summary-label"><i class="fas fa-users me-1"></i> Usuários Cadastrados</div>
            <div class="summary-value"><?php echo $totalUsuarios; ?></div>
        </div>
        <div class="summary-card summary-blue">
            <div class="summary-label"><i class="fas fa-door-open me-1"></i> Salas Disponíveis</div>
            <div class="summary-value"><?php echo $totalSalas; ?></div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
        <!-- Reservas por Mês -->
        <div class="chart-container">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Reservas por Mês</div>
                    <div class="chart-subtitle">Últimos 6 meses</div>
                </div>
            </div>
            <div class="chart-body" style="height:260px">
                <canvas id="chartMensal"></canvas>
            </div>
        </div>

        <!-- Status das Reservas -->
        <div class="chart-container">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Status das Reservas</div>
                    <div class="chart-subtitle">Distribuição por estado</div>
                </div>
            </div>
            <div class="chart-body" style="height:260px">
                <canvas id="chartStatus"></canvas>
            </div>
        </div>

        <!-- Salas Mais Utilizadas -->
        <div class="chart-container">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Salas Mais Utilizadas</div>
                    <div class="chart-subtitle">Top 5 por reservas</div>
                </div>
            </div>
            <div class="chart-body" style="height:260px">
                <canvas id="chartTopSalas"></canvas>
            </div>
        </div>

        <!-- Usuários Mais Ativos -->
        <div class="chart-container">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Usuários Mais Ativos</div>
                    <div class="chart-subtitle">Top 5 por reservas</div>
                </div>
            </div>
            <div class="chart-body" style="height:260px">
                <canvas id="chartTopUsuarios"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="charts-row" style="margin-top:1.5rem">

        <!-- Salas Mais Reservadas -->
        <div class="sc-card sc-card-gold">
            <div class="sc-card-header">
                <div>
                    <div class="sc-card-title"><i class="fas fa-trophy me-2 text-gold"></i>Salas Mais Reservadas</div>
                </div>
            </div>
            <div class="sc-table-wrapper" style="margin-top:1rem">
                <div class="sc-table-scroll">
                    <table class="sc-table sc-table-static">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Sala</th>
                                <th>Descrição</th>
                                <th>Reservas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $posicao = 1; foreach ($salasMaisReservadas as $sala): ?>
                            <tr>
                                <td>
                                    <?php if ($posicao == 1): ?>
                                        <i class="fas fa-medal" style="color:#c9a84c"></i>
                                    <?php elseif ($posicao == 2): ?>
                                        <i class="fas fa-medal" style="color:#a0a0b0"></i>
                                    <?php elseif ($posicao == 3): ?>
                                        <i class="fas fa-medal" style="color:#cd7f32"></i>
                                    <?php else: ?>
                                        <span style="color:#6b7280"><?php echo $posicao; ?>º</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong>Sala <?php echo htmlspecialchars($sala['NUM_SALA']); ?></strong></td>
                                <td style="color:#6b7280;font-size:.85rem"><?php echo htmlspecialchars(substr($sala['DESCRICAO'], 0, 30)); ?>...</td>
                                <td><span class="badge-status" style="background:rgba(201,168,76,.15);color:#b8963e;border-color:rgba(201,168,76,.3)"><?php echo $sala['total']; ?></span></td>
                            </tr>
                            <?php $posicao++; endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Usuários Mais Ativos -->
        <div class="sc-card sc-card-gold">
            <div class="sc-card-header">
                <div>
                    <div class="sc-card-title"><i class="fas fa-user-check me-2 text-gold"></i>Usuários Mais Ativos</div>
                </div>
            </div>
            <div class="sc-table-wrapper" style="margin-top:1rem">
                <div class="sc-table-scroll">
                    <table class="sc-table sc-table-static">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nome</th>
                                <th>Departamento</th>
                                <th>Reservas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $posicao = 1; foreach ($usuariosMaisAtivos as $usuario): ?>
                            <tr>
                                <td><span style="color:#6b7280"><?php echo $posicao; ?>º</span></td>
                                <td><?php echo htmlspecialchars($usuario['USUARIO_NOME']); ?></td>
                                <td style="color:#6b7280;font-size:.85rem"><?php echo htmlspecialchars($usuario['USUARIO_DEPARTAMENTO'] ?? '—'); ?></td>
                                <td><span class="badge-status" style="background:rgba(201,168,76,.15);color:#b8963e;border-color:rgba(201,168,76,.3)"><?php echo $usuario['total']; ?></span></td>
                            </tr>
                            <?php $posicao++; endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- /.charts-row -->

    <!-- Últimas Reservas -->
    <div class="sc-card" style="margin-top:1.5rem">
        <div class="sc-card-header">
            <div>
                <div class="sc-card-title"><i class="fas fa-history me-2 text-gold"></i>Últimas Reservas</div>
                <div class="sc-card-subtitle">10 reservas mais recentes</div>
            </div>
        </div>
        <div class="sc-table-wrapper" style="margin-top:1rem">
            <div class="sc-table-scroll">
                <table class="sc-table sc-table-static">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Sala</th>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Duração</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservasRecentes as $reserva): ?>
                        <?php
                            $statusMap = [
                                'Ativa'     => ['class' => 'badge-status-success', 'label' => 'Ativa'],
                                'Reservado' => ['class' => 'badge-status-info',    'label' => 'Reservado'],
                                'Pendente'  => ['class' => 'badge-status-warning', 'label' => 'Pendente'],
                                'Cancelada' => ['class' => 'badge-status-danger',  'label' => 'Cancelada'],
                            ];
                            $st = $statusMap[$reserva['ESTADO']] ?? ['class' => '', 'label' => $reserva['ESTADO']];
                        ?>
                        <tr>
                            <td><span style="color:#a0a0b0;font-size:.8rem">#<?php echo $reserva['RESERVA_ID']; ?></span></td>
                            <td><?php echo htmlspecialchars($reserva['USUARIO_NOME']); ?></td>
                            <td><strong>Sala <?php echo htmlspecialchars($reserva['NUM_SALA']); ?></strong></td>
                            <td><?php echo date('d/m/Y', strtotime($reserva['DATA_RESERVA'])); ?></td>
                            <td><?php echo date('H:i', strtotime($reserva['HORA_INICIO'])); ?></td>
                            <td><?php echo date('H:i', strtotime($reserva['TEMPO_RESERVA'])); ?></td>
                            <td><span class="badge-status <?php echo $st['class']; ?>"><?php echo $st['label']; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div><!-- /.reports-page -->

<!-- Chart.js Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const GOLD    = '#c9a84c';
    const CHARCOAL = '#1a1a2e';
    const AMBER   = '#d4a843';
    const GRAY    = '#6b7280';
    const BLUE    = '#3b82f6';
    const RED     = '#dc2626';

    Chart.defaults.font.family = '"DM Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
    Chart.defaults.color = GRAY;

    // ── 1. Line chart – Reservas por Mês ────────────────────────
    const mesesLabels = <?php echo json_encode($mesesLabels); ?>;
    const mesesValues = <?php echo json_encode($mesesValues); ?>;

    const ctxMensal = document.getElementById('chartMensal');
    if (ctxMensal) {
        const gradMensal = ctxMensal.getContext('2d').createLinearGradient(0, 0, 0, 260);
        gradMensal.addColorStop(0, 'rgba(201,168,76,0.25)');
        gradMensal.addColorStop(1, 'rgba(201,168,76,0.00)');

        new Chart(ctxMensal, {
            type: 'line',
            data: {
                labels: mesesLabels,
                datasets: [{
                    label: 'Reservas',
                    data: mesesValues,
                    borderColor: GOLD,
                    backgroundColor: gradMensal,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: GOLD,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: CHARCOAL,
                        titleFont: { weight: '600' },
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: GRAY },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        ticks: { color: GRAY },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ── 2. Doughnut – Status das Reservas ───────────────────────
    const ctxStatus = document.getElementById('chartStatus');
    if (ctxStatus) {
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Ativa', 'Reservado', 'Pendente', 'Cancelada'],
                datasets: [{
                    data: [
                        <?php echo (int)$reservasAtivas; ?>,
                        <?php echo (int)$reservasReservado; ?>,
                        <?php echo (int)$reservasPendente; ?>,
                        <?php echo (int)$reservasCanceladas; ?>
                    ],
                    backgroundColor: [GOLD, BLUE, GRAY, RED],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 16, usePointStyle: true, pointStyleWidth: 10 }
                    },
                    tooltip: {
                        backgroundColor: CHARCOAL,
                        padding: 12,
                        cornerRadius: 8
                    }
                }
            }
        });
    }

    // ── 3. Bar – Salas Mais Utilizadas ──────────────────────────
    const topSalasLabels = <?php echo json_encode($topSalasLabels); ?>;
    const topSalasValues = <?php echo json_encode($topSalasValues); ?>;

    const ctxTopSalas = document.getElementById('chartTopSalas');
    if (ctxTopSalas) {
        new Chart(ctxTopSalas, {
            type: 'bar',
            data: {
                labels: topSalasLabels,
                datasets: [{
                    label: 'Reservas',
                    data: topSalasValues,
                    backgroundColor: CHARCOAL,
                    borderRadius: 6,
                    hoverBackgroundColor: GOLD
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: CHARCOAL,
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: GRAY },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        ticks: { color: GRAY },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ── 4. Horizontal Bar – Usuários Mais Ativos ────────────────
    const topUsuariosLabels = <?php echo json_encode($topUsuariosLabels); ?>;
    const topUsuariosValues = <?php echo json_encode($topUsuariosValues); ?>;

    const ctxTopUsuarios = document.getElementById('chartTopUsuarios');
    if (ctxTopUsuarios) {
        new Chart(ctxTopUsuarios, {
            type: 'bar',
            data: {
                labels: topUsuariosLabels,
                datasets: [{
                    label: 'Reservas',
                    data: topUsuariosValues,
                    backgroundColor: AMBER,
                    borderRadius: 6,
                    hoverBackgroundColor: GOLD
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: CHARCOAL,
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: GRAY },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    y: {
                        ticks: { color: GRAY },
                        grid: { display: false }
                    }
                }
            }
        });
    }

});
</script>

<!-- Print Styles -->
<style>
@media print {
    .sc-sidebar,
    .sc-topbar,
    .page-header-right,
    .sidebar-overlay {
        display: none !important;
    }
    .sc-main {
        margin-left: 0 !important;
    }
    .sc-content {
        padding: 0 !important;
    }
    .sc-card,
    .chart-container,
    .summary-card {
        box-shadow: none !important;
        border: 1px solid #d1d5db !important;
        break-inside: avoid;
    }
    .charts-grid,
    .charts-row {
        grid-template-columns: 1fr 1fr !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
