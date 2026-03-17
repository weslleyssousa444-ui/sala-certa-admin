// ===== SALA CERTA ADMIN v2.0 =====
// Obsidian & Gold design system

// ── 1. SIDEBAR TOGGLE (mobile) ────────────────────────────────────────────────
function initSidebar() {
    const toggle  = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sc-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    if (!toggle || !sidebar) return;

    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        if (overlay) overlay.classList.toggle('active');
    });

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }
}

// ── 2. NOTIFICATION DROPDOWN ──────────────────────────────────────────────────
function initNotifications() {
    const bell     = document.querySelector('.notification-bell');
    const dropdown = document.querySelector('.notification-dropdown');

    if (!bell || !dropdown) return;

    bell.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('open');
    });

    // Close when clicking outside
    document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target) && !bell.contains(e.target)) {
            dropdown.classList.remove('open');
        }
    });

    // "Marcar todas como lidas" button
    const markAllBtn = dropdown.querySelector('.mark-all-read');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', () => {
            fetch('pages/marcar_notificacoes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'mark_all_read' })
            })
            .then(res => res.json())
            .then(() => {
                dropdown.querySelectorAll('.notification-item.unread')
                    .forEach(item => item.classList.remove('unread'));
                const badge = bell.querySelector('.notification-badge');
                if (badge) badge.remove();
            })
            .catch(err => console.warn('Notificações: erro ao marcar como lidas', err));
        });
    }
}

// ── 3. SCROLL REVEAL ──────────────────────────────────────────────────────────
function initScrollReveal() {
    const revealEls   = document.querySelectorAll('.reveal');
    const staggerParents = document.querySelectorAll('.reveal-stagger');

    if (!revealEls.length && !staggerParents.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    revealEls.forEach(el => observer.observe(el));

    // Stagger: reveal children with incremental delay
    const staggerObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const children = entry.target.querySelectorAll(':scope > *');
                children.forEach((child, i) => {
                    setTimeout(() => child.classList.add('revealed'), i * 100);
                });
                staggerObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    staggerParents.forEach(el => staggerObserver.observe(el));
}

// ── 4. COUNT-UP ANIMATION ─────────────────────────────────────────────────────
function initCountUp() {
    const countEls = document.querySelectorAll('.count-up[data-target]');
    if (!countEls.length) return;

    const easeOut = (t) => 1 - Math.pow(1 - t, 3);

    const animate = (el, target, duration) => {
        let start = null;
        const step = (timestamp) => {
            if (!start) start = timestamp;
            const elapsed  = timestamp - start;
            const progress = Math.min(elapsed / duration, 1);
            el.textContent = Math.floor(easeOut(progress) * target);
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target;
        };
        requestAnimationFrame(step);
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseInt(entry.target.dataset.target, 10);
                if (!isNaN(target)) animate(entry.target, target, 1500);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    countEls.forEach(el => observer.observe(el));
}

// ── 5. DATATABLES INIT ────────────────────────────────────────────────────────
function initDataTables() {
    if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') return;

    $('.sc-table').each(function () {
        if ($.fn.DataTable.isDataTable(this)) return;

        $(this).DataTable({
            pageLength: 10,
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
            },
            drawCallback: function () {
                // Style pagination buttons with gold theme
                document.querySelectorAll('.paginate_button').forEach(btn => {
                    btn.classList.add('sc-page-btn');
                });
            }
        });
    });
}

// ── 6. FORM VALIDATION ────────────────────────────────────────────────────────
function initFormValidation() {
    document.querySelectorAll('.needs-validation').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();

                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
            }
            form.classList.add('was-validated');
        }, false);
    });
}

// ── 7. DELETE CONFIRMATION ────────────────────────────────────────────────────
function initDeleteConfirmation() {
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.href;

            if (confirm('Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.')) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                this.disabled = true;
                setTimeout(() => { window.location.href = href; }, 400);
            }
        });
    });
}

