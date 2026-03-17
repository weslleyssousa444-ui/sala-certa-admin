<?php
require_once '../config/config.php';
require_once '../classes/Sala.php';
require_once '../includes/alert.php';

requireLogin();

$error = '';
$success = '';

// Buscar salas existentes
$salasExistentes = Sala::listarTodas();

// Agrupar por andar
$salasPorAndar = [];
foreach ($salasExistentes as $sala) {
    $numSala = $sala['NUM_SALA'];
    if (strpos($numSala, 'T') === 0) {
        $andar = 'Térreo';
    } else if (strpos($numSala, '1') === 0) {
        $andar = '1º Andar';
    } else if (strpos($numSala, '2') === 0) {
        $andar = '2º Andar';
    } else {
        $andar = 'Outros';
    }

    if (!isset($salasPorAndar[$andar])) {
        $salasPorAndar[$andar] = [];
    }
    $salasPorAndar[$andar][] = $sala;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $andar = filter_input(INPUT_POST, 'andar', FILTER_SANITIZE_SPECIAL_CHARS);
    $numeroSala = filter_input(INPUT_POST, 'numero_sala', FILTER_VALIDATE_INT);
    $qtdPessoas = filter_input(INPUT_POST, 'qtd_pessoas', FILTER_VALIDATE_INT);
    $tipoSala = filter_input(INPUT_POST, 'tipo_sala', FILTER_SANITIZE_SPECIAL_CHARS);
    $setorResponsavel = filter_input(INPUT_POST, 'setor_responsavel', FILTER_SANITIZE_SPECIAL_CHARS);

    // Formar o número da sala
    $prefixo = '';
    switch($andar) {
        case 'terreo': $prefixo = 'T'; break;
        case '1': $prefixo = '1'; break;
        case '2': $prefixo = '2'; break;
    }

    $numSala = $prefixo . $numeroSala;

    // Montar descrição com TODOS os recursos
    $recursos = [];

    if (isset($_POST['ar_condicionado'])) $recursos[] = "Ar-condicionado";
    if (isset($_POST['projetor'])) $recursos[] = "Projetor";
    if (isset($_POST['tv'])) {
        $qtd = isset($_POST['qtd_tv']) ? (int)$_POST['qtd_tv'] : 1;
        $recursos[] = $qtd > 1 ? "$qtd TVs" : "TV";
    }
    if (isset($_POST['computadores'])) {
        $qtd = isset($_POST['qtd_computadores']) ? (int)$_POST['qtd_computadores'] : 1;
        $recursos[] = $qtd > 1 ? "$qtd Computadores" : "Computador";
    }
    if (isset($_POST['quadro_branco'])) $recursos[] = "Quadro branco";
    if (isset($_POST['som'])) $recursos[] = "Sistema de som";
    if (isset($_POST['wifi'])) $recursos[] = "Wi-Fi";
    if (isset($_POST['webcam'])) $recursos[] = "Webcam";
    if (isset($_POST['microfone'])) $recursos[] = "Microfone";
    if (isset($_POST['mesa_reuniao'])) $recursos[] = "Mesa de reunião";
    if (isset($_POST['lousa_digital'])) $recursos[] = "Lousa digital";
    if (isset($_POST['impressora'])) $recursos[] = "Impressora";
    if (isset($_POST['telefone'])) $recursos[] = "Telefone";
    if (isset($_POST['cadeiras'])) {
        $qtd = isset($_POST['qtd_cadeiras']) ? (int)$_POST['qtd_cadeiras'] : $qtdPessoas;
        $recursos[] = "$qtd Cadeiras";
    }

    $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS);

    // Montar localização
    $andarNome = ['terreo' => 'Térreo', '1' => '1º Andar', '2' => '2º Andar'][$andar] ?? '';

    $descricao = "Localização: " . $andarNome . " - Sala " . $numeroSala;
    $descricao .= ". Recursos: " . (count($recursos) > 0 ? implode(', ', $recursos) : 'Nenhum recurso adicional');

    if (!empty($observacoes)) {
        $descricao .= ". Observações: " . $observacoes;
    }

    if (empty($andar) || empty($numeroSala) || empty($qtdPessoas) || empty($tipoSala)) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } else if ($numeroSala <= 0) {
        $error = 'O número da sala deve ser maior que zero.';
    } else if ($qtdPessoas <= 0) {
        $error = 'A capacidade da sala deve ser maior que zero.';
    } else {
        $sala = new Sala();
        $sala->setNumSala($numSala);
        $sala->setQtdPessoas($qtdPessoas);
        $sala->setDescricao($descricao);
        $sala->setTipoSala($tipoSala);
        $sala->setSetorResponsavel($setorResponsavel ?? '');

        if ($sala->cadastrar()) {
            $success = 'Sala cadastrada com sucesso!';
            header('Refresh: 2');
        } else {
            $error = 'Erro ao cadastrar sala. Verifique se o número da sala já está em uso.';
        }
    }
}

