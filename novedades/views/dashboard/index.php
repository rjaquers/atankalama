<?php include __DIR__ . '/../layout.php'; ?>

<div class="container mt-4">

    <!-- ── Encabezado ──────────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-speedometer2"></i> Panel de Control (KPIs)</h3>
        <a href="index.php?route=novedades/form" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nueva Novedad
        </a>
    </div>

    <!-- ── Filtros ─────────────────────────────────────────────────── -->
    <div class="card shadow-sm mb-4">
        <div class="card-body bg-light">
            <!-- Botones de tramo rápido -->
            <div class="mb-3 d-flex flex-wrap gap-2">
                <span class="text-muted small align-self-center me-1">Acceso rápido:</span>
                <a href="index.php?route=dashboard&tramo=7d"
                   class="btn btn-sm <?= ($tramo === '7d')        ? 'btn-dark'           : 'btn-outline-secondary' ?>">
                    Últimos 7 días
                </a>
                <a href="index.php?route=dashboard&tramo=10d"
                   class="btn btn-sm <?= ($tramo === '10d')       ? 'btn-dark'           : 'btn-outline-secondary' ?>">
                    Últimos 10 días
                </a>
                <a href="index.php?route=dashboard"
                   class="btn btn-sm <?= ($tramo === '' && !isset($_GET['start'])) ? 'btn-dark' : 'btn-outline-secondary' ?>">
                    Este mes
                </a>
                <a href="index.php?route=dashboard&tramo=mes-pasado"
                   class="btn btn-sm <?= ($tramo === 'mes-pasado') ? 'btn-dark'           : 'btn-outline-secondary' ?>">
                    Mes pasado
                </a>
            </div>

            <!-- Filtro manual por rango -->
            <form action="index.php" method="GET" class="row g-3 align-items-center">
                <input type="hidden" name="route" value="dashboard">
                <div class="col-auto">
                    <label for="start" class="col-form-label"><i class="bi bi-calendar3"></i> Desde:</label>
                </div>
                <div class="col-auto">
                    <input type="date" id="start" name="start" class="form-control"
                           value="<?= htmlspecialchars($start) ?>" required>
                </div>
                <div class="col-auto">
                    <label for="end" class="col-form-label"><i class="bi bi-calendar3"></i> Hasta:</label>
                </div>
                <div class="col-auto">
                    <input type="date" id="end" name="end" class="form-control"
                           value="<?= htmlspecialchars($end) ?>" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                </div>
            </form>

            <div class="mt-2 text-muted small">
                <i class="bi bi-calendar-range"></i>
                Mostrando del <strong><?= date('d/m/Y', strtotime($start)) ?></strong>
                al <strong><?= date('d/m/Y', strtotime($end)) ?></strong>
            </div>
        </div>
    </div>

    <!-- ── KPI Cards ───────────────────────────────────────────────── -->
    <div class="row mb-4 g-3 kpi-row">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title text-uppercase text-white-50 small">Total Novedades</h6>
                    <h2 class="display-5 fw-bold mb-0"><?= number_format($total) ?></h2>
                    <div class="mt-2 small"><i class="bi bi-calendar-check"></i> En el período</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title text-uppercase small" style="opacity:.7;">Seguimientos Pendientes</h6>
                    <h2 class="display-5 fw-bold mb-0"><?= number_format($pendientes) ?></h2>
                    <div class="mt-2 small"><i class="bi bi-exclamation-triangle"></i> Requieren atención</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title text-uppercase text-white-50 small">Novedades Críticas</h6>
                    <h2 class="display-5 fw-bold mb-0"><?= number_format($criticas) ?></h2>
                    <div class="mt-2 small">
                        <i class="bi bi-lightning-fill"></i> Importancia ≥ 8
                        <?php if ($total > 0): ?>
                            &nbsp;(<?= round($criticas / $total * 100) ?>%)
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark text-white shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title text-uppercase text-white-50 small">Más Activo</h6>
                    <?php if (!empty($topRegistradores)): ?>
                        <h5 class="fw-bold mb-0 mt-1"><?= htmlspecialchars($topRegistradores[0]['nombre']) ?></h5>
                        <div class="mt-2 small">
                            <i class="bi bi-person-fill-check"></i>
                            <?= $topRegistradores[0]['total'] ?> novedades registradas
                        </div>
                    <?php else: ?>
                        <h5 class="fw-bold mb-0 mt-1 text-muted">—</h5>
                        <div class="mt-2 small">Sin registros</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Estadísticas por Hotel ──────────────────────────────────── -->
    <?php if (!empty($estadisticasPorHotel)): ?>
    <div class="mb-4">
        <h5 class="fw-semibold mb-3"><i class="bi bi-building"></i> Desglose por Hotel</h5>
        <div class="row g-3">
            <?php
            $hotelColors = ['#0d6efd', '#dc3545', '#198754', '#ffc107'];
            foreach ($estadisticasPorHotel as $idx => $h):
                $pct    = $total > 0 ? round($h['total'] / $total * 100) : 0;
                $topAreas = $topAreasPorHotel[$h['hotel']] ?? [];
            ?>
            <div class="col-md-6">
                <div class="card shadow-sm h-100" style="border-left: 4px solid <?= $hotelColors[$idx % count($hotelColors)] ?>;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="card-title fw-bold mb-0">
                                    <i class="bi bi-building-fill me-1"></i>
                                    <?= htmlspecialchars($h['hotel']) ?>
                                </h6>
                                <span class="text-muted small"><?= $pct ?>% del total del período</span>
                            </div>
                            <span class="badge fs-5 text-white" style="background:<?= $hotelColors[$idx % count($hotelColors)] ?>;">
                                <?= number_format($h['total']) ?>
                            </span>
                        </div>

                        <!-- Mini stats -->
                        <div class="row g-2 mb-3">
                            <div class="col-4 text-center">
                                <div class="border rounded p-2">
                                    <div class="fs-5 fw-bold text-danger"><?= $h['criticas'] ?></div>
                                    <div class="small text-muted">Críticas</div>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="border rounded p-2">
                                    <div class="fs-5 fw-bold text-warning"><?= $h['pendientes'] ?></div>
                                    <div class="small text-muted">Pendientes</div>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="border rounded p-2">
                                    <div class="fs-5 fw-bold text-info"><?= $h['promedio_importancia'] ?></div>
                                    <div class="small text-muted">Prom. nivel</div>
                                </div>
                            </div>
                        </div>

                        <!-- Top áreas del hotel -->
                        <?php if (!empty($topAreas)): ?>
                        <div class="small text-muted mb-1">Top áreas con más novedades:</div>
                        <?php foreach ($topAreas as $ta): ?>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-capitalize"><?= htmlspecialchars($ta['area']) ?></span>
                                <div class="d-flex align-items-center gap-2" style="min-width:100px;">
                                    <div class="progress flex-grow-1" style="height:6px;">
                                        <div class="progress-bar" style="width:<?= $h['total'] > 0 ? round($ta['total']/$h['total']*100) : 0 ?>%;background:<?= $hotelColors[$idx % count($hotelColors)] ?>"></div>
                                    </div>
                                    <span class="badge bg-secondary small"><?= $ta['total'] ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Gráficos comparativos por hotel (fila única) ───────────── -->
    <?php
        $hotelColors   = ['#0d6efd', '#dc3545', '#198754', '#ffc107'];
        $hotelesLegend = array_column($estadisticasPorHotel, 'hotel');

        // Función helper para generar los badges de leyenda
        $legendaBadges = function() use ($hotelesLegend, $hotelColors) {
            foreach ($hotelesLegend as $i => $h) {
                echo '<span class="badge ms-2" style="background:' . $hotelColors[$i % count($hotelColors)] . ';">'
                    . htmlspecialchars($h) . '</span>';
            }
        };
    ?>

    <div class="row mb-4 g-3">

        <!-- Departamentos involucrados -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-tag-fill me-1 text-primary"></i> Por Departamento
                    <?php $legendaBadges(); ?>
                </div>
                <div class="card-body" style="position:relative;height:300px;">
                    <canvas id="tipoChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Áreas -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-geo-alt-fill me-1 text-primary"></i> Top Áreas
                    <?php $legendaBadges(); ?>
                </div>
                <div class="card-body" style="position:relative;height:300px;">
                    <canvas id="areaChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Nivel de Criticidad -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-thermometer-half me-1 text-primary"></i> Criticidad
                    <?php $legendaBadges(); ?>
                </div>
                <div class="card-body" style="position:relative;height:300px;">
                    <canvas id="criticidadChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    <!-- ── Top Registradores + Recientes ───────────────────────────── -->
    <div class="row mb-4 g-3">

        <!-- Top usuarios -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-trophy-fill me-1 text-warning"></i> Ranking de Registros
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Colaborador</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($topRegistradores)): ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">Sin datos</td></tr>
                            <?php else: ?>
                                <?php foreach ($topRegistradores as $i => $reg): ?>
                                <?php
                                    $medals = ['🥇','🥈','🥉','4','5'];
                                    $medal  = $medals[$i] ?? ($i + 1);
                                    $maxVal = $topRegistradores[0]['total'];
                                    $pctBar = $maxVal > 0 ? round($reg['total'] / $maxVal * 100) : 0;
                                ?>
                                <tr>
                                    <td class="fw-bold"><?= $medal ?></td>
                                    <td>
                                        <div><?= htmlspecialchars($reg['nombre']) ?></div>
                                        <div class="progress mt-1" style="height:4px;">
                                            <div class="progress-bar bg-warning" style="width:<?= $pctBar ?>%"></div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-secondary"><?= $reg['total'] ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Últimas novedades -->
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-clock-history me-1"></i> Últimas 5 Novedades
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Hotel</th>
                                    <th>Área</th>
                                    <th>Departamento</th>
                                    <th>Nivel</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recientes)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-3">No hay novedades</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recientes as $r): ?>
                                    <tr>
                                        <td>#<?= $r['id'] ?></td>
                                        <td><?= date('d/m H:i', strtotime($r['fecha_registro'])) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($r['hotel']) ?></span></td>
                                        <td class="text-capitalize"><?= htmlspecialchars($r['area']) ?></td>
                                        <td><?= htmlspecialchars($r['tipo_novedad'] ?? 'Otro') ?></td>
                                        <td>
                                            <?php
                                                $imp = (int) $r['nivel_importancia'];
                                                $cls = 'bg-success';
                                                if ($imp >= 4) $cls = 'bg-warning text-dark';
                                                if ($imp >= 8) $cls = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $cls ?>"><?= $imp ?>/10</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-end bg-white">
                    <a href="index.php?route=novedades/list" class="btn btn-sm btn-outline-primary">
                        Ver todas
                    </a>
                </div>
            </div>
        </div>
    </div>

