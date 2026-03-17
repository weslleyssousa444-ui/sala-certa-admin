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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Painel Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/header-modern.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/style-no-sidebar.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/global.css">

    <link rel="shortcut icon" href="<?php echo $baseUrl; ?>/assets/img/logo.png">

    <style>
        .user-dropdown {
            position: relative;
        }

        .user-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 5px 12px;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 40px;
        }

        .user-dropdown-toggle:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #37D0C0;
            font-weight: 700;
            font-size: 0.85rem;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.85rem;
        }

        .user-role {
            font-size: 0.65rem;
            opacity: 0.9;
        }

        .dropdown-arrow {
            font-size: 0.7rem;
            transition: transform 0.3s ease;
        }

        .user-dropdown .dropdown-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            min-width: 220px;
            padding: 8px;
            display: none;
            z-index: 9999;
        }

        .user-dropdown.open .dropdown-menu {
            display: block !important;
        }
        
        .user-dropdown.open .dropdown-arrow {
            transform: rotate(180deg);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .dropdown-item:hover {
            background: rgba(55, 208, 192, 0.1);
            color: #37D0C0;
        }

        .dropdown-item i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .dropdown-divider {
            height: 1px;
            background: #e1e8ed;
            margin: 8px 0;
        }

        .dropdown-item.danger {
            color: #e40b0b;
        }

        .dropdown-item.danger:hover {
            background: rgba(228, 11, 11, 0.1);
        }

        .navbar {
            margin: 0 !important;
            padding: 0 !important;
        }

        .top-menu-bar {
            margin: 0 !important;
            padding: 0 15px !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.toggleUserDropdown = function() {
                const dropdown = document.querySelector('.user-dropdown');
                if (dropdown) {
                    dropdown.classList.toggle('open');
                }
            };

            document.addEventListener('click', function(event) {
                const dropdown = document.querySelector('.user-dropdown');
                if (dropdown && !dropdown.contains(event.target)) {
                    dropdown.classList.remove('open');
                }
            });
        });
    </script>
</head>
<body>
    <div class="wrapper">
        <div id="content">
            <?php if (isLoggedIn()): ?>
            <nav class="navbar navbar-expand-lg">
                <div class="top-menu-bar">
                    <div class="menu-left">
                        <a href="<?php echo $baseUrl; ?>/index.php" class="logo-section">
                            <img src="<?php echo $baseUrl; ?>/assets/img/logo.png" class="logo-icon" alt="Sala Certa" style="width: 35px; height: 35px;">
                            <div class="logo-text">
                                <span class="main">Sala Certa</span>
                                <span class="sub">Admin</span>
                            </div>
                        </a>
                    </div>

                    <div class="menu-center">
                        <ul class="nav-menu">
                            <li class="nav-item-custom">
                                <a href="<?php echo $baseUrl; ?>/index.php" class="nav-link-custom <?php echo isCurrentPage('index.php') ? 'active' : ''; ?>">
                                    <i class="fas fa-home"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>

                            <li class="nav-item-custom">
                                <a href="<?php echo $baseUrl; ?>/pages/reservas.php" class="nav-link-custom has-submenu <?php echo isCurrentSection('reservas') ? 'active' : ''; ?>">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Reservas</span>
                                </a>

                                <div class="submenu">
                                    <a href="<?php echo $baseUrl; ?>/pages/reservas.php" class="submenu-item">
                                        <i class="fas fa-list"></i>
                                        <span>Listar Todas</span>
                                    </a>
                                    <a href="<?php echo $baseUrl; ?>/pages/nova_reserva.php" class="submenu-item">
                                        <i class="fas fa-plus"></i>
                                        <span>Nova Reserva</span>
                                    </a>
                                </div>
                            </li>

                            <li class="nav-item-custom">
                                <a href="<?php echo $baseUrl; ?>/pages/salas.php" class="nav-link-custom has-submenu <?php echo isCurrentSection('salas') ? 'active' : ''; ?>">
                                    <i class="fas fa-door-open"></i>
                                    <span>Salas</span>
                                </a>

                                <div class="submenu">
                                    <a href="<?php echo $baseUrl; ?>/pages/salas.php" class="submenu-item">
                                        <i class="fas fa-list"></i>
                                        <span>Listar Todas</span>
                                    </a>
                                    <a href="<?php echo $baseUrl; ?>/pages/nova_sala.php" class="submenu-item">
                                        <i class="fas fa-plus"></i>
                                        <span>Nova Sala</span>
                                    </a>
                                </div>
                            </li>

                            <li class="nav-item-custom">
                                <a href="<?php echo $baseUrl; ?>/pages/usuarios.php" class="nav-link-custom has-submenu <?php echo isCurrentSection('usuarios') ? 'active' : ''; ?>">
                                    <i class="fas fa-users"></i>
                                    <span>Usuários</span>
                                </a>

                                <div class="submenu">
                                    <a href="<?php echo $baseUrl; ?>/pages/usuarios.php" class="submenu-item">
                                        <i class="fas fa-list"></i>
                                        <span>Listar Todos</span>
                                    </a>
                                    <a href="<?php echo $baseUrl; ?>/pages/novo_usuario.php" class="submenu-item">
                                        <i class="fas fa-user-plus"></i>
                                        <span>Novo Usuário</span>
                                    </a>
                                </div>
                            </li>

                            <li class="nav-item-custom">
                                <a href="<?php echo $baseUrl; ?>/pages/relatorios.php" class="nav-link-custom <?php echo isCurrentPage('relatorios.php') ? 'active' : ''; ?>">
                                    <i class="fas fa-chart-line"></i>
                                    <span>Relatórios</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="menu-right">
                        <div class="quick-actions">
                            <button class="quick-action-btn" onclick="window.location.href='<?php echo $baseUrl; ?>/pages/nova_reserva.php'" data-bs-toggle="tooltip" title="Nova Reserva">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>

                        <div class="user-dropdown">
                            <div class="user-dropdown-toggle" onclick="toggleUserDropdown()">
                                <div class="user-avatar">
                                    <?php
                                    $foto_existe = false;
                                    if (isset($_SESSION['usuario_foto']) && !empty($_SESSION['usuario_foto'])) {
                                        if (file_exists($_SESSION['usuario_foto'])) {
                                            $foto_existe = true;
                                            echo '<img src="' . htmlspecialchars($_SESSION['usuario_foto']) . '?t=' . time() . '" alt="' . htmlspecialchars($_SESSION['usuario_nome']) . '">';
                                        }
                                    }

                                    if (!$foto_existe) {
                                        echo strtoupper(substr($_SESSION['usuario_nome'], 0, 1));
                                    }
                                    ?>
                                </div>
                                <div class="user-info">
                                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                                    <span class="user-role">Administrador</span>
                                </div>
                                <i class="fas fa-chevron-down dropdown-arrow"></i>
                            </div>

                            <div class="dropdown-menu">
                                <a href="<?php echo $baseUrl; ?>/pages/perfil.php" class="dropdown-item">
                                    <i class="fas fa-user"></i>
                                    <span>Meu Perfil</span>
                                </a>
                                <a href="<?php echo $baseUrl; ?>/pages/configuracoes.php" class="dropdown-item">
                                    <i class="fas fa-cog"></i>
                                    <span>Configurações</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="<?php echo $baseUrl; ?>/logout.php" class="dropdown-item danger">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Sair</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
            <?php endif; ?>

            <div class="container-fluid py-4">
