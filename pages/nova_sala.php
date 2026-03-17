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

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Cadastrar Nova Sala</h2>
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
    </div>
</div>

<!-- Card com salas já cadastradas -->
<?php if (count($salasExistentes) > 0): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Salas Já Cadastradas (<?php echo count($salasExistentes); ?>)
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($salasPorAndar as $andar => $salas): ?>
                    <div class="col-md-4 mb-3">
                        <h6 class="text-info border-bottom pb-2">
                            <i class="fas fa-building me-2"></i><?php echo $andar; ?> (<?php echo count($salas); ?>)
                        </h6>
                        <?php foreach ($salas as $sala): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                            <div>
                                <strong class="text-primary">Sala <?php echo $sala['NUM_SALA']; ?></strong>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-users"></i> <?php echo $sala['QTD_PESSOAS']; ?> pessoas
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Formulário -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Dados da Sala</h5>
    </div>
    <div class="card-body">
        <form method="post" class="needs-validation" novalidate>
            <!-- Informações Básicas -->
            <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Informações Básicas</h6>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="andar" class="form-label">Andar *</label>
                    <select class="form-select" id="andar" name="andar" required>
                        <option value="">Selecione o andar</option>
                        <option value="terreo">Térreo</option>
                        <option value="1">1º Andar</option>
                        <option value="2">2º Andar</option>
                    </select>
                    <div class="invalid-feedback">Selecione o andar.</div>
                </div>
                
                <div class="col-md-4">
                    <label for="numero_sala" class="form-label">Número da Sala *</label>
                    <input type="number" class="form-control" id="numero_sala" name="numero_sala" required min="1">
                    <small class="text-muted">Ex: para sala T1, digite apenas 1</small>
                    <div class="invalid-feedback">Informe o número da sala.</div>
                </div>
                
                <div class="col-md-4">
                    <label for="qtd_pessoas" class="form-label">Capacidade *</label>
                    <input type="number" class="form-control" id="qtd_pessoas" name="qtd_pessoas" required min="1">
                    <small class="text-muted">Número de pessoas</small>
                    <div class="invalid-feedback">Informe a capacidade.</div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="tipo_sala" class="form-label">Tipo de Sala *</label>
                    <select class="form-select" id="tipo_sala" name="tipo_sala" required>
                        <option value="">Selecione o tipo</option>
                        <option value="aula">Sala de Aula</option>
                        <option value="laboratorio">Laboratório</option>
                        <option value="reuniao">Sala de Reunião</option>
                        <option value="auditorio">Auditório</option>
                    </select>
                    <div class="invalid-feedback">Selecione o tipo de sala.</div>
                </div>
                
                <div class="col-md-6">
                    <label for="setor_responsavel" class="form-label">Setor Responsável</label>
                    <select class="form-select" id="setor_responsavel" name="setor_responsavel">
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
            </div>
            
            <hr>
            
            <!-- Recursos e Equipamentos -->
            <h6 class="text-primary mb-3"><i class="fas fa-tools me-2"></i>Recursos e Equipamentos</h6>
            
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="ar_condicionado" name="ar_condicionado">
                        <label class="form-check-label" for="ar_condicionado">
                            <i class="fas fa-snowflake text-info"></i> Ar-condicionado
                        </label>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="projetor" name="projetor">
                        <label class="form-check-label" for="projetor">
                            <i class="fas fa-video text-primary"></i> Projetor
                        </label>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="quadro_branco" name="quadro_branco">
                        <label class="form-check-label" for="quadro_branco">
                            <i class="fas fa-chalkboard text-secondary"></i> Quadro branco
                        </label>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="som" name="som">
                        <label class="form-check-label" for="som">
                            <i class="fas fa-volume-up text-warning"></i> Sistema de som
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="wifi" name="wifi">
                        <label class="form-check-label" for="wifi">
                            <i class="fas fa-wifi text-success"></i> Wi-Fi
                        </label>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="webcam" name="webcam">
                        <label class="form-check-label" for="webcam">
                            <i class="fas fa-camera text-danger"></i> Webcam
                        </label>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="microfone" name="microfone">
                        <label class="form-check-label" for="microfone">
                            <i class="fas fa-microphone text-info"></i> Microfone
                        </label>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="mesa_reuniao" name="mesa_reuniao">
                        <label class="form-check-label" for="mesa_reuniao">
                            <i class="fas fa-table text-primary"></i> Mesa de reunião
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="lousa_digital" name="lousa_digital">
                        <label class="form-check-label" for="lousa_digital">
                            <i class="fas fa-chalkboard-teacher text-success"></i> Lousa digital
                        </label>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="impressora" name="impressora">
                        <label class="form-check-label" for="impressora">
                            <i class="fas fa-print text-secondary"></i> Impressora
                        </label>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="telefone" name="telefone">
                        <label class="form-check-label" for="telefone">
                            <i class="fas fa-phone text-warning"></i> Telefone
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="tv" name="tv">
                        <label class="form-check-label" for="tv">
                            <i class="fas fa-tv text-danger"></i> TV
                        </label>
                    </div>
                    <input type="number" class="form-control form-control-sm" id="qtd_tv" name="qtd_tv" placeholder="Quantidade" min="1" value="1">
                </div>
                
                <div class="col-md-4">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="computadores" name="computadores">
                        <label class="form-check-label" for="computadores">
                            <i class="fas fa-desktop text-primary"></i> Computadores
                        </label>
                    </div>
                    <input type="number" class="form-control form-control-sm" id="qtd_computadores" name="qtd_computadores" placeholder="Quantidade" min="1" value="1">
                </div>
                
                <div class="col-md-4">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="cadeiras" name="cadeiras">
                        <label class="form-check-label" for="cadeiras">
                            <i class="fas fa-chair text-secondary"></i> Cadeiras
                        </label>
                    </div>
                    <input type="number" class="form-control form-control-sm" id="qtd_cadeiras" name="qtd_cadeiras" placeholder="Quantidade" min="1">
                </div>
            </div>
            
            <hr>
            
            <!-- Observações -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="observacoes" class="form-label">Observações Adicionais</label>
                    <textarea class="form-control" id="observacoes" name="observacoes" rows="3" placeholder="Informações adicionais sobre a sala..."></textarea>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i>Cadastrar Sala
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Validação do formulário
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

// Sincronizar quantidade de cadeiras com capacidade
document.getElementById('qtd_pessoas').addEventListener('change', function() {
    const qtdCadeiras = document.getElementById('qtd_cadeiras');
    if (!qtdCadeiras.value) {
        qtdCadeiras.value = this.value;
    }
});
</script>

<?php include '../includes/footer.php'; ?>

