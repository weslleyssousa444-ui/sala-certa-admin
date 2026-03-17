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
$sql = "SELECT u.USUARIO_NOME, u.USUARIO_EMAIL, COUNT(r.RESERVA_ID) as total
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

$pageTitle = 'Relatórios';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Header do Relatório -->
    <div class="relatorio-header text-center py-5 mb-4">
        <div class="container">
            <i class="fas fa-building fa-3x text-white mb-3"></i>
            <h1 class="text-white mb-2">Sala Certa</h1>
            <h3 class="text-white-50 mb-3">Relatório de Gestão de Reservas</h3>
            <p class="text-white-50 mb-0">Gerado em: <?php echo date('d/m/Y \à\s H:i'); ?></p>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Imprimir
                </button>
                <button class="btn btn-success" onclick="compartilharRelatorio()">
                    <i class="fas fa-share-alt me-2"></i>Compartilhar
                </button>
                <button class="btn btn-danger" onclick="exportarPDF()">
                    <i class="fas fa-file-pdf me-2"></i>Exportar PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Estatísticas Gerais -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-box bg-primary text-white">
                <div class="d-flex align-items-center">
                    <div class="icon me-3">
                        <i class="fas fa-calendar-check fa-3x"></i>
                    </div>
                    <div>
                        <h3 class="mb-0"><?php echo $totalReservas; ?></h3>
                        <p class="mb-0">Total de Reservas</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stat-box bg-success text-white">
                <div class="d-flex align-items-center">
                    <div class="icon me-3">
                        <i class="fas fa-check-circle fa-3x"></i>
                    </div>
                    <div>
                        <h3 class="mb-0"><?php echo $reservasAtivas; ?></h3>
                        <p class="mb-0">Reservas Ativas</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stat-box bg-info text-white">
                <div class="d-flex align-items-center">
                    <div class="icon me-3">
                        <i class="fas fa-users fa-3x"></i>
                    </div>
                    <div>
                        <h3 class="mb-0"><?php echo $totalUsuarios; ?></h3>
                        <p class="mb-0">Usuários Cadastrados</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stat-box bg-warning text-white">
                <div class="d-flex align-items-center">
                    <div class="icon me-3">
                        <i class="fas fa-door-open fa-3x"></i>
                    </div>
                    <div>
                        <h3 class="mb-0"><?php echo $totalSalas; ?></h3>
                        <p class="mb-0">Salas Disponíveis</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-chart-line text-primary me-2"></i>Reservas por Mês</h5>
                </div>
                <div class="card-body">
                    <canvas id="reservasMesChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-chart-pie text-success me-2"></i>Status das Reservas</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Salas Mais Reservadas E Usuários Mais Ativos - LADO A LADO -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-trophy me-2"></i>Salas Mais Reservadas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Posição</th>
                                    <th>Sala</th>
                                    <th>Descrição</th>
                                    <th>Total de Reservas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $posicao = 1; ?>
                                <?php foreach ($salasMaisReservadas as $sala): ?>
                                <tr>
                                    <td>
                                        <?php if ($posicao == 1): ?>
                                            <i class="fas fa-medal text-warning"></i>
                                        <?php elseif ($posicao == 2): ?>
                                            <i class="fas fa-medal text-secondary"></i>
                                        <?php elseif ($posicao == 3): ?>
                                            <i class="fas fa-medal" style="color: #cd7f32;"></i>
                                        <?php else: ?>
                                            <?php echo $posicao; ?>º
                                        <?php endif; ?>
                                    </td>
                                    <td><strong>Sala <?php echo $sala['NUM_SALA']; ?></strong></td>
                                    <td><?php echo substr($sala['DESCRICAO'], 0, 30); ?>...</td>
                                    <td><span class="badge bg-primary"><?php echo $sala['total']; ?></span></td>
                                </tr>
                                <?php $posicao++; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-user-check me-2"></i>Usuários Mais Ativos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Posição</th>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Total de Reservas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $posicao = 1; ?>
                                <?php foreach ($usuariosMaisAtivos as $usuario): ?>
                                <tr>
                                    <td><?php echo $posicao; ?>º</td>
                                    <td><?php echo $usuario['USUARIO_NOME']; ?></td>
                                    <td><?php echo $usuario['USUARIO_EMAIL']; ?></td>
                                    <td><span class="badge bg-success"><?php echo $usuario['total']; ?></span></td>
                                </tr>
                                <?php $posicao++; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Últimas Reservas -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-history text-primary me-2"></i>Últimas Reservas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
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
                                <tr>
                                    <td><span class="badge bg-secondary">#<?php echo $reserva['RESERVA_ID']; ?></span></td>
                                    <td><?php echo $reserva['USUARIO_NOME']; ?></td>
                                    <td><strong>Sala <?php echo $reserva['NUM_SALA']; ?></strong></td>
                                    <td><?php echo date('d/m/Y', strtotime($reserva['DATA_RESERVA'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($reserva['HORA_INICIO'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($reserva['TEMPO_RESERVA'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $reserva['ESTADO'] == 'Ativa' ? 'success' : 'danger'; ?>">
                                            <?php echo $reserva['ESTADO']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para os gráficos -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dados para o gráfico de Reservas por Mês
    const mesesLabels = <?php echo json_encode($mesesLabels); ?>;
    const mesesValues = <?php echo json_encode($mesesValues); ?>;
    
    // Gráfico de Linha - Reservas por Mês
    const ctxMes = document.getElementById('reservasMesChart');
    if (ctxMes) {
        new Chart(ctxMes, {
            type: 'line',
            data: {
                labels: mesesLabels,
                datasets: [{
                    label: 'Reservas',
                    data: mesesValues,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#667eea',
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
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    // Dados para o gráfico de Status
    const reservasAtivas = <?php echo $reservasAtivas; ?>;
    const reservasCanceladas = <?php echo $reservasCanceladas; ?>;
    
    // Gráfico de Rosca - Status das Reservas
    const ctxStatus = document.getElementById('statusChart');
    if (ctxStatus) {
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Ativas', 'Canceladas'],
                datasets: [{
                    data: [reservasAtivas, reservasCanceladas],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12
                    }
                }
            }
        });
    }
});

// Função para compartilhar
function compartilharRelatorio() {
    if (navigator.share) {
        navigator.share({
            title: 'Relatório Sala Certa',
            text: 'Confira o relatório de gestão de reservas',
            url: window.location.href
        }).catch(err => console.log('Erro ao compartilhar:', err));
    } else {
        alert('Compartilhamento não suportado neste navegador');
    }
}

// Função para exportar PDF
function exportarPDF() {
    alert('Funcionalidade de exportação PDF em desenvolvimento. Use a opção de impressão e salve como PDF.');
    window.print();
}
</script>

<!-- CSS Adicional para Relatórios -->
<style>
.relatorio-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 1rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
}

.stat-box {
    padding: 1.5rem;
    border-radius: 0.75rem;
    margin-bottom: 0;
    box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);
}

.stat-box h3 {
    font-size: 2rem;
    font-weight: 700;
}

.stat-box p {
    opacity: 0.9;
    font-size: 0.9rem;
    margin: 0;
}

canvas {
    max-height: 300px !important;
}

@media print {
    .btn,
    .navbar,
    .no-print {
        display: none !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
        break-inside: avoid;
    }
}
</style>

<?php include '../includes/footer.php'; ?>

