<!DOCTYPE html>
<html lang='es'>

<head>
    <?php include(ROOT_PATH . '../public/static/templates/head.php'); ?>
</head>

<body class='pro-body'>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

    <div class='container py-4'>

        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom"
            style="border-color: var(--color-border) !important;">
            <h2 class="mb-0 fw-bold">
                <i class="bi bi-building me-2" style="color: var(--color-cta)"></i>Empresas
            </h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoProyecto">
                <i class="bi bi-plus-circle me-1"></i>Nuevo Proyecto
            </button>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>Operación realizada correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="pro-card border-0 mb-4">
            <div class="card-header bg-transparent py-3 px-4" style="border-bottom: 1px solid var(--color-border);">
                <h5 class="fw-bold mb-0" style="color: var(--color-primary);">
                    <i class="bi bi-table me-2" style="color: var(--color-cta)"></i>Listado de Empresas
                </h5>
            </div>
            <div class="card-body px-4 pb-4 pt-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tablaEmpresas">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">Razón Social</th>
                                <th class="border-0">Nombre Comercial</th>
                                <th class="border-0">Contacto</th>
                                <th class="border-0">Email</th>
                                <th class="border-0 text-center">Tipo</th>
                                <th class="border-0 text-center">Proyectos</th>
                                <th class="border-0 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($empresas as $emp): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($emp['business_name']) ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($emp['trade_name'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($emp['contact_name'] ?? '—') ?></td>
                                    <td class="small"><?= htmlspecialchars($emp['contact_email'] ?? '—') ?></td>
                                    <td class="text-center">
                                        <?php if (($emp['type'] ?? '') === 'proveedor'): ?>
                                            <span class="badge bg-warning text-dark">Proveedor</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Cliente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?= (int)$emp['proyectos'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="index.php?page=empresa/ver/<?= $emp['id'] ?>"
                                           class="btn btn-sm btn-outline-primary shadow-sm"
                                           title="Ver empresa y proyectos">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($empresas)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-building d-block mb-3" style="font-size: 2rem; color: #cbd5e1;"></i>
                                        No hay empresas registradas.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Proyecto -->
    <div class="modal fade" id="modalNuevoProyecto" tabindex="-1" aria-labelledby="modalNuevoProyectoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="index.php?page=empresa/agregarProyecto">
                    <input type="hidden" name="redirect" value="empresa/index">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalNuevoProyectoLabel">
                            <i class="bi bi-plus-circle me-2"></i>Nuevo Proyecto / Faena
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="selectEmpresa" class="form-label fw-semibold">Empresa <span class="text-danger">*</span></label>
                            <select class="form-select" id="selectEmpresa" name="company_id" required>
                                <option value="">— Seleccionar empresa —</option>
                                <?php foreach ($empresas as $emp): ?>
                                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['business_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
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

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#tablaEmpresas').DataTable({
                "order": [[0, "asc"]],
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
                "pageLength": 25,
                "responsive": true
            });
        });
    </script>
</body>

</html>
