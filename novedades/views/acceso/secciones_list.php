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
        <h4><i class="bi bi-link-45deg"></i> Secciones</h4>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <form method="GET" action="index.php" class="d-flex gap-2">
                <input type="hidden" name="route" value="acceso/secciones/list">
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
            <a href="index.php?route=acceso/secciones/create<?= $appSeleccionada ? '&app_id='.$appSeleccionada : '' ?>"
               class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Nueva sección
            </a>
        </div>
    </div>

    <table id="tablaSecciones" class="table table-striped table-hover align-middle">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Ruta (slug)</th>
                <th>App</th>
                <th>Tipo</th>
                <th>Origen</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($secciones as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['nombre']) ?></td>
                <td><code class="small"><?= htmlspecialchars($s['slug']) ?></code></td>
                <td>
                    <span class="badge bg-light text-dark border small">
                        <?= htmlspecialchars($s['app_nombre']) ?>
                    </span>
                </td>
                <td>
                    <?php if ($s['tipo'] === 'publica'): ?>
                        <span class="badge bg-success"><i class="bi bi-globe"></i> Pública</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark"><i class="bi bi-lock"></i> Restringida</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($s['origen'] === 'auto'): ?>
                        <span class="badge bg-info text-dark small">Auto</span>
                    <?php else: ?>
                        <span class="badge bg-secondary small">Manual</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($s['estado'] === 'activo'): ?>
                        <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td class="d-flex gap-1">
                    <a href="index.php?route=acceso/secciones/edit&id=<?= $s['id'] ?>"
                       class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a>
                    <a href="index.php?route=acceso/secciones/eliminar&id=<?= $s['id'] ?>"
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('¿Eliminar la sección «<?= htmlspecialchars(addslashes($s['nombre'])) ?>»?')">
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
    $('#tablaSecciones').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
        order: [[2, 'asc'], [1, 'asc']],
        pageLength: 50,
        columnDefs: [{ orderable: false, targets: 6 }]
    });
});
</script>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
