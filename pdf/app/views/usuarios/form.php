<?php
  $esEditar = isset($usuario);
  $formAction = $esEditar
    ? BASE_URL . '/usuarios/editar/' . (int)$usuario['id']
    : BASE_URL . '/usuarios/crear';
?>
<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="mb-4">
  <a href="<?= BASE_URL ?>/usuarios" class="text-decoration-none text-muted">
    <i class="bi bi-arrow-left me-1"></i> Volver a Usuarios
  </a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="stat-card">

      <div class="d-flex align-items-center gap-2 mb-4">
        <div class="stat-icon bg-primary-subtle text-primary">
          <i class="bi bi-<?= $esEditar ? 'pencil-fill' : 'person-plus-fill' ?>"></i>
        </div>
        <div>
          <h5 class="fw-bold mb-0"><?= $esEditar ? 'Editar Usuario' : 'Nuevo Usuario' ?></h5>
          <small class="text-muted"><?= $esEditar ? 'Modifica los datos del usuario' : 'Completa los datos para crear el usuario' ?></small>
        </div>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-1"></i>
          <?= htmlspecialchars($error) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= $formAction ?>">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <!-- Nombre -->
        <div class="mb-3">
          <label for="nombre" class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
          <input type="text" id="nombre" name="nombre" class="form-control"
                 value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>"
                 required placeholder="Ej: María González">
        </div>

        <!-- Email -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Correo institucional <span class="text-danger"><?= $esEditar ? '' : '*' ?></span></label>
          <?php if ($esEditar): ?>
            <p class="form-control-plaintext border rounded px-3 py-2 bg-light text-muted mb-0">
              <?= htmlspecialchars($usuario['email']) ?>
            </p>
            <input type="hidden" name="email" value="<?= htmlspecialchars($usuario['email']) ?>">
            <small class="text-muted"><i class="bi bi-lock me-1"></i>El correo institucional no puede modificarse.</small>
          <?php else: ?>
            <input type="email" id="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   required placeholder="usuario@hotel.cl">
          <?php endif; ?>
        </div>

        <!-- Rol -->
        <div class="mb-3">
          <label for="rol_id" class="form-label fw-semibold">Rol</label>
          <select id="rol_id" name="rol_id" class="form-select">
            <?php foreach ($roles as $rol): ?>
              <option value="<?= (int)$rol['id'] ?>"
                <?= (isset($usuario) && (int)$usuario['rol_id'] === (int)$rol['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($rol['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Área -->
        <div class="mb-3">
          <label for="area_id" class="form-label fw-semibold">Área</label>
          <select id="area_id" name="area_id" class="form-select">
            <option value="">Sin área</option>
            <?php foreach ($areas as $area): ?>
              <option value="<?= (int)$area['id'] ?>"
                <?= (isset($usuario) && (int)$usuario['area_id'] === (int)$area['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($area['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Es Jefe de Área -->
        <div class="mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="es_jefe" name="es_jefe" value="1"
                   <?= (isset($usuario) && (int)$usuario['es_jefe'] === 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="es_jefe">
              <i class="bi bi-star-fill text-warning me-1"></i> Es Jefe de Área
            </label>
          </div>
        </div>

        <!-- Estado (solo edición) -->
        <?php if ($esEditar): ?>
          <div class="mb-3">
            <label for="estado" class="form-label fw-semibold">Estado</label>
            <select id="estado" name="estado" class="form-select">
              <option value="1" <?= (int)$usuario['estado'] === 1 ? 'selected' : '' ?>>Activo</option>
              <option value="0" <?= (int)$usuario['estado'] === 0 ? 'selected' : '' ?>>Inactivo</option>
            </select>
          </div>
        <?php endif; ?>

        <!-- Nota OTP -->
        <div class="alert alert-info py-2 px-3 mb-4" style="font-size:13px">
          <i class="bi bi-shield-lock me-1"></i>
          El usuario accede al sistema mediante código OTP enviado a su correo. No se usa contraseña.
        </div>

        <!-- Acceso a otras apps del hotel (solo en edición) -->
        <?php if ($esEditar && !empty($appsHotel)): ?>
        <div class="mb-4">
          <label class="form-label fw-semibold">
            <i class="bi bi-grid-fill text-primary me-1"></i> Acceso a apps del hotel
          </label>
          <div class="border rounded p-3" style="background:#f8fafc">
            <div class="row g-2">
              <?php foreach ($appsHotel as $app): ?>
              <div class="col-sm-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox"
                         id="app_<?= (int)$app['id'] ?>"
                         name="hotel_apps[]"
                         value="<?= (int)$app['id'] ?>"
                         <?= (int)$app['tiene_acceso'] ? 'checked' : '' ?>>
                  <label class="form-check-label" for="app_<?= (int)$app['id'] ?>">
                    <?= htmlspecialchars($app['nombre']) ?>
                    <small class="text-muted ms-1">(<?= htmlspecialchars($app['slug']) ?>)</small>
                  </label>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <small class="text-muted mt-2 d-block">
              <i class="bi bi-info-circle me-1"></i>
              Define a qué sistemas del hotel puede acceder este usuario con OTP.
            </small>
          </div>
        </div>
        <?php endif; ?>

        <!-- Botones -->
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Guardar
          </button>
          <a href="<?= BASE_URL ?>/usuarios" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg me-1"></i> Cancelar
          </a>
        </div>

      </form>
    </div>
  </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
