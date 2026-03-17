<?php
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/config.php';
}

// Detectar se estamos na raiz ou em subpasta
$isSubfolder = (basename(dirname($_SERVER['SCRIPT_FILENAME'])) === 'pages');
$baseUrl = $isSubfolder ? '..' : '.';

if (!isset($pageTitle)) {
    $pageTitle = APP_NAME;
}

// Helper: tempo relativo
if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        $now = new DateTime();
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
        if ($diff->d > 0) return "há {$diff->d} dia(s)";
        if ($diff->h > 0) return "há {$diff->h}h";
        if ($diff->i > 0) return "há {$diff->i}min";
        return "agora";
    }
}

// Determinar página activa
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle); ?> - Sala Certa</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">

  <!-- Cropper.js CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">

  <!-- Custom CSS (compiled SCSS) -->
  <link href="<?php echo $baseUrl; ?>/assets/css/main.css" rel="stylesheet">

  <link rel="shortcut icon" href="<?php echo $baseUrl; ?>/assets/img/logo.png">
</head>
<body>

<?php if (isLoggedIn()): ?>

<?php
// Foto do usuário
$userPhoto = null;
if (isset($_SESSION['usuario_foto']) && !empty($_SESSION['usuario_foto'])) {
    if (file_exists($_SESSION['usuario_foto'])) {
        $userPhoto = htmlspecialchars($_SESSION['usuario_foto']) . '?t=' . time();
    }
}
$defaultAvatar = $baseUrl . '/assets/img/default-avatar.png';
$avatarSrc = $userPhoto ?? $defaultAvatar;
?>

<aside class="sc-sidebar" id="sidebar">
  <div class="sidebar-logo">
    <a href="<?php echo $baseUrl; ?>/index.php" style="display:flex;align-items:center;gap:12px;text-decoration:none;">
      <img src="<?php echo $baseUrl; ?>/assets/img/logo.png" alt="Sala Certa" style="width:40px;height:40px;border-radius:8px;">
      <div>
        <span class="logo-text">Sala Certa</span>
        <span class="logo-subtitle">Reserva de Salas</span>
      </div>
    </a>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-item">
      <a href="<?php echo $baseUrl; ?>/index.php"
         class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
        <i class="fas fa-th-large"></i> Dashboard
      </a>
    </div>
    <div class="nav-item">
      <a href="<?php echo $baseUrl; ?>/pages/reservas.php"
         class="nav-link <?php echo isCurrentSection('reservas') ? 'active' : ''; ?>">
        <i class="fas fa-calendar-check"></i> Reservas
      </a>
    </div>
    <div class="nav-item">
      <a href="<?php echo $baseUrl; ?>/pages/calendario.php"
         class="nav-link <?php echo $currentPage === 'calendario.php' ? 'active' : ''; ?>">
        <i class="fas fa-calendar-alt"></i> Calendario
      </a>
    </div>
    <?php if (isAdmin()): ?>
    <div class="nav-item">
      <a href="<?php echo $baseUrl; ?>/pages/salas.php"
         class="nav-link <?php echo isCurrentSection('salas') ? 'active' : ''; ?>">
        <i class="fas fa-door-open"></i> Salas
      </a>
    </div>
    <div class="nav-item">
      <a href="<?php echo $baseUrl; ?>/pages/usuarios.php"
         class="nav-link <?php echo isCurrentSection('usuarios') ? 'active' : ''; ?>">
        <i class="fas fa-users"></i> Usuarios
      </a>
    </div>
    <?php endif; ?>
    <div class="nav-item">
      <a href="<?php echo $baseUrl; ?>/pages/relatorios.php"
         class="nav-link <?php echo $currentPage === 'relatorios.php' ? 'active' : ''; ?>">
        <i class="fas fa-chart-bar"></i> Relatorios
      </a>
    </div>
  </nav>

  <div class="sidebar-user">
    <img src="<?php echo $avatarSrc; ?>" alt="" class="sidebar-avatar">
    <div class="sidebar-user-info">
      <div class="sidebar-user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? ''); ?></div>
      <div class="sidebar-user-role"><?php echo htmlspecialchars($_SESSION['usuario_cargo'] ?? ($_SESSION['tipo_usuario'] ?? '')); ?></div>
    </div>
    <div class="sidebar-user-actions">
      <a href="<?php echo $baseUrl; ?>/pages/configuracoes.php" title="Configurações"><i class="fas fa-cog"></i></a>
      <a href="<?php echo $baseUrl; ?>/logout.php" title="Sair"><i class="fas fa-sign-out-alt"></i></a>
    </div>
  </div>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sc-main">
  <header class="sc-topbar">
    <div class="topbar-left">
      <button class="hamburger" id="hamburgerBtn">
        <i class="fas fa-bars"></i>
      </button>
      <nav class="breadcrumb">
        <a href="<?php echo $baseUrl; ?>/index.php">Dashboard</a>
        <span class="separator">/</span>
        <span class="current"><?php echo htmlspecialchars($pageTitle ?? 'Dashboard'); ?></span>
      </nav>
    </div>
    <div class="topbar-right">
      <!-- Notification bell -->
      <div style="position:relative">
        <?php
        require_once CLASSES_PATH . '/Notificacao.php';
        $notifCount = Notificacao::contarNaoLidas($_SESSION['usuario_id'] ?? 0);
        if (isset($_SESSION['usuario_id'])) {
            Notificacao::gerarLembretes($_SESSION['usuario_id']);
        }
        ?>
        <button class="notification-bell" id="notifBell">
          <i class="fas fa-bell"></i>
          <?php if ($notifCount > 0): ?>
          <span class="notification-badge"><?php echo $notifCount; ?></span>
          <?php endif; ?>
        </button>
        <!-- Notification dropdown -->
        <div class="notification-dropdown" id="notifDropdown">
          <div class="notification-header">
            <h6>Notificações</h6>
            <button class="mark-all-read" id="markAllRead">Marcar todas como lidas</button>
          </div>
          <div class="notification-list" id="notifList">
            <?php
            $notifs = Notificacao::listarPorUsuario($_SESSION['usuario_id'] ?? 0, 10);
            if (empty($notifs)): ?>
              <div class="notification-empty">Nenhuma notificação</div>
            <?php else:
              foreach ($notifs as $n):
                $iconClass = 'icon-' . $n['TIPO'];
                $icons = [
                    'confirmacao'  => 'fa-check',
                    'lembrete'     => 'fa-clock',
                    'cancelamento' => 'fa-times',
                    'conflito'     => 'fa-exclamation-triangle'
                ];
                $icon   = $icons[$n['TIPO']] ?? 'fa-bell';
                $unread = !$n['LIDA'] ? 'unread' : '';
            ?>
              <div class="notification-item <?php echo $unread; ?>" data-id="<?php echo $n['NOTIFICACAO_ID']; ?>">
                <div class="notification-icon <?php echo $iconClass; ?>">
                  <i class="fas <?php echo $icon; ?>"></i>
                </div>
                <div class="notification-content">
                  <div class="notification-text"><?php echo htmlspecialchars($n['MENSAGEM']); ?></div>
                  <div class="notification-time"><?php echo timeAgo($n['DATA_CRIACAO']); ?></div>
                </div>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>

      <img src="<?php echo $avatarSrc; ?>" alt="" class="topbar-avatar">
    </div>
  </header>

  <main class="sc-content">

<?php else: ?>
<div class="sc-main">
  <main class="sc-content">
<?php endif; ?>
