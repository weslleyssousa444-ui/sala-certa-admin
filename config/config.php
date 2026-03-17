<?php

// ===== Config do Banco =====
define('DB_HOST', 'localhost');
define('DB_USER', 'u400600347_salacerta');
define('DB_PASS', '97120Wes@');
define('DB_NAME', 'u400600347_salacerta');

// ===== App =====
define('APP_NAME', 'Sala Certa Admin');
define('APP_URL', 'https://saddlebrown-ape-456330.hostingersite.com');
define('APP_VERSION', '1.0.0');
define('TABLE_ADMIN', 'ADMINISTRATIVO');

// ===== Incluir Caminhos =====
require_once __DIR__ . '/paths.php';

// ===== Sessão (CORRIGIDO - evita duplicação) =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

// Verificar se usuário é admin
function isAdmin() {
    return isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';
}

// Verificar se usuário tem setor (acesso administrativo)
function hasSetor() {
    return isset($_SESSION['setor']) && !empty($_SESSION['setor']);
}

// Verificar se usuário pode acessar o painel (admin OU tem setor)
function canAccessPanel() {
    return isAdmin() || hasSetor();
}

// Redirecionar se não estiver logado
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
    // CRÍTICO: Bloquear usuário comum
    if (!canAccessPanel()) {
        session_destroy();
        header('Location: ' . APP_URL . '/login.php?erro=acesso_negado');
        exit;
    }
}

// Redirecionar se não for admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}

function isCurrentPage($page) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    return $currentPage == $page;
}

function isCurrentSection($section) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    switch ($section) {
        case 'reservas':
            return in_array($currentPage, ['reservas.php', 'nova_reserva.php', 'editar_reserva.php', 'ver_reserva.php', 'reservas_por_data.php']);
        case 'salas':
            return in_array($currentPage, ['salas.php', 'nova_sala.php', 'editar_sala.php', 'ver_sala.php']);
        case 'usuarios':
            return in_array($currentPage, ['usuarios.php', 'novo_usuario.php', 'editar_usuario.php', 'ver_usuario.php']);
        case 'relatorios':
            return in_array($currentPage, ['relatorios.php']);
        default:
            return false;
    }
}
?>