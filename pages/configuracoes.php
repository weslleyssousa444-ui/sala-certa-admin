<?php
require_once '../config/config.php';
require_once '../classes/Usuario.php';
require_once '../classes/ConfiguracaoNotificacao.php';
require_once '../includes/alert.php';

requireLogin();

$error   = '';
$success = '';

$usuario     = Usuario::buscarPorId($_SESSION['usuario_id']);
$configNotif = ConfiguracaoNotificacao::buscarPorUsuario($_SESSION['usuario_id']);

// ── Tab routing: decide active tab after POST ──
$activeTab = $_GET['tab'] ?? 'dados';
if (!in_array($activeTab, ['dados', 'senha', 'notificacoes'])) {
    $activeTab = 'dados';
}

// ── Handle: photo (cropped base64) ──
if (isset($_POST['cropped_image'])) {
    $data = $_POST['cropped_image'];
    list($type, $data) = explode(';', $data);
    list(, $data)      = explode(',', $data);
    $data = base64_decode($data);

    $upload_dir = 'uploads/perfil/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $new_filename = 'user_' . $_SESSION['usuario_id'] . '_' . time() . '.png';
    $upload_path  = $upload_dir . $new_filename;

    if (file_put_contents($upload_path, $data)) {
        if (!empty($usuario['FOTO_PERFIL']) && file_exists($usuario['FOTO_PERFIL'])) {
            unlink($usuario['FOTO_PERFIL']);
        }
        $conn = Conexao::getConn();
        $sql  = "UPDATE USUARIO SET FOTO_PERFIL = :foto WHERE USUARIO_ID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':foto', $upload_path);
        $stmt->bindValue(':id', $_SESSION['usuario_id']);
        if ($stmt->execute()) {
            $_SESSION['usuario_foto'] = $upload_path;
            header('Location: configuracoes.php?tab=dados&success=foto_atualizada');
            exit;
        } else {
            $error = 'Erro ao salvar foto no banco de dados.';
        }
    } else {
        $error = 'Erro ao salvar arquivo cortado.';
    }
    $activeTab = 'dados';
}

// ── Handle: remove photo ──
if (isset($_POST['remover_foto'])) {
    if (!empty($usuario['FOTO_PERFIL']) && file_exists($usuario['FOTO_PERFIL'])) {
        unlink($usuario['FOTO_PERFIL']);
    }
    $conn = Conexao::getConn();
    $sql  = "UPDATE USUARIO SET FOTO_PERFIL = NULL WHERE USUARIO_ID = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $_SESSION['usuario_id']);
    if ($stmt->execute()) {
        unset($_SESSION['usuario_foto']);
        $success = 'Foto de perfil removida com sucesso!';
        $usuario = Usuario::buscarPorId($_SESSION['usuario_id']);
    } else {
        $error = 'Erro ao remover foto.';
    }
    $activeTab = 'dados';
}

// ── Handle: update personal data ──
if (isset($_POST['atualizar_dados'])) {
    $nome  = htmlspecialchars(trim($_POST['nome'] ?? ''));
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (empty($nome) || empty($email)) {
        $error = 'Nome e email são obrigatórios.';
    } else {
        $conn = Conexao::getConn();
        $sql  = "UPDATE USUARIO SET USUARIO_NOME = :nome, USUARIO_EMAIL = :email WHERE USUARIO_ID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':id', $_SESSION['usuario_id']);
        if ($stmt->execute()) {
            $_SESSION['usuario_nome']  = $nome;
            $_SESSION['usuario_email'] = $email;
            $success = 'Dados atualizados com sucesso!';
            $usuario = Usuario::buscarPorId($_SESSION['usuario_id']);
        } else {
            $error = 'Erro ao atualizar dados.';
        }
    }
    $activeTab = 'dados';
}

