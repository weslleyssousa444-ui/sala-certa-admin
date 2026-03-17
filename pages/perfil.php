<?php
require_once '../config/config.php';
require_once '../classes/Usuario.php';
require_once '../includes/alert.php';

requireLogin();

$error = '';
$success = '';

$usuario = Usuario::buscarPorId($_SESSION['usuario_id']);

// FORÇAR SINCRONIZAÇÃO DA FOTO NA SESSÃO
if (!empty($usuario['FOTO_PERFIL'])) {
    $_SESSION['usuario_foto'] = $usuario['FOTO_PERFIL'];
} else {
    if (isset($_SESSION['usuario_foto'])) {
        unset($_SESSION['usuario_foto']);
    }
}

// Processar foto cortada via base64
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
            header('Location: perfil.php?success=foto_atualizada');
            exit;
        } else {
            $error = 'Erro ao salvar foto no banco de dados.';
        }
    } else {
        $error = 'Erro ao salvar arquivo cortado.';
    }
}

// Verificar se foi redirecionado após upload
if (isset($_GET['success']) && $_GET['success'] == 'foto_atualizada') {
    $success = 'Foto de perfil atualizada com sucesso!';
}

// Remover foto
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
}

// Atualizar dados do perfil
if (isset($_POST['atualizar_perfil'])) {
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
            $success = 'Perfil atualizado com sucesso!';
            $usuario = Usuario::buscarPorId($_SESSION['usuario_id']);
        } else {
            $error = 'Erro ao atualizar perfil.';
        }
    }
}

// Generate initials
$nomePartes = explode(' ', trim($usuario['USUARIO_NOME']));
$iniciais   = strtoupper(substr($nomePartes[0], 0, 1));
if (count($nomePartes) > 1) {
    $iniciais .= strtoupper(substr(end($nomePartes), 0, 1));
}

$pageTitle = 'Meu Perfil';
include '../includes/header.php';
?>

