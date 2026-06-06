<?php include 'layout.php'; ?>

<div class="container-fluid mt-4">

    <?php if (!empty($_SESSION['flash_ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_ok']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_ok']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-building"></i> Gestión de Empresas</h4>
        <a href="index.php?route=doc_companies/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Empresa
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table id="tablaEmpresas" class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Razón Social</th>
                        <th>RUT</th>
                        <th>Tipo</th>
                        <th>Contacto</th>
                        <th>Ciudad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($companies as $c): ?>
                    <tr>
                        <td class="text-muted small"><?= $c['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($c['business_name']) ?></strong>
                            <?php if ($c['trade_name']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($c['trade_name']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($c['rut'] ?: '—') ?></td>
                        <td>
                            <span class="badge bg-<?= $c['type'] === 'cliente' ? 'info text-dark' : 'warning text-dark' ?>">
                                <?= ucfirst($c['type']) ?>
                            </span>
                        </td>
                        <td>
                            <?= htmlspecialchars($c['contact_name'] ?: '—') ?>
                            <?php if ($c['contact_email']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($c['contact_email']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($c['city'] ?: '—') ?></td>
                        <td>
                            <?php if ($c['active']): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="index.php?route=doc_companies/proyectos&company=<?= $c['id'] ?>"
                                   class="btn btn-outline-info" title="Proyectos">
                                    <i class="bi bi-diagram-3-fill"></i>
                                </a>
                                <a href="index.php?route=emp/usuarios/list&company=<?= $c['id'] ?>"
                                   class="btn btn-outline-warning" title="Usuarios con acceso al portal">
                                    <i class="bi bi-people-fill"></i>
                                </a>
                                <a href="index.php?route=doc_companies/edit&id=<?= $c['id'] ?>"
                                   class="btn btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="index.php?route=doc_companies/delete" 
                                      onsubmit="return confirm('¿Está seguro de eliminar esta empresa?')" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#tablaEmpresas').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
        order: [[0, 'asc']],
        pageLength: 25
    });
});
</script>

<?php include '../helpers/cierre.php'; ?>
