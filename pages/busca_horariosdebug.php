<?php
/**
 * ========================================
 * DEBUG COMPLETO - VERSÃO PARA PASTA PAGES
 * ========================================
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$debug = [];
$debug[] = "=== DEBUG INICIADO ===";
$debug[] = "Arquivo: " . __FILE__;
$debug[] = "Diretório: " . __DIR__;
$debug[] = "Data/Hora: " . date('Y-m-d H:i:s');

// PARÂMETROS
$debug[] = "\n1. PARÂMETROS:";
$debug[] = "sala_id: " . ($_GET['sala_id'] ?? 'NÃO ENVIADO');
$debug[] = "data: " . ($_GET['data'] ?? 'NÃO ENVIADO');

// INCLUDES
$debug[] = "\n2. TESTANDO INCLUDES:";
try {
    require_once __DIR__ . '/../config/config.php';
    $debug[] = "✅ config.php OK";
} catch (Exception $e) {
    $debug[] = "❌ config.php ERRO: " . $e->getMessage();
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro config', 'debug' => $debug]);
    exit;
}

try {
    require_once __DIR__ . '/../classes/Reserva.php';
    $debug[] = "✅ Reserva.php OK";
} catch (Exception $e) {
    $debug[] = "❌ Reserva.php ERRO: " . $e->getMessage();
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro Reserva', 'debug' => $debug]);
    exit;
}

// CONEXÃO
$debug[] = "\n3. TESTANDO CONEXÃO:";
try {
    $conn = Conexao::getConn();
    $debug[] = "✅ Conexão OK";
    $dbName = $conn->query("SELECT DATABASE()")->fetchColumn();
    $debug[] = "Banco: " . $dbName;
} catch (Exception $e) {
    $debug[] = "❌ ERRO CONEXÃO: " . $e->getMessage();
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro conexão', 'debug' => $debug]);
    exit;
}

// VALIDAR
$debug[] = "\n4. VALIDANDO:";
$salaId = filter_input(INPUT_GET, 'sala_id', FILTER_VALIDATE_INT);
$data = filter_input(INPUT_GET, 'data', FILTER_SANITIZE_SPECIAL_CHARS);

$debug[] = "sala_id filtrado: " . ($salaId ?? 'NULL');
$debug[] = "data filtrada: " . ($data ?? 'NULL');

if (!$salaId || !$data) {
    $debug[] = "❌ Parâmetros inválidos";
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Parâmetros inválidos', 'debug' => $debug]);
    exit;
}
$debug[] = "✅ Parâmetros OK";

// VERIFICAR SALA
$debug[] = "\n5. VERIFICANDO SALA:";
try {
    $stmt = $conn->prepare("SELECT SALA_ID, NUM_SALA FROM SALA WHERE SALA_ID = ?");
    $stmt->execute([$salaId]);
    $sala = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sala) {
        $debug[] = "✅ Sala: " . $sala['NUM_SALA'];
    } else {
        $debug[] = "❌ Sala não encontrada";
    }
} catch (Exception $e) {
    $debug[] = "❌ Erro: " . $e->getMessage();
}

// BUSCAR RESERVAS
$debug[] = "\n6. BUSCANDO RESERVAS:";
try {
    $sql = "SELECT HORA_INICIO, TEMPO_RESERVA 
            FROM RESERVA_SALA 
            WHERE SALA_ID = :sala 
            AND DATA_RESERVA = :data 
            AND ESTADO = 'Ativa'";
    
    $debug[] = "SQL: $sql";
    $debug[] = "Parâmetros: sala=$salaId, data=$data";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':sala', $salaId, PDO::PARAM_INT);
    $stmt->bindValue(':data', $data, PDO::PARAM_STR);
    $stmt->execute();
    
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug[] = "✅ Encontradas: " . count($reservas) . " reservas";
    $debug[] = "Dados: " . json_encode($reservas);
    
    // PROCESSAR
    $horarios = [];
    foreach ($reservas as $r) {
        $inicio = date('H:i', strtotime($r['HORA_INICIO']));
        $inicio_ts = strtotime($r['HORA_INICIO']);
        $tempo_ts = strtotime($r['TEMPO_RESERVA']);
        $duracao = $tempo_ts - strtotime('00:00:00');
        $fim = date('H:i', $inicio_ts + $duracao);
        
        $horarios[] = ['inicio' => $inicio, 'fim' => $fim];
    }
    
    $debug[] = "✅ Processados: " . count($horarios);
    
    // RETORNAR
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'horarios' => $horarios,
        'total' => count($horarios),
        'debug' => $debug
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    $debug[] = "❌ ERRO: " . $e->getMessage();
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro query', 'debug' => $debug]);
}
?>