<!-- Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<style>
/* Crop modal – minimal inline since it is modal-specific overlay */
.crop-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    inset: 0;
    background: rgba(0,0,0,0.88);
    overflow-y: auto;
}
.crop-modal-content {
    position: relative;
    background: #1e2044;
    border: 1px solid rgba(201,168,76,0.25);
    border-radius: 16px;
    margin: 3% auto;
    padding: 32px;
    width: 90%;
    max-width: 680px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.6);
}
.crop-modal-content h3 { color: #f8f7f4; margin-bottom: 0; }
.crop-container { width: 100%; max-height: 480px; margin: 20px 0; overflow: hidden; }
.crop-container img { max-width: 100%; display: block; }
.crop-buttons { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
.close-crop {
    position: absolute; top: 14px; right: 20px;
    font-size: 28px; font-weight: bold; color: #9ca3af;
    cursor: pointer; line-height: 1; background: none; border: none;
}
.close-crop:hover { color: #f8f7f4; }
</style>

<!-- Page Header -->
<div class="page-header">
    <h2><i class="fas fa-user-circle me-2"></i>Meu Perfil</h2>
    <div class="page-actions">
        <a href="configuracoes.php" class="btn-gold-outline">
            <i class="fas fa-cog me-2"></i>Configurações
        </a>
    </div>
</div>

<?php if ($error):   showAlert($error,   'danger');  endif; ?>
<?php if ($success): showAlert($success, 'success'); endif; ?>

<div style="display:grid; grid-template-columns:320px 1fr; gap:24px; align-items:start">

    <!-- ─── Left: Profile Card ─── -->
    <div>
        <!-- Avatar + identity -->
        <div class="sc-card" style="text-align:center; padding:32px 24px">
            <div class="profile-photo-upload" style="position:relative; width:140px; height:140px; margin:0 auto 20px;">
                <label for="foto_perfil" class="profile-photo-container" style="
                    display:block; width:140px; height:140px; border-radius:50%;
                    overflow:hidden; cursor:pointer; position:relative;
                    border:3px solid rgba(201,168,76,0.5);
                    box-shadow:0 4px 16px rgba(0,0,0,0.4);
                    transition:transform .25s;
                ">
                    <?php if (!empty($usuario['FOTO_PERFIL']) && file_exists($usuario['FOTO_PERFIL'])): ?>
                        <img src="<?= htmlspecialchars($usuario['FOTO_PERFIL']) ?>?v=<?= time() ?>"
                             alt="Foto de perfil"
                             id="photoPreview"
                             style="width:100%;height:100%;object-fit:cover;border-radius:50%">
                    <?php else: ?>
                        <div id="photoPreview" style="
                            width:100%;height:100%;border-radius:50%;
                            background:linear-gradient(135deg,#c9a84c,#a07830);
                            display:flex;align-items:center;justify-content:center;
                            font-size:3rem;font-weight:700;color:#1a1a2e;
                        "><?= htmlspecialchars($iniciais) ?></div>
                    <?php endif; ?>
                    <!-- hover overlay -->
                    <div style="
                        position:absolute;inset:0;border-radius:50%;
                        background:rgba(0,0,0,0.55);
                        display:flex;align-items:center;justify-content:center;
                        opacity:0;transition:opacity .25s;
                    " class="photo-hover-overlay">
                        <i class="fas fa-camera" style="color:#fff;font-size:1.6rem"></i>
                    </div>
                </label>

                <?php if (!empty($usuario['FOTO_PERFIL'])): ?>
                <form method="post" style="display:inline">
                    <button type="submit" name="remover_foto"
                            onclick="return confirm('Deseja remover a foto de perfil?')"
                            title="Remover foto"
                            style="
                                position:absolute;bottom:4px;right:4px;
                                width:36px;height:36px;border-radius:50%;
                                background:#ef4444;color:#fff;border:2px solid #1a1a2e;
                                display:flex;align-items:center;justify-content:center;
                                cursor:pointer;transition:background .2s;font-size:.85rem;
                            ">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
                <?php endif; ?>
            </div>

            <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" style="display:none">

            <h4 style="margin:0;font-size:1.15rem;font-weight:700;color:#f8f7f4" id="displayNome">
                <?= htmlspecialchars($usuario['USUARIO_NOME']) ?>
            </h4>

            <?php if (!empty($usuario['USUARIO_CARGO'])): ?>
            <div style="font-size:.85rem;color:#c9a84c;margin-top:4px" id="displayCargo">
                <?= htmlspecialchars($usuario['USUARIO_CARGO']) ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($usuario['USUARIO_DEPARTAMENTO'])): ?>
            <div style="font-size:.8rem;color:#9ca3af;margin-top:2px" id="displayDepto">
                <?= htmlspecialchars($usuario['USUARIO_DEPARTAMENTO']) ?>
            </div>
            <?php endif; ?>

            <p style="font-size:.75rem;color:#6b7280;margin-top:16px;margin-bottom:0">
                <i class="fas fa-camera me-1"></i>Clique na foto para alterar &bull; JPG, PNG. Máx 5MB
            </p>
        </div>

        <!-- Quick details -->
        <div class="sc-card" style="margin-top:16px">
            <div class="sc-card-header">
                <h5 class="sc-card-title">Detalhes</h5>
            </div>
            <?php
            $details = [
                ['label'=>'Email',        'value'=>$usuario['USUARIO_EMAIL']],
                ['label'=>'CPF',          'value'=>$usuario['USUARIO_CPF'] ?? '—'],
                ['label'=>'Departamento', 'value'=>$usuario['USUARIO_DEPARTAMENTO'] ?? '—'],
                ['label'=>'Cargo',        'value'=>$usuario['USUARIO_CARGO'] ?? '—'],
            ];
            foreach ($details as $d): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid rgba(255,255,255,0.06)">
                <span style="font-size:.8rem;color:#6b7280"><?= $d['label'] ?></span>
                <span style="font-weight:600;font-size:.85rem;text-align:right;max-width:60%;word-break:break-all">
                    <?= htmlspecialchars($d['value']) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ─── Right: Edit form ─── -->
    <div class="sc-card">
        <div class="sc-card-header" style="display:flex;justify-content:space-between;align-items:center">
            <div>
                <h5 class="sc-card-title">Informações Pessoais</h5>
                <div class="sc-card-subtitle">Gerencie seus dados de exibição</div>
            </div>
            <button type="button" id="btnEditar" class="btn-gold-outline" onclick="toggleEdit()">
                <i class="fas fa-edit me-2"></i>Editar Perfil
            </button>
        </div>

        <form method="post" class="sc-form" id="perfilForm">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nome Completo <span class="required">*</span></label>
                    <input type="text" name="nome" id="inputNome" class="form-control" readonly
                           value="<?= htmlspecialchars($usuario['USUARIO_NOME']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="email" id="inputEmail" class="form-control" readonly
                           value="<?= htmlspecialchars($usuario['USUARIO_EMAIL']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">CPF</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['USUARIO_CPF'] ?? '') ?>" disabled>
                    <div class="form-hint">Não é possível alterar o CPF</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Departamento</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['USUARIO_DEPARTAMENTO'] ?? '') ?>" disabled>
                    <div class="form-hint">Gerenciado pelo administrador</div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Cargo</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['USUARIO_CARGO'] ?? '') ?>" disabled>
                    <div class="form-hint">Gerenciado pelo administrador</div>
                </div>
            </div>

            <!-- Save button — hidden until edit mode -->
            <div id="saveActions" style="display:none; text-align:right; padding-top:8px">
                <button type="button" class="btn-gold-outline" onclick="cancelEdit()" style="margin-right:8px">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button type="submit" name="atualizar_perfil" class="btn-gold">
                    <i class="fas fa-save me-2"></i>Salvar Alterações
                </button>
            </div>
        </form>

        <div style="margin-top:20px;padding-top:20px;border-top:1px solid rgba(255,255,255,0.08)">
            <p style="font-size:.8rem;color:#6b7280;margin:0">
                <i class="fas fa-lock me-1"></i>
                Para alterar sua senha, acesse
                <a href="configuracoes.php?tab=senha" style="color:#c9a84c">Configurações &rarr; Senha</a>.
            </p>
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
// ── Photo hover effect ──
const photoLabel = document.querySelector('.profile-photo-container');
const overlay    = document.querySelector('.photo-hover-overlay');
if (photoLabel && overlay) {
    photoLabel.addEventListener('mouseenter', () => overlay.style.opacity = '1');
    photoLabel.addEventListener('mouseleave', () => overlay.style.opacity = '0');
}

