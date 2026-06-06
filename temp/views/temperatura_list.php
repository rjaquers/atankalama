<?php if (empty($registros)): ?>
    <div class="alert alert-info text-center mt-3 mx-3">
        <i class="fa-solid fa-info-circle me-1"></i>
        No hay registros para la fecha seleccionada.
    </div>
<?php endif; ?>

<!-- Encabezado principal -->
<div class="pro-card mt-3 mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <span class='fw-semibold'>
            <i class="fa-solid fa-list-check me-1"></i> Registros de Temperaturas
        </span>
        <div class="d-flex gap-2">
            <a href="cron_envio.php" class="btn btn-warning btn-sm">
                <i class="fa-solid fa-paper-plane me-1"></i>
                <span class="d-none d-sm-inline">Enviar Reporte</span>
                <span class="d-sm-none">Enviar</span>
            </a>
            <a href="index.php" class="btn btn-light btn-sm">
                <i class="fa-solid fa-plus me-1"></i>
                <span class="d-none d-sm-inline">Nuevo Registro</span>
                <span class="d-sm-none">Nuevo</span>
            </a>
        </div>
    </div>

    <div class="card-body p-3">

        <!-- Selector de fecha -->
        <form class="mb-3 d-flex gap-2 align-items-end flex-wrap" action="index.php" method="get">
            <input type="hidden" name="route" value="listar">
            <div class="flex-grow-1">
                <label class="form-label text-muted small fw-semibold mb-1">
                    <i class="fa-regular fa-calendar-alt me-1"></i> Filtrar por Fecha
                </label>
                <input type="text" id="inputFecha" name="fecha" class="form-control"
                    value="<?= htmlspecialchars($_GET['fecha'] ?? date('Y-m-d')); ?>"
                    placeholder="Seleccionar fecha..." readonly style="cursor:pointer;">
            </div>
            <div>
                <button class="btn btn-primary px-4" style='min-height: 48px;'>
                    <i class="fa-solid fa-search me-1"></i> Buscar
                </button>
            </div>
        </form>

        <!-- Nota de exportación solo para escritorio -->
        <p class="text-muted small d-none d-md-block mb-2">
            <i class="fa-solid fa-info-circle me-1"></i>
            Desde un computador puedes exportar a Excel, PDF o imprimir usando los botones sobre la tabla.
        </p>

        <!-- Tabla -->
        <div class="table-responsive">
            <table id="tablaTemperaturas" class="pro-table align-middle" style="width:100%">
                <thead>
                    <tr class='small'>
                        <th>Nombre</th>
                        <th>Lugar</th>
                        <th>°C</th>
                        <th>Fecha/Hora</th>
                        <th>Fotos</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $r):
                        $hotelBadgeClass = 'bg-primary';
                        $hName = strtolower($r['hotel']);
                        if (strpos($hName, 'cocina') !== false)
                            $hotelBadgeClass = 'bg-danger';
                        elseif (strpos($hName, 'frio') !== false)
                            $hotelBadgeClass = 'bg-info text-dark';
                        elseif (strpos($hName, 'comedor') !== false)
                            $hotelBadgeClass = 'bg-success';
                        ?>
                        <tr class="align-middle">
                            <td class="fw-semibold"><?= htmlspecialchars($r['nombre']); ?></td>
                            <td>
                                <span class="badge rounded-pill <?= $hotelBadgeClass ?> px-2 py-1"
                                    style="font-size: 0.72rem;">
                                    <?= htmlspecialchars($r['hotel']); ?>
                                </span>
                            </td>
                            <td class="fw-bold text-primary"><?= $r['temperatura']; ?>°</td>
                            <td class="text-muted small">
                                <i class="fa-regular fa-clock me-1"></i><?= $r['fecha_hora']; ?>
                            </td>
                            <td>
                                <?php foreach (explode(',', $r['fotos']) as $foto): ?>
                                    <?php if (trim($foto) !== ''): ?>
                                        <a href="<?= htmlspecialchars($foto); ?>"
                                            class="glightbox"
                                            data-gallery="fotos-<?= $r['id']; ?>"
                                            data-title="<?= htmlspecialchars($r['nombre']); ?> - <?= htmlspecialchars($r['hotel']); ?>">
                                            <img src="<?= htmlspecialchars($foto); ?>"
                                                class="thumb img-thumbnail border-0 shadow-sm"
                                                style="width:44px;height:44px;object-fit:cover;border-radius:8px;cursor:pointer;">
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button
                                        class='btn btn-outline-danger btn-sm ver-pdf d-flex align-items-center justify-content-center'
                                        style="width: 40px; height: 40px; padding: 0; border-radius: 10px;"
                                        data-id="<?= $r['id'] ?>"
                                        title='Descargar PDF'>
                                        <i class='fa-solid fa-file-pdf'></i>
                                    </button>
                                    <button
                                        class='btn btn-danger btn-sm eliminar-registro d-flex align-items-center justify-content-center'
                                        style="width: 40px; height: 40px; padding: 0; border-radius: 10px;"
                                        data-id="<?= $r['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($r['nombre']) ?>"
                                        title='Eliminar registro'>
                                        <i class='fa-solid fa-trash'></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal PDF (solo escritorio) -->
