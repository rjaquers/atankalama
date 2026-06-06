<?php include 'layout.php'; ?>

<div class="container-fluid mt-4">

    <?php if (!empty($_SESSION['flash_ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['flash_ok'] ?>
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
        <div>
            <h4 class="mb-0">
                <i class="bi bi-diagram-3-fill"></i>
                Proyectos — <?= htmlspecialchars($empresa['business_name']) ?>
            </h4>
            <small class="text-muted">Empresa #<?= $empresa['id'] ?></small>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?route=doc_companies/list" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a Empresas
            </a>
            <a href="index.php?route=doc_companies/proyectos/create&company=<?= $empresa['id'] ?>"
               class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuevo proyecto
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table id="tablaProyectos" class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre del proyecto</th>
                        <th>Estado</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($proyectos as $p): ?>
                    <tr>
                        <td class="text-muted small"><?= $p['id'] ?></td>
                        <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                        <td>
                            <?php if ($p['active']): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted">
                            <?= $p['created_at'] ? date('d/m/Y', strtotime($p['created_at'])) : '—' ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="index.php?route=doc_companies/proyectos/edit&id=<?= $p['id'] ?>&company=<?= $empresa['id'] ?>"
                                   class="btn btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="index.php?route=doc_companies/proyectos/delete"
                                      onsubmit="return confirm('¿Eliminar el proyecto «<?= htmlspecialchars(addslashes($p['name'])) ?>»?')">
                                    <input type="hidden" name="id"         value="<?= $p['id'] ?>">
                                    <input type="hidden" name="company_id" value="<?= $empresa['id'] ?>">
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

            <?php if (empty($proyectos)): ?>
                <p class="text-center text-muted py-4">
                    <i class="bi bi-diagram-3 fs-2 d-block mb-2"></i>
                    Esta empresa aún no tiene proyectos registrados.
                    <a href="index.php?route=doc_companies/proyectos/create&company=<?= $empresa['id'] ?>">Agregar el primero</a>.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#tablaProyectos').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
        order: [[2, 'desc'], [1, 'asc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: [4] }]
    });
});
</script>

<?php include '../helpers/cierre.php'; ?>