// ── 8. AUTO-DISMISS ALERTS ────────────────────────────────────────────────────
function initAlerts() {
    document.querySelectorAll('.sc-alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.6s ease';
            alert.style.opacity   = '0';
            setTimeout(() => alert.remove(), 650);
        }, 5000);
    });
}

// ── 9. INPUT MASKS ────────────────────────────────────────────────────────────
function initMasks() {
    if (typeof $ === 'undefined' || typeof $.fn.mask === 'undefined') return;

    $('.cpf-mask').mask('000.000.000-00');
    $('.date-mask').mask('00/00/0000');
    $('.time-mask').mask('00:00');
    $('.phone-mask').mask('(00) 00000-0000');
}

// ── 10. CHART.JS DEFAULTS ─────────────────────────────────────────────────────
function initChartDefaults() {
    if (typeof Chart === 'undefined') return;

    Chart.defaults.font.family = "'DM Sans', sans-serif";
    Chart.defaults.color       = '#6b7280';

    // Gold palette exposed globally for charts
    window.SC_CHART_COLORS = {
        gold:         '#c9a84c',
        goldLight:    'rgba(201,168,76,0.2)',
        charcoal:     '#1a1a2e',
        offwhite:     '#f8f7f4',
        palette: ['#c9a84c', '#e4c97e', '#a07830', '#f0dfa0', '#7a5c20', '#d4b468']
    };
}

// ── 11. PARALLAX (landing page only) ─────────────────────────────────────────
function initParallax() {
    const heroBg = document.querySelector('.hero-bg');
    if (!heroBg) return;

    const applyParallax = () => {
        if (window.innerWidth < 992) {
            heroBg.style.transform = '';
            return;
        }
        heroBg.style.transform = `translateY(${window.scrollY * 0.3}px)`;
    };

    window.addEventListener('scroll', applyParallax, { passive: true });
    window.addEventListener('resize', applyParallax, { passive: true });
}

// ── 12. SETTINGS TAB SWITCHING ────────────────────────────────────────────────
function initSettingsTabs() {
    const tabs = document.querySelectorAll('.settings-tab');
    if (!tabs.length) return;

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const target = this.dataset.tab;
            document.querySelectorAll('.settings-panel').forEach(panel => {
                panel.style.display = panel.dataset.panel === target ? '' : 'none';
            });
        });
    });

    // Activate first tab by default if none is active
    const active = document.querySelector('.settings-tab.active');
    if (!active && tabs[0]) tabs[0].click();
}

// ── 13. PASSWORD TOGGLE ───────────────────────────────────────────────────────
function initPasswordToggle() {
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.querySelector(this.dataset.target)
                       || this.closest('.input-group')?.querySelector('input');
            if (!input) return;

            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye',      !isPassword);
                icon.classList.toggle('fa-eye-slash', isPassword);
            }
        });
    });
}

// ── 14. DEBOUNCE UTILITY ──────────────────────────────────────────────────────
function debounce(func, wait = 300) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Expose globally so inline scripts can use it
window.debounce = debounce;

// ── 15. CONSOLE BRANDING ──────────────────────────────────────────────────────
function printBranding() {
    console.log(
        '%cSala Certa Admin v2.0',
        'color:#c9a84c;font-family:Playfair Display,serif;font-size:20px;font-weight:700;'
    );
    console.log(
        '%cObsidian & Gold • Sistema de Gestão de Reservas',
        'color:#f8f7f4;background:#1a1a2e;padding:4px 8px;border-radius:4px;font-size:12px;'
    );
}

// ── BOOTSTRAP TOOLTIPS (kept for backwards compatibility) ─────────────────────
function initTooltips() {
    if (typeof bootstrap === 'undefined') return;
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el, { animation: true, delay: { show: 100, hide: 100 } });
    });
}

// ── INIT ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initNotifications();
    initScrollReveal();
    initCountUp();
    initDataTables();
    initFormValidation();
    initDeleteConfirmation();
    initAlerts();
    initMasks();
    initChartDefaults();
    initParallax();
    initSettingsTabs();
    initPasswordToggle();
    initTooltips();
    printBranding();
});
