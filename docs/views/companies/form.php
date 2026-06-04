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
<div class="page-header">
  <h3>
    <i class="fa-solid fa-building"></i>
    <?= $isEdit ? 'Editar Empresa' : 'Nueva Empresa' ?>
  </h3>
  <div>
    <a href="<?= BASE_URL ?>/companies" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
  </div>
</div>

<div class="card fade-in">
  <div class="card-body">
    <form method="post"
          action="<?= BASE_URL ?>/companies/<?= $isEdit ? 'update/' . $company['id'] : 'store' ?>">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

      <!-- Tipo de empresa -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label">Tipo de Empresa <span class="text-danger">*</span></label>
          <select name="type" class="form-select" required id="companyType">
            <option value="cliente" <?= ($company['type'] ?? 'cliente') === 'cliente' ? 'selected' : '' ?>>
              🤝 Cliente (Arriendo / Hospedaje)
            </option>
            <option value="proveedor" <?= ($company['type'] ?? '') === 'proveedor' ? 'selected' : '' ?>>
              🚚 Proveedor
            </option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">RUT</label>
          <input type="text" name="rut" class="form-control"
                 placeholder="12.345.678-9"
                 value="<?= htmlspecialchars($company['rut'] ?? '') ?>">
        </div>
      </div>

      <hr class="my-4">

      <!-- Datos de la empresa -->
      <h6 class="text-muted mb-3"><i class="fa-solid fa-building"></i> Datos de la Empresa</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label">Razón Social <span class="text-danger">*</span></label>
          <input type="text" name="business_name" class="form-control" required
                 placeholder="Empresa S.A."
                 value="<?= htmlspecialchars($company['business_name'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Nombre de Fantasía</label>
          <input type="text" name="trade_name" class="form-control"
                 placeholder="Nombre comercial"
                 value="<?= htmlspecialchars($company['trade_name'] ?? '') ?>">
        </div>
        <div class="col-md-8">
          <label class="form-label">Dirección</label>
          <input type="text" name="address" class="form-control"
                 placeholder="Av. Principal 123"
                 value="<?= htmlspecialchars($company['address'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Ciudad</label>
          <input type="text" name="city" class="form-control"
                 placeholder="Copiapó"
                 value="<?= htmlspecialchars($company['city'] ?? '') ?>">
        </div>
      </div>

      <hr class="my-4">

      <!-- Contacto -->
      <h6 class="text-muted mb-3"><i class="fa-solid fa-user"></i> Contacto Principal</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label">Nombre</label>
          <input type="text" name="contact_name" class="form-control"
                 placeholder="Juan Pérez"
                 value="<?= htmlspecialchars($company['contact_name'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Email</label>
          <input type="email" name="contact_email" class="form-control"
                 placeholder="contacto@empresa.com"
                 value="<?= htmlspecialchars($company['contact_email'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Teléfono</label>
          <input type="text" name="contact_phone" class="form-control"
                 placeholder="+56 9 1234 5678"
                 value="<?= htmlspecialchars($company['contact_phone'] ?? '') ?>">
        </div>
      </div>

      <hr class="my-4">

      <!-- Notas -->
      <div class="row g-3 mb-4">
        <div class="col-12">
          <label class="form-label">Notas adicionales</label>
          <textarea name="notes" class="form-control" rows="3"
                    placeholder="Observaciones, condiciones especiales..."><?= htmlspecialchars($company['notes'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- Botones -->
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-atk">
          <i class="fa-solid fa-save"></i>
          <?= $isEdit ? 'Guardar Cambios' : 'Crear Empresa' ?>
        </button>
        <a href="<?= BASE_URL ?>/companies" class="btn btn-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