// ── Handle: change password ──
if (isset($_POST['alterar_senha'])) {
    $senha_atual    = $_POST['senha_atual']    ?? '';
    $senha_nova     = $_POST['senha_nova']     ?? '';
    $senha_confirma = $_POST['senha_confirma'] ?? '';

    if (empty($senha_atual) || empty($senha_nova) || empty($senha_confirma)) {
        $error = 'Por favor, preencha todos os campos de senha.';
    } elseif ($senha_nova !== $senha_confirma) {
        $error = 'As senhas novas não coincidem.';
    } elseif (strlen($senha_nova) < 6) {
        $error = 'A nova senha deve ter pelo menos 6 caracteres.';
    } else {
        $conn = Conexao::getConn();
        $stmt = $conn->prepare("SELECT USUARIO_SENHA FROM USUARIO WHERE USUARIO_ID = :id");
        $stmt->bindValue(':id', $_SESSION['usuario_id']);
        $stmt->execute();
        $senha_bd = $stmt->fetch()['USUARIO_SENHA'];

        $senha_correta = password_verify($senha_atual, $senha_bd) || ($senha_bd === $senha_atual);

        if (!$senha_correta) {
            $error = 'Senha atual incorreta.';
        } else {
            $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare("UPDATE USUARIO SET USUARIO_SENHA = :senha WHERE USUARIO_ID = :id");
            $stmt2->bindValue(':senha', $senha_hash);
            $stmt2->bindValue(':id', $_SESSION['usuario_id']);
            if ($stmt2->execute()) {
                $success = 'Senha alterada com sucesso!';
            } else {
                $error = 'Erro ao alterar senha.';
            }
        }
    }
    $activeTab = 'senha';
}

// ── Handle: notification settings ──
if (isset($_POST['salvar_notificacoes'])) {
    $conf = isset($_POST['notif_confirmacao']) ? 1 : 0;
    $lemb = isset($_POST['notif_lembrete'])    ? 1 : 0;
    $canc = isset($_POST['notif_cancelamento']) ? 1 : 0;

    if (ConfiguracaoNotificacao::atualizar($_SESSION['usuario_id'], $conf, $lemb, $canc)) {
        $success     = 'Preferências de notificação salvas com sucesso!';
        $configNotif = ConfiguracaoNotificacao::buscarPorUsuario($_SESSION['usuario_id']);
    } else {
        $error = 'Erro ao salvar preferências de notificação.';
    }
    $activeTab = 'notificacoes';
}

// After redirect check
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'foto_atualizada') $success = 'Foto de perfil atualizada com sucesso!';
}

// Generate initials
$nomePartes = explode(' ', trim($usuario['USUARIO_NOME']));
$iniciais   = strtoupper(substr($nomePartes[0], 0, 1));
if (count($nomePartes) > 1) {
    $iniciais .= strtoupper(substr(end($nomePartes), 0, 1));
}

$pageTitle = 'Configurações';
include '../includes/header.php';
?>

<!-- Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<style>
/* ── Settings layout ── */
.settings-page {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 24px;
    align-items: start;
}
@media (max-width: 768px) {
    .settings-page { grid-template-columns: 1fr; }
}

