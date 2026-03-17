<?php
/**
 * DASHBOARD DE TESTES - SALA CERTA
 * VERSÃO COMPLETA E CORRIGIDA
 * Com todos os gráficos funcionais, output técnico e histórico
 */

error_reporting(0);
ini_set('display_errors', '0');

$projectRoot = realpath(__DIR__ . '/../../');

$configFile = $projectRoot . '/config/config.php';
$conexaoFile = $projectRoot . '/config/conexao.php';

if (file_exists($configFile)) {
    require_once $configFile;
}
if (file_exists($conexaoFile)) {
    require_once $conexaoFile;
}

if (function_exists('requireLogin')) {
    requireLogin();
}
if (function_exists('requireAdmin')) {
    requireAdmin();
}

// Carregar sistema de histórico
require_once __DIR__ . '/historico_testes.php';
$historicoManager = new HistoricoTestes();

// Ler resultados (INCLUINDO COBERTURA)
$testResultsFile = __DIR__ . '/reports/test-results.json';
$lastResults = null;
$cobertura = null;

if (file_exists($testResultsFile)) {
    $content = @file_get_contents($testResultsFile);
    if ($content) {
        $lastResults = json_decode($content, true);
        if (isset($lastResults['cobertura'])) {
            $cobertura = $lastResults['cobertura'];
        }
    }
}

// Carregar comparativo
$comparativo = $historicoManager->gerarComparativo();

