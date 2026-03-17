<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Usuario.php';
require_once __DIR__ . '/../includes/alert.php';

requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'] ?? '';
    $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);
    $curso = filter_input(INPUT_POST, 'curso', FILTER_SANITIZE_SPECIAL_CHARS);
    $outro_curso = filter_input(INPUT_POST, 'outro_curso', FILTER_SANITIZE_SPECIAL_CHARS);
    $tipoUsuario = filter_input(INPUT_POST, 'tipo_usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $setor = 'academico'; // SEMPRE ACADÊMICO
    
    // Se selecionou "Outro", usar o curso digitado
    if ($curso === 'Outro' && !empty($outro_curso)) {
        $curso = $outro_curso;
    }
    
    if (empty($nome) || empty($email) || empty($senha) || empty($cpf) || empty($curso)) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } else if ($curso === 'Outro' && empty($outro_curso)) {
        $error = 'Por favor, especifique o nome do curso.';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido.';
    } else if (strlen($cpf) != 11) {
        $error = 'CPF inválido.';
    } else if (strlen($senha) < 6) {
        $error = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        $usuario = new Usuario();
        $usuario->setNome($nome);
        $usuario->setEmail($email);
        $usuario->setSenha($senha);
        $usuario->setCpf($cpf);
        $usuario->setCurso($curso);
        $usuario->setTipoUsuario($tipoUsuario);
        $usuario->setSetor($setor ?? '');
        
        if ($usuario->cadastrar()) {
            $success = 'Usuário cadastrado com sucesso!';
            $nome = $email = $cpf = $curso = $tipoUsuario = '';
        } else {
            $error = 'Erro ao cadastrar usuário. Verifique se o e-mail já está em uso.';
        }
    }
}

$pageTitle = 'Novo Usuário';
include __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Cadastrar Novo Usuário</h2>
            <a href="usuarios.php" class="btn btn-secondary">
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
                <h5 class="card-title mb-0">Formulário de Cadastro</h5>
            </div>
            <div class="card-body">
                <form method="post" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nome" class="form-label">Nome Completo *</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $nome ?? ''; ?>" required>
                            <div class="invalid-feedback">
                                Por favor, informe o nome completo.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $email ?? ''; ?>" required>
                            <div class="invalid-feedback">
                                Por favor, informe um email válido.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="senha" class="form-label">Senha *</label>
                            <input type="password" class="form-control" id="senha" name="senha" required minlength="6">
                            <small class="text-muted">Mínimo 6 caracteres</small>
                            <div class="invalid-feedback">
                                A senha deve ter pelo menos 6 caracteres.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="cpf" class="form-label">CPF *</label>
                            <input type="text" class="form-control" id="cpf" name="cpf" value="<?php echo $cpf ?? ''; ?>" required maxlength="11">
                            <small class="text-muted">Apenas números</small>
                            <div class="invalid-feedback">
                                Por favor, informe o CPF.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="curso" class="form-label">Curso *</label>
                            <select class="form-select" id="curso" name="curso" required>
                                <option value="">Selecione o curso</option>
                                <option value="Administração">Administração</option>
                                <option value="Análise e Desenvolvimento de Sistemas">Análise e Desenvolvimento de Sistemas</option>
                                <option value="Ciência da Computação">Ciência da Computação</option>
                                <option value="Ciências Contábeis">Ciências Contábeis</option>
                                <option value="Direito">Direito</option>
                                <option value="Educação Física">Educação Física</option>
                                <option value="Enfermagem">Enfermagem</option>
                                <option value="Engenharia Civil">Engenharia Civil</option>
                                <option value="Engenharia de Produção">Engenharia de Produção</option>
                                <option value="Engenharia Elétrica">Engenharia Elétrica</option>
                                <option value="Engenharia Mecânica">Engenharia Mecânica</option>
                                <option value="Fisioterapia">Fisioterapia</option>
                                <option value="Gestão Comercial">Gestão Comercial</option>
                                <option value="Gestão de Recursos Humanos">Gestão de Recursos Humanos</option>
                                <option value="Gestão Financeira">Gestão Financeira</option>
                                <option value="Logística">Logística</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Nutrição">Nutrição</option>
                                <option value="Pedagogia">Pedagogia</option>
                                <option value="Psicologia">Psicologia</option>
                                <option value="Publicidade e Propaganda">Publicidade e Propaganda</option>
                                <option value="Redes de Computadores">Redes de Computadores</option>
                                <option value="Sistemas de Informação">Sistemas de Informação</option>
                                <option value="Outro">Outro (especifique abaixo)</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor, selecione o curso.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="tipo_usuario" class="form-label">Tipo de Usuário *</label>
                            <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                                <option value="">Selecione o tipo</option>
                                <option value="comum">Usuário Comum</option>
                                <option value="admin">Administrador</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor, selecione o tipo de usuário.
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campo para curso personalizado -->
                    <div class="row mb-3" id="outro_curso_div" style="display: none;">
                        <div class="col-md-12">
                            <label for="outro_curso" class="form-label">Especifique o Curso *</label>
                            <input type="text" class="form-control" id="outro_curso" name="outro_curso" placeholder="Digite o nome do curso">
                            <small class="text-muted">Este campo é obrigatório quando "Outro" é selecionado</small>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Importante:</strong> 
                        <ul class="mb-0 mt-2">
                            <li><strong>Administrador:</strong> Acesso total ao sistema</li>
                            <li><strong>Usuário comum:</strong> Apenas visualiza suas próprias reservas</li>
                            <li>Todos os usuários são cadastrados no setor <strong>Acadêmico</strong> por padrão</li>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Cadastrar Usuário
                        </button>
                    </div>
                </form>
            </div>
        </div>
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

// Máscara de CPF
document.getElementById('cpf').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    e.target.value = value;
});

// Mostrar/Ocultar campo "Outro Curso"
document.getElementById('curso').addEventListener('change', function() {
    const outroCursoDiv = document.getElementById('outro_curso_div');
    const outroCursoInput = document.getElementById('outro_curso');
    
    if (this.value === 'Outro') {
        outroCursoDiv.style.display = 'block';
        outroCursoInput.required = true;
    } else {
        outroCursoDiv.style.display = 'none';
        outroCursoInput.required = false;
        outroCursoInput.value = '';
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>