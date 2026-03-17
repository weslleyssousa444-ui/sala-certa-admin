# ========================================
# SCRIPT DE TESTES RÁPIDO
# Sistema Sala Certa
# ========================================

param(
    [string]$File = "",
    [switch]$Verbose,
    [switch]$Watch
)

function Run-Tests {
    Write-Host ""
    Write-Host "🧪 Executando testes..." -ForegroundColor Cyan
    Write-Host ""
    
    $cmd = "vendor\bin\phpunit --testdox --colors=always"
    
    if ($File) {
        $cmd += " $File"
    }
    
    if ($Verbose) {
        $cmd += " --verbose"
    }
    
    Invoke-Expression $cmd
    
    Write-Host ""
    Write-Host "✅ Testes concluídos!" -ForegroundColor Green
    Write-Host ""
}

if ($Watch) {
    Write-Host "👀 Modo watch ativado. Pressione Ctrl+C para sair." -ForegroundColor Yellow
    
    while ($true) {
        Run-Tests
        Write-Host "Aguardando alterações... (Enter para rodar novamente)" -ForegroundColor Gray
        $null = Read-Host
    }
} else {
    Run-Tests
}