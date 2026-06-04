<?php include 'layout.php'; ?>

<div class="container mt-4" style="max-width:520px;">
    <h4 class="mb-4"><i class="bi bi-person-badge-fill"></i> Nuevo Rol</h4>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form method="POST" action="index.php?route=acceso/roles/store">
        <div class="mb-3">
            <label class="form-label">Aplicación</label>
            <select name="app_id" class="form-select" required>
                <option value="">— Seleccionar —</option>
                <?php foreach ($apps as $a): ?>
                    <option value="<?= $a['id'] ?>"
                        <?= $appId == $a['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Nombre del rol</label>
            <input type="text" name="nombre" class="form-control" required maxlength="80"
                   placeholder="Ej: Administrador, Recepcionista, Jefe de Área"
                   value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
        </div>
        <div class="mb-4">
            <label class="form-label">Descripción <span class="text-muted small">(opcional)</span></label>
            <input type="text" name="descripcion" class="form-control" maxlength="255"
                   value="<?= htmlspecialchars($_POST['descripcion'] ?? '') ?>">
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
            <a href="index.php?route=acceso/roles/list" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include '../helpers/cierre.php'; ?>
