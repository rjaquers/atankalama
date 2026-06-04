<?php require VIEW_PATH . "/layouts/header.php"; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <i class="fa-solid fa-exclamation-circle"></i> <?= $_SESSION['flash_error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php
$statusColors = [
  'borrador' => 'bg-secondary', 'confirmada' => 'bg-success',
  'en_uso' => 'bg-info', 'finalizada' => 'bg-dark',
  'cancelada' => 'bg-danger', 'no_asistio' => 'bg-warning text-dark'
];
$bColor = $statusColors[$booking['booking_status']] ?? 'bg-secondary';
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3>
      <i class="fa-solid fa-calendar-check text-primary"></i>
      Reserva <?= htmlspecialchars($booking['folio']) ?>
      <span class="badge <?= $bColor ?> ms-2"><?= ucfirst(str_replace('_', ' ', $booking['booking_status'])) ?></span>
      <?php if ($booking['is_free']): ?>
        <span class="badge bg-secondary ms-1">🎁 Gratuita</span>
      <?php endif; ?>
    </h3>
  </div>
  <div class="d-flex gap-2">
    <?php if (AuthService::hasPermission('bookings_edit') && !in_array($booking['booking_status'], ['cancelada', 'finalizada'])): ?>
      <!-- Cambio de estado -->
      <div class="btn-group">
        <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
          <i class="fa-solid fa-exchange-alt"></i> Cambiar estado
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <?php
          $transitions = [
            'borrador' => ['confirmada'],
            'confirmada' => ['en_uso', 'no_asistio'],
            'en_uso' => ['finalizada'],
          ];
          $available = $transitions[$booking['booking_status']] ?? [];
          foreach ($available as $st):
          ?>
            <li>
              <form method="post" action="<?= BASE_URL ?>/bookings/changeStatus/<?= $booking['id'] ?>">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <input type="hidden" name="booking_status" value="<?= $st ?>">
                <button type="submit" class="dropdown-item">
                  <?= ucfirst(str_replace('_', ' ', $st)) ?>
                </button>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if (AuthService::hasPermission('bookings_edit') && !in_array($booking['booking_status'], ['cancelada', 'finalizada'])): ?>
      <a href="<?= BASE_URL ?>/bookings/edit/<?= $booking['id'] ?>" class="btn btn-outline-warning btn-sm">
        <i class="fa-solid fa-pen"></i> Editar
      </a>
    <?php endif; ?>

    <?php if (AuthService::hasPermission('bookings_cancel') && !in_array($booking['booking_status'], ['cancelada', 'finalizada'])): ?>
      <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalCancel">
        <i class="fa-solid fa-ban"></i> Cancelar
      </button>
    <?php endif; ?>

    <a href="<?= BASE_URL ?>/bookings" class="btn btn-outline-secondary btn-sm">
      <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
  </div>
</div>

<div class="row g-4">
  <!-- Columna Izquierda: Detalle -->
  <div class="col-lg-7">
    <!-- Info del Espacio -->
    <div class="card">
      <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fa-solid fa-door-open text-primary"></i> Espacio y Horario</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <?php if (!empty($booking['main_image'])): ?>
            <div class="col-md-4">
              <img src="<?= BASE_URL ?>/<?= htmlspecialchars($booking['main_image']) ?>" 
                   class="img-fluid rounded border shadow-sm" style="max-height: 140px; width: 100%; object-fit: cover;"
                   alt="Foto de <?= htmlspecialchars($booking['space_name']) ?>">
            </div>
            <div class="col-md-8">
          <?php else: ?>
            <div class="col-md-12">
          <?php endif; ?>
              <div class="row g-3">
                <div class="col-md-6">
                  <small class="text-muted d-block">Espacio</small>
                  <strong>
              <a href="<?= BASE_URL ?>/spaces/show/<?= $booking['space_id'] ?>" class="text-decoration-none">
                <?= htmlspecialchars($booking['space_code'] . ' — ' . $booking['space_name']) ?>
              </a>
            </strong>
            <br><span class="badge bg-primary"><?= ucfirst($booking['space_type']) ?></span>
          </div>
          <div class="col-md-6">
            <small class="text-muted d-block">Modalidad</small>
            <strong><?= ucfirst(str_replace('_', ' ', $booking['booking_mode'])) ?></strong>
          </div>
          <div class="col-md-6">
            <small class="text-muted d-block">Inicio</small>
            <strong class="text-success"><?= date('d/m/Y H:i', strtotime($booking['start_datetime'])) ?></strong>
          </div>
          <div class="col-md-6">
            <small class="text-muted d-block">Fin</small>
            <strong class="text-danger"><?= date('d/m/Y H:i', strtotime($booking['end_datetime'])) ?></strong>
          </div>
          <?php if (!empty($booking['space_restrictions'])): ?>
            <div class="col-12">
              <div class="alert alert-warning py-2 mb-0">
                <i class="fa-solid fa-exclamation-triangle"></i> <strong>Restricciones:</strong>
                <?= htmlspecialchars($booking['space_restrictions']) ?>
              </div>
            </div>
          <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

    <!-- Empresa / Cliente -->
    <div class="card mt-4">
      <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fa-solid fa-building text-info"></i> Empresa / Cliente</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <small class="text-muted d-block">Empresa</small>
            <strong><?= htmlspecialchars($booking['company_name'] ?? 'Sin empresa') ?></strong>
            <?php if (!empty($booking['company_rut'])): ?>
              <br><small class="text-muted">RUT: <?= htmlspecialchars($booking['company_rut']) ?></small>
            <?php endif; ?>
          </div>
          <div class="col-md-6">
            <small class="text-muted d-block">Cliente / Contacto</small>
            <strong><?= htmlspecialchars($booking['client_name'] ?: '-') ?></strong>
          </div>
          <?php if (!empty($booking['contract_code'])): ?>
          <div class="col-md-6">
            <small class="text-muted d-block">Contrato Asociado</small>
            <a href="<?= BASE_URL ?>/contracts/show/<?= $booking['contract_id'] ?>" class="fw-bold text-decoration-none">
              <?= htmlspecialchars($booking['contract_code']) ?>
            </a>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Observaciones -->
    <?php if (!empty($booking['notes_client']) || !empty($booking['notes_internal'])): ?>
    <div class="card mt-4">
      <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fa-solid fa-sticky-note text-warning"></i> Observaciones</h6>
      </div>
      <div class="card-body">
        <?php if (!empty($booking['notes_client'])): ?>
          <h6 class="small fw-bold text-muted">Del cliente:</h6>
          <p style="white-space: pre-wrap;"><?= htmlspecialchars($booking['notes_client']) ?></p>
        <?php endif; ?>
        <?php if (!empty($booking['notes_internal'])): ?>
          <h6 class="small fw-bold text-muted">Internas:</h6>
          <p style="white-space: pre-wrap;" class="mb-0"><?= htmlspecialchars($booking['notes_internal']) ?></p>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Cancelación -->
    <?php if ($booking['booking_status'] === 'cancelada'): ?>
    <div class="card mt-4 border-danger">
      <div class="card-header bg-danger text-white">
        <h6 class="mb-0"><i class="fa-solid fa-ban"></i> Cancelación</h6>
      </div>
      <div class="card-body">
        <p class="mb-1"><strong>Motivo:</strong> <?= htmlspecialchars($booking['cancel_reason']) ?></p>
        <small class="text-muted">
          Por: <?= htmlspecialchars($booking['cancelled_by_name'] ?? '-') ?> — 
          <?= $booking['cancelled_at'] ? date('d/m/Y H:i', strtotime($booking['cancelled_at'])) : '' ?>
        </small>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Columna Derecha: Financiero -->
  <div class="col-lg-5">
    <!-- Resumen financiero -->
    <div class="card">
      <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fa-solid fa-dollar-sign text-success"></i> Resumen Financiero</h6>
      </div>
      <div class="card-body">
        <?php if ($booking['is_free']): ?>
          <div class="text-center py-3">
            <span class="badge bg-secondary fs-5">🎁 Reserva Gratuita</span>
            <?php if (!empty($booking['free_reason'])): ?>
              <p class="text-muted mt-2 mb-0"><?= htmlspecialchars($booking['free_reason']) ?></p>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <table class="table table-borderless mb-0">
            <tr>
              <td class="text-muted">Valor Base:</td>
              <td class="text-end fw-bold">$<?= number_format((float)$booking['base_price'], 0, ',', '.') ?></td>
            </tr>
            <?php if ((float)$booking['discount'] > 0): ?>
            <tr>
              <td class="text-muted">Descuento:</td>
              <td class="text-end text-success">- $<?= number_format((float)$booking['discount'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <?php if ((float)$booking['surcharge'] > 0): ?>
            <tr>
              <td class="text-muted">Recargo:</td>
              <td class="text-end text-danger">+ $<?= number_format((float)$booking['surcharge'], 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            <tr class="border-top">
              <td class="fw-bold fs-5">TOTAL:</td>
              <td class="text-end fw-bold fs-5 text-primary">$<?= number_format((float)$booking['total_price'], 0, ',', '.') ?></td>
            </tr>
          </table>
          <div class="mt-2 text-center">
            <small class="text-muted">Estado:</small>
            <span class="badge bg-light text-dark border ms-1"><?= ucfirst(str_replace('_', ' ', $booking['charge_status'])) ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Pagos / Cargos -->
    <div class="card mt-4">
      <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
        <h6 class="mb-0 text-primary fw-bold"><i class="fa-solid fa-file-invoice-dollar"></i> Cobros y Abonos</h6>
        <?php if (empty($booking['contract_id']) && !$booking['is_free'] && !in_array($booking['booking_status'], ['cancelada', 'borrador'])): ?>
          <button type="button" class="btn btn-xs btn-atk py-1" data-bs-toggle="modal" data-bs-target="#modalAddPayment">
            <i class="fa-solid fa-plus small"></i> Pago
          </button>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <?php if ($booking['is_free']): ?>
          <div class="text-center py-2 text-muted small"><i class="fa-solid fa-gift"></i> Sin cobro.</div>
        <?php elseif (!empty($booking['contract_id'])): ?>
          <div class="alert alert-light border small py-2 mb-0">
            <i class="fa-solid fa-info-circle text-info"></i> Cargo en contrato:
            <a href="<?= BASE_URL ?>/contracts/show/<?= $booking['contract_id'] ?>" class="fw-bold"><?= htmlspecialchars($booking['contract_code']) ?></a>
            <hr class="my-1">
            <?php if (!empty($paymentInfo)): ?>
              <span class="badge bg-success" style="opacity: 0.8;"><i class="fa-solid fa-check"></i> Cargo generado</span>
            <?php else: ?>
              <span class="text-muted italic small"><i class="fa-solid fa-clock"></i> Pendiente (al Confirmar).</span>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <?php 
          $totalPaid = 0;
          foreach ($paymentInfo as $p) $totalPaid += (float)$p['amount'];
          $pending = (float)$booking['total_price'] - $totalPaid;
          ?>
          <div class="d-flex justify-content-between mb-1">
            <small class="text-muted">Total:</small>
            <span class="fw-bold">$<?= number_format((float)$booking['total_price'], 0, ',', '.') ?></span>
          </div>
          <div class="d-flex justify-content-between mb-1 text-success">
            <small>Pagado:</small>
            <span class="fw-bold">$<?= number_format($totalPaid, 0, ',', '.') ?></span>
          </div>
          <div class="d-flex justify-content-between border-top pt-1 <?= $pending > 0 ? 'text-danger' : 'text-primary' ?>">
            <small>Saldo:</small>
            <span class="fw-bold">$<?= number_format($pending, 0, ',', '.') ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Extras -->
    <?php if (!empty($items)): ?>
    <div class="card mt-4">
      <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fa-solid fa-puzzle-piece text-info"></i> Extras (<?= count($items) ?>)</h6>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead class="table-light">
            <tr>
              <th>Item</th>
              <th class="text-center">Cant.</th>
              <th class="text-end">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it): ?>
            <tr>
              <td><?= htmlspecialchars($it['description']) ?></td>
              <td class="text-center"><?= (float)$it['quantity'] ?></td>
              <td class="text-end fw-bold">$<?= number_format((float)$it['subtotal'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Info de registro -->
    <div class="card mt-4">
      <div class="card-body">
        <small class="text-muted d-block">
          <i class="fa-solid fa-user"></i> Creado por: <?= htmlspecialchars($booking['created_by_name'] ?? '-') ?>
        </small>
        <small class="text-muted d-block">
          <i class="fa-solid fa-calendar"></i> Fecha: <?= date('d/m/Y H:i', strtotime($booking['created_at'])) ?>
        </small>
        <?php if (!empty($booking['origin'])): ?>
        <small class="text-muted d-block">
          <i class="fa-solid fa-tag"></i> Origen: <?= ucfirst($booking['origin']) ?>
        </small>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Historial -->
<?php if (!empty($history)): ?>
<div class="card mt-4 mb-4">
  <div class="card-header bg-white">
    <h6 class="mb-0"><i class="fa-solid fa-clock-rotate-left text-primary"></i> Historial (<?= count($history) ?>)</h6>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm table-borderless align-middle" style="font-size: 0.85rem;" id="tableBookingHistory">
        <thead class="text-muted border-bottom">
          <tr>
            <th style="width: 130px;">Fecha / Hora</th>
            <th style="width: 140px;">Acción</th>
            <th>Descripción</th>
            <th style="width: 150px;">Usuario</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($history as $h): ?>
          <tr class="border-bottom">
            <td class="text-muted">
              <?= date('d/m/Y', strtotime($h['created_at'])) ?>
              <span class="opacity-75 small"><?= date('H:i', strtotime($h['created_at'])) ?></span>
            </td>
            <td><span class="fw-bold"><?= ucfirst(str_replace('_', ' ', $h['action'])) ?></span></td>
            <td class="text-muted"><?= htmlspecialchars($h['description'] ?: '-') ?></td>
            <td>
              <span class="badge bg-light text-dark fw-normal border">
                <i class="fa-solid fa-user-circle small opacity-50"></i> <?= htmlspecialchars($h['user_name'] ?? 'Sistema') ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Modal Cancelar -->
<div class="modal fade" id="modalCancel" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="<?= BASE_URL ?>/bookings/cancel/<?= $booking['id'] ?>">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="fa-solid fa-ban"></i> Cancelar Reserva</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Está a punto de cancelar la reserva <strong><?= htmlspecialchars($booking['folio']) ?></strong>.</p>
          <div class="mb-3">
            <label class="form-label fw-bold">Motivo de cancelación <span class="text-danger">*</span></label>
            <textarea name="cancel_reason" class="form-control" rows="3" required
                      placeholder="Indique el motivo de la cancelación"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
          <button type="submit" class="btn btn-danger">
            <i class="fa-solid fa-ban"></i> Confirmar Cancelación
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Registrar Pago -->
<div class="modal fade" id="modalAddPayment" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= BASE_URL ?>/bookings/addPayment/<?= $booking['id'] ?>" method="POST">
        <?= csrf_token() ?>
        <div class="modal-header">
          <h5 class="modal-title">Registrar Pago / Abono</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Monto a pagar</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" name="amount" class="form-control" value="<?= (float)$booking['total_price'] - $totalPaid ?>" required min="1">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Fecha</label>
              <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Método de Pago</label>
              <select name="payment_method" class="form-select">
                <option value="transferencia">Transferencia</option>
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta (Débito/Crédito)</option>
                <option value="cheque">Cheque</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nº Referencia</label>
              <input type="text" name="reference_number" class="form-control" placeholder="Ej: Transf #123">
            </div>
            <div class="col-12">
              <label class="form-label">Notas / Observaciones</label>
              <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-atk">Registrar abono</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>

<script>
$(document).ready(function() {
  if ($('#tableBookingHistory').length) {
    $('#tableBookingHistory').DataTable({
      pageLength: 15, lengthChange: false, searching: false, ordering: false, info: true,
      language: {
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }
      }
    });
  }
});
</script>
