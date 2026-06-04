<?php require VIEW_PATH . "/layouts/header.php"; ?>

<!-- Bienvenida -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3 class="mb-1"><i class="fa-solid fa-gauge-high"></i> Dashboard</h3>
    <p class="text-muted mb-0">
      Bienvenido, <strong><?= htmlspecialchars($userName) ?></strong>
      <span class="badge bg-dark ms-1"><?= ucfirst(htmlspecialchars($userRole)) ?></span>
    </p>
  </div>
  <div class="text-muted">
    <i class="fa-regular fa-calendar"></i> <?= date('d/m/Y') ?>
  </div>
</div>

<!-- KPIs Row 1: Contratos -->
<div class="row g-3 mb-4 fade-in">
  <div class="col-6 col-xl-3">
    <div class="card kpi-card success">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Contratos Vigentes</div>
            <div class="kpi-value"><?= (int)$totalVigentes ?></div>
          </div>
          <i class="fa-solid fa-file-circle-check fa-2x opacity-50" style="color:var(--atk-success)"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card kpi-card warning">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Por Renovar / Vencer</div>
            <div class="kpi-value"><?= (int)$totalPorRenovar + (int)$totalExpiring ?></div>
          </div>
          <i class="fa-solid fa-clock fa-2x opacity-50" style="color:var(--atk-warning)"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card kpi-card danger">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Vencidos</div>
            <div class="kpi-value"><?= (int)$totalVencidos ?></div>
          </div>
          <i class="fa-solid fa-file-circle-xmark fa-2x opacity-50" style="color:var(--atk-danger)"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Borradores</div>
            <div class="kpi-value"><?= (int)$totalBorradores ?></div>
          </div>
          <i class="fa-solid fa-file-pen fa-2x opacity-50" style="color:var(--atk-primary)"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- KPIs Row 2: Pagos, Empresas, Huéspedes -->
<div class="row g-3 mb-4 fade-in">
  <div class="col-6 col-xl-3">
    <div class="card kpi-card warning">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Pagos Pendientes</div>
            <div class="kpi-value"><?= (int)$totalPagosPendientes ?></div>
          </div>
          <i class="fa-solid fa-money-bill-wave fa-2x opacity-50" style="color:var(--atk-warning)"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card kpi-card danger">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Monto Pendiente</div>
            <div class="kpi-value" style="font-size:1.4rem">$<?= number_format($montoPendiente, 0, ',', '.') ?></div>
          </div>
          <i class="fa-solid fa-hand-holding-dollar fa-2x opacity-50" style="color:var(--atk-danger)"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card kpi-card info">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Empresas Activas</div>
            <div class="kpi-value"><?= (int)$totalEmpresas ?></div>
          </div>
          <i class="fa-solid fa-building fa-2x opacity-50" style="color:var(--atk-info)"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card kpi-card success">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Recaudación Real <?= date('Y') ?></div>
            <div class="kpi-value" style="font-size:1.4rem">$<?= number_format($totalRecaudado, 0, ',', '.') ?></div>
          </div>
          <i class="fa-solid fa-cash-register fa-2x opacity-50" style="color:var(--atk-success)"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Gráfico: Estado de Contratos -->
  <div class="col-lg-5">
    <div class="card fade-in h-100">
      <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fa-solid fa-chart-pie text-primary"></i> Distribución de Contratos</h6>
      </div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <div style="max-width: 300px; width: 100%">
          <canvas id="chartEstados"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Contratos próximos a vencer -->
  <div class="col-lg-7">
    <div class="card fade-in h-100">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fa-solid fa-triangle-exclamation text-warning"></i> Próximos a Vencer (30 días)</h6>
        <?php if(AuthService::hasPermission('contracts_view')): ?>
        <a href="<?= BASE_URL ?>/contracts?status=vigente" class="btn btn-sm btn-outline-primary">Ver todos</a>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <?php if(empty($expiring)): ?>
          <div class="text-center text-muted py-4">
            <i class="fa-solid fa-check-circle fa-3x mb-2 text-success opacity-50"></i>
            <p>No hay contratos próximos a vencer</p>
          </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead>
              <tr>
                <th>Código</th>
                <th>Empresa</th>
                <th>Vence</th>
                <th>Días</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach(array_slice($expiring, 0, 8) as $e): ?>
              <?php
                $daysLeft = (int)((strtotime($e['end_date']) - time()) / 86400);
                $badgeClass = $daysLeft <= 7 ? 'bg-danger' : ($daysLeft <= 15 ? 'bg-warning text-dark' : 'bg-info');
              ?>
              <tr>
                <td>
                  <a href="<?= BASE_URL ?>/contracts/show/<?= $e['id'] ?>" class="text-decoration-none fw-bold">
                    <?= htmlspecialchars($e['code']) ?>
                  </a>
                </td>
                <td><?= htmlspecialchars($e['business_name']) ?></td>
                <td><?= date('d/m/Y', strtotime($e['end_date'])) ?></td>
                <td><span class="badge <?= $badgeClass ?>"><?= $daysLeft ?> días</span></td>
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

<!-- Actividad Reciente -->
<div class="card mt-4 fade-in">
  <div class="card-header bg-white">
    <h6 class="mb-0"><i class="fa-solid fa-clock-rotate-left text-primary"></i> Actividad Reciente</h6>
  </div>
  <div class="card-body">
    <?php if(empty($recentActivity)): ?>
      <div class="text-center text-muted py-3">
        <p>No hay actividad registrada aún</p>
      </div>
    <?php else: ?>
    <div class="list-group list-group-flush">
      <?php foreach($recentActivity as $act): ?>
      <div class="list-group-item d-flex align-items-center px-0">
        <?php
          $iconMap = [
            'creado' => 'fa-plus-circle text-success',
            'editado' => 'fa-pen text-warning',
            'eliminado' => 'fa-trash text-danger',
            'pago_registrado' => 'fa-money-check text-info',
            'estado_cambiado' => 'fa-exchange-alt text-primary',
          ];
          $icon = $iconMap[$act['action']] ?? 'fa-circle text-muted';
        ?>
        <i class="fa-solid <?= $icon ?> me-3"></i>
        <div class="flex-grow-1">
          <div class="small">
            <strong><?= htmlspecialchars($act['user_name'] ?? 'Sistema') ?></strong>
            <span class="text-muted">— <?= htmlspecialchars($act['action']) ?></span>
            <?php if(!empty($act['contract_code'])): ?>
              <a href="<?= BASE_URL ?>/contracts/show/<?= $act['contract_id'] ?>" class="text-decoration-none ms-1">
                <?= htmlspecialchars($act['contract_code']) ?>
              </a>
            <?php endif; ?>
          </div>
          <?php if(!empty($act['description'])): ?>
          <small class="text-muted"><?= htmlspecialchars($act['description']) ?></small>
          <?php endif; ?>
        </div>
        <small class="text-muted ms-2"><?= date('d/m H:i', strtotime($act['created_at'])) ?></small>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Chart.js -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartEstados');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Vigentes', 'Borradores', 'Por Renovar', 'Vencidos'],
                datasets: [{
                    data: [
                        <?= (int)$chartData['vigentes'] ?>,
                        <?= (int)$chartData['borradores'] ?>,
                        <?= (int)$chartData['por_renovar'] ?>,
                        <?= (int)$chartData['vencidos'] ?>
                    ],
                    backgroundColor: ['#28a745', '#6c757d', '#ffc107', '#dc3545'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15 }
                    }
                },
                cutout: '60%'
            }
        });
    }
});
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
