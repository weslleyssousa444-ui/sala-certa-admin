<?php
// ===== Config do Banco (Hostinger) =====
define('DB_HOST', 'localhost');                 // Host Hostinger
define('DB_USER', 'u901713138_admin');          // Seu usuário
define('DB_PASS', '97120Wes@');                 // Sua senha
define('DB_NAME', 'u901713138_salacerta');      // Seu banco

// ===== App =====
define('APP_NAME', 'Sala Certa');
define('APP_URL', 'https://salacerta.online');
define('APP_VERSION', '1.0.0');

// ===== Headers úteis =====
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // se quiser restringir, troque * pelo seu domínio
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Pré-flight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ===== Conexão PDO =====
$charset = 'utf8mb4';
$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    // ---- Entrada: aceita POST novo OU GET legado ----
    // Android (Retrofit): @FormUrlEncoded + @Field("USUARIO_EMAIL"), @Field("USUARIO_SENHA")
    $email = $_POST['USUARIO_EMAIL'] ?? null;
    $senha = $_POST['USUARIO_SENHA'] ?? null;

    // Legado (navegador/link): ?usuario=...&senha=...
    if ($email === null && isset($_GET['usuario'])) {
        $email = $_GET['usuario'];
    }
    if ($senha === null && isset($_GET['senha'])) {
        $senha = $_GET['senha'];
    }

    // Normaliza
    $email = trim((string)$email);
    $senha = trim((string)$senha);

    if ($email === '' || $senha === '') {
        http_response_code(400);
        echo json_encode(["error" => "Campos obrigatórios ausentes"]);
        exit;
    }

    // ---- Consulta na MESMA TABELA: USUARIO ----
    // ATENÇÃO: se a senha estiver hasheada no banco, troque a lógica conforme comentário abaixo.
    $sql = "SELECT USUARIO_ID, USUARIO_NOME, USUARIO_EMAIL, USUARIO_CPF, USUARIO_CURSO
            FROM USUARIO
            WHERE USUARIO_EMAIL = :email AND USUARIO_SENHA = :senha
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':senha', $senha, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch();

    // Resposta: o app espera ARRAY JSON (lista); vazio = inválido
    if ($user) {
        echo json_encode([$user], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        echo json_encode([]);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no servidor']);
    // log interno opcional: error_log($e->getMessage());
    exit;
}
