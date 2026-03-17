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
            if ($usuario->getTipoUsuario() == 'admin' || !empty($usuario->getSetor())) {
                $_SESSION['usuario_id'] = $usuario->getId();
                $_SESSION['usuario_nome'] = $usuario->getNome();
                $_SESSION['usuario_email'] = $usuario->getEmail();
                $_SESSION['tipo_usuario'] = $usuario->getTipoUsuario();
                $_SESSION['setor'] = $usuario->getSetor();

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo htmlspecialchars($pageTitle); ?> - <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="assets/css/login.css" />
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <div class="text-center mb-4">
                <img src="assets/img/logo.png" alt="Logo Sala Certa" class="mb-3" style="max-width: 120px; display: block; margin-left: auto; margin-right: auto;">        
                <h2 style="color: white; font-size: 1.8rem; font-weight: 700; margin-bottom: 0.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">Sala Certa Admin</h2>        
                <p style="color: rgba(255,255,255,0.9);">Painel Administrativo</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="seuemail@exemplo.com" required autocomplete="username" />
                    </div>
                </div>

                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="senha" name="senha" placeholder="••••••••" required autocomplete="current-password" />
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </div>
            </form>

            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    Usuários comuns devem utilizar o aplicativo móvel
                </small>
            </div>

            <div class="text-center mt-3">
                <small>Versão <?php echo htmlspecialchars(APP_VERSION); ?></small>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
