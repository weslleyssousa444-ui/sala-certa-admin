<?php
$pageTitle = 'Dashboard';
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

// Today's reservations for timeline
$sql = "SELECT r.*, u.USUARIO_NOME, s.NUM_SALA
        FROM RESERVA_SALA r
        JOIN USUARIO u ON r.USUARIO_ID = u.USUARIO_ID
        JOIN SALA s ON r.SALA_ID = s.SALA_ID
        WHERE r.DATA_RESERVA = :hoje
        ORDER BY r.HORA_INICIO ASC";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':hoje', $hoje);
$stmt->execute();
$reservasDeHoje = $stmt->fetchAll();

// Greeting based on hour
$hora = (int)date('H');
if ($hora < 12) $saudacao = 'Bom dia';
elseif ($hora < 18) $saudacao = 'Boa tarde';
else $saudacao = 'Boa noite';

// Portuguese date
$diasSemana = ['Sunday' => 'Domingo', 'Monday' => 'Segunda-feira', 'Tuesday' => 'Terça-feira',
               'Wednesday' => 'Quarta-feira', 'Thursday' => 'Quinta-feira', 'Friday' => 'Sexta-feira', 'Saturday' => 'Sábado'];
$meses = ['January' => 'Janeiro', 'February' => 'Fevereiro', 'March' => 'Março', 'April' => 'Abril',
          'May' => 'Maio', 'June' => 'Junho', 'July' => 'Julho', 'August' => 'Agosto',
          'September' => 'Setembro', 'October' => 'Outubro', 'November' => 'Novembro', 'December' => 'Dezembro'];
$dataHoje = $diasSemana[date('l')] . ', ' . date('d') . ' de ' . $meses[date('F')] . ' de ' . date('Y');

include 'includes/header.php';
?>

