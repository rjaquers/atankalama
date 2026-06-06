<?php require VIEW_PATH . "/layouts/header.php"; ?>

<style>
/* --- Emil Kowalski Principles --- */

/* 1. Responsividad Táctil */
.btn-atk, .btn-outline-atk, .btn-success, .btn-primary, .btn-outline-secondary {
  transition: transform 150ms cubic-bezier(0.23, 1, 0.32, 1), background-color 200ms ease;
}
.btn-atk:active, .btn-outline-atk:active, .btn-success:active, .btn-primary:active, .btn-outline-secondary:active {
  transform: scale(0.97);
}

/* 2. Entrada Escalonada (Stagger) */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px) scale(0.99);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.fade-in-up {
  opacity: 0;
  animation: fadeInUp 400ms cubic-bezier(0.23, 1, 0.32, 1) forwards;
}

.delay-1 { animation-delay: 50ms; }
.delay-2 { animation-delay: 100ms; }
.delay-3 { animation-delay: 150ms; }
.delay-4 { animation-delay: 200ms; }

/* 3. Pulido Visual */
.card {
  border-radius: 12px;
  overflow: hidden;
  transition: box-shadow 200ms ease;
}
.card:hover {
  box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
}

.badge {
  padding: 0.5em 0.8em;
  font-weight: 500;
  letter-spacing: 0.02em;
}

