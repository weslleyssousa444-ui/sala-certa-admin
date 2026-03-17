<?php
/**
 * EXECUTOR DE TESTES - SALA CERTA
 * VERSÃO CORRIGIDA - Contagem precisa de testes
 *
 * CORREÇÕES:
 * 1. [REMOVIDO] Contagem estática (#[Test]) que não via DataProviders.
 * 2. O script agora confia na saída do PHPUnit (linha "OK (151 tests...)").
 * 3. A função `analisarResultados` foi melhorada para capturar o total dinâmico.
 */

error_reporting(0);
ini_set('display_errors', '0');

$projectRoot = realpath(__DIR__ . '/../../');
$reportsDir = __DIR__ . '/reports';

if (!is_dir($reportsDir)) {
    mkdir($reportsDir, 0777, true);
}

/**
 * ═══════════════════════════════════════════════════════════
 * FUNÇÃO DE CONTAGEM ESTÁTICA (Usada apenas para Cobertura)
 * ═══════════════════════════════════════════════════════════
 * Esta função conta estaticamente os arquivos de teste para o
 * relatório de cobertura (quantas classes têm testes).
 * ELA NÃO É USADA PARA O TOTAL DE TESTES.
 */
function contarTestesReais($projectRoot) {
    // Lista de diretórios de teste
    $testDirectories = [
        $projectRoot . '/tests/Unit',
        $projectRoot . '/tests/Integration' 
        // Adicione mais pastas se necessário
    ];

    $resultado = [
        'total' => 0,
        'por_classe' => []
    ];

    $testFiles = [];
    foreach ($testDirectories as $testsDir) {
        if (is_dir($testsDir)) {
            $testFiles = array_merge($testFiles, glob($testsDir . '/*Test.php'));
        }
    }

    foreach ($testFiles as $testFile) {
        $className = basename($testFile, '.php');
        $content = file_get_contents($testFile);
        
        // Contar atributos #[Test] com função pública
        preg_match_all('/#\[Test\]\s*\n\s*public\s+function\s+(\w+)/s', $content, $matches);
        $numTestes = count($matches[0]);
        
        $resultado['por_classe'][$className] = [
            'num_testes' => $numTestes,
            'nomes_testes' => $matches[1] ?? []
        ];
        $resultado['total'] += $numTestes;
    }
    
    return $resultado;
}

// Diagnóstico (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $diagnostic = [
        'php_version' => PHP_VERSION,
        'project_root' => $projectRoot,
        'checks' => [
            'Diretório /tests existe' => is_dir($projectRoot . '/tests'),
            'Diretório /tests/Unit existe' => is_dir($projectRoot . '/tests/Unit'),
            'phpunit.xml existe' => file_exists($projectRoot . '/phpunit.xml'),
            'vendor/autoload.php existe' => file_exists($projectRoot . '/vendor/autoload.php'),
            'bootstrap.php existe' => file_exists($projectRoot . '/tests/bootstrap.php'),
        ],
        'functions' => [
            'exec' => function_exists('exec'),
            'shell_exec' => function_exists('shell_exec'),
            'system' => function_exists('system'),
        ]
    ];

    // Contagem real direta dos arquivos
    $contagemReal = contarTestesReais($projectRoot);
    $diagnostic['test_count_static'] = $contagemReal['total']; // Renomeado para clareza
    $diagnostic['tests_por_classe'] = $contagemReal['por_classe'];
    $diagnostic['test_files_found'] = array_keys($contagemReal['por_classe']);

    header('Content-Type: application/json');
    echo json_encode($diagnostic, JSON_PRETTY_PRINT);
    exit;
}

