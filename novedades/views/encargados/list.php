<?php
 include __DIR__ . '/../layout.php'; ?>

<div class="container mt-4">
    <h3><i class="bi bi-person-badge"></i> Encargados de Empresas</h3>

    <a href="index.php?route=encargados/create" class="btn btn-primary mb-3">
        <i class="bi bi-plus-circle"></i> Nuevo Encargado
    </a>

    <table id="tablaEncargados" class='table table-striped table-hover'>
        <thead>
            <tr >
                <th>Nombre</th>
                <th>Empresa</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Periodo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($encargados as $e): ?>
            <tr >

                <td><?= htmlspecialchars($e['nombre']) ?></td>
                <td><?= htmlspecialchars($e['empresa']) ?></td>
                <td><?= htmlspecialchars($e['telefono']) ?></td>
                <td><?= htmlspecialchars($e['correo']) ?></td>
                <td><?= htmlspecialchars($e['periodo_desde'].' → '.$e['periodo_hasta']) ?></td>
                <td>
                    <?= $e['activo'] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' ?>
                </td>
                <td>
                    <a href="index.php?route=encargados/edit&id=<?= $e['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                    <a href="index.php?route=encargados/delete&id=<?= $e['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este encargado?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $('#tablaEncargados').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
        },
        "order": [[ 0, "asc" ]], // Ordenar por nombre por defecto
        "pageLength": 25
    });
});
</script>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
