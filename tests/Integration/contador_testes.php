<?php
/**
 * CONTADOR PRECISO DE TESTES
 * Analisa os arquivos de teste e retorna contagem exata
 */

function contarTestesReais($testsDir) {
    $resultado = [
        'total' => 0,
        'por_classe' => []
    ];
    
    if (!is_dir($testsDir)) {
        return $resultado;
    }
    
    $testFiles = glob($testsDir . '/*Test.php');
    
    foreach ($testFiles as $testFile) {
        $className = basename($testFile, '.php');
        $content = file_get_contents($testFile);
        
        // Contar atributos #[Test]
        preg_match_all('/#\[Test\]\s*\n\s*public\s+function/s', $content, $matches);
        $numTestes = count($matches[0]);
        
        $resultado['por_classe'][$className] = $numTestes;
        $resultado['total'] += $numTestes;
    }
    
    return $resultado;
}

// Teste
$testsDir = '/mnt/project';
$resultado = contarTestesReais($testsDir);

echo "=== CONTAGEM REAL DE TESTES ===\n\n";
echo "TOTAL DE TESTES: " . $resultado['total'] . "\n\n";
echo "POR CLASSE:\n";
foreach ($resultado['por_classe'] as $classe => $num) {
    echo "  - $classe: $num testes\n";
}
echo "\n";
echo "ESPERADO:\n";
echo "  - ReservaTest: 30 testes\n";
echo "  - SalaTest: 49 testes\n";
echo "  - UsuarioTest: 59 testes\n";
echo "  - TOTAL: 138 testes\n";