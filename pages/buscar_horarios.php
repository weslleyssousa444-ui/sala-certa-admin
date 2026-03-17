<?php
/**
 * ========================================
 * BUSCAR HORÁRIOS - VERSÃO PARA PASTA PAGES
 * ========================================
 * Arquivo: pages/buscar_horarios.php
 * 
 * ATENÇÃO: Os caminhos usam "../" para subir uma pasta
 * porque este arquivo está em /pages/ mas config/ e classes/
 * estão na raiz do projeto
 */

// Desabilitar exibição de erros no JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ========================================
// INCLUDES - SOBE UMA PASTA COM ../
// ========================================
try {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../classes/Reserva.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao carregar dependências: ' . $e->getMessage(),
        'horarios' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ========================================
// PROCESSAR REQUISIÇÃO
// ========================================
try {
    // 1. VALIDAR PARÂMETROS
    $salaId = filter_input(INPUT_GET, 'sala_id', FILTER_VALIDATE_INT);
    $data = filter_input(INPUT_GET, 'data', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (!$salaId || $salaId <= 0) {
        throw new InvalidArgumentException('ID da sala inválido');
    }
    
    if (!$data) {
        throw new InvalidArgumentException('Data não informada');
    }
    
    // 2. VALIDAR FORMATO DA DATA
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
        throw new InvalidArgumentException('Formato de data inválido. Use YYYY-MM-DD');
    }
    
    // 3. BUSCAR HORÁRIOS
    $reservas = Reserva::buscarHorariosDisponiveis($salaId, $data);
    
    // 4. PROCESSAR HORÁRIOS
    $horarios = [];
    
    if (is_array($reservas) && count($reservas) > 0) {
        foreach ($reservas as $reserva) {
            // Validar campos
            if (!isset($reserva['HORA_INICIO']) || !isset($reserva['TEMPO_RESERVA'])) {
                continue;
            }
            
            try {
                // Formatar hora início
                $inicio = date('H:i', strtotime($reserva['HORA_INICIO']));
                
                // Calcular hora fim
                $horaInicioTimestamp = strtotime($reserva['HORA_INICIO']);
                $tempoReservaTimestamp = strtotime($reserva['TEMPO_RESERVA']);
                $tempoReservaSegundos = $tempoReservaTimestamp - strtotime('00:00:00');
                $horaFimTimestamp = $horaInicioTimestamp + $tempoReservaSegundos;
                $fim = date('H:i', $horaFimTimestamp);
                
                $horarios[] = [
                    'inicio' => $inicio,
                    'fim' => $fim
                ];
                
            } catch (Exception $e) {
                continue;
            }
        }
    }
    
    // 5. RETORNAR SUCESSO
    echo json_encode([
        'success' => true,
        'horarios' => $horarios,
        'total' => count($horarios)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'horarios' => []
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao acessar banco de dados',
        'horarios' => []
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao buscar horários',
        'horarios' => []
    ], JSON_UNESCAPED_UNICODE);
}
?>