<?php include __DIR__ . '/../layout.php'; ?>

<div class="container mt-4" style="max-width:700px;">

    <div class="d-flex align-items-center gap-2 mb-1">
        <a href="index.php?route=acceso/usuarios/list" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0"><i class="bi bi-key-fill"></i> Accesos y Roles</h4>
    </div>
    <p class="text-muted small mb-4">
        <strong><?= htmlspecialchars(trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''))) ?></strong>
        — <?= htmlspecialchars($usuario['email']) ?>
    </p>

    <?php if (!empty($_SESSION['flash_ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_ok']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_ok']); ?>
    <?php endif; ?>

    <form method="POST" action="index.php?route=acceso/usuarios/permisos/save">
        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">

        <div class="card">
            <div class="card-header fw-semibold">
                Aplicaciones y roles asignados
            </div>
            <ul class="list-group list-group-flush">
            <?php foreach ($apps as $app): ?>
                <?php $tieneAcceso = (bool)$app['tiene_acceso']; ?>
                <li class="list-group-item py-3">
                    <div class="d-flex align-items-start gap-3">
                        <!-- Checkbox de acceso a la app -->
                        <div class="form-check mt-1">
                            <input class="form-check-input app-check"
                                   type="checkbox"
                                   name="apps[]"
                                   value="<?= $app['id'] ?>"
                                   id="app_<?= $app['id'] ?>"
                                   data-app="<?= $app['id'] ?>"
                                   <?= $tieneAcceso ? 'checked' : '' ?>>
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-check-label fw-semibold" for="app_<?= $app['id'] ?>">
                                <?= htmlspecialchars($app['nombre']) ?>
                                <span class="text-muted fw-normal small ms-1">(<?= htmlspecialchars($app['slug']) ?>)</span>
                            </label>

                            <!-- Selector de rol (visible solo si hay acceso) -->
                            <div class="rol-selector mt-2 <?= $tieneAcceso ? '' : 'd-none' ?>"
                                 id="rol_wrap_<?= $app['id'] ?>">
                                <select name="roles[<?= $app['id'] ?>]"
                                        class="form-select form-select-sm"
                                        style="max-width:280px;">
                                    <option value="">— Sin rol asignado —</option>
                                    <?php foreach ($rolesPorApp[$app['id']] ?? [] as $rol): ?>
                                        <option value="<?= $rol['id'] ?>"
                                            <?= (int)$app['rol_id'] === (int)$rol['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($rol['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($rolesPorApp[$app['id']])): ?>
                                    <small class="text-warning">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        Esta app no tiene roles definidos aún.
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Guardar cambios
            </button>
            <a href="index.php?route=acceso/usuarios/edit&id=<?= $usuario['id'] ?>"
               class="btn btn-outline-secondary">Editar datos</a>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('.app-check').forEach(function (chk) {
    chk.addEventListener('change', function () {
        var wrap = document.getElementById('rol_wrap_' + this.dataset.app);
        wrap.classList.toggle('d-none', !this.checked);
    });
});
</script>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
