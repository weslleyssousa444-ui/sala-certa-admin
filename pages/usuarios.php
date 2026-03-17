<?php
require_once '../config/config.php';
require_once '../classes/Usuario.php';
require_once '../includes/alert.php';

// Verificar se está logado
requireLogin();

// Excluir usuário
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id = $_GET['excluir'];
    
    // Não permitir excluir o próprio usuário
    if ($id == $_SESSION['usuario_id']) {
        $errorMsg = "Você não pode excluir seu próprio usuário.";
    } else {
        if (Usuario::excluir($id)) {
            $successMsg = "Usuário excluído com sucesso!";
        } else {
            $errorMsg = "Erro ao excluir o usuário.";
        }
    }
}

// Listar todos os usuários
$usuarios = Usuario::listarTodos();

$pageTitle = 'Usuários';
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gerenciar Usuários</h2>
            <a href="novo_usuario.php" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>Novo Usuário
            </a>
        </div>
        
        <?php if (isset($successMsg)): ?>
            <?php showAlert($successMsg, 'success'); ?>
        <?php endif; ?>
        
        <?php if (isset($errorMsg)): ?>
            <?php showAlert($errorMsg, 'danger'); ?>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Lista de Usuários</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>CPF</th>
                                <th>Curso</th>
                                <th>Tipo</th>
                                <th>Setor</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo $usuario['USUARIO_ID']; ?></td>
                                <td><?php echo $usuario['USUARIO_NOME']; ?></td>
                                <td><?php echo $usuario['USUARIO_EMAIL']; ?></td>
                                <td><?php echo $usuario['USUARIO_CPF']; ?></td>
                                <td><?php echo $usuario['USUARIO_CURSO']; ?></td>
                                <td>
                                    <?php 
                                    $tipo = $usuario['TIPO_USUARIO'] ?? 'comum';
                                    $badgeClass = $tipo == 'admin' ? 'bg-danger' : 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo ucfirst($tipo); ?>
                                    </span>
                                </td>
                                <td><?php echo !empty($usuario['SETOR']) ? ucfirst($usuario['SETOR']) : '<span class="text-muted">-</span>'; ?></td>
                                <td>
                                    <a href="ver_usuario.php?id=<?php echo $usuario['USUARIO_ID']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Ver detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="editar_usuario.php?id=<?php echo $usuario['USUARIO_ID']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($usuario['USUARIO_ID'] != $_SESSION['usuario_id']): ?>
                                    <a href="usuarios.php?excluir=<?php echo $usuario['USUARIO_ID']; ?>" class="btn btn-sm btn-danger btn-delete" data-bs-toggle="tooltip" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Nenhum usuário encontrado</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

