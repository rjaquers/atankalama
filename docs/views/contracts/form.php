<?php require VIEW_PATH . "/layouts/header.php"; ?>

<style>
/* Feedback de presión al estilo Emil Kowalski */
.btn-atk {
  transition: transform 150ms cubic-bezier(0.23, 1, 0.32, 1), background-color 200ms ease, opacity 200ms ease;
  position: relative;
  overflow: hidden;
}

.btn-atk:active {
  transform: scale(0.97);
}

.btn-atk:disabled {
  opacity: 0.8 !important;
  cursor: not-allowed !important;
  pointer-events: none;
}

/* Spinner rápido para mejor percepción de rendimiento (0.6s vs 1s standard) */
.spinner-fast {
  animation: spinner-spin 0.6s linear infinite;
  display: inline-block;
  margin-right: 8px;
  vertical-align: middle;
}

@keyframes spinner-spin {
  to { transform: rotate(360deg); }
}

/* Animación de entrada suave para el contenido de carga */
.button-content-loading {
  display: inline-flex;
  align-items: center;
  transition: opacity 200ms ease, transform 200ms cubic-bezier(0.23, 1, 0.32, 1);
}
</style>

<!-- Mensajes flash -->
<?php if(!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <i class="fa-solid fa-exclamation-circle"></i> <?= $_SESSION['flash_error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header">
  <h3>
    <i class="<?= !empty($isQuotation) ? 'fa-solid fa-file-invoice-dollar' : 'fa-solid fa-file-contract' ?>"></i>
    <?php 
      if (!empty($isQuotation)) {
        echo $isEdit ? 'Editar Cotización — ' . htmlspecialchars($contract['code']) : 'Nueva Cotización';
      } else {
        echo $isEdit ? 'Editar Contrato — ' . htmlspecialchars($contract['code']) : 'Nuevo Contrato';
      }
    ?>
  </h3>
  <div>
    <a href="<?= BASE_URL ?>/<?= !empty($isQuotation) ? 'quotations' : 'contracts' ?>" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
  </div>
</div>

<form method="post"
      action="<?= BASE_URL ?>/<?= !empty($isQuotation) ? 'quotations' : 'contracts' ?>/<?= $isEdit ? 'update/' . $contract['id'] : 'store' ?>"
      id="contractForm" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

  <!-- ================================================== -->
  <!-- PASO 1: Empresa + Tipo + Hotel(es)                 -->
  <!-- ================================================== -->
  <div class="card fade-in mb-4">
    <div class="card-header bg-white">
      <h6 class="mb-0">
        <span class="badge bg-primary rounded-pill me-2">1</span>
        <i class="fa-solid fa-building"></i> Empresa, Tipo y Hotel(es)
      </h6>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <!-- Empresa -->
        <div class="col-md-6">
          <label class="form-label">Empresa <span class="text-danger">*</span></label>
          <select name="company_id" class="form-select" required id="selectCompany">
            <option value="">-- Seleccione empresa --</option>
            <?php foreach($formData['companies'] as $co): ?>
            <option value="<?= $co['id'] ?>"
              <?= ((int)($contract['company_id'] ?? $preselectedCompanyId ?? 0) === (int)$co['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($co['business_name']) ?>
              <?= !empty($co['rut']) ? ' — ' . htmlspecialchars($co['rut']) : '' ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Tipo de contrato -->
        <div class="col-md-3">
          <label class="form-label"><?= !empty($isQuotation) ? 'Tipo de Cotización' : 'Tipo de Contrato' ?> <span class="text-danger">*</span></label>
          <select name="contract_type" class="form-select" required id="selectContractType">
            <option value="">-- Seleccione --</option>
            <option value="arriendo" <?= ($contract['contract_type'] ?? '') === 'arriendo' ? 'selected' : '' ?>>
              🏠 Arriendo
            </option>
            <option value="hospedaje" <?= ($contract['contract_type'] ?? '') === 'hospedaje' ? 'selected' : '' ?>>
              🛏️ Hospedaje
            </option>
            <option value="proveedor" <?= ($contract['contract_type'] ?? '') === 'proveedor' ? 'selected' : '' ?>>
              🚚 Proveedor
            </option>
          </select>
        </div>

        <!-- Plantilla (opcional) -->
        <div class="col-md-3">
          <label class="form-label">Plantilla <small class="text-muted">(opcional)</small></label>
          <select name="template_id" class="form-select" id="selectTemplate">
            <option value="">Sin plantilla</option>
            <?php foreach($formData['templates'] as $tpl): ?>
            <option value="<?= $tpl['id'] ?>"
              <?= ((int)($contract['template_id'] ?? 0) === (int)$tpl['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($tpl['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <hr class="my-4">

      <!-- Hotel(es) -->
      <h6 class="text-muted mb-3"><i class="fa-solid fa-hotel"></i> Hotel(es) donde aplica <span class="text-danger">*</span></h6>
      <div class="row g-3">
        <?php foreach($formData['hotels'] as $hotel): ?>
        <div class="col-md-4">
          <div class="form-check card p-3 border">
            <input class="form-check-input" type="checkbox" name="hotel_ids[]"
                   value="<?= $hotel['id'] ?>" id="hotel_<?= $hotel['id'] ?>"
                   <?= in_array($hotel['id'], $selectedHotels) ? 'checked' : '' ?>>
            <label class="form-check-label ms-2" for="hotel_<?= $hotel['id'] ?>">
              <strong><?= htmlspecialchars($hotel['name']) ?></strong>
              <?php if(!empty($hotel['code'])): ?>
                <span class="badge bg-light text-dark ms-1"><?= htmlspecialchars($hotel['code']) ?></span>
              <?php endif; ?>
              <?php if(!empty($hotel['address'])): ?>
                <br><small class="text-muted"><?= htmlspecialchars($hotel['address']) ?></small>
              <?php endif; ?>
            </label>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- ================================================== -->
  <!-- PASO 2: Fechas, Montos, Frecuencia                 -->
  <!-- ================================================== -->
  <div class="card fade-in mb-4">
    <div class="card-header bg-white">
      <h6 class="mb-0">
        <span class="badge bg-primary rounded-pill me-2">2</span>
        <i class="fa-solid fa-calendar-days"></i> Fechas, Montos y Condiciones
      </h6>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <!-- Duración -->
        <div class="col-md-3">
          <label class="form-label">Tipo de Duración <span class="text-danger">*</span></label>
          <select name="duration_type" class="form-select" id="selectDurationType" required>
            <option value="indefinido" <?= ($contract['duration_type'] ?? 'indefinido') === 'indefinido' ? 'selected' : '' ?>>
              ♾️ Indefinido
            </option>
            <option value="plazo_fijo" <?= ($contract['duration_type'] ?? '') === 'plazo_fijo' ? 'selected' : '' ?>>
              📅 Plazo Fijo
            </option>
            <option value="por_temporada" <?= ($contract['duration_type'] ?? '') === 'por_temporada' ? 'selected' : '' ?>>
              🌤️ Por Temporada
            </option>
          </select>
        </div>

        <!-- Fecha inicio -->
        <div class="col-md-3">
          <label class="form-label">Fecha Inicio <span class="text-danger">*</span></label>
          <input type="date" name="start_date" class="form-control" required
                 value="<?= htmlspecialchars($contract['start_date'] ?? date('Y-m-d')) ?>">
        </div>

        <!-- Fecha término -->
        <div class="col-md-3" id="endDateWrapper">
          <label class="form-label">Fecha Término</label>
          <input type="date" name="end_date" class="form-control" id="inputEndDate"
                 value="<?= htmlspecialchars($contract['end_date'] ?? '') ?>">
        </div>

        <!-- Estado -->
        <div class="col-md-3">
          <label class="form-label">Estado</label>
          <select name="status" class="form-select">
            <?php if(!empty($isQuotation)): ?>
              <option value="quotation_draft" <?= ($contract['status'] ?? 'quotation_draft') === 'quotation_draft' ? 'selected' : '' ?>>📝 Borrador Cotización</option>
              <option value="quotation_sent" <?= ($contract['status'] ?? '') === 'quotation_sent' ? 'selected' : '' ?>>📤 Enviada</option>
              <option value="quotation_approved" <?= ($contract['status'] ?? '') === 'quotation_approved' ? 'selected' : '' ?>>🤝 Aprobada</option>
            <?php else: ?>
              <option value="borrador" <?= ($contract['status'] ?? 'borrador') === 'borrador' ? 'selected' : '' ?>>📝 Borrador</option>
              <option value="vigente" <?= ($contract['status'] ?? '') === 'vigente' ? 'selected' : '' ?>>✅ Vigente</option>
              <option value="por_renovar" <?= ($contract['status'] ?? '') === 'por_renovar' ? 'selected' : '' ?>>⏳ Por Renovar</option>
            <?php endif; ?>
          </select>
        </div>
      </div>

      <?php if(empty($isQuotation)): ?>
      <hr class="my-4">

      <div class="row g-3">
        <!-- Modo de precio -->
        <div class="col-md-3">
          <label class="form-label">Modo de Precio</label>
          <select name="pricing_mode" class="form-select" id="selectPricingMode">
            <option value="grupo" <?= ($contract['pricing_mode'] ?? 'grupo') === 'grupo' ? 'selected' : '' ?>>
              👥 Precio por grupo (fijo)
            </option>
            <option value="por_persona" <?= ($contract['pricing_mode'] ?? '') === 'por_persona' ? 'selected' : '' ?>>
              🧑 Precio por persona
            </option>
          </select>
        </div>

        <!-- Monto base -->
        <div class="col-md-3">
          <label class="form-label">Monto Base ($)</label>
          <input type="number" name="base_amount" class="form-control" step="1" min="0"
                 placeholder="0"
                 value="<?= (int)($contract['base_amount'] ?? 0) ?>">
        </div>

        <!-- Huéspedes base -->
        <div class="col-md-3" id="baseGuestsWrapper">
          <label class="form-label">Huéspedes Base</label>
          <input type="number" name="base_guests" class="form-control" min="0"
                 placeholder="Cantidad base"
                 value="<?= htmlspecialchars($contract['base_guests'] ?? '') ?>">
        </div>

        <!-- Frecuencia de pago -->
        <div class="col-md-3">
          <label class="form-label">Frecuencia de Pago</label>
          <select name="payment_frequency" class="form-select">
            <option value="semanal" <?= ($contract['payment_frequency'] ?? '') === 'semanal' ? 'selected' : '' ?>>Semanal</option>
            <option value="quincenal" <?= ($contract['payment_frequency'] ?? '') === 'quincenal' ? 'selected' : '' ?>>Quincenal</option>
            <option value="mensual" <?= ($contract['payment_frequency'] ?? 'mensual') === 'mensual' ? 'selected' : '' ?>>Mensual</option>
            <option value="otro" <?= ($contract['payment_frequency'] ?? '') === 'otro' ? 'selected' : '' ?>>Otro</option>
          </select>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ================================================== -->
  <!-- PASO 3: Servicios incluidos                        -->
  <!-- ================================================== -->
  <div class="card fade-in mb-4">
    <div class="card-header bg-white">
      <h6 class="mb-0">
        <span class="badge bg-primary rounded-pill me-2">3</span>
        <i class="fa-solid fa-concierge-bell"></i> Servicios Incluidos y Precios Personalizados
      </h6>
    </div>
    <div class="card-body">
      <?php if(empty($formData['services'])): ?>
        <p class="text-muted">No hay servicios configurados.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th width="40"></th>
                <th>Servicio</th>
                <th width="150">Valor Unitario</th>
                <th width="120">Moneda</th>
                <th width="150">Tipo Cobro</th>
                <th>Notas del Servicio</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($formData['services'] as $svc): 
                $isChecked = in_array($svc['id'], $selectedServices);
                $svcDetail = $isChecked ? array_filter($services, function($s) use($svc) { return (int)$s['id'] === (int)$svc['id']; }) : [];
                $svcDetail = !empty($svcDetail) ? reset($svcDetail) : null;
                
                // Valor por defecto: si no hay detalle (creación), usar base_price del catálogo
                $displayPrice = $svcDetail ? (float)$svcDetail['unit_price'] : (float)($svc['base_price'] ?? 0);
              ?>
              <tr>
                <td>
                  <input class="form-check-input svc-check" type="checkbox" name="service_ids[]"
                         value="<?= $svc['id'] ?>" id="svc_<?= $svc['id'] ?>"
                         <?= $isChecked ? 'checked' : '' ?>>
                </td>
                <td>
                  <label class="form-check-label fw-bold" for="svc_<?= $svc['id'] ?>">
                    <?= htmlspecialchars($svc['name']) ?>
                  </label>
                </td>
                <td>
                  <input type="number" name="service_prices[<?= $svc['id'] ?>]" class="form-control form-control-sm"
                         step="0.01" value="<?= $displayPrice ?>" 
                         placeholder="0.00" <?= $isChecked ? '' : 'disabled' ?>>
                </td>
                <td>
                  <select name="service_currencies[<?= $svc['id'] ?>]" class="form-select form-select-sm" <?= $isChecked ? '' : 'disabled' ?>>
                    <option value="CLP" <?= ($svcDetail['currency'] ?? 'CLP') === 'CLP' ? 'selected' : '' ?>>$ CLP</option>
                    <option value="UF" <?= ($svcDetail['currency'] ?? '') === 'UF' ? 'selected' : '' ?>>UF</option>
                  </select>
                </td>
                <td>
                  <select name="service_billings[<?= $svc['id'] ?>]" class="form-select form-select-sm" <?= $isChecked ? '' : 'disabled' ?>>
                    <option value="per_person" <?= ($svcDetail['billing_type'] ?? 'per_person') === 'per_person' ? 'selected' : '' ?>>Por persona</option>
                    <option value="per_day" <?= ($svcDetail['billing_type'] ?? '') === 'per_day' ? 'selected' : '' ?>>Por día</option>
                    <option value="per_event" <?= ($svcDetail['billing_type'] ?? '') === 'per_event' ? 'selected' : '' ?>>Por evento</option>
                  </select>
                </td>
                <td>
                  <input type="text" name="service_notes[<?= $svc['id'] ?>]" class="form-control form-control-sm"
                         value="<?= htmlspecialchars($svcDetail['contract_notes'] ?? '') ?>" 
                         placeholder="Notas específicas..." <?= $isChecked ? '' : 'disabled' ?>>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ================================================== -->
  <!-- PASO 4: Archivos Adjuntos (Fotos/PDFs)             -->
  <!-- ================================================== -->
  <?php if(!empty($isQuotation)): ?>
  <div class="card fade-in mb-4">
    <div class="card-header bg-white">
      <h6 class="mb-0">
        <span class="badge bg-primary rounded-pill me-2">4</span>
        <i class="fa-solid fa-paperclip"></i> Archivos Adjuntos (Fotos, Planos, Diseños)
      </h6>
    </div>
    <div class="card-body">
      <?php if(!empty($attachments)): ?>
        <div class="row g-2 mb-3">
          <label class="form-label small fw-bold text-muted">Archivos actuales:</label>
          <?php foreach($attachments as $att): 
            $isImg = strpos($att['mime_type'], 'image') !== false;
          ?>
          <div class="col-md-3 col-lg-2">
            <div class="card h-100 border shadow-sm">
              <?php if($isImg): ?>
                <img src="<?= BASE_URL . $att['file_path'] ?>" class="card-img-top" style="height: 100px; object-fit: cover;">
              <?php else: ?>
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 100px;">
                  <i class="fa-solid fa-file-pdf fa-3x text-danger"></i>
                </div>
              <?php endif; ?>
              <div class="card-body p-2 text-center">
                <small class="text-truncate d-block" title="<?= htmlspecialchars($att['original_name']) ?>">
                  <?= htmlspecialchars($att['original_name']) ?>
                </small>
                <a href="<?= BASE_URL . $att['file_path'] ?>" target="_blank" class="btn btn-sm btn-link p-0 mt-1">
                  <i class="fa-solid fa-download"></i> Ver
                </a>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <hr>
      <?php endif; ?>

      <div class="mb-3">
        <label class="form-label">Subir nuevos archivos <small class="text-muted">(PDF, JPG, PNG)</small></label>
        <input type="file" name="attachments[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png">
      </div>
      <div id="filePreviews" class="row g-2"></div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ================================================== -->
  <!-- PASO 4: Escalas de precio (tiers) — solo por_persona -->
  <!-- ================================================== -->
  <?php if(empty($isQuotation)): ?>
  <div class="card fade-in mb-4" id="tiersSection" style="display: none;">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
...
    </div>
  </div>
  <?php endif; ?>

  <!-- ================================================== -->
  <!-- Notas                                              -->
  <!-- ================================================== -->
  <div class="card fade-in mb-4">
    <div class="card-header bg-white">
      <h6 class="mb-0">
        <i class="fa-solid fa-sticky-note"></i> Notas Adicionales
      </h6>
    </div>
    <div class="card-body">
      <textarea name="notes" class="form-control" rows="3"
                placeholder="Observaciones, condiciones especiales, acuerdos verbales..."><?= htmlspecialchars($contract['notes'] ?? '') ?></textarea>
    </div>
  </div>

  <!-- Botones -->
  <div class="d-flex gap-2 mb-4">
    <button type="submit" class="btn btn-atk btn-lg">
      <i class="fa-solid fa-save"></i>
      <?php 
        if ($isEdit) {
          echo 'Guardar Cambios';
        } else {
          echo !empty($isQuotation) ? 'Crear Cotización' : 'Crear Contrato';
        }
      ?>
    </button>
    <?php if($isEdit && !empty($isQuotation)): ?>
      <a href="<?= BASE_URL ?>/quotations/preview/<?= $contract['id'] ?>" target="_blank" class="btn btn-outline-atk btn-lg">
        <i class="fa-solid fa-file-pdf"></i> Ver PDF
      </a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/<?= !empty($isQuotation) ? 'quotations' : 'contracts' ?>" class="btn btn-secondary btn-lg">Cancelar</a>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ==========================================
    // Habilitar/deshabilitar campos de servicios
    // ==========================================
    const svcChecks = document.querySelectorAll('.svc-check');
    svcChecks.forEach(check => {
        check.addEventListener('change', function() {
            const row = this.closest('tr');
            const inputs = row.querySelectorAll('input:not(.svc-check), select');
            inputs.forEach(input => {
                if (this.checked) {
                    input.removeAttribute('disabled');
                } else {
                    input.setAttribute('disabled', 'disabled');
                }
            });
        });
    });

    // ==========================================
    // Toggle fecha término según tipo de duración
    // ==========================================
    const selectDuration = document.getElementById('selectDurationType');
    const endDateWrapper = document.getElementById('endDateWrapper');
    const inputEndDate   = document.getElementById('inputEndDate');

    function toggleEndDate() {
        if (selectDuration.value === 'indefinido') {
            endDateWrapper.style.opacity = '0.5';
            inputEndDate.removeAttribute('required');
        } else {
            endDateWrapper.style.opacity = '1';
            if (selectDuration.value === 'plazo_fijo') {
                inputEndDate.setAttribute('required', 'required');
            }
        }
    }
    selectDuration.addEventListener('change', toggleEndDate);
    toggleEndDate();

    // ==========================================
    // Toggle sección tiers según pricing_mode
    // ==========================================
    const selectPricing = document.getElementById('selectPricingMode');
    const tiersSection  = document.getElementById('tiersSection');
    const baseGuestsW   = document.getElementById('baseGuestsWrapper');

    function toggleTiers() {
        if (selectPricing.value === 'por_persona') {
            tiersSection.style.display = '';
            baseGuestsW.style.display = '';
        } else {
            tiersSection.style.display = 'none';
            baseGuestsW.style.display = 'none';
        }
    }
    selectPricing.addEventListener('change', toggleTiers);
    toggleTiers();

    // ==========================================
    // Agregar/eliminar filas de tiers
    // ==========================================
    let tierIndex = document.querySelectorAll('.tier-row').length;
    const tiersContainer = document.getElementById('tiersContainer');

    document.getElementById('btnAddTier').addEventListener('click', function() {
        const html = `
        <div class="tier-row card p-3 mb-2 border" data-index="${tierIndex}">
          <div class="row g-2 align-items-end">
            <div class="col-md-2">
              <label class="form-label small">Mín. Huéspedes</label>
              <input type="number" name="tiers[${tierIndex}][min_guests]" class="form-control form-control-sm"
                     min="1" value="1" required>
            </div>
            <div class="col-md-2">
              <label class="form-label small">Máx. Huéspedes</label>
              <input type="number" name="tiers[${tierIndex}][max_guests]" class="form-control form-control-sm"
                     min="0" placeholder="∞ (vacío)">
            </div>
            <div class="col-md-2">
              <label class="form-label small">$/persona</label>
              <input type="number" name="tiers[${tierIndex}][price_per_person]" class="form-control form-control-sm"
                     min="0" step="1" value="0" required>
            </div>
            <div class="col-md-2">
              <label class="form-label small">Desc. %</label>
              <input type="number" name="tiers[${tierIndex}][discount_percent]" class="form-control form-control-sm"
                     min="0" max="100" step="0.1" value="0">
            </div>
            <div class="col-md-3">
              <label class="form-label small">Descripción</label>
              <input type="text" name="tiers[${tierIndex}][description]" class="form-control form-control-sm"
                     placeholder="Ej: Tier ${tierIndex + 1}">
            </div>
            <div class="col-md-1">
              <button type="button" class="btn btn-sm btn-outline-danger btn-remove-tier" title="Eliminar">
                <i class="fa-solid fa-times"></i>
              </button>
            </div>
          </div>
        </div>`;
        tiersContainer.insertAdjacentHTML('beforeend', html);
        tierIndex++;
    });

    tiersContainer.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-remove-tier');
        if (btn) {
            btn.closest('.tier-row').remove();
        }
    });

    // ==========================================
    // Feedback de carga Emil Kowalski al enviar
    // ==========================================
    const contractForm = document.getElementById('contractForm');
    contractForm.addEventListener('submit', function(e) {
        const btn = this.querySelector('button[type="submit"]');
        
        // Evitar múltiples envíos si ya está cargando
        if (btn.classList.contains('is-loading')) {
            e.preventDefault();
            return;
        }

        // Estado visual de carga
        btn.classList.add('is-loading');
        btn.setAttribute('disabled', 'disabled');
        
        const isQuotation = <?= !empty($isQuotation) ? 'true' : 'false' ?>;
        const loadingText = isQuotation ? 'Creando Cotización...' : 'Guardando Contrato...';

        // Reemplazar contenido con spinner y animación suave
        btn.innerHTML = `
            <span class="button-content-loading" style="opacity: 0; transform: scale(0.95);">
                <i class="fa-solid fa-circle-notch spinner-fast"></i>
                ${loadingText}
            </span>
        `;

        // Micro-delay para que el navegador registre el cambio y ejecute la transición
        setTimeout(() => {
            const content = btn.querySelector('.button-content-loading');
            if (content) {
                content.style.opacity = '1';
                content.style.transform = 'scale(1)';
            }
        }, 10);
    });
});
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
