<?php require VIEW_PATH . "/layouts/header.php"; ?>

<!-- Mensajes flash -->
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
// Variables de ayuda para badges de estado
$statusClasses = [
  'borrador' => 'bg-secondary',
  'vigente' => 'bg-success',
  'por_renovar' => 'bg-warning text-dark',
  'vencido' => 'bg-danger',
  'cancelado' => 'bg-dark',
];
$statusClass = $statusClasses[$contract['status']] ?? 'bg-secondary';
$statusLabel = ucfirst(str_replace('_', ' ', $contract['status']));
?>

<!-- Page Header -->
<div class="page-header">
  <h3>
    <i class="fa-solid fa-file-contract"></i>
    <?= htmlspecialchars($contract['code']) ?>
    <span class="badge <?= $statusClass ?> ms-2"><?= $statusLabel ?></span>
  </h3>
  <!-- ======================================== -->
  <!-- Cambiar Estado                           -->
  <!-- ======================================== -->
  <?php if (AuthService::hasPermission('contracts_edit')): ?>
    <div class="card mt-12 fade-in">

      <form method="post" action="<?= BASE_URL ?>/contracts/changeStatus/<?= $contract['id'] ?>"
        class="d-flex gap-2 align-items-center" onsubmit="return confirm('¿Cambiar el estado del contrato?')">
        Cambiar Estado
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
        <select name="status" class="form-select" style="max-width: 250px;">
          <option value="borrador" <?= $contract['status'] === 'borrador' ? 'selected' : '' ?>>📝 Borrador</option>
          <option value="vigente" <?= $contract['status'] === 'vigente' ? 'selected' : '' ?>>✅ Vigente</option>
          <option value="por_renovar" <?= $contract['status'] === 'por_renovar' ? 'selected' : '' ?>>⏳ Por Renovar</option>
          <option value="vencido" <?= $contract['status'] === 'vencido' ? 'selected' : '' ?>>❌ Vencido</option>
          <option value="cancelado" <?= $contract['status'] === 'cancelado' ? 'selected' : '' ?>>🚫 Cancelado</option>
        </select>
        <button type="submit" class="btn  btn-sm btn-outline-primary">
          <i class="fa-solid fa-check"></i> Aplicar
        </button>
      </form>
    </div>

  <?php endif; ?>
  <div class="d-flex gap-2">
    <?php if (AuthService::hasPermission('contracts_edit')): ?>
      <button type="button" class="btn btn-sm btn-atk" data-bs-toggle="modal" data-bs-target="#modalUpload">
        <i class="fa-solid fa-plus"></i> Subir Archivo
      </button>
    <?php endif; ?>
    <?php if ($contract['template_id']): ?>
      <a href="<?= BASE_URL ?>/contracts/generatePdf/<?= $contract['id'] ?>" class="btn btn-outline-danger">
        <i class="fa-solid fa-file-pdf"></i>
        <?= !empty($contract['generated_pdf_path']) ? 'Regenerar PDF' : 'Generar PDF' ?>
      </a>
    <?php endif; ?>
    <?php if (!empty($contract['generated_pdf_path'])): ?>
      <a href="<?= BASE_URL ?>/contracts/downloadPdf/<?= $contract['id'] ?>" target="_blank" class="btn btn-danger">
        <i class="fa-solid fa-download"></i> Ver PDF
      </a>
    <?php endif; ?>
    <?php if (AuthService::hasPermission('contracts_edit')): ?>
      <a href="<?= BASE_URL ?>/contracts/edit/<?= $contract['id'] ?>" class="btn btn-warning">
        <i class="fa-solid fa-pen"></i> Editar
      </a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/contracts" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
  </div>
</div>

