<?php include 'layout.php'; ?>

<div class="container mt-4" style="max-width:520px;">
    <h4 class="mb-4"><i class="bi bi-pencil-square"></i> Editar Área</h4>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form method="POST" action="index.php?route=chk/areas/update">
        <input type="hidden" name="id" value="<?= $area['id'] ?>">

        <div class="mb-3">
            <label class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" class="form-control" required maxlength="100"
                   value="<?= htmlspecialchars($area['nombre']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($area['descripcion'] ?? '') ?></textarea>
        </div>
        <div class="mb-4">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <option value="activo"   <?= $area['estado'] === 'activo'   ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= $area['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Actualizar</button>
            <a href="index.php?route=chk/areas/list" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include '../helpers/cierre.php'; ?>
