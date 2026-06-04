<?php include __DIR__ . '/../layout.php'; ?>

<div class="container mt-4" style="max-width:520px;">
    <h4 class="mb-1"><i class="bi bi-pencil-square"></i> Editar Rol</h4>
    <p class="text-muted small mb-4"><?= htmlspecialchars($rol['app_nombre']) ?></p>

    <form method="POST" action="index.php?route=acceso/roles/update">
        <input type="hidden" name="id" value="<?= $rol['id'] ?>">

        <div class="mb-3">
            <label class="form-label">Nombre del rol</label>
            <input type="text" name="nombre" class="form-control" required maxlength="80"
                   value="<?= htmlspecialchars($rol['nombre']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input type="text" name="descripcion" class="form-control" maxlength="255"
                   value="<?= htmlspecialchars($rol['descripcion'] ?? '') ?>">
        </div>
        <div class="mb-4">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <option value="activo"   <?= $rol['estado'] === 'activo'   ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= $rol['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Actualizar</button>
            <a href="index.php?route=acceso/roles/secciones&id=<?= $rol['id'] ?>"
               class="btn btn-outline-warning">
                <i class="bi bi-shield-check"></i> Secciones
            </a>
            <a href="index.php?route=acceso/roles/list" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
