<?php
// ===== Config do Banco (Hostinger) =====
define('DB_HOST', 'localhost');                 // Host Hostinger
define('DB_USER', 'u400600347_salacerta');      // Usuário
define('DB_PASS', '97120Wes@');                 // Senha
define('DB_NAME', 'u400600347_salacerta');      // Banco

// ===== App =====
define('APP_NAME', 'Sala Certa');
define('APP_URL', 'https://saddlebrown-ape-456330.hostingersite.com');
define('APP_VERSION', '1.0.0');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
date_default_timezone_set('America/Sao_Paulo');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

$charset = 'utf8mb4';
$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Erro de conexão: '.$e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}
?>