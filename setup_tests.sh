#!/bin/bash

# ============================================================
# SCRIPT DE INSTALAÇÃO E CONFIGURAÇÃO DOS TESTES
# Sistema Sala Certa
# ============================================================

echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "    INSTALAÇÃO E CONFIGURAÇÃO DE TESTES - SALA CERTA"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para imprimir mensagens
print_step() {
    echo -e "${BLUE}➜${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

# ============================================================
# PASSO 1: Verificar PHP
# ============================================================
print_step "Verificando versão do PHP..."

PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "  Versão encontrada: $PHP_VERSION"

if php -r "exit(version_compare(PHP_VERSION, '8.1.0', '<') ? 1 : 0);"; then
    print_success "PHP 8.1+ detectado"
else
    print_error "PHP 8.1 ou superior é necessário"
    print_warning "Instale o PHP 8.1+ e tente novamente"
    exit 1
fi

# ============================================================
# PASSO 2: Verificar/Instalar Composer
# ============================================================
print_step "Verificando Composer..."

if ! command -v composer &> /dev/null; then
    print_warning "Composer não encontrado. Instalando..."
    
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet
    rm composer-setup.php
    
    if [ -f "composer.phar" ]; then
        print_success "Composer instalado localmente"
        COMPOSER_CMD="php composer.phar"
    else
        print_error "Falha ao instalar Composer"
        exit 1
    fi
else
    print_success "Composer encontrado"
    COMPOSER_CMD="composer"
fi

# ============================================================
# PASSO 3: Criar estrutura de diretórios
# ============================================================
print_step "Criando estrutura de diretórios..."

mkdir -p tests/Unit
mkdir -p tests/logs
mkdir -p tests/coverage

print_success "Estrutura criada"

# ============================================================
# PASSO 4: Instalar dependências
# ============================================================
print_step "Instalando dependências do Composer..."

$COMPOSER_CMD install

if [ $? -eq 0 ]; then
    print_success "Dependências instaladas"
else
    print_error "Falha ao instalar dependências"
    exit 1
fi

# ============================================================
# PASSO 5: Verificar arquivos essenciais
# ============================================================
print_step "Verificando arquivos essenciais..."

FILES=(
    "phpunit.xml:Configuração do PHPUnit"
    "tests/bootstrap.php:Bootstrap dos testes"
    "tests/Unit/UsuarioTest.php:Testes do Usuario"
    "tests/Unit/SalaTest.php:Testes da Sala"
    "tests/Unit/ReservaTest.php:Testes da Reserva"
    "classes/Usuario.php:Classe Usuario"
    "classes/Sala.php:Classe Sala"
    "classes/Reserva.php:Classe Reserva"
)

MISSING_FILES=0

for file_desc in "${FILES[@]}"; do
    IFS=':' read -r file desc <<< "$file_desc"
    if [ -f "$file" ]; then
        print_success "$desc: OK"
    else
        print_warning "$desc: FALTANDO"
        MISSING_FILES=$((MISSING_FILES + 1))
    fi
done

if [ $MISSING_FILES -gt 0 ]; then
    print_warning "$MISSING_FILES arquivo(s) faltando"
    echo ""
    echo "Por favor, copie os arquivos fornecidos para os locais corretos:"
    echo "  - phpunit.xml → raiz do projeto"
    echo "  - tests/bootstrap.php"
    echo "  - tests/Unit/*.php"
    echo ""
fi

# ============================================================
# PASSO 6: Configurar permissões
# ============================================================
print_step "Configurando permissões..."

chmod -R 755 tests/
chmod -R 755 vendor/ 2>/dev/null || true

print_success "Permissões configuradas"

# ============================================================
# PASSO 7: Executar diagnóstico
# ============================================================
print_step "Executando diagnóstico..."
echo ""

if [ -f "diagnostico.php" ]; then
    php diagnostico.php
else
    print_warning "diagnostico.php não encontrado"
fi

# ============================================================
# PASSO 8: Executar testes
# ============================================================
echo ""
print_step "Executando testes pela primeira vez..."
echo ""

if [ -f "vendor/bin/phpunit" ]; then
    vendor/bin/phpunit --testdox --colors=always
    
    if [ $? -eq 0 ]; then
        echo ""
        print_success "TESTES EXECUTADOS COM SUCESSO!"
    else
        echo ""
        print_warning "Alguns testes falharam"
    fi
else
    print_error "PHPUnit não encontrado em vendor/bin/phpunit"
fi

# ============================================================
# RESUMO FINAL
# ============================================================
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "                    INSTALAÇÃO CONCLUÍDA"
echo "═══════════════════════════════════════════════════════════════"
echo ""
echo "📝 Comandos úteis:"
echo ""
echo "  # Executar todos os testes"
echo "  vendor/bin/phpunit"
echo ""
echo "  # Executar com saída detalhada"
echo "  vendor/bin/phpunit --testdox --colors=always"
echo ""
echo "  # Executar teste específico"
echo "  vendor/bin/phpunit tests/Unit/UsuarioTest.php"
echo ""
echo "  # Diagnóstico do sistema"
echo "  php diagnostico.php"
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo ""