<?php include 'layout.php'; ?>

<div class="container mt-4" style="max-width:560px;">
    <h4 class="mb-1"><i class="bi bi-pencil-square"></i> Editar Usuario</h4>
    <p class="text-muted small mb-4"><?= htmlspecialchars($usuario['email']) ?></p>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form method="POST" action="index.php?route=acceso/usuarios/update">
        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">

        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required maxlength="100"
                   value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Apellido</label>
            <input type="text" name="apellido" class="form-control" required maxlength="100"
                   value="<?= htmlspecialchars($usuario['apellido'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Correo electrónico</label>
            <input type="email" name="email" class="form-control" required maxlength="255"
                   value="<?= htmlspecialchars($usuario['email']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Teléfono <span class="text-muted">(opcional)</span></label>
            <input type="tel" name="telefono" class="form-control" maxlength="30"
                   value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">RUT <span class="text-muted">(opcional)</span></label>
            <input type="text" name="rut" class="form-control" maxlength="12"
                   placeholder="12345678-9"
                   value="<?= htmlspecialchars($usuario['rut'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Perfil</label>
            <select name="perfil" class="form-select">
                <?php foreach ($perfiles as $p): ?>
                    <option value="<?= htmlspecialchars($p) ?>"
                        <?= ($usuario['perfil'] ?? '') === $p ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <option value="activo"   <?= $usuario['estado'] === 'activo'   ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= $usuario['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Actualizar
            </button>
            <a href="index.php?route=acceso/usuarios/permisos&id=<?= $usuario['id'] ?>"
               class="btn btn-outline-warning">
                <i class="bi bi-key-fill"></i> Gestionar accesos
            </a>
            <a href="index.php?route=acceso/usuarios/list" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>

    <!-- ── Estado de verificación de correo ───────────────────────────────── -->
    <div class="card mt-4 border-0 shadow-sm">
        <div class="card-body">
            <h6 class="card-title mb-3">
                <i class="bi bi-envelope-check me-1"></i> Verificación de correo
            </h6>
            <?php if (!empty($usuario['validado'])): ?>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-success fs-6 px-3 py-2">
                        <i class="bi bi-patch-check-fill me-1"></i> Correo verificado
                    </span>
                    <div class="text-muted small">
                        El usuario recibió y confirmó su código en
                        <strong><?= htmlspecialchars($usuario['email']) ?></strong>.
                        Su correo de reenvío funciona correctamente.
                    </div>
                </div>
                <form method="POST" action="index.php?route=acceso/usuarios/reset-validado"
                      class="mt-3"
                      onsubmit="return confirm('¿Resetear la verificación de <?= htmlspecialchars(addslashes(trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')) ?: $usuario['email'])) ?>?\nDeberá volver a verificar su correo.')">
                    <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Resetear verificación
                    </button>
                </form>
            <?php else: ?>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                        <i class="bi bi-patch-exclamation-fill me-1"></i> Sin verificar
                    </span>
                    <div class="text-muted small">
                        Este usuario aún no ha completado la verificación.<br>
                        Puede hacerlo en:
                        <a href="https://www.atankalama.com/validacion/" target="_blank">
                            atankalama.com/validacion/
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../helpers/cierre.php'; ?>
