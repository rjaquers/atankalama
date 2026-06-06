<?php
/**
 * Vista de Dashboard con Analítica - Atankalama Empresas
 */
Layout::header($title, $user, 'dashboard');
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h3 class="fw-bold mb-1">¡Hola, <?= explode(' ', $user['name'])[0] ?>! 👋</h3>
        <p class="text-muted">Aquí tienes un resumen de lo que está pasando en su empresa.</p>
    </div>
    <div class="col-md-4 text-md-end">
         <a href="<?= BASE_URL ?>export/alimentacion/<?= $days ?>" class="btn btn-success shadow-sm">
            <i class="fa-solid fa-file-excel me-2"></i> Reporte Completo
         </a>
    </div>
</div>

<!-- Filtros Rápidos -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-3">
        <div class="d-flex overflow-auto">
            <div class="btn-group" role="group">
                <a href="?days=1" class="btn btn-outline-primary px-4 filter-btn <?= $days == 1 ? 'active' : '' ?>">Hoy</a>
                <a href="?days=7" class="btn btn-outline-primary px-4 filter-btn <?= $days == 7 ? 'active' : '' ?>">Esta Semana</a>
                <a href="?days=30" class="btn btn-outline-primary px-4 filter-btn <?= $days == 30 ? 'active' : '' ?>">Últimos 30 días</a>
                <a href="?days=last_month" class="btn btn-outline-primary px-4 filter-btn <?= $days == 'last_month' ? 'active' : '' ?>">Mes Pasado</a>
            </div>
        </div>
    </div>
</div>

<!-- KPIs -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #0056b3 0%, #007bff 100%); color: white;">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-white-50 text-uppercase small fw-bold">Alimentación Consumida</h6>
                    <h1 class="display-5 fw-bold mb-0"><?= number_format($totalAlimentacion, 0, ',', '.') ?></h1>
                    <small class="text-white-50">Porciones/Servicios en el periodo</small>
                </div>
                <div class="ms-3 opacity-25">
                    <i class="fa-solid fa-utensils fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #198754 0%, #28a745 100%); color: white;">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-white-50 text-uppercase small fw-bold">Otros Servicios</h6>
                    <h1 class="display-5 fw-bold mb-0"><?= number_format($totalServicios, 0, ',', '.') ?></h1>
                    <small class="text-white-50">Habitaciones y adicionales</small>
                </div>
                <div class="ms-3 opacity-25">
                    <i class="fa-solid fa-bell fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Gráfica de Tendencia -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-bold"><i class="fa-solid fa-chart-line me-2 text-primary"></i>Tendencia de Consumo Diario</h6>
            </div>
            <div class="card-body">
                <canvas id="chartDaily" height="250"></canvas>
            </div>
        </div>
    </div>
    <!-- Gráfica de Distribución -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-bold"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Tipo de Servicio</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="chartDistribution"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de últimos registros -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Últimos Movimientos</h6>
        <a href="<?= BASE_URL ?>alimentacion" class="btn btn-sm btn-light text-primary fw-bold">Ver Historial Completo</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light border-0">
                    <tr>
                        <th class="ps-4">Fecha</th>
                        <th>Servicio</th>
                        <th>Comensal</th>
                        <th>RUT</th>
                        <th class="pe-4 text-end">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($latestRecords)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No se registran datos en este periodo.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($latestRecords as $r): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?= date('d M', strtotime($r['fecha'])) ?></div>
                                    <small class="text-muted"><?= date('H:i', strtotime($r['hora_servicio'])) ?> hrs</small>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle text-capitalize px-3">
                                        <?= $r['tipo_servicio'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($r['nombre_comensal'] ?? 'Huésped Hotel') ?></td>
                                <td><code><?= $r['rut_masked'] ?? '---' ?></code></td>
                                <td class="pe-4 text-end">
                                    <?php if($r['cobrado']): ?>
                                        <span class="text-success small fw-bold"><i class="fa-solid fa-check-circle me-1"></i> Facturado</span>
                                    <?php else: ?>
                                        <span class="text-warning small fw-bold"><i class="fa-solid fa-clock me-1"></i> Por liquidar</span>
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

<!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Configuración de gráficas
const dailyRaw = <?= $chartDaily ?>;
const distRaw = <?= $chartDist ?>;

// 1. Gráfica de Líneas (Tendencia)
new Chart(document.getElementById('chartDaily'), {
    type: 'line',
    data: {
        labels: dailyRaw.map(d => d.label.split('-').reverse().slice(0,2).join('/')),
        datasets: [{
            label: 'Servicios',
            data: dailyRaw.map(d => d.value),
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.05)',
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: '#007bff'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { display: false } },
            x: { grid: { display: false } }
        }
    }
});

// 2. Gráfica de Rosquilla (Distribución)
new Chart(document.getElementById('chartDistribution'), {
    type: 'doughnut',
    data: {
        labels: distRaw.map(d => d.label.charAt(0).toUpperCase() + d.label.slice(1)),
        datasets: [{
            data: distRaw.map(d => d.value),
            backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1'],
            borderWidth: 0,
            hoverOffset: 10
        }]
    },
    options: {
        responsive: true,
        cutout: '70%',
        plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
        }
    }
});
</script>

<?php Layout::footer(); ?>
