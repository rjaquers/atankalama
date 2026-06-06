<?php
/**
 * Vista Detalle por Persona - Atankalama Empresas
 */
Layout::header($title, $user, 'alimentacion');
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>alimentacion" class="text-decoration-none">Alimentación</a></li>
            <li class="breadcrumb-item active">Historial Personal</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-0"><?= htmlspecialchars($nombre) ?></h3>
            <p class="text-muted mb-0">RUT: <?= $rut ?></p>
        </div>
        <div class="text-end">
            <div class="card bg-primary text-white shadow-sm border-0">
                <div class="card-body py-2 px-4">
                    <small class="text-white-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Total Servicios</small>
                    <h4 class="mb-0 fw-bold"><?= count($registros) ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Historial Personal -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-list-check me-2"></i>Desglose de consumos históricos</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Fecha</th>
                        <th>Hora</th>
                        <th>Tipo de Servicio</th>
                        <th>Habitación</th>
                        <th class="pe-4 text-end">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($registros as $r): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold"><?= date('d/m/Y', strtotime($r['fecha'])) ?></span>
                            </td>
                            <td><?= date('H:i', strtotime($r['hora_servicio'])) ?> hrs</td>
                            <td>
                                <span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle text-capitalize px-3">
                                    <?= $r['tipo_servicio'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($r['habitacion'] ?? '---') ?></span>
                            </td>
                            <td class="pe-4 text-end">
                                <?php if($r['cobrado']): ?>
                                    <span class="text-success small fw-bold"><i class="fa-solid fa-check-circle me-1"></i> Cobrado</span>
                                <?php else: ?>
                                    <span class="text-warning small fw-bold"><i class="fa-solid fa-clock me-1"></i> Pendiente</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4 d-flex justify-content-between align-items-center">
    <a href="<?= BASE_URL ?>alimentacion" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-2"></i> Volver al listado general
    </a>
    <small class="text-muted italic">* El historial incluye todos los registros asociados a este RUT en el sistema.</small>
</div>

<?php Layout::footer(); ?>