<div class="row g-4 fade-in">
  <!-- ======================================== -->
  <!-- Información del contrato                 -->
  <!-- ======================================== -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fa-solid fa-info-circle text-primary"></i> Información del Contrato</h6>
      </div>
      <div class="card-body">
        <table class="table table-borderless mb-0">
          <tr>
            <td class="text-muted" style="width: 40%">Código:</td>
            <td class="fw-bold"><?= htmlspecialchars($contract['code']) ?></td>
          </tr>
          <tr>
            <td class="text-muted">Tipo:</td>
            <td>
              <?php
              $typeIcons = ['arriendo' => 'fa-house', 'hospedaje' => 'fa-bed', 'proveedor' => 'fa-truck'];
              $typeColors = ['arriendo' => 'bg-primary', 'hospedaje' => 'bg-success', 'proveedor' => 'bg-info'];
              $icon = $typeIcons[$contract['contract_type']] ?? 'fa-file';
              $color = $typeColors[$contract['contract_type']] ?? 'bg-secondary';
              ?>
              <span class="badge <?= $color ?>">
                <i class="fa-solid <?= $icon ?>"></i> <?= ucfirst(htmlspecialchars($contract['contract_type'])) ?>
              </span>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Duración:</td>
            <td><?= ucfirst(str_replace('_', ' ', $contract['duration_type'])) ?></td>
          </tr>
          <tr>
            <td class="text-muted">Fecha Inicio:</td>
            <td class="fw-bold"><?= date('d/m/Y', strtotime($contract['start_date'])) ?></td>
          </tr>
          <tr>
            <td class="text-muted">Fecha Término:</td>
            <td>
              <?php if ($contract['end_date']): ?>
                <?= date('d/m/Y', strtotime($contract['end_date'])) ?>
                <?php
                $daysLeft = (strtotime($contract['end_date']) - time()) / 86400;
                if ($daysLeft <= 30 && $daysLeft > 0):
                  ?>
                  <span class="badge bg-warning text-dark ms-1"><i class="fa-solid fa-clock"></i> <?= (int) $daysLeft ?>
                    días</span>
                <?php elseif ($daysLeft <= 0): ?>
                  <span class="badge bg-danger ms-1"><i class="fa-solid fa-exclamation-triangle"></i> Vencido</span>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-muted">Indefinido</span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Modo de Precio:</td>
            <td>
              <?= $contract['pricing_mode'] === 'por_persona' ? '🧑 Por persona' : '👥 Por grupo (fijo)' ?>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Frecuencia Pago:</td>
            <td><?= ucfirst(htmlspecialchars($contract['payment_frequency'])) ?></td>
          </tr>
          <tr>
            <td class="text-muted">Creado por:</td>
            <td>
              <?= htmlspecialchars($contract['created_by_name'] ?? 'Sistema') ?>
              <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($contract['created_at'])) ?></small>
            </td>
          </tr>
          <?php if (!empty($contract['template_name'])): ?>
            <tr>
              <td class="text-muted">Plantilla:</td>
              <td><?= htmlspecialchars($contract['template_name']) ?></td>
            </tr>
          <?php endif; ?>
        </table>
      </div>
    </div>
  </div>

  <!-- ======================================== -->
  <!-- Empresa + Finanzas                       -->
  <!-- ======================================== -->
  <div class="col-lg-6">
    <!-- Empresa -->
    <div class="card mb-4">
      <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fa-solid fa-building text-primary"></i> Empresa</h6>
      </div>
      <div class="card-body">
        <table class="table table-borderless mb-0">
          <tr>
            <td class="text-muted" style="width: 40%">Razón Social:</td>
            <td class="fw-bold">
              <a href="<?= BASE_URL ?>/companies/show/<?= $contract['company_id'] ?>" class="text-decoration-none">
                <?= htmlspecialchars($contract['business_name']) ?>
              </a>
            </td>
          </tr>
          <?php if (!empty($contract['trade_name'])): ?>
            <tr>
              <td class="text-muted">Nombre Fantasía:</td>
              <td><?= htmlspecialchars($contract['trade_name']) ?></td>
            </tr>
          <?php endif; ?>
          <tr>
            <td class="text-muted">RUT:</td>
            <td><?= htmlspecialchars($contract['company_rut'] ?: '-') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Contacto:</td>
            <td>
              <?= htmlspecialchars($contract['contact_name'] ?: '-') ?>
              <?php if (!empty($contract['contact_email'])): ?>
                <br><a href="mailto:<?= htmlspecialchars($contract['contact_email']) ?>">
                  <i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($contract['contact_email']) ?>
                </a>
              <?php endif; ?>
              <?php if (!empty($contract['contact_phone'])): ?>
                <br><a href="tel:<?= htmlspecialchars($contract['contact_phone']) ?>">
                  <i class="fa-solid fa-phone"></i> <?= htmlspecialchars($contract['contact_phone']) ?>
                </a>
              <?php endif; ?>
            </td>
          </tr>
        </table>
      </div>
    </div>

    <!-- Resumen Financial -->
    <div class="card">
      <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fa-solid fa-dollar-sign text-primary"></i> Resumen Financiero</h6>
      </div>
      <div class="card-body">
        <div class="row g-3 text-center">
          <div class="col-4">
            <div class="p-2 rounded" style="background: var(--atk-bg-subtle);">
              <small class="text-muted d-block">Total Cargos</small>
              <strong class="fs-5">$<?= number_format($totalCharged, 0, ',', '.') ?></strong>
            </div>
          </div>
          <div class="col-4">
            <div class="p-2 rounded bg-success bg-opacity-10">
              <small class="text-muted d-block">Pagado</small>
              <strong class="fs-5 text-success">$<?= number_format($totalPaid, 0, ',', '.') ?></strong>
            </div>
          </div>
          <div class="col-4">
            <div class="p-2 rounded <?= $saldo > 0 ? 'bg-danger bg-opacity-10' : 'bg-success bg-opacity-10' ?>">
              <small class="text-muted d-block">Saldo</small>
              <strong
                class="fs-5 <?= $saldo > 0 ? 'text-danger' : 'text-success' ?>">$<?= number_format($saldo, 0, ',', '.') ?></strong>
            </div>
          </div>
        </div>
        <?php if ($totalCharged > 0): ?>
          <div class="progress mt-3" style="height: 8px;">
            <?php $pct = min(100, ($totalPaid / $totalCharged) * 100); ?>
            <div class="progress-bar bg-success" style="width: <?= $pct ?>%"></div>
          </div>
          <small class="text-muted"><?= number_format($pct, 1) ?>% cobrado</small>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ======================================== -->
<!-- Hoteles donde aplica                     -->
<!-- ======================================== -->
<div class="card mt-4 fade-in">
  <div class="card-header bg-white">
    <h6 class="mb-0"><i class="fa-solid fa-hotel text-primary"></i> Hotel(es) (<?= count($hotels) ?>)</h6>
  </div>
  <div class="card-body">
    <div class="row g-2">
      <?php foreach ($hotels as $h): ?>
        <div class="col-md-4">
          <div class="card border p-3">
            <strong><i class="fa-solid fa-hotel"></i> <?= htmlspecialchars($h['name']) ?></strong>
            <?php if (!empty($h['code'])): ?>
              <span class="badge bg-light text-dark"><?= htmlspecialchars($h['code']) ?></span>
            <?php endif; ?>
            <?php if (!empty($h['address'])): ?>
              <br><small class="text-muted"><?= htmlspecialchars($h['address']) ?>,
                <?= htmlspecialchars($h['city'] ?? '') ?></small>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($hotels)): ?>
        <p class="text-muted mb-0"><i class="fa-solid fa-info-circle"></i> Sin hoteles asociados</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ======================================== -->
