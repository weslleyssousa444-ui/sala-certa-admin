-- =============================================
-- SALA CERTA - Script Completo de Criação do Banco de Dados
-- Banco: u400600347_salacerta
-- Hostinger: saddlebrown-ape-456330.hostingersite.com
-- Versão: 1.0.0
-- Data: 2026-03-16
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "-03:00";

-- Charset e Collation do banco
ALTER DATABASE `u400600347_salacerta` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- REMOVER TABELAS EXISTENTES (ordem inversa por FK)
-- =============================================
DROP TABLE IF EXISTS `RESERVA_SALA`;
DROP TABLE IF EXISTS `SALA`;
DROP TABLE IF EXISTS `USUARIO`;

-- =============================================
-- TABELA: USUARIO
-- Armazena todos os usuários do sistema (admin e comuns)
-- O login do painel admin usa USUARIO_EMAIL + USUARIO_SENHA
-- A API mobile também consulta esta tabela
-- TIPO_USUARIO: 'admin' pode acessar o painel web
--               'comum' acessa apenas o app mobile
-- SETOR: se preenchido, permite acesso ao painel mesmo sem ser admin
-- FOTO_PERFIL: caminho relativo do arquivo de foto (uploads/perfil/)
-- =============================================
CREATE TABLE `USUARIO` (
    `USUARIO_ID` INT(11) NOT NULL AUTO_INCREMENT,
    `USUARIO_NOME` VARCHAR(255) NOT NULL,
    `USUARIO_EMAIL` VARCHAR(255) NOT NULL,
    `USUARIO_SENHA` VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt via password_hash() ou texto puro (legado)',
    `USUARIO_CPF` VARCHAR(14) NOT NULL COMMENT 'Formato: 000.000.000-00 ou apenas números',
    `USUARIO_CURSO` VARCHAR(255) DEFAULT NULL,
    `TIPO_USUARIO` ENUM('admin', 'comum') NOT NULL DEFAULT 'comum',
    `SETOR` VARCHAR(255) DEFAULT '' COMMENT 'Se preenchido, permite acesso ao painel admin',
    `FOTO_PERFIL` VARCHAR(500) DEFAULT NULL COMMENT 'Caminho relativo: uploads/perfil/user_X_timestamp.png',
    PRIMARY KEY (`USUARIO_ID`),
    UNIQUE KEY `uk_usuario_email` (`USUARIO_EMAIL`),
    UNIQUE KEY `uk_usuario_cpf` (`USUARIO_CPF`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABELA: SALA
-- Armazena as salas disponíveis para reserva
-- NUM_SALA segue convenção de andares:
--   Térreo: T01, T02, T03...
--   1º Andar: 101, 102, 103...
--   2º Andar: 201, 202, 203...
--   Especiais: LAB-01, AUD-01, REU-01, BIB-01
-- TIPO_SALA: usado no frontend para badges e filtros
-- SETOR_RESPONSAVEL: setor que gerencia a sala
-- =============================================
CREATE TABLE `SALA` (
    `SALA_ID` INT(11) NOT NULL AUTO_INCREMENT,
    `NUM_SALA` VARCHAR(50) NOT NULL COMMENT 'Número/código da sala (ex: 101, T01, LAB-01)',
    `QTD_PESSOAS` INT(11) NOT NULL DEFAULT 0 COMMENT 'Capacidade máxima de pessoas',
    `DESCRICAO` TEXT DEFAULT NULL COMMENT 'Descrição da sala e recursos disponíveis',
    `TIPO_SALA` VARCHAR(100) DEFAULT '' COMMENT 'Tipo: Sala de Aula, Laboratório, Auditório, Sala de Reunião, Biblioteca',
    `SETOR_RESPONSAVEL` VARCHAR(255) DEFAULT '' COMMENT 'Setor responsável pela sala',
    PRIMARY KEY (`SALA_ID`),
    UNIQUE KEY `uk_num_sala` (`NUM_SALA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABELA: RESERVA_SALA
-- Armazena todas as reservas de salas
-- HORA_INICIO: horário de início (TIME)
-- TEMPO_RESERVA: duração em formato TIME (ex: 01:30:00 = 1h30min)
-- ESTADO: controla o ciclo de vida da reserva
--   'Ativa'     - Reserva confirmada e ativa (painel admin)
--   'Reservado'  - Reserva feita pelo app mobile (aguardando)
--   'Pendente'   - Reserva pendente de confirmação
--   'Cancelada'  - Reserva cancelada pelo usuário ou admin
-- A verificação de conflitos usa ESTADO IN ('Ativa','Reservado','Pendente')
-- Foreign Keys com RESTRICT impedem exclusão de usuários/salas com reservas
-- =============================================
CREATE TABLE `RESERVA_SALA` (
    `RESERVA_ID` INT(11) NOT NULL AUTO_INCREMENT,
    `USUARIO_ID` INT(11) NOT NULL,
    `SALA_ID` INT(11) NOT NULL,
    `DATA_RESERVA` DATE NOT NULL COMMENT 'Data da reserva (YYYY-MM-DD)',
    `HORA_INICIO` TIME NOT NULL COMMENT 'Horário de início (HH:MM:SS)',
    `TEMPO_RESERVA` TIME NOT NULL COMMENT 'Duração da reserva (ex: 01:30:00)',
    `ESTADO` ENUM('Ativa', 'Cancelada', 'Pendente', 'Reservado') NOT NULL DEFAULT 'Ativa',
    PRIMARY KEY (`RESERVA_ID`),
    KEY `idx_reserva_usuario` (`USUARIO_ID`),
    KEY `idx_reserva_sala` (`SALA_ID`),
    KEY `idx_reserva_data` (`DATA_RESERVA`),
    KEY `idx_reserva_estado` (`ESTADO`),
    KEY `idx_reserva_sala_data_estado` (`SALA_ID`, `DATA_RESERVA`, `ESTADO`) COMMENT 'Índice composto para verificação de conflitos',
    CONSTRAINT `fk_reserva_usuario` FOREIGN KEY (`USUARIO_ID`) REFERENCES `USUARIO` (`USUARIO_ID`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_reserva_sala` FOREIGN KEY (`SALA_ID`) REFERENCES `SALA` (`SALA_ID`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DADOS: USUÁRIOS
-- =============================================
-- NOTA SOBRE SENHAS:
-- O hash abaixo ($2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)
-- corresponde à senha "password" em bcrypt.
-- O sistema suporta tanto hash bcrypt quanto texto puro (compatibilidade legado).
-- Para o login funcionar, use as senhas em TEXTO PURO listadas abaixo,
-- pois a API mobile (api/login.php) compara texto puro diretamente.
-- =============================================

-- ===== ADMIN PRINCIPAL =====
-- Email: salacerta@salacerta.com | Senha: admin123
-- Acesso: Painel Web Admin (TIPO_USUARIO = admin, SETOR = TI)
INSERT INTO `USUARIO` (`USUARIO_ID`, `USUARIO_NOME`, `USUARIO_EMAIL`, `USUARIO_SENHA`, `USUARIO_CPF`, `USUARIO_CURSO`, `TIPO_USUARIO`, `SETOR`, `FOTO_PERFIL`) VALUES
(1, 'Administrador Sala Certa', 'salacerta@salacerta.com', 'admin123', '000.000.000-00', 'Administração de Sistemas', 'admin', 'TI', NULL);

-- ===== COORDENADOR (acessa painel pelo SETOR) =====
-- Email: coordenador@senac.com | Senha: coord123
-- Acesso: Painel Web Admin (TIPO_USUARIO = comum, mas SETOR preenchido)
INSERT INTO `USUARIO` (`USUARIO_ID`, `USUARIO_NOME`, `USUARIO_EMAIL`, `USUARIO_SENHA`, `USUARIO_CPF`, `USUARIO_CURSO`, `TIPO_USUARIO`, `SETOR`, `FOTO_PERFIL`) VALUES
(2, 'Ana Paula Coordenadora', 'coordenador@senac.com', 'coord123', '111.222.333-44', 'Coordenação Acadêmica', 'comum', 'Acadêmico', NULL);

-- ===== PROFESSORES (usuários comuns com reservas) =====
-- Email: professor@senac.com | Senha: prof123
INSERT INTO `USUARIO` (`USUARIO_ID`, `USUARIO_NOME`, `USUARIO_EMAIL`, `USUARIO_SENHA`, `USUARIO_CPF`, `USUARIO_CURSO`, `TIPO_USUARIO`, `SETOR`, `FOTO_PERFIL`) VALUES
(3, 'Carlos Eduardo Silva', 'professor@senac.com', 'prof123', '222.333.444-55', 'Sistemas de Informação', 'comum', '', NULL);

-- Email: maria@senac.com | Senha: maria123
INSERT INTO `USUARIO` (`USUARIO_ID`, `USUARIO_NOME`, `USUARIO_EMAIL`, `USUARIO_SENHA`, `USUARIO_CPF`, `USUARIO_CURSO`, `TIPO_USUARIO`, `SETOR`, `FOTO_PERFIL`) VALUES
(4, 'Maria Fernanda Oliveira', 'maria@senac.com', 'maria123', '333.444.555-66', 'Engenharia de Software', 'comum', '', NULL);

-- ===== ALUNOS (usuários comuns - app mobile) =====
-- Email: joao@aluno.senac.com | Senha: joao123
INSERT INTO `USUARIO` (`USUARIO_ID`, `USUARIO_NOME`, `USUARIO_EMAIL`, `USUARIO_SENHA`, `USUARIO_CPF`, `USUARIO_CURSO`, `TIPO_USUARIO`, `SETOR`, `FOTO_PERFIL`) VALUES
(5, 'João Pedro Santos', 'joao@aluno.senac.com', 'joao123', '444.555.666-77', 'Sistemas de Informação', 'comum', '', NULL);

-- Email: lucas@aluno.senac.com | Senha: lucas123
INSERT INTO `USUARIO` (`USUARIO_ID`, `USUARIO_NOME`, `USUARIO_EMAIL`, `USUARIO_SENHA`, `USUARIO_CPF`, `USUARIO_CURSO`, `TIPO_USUARIO`, `SETOR`, `FOTO_PERFIL`) VALUES
(6, 'Lucas Mendes Pereira', 'lucas@aluno.senac.com', 'lucas123', '555.666.777-88', 'Análise e Desenvolvimento de Sistemas', 'comum', '', NULL);

-- Email: juliana@aluno.senac.com | Senha: juliana123
INSERT INTO `USUARIO` (`USUARIO_ID`, `USUARIO_NOME`, `USUARIO_EMAIL`, `USUARIO_SENHA`, `USUARIO_CPF`, `USUARIO_CURSO`, `TIPO_USUARIO`, `SETOR`, `FOTO_PERFIL`) VALUES
(7, 'Juliana Costa Ribeiro', 'juliana@aluno.senac.com', 'juliana123', '666.777.888-99', 'Design Gráfico', 'comum', '', NULL);

-- Email: rafael@aluno.senac.com | Senha: rafael123
INSERT INTO `USUARIO` (`USUARIO_ID`, `USUARIO_NOME`, `USUARIO_EMAIL`, `USUARIO_SENHA`, `USUARIO_CPF`, `USUARIO_CURSO`, `TIPO_USUARIO`, `SETOR`, `FOTO_PERFIL`) VALUES
(8, 'Rafael Almeida Souza', 'rafael@aluno.senac.com', 'rafael123', '777.888.999-00', 'Redes de Computadores', 'comum', '', NULL);

-- =============================================
-- DADOS: SALAS
-- Organização por andares conforme nova_reserva.php:
--   Térreo (T): salas começam com T
--   1º Andar: salas começam com 1
--   2º Andar: salas começam com 2
-- =============================================

-- ===== TÉRREO =====
INSERT INTO `SALA` (`SALA_ID`, `NUM_SALA`, `QTD_PESSOAS`, `DESCRICAO`, `TIPO_SALA`, `SETOR_RESPONSAVEL`) VALUES
(1,  'T01', 30, 'Sala de aula padrão com projetor multimídia, quadro branco e ar condicionado', 'Sala de Aula', 'Acadêmico'),
(2,  'T02', 40, 'Sala de aula ampla com projetor, sistema de som e ar condicionado', 'Sala de Aula', 'Acadêmico'),
(3,  'T03', 20, 'Sala de aula pequena com TV 55" e quadro branco', 'Sala de Aula', 'Acadêmico'),
(4,  'T04', 100, 'Auditório principal com palco, sistema de som profissional e projetor', 'Auditório', 'Eventos'),
(5,  'T05', 50, 'Sala de estudos da biblioteca com mesas individuais e Wi-Fi', 'Biblioteca', 'Acadêmico');

-- ===== 1º ANDAR =====
INSERT INTO `SALA` (`SALA_ID`, `NUM_SALA`, `QTD_PESSOAS`, `DESCRICAO`, `TIPO_SALA`, `SETOR_RESPONSAVEL`) VALUES
(6,  '101', 35, 'Sala de aula com projetor, ar condicionado e cortinas blackout', 'Sala de Aula', 'Acadêmico'),
(7,  '102', 30, 'Sala de aula com TV interativa 75" e sistema de videoconferência', 'Sala de Aula', 'Acadêmico'),
(8,  '103', 25, 'Laboratório de Informática com 25 computadores Dell, projetor e ar condicionado', 'Laboratório', 'TI'),
(9,  '104', 20, 'Laboratório de Informática com 20 computadores HP e impressora laser', 'Laboratório', 'TI'),
(10, '105', 10, 'Sala de reuniões com TV 55", videoconferência e mesa para 10 pessoas', 'Sala de Reunião', 'Administrativo');

-- ===== 2º ANDAR =====
INSERT INTO `SALA` (`SALA_ID`, `NUM_SALA`, `QTD_PESSOAS`, `DESCRICAO`, `TIPO_SALA`, `SETOR_RESPONSAVEL`) VALUES
(11, '201', 35, 'Sala de aula com projetor multimídia e ar condicionado', 'Sala de Aula', 'Acadêmico'),
(12, '202', 30, 'Sala de aula com quadro branco magnético e projetor', 'Sala de Aula', 'Acadêmico'),
(13, '203', 30, 'Laboratório de Redes com 15 estações, switches e roteadores Cisco', 'Laboratório', 'TI'),
(14, '204', 25, 'Laboratório de Design com iMacs e mesas digitalizadoras Wacom', 'Laboratório', 'TI'),
(15, '205', 8, 'Sala de reuniões pequena com TV 43" e mesa para 8 pessoas', 'Sala de Reunião', 'Administrativo');

-- =============================================
-- DADOS: RESERVAS DE TESTE
-- Mix de estados e datas para popular dashboard e relatórios
-- Horário de funcionamento: 07:00 às 22:00
-- Duração máxima: 3 horas
-- =============================================

-- ===== RESERVAS ATIVAS (hoje e próximos dias) =====

-- Reservas do Admin (USUARIO_ID = 1) - reuniões de TI
INSERT INTO `RESERVA_SALA` (`USUARIO_ID`, `SALA_ID`, `DATA_RESERVA`, `HORA_INICIO`, `TEMPO_RESERVA`, `ESTADO`) VALUES
(1, 10, '2026-03-16', '09:00:00', '01:00:00', 'Ativa'),
(1, 15, '2026-03-17', '14:00:00', '01:30:00', 'Ativa'),
(1, 8,  '2026-03-18', '08:00:00', '02:00:00', 'Ativa');

-- Reservas da Coordenadora Ana (USUARIO_ID = 2)
INSERT INTO `RESERVA_SALA` (`USUARIO_ID`, `SALA_ID`, `DATA_RESERVA`, `HORA_INICIO`, `TEMPO_RESERVA`, `ESTADO`) VALUES
(2, 4,  '2026-03-17', '10:00:00', '02:00:00', 'Ativa'),
(2, 6,  '2026-03-18', '13:00:00', '01:30:00', 'Ativa'),
(2, 10, '2026-03-19', '09:00:00', '01:00:00', 'Ativa');

-- Reservas do Prof. Carlos (USUARIO_ID = 3)
INSERT INTO `RESERVA_SALA` (`USUARIO_ID`, `SALA_ID`, `DATA_RESERVA`, `HORA_INICIO`, `TEMPO_RESERVA`, `ESTADO`) VALUES
(3, 1,  '2026-03-16', '07:30:00', '01:30:00', 'Ativa'),
(3, 8,  '2026-03-16', '10:00:00', '02:00:00', 'Ativa'),
(3, 6,  '2026-03-17', '07:30:00', '01:30:00', 'Ativa'),
(3, 9,  '2026-03-17', '10:00:00', '02:00:00', 'Ativa'),
(3, 11, '2026-03-18', '07:30:00', '01:30:00', 'Ativa'),
(3, 13, '2026-03-19', '14:00:00', '02:00:00', 'Ativa');

-- Reservas da Profa. Maria (USUARIO_ID = 4)
INSERT INTO `RESERVA_SALA` (`USUARIO_ID`, `SALA_ID`, `DATA_RESERVA`, `HORA_INICIO`, `TEMPO_RESERVA`, `ESTADO`) VALUES
(4, 2,  '2026-03-16', '08:00:00', '01:30:00', 'Ativa'),
(4, 14, '2026-03-16', '13:30:00', '02:00:00', 'Ativa'),
(4, 7,  '2026-03-17', '08:00:00', '01:30:00', 'Ativa'),
(4, 12, '2026-03-18', '10:00:00', '01:30:00', 'Ativa'),
(4, 14, '2026-03-20', '09:00:00', '02:00:00', 'Ativa');

-- ===== RESERVAS VIA APP MOBILE (estado Reservado) =====

-- Reservas do João - aluno (USUARIO_ID = 5)
INSERT INTO `RESERVA_SALA` (`USUARIO_ID`, `SALA_ID`, `DATA_RESERVA`, `HORA_INICIO`, `TEMPO_RESERVA`, `ESTADO`) VALUES
(5, 5,  '2026-03-17', '14:00:00', '02:00:00', 'Reservado'),
(5, 3,  '2026-03-18', '16:00:00', '01:30:00', 'Reservado'),
(5, 8,  '2026-03-19', '15:00:00', '01:00:00', 'Reservado');

-- Reservas do Lucas - aluno (USUARIO_ID = 6)
INSERT INTO `RESERVA_SALA` (`USUARIO_ID`, `SALA_ID`, `DATA_RESERVA`, `HORA_INICIO`, `TEMPO_RESERVA`, `ESTADO`) VALUES
(6, 9,  '2026-03-17', '15:00:00', '02:00:00', 'Reservado'),
(6, 13, '2026-03-18', '17:00:00', '01:30:00', 'Reservado');

-- Reservas da Juliana - aluna (USUARIO_ID = 7)
INSERT INTO `RESERVA_SALA` (`USUARIO_ID`, `SALA_ID`, `DATA_RESERVA`, `HORA_INICIO`, `TEMPO_RESERVA`, `ESTADO`) VALUES
(7, 14, '2026-03-17', '09:00:00', '02:00:00', 'Reservado'),
(7, 5,  '2026-03-19', '10:00:00', '01:30:00', 'Reservado');

-- ===== RESERVAS PENDENTES =====
INSERT INTO `RESERVA_SALA` (`USUARIO_ID`, `SALA_ID`, `DATA_RESERVA`, `HORA_INICIO`, `TEMPO_RESERVA`, `ESTADO`) VALUES
(8, 11, '2026-03-20', '08:00:00', '01:30:00', 'Pendente'),
(5, 1,  '2026-03-20', '14:00:00', '02:00:00', 'Pendente'),
(6, 7,  '2026-03-21', '10:00:00', '01:30:00', 'Pendente');

-- ===== RESERVAS CANCELADAS (histórico) =====
INSERT INTO `RESERVA_SALA` (`USUARIO_ID`, `SALA_ID`, `DATA_RESERVA`, `HORA_INICIO`, `TEMPO_RESERVA`, `ESTADO`) VALUES
(3, 2,  '2026-03-10', '08:00:00', '01:30:00', 'Cancelada'),
(4, 9,  '2026-03-11', '14:00:00', '02:00:00', 'Cancelada'),
(5, 1,  '2026-03-12', '10:00:00', '01:00:00', 'Cancelada'),
(7, 6,  '2026-03-13', '09:00:00', '01:30:00', 'Cancelada');

-- ===== RESERVAS PASSADAS (para relatórios com histórico) =====
INSERT INTO `RESERVA_SALA` (`USUARIO_ID`, `SALA_ID`, `DATA_RESERVA`, `HORA_INICIO`, `TEMPO_RESERVA`, `ESTADO`) VALUES
(3, 1,  '2026-02-10', '07:30:00', '01:30:00', 'Ativa'),
(3, 8,  '2026-02-10', '10:00:00', '02:00:00', 'Ativa'),
(4, 2,  '2026-02-11', '08:00:00', '01:30:00', 'Ativa'),
(3, 6,  '2026-02-17', '07:30:00', '01:30:00', 'Ativa'),
(4, 7,  '2026-02-18', '08:00:00', '01:30:00', 'Ativa'),
(3, 11, '2026-02-24', '07:30:00', '01:30:00', 'Ativa'),
(4, 12, '2026-02-25', '10:00:00', '01:30:00', 'Ativa'),
(2, 4,  '2026-02-20', '10:00:00', '02:00:00', 'Ativa'),
(1, 10, '2026-02-21', '09:00:00', '01:00:00', 'Ativa'),
(5, 5,  '2026-02-12', '14:00:00', '02:00:00', 'Ativa'),
(6, 9,  '2026-02-13', '15:00:00', '02:00:00', 'Ativa'),
(7, 14, '2026-02-14', '09:00:00', '02:00:00', 'Ativa'),
(3, 1,  '2026-01-13', '07:30:00', '01:30:00', 'Ativa'),
(3, 8,  '2026-01-14', '10:00:00', '02:00:00', 'Ativa'),
(4, 2,  '2026-01-15', '08:00:00', '01:30:00', 'Ativa'),
(4, 7,  '2026-01-20', '08:00:00', '01:30:00', 'Ativa'),
(2, 4,  '2026-01-22', '10:00:00', '02:00:00', 'Ativa'),
(1, 10, '2026-01-23', '09:00:00', '01:00:00', 'Ativa');

COMMIT;

-- =============================================
-- RESUMO DAS CREDENCIAIS DE TESTE
-- =============================================
--
-- ADMIN (acessa painel web):
--   Email: salacerta@salacerta.com
--   Senha: admin123
--   Tipo: admin | Setor: TI
--
-- COORDENADORA (acessa painel web pelo setor):
--   Email: coordenador@senac.com
--   Senha: coord123
--   Tipo: comum | Setor: Acadêmico
--
-- PROFESSOR 1 (app mobile + reservas via painel):
--   Email: professor@senac.com
--   Senha: prof123
--
-- PROFESSORA 2 (app mobile + reservas via painel):
--   Email: maria@senac.com
--   Senha: maria123
--
-- ALUNOS (apenas app mobile):
--   joao@aluno.senac.com / joao123
--   lucas@aluno.senac.com / lucas123
--   juliana@aluno.senac.com / juliana123
--   rafael@aluno.senac.com / rafael123
--
-- TOTAL: 8 usuários, 15 salas (3 andares), 48 reservas
-- =============================================
