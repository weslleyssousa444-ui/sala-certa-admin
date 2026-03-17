<?php
require_once __DIR__.'/conexao.php';
header('Content-Type: application/json; charset=utf-8');

// Aceita hora_inicio ou horario_inicio (compatibilidade)
$salaId       = isset($_POST['sala_id']) ? (int) $_POST['sala_id'] : 0;
$dataReserva  = $_POST['data_reserva']  ?? '';
$horaInicio   = $_POST['hora_inicio']   ?? ($_POST['horario_inicio'] ?? '');
$tempoReserva = $_POST['tempo_reserva'] ?? '';

if (!$salaId || !$dataReserva || !$horaInicio || !$tempoReserva) {
    echo json_encode(['error'=>'Dados incompletos']);
    exit;
}

try {
    $sql = "SELECT HORA_INICIO, TEMPO_RESERVA FROM RESERVA_SALA 
            WHERE SALA_ID = :sala AND DATA_RESERVA = :data AND ESTADO IN ('Reservado','Pendente')";
    $st = $pdo->prepare($sql);
    $st->execute([':sala'=>$salaId, ':data'=>$dataReserva]);

    $iniNovo = strtotime($dataReserva.' '.$horaInicio);
    list($h,$m,$s)= array_map('intval', explode(':',$tempoReserva));
    $fimNovo = $iniNovo + ($h*3600 + $m*60 + $s);
    $conflito = false;

    foreach ($st as $r) {
        $iniExist = strtotime($dataReserva.' '.$r['HORA_INICIO']);
        list($hh,$mm,$ss)= array_map('intval', explode(':',$r['TEMPO_RESERVA']));
        $fimExist = $iniExist + ($hh*3600 + $mm*60 + $ss);
        if ($iniNovo < $fimExist && $fimNovo > $iniExist) { $conflito = true; break; }
    }

    echo json_encode(['conflito'=>$conflito], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}