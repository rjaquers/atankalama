<!-- ===================================================
     Scripts Globales

=================================================== -->
<!-- Inicia Datatables:-->
<script src='https://code.jquery.com/jquery-3.7.1.min.js'></script>

<script src='https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js'></script>
<script src='https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js'></script>

<script src='https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js'></script>
<script src='https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js'></script>

<script src='https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js'></script>

<script src='https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js'></script>
<script src='https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js'></script>



<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        if (typeof $ === 'undefined' || !$.fn.DataTable) {
            console.error('❌ DataTables no está disponible');
            return;
        }

        document.querySelectorAll('.datatable-export').forEach(function (table) {

            // Evita reinicialización
            if ($.fn.DataTable.isDataTable(table)) {
                return;
            }

            $(table).DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                order: [],
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-primary btn-sm'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-secondary btn-sm'
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
                }
            });

        });

    });
</script>
<!-- fin Datatables:-->



<script>
    // Auto-hide alerts
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(el => new bootstrap.Alert(el).close());
        }, 5000);
    });

    // Sidebar toggle
    document.addEventListener('DOMContentLoaded', () => {
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('show'));
            document.addEventListener('click', e => {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) sidebar.classList.remove('show');
            });
        }
    });

    // Confirm delete actions
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.delete-confirm').forEach(link => {
            link.addEventListener('click', e => {
                if (!confirm('¿Estás seguro de que quieres eliminar este elemento?')) e.preventDefault();
            });
        });
    });

    // Smooth scroll to top
    document.querySelector('a[href="#top"]')?.addEventListener('click', e => {
        e.preventDefault();
        window.scrollTo({top: 0, behavior: 'smooth'});
    });

    /**
     * Fuerza recarga del sitio agregando parámetro único
     * Evita uso de caché del navegador en esa petición.
     */
    function hardReload() {
        const url = new URL(window.location.href);
        url.searchParams.set('_reload', new Date().getTime());
        window.location.href = url.toString();
    }



    //Search
        document.addEventListener('DOMContentLoaded', function () {

        const input = document.getElementById('product-search');
        const resultsBox = document.getElementById('product-results');

        if (!input) return;

        input.addEventListener('keyup', function () {

        const query = input.value.trim();

        if (query.length < 3) {
        resultsBox.classList.add('d-none');
        resultsBox.innerHTML = '';
        return;
    }

        fetch(`index.php?page=products&action=search&q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {

        resultsBox.innerHTML = '';

        if (!data.length) {
        resultsBox.innerHTML = `
                        <div class="list-group-item text-muted">
                            Sin resultados
                        </div>`;
        resultsBox.classList.remove('d-none');
        return;
    }

        data.forEach(product => {

        const item = document.createElement('a');
        item.href = `index.php?page=products&action=edit&id=${product.id}`;
        item.className = 'list-group-item list-group-item-action';

        item.innerHTML = `
                        <div class="d-flex justify-content-between">
                            <span>${product.name}</span>
                            <strong>${product.quantity}</strong>
                        </div>
                    `;

        resultsBox.appendChild(item);
    });

        resultsBox.classList.remove('d-none');
    })
        .catch(err => console.error('Error en búsqueda:', err));
    });

        // Ocultar si se hace click fuera
        document.addEventListener('click', function (e) {
        if (!resultsBox.contains(e.target) && e.target !== input) {
        resultsBox.classList.add('d-none');
    }
    });

    });

    let timeout;
    input.addEventListener('keyup', function () {
        clearTimeout(timeout);
        timeout = setTimeout(() => { /* fetch */ }, 300);
    });

</script>

