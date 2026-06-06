<!DOCTYPE html>
<html lang='es'>

<head>
    <?php include(ROOT_PATH . '../public/static/templates/head.php'); ?>
</head>

<body class='pro-body'>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

    <div class='container py-4'>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom"
            style="border-color: var(--color-border) !important;">
            <h2 class="mb-0 fw-bold">
                <i class="bi bi-building me-2" style="color: var(--color-cta)"></i>
                <?= htmlspecialchars($empresa['business_name']) ?>
            </h2>
            <a href="index.php?page=empresa/index" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= $_GET['success'] === 'proyecto' ? 'Proyecto/faena agregado correctamente.' : 'Proyecto eliminado correctamente.' ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Faltan datos requeridos.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">

            <!-- Datos de la empresa -->
            <div class="col-md-4">
                <div class="pro-card border-0 h-100">
                    <div class="card-header bg-transparent py-3 px-4" style="border-bottom: 1px solid var(--color-border);">
                        <h5 class="fw-bold mb-0" style="color: var(--color-primary);">
                            <i class="bi bi-info-circle me-2" style="color: var(--color-cta)"></i>Datos
                        </h5>
                    </div>
                    <div class="card-body px-4 py-3">
                        <dl class="row mb-0">
                            <?php if (!empty($empresa['rut'])): ?>
                                <dt class="col-5 text-muted small">RUT</dt>
                                <dd class="col-7 fw-semibold"><?= htmlspecialchars($empresa['rut']) ?></dd>
                            <?php endif; ?>

                            <?php if (!empty($empresa['trade_name'])): ?>
                                <dt class="col-5 text-muted small">Nombre Comercial</dt>
                                <dd class="col-7"><?= htmlspecialchars($empresa['trade_name']) ?></dd>
                            <?php endif; ?>

                            <?php if (!empty($empresa['contact_name'])): ?>
                                <dt class="col-5 text-muted small">Contacto</dt>
                                <dd class="col-7"><?= htmlspecialchars($empresa['contact_name']) ?></dd>
                            <?php endif; ?>

                            <?php if (!empty($empresa['contact_email'])): ?>
                                <dt class="col-5 text-muted small">Email</dt>
                                <dd class="col-7 small"><?= htmlspecialchars($empresa['contact_email']) ?></dd>
                            <?php endif; ?>

                            <?php if (!empty($empresa['contact_phone'])): ?>
                                <dt class="col-5 text-muted small">Teléfono</dt>
                                <dd class="col-7"><?= htmlspecialchars($empresa['contact_phone']) ?></dd>
                            <?php endif; ?>

                            <?php if (!empty($empresa['city'])): ?>
                                <dt class="col-5 text-muted small">Ciudad</dt>
                                <dd class="col-7"><?= htmlspecialchars($empresa['city']) ?></dd>
                            <?php endif; ?>

                            <?php if (!empty($empresa['notes'])): ?>
                                <dt class="col-5 text-muted small">Notas</dt>
                                <dd class="col-7 small"><?= nl2br(htmlspecialchars($empresa['notes'])) ?></dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Proyectos / Faenas -->
            <div class="col-md-8">
                <div class="pro-card border-0">
                    <div class="card-header bg-transparent py-3 px-4 d-flex justify-content-between align-items-center"
                        style="border-bottom: 1px solid var(--color-border);">
                        <h5 class="fw-bold mb-0" style="color: var(--color-primary);">
                            <i class="bi bi-diagram-3 me-2" style="color: var(--color-cta)"></i>
                            Proyectos / Faenas
                            <span class="badge bg-secondary ms-1"><?= count($proyectos) ?></span>
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarProyecto">
                            <i class="bi bi-plus-circle me-1"></i>Agregar
                        </button>
                    </div>
                    <div class="card-body px-4 pb-4 pt-3">
                        <?php if (empty($proyectos)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-diagram-3 d-block mb-3" style="font-size: 2rem; color: #cbd5e1;"></i>
                                No hay proyectos registrados para esta empresa.
                            </div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($proyectos as $proy): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span>
                                            <i class="bi bi-folder2-open me-2 text-muted"></i>
                                            <?= htmlspecialchars($proy['name']) ?>
                                        </span>
                                        <a href="index.php?page=empresa/eliminarProyecto&id=<?= $proy['id'] ?>&company_id=<?= $empresa['id'] ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('¿Eliminar el proyecto «<?= htmlspecialchars(addslashes($proy['name'])) ?>»?')"
                                           title="Eliminar proyecto">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Agregar Proyecto -->
    <div class="modal fade" id="modalAgregarProyecto" tabindex="-1" aria-labelledby="modalAgregarProyectoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="index.php?page=empresa/agregarProyecto">
                    <input type="hidden" name="company_id" value="<?= $empresa['id'] ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAgregarProyectoLabel">
                            <i class="bi bi-plus-circle me-2"></i>Agregar Proyecto / Faena
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombreProyecto" class="form-label fw-semibold">Nombre del proyecto / faena <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombreProyecto" name="nombre"
                                   placeholder="Ej: Faena Campaña Norte 2026" required maxlength="200">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>
</body>

</html>
