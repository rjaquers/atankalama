<?php include 'layout.php'; ?>

<div class="container mt-4" style="max-width:560px;">
    <h4 class="mb-4"><i class="bi bi-person-plus-fill"></i> Nuevo Usuario</h4>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form method="POST" action="index.php?route=acceso/usuarios/store">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required maxlength="100"
                   value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Apellido</label>
            <input type="text" name="apellido" class="form-control" required maxlength="100"
                   value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Correo electrónico</label>
            <input type="email" name="email" class="form-control" required maxlength="255"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <div class="form-text">Este correo recibirá los códigos OTP para ingresar.</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Teléfono <span class="text-muted">(opcional)</span></label>
            <input type="tel" name="telefono" class="form-control" maxlength="30"
                   value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">RUT <span class="text-muted">(opcional)</span></label>
            <input type="text" name="rut" class="form-control" maxlength="12"
                   placeholder="12345678-9"
                   value="<?= htmlspecialchars($_POST['rut'] ?? '') ?>">
        </div>
        <div class="mb-4">
            <label class="form-label">Perfil</label>
            <select name="perfil" class="form-select">
                <?php foreach ($perfiles as $p): ?>
                    <option value="<?= htmlspecialchars($p) ?>" 
                        <?= (($_POST['perfil'] ?? 'Operador') === $p) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Guardar
            </button>
            <a href="index.php?route=acceso/usuarios/list" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include '../helpers/cierre.php'; ?>
