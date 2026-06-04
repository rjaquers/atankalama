<?php
 include __DIR__ . '/../layout.php'; ?>

<div class="container mt-4">
    <h3><i class="bi bi-pencil-square"></i> Editar Encargado de Empresa</h3>

    <form method="POST" action="index.php?route=encargados/update" class="card p-4 mt-3">

        <input type="hidden" name="id" value="<?= htmlspecialchars($encargado['id']) ?>">

        <div class="mb-3">
            <label for="empresa_id" class="form-label">Empresa</label>
            <select name="empresa_id" id="empresa_id" class="form-select" required>
                <?php foreach ($empresas as $emp): ?>
                    <option value="<?= $emp['id'] ?>" <?= $encargado['empresa_id'] == $emp['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($emp['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre del encargado</label>
            <input type="text" class="form-control" id="nombre" name="nombre"
                   value="<?= htmlspecialchars($encargado['nombre']) ?>" required>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" id="telefono" name="telefono"
                       value="<?= htmlspecialchars($encargado['telefono']) ?>">
            </div>
            <div class="col-md-6">
                <label for="correo" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" id="correo" name="correo"
                       value="<?= htmlspecialchars($encargado['correo']) ?>" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="periodo_desde" class="form-label">Periodo desde</label>
                <input type="date" class="form-control" id="periodo_desde" name="periodo_desde"
                       value="<?= htmlspecialchars($encargado['periodo_desde']) ?>">
            </div>
            <div class="col-md-6">
                <label for="periodo_hasta" class="form-label">Periodo hasta</label>
                <input type="date" class="form-control" id="periodo_hasta" name="periodo_hasta"
                       value="<?= htmlspecialchars($encargado['periodo_hasta']) ?>">
            </div>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="activo" name="activo"
                   <?= $encargado['activo'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="activo">Encargado activo</label>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save"></i> Guardar cambios
            </button>
            <a href="index.php?route=encargados/list" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
