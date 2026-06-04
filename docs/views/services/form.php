<?php require VIEW_PATH . "/layouts/header.php"; ?>

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
    <i class="fa-solid fa-box-open"></i>
    <?= $isEdit ? 'Editar Servicio' : 'Nuevo Servicio' ?>
  </h3>
  <div>
    <a href="<?= BASE_URL ?>/services" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card fade-in">
      <div class="card-body">
        <form method="post"
              action="<?= BASE_URL ?>/services/<?= $isEdit ? 'update/' . $service['id'] : 'store' ?>">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

          <div class="mb-3">
            <label class="form-label">Nombre del Servicio <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required
                   placeholder="Ej: Alojamiento, Desayuno, Lavandería..."
                   value="<?= htmlspecialchars($service['name'] ?? '') ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Precio Base (Referencial) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">$</span>
              <input type="number" name="base_price" class="form-control" step="1" min="0" required
                     placeholder="0"
                     value="<?= (int)($service['base_price'] ?? 0) ?>">
            </div>
            <small class="text-muted">Este valor se usará por defecto al crear nuevas cotizaciones.</small>
          </div>

          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-control" rows="3"
                      placeholder="Detalle del servicio..."><?= htmlspecialchars($service['description'] ?? '') ?></textarea>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-atk">
              <i class="fa-solid fa-save"></i>
              <?= $isEdit ? 'Guardar Cambios' : 'Crear Servicio' ?>
            </button>
            <a href="<?= BASE_URL ?>/services" class="btn btn-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