// Calcular total de testes implementados
$totalTestesReais = 0;
if ($cobertura && isset($cobertura['detalhes'])) {
    foreach ($cobertura['detalhes'] as $detalhe) {
        $totalTestesReais += $detalhe['num_testes'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Testes - Sala Certa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
:root {
    --primary: #37D0C0;
    --primary-dark: #2bb8a8;
    --success: #28a745;
    --danger: #dc3545;
    --warning: #ffc107;
    --info: #17a2b8;
    --bg-light: #F5FBFF;
    --text-dark: #2c3e50;
    --text-light: #7f8c8d;
    --border-color: #e1e8ed;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Inter', sans-serif; background: var(--bg-light); color: var(--text-dark); padding: 20px; }
.main-container { max-width: 1400px; margin: 0 auto; }
.header-dashboard { background: white; border-radius: 20px; padding: 40px; margin-bottom: 30px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); text-align: center; }
.header-dashboard h1 { font-size: 2.5em; color: var(--text-dark); margin-bottom: 10px; }
.header-dashboard p { color: var(--text-light); font-size: 1.1em; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
.stat-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); transition: transform 0.3s, box-shadow 0.3s; border-left: 4px solid var(--primary); }
.stat-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.15); }
.stat-card.success { border-left-color: var(--success); }
.stat-card.danger { border-left-color: var(--danger); }
.stat-card.warning { border-left-color: var(--warning); }
.stat-card.info { border-left-color: var(--info); }
.stat-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.stat-card-title { font-size: 0.9em; color: var(--text-light); text-transform: uppercase; font-weight: 600; }
.stat-card-icon { font-size: 1.5em; opacity: 0.3; }
.stat-card-value { font-size: 2.5em; font-weight: 700; color: var(--text-dark); }
.stat-card-footer { margin-top: 10px; font-size: 0.85em; color: var(--text-light); }
.controls { background: white; border-radius: 15px; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.btn-test { padding: 12px 30px; border-radius: 10px; font-weight: 600; border: none; cursor: pointer; transition: all 0.3s; margin: 5px; font-size: 1em; }
.btn-test-primary { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; }
.btn-test-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(55, 208, 192, 0.3); }
.btn-test-secondary { background: #6c757d; color: white; }
.btn-test-info { background: var(--info); color: white; }
.btn-test-success { background: var(--success); color: white; }
.coverage-section { background: white; border-radius: 15px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.coverage-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.coverage-header h3 { margin: 0; color: var(--text-dark); }
.coverage-badge { font-size: 1.5em; font-weight: 700; padding: 10px 20px; border-radius: 10px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; }
.coverage-bar-container { position: relative; height: 40px; background: #f0f0f0; border-radius: 10px; overflow: hidden; margin-bottom: 30px; }
.coverage-bar { height: 100%; background: linear-gradient(90deg, var(--success), var(--primary)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.1em; transition: width 1s ease; }
.coverage-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
.coverage-item { background: var(--bg-light); padding: 15px; border-radius: 10px; border-left: 3px solid var(--primary); }
.coverage-item.tested { border-left-color: var(--success); }
.coverage-item.not-tested { border-left-color: var(--danger); }
.coverage-item-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.coverage-item-name { font-weight: 600; color: var(--text-dark); }
.coverage-item-status { padding: 3px 10px; border-radius: 20px; font-size: 0.75em; font-weight: 600; }
.coverage-item-status.success { background: rgba(40, 167, 69, 0.1); color: var(--success); }
.coverage-item-status.danger { background: rgba(220, 53, 69, 0.1); color: var(--danger); }
.coverage-item-tests { font-size: 0.85em; color: var(--text-light); }
.test-suites { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-bottom: 30px; }
.suite-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.suite-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid var(--border-color); }
.suite-name { font-size: 1.3em; font-weight: 700; color: var(--text-dark); }
.suite-badge { padding: 5px 15px; border-radius: 20px; font-size: 0.85em; font-weight: 600; }
.suite-badge.success { background: rgba(40, 167, 69, 0.1); color: var(--success); }
.suite-badge.danger { background: rgba(220, 53, 69, 0.1); color: var(--danger); }
.suite-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px; }
.suite-stat { text-align: center; padding: 10px; background: var(--bg-light); border-radius: 8px; }
.suite-stat-value { font-size: 1.8em; font-weight: 700; color: var(--text-dark); }
.suite-stat-label { font-size: 0.85em; color: var(--text-light); margin-top: 5px; }
.progress { height: 10px; border-radius: 10px; margin-top: 15px; }
.test-details { margin-top: 20px; max-height: 400px; overflow-y: auto; }
.test-details::-webkit-scrollbar { width: 8px; }
.test-details::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
.test-details::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
.test-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; margin: 5px 0; background: var(--bg-light); border-radius: 8px; border-left: 3px solid transparent; transition: all 0.2s; font-size: 0.9em; }
.test-item:hover { transform: translateX(5px); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.test-item.passed { border-left-color: var(--success); }
.test-item.failed { border-left-color: var(--danger); }
.test-item-name { flex: 1; color: var(--text-dark); }
.test-item-time { font-size: 0.85em; color: var(--text-light); margin: 0 15px; }
.test-item-assertions { font-size: 0.85em; color: var(--info); background: rgba(23, 162, 184, 0.1); padding: 3px 10px; border-radius: 15px; }
.test-item-icon { font-size: 1.1em; margin-right: 10px; }
.charts-section { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 30px; }
.chart-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.chart-card h3 { margin-bottom: 20px; color: var(--text-dark); font-size: 1.2em; }
.terminal-output { background: #1e1e1e; color: #d4d4d4; border-radius: 15px; padding: 25px; font-family: 'Courier New', monospace; font-size: 0.9em; max-height: 500px; overflow-y: auto; box-shadow: 0 2px 10px rgba(0,0,0,0.15); white-space: pre-wrap; word-wrap: break-word; margin-top: 30px; }
.terminal-output::-webkit-scrollbar { width: 10px; }
.terminal-output::-webkit-scrollbar-track { background: #2d2d2d; border-radius: 10px; }
.terminal-output::-webkit-scrollbar-thumb { background: #555; border-radius: 10px; }
.loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); display: none; align-items: center; justify-content: center; z-index: 9999; }
.loading-content { background: white; padding: 40px; border-radius: 20px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
.spinner { border: 4px solid #f3f3f3; border-top: 4px solid var(--primary); border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto 20px; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
.alert-custom { border-radius: 15px; padding: 20px; margin-bottom: 20px; border-left: 4px solid; }
.alert-success { background: rgba(40, 167, 69, 0.1); border-left-color: var(--success); color: #155724; }
.alert-warning { background: rgba(255, 193, 7, 0.1); border-left-color: var(--warning); color: #856404; }
.alert-danger { background: rgba(220, 53, 69, 0.1); border-left-color: var(--danger); color: #721c24; }
.alert-info { background: rgba(23, 162, 184, 0.1); border-left-color: var(--info); color: #0c5460; }
.evolution-section { background: white; border-radius: 15px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
@media (max-width: 768px) {
    .stats-grid, .test-suites, .charts-section, .coverage-details { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
    <div class="main-container">
        <div class="header-dashboard">
            <h1><i class="fas fa-flask"></i> Dashboard de Testes</h1>
            <p>Sistema de Testes Automatizados - Sala Certa Admin</p>
        </div>

        <!-- Controles -->
        <div class="controls">
            <h5><i class="fas fa-sliders-h"></i> Controles</h5>
            <div style="margin-top: 15px;">
                <button onclick="executarTestes()" class="btn-test btn-test-primary" id="btnExecutar">
                    <i class="fas fa-play-circle"></i> Executar Testes
                </button>
                <button onclick="location.reload()" class="btn-test btn-test-secondary">
                    <i class="fas fa-sync"></i> Atualizar
                </button>
                <button onclick="testarExecutor()" class="btn-test btn-test-info">
                    <i class="fas fa-stethoscope"></i> Diagnóstico
                </button>
                <button onclick="toggleOutput()" class="btn-test btn-test-success">
                    <i class="fas fa-terminal"></i> Ver Output
                </button>
                <button onclick="mostrarUltimoResultado()" class="btn-test btn-test-warning" style="background: #ffc107; color: #212529;">
                    <i class="fas fa-file-alt"></i> Ver Resultado
                </button>
                </div>
        </div>
        </div>

        <?php if ($lastResults && $lastResults['totalTests'] > 0): ?>
        
        <!-- Alertas -->
        <?php if ($lastResults['failed'] == 0): ?>
        <div class="alert-custom alert-success">
            <strong><i class="fas fa-check-circle"></i> Sucesso Total!</strong>
            <p class="mb-0" style="margin-top: 8px;">Todos os <?php echo $lastResults['totalTests']; ?> testes passaram! ✨</p>
        </div>
        <?php else: ?>
        <div class="alert-custom alert-danger">
            <strong><i class="fas fa-times-circle"></i> Falhas Detectadas!</strong>
            <p class="mb-0" style="margin-top: 8px;"><?php echo $lastResults['failed']; ?> teste(s) falharam.</p>
        </div>
        <?php endif; ?>

        <!-- Comparativo de Evolução -->
        <?php if ($comparativo['disponivel']): ?>
        <div class="evolution-section">
            <h3 style="margin-bottom: 20px; color: var(--text-dark);">
                <i class="fas fa-chart-line"></i> Evolução e Comparativo
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px;">
                <!-- Execução Anterior -->
                <div style="background: var(--bg-light); padding: 20px; border-radius: 10px;">
                    <h4 style="margin-bottom: 15px; color: var(--text-light);">
                        <i class="fas fa-history"></i> Execução Anterior
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; font-size: 0.9em;">
                        <div><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($comparativo['anterior']['timestamp'])); ?></div>
                        <div><strong>Testes:</strong> <?php echo $comparativo['anterior']['totalTests']; ?></div>
                        <div><strong>Passou:</strong> <span style="color: var(--success);"><?php echo $comparativo['anterior']['passed']; ?></span></div>
                        <div><strong>Falhou:</strong> <span style="color: var(--danger);"><?php echo $comparativo['anterior']['failed']; ?></span></div>
                        <div><strong>Tempo:</strong> <?php echo $comparativo['anterior']['time']; ?>s</div>
                        <div><strong>Taxa:</strong> <?php echo $comparativo['anterior']['percentage']; ?>%</div>
                    </div>
                </div>
                
                <!-- Execução Atual -->
                <div style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); padding: 20px; border-radius: 10px; color: white;">
                    <h4 style="margin-bottom: 15px;">
                        <i class="fas fa-clock"></i> Execução Atual
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; font-size: 0.9em;">
                        <div><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($comparativo['atual']['timestamp'])); ?></div>
                        <div><strong>Testes:</strong> <?php echo $comparativo['atual']['totalTests']; ?></div>
                        <div><strong>Passou:</strong> <?php echo $comparativo['atual']['passed']; ?></div>
                        <div><strong>Falhou:</strong> <?php echo $comparativo['atual']['failed']; ?></div>
                        <div><strong>Tempo:</strong> <?php echo $comparativo['atual']['time']; ?>s</div>
                        <div><strong>Taxa:</strong> <?php echo $comparativo['atual']['percentage']; ?>%</div>
                    </div>
                </div>
            </div>
            
            <!-- Indicadores de Melhoria -->
            <div style="background: var(--bg-light); padding: 20px; border-radius: 10px;">
                <h4 style="margin-bottom: 15px; color: var(--text-dark);">
                    <i class="fas fa-trophy"></i> Indicadores de Progresso
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <!-- Falhas -->
                    <div style="padding: 15px; background: white; border-radius: 8px; border-left: 4px solid <?php echo $comparativo['melhorias']['menos_falhas'] ? 'var(--success)' : 'var(--warning)'; ?>;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600;">Falhas</span>
                            <span style="font-size: 1.5em;">
                                <?php if ($comparativo['melhorias']['menos_falhas']): ?>
                                    <i class="fas fa-arrow-down" style="color: var(--success);"></i>
                                <?php else: ?>
                                    <i class="fas fa-arrow-up" style="color: var(--warning);"></i>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div style="margin-top: 10px; font-size: 1.2em; font-weight: 700;">
                            <?php 
                            $diff = $comparativo['diferencas']['falhou'];
                            echo ($diff > 0 ? '+' : '') . $diff; 
                            ?>
                        </div>
                    </div>
                    
                    <!-- Tempo -->
                    <div style="padding: 15px; background: white; border-radius: 8px; border-left: 4px solid <?php echo $comparativo['melhorias']['mais_rapido'] ? 'var(--success)' : 'var(--warning)'; ?>;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600;">Tempo</span>
                            <span style="font-size: 1.5em;">
                                <?php if ($comparativo['melhorias']['mais_rapido']): ?>
                                    <i class="fas fa-arrow-down" style="color: var(--success);"></i>
                                <?php else: ?>
                                    <i class="fas fa-arrow-up" style="color: var(--warning);"></i>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div style="margin-top: 10px; font-size: 1.2em; font-weight: 700;">
                            <?php 
                            $diff = $comparativo['diferencas']['tempo'];
                            echo ($diff > 0 ? '+' : '') . $diff . 's'; 
                            ?>
                        </div>
                    </div>
                    
                    <!-- Cobertura -->
                    <div style="padding: 15px; background: white; border-radius: 8px; border-left: 4px solid <?php echo $comparativo['melhorias']['melhor_cobertura'] ? 'var(--success)' : 'var(--warning)'; ?>;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600;">Cobertura</span>
                            <span style="font-size: 1.5em;">
                                <?php if ($comparativo['melhorias']['melhor_cobertura']): ?>
                                    <i class="fas fa-arrow-up" style="color: var(--success);"></i>
                                <?php else: ?>
                                    <i class="fas fa-minus" style="color: var(--warning);"></i>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div style="margin-top: 10px; font-size: 1.2em; font-weight: 700;">
                            <?php 
                            $diff = $comparativo['diferencas']['cobertura'];
                            echo ($diff > 0 ? '+' : '') . $diff . '%'; 
                            ?>
                        </div>
                    </div>
                    
                    <!-- Taxa de Sucesso -->
                    <div style="padding: 15px; background: white; border-radius: 8px; border-left: 4px solid <?php echo $comparativo['melhorias']['melhor_taxa'] ? 'var(--success)' : 'var(--warning)'; ?>;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600;">Taxa Sucesso</span>
                            <span style="font-size: 1.5em;">
                                <?php if ($comparativo['melhorias']['melhor_taxa']): ?>
                                    <i class="fas fa-arrow-up" style="color: var(--success);"></i>
                                <?php else: ?>
                                    <i class="fas fa-arrow-down" style="color: var(--warning);"></i>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div style="margin-top: 10px; font-size: 1.2em; font-weight: 700;">
                            <?php 
                            $diff = $comparativo['diferencas']['percentage'];
                            echo ($diff > 0 ? '+' : '') . $diff . '%'; 
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cobertura -->
        <?php if ($cobertura): ?>
        <div class="coverage-section">
            <div class="coverage-header">
                <h3><i class="fas fa-chart-line"></i> Cobertura de Testes do Projeto</h3>
                <div class="coverage-badge">
                    <?php echo $cobertura['porcentagem']; ?>%
                </div>
            </div>
            
            <div class="coverage-bar-container">
                <div class="coverage-bar" style="width: <?php echo $cobertura['porcentagem']; ?>%;">
                    <?php echo $cobertura['classes_com_testes']; ?> de <?php echo $cobertura['total_classes']; ?> classes testadas
                </div>
            </div>

            <div style="text-align: center; margin-bottom: 20px; color: var(--text-light);">
                <p>
                    <i class="fas fa-info-circle"></i>
                    <?php 
                    $faltam = $cobertura['total_classes'] - $cobertura['classes_com_testes'];
                    if ($faltam == 0) {
                        echo "🎉 Todas as classes possuem testes!";
                    } else {
                        echo "Faltam testes para " . $faltam . " classe(s)";
                    }
                    ?>
                    • Total de <?php echo $totalTestesReais; ?> testes implementados
                </p>
            </div>

            <h4 style="margin-bottom: 15px; color: var(--text-dark);">
                <i class="fas fa-layer-group"></i> Detalhamento por Camada
            </h4>
            <div class="coverage-details">
                <?php foreach ($cobertura['detalhes'] as $className => $info): ?>
                <div class="coverage-item <?php echo $info['tem_teste'] ? 'tested' : 'not-tested'; ?>">
                    <div class="coverage-item-header">
                        <span class="coverage-item-name"><?php echo $className; ?></span>
                        <span class="coverage-item-status <?php echo $info['tem_teste'] ? 'success' : 'danger'; ?>">
                            <?php echo $info['tem_teste'] ? '✓ Testada' : '✗ Sem testes'; ?>
                        </span>
                    </div>
                    <div class="coverage-item-tests">
                        <i class="fas fa-vial"></i> 
                        <?php 
                        if ($info['tem_teste']) {
                            echo $info['num_testes'] . " teste(s) implementado(s)";
                        } else {
                            echo "Nenhum teste encontrado";
                        }
                        ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card success">
                <div class="stat-card-header">
                    <span class="stat-card-title">Total de Testes</span>
                    <i class="fas fa-vial stat-card-icon"></i>
                </div>
                <div class="stat-card-value"><?php echo $lastResults['totalTests']; ?></div>
                <div class="stat-card-footer"><?php echo $lastResults['assertions']; ?> asserções</div>
            </div>

            <div class="stat-card success">
                <div class="stat-card-header">
                    <span class="stat-card-title">Passaram</span>
                    <i class="fas fa-check-circle stat-card-icon"></i>
                </div>
                <div class="stat-card-value" style="color: var(--success);"><?php echo $lastResults['passed']; ?></div>
                <div class="stat-card-footer"><?php echo $lastResults['percentage']; ?>% de sucesso</div>
            </div>

            <div class="stat-card <?php echo $lastResults['failed'] > 0 ? 'danger' : 'success'; ?>">
                <div class="stat-card-header">
                    <span class="stat-card-title">Falharam</span>
                    <i class="fas fa-times-circle stat-card-icon"></i>
                </div>
                <div class="stat-card-value" style="color: <?php echo $lastResults['failed'] > 0 ? 'var(--danger)' : 'var(--success)'; ?>;"><?php echo $lastResults['failed']; ?></div>
                <div class="stat-card-footer"><?php echo $lastResults['errors']; ?> erros</div>
            </div>

        </div>

        <!-- Suites -->
        <?php if (!empty($lastResults['testSuites'])): ?>
        <h3 style="margin-bottom: 20px; color: var(--text-dark);"><i class="fas fa-cube"></i> Suites de Teste Detalhadas</h3>
        <div class="test-suites">
            <?php foreach ($lastResults['testSuites'] as $suite): ?>
            <div class="suite-card">
                <div class="suite-header">
                    <span class="suite-name"><?php echo htmlspecialchars($suite['name']); ?></span>
                    <span class="suite-badge <?php echo $suite['failed'] == 0 ? 'success' : 'danger'; ?>">
                        <?php echo $suite['percentage']; ?>%
                    </span>
                </div>

                <div class="suite-stats">
                    <div class="suite-stat">
                        <div class="suite-stat-value" style="color: var(--success);"><?php echo $suite['passed']; ?></div>
                        <div class="suite-stat-label">Passaram</div>
                    </div>
                    <div class="suite-stat">
                        <div class="suite-stat-value" style="color: var(--danger);"><?php echo $suite['failed']; ?></div>
                        <div class="suite-stat-label">Falharam</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; font-size: 0.9em; color: var(--text-light); margin-bottom: 15px;">
                    <div><i class="fas fa-check"></i> Total: <?php echo $suite['total']; ?></div>
                    <div><i class="fas fa-clipboard-check"></i> <?php echo $suite['assertions']; ?> asserções</div>
                    <div><i class="fas fa-percentage"></i> <?php echo $suite['percentage']; ?>%</div>
                </div>

                <div class="progress">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $suite['percentage']; ?>%"></div>
                </div>

                <?php if (!empty($suite['tests'])): ?>
                <div class="test-details">
                    <h6 style="margin-bottom: 15px; color: var(--text-dark); margin-top: 15px;">
                        <i class="fas fa-list"></i> Testes Executados (<?php echo count($suite['tests']); ?>)
                    </h6>
                    <?php foreach ($suite['tests'] as $test): ?>
                    <div class="test-item <?php echo $test['status']; ?>">
                        <span class="test-item-icon" style="color: <?php echo $test['status'] == 'passed' ? 'var(--success)' : 'var(--danger)'; ?>;">
                            <?php echo $test['status'] == 'passed' ? '✓' : '✗'; ?>
                        </span>
                        <span class="test-item-name"><?php echo htmlspecialchars($test['name']); ?></span>
                        <span class="test-item-assertions"><?php echo $test['assertions']; ?> assertions</span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Gráficos -->
        <h3 style="margin-bottom: 20px; color: var(--text-dark);"><i class="fas fa-chart-pie"></i> Análise Visual</h3>
        <div class="charts-section">
            <div class="chart-card">
                <h3>Distribuição de Resultados</h3>
                <canvas id="pieChart"></canvas>
            </div>

            <?php if ($cobertura): ?>
            <div class="chart-card">
                <h3>Cobertura de Classes</h3>
                <canvas id="coverageChart"></canvas>
            </div>
            <?php endif; ?>
            
            <?php
            $tendencia = $historicoManager->gerarGraficoTendencia();
            if (count($tendencia['labels']) >= 2):
            ?>
            <div class="chart-card">
                <h3>Tendência de Qualidade</h3>
                <canvas id="trendChart"></canvas>
            </div>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <!-- Mensagem inicial -->
        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
            <i class="fas fa-flask" style="font-size: 4em; color: var(--text-light); margin-bottom: 20px;"></i>
            <h3 style="color: var(--text-dark);">Nenhum teste executado ainda</h3>
            <p style="color: var(--text-light); margin-top: 10px;">Clique em "Executar Testes" para começar! 🚀</p>
        </div>
        <?php endif; ?>

        <div class="terminal-output" id="testOutput" style="display: none;"></div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <h3 style="margin: 0; color: var(--text-dark);">Executando Testes...</h3>
            <p style="margin-top: 10px; color: var(--text-light);">Aguarde alguns instantes</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    <?php if ($lastResults && $lastResults['totalTests'] > 0): ?>
    // 1. Gráfico de Pizza - Resultados
    (function() {
        const ctx = document.getElementById('pieChart');
        if (!ctx) return;
        
        const passed = <?php echo intval($lastResults['passed']); ?>;
        const failed = <?php echo intval($lastResults['failed']); ?>;
        const errors = <?php echo intval($lastResults['errors']); ?>;
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Passaram', 'Falharam', 'Erros'],
                datasets: [{
                    data: [passed, failed, errors],
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
                    borderWidth: 3,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = passed + failed + errors;
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    })();

    
    // 4. Gráfico de Cobertura
    <?php if ($cobertura): ?>
    (function() {
        const ctx = document.getElementById('coverageChart');
        if (!ctx) return;
        
        const testadas = <?php echo intval($cobertura['classes_com_testes']); ?>;
        const semTestes = <?php echo intval($cobertura['total_classes'] - $cobertura['classes_com_testes']); ?>;
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Testadas', 'Sem Testes'],
                datasets: [{
                    data: [testadas, semTestes],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderWidth: 3,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = testadas + semTestes;
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    })();
    <?php endif; ?>
    
    // 5. Gráfico de Tendência
    <?php
    $tendencia = $historicoManager->gerarGraficoTendencia();
    if (count($tendencia['labels']) >= 2):
    ?>
    (function() {
        const ctx = document.getElementById('trendChart');
        if (!ctx) return;
        
        const labels = <?php echo json_encode($tendencia['labels']); ?>;
        const passed = <?php echo json_encode($tendencia['passed']); ?>;
        const failed = <?php echo json_encode($tendencia['failed']); ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Testes Aprovados',
                        data: passed,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Testes Falhados',
                        data: failed,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    })();
    <?php endif; ?>
    <?php endif; ?>

    function testarExecutor() {
        const output = document.getElementById('testOutput');
        output.style.display = 'block';
        output.textContent = 'Executando diagnóstico...\n\n';
        fetch('executar_testes.php', { method: 'GET' })
            .then(r => r.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    output.textContent = '╔═══════════════════════════════════╗\n      DIAGNÓSTICO DO SISTEMA\n╚═══════════════════════════════════╝\n\n';
                    output.textContent += '📋 PHP: ' + data.php_version + '\n📂 Root: ' + data.project_root + '\n\n✓ VERIFICAÇÕES:\n';
                    for (let key in data.checks) {
                        output.textContent += (data.checks[key] ? '  ✓' : '  ✗') + ' ' + key + '\n';
                    }
                    output.textContent += '\n🔍 TESTES: ' + data.test_count + '\n';
                    if (data.test_files_found) data.test_files_found.forEach(f => output.textContent += '  • ' + f + '\n');
                    output.textContent += '\n⚙️ FUNÇÕES:\n';
                    for (let key in data.functions) {
                        output.textContent += '  ' + key + '(): ' + (data.functions[key] ? '✓' : '✗') + '\n';
                    }
                    output.textContent += '\n╚═══════════════════════════════════╝\n';
                } catch (e) {
                    output.textContent += text;
                }
            })
            .catch(err => output.textContent = '✗ ERRO: ' + err.message);
    }

    async function executarTestes() {
        const btn = document.getElementById('btnExecutar');
        const overlay = document.getElementById('loadingOverlay');
        const output = document.getElementById('testOutput');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Executando...';
        overlay.style.display = 'flex';
        output.style.display = 'block';
        output.style.background = '#1e1e1e';
        
        try {
            const response = await fetch('executar_testes.php', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json' } 
            });
            
            if (!response.ok) throw new Error('HTTP ' + response.status);
            
            const data = JSON.parse(await response.text());
            
                // ... (dentro da função executarTestes)
            if (data.success && data.results) {
                const results = data.results;
                
                // =============== BLOCO MODIFICADO ===============
                // Usa a nova função de formatação
                let outputText = formatarOutputResultados(results);
                
                // Adiciona o rodapé específico da execução
                outputText += '═══════════════════════════════════════════════════════════════════════\n';
                outputText += '✓ CONCLUÍDO! Recarregando página em 3 segundos...\n';
                outputText += '═══════════════════════════════════════════════════════════════════════\n';
                
                output.textContent = outputText;
                // ================================================
                
                // Atualizar botão
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Concluído!';
                btn.classList.remove('btn-test-primary');
                btn.classList.add('btn-test-success');
                
                // Recarregar após 3 segundos
                setTimeout(() => location.reload(), 3000);
                
            } else {
                output.textContent = '✗ ERRO\n\n' + (data.message || 'Desconhecido') + '\n\n' + (data.output || '');
                output.style.background = '#7f1d1d';
            }
            
        } catch (error) {
            output.textContent = '✗ ERRO DE CONEXÃO\n\n' + error.message;
            output.style.background = '#7f1d1d';
        } finally {
            overlay.style.display = 'none';
            if (btn.disabled) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-play-circle"></i> Executar Testes';
            }
        }
    }


    function toggleOutput() {
        const output = document.getElementById('testOutput');
        output.style.display = output.style.display === 'none' ? 'block' : 'none';
        if (output.style.display === 'block') output.scrollIntoView({ behavior: 'smooth' });
    }
    
    function formatarOutputResultados(results) {
        let outputText = '';
        
        // Cabeçalho
        outputText += '╔═══════════════════════════════════════════════════════════════════════╗\n';
        outputText += '               ✓ RESULTADO DOS TESTES (CARREGADO DO ARQUIVO)               \n';
        outputText += '╚═══════════════════════════════════════════════════════════════════════╝\n\n';
        
        // Resumo geral
        outputText += '📊 RESUMO GERAL:\n';
        outputText += '   • Total de Testes: ' + results.totalTests + '\n';
        outputText += '   • ✓ Passaram: ' + results.passed + ' (' + results.percentage + '%)\n';
        outputText += '   • ✗ Falharam: ' + results.failed + '\n';
        outputText += '   • ⏱️  Tempo Total: ' + results.time + 's\n';
        outputText += '   • 📝 Assertions: ' + results.assertions + '\n';
        outputText += '\n';
        
        // Detalhes por Suite
        if (results.testSuites && results.testSuites.length > 0) {
            outputText += '═══════════════════════════════════════════════════════════════════════\n';
            outputText += '📦 DETALHAMENTO POR CLASSE:\n';
            outputText += '═══════════════════════════════════════════════════════════════════════\n\n';
            
            results.testSuites.forEach((suite, suiteIndex) => {
                // Cabeçalho da Suite
                outputText += '┌─────────────────────────────────────────────────────────────────────┐\n';
                outputText += '│ ' + (suiteIndex + 1) + '. ' + (suite.name || 'Suite Desconhecida').padEnd(65) + ' │\n';
                outputText += '├─────────────────────────────────────────────────────────────────────┤\n';
                outputText += '│ Total: ' + (suite.total || 0) + ' testes';
                outputText += ' | Passou: ' + (suite.passed || 0);
                outputText += ' | Falhou: ' + (suite.failed || 0);
                outputText += ' | Tempo: ' + (suite.time || '0.00') + 's'.padEnd(20) + ' │\n';
                outputText += '└─────────────────────────────────────────────────────────────────────┘\n\n';
                
                // Lista de testes individuais
                if (suite.tests && suite.tests.length > 0) {
                    suite.tests.forEach((test, testIndex) => {
                        const status = test.status === 'passed' ? '✓' : '✗';
                        const testNum = String(testIndex + 1).padStart(2, '0');
                        
                        let testName = test.name || 'Teste sem nome';
                        if (testName.length > 50) {
                            testName = testName.substring(0, 47) + '...';
                        }
                        
                        const timeMs = (parseFloat(test.time || 0) * 1000).toFixed(1);
                        const timeFormatted = timeMs + 'ms';
                        
                        const assertions = (test.assertions || 0) + ' assert';
                        
                        outputText += '   ' + testNum + '. ' + status + ' ';
                        outputText += testName.padEnd(52);
                        outputText += ' ⏱️  ' + timeFormatted.padStart(8);
                        outputText += ' 📝 ' + assertions;
                        outputText += '\n';
                    });
                } else {
                    outputText += '   ℹ️  Nenhum detalhe de teste disponível\n';
                }
                
                outputText += '\n';
            });
        }
        
        return outputText;
    }

    // ======================================================================
    // PASSO 2B: NOVA FUNÇÃO PARA O BOTÃO "Ver Resultado"
    // ======================================================================
    async function mostrarUltimoResultado() {
        const output = document.getElementById('testOutput');
        output.style.display = 'block';
        output.style.background = '#1e1e1e'; // Resetar a cor
        output.textContent = 'Carregando último resultado salvo...';
        
        try {
            // Busca o arquivo JSON que o executar_testes.php salva
            const response = await fetch('reports/test-results.json?v=' + new Date().getTime()); // Adiciona cache-bust
            
            if (!response.ok) {
                throw new Error('Arquivo test-results.json não encontrado. Execute os testes primeiro.');
            }
            
            const results = await response.json();
            
            // Usa a nova função de formatação
            output.textContent = formatarOutputResultados(results);
            
        } catch (error) {
            output.textContent = '✗ ERRO AO CARREGAR RESULTADO\n\n' + error.message;
            output.style.background = '#7f1d1d';
        }
    }
    
    </script>
</body>
</html>