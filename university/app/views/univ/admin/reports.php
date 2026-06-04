<?php require VIEW_PATH . "/layouts/header.php"; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h3 class="m-0"><i class="fa-solid fa-chart-pie"></i> Reportes de Capacitación</h3>
  <a href="<?= BASE_URL ?>/univAdmin" class="btn btn-outline-dark">
    <i class="fa-solid fa-arrow-left"></i> Volver a Cursos
  </a>
</div>

<div class="row g-3 mb-4">
  <!-- Status Global -->
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white fw-bold">Estado de Matrículas</div>
      <div class="card-body">
        <ul class="list-group list-group-flush">
          <?php foreach ($stats as $s): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span class="text-capitalize"><?= $s['status'] ?></span>
              <span class="badge bg-primary rounded-pill"><?= $s['total'] ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>

  <!-- Top Empleados -->
  <div class="col-md-8">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white fw-bold">Top 10 Empleados (Créditos)</div>
      <div class="card-body p-0">
        <table class="table table-hover m-0">
          <thead class="table-light">
            <tr>
              <th>Nombre</th>
              <th>Perfil</th>
              <th class="text-end">Créditos</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($ranking as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['nombre'] . ' ' . $r['apellido']) ?></td>
                <td><small class="text-muted"><?= htmlspecialchars($r['perfil']) ?></small></td>
                <td class="text-end fw-bold text-success"><?= $r['total_creditos'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Rendimiento por Curso -->
<div class="card shadow-sm">
  <div class="card-header bg-white fw-bold">Rendimiento por Curso</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle m-0">
        <thead class="table-light">
          <tr>
            <th>Nombre del Curso</th>
            <th>Inscritos</th>
            <th>Aprobados</th>
            <th>Tasa de Éxito</th>
            <th style="width: 200px;">Progreso Global</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($topCourses as $tc): ?>
            <?php 
              $rate = $tc['inscritos'] > 0 ? round(($tc['aprobados'] / $tc['inscritos']) * 100) : 0;
            ?>
            <tr>
              <td><?= htmlspecialchars($tc['nombre']) ?></td>
              <td><?= $tc['inscritos'] ?></td>
              <td><?= $tc['aprobados'] ?></td>
              <td>
                <span class="badge <?= $rate >= 70 ? 'bg-success' : ($rate >= 40 ? 'bg-warning' : 'bg-danger') ?>">
                  <?= $rate ?>%
                </span>
              </td>
              <td>
                <div class="progress" style="height: 8px;">
                  <div class="progress-bar" style="width: <?= $rate ?>%"></div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
