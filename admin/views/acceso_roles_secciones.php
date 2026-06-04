<?php include 'layout.php'; ?>

<div class="container mt-4" style="max-width:700px;">

    <div class="d-flex align-items-center gap-2 mb-1">
        <a href="index.php?route=acceso/roles/list&app_id=<?= $rol['app_id'] ?>"
           class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0"><i class="bi bi-shield-check"></i> Secciones del Rol</h4>
    </div>
    <p class="text-muted small mb-4">
        <strong><?= htmlspecialchars($rol['nombre']) ?></strong>
        — <?= htmlspecialchars($rol['app_nombre']) ?>
    </p>

    <?php if (!empty($_SESSION['flash_ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_ok']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_ok']); ?>
    <?php endif; ?>

    <?php if (empty($secciones)): ?>
        <div class="alert alert-warning">
            Esta app no tiene secciones registradas.
            <a href="index.php?route=acceso/secciones/create&app_id=<?= $rol['app_id'] ?>">Agregar una sección</a>.
        </div>
    <?php else: ?>

    <form method="POST" action="index.php?route=acceso/roles/secciones/save">
        <input type="hidden" name="rol_id" value="<?= $rol['id'] ?>">

        <?php
        $publicas     = array_filter($secciones, fn($s) => $s['tipo'] === 'publica');
        $restringidas = array_filter($secciones, fn($s) => $s['tipo'] === 'restringida');
        ?>

        <?php if ($publicas): ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-globe"></i> Secciones públicas</span>
                <small class="text-muted">Accesibles por todos los usuarios autenticados</small>
            </div>
            <ul class="list-group list-group-flush">
            <?php foreach ($publicas as $s): ?>
                <li class="list-group-item py-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               name="secciones[]" value="<?= $s['id'] ?>"
                               id="sec_<?= $s['id'] ?>"
                               <?= $s['habilitada'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="sec_<?= $s['id'] ?>">
                            <code class="text-success small"><?= htmlspecialchars($s['slug']) ?></code>
                            <span class="ms-2 text-muted small">— <?= htmlspecialchars($s['nombre']) ?></span>
                        </label>
                    </div>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if ($restringidas): ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-lock-fill"></i> Secciones restringidas</span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="selTodos">Seleccionar todos</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="deselTodos">Quitar todos</button>
                </div>
            </div>
            <ul class="list-group list-group-flush">
            <?php foreach ($restringidas as $s): ?>
                <li class="list-group-item py-2">
                    <div class="form-check">
                        <input class="form-check-input chk-restringida" type="checkbox"
                               name="secciones[]" value="<?= $s['id'] ?>"
                               id="sec_<?= $s['id'] ?>"
                               <?= $s['habilitada'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="sec_<?= $s['id'] ?>">
                            <code class="text-danger small"><?= htmlspecialchars($s['slug']) ?></code>
                            <span class="ms-2 text-muted small">— <?= htmlspecialchars($s['nombre']) ?></span>
                        </label>
                    </div>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Guardar secciones
            </button>
        </div>
    </form>

    <?php endif; ?>
</div>

<script>
document.getElementById('selTodos')?.addEventListener('click', function () {
    document.querySelectorAll('.chk-restringida').forEach(c => c.checked = true);
});
document.getElementById('deselTodos')?.addEventListener('click', function () {
    document.querySelectorAll('.chk-restringida').forEach(c => c.checked = false);
});
</script>

<?php include '../helpers/cierre.php'; ?>
