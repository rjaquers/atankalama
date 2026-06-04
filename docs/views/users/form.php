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
    <i class="fa-solid fa-user-plus"></i>
    <?= $isEdit ? 'Editar Usuario' : 'Nuevo Usuario' ?>
  </h3>
  <div>
    <a href="<?= BASE_URL ?>/users" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card fade-in">
      <div class="card-body">
        <form method="post"
              action="<?= BASE_URL ?>/users/<?= $isEdit ? 'update/' . $user['id'] : 'store' ?>">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

          <div class="row g-3">
            <!-- Nombre -->
            <div class="col-md-6">
              <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required
                     placeholder="Juan Pérez"
                     value="<?= htmlspecialchars($user['name'] ?? '') ?>">
            </div>

            <!-- Email -->
            <div class="col-md-6">
              <label class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" required
                     placeholder="usuario@atankalama.com"
                     value="<?= htmlspecialchars($user['email'] ?? '') ?>">
            </div>

            <!-- Rol -->
            <div class="col-md-6">
              <label class="form-label">Rol <span class="text-danger">*</span></label>
              <select name="role_id" class="form-select" required>
                <option value="">-- Seleccionar Rol --</option>
                <?php foreach($roles as $r): ?>
                <option value="<?= (int)$r['id'] ?>"
                        <?= (int)($user['role_id'] ?? 0) === (int)$r['id'] ? 'selected' : '' ?>>
                  <?= ucfirst(htmlspecialchars($r['name'])) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Contraseña -->
            <div class="col-md-6">
              <label class="form-label">
                Contraseña
                <?= !$isEdit ? '<span class="text-danger">*</span>' : '' ?>
              </label>
              <input type="password" name="password" class="form-control"
                     placeholder="<?= $isEdit ? 'Dejar vacío para no cambiar' : 'Mínimo 6 caracteres' ?>"
                     minlength="6"
                     <?= !$isEdit ? 'required' : '' ?>>
              <?php if($isEdit): ?>
              <small class="text-muted">Solo completar si desea cambiar la contraseña actual</small>
              <?php endif; ?>
            </div>
          </div>

          <!-- Info de permisos por rol -->
          <div class="alert alert-light border mt-4" id="roleInfo">
            <h6 class="mb-2"><i class="fa-solid fa-info-circle text-primary"></i> Permisos por rol</h6>
            <div class="row small">
              <div class="col-md-3">
                <strong>Admin:</strong><br>
                Acceso total al sistema
              </div>
              <div class="col-md-3">
                <strong>Vendedor:</strong><br>
                Contratos, Empresas, Reportes
              </div>
              <div class="col-md-3">
                <strong>Cobranzas:</strong><br>
                Pagos, Contratos (ver), Reportes
              </div>
              <div class="col-md-3">
                <strong>Recepción:</strong><br>
                Contratos (ver), Subir Archivos
              </div>
            </div>
          </div>

          <!-- Botones -->
          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-atk">
              <i class="fa-solid fa-save"></i>
              <?= $isEdit ? 'Guardar Cambios' : 'Crear Usuario' ?>
            </button>
            <a href="<?= BASE_URL ?>/users" class="btn btn-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
