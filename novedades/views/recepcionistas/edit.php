<?php include __DIR__ . '/../layout.php'; ?>

<div class="container mt-4">
    <h3><i class="bi bi-person"></i> Editar integrante del personal</h3>

    <form action="index.php?route=recepcionistas/update" method="post" class="card p-4 mt-3">
        <input type="hidden" name="id" value="<?= htmlspecialchars($recepcionista['id']) ?>">

        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control"
                   value="<?= htmlspecialchars($recepcionista['nombre']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Área</label>
            <select class="form-select" name="area_id">
                <option value="">— Sin área asignada —</option>
                <?php foreach ($areas as $area): ?>
                    <option value="<?= $area['id'] ?>"
                        <?= (int)($recepcionista['area_id'] ?? 0) === (int)$area['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($area['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Fono</label>
            <input type="text" name="fono" class="form-control"
                   value="<?= htmlspecialchars($recepcionista['fono'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Correo electrónico</label>
            <input type="email" name="correo" class="form-control"
                   value="<?= htmlspecialchars($recepcionista['correo'] ?? '') ?>">
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="activo" id="activo"
                   <?= $recepcionista['activo'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="activo">Activo</label>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save"></i> Guardar cambios
            </button>
            <a href="index.php?route=recepcionistas/list" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