<!-- Servicios incluidos                      -->
<!-- ======================================== -->
<div class="card mt-4 fade-in">
  <div class="card-header bg-white">
    <h6 class="mb-0"><i class="fa-solid fa-concierge-bell text-primary"></i> Servicios Incluidos
      (<?= count($services) ?>)</h6>
  </div>
  <div class="card-body">
    <?php if (empty($services)): ?>
      <p class="text-muted mb-0"><i class="fa-solid fa-info-circle"></i> Sin servicios asociados</p>
    <?php else: ?>
      <div class="row g-2">
        <?php foreach ($services as $svc): ?>
          <div class="col-md-3">
            <div class="card border p-2">
              <strong><i class="fa-solid fa-check text-success"></i> <?= htmlspecialchars($svc['name']) ?></strong>
              <?php if (!empty($svc['contract_notes'])): ?>
                <br><small class="text-muted"><?= htmlspecialchars($svc['contract_notes']) ?></small>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- ======================================== -->
<!-- Escalas de precio (tiers)                -->
<!-- ======================================== -->
<?php if ($contract['pricing_mode'] === 'por_persona' && !empty($tiers)): ?>
  <div class="card mt-4 fade-in">
    <div class="card-header bg-white">
      <h6 class="mb-0"><i class="fa-solid fa-layer-group text-primary"></i> Escalas de Precio</h6>
    </div>
    <div class="card-body">
      <table class="table table-striped mb-0">
        <thead>
          <tr>
            <th>Escala</th>
            <th>Mín. Huéspedes</th>
            <th>Máx. Huéspedes</th>
            <th class="text-end">Precio/Persona</th>
            <th class="text-end">Descuento</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tiers as $tier): ?>
            <tr>
              <td><strong><?= htmlspecialchars($tier['description'] ?? 'Sin nombre') ?></strong></td>
              <td><?= number_format((int) $tier['min_guests'], 0, ',', '.') ?></td>
              <td><?= $tier['max_guests'] ? number_format((int) $tier['max_guests'], 0, ',', '.') : '∞' ?></td>
              <td class="text-end fw-bold">$<?= number_format((float) $tier['price_per_person'], 0, ',', '.') ?></td>
              <td class="text-end"><?= (float) $tier['discount_percent'] ?>%</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<!-- ======================================== -->
