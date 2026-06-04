<?php include 'layout.php'; ?>

<div class="container mt-4" style="max-width:520px;">
    <h4 class="mb-4"><i class="bi bi-plus-circle"></i> Nueva Sección</h4>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form method="POST" action="index.php?route=acceso/secciones/store">
        <div class="mb-3">
            <label class="form-label">Aplicación</label>
            <select name="app_id" class="form-select" required>
                <option value="">— Seleccionar —</option>
                <?php foreach ($apps as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= $appId == $a['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Ruta <span class="text-muted small">(slug, tal como aparece en el router)</span></label>
            <input type="text" name="slug" class="form-control" required maxlength="150"
                   placeholder="Ej: empresas/list, /reportes, cocina/index"
                   value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Nombre legible</label>
            <input type="text" name="nombre" class="form-control" required maxlength="150"
                   placeholder="Ej: Listar empresas"
                   value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
        </div>
        <div class="mb-4">
            <label class="form-label">Tipo</label>
            <select name="tipo" class="form-select">
                <option value="restringida">Restringida — requiere rol con acceso</option>
                <option value="publica">Pública — cualquier usuario autenticado</option>
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
            <a href="index.php?route=acceso/secciones/list" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include '../helpers/cierre.php'; ?>