// Executar testes (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = [
        'success' => false,
        'message' => '',
        'output' => '',
        'results' => null
    ];

    try {
        $phpunitPaths = [
            $projectRoot . '/vendor/bin/phpunit',
            $projectRoot . '/vendor/phpunit/phpunit/phpunit',
        ];

        $phpunitPath = null;
        foreach ($phpunitPaths as $path) {
            if (file_exists($path)) {
                $phpunitPath = $path;
                break;
            }
        }

        if (!$phpunitPath) {
            throw new Exception('PHPUnit não encontrado');
        }

        $command = sprintf(
            'cd %s && php %s --testdox --colors=never 2>&1',
            escapeshellarg($projectRoot),
            escapeshellarg($phpunitPath)
        );

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        $fullOutput = implode("\n", $output);
        $response['output'] = $fullOutput;


        // ═══════════════════════════════════════════════════════════
        // CORREÇÃO: Analisar resultados DINÂMICOS do PHPUnit
        // ═══════════════════════════════════════════════════════════
        
        // Analisar resultados do PHPUnit (Isto agora captura o total 151)
        $results = analisarResultados($fullOutput, $projectRoot);
        
        // [REMOVIDO] Bloco que sobrescrevia o totalTests com a contagem estática.
        
        // Recalcular porcentagem
        if ($results['totalTests'] > 0) {
            $results['percentage'] = round(($results['passed'] / $results['totalTests']) * 100, 1);
        }
        
        // [REMOVIDO] Bloco que chamava atualizarSuitesComContagemReal
        // A função analisarSuites() agora é a única fonte para $results['testSuites']

        // Calcular cobertura (isto ainda usa a contagem estática, o que está OK)
        $results['cobertura'] = calcularCobertura($projectRoot);
        
        // Salvar resultados
        file_put_contents(
            $reportsDir . '/test-results.json',
            json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        // Salvar no histórico
        require_once __DIR__ . '/historico_testes.php';
        $historicoManager = new HistoricoTestes();
        $historicoManager->salvarExecucao($results);

        $response['success'] = true;
        $response['message'] = 'Testes executados com sucesso';
        $response['results'] = $results;

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        $response['output'] = 'Erro: ' . $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * [REMOVIDO] A função 'atualizarSuitesComContagemReal' foi removida
 * pois causava a contagem incorreta (135 em vez de 151).
 */

/**
 * ═══════════════════════════════════════════════════════════
 * FUNÇÃO DE ANÁLISE DE RESULTADOS (CORRIGIDA)
 * ═══════════════════════════════════════════════════════════
 * Esta função agora lê a linha de sumário do PHPUnit (ex: "OK (151 tests...)")
 * para obter a contagem dinâmica correta, que inclui DataProviders.
 */
function analisarResultados($output, $projectRoot) {
    $results = [
        'timestamp' => date('Y-m-d H:i:s'),
        'totalTests' => 0,
        'passed' => 0,
        'failed' => 0,
        'errors' => 0,
        'assertions' => 0,
        'time' => '0.00',
        'percentage' => 0,
        'projectStatus' => 'unknown',
        'testSuites' => []
    ];

    $lines = explode("\n", $output);
    $summaryFound = false;

    // Extrair métricas do PHPUnit (focando na linha de sumário)
    foreach ($lines as $line) {
        
        // CASO 1: Sucesso total
        // Ex: OK (151 tests, 217 assertions)
        if (!$summaryFound && preg_match('/OK \((\d+) tests, (\d+) assertions\)/', $line, $matches)) {
            $results['totalTests'] = (int)$matches[1];
            $results['passed'] = (int)$matches[1];
            $results['assertions'] = (int)$matches[2];
            $results['failed'] = 0;
            $results['errors'] = 0;
            $summaryFound = true;
        } 
        // CASO 2: Falhas ou Erros
        // Ex: Tests: 151, Assertions: 217, Failures: 1.
        elseif (!$summaryFound && preg_match('/Tests: (\d+), Assertions: (\d+)(, Failures: (\d+))?(, Errors: (\d+))?/', $line, $matches)) {
            $results['totalTests'] = (int)$matches[1];
            $results['assertions'] = (int)$matches[2];
            $results['failed'] = isset($matches[4]) ? (int)$matches[4] : 0;
            $results['errors'] = isset($matches[6]) ? (int)$matches[6] : 0;
            $results['passed'] = $results['totalTests'] - $results['failed'] - $results['errors'];
            $summaryFound = true;
        }

        // Capturar o tempo
        if (preg_match('/Time:\s*([\d:.]+)/', $line, $matches)) {
            $results['time'] = $matches[1];
        }
    }

    $results['testSuites'] = analisarSuites($output);

    return $results;
}

/**
 * Analisar suites (simplificado - apenas para estrutura visual)
 */
function analisarSuites($output) {
    $suites = [];
    $lines = explode("\n", $output);
    
    $currentSuite = null;
    
    foreach ($lines as $line) {
        // Detectar início de suite
        if (preg_match('/^(?:Tests\\\\Unit\\\\)?([A-Z][a-zA-Z]+Test)\s*$/i', $line, $matches)) {
            if ($currentSuite) {
                $suites[] = $currentSuite;
            }
            
            $currentSuite = [
                'name' => $matches[1],
                'tests' => [],
                'total' => 0, // A contagem agora vem do parse visual
                'passed' => 0,
                'failed' => 0,
                'time' => '0.00',
                'assertions' => 0
            ];
        }
        // Detectar testes individuais (para exibição visual)
        elseif ($currentSuite !== null) {
            if (preg_match('/^\s*([✔✘])\s+(.+)$/u', $line, $matches)) {
                $isPassed = ($matches[1] === '✔');
                $testName = trim($matches[2]);
                
                $currentSuite['tests'][] = [
                    'name' => $testName,
                    'status' => $isPassed ? 'passed' : 'failed',
                    'time' => '0.00',
                    'assertions' => 1
                ];
                
                if ($isPassed) {
                    $currentSuite['passed']++;
                } else {
                    $currentSuite['failed']++;
                }
                $currentSuite['total']++; // Contagem baseada nos testes encontrados
            }
        }
    }
    
    if ($currentSuite) {
        $suites[] = $currentSuite;
    }
    
    return $suites;
}

/**
 * Calcular cobertura (usando contagem estática, o que está correto para este fim)
 */
function calcularCobertura($projectRoot) {
    $classesDir = $projectRoot . '/classes';
    $testsDir = $projectRoot . '/tests/Unit';
    
    $cobertura = [
        'total_classes' => 0,
        'classes_com_testes' => 0,
        'detalhes' => [],
        'porcentagem' => 0,
        'por_camada' => []
    ];
    
    if (!is_dir($classesDir)) {
        return $cobertura;
    }
    
    $classes = glob($classesDir . '/*.php');
    $cobertura['total_classes'] = count($classes);
    
    // Usar função de contagem estática (contarTestesReais)
    $contagemReal = contarTestesReais($projectRoot);
    
    foreach ($classes as $classFile) {
        $className = basename($classFile, '.php');
        $testClassName = $className . 'Test';
        
        $temTeste = isset($contagemReal['por_classe'][$testClassName]);
        $numTestes = $temTeste ? $contagemReal['por_classe'][$testClassName]['num_testes'] : 0;
        
        if ($temTeste) {
            $cobertura['classes_com_testes']++;
        }
        
        $cobertura['detalhes'][$className] = [
            'tem_teste' => $temTeste,
            'num_testes' => $numTestes,
            'arquivo_teste' => $testClassName . '.php'
        ];
        
        $cobertura['por_camada'][$className] = [
            'testes' => $numTestes,
            'cobertura' => $temTeste ? 100 : 0
        ];
    }
    
    if ($cobertura['total_classes'] > 0) {
        $cobertura['porcentagem'] = round(
            ($cobertura['classes_com_testes'] / $cobertura['total_classes']) * 100,
            1
        );
    }
    
    return $cobertura;
}