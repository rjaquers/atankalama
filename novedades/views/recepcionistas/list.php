<?php include __DIR__ . '/../layout.php'; ?>

<div class='container mt-4'>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class='bi bi-people'></i> Personal del Hotel</h3>
        <a href='index.php?route=recepcionistas/create' class='btn btn-primary'>
            <i class='bi bi-person-plus'></i> Nuevo integrante
        </a>
    </div>

    <table id="tablaPersonal" class='table table-striped table-hover'>
        <thead>
        <tr>
            <th>Nombre</th>
            <th>Área</th>
            <th>Correo</th>
            <th>Fono</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($recepcionistas as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['nombre']) ?></td>
                <td>
                    <?php if ($r['area_nombre']): ?>
                        <span class="badge"
                              style="background-color: <?= htmlspecialchars($r['area_color'] ?? '#6c757d') ?>;">
                            <?= htmlspecialchars($r['area_nombre']) ?>
                        </span>
                    <?php else: ?>
                        <span class="text-muted small">Sin área</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($r['correo'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['fono'] ?? '') ?></td>
                <td>
                    <?php if ($r['activo']): ?>
                        <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td class="d-flex gap-1">
                    <a href="index.php?route=recepcionistas/edit&id=<?= $r['id'] ?>"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil-square"></i> Editar
                    </a>
                    <?php if ($r['activo']): ?>
                        <a href="index.php?route=recepcionistas/desactivar&id=<?= $r['id'] ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('¿Seguro que deseas desactivar a <?= htmlspecialchars(addslashes($r['nombre'])) ?>?')">
                            <i class="bi bi-person-dash"></i> Desactivar
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $('#tablaPersonal').DataTable({
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json" },
        "order": [[ 1, "asc" ], [ 0, "asc" ]],
        "pageLength": 25,
        "columnDefs": [
            { "orderable": false, "targets": 5 }
        ]
    });
});
</script>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
