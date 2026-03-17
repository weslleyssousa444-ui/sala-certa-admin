</div> <!-- Fim container-fluid -->
    </div> <!-- Fim content -->
</div> <!-- Fim wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- jQuery Mask Plugin -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
jQuery(document).ready(function($) {
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // DataTables - Verificar se já não foi inicializado
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.datatable').each(function() {
            if (!$.fn.DataTable.isDataTable(this)) {
                $(this).DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/pt-BR.json'
                    },
                    responsive: true,
                    pageLength: 10,
                    order: [[0, 'desc']]
                });
            }
        });
    }
    
    // Máscaras
    if (typeof $.fn.mask !== 'undefined') {
        $('.cpf-mask').mask('000.000.000-00');
        $('.date-mask').mask('00/00/0000');
        $('.time-mask').mask('00:00');
        $('.phone-mask').mask('(00) 00000-0000');
    }
    
    // Confirmação de exclusão
    $('.btn-delete').on('click', function(e) {
        if (!confirm('Tem certeza que deseja excluir este item?')) {
            e.preventDefault();
        }
    });
    
    // Auto-fechar alertas
    $('.alert').each(function() {
        var alert = $(this);
        setTimeout(function() {
            alert.fadeOut('slow');
        }, 5000);
    });
});

// Dropdown do usuário
function toggleUserDropdown() {
    const dropdown = document.querySelector('.user-dropdown');
    if (dropdown) {
        dropdown.classList.toggle('open');
    }
}

// Fechar dropdown ao clicar fora
document.addEventListener('click', function(event) {
    const userDropdown = document.querySelector('.user-dropdown');
    if (userDropdown && !userDropdown.contains(event.target)) {
        userDropdown.classList.remove('open');
    }
});
</script>

</body>
</html>