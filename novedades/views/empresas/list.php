<?php
include __DIR__ . '/../layout.php'; ?>

<div class="container mt-4">
    <h3><i class="bi bi-building"></i> Empresas</h3>

    <a href="index.php?route=empresas/create" class="btn btn-primary mb-3">
        <i class="bi bi-plus-circle"></i> Nueva Empresa
    </a>

    <table id='tablaEmpresas' class='table table-striped table-hover'>
        <thead>
            <tr class="table-warning">
                <th>Razón Social</th>
                <th>Nombre Fantasía</th>
                <th>RUT</th>
                <th>Tipo</th>
                <th>Contacto</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($empresas as $e): ?>
            <tr>
                <td><?= htmlspecialchars($e['business_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($e['trade_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($e['rut'] ?? '') ?></td>
                <td>
                    <?php if (($e['type'] ?? '') === 'proveedor'): ?>
                        <span class="badge bg-info">Proveedor</span>
                    <?php else: ?>
                        <span class="badge bg-primary">Cliente</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?= htmlspecialchars($e['contact_name'] ?? '') ?><br>
                    <small class="text-muted"><?= htmlspecialchars($e['contact_email'] ?? '') ?></small>
                </td>
                <td><?= htmlspecialchars($e['contact_phone'] ?? '') ?></td>
                <td>
                    <?php if ($e['active'] ?? 1): ?>
                        <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td class="d-flex gap-1">
                    <a href="index.php?route=empresas/edit&id=<?= $e['id'] ?>"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <a href="index.php?route=empresas/delete&id=<?= $e['id'] ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('¿Seguro que deseas eliminar esta empresa?')">
                        <i class="bi bi-trash"></i> Eliminar
                    </a>

                    <a href="index.php?route=encargados/list&empresa_id=<?=(int)$e['id']?>"
                       class='btn btn-sm btn-outline-secondary'>
                        <i class='bi bi-people'></i> Encargados
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
</table>
</div>

<script>
    $(document).ready(function () {
        $('#tablaEmpresas').DataTable({
            pageLength: 25,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: 5 }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            }
        });
    });
</script>
<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
