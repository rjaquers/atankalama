<!DOCTYPE html>
<html lang='es'>
<head>
    <?php include(ROOT_PATH . '../public/static/templates/head.php'); ?>
</head>
<body class='pro-body'>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

    <div class='container-fluid px-4 py-4'>

        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom"
            style="border-color: var(--color-border) !important;">
            <h2 class="mb-0 fw-bold">
                <i class="bi bi-journal-text me-2" style="color:var(--color-cta)"></i>Listado de Comandas
            </h2>
            <div class="d-flex gap-2">
                <a href="index.php?page=comanda/imprimir&fecha=<?= urlencode($fecha) ?>" target="_blank"
                   class="btn btn-outline-secondary px-3" style="width:auto;" title="Ver versión para imprimir">
                    <i class="bi bi-printer me-1"></i>Imprimir
                </a>
                <a href="index.php?page=comanda/cena&tipo=almuerzo" class="btn btn-pro-action px-3" style="width:auto;">
                    <i class="bi bi-sun-fill me-1"></i>+ Almuerzo
                </a>
                <a href="index.php?page=comanda/cena" class="btn btn-pro-action px-3" style="width:auto;">
                    <i class="bi bi-moon-stars-fill me-1"></i>+ Cena / Colación
                </a>
                <a href="index.php?page=comanda/especial" class="btn btn-pro-action px-3" style="width:auto;">
                    <i class="bi bi-star-fill me-1"></i>+ Especial
                </a>
                <a href="index.php?page=comanda/desayuno" class="btn btn-pro-action px-3" style="width:auto;">
                    <i class="bi bi-sun-fill me-1"></i>+ Desayuno
                </a>
            </div>
        </div>

        <?php if (isset($_GET['ok']) && $_GET['ok'] === 'comanda_editada'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>Comanda actualizada correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Selector de fecha -->
        <div class="pro-card border-0 mb-4">
            <div class="card-body px-4 py-3">
                <form method="GET" action="index.php" class="d-flex align-items-center gap-3">
                    <input type="hidden" name="page" value="comanda/listado">
                    <label class="fw-bold text-muted mb-0">
                        <i class="bi bi-calendar3 me-2 text-primary"></i>Ver fecha:
                    </label>
                    <input type="date" name="fecha" class="form-control border-0 shadow-none" style="max-width:200px;"
                        value="<?= htmlspecialchars($fecha) ?>">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-search me-1"></i>Ver
                    </button>
                    <a href="index.php?page=comanda/listado" class="btn btn-outline-secondary px-3">Hoy</a>
                </form>
            </div>
        </div>

        <?php
        $tipos = [
            'almuerzo'          => ['label' => 'Almuerzos',                'icon' => 'bi-sun-fill',         'color' => 'warning'],
            'cena'              => ['label' => 'Cenas',                    'icon' => 'bi-moon-stars-fill',  'color' => 'primary'],
            'colacion'          => ['label' => 'Colaciones',               'icon' => 'bi-cup-hot-fill',     'color' => 'success'],
            'colacion_especial' => ['label' => 'Colaciones Especiales',    'icon' => 'bi-star-fill',        'color' => 'dark'],
            'desayuno'          => ['label' => 'Desayunos',                'icon' => 'bi-sun-fill',         'color' => 'info'],
        ];

        $porTipo = [];
        foreach ($comandas as $c) {
            $porTipo[$c['tipo_servicio']][] = $c;
        }
        ?>

        <?php if (empty($comandas)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>No hay comandas registradas para
                <strong><?= date('d/m/Y', strtotime($fecha)) ?></strong>.
            </div>
        <?php endif; ?>

        <?php foreach ($tipos as $tipo => $meta): ?>
            <?php if (empty($porTipo[$tipo])): continue; endif; ?>
            
            <div class="mb-5">
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom" style="border-color: var(--color-border) !important;">
                    <h4 class="fw-bold mb-0" style="color:var(--color-primary);">
                        <i class="bi <?= $meta['icon'] ?> me-2 text-<?= $meta['color'] ?>"></i>
                        <?= $meta['label'] ?> — <?= date('d/m/Y', strtotime($fecha)) ?>
                    </h4>
                </div>

                <div class="row g-4">
                    <?php 
                    $hoteles = ['Atankalama', 'Atankalama Inn'];
                    foreach ($hoteles as $hotelIdx => $hotelName): 
                        $filasHotel = array_filter($porTipo[$tipo], fn($c) => $c['nombre_hotel'] === $hotelName);
                        $colorHotel = $hotelName === 'Atankalama' ? 'primary' : 'info';
                        $iconHotel = $hotelName === 'Atankalama' ? 'bi-building' : 'bi-building-fill';
                    ?>
                    <div class="col-xl-6">
                        <div class="pro-card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent py-3 px-4 d-flex justify-content-between align-items-center" 
                                 style="border-bottom:1px solid var(--color-border); border-top: 4px solid <?= $hotelName === 'Atankalama' ? 'var(--color-cta)' : '#06b6d4' ?> !important;">
                                <h5 class="fw-bold mb-0" style="color:var(--color-primary);">
                                    <i class="bi <?= $iconHotel ?> me-2 text-<?= $colorHotel ?>"></i>
                                    <?= strtoupper($hotelName) ?>
                                </h5>
                                <span class="badge bg-<?= $colorHotel ?> rounded-pill px-3 py-2">
                                    <span class="total-pax-hotel"><?= array_sum(array_column($filasHotel, 'cantidad_personas')) ?></span> PAX
                                </span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle table-pro-comandas">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="px-4">Empresa / Solicitante</th>
                                                <th class="text-center">Personas</th>
                                                <th class="text-center">Hora</th>
                                                <th>Observaciones</th>
                                                <th class="text-center">Origen</th>
                                                <th class="text-center px-4">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($filasHotel as $c): ?>
                                            <tr>
                                                <td class="px-4">
                                                    <?php if ($c['tipo_solicitante'] === 'empresa'): ?>
                                                        <span class="fw-semibold"><?= htmlspecialchars($c['nombre_empresa'] ?: ($c['nombre_empresa_oficial'] ?? '—')) ?></span>
                                                        <?php if ($c['nombre_contacto']): ?>
                                                            <br><small class="text-muted"><?= htmlspecialchars($c['nombre_contacto']) ?></small>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($c['nombre_contacto'] ?: 'Particular') ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center fw-bold fs-5"><?= $c['cantidad_personas'] ?></td>
                                                <td class="text-center">
                                                    <?= $c['hora_servicio'] ? substr($c['hora_servicio'], 0, 5) . ' hrs' : '<span class="text-muted">—</span>' ?>
                                                </td>
                                                <td class="text-muted small"><?= htmlspecialchars($c['observaciones'] ?: '—') ?></td>
                                                <td class="text-center">
                                                    <?php if ($c['origen'] === 'urgente'): ?>
                                                        <span class="badge bg-danger"><i class="bi bi-lightning-fill me-1"></i>Urgente</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Programada</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center px-4">
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit-comanda"
                                                                data-bs-toggle="modal" data-bs-target="#modalEditarComanda"
                                                                data-comanda='<?= json_encode($c) ?>' title="Editar comanda">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <a href="index.php?page=voucher/clientes/<?= $c['id'] ?>"
                                                           class="btn btn-sm btn-outline-secondary" title="Gestionar vouchers">
                                                            <i class="bi bi-ticket-perforated"></i>
                                                        </a>
                                                        <?php
                                                            $vTotal    = $c['voucher_count']    ?? 0;
                                                            $vImpresos = $c['voucher_impresos'] ?? 0;
                                                        ?>
                                                        <?php if ($vTotal === 0): ?>
                                                        <span class="btn btn-sm btn-outline-secondary disabled"
                                                              title="Sin vouchers generados">
                                                            <i class="bi bi-printer"></i>
                                                        </span>
                                                        <?php elseif ($vImpresos === 0): ?>
                                                        <a href="index.php?page=voucher/imprimir/<?= $c['id'] ?>"
                                                           class="btn btn-sm btn-outline-warning" target="_blank"
                                                           title="Vouchers pendientes de imprimir (<?= $vTotal ?>)">
                                                            <i class="bi bi-printer-fill"></i>
                                                        </a>
                                                        <?php else: ?>
                                                        <a href="index.php?page=voucher/imprimir/<?= $c['id'] ?>"
                                                           class="btn btn-sm btn-outline-success" target="_blank"
                                                           title="Vouchers impresos (<?= $vImpresos ?>/<?= $vTotal ?>)">
                                                            <i class="bi bi-printer"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                        <form method="POST" action="index.php?page=comanda/eliminar"
                                                            onsubmit="return confirm('¿Eliminar esta comanda?')"
                                                            class="d-inline">
                                                            <input type="hidden" name="id"    value="<?= $c['id'] ?>">
                                                            <input type="hidden" name="fecha" value="<?= htmlspecialchars($fecha) ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="d-none"> <!-- Ocultamos tfoot nativo para usar el badge de arriba -->
                                            <tr>
                                                <th></th>
                                                <th class="total-footer-pax"><?= array_sum(array_column($filasHotel, 'cantidad_personas')) ?></th>
                                                <th colspan="4"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

    </div>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- MODAL: Editar Comanda                                 -->
    <!-- ══════════════════════════════════════════════════════ -->
    <div class="modal fade" id="modalEditarComanda" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Editar Comanda
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?page=voucher/guardarEdicionComanda">
                    <div class="modal-body px-4 py-3">
                        <input type="hidden" name="id" id="edit_comanda_id">
                        <input type="hidden" name="redir" value="comanda/listado&fecha=<?= urlencode($fecha) ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Hotel</label>
                                <select name="nombre_hotel" id="edit_nombre_hotel" class="form-select shadow-none">
                                    <option value="Atankalama">Atankalama</option>
                                    <option value="Atankalama Inn">Atankalama Inn</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Hora de servicio</label>
                                <input type="time" name="hora_servicio" id="edit_hora_servicio" class="form-control shadow-none">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Cantidad de personas</label>
                                <input type="number" name="cantidad_personas" id="edit_cantidad_personas" class="form-control shadow-none" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Para llevar</label>
                                <select name="es_para_llevar" id="edit_es_para_llevar" class="form-select shadow-none">
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Empresa</label>
                                <select name="nombre_empresa" id="edit_nombre_empresa" class="form-select shadow-none">
                                    <option value="">— Particular / Sin empresa —</option>
                                    <?php foreach ($empresasLista as $emp): ?>
                                        <option value="<?= htmlspecialchars($emp['business_name']) ?>">
                                            <?= htmlspecialchars($emp['business_name']) ?>
                                            <?php if (!empty($emp['contact_name'])): ?>
                                                (<?= htmlspecialchars($emp['contact_name']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nombre de contacto</label>
                                <input type="text" name="nombre_contacto" id="edit_nombre_contacto" class="form-control shadow-none" placeholder="Ej: Juan Pérez">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Observaciones</label>
                                <textarea name="observaciones" id="edit_observaciones" class="form-control shadow-none" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i>Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
    /* Estilos personalizados para DataTables */
    .dataTables_wrapper .dataTables_filter {
        padding: 1rem;
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid var(--color-border);
        border-radius: 8px;
        padding: 0.4rem 0.8rem;
        margin-left: 0.5rem;
        outline: none;
    }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 1rem;
    }
    .table-pro-comandas thead th {
        border-bottom: 2px solid var(--color-border);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.025em;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar DataTables
        if ($.fn.DataTable) {
            $('.table-pro-comandas').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "pageLength": 25,
                "order": [[2, "asc"]], // Ordenar por hora por defecto
                "columnDefs": [
                    { "orderable": false, "targets": 5 } // Columna de acciones no ordenable
                ],
                "dom": '<"d-flex justify-content-between align-items-center"f>t<"d-flex justify-content-between align-items-center"ip>',
                "drawCallback": function(settings) {
                    var api = this.api();
                    // Calcular total de la columna 'Personas' (índice 1 en esta nueva estructura)
                    var total = api.column(1, {page:'current'}).data().reduce(function(a, b) {
                        return (parseInt(a) || 0) + (parseInt(b) || 0);
                    }, 0);
                    
                    // Actualizar el footer oculto (opcional, por consistencia)
                    $(api.column(1).footer()).html(total);
                    
                    // Actualizar el badge de PAX en el header de la card correspondiente
                    var card = $(this).closest('.pro-card');
                    card.find('.total-pax-hotel').text(total);
                }
            });
        }

        // Lógica del modal editar
        const modal = document.getElementById('modalEditarComanda');
        const btns  = document.querySelectorAll('.btn-edit-comanda');

        btns.forEach(btn => {
            btn.addEventListener('click', function() {
                const c = JSON.parse(this.getAttribute('data-comanda'));
                
                document.getElementById('edit_comanda_id').value        = c.id;
                document.getElementById('edit_nombre_hotel').value      = c.nombre_hotel;
                document.getElementById('edit_hora_servicio').value     = c.hora_servicio ? c.hora_servicio.substring(0, 5) : '';
                document.getElementById('edit_cantidad_personas').value = c.cantidad_personas;
                document.getElementById('edit_es_para_llevar').value    = c.es_para_llevar;
                document.getElementById('edit_nombre_empresa').value    = c.nombre_empresa || '';
                document.getElementById('edit_nombre_contacto').value   = c.nombre_contacto || '';
                document.getElementById('edit_observaciones').value     = c.observaciones || '';
            });
        });
    });
    </script>

    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>
</body>
</html>
