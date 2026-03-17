<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/config.php';
require_once '../config/conexao.php';

// Only authenticated users may fetch events
session_start();
if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

try {
    $pdo = Conexao::getConn();

    // FullCalendar sends start and end as ISO date strings
    $start = $_GET['start'] ?? date('Y-m-01');
    $end   = $_GET['end']   ?? date('Y-m-t');

    // Strip time component if present (FullCalendar may send "2025-03-01T00:00:00")
    $start = substr($start, 0, 10);
    $end   = substr($end,   0, 10);

    $sql = "SELECT rs.RESERVA_ID,
                   rs.DATA_RESERVA,
                   rs.HORA_INICIO,
                   rs.TEMPO_RESERVA,
                   rs.ESTADO,
                   s.NUM_SALA,
                   u.USUARIO_NOME
            FROM   RESERVA_SALA rs
            JOIN   SALA    s ON rs.SALA_ID    = s.SALA_ID
            JOIN   USUARIO u ON rs.USUARIO_ID = u.USUARIO_ID
            WHERE  rs.DATA_RESERVA BETWEEN :start AND :end
            ORDER  BY rs.DATA_RESERVA, rs.HORA_INICIO";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':start' => $start, ':end' => $end]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Map status → FullCalendar className (matches .fc .fc-event.status-* in main.css)
    $statusClasses = [
        'Ativa'     => 'status-ativa',
        'Reservado' => 'status-reservado',
        'Pendente'  => 'status-pendente',
        'Cancelada' => 'status-cancelada',
    ];

    $events = [];

    foreach ($reservas as $r) {
        $inicioStr = $r['DATA_RESERVA'] . 'T' . $r['HORA_INICIO'];
        $startDt   = new DateTime($inicioStr);

        // TEMPO_RESERVA is stored as HH:MM:SS – extract hours and minutes
        $tempo  = new DateTime($r['TEMPO_RESERVA']);
        $endDt  = clone $startDt;
        $endDt->add(new DateInterval(
            'PT' . (int)$tempo->format('H') . 'H' . (int)$tempo->format('i') . 'M'
        ));

        $events[] = [
            'id'            => $r['RESERVA_ID'],
            'title'         => 'Sala ' . $r['NUM_SALA'] . ' – ' . $r['USUARIO_NOME'],
            'start'         => $startDt->format('Y-m-d\TH:i:s'),
            'end'           => $endDt->format('Y-m-d\TH:i:s'),
            'className'     => $statusClasses[$r['ESTADO']] ?? '',
            'extendedProps' => [
                'estado'  => $r['ESTADO'],
                'usuario' => $r['USUARIO_NOME'],
                'sala'    => $r['NUM_SALA'],
            ],
        ];
    }

    echo json_encode($events, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([]);
}
