<?php

require_once __DIR__ . '/../config/conexao.php';

class Notificacao {

    // Criar nova notificação
    public static function criar($usuarioId, $tipo, $mensagem, $reservaId = null) {
        $conn = Conexao::getConn();

        $sql = "INSERT INTO NOTIFICACAO (USUARIO_ID, TIPO, MENSAGEM, RESERVA_ID, LIDA, DATA_CRIACAO)
                VALUES (:usuarioId, :tipo, :mensagem, :reservaId, 0, NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->bindValue(':tipo', $tipo);
        $stmt->bindValue(':mensagem', $mensagem);
        $stmt->bindValue(':reservaId', $reservaId);

        $stmt->execute();

        return $conn->lastInsertId();
    }

    // Listar notificações por usuário
    public static function listarPorUsuario($usuarioId, $limite = 20) {
        $conn = Conexao::getConn();

        $sql = "SELECT * FROM NOTIFICACAO
                WHERE USUARIO_ID = :usuarioId
                ORDER BY DATA_CRIACAO DESC
                LIMIT :limite";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Contar notificações não lidas
    public static function contarNaoLidas($usuarioId) {
        $conn = Conexao::getConn();

        $sql = "SELECT COUNT(*) AS total FROM NOTIFICACAO
                WHERE USUARIO_ID = :usuarioId
                AND LIDA = 0";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->execute();

        $resultado = $stmt->fetch();
        return (int)$resultado['total'];
    }

    // Marcar notificação como lida
    public static function marcarComoLida($notificacaoId) {
        $conn = Conexao::getConn();

        $sql = "UPDATE NOTIFICACAO SET LIDA = 1 WHERE NOTIFICACAO_ID = :notificacaoId";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':notificacaoId', $notificacaoId);

        return $stmt->execute();
    }

    // Marcar todas as notificações do usuário como lidas
    public static function marcarTodasComoLidas($usuarioId) {
        $conn = Conexao::getConn();

        $sql = "UPDATE NOTIFICACAO SET LIDA = 1 WHERE USUARIO_ID = :usuarioId AND LIDA = 0";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId);

        return $stmt->execute();
    }

    // Gerar lembretes para reservas próximas (dentro de 2 horas)
    public static function gerarLembretes($usuarioId) {
        $conn = Conexao::getConn();

        // Verifica preferências de notificação do usuário
        require_once __DIR__ . '/ConfiguracaoNotificacao.php';
        $config = ConfiguracaoNotificacao::buscarPorUsuario($usuarioId);

        if (empty($config['NOTIF_LEMBRETE'])) {
            return 0;
        }

        $hoje = date('Y-m-d');
        $agora = date('H:i:s');
        $doisHorasDepois = date('H:i:s', strtotime('+2 hours'));

        // Busca reservas do usuário nas próximas 2 horas
        $sql = "SELECT r.RESERVA_ID, r.HORA_INICIO, s.NUM_SALA
                FROM RESERVA_SALA r
                JOIN SALA s ON r.SALA_ID = s.SALA_ID
                WHERE r.USUARIO_ID = :usuarioId
                AND r.DATA_RESERVA = :hoje
                AND r.HORA_INICIO BETWEEN :agora AND :doisHorasDepois
                AND r.ESTADO IN ('Ativa', 'Reservado', 'Pendente')";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId);
        $stmt->bindValue(':hoje', $hoje);
        $stmt->bindValue(':agora', $agora);
        $stmt->bindValue(':doisHorasDepois', $doisHorasDepois);
        $stmt->execute();

        $reservas = $stmt->fetchAll();
        $criados = 0;

        foreach ($reservas as $reserva) {
            // Verifica se já existe lembrete para esta reserva
            $sqlVerifica = "SELECT COUNT(*) AS total FROM NOTIFICACAO
                            WHERE USUARIO_ID = :usuarioId
                            AND RESERVA_ID = :reservaId
                            AND TIPO = 'lembrete'";

            $stmtVerifica = $conn->prepare($sqlVerifica);
            $stmtVerifica->bindValue(':usuarioId', $usuarioId);
            $stmtVerifica->bindValue(':reservaId', $reserva['RESERVA_ID']);
            $stmtVerifica->execute();

            $existe = $stmtVerifica->fetch();

            if ((int)$existe['total'] === 0) {
                $horaFormatada = date('H:i', strtotime($reserva['HORA_INICIO']));
                $mensagem = "Sua reserva na Sala " . $reserva['NUM_SALA'] . " começa em breve (" . $horaFormatada . ")";

                self::criar($usuarioId, 'lembrete', $mensagem, $reserva['RESERVA_ID']);
                $criados++;
            }
        }

        return $criados;
    }
}