<!-- Cargos Adicionales + Generar Deuda (lado a lado) -->
<!-- ======================================== -->
<?php if (AuthService::hasPermission('payments_register')): ?>
<div class="row g-4 mt-2 fade-in">
  <!-- Col Izq: Cargos Adicionales por Servicios -->
  <div class="col-lg-6">
    <div class="card h-100 border-info">
      <div class="card-header bg-info text-white">
        <h6 class="mb-0"><i class="fa-solid fa-receipt"></i> Cargos Adicionales por Servicios</h6>
      </div>
      <div class="card-body">
        <div class="text-muted small mb-3">
          <i class="fa-solid fa-info-circle"></i>
          Cada cargo se registra como movimiento independiente en la cuenta corriente del contrato.
        </div>

        <form method="post" action="<?= BASE_URL ?>/payments/storeServiceCharge" id="formServiceCharge">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="hidden" name="contract_id" value="<?= $contract['id'] ?>">

          <div class="mb-3">
            <label class="form-label small fw-bold mb-1">Tipo de Cargo <span class="text-danger">*</span></label>
            <select name="service_name" class="form-select form-select-sm" required>
              <option value="">-- Seleccionar --</option>
              <optgroup label="Cargos Estándar">
                <option value="Ajustar cargo">Ajustar cargo</option>
                <option value="Añadir producto / Insumo">Añadir producto / Insumo</option>
                <option value="Impuesto o tasa no incluida">Impuesto o tasa no incluida</option>
                <option value="Ingresos por habitación">Ingresos por habitación</option>
                <option value="Tarifa de cancelación">Tarifa de cancelación</option>
                <option value="Tarifa de No Show">Tarifa de No Show</option>
              </optgroup>
              <optgroup label="Servicios del Hotel">
                <?php foreach ($availableServices as $srv): ?>
                  <option value="<?= htmlspecialchars($srv['name']) ?>"><?= htmlspecialchars($srv['name']) ?></option>
                <?php endforeach; ?>
              </optgroup>
            </select>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-5">
              <label class="form-label small fw-bold mb-1">Fecha del Servicio <span class="text-danger">*</span></label>
              <input type="date" name="service_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-3">
              <label class="form-label small fw-bold mb-1">Cantidad <span class="text-danger">*</span></label>
              <input type="number" name="service_qty" class="form-control form-control-sm" min="1" value="1" required>
            </div>
            <div class="col-4">
              <label class="form-label small fw-bold mb-1">Precio Unitario</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text">$</span>
                <input type="number" name="service_price" class="form-control form-control-sm" placeholder="0" min="0" step="100">
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold mb-1">Observación (opcional)</label>
            <input type="text" name="service_notes" class="form-control form-control-sm" placeholder="Detalle adicional del cargo">
          </div>

          <button type="submit" class="btn btn-info text-white w-100"
                  onclick="return confirm('¿Registrar este cargo adicional como movimiento del contrato?')">
            <i class="fa-solid fa-plus-circle"></i> Registrar Cargo de Servicio
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Col Der: Generar Cargo / Deuda del Período -->
  <div class="col-lg-6">
    <div class="card h-100 border-primary">
      <div class="card-header bg-primary text-white">
        <h6 class="mb-0"><i class="fa-solid fa-calculator"></i> Generar Cargo / Deuda del Período</h6>
      </div>
      <div class="card-body bg-light">
        <form method="post" id="formGenerateDebt" action="<?= BASE_URL ?>/payments/storeDebt">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="hidden" name="contract_id" value="<?= $contract['id'] ?>">

          <?php if ($contract['pricing_mode'] === 'por_persona'): ?>
            <div class="row align-items-end g-2 mb-3">
              <div class="col-md-3">
                <label class="form-label small fw-bold">Huéspedes</label>
                <input type="number" id="guestCount" class="form-control form-control-sm" min="0" placeholder="Ej: 2" value="">
              </div>
              <div class="col-md-2">
                <label class="form-label small fw-bold">Noches</label>
                <input type="number" id="nightCount" name="nights" class="form-control form-control-sm" min="1" value="1">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-bold">Escala (Auto)</label>
                <input type="text" id="tierDisplay" class="form-control form-control-sm" readonly placeholder="Calculando...">
              </div>
              <div class="col-md-3">
                <label class="form-label small fw-bold">Subtotal</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text">$</span>
                  <input type="text" id="baseSubtotal" class="form-control fw-bold text-end" readonly value="0">
                </div>
              </div>
            </div>
          <?php else: ?>
            <div class="mb-3">
              <p class="mb-0 text-muted small">Cobro fijo por período. Monto base:
                <strong>$<?= number_format((float) $contract['base_amount'], 0, ',', '.') ?></strong>.
              </p>
              <input type="hidden" id="baseSubtotal" value="<?= (float) $contract['base_amount'] ?>">
            </div>
          <?php endif; ?>

          <h6 class="text-primary mt-2 mb-2"><i class="fa-solid fa-calendar"></i> Período</h6>
          <div class="row g-2 mb-3">
            <div class="col-md-4">
              <label class="form-label small fw-bold mb-1">Tipo <span class="text-danger">*</span></label>
              <select name="period_type" class="form-select form-select-sm" required>
                <option value="mensual" <?= $contract['payment_frequency'] === 'mensual' ? 'selected' : '' ?>>Mensual</option>
                <option value="semanal" <?= $contract['payment_frequency'] === 'semanal' ? 'selected' : '' ?>>Semanal</option>
                <option value="quincenal" <?= $contract['payment_frequency'] === 'quincenal' ? 'selected' : '' ?>>Quincenal</option>
                <option value="anual" <?= $contract['payment_frequency'] === 'anual' ? 'selected' : '' ?>>Anual</option>
                <option value="otro" <?= !in_array($contract['payment_frequency'], ['mensual', 'semanal', 'quincenal', 'anual']) ? 'selected' : '' ?>>Otro</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold mb-1">Inicio <span class="text-danger">*</span></label>
              <input type="date" name="period_start" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold mb-1">Término <span class="text-danger">*</span></label>
              <input type="date" name="period_end" class="form-control form-control-sm" required>
            </div>
          </div>

          <hr class="my-3">

          <div class="row g-2 align-items-end">
            <div class="col-5">
              <label class="form-label small fw-bold mb-1">Total a Cobrar</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-primary text-white"><i class="fa-solid fa-dollar-sign"></i></span>
                <input type="number" name="amount" id="finalAmount" class="form-control fw-bold border-primary text-primary"
                  readonly value="<?= (float) $contract['base_amount'] ?>">
              </div>
            </div>
            <div class="col-4">
              <label class="form-label small fw-bold mb-1">Vencimiento</label>
              <input type="date" name="payment_date" class="form-control form-control-sm" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-3">
              <textarea name="notes" id="hiddenNotes" class="d-none"></textarea>
              <button type="submit" class="btn btn-primary btn-sm w-100">
                <i class="fa-solid fa-save"></i> Registrar
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ======================================== -->
<!-- Pagos registrados                        -->
<!-- ======================================== -->
<div class="card mt-4 fade-in">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h6 class="mb-0"><i class="fa-solid fa-credit-card text-primary"></i> Movimientos (<?= count($payments) ?>)</h6>
    <?php if (AuthService::hasPermission('payments_register')): ?>
      <a href="<?= BASE_URL ?>/payments/create?contract_id=<?= $contract['id'] ?>" class="btn btn-sm btn-atk">
        <i class="fa-solid fa-plus"></i> Registrar Pago
      </a>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <?php if (empty($payments)): ?>
      <div class="text-center text-muted py-3">
        <i class="fa-solid fa-wallet fa-2x mb-2 opacity-25"></i>
        <p>No hay movimientos registrados</p>
      </div>
    <?php else: ?>
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Tipo / Concepto</th>
            <th class="text-end">Monto</th>
            <th>Método</th>
            <th>Período</th>
            <th>Estado</th>
            <th>Registrado por</th>
            <?php if (AuthService::hasPermission('payments_void')): ?>
              <th class="text-center">Acciones</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $p): ?>
            <?php
            $isCharge = ($p['status'] === 'pendiente');
            $isPayment = ($p['status'] === 'pagado' || $p['status'] === 'parcial');
            $colorClass = $isCharge ? 'text-danger' : ($isPayment ? 'text-success' : 'text-muted');
            $sign = $isCharge ? '+' : ($isPayment ? '-' : '');
            ?>
            <tr>
              <td><?= date('d/m/Y', strtotime($p['payment_date'])) ?></td>
              <td>
                <?php
                // Determinar tipo/concepto del movimiento
                if (!empty($p['booking_id']) && $isCharge): ?>
                  <span class="badge bg-info text-dark">
                    <i class="fa-solid fa-calendar-check"></i> Reserva
                  </span>
                  <br><a href="<?= BASE_URL ?>/bookings/show/<?= $p['booking_id'] ?>" class="small text-decoration-none">
                    <?= htmlspecialchars($p['booking_folio'] ?? 'RES-' . $p['booking_id']) ?>
                  </a>
                <?php elseif ($isCharge && !empty($p['period_type']) && $p['period_type'] === 'servicio'): ?>
                  <span class="badge bg-info">
                    <i class="fa-solid fa-concierge-bell"></i> Servicio
                  </span>
                  <?php
                  // Extraer nombre del servicio desde las notas
                  $svcName = '';
                  if (!empty($p['notes']) && preg_match('/Cargo por servicio: (.+?)(\(|$|\n)/', $p['notes'], $m)) {
                    $svcName = trim($m[1]);
                  }
                  if ($svcName): ?>
                    <br><small class="text-muted"><?= htmlspecialchars($svcName) ?></small>
                  <?php endif; ?>
                <?php elseif ($isCharge): ?>
                  <span class="badge bg-warning text-dark">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Cargo
                  </span>
                  <?php if (!empty($p['period_type']) && $p['period_type'] !== 'otro'): ?>
                    <br><small class="text-muted"><?= ucfirst($p['period_type']) ?></small>
                  <?php endif; ?>
                <?php elseif ($p['status'] === 'anulado'): ?>
                  <span class="badge bg-dark">
                    <i class="fa-solid fa-ban"></i> Anulado
                  </span>
                <?php elseif ($isPayment): ?>
                  <span class="badge bg-success">
                    <i class="fa-solid fa-money-bill-wave"></i> Pago
                  </span>
                <?php else: ?>
                  <span class="badge bg-secondary">Otro</span>
                <?php endif; ?>
                <?php if (!empty($p['notes'])): ?>
                  <i class="fa-solid fa-comment-dots text-muted ms-1" title="<?= htmlspecialchars($p['notes']) ?>" data-bs-toggle="tooltip"></i>
                <?php endif; ?>
              </td>
              <td class="text-end fw-bold <?= $colorClass ?>"><?= $sign ?>
                $<?= number_format((float) $p['amount'], 0, ',', '.') ?></td>
              <td><?= ucfirst(htmlspecialchars($p['payment_method'])) ?></td>
              <td>
                <?php if ($p['period_start'] && $p['period_end']): ?>
                  <?php 
                    $d1 = new DateTime($p['period_start']);
                    $d2 = new DateTime($p['period_end']);
                    $diffNights = $d1->diff($d2)->days;
                  ?>
                  <?= date('d/m/y', strtotime($p['period_start'])) ?> — <?= date('d/m/y', strtotime($p['period_end'])) ?>
                  <?php if ($diffNights > 0): ?>
                    <br><small class="text-muted"><i class="fa-solid fa-moon"></i> <?= $diffNights ?> noches</small>
                  <?php endif; ?>
                <?php else: ?>
                  -
                <?php endif; ?>
              </td>
              <td>
                <?php
                $payStatusClasses = [
                  'pendiente' => 'bg-warning text-dark',
                  'pagado' => 'bg-success',
                  'parcial' => 'bg-info',
                  'anulado' => 'bg-danger',
                ];
                $pClass = $payStatusClasses[$p['status']] ?? 'bg-secondary';
                ?>
                <span class="badge <?= $pClass ?>"><?= ucfirst($p['status']) ?></span>
              </td>
              <td><small><?= htmlspecialchars($p['registered_by_name'] ?? '-') ?></small></td>
              <?php if (AuthService::hasPermission('payments_void')): ?>
                <td class="text-center">
                  <?php if ($p['status'] !== 'anulado'): ?>
                    <form method="post" action="<?= BASE_URL ?>/payments/void/<?= $p['id'] ?>"
                      onsubmit="return confirm('¿Está seguro de ANULAR este registro? No se eliminará pero dejará de afectar saldos.')"
                      class="d-inline">
                      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Anular Registro">
                        <i class="fa-solid fa-ban"></i>
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="text-muted small"><i class="fa-solid fa-lock"></i></span>
                  <?php endif; ?>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot class="table-group-divider">
          <tr>
            <td class="text-end fw-bold align-middle">Saldo Actual Cliente:</td>
            <td class="text-end fw-bold fs-5 <?= $saldo > 0 ? 'text-danger' : 'text-success' ?>">
              $<?= number_format($saldo, 0, ',', '.') ?>
            </td>
            <td colspan="5"></td>
          </tr>
        </tfoot>
      </table>
    <?php endif; ?>
  </div>
