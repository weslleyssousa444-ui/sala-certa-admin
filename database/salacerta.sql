-- =============================================
-- SALA CERTA - Script de Criação do Banco de Dados
-- Banco: u400600347_salacerta
-- Hostinger: saddlebrown-ape-456330.hostingersite.com
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "-03:00";

-- =============================================
-- TABELA: USUARIO
-- =============================================
DROP TABLE IF EXISTS `RESERVA_SALA`;
DROP TABLE IF EXISTS `SALA`;
DROP TABLE IF EXISTS `USUARIO`;

CREATE TABLE `USUARIO` (
    `USUARIO_ID` INT(11) NOT NULL AUTO_INCREMENT,
    `USUARIO_NOME` VARCHAR(255) NOT NULL,
    `USUARIO_EMAIL` VARCHAR(255) NOT NULL,
    `USUARIO_SENHA` VARCHAR(255) NOT NULL,
    `USUARIO_CPF` VARCHAR(14) NOT NULL,
    `USUARIO_CURSO` VARCHAR(255) DEFAULT NULL,
    `TIPO_USUARIO` ENUM('admin', 'comum') NOT NULL DEFAULT 'comum',
    `SETOR` VARCHAR(255) DEFAULT '',
    `FOTO_PERFIL` VARCHAR(500) DEFAULT NULL,
    PRIMARY KEY (`USUARIO_ID`),
    UNIQUE KEY `uk_usuario_email` (`USUARIO_EMAIL`),
    UNIQUE KEY `uk_usuario_cpf` (`USUARIO_CPF`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABELA: SALA
-- =============================================
CREATE TABLE `SALA` (
    `SALA_ID` INT(11) NOT NULL AUTO_INCREMENT,
    `NUM_SALA` VARCHAR(50) NOT NULL,
    `QTD_PESSOAS` INT(11) NOT NULL DEFAULT 0,
    `DESCRICAO` TEXT DEFAULT NULL,
    `TIPO_SALA` VARCHAR(100) DEFAULT '',
    `SETOR_RESPONSAVEL` VARCHAR(255) DEFAULT '',
    PRIMARY KEY (`SALA_ID`),
    UNIQUE KEY `uk_num_sala` (`NUM_SALA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABELA: RESERVA_SALA
-- =============================================
CREATE TABLE `RESERVA_SALA` (
    `RESERVA_ID` INT(11) NOT NULL AUTO_INCREMENT,
    `USUARIO_ID` INT(11) NOT NULL,
    `SALA_ID` INT(11) NOT NULL,
    `DATA_RESERVA` DATE NOT NULL,
    `HORA_INICIO` TIME NOT NULL,
    `TEMPO_RESERVA` TIME NOT NULL,
    `ESTADO` ENUM('Ativa', 'Cancelada', 'Pendente', 'Reservado') NOT NULL DEFAULT 'Ativa',
    PRIMARY KEY (`RESERVA_ID`),
    KEY `idx_reserva_usuario` (`USUARIO_ID`),
    KEY `idx_reserva_sala` (`SALA_ID`),
    KEY `idx_reserva_data` (`DATA_RESERVA`),
    KEY `idx_reserva_estado` (`ESTADO`),
    CONSTRAINT `fk_reserva_usuario` FOREIGN KEY (`USUARIO_ID`) REFERENCES `USUARIO` (`USUARIO_ID`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_reserva_sala` FOREIGN KEY (`SALA_ID`) REFERENCES `SALA` (`SALA_ID`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DADOS: USUÁRIOS
-- =============================================
-- Admin: salacerta@salacerta.com / senha: admin123
-- Hash bcrypt de "admin123"
INSERT INTO `USUARIO` (`USUARIO_ID`, `USUARIO_NOME`, `USUARIO_EMAIL`, `USUARIO_SENHA`, `USUARIO_CPF`, `USUARIO_CURSO`, `TIPO_USUARIO`, `SETOR`) VALUES
(1, 'Administrador Sala Certa', 'salacerta@salacerta.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '000.000.000-00', 'Administração', 'admin', 'TI');

-- Usuário de teste 1: maria@teste.com / senha: teste123
INSERT INTO `USUARIO` (`USUARIO_ID`, `USUARIO_NOME`, `USUARIO_EMAIL`, `USUARIO_SENHA`, `USUARIO_CPF`, `USUARIO_CURSO`, `TIPO_USUARIO`, `SETOR`) VALUES
(2, 'Maria Silva Santos', 'maria@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '111.222.333-44', 'Sistemas de Informação', 'comum', '');

-- Usuário de teste 2: joao@teste.com / senha: teste123
INSERT INTO `USUARIO` (`USUARIO_ID`, `USUARIO_NOME`, `USUARIO_EMAIL`, `USUARIO_SENHA`, `USUARIO_CPF`, `USUARIO_CURSO`, `TIPO_USUARIO`, `SETOR`) VALUES
(3, 'João Pedro Oliveira', 'joao@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555.666.777-88', 'Engenharia de Software', 'comum', '');

-- =============================================
-- DADOS: SALAS
-- =============================================
INSERT INTO `SALA` (`SALA_ID`, `NUM_SALA`, `QTD_PESSOAS`, `DESCRICAO`, `TIPO_SALA`, `SETOR_RESPONSAVEL`) VALUES
(1, '101', 30, 'Sala de aula padrão com projetor multimídia e quadro branco', 'Sala de Aula', 'Acadêmico'),
(2, '102', 40, 'Sala de aula ampla com ar condicionado e projetor', 'Sala de Aula', 'Acadêmico'),
(3, '103', 25, 'Sala de aula com TV interativa', 'Sala de Aula', 'Acadêmico'),
(4, 'LAB-01', 25, 'Laboratório de Informática com 25 computadores e projetor', 'Laboratório', 'TI'),
(5, 'LAB-02', 20, 'Laboratório de Informática com 20 computadores', 'Laboratório', 'TI'),
(6, 'LAB-03', 30, 'Laboratório de Redes e Infraestrutura', 'Laboratório', 'TI'),
(7, 'AUD-01', 100, 'Auditório principal com sistema de som e projetor', 'Auditório', 'Eventos'),
(8, 'REU-01', 10, 'Sala de reuniões com TV e videoconferência', 'Sala de Reunião', 'Administrativo'),
(9, 'REU-02', 8, 'Sala de reuniões pequena', 'Sala de Reunião', 'Administrativo'),
(10, 'BIB-01', 50, 'Sala de estudos da biblioteca', 'Biblioteca', 'Acadêmico');

-- =============================================
-- DADOS: RESERVAS DE TESTE
-- =============================================

-- Reservas da Maria (USUARIO_ID = 2)
INSERT INTO `RESERVA_SALA` (`USUARIO_ID`, `SALA_ID`, `DATA_RESERVA`, `HORA_INICIO`, `TEMPO_RESERVA`, `ESTADO`) VALUES
(2, 1, '2026-03-17', '08:00:00', '01:30:00', 'Ativa'),
(2, 4, '2026-03-17', '10:00:00', '02:00:00', 'Ativa'),
(2, 7, '2026-03-18', '14:00:00', '01:00:00', 'Ativa'),
(2, 2, '2026-03-19', '09:00:00', '01:30:00', 'Pendente'),
(2, 5, '2026-03-15', '08:00:00', '02:00:00', 'Cancelada');

-- Reservas do João (USUARIO_ID = 3)
INSERT INTO `RESERVA_SALA` (`USUARIO_ID`, `SALA_ID`, `DATA_RESERVA`, `HORA_INICIO`, `TEMPO_RESERVA`, `ESTADO`) VALUES
(3, 3, '2026-03-17', '13:00:00', '01:30:00', 'Ativa'),
(3, 6, '2026-03-17', '15:00:00', '02:00:00', 'Ativa'),
(3, 8, '2026-03-18', '10:00:00', '01:00:00', 'Reservado'),
(3, 1, '2026-03-20', '14:00:00', '01:30:00', 'Ativa'),
(3, 10, '2026-03-16', '09:00:00', '03:00:00', 'Ativa');

-- Reservas do Admin (USUARIO_ID = 1) - para ter dados no dashboard
INSERT INTO `RESERVA_SALA` (`USUARIO_ID`, `SALA_ID`, `DATA_RESERVA`, `HORA_INICIO`, `TEMPO_RESERVA`, `ESTADO`) VALUES
(1, 9, '2026-03-17', '09:00:00', '01:00:00', 'Ativa'),
(1, 4, '2026-03-18', '08:00:00', '02:00:00', 'Ativa');

COMMIT;
