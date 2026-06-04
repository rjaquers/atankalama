<?php include __DIR__ . '/../layout.php'; ?>

<div class="container mt-4" style="max-width:520px;">
    <h4 class="mb-1"><i class="bi bi-pencil-square"></i> Editar Sección</h4>
    <p class="text-muted small mb-4"><?= htmlspecialchars($seccion['app_nombre']) ?></p>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form method="POST" action="index.php?route=acceso/secciones/update">
        <input type="hidden" name="id" value="<?= $seccion['id'] ?>">

        <div class="mb-3">
            <label class="form-label">Ruta</label>
            <input type="text" name="slug" class="form-control" required maxlength="150"
                   value="<?= htmlspecialchars($seccion['slug']) ?>">
            <?php if ($seccion['origen'] === 'auto'): ?>
                <div class="form-text text-info">
                    <i class="bi bi-info-circle"></i> Detectada automáticamente del router.
                </div>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label class="form-label">Nombre legible</label>
            <input type="text" name="nombre" class="form-control" required maxlength="150"
                   value="<?= htmlspecialchars($seccion['nombre']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Tipo</label>
            <select name="tipo" class="form-select">
                <option value="restringida" <?= $seccion['tipo'] === 'restringida' ? 'selected' : '' ?>>
                    Restringida
                </option>
                <option value="publica" <?= $seccion['tipo'] === 'publica' ? 'selected' : '' ?>>
                    Pública
                </option>
            </select>
        </div>
        <div class="mb-4">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <option value="activo"   <?= $seccion['estado'] === 'activo'   ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= $seccion['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Actualizar</button>
            <a href="index.php?route=acceso/secciones/list" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