</div>

<!-- ======================================== -->
<!-- Comandas de Cocina (Alimentación)        -->
<!-- ======================================== -->
<div class="card mt-4 fade-in">
  <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h6 class="mb-0">
        <i class="fa-solid fa-utensils text-primary"></i>
        Comandas de Cocina
        <?php if($resumenComandas['total'] > 0): ?>
          <span class="badge bg-secondary ms-1"><?= (int)$resumenComandas['total'] ?></span>
        <?php endif; ?>
      </h6>
    </div>
    <div class="d-flex gap-2 flex-wrap small">
      <span class="badge bg-success"><i class="fa-solid fa-check"></i> Cobrado: <?= (int)$resumenComandas['cobrado'] ?></span>
      <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock"></i> Pendiente: <?= (int)$resumenComandas['pendiente'] ?></span>
      <span class="badge bg-info text-dark"><i class="fa-solid fa-users"></i> Personas: <?= (int)$resumenComandas['total_personas'] ?></span>
      <a href="https://www.atankalama.com/cocina/public/index.php?page=comanda/listado" target="_blank" class="btn btn-xs btn-outline-primary ms-2" style="font-size: 0.7rem; padding: 0.1rem 0.4rem;">
        <i class="fa-solid fa-external-link"></i> Abrir Cocina
      </a>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card-body border-bottom pb-3">
    <form method="GET" action="" class="row g-2 align-items-end">
      <input type="hidden" name="url" value="contracts/show/<?= (int)$contract['id'] ?>">

      <div class="col-sm-6 col-md-2">
        <label class="form-label small text-muted mb-1">Tipo</label>
        <select name="tipo_servicio" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="desayuno"          <?= ($filtrosServicio['tipo_servicio'] === 'desayuno')          ? 'selected' : '' ?>>Desayuno</option>
          <option value="cena"              <?= ($filtrosServicio['tipo_servicio'] === 'cena')              ? 'selected' : '' ?>>Cena</option>
          <option value="colacion"          <?= ($filtrosServicio['tipo_servicio'] === 'colacion')          ? 'selected' : '' ?>>Colación</option>
          <option value="colacion_especial" <?= ($filtrosServicio['tipo_servicio'] === 'colacion_especial') ? 'selected' : '' ?>>Col. Especial</option>
        </select>
      </div>

      <div class="col-sm-6 col-md-2">
        <label class="form-label small text-muted mb-1">Estado cobro</label>
        <select name="cobrado" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="0" <?= ($filtrosServicio['cobrado'] === '0') ? 'selected' : '' ?>>Pendiente</option>
          <option value="1" <?= ($filtrosServicio['cobrado'] === '1') ? 'selected' : '' ?>>Cobrado</option>
        </select>
      </div>

      <div class="col-sm-6 col-md-2">
        <label class="form-label small text-muted mb-1">Desde</label>
        <input type="date" name="fecha_desde" class="form-control form-control-sm"
               value="<?= htmlspecialchars($filtrosServicio['fecha_desde']) ?>">
      </div>

      <div class="col-sm-6 col-md-2">
        <label class="form-label small text-muted mb-1">Hasta</label>
        <input type="date" name="fecha_hasta" class="form-control form-control-sm"
               value="<?= htmlspecialchars($filtrosServicio['fecha_hasta']) ?>">
      </div>

      <div class="col-sm-6 col-md-2 d-flex align-items-end gap-1">
        <div class="form-check mb-0 ms-1">
          <input class="form-check-input" type="checkbox" name="sin_contrato" id="sinContrato" value="1"
                 <?= !empty($filtrosServicio['sin_contrato']) ? 'checked' : '' ?>>
          <label class="form-check-label small" for="sinContrato">Sin contrato</label>
        </div>
      </div>

      <div class="col-sm-6 col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-primary flex-fill">
          <i class="fa-solid fa-filter"></i> Filtrar
        </button>
        <a href="?url=contracts/show/<?= (int)$contract['id'] ?>" class="btn btn-sm btn-outline-secondary">
          <i class="fa-solid fa-xmark"></i>
        </a>
      </div>
    </form>
  </div>

  <div class="card-body p-0">
    <?php if (empty($comandas)): ?>
      <div class="text-center text-muted py-4">
        <i class="fa-solid fa-plate-wheat fa-3x mb-3 opacity-25"></i>
        <p>No hay comandas registradas con los filtros seleccionados</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover table-sm mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>Fecha</th>
              <th>Tipo</th>
              <th class="text-center">Pers.</th>
              <th>Hora</th>
              <th>Observaciones</th>
              <th class="text-center">Cobrado</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($comandas as $c): ?>
              <tr id="fila-comanda-<?= (int)$c['id'] ?>">
                <td class="text-nowrap"><?= date('d/m/Y', strtotime($c['fecha'])) ?></td>
                <td>
                  <?php
                  $tipoLabel = CocinaServicioModel::TIPOS[$c['tipo_servicio']] ?? ucfirst($c['tipo_servicio']);
                  $tipoClass = [
                    'desayuno' => 'bg-info text-dark',
                    'cena' => 'bg-primary',
                    'colacion' => 'bg-success',
                    'colacion_especial' => 'bg-warning text-dark'
                  ][$c['tipo_servicio']] ?? 'bg-secondary';
                  ?>
                  <span class="badge <?= $tipoClass ?>"><?= $tipoLabel ?></span>
                </td>
                <td class="text-center fw-bold"><?= (int)$c['cantidad_personas'] ?></td>
                <td class="text-nowrap small"><?= $c['hora_servicio'] ? substr($c['hora_servicio'], 0, 5) : '-' ?></td>
                <td class="small text-muted" style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"
                    title="<?= htmlspecialchars($c['observaciones'] ?? '') ?>">
                  <?= htmlspecialchars($c['observaciones'] ?? '-') ?>
                </td>
                <td class="text-center">
                  <button type="button"
                          class="btn btn-sm btn-cobrado-comanda <?= $c['cobrado'] ? 'btn-success' : 'btn-outline-secondary' ?>"
                          data-id="<?= (int)$c['id'] ?>"
                          data-cobrado="<?= (int)$c['cobrado'] ?>"
                          title="<?= $c['cobrado'] ? 'Cobrado — clic para revertir' : 'Marcar como cobrado' ?>">
                    <i class="fa-solid <?= $c['cobrado'] ? 'fa-check-circle' : 'fa-circle' ?>"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- ======================================== -->
