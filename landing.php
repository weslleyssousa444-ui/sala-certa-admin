<?php
// Get dynamic counts from database
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/conexao.php';
try {
    $pdo = Conexao::getConn();
    $totalReservas = $pdo->query("SELECT COUNT(*) FROM RESERVA_SALA")->fetchColumn();
    $totalSalas = $pdo->query("SELECT COUNT(*) FROM SALA")->fetchColumn();
    $totalUsuarios = $pdo->query("SELECT COUNT(*) FROM USUARIO")->fetchColumn();
} catch (Throwable $e) {
    $totalReservas = 500;
    $totalSalas = 50;
    $totalUsuarios = 200;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sala Certa - Reserve o Espaço Perfeito</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
</head>
<body class="landing-page">

    <!-- Section 1: Hero -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <img src="assets/img/logo.png" alt="Sala Certa" style="width:80px;height:80px;border-radius:16px;margin-bottom:24px;box-shadow:0 8px 32px rgba(0,0,0,0.3);">
            <h1>Reserve o Espaço Perfeito</h1>
            <p>Gerencie reservas de salas de forma simples, rápida e inteligente.<br>A solução corporativa que a sua organização precisa.</p>
            <a href="login.php" class="btn btn-cta">Começar Agora <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
        <div class="scroll-arrow">
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>

    <!-- Section 2: Features -->
    <section class="features-section">
        <div class="container">
            <div class="section-title">
                <h2>Tudo que você precisa</h2>
                <p>Uma plataforma completa para gerenciamento de espaços corporativos</p>
            </div>
            <div class="features-grid reveal reveal-stagger">
                <div class="feature-card reveal-child">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h4>Reserva Inteligente</h4>
                    <p>Faça reservas em segundos com verificação automática de conflitos</p>
                </div>
                <div class="feature-card reveal-child">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h4>Calendário Visual</h4>
                    <p>Visualize todas as reservas em formato de calendário interativo</p>
                </div>
                <div class="feature-card reveal-child">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h4>Relatórios Completos</h4>
                    <p>Acompanhe métricas detalhadas de uso das salas</p>
                </div>
                <div class="feature-card reveal-child">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h4>Notificações</h4>
                    <p>Receba lembretes e atualizações sobre suas reservas</p>
                </div>
                <div class="feature-card reveal-child">
                    <div class="feature-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h4>Reserva Recorrente</h4>
                    <p>Agende reservas semanais, quinzenais ou mensais</p>
                </div>
                <div class="feature-card reveal-child">
                    <div class="feature-icon">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <h4>Gestão de Salas</h4>
                    <p>Gerencie salas de teatro, reunião, apresentação e mais</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 3: How It Works -->
    <section class="how-it-works">
        <div class="container">
            <div class="section-title">
                <h2>Como Funciona</h2>
                <p>Em apenas três passos simples você garante o espaço ideal</p>
            </div>
            <div class="steps-container">
                <div class="step reveal">
                    <div class="step-number">01</div>
                    <h4>Escolha a Sala</h4>
                    <p>Navegue pelo catálogo e selecione a sala ideal</p>
                </div>
                <div class="step reveal">
                    <div class="step-number">02</div>
                    <h4>Selecione Data e Horário</h4>
                    <p>Escolha quando precisa e veja disponibilidade em tempo real</p>
                </div>
                <div class="step reveal">
                    <div class="step-number">03</div>
                    <h4>Confirme a Reserva</h4>
                    <p>Pronto! Sua sala está reservada com confirmação instantânea</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 4: Impact Numbers -->
    <section class="impact-section reveal">
        <div class="container">
            <div class="section-title">
                <h2>Nosso Impacto</h2>
                <p>Números que demonstram a confiança dos nossos usuários</p>
            </div>
            <div class="impact-grid">
                <div class="impact-item">
                    <div class="impact-number" data-target="<?= (int)$totalReservas ?>">0+</div>
                    <div class="impact-label">Reservas</div>
                </div>
                <div class="impact-item">
                    <div class="impact-number" data-target="<?= (int)$totalSalas ?>">0+</div>
                    <div class="impact-label">Salas</div>
                </div>
                <div class="impact-item">
                    <div class="impact-number" data-target="<?= (int)$totalUsuarios ?>">0+</div>
                    <div class="impact-label">Usuários</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 5: Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="footer-logo" style="display:flex;align-items:center;gap:12px;justify-content:center;">
                <img src="assets/img/logo.png" alt="Sala Certa" style="width:32px;height:32px;border-radius:6px;">
                <span>Sala Certa</span>
            </div>
            <nav class="footer-links">
                <a href="login.php">Login</a>
                <a href="#">Sobre</a>
            </nav>
            <p class="footer-copy">&copy; <?= date('Y') ?> Sala Certa. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Parallax effect (desktop only)
        if (window.innerWidth >= 992) {
            const heroBg = document.querySelector('.hero-bg');
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                if (heroBg) heroBg.style.transform = 'translateY(' + scrolled * 0.4 + 'px)';
            });
        }

        // Scroll reveal with IntersectionObserver
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        document.querySelectorAll('.reveal, .reveal-stagger').forEach(function(el) {
            observer.observe(el);
        });

        // Count-up animation
        const countObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.getAttribute('data-target'));
                    let current = 0;
                    const increment = Math.ceil(target / 60);
                    const timer = setInterval(function() {
                        current += increment;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        el.textContent = current + '+';
                    }, 25);
                    countObserver.unobserve(el);
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('.impact-number').forEach(function(el) {
            countObserver.observe(el);
        });

        // Smooth scroll for arrow
        document.querySelector('.scroll-arrow')?.addEventListener('click', function() {
            document.querySelector('.features-section')?.scrollIntoView({ behavior: 'smooth' });
        });
    });
    </script>
</body>
</html>
