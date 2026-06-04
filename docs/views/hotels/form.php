<?php require VIEW_PATH . "/layouts/header.php"; ?>

<!-- Mensajes flash -->
<?php if(!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show shadow-sm">
    <i class="fa-solid fa-exclamation-circle text-danger me-2"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
  <h3 class="mb-0">
    <i class="fa-solid fa-hotel text-primary me-2"></i>
    <?= $isEdit ? 'Editar Hotel — ' . htmlspecialchars($hotel['name'] ?? '') : 'Nuevo Hotel' ?>
  </h3>
  <div>
    <a href="<?= BASE_URL ?>/hotels" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm">
      <i class="fa-solid fa-arrow-left me-1"></i> Volver
    </a>
  </div>
</div>

<form method="post"
      action="<?= BASE_URL ?>/hotels/<?= $isEdit ? 'update/' . $hotel['id'] : 'store' ?>"
      id="hotelForm"
      class="pb-5">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

  <div class="row g-4 fade-in">
    <!-- Panel Izquierdo: Datos Básicos -->
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
          <h6 class="fw-bold mb-0 text-muted small text-uppercase" style="letter-spacing: 0.1em;">
            <i class="fa-solid fa-info-circle me-1"></i> Información General
          </h6>
        </div>
        <div class="card-body p-4">
          <div class="row g-3">
            <div class="col-md-9">
              <label class="form-label fw-semibold">Nombre del Hotel <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control form-control-lg border-2 shadow-none"
                     required placeholder="Ej: Atankalama Inn"
                     value="<?= htmlspecialchars($hotel['name'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Código <span class="text-danger">*</span></label>
              <input type="text" name="code" class="form-control form-control-lg border-2 shadow-none"
                     required placeholder="Ej: INN"
                     value="<?= htmlspecialchars($hotel['code'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">RUT Empresa</label>
              <input type="text" name="rut" class="form-control border-2 shadow-none"
                     placeholder="Ej: 77.234.XXX-Y"
                     value="<?= htmlspecialchars($hotel['rut'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Ciudad</label>
              <input type="text" name="city" class="form-control border-2 shadow-none"
                     placeholder="Ej: Copiapó"
                     value="<?= htmlspecialchars($hotel['city'] ?? '') ?>">
            </div>
            <div class="col-md-12">
              <label class="form-label fw-semibold">Dirección</label>
              <input type="text" name="address" class="form-control border-2 shadow-none"
                     placeholder="Ej: Calle Principal 123"
                     value="<?= htmlspecialchars($hotel['address'] ?? '') ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Legal Info -->
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
          <h6 class="fw-bold mb-0 text-muted small text-uppercase" style="letter-spacing: 0.1em;">
            <i class="fa-solid fa-scale-balanced me-1"></i> Información Legal y Contacto
          </h6>
        </div>
        <div class="card-body p-4">
          <div class="row g-3">
            <div class="col-md-7">
              <label class="form-label fw-semibold">Representante Legal</label>
              <input type="text" name="legal_representative" class="form-control border-2 shadow-none"
                     placeholder="Ej: Juan Pérez"
                     value="<?= htmlspecialchars($hotel['legal_representative'] ?? '') ?>">
            </div>
            <div class="col-md-5">
              <label class="form-label fw-semibold">RUT Representante</label>
              <input type="text" name="representative_rut" class="form-control border-2 shadow-none"
                     placeholder="Ej: 12.345.678-9"
                     value="<?= htmlspecialchars($hotel['representative_rut'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Teléfono de Contacto</label>
              <input type="text" name="phone" class="form-control border-2 shadow-none"
                     placeholder="Ej: +56 9 1234 5678"
                     value="<?= htmlspecialchars($hotel['phone'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email del Hotel</label>
              <input type="email" name="email" class="form-control border-2 shadow-none"
                     placeholder="Ej: admin@atankalama.com"
                     value="<?= htmlspecialchars($hotel['email'] ?? '') ?>">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel Derecho: Acciones -->
    <div class="col-lg-4">
      <div class="sticky-top" style="top: 1rem;">
        <div class="card border-0 shadow-sm rounded-4 bg-light">
          <div class="card-body p-4">
            <h6 class="fw-bold mb-3">Guardar Cambios</h6>
            <p class="text-muted small">Asegúrese de que el código sea único. Este se usará en reportes y como identificador del hotel.</p>
            <hr>
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-atk btn-lg rounded-pill shadow-sm">
                <i class="fa-solid fa-save me-1"></i> <?= $isEdit ? 'Actualizar Hotel' : 'Crear Hotel' ?>
              </button>
              <a href="<?= BASE_URL ?>/hotels" class="btn btn-white btn-lg rounded-pill border shadow-sm">
                Cancelar
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