<!-- Archivos Adjuntos                        -->
<!-- ======================================== -->
<div class="card mt-4 fade-in">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h6 class="mb-0"><i class="fa-solid fa-paperclip text-primary"></i> Archivos Adjuntos (<?= count($attachments) ?>)
    </h6>

  </div>
  <div class="card-body">
    <?php if (empty($attachments)): ?>
      <p class="text-muted mb-0">No hay archivos adjuntos</p>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($attachments as $att): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 border">
              <div class="card-body p-3 d-flex align-items-center">
                <div class="me-3">
                  <?php
                  $isImg = strpos($att['mime_type'], 'image') !== false;
                  $icon = $isImg ? 'fa-file-image text-info' : 'fa-file-pdf text-danger';
                  ?>
                  <i class="fa-solid <?= $icon ?> fa-2x"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                  <div class="text-truncate fw-bold" title="<?= htmlspecialchars($att['original_name']) ?>">
                    <?= htmlspecialchars($att['original_name']) ?>
                  </div>
                  <small class="text-muted d-block mt-1">
                    <?= ucfirst(str_replace('_', ' ', $att['category'])) ?>
                    <?= !empty($att['payment_id']) ? '<span class="badge bg-secondary ms-1">Pago #' . $att['payment_id'] . '</span>' : '' ?>
                    <br>
                    <i class="fa-solid fa-weight-hanging"></i> <?= number_format($att['file_size'] / 1024, 1) ?> KB
                  </small>
                </div>
                <div class="ms-2">
                  <div class="btn-group btn-group-sm">
                    <a href="<?= BASE_URL . $att['file_path'] ?>" target="_blank" class="btn btn-outline-primary"
                      title="Ver">
                      <i class="fa-solid fa-external-link"></i>
                    </a>
                    <?php if (AuthService::hasPermission('contracts_edit')): ?>
                      <form method="post" action="<?= BASE_URL ?>/contracts/deleteAttachment/<?= $att['id'] ?>"
                        onsubmit="return confirm('¿Eliminar este archivo?')" class="d-inline">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Upload -->
