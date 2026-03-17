<?php
require_once 'config/config.php';
require_once 'config/conexao.php';
require_once 'classes/Usuario.php';
require_once 'includes/alert.php';

// Se já estiver logado, redireciona para o dashboard
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

// Processa o login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        $usuario = new Usuario();
        if ($usuario->login($email, $senha)) {
            if ($usuario->getTipoUsuario() == 'admin' || !empty($usuario->getUsuarioDepartamento())) {
                $_SESSION['usuario_id']             = $usuario->getId();
                $_SESSION['usuario_nome']           = $usuario->getNome();
                $_SESSION['usuario_email']          = $usuario->getEmail();
                $_SESSION['tipo_usuario']           = $usuario->getTipoUsuario();
                $_SESSION['usuario_departamento']   = $usuario->getUsuarioDepartamento();
                $_SESSION['usuario_cargo']          = $usuario->getUsuarioCargo();

                $usuario_dados = Usuario::buscarPorId($usuario->getId());
                if (!empty($usuario_dados['FOTO_PERFIL']) && file_exists($usuario_dados['FOTO_PERFIL'])) {
                    $_SESSION['usuario_foto'] = $usuario_dados['FOTO_PERFIL'];
                }

                header('Location: index.php');
                exit;
            } else {
                $error = 'Acesso negado. Usuários comuns devem utilizar o aplicativo móvel. Apenas administradores podem acessar este painel.';
            }
        } else {
            $error = 'Email ou senha incorretos.';
        }
    }
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($pageTitle); ?> - <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
</head>
<body class="login-page">

    <!-- Left side: visual (hidden on mobile) -->
    <div class="login-visual">
        <div class="visual-bg">
            <img src="assets/img/hero-bg.jpg" alt="" />
        </div>
        <div class="visual-overlay"></div>
        <div class="visual-content">
            <span class="visual-logo" style="display:flex;align-items:center;gap:12px;">
                <img src="assets/img/logo.png" alt="Sala Certa" style="width:48px;height:48px;border-radius:10px;">
                Sala Certa
            </span>

            <div class="visual-headline">
                <h1>Gerencie suas reservas <span>com elegância</span></h1>
                <p>Painel administrativo para controle completo de salas, reservas e usuários.</p>
            </div>

            <div class="visual-features">
                <div class="visual-feature">
                    <span class="feature-icon"><i class="fas fa-calendar-check"></i></span>
                    <span class="feature-text">Reservas em tempo real</span>
                </div>
                <div class="visual-feature">
                    <span class="feature-icon"><i class="fas fa-door-open"></i></span>
                    <span class="feature-text">Gestão completa de salas</span>
                </div>
                <div class="visual-feature">
                    <span class="feature-icon"><i class="fas fa-users"></i></span>
                    <span class="feature-text">Controle de usuários e acessos</span>
                </div>
            </div>

            <div class="visual-footer">
                Sala Certa v<?php echo htmlspecialchars(APP_VERSION); ?>
            </div>
        </div>
    </div>

    <!-- Right side: form -->
    <div class="login-form-side">

        <!-- Mobile logo (hidden on desktop) -->
        <span class="mobile-logo" style="display:flex;align-items:center;gap:10px;justify-content:center;">
            <img src="assets/img/logo.png" alt="Sala Certa" style="width:36px;height:36px;border-radius:8px;">
            Sala Certa
        </span>

        <div class="login-form-container">
            <div class="login-form-header">
                <h2>Bem-vindo de volta</h2>
                <p>Acesse o painel de reservas</p>
            </div>

            <div class="login-card">

                <?php if (!empty($error)): ?>
                    <div class="sc-alert sc-alert-danger login-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="seu@email.com"
                            required
                            autocomplete="username"
                        />
                    </div>

                    <div class="form-group">
                        <label for="senha" class="form-label">Senha</label>
                        <div class="password-field">
                            <input
                                type="password"
                                id="senha"
                                name="senha"
                                class="form-control"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                            />
                            <button type="button" class="password-toggle" onclick="toggleSenha()" aria-label="Mostrar/ocultar senha">
                                <i class="fas fa-eye" id="senhaIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary login-submit">
                        <i class="fas fa-sign-in-alt me-2"></i>Entrar
                    </button>
                </form>

            </div><!-- /.login-card -->

            <div class="login-footer">
                <p>Sala Certa v<?php echo htmlspecialchars(APP_VERSION); ?> &mdash; Apenas administradores</p>
            </div>
        </div><!-- /.login-form-container -->

    </div><!-- /.login-form-side -->

    <script>
        function toggleSenha() {
            const input = document.getElementById('senha');
            const icon  = document.getElementById('senhaIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>

</body>
</html>
