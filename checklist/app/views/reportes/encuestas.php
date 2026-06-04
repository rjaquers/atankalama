<?php
$checklistSeleccionado = null;
if (!empty($filters['checklist_id'])) {
    foreach ($checklists as $cl) {
        if ($cl['id'] == $filters['checklist_id']) {
            $checklistSeleccionado = $cl;
            break;
        }
    }
}
$totales = $stats['totales'];
?>

<!-- Header -->
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-bold"><i class="bi bi-bar-chart-line me-2"></i> Estadísticas de Encuestas Abiertas</h2>
        <p class="text-muted mb-0">Análisis de respuestas recibidas a través de los formularios públicos.</p>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="<?= BASE_URL ?>/reportes" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-list-ul me-1"></i> Ver Reportes
        </a>
        <a href="<?= BASE_URL ?>/reportes/stats" class="btn btn-outline-secondary btn-sm ms-1">
            <i class="bi bi-graph-up-arrow me-1"></i> Cumplimiento Interno
        </a>
        <?php
        $exportParams = http_build_query(array_filter([
            'checklist_id' => $filters['checklist_id'],
            'startDate'    => $filters['startDate'],
            'endDate'      => $filters['endDate'],
        ]));
        ?>
        <a href="<?= BASE_URL ?>/reportes/encuestas/exportar<?= $exportParams ? '?' . $exportParams : '' ?>"
           class="btn btn-success btn-sm ms-1">
            <i class="bi bi-file-earmark-excel me-1"></i> Exportar Excel
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/reportes/encuestas" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold">Encuesta / Checklist</label>
                <select name="checklist_id" class="form-select">
                    <option value="">Todas las encuestas</option>
                    <?php foreach ($checklists as $cl): ?>
                        <option value="<?= $cl['id'] ?>" <?= ($filters['checklist_id'] == $cl['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cl['nombre']) ?> — <?= htmlspecialchars($cl['area']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Fecha Inicio</label>
                <input type="date" name="startDate" class="form-control" value="<?= htmlspecialchars($filters['startDate']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Fecha Fin</label>
                <input type="date" name="endDate" class="form-control" value="<?= htmlspecialchars($filters['endDate']) ?>">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="bi bi-search me-1"></i> Filtrar
                </button>
                <a href="<?= BASE_URL ?>/reportes/encuestas" class="btn btn-light border">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                    <i class="bi bi-send-check fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Respuestas</div>
                    <div class="fw-bold fs-3"><?= number_format($totales['total_respuestas'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                    <i class="bi bi-link-45deg fs-4 text-success"></i>
                </div>
                <div>
                    <div class="text-muted small">Encuestas Activas</div>
                    <div class="fw-bold fs-3"><?= number_format($totales['total_checklists'] ?? count($checklists)) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                    <i class="bi bi-calendar-check fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="text-muted small">Respuestas Este Mes</div>
                    <div class="fw-bold fs-3"><?= number_format($totales['respuestas_mes'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Tendencia últimos 30 días -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Tendencia de Respuestas (Últimos 30 días)</h5>
            </div>
            <div class="card-body">
                <div style="height: 260px; position: relative;">
                    <canvas id="tendenciaChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen por Encuesta -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Respuestas por Encuesta</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Encuesta</th>
                                <th class="text-center">Respuestas</th>
                                <th>Última</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stats['por_checklist'])): ?>
                                <tr><td colspan="3" class="text-center text-muted py-4">Sin encuestas abiertas</td></tr>
                            <?php else: ?>
                                <?php foreach ($stats['por_checklist'] as $cl): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <a href="?checklist_id=<?= $cl['id'] ?>" class="fw-semibold text-decoration-none">
                                                <?= htmlspecialchars($cl['nombre']) ?>
                                            </a>
                                            <div class="text-muted small"><?= htmlspecialchars($cl['area']) ?></div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary rounded-pill px-3"><?= $cl['total_respuestas'] ?></span>
                                        </td>
                                        <td class="text-muted small">
                                            <?= $cl['ultima_respuesta'] ? date('d/m/Y', strtotime($cl['ultima_respuesta'])) : '—' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Desglose por Pregunta (solo si hay checklist seleccionado) -->
<?php if (!empty($stats['por_pregunta']) && $checklistSeleccionado): ?>
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-list-check me-2"></i>
                Desglose por Pregunta — <?= htmlspecialchars($checklistSeleccionado['nombre']) ?>
            </h5>
        </div>
        <div class="card-body">
            <?php
            $grupoActual = null;
            foreach ($stats['por_pregunta'] as $p):
                if ($p['grupo'] !== $grupoActual):
                    if ($grupoActual !== null) echo '</div>';
                    $grupoActual = $p['grupo'];
                    if ($grupoActual):
            ?>
                <h6 class="fw-bold text-uppercase text-muted mt-4 mb-3 small">
                    <i class="bi bi-folder2 me-1"></i> <?= htmlspecialchars($grupoActual) ?>
                </h6>
                <div>
            <?php   else: ?>
                <div>
            <?php   endif; ?>
            <?php endif; ?>

                <div class="mb-4 p-3 bg-light rounded">
                    <div class="fw-semibold mb-2"><?= htmlspecialchars($p['pregunta']) ?>
                        <span class="badge bg-secondary ms-2 fw-normal" style="font-size:0.7rem;"><?= $p['tipo_respuesta'] ?></span>
                    </div>

                    <?php if ($p['tipo_respuesta'] === 'boolean'): ?>
                        <?php
                            $total = $p['total_si'] + $p['total_no'];
                            $pctSi = $total > 0 ? round($p['total_si'] / $total * 100, 1) : 0;
                            $pctNo = $total > 0 ? round($p['total_no'] / $total * 100, 1) : 0;
                            $color = $pctSi >= 80 ? 'bg-success' : ($pctSi >= 50 ? 'bg-warning' : 'bg-danger');
                        ?>
                        <div class="d-flex align-items-center gap-3">
                            <div class="flex-fill">
                                <div class="d-flex justify-content-between mb-1 small">
                                    <span class="text-success fw-bold">Sí: <?= $p['total_si'] ?> (<?= $pctSi ?>%)</span>
                                    <span class="text-danger fw-bold">No: <?= $p['total_no'] ?> (<?= $pctNo ?>%)</span>
                                </div>
                                <div class="progress" style="height:10px;border-radius:5px;">
                                    <div class="progress-bar <?= $color ?>" style="width:<?= $pctSi ?>%"></div>
                                </div>
                            </div>
                            <div class="text-muted small"><?= $total ?> resp.</div>
                        </div>

                    <?php elseif ($p['tipo_respuesta'] === 'numeric_scale'): ?>
                        <div class="d-flex gap-4 flex-wrap">
                            <div class="text-center">
                                <div class="fs-4 fw-bold text-primary"><?= $p['promedio_num'] !== null ? round($p['promedio_num'], 1) : '—' ?></div>
                                <div class="text-muted small">Promedio</div>
                            </div>
                            <div class="text-center">
                                <div class="fs-4 fw-bold text-success"><?= $p['max_num'] ?? '—' ?></div>
                                <div class="text-muted small">Máximo</div>
                            </div>
                            <div class="text-center">
                                <div class="fs-4 fw-bold text-danger"><?= $p['min_num'] ?? '—' ?></div>
                                <div class="text-muted small">Mínimo</div>
                            </div>
                            <div class="text-center">
                                <div class="fs-4 fw-bold text-secondary"><?= $p['total'] ?></div>
                                <div class="text-muted small">Respuestas</div>
                            </div>
                        </div>

                    <?php elseif ($p['tipo_respuesta'] === 'text'): ?>
                        <?php if (!empty($p['respuestas_texto'])): ?>
                            <div class="mt-2" style="max-height:200px;overflow-y:auto;">
                                <?php foreach ($p['respuestas_texto'] as $rt): ?>
                                    <div class="border-start border-3 border-primary ps-3 mb-2">
                                        <div class="small"><?= htmlspecialchars($rt['texto']) ?></div>
                                        <div class="text-muted" style="font-size:0.7rem;"><?= date('d/m/Y H:i', strtotime($rt['fecha'])) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">Sin respuestas de texto aún.</span>
                        <?php endif; ?>

                    <?php elseif ($p['tipo_respuesta'] === 'photo'): ?>
                        <span class="text-muted small"><i class="bi bi-camera me-1"></i><?= $p['total'] ?> foto(s) recibida(s).</span>

                    <?php else: ?>
                        <span class="text-muted small"><?= $p['total'] ?> respuesta(s).</span>
                    <?php endif; ?>
                </div>

            <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php elseif (!empty($filters['checklist_id']) && empty($stats['por_pregunta'])): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i> Esta encuesta aún no tiene respuestas.
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tendenciaData = <?php
        $dias = [];
        $valores = [];
        // Llenar los últimos 30 días
        $mapa = [];
        foreach ($stats['tendencia'] as $row) {
            $mapa[$row['dia']] = (int)$row['total'];
        }
        for ($i = 29; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $dias[] = date('d/m', strtotime($d));
            $valores[] = $mapa[$d] ?? 0;
        }
        echo json_encode(['labels' => $dias, 'data' => $valores]);
    ?>;

    const ctx = document.getElementById('tendenciaChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: tendenciaData.labels,
                datasets: [{
                    label: 'Respuestas',
                    data: tendenciaData.data,
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.08)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            maxTicksLimit: 10,
                            maxRotation: 0
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        padding: 10,
                        cornerRadius: 8
                    }
                }
            }
        });
    }
});
</script>