<div class="modal fade" id="modalUpload" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="<?= BASE_URL ?>/contracts/uploadAttachment/<?= $contract['id'] ?>"
        enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
        <div class="modal-header">
          <h5 class="modal-title">Subir Archivo Adjunto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Archivo <small class="text-muted">(PDF, JPG, PNG)</small></label>
            <input type="file" name="attachment" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Categoría</label>
            <select name="category" class="form-select">
              <option value="contrato_firmado">📜 Contrato Firmado</option>
              <option value="evidencia_cobro">📎 Evidencia de Cobro</option>
              <option value="comprobante_pago">💵 Comprobante de Pago</option>
              <option value="anexo">➕ Anexo / Adenda</option>
              <option value="identidad">🆔 Documento Identidad</option>
              <option value="otro">📁 Otro</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-atk">Subir Archivo</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ======================================== -->
<!-- Notas                                    -->
<!-- ======================================== -->
<!-- ======================================== -->
<!-- Observaciones / Crud de Notas            -->
<!-- ======================================== -->
<div class="card mt-4 fade-in">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h6 class="mb-0"><i class="fa-solid fa-sticky-note text-primary"></i> Observaciones (<?= count($notes) ?>)</h6>
  </div>
  <div class="card-body">
    <!-- Formulario para nueva nota -->
    <form method="post" action="<?= BASE_URL ?>/contracts/saveNote/<?= $contract['id'] ?>" class="mb-4">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <div class="input-group">
        <textarea name="note" class="form-control" rows="2" placeholder="Escriba una observación..." required></textarea>
        <button type="submit" class="btn btn-atk">
          <i class="fa-solid fa-paper-plane"></i> Guardar
        </button>
      </div>
    </form>

    <?php if (empty($notes)): ?>
      <p class="text-muted mb-0"><i class="fa-solid fa-info-circle"></i> No hay observaciones registradas para este contrato.</p>
    <?php else: ?>
      <div class="list-group list-group-flush border-top">
        <?php foreach ($notes as $n): ?>
          <div class="list-group-item px-0 py-3">
            <div class="d-flex justify-content-between align-items-start">
              <div class="flex-grow-1">
                <p class="mb-1 text-dark" style="white-space: pre-wrap;"><?= htmlspecialchars($n['note']) ?></p>
                <small class="text-muted">
                  <i class="fa-solid fa-user small"></i> <?= htmlspecialchars($n['user_name'] ?? 'Usuario') ?> — 
                  <i class="fa-solid fa-calendar small"></i> <?= date('d/m/Y H:i', strtotime($n['created_at'])) ?>
                </small>
              </div>
              <?php if (AuthService::hasPermission('contracts_edit')): ?>
                <form method="post" action="<?= BASE_URL ?>/contracts/deleteNote/<?= $n['id'] ?>" 
                      onsubmit="return confirm('¿Eliminar esta observación?')" class="ms-2">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                  <button type="submit" class="btn btn-link text-danger p-0" title="Eliminar">
                    <i class="fa-solid fa-trash-can"></i>
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Mostrar nota antigua si existe (migración visual) -->
    <?php if (!empty($contract['notes'])): ?>
      <div class="alert alert-light border mt-3 mb-0">
        <small class="text-muted d-block mb-1 fw-bold">Nota original del contrato:</small>
        <div class="text-muted" style="white-space: pre-wrap; font-style: italic;"><?= htmlspecialchars($contract['notes']) ?></div>
      </div>
    <?php endif; ?>
  </div>
</div>



<!-- ======================================== -->
<!-- Historial de cambios                     -->
<!-- ======================================== -->
<div class="card mt-4 mb-4 fade-in">
  <div class="card-header bg-white">
    <h6 class="mb-0"><i class="fa-solid fa-clock-rotate-left text-primary"></i> Historial (últimas
      <?= count($history) ?>)
    </h6>
  </div>
  <div class="card-body">
    <?php if (empty($history)): ?>
      <p class="text-muted mb-0">Sin registro de cambios</p>
    <?php else: ?>
      <div class="table-responsive">
        <table id="tableHistory" class="table table-sm table-borderless mb-0 align-middle" style="font-size: 0.85rem;">
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
              <?php
              $actionIcons = [
                'creado'        => 'fa-plus-circle text-success',
                'editado'       => 'fa-pen text-warning',
                'eliminado'     => 'fa-trash text-danger',
                'cambio_estado' => 'fa-exchange-alt text-info',
                'archivo_subido' => 'fa-file-arrow-up text-primary',
                'archivo_eliminado' => 'fa-file-circle-minus text-danger',
              ];
              $aIcon = $actionIcons[$h['action']] ?? 'fa-circle text-secondary';
              ?>
              <tr class="border-bottom">
                <td class="text-muted">
                  <?= date('d/m/Y', strtotime($h['created_at'])) ?> 
                  <span class="opacity-75 small"><?= date('H:i', strtotime($h['created_at'])) ?></span>
                </td>
                <td>
                  <i class="fa-solid <?= $aIcon ?> opacity-75 me-1"></i>
                  <span class="fw-bold"><?= ucfirst(str_replace('_', ' ', $h['action'])) ?></span>
                </td>
                <td class="text-muted">
                  <?= htmlspecialchars($h['description'] ?: '-') ?>
                </td>
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
    <?php endif; ?>
  </div>