// ── Inline edit toggle ──
const editableIds   = ['inputNome', 'inputEmail'];
let   originalValues = {};

function toggleEdit() {
    editableIds.forEach(id => {
        const el = document.getElementById(id);
        originalValues[id] = el.value;
        el.removeAttribute('readonly');
    });
    document.getElementById('saveActions').style.display = 'block';
    document.getElementById('btnEditar').style.display   = 'none';
}

function cancelEdit() {
    editableIds.forEach(id => {
        const el = document.getElementById(id);
        el.value = originalValues[id];
        el.setAttribute('readonly', '');
    });
    document.getElementById('saveActions').style.display = 'none';
    document.getElementById('btnEditar').style.display   = '';
}

// ── Cropper.js ──
let cropper;

document.getElementById('foto_perfil').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    if (file.size > 5242880) { alert('Arquivo muito grande. Máximo: 5MB'); return; }
    if (!file.type.match('image.*')) { alert('Por favor, selecione uma imagem válida'); return; }

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
    if (!cropper) { alert('Erro ao processar imagem'); return; }
    const canvas = cropper.getCroppedCanvas({
        width: 400, height: 400,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });
    const croppedImage = canvas.toDataURL('image/png');
    updatePhotoPreview(croppedImage);

    const form  = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';
    const input  = document.createElement('input');
    input.type   = 'hidden';
    input.name   = 'cropped_image';
    input.value  = croppedImage;
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

function updatePhotoPreview(imageUrl) {
    const preview = document.getElementById('photoPreview');
    if (preview.tagName === 'DIV') {
        const img     = document.createElement('img');
        img.src       = imageUrl;
        img.alt       = 'Foto de perfil';
        img.id        = 'photoPreview';
        img.style.cssText = 'width:100%;height:100%;object-fit:cover;border-radius:50%';
        preview.parentNode.replaceChild(img, preview);
    } else {
        preview.src = imageUrl + '?' + Date.now();
    }
    closeCropModal();
}

window.addEventListener('click', function(e) {
    if (e.target === document.getElementById('cropModal')) closeCropModal();
});
</script>

<?php include '../includes/footer.php'; ?>
