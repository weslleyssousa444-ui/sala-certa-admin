<?php
require_once '../config/config.php';
require_once '../classes/Sala.php';
require_once '../includes/alert.php';

requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: salas.php');
    exit;
}

$id = $_GET['id'];
$sala_dados = Sala::buscarPorId($id);

if (!$sala_dados) {
    header('Location: salas.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numSala = filter_input(INPUT_POST, 'num_sala', FILTER_SANITIZE_SPECIAL_CHARS);
    $qtdPessoas = filter_input(INPUT_POST, 'qtd_pessoas', FILTER_VALIDATE_INT);
    $descricao = htmlspecialchars($_POST['descricao'] ?? '');
    $tipoSala = filter_input(INPUT_POST, 'tipo_sala', FILTER_SANITIZE_SPECIAL_CHARS);
    $setorResponsavel = filter_input(INPUT_POST, 'setor_responsavel', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($numSala) || empty($qtdPessoas) || empty($tipoSala)) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } else if ($qtdPessoas <= 0) {
        $error = 'A capacidade da sala deve ser maior que zero.';
    } else {
        $sala = new Sala();
        $sala->setId($id);
        $sala->setNumSala($numSala);
        $sala->setQtdPessoas($qtdPessoas);
        $sala->setDescricao($descricao ?? '');
        $sala->setTipoSala($tipoSala);
        $sala->setSetorResponsavel($setorResponsavel ?? '');

        if ($sala->atualizar()) {
            $success = 'Sala atualizada com sucesso!';
            $sala_dados = Sala::buscarPorId($id);
        } else {
            $error = 'Erro ao atualizar sala.';
        }
    }
}

$pageTitle = 'Editar Sala';
include '../includes/header.php';
?>

<div class="page-header">
    <h2>Editar Sala</h2>
    <a href="salas.php" class="btn-gray"><i class="fas fa-arrow-left me-2"></i>Voltar</a>
</div>

<?php if ($error): ?>
    <div class="sc-alert sc-alert-danger"><i class="fas fa-exclamation-circle"></i><span><?= $error ?></span></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="sc-alert sc-alert-success"><i class="fas fa-check-circle"></i><span><?= $success ?></span></div>
<?php endif; ?>

<div class="sc-card">
    <form method="post" class="sc-form">
        <div class="form-row">
            <div>
                <label class="form-label">Número / Código da Sala *</label>
                <input type="text" name="num_sala" class="form-control" value="<?= htmlspecialchars($sala_dados['NUM_SALA']) ?>" required>
            </div>
            <div>
                <label class="form-label">Capacidade (pessoas) *</label>
                <input type="number" name="qtd_pessoas" class="form-control" value="<?= htmlspecialchars($sala_dados['QTD_PESSOAS']) ?>" required min="1">
            </div>
        </div>

        <div class="form-row">
            <div>
                <label class="form-label">Tipo de Sala *</label>
                <select name="tipo_sala" class="form-select" required>
                    <option value="">Selecione o tipo</option>
                    <option value="Teatro" <?= ($sala_dados['TIPO_SALA'] == 'Teatro') ? 'selected' : '' ?>>Teatro</option>
                    <option value="Apresentação" <?= ($sala_dados['TIPO_SALA'] == 'Apresentação') ? 'selected' : '' ?>>Apresentação</option>
                    <option value="reuniao" <?= ($sala_dados['TIPO_SALA'] == 'reuniao') ? 'selected' : '' ?>>Reunião</option>
                    <option value="auditorio" <?= ($sala_dados['TIPO_SALA'] == 'auditorio') ? 'selected' : '' ?>>Auditório</option>
                    <option value="outro" <?= ($sala_dados['TIPO_SALA'] == 'outro') ? 'selected' : '' ?>>Outro</option>
                </select>
            </div>
            <div>
                <label class="form-label">Setor Responsável</label>
                <select name="setor_responsavel" class="form-select">
                    <option value="">Selecione o setor</option>
                    <option value="academico" <?= (isset($sala_dados['SETOR_RESPONSAVEL']) && $sala_dados['SETOR_RESPONSAVEL'] == 'academico') ? 'selected' : '' ?>>Acadêmico</option>
                    <option value="financeiro" <?= (isset($sala_dados['SETOR_RESPONSAVEL']) && $sala_dados['SETOR_RESPONSAVEL'] == 'financeiro') ? 'selected' : '' ?>>Financeiro</option>
                    <option value="secretaria" <?= (isset($sala_dados['SETOR_RESPONSAVEL']) && $sala_dados['SETOR_RESPONSAVEL'] == 'secretaria') ? 'selected' : '' ?>>Secretaria</option>
                    <option value="ti" <?= (isset($sala_dados['SETOR_RESPONSAVEL']) && $sala_dados['SETOR_RESPONSAVEL'] == 'ti') ? 'selected' : '' ?>>TI</option>
                    <option value="rh" <?= (isset($sala_dados['SETOR_RESPONSAVEL']) && $sala_dados['SETOR_RESPONSAVEL'] == 'rh') ? 'selected' : '' ?>>Recursos Humanos</option>
                    <option value="coordenacao" <?= (isset($sala_dados['SETOR_RESPONSAVEL']) && $sala_dados['SETOR_RESPONSAVEL'] == 'coordenacao') ? 'selected' : '' ?>>Coordenação</option>
                    <option value="diretoria" <?= (isset($sala_dados['SETOR_RESPONSAVEL']) && $sala_dados['SETOR_RESPONSAVEL'] == 'diretoria') ? 'selected' : '' ?>>Diretoria</option>
                </select>
            </div>
        </div>

        <div>
            <label class="form-label">Descrição / Recursos</label>
            <textarea name="descricao" class="form-control" rows="3"><?= htmlspecialchars($sala_dados['DESCRICAO'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <a href="salas.php" class="btn-gray">Cancelar</a>
            <button type="submit" class="btn-gold"><i class="fas fa-save me-2"></i>Salvar</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