.card-header.bg-atk {
  background: linear-gradient(135deg, #1a3a5c 0%, #2a5a8c 100%);
}

/* 4. Imagenes con Fade */
.card-img-top {
  transition: filter 300ms ease;
}
.card:hover .card-img-top {
  filter: brightness(1.05);
}
</style>

<div class="page-header fade-in-up">
  <h3>
    <i class="fa-solid fa-file-invoice-dollar text-atk"></i>
    Cotización: <?= htmlspecialchars($quotation['code']) ?> 
    <span class="badge bg-light text-dark border">Versión <?= (int)$quotation['version_number'] ?></span>
  </h3>
  <div class="d-flex gap-2">
    <a href="<?= BASE_URL ?>/quotations" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
    <a href="<?= BASE_URL ?>/quotations/edit/<?= $quotation['id'] ?>" class="btn btn-outline-atk">
      <i class="fa-solid fa-pen"></i> Editar
    </a>
    <a href="<?= BASE_URL ?>/quotations/createVersion/<?= $quotation['id'] ?>" class="btn btn-outline-success">
      <i class="fa-solid fa-copy"></i> Copiar Cotización
    </a>
    <?php if($quotation['status'] !== 'quotation_approved'): ?>
    <a href="<?= BASE_URL ?>/quotations/approve/<?= $quotation['id'] ?>" class="btn btn-success" onclick="return confirm('¿Marcar como aprobada y proceder a contrato?')">
      <i class="fa-solid fa-handshake"></i> Aprobar
    </a>
    <?php endif; ?>
<?php if(!empty($quotation['generated_pdf_path'])): 
      $pdfUrl = (strpos($quotation['generated_pdf_path'], '/uploads/') === 0) 
                ? $quotation['generated_pdf_path'] 
                : '/public/uploads/contracts/' . $quotation['generated_pdf_path'];
    ?>
      <a href="<?= BASE_URL . $pdfUrl ?>" target="_blank" class="btn btn-primary">
        <i class="fa-solid fa-download"></i> Descargar PDF
      </a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/quotations/generatePdf/<?= $quotation['id'] ?>" class="btn btn-atk">
      <i class="fa-solid fa-file-pdf"></i> Regenerar PDF
    </a>
    <button type="button" class="btn btn-info text-white"
            data-bs-toggle="modal" data-bs-target="#modalEnviarCotizacion">
      <i class="fa-solid fa-paper-plane"></i> Enviar
    </button>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- MODAL: Enviar Cotización por correo                        -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalEnviarCotizacion" tabindex="-1" aria-labelledby="modalEnviarLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="modalEnviarLabel">
          <i class="fa-solid fa-paper-plane me-2"></i>
          Enviar cotización — <?= htmlspecialchars($quotation['code']) ?>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form method="POST" action="<?= BASE_URL ?>/quotations/sendEmail/<?= $quotation['id'] ?>">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <div class="modal-body">

          <!-- DESTINATARIOS -->
          <div class="mb-4">
            <label class="form-label fw-bold">
              <i class="fa-solid fa-users me-1 text-info"></i> Destinatarios
            </label>

            <div id="listaDestinatarios" class="d-flex flex-column gap-2 p-3 bg-light rounded">

              <?php /* Contacto principal de la empresa */ ?>
              <?php if (!empty($quotation['contact_email'])): ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="recipients[]"
                       id="dest_principal" value="<?= htmlspecialchars($quotation['contact_email']) ?>" checked>
                <label class="form-check-label" for="dest_principal">
                  <strong><?= htmlspecialchars($quotation['contact_name'] ?? 'Contacto principal') ?></strong>
                  <span class="text-muted ms-1"><?= htmlspecialchars($quotation['contact_email']) ?></span>
                  <span class="badge bg-secondary ms-1" style="font-size:.65rem;">principal</span>
                </label>
              </div>
              <?php endif; ?>

              <?php /* Contactos adicionales de la empresa */ ?>
              <?php foreach ($contacts as $ct): ?>
              <div class="form-check destinatario-item" data-id="<?= $ct['id'] ?>">
                <input class="form-check-input" type="checkbox" name="recipients[]"
                       id="dest_<?= $ct['id'] ?>" value="<?= htmlspecialchars($ct['email']) ?>" checked>
                <label class="form-check-label" for="dest_<?= $ct['id'] ?>">
                  <strong><?= htmlspecialchars($ct['name']) ?></strong>
                  <span class="text-muted ms-1"><?= htmlspecialchars($ct['email']) ?></span>
                  <?php if ($ct['role']): ?>
                    <span class="badge bg-light text-dark border ms-1" style="font-size:.65rem;"><?= htmlspecialchars($ct['role']) ?></span>
                  <?php endif; ?>
                </label>
              </div>
              <?php endforeach; ?>

              <?php /* Correo del vendedor/usuario actual */ ?>
              <div class="form-check border-top pt-2 mt-1">
                <input class="form-check-input" type="checkbox" name="recipients[]"
                       id="dest_yo" value="<?= htmlspecialchars($_SESSION['user_email']) ?>">
                <label class="form-check-label" for="dest_yo">
                  <i class="fa-solid fa-user me-1 text-primary"></i>
                  <strong>Mi correo</strong>
                  <span class="text-muted ms-1"><?= htmlspecialchars($_SESSION['user_email']) ?></span>
                </label>
              </div>

            </div><!-- /listaDestinatarios -->

            <!-- Agregar nuevo contacto inline -->
            <div class="mt-2">
              <button type="button" class="btn btn-sm btn-outline-secondary"
                      onclick="toggleFormContacto()">
                <i class="fa-solid fa-user-plus me-1"></i> Agregar contacto de empresa
              </button>

              <div id="formNuevoContacto" class="card card-body mt-2 border-info" style="display:none;">
                <p class="small text-muted mb-2">El contacto quedará guardado en la empresa.</p>
                <div class="row g-2">
                  <div class="col-md-5">
                    <input type="text" id="nc_nombre" class="form-control form-control-sm"
                           placeholder="Nombre completo *">
                  </div>
                  <div class="col-md-5">
                    <input type="email" id="nc_email" class="form-control form-control-sm"
                           placeholder="correo@empresa.com *">
                  </div>
                  <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-info text-white w-100"
                            onclick="guardarNuevoContacto()">
                      <i class="fa-solid fa-check"></i>
                    </button>
                  </div>
                  <div class="col-md-5">
                    <input type="text" id="nc_cargo" class="form-control form-control-sm"
                           placeholder="Cargo (opcional)">
                  </div>
                  <div class="col-md-5">
                    <input type="text" id="nc_telefono" class="form-control form-control-sm"
                           placeholder="Teléfono (opcional)">
                  </div>
                  <div class="col-12">
                    <div id="nc_error" class="text-danger small" style="display:none;"></div>
                  </div>
                </div>
              </div>
            </div>

          </div><!-- /destinatarios -->

          <!-- ASUNTO -->
          <div class="mb-3">
            <label class="form-label fw-bold" for="envioAsunto">Asunto</label>
            <input type="text" name="subject" id="envioAsunto" class="form-control"
                   value="Propuesta Comercial — <?= htmlspecialchars($quotation['business_name']) ?> — <?= htmlspecialchars($quotation['code']) ?>">
          </div>

          <!-- MENSAJE -->
          <div class="mb-3">
            <label class="form-label fw-bold" for="envioMensaje">Mensaje</label>
            <textarea name="message" id="envioMensaje" class="form-control" rows="6"><?php
$contactoNombre = $quotation['contact_name'] ?? 'equipo';
$vendedor       = $_SESSION['user_name']    ?? 'el equipo de Atankalama';
echo htmlspecialchars(
    "Estimado/a {$contactoNombre},\n\n" .
    "Adjuntamos la propuesta comercial correspondiente a {$quotation['business_name']}.\n\n" .
    "Quedo a disposición para cualquier consulta o aclaración.\n\n" .
    "Saludos cordiales,\n{$vendedor}"
);
            ?></textarea>
          </div>

          <!-- PDF adjunto -->
          <div class="alert alert-light border d-flex align-items-center gap-2 py-2 mb-0">
            <i class="fa-solid fa-paperclip text-danger"></i>
            <span class="small">
              PDF adjunto automáticamente:
              <strong>Cotizacion_<?= htmlspecialchars($quotation['code']) ?>.pdf</strong>
              <?php if (empty($quotation['generated_pdf_path'])): ?>
                <span class="badge bg-warning text-dark ms-1">se generará al enviar</span>
              <?php endif; ?>
            </span>
          </div>

        </div><!-- /modal-body -->

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-info text-white px-4" id="btnEnviarCorreo">
            <i class="fa-solid fa-paper-plane me-1"></i> Enviar correo
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<script>
const COMPANY_ID = <?= (int)$quotation['company_id'] ?>;
const CSRF_TOKEN = '<?= csrf_token() ?>';

function toggleFormContacto() {
  const f = document.getElementById('formNuevoContacto');
  f.style.display = f.style.display === 'none' ? '' : 'none';
}

function guardarNuevoContacto() {
  const nombre   = document.getElementById('nc_nombre').value.trim();
  const email    = document.getElementById('nc_email').value.trim();
  const cargo    = document.getElementById('nc_cargo').value.trim();
  const telefono = document.getElementById('nc_telefono').value.trim();
  const errDiv   = document.getElementById('nc_error');
  errDiv.style.display = 'none';

  if (!nombre || !email) {
    errDiv.textContent = 'Nombre y correo son obligatorios.';
    errDiv.style.display = '';
    return;
  }

  const btn = event.currentTarget;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

  const fd = new FormData();
  fd.append('company_id', COMPANY_ID);
  fd.append('name',  nombre);
  fd.append('email', email);
  fd.append('phone', telefono);
  fd.append('role',  cargo);
  fd.append('csrf', CSRF_TOKEN);

  fetch('<?= BASE_URL ?>/?url=contacts/store', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
      if (!res.success) {
        errDiv.textContent = res.message || 'Error al guardar.';
        errDiv.style.display = '';
        return;
      }
      // Agregar checkbox a la lista y marcarlo
      const ct = res.contact;
      const wrap = document.createElement('div');
      wrap.className = 'form-check destinatario-item';
      wrap.dataset.id = ct.id;
      wrap.innerHTML = `
        <input class="form-check-input" type="checkbox" name="recipients[]"
               id="dest_new_${ct.id}" value="${ct.email}" checked>
        <label class="form-check-label" for="dest_new_${ct.id}">
          <strong>${ct.name}</strong>
          <span class="text-muted ms-1">${ct.email}</span>
          ${ct.role ? `<span class="badge bg-light text-dark border ms-1" style="font-size:.65rem;">${ct.role}</span>` : ''}
          <span class="badge bg-success ms-1" style="font-size:.65rem;">nuevo</span>
        </label>`;

      // Insertar antes de "Mi correo"
      const yo = document.getElementById('dest_yo').closest('.form-check');
      document.getElementById('listaDestinatarios').insertBefore(wrap, yo);

      // Limpiar y ocultar formulario
      ['nc_nombre','nc_email','nc_cargo','nc_telefono'].forEach(id => {
        document.getElementById(id).value = '';
      });
      document.getElementById('formNuevoContacto').style.display = 'none';
    })
    .catch(() => {
      errDiv.textContent = 'Error de conexión.';
      errDiv.style.display = '';
    })
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-check"></i>';
    });
}

