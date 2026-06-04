<!--
  = Proyecto: Sistema de Contratos Atankalama =
  = Autor: Rodrigo Jaque Escobar              =
  = Contacto: rjaquers@gmail.com              =

-->
<?php
$title = "Plantillas de Contrato";
include VIEW_PATH . "/layouts/header.php";
?>

<div class="header-section mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="h3 fw-bold mb-1">✍️ Plantillas de Contrato</h2>
            <p class="text-muted small">Personaliza el contenido HTML de tus documentos oficiales.</p>
        </div>
        <a href="<?= BASE_URL ?>/templates/create" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> Nueva Plantilla
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Nombre</th>
                    <th>Tipo</th>
                    <th>Actualizado</th>
                    <th class="text-end pe-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($templates as $t): ?>
                    <tr>
                        <td class="ps-4">
                            <strong><?= htmlspecialchars($t['name']) ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1 uppercase border border-secondary-subtle">
                                <?= $t['contract_type'] ?>
                            </span>
                        </td>
                        <td class="text-muted small">
                            <?= date('d/m/Y H:i', strtotime($t['updated_at'])) ?>
                        </td>
                        <td class="text-end pe-4">
                            <a href="<?= BASE_URL ?>/templates/edit/<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary shadow-sm rounded-pill px-3">
                                <i class="fa-solid fa-edit me-1"></i> Editar HTML
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($templates)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-file-circle-minus fa-2x mb-3 opacity-25"></i>
                            <p>No hay plantillas creadas. <a href="<?= BASE_URL ?>/templates/create">Comienza aquí</a>.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include VIEW_PATH . "/layouts/footer.php"; ?>
