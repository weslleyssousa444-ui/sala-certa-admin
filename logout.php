<?php
require_once 'config/config.php';

// Destruir a sessão
session_start();
session_destroy();

// Redirecionar para a página de login
header('Location: login.php');
exit;
?>
