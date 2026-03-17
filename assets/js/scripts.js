// ===== SALA CERTA - SCRIPTS MODERNOS =====

// Aguardar carregamento completo do DOM
document.addEventListener('DOMContentLoaded', function() {
    
    // ===== TOGGLE SIDEBAR =====
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarCollapse && sidebar) {
        sidebarCollapse.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            
            // Adicionar animação suave
            sidebar.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });
        
        // Fechar sidebar ao clicar fora (apenas em mobile)
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(e.target) && !sidebarCollapse.contains(e.target)) {
                    sidebar.classList.add('active');
                }
            }
        });
    }
    
    // ===== TOOLTIPS BOOTSTRAP =====
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            animation: true,
            delay: { show: 100, hide: 100 }
        });
    });
    
    // ===== CONFIRMAÇÃO DE EXCLUSÃO =====
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const confirmDelete = confirm('Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.');
            
            if (confirmDelete) {
                // Adicionar animação de loading
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                this.disabled = true;
                
                // Redirecionar após breve delay para mostrar animação
                setTimeout(() => {
                    window.location.href = this.href;
                }, 300);
            }
        });
    });
    
    // ===== VALIDAÇÃO DE FORMULÁRIOS =====
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Scroll para o primeiro campo inválido
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // ===== ANIMAÇÃO DOS CARDS =====
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '0';
                entry.target.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    entry.target.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, 100);
                
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observar todos os cards
    const cards = document.querySelectorAll('.card, .stats-card');
    cards.forEach(card => observer.observe(card));
    
    // ===== DATATABLES CONFIGURAÇÃO - REMOVIDO (já está no footer.php) =====
    // A inicialização do DataTables agora está centralizada no footer.php
    
    // ===== GRÁFICOS - CONFIGURAÇÃO GLOBAL =====
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
        Chart.defaults.color = '#7f8c8d';
        Chart.defaults.plugins.legend.display = true;
        Chart.defaults.plugins.legend.position = 'bottom';
        Chart.defaults.elements.line.tension = 0.4;
        Chart.defaults.elements.point.radius = 4;
        Chart.defaults.elements.point.hoverRadius = 6;
    }
    
    // ===== FUNÇÃO PARA GRÁFICO DE RESERVAS =====
    window.initReservasChart = function(data) {
        const ctx = document.getElementById('reservasChart');
        if (!ctx) return;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Reservas',
                    data: data.values,
                    borderColor: '#37D0C0',
                    backgroundColor: 'rgba(55, 208, 192, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        borderColor: '#37D0C0',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    };
    
    // ===== FUNÇÃO PARA GRÁFICO DE SALAS =====
    window.initSalasChart = function(data) {
        const ctx = document.getElementById('salasChart');
        if (!ctx) return;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Reservas',
                    data: data.values,
                    backgroundColor: [
                        '#37D0C0',
                        '#53C598',
                        '#B3E2D0',
                        '#7fcfba',
                        '#a5e0cf'
                    ],
                    borderWidth: 0,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    };
    
    // ===== AUTO-DISMISS ALERTS =====
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000); // Auto-dismiss após 5 segundos
    });
    
    // ===== ANIMAÇÃO DE NÚMEROS (COUNT UP) =====
    const animateValue = (element, start, end, duration) => {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            element.innerHTML = Math.floor(progress * (end - start) + start);
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    };
    
    // Animar números nos stats cards
    const statsNumbers = document.querySelectorAll('.stats-card h4');
    statsNumbers.forEach(stat => {
        const finalValue = parseInt(stat.textContent);
        if (!isNaN(finalValue)) {
            stat.textContent = '0';
            setTimeout(() => {
                animateValue(stat, 0, finalValue, 1500);
            }, 300);
        }
    });
    
    // ===== SMOOTH SCROLL PARA ÂNCORAS =====
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // ===== LOADING STATE NOS BOTÕES DE SUBMIT =====
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(button => {
        button.closest('form')?.addEventListener('submit', function(e) {
            if (this.checkValidity()) {
                button.disabled = true;
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processando...';
                
                // Restaurar após 5 segundos (failsafe)
                setTimeout(() => {
                    button.disabled = false;
                    button.innerHTML = originalText;
                }, 5000);
            }
        });
    });
    
    // ===== MÁSCARAS DE INPUT =====
    if (typeof $ !== 'undefined' && typeof $.fn.mask !== 'undefined') {
        $('.cpf-mask').mask('000.000.000-00');
        $('.date-mask').mask('00/00/0000');
        $('.time-mask').mask('00:00');
        $('.phone-mask').mask('(00) 00000-0000');
    }
    
    // ===== PLACEHOLDER ANIMATION =====
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
    });
    
    // ===== MENSAGEM DE BOAS-VINDAS =====
    console.log('%c🎉 Sala Certa Admin', 'color: #37D0C0; font-size: 24px; font-weight: bold;');
    console.log('%cVersão 2.0 - Sistema de Gestão de Reservas', 'color: #53C598; font-size: 14px;');
    console.log('%cDesenvolvido com ❤️', 'color: #B3E2D0; font-size: 12px;');
    
});

// ===== DEBOUNCE FUNCTION (Útil para search) =====
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ===== LOADING SCREEN (Opcional) =====
window.addEventListener('load', function() {
    const loader = document.getElementById('loader');
    if (loader) {
        loader.style.opacity = '0';
        setTimeout(() => {
            loader.style.display = 'none';
        }, 300);
    }
});