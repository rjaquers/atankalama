<?php include 'layout.php'; ?>

<div class="container mt-4" style="max-width:640px">

    <?php if (!empty($_SESSION['flash_ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['flash_ok'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_ok']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="index.php?route=emp/usuarios/list&company=<?= $empresa['id'] ?>"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Editar usuario</h4>
            <small class="text-muted"><?= htmlspecialchars($empresa['business_name']) ?></small>
        </div>
    </div>

    <!-- ── Datos del usuario ─────────────────────────────────── -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold"><i class="bi bi-person me-1"></i> Datos</div>
        <div class="card-body">
            <form method="POST" action="index.php?route=emp/usuarios/update">
                <input type="hidden" name="id"         value="<?= $usuario['id'] ?>">
                <input type="hidden" name="company_id" value="<?= $empresa['id'] ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($usuario['name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Correo electrónico <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($usuario['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Rol</label>
                    <select name="role" class="form-select">
                        <option value="visor" <?= $usuario['role'] === 'visor' ? 'selected' : '' ?>>
                            Visor — solo consultar información
                        </option>
                        <option value="admin" <?= $usuario['role'] === 'admin' ? 'selected' : '' ?>>
                            Admin — puede gestionar usuarios de la empresa
                        </option>
                    </select>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" name="status" id="chkStatus"
                           <?= $usuario['status'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="chkStatus">
                        Cuenta activa (si se desactiva, el usuario no podrá iniciar sesión)
                    </label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="index.php?route=emp/usuarios/list&company=<?= $empresa['id'] ?>"
                       class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-floppy"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Restablecer contraseña ────────────────────────────── -->
    <div class="card shadow-sm mb-4 border-warning">
        <div class="card-header fw-semibold text-warning-emphasis bg-warning-subtle">
            <i class="bi bi-key-fill me-1"></i> Restablecer contraseña
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Asigna una contraseña nueva directamente. El usuario deberá usar esta
                para ingresar al portal de la empresa.
            </p>
            <form method="POST" action="index.php?route=emp/usuarios/reset-password">
                <input type="hidden" name="id"         value="<?= $usuario['id'] ?>">
                <input type="hidden" name="company_id" value="<?= $empresa['id'] ?>">

                <div class="input-group">
                    <input type="text" name="new_password" class="form-control"
                           placeholder="Nueva contraseña (mínimo 6 caracteres)"
                           minlength="6" required>
                    <button type="submit" class="btn btn-warning"
                            onclick="return confirm('¿Confirmas el restablecimiento de contraseña?')">
                        <i class="bi bi-arrow-repeat"></i> Restablecer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Información de auditoría ──────────────────────────── -->
    <div class="card shadow-sm bg-light border-0 text-muted small">
        <div class="card-body py-2 px-3">
            <div class="row">
                <div class="col-6">
                    <i class="bi bi-calendar3 me-1"></i>
                    Creado: <?= $usuario['created_at'] ? date('d/m/Y H:i', strtotime($usuario['created_at'])) : '—' ?>
                </div>
                <div class="col-6">
                    <i class="bi bi-box-arrow-in-right me-1"></i>
                    Último acceso: <?= $usuario['last_login'] ? date('d/m/Y H:i', strtotime($usuario['last_login'])) : 'Nunca' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../helpers/cierre.php'; ?>
