<?php include __DIR__ . '/../layout.php'; ?>

<div class="container-fluid mt-4">

    <?php if (!empty($_SESSION['flash_ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_ok']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_ok']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4><i class="bi bi-person-badge-fill"></i> Roles</h4>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <!-- Filtro por app -->
            <form method="GET" action="index.php" class="d-flex gap-2">
                <input type="hidden" name="route" value="acceso/roles/list">
                <select name="app_id" class="form-select form-select-sm" onchange="this.form.submit()"
                        style="min-width:180px;">
                    <option value="">Todas las apps</option>
                    <?php foreach ($apps as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= $appSeleccionada == $a['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <a href="index.php?route=acceso/roles/create<?= $appSeleccionada ? '&app_id='.$appSeleccionada : '' ?>"
               class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Nuevo rol
            </a>
        </div>
    </div>

    <table id="tablaRoles" class="table table-striped table-hover align-middle">
        <thead>
            <tr>
                <th>Rol</th>
                <th>Aplicación</th>
                <th>Secciones</th>
                <th>Usuarios</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($roles as $r): ?>
            <tr>
                <td class="fw-semibold"><?= htmlspecialchars($r['nombre']) ?></td>
                <td>
                    <span class="badge bg-light text-dark border">
                        <?= htmlspecialchars($r['app_nombre']) ?>
                    </span>
                </td>
                <td><span class="badge bg-secondary"><?= $r['total_secciones'] ?></span></td>
                <td><span class="badge bg-info text-dark"><?= $r['total_usuarios'] ?></span></td>
                <td>
                    <?php if ($r['estado'] === 'activo'): ?>
                        <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td class="d-flex gap-1 flex-wrap">
                    <a href="index.php?route=acceso/roles/secciones&id=<?= $r['id'] ?>"
                       class="btn btn-sm btn-outline-warning" title="Gestionar secciones del rol">
                        <i class="bi bi-shield-check"></i> Secciones
                    </a>
                    <a href="index.php?route=acceso/roles/edit&id=<?= $r['id'] ?>"
                       class="btn btn-sm btn-outline-primary" title="Editar rol">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="index.php?route=acceso/roles/eliminar&id=<?= $r['id'] ?>"
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('¿Eliminar el rol «<?= htmlspecialchars(addslashes($r['nombre'])) ?>»? Se quitará de todos los usuarios.')">
                        <i class="bi bi-trash"></i>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function () {
    $('#tablaRoles').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
        order: [[1, 'asc'], [0, 'asc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: 5 }]
    });
});
</script>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
