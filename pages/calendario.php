<?php
$pageTitle = 'Calendário';
require_once '../config/config.php';
require_once '../classes/Reserva.php';
requireLogin();
include '../includes/header.php';
?>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css" rel="stylesheet">

<div class="page-header">
    <div class="page-header-left">
        <h2 class="page-title">Calendário de Reservas</h2>
        <p class="page-subtitle">Visualize e gerencie reservas por data</p>
    </div>
    <div class="page-header-right">
        <a href="nova_reserva.php" class="btn-gold">
            <i class="fas fa-plus me-2"></i>Nova Reserva
        </a>
    </div>
</div>

<div class="sc-calendar" style="padding:1.5rem;position:relative">
    <div id="calendar"></div>
</div>

<!-- Side Panel -->
<div class="calendar-side-panel" id="sidePanel">
    <div class="panel-header">
        <span class="panel-title" id="panelDate">Detalhes</span>
        <button class="panel-close" id="panelClose">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="panel-body" id="panelContent"></div>
    <div class="panel-footer">
        <a href="nova_reserva.php" class="btn-gold" style="width:100%;justify-content:center">
            <i class="fas fa-plus me-2"></i>Nova Reserva
        </a>
    </div>
</div>

<div class="calendar-panel-overlay" id="panelOverlay"></div>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.9/locales/pt-br.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    var calendarEl = document.getElementById('calendar');
    var sidePanel  = document.getElementById('sidePanel');
    var overlay    = document.getElementById('panelOverlay');

    function openPanel() {
        sidePanel.classList.add('open');
        overlay.classList.add('visible');
    }

    function closePanel() {
        sidePanel.classList.remove('open');
        overlay.classList.remove('visible');
    }

    document.getElementById('panelClose').addEventListener('click', closePanel);
    overlay.addEventListener('click', closePanel);

    // Status label helpers
    var statusLabels = {
        'Ativa':     '<span style="color:#c9a84c;font-weight:600">Ativa</span>',
        'Reservado': '<span style="color:#3b82f6;font-weight:600">Reservado</span>',
        'Pendente':  '<span style="color:#6b7280;font-weight:600">Pendente</span>',
        'Cancelada': '<span style="color:#dc2626;font-weight:600">Cancelada</span>'
    };

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week:  'Semana',
            day:   'Dia'
        },
        events: 'calendar_events.php',
        eventClick: function (info) {
            var ev    = info.event;
            var props = ev.extendedProps;
            var start = ev.start;
            var end   = ev.end;

            var startStr = start
                ? start.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                : '—';
            var endStr = end
                ? end.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                : '—';
            var dateStr = start
                ? start.toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
                : '';

            document.getElementById('panelDate').textContent = ev.title;

            document.getElementById('panelContent').innerHTML =
                '<div class="detail-row">' +
                    '<div class="detail-icon"><i class="fas fa-calendar-day"></i></div>' +
                    '<div class="detail-content">' +
                        '<div class="detail-label">Data</div>' +
                        '<div class="detail-value">' + dateStr + '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="detail-row">' +
                    '<div class="detail-icon"><i class="fas fa-clock"></i></div>' +
                    '<div class="detail-content">' +
                        '<div class="detail-label">Horário</div>' +
                        '<div class="detail-value">' + startStr + ' – ' + endStr + '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="detail-row">' +
                    '<div class="detail-icon"><i class="fas fa-door-open"></i></div>' +
                    '<div class="detail-content">' +
                        '<div class="detail-label">Sala</div>' +
                        '<div class="detail-value">Sala ' + (props.sala || '—') + '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="detail-row">' +
                    '<div class="detail-icon"><i class="fas fa-user"></i></div>' +
                    '<div class="detail-content">' +
                        '<div class="detail-label">Usuário</div>' +
                        '<div class="detail-value">' + (props.usuario || '—') + '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="detail-row">' +
                    '<div class="detail-icon"><i class="fas fa-info-circle"></i></div>' +
                    '<div class="detail-content">' +
                        '<div class="detail-label">Status</div>' +
                        '<div class="detail-value">' + (statusLabels[props.estado] || props.estado || '—') + '</div>' +
                    '</div>' +
                '</div>';

            openPanel();
        },
        dateClick: function (info) {
            window.location.href = 'nova_reserva.php?data=' + info.dateStr;
        },
        eventDidMount: function (info) {
            // Tooltip nativo via title
            info.el.setAttribute('title', info.event.title);
        }
    });

    calendar.render();
});
</script>

<?php include '../includes/footer.php'; ?>
