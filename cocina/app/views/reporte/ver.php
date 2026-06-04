<!DOCTYPE html>
<html lang='es'>

<head>
    <?php include(ROOT_PATH . '../public/static/templates/head.php'); ?>
</head>

<body class='pro-body'>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom"
            style="border-color: var(--color-border) !important;">
            <h2 class="mb-0 fw-bold"><i class="bi bi-graph-up me-2" style="color: var(--color-cta)"></i>Ventas Últimos 5
                Días</h2>
        </div>

        <div class="pro-card border-0 mb-4 p-4">
            <form class='row g-3' method='GET' action='index.php'>
                <input type='hidden' name='page' value='Reporte/ver'>
                <div class='col-auto'>
                    <label class='form-label text-muted'>Desde</label>
                    <input type='date' class='form-control bg-transparent text-light border'
                        style="border-color: var(--color-border) !important;" name='fecha_inicio'
                        value="<?= $_GET['fecha_inicio'] ?? '' ?>">
                </div>
                <div class='col-auto'>
                    <label class='form-label text-muted'>Hasta</label>
                    <input type='date' class='form-control bg-transparent text-light border'
                        style="border-color: var(--color-border) !important;" name='fecha_fin'
                        value="<?= $_GET['fecha_fin'] ?? '' ?>">
                </div>
                <div class='col-auto align-self-end'>
                    <button type='submit' class='btn btn-pro-primary'><i class="bi bi-funnel me-1"></i>Filtrar</button>
                    <a href='index.php?page=Reporte/ver' class='btn btn-outline-secondary ms-2'><i
                            class="bi bi-arrow-counterclockwise me-1"></i>Limpiar</a>
                </div>
            </form>
        </div>


        <?php
        //       // Construye la URL con los filtros activos
        //       $query_excel = 'index.php?page=Reporte/excel';
        //       if (! empty($_GET['fecha_inicio'])) {
        //           $query_excel .= '&fecha_inicio='.urlencode($_GET['fecha_inicio']);
        //       }
        //       if (! empty($_GET['fecha_fin'])) {
        //           $query_excel .= '&fecha_fin='.urlencode($_GET['fecha_fin']);
        //       }
        //       ?>
        <!--       <div class="mb-3">-->
        <!--           <a href="--><?php //=$query_excel ?><!--" class="btn btn-success">-->
        <!--               <i class="bi bi-file-earmark-excel"></i> Exportar a Excel-->
        <!--           </a>-->
        <!--       </div>-->



        <div class="pro-card border-0 mb-4 p-4">
            <div class="table-responsive">
                <table id='tablaReporte' class='table pro-table mb-0'>
                    <thead>
                        <tr>
                            <th>Acciones</th>
                            <th>Fecha</th>
                            <th>Total de Órdenes</th>
                            <th>Total Ventas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($datos as $fila): ?>

                            <tr>
                                <td>
                                    <button class='btn btn-pro-action edit ver-detalle' data-fecha="<?= $fila['fecha'] ?>"
                                        title='Ver detalles'>
                                        <i class='bi bi-eye-fill'></i>
                                    </button>

                                    <a href="index.php?page=Reporte/detalleExcel&fecha=<?= $fila['fecha'] ?>"
                                        class='btn btn-pro-action edit ms-1' target='_blank'
                                        title='Exportar productos a Excel'>
                                        <i class='bi bi-file-earmark-excel-fill'></i>
                                    </a>
                                </td>
                                <td><?= date('d-m-Y', strtotime($fila['fecha'])) ?></td>
                                <td><?= $fila['total_ordenes'] ?></td>
                                <td><span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">$
                                        <?= number_format($fila['total_ventas'], 0, ',', '.') ?></span></td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <a href="index.php?page=cocina/index" class="btn btn-secondary mt-4">← Volver al menú</a>


    <!-- Modal de productos -->
    <div class='modal fade' id='modalDetalle' tabindex='-1' aria-labelledby='modalDetalleLabel' aria-hidden='true'>
        <div class='modal-dialog modal-lg'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title' id='modalDetalleLabel'>Productos vendidos</h5>
                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Cerrar'></button>
                </div>
                <div class='modal-body' id='detalleContenido'>
                    <!-- Contenido dinámico -->
                </div>
            </div>
        </div>
    </div>



    <!--footer-->
    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>
    <!--footer-->

    <!-- Inicialización del modal y DataTables -->
    <script>
        document.querySelectorAll('.ver-detalle').forEach(btn => {
            btn.addEventListener('click', () => {
                const fecha = btn.dataset.fecha;
                fetch(`index.php?page=Reporte/detalles&fecha=${fecha}`)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('detalleContenido').innerHTML = html;
                        new bootstrap.Modal(document.getElementById('modalDetalle')).show();
                    });
            });
        });

        $(document).ready(function () {
            $('#tablaReporte').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                        className: 'btn btn-success'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                        className: 'btn btn-danger'
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> Imprimir',
                        className: 'btn btn-secondary'
                    }
                ],
                order: [[1, 'desc']]
            });
        });
    </script>
</body>

</html>