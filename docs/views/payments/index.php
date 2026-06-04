<?php require VIEW_PATH . "/layouts/header.php"; ?>

<!-- Mensajes flash -->
<?php if(!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if(!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <i class="fa-solid fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
  <h3><i class="fa-solid fa-credit-card"></i> Gestión de Pagos</h3>
  <div>
    <!-- Botón para ver contratos y registrar pagos desde allí -->
    <a href="<?= BASE_URL ?>/contracts" class="btn btn-outline-secondary btn-sm">
      <i class="fa-solid fa-file-contract"></i> Seleccionar Contrato para Pago
    </a>
  </div>
</div>

<!-- KPIs de Cobranzas -->
<div class="row g-3 mb-4 fade-in">
  <div class="col-12 col-md-6">
    <div class="card kpi-card danger h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Total Pendiente</div>
            <div class="kpi-value text-danger">$<?= number_format($totalPending, 0, ',', '.') ?></div>
            <div class="text-muted small mt-1">Suma de pagos con estado "Pendiente"</div>
          </div>
          <i class="fa-solid fa-hand-holding-dollar fa-3x opacity-25"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6">
    <div class="card kpi-card warning h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Pagos Pendientes</div>
            <div class="kpi-value text-warning"><?= (int)$countPending ?></div>
            <div class="text-muted small mt-1">Registros esperando cobro</div>
          </div>
          <i class="fa-solid fa-clock fa-3x opacity-25"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Listado de Pagos Pendientes (Dashboard Cobranzas) -->
<div class="card fade-in">
  <div class="card-header bg-white">
    <h6 class="mb-0"><i class="fa-solid fa-list text-primary"></i> Pagos Pendientes de Cobro</h6>
  </div>
  <div class="card-body">
    <?php if(empty($pendingPayments)): ?>
      <div class="text-center text-muted py-5">
        <i class="fa-solid fa-circle-check fa-4x mb-3 text-success opacity-25"></i>
        <p class="fs-5">No hay pagos pendientes de cobrar</p>
        <p class="small">Todos los pagos registrados están al día o han sido anulados.</p>
      </div>
    <?php else: ?>
    <table id="tablaPagosPendientes" class="table table-striped table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>Contrato</th>
          <th>Empresa</th>
          <th class="text-end">Monto</th>
          <th>Período Cubierto</th>
          <th>Vence el Periodo</th>
          <th class="text-center">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($pendingPayments as $p): ?>
        <tr>
          <td>
            <a href="<?= BASE_URL ?>/contracts/show/<?= $p['contract_id'] ?>" class="fw-bold text-decoration-none">
              <?= htmlspecialchars($p['contract_code']) ?>
            </a>
          </td>
          <td>
            <?= htmlspecialchars($p['business_name'] ?? '-') ?>
          </td>
          <td class="text-end fw-bold text-danger">
            $<?= number_format((float)$p['amount'], 0, ',', '.') ?>
          </td>
          <td>
            <?php if ($p['period_start'] && $p['period_end']): ?>
              <?= date('d/m/Y', strtotime($p['period_start'])) ?> — <?= date('d/m/Y', strtotime($p['period_end'])) ?>
            <?php else: ?>
              <span class="text-muted italic">No especificado</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($p['period_end']): ?>
                <?php
                    $dEnd = strtotime($p['period_end']);
                    $diff = round(($dEnd - time()) / 86400);
                    $cClass = ($diff < 0) ? 'text-danger fw-bold' : (($diff < 7) ? 'text-warning fw-bold' : '');
                ?>
                <span class="<?= $cClass ?>">
                    <?= date('d/m/Y', $dEnd) ?>
                    <?php if ($diff < 0): ?> (Vencido hace <?= abs($diff) ?> d)<?php endif; ?>
                </span>
            <?php else: ?>
                -
            <?php endif; ?>
          </td>
          <td class="text-center">
            <div class="btn-group btn-group-sm">
              <a href="<?= BASE_URL ?>/payments/edit/<?= $p['id'] ?>" class="btn btn-primary" title="Registrar Pago">
                <i class="fa-solid fa-dollar-sign"></i> Cobrar
              </a>
              <a href="<?= BASE_URL ?>/contracts/show/<?= $p['contract_id'] ?>" class="btn btn-outline-secondary" title="Ver Contrato">
                <i class="fa-solid fa-eye"></i>
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<!-- Script DataTables -->
<script>
$(document).ready(function() {
    if ($('#tablaPagosPendientes').length > 0) {
        $('#tablaPagosPendientes').DataTable({
            order: [[4, 'asc']], // Ordenar por fecha de vencimiento periodo
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }
        });
    }
});
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
