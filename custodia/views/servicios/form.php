<?php include __DIR__ . '/../../includes/header.php'; ?>
<?php include __DIR__.'/../../includes/inc_proyect.php'; ?>
<?php
// Resumen:
// Formulario para crear o editar servicios/adicionales de colaciones.
// Si $modo='nuevo' crea; si $modo='editar' actualiza.
// Fin resumen.
?>

<div class="container py-3" style="max-width: 720px;">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="m-0"><?= ($modo === 'editar') ? 'Editar servicio' : 'Nuevo servicio' ?></h3>
        <a class="btn btn-outline-secondary" href="<?= url('/servicios/listar') ?>">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <?= htmlspecialchars($flash['msg']) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" action="<?= ($modo === 'editar') ? url('/servicios/actualizar') : url('/servicios/guardar') ?>">
                <?php if ($modo === 'editar'): ?>
                    <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text"
                           name="nombre"
                           class="form-control"
                           maxlength="50"
                           required
                           value="<?= htmlspecialchars($item['nombre'] ?? '') ?>">
                    <div class="form-text">Debe ser único. Máximo 50 caracteres.</div>
                </div>

                <div class='mb-3'>
                    <label class='form-label'>Tipo de servicio</label>
                    <select name='tipo' class='form-select' required>
                        <option value='1' <?=(($item['tipo'] ?? 2) == 1) ? 'selected' : ''?>>
                            Plato principal
                        </option>
                        <option value="2" <?=(($item['tipo'] ?? 2) == 2) ? 'selected' : ''?>>
                            Acompañamiento
                        </option>
                    </select>
                </div>


                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                    <a class="btn btn-outline-secondary" href="<?= url('/servicios/listar') ?>">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>


<?php include __DIR__ . '/../../includes/footer.php'; ?>
