<?php
  $esEditar   = isset($area);
  $formAction = $esEditar
    ? BASE_URL . '/areas/editar/' . (int)$area['id']
    : BASE_URL . '/areas/crear';
?>
<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="mb-4">
  <a href="<?= BASE_URL ?>/areas" class="text-decoration-none text-muted">
    <i class="bi bi-arrow-left me-1"></i> Volver a Áreas
  </a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="stat-card">

      <div class="d-flex align-items-center gap-2 mb-4">
        <div class="stat-icon bg-primary-subtle text-primary">
          <i class="bi bi-<?= $esEditar ? 'pencil-fill' : 'plus-circle-fill' ?>"></i>
        </div>
        <div>
          <h5 class="fw-bold mb-0"><?= $esEditar ? 'Editar Área' : 'Nueva Área' ?></h5>
          <small class="text-muted"><?= $esEditar ? 'Modifica los datos del área' : 'Completa los datos del área' ?></small>
        </div>
      </div>

      <form method="POST" action="<?= $formAction ?>">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <!-- Nombre -->
        <div class="mb-3">
          <label for="nombre" class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
          <input type="text" id="nombre" name="nombre" class="form-control" required
                 value="<?= htmlspecialchars($area['nombre'] ?? '') ?>"
                 placeholder="Ej: Housekeeping">
        </div>

        <!-- Descripción -->
        <div class="mb-3">
          <label for="descripcion" class="form-label fw-semibold">Descripción</label>
          <textarea id="descripcion" name="descripcion" class="form-control" rows="2"
                    placeholder="Descripción breve del área..."><?= htmlspecialchars($area['descripcion'] ?? '') ?></textarea>
        </div>

        <!-- Color -->
        <div class="mb-3">
          <label for="color" class="form-label fw-semibold">Color</label>
          <div class="d-flex align-items-center gap-3">
            <input type="color" id="color" name="color" class="form-control form-control-color"
                   value="<?= htmlspecialchars($area['color'] ?? '#3B82F6') ?>"
                   title="Selecciona el color del área">
            <div id="color-preview"
                 style="width:40px;height:40px;border-radius:8px;background:<?= htmlspecialchars($area['color'] ?? '#3B82F6') ?>;border:1px solid rgba(0,0,0,.1);transition:background .2s">
            </div>
            <span class="text-muted" style="font-size:13px">Vista previa</span>
          </div>
        </div>

        <!-- Icono Bootstrap Icons -->
        <div class="mb-3">
          <label for="icono" class="form-label fw-semibold">Icono <span class="text-muted fw-normal">(Bootstrap Icons)</span></label>
          <input type="text" id="icono" name="icono" class="form-control"
                 value="<?= htmlspecialchars($area['icono'] ?? '') ?>"
                 placeholder="Ej: wrench, utensils, building, star">
          <div class="mt-2 d-flex align-items-center gap-2">
            <i id="icono-preview"
               class="bi bi-<?= htmlspecialchars($area['icono'] ?? 'building') ?> fs-3"
               style="color:<?= htmlspecialchars($area['color'] ?? '#3B82F6') ?>"></i>
            <small class="text-muted">Vista previa del icono</small>
          </div>
          <small class="text-muted">
            Busca iconos en
            <a href="https://icons.getbootstrap.com/" target="_blank" rel="noopener">icons.getbootstrap.com</a>
          </small>
        </div>

        <!-- Estado (solo edición) -->
        <?php if ($esEditar): ?>
          <div class="mb-3">
            <label for="estado" class="form-label fw-semibold">Estado</label>
            <select id="estado" name="estado" class="form-select">
              <option value="activo"   <?= ($area['estado'] ?? '') === 'activo'   ? 'selected' : '' ?>>Activo</option>
              <option value="inactivo" <?= ($area['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
          </div>
        <?php endif; ?>

        <!-- Botones -->
        <div class="d-flex gap-2 mt-4">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Guardar
          </button>
          <a href="<?= BASE_URL ?>/areas" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg me-1"></i> Cancelar
          </a>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
// Preview color en tiempo real
document.getElementById('color').addEventListener('input', function () {
  document.getElementById('color-preview').style.background = this.value;
  document.getElementById('icono-preview').style.color = this.value;
});

// Preview icono en tiempo real
document.getElementById('icono').addEventListener('input', function () {
  var val = this.value.trim() || 'building';
  document.getElementById('icono-preview').className = 'bi bi-' + val + ' fs-3';
});
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