<div class="dashboard">

  <!-- Greeting Banner -->
  <div class="dashboard-greeting">
    <div class="greeting-text">
      <div class="greeting-label">Painel de Controle</div>
      <h2><?= htmlspecialchars($saudacao) ?>, <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?></h2>
      <p><?= htmlspecialchars($dataHoje) ?></p>
    </div>
    <div class="greeting-action">
      <a href="pages/reservas.php?nova=1" class="btn btn-gold">
        <i class="fas fa-plus"></i> Nova Reserva
      </a>
    </div>
  </div>

  <!-- Stats Row -->
  <div class="stats-row">

    <div class="stats-card">
      <div class="stats-card-inner">
        <div class="stats-card-data">
          <div class="stats-card-label">Total Reservas</div>
          <div class="stats-card-number count-up" data-target="<?= (int)$totalReservas ?>">0</div>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-calendar-check"></i>
        </div>
      </div>
    </div>

    <div class="stats-card">
      <div class="stats-card-inner">
        <div class="stats-card-data">
          <div class="stats-card-label">Reservas Hoje</div>
          <div class="stats-card-number count-up" data-target="<?= (int)$reservasHoje ?>">0</div>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-calendar-day"></i>
        </div>
      </div>
    </div>

    <div class="stats-card">
      <div class="stats-card-inner">
        <div class="stats-card-data">
          <div class="stats-card-label">Usuários Ativos</div>
          <div class="stats-card-number count-up" data-target="<?= (int)$totalUsuarios ?>">0</div>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-users"></i>
        </div>
      </div>
    </div>

    <div class="stats-card">
      <div class="stats-card-inner">
        <div class="stats-card-data">
          <div class="stats-card-label">Salas Disponíveis</div>
          <div class="stats-card-number count-up" data-target="<?= (int)$totalSalas ?>">0</div>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-door-open"></i>
        </div>
      </div>
    </div>

  </div><!-- /.stats-row -->

  <!-- Charts Row -->
  <div class="charts-row">

    <div class="chart-container">
      <div class="chart-header">
        <div class="chart-title">Reservas — Últimos 7 Dias</div>
      </div>
      <div class="chart-body">
        <?php if (array_sum($reservasPorDia['values']) === 0): ?>
        <div class="chart-empty">
          <i class="fas fa-chart-line"></i>
          <p>Dados insuficientes</p>
        </div>
        <?php else: ?>
        <canvas id="chartReservasDias"></canvas>
        <?php endif; ?>
      </div>
    </div>

    <div class="chart-container">
      <div class="chart-header">
        <div class="chart-title">Salas Mais Reservadas</div>
      </div>
      <div class="chart-body">
        <?php if (array_sum($reservasPorSala['values']) === 0): ?>
        <div class="chart-empty">
          <i class="fas fa-chart-bar"></i>
          <p>Dados insuficientes</p>
        </div>
        <?php else: ?>
        <canvas id="chartTopSalas"></canvas>
        <?php endif; ?>
      </div>
    </div>

  </div><!-- /.charts-row -->

  <!-- Recent Reservations Table -->
  <div class="sc-card">
    <div class="sc-card-header">
      <h4 class="sc-card-title">Reservas Recentes</h4>
      <a href="pages/reservas.php" class="sc-card-link" style="font-size:0.875rem;color:#c9a84c;text-decoration:none;font-weight:600;">Ver todas &rarr;</a>
    </div>

    <?php if (empty($ultimasReservas)): ?>
    <div class="empty-state empty-state-sm">
      <div class="empty-state-icon empty-icon-charcoal">
        <i class="fas fa-calendar"></i>
      </div>
      <div class="empty-state-title">Nenhuma reserva encontrada</div>
      <div class="empty-state-text">Ainda não existem reservas registadas no sistema.</div>
    </div>
    <?php else: ?>
    <div class="sc-table-wrapper">
      <div class="sc-table-scroll">
        <table class="sc-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Usuário</th>
              <th>Sala</th>
              <th>Data</th>
              <th>Hora</th>
              <th>Estado</th>
              <th class="col-actions">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($ultimasReservas as $reserva):
              $estado = $reserva['ESTADO'] ?? '';
              $estadoClass = 'badge-status badge-' . strtolower(str_replace(' ', '-', $estado));
            ?>
            <tr>
              <td><?= (int)$reserva['RESERVA_ID'] ?></td>
              <td><?= htmlspecialchars($reserva['USUARIO_NOME']) ?></td>
              <td>Sala <?= htmlspecialchars($reserva['NUM_SALA']) ?></td>
              <td><?= date('d/m/Y', strtotime($reserva['DATA_RESERVA'])) ?></td>
              <td><?= date('H:i', strtotime($reserva['HORA_INICIO'])) ?></td>
              <td>
                <span class="<?= htmlspecialchars($estadoClass) ?>">
                  <?= htmlspecialchars($estado) ?>
                </span>
              </td>
              <td>
                <a href="pages/ver_reserva.php?id=<?= (int)$reserva['RESERVA_ID'] ?>"
                   class="btn btn-sm btn-outline-secondary"
                   title="Ver detalhes">
                  <i class="fas fa-eye"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div><!-- /.sc-card -->

  <!-- Today's Timeline -->
  <div class="section-gap">
    <div class="dashboard-section-header">
      <h5 style="font-family:'Playfair Display',Georgia,serif;font-weight:600;color:#1a1a2e;margin:0;">
        Próximas Reservas de Hoje
      </h5>
    </div>

    <?php if (empty($reservasDeHoje)): ?>
    <div class="empty-state empty-state-sm empty-state-inline">
      <div class="empty-state-icon empty-icon-charcoal">
        <i class="fas fa-calendar"></i>
      </div>
      <div class="empty-state-body">
        <div class="empty-state-title">Nenhuma reserva agendada para hoje</div>
        <div class="empty-state-text">Não há reservas marcadas para o dia de hoje.</div>
      </div>
    </div>
    <?php else: ?>
    <div class="timeline">
      <?php foreach ($reservasDeHoje as $r):
        $estado = $r['ESTADO'] ?? '';
        $dotClass = 'timeline-dot';
        if (strtolower($estado) === 'ativa') $dotClass .= ' dot-green';
        elseif (strtolower($estado) === 'cancelada') $dotClass .= ' dot-red';
        else $dotClass .= ' dot-blue';
      ?>
      <div class="timeline-item">
        <div class="<?= $dotClass ?>"></div>
        <div class="timeline-content">
          <div class="timeline-header">
            <div class="timeline-title">
              Sala <?= htmlspecialchars($r['NUM_SALA']) ?> &mdash; <?= htmlspecialchars($r['USUARIO_NOME']) ?>
            </div>
            <div class="timeline-time">
              <?= date('H:i', strtotime($r['HORA_INICIO'])) ?>
              <?php if (!empty($r['HORA_FIM'])): ?>
              &ndash; <?= date('H:i', strtotime($r['HORA_FIM'])) ?>
              <?php endif; ?>
            </div>
          </div>
          <div class="timeline-meta">
            <div class="timeline-meta-item">
              <i class="fas fa-door-open"></i> Sala <?= htmlspecialchars($r['NUM_SALA']) ?>
            </div>
            <div class="timeline-meta-item">
              <span class="badge-status badge-<?= strtolower($estado) ?>">
                <?= htmlspecialchars($estado) ?>
              </span>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div><!-- /.timeline -->
    <?php endif; ?>
  </div><!-- /.section-gap -->

