<?php
/**
 * Vista de Alimentación - Atankalama Empresas
 */
Layout::header($title, $user, 'alimentacion');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Historial de Alimentación</h3>
    <a href="<?= BASE_URL ?>export/alimentacion/<?= $days ?>?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" class="btn btn-success">
        <i class="fa-solid fa-file-excel me-2"></i> Exportar este reporte
    </a>
</div>

<!-- Filtros Rápidos y Personalizados -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <div class="row align-items-end g-3">
            <div class="col-lg-5">
                <h6 class="text-muted small text-uppercase mb-2">Periodos Rápidos</h6>
                <div class="btn-group w-100" role="group">
                    <a href="?days=1" class="btn btn-outline-primary filter-btn <?= $days == 1 ? 'active' : '' ?>">Hoy</a>
                    <a href="?days=7" class="btn btn-outline-primary filter-btn <?= $days == 7 ? 'active' : '' ?>">7 días</a>
                    <a href="?days=30" class="btn btn-outline-primary filter-btn <?= $days == 30 ? 'active' : '' ?>">30 días</a>
                    <a href="?days=last_month" class="btn btn-outline-primary filter-btn <?= $days == 'last_month' ? 'active' : '' ?>">Mes Pasado</a>
                </div>
            </div>
            <div class="col-lg-7">
                <form action="" method="GET" class="row g-2 align-items-end">
                    <input type="hidden" name="days" value="custom">
                    <div class="col-md-5">
                        <label class="small text-muted text-uppercase mb-1">Desde</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="<?= $start_date ?>" required>
                    </div>
                    <div class="col-md-5">
                        <label class="small text-muted text-uppercase mb-1">Hasta</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="<?= $end_date ?>" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-filter"></i></button>
                    </div>
                </form>
            </div>
        </div>
        <div class="mt-3 text-end">
            <span class="badge bg-light text-dark border p-2">
                Total registros en este periodo: <?= count($registros) ?>
            </span>
        </div>
    </div>
</div>

<!-- Tabla Detallada -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Servicio</th>
                        <th>Comensal</th>
                        <th>Proyecto</th>
                        <th>RUT</th>
                        <th>Hab.</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($registros)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fa-solid fa-folder-open fa-3x text-light mb-3"></i>
                                <p class="text-muted">No se encontraron consumos en el periodo seleccionado.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($registros as $r): ?>
                            <tr>
                                <td><i class="fa-regular fa-calendar me-2 text-muted"></i><?= !empty($r['fecha']) ? date('d/m/Y', strtotime($r['fecha'])) : '---' ?></td>
                                <td><i class="fa-regular fa-clock me-2 text-muted"></i><?= !empty($r['hora_servicio']) ? date('H:i', strtotime($r['hora_servicio'])) : '---' ?></td>
                                <td>
                                    <span class="badge rounded-pill bg-info text-dark px-3 text-capitalize">
                                        <?= $r['tipo_servicio'] ?>
                                    </span>
                                </td>
                                <td class="fw-medium">
                                    <a href="<?= BASE_URL ?>alimentacion/persona/<?= base64_encode($r['rut']) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($r['nombre_comensal'] ?? 'Sin nombre') ?>
                                        <i class="fa-solid fa-up-right-from-square ms-1 small opacity-50"></i>
                                    </a>
                                </td>
                                <td>
                                    <span class="small text-muted"><?= htmlspecialchars($r['proyecto_nombre'] ?? '---') ?></span>
                                </td>
                                <td><code><?= $r['rut_masked'] ?? '---' ?></code></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($r['habitacion'] ?? '---') ?></span></td>
                                <td>
                                    <?php if($r['cobrado']): ?>
                                        <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Cobrado</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Pendiente</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4 small text-muted">
    <i class="fa-solid fa-circle-info me-1"></i> Mostrando hasta un máximo de 1000 registros por consulta. Para reportes mayores, utilice la función de exportación.
</div>

<?php Layout::footer(); ?>