<div class='modal fade' id='pdfModal' tabindex='-1' aria-hidden='true'>
    <div class='modal-dialog modal-dialog-centered modal-xl'>
        <div class='modal-content'>
            <div class='modal-header bg-danger text-white'>
                <h5 class='modal-title'>
                    <i class='fa-solid fa-file-pdf me-1'></i> Vista del PDF
                </h5>
                <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'></button>
            </div>
            <div class='modal-body p-0'>
                <iframe id='visorPDF' src='' frameborder='0'
                    style='width:100%; height:80vh; display:block;'></iframe>
                <div id='fallbackPDF' class='text-center p-4' style='display:none;'>
                    <p class='mb-3 text-muted'>Tu navegador no puede mostrar la vista previa del PDF.</p>
                    <a href='#' target='_blank' id='linkPDF' class='btn btn-outline-danger'>
                        <i class='fa-solid fa-download me-1'></i> Descargar PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const isMobile = window.innerWidth < 768;

        const visorPDF   = document.getElementById('visorPDF');
        const linkPDF    = document.getElementById('linkPDF');
        const fallback   = document.getElementById('fallbackPDF');
        const modalEl    = document.getElementById('pdfModal');
        const modalPDF   = modalEl ? new bootstrap.Modal(modalEl) : null;

        document.querySelectorAll('.ver-pdf').forEach(btn => {
            btn.addEventListener('click', () => {
                const id  = btn.getAttribute('data-id');
                const url = `index.php?route=exportarPDF&id=${id}`;

                if (isMobile) {
                    // En móvil: descarga directa sin modal
                    window.location.href = url + '&modo=descargar';
                } else {
                    // En escritorio: mostrar en modal
                    visorPDF.src = url;
                    linkPDF.href = url + '&modo=descargar';
                    modalPDF.show();

                    setTimeout(() => {
                        if (!visorPDF.src) {
                            visorPDF.style.display = 'none';
                            fallback.style.display = 'block';
                        }
                    }, 2000);
                }
            });
        });

        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', () => {
                visorPDF.src = '';
                linkPDF.href = '#';
                fallback.style.display = 'none';
                visorPDF.style.display = 'block';
            });
        }

        document.querySelectorAll('.eliminar-registro').forEach(btn => {
            btn.addEventListener('click', () => {
                const id     = btn.getAttribute('data-id');
                const nombre = btn.getAttribute('data-nombre');

                Swal.fire({
                    icon: 'warning',
                    title: '¿Eliminar registro?',
                    html: `Se eliminará el registro de <b>${nombre}</b> y todas sus fotos.<br><span class="text-danger">Esta acción no se puede deshacer.</span>`,
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fa-solid fa-trash me-1"></i> Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    const fd = new FormData();
                    fd.append('id', id);

                    fetch('index.php?route=eliminar', { method: 'POST', body: fd })
                        .then(r => r.json())
                        .then(data => {
                            if (data.ok) {
                                const fila     = btn.closest('tr');
                                const tablaApi = $('#tablaTemperaturas').DataTable();
                                const row      = tablaApi.row(fila.classList.contains('child') ? fila.previousElementSibling : fila);
                                row.remove().draw(false);

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Registro eliminado',
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error al eliminar',
                                    text: 'No se pudo eliminar el registro. Intenta nuevamente.',
                                    confirmButtonColor: '#0d6efd'
                                });
                            }
                        })
                        .catch(() => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de red',
                                text: 'No se pudo conectar al servidor.',
                                confirmButtonColor: '#0d6efd'
                            });
                        });
                });
            });
        });
    });
</script>

<!-- Flatpickr - Selector de fecha sin picker nativo -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
    flatpickr('#inputFecha', {
        locale: 'es',
        dateFormat: 'Y-m-d',
        disableMobile: true,   // siempre usa el calendario JS, nunca el picker nativo del SO
        allowInput: false,
        defaultDate: document.getElementById('inputFecha').value || new Date(),
        onChange: function (selectedDates, dateStr, instance) {
            // Auto-submit al seleccionar fecha (UX mobile natural)
            instance.element.closest('form').submit();
        }
    });
</script>

<!-- GLightbox -->
<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css'>
<script src='https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        GLightbox({ selector: '.glightbox' });
    });
</script>

<!-- DataTables (solo necesario en esta vista) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel='stylesheet' href='https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css'>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
    $(document).ready(function () {
        $('#tablaTemperaturas').DataTable({
            responsive: { details: { type: 'column', target: 'tr' } },
            order: [[3, 'desc']],
            pageLength: 10,
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json",
                searchPlaceholder: "Buscar registros...",
            },
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    title: 'Temperaturas Atankalama',
                    text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                    className: 'btn btn-success btn-sm'
                },
                {
                    extend: 'pdfHtml5',
                    title: 'Temperaturas Atankalama',
                    text: '<i class="fa-solid fa-file-pdf me-1"></i> PDF',
                    className: 'btn btn-danger btn-sm'
                },
                {
                    extend: 'print',
                    text: '<i class="fa-solid fa-print me-1"></i> Imprimir',
                    className: 'btn btn-secondary btn-sm'
                }
            ]
        });
    });
</script>
