<?php include __DIR__ . '/../layout.php'; ?>

<div class="container mt-4">
    <h3><i class="bi bi-person-plus"></i> Nuevo integrante del personal</h3>

    <form method="POST" action="index.php?route=recepcionistas/store" class="card p-4 mt-3">

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre completo</label>
            <input type="text" class="form-control" id="nombre" name="nombre"
                   placeholder="Ej: Ana Pérez" required>
        </div>

        <div class="mb-3">
            <label for="area_id" class="form-label">Área</label>
            <select class="form-select" id="area_id" name="area_id">
                <option value="">— Sin área asignada —</option>
                <?php foreach ($areas as $area): ?>
                    <option value="<?= $area['id'] ?>">
                        <?= htmlspecialchars($area['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="fono" class="form-label">Fono de contacto</label>
            <input type="text" class="form-control" id="fono" name="fono"
                   placeholder="Ej: +56 9 1234 5678">
        </div>

        <div class="mb-3">
            <label for="correo" class="form-label">Correo electrónico</label>
            <input type="email" class="form-control" id="correo" name="correo"
                   placeholder="Ej: correo@empresa.com">
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check2"></i> Guardar
            </button>
            <a href="index.php?route=recepcionistas/list" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
