<?php
/**
 * GERENCIADOR DE HISTÓRICO DE TESTES
 * Armazena e compara resultados ao longo do tempo
 */

class HistoricoTestes {
    private $historicoFile;
    
    public function __construct() {
        $this->historicoFile = __DIR__ . '/reports/historico.json';
        
        // Criar diretório se não existir
        $dir = dirname($this->historicoFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
    
    /**
     * Salvar execução no histórico
     */
    public function salvarExecucao($results) {
        $historico = $this->carregarHistorico();
        
        $registro = [
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'totalTests' => $results['totalTests'],
            'passed' => $results['passed'],
            'failed' => $results['failed'],
            'errors' => $results['errors'],
            'percentage' => $results['percentage'],
            'time' => floatval($results['time']),
            'assertions' => $results['assertions'],
            'cobertura' => $results['cobertura']['porcentagem'] ?? 0,
            'projectStatus' => $results['projectStatus']
        ];
        
        // Adicionar no início do array
        array_unshift($historico, $registro);
        
        // Manter apenas últimos 50 registros
        $historico = array_slice($historico, 0, 50);
        
        file_put_contents($this->historicoFile, json_encode($historico, JSON_PRETTY_PRINT));
        
        return $registro;
    }
    
    /**
     * Carregar histórico
     */
    public function carregarHistorico() {
        if (!file_exists($this->historicoFile)) {
            return [];
        }
        
        $content = file_get_contents($this->historicoFile);
        return json_decode($content, true) ?: [];
    }
    
    /**
     * Gerar comparativo entre última e penúltima execução
     */
    public function gerarComparativo() {
        $historico = $this->carregarHistorico();
        
        if (count($historico) < 2) {
            return [
                'disponivel' => false,
                'mensagem' => 'Aguardando mais execuções para gerar comparativo'
            ];
        }
        
        $atual = $historico[0];
        $anterior = $historico[1];
        
        $diferencas = [
            'testes' => $atual['totalTests'] - $anterior['totalTests'],
            'passou' => $atual['passed'] - $anterior['passed'],
            'falhou' => $atual['failed'] - $anterior['failed'],
            'erros' => $atual['errors'] - $anterior['errors'],
            'tempo' => round($atual['time'] - $anterior['time'], 2),
            'cobertura' => round($atual['cobertura'] - $anterior['cobertura'], 1),
            'percentage' => round($atual['percentage'] - $anterior['percentage'], 1)
        ];
        
        $melhorias = [
            'menos_falhas' => $atual['failed'] < $anterior['failed'],
            'mais_rapido' => $atual['time'] < $anterior['time'],
            'melhor_cobertura' => $atual['cobertura'] > $anterior['cobertura'],
            'melhor_taxa' => $atual['percentage'] > $anterior['percentage'],
            'menos_erros' => $atual['errors'] < $anterior['errors']
        ];
        
        // Calcular score de melhoria geral
        $scoreMelhoria = 0;
        foreach ($melhorias as $melhoria) {
            if ($melhoria) $scoreMelhoria++;
        }
        
        return [
            'disponivel' => true,
            'atual' => $atual,
            'anterior' => $anterior,
            'diferencas' => $diferencas,
            'melhorias' => $melhorias,
            'score_melhoria' => $scoreMelhoria,
            'total_aspectos' => count($melhorias),
            'porcentagem_melhoria' => round(($scoreMelhoria / count($melhorias)) * 100, 1)
        ];
    }
    
    /**
     * Gerar dados para gráfico de tendência
     */
    public function gerarGraficoTendencia($limite = 10) {
        $historico = array_reverse($this->carregarHistorico());
        
        // Limitar aos últimos N registros
        $historico = array_slice($historico, -$limite);
        
        $dados = [
            'labels' => [],
            'passed' => [],
            'failed' => [],
            'time' => [],
            'cobertura' => [],
            'percentage' => []
        ];
        
        foreach ($historico as $registro) {
            $dados['labels'][] = date('d/m H:i', strtotime($registro['timestamp']));
            $dados['passed'][] = intval($registro['passed']);
            $dados['failed'][] = intval($registro['failed']);
            $dados['time'][] = floatval($registro['time']);
            $dados['cobertura'][] = floatval($registro['cobertura']);
            $dados['percentage'][] = floatval($registro['percentage']);
        }
        
        return $dados;
    }
    
    /**
     * Obter estatísticas gerais
     */
    public function gerarEstatisticas() {
        $historico = $this->carregarHistorico();
        
        if (empty($historico)) {
            return [
                'disponivel' => false,
                'mensagem' => 'Nenhuma execução no histórico'
            ];
        }
        
        $totalExecucoes = count($historico);
        
        // Calcular médias
        $somaPassed = array_sum(array_column($historico, 'passed'));
        $somaFailed = array_sum(array_column($historico, 'failed'));
        $somaTime = array_sum(array_column($historico, 'time'));
        $somaCobertura = array_sum(array_column($historico, 'cobertura'));
        $somaPercentage = array_sum(array_column($historico, 'percentage'));
        
        // Encontrar melhor e pior execução
        $melhorPercentage = max(array_column($historico, 'percentage'));
        $piorPercentage = min(array_column($historico, 'percentage'));
        
        $tempoMaisRapido = min(array_column($historico, 'time'));
        $tempoMaisLento = max(array_column($historico, 'time'));
        
        return [
            'disponivel' => true,
            'total_execucoes' => $totalExecucoes,
            'medias' => [
                'passed' => round($somaPassed / $totalExecucoes, 1),
                'failed' => round($somaFailed / $totalExecucoes, 1),
                'time' => round($somaTime / $totalExecucoes, 2),
                'cobertura' => round($somaCobertura / $totalExecucoes, 1),
                'percentage' => round($somaPercentage / $totalExecucoes, 1)
            ],
            'recordes' => [
                'melhor_taxa' => $melhorPercentage,
                'pior_taxa' => $piorPercentage,
                'mais_rapido' => $tempoMaisRapido,
                'mais_lento' => $tempoMaisLento
            ],
            'primeira_execucao' => end($historico)['timestamp'],
            'ultima_execucao' => $historico[0]['timestamp']
        ];
    }
    
    /**
     * Limpar histórico
     */
    public function limparHistorico() {
        if (file_exists($this->historicoFile)) {
            unlink($this->historicoFile);
        }
        return true;
    }
    
    /**
     * Exportar histórico para CSV
     */
    public function exportarCSV() {
        $historico = $this->carregarHistorico();
        
        if (empty($historico)) {
            return false;
        }
        
        $csv = "Timestamp,Data,Hora,Total Testes,Passou,Falhou,Erros,Taxa Sucesso (%),Tempo (s),Cobertura (%),Status\n";
        
        foreach ($historico as $registro) {
            $csv .= sprintf(
                "%s,%s,%s,%d,%d,%d,%d,%.1f,%.2f,%.1f,%s\n",
                $registro['timestamp'],
                $registro['data'],
                $registro['hora'],
                $registro['totalTests'],
                $registro['passed'],
                $registro['failed'],
                $registro['errors'],
                $registro['percentage'],
                $registro['time'],
                $registro['cobertura'],
                $registro['projectStatus']
            );
        }
        
        return $csv;
    }
}