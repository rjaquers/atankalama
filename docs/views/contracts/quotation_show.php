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
  </div>
</div>

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