<?php
/**
 * Vista de Servicios - Atankalama Empresas
 */
Layout::header($title, $user, 'servicios');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Historial de Servicios</h3>
    <!-- <button class="btn btn-success"><i class="fa-solid fa-file-excel me-2"></i> Exportar</button> -->
</div>

<!-- Filtros Rápidos -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h6 class="text-muted small text-uppercase mb-2">Filtrar por periodo</h6>
                <div class="btn-group" role="group">
                    <a href="?days=1" class="btn btn-outline-primary filter-btn <?= $days == 1 ? 'active' : '' ?>">Hoy</a>
                    <a href="?days=4" class="btn btn-outline-primary filter-btn <?= $days == 4 ? 'active' : '' ?>">4 días</a>
                    <a href="?days=7" class="btn btn-outline-primary filter-btn <?= $days == 7 ? 'active' : '' ?>">7 días</a>
                    <a href="?days=30" class="btn btn-outline-primary filter-btn <?= $days == 30 ? 'active' : '' ?>">30 días</a>
                    <a href="?days=last_month" class="btn btn-outline-primary filter-btn <?= $days == 'last_month' ? 'active' : '' ?>">Mes Pasado</a>
                </div>
            </div>
            <div class="text-end">
                <span class="badge bg-light text-dark border p-2">
                    Total registros: <?= count($servicios) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Servicios -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha / Hora</th>
                        <th>Huésped</th>
                        <th>Habitación</th>
                        <th>Lugar</th>
                        <th>Estado</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($servicios)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fa-solid fa-bell-slash fa-3x text-light mb-3"></i>
                                <p class="text-muted">No se encontraron servicios en el periodo seleccionado.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($servicios as $s): ?>
                            <tr>
                                <td>
                                    <div><?= date('d/m/Y', strtotime($s['fecha_hora'])) ?></div>
                                    <small class="text-muted"><?= date('H:i', strtotime($s['fecha_hora'])) ?> hrs</small>
                                </td>
                                <td class="fw-medium"><?= htmlspecialchars($s['nombre_huesped'] ?? 'Particular') ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($s['habitacion']) ?></span></td>
                                <td><?= htmlspecialchars($s['lugar']) ?></td>
                                <td>
                                    <?php if($s['estado'] == 'cerrada'): ?>
                                        <span class="badge bg-success">Cerrada</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold">$<?= number_format($s['total'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php Layout::footer(); ?>
