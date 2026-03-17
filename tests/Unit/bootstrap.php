<?php
/**
 * Bootstrap para testes PHPUnit
 */

// Carregar autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Carregar configurações necessárias para os testes
require_once __DIR__ . '/../config/conexao.php';

// Definir ambiente de teste
define('TEST_MODE', true);

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

// Função helper para criar conexão de teste (opcional)
function getTestConnection() {
    return Conexao::getConn();
}

echo "\n✅ Bootstrap de testes carregado com sucesso!\n\n";
