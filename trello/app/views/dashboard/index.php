<?php require VIEW_PATH . "/layouts/header.php"; ?>

<div class="container-fluid py-4 px-4">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="text-white mb-0"><i class="bi bi-speedometer2 me-2"></i> Dashboard de Indicadores</h4>
    <div class="text-white-50 small">
      <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?>
    </div>
  </div>

  <!-- Tarjetas de Resumen -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-md-3 animate-in" style="--index: 1">
      <div class="card border-0 shadow-sm bg-primary text-white h-100">
        <div class="card-body d-flex align-items-center">
          <div>
            <div class="small opacity-75">Total Tarjetas</div>
            <div class="display-6 fw-bold"><?= $stats['total_tarjetas'] ?></div>
          </div>
          <i class="bi bi-kanban fs-1 ms-auto opacity-25"></i>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3 animate-in" style="--index: 2">
      <div class="card border-0 shadow-sm bg-success text-white h-100">
        <div class="card-body d-flex align-items-center">
          <div>
            <div class="small opacity-75">Completadas</div>
            <div class="display-6 fw-bold"><?= $stats['completadas'] ?></div>
          </div>
          <i class="bi bi-check-circle fs-1 ms-auto opacity-25"></i>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3 animate-in" style="--index: 3">
      <div class="card border-0 shadow-sm bg-danger text-white h-100">
        <div class="card-body d-flex align-items-center">
          <div>
            <div class="small opacity-75">Atrasadas</div>
            <div class="display-6 fw-bold"><?= $stats['atrasadas'] ?></div>
          </div>
          <i class="bi bi-exclamation-triangle fs-1 ms-auto opacity-25"></i>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3 animate-in" style="--index: 4">
      <div class="card border-0 shadow-sm bg-info text-white h-100">
        <div class="card-body d-flex align-items-center">
          <div>
            <div class="small opacity-75">% Avance Global</div>
            <?php 
              $perc = $stats['total_tarjetas'] > 0 ? round(($stats['completadas'] / $stats['total_tarjetas']) * 100) : 0;
            ?>
            <div class="display-6 fw-bold"><?= $perc ?>%</div>
          </div>
          <i class="bi bi-graph-up-arrow fs-1 ms-auto opacity-25"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <!-- Gráfico de Barras por Tablero -->
    <div class="col-12 col-xl-8">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white py-3">
          <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Avance por Proyecto</h6>
        </div>
        <div class="card-body">
          <canvas id="chartProyectos" height="300"></canvas>
        </div>
      </div>
    </div>

    <!-- Lista de Tableros -->
    <div class="col-12 col-xl-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white py-3">
          <h6 class="mb-0 fw-bold"><i class="bi bi-list-task me-2 text-primary"></i>Resumen de Tableros</h6>
        </div>
        <div class="card-body p-0">
          <div class="list-group list-group-flush">
            <?php foreach ($tableros_stats as $ts): 
               $p = $ts['total'] > 0 ? round(($ts['completadas'] / $ts['total']) * 100) : 0;
            ?>
              <a href="<?= BASE_URL ?>/tablero/ver?id=<?= $ts['id'] ?>" class="list-group-item list-group-item-action py-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="fw-bold small"><?= htmlspecialchars($ts['nombre']) ?></span>
                  <span class="badge rounded-pill bg-light text-dark border small"><?= $p ?>%</span>
                </div>
                <div class="progress" style="height: 6px;">
                  <div class="progress-bar" role="progressbar" style="width: <?= $p ?>%; background-color: <?= $ts['fondo_color'] ?>"></div>
                </div>
                <div class="text-muted mt-1" style="font-size: .65rem;">
                  <?= $ts['completadas'] ?> de <?= $ts['total'] ?> tareas listas
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('chartProyectos').getContext('2d');
    const data = <?= json_encode($tableros_stats) ?>;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(t => t.nombre),
            datasets: [{
                label: 'Tareas Totales',
                data: data.map(t => t.total),
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }, {
                label: 'Tareas Completadas',
                data: data.map(t => t.completadas),
                backgroundColor: 'rgba(34, 197, 94, 0.2)',
                borderColor: 'rgba(34, 197, 94, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            },
            plugins: {
                legend: { position: 'top' }
            }
        }
    });
});
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