</div><!-- /container -->

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Datos pivotados desde PHP (hotel, labels, datasets)
    const dataTipo = <?= json_encode($tipoPorHotel) ?>;
    const dataArea = <?= json_encode($areaPorHotel) ?>;
    const dataCrit = <?= json_encode($critPorHotel) ?>;

    // Capitalizar primera letra de cada etiqueta
    const cap = s => s ? s.charAt(0).toUpperCase() + s.slice(1) : s;

    const opcionesBase = (indexAxis = 'x') => ({
        indexAxis,
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top', labels: { boxWidth: 14, font: { size: 12 } } },
        },
        scales: {
            x: { beginAtZero: true, ticks: { precision: 0 } },
            y: { beginAtZero: true, ticks: { precision: 0 } },
        }
    });

    // ── Por Departamento (barras horizontales agrupadas) ──────────
    new Chart(document.getElementById('tipoChart'), {
        type: 'bar',
        data: {
            labels: dataTipo.labels,
            datasets: dataTipo.datasets,
        },
        options: { ...opcionesBase('y') }
    });

    // ── Top Áreas (barras verticales agrupadas) ───────────────────
    const areaDataset = {
        ...dataArea,
        labels: dataArea.labels.map(cap),
    };
    new Chart(document.getElementById('areaChart'), {
        type: 'bar',
        data: areaDataset,
        options: { ...opcionesBase('x') }
    });

    // ── Nivel de Criticidad (barras verticales agrupadas) ─────────
    new Chart(document.getElementById('criticidadChart'), {
        type: 'bar',
        data: dataCrit,
        options: {
            ...opcionesBase('x'),
            // Colores semánticos para las 3 categorías fijas
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 14, font: { size: 12 } } },
            },
        }
    });
});
</script>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