/* ── Vertical tab nav ── */
.settings-tabs {
    background: #1e2044;
    border: 1px solid rgba(201,168,76,0.15);
    border-radius: 12px;
    overflow: hidden;
}
.settings-tab-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    cursor: pointer;
    font-size: .9rem;
    font-weight: 500;
    color: #9ca3af;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    transition: background .2s, color .2s;
    user-select: none;
    text-decoration: none;
}
.settings-tab-item:last-child { border-bottom: none; }
.settings-tab-item:hover { background: rgba(201,168,76,0.06); color: #f8f7f4; }
.settings-tab-item.active {
    background: rgba(201,168,76,0.12);
    color: #c9a84c;
    border-left: 3px solid #c9a84c;
}
.settings-tab-item i { width: 16px; text-align: center; }

/* ── Tab content panels ── */
.settings-content { display: none; }
.settings-content.active { display: block; }

/* ── Toggle switch ── */
.notif-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.notif-row:last-of-type { border-bottom: none; }
.notif-label h6 { margin: 0; font-size: .95rem; font-weight: 600; color: #f8f7f4; }
.notif-label p  { margin: 2px 0 0; font-size: .8rem; color: #6b7280; }

/* iOS-style toggle */
.toggle-switch { position: relative; display: inline-block; width: 48px; height: 26px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
    position: absolute; inset: 0;
    background: #374151;
    border-radius: 26px;
    cursor: pointer;
    transition: background .25s;
}
.toggle-slider::before {
    content: '';
    position: absolute;
    left: 3px; top: 3px;
    width: 20px; height: 20px;
    background: #fff;
    border-radius: 50%;
    transition: transform .25s;
}
.toggle-switch input:checked + .toggle-slider { background: #c9a84c; }
.toggle-switch input:checked + .toggle-slider::before { transform: translateX(22px); }

/* ── Photo upload ── */
.photo-upload-wrap {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto 16px;
}
.photo-upload-circle {
    display: block;
    width: 120px; height: 120px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid rgba(201,168,76,0.5);
    cursor: pointer;
    position: relative;
    transition: transform .2s;
}
.photo-upload-circle:hover { transform: scale(1.04); }
.photo-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg,#c9a84c,#a07830);
    display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem; font-weight: 700; color: #1a1a2e; border-radius: 50%;
}
.photo-overlay {
    position: absolute; inset: 0; border-radius: 50%;
    background: rgba(0,0,0,.55);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity .2s;
}
.photo-upload-circle:hover .photo-overlay { opacity: 1; }
.photo-overlay i { color: #fff; font-size: 1.4rem; }

/* Crop modal */
.crop-modal {
    display: none; position: fixed; z-index: 9999;
    inset: 0; background: rgba(0,0,0,.88); overflow-y: auto;
}
.crop-modal-content {
    position: relative;
    background: #1e2044;
    border: 1px solid rgba(201,168,76,0.25);
    border-radius: 16px;
    margin: 4% auto;
    padding: 32px;
    width: 90%; max-width: 640px;
    box-shadow: 0 8px 40px rgba(0,0,0,.6);
}
.crop-modal-content h3 { color: #f8f7f4; margin-bottom: 0; }
.crop-container { width: 100%; max-height: 420px; margin: 20px 0; overflow: hidden; }
.crop-container img { max-width: 100%; display: block; }
.crop-buttons { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
.close-crop {
    position: absolute; top: 14px; right: 20px;
    font-size: 28px; font-weight: bold; color: #9ca3af;
    cursor: pointer; background: none; border: none; line-height: 1;
}
.close-crop:hover { color: #f8f7f4; }
</style>

<!-- Page Header -->
<div class="page-header">
    <h2><i class="fas fa-cog me-2"></i>Configurações</h2>
    <div class="page-actions">
        <a href="perfil.php" class="btn-gold-outline">
            <i class="fas fa-user me-2"></i>Meu Perfil
        </a>
    </div>
</div>

<?php if ($error):   showAlert($error,   'danger');  endif; ?>
<?php if ($success): showAlert($success, 'success'); endif; ?>

<div class="settings-page">

    <!-- ─── Sidebar tabs ─── -->
    <nav class="settings-tabs">
        <a class="settings-tab-item <?= $activeTab === 'dados'          ? 'active' : '' ?>"
           href="#" onclick="switchTab('dados');return false">
            <i class="fas fa-user"></i> Meus Dados
        </a>
        <a class="settings-tab-item <?= $activeTab === 'senha'          ? 'active' : '' ?>"
           href="#" onclick="switchTab('senha');return false">
            <i class="fas fa-key"></i> Senha
        </a>
        <a class="settings-tab-item <?= $activeTab === 'notificacoes'   ? 'active' : '' ?>"
           href="#" onclick="switchTab('notificacoes');return false">
            <i class="fas fa-bell"></i> Notificações
        </a>
    </nav>

    <!-- ─── Tab: Meus Dados ─── -->
    <div id="tab-dados" class="settings-content <?= $activeTab === 'dados' ? 'active' : '' ?>">
        <div class="sc-card">
            <div class="sc-card-header">
                <h5 class="sc-card-title">Meus Dados</h5>
                <div class="sc-card-subtitle">Atualize suas informações pessoais e foto de perfil</div>
            </div>

            <!-- Photo section -->
            <div style="text-align:center; padding-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.08); margin-bottom:20px">
                <div class="photo-upload-wrap">
                    <label for="foto_perfil" class="photo-upload-circle">
                        <?php if (!empty($usuario['FOTO_PERFIL']) && file_exists($usuario['FOTO_PERFIL'])): ?>
                            <img src="<?= htmlspecialchars($usuario['FOTO_PERFIL']) ?>?<?= time() ?>"
                                 alt="Foto de perfil" id="photoPreview"
                                 style="width:100%;height:100%;object-fit:cover">
                        <?php else: ?>
                            <div class="photo-placeholder" id="photoPreview">
                                <?= htmlspecialchars($iniciais) ?>
                            </div>
                        <?php endif; ?>
                        <div class="photo-overlay"><i class="fas fa-camera"></i></div>
                    </label>

                    <?php if (!empty($usuario['FOTO_PERFIL'])): ?>
                    <form method="post" style="display:inline">
                        <button type="submit" name="remover_foto"
                                onclick="return confirm('Deseja remover a foto de perfil?')"
                                title="Remover foto"
                                style="
                                    position:absolute;bottom:4px;right:4px;
                                    width:32px;height:32px;border-radius:50%;
                                    background:#ef4444;color:#fff;border:2px solid #1a1a2e;
                                    display:flex;align-items:center;justify-content:center;
                                    cursor:pointer;font-size:.75rem;
                                ">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>

                <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" style="display:none">
                <p style="font-size:.75rem;color:#6b7280;margin:0">
                    <i class="fas fa-camera me-1"></i>Clique para alterar a foto &bull; JPG, PNG. Máx 5MB
                </p>
            </div>

            <!-- Data form -->
            <form method="post" class="sc-form">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nome Completo <span class="required">*</span></label>
                        <input type="text" name="nome" class="form-control"
                               value="<?= htmlspecialchars($usuario['USUARIO_NOME']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($usuario['USUARIO_EMAIL']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">CPF</label>
                        <input type="text" class="form-control"
                               value="<?= htmlspecialchars($usuario['USUARIO_CPF'] ?? '') ?>" disabled>
                        <div class="form-hint">Não é possível alterar o CPF</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Departamento</label>
                        <input type="text" class="form-control"
                               value="<?= htmlspecialchars($usuario['USUARIO_DEPARTAMENTO'] ?? '') ?>" disabled>
                        <div class="form-hint">Gerenciado pelo administrador</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Cargo</label>
                        <input type="text" class="form-control"
                               value="<?= htmlspecialchars($usuario['USUARIO_CARGO'] ?? '') ?>" disabled>
                        <div class="form-hint">Gerenciado pelo administrador</div>
                    </div>
                </div>

                <div style="text-align:right">
                    <button type="submit" name="atualizar_dados" class="btn-gold">
                        <i class="fas fa-save me-2"></i>Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ─── Tab: Senha ─── -->
    <div id="tab-senha" class="settings-content <?= $activeTab === 'senha' ? 'active' : '' ?>">
        <div class="sc-card">
            <div class="sc-card-header">
                <h5 class="sc-card-title">Alterar Senha</h5>
                <div class="sc-card-subtitle">Escolha uma senha forte com pelo menos 6 caracteres</div>
            </div>

            <form method="post" class="sc-form">
                <div class="form-group">
                    <label class="form-label">Senha Atual <span class="required">*</span></label>
                    <input type="password" name="senha_atual" class="form-control"
                           placeholder="Digite sua senha atual" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nova Senha <span class="required">*</span></label>
                        <input type="password" name="senha_nova" class="form-control"
                               placeholder="Mínimo 6 caracteres" minlength="6" required
                               id="novaSenha">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirmar Nova Senha <span class="required">*</span></label>
                        <input type="password" name="senha_confirma" class="form-control"
                               placeholder="Repita a nova senha" minlength="6" required
                               id="confirmaSenha" oninput="checkPasswordMatch()">
                    </div>
                </div>

                <div id="passwordMatchMsg" style="display:none; font-size:.8rem; margin-bottom:12px"></div>

                <div style="text-align:right">
                    <button type="submit" name="alterar_senha" class="btn-gold">
                        <i class="fas fa-key me-2"></i>Alterar Senha
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ─── Tab: Notificações ─── -->
    <div id="tab-notificacoes" class="settings-content <?= $activeTab === 'notificacoes' ? 'active' : '' ?>">
        <div class="sc-card">
            <div class="sc-card-header">
                <h5 class="sc-card-title">Preferências de Notificação</h5>
                <div class="sc-card-subtitle">Escolha quais notificações deseja receber</div>
            </div>

            <form method="post">
                <div class="notif-row">
                    <div class="notif-label">
                        <h6><i class="fas fa-check-circle me-2" style="color:#10b981"></i>Confirmação de Reserva</h6>
                        <p>Receba uma notificação quando sua reserva for confirmada</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="notif_confirmacao" value="1"
                               <?= !empty($configNotif['NOTIF_CONFIRMACAO']) ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="notif-row">
                    <div class="notif-label">
                        <h6><i class="fas fa-clock me-2" style="color:#f59e0b"></i>Lembrete de Reserva</h6>
                        <p>Receba lembretes antes do horário da sua reserva</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="notif_lembrete" value="1"
                               <?= !empty($configNotif['NOTIF_LEMBRETE']) ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="notif-row">
                    <div class="notif-label">
                        <h6><i class="fas fa-times-circle me-2" style="color:#ef4444"></i>Cancelamento de Reserva</h6>
                        <p>Receba uma notificação quando uma reserva for cancelada</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="notif_cancelamento" value="1"
                               <?= !empty($configNotif['NOTIF_CANCELAMENTO']) ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div style="text-align:right; padding-top:16px">
                    <button type="submit" name="salvar_notificacoes" class="btn-gold">
                        <i class="fas fa-save me-2"></i>Salvar Preferências
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- ─── Crop Modal ─── -->
<div id="cropModal" class="crop-modal">
    <div class="crop-modal-content">
        <button class="close-crop" onclick="closeCropModal()">&times;</button>
        <h3><i class="fas fa-crop me-2" style="color:#c9a84c"></i>Ajustar Foto de Perfil</h3>
        <div class="crop-container">
            <img id="cropImage" src="" alt="Imagem para cortar">
        </div>
        <div class="crop-buttons">
            <button class="btn-gold-outline" onclick="closeCropModal()">
                <i class="fas fa-times me-2"></i>Cancelar
            </button>
            <button class="btn-gold" onclick="cropAndUpload()">
                <i class="fas fa-check me-2"></i>Salvar Foto
            </button>
        </div>
    </div>
</div>

<script>
// ── Active tab on load (PHP-driven, just update nav state) ──
const INITIAL_TAB = '<?= $activeTab ?>';

function switchTab(tabId) {
    // Hide all content
    document.querySelectorAll('.settings-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.settings-tab-item').forEach(el => el.classList.remove('active'));

    // Show selected
    const content = document.getElementById('tab-' + tabId);
    if (content) content.classList.add('active');

    const navItems = document.querySelectorAll('.settings-tab-item');
    navItems.forEach(item => {
        if (item.getAttribute('onclick') && item.getAttribute('onclick').includes("'" + tabId + "'")) {
            item.classList.add('active');
        }
    });
}

// ── Password match indicator ──
function checkPasswordMatch() {
    const nova     = document.getElementById('novaSenha').value;
    const confirma = document.getElementById('confirmaSenha').value;
    const msg      = document.getElementById('passwordMatchMsg');
    if (!confirma) { msg.style.display = 'none'; return; }
    msg.style.display = 'block';
    if (nova === confirma) {
        msg.style.color = '#10b981';
        msg.innerHTML   = '<i class="fas fa-check me-1"></i>Senhas coincidem';
    } else {
        msg.style.color = '#ef4444';
        msg.innerHTML   = '<i class="fas fa-times me-1"></i>Senhas não coincidem';
    }
}

// ── Cropper.js ──
let cropper;

document.getElementById('foto_perfil').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 5242880) { alert('Arquivo muito grande. Máximo: 5MB'); return; }
    if (!file.type.match('image.*')) { alert('Selecione uma imagem válida'); return; }

    const reader = new FileReader();
    reader.onload = function(ev) {
        document.getElementById('cropModal').style.display = 'block';
        const image = document.getElementById('cropImage');
        image.src = ev.target.result;
        if (cropper) cropper.destroy();
        cropper = new Cropper(image, {
            aspectRatio: 1,
            viewMode: 2,
            dragMode: 'move',
            autoCropArea: 1,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        });
    };
    reader.readAsDataURL(file);
});

function closeCropModal() {
    document.getElementById('cropModal').style.display = 'none';
    if (cropper) { cropper.destroy(); cropper = null; }
    document.getElementById('foto_perfil').value = '';
}

function cropAndUpload() {
    if (!cropper) return;
    canvas = cropper.getCroppedCanvas({
        width: 200, height: 200,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });
    canvas.toBlob(function(blob) {
        const reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onloadend = function() {
            const base64data = reader.result;
            // Update preview optimistically
            const preview = document.getElementById('photoPreview');
            if (preview.tagName === 'DIV') {
                const img = document.createElement('img');
                img.src   = base64data;
                img.alt   = 'Foto de perfil';
                img.id    = 'photoPreview';
                img.style.cssText = 'width:100%;height:100%;object-fit:cover';
                preview.parentNode.replaceChild(img, preview);
            } else {
                preview.src = base64data;
            }

            const form  = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            const input  = document.createElement('input');
            input.type   = 'hidden';
            input.name   = 'cropped_image';
            input.value  = base64data;
            form.appendChild(input);
            document.body.appendChild(form);
            closeCropModal();
            form.submit();
        };
    });
}

window.addEventListener('click', function(e) {
    if (e.target === document.getElementById('cropModal')) closeCropModal();
});
</script>

<?php include '../includes/footer.php'; ?>
