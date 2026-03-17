<?php
require_once '../config/config.php';
require_once '../classes/Usuario.php';
require_once '../includes/alert.php';

requireLogin();

$error = '';
$success = '';

$usuario = Usuario::buscarPorId($_SESSION['usuario_id']);

// Processar upload de foto
if (isset($_POST['upload_foto'])) {
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto_perfil']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            if ($_FILES['foto_perfil']['size'] <= 5242880) {
                $upload_dir = 'uploads/perfil/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $new_filename = 'user_' . $_SESSION['usuario_id'] . '_' . time() . '.' . $filetype;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $upload_path)) {
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
                    } else {
                        $error = 'Erro ao salvar foto no banco de dados.';
                    }
                } else {
                    $error = 'Erro ao fazer upload do arquivo.';
                }
            } else {
                $error = 'Arquivo muito grande. Máximo: 5MB.';
            }
        } else {
            $error = 'Tipo de arquivo não permitido. Use: JPG, JPEG, PNG ou GIF.';
        }
    } else {
        $error = 'Nenhum arquivo selecionado ou erro no upload.';
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
        } else {
            $error = 'Erro ao salvar foto no banco de dados.';
        }
    } else {
        $error = 'Erro ao salvar arquivo cortado.';
    }
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
if (isset($_POST['atualizar_dados'])) {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (empty($nome) || empty($email)) {
        $error = 'Nome e email são obrigatórios.';
    } else {
        $conn = Conexao::getConn();
        $sql = "UPDATE USUARIO SET USUARIO_NOME = :nome, USUARIO_EMAIL = :email WHERE USUARIO_ID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':id', $_SESSION['usuario_id']);
        
        if ($stmt->execute()) {
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_email'] = $email;
            $success = 'Dados atualizados com sucesso!';
            $usuario = Usuario::buscarPorId($_SESSION['usuario_id']);
        } else {
            $error = 'Erro ao atualizar dados.';
        }
    }
}

// Alterar senha
if (isset($_POST['alterar_senha'])) {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $senha_nova = $_POST['senha_nova'] ?? '';
    $senha_confirma = $_POST['senha_confirma'] ?? '';
    
    if (empty($senha_atual) || empty($senha_nova) || empty($senha_confirma)) {
        $error = 'Por favor, preencha todos os campos de senha.';
    } elseif ($senha_nova !== $senha_confirma) {
        $error = 'As senhas novas não coincidem.';
    } elseif (strlen($senha_nova) < 6) {
        $error = 'A nova senha deve ter pelo menos 6 caracteres.';
    } else {
        $conn = Conexao::getConn();
        $sql = "SELECT USUARIO_SENHA FROM USUARIO WHERE USUARIO_ID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $_SESSION['usuario_id']);
        $stmt->execute();
        $senha_bd = $stmt->fetch()['USUARIO_SENHA'];
        
        $senha_correta = false;
        if (password_verify($senha_atual, $senha_bd)) {
            $senha_correta = true;
        } elseif ($senha_bd === $senha_atual) {
            $senha_correta = true;
        }
        
        if (!$senha_correta) {
            $error = 'Senha atual incorreta.';
        } else {
            $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
            $sql = "UPDATE USUARIO SET USUARIO_SENHA = :senha WHERE USUARIO_ID = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':senha', $senha_hash);
            $stmt->bindValue(':id', $_SESSION['usuario_id']);
            
            if ($stmt->execute()) {
                $success = 'Senha alterada com sucesso!';
            } else {
                $error = 'Erro ao alterar senha.';
            }
        }
    }
}

$pageTitle = 'Configurações';
include '../includes/header.php';
?>

<style>
.crop-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
}

.crop-modal-content {
    position: relative;
    background-color: white;
    margin: 5% auto;
    padding: 30px;
    width: 90%;
    max-width: 600px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.crop-container {
    max-width: 100%;
    max-height: 400px;
    margin: 20px 0;
}

.crop-container img {
    max-width: 100%;
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
    right: 20px;
    font-size: 28px;
    font-weight: bold;
    color: #aaa;
    cursor: pointer;
}

.close-crop:hover {
    color: #000;
}

.profile-photo-upload {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto 20px;
}

.profile-photo-container {
    width: 150px !important;
    height: 150px !important;
    border-radius: 50% !important;
    overflow: hidden;
    border: 4px solid #37D0C0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}

.profile-photo-container:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.profile-photo-preview {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover;
    border-radius: 50% !important;
}

.profile-photo-placeholder {
    width: 100% !important;
    height: 100% !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #37D0C0, #53C598);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 4rem;
    font-weight: 700;
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
    transition: opacity 0.3s ease;
    border-radius: 50%;
}

.profile-photo-container:hover .profile-photo-overlay {
    opacity: 1;
}

.profile-photo-overlay i {
    color: white;
    font-size: 2.5rem;
}

.profile-photo-input {
    display: none;
}

.remove-photo-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    background: #e40b0b;
    color: white;
    border: 3px solid white;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.remove-photo-btn:hover {
    background: #c70a0a;
    transform: scale(1.1);
}
</style>

<div class="row">
    <div class="col-md-12">
        <h2><i class="fas fa-cog me-2"></i>Configurações da Conta</h2>
        
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
                            <img src="<?php echo $usuario['FOTO_PERFIL']; ?>?<?php echo time(); ?>" alt="Foto de perfil" class="profile-photo-preview" id="photoPreview">
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
                            <label class="form-label">CPF</label>
                            <input type="text" class="form-control" value="<?php echo $usuario['USUARIO_CPF']; ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Curso</label>
                            <input type="text" class="form-control" value="<?php echo $usuario['USUARIO_CURSO']; ?>" disabled>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" name="atualizar_dados" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Alterar Senha</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="senha_atual" class="form-label">Senha Atual</label>
                        <input type="password" class="form-control" id="senha_atual" name="senha_atual">
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
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" name="alterar_senha" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i>Alterar Senha
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="cropModal" class="crop-modal">
    <div class="crop-modal-content">
        <span class="close-crop" onclick="closeCropModal()">&times;</span>
        <h3>Ajustar Foto de Perfil</h3>
        <div class="crop-container">
            <img id="cropImage" src="" alt="Imagem para cortar">
        </div>
        <div class="crop-buttons">
            <button class="btn btn-secondary" onclick="closeCropModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="cropAndUpload()">
                <i class="fas fa-check me-2"></i>Salvar Foto
            </button>
        </div>
    </div>
</div>

<script>
let cropper;

document.getElementById('foto_perfil').addEventListener('change', function(e) {
    const file = e.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(event) {
            document.getElementById('cropModal').style.display = 'block';
            
            const image = document.getElementById('cropImage');
            image.src = event.target.result;
            
            if (cropper) {
                cropper.destroy();
            }
            
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

function closeCropModal() {
    document.getElementById('cropModal').style.display = 'none';
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
    document.getElementById('foto_perfil').value = '';
}

function cropAndUpload() {
    if (!cropper) return;
    
    const canvas = cropper.getCroppedCanvas({
        width: 200,
        height: 200,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });
    
    canvas.toBlob(function(blob) {
        const reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onloadend = function() {
            const base64data = reader.result;
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'cropped_image';
            input.value = base64data;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

window.onclick = function(event) {
    const modal = document.getElementById('cropModal');
    if (event.target == modal) {
        closeCropModal();
    }
}
</script>

<?php include '../includes/footer.php'; ?>

