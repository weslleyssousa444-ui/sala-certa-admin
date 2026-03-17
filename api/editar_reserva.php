<?php
require_once __DIR__.'/conexao.php';
header('Content-Type: application/json; charset=utf-8');

function bad($m,$c=400){ http_response_code($c); echo json_encode(['status'=>'erro','error'=>$m], JSON_UNESCAPED_UNICODE); exit; }

$reservaId = isset($_POST['reserva_id']) ? (int) $_POST['reserva_id'] : 0;
$usuarioId = isset($_POST['usuario_id']) ? (int) $_POST['usuario_id'] : 0;
$data      = $_POST['data_reserva']  ?? '';
$hora      = $_POST['hora_inicio']   ?? '';
$tempo     = $_POST['tempo_reserva'] ?? '';
$estado    = $_POST['estado']        ?? 'Reservado';

if (!$reservaId || !$usuarioId || !$data || !$hora || !$tempo) bad('Dados incompletos.');

try {
    // Checa conflito (ignora a própria reserva)
    $q = "SELECT HORA_INICIO, TEMPO_RESERVA FROM RESERVA_SALA
          WHERE SALA_ID = (SELECT SALA_ID FROM RESERVA_SALA WHERE RESERVA_ID = :rid)
            AND DATA_RESERVA = :data
            AND ESTADO IN ('Reservado','Pendente')
            AND RESERVA_ID <> :rid";
    $st = $pdo->prepare($q);
    $st->execute([':rid'=>$reservaId, ':data'=>$data]);

    $iniNovo = strtotime($data.' '.$hora);
    list($h,$m,$s)= array_map('intval', explode(':',$tempo));
    $fimNovo = $iniNovo + ($h*3600 + $m*60 + $s);

    foreach ($st as $r) {
        $iniExist = strtotime($data.' '.$r['HORA_INICIO']);
        list($hh,$mm,$ss)= array_map('intval', explode(':',$r['TEMPO_RESERVA']));
        $fimExist = $iniExist + ($hh*3600 + $mm*60 + $ss);
        if ($iniNovo < $fimExist && $fimNovo > $iniExist) bad('Conflito de horário.');
    }

    $u = $pdo->prepare("UPDATE RESERVA_SALA SET DATA_RESERVA=:d, HORA_INICIO=:h, TEMPO_RESERVA=:t, ESTADO=:e WHERE RESERVA_ID=:rid AND USUARIO_ID=:uid");
    $u->execute([':d'=>$data, ':h'=>$hora, ':t'=>$tempo, ':e'=>$estado, ':rid'=>$reservaId, ':uid'=>$usuarioId]);
    echo json_encode(['status'=>'sucesso'], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    bad('Erro de banco: '.$e->getMessage(), 500);
}