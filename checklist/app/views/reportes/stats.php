<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold"><i class="bi bi-graph-up-arrow me-2"></i> Dashboard de Cumplimiento</h2>
        <p class="text-muted">Análisis agregado de desempeño por área, personal y periodos.</p>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="<?= BASE_URL ?>/reportes" class="btn btn-outline-secondary">
            <i class="bi bi-list-ul me-2"></i> Ver Todos los Reportes
        </a>
    </div>
</div>

<!-- Resumen Mensual Ejecutivo -->
<?php
$meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$hotelAtank     = $monthlySummary['por_hotel']['Atankalama']     ?? 0;
$hotelInn       = $monthlySummary['por_hotel']['Atankalama Inn'] ?? 0;
$totalControles = $monthlySummary['total_controles'];
$totalDefectos  = $monthlySummary['total_defectos'];
$pctDefectos    = $totalControles > 0 ? round(($totalDefectos / $totalControles) * 100, 1) : 0;
$cumplimiento   = $totalControles > 0 ? round(100 - $pctDefectos, 1) : 0;
$barColor       = $cumplimiento >= 80 ? 'bg-success' : ($cumplimiento >= 50 ? 'bg-warning' : 'bg-danger');
$badgeColor     = $cumplimiento >= 80 ? 'bg-success' : ($cumplimiento >= 50 ? 'bg-warning' : 'bg-danger');
?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">Resumen Mensual</h5>
            <form method="GET" action="<?= BASE_URL ?>/reportes/stats" class="d-flex gap-2 align-items-center mb-0">
                <select name="summary_month" class="form-select form-select-sm" style="width:130px;" onchange="this.form.submit()">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m === $summaryMonth ? 'selected' : '' ?>><?= $meses[$m] ?></option>
                    <?php endfor; ?>
                </select>
                <select name="summary_year" class="form-select form-select-sm" style="width:90px;" onchange="this.form.submit()">
                    <?php for ($y = date('Y'); $y >= 2026; $y--): ?>
                        <option value="<?= $y ?>" <?= $y === $summaryYear ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
        <div class="row g-4">
            <!-- Controles -->
            <div class="col-md-4">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="fw-semibold">Controles Realizados</span>
                    <span class="h4 fw-bold mb-0"><?= $totalControles ?></span>
                </div>
                <div class="progress mb-2" style="height:6px; border-radius:3px;">
                    <div class="progress-bar bg-primary" style="width:100%"></div>
                </div>
                <div class="d-flex gap-4">
                    <div>
                        <div class="small text-muted">Atankalama</div>
                        <div class="fw-bold"><?= $hotelAtank ?></div>
                    </div>
                    <div>
                        <div class="small text-muted">Atankalama Inn</div>
                        <div class="fw-bold"><?= $hotelInn ?></div>
                    </div>
                </div>
            </div>
            <!-- Defectos -->
            <div class="col-md-4">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="fw-semibold">Defectos Levantados</span>
                    <span class="h4 fw-bold mb-0 <?= $totalDefectos > 0 ? 'text-danger' : 'text-success' ?>"><?= $totalDefectos ?></span>
                </div>
                <div class="progress mb-2" style="height:6px; border-radius:3px;">
                    <div class="progress-bar bg-danger" style="width:<?= $totalControles > 0 ? min(100, round($totalDefectos / $totalControles * 100)) : 0 ?>%"></div>
                </div>
                <small class="text-muted"><?= $pctDefectos ?>% de respuestas "No" sobre el total</small>
            </div>
            <!-- Cumplimiento -->
            <div class="col-md-4">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="fw-semibold">Tasa de Cumplimiento</span>
                    <span class="badge <?= $badgeColor ?> rounded-pill px-3 py-2 fs-6"><?= $cumplimiento ?>%</span>
                </div>
                <div class="progress mb-2" style="height:6px; border-radius:3px;">
                    <div class="progress-bar <?= $barColor ?>" style="width:<?= $cumplimiento ?>%"></div>
                </div>
                <small class="text-muted"><?= $meses[$summaryMonth] ?> <?= $summaryYear ?></small>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <!-- Tabs de tipo -->
        <div class="d-flex gap-2 mb-4">
            <?php
            $tipoActual = $filters['tipo'] ?? '';
            $tipoBase = http_build_query(array_filter(array_diff_key($filters, ['tipo' => ''])));
            ?>
            <a href="?<?= $tipoBase ?>&tipo=" class="btn btn-sm <?= $tipoActual === '' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-grid me-1"></i> Todos
            </a>
            <a href="?<?= $tipoBase ?>&tipo=personas" class="btn btn-sm <?= $tipoActual === 'personas' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-person me-1"></i> Personas
            </a>
            <a href="?<?= $tipoBase ?>&tipo=habitaciones" class="btn btn-sm <?= $tipoActual === 'habitaciones' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-door-open me-1"></i> Habitaciones
            </a>
        </div>
        <form method="GET" action="<?= BASE_URL ?>/reportes/stats" class="row g-3 align-items-end">
        <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipoActual) ?>">
            <div class="col-md-2">
                <label class="form-label fw-bold">Fecha Inicio</label>
                <input type="date" name="startDate" class="form-control"
                    value="<?= htmlspecialchars($filters['startDate'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Fecha Fin</label>
                <input type="date" name="endDate" class="form-control"
                    value="<?= htmlspecialchars($filters['endDate'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Hotel</label>
                <select name="hotel" class="form-select">
                    <option value="">Todos</option>
                    <option value="Atankalama" <?= ($filters['hotel'] ?? '') == 'Atankalama' ? 'selected' : '' ?>>
                        Atankalama</option>
                    <option value="Atankalama Inn" <?= ($filters['hotel'] ?? '') == 'Atankalama Inn' ? 'selected' : '' ?>>
                        Atankalama Inn</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Área</label>
                <select name="area" class="form-select">
                    <option value="">Todas las Áreas</option>
                    <?php foreach ($areas as $a): ?>
                        <option value="<?= htmlspecialchars($a['nombre']) ?>" <?= ($filters['area'] ?? '') == $a['nombre'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Personal</label>
                <input type="text" name="persona" class="form-control" placeholder="Nombre o apellido..."
                    value="<?= htmlspecialchars($filters['persona'] ?? '') ?>">
            </div>
            <div class="col-12 text-end mt-3">
                <button type="submit" name="export" value="1" class="btn btn-success me-2">
                    <i class="bi bi-file-earmark-excel me-1"></i> Exportar a Excel
                </button>
                <a href="<?= BASE_URL ?>/reportes/stats" class="btn btn-light border me-2">
                    <i class="bi bi-x-circle me-1"></i> Limpiar
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-search me-1"></i> Filtrar Resultados
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Cumplimiento por Área -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Cumplimiento por Área</h5>
            </div>
            <div class="card-body">
                <?php foreach ($stats['por_area'] as $area):
                    $pct = ($area['total_si'] + $area['total_no']) > 0 ? round(($area['total_si'] / ($area['total_si'] + $area['total_no'])) * 100, 1) : 0;
                    $color = $pct >= 80 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger');
                    ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold">
                                <?= htmlspecialchars($area['area']) ?>
                            </span>
                            <span class="fw-bold">
                                <?= $pct ?>%
                            </span>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 6px;">
                            <div class="progress-bar <?= $color ?>" role="progressbar" style="width: <?= $pct ?>%"></div>
                        </div>
                        <small class="text-muted">
                            <?= $area['total_si'] ?> Sí /
                            <?= $area['total_no'] ?> No (Total:
                            <?= $area['total_respuestas'] ?>)
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Cumplimiento por Mes (Tendencia) -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Tendencia Mensual</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Mes</th>
                                <th>Reportes</th>
                                <th>Cumplimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['por_mes'] as $mes):
                                $pct = ($mes['total_si'] + $mes['total_no']) > 0 ? round(($mes['total_si'] / ($mes['total_si'] + $mes['total_no'])) * 100, 1) : 0;
                                $badge = $pct >= 80 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger');
                                $mesNombre = date('F Y', strtotime($mes['mes'] . '-01'));
                                ?>
                                <tr>
                                    <td class="fw-semibold">
                                        <?= $mesNombre ?>
                                    </td>
                                    <td>
                                        <?= $mes['total_evaluaciones'] ?> reportes
                                    </td>
                                    <td>
                                        <span class="badge <?= $badge ?> rounded-pill px-3 py-2">
                                            <?= $pct ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Checklists Creados por Tipo -->

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Checklists Creados por Tipo</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center">
                        <div style="height: 250px; position: relative;">
                            <canvas id="tipoChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6 mt-4 mt-md-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <tbody>
                                    <?php
                                    $colores = ['#4361ee', '#2ecc71', '#f39c12', '#e74c3c', '#9b59b6', '#34495e', '#1abc9c', '#3498db'];
                                    $labelsCount = [];
                                    $dataCount = [];
                                    foreach ($stats['por_tipo'] as $i => $tipo):
                                        $color = $colores[$i % count($colores)];
                                        $labelsCount[] = '"' . addslashes($tipo['checklist_nombre']) . '"';
                                        $dataCount[] = $tipo['total_creados'];
                                        ?>
                                        <tr>
                                            <td>
                                                <i class="bi bi-circle-fill me-2"
                                                    style="color: <?= $color ?>; font-size: 0.6rem;"></i>
                                                <span
                                                    class="fw-semibold text-dark"><?= htmlspecialchars($tipo['checklist_nombre']) ?></span>
                                            </td>
                                            <td class="text-end fw-bold fs-5 text-primary">
                                                <?= $tipo['total_creados'] ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- Cumplimiento por Persona -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">
                    <?= ($filters['tipo'] ?? '') === 'habitaciones' ? 'Desempeño por Habitación (Top 10)' : 'Desempeño por Personal (Top 10)' ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-striped mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4"><?= ($filters['tipo'] ?? '') === 'habitaciones' ? 'Hotel / Habitación' : 'Persona Evaluada' ?></th>
                                <th>SÍ</th>
                                <th>NO</th>
                                <th>Total Respuestas</th>
                                <th>Nivel de Cumplimiento</th>
                                <th style="width: 200px;">Visual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['por_persona'] as $p):
                                $pct = ($p['total_si'] + $p['total_no']) > 0 ? round(($p['total_si'] / ($p['total_si'] + $p['total_no'])) * 100, 1) : 0;
                                $color = $pct >= 80 ? 'text-success' : ($pct >= 50 ? 'text-warning' : 'text-danger');
                                $barBg = $pct >= 80 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger');
                                ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3 bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                                <?= ($filters['tipo'] ?? '') === 'habitaciones' ? '<i class="bi bi-door-open" style="font-size:0.85rem"></i>' : substr($p['evaluado_nombre'], 0, 1) ?>
                                            </div>
                                            <div class="fw-bold">
                                                <?php if (($filters['tipo'] ?? '') === 'habitaciones'): ?>
                                                    <?= htmlspecialchars($p['evaluado_nombre']) ?> &mdash; Hab. <?= htmlspecialchars($p['evaluado_apellido']) ?>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($p['evaluado_nombre'] . ' ' . $p['evaluado_apellido']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="text-success">
                                            <?= $p['total_si'] ?>
                                        </span></td>
                                    <td><span class="text-danger">
                                            <?= $p['total_no'] ?>
                                        </span></td>
                                    <td>
                                        <?= $p['total_respuestas'] ?>
                                    </td>
                                    <td>
                                        <span class="h5 mb-0 fw-bold <?= $color ?>">
                                            <?= $pct ?>%
                                        </span>
                                    </td>
                                    <td class="pe-4">
                                        <div class="progress" style="height: 8px; border-radius: 4px;">
                                            <div class="progress-bar <?= $barBg ?>" role="progressbar"
                                                style="width: <?= $pct ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Detalle por Evaluación -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Detalle de Evaluaciones</h5>
                <small class="text-muted">
                    <?= $stats['total_evaluaciones'] ?> evaluaciones en total
                </small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Fecha</th>
                                <th>Persona Evaluada</th>
                                <th>Checklist</th>
                                <th>SÍ</th>
                                <th>NO</th>
                                <th>% Cumplimiento</th>
                                <th class="pe-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['por_evaluacion'] as $eva):
                                $pct = ($eva['total_si'] + $eva['total_no']) > 0 ? round(($eva['total_si'] / ($eva['total_si'] + $eva['total_no'])) * 100, 1) : 0;
                                $badge = $pct >= 80 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger');
                                ?>
                                <tr>
                                    <td class="ps-4 py-3 fw-bold text-primary">
                                        #<?= $eva['evaluacion_id'] ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y H:i', strtotime($eva['fecha_evaluacion'])) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($eva['evaluado_nombre'] . ' ' . $eva['evaluado_apellido']) ?>
                                    </td>
                                    <td class="text-muted">
                                        <?= htmlspecialchars($eva['checklist_nombre']) ?>
                                    </td>
                                    <td><span class="text-success"><?= $eva['total_si'] ?></span></td>
                                    <td><span class="text-danger"><?= $eva['total_no'] ?></span></td>
                                    <td>
                                        <span class="badge <?= $badge ?> rounded-pill">
                                            <?= $pct ?>%
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <a href="<?= BASE_URL ?>/reportes/view?id=<?= $eva['evaluacion_id'] ?>"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($totalPages > 1): ?>
            <?php
                $queryParams = array_filter($filters);
                $queryParams['page'] = null;
                $baseQuery = http_build_query(array_filter($queryParams));
                $baseQuery = $baseQuery ? $baseQuery . '&page=' : 'page=';
            ?>
            <div class="card-footer bg-white border-top py-3">
                <nav>
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= $baseQuery . ($page - 1) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <?php if ($p === 1 || $p === $totalPages || abs($p - $page) <= 2): ?>
                                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= $baseQuery . $p ?>"><?= $p ?></a>
                                </li>
                            <?php elseif (abs($p - $page) === 3): ?>
                                <li class="page-item disabled"><span class="page-link">…</span></li>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= $baseQuery . ($page + 1) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<style>
    .avatar-sm {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }

    .bg-primary-subtle {
        background-color: #cfe2ff;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('tipoChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: [<?= implode(',', $labelsCount ?? []) ?>],
                    datasets: [{
                        data: [<?= implode(',', $dataCount ?? []) ?>],
                        backgroundColor: ['#4361ee', '#2ecc71', '#f39c12', '#e74c3c', '#9b59b6', '#34495e', '#1abc9c', '#3498db'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            padding: 12,
                            cornerRadius: 8,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)'
                        }
                    }
                }
            });
        }
    });
</script>