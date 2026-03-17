<?php
require_once __DIR__.'/conexao.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $usuarioId    = isset($_POST['usuario_id']) ? (int) $_POST['usuario_id'] : 0;
    $salaId       = isset($_POST['sala_id']) ? (int) $_POST['sala_id'] : 0;
    $dataReserva  = $_POST['data_reserva']  ?? '';
    $horaInicio   = $_POST['hora_inicio']   ?? '';
    $tempoReserva = $_POST['tempo_reserva'] ?? '';
    $estado       = 'Reservado';

    if (!$usuarioId || !$salaId || !$dataReserva || !$horaInicio || !$tempoReserva) {
        echo json_encode(['status'=>'erro','message'=>'Dados incompletos.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Checagem de conflito inline (extra segurança)
    $sqlChk = "SELECT HORA_INICIO, TEMPO_RESERVA FROM RESERVA_SALA
               WHERE SALA_ID = :sala AND DATA_RESERVA = :data AND ESTADO IN ('Reservado','Pendente')";
    $chk = $pdo->prepare($sqlChk);
    $chk->execute([':sala'=>$salaId, ':data'=>$dataReserva]);
    $conflito = false;
    $iniNovo = strtotime($dataReserva.' '.$horaInicio);
    list($h,$m,$s) = array_map('intval', explode(':',$tempoReserva));
    $fimNovo = $iniNovo + ($h*3600 + $m*60 + $s);

    foreach ($chk as $r) {
        $iniExist = strtotime($dataReserva.' '.$r['HORA_INICIO']);
        list($hh,$mm,$ss)= array_map('intval', explode(':',$r['TEMPO_RESERVA']));
        $fimExist = $iniExist + ($hh*3600 + $mm*60 + $ss);
        if ($iniNovo < $fimExist && $fimNovo > $iniExist) { $conflito = true; break; }
    }
    if ($conflito) {
        echo json_encode(['status'=>'erro','message'=>'Conflito de horário.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sql = "INSERT INTO RESERVA_SALA (USUARIO_ID, SALA_ID, DATA_RESERVA, HORA_INICIO, TEMPO_RESERVA, ESTADO)
            VALUES (:usuario_id, :sala_id, :data_reserva, :hora_inicio, :tempo_reserva, :estado)";
    $st = $pdo->prepare($sql);
    $st->execute([
        ':usuario_id'=>$usuarioId, ':sala_id'=>$salaId, ':data_reserva'=>$dataReserva,
        ':hora_inicio'=>$horaInicio, ':tempo_reserva'=>$tempoReserva, ':estado'=>$estado
    ]);
    echo json_encode(['status'=>'sucesso','reserva_id'=>(int)$pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error'=>'Erro: '.$e->getMessage()], JSON_UNESCAPED_UNICODE);
}