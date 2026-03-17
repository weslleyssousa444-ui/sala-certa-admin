# 🎓 Sistema Sala Certa

Sistema de gerenciamento e reserva de salas educacionais.

## 🚀 Comandos Rápidos
```bash
# Rodar testes
.\test-quick.bat

# Verificar antes do deploy
.\pre-deploy.bat

# Rodar testes específicos
vendor\bin\phpunit tests\Unit\UsuarioTest.php --testdox
```

## 📊 Estatísticas de Testes

- **23 testes unitários**
- **33 assertions**
- **100% de sucesso**
- **3 classes testadas:** Usuario, Sala, Reserva

## 📁 Estrutura do Projeto
```
sala_certa_admin/
├── classes/          # Classes do sistema
├── config/           # Configurações
├── tests/            # Testes unitários
│   ├── Unit/        # Testes de unidade
│   └── README.md    # Documentação dos testes
├── scripts/         # Scripts de automação
├── vendor/          # Dependências (Composer)
└── phpunit.xml      # Configuração PHPUnit
```

## 🧪 Documentação de Testes

Veja documentação completa em: [tests/README.md](tests/README.md)

## 🔧 Requisitos

- PHP 8.2+
- Composer
- MySQL/MariaDB

## 📦 Deploy

1. Execute verificação: `.\pre-deploy.bat`
2. Faça backup do banco
3. Upload dos arquivos para servidor
4. Execute `composer install --no-dev` no servidor
5. Teste o sistema

---

**Desenvolvido com ❤️ para Sistema Sala Certa**