<?php require VIEW_PATH . "/layouts/header.php"; ?>

<!-- Mensajes flash -->
<?php if(!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <i class="fa-solid fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
  <h3>
    <i class="fa-solid fa-credit-card"></i> 
    <?= $isEdit ? 'Editar Pago #' . $payment['id'] : 'Registrar Nuevo Pago' ?>
  </h3>
  <div>
    <a href="<?= BASE_URL ?>/contracts/show/<?= $contract['id'] ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fa-solid fa-arrow-left"></i> Volver al Contrato
    </a>
  </div>
</div>

<div class="row g-4">
  <!-- Información del Contrato Relacionado -->
  <div class="col-lg-4">
    <div class="card h-100 border-primary fade-in">
      <div class="card-header bg-primary text-white">
        <h6 class="mb-0"><i class="fa-solid fa-file-contract"></i> Información del Contrato</h6>
      </div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr>
            <td class="text-muted" style="width: 40%">Código:</td>
            <td class="fw-bold"><?= htmlspecialchars($contract['code']) ?></td>
          </tr>
          <tr>
            <td class="text-muted">Empresa:</td>
            <td class="fw-bold">
              <a href="<?= BASE_URL ?>/companies/show/<?= $contract['company_id'] ?>" class="text-decoration-none">
                <?= htmlspecialchars($contract['business_name']) ?>
              </a>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Monto Base:</td>
            <td class="fw-bold">$<?= number_format((float)$contract['base_amount'], 0, ',', '.') ?></td>
          </tr>
          <tr>
              <td class="text-muted">Frecuencia:</td>
              <td class="fw-bold"><?= ucfirst(htmlspecialchars($contract['payment_frequency'])) ?></td>
          </tr>
          <tr>
            <td class="text-muted">Tipo:</td>
            <td>
              <span class="badge bg-info"><?= ucfirst(htmlspecialchars($contract['contract_type'])) ?></span>
            </td>
          </tr>
        </table>
        
        <hr class="my-3">
        
        <div class="text-center p-3 rounded" style="background: var(--atk-bg-subtle);">
            <div class="text-muted small">Estado Actual</div>
            <div class="badge badge-<?= $contract['status'] ?> fs-6 mt-1"><?= ucfirst(str_replace('_', ' ', $contract['status'])) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Formulario de Pago -->
  <div class="col-lg-8">
    <div class="card fade-in">
      <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fa-solid fa-dollar-sign text-primary"></i> Datos del Pago</h6>
      </div>
      <div class="card-body p-4">
        <form id="paymentForm" method="post" action="<?= BASE_URL ?>/payments/<?= $isEdit ? 'update/' . $payment['id'] : 'store' ?>" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="hidden" name="contract_id" value="<?= $contract['id'] ?>">

          <div class="row g-3 mb-4">


          <div class="row g-3 mb-4">
            <!-- Monto del Pago -->
            <div class="col-md-6">
              <label class="form-label fw-bold">Monto del Pago ($) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-money-bill-wave"></i></span>
                <input type="number" name="amount" id="finalAmount" class="form-control form-control-lg" step="0.01" min="0.01" required
                       placeholder="0.00"
                       value="<?= (float)($payment['amount'] ?? '') ?>">
              </div>
            </div>

            <!-- Fecha del Pago -->
            <div class="col-md-6">
              <label class="form-label fw-bold">Fecha del Pago <span class="text-danger">*</span></label>
              <input type="date" name="payment_date" class="form-control form-control-lg" required
                     value="<?= htmlspecialchars($payment['payment_date'] ?? date('Y-m-d')) ?>">
            </div>
          </div>

          <div class="row g-3 mb-4">
            <!-- Método de Pago -->
            <div class="col-md-6">
              <label class="form-label fw-bold">Método de Pago <span class="text-danger">*</span></label>
              <select name="payment_method" class="form-select form-select-lg" required>
                <option value="">Seleccione...</option>
                <option value="transferencia" <?= ($payment['payment_method'] ?? '') === 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
                <option value="efectivo" <?= ($payment['payment_method'] ?? '') === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                <option value="cheque" <?= ($payment['payment_method'] ?? '') === 'cheque' ? 'selected' : '' ?>>Cheque</option>
                <option value="tarjeta" <?= ($payment['payment_method'] ?? '') === 'tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                <option value="otro" <?= ($payment['payment_method'] ?? '') === 'otro' ? 'selected' : '' ?>>Otro</option>
              </select>
            </div>

            <!-- Número de Referencia (Comprobante) -->
            <div class="col-md-6">
              <label class="form-label fw-bold">N° Referencia / Comprobante</label>
              <input type="text" name="reference_number" class="form-control form-control-lg"
                     placeholder="Ej: Transf. 123456"
                     value="<?= htmlspecialchars($payment['reference_number'] ?? '') ?>">
            </div>
          </div>
          
          <div class="row g-3 mb-4">
              <!-- Adjunto de pago -->
              <div class="col-12">
                  <label class="form-label fw-bold"><i class="fa-solid fa-paperclip text-primary"></i> Subir Comprobante Adicional (Opcional)</label>
                  <input type="file" name="payment_proof" class="form-control" accept=".pdf,image/jpeg,image/png,image/webp">
                  <small class="text-muted">Si tienes una foto o PDF de la transferencia pagada, adjúntala aquí.</small>
              </div>
          </div>

          <hr class="my-4">

          <h6 class="mb-3 text-muted"><i class="fa-solid fa-calendar text-primary"></i> Período que Cubre el Pago</h6>
          
          <div class="row g-3 mb-4">
            <!-- Tipo de Periodo -->
            <div class="col-md-4">
              <label class="form-label fw-bold">Tipo de Período <span class="text-danger">*</span></label>
              <select name="period_type" class="form-select" required>
                <option value="mensual" <?= ($payment['period_type'] ?? $contract['payment_frequency'] === 'mensual' ? 'selected' : '') == 'mensual' ? 'selected' : '' ?>>Mensual</option>
                <option value="semanal" <?= ($payment['period_type'] ?? '') === 'semanal' ? 'selected' : '' ?>>Semanal</option>
                <option value="quincenal" <?= ($payment['period_type'] ?? '') === 'quincenal' ? 'selected' : '' ?>>Quincenal</option>
                <option value="otro" <?= ($payment['period_type'] ?? '') === 'otro' ? 'selected' : '' ?>>Otro</option>
              </select>
            </div>

            <!-- Desde -->
            <div class="col-md-4">
              <label class="form-label fw-bold">Fecha Inicio</label>
              <input type="date" name="period_start" class="form-control"
                     value="<?= htmlspecialchars($payment['period_start'] ?? '') ?>">
            </div>

            <!-- Hasta -->
            <div class="col-md-4">
              <label class="form-label fw-bold">Fecha Término</label>
              <input type="date" name="period_end" class="form-control"
                     value="<?= htmlspecialchars($payment['period_end'] ?? '') ?>">
            </div>
          </div>

          <div class="row g-3 mb-4">
              <!-- Estado del Pago (Solo si es Admin o Cobranzas) -->
              <div class="col-md-4">
                  <label class="form-label fw-bold">Estado</label>
                  <select name="status" class="form-select">
                      <option value="pagado" <?= ($payment['status'] ?? 'pagado') === 'pagado' ? 'selected' : '' ?>>✅ Pagado</option>
                      <option value="pendiente" <?= ($payment['status'] ?? '') === 'pendiente' ? 'selected' : '' ?>>⚠️ Pendiente</option>
                      <option value="parcial" <?= ($payment['status'] ?? '') === 'parcial' ? 'selected' : '' ?>>📘 Parcial</option>
                  </select>
              </div>
          </div>

          <!-- Notas adicionales -->
          <div class="mb-4">
            <label class="form-label fw-bold">Notas u Observaciones</label>
            <textarea name="notes" id="paymentNotes" class="form-control" rows="3"
                      placeholder="Agrega información relevante sobre el pago..."><?= htmlspecialchars($payment['notes'] ?? '') ?></textarea>
          </div>

          <!-- Botones de Acción -->
          <div class="d-flex gap-2 justify-content-end border-top pt-4">
            <button type="submit" class="btn btn-atk btn-lg shadow-sm">
                <i class="fa-solid fa-save"></i> <?= $isEdit ? 'Guardar Cambios' : 'Registrar Pago' ?>
            </button>
            <a href="<?= BASE_URL ?>/contracts/show/<?= $contract['id'] ?>" class="btn btn-secondary btn-lg">
                Cancelar
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Validaciones de fechas simples
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const pStart = this.period_start.value;
    const pEnd = this.period_end.value;
    
    if (pStart && pEnd && pStart > pEnd) {
        e.preventDefault();
        alert('La fecha de inicio del período no puede ser posterior a la fecha de término.');
    }
});
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