// Deshabilitar botón al enviar para evitar doble envío
document.getElementById('btnEnviarCorreo')?.closest('form').addEventListener('submit', function() {
  const btn = document.getElementById('btnEnviarCorreo');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Enviando...';
});
</script>

<div class="row g-4">
  <!-- Columna Izquierda: Datos Generales -->
  <div class="col-lg-8">
    <div class="card mb-4 shadow-sm border-0 fade-in-up delay-1">
      <div class="card-header bg-atk text-white">
        <h6 class="mb-0"><i class="fa-solid fa-info-circle"></i> Información de la Propuesta</h6>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="text-muted small fw-bold text-uppercase">Empresa Cliente</label>
            <div class="fs-5 fw-bold text-atk"><?= htmlspecialchars($quotation['business_name']) ?></div>
            <div class="text-muted"><?= htmlspecialchars($quotation['company_rut'] ?? '') ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small fw-bold text-uppercase">Estado de la Cotización</label>
            <div>
              <?php
                $badges = ['quotation_draft' => 'bg-secondary', 'quotation_sent' => 'bg-info', 'quotation_approved' => 'bg-success'];
                $labels = ['quotation_draft' => 'Borrador', 'quotation_sent' => 'Enviada al Cliente', 'quotation_approved' => 'Aprobada'];
              ?>
              <span class="badge <?= $badges[$quotation['status']] ?? 'bg-light text-dark' ?> fs-6">
                <?= $labels[$quotation['status']] ?? $quotation['status'] ?>
              </span>
            </div>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-4">
            <label class="text-muted small fw-bold text-uppercase">Tipo</label>
            <div><?= ucfirst($quotation['contract_type']) ?></div>
          </div>
          <div class="col-md-4">
            <label class="text-muted small fw-bold text-uppercase">Fecha Estimada Inicio</label>
            <div><?= !empty($quotation['start_date']) ? date('d/m/Y', strtotime($quotation['start_date'])) : '-' ?></div>
          </div>
          <div class="col-md-4">
            <label class="text-muted small fw-bold text-uppercase">Fecha Término</label>
            <div><?= !empty($quotation['end_date']) ? date('d/m/Y', strtotime($quotation['end_date'])) : 'Indefinido' ?></div>
          </div>
        </div>

        <?php if(!empty($quotation['notes'])): ?>
          <hr>
          <label class="text-muted small fw-bold text-uppercase">Notas Comerciales</label>
          <p class="mb-0"><?= nl2br(htmlspecialchars($quotation['notes'])) ?></p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Archivos Adjuntos -->
    <?php if(!empty($attachments)): ?>
    <div class="card mb-4 shadow-sm border-0 fade-in-up delay-2">
      <div class="card-header bg-light">
        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-paperclip"></i> Archivos y Diseños Adjuntos</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <?php foreach($attachments as $att): 
            $isImg = strpos($att['mime_type'], 'image') !== false;
          ?>
          <div class="col-md-4 col-lg-3">
            <div class="card h-100 border shadow-sm">
              <?php if($isImg): ?>
                <a href="<?= BASE_URL . $att['file_path'] ?>" target="_blank">
                  <img src="<?= BASE_URL . $att['file_path'] ?>" class="card-img-top" style="height: 120px; object-fit: cover;">
                </a>
              <?php else: ?>
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 120px;">
                  <i class="fa-solid fa-file-pdf fa-3x text-danger"></i>
                </div>
              <?php endif; ?>
              <div class="card-body p-2">
                <small class="text-truncate d-block fw-bold" title="<?= htmlspecialchars($att['original_name']) ?>">
                  <?= htmlspecialchars($att['original_name']) ?>
                </small>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <span class="badge bg-light text-dark border small"><?= strtoupper(explode('/', $att['mime_type'])[1]) ?></span>
                  <a href="<?= BASE_URL . $att['file_path'] ?>" target="_blank" class="btn btn-sm btn-atk py-0 px-2">
                    Ver
                  </a>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Historial de Acciones -->
    <div class="card mb-4 shadow-sm border-0 fade-in-up delay-3">
      <div class="card-header bg-light">
        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-history"></i> Historial de Acciones</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead class="bg-light small">
              <tr>
                <th class="ps-3">Fecha</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Descripción</th>
              </tr>
            </thead>
            <tbody class="small">
              <?php if(empty($history)): ?>
                <tr><td colspan="4" class="text-center py-3 text-muted">No hay acciones registradas.</td></tr>
              <?php else: ?>
                <?php foreach($history as $h): ?>
                <tr>
                  <td class="ps-3"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></td>
                  <td><strong><?= htmlspecialchars($h['user_name']) ?></strong></td>
                  <td>
                    <span class="badge bg-light text-dark border"><?= ucfirst($h['action']) ?></span>
                  </td>
                  <td><?= htmlspecialchars($h['description'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Tabla de Servicios -->
    <div class="card shadow-sm border-0 mb-4 fade-in-up delay-4">
      <div class="card-header bg-light">
        <h6 class="mb-0 text-atk fw-bold"><i class="fa-solid fa-concierge-bell"></i> Detalle de Servicios Propuestos</h6>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light small">
            <tr>
              <th>Servicio</th>
              <th>Valor Unitario</th>
              <th>Moneda</th>
              <th>Tipo Cobro</th>
              <th>Notas Específicas</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($services as $s): ?>
            <tr>
              <td><span class="fw-bold"><?= htmlspecialchars($s['name']) ?></span></td>
              <td><?= number_format($s['unit_price'], 2, ',', '.') ?></td>
              <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($s['currency']) ?></span></td>
              <td>
                <?php
                  $billings = ['per_person' => 'Por persona', 'per_day' => 'Por día', 'per_event' => 'Por evento'];
                  echo $billings[$s['billing_type']] ?? $s['billing_type'];
                ?>
              </td>
              <td><small class="text-muted"><?= htmlspecialchars($s['contract_notes'] ?? '-') ?></small></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Columna Derecha: Hoteles y Resumen -->
  <div class="col-lg-4">
    <div class="card mb-4 shadow-sm border-0 fade-in-up delay-1">
      <div class="card-header bg-light">
        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-hotel"></i> Hoteles Incluidos</h6>
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach($hotels as $h): ?>
        <li class="list-group-item">
          <div class="fw-bold"><?= htmlspecialchars($h['name']) ?></div>
          <small class="text-muted"><?= htmlspecialchars($h['address']) ?></small>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="card shadow-sm border-0 bg-light fade-in-up delay-2">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Resumen Comercial</h6>
        <div class="d-flex justify-content-between mb-2">
          <span>Modo de Precio:</span>
          <span class="fw-bold"><?= $quotation['pricing_mode'] === 'por_persona' ? 'Por Persona' : 'Fijo por Grupo' ?></span>
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span>Monto Base:</span>
          <span class="fw-bold">$ <?= number_format($quotation['base_amount'], 0, ',', '.') ?></span>
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span>Frecuencia:</span>
          <span class="fw-bold"><?= ucfirst($quotation['payment_frequency']) ?></span>
        </div>
        <hr>
        <div class="text-center">
          <small class="text-muted d-block mb-2">Creada por: <?= htmlspecialchars($quotation['created_by_name'] ?? 'N/A') ?></small>
          <small class="text-muted">Fecha creación: <?= date('d/m/Y H:i', strtotime($quotation['created_at'])) ?></small>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>