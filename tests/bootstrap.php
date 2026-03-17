<?php
/**
 * Bootstrap para testes PHPUnit
 * Sala Certa - Sistema de Reservas
 */

// Definir modo de teste primeiro
define('TEST_MODE', true);

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

// Desabilitar output durante testes
ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);

// Carregar autoloader do Composer
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("\n❌ Erro: Composer autoloader não encontrado em: $autoloadPath\n");
}
require_once $autoloadPath;

// Mock da classe Conexao para testes
if (!class_exists('Conexao')) {
    class Conexao {
        public static function getConn() {
            // Retorna um mock para testes que não precisam de banco
            return new stdClass();
        }
    }
}

// Carregar classes do projeto
$classesDir = __DIR__ . '/../classes';
if (is_dir($classesDir)) {
    require_once $classesDir . '/Usuario.php';
    require_once $classesDir . '/Sala.php';
    require_once $classesDir . '/Reserva.php';
}

echo "\n";
echo "✅ Bootstrap de testes carregado com sucesso!\n";
echo "📁 Diretório do projeto: " . dirname(__DIR__) . "\n";
echo "🧪 Modo de teste ativado\n";
echo "📦 PHPUnit " . PHPUnit\Runner\Version::id() . "\n";
echo "\n";