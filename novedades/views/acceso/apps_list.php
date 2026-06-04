<?php include __DIR__ . '/../layout.php'; ?>

<div class="container-fluid mt-4">

    <?php if (!empty($_SESSION['flash_ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_ok']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_ok']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-grid-fill"></i> Aplicaciones</h4>
        <a href="index.php?route=acceso/apps/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva app
        </a>
    </div>

    <table id="tablaApps" class="table table-striped table-hover align-middle">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Slug</th>
                <th>Usuarios</th>
                <th>Roles</th>
                <th>Secciones</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($apps as $a): ?>
            <tr>
                <td class="fw-semibold"><?= htmlspecialchars($a['nombre']) ?></td>
                <td><code><?= htmlspecialchars($a['slug']) ?></code></td>
                <td><span class="badge bg-info text-dark"><?= $a['total_usuarios'] ?></span></td>
                <td><span class="badge bg-secondary"><?= $a['total_roles'] ?></span></td>
                <td><span class="badge bg-secondary"><?= $a['total_secciones'] ?></span></td>
                <td>
                    <?php if ($a['estado'] === 'activo'): ?>
                        <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td class="d-flex gap-1 flex-wrap">
                    <?php if (!empty($a['url_inicio'])): ?>
                    <a href="<?= htmlspecialchars($a['url_inicio']) ?>"
                       class="btn btn-sm btn-success" title="Abrir página de inicio" target="_blank">
                        <i class="bi bi-house-door"></i> Inicio
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($a['url_admin'])): ?>
                    <a href="<?= htmlspecialchars($a['url_admin']) ?>"
                       class="btn btn-sm btn-outline-success" title="Abrir panel de administración" target="_blank">
                        <i class="bi bi-gear"></i> Admin
                    </a>
                    <?php endif; ?>
                    <a href="index.php?route=acceso/apps/edit&id=<?= $a['id'] ?>"
                       class="btn btn-sm btn-outline-primary" title="Editar">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="index.php?route=acceso/roles/list&app_id=<?= $a['id'] ?>"
                       class="btn btn-sm btn-outline-secondary" title="Ver roles">
                        <i class="bi bi-person-badge"></i> Roles
                    </a>
                    <a href="index.php?route=acceso/secciones/list&app_id=<?= $a['id'] ?>"
                       class="btn btn-sm btn-outline-secondary" title="Ver secciones">
                        <i class="bi bi-link-45deg"></i> Secciones
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function () {
    $('#tablaApps').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
        order: [[0, 'asc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: 6 }]
    });
});
</script>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
