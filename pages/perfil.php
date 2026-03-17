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
    list(, $data) = explode(',', $data);
    $data = base64_decode($data);
    
    $upload_dir = 'uploads/perfil/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $new_filename = 'user_' . $_SESSION['usuario_id'] . '_' . time() . '.png';
    $upload_path = $upload_dir . $new_filename;
    
    if (file_put_contents($upload_path, $data)) {
        if (!empty($usuario['FOTO_PERFIL']) && file_exists($usuario['FOTO_PERFIL'])) {
            unlink($usuario['FOTO_PERFIL']);
        }
        
        $conn = Conexao::getConn();
        $sql = "UPDATE USUARIO SET FOTO_PERFIL = :foto WHERE USUARIO_ID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':foto', $upload_path);
        $stmt->bindValue(':id', $_SESSION['usuario_id']);
        
        if ($stmt->execute()) {
            $_SESSION['usuario_foto'] = $upload_path;
            $success = 'Foto de perfil atualizada com sucesso!';
            $usuario = Usuario::buscarPorId($_SESSION['usuario_id']);
            
            // FORÇAR RELOAD DA PÁGINA
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
    $sql = "UPDATE USUARIO SET FOTO_PERFIL = NULL WHERE USUARIO_ID = :id";
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
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha_atual = $_POST['senha_atual'] ?? '';
    $senha_nova = $_POST['senha_nova'] ?? '';
    $senha_confirma = $_POST['senha_confirma'] ?? '';
    
    if (empty($nome) || empty($email)) {
        $error = 'Nome e email são obrigatórios.';
    } else {
        $usuarioObj = Usuario::buscarPorId($_SESSION['usuario_id']);
        
        if (!empty($senha_atual)) {
            if (!password_verify($senha_atual, $usuarioObj['USUARIO_SENHA'])) {
                $error = 'Senha atual incorreta.';
            } else if (empty($senha_nova) || empty($senha_confirma)) {
                $error = 'Preencha a nova senha e a confirmação.';
            } else if ($senha_nova !== $senha_confirma) {
                $error = 'As senhas não coincidem.';
            } else if (strlen($senha_nova) < 6) {
                $error = 'A nova senha deve ter pelo menos 6 caracteres.';
            }
        }
        
        if (empty($error)) {
            $conn = Conexao::getConn();
            
            if (!empty($senha_nova)) {
                $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
                $sql = "UPDATE USUARIO SET USUARIO_NOME = :nome, USUARIO_EMAIL = :email, USUARIO_SENHA = :senha WHERE USUARIO_ID = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':nome', $nome);
                $stmt->bindValue(':email', $email);
                $stmt->bindValue(':senha', $senha_hash);
                $stmt->bindValue(':id', $_SESSION['usuario_id']);
            } else {
                $sql = "UPDATE USUARIO SET USUARIO_NOME = :nome, USUARIO_EMAIL = :email WHERE USUARIO_ID = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':nome', $nome);
                $stmt->bindValue(':email', $email);
                $stmt->bindValue(':id', $_SESSION['usuario_id']);
            }
            
            if ($stmt->execute()) {
                $_SESSION['usuario_nome'] = $nome;
                $_SESSION['usuario_email'] = $email;
                $success = 'Perfil atualizado com sucesso!';
                $usuario = Usuario::buscarPorId($_SESSION['usuario_id']);
            } else {
                $error = 'Erro ao atualizar perfil.';
            }
        }
    }
}

$pageTitle = 'Meu Perfil';
include '../includes/header.php';
?>

<!-- Adicionar Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<style>
/* Modal de Crop */
.crop-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    overflow: auto;
}

.crop-modal-content {
    position: relative;
    background-color: white;
    margin: 2% auto;
    padding: 30px;
    width: 90%;
    max-width: 700px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
}

.crop-container {
    width: 100%;
    max-height: 500px;
    margin: 20px 0;
    overflow: hidden;
}

.crop-container img {
    max-width: 100%;
    display: block;
}

.crop-buttons {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.close-crop {
    position: absolute;
    top: 15px;
    right: 25px;
    font-size: 35px;
    font-weight: bold;
    color: #aaa;
    cursor: pointer;
    transition: color 0.3s;
}

.close-crop:hover {
    color: #000;
}

/* Estilo do preview de foto */
.profile-photo-upload {
    position: relative;
    display: inline-block;
}

.profile-photo-container {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid #37D0C0;
    cursor: pointer;
    display: block;
    margin: 0 auto;
    position: relative;
    transition: transform 0.3s;
}

.profile-photo-container:hover {
    transform: scale(1.05);
}

.profile-photo-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-photo-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    font-weight: bold;
    color: white;
}

.profile-photo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.profile-photo-container:hover .profile-photo-overlay {
    opacity: 1;
}

.profile-photo-overlay i {
    font-size: 2.5rem;
    color: white;
}

.profile-photo-input {
    display: none;
}

.remove-photo-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    cursor: pointer;
    font-size: 1rem;
    transition: background 0.3s;
}

.remove-photo-btn:hover {
    background: #c82333;
}
</style>

