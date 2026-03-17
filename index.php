<?php
require_once 'config/config.php';
require_once 'config/conexao.php';
require_once 'classes/Reserva.php';
require_once 'classes/Sala.php';
require_once 'classes/Usuario.php';

requireLogin();

function contarReservasPorDia($dias = 7) {
    $conn = Conexao::getConn();
    $labels = [];
    $values = [];

    $hoje = date('Y-m-d');

    for ($i = 0; $i < $dias; $i++) {
        $data_consulta = date('Y-m-d', strtotime($hoje . ' + ' . $i . ' days'));

        $sql = "SELECT COUNT(*) as total FROM RESERVA_SALA WHERE DATA_RESERVA = :data";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':data', $data_consulta);
        $stmt->execute();

        $resultado = $stmt->fetch();
        $total = $resultado['total'];

        $labels[] = date('d/m', strtotime($data_consulta));
        $values[] = (int)$total;
    }

    return ['labels' => $labels, 'values' => $values];
}

function contarReservasPorSala() {
    $conn = Conexao::getConn();

    $sql = "SELECT s.NUM_SALA, COUNT(r.RESERVA_ID) as total
            FROM SALA s
            LEFT JOIN RESERVA_SALA r ON s.SALA_ID = r.SALA_ID
            GROUP BY s.SALA_ID
            ORDER BY total DESC
            LIMIT 5";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $resultado = $stmt->fetchAll();

    $labels = [];
    $values = [];

    foreach ($resultado as $row) {
        $labels[] = 'Sala ' . $row['NUM_SALA'];
        $values[] = (int)$row['total'];
    }

    return ['labels' => $labels, 'values' => $values];
}

$totalUsuarios = count(Usuario::listarTodos());
$totalSalas = count(Sala::listarTodas());

$conn = Conexao::getConn();

$sql = "SELECT COUNT(*) as total FROM RESERVA_SALA";
$stmt = $conn->prepare($sql);
$stmt->execute();
$resultado = $stmt->fetch();
$totalReservas = $resultado['total'];

$hoje = date('Y-m-d');
$sql = "SELECT COUNT(*) as total FROM RESERVA_SALA WHERE DATA_RESERVA = :hoje";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':hoje', $hoje);
$stmt->execute();
$resultado = $stmt->fetch();
$reservasHoje = $resultado['total'];

$reservasPorDia = contarReservasPorDia();
$reservasPorSala = contarReservasPorSala();

$sql = "SELECT r.*, u.USUARIO_NOME, s.NUM_SALA
        FROM RESERVA_SALA r
        JOIN USUARIO u ON r.USUARIO_ID = u.USUARIO_ID
        JOIN SALA s ON r.SALA_ID = s.SALA_ID
        ORDER BY r.DATA_RESERVA DESC, r.HORA_INICIO DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->execute();
$ultimasReservas = $stmt->fetchAll();

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h2 class="mb-4">Dashboard</h2>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
        <div class="stats-card bg-primary text-white">
            <div class="icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="info">
                <h4><?php echo $totalReservas; ?></h4>
                <p>Reservas Totais</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
        <div class="stats-card bg-success text-white">
            <div class="icon">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="info">
                <h4><?php echo $reservasHoje; ?></h4>
                <p>Reservas Hoje</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
        <div class="stats-card bg-info text-white">
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="info">
                <h4><?php echo $totalUsuarios; ?></h4>
                <p>Usuários</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
        <div class="stats-card bg-warning text-white">
            <div class="icon">
                <i class="fas fa-door-open"></i>
            </div>
            <div class="info">
                <h4><?php echo $totalSalas; ?></h4>
                <p>Salas</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Reservas por Dia</h5>
            </div>
            <div class="card-body">
                <canvas id="reservasChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Salas mais Reservadas</h5>
            </div>
            <div class="card-body">
                <canvas id="salasChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Últimas Reservas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuário</th>
                                <th>Sala</th>
                                <th>Data</th>
                                <th>Hora</th>
                                <th>Estado</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimasReservas as $reserva): ?>
                            <tr>
                                <td><?php echo $reserva['RESERVA_ID']; ?></td>
                                <td><?php echo $reserva['USUARIO_NOME']; ?></td>
                                <td>Sala <?php echo $reserva['NUM_SALA']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($reserva['DATA_RESERVA'])); ?></td>
                                <td><?php echo date('H:i', strtotime($reserva['HORA_INICIO'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $reserva['ESTADO'] == 'Ativa' ? 'success' : 'danger'; ?>">
                                        <?php echo $reserva['ESTADO']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="pages/ver_reserva.php?id=<?php echo $reserva['RESERVA_ID']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Ver detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($ultimasReservas)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Nenhuma reserva encontrada</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <a href="pages/reservas.php" class="btn btn-primary mt-3">Ver todas as reservas</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var reservasPorDiaData = <?php echo json_encode($reservasPorDia); ?>;

    var ctxReservas = document.getElementById('reservasChart');
    if (ctxReservas) {
        new Chart(ctxReservas, {
            type: 'line',
            data: {
                labels: reservasPorDiaData.labels,
                datasets: [{
                    label: 'Reservas',
                    data: reservasPorDiaData.values,
                    borderColor: '#37D0C0',
                    backgroundColor: 'rgba(55, 208, 192, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    var reservasPorSalaData = <?php echo json_encode($reservasPorSala); ?>;

    var ctxSalas = document.getElementById('salasChart');
    if (ctxSalas) {
        new Chart(ctxSalas, {
            type: 'bar',
            data: {
                labels: reservasPorSalaData.labels,
                datasets: [{
                    label: 'Reservas',
                    data: reservasPorSalaData.values,
                    backgroundColor: [
                        '#37D0C0',
                        '#53C598',
                        '#B3E2D0',
                        '#7fcfba',
                        '#a5e0cf'
                    ],
                    borderWidth: 0,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
