<?php
require_once __DIR__.'/conexao.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (isset($_POST['USUARIO_NOME'], $_POST['USUARIO_EMAIL'], $_POST['USUARIO_SENHA'], $_POST['USUARIO_CPF'], $_POST['USUARIO_CARGO'])) {
        $nome  = trim($_POST['USUARIO_NOME']);
        $email = trim($_POST['USUARIO_EMAIL']);
        $senha = trim($_POST['USUARIO_SENHA']);
        $cpf   = trim($_POST['USUARIO_CPF']);
        $cargo = trim($_POST['USUARIO_CARGO']);

        // (Opcional) validar duplicidade de email/CPF
        $ck = $pdo->prepare("SELECT 1 FROM USUARIO WHERE USUARIO_EMAIL = :e OR USUARIO_CPF = :c LIMIT 1");
        $ck->execute([':e'=>$email, ':c'=>$cpf]);
        if ($ck->fetch()) {
            echo json_encode(['status'=>'error','message'=>'E-mail ou CPF já cadastrado.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (isset($_POST['USUARIO_DEPARTAMENTO']) && trim($_POST['USUARIO_DEPARTAMENTO']) !== '') {
            $departamento = trim($_POST['USUARIO_DEPARTAMENTO']);
            $sql = "INSERT INTO USUARIO (USUARIO_NOME, USUARIO_EMAIL, USUARIO_SENHA, USUARIO_CPF, USUARIO_CARGO, USUARIO_DEPARTAMENTO)
                    VALUES (:n,:e,:s,:c,:u,:d)";
            $st = $pdo->prepare($sql);
            $st->execute([':n'=>$nome, ':e'=>$email, ':s'=>$senha, ':c'=>$cpf, ':u'=>$cargo, ':d'=>$departamento]);
        } else {
            $sql = "INSERT INTO USUARIO (USUARIO_NOME, USUARIO_EMAIL, USUARIO_SENHA, USUARIO_CPF, USUARIO_CARGO)
                    VALUES (:n,:e,:s,:c,:u)";
            $st = $pdo->prepare($sql);
            $st->execute([':n'=>$nome, ':e'=>$email, ':s'=>$senha, ':c'=>$cpf, ':u'=>$cargo]);
        }
        echo json_encode(['status'=>'success','message'=>'Usuário cadastrado com sucesso!'], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['status'=>'error','message'=>'Dados incompletos'], JSON_UNESCAPED_UNICODE);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Erro: '.$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
