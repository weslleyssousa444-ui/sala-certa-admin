<?php
require_once '../config/config.php';
require_once '../classes/Sala.php';
require_once '../includes/alert.php';

// Verificar se está logado
requireLogin();

// Verificar se ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: salas.php');
    exit;
}

$id = $_GET['id'];
$sala_dados = Sala::buscarPorId($id);

// Verificar se a sala existe
if (!$sala_dados) {
    header('Location: salas.php');
    exit;
}

$error = '';
$success = '';

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numSala = filter_input(INPUT_POST, 'num_sala', FILTER_VALIDATE_INT);
    $qtdPessoas = filter_input(INPUT_POST, 'qtd_pessoas', FILTER_VALIDATE_INT);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING);
    $tipoSala = filter_input(INPUT_POST, 'tipo_sala', FILTER_SANITIZE_STRING);
    $setorResponsavel = filter_input(INPUT_POST, 'setor_responsavel', FILTER_SANITIZE_STRING);
    
    // Validar dados
    if (empty($numSala) || empty($qtdPessoas) || empty($descricao) || empty($tipoSala)) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } else if ($numSala <= 0) {
        $error = 'O número da sala deve ser maior que zero.';
    } else if ($qtdPessoas <= 0) {
        $error = 'A capacidade da sala deve ser maior que zero.';
    } else {
        // Atualizar sala
        $sala = new Sala();
        $sala->setId($id);
        $sala->setNumSala($numSala);
        $sala->setQtdPessoas($qtdPessoas);
        $sala->setDescricao($descricao);
        $sala->setTipoSala($tipoSala);
        $sala->setSetorResponsavel($setorResponsavel);
        
        if ($sala->atualizar()) {
            $success = 'Sala atualizada com sucesso!';
            // Recarregar os dados da sala
            $sala_dados = Sala::buscarPorId($id);
        } else {
            $error = 'Erro ao atualizar sala.';
        }
    }
}

$pageTitle = 'Editar Sala';
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Editar Sala</h2>
            <a href="salas.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
        
        <?php if ($error): ?>
            <?php showAlert($error, 'danger'); ?>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <?php showAlert($success, 'success'); ?>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Formulário de Edição</h5>
            </div>
            <div class="card-body">
                <form method="post" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="num_sala" class="form-label">Número da Sala</label>
                            <input type="number" class="form-control" id="num_sala" name="num_sala" value="<?php echo $sala_dados['NUM_SALA']; ?>" required min="1">
                            <div class="invalid-feedback">
                                Por favor, informe um número válido para a sala.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="qtd_pessoas" class="form-label">Capacidade (pessoas)</label>
                            <input type="number" class="form-control" id="qtd_pessoas" name="qtd_pessoas" value="<?php echo $sala_dados['QTD_PESSOAS']; ?>" required min="1">
                            <div class="invalid-feedback">
                                Por favor, informe a capacidade da sala.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tipo_sala" class="form-label">Tipo de Sala</label>
                            <select class="form-select" id="tipo_sala" name="tipo_sala" required>
                                <option value="">Selecione o tipo</option>
                                <option value="aula" <?php echo (isset($sala_dados['TIPO_SALA']) && $sala_dados['TIPO_SALA'] == 'aula') ? 'selected' : ''; ?>>Sala de Aula</option>
                                <option value="laboratorio" <?php echo (isset($sala_dados['TIPO_SALA']) && $sala_dados['TIPO_SALA'] == 'laboratorio') ? 'selected' : ''; ?>>Laboratório</option>
                                <option value="reuniao" <?php echo (isset($sala_dados['TIPO_SALA']) && $sala_dados['TIPO_SALA'] == 'reuniao') ? 'selected' : ''; ?>>Sala de Reunião</option>
                                <option value="auditorio" <?php echo (isset($sala_dados['TIPO_SALA']) && $sala_dados['TIPO_SALA'] == 'auditorio') ? 'selected' : ''; ?>>Auditório</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor, selecione o tipo de sala.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="setor_responsavel" class="form-label">Setor Responsável</label>
                            <select class="form-select" id="setor_responsavel" name="setor_responsavel">
                                <option value="">Selecione o setor</option>
                                <option value="academico" <?php echo (isset($sala_dados['SETOR_RESPONSAVEL']) && $sala_dados['SETOR_RESPONSAVEL'] == 'academico') ? 'selected' : ''; ?>>Acadêmico</option>
                                <option value="financeiro" <?php echo (isset($sala_dados['SETOR_RESPONSAVEL']) && $sala_dados['SETOR_RESPONSAVEL'] == 'financeiro') ? 'selected' : ''; ?>>Financeiro</option>
                                <option value="secretaria" <?php echo (isset($sala_dados['SETOR_RESPONSAVEL']) && $sala_dados['SETOR_RESPONSAVEL'] == 'secretaria') ? 'selected' : ''; ?>>Secretaria</option>
                                <option value="ti" <?php echo (isset($sala_dados['SETOR_RESPONSAVEL']) && $sala_dados['SETOR_RESPONSAVEL'] == 'ti') ? 'selected' : ''; ?>>TI</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" required><?php echo $sala_dados['DESCRICAO']; ?></textarea>
                        <div class="invalid-feedback">
                            Por favor, informe uma descrição para a sala.
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Atualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


