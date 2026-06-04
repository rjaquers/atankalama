<?php include 'layout.php'; ?>

<div class="container mt-4" style="max-width:520px;">
    <h4 class="mb-4"><i class="bi bi-pencil-square"></i> Editar Aplicación</h4>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form method="POST" action="index.php?route=acceso/apps/update">
        <input type="hidden" name="id" value="<?= $app['id'] ?>">

        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required maxlength="100"
                   value="<?= htmlspecialchars($app['nombre']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-control" required maxlength="50"
                   pattern="[a-z0-9_]+" title="Solo minúsculas, números y guión bajo"
                   value="<?= htmlspecialchars($app['slug']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input type="text" name="descripcion" class="form-control" maxlength="255"
                   value="<?= htmlspecialchars($app['descripcion'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">URL de inicio <span class="text-muted small">(página principal para usuarios)</span></label>
            <input type="text" name="url_inicio" class="form-control" maxlength="500"
                   placeholder="/nombre-app/public/index.php"
                   value="<?= htmlspecialchars($app['url_inicio'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">URL de administración <span class="text-muted small">(panel admin)</span></label>
            <input type="text" name="url_admin" class="form-control" maxlength="500"
                   placeholder="/nombre-app/public/index.php?route=admin"
                   value="<?= htmlspecialchars($app['url_admin'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Prefijo de sesión <span class="text-muted small">(ej: nov, coc, inv — usado por el portal SSO)</span></label>
            <input type="text" name="session_prefix" class="form-control" maxlength="20"
                   placeholder="ej: nov"
                   value="<?= htmlspecialchars($app['session_prefix'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Ícono <span class="text-muted small">(clase Bootstrap Icons)</span></label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi <?= htmlspecialchars($app['icono'] ?? 'bi-grid-3x3-gap') ?>" id="iconoPreview"></i>
                </span>
                <input type="text" name="icono" id="iconoInput" class="form-control" maxlength="50"
                       placeholder="bi-grid-3x3-gap"
                       value="<?= htmlspecialchars($app['icono'] ?? '') ?>">
            </div>
            <div class="form-text">
                Ver íconos en <a href="https://icons.getbootstrap.com" target="_blank" rel="noopener">icons.getbootstrap.com</a>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <option value="activo"   <?= $app['estado'] === 'activo'   ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= $app['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Actualizar</button>
            <a href="index.php?route=acceso/apps/list" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script>
document.getElementById('iconoInput')?.addEventListener('input', function () {
    const el = document.getElementById('iconoPreview');
    el.className = 'bi ' + (this.value.trim() || 'bi-grid-3x3-gap');
});
</script>
<?php include '../helpers/cierre.php'; ?>
