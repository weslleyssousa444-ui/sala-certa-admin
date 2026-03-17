<?php
require_once __DIR__.'/conexao.php';
header('Content-Type: application/json; charset=utf-8');

$usuarioId = isset($_GET['usuario_id']) ? (int) $_GET['usuario_id'] : 0;
$estados   = isset($_GET['estado']) ? (array) $_GET['estado'] : null; // ex.: estado[]=Reservado&estado[]=Pendente
$debug     = isset($_GET['debug']) ? (int) $_GET['debug'] : 0;

try {
    if (!$usuarioId) {
        http_response_code(200);
        echo json_encode([], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sql = "SELECT RESERVA_ID, USUARIO_ID, SALA_ID, DATA_RESERVA, HORA_INICIO, TEMPO_RESERVA, ESTADO
            FROM RESERVA_SALA WHERE USUARIO_ID = :usuario";
    $params = [':usuario'=>$usuarioId];

    if (is_array($estados) && count($estados) > 0) {
        // Filtra pelos estados enviados
        $in = implode(',', array_fill(0, count($estados), '?'));
        $sql .= " AND ESTADO IN ($in)";
        $params = array_merge($params, $estados);
        // PDO -> numeradas
        $st = $pdo->prepare(str_replace(':usuario','?',$sql) . " ORDER BY DATA_RESERVA, HORA_INICIO");
        $bind = array_merge([$usuarioId], $estados);
        $st->execute($bind);
    } else {
        // Sem filtro: devolve tudo (inclui Cancelada)
        $sql .= " ORDER BY DATA_RESERVA, HORA_INICIO";
        $st = $pdo->prepare($sql);
        $st->execute([':usuario'=>$usuarioId]);
    }

    $rows = $st->fetchAll();

    if ($debug) {
        http_response_code(200);
        echo json_encode([
            'input'=>['usuario_id'=>$usuarioId,'estado'=>$estados],
            'count'=>count($rows),
            'amostra'=>array_slice($rows,0,5)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(200);
    echo json_encode($rows ?: [], JSON_UNESCAPED_UNICODE);
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