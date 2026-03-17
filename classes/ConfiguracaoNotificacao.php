<?php

require_once __DIR__ . '/../config/conexao.php';

class ConfiguracaoNotificacao {

    // Buscar configuração por usuário; cria padrão (tudo ativo) se não existir
    public static function buscarPorUsuario($usuarioId) {
        $conn = Conexao::getConn();

        $sql = "SELECT * FROM CONFIGURACAO_NOTIFICACAO WHERE USUARIO_ID = :usuarioId";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->execute();

        $config = $stmt->fetch();

        if (!$config) {
            // Cria configuração padrão com tudo ativo
            $sqlInsert = "INSERT INTO CONFIGURACAO_NOTIFICACAO (USUARIO_ID, NOTIF_CONFIRMACAO, NOTIF_LEMBRETE, NOTIF_CANCELAMENTO)
                          VALUES (:usuarioId, 1, 1, 1)";

            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bindValue(':usuarioId', $usuarioId);
            $stmtInsert->execute();

            // Busca o registro recém-criado
            $stmt->execute();
            $config = $stmt->fetch();
        }

        return $config;
    }

    // Atualizar configuração de notificação (INSERT ON DUPLICATE KEY UPDATE)
    public static function atualizar($usuarioId, $confirmacao, $lembrete, $cancelamento) {
        $conn = Conexao::getConn();

        $sql = "INSERT INTO CONFIGURACAO_NOTIFICACAO (USUARIO_ID, NOTIF_CONFIRMACAO, NOTIF_LEMBRETE, NOTIF_CANCELAMENTO)
                VALUES (:usuarioId, :confirmacao, :lembrete, :cancelamento)
                ON DUPLICATE KEY UPDATE
                    NOTIF_CONFIRMACAO = :confirmacao2,
                    NOTIF_LEMBRETE = :lembrete2,
                    NOTIF_CANCELAMENTO = :cancelamento2";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->bindValue(':confirmacao', (int)$confirmacao, PDO::PARAM_INT);
        $stmt->bindValue(':lembrete', (int)$lembrete, PDO::PARAM_INT);
        $stmt->bindValue(':cancelamento', (int)$cancelamento, PDO::PARAM_INT);
        $stmt->bindValue(':confirmacao2', (int)$confirmacao, PDO::PARAM_INT);
        $stmt->bindValue(':lembrete2', (int)$lembrete, PDO::PARAM_INT);
        $stmt->bindValue(':cancelamento2', (int)$cancelamento, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