</div>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>

<script>
  const tiers = <?= json_encode($tiers ?? []) ?>;
  const pricingMode = <?= json_encode($contract['pricing_mode']) ?>;
  const baseAmountContract = <?= json_encode((float) $contract['base_amount']) ?>;

  // Calcula la escala de precio y actualiza el total
  function updateCalculation() {
    let subtotalBase = 0;
    let tierText = '';

    if (pricingMode === 'por_persona') {
      const guests = parseInt(document.getElementById('guestCount').value) || 0;
      const nights = parseInt(document.getElementById('nightCount').value) || 1;
      let applicableTier = null;

      for (let i = tiers.length - 1; i >= 0; i--) {
        const t = tiers[i];
        const max = t.max_guests ? parseInt(t.max_guests) : Infinity;
        if (guests >= parseInt(t.min_guests) && guests <= max) {
          applicableTier = t;
          break;
        }
      }

      if (applicableTier) {
        const price = parseFloat(applicableTier.price_per_person);
        subtotalBase = price * guests * nights;
        let desc = applicableTier.description || 'Tier';
        tierText = `${desc} ($${price.toLocaleString('es-CL')} c/u)`;
      } else if (guests > 0) {
        tierText = 'Sin escala (Revisar contrato)';
        subtotalBase = 0;
      } else {
        tierText = 'Esperando huéspedes...';
        subtotalBase = 0;
      }

      const tierDisplay = document.getElementById('tierDisplay');
      const baseSubtotal = document.getElementById('baseSubtotal');
      if (tierDisplay) tierDisplay.value = tierText;
      if (baseSubtotal) baseSubtotal.value = subtotalBase;
    } else {
      subtotalBase = baseAmountContract;
    }

    if (document.getElementById('finalAmount')) {
      document.getElementById('finalAmount').value = subtotalBase;
    }
  }

  function calculateNights() {
    const startInput = document.querySelector('input[name="period_start"]');
    const endInput = document.querySelector('input[name="period_end"]');
    const nightInput = document.getElementById('nightCount');
    
    if (startInput && endInput && nightInput && startInput.value && endInput.value) {
      // Usar fechas UTC para evitar problemas de zona horaria al restar días
      const d1 = new Date(startInput.value + 'T00:00:00');
      const d2 = new Date(endInput.value + 'T00:00:00');
      const diffTime = d2.getTime() - d1.getTime();
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      if (diffDays >= 0) {
        nightInput.value = diffDays;
        updateCalculation();
      }
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    const guestInput = document.getElementById('guestCount');
    const nightInput = document.getElementById('nightCount');
    const startInput = document.querySelector('input[name="period_start"]');
    const endInput = document.querySelector('input[name="period_end"]');

    if (guestInput) guestInput.addEventListener('input', updateCalculation);
    if (nightInput) nightInput.addEventListener('input', updateCalculation);
    
    if (startInput) startInput.addEventListener('change', calculateNights);
    if (endInput) endInput.addEventListener('change', calculateNights);

    const formGenerate = document.getElementById('formGenerateDebt');
    if (formGenerate) {
      formGenerate.addEventListener('submit', function (e) {
        const pStart = this.period_start.value;
        const pEnd = this.period_end.value;

        if (pStart && pEnd && pStart > pEnd) {
          e.preventDefault();
          alert('La fecha de inicio del período no puede ser posterior a la fecha de término.');
          return;
        }

        let noteStr = '';
        if (pricingMode === 'por_persona') {
          const guests = parseInt(document.getElementById('guestCount').value) || 0;
          const nights = parseInt(document.getElementById('nightCount').value) || 1;
          const tierText = document.getElementById('tierDisplay').value;
          noteStr += `Cobro base por ${guests} huéspedes y ${nights} noches. Escala: ${tierText}.`;
        }

        if (noteStr === '') {
          noteStr = "Cobro según base/frecuencia estipulada.";
        }

        document.getElementById('hiddenNotes').value = noteStr.trim();
      });
    }

    if ($('#tableHistory').length) {
      $('#tableHistory').DataTable({
        pageLength: 15,
        lengthChange: false,
        searching: false,
        ordering: false,
        info: true,
        language: {
          info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
          paginate: {
            first: "Primero",
            last: "Último",
            next: "Siguiente",
            previous: "Anterior"
          }
        }
      });
    }

    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (el) {
      return new bootstrap.Tooltip(el);
    });

    // Lógica para toggle de cobrado en comandas
    const baseUrl = '<?= rtrim(BASE_URL, '/') ?>';
    document.querySelectorAll('.btn-cobrado-comanda').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const id = parseInt(this.dataset.id, 10);
        btn.disabled = true;

        fetch(baseUrl + '?url=companies/toggleCobrado/' + id, {
          method : 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (!data.success) { alert('Error al actualizar'); btn.disabled = false; return; }

          const cobrado = data.cobrado;
          btn.dataset.cobrado = cobrado;

          if (cobrado) {
            btn.classList.replace('btn-outline-secondary', 'btn-success');
            btn.querySelector('i').classList.replace('fa-circle', 'fa-check-circle');
            btn.title = 'Cobrado — clic para revertir';
          } else {
            btn.classList.replace('btn-success', 'btn-outline-secondary');
            btn.querySelector('i').classList.replace('fa-check-circle', 'fa-circle');
            btn.title = 'Marcar como cobrado';
          }
          btn.disabled = false;
        })
        .catch(function () { alert('Error de red'); btn.disabled = false; });
      });
    });
  });
</script>

