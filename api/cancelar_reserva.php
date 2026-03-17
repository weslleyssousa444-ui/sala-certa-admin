<?php
require_once __DIR__.'/conexao.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_POST['reserva_id'], $_POST['usuario_id'])) {
    echo json_encode(['error'=>'Dados incompletos']);
    exit;
}
try {
    $st = $pdo->prepare("UPDATE RESERVA_SALA SET ESTADO='Cancelada' WHERE RESERVA_ID=:rid AND USUARIO_ID=:uid");
    $st->execute([':rid'=>(int)$_POST['reserva_id'], ':uid'=>(int)$_POST['usuario_id']]);
    echo json_encode(['status'=>'Reserva cancelada com sucesso']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}