$pageTitle = 'Nova Sala';
include '../includes/header.php';
?>

<div class="page-header">
    <h2>Cadastrar Nova Sala</h2>
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
                <label class="form-label">Andar *</label>
                <select name="andar" class="form-select" required>
                    <option value="">Selecione o andar</option>
                    <option value="terreo">Térreo</option>
                    <option value="1">1º Andar</option>
                    <option value="2">2º Andar</option>
                </select>
            </div>
            <div>
                <label class="form-label">Número da Sala *</label>
                <input type="number" name="numero_sala" class="form-control" required min="1" placeholder="Ex: 1 (para sala T1 no térreo)">
            </div>
        </div>

        <div class="form-row">
            <div>
                <label class="form-label">Tipo de Sala *</label>
                <select name="tipo_sala" class="form-select" required>
                    <option value="">Selecione o tipo</option>
                    <option value="Teatro">Teatro</option>
                    <option value="Apresentação">Apresentação</option>
                    <option value="reuniao">Reunião</option>
                    <option value="auditorio">Auditório</option>
                    <option value="outro">Outro</option>
                </select>
            </div>
            <div>
                <label class="form-label">Capacidade (pessoas) *</label>
                <input type="number" name="qtd_pessoas" id="qtd_pessoas" class="form-control" required min="1">
            </div>
        </div>

        <div class="form-row">
            <div>
                <label class="form-label">Setor Responsável</label>
                <select name="setor_responsavel" class="form-select">
                    <option value="">Selecione o setor</option>
                    <option value="academico">Acadêmico</option>
                    <option value="financeiro">Financeiro</option>
                    <option value="secretaria">Secretaria</option>
                    <option value="ti">Tecnologia da Informação</option>
                    <option value="rh">Recursos Humanos</option>
                    <option value="marketing">Marketing</option>
                    <option value="coordenacao">Coordenação</option>
                    <option value="diretoria">Diretoria</option>
                </select>
            </div>
            <div>
                <label class="form-label">Observações</label>
                <input type="text" name="observacoes" class="form-control" placeholder="Informações adicionais sobre a sala...">
            </div>
        </div>

        <hr style="border-color: var(--border-color); margin: 1.5rem 0;">
        <p class="form-label" style="margin-bottom: 1rem;"><i class="fas fa-tools me-2"></i>Recursos e Equipamentos</p>

        <div class="form-row">
            <div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="ar_condicionado" name="ar_condicionado">
                    <label class="form-check-label" for="ar_condicionado">Ar-condicionado</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="projetor" name="projetor">
                    <label class="form-check-label" for="projetor">Projetor</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="quadro_branco" name="quadro_branco">
                    <label class="form-check-label" for="quadro_branco">Quadro branco</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="som" name="som">
                    <label class="form-check-label" for="som">Sistema de som</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="wifi" name="wifi">
                    <label class="form-check-label" for="wifi">Wi-Fi</label>
                </div>
            </div>
            <div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="webcam" name="webcam">
                    <label class="form-check-label" for="webcam">Webcam</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="microfone" name="microfone">
                    <label class="form-check-label" for="microfone">Microfone</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="mesa_reuniao" name="mesa_reuniao">
                    <label class="form-check-label" for="mesa_reuniao">Mesa de reunião</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="lousa_digital" name="lousa_digital">
                    <label class="form-check-label" for="lousa_digital">Lousa digital</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="impressora" name="impressora">
                    <label class="form-check-label" for="impressora">Impressora</label>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="tv" name="tv">
                    <label class="form-check-label" for="tv">TV</label>
                </div>
                <input type="number" class="form-control" name="qtd_tv" placeholder="Quantidade de TVs" min="1" value="1">
            </div>
            <div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="computadores" name="computadores">
                    <label class="form-check-label" for="computadores">Computadores</label>
                </div>
                <input type="number" class="form-control" name="qtd_computadores" placeholder="Quantidade de computadores" min="1" value="1">
            </div>
        </div>

        <div class="form-row">
            <div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="cadeiras" name="cadeiras">
                    <label class="form-check-label" for="cadeiras">Cadeiras</label>
                </div>
                <input type="number" class="form-control" id="qtd_cadeiras" name="qtd_cadeiras" placeholder="Quantidade de cadeiras" min="1">
            </div>
            <div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="telefone" name="telefone">
                    <label class="form-check-label" for="telefone">Telefone</label>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="salas.php" class="btn-gray">Cancelar</a>
            <button type="submit" class="btn-gold"><i class="fas fa-save me-2"></i>Salvar</button>
        </div>
    </form>
</div>

<script>
document.getElementById('qtd_pessoas').addEventListener('change', function() {
    const qtdCadeiras = document.getElementById('qtd_cadeiras');
    if (!qtdCadeiras.value) {
        qtdCadeiras.value = this.value;
    }
});
</script>

<?php include '../includes/footer.php'; ?>
