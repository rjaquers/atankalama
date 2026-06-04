<!DOCTYPE html>
<html lang='es'>

<head>
    <?php include(ROOT_PATH . '../public/static/templates/head.php'); ?>
    <style>
        :root {
            --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
            --ease-in-out: cubic-bezier(0.77, 0, 0.175, 1);
        }

        .stat-card {
            transition: transform 0.2s var(--ease-out), box-shadow 0.2s var(--ease-out);
            border: 1px solid var(--color-border);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.05);
        }

        .filter-chip {
            transition: all 0.2s var(--ease-out);
            cursor: pointer;
            border: 1px solid var(--color-border);
            padding: 0.5rem 1rem;
            border-radius: 100px;
            font-size: 0.875rem;
            font-weight: 500;
            background: transparent;
            color: var(--color-primary);
        }

        .filter-chip.active {
            background: var(--color-cta);
            color: white;
            border-color: var(--color-cta);
        }

        .filter-chip:active {
            transform: scale(0.95);
        }

        .chart-container {
            position: relative;
            margin: auto;
            height: 350px;
            width: 100%;
            opacity: 0;
            transform: translateY(10px);
            animation: slideUp 0.6s var(--ease-out) forwards;
        }

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }

        .table-hover tbody tr {
            transition: background-color 0.15s var(--ease-out);
        }
        
        .badge-period {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    </style>
</head>

<body class='pro-body'>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom"
            style="border-color: var(--color-border) !important;">
            <div>
                <h2 class="mb-0 fw-bold">
                    <i class="bi bi-bar-chart-steps me-2" style="color:var(--color-cta)"></i>Análisis de Demanda
                </h2>
                <p class="text-muted small mb-0">Visualiza el comportamiento de pedidos por empresa y periodo.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="index.php?page=estadistica/exportar_excel&fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>"
                    class="btn btn-outline-success px-3">
                    <i class="bi bi-file-earmark-excel me-1"></i>Excel
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="pro-card border-0 mb-4 stagger-1" style="animation: slideUp 0.5s var(--ease-out) forwards;">
            <div class="card-body px-4 py-3">
                <form method="GET" action="index.php" id="filterForm" class="row g-3 align-items-end">
                    <input type="hidden" name="page" value="estadistica/index">
                    <input type="hidden" name="periodo" id="periodoInput" value="<?= $periodo ?>">
                    
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">Rango de fechas</label>
                        <div class="input-group">
                            <input type="date" name="fecha_inicio" class="form-control border-0 shadow-none bg-light" 
                                value="<?= $fecha_inicio ?>">
                            <span class="input-group-text border-0 bg-light text-muted">a</span>
                            <input type="date" name="fecha_fin" class="form-control border-0 shadow-none bg-light" 
                                value="<?= $fecha_fin ?>">
                        </div>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label small fw-semibold text-muted">Agrupar por</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="filter-chip <?= $periodo === 'day' ? 'active' : '' ?>" onclick="setPeriodo('day')">Día</button>
                            <button type="button" class="filter-chip <?= $periodo === 'week' ? 'active' : '' ?>" onclick="setPeriodo('week')">Semana</button>
                            <button type="button" class="filter-chip <?= $periodo === 'month' ? 'active' : '' ?>" onclick="setPeriodo('month')">Mes</button>
                            <button type="button" class="filter-chip <?= $periodo === 'year' ? 'active' : '' ?>" onclick="setPeriodo('year')">Año</button>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resumen Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="pro-card stat-card border-0 p-4 text-center">
                    <h6 class="text-muted small fw-bold text-uppercase mb-2">Total Personas (PAX)</h6>
                    <h2 class="fw-bold mb-0 text-primary">
                        <?= number_format(array_sum(array_column($datos['resumen_empresas'], 'total_pax')), 0, ',', '.') ?>
                    </h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="pro-card stat-card border-0 p-4 text-center">
                    <h6 class="text-muted small fw-bold text-uppercase mb-2">Total Comandas</h6>
                    <h2 class="fw-bold mb-0 text-success">
                        <?= number_format(array_sum(array_column($datos['resumen_empresas'], 'total_comandas')), 0, ',', '.') ?>
                    </h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="pro-card stat-card border-0 p-4 text-center">
                    <h6 class="text-muted small fw-bold text-uppercase mb-2">Promedio PAX/Comanda</h6>
                    <?php 
                    $totalPax = array_sum(array_column($datos['resumen_empresas'], 'total_pax'));
                    $totalCom = array_sum(array_column($datos['resumen_empresas'], 'total_comandas'));
                    $promedio = $totalCom > 0 ? $totalPax / $totalCom : 0;
                    ?>
                    <h2 class="fw-bold mb-0 text-info"><?= number_format($promedio, 1, ',', '.') ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="pro-card stat-card border-0 p-4 text-center">
                    <h6 class="text-muted small fw-bold text-uppercase mb-2">Empresas Activas</h6>
                    <h2 class="fw-bold mb-0 text-warning"><?= count($datos['resumen_empresas']) ?></h2>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Gráfico Principal -->
            <div class="col-xl-8">
                <div class="pro-card border-0 p-4 h-100 stagger-2">
                    <h5 class="fw-bold mb-4">Tendencia por Empresa (PAX)</h5>
                    <div class="chart-container">
                        <canvas id="chartEmpresas"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Distribución por Tipo -->
            <div class="col-xl-4">
                <div class="pro-card border-0 p-4 h-100 stagger-2">
                    <h5 class="fw-bold mb-4">Distribución por Servicio</h5>
                    <div class="chart-container" style="height:300px;">
                        <canvas id="chartTipos"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Detalle por Periodo -->
            <div class="col-xl-12">
                <div class="pro-card border-0 p-0 overflow-hidden stagger-3">
                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Detalle Cronológico por Empresa</h5>
                        <span class="badge bg-light text-dark border badge-period">Filtro: <?= $periodo ?></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle" id="tableDetalle">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4">Periodo</th>
                                    <th>Empresa</th>
                                    <th>Servicio</th>
                                    <th class="text-center">Total PAX</th>
                                    <th class="text-center">Comandas</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datos['comandas_empresa'] as $c): ?>
                                <tr>
                                    <td class="px-4 fw-semibold"><?= $c['periodo'] ?></td>
                                    <td>
                                        <span class="badge bg-white text-primary border px-2 py-1">
                                            <?= htmlspecialchars($c['empresa']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $icon = match($c['tipo_servicio']) {
                                            'almuerzo' => 'bi-sun-fill text-warning',
                                            'cena' => 'bi-moon-stars-fill text-primary',
                                            'desayuno' => 'bi-sun text-info',
                                            'colacion' => 'bi-cup-hot-fill text-success',
                                            default => 'bi-star-fill text-dark'
                                        };
                                        ?>
                                        <i class="bi <?= $icon ?> me-1"></i> <?= ucfirst(str_replace('_', ' ', $c['tipo_servicio'])) ?>
                                    </td>
                                    <td class="text-center fw-bold fs-5"><?= $c['total_pax'] ?></td>
                                    <td class="text-center"><?= $c['total_comandas'] ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-secondary border-0">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Órdenes Sin Pago (Seccion Original) -->
        <div class="mt-5">
            <h4 class="mb-3 fw-bold" style="color: var(--color-primary);">
                <i class="bi bi-exclamation-triangle text-warning me-2"></i>Órdenes Pendientes de Pago
            </h4>
            <?php if (!empty($datos['sin_pago'])): ?>
                <div class="pro-card border-0 p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4">ID</th>
                                    <th>Habitación</th>
                                    <th>Fecha</th>
                                    <th class="text-end px-4">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datos['sin_pago'] as $orden): ?>
                                    <tr>
                                        <td class="px-4"><span class="text-muted">#<?= str_pad($orden['id'], 4, '0', STR_PAD_LEFT) ?></span></td>
                                        <td><span class="fw-bold text-primary"><?= htmlspecialchars($orden['habitacion']) ?></span></td>
                                        <td><?= date('d/m/Y H:i', strtotime($orden['fecha_hora'])) ?></td>
                                        <td class="text-end px-4">
                                            <span class="badge bg-danger rounded-pill px-3">$<?= number_format($orden['total'], 0, ',', '.') ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-success border-0 shadow-sm">
                    <i class="bi bi-check-circle-fill me-2"></i> Todas las órdenes están al día con sus pagos.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function setPeriodo(p) {
            document.getElementById('periodoInput').value = p;
            document.getElementById('filterForm').submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Data para Gráfico de Empresas
            <?php
            $periodosUnicos = array_values(array_unique(array_column($datos['comandas_empresa'], 'periodo')));
            sort($periodosUnicos);
            $empresasUnicas = array_unique(array_column($datos['comandas_empresa'], 'empresa'));
            
            $datasets = [];
            $colores = ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0'];
            
            $colorIdx = 0;
            foreach ($empresasUnicas as $emp) {
                $dataEmp = [];
                foreach ($periodosUnicos as $per) {
                    $sumPax = 0;
                    foreach ($datos['comandas_empresa'] as $row) {
                        if ($row['periodo'] === $per && $row['empresa'] === $emp) {
                            $sumPax += (int)$row['total_pax'];
                        }
                    }
                    $dataEmp[] = $sumPax;
                }
                
                $color = $colores[$colorIdx % count($colores)];
                $colorIdx++;
                
                $datasets[] = [
                    'label' => $emp,
                    'data' => $dataEmp,
                    'borderColor' => $color,
                    'backgroundColor' => $color . '20', // Corregido: concatenación con punto
                    'tension' => 0.4,
                    'fill' => false,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6
                ];
            }
            ?>

            const ctxEmp = document.getElementById('chartEmpresas').getContext('2d');
            new Chart(ctxEmp, {
                type: 'line',
                data: {
                    labels: <?= json_encode($periodosUnicos) ?>,
                    datasets: <?= json_encode($datasets) ?>
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { display: false } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // Data para Gráfico de Tipos
            const ctxTipo = document.getElementById('chartTipos').getContext('2d');
            new Chart(ctxTipo, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode(array_map('ucfirst', array_column($datos['tipos_servicio'], 'tipo_servicio'))) ?>,
                    datasets: [{
                        data: <?= json_encode(array_column($datos['tipos_servicio'], 'total')) ?>,
                        backgroundColor: ['#ffc107', '#0d6efd', '#0dcaf0', '#198754', '#6c757d'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    </script>

    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>
</body>
</html>