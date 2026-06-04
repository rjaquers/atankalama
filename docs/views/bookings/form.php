<?php require VIEW_PATH . "/layouts/header.php"; ?>

<?php
$isEdit = !empty($booking);
$title = $isEdit ? 'Editar Reserva' : 'Nueva Reserva';
$action = $isEdit ? BASE_URL . '/bookings/update/' . $booking['id'] : BASE_URL . '/bookings/store';
?>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <i class="fa-solid fa-exclamation-circle"></i> <?= $_SESSION['flash_error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h3><i class="fa-solid fa-calendar-plus text-primary"></i> <?= $title ?></h3>
  <a href="<?= BASE_URL ?>/bookings" class="btn btn-outline-secondary">
    <i class="fa-solid fa-arrow-left"></i> Volver
  </a>
</div>

<form method="post" action="<?= $action ?>" id="formBooking">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

  <div class="row g-4">
    <!-- Col Izquierda -->
    <div class="col-lg-7">
      <!-- Espacio y Fechas -->
      <div class="card">
        <div class="card-header bg-white">
          <h6 class="mb-0"><i class="fa-solid fa-door-open text-primary"></i> Espacio y Horario</h6>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-bold">Espacio <span class="text-danger">*</span></label>
              <select name="space_id" id="spaceSelect" class="form-select" required>
                <option value="">-- Seleccionar espacio --</option>
                <?php foreach ($spaces as $s): ?>
                  <option value="<?= $s['id'] ?>"
                    data-hourly="<?= $s['allows_hourly'] ?>" data-daily="<?= $s['allows_daily'] ?>"
                    data-monthly="<?= $s['allows_monthly'] ?>" data-capacity="<?= $s['capacity'] ?? '' ?>"
                    data-price-hour="<?= $s['base_price_hour'] ?? 0 ?>"
                    data-price-day="<?= $s['base_price_day'] ?? 0 ?>"
                    data-price-month="<?= $s['base_price_month'] ?? 0 ?>"
                    data-restrictions="<?= htmlspecialchars($s['restrictions'] ?? '') ?>"
                    <?= ($selectedSpace && $selectedSpace['id'] == $s['id']) ? 'selected' : '' ?>
                    <?= ($booking['space_id'] ?? '') == $s['id'] ? 'selected' : '' ?>
                  >
                    <?= htmlspecialchars($s['code'] . ' — ' . $s['name'] . ' (' . ucfirst($s['space_type']) . ')') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Alerta restricciones -->
            <div class="col-12" id="restrictionsAlert" style="display: none;">
              <div class="alert alert-warning mb-0 py-2">
                <i class="fa-solid fa-exclamation-triangle"></i> <strong>Restricciones:</strong>
                <span id="restrictionsText"></span>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">Modalidad</label>
              <select name="booking_mode" id="bookingMode" class="form-select">
                <option value="por_hora" <?= ($booking['booking_mode'] ?? '') === 'por_hora' ? 'selected' : '' ?>>🕐 Por Hora</option>
                <option value="por_dia" <?= ($booking['booking_mode'] ?? '') === 'por_dia' ? 'selected' : '' ?>>📅 Por Día</option>
                <option value="por_mes" <?= ($booking['booking_mode'] ?? '') === 'por_mes' ? 'selected' : '' ?>>📆 Por Mes</option>
                <option value="precio_cerrado" <?= ($booking['booking_mode'] ?? '') === 'precio_cerrado' ? 'selected' : '' ?>>💰 Precio Cerrado</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Origen</label>
              <select name="origin" class="form-select">
                <option value="ventas" <?= ($booking['origin'] ?? '') === 'ventas' ? 'selected' : '' ?>>🏷️ Ventas</option>
                <option value="administracion" <?= ($booking['origin'] ?? '') === 'administracion' ? 'selected' : '' ?>>⚙️ Administración</option>
                <option value="recepcion" <?= ($booking['origin'] ?? '') === 'recepcion' ? 'selected' : '' ?>>🛎️ Recepción</option>
                <option value="gerencia" <?= ($booking['origin'] ?? '') === 'gerencia' ? 'selected' : '' ?>>👔 Gerencia</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label fw-bold">Fecha Inicio <span class="text-danger">*</span></label>
              <input type="date" name="start_date" class="form-control" required
                     value="<?= $booking ? date('Y-m-d', strtotime($booking['start_datetime'])) : date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold">Hora Inicio</label>
              <input type="time" name="start_time" class="form-control"
                     value="<?= $booking ? date('H:i', strtotime($booking['start_datetime'])) : '09:00' ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold">Fecha Fin <span class="text-danger">*</span></label>
              <input type="date" name="end_date" class="form-control" required
                     value="<?= $booking ? date('Y-m-d', strtotime($booking['end_datetime'])) : date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold">Hora Fin</label>
              <input type="time" name="end_time" class="form-control"
                     value="<?= $booking ? date('H:i', strtotime($booking['end_datetime'])) : '18:00' ?>">
            </div>

            <!-- Indicador de disponibilidad -->
            <div class="col-12">
              <div id="availabilityCheck" class="alert alert-secondary py-2 mb-0" style="display: none;">
                <i class="fa-solid fa-spinner fa-spin"></i> Verificando disponibilidad...
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empresa / Contrato -->
      <div class="card mt-4">
        <div class="card-header bg-white">
          <h6 class="mb-0"><i class="fa-solid fa-building text-info"></i> Empresa / Cliente</h6>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Empresa</label>
              <select name="company_id" id="companySelect" class="form-select">
                <option value="">-- Sin empresa --</option>
                <?php foreach ($companies as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= ($booking['company_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['business_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Cliente / Contacto</label>
              <input type="text" name="client_name" class="form-control" placeholder="Nombre del responsable"
                     value="<?= htmlspecialchars($booking['client_name'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Contrato Asociado</label>
              <select name="contract_id" id="contractSelect" class="form-select">
                <option value="">-- Sin contrato --</option>
                <?php if ($booking && $booking['contract_id']): ?>
                  <option value="<?= $booking['contract_id'] ?>" selected>
                    <?= htmlspecialchars($booking['contract_code']) ?>
                  </option>
                <?php endif; ?>
              </select>
              <small class="text-muted">Si la reserva se imputa a un contrato vigente</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Observaciones -->
      <div class="card mt-4">
        <div class="card-header bg-white">
          <h6 class="mb-0"><i class="fa-solid fa-sticky-note text-warning"></i> Observaciones</h6>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Observaciones del cliente</label>
              <textarea name="notes_client" class="form-control" rows="2"
                        placeholder="Requerimientos del cliente"><?= htmlspecialchars($booking['notes_client'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Notas internas</label>
              <textarea name="notes_internal" class="form-control" rows="2"
                        placeholder="Notas del equipo"><?= htmlspecialchars($booking['notes_internal'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Col Derecha: Precios y Extras -->
    <div class="col-lg-5">
      <div class="card">
        <div class="card-header bg-white">
          <h6 class="mb-0"><i class="fa-solid fa-dollar-sign text-success"></i> Precio y Cobro</h6>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-bold">Valor Base Acordado</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" name="base_price" id="basePrice" class="form-control" step="100"
                       value="<?= (float)($booking['base_price'] ?? 0) ?>">
              </div>
              <small class="text-muted" id="priceHint">Ref: $0</small>
            </div>
            <div class="col-md-6">
              <label class="form-label">Descuento</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text">$</span>
                <input type="number" name="discount" class="form-control" step="100"
                       value="<?= (float)($booking['discount'] ?? 0) ?>">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Recargo</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text">$</span>
                <input type="number" name="surcharge" class="form-control" step="100"
                       value="<?= (float)($booking['surcharge'] ?? 0) ?>">
              </div>
            </div>
          </div>

          <!-- Gratuidad -->
          <div class="border rounded p-3 mt-3">
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" name="is_free" value="1" id="swFree"
                     <?= ($booking['is_free'] ?? 0) ? 'checked' : '' ?>>
              <label class="form-check-label fw-bold" for="swFree">
                🎁 Reserva Gratuita
              </label>
            </div>
            <div id="freeReasonBox" style="display: none;">
              <textarea name="free_reason" class="form-control form-control-sm" rows="2"
                        placeholder="Motivo obligatorio de la gratuidad"><?= htmlspecialchars($booking['free_reason'] ?? '') ?></textarea>
            </div>
          </div>

          <!-- Estado -->
          <div class="mt-3">
            <label class="form-label fw-bold">Estado Inicial</label>
            <select name="booking_status" class="form-select">
              <option value="confirmada" <?= ($booking['booking_status'] ?? '') === 'confirmada' ? 'selected' : '' ?>>✅ Confirmada</option>
              <option value="borrador" <?= ($booking['booking_status'] ?? '') === 'borrador' ? 'selected' : '' ?>>📝 Borrador</option>
              <?php if ($isEdit): ?>
                <option value="en_uso" <?= ($booking['booking_status'] ?? '') === 'en_uso' ? 'selected' : '' ?>>🏃 En Uso</option>
                <option value="finalizada" <?= ($booking['booking_status'] ?? '') === 'finalizada' ? 'selected' : '' ?>>🏁 Finalizada</option>
              <?php endif; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Extras -->
      <div class="card mt-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h6 class="mb-0"><i class="fa-solid fa-puzzle-piece text-primary"></i> Extras</h6>
          <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddExtra">
            <i class="fa-solid fa-plus"></i> Agregar
          </button>
        </div>
        <div class="card-body">
          <div id="extrasContainer">
            <p class="text-muted mb-0 small"><i class="fa-solid fa-info-circle"></i> Sin extras agregados</p>
          </div>

          <!-- Selector de extras (oculto por defecto) -->
          <div id="extraSelector" class="border rounded p-3 mt-3" style="display: none;">
            <div class="row g-2">
              <div class="col-md-6">
                <select id="newExtraId" class="form-select form-select-sm">
                  <option value="">-- Extra --</option>
                  <?php foreach ($extras as $ex): ?>
                    <option value="<?= $ex['id'] ?>" data-price="<?= (float)$ex['unit_price'] ?>" data-name="<?= htmlspecialchars($ex['name']) ?>">
                      <?= htmlspecialchars($ex['name']) ?> ($<?= number_format((float)$ex['unit_price'], 0, ',', '.') ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <input type="number" id="newExtraQty" class="form-control form-control-sm" value="1" min="1" placeholder="Cant">
              </div>
              <div class="col-md-3">
                <button type="button" class="btn btn-sm btn-primary w-100" id="btnConfirmExtra">
                  <i class="fa-solid fa-check"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Botones -->
      <div class="card mt-4">
        <div class="card-body d-grid gap-2">
          <button type="submit" class="btn btn-atk btn-lg">
            <i class="fa-solid fa-save"></i> <?= $isEdit ? 'Guardar Cambios' : 'Crear Reserva' ?>
          </button>
          <a href="<?= BASE_URL ?>/bookings" class="btn btn-outline-secondary">Cancelar</a>
        </div>
      </div>
    </div>
  </div>
</form>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const extrasAdded = <?= isset($items) ? json_encode(array_map(function($it) {
    return [
      'id' => $it['extra_id'],
      'name' => $it['description'],
      'price' => (float)$it['unit_price'],
      'qty' => (float)$it['quantity']
    ];
  }, $items)) : '[]' ?>;
  
  if (extrasAdded.length > 0) renderExtras();

  // Mostrar restricciones al seleccionar espacio
  const spaceSelect = document.getElementById('spaceSelect');
  spaceSelect.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const restrictions = opt.dataset.restrictions || '';
    const box = document.getElementById('restrictionsAlert');
    if (restrictions) {
      document.getElementById('restrictionsText').textContent = restrictions;
      box.style.display = 'block';
    } else {
      box.style.display = 'none';
    }

    // Actualizar hint de precio
    const mode = document.getElementById('bookingMode').value;
    let ref = 0;
    if (mode === 'por_hora') ref = parseFloat(opt.dataset.priceHour || 0);
    else if (mode === 'por_dia') ref = parseFloat(opt.dataset.priceDay || 0);
    else if (mode === 'por_mes') ref = parseFloat(opt.dataset.priceMonth || 0);
    document.getElementById('priceHint').textContent = 'Ref: $' + ref.toLocaleString('es-CL');
  });

  // Cambio de modo actualiza hint
  document.getElementById('bookingMode').addEventListener('change', function() {
    spaceSelect.dispatchEvent(new Event('change'));
  });

  // Trigger inicial si hay espacio seleccionado
  if (spaceSelect.value) {
    spaceSelect.dispatchEvent(new Event('change'));
  }

  // Gratuidad toggle
  document.getElementById('swFree').addEventListener('change', function() {
    document.getElementById('freeReasonBox').style.display = this.checked ? 'block' : 'none';
  });
  if (document.getElementById('swFree').checked) {
    document.getElementById('freeReasonBox').style.display = 'block';
  }

  // Extras
  document.getElementById('btnAddExtra').addEventListener('click', function() {
    document.getElementById('extraSelector').style.display = 
      document.getElementById('extraSelector').style.display === 'none' ? 'block' : 'none';
  });

  document.getElementById('btnConfirmExtra').addEventListener('click', function() {
    const select = document.getElementById('newExtraId');
    const opt = select.options[select.selectedIndex];
    const qty = parseInt(document.getElementById('newExtraQty').value) || 1;

    if (!select.value) return;

    extrasAdded.push({
      id: select.value,
      name: opt.dataset.name,
      price: parseFloat(opt.dataset.price),
      qty: qty
    });

    select.value = '';
    document.getElementById('newExtraQty').value = '1';
    renderExtras();
  });

  function renderExtras() {
    const container = document.getElementById('extrasContainer');
    if (extrasAdded.length === 0) {
      container.innerHTML = '<p class="text-muted mb-0 small"><i class="fa-solid fa-info-circle"></i> Sin extras agregados</p>';
      return;
    }

    let html = '';
    extrasAdded.forEach((ex, i) => {
      const sub = ex.qty * ex.price;
      html += `
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded bg-light">
          <div>
            <strong>${ex.name}</strong> <span class="text-muted">x${ex.qty}</span>
            <input type="hidden" name="extra_ids[]" value="${ex.id}">
            <input type="hidden" name="extra_qtys[]" value="${ex.qty}">
          </div>
          <div class="d-flex align-items-center gap-2">
            <span>$${sub.toLocaleString('es-CL')}</span>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeExtraItem(${i})">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        </div>
      `;
    });
    container.innerHTML = html;
  }

  window.removeExtraItem = function(index) {
    extrasAdded.splice(index, 1);
    renderExtras();
  };

  // Verificar disponibilidad al cambiar fechas/hora/espacio
  let checkTimeout;
  ['spaceSelect', 'start_date', 'start_time', 'end_date', 'end_time'].forEach(function(name) {
    const el = document.querySelector(`[name="${name}"]`) || document.getElementById(name);
    if (el) {
      el.addEventListener('change', function() {
        clearTimeout(checkTimeout);
        checkTimeout = setTimeout(checkAvailability, 500);
      });
    }
  });

  function checkAvailability() {
    const spaceId = spaceSelect.value;
    const sDate = document.querySelector('[name="start_date"]').value;
    const sTime = document.querySelector('[name="start_time"]').value;
    const eDate = document.querySelector('[name="end_date"]').value;
    const eTime = document.querySelector('[name="end_time"]').value;

    if (!spaceId || !sDate || !eDate) return;

    const start = `${sDate} ${sTime || '00:00'}:00`;
    const end = `${eDate} ${eTime || '23:59'}:00`;

    const box = document.getElementById('availabilityCheck');
    box.style.display = 'block';
    box.className = 'alert alert-secondary py-2 mb-0';
    box.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verificando disponibilidad...';

    fetch(`<?= BASE_URL ?>/bookings/checkAvailability?space_id=${spaceId}&start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}&exclude_id=<?= $booking['id'] ?? '' ?>`)
      .then(r => r.json())
      .then(data => {
        if (data.available) {
          box.className = 'alert alert-success py-2 mb-0';
          box.innerHTML = '<i class="fa-solid fa-check-circle"></i> ✅ Disponible';
        } else {
          box.className = 'alert alert-danger py-2 mb-0';
          box.innerHTML = '<i class="fa-solid fa-times-circle"></i> ❌ ' + data.message;
        }
      })
      .catch(() => {
        box.style.display = 'none';
      });
  }

  // Carga dinámica de contratos por empresa
  const companySelect = document.getElementById('companySelect');
  const contractSelect = document.getElementById('contractSelect');

  function loadContracts(companyId, selectedContractId = null) {
    if (!companyId) {
      contractSelect.innerHTML = '<option value="">-- Sin contrato --</option>';
      return;
    }

    contractSelect.innerHTML = '<option value="">Cargando...</option>';

    fetch(`<?= BASE_URL ?>/bookings/getContractsByCompany?company_id=${companyId}`)
      .then(r => {
        if (!r.ok) throw new Error('Error en el servidor: ' + r.status);
        return r.json();
      })
      .then(data => {
        let html = '<option value="">-- Sin contrato (Opcional) --</option>';
        data.forEach(c => {
          const sel = (selectedContractId == c.id) ? 'selected' : '';
          html += `<option value="${c.id}" ${sel}>${c.code}</option>`;
        });
        contractSelect.innerHTML = html;
      })
      .catch((err) => {
        console.error(err);
        contractSelect.innerHTML = '<option value="">Error al cargar contratos</option>';
      });
  }

  companySelect.addEventListener('change', function() {
    loadContracts(this.value);
  });

  // Si ya hay una empresa seleccionada al cargar (ej: edición), cargar sus contratos
  if (companySelect.value) {
    loadContracts(companySelect.value, <?= $booking['contract_id'] ?? 'null' ?>);
  }
});
</script>
