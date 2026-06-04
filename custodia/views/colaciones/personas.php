<?php include __DIR__.'/../../includes/header.php'; ?>
<?php include __DIR__.'/../../includes/inc_proyect.php'; ?>
<?php
// Variables entregadas desde el controlador:
// $lote_id, $personas, $nombresServicios, $serviciosRaw
?>
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<style>
    body {
        background: #f8f9fa;
    }

    .reporte-header {
        border-bottom: 2px solid #000;
        padding-bottom: 8px;
        margin-bottom: 20px;
    }

    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>

<div class="container mt-4">

    <!-- BOTONES -->
    <div class="no-print d-flex justify-content-between mb-3">
        <a href="<?=url('/colaciones/lotes')?>" class="btn btn-secondary btn-sm">Volver</a>
        <div class="d-flex gap-2">
            <a href="<?=url('/colaciones/personas/'.(int)$lote_id.'/exportar')?>"
               class="btn btn-success btn-sm"
               title="Exportar reporte completo a Excel">
                <i class="fa-solid fa-file-excel"></i> Exportar Excel
            </a>
            <button onclick="window.print()" class="btn btn-dark btn-sm">Imprimir</button>
        </div>
    </div>

    <h3 class="mb-3">Personas asociadas al Lote <?=(int)$lote_id?></h3>

    <div class='mb-3'>
        <h5>Servicios del lote</h5>

        <span class='badge bg-primary'>
            <?=$nombresServicios[$serviciosRaw['principal']] ?? 'Servicio principal'?>
        </span>

        <?php foreach ($serviciosRaw['adicionales'] as $sid): ?>
            <span class="badge bg-info text-dark"><?=$nombresServicios[$sid] ?? 'Adicional'?></span>
        <?php endforeach; ?>
    </div>

    <div class="reporte-header no-print">
        <div class='card mb-4 no-print'>
            <div class='card-header bg-primary text-white'>
                <strong>Datos del Lote</strong>
            </div>
            <div class='card-body'>

                <form id='formLoteFecha' class='row g-3'>
                    <input type='hidden' name='lote_id' value="<?=(int)$lote_id?>">

                    <div class='col-md-4'>
                        <label class='form-label'>Fecha inicio servicio</label>
                        <input type='date' name='fecha_servicio'
                               value="<?=htmlspecialchars($lote['fecha_servicio'])?>"
                               class='form-control' required>
                    </div>

                    <div class='col-md-4'>
                        <label class='form-label'>Fecha fin servicio</label>
                        <input type='date' name='fecha_fin_servicio'
                               value="<?=htmlspecialchars($lote['fecha_fin_servicio'])?>"
                               class='form-control' required>
                    </div>

                    <div class='col-md-4 d-flex align-items-end'>
                        <button class='btn btn-success w-100'>
                            Actualizar fechas del Servicio
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- CARD GESTIONAR SERVICIOS -->
    <div class='card mb-4 no-print'>
        <div class='card-header bg-secondary text-white'>
            <strong>Gestionar Servicios del Lote</strong>
        </div>
        <div class='card-body'>
            <form id='formServicios'>
                <input type='hidden' name='lote_id' value="<?=(int)$lote_id?>">

                <?php
                    $idsActivos = array_merge(
                        $serviciosRaw['principal'] ? [$serviciosRaw['principal']] : [],
                        $serviciosRaw['adicionales']
                    );
                ?>
                <div class='mb-3'>
                    <div class='d-flex flex-wrap gap-3'>
                        <?php foreach ($todosServicios as $t): ?>
                            <div class='form-check'>
                                <input class='form-check-input'
                                       type='checkbox'
                                       name='servicios[]'
                                       value="<?=(int)$t['id']?>"
                                       id="servicio_<?=(int)$t['id']?>"
                                    <?= in_array((int)$t['id'], $idsActivos) ? 'checked' : '' ?>>
                                <label class='form-check-label' for="servicio_<?=(int)$t['id']?>">
                                    <?=htmlspecialchars($t['nombre'])?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type='submit' class='btn btn-secondary'>
                    Actualizar Servicios
                </button>
            </form>
        </div>
    </div>

    <!-- TABS -->
    <ul class="nav nav-tabs mb-3 no-print" id="tabsLote" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabPersonas">
                Personas (<?= count($personas) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabReporte">
                Reporte de Impresiones
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- ===== TAB 1: PERSONAS ===== -->
        <div class="tab-pane fade show active" id="tabPersonas">

            <div class="mb-3">
                <button class="btn btn-success btn-sm" id="btnAgregarPersona">
                    + Agregar Persona
                </button>
            </div>

            <table id="tablaPersonas" class="table table-striped table-bordered table-sm" style="width:100%">
                <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>RUT</th>
                    <th>Nombre</th>
                    <th>Habitación</th>
                    <th>Estado</th>
                    <th class="no-print">Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($personas as $p): ?>
                    <tr>
                        <td><?=(int)$p['id']?></td>
                        <td><?=htmlspecialchars($p['guest_rut'])?></td>
                        <td><?=htmlspecialchars($p['guest_nombre'])?></td>
                        <td><?=htmlspecialchars($p['guest_habitacion'] ?? '—')?></td>
                        <td><?=htmlspecialchars($p['estado'])?></td>
                        <td class="no-print">
                            <button class="btn btn-warning btn-sm editarPersona"
                                    data-id="<?=(int)$p['id']?>"
                                    data-rut="<?=htmlspecialchars($p['guest_rut'], ENT_QUOTES)?>"
                                    data-nombre="<?=htmlspecialchars($p['guest_nombre'], ENT_QUOTES)?>"
                                    data-hab="<?=htmlspecialchars($p['guest_habitacion'] ?? '', ENT_QUOTES)?>">
                                Editar
                            </button>
                            <button class="btn btn-danger btn-sm eliminarPersona"
                                    data-id="<?=(int)$p['id']?>">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

        </div><!-- /tabPersonas -->

        <!-- ===== TAB 2: REPORTE DE IMPRESIONES ===== -->
        <div class="tab-pane fade" id="tabReporte">

            <?php
            // Extraer los servicios del lote en orden para las columnas
            $serviciosReporte = [];
            foreach ($idsServicios as $sid) {
                if ($sid && isset($nombresServicios[$sid])) {
                    $serviciosReporte[$sid] = $nombresServicios[$sid];
                }
            }

            // Estadísticas globales
            $totalPersonas   = count($reporteImpresiones);
            $imprimieronAlgo = 0;
            foreach ($reporteImpresiones as $r) {
                foreach ($r['servicios'] as $s) {
                    if ($s['total'] > 0) { $imprimieronAlgo++; break; }
                }
            }
            ?>

            <!-- Resumen -->
            <div class="row mb-3 g-2">
                <div class="col-auto">
                    <div class="card border-primary text-center px-4 py-2">
                        <div class="fs-4 fw-bold text-primary"><?= count($personas) ?></div>
                        <div class="small text-muted">Personas en el lote</div>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="card border-success text-center px-4 py-2">
                        <div class="fs-4 fw-bold text-success"><?= $imprimieronAlgo ?></div>
                        <div class="small text-muted">Imprimieron al menos 1</div>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="card border-danger text-center px-4 py-2">
                        <div class="fs-4 fw-bold text-danger"><?= count($personas) - $imprimieronAlgo ?></div>
                        <div class="small text-muted">Sin impresiones</div>
                    </div>
                </div>
            </div>

            <?php if (empty($reporteImpresiones)): ?>
                <div class="alert alert-info">No hay datos de impresiones para este lote.</div>
            <?php else: ?>

                <div class="table-responsive">
                    <table id="tablaReporte" class="table table-bordered table-sm table-hover align-middle">
                        <thead class="table-dark">
                        <tr>
                            <th>Nombre</th>
                            <th>RUT</th>
                            <th>Hab.</th>
                            <?php foreach ($serviciosReporte as $sid => $snombre): ?>
                                <th class="text-center"><?=htmlspecialchars($snombre)?></th>
                            <?php endforeach; ?>
                            <th class="text-center">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($reporteImpresiones as $r):
                            $totalPersona = 0;
                            foreach ($r['servicios'] as $s) { $totalPersona += $s['total']; }
                        ?>
                            <tr class="<?= $totalPersona === 0 ? 'table-warning' : '' ?>">
                                <td><?=htmlspecialchars($r['nombre'] ?? '—')?></td>
                                <td class="text-nowrap"><?=htmlspecialchars($r['rut'])?></td>
                                <td><?=htmlspecialchars($r['habitacion'] ?? '—')?></td>

                                <?php foreach ($serviciosReporte as $sid => $snombre):
                                    $svc = $r['servicios'][$sid] ?? null;
                                    $cnt = $svc['total'] ?? 0;
                                ?>
                                    <td class="text-center">
                                        <?php if ($cnt === 0): ?>
                                            <span class="badge bg-secondary">No imprimió</span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?= $cnt ?> vez<?= $cnt > 1 ? 'es' : '' ?></span>
                                            <?php if ($svc['ultima']): ?>
                                                <br><small class="text-muted">
                                                    <?= date('d/m H:i', strtotime($svc['ultima'])) ?>
                                                </small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>

                                <td class="text-center fw-bold">
                                    <?= $totalPersona > 0
                                        ? '<span class="badge bg-primary">'.$totalPersona.'</span>'
                                        : '<span class="badge bg-warning text-dark">0</span>'
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

        </div><!-- /tabReporte -->

    </div><!-- /tab-content -->

</div>

<!-- MODAL CRUD PERSONA -->
<div class="modal fade" id="modalPersona" tabindex="-1">
    <div class="modal-dialog">
        <form id="formPersona" method="post">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Editar Persona</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="persona_id">
                    <input type="hidden" name="lote_id" value="<?=(int)$lote_id?>">

                    <div class="mb-3">
                        <label>RUT</label>
                        <input type="text" class="form-control" name="guest_rut" id="persona_rut" required>
                    </div>

                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" class="form-control" name="guest_nombre" id="persona_nombre" required>
                    </div>

                    <div class="mb-3">
                        <label>Habitación</label>
                        <input type="text" class="form-control" name="guest_habitacion" id="persona_hab">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>

            </div>
        </form>
    </div>
</div>

<!-- LIBRERÍAS DATATABLES -->
<link rel='stylesheet' href='https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css'>
<script src='https://code.jquery.com/jquery-3.7.1.min.js'></script>
<script src='https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js'></script>
<script src='https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js'></script>

<script>
    $(document).ready(function () {

        // ------------------------------ DataTable personas ------------------------------
        $('#tablaPersonas').DataTable({
            pageLength: 25,
            order: [[2, 'asc']],
            language: {url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'},
        });

        // ------------------------------ DataTable reporte impresiones ------------------------------
        // Se inicializa al mostrar el tab para evitar errores de columnas
        $('button[data-bs-target="#tabReporte"]').one('shown.bs.tab', function () {
            $('#tablaReporte').DataTable({
                pageLength: 50,
                order:      [[0, 'asc']],
                language:   {url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'},
            });
        });

        const modal = new bootstrap.Modal(document.getElementById('modalPersona'));

        // ------------------------------ AGREGAR PERSONA ------------------------------
        $('#btnAgregarPersona').on('click', function () {
            $('#formPersona')[0].reset();
            $('#persona_id').val('');
            $('.modal-title').text('Agregar Persona');
            modal.show();
        });

        // ------------------------------ EDITAR PERSONA ------------------------------
        $('.editarPersona').on('click', function () {
            $('#persona_id').val($(this).data('id'));
            $('#persona_rut').val($(this).data('rut'));
            $('#persona_nombre').val($(this).data('nombre'));
            $('#persona_hab').val($(this).data('hab'));
            $('.modal-title').text('Editar Persona');
            modal.show();
        });

        // ------------------------------ GUARDAR PERSONA ------------------------------
        $('#formPersona').on('submit', function (e) {
            e.preventDefault();

            const datos = $(this).serialize();

            $.post('<?= url('/colaciones/persona/guardar') ?>', datos, function (resp) {

                Swal.fire({
                    icon: 'success',
                    title: 'Guardado correctamente',
                    showConfirmButton: false,
                    timer: 1200
                });

                modal.hide();

                setTimeout(() => location.reload(), 1300);
            });

        });

        // ------------------------------ ELIMINAR PERSONA ------------------------------
        $('.eliminarPersona').on('click', function () {

            const id = $(this).data('id');

            Swal.fire({
                title: '¿Eliminar persona?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {

                if (result.isConfirmed) {

                    $.post('<?= url('/colaciones/persona/eliminar') ?>', {id}, function () {

                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            showConfirmButton: false,
                            timer: 1000
                        });

                        setTimeout(() => location.reload(), 1100);
                    });
                }

            });

        });

    });
</script>
<script>
    $('#formLoteFecha').on('submit', function (e) {
        e.preventDefault();

        $.post('<?= url('/colaciones/lotes/actualizar-fechas') ?>',
            $(this).serialize(),
            function (resp) {

                Swal.fire({
                    icon: 'success',
                    title: 'Fechas del lote actualizadas',
                    timer: 1200,
                    showConfirmButton: false
                });
            }
        );
    });

    $('#formServicios').on('submit', function (e) {
        e.preventDefault();

        $.post('<?= url('/colaciones/lotes/actualizar-servicios') ?>',
            $(this).serialize(),
            function (resp) {
                if (resp.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Servicios actualizados',
                        timer: 1200,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            },
            'json'
        );
    });
</script>

<?php include __DIR__.'/../../includes/footer.php'; ?>