<div class="row">
    <div class="col-md-12">
        <h2><i class="fas fa-user-circle me-2"></i>Meu Perfil</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Foto de Perfil</h5>
            </div>
            <div class="card-body text-center">
                <div class="profile-photo-upload">
                    <label for="foto_perfil" class="profile-photo-container">
                        <?php if (!empty($usuario['FOTO_PERFIL']) && file_exists($usuario['FOTO_PERFIL'])): ?>
                            <img src="<?php echo $usuario['FOTO_PERFIL']; ?>?v=<?php echo time(); ?>" 
                                 alt="Foto de perfil" 
                                 class="profile-photo-preview" 
                                 id="photoPreview">
                        <?php else: ?>
                            <div class="profile-photo-placeholder" id="photoPreview">
                                <?php echo strtoupper(substr($usuario['USUARIO_NOME'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="profile-photo-overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </label>
                    
                    <?php if (!empty($usuario['FOTO_PERFIL'])): ?>
                    <form method="post" style="display: inline;">
                        <button type="submit" name="remover_foto" class="remove-photo-btn" onclick="return confirm('Deseja remover a foto de perfil?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                
                <input type="file" id="foto_perfil" name="foto_perfil" class="profile-photo-input" accept="image/*">
                
                <p class="text-muted mt-3 mb-0">
                    <small>
                        <i class="fas fa-info-circle"></i>
                        JPG, JPEG, PNG ou GIF. Máx: 5MB
                    </small>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informações Pessoais</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nome" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $usuario['USUARIO_NOME']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $usuario['USUARIO_EMAIL']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="cpf" class="form-label">CPF</label>
                            <input type="text" class="form-control" value="<?php echo $usuario['USUARIO_CPF']; ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="curso" class="form-label">Curso</label>
                            <input type="text" class="form-control" value="<?php echo $usuario['USUARIO_CURSO']; ?>" disabled>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3"><i class="fas fa-key me-2"></i>Alterar Senha (opcional)</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="senha_atual" class="form-label">Senha Atual</label>
                            <input type="password" class="form-control" id="senha_atual" name="senha_atual">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="senha_nova" class="form-label">Nova Senha</label>
                            <input type="password" class="form-control" id="senha_nova" name="senha_nova" minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label for="senha_confirma" class="form-label">Confirmar Nova Senha</label>
                            <input type="password" class="form-control" id="senha_confirma" name="senha_confirma" minlength="6">
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Para alterar a senha, preencha todos os campos de senha. Deixe em branco para manter a senha atual.</small>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" name="atualizar_perfil" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Crop -->
<div id="cropModal" class="crop-modal">
    <div class="crop-modal-content">
        <span class="close-crop" onclick="closeCropModal()">&times;</span>
        <h3 class="mb-3"><i class="fas fa-crop me-2"></i>Ajustar Foto de Perfil</h3>
        <div class="crop-container">
            <img id="cropImage" src="" alt="Imagem para cortar">
        </div>
        <div class="crop-buttons">
            <button class="btn btn-secondary" onclick="closeCropModal()">
                <i class="fas fa-times me-2"></i>Cancelar
            </button>
            <button class="btn btn-primary" onclick="cropAndUpload()">
                <i class="fas fa-check me-2"></i>Salvar Foto
            </button>
        </div>
    </div>
</div>

<script>
let cropper;

// Quando selecionar arquivo
document.getElementById('foto_perfil').addEventListener('change', function(e) {
    const file = e.target.files[0];
    
    if (file) {
        // Validar tamanho (5MB)
        if (file.size > 5242880) {
            alert('Arquivo muito grande. Máximo: 5MB');
            return;
        }
        
        // Validar tipo
        if (!file.type.match('image.*')) {
            alert('Por favor, selecione uma imagem válida');
            return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(event) {
            // Mostrar modal
            document.getElementById('cropModal').style.display = 'block';
            
            // Configurar imagem no cropper
            const image = document.getElementById('cropImage');
            image.src = event.target.result;
            
            // Destruir cropper anterior se existir
            if (cropper) {
                cropper.destroy();
            }
            
            // Inicializar novo cropper
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
    }
});

// Fechar modal
function closeCropModal() {
    document.getElementById('cropModal').style.display = 'none';
    if (cropper) {
        cropper.destroy();
    }
    document.getElementById('foto_perfil').value = '';
}

// Recortar e fazer upload
function cropAndUpload() {
    if (!cropper) {
        alert('Erro ao processar imagem');
        return;
    }
    
    // Obter imagem cortada em formato circular
    const canvas = cropper.getCroppedCanvas({
        width: 400,
        height: 400,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });
    
    // Converter para base64
    const croppedImage = canvas.toDataURL('image/png');
    
    // Atualizar preview ANTES de enviar
    updatePhotoPreview(croppedImage);
    
    // Criar formulário e enviar
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'cropped_image';
    input.value = croppedImage;
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

// Atualizar preview da foto
function updatePhotoPreview(imageUrl) {
    const preview = document.getElementById('photoPreview');
    
    // Se é placeholder, substituir por img
    if (preview.classList.contains('profile-photo-placeholder')) {
        const img = document.createElement('img');
        img.src = imageUrl;
        img.alt = 'Foto de perfil';
        img.className = 'profile-photo-preview';
        img.id = 'photoPreview';
        preview.parentNode.replaceChild(img, preview);
    } else {
        // Já é img, só atualizar src
        preview.src = imageUrl + '?' + new Date().getTime();
    }
    
    closeCropModal();
}

// Fechar modal ao clicar fora
window.onclick = function(event) {
    const modal = document.getElementById('cropModal');
    if (event.target == modal) {
        closeCropModal();
    }
}
</script>

<?php include '../includes/footer.php'; ?>

