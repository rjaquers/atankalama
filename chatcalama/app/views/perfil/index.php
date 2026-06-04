<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-person-circle text-primary me-2"></i>Mi Perfil</h4>
  <small class="text-muted">Administra tu información personal</small>
</div>

<!-- Mensajes flash -->
<?php if (!empty($msg)): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="row g-4">

  <!-- ==============================
       Columna izquierda — Datos básicos
       ============================== -->
  <div class="col-md-4">
    <div class="stat-card text-center">

      <!-- Foto de perfil -->
      <div class="d-flex justify-content-center mb-3">
        <?php if (!empty($usuario['foto_perfil'])): ?>
          <img src="<?= BASE_URL ?>/<?= htmlspecialchars($usuario['foto_perfil']) ?>"
               alt="foto de perfil"
               style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid #e2e8f0">
        <?php else: ?>
          <div style="width:120px;height:120px;border-radius:50%;background:var(--primary);
                      color:#fff;font-size:48px;font-weight:800;
                      display:flex;align-items:center;justify-content:center;">
            <?= strtoupper(mb_substr($usuario['nombre'] ?? 'U', 0, 1, 'UTF-8')) ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Botón cambiar foto (abre modal) -->
      <button type="button" class="btn btn-sm btn-outline-primary mb-3"
              data-bs-toggle="collapse" data-bs-target="#foto-collapse">
        <i class="bi bi-camera-fill me-1"></i> Cambiar foto
      </button>

      <!-- Nombre -->
      <h5 class="fw-bold mb-1"><?= htmlspecialchars($usuario['nombre'] ?? '') ?></h5>

      <!-- Email -->
      <p class="text-muted mb-3" style="font-size:13px">
        <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($usuario['email'] ?? '') ?>
      </p>

      <!-- Área -->
      <?php if (!empty($usuario['area_nombre'])): ?>
        <div class="mb-2">
          <span class="badge fs-6 px-3 py-2"
                style="background:<?= htmlspecialchars($usuario['area_color'] ?? '#3B82F6') ?>20;
                       color:<?= htmlspecialchars($usuario['area_color'] ?? '#3B82F6') ?>;
                       border:1px solid <?= htmlspecialchars($usuario['area_color'] ?? '#3B82F6') ?>40">
            <?= htmlspecialchars($usuario['area_nombre']) ?>
          </span>
        </div>
      <?php endif; ?>

      <!-- Rol -->
      <?php if (!empty($usuario['rol_nombre'])): ?>
        <div class="mb-3">
          <span class="badge bg-primary-subtle text-primary-emphasis">
            <i class="bi bi-shield-fill me-1"></i><?= htmlspecialchars($usuario['rol_nombre']) ?>
          </span>
        </div>
      <?php endif; ?>

      <!-- Nota OTP -->
      <div class="alert alert-light py-2 px-3 text-start" style="font-size:12px">
        <i class="bi bi-shield-lock text-primary me-1"></i>
        Acceso via OTP — no hay contraseña
      </div>

    </div>
  </div>

  <!-- ==============================
       Columna derecha
       ============================== -->
  <div class="col-md-8">

    <!-- Form editar perfil -->
    <div class="stat-card mb-4">
      <h6 class="fw-bold mb-3"><i class="bi bi-pencil-fill me-1 text-primary"></i>Editar perfil</h6>

      <form method="POST" action="<?= BASE_URL ?>/perfil/actualizar">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <!-- Nombre -->
        <div class="mb-3">
          <label for="nombre" class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
          <input type="text" id="nombre" name="nombre" class="form-control" required
                 value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>">
        </div>

        <!-- Email (no editable) -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Correo institucional</label>
          <p class="form-control-plaintext border rounded px-3 py-2 bg-light text-muted mb-0">
            <?= htmlspecialchars($usuario['email'] ?? '') ?>
          </p>
          <small class="text-muted"><i class="bi bi-lock me-1"></i>El correo no puede modificarse.</small>
        </div>

        <!-- Área -->
        <div class="mb-3">
          <label for="area_id" class="form-label fw-semibold">Área</label>
          <select id="area_id" name="area_id" class="form-select">
            <option value="">Sin área</option>
            <?php foreach ($areas as $a): ?>
              <option value="<?= (int)$a['id'] ?>"
                <?= (int)($usuario['area_id'] ?? 0) === (int)$a['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($a['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i> Guardar cambios
        </button>
      </form>
    </div>

    <!-- Form foto de perfil (colapsable en móvil, siempre visible en desktop) -->
    <div class="stat-card" id="foto-collapse">
      <h6 class="fw-bold mb-3"><i class="bi bi-image-fill me-1 text-primary"></i>Foto de perfil</h6>

      <form method="POST" action="<?= BASE_URL ?>/perfil/subirFoto"
            enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <!-- Preview antes de subir -->
        <div id="foto-preview" class="mb-3 text-center" style="display:none">
          <img id="foto-preview-img" src="" alt="preview"
               style="max-width:160px;max-height:160px;border-radius:12px;object-fit:cover;border:2px solid #e2e8f0">
        </div>

        <div class="mb-3">
          <label for="foto-input" class="form-label fw-semibold">Seleccionar imagen</label>
          <input type="file" id="foto-input" name="foto" class="form-control"
                 accept="image/*">
        </div>

        <div class="d-flex align-items-center gap-3">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-cloud-upload-fill me-1"></i> Subir foto
          </button>
          <small class="text-muted">
            <i class="bi bi-info-circle me-1"></i>Se convierte automáticamente a formato WebP
          </small>
        </div>

      </form>
    </div>

  </div><!-- /col-md-8 -->
</div><!-- /row -->

<script>
// Preview de imagen seleccionada antes de subir
document.getElementById('foto-input').addEventListener('change', function () {
  var file = this.files[0];
  if (!file) return;
  var reader = new FileReader();
  reader.onload = function (e) {
    document.getElementById('foto-preview-img').src = e.target.result;
    document.getElementById('foto-preview').style.display = 'block';
  };
  reader.readAsDataURL(file);
});
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
