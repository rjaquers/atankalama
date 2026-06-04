<div class="mb-4">
    <h2 class="fw-bold">Bienvenido, <?= htmlspecialchars(explode('@', \AccesoBootstrap::email() ?? '')[0]) ?></h2>
    <p class="text-muted">Resumen del sistema de cumplimiento hotelero.</p>
</div>

<!-- KPI Cards -->
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-4 border-start border-4 border-primary">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase small ls-1 mb-1">Checklists Activos</h6>
                        <h2 class="mb-0 fw-bold"><?= $stats['total_checklists'] ?></h2>
                    </div>
                    <div class="bg-primary-subtle p-3 rounded-circle">
                        <i class="bi bi-list-check text-primary h4 mb-0"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-4 border-start border-4 border-success">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase small ls-1 mb-1">Evaluaciones Realizadas</h6>
                        <h2 class="mb-0 fw-bold"><?= $stats['total_evaluaciones'] ?></h2>
                    </div>
                    <div class="bg-success-subtle p-3 rounded-circle">
                        <i class="bi bi-clipboard-data text-success h4 mb-0"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-4 border-start border-4 border-info">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase small ls-1 mb-1">Tu Actividad</h6>
                        <h2 class="mb-0 fw-bold"><?= count($stats['recientes']) ?></h2>
                    </div>
                    <div class="bg-info-subtle p-3 rounded-circle">
                        <i class="bi bi-person-check text-info h4 mb-0"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Acciones Rápidas</h5>
                <div class="d-grid gap-3">
                    <a href="<?= BASE_URL ?>/evaluaciones" class="btn btn-primary py-3 rounded-4 shadow-sm">
                        <i class="bi bi-play-circle me-2"></i> Iniciar Evaluación
                    </a>
                    <a href="<?= BASE_URL ?>/checklists/nuevo" class="btn btn-outline-secondary py-3 rounded-4">
                        <i class="bi bi-plus-lg me-2"></i> Crear Nuevo Checklist
                    </a>
                    <a href="<?= BASE_URL ?>/reportes" class="btn btn-outline-secondary py-3 rounded-4">
                        <i class="bi bi-bar-chart me-2"></i> Ver Reportes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Actividad Reciente</h5>
                <?php if (empty($stats['recientes'])): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-clock-history text-muted display-4 mb-3 d-block"></i>
                        <p class="text-muted">No hay evaluaciones registradas recientemente.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th>Evaluado</th>
                                    <th>Checklist</th>
                                    <th>Fecha</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['recientes'] as $row): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">
                                                <?= htmlspecialchars($row['evaluado_nombre'] . ' ' . $row['evaluado_apellido']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-light text-dark border"><?= htmlspecialchars($row['checklist_nombre']) ?></span>
                                        </td>
                                        <td>
                                            <small
                                                class="text-muted"><?= date('d/m/Y H:i', strtotime($row['fecha_evaluacion'])) ?></small>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>/reportes/ver?id=<?= $row['id'] ?>"
                                                class="btn btn-sm btn-light border">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .ls-1 {
        letter-spacing: 1px;
    }

    .bg-primary-subtle {
        background-color: #e7f1ff;
    }

    .bg-success-subtle {
        background-color: #e6fcf5;
    }

    .bg-info-subtle {
        background-color: #e3f9fb;
    }
</style>