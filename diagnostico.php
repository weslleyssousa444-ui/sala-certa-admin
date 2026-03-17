#!/usr/bin/env php
<?php
/**
 * DIAGNÓSTICO DO SISTEMA DE TESTES
 * Execute: php diagnostico.php
 */

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "       DIAGNÓSTICO DO SISTEMA DE TESTES - SALA CERTA\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. Informações do PHP
echo "📋 INFORMAÇÕES DO AMBIENTE\n";
echo str_repeat("─", 63) . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHPUnit: ";
$phpunitVersion = shell_exec('vendor/bin/phpunit --version 2>&1');
echo trim($phpunitVersion) . "\n";
echo "Diretório: " . __DIR__ . "\n\n";

// 2. Verificar arquivos essenciais
echo "✓ ARQUIVOS ESSENCIAIS\n";
echo str_repeat("─", 63) . "\n";

$files = [
    'phpunit.xml' => 'Configuração do PHPUnit',
    'composer.json' => 'Configuração do Composer',
    'composer.lock' => 'Lock do Composer',
    'vendor/autoload.php' => 'Autoloader',
    'vendor/bin/phpunit' => 'Executável do PHPUnit',
    'tests/bootstrap.php' => 'Bootstrap dos testes',
    'tests/Unit' => 'Diretório de testes unitários',
    'classes/Usuario.php' => 'Classe Usuario',
    'classes/Sala.php' => 'Classe Sala',
    'classes/Reserva.php' => 'Classe Reserva',
];

$allOk = true;
foreach ($files as $file => $desc) {
    $exists = file_exists($file);
    $symbol = $exists ? '✓' : '✗';
    $status = $exists ? 'OK' : 'FALTANDO';
    echo sprintf("  %s %-35s %s\n", $symbol, $desc . ':', $status);
    if (!$exists) $allOk = false;
}
echo "\n";

// 3. Verificar testes
echo "📝 ARQUIVOS DE TESTE\n";
echo str_repeat("─", 63) . "\n";

$testFiles = glob('tests/Unit/*Test.php');
echo "Total: " . count($testFiles) . " arquivo(s)\n";
foreach ($testFiles as $file) {
    $basename = basename($file);
    $size = filesize($file);
    $sizeKb = round($size / 1024, 1);
    echo "  • $basename ($sizeKb KB)\n";
}
echo "\n";

// 4. Verificar permissões
echo "🔐 PERMISSÕES\n";
echo str_repeat("─", 63) . "\n";

$dirs = ['tests', 'tests/Unit', 'vendor'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir) ? 'Sim' : 'Não';
        echo "  $dir: $perms (Gravável: $writable)\n";
    }
}
echo "\n";

// 5. Testar execução
echo "⚙️  TESTE DE EXECUÇÃO\n";
echo str_repeat("─", 63) . "\n";

if ($allOk) {
    echo "Executando: vendor/bin/phpunit --version\n";
    $output = shell_exec('vendor/bin/phpunit --version 2>&1');
    echo $output . "\n";
    
    echo "Executando: vendor/bin/phpunit --list-tests\n";
    $output = shell_exec('vendor/bin/phpunit --list-tests 2>&1');
    echo $output . "\n";
} else {
    echo "⚠️  Corrija os arquivos faltantes antes de executar os testes.\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "Para executar os testes:\n";
echo "  vendor/bin/phpunit\n";
echo "  vendor/bin/phpunit --testdox\n";
echo "  vendor/bin/phpunit --testdox --colors=always\n";
echo "═══════════════════════════════════════════════════════════════\n\n";