</div><!-- /.dashboard -->

<!-- Chart.js Configuration -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Count-up animation
    document.querySelectorAll('.count-up').forEach(function (el) {
        var target = parseInt(el.getAttribute('data-target'), 10) || 0;
        if (target === 0) { el.textContent = '0'; return; }
        var duration = 900;
        var steps = 40;
        var step = 0;
        var interval = setInterval(function () {
            step++;
            el.textContent = Math.round(target * (step / steps));
            if (step >= steps) { el.textContent = target; clearInterval(interval); }
        }, duration / steps);
    });

    var chartDefaults = {
        font: { family: "'DM Sans', sans-serif" },
        color: '#6b7280'
    };
    Chart.defaults.font.family = chartDefaults.font.family;
    Chart.defaults.color = chartDefaults.color;

    // Line chart — Reservations per day
    var ctxLine = document.getElementById('chartReservasDias');
    if (ctxLine) {
        var reservasPorDia = <?= json_encode($reservasPorDia) ?>;
        var gradLine = ctxLine.getContext('2d').createLinearGradient(0, 0, 0, 280);
        gradLine.addColorStop(0, 'rgba(201,168,76,0.25)');
        gradLine.addColorStop(1, 'rgba(201,168,76,0.01)');

        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: reservasPorDia.labels,
                datasets: [{
                    label: 'Reservas',
                    data: reservasPorDia.values,
                    borderColor: '#c9a84c',
                    backgroundColor: gradLine,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#c9a84c',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: "'DM Sans', sans-serif", size: 12 } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            font: { family: "'DM Sans', sans-serif", size: 12 }
                        },
                        grid: { color: 'rgba(26,26,46,0.06)' }
                    }
                }
            }
        });
    }

    // Bar chart — Top rooms
    var ctxBar = document.getElementById('chartTopSalas');
    if (ctxBar) {
        var reservasPorSala = <?= json_encode($reservasPorSala) ?>;

        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: reservasPorSala.labels,
                datasets: [{
                    label: 'Reservas',
                    data: reservasPorSala.values,
                    backgroundColor: 'rgba(26,26,46,0.75)',
                    hoverBackgroundColor: '#c9a84c',
                    borderWidth: 0,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: "'DM Sans', sans-serif", size: 12 } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            font: { family: "'DM Sans', sans-serif", size: 12 }
                        },
                        grid: { color: 'rgba(26,26,46,0.06)' }
                    }
                }
            }
        });
    }

});
</script>

<?php include 'includes/footer.php'; ?>
