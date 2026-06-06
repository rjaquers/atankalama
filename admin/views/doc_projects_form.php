<?php include 'layout.php'; ?>

<div class="container mt-4" style="max-width:560px">

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <?php $esEdicion = !empty($proyecto); ?>

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="index.php?route=doc_companies/proyectos&company=<?= $empresa['id'] ?>"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0">
                <i class="bi bi-<?= $esEdicion ? 'pencil-square' : 'plus-circle' ?>"></i>
                <?= $esEdicion ? 'Editar proyecto' : 'Nuevo proyecto' ?>
            </h4>
            <small class="text-muted"><?= htmlspecialchars($empresa['business_name']) ?></small>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if ($esEdicion): ?>
                <form method="POST" action="index.php?route=doc_companies/proyectos/update">
                    <input type="hidden" name="id"         value="<?= $proyecto['id'] ?>">
                    <input type="hidden" name="company_id" value="<?= $empresa['id'] ?>">
            <?php else: ?>
                <form method="POST" action="index.php?route=doc_companies/proyectos/store">
                    <input type="hidden" name="company_id" value="<?= $empresa['id'] ?>">
            <?php endif; ?>

                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        Nombre del proyecto <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name" class="form-control form-control-lg"
                           value="<?= htmlspecialchars($proyecto['name'] ?? '') ?>"
                           placeholder="Ej: Faena Norte, Proyecto Collahuasi…"
                           required autofocus>
                </div>

                <?php if ($esEdicion): ?>
                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" name="active" id="chkActive"
                           <?= $proyecto['active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="chkActive">Proyecto activo</label>
                </div>
                <?php endif; ?>

                <div class="d-flex justify-content-end gap-2">
                    <a href="index.php?route=doc_companies/proyectos&company=<?= $empresa['id'] ?>"
                       class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-floppy"></i>
                        <?= $esEdicion ? 'Guardar cambios' : 'Crear proyecto' ?>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../helpers/cierre.php'; ?>
