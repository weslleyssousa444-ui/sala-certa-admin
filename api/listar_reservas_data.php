<?php
require_once __DIR__.'/conexao.php';
header('Content-Type: application/json; charset=utf-8');

$salaId = isset($_GET['sala_id']) ? (int) $_GET['sala_id'] : 0;
$data   = $_GET['data_reserva'] ?? '';
$estados = isset($_GET['estado']) ? (array) $_GET['estado'] : ['Reservado','Pendente']; // padrão
$debug  = isset($_GET['debug']) ? (int) $_GET['debug'] : 0;

try {
    if (!$salaId || !$data) {
        http_response_code(200);
        echo json_encode([], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $in = implode(',', array_fill(0, count($estados), '?'));
    $sql = "SELECT HORA_INICIO, TEMPO_RESERVA FROM RESERVA_SALA
            WHERE SALA_ID = ? AND DATA_RESERVA = ? AND ESTADO IN ($in)
            ORDER BY HORA_INICIO";
    $params = array_merge([$salaId, $data], $estados);
    $st = $pdo->prepare($sql);
    $st->execute($params);

    $slots = [];
    foreach ($st as $r) {
        $ini = strtotime($data.' '.$r['HORA_INICIO']);
        list($h,$m,$s) = array_map('intval', explode(':',$r['TEMPO_RESERVA']));
        $fim = $ini + ($h*3600 + $m*60 + $s);
        $slots[] = date('H:i', $ini).' - '.date('H:i', $fim);
    }

    if ($debug) {
        http_response_code(200);
        echo json_encode([
            'input'=>['sala_id'=>$salaId,'data_reserva'=>$data,'estado'=>$estados],
            'intervalos'=>$slots,
            'total'=>count($slots)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(200);
    echo json_encode($slots, JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    if ($debug) {
        http_response_code(200);
        echo json_encode(['error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
    http_response_code(200);
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}