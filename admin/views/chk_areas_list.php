<?php include 'layout.php'; ?>

<div class="container-fluid mt-4">

    <?php if (!empty($_SESSION['flash_ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_ok']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_ok']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-diagram-3-fill"></i> Áreas</h4>
        <a href="index.php?route=chk/areas/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva área
        </a>
    </div>

    <table id="tablaAreas" class="table table-striped table-hover align-middle">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Creada</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($areas as $a): ?>
            <tr>
                <td class="fw-semibold"><?= htmlspecialchars($a['nombre']) ?></td>
                <td class="text-muted"><?= htmlspecialchars($a['descripcion'] ?? '—') ?></td>
                <td>
                    <?php if ($a['estado'] === 'activo'): ?>
                        <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td class="small text-muted"><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
                <td class="d-flex gap-1">
                    <a href="index.php?route=chk/areas/edit&id=<?= $a['id'] ?>"
                       class="btn btn-sm btn-outline-primary" title="Editar">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <form method="POST" action="index.php?route=chk/areas/eliminar"
                          onsubmit="return confirm('¿Eliminar el área «<?= htmlspecialchars($a['nombre'], ENT_QUOTES) ?>»? Esta acción no se puede deshacer.')">
                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function () {
    $('#tablaAreas').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
        order: [[0, 'asc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: 4 }]
    });
});
</script>

<?php include '../helpers/cierre.php'; ?>
