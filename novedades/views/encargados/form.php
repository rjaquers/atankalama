<?php include __DIR__ . '/../layout.php'; ?>

<div class="container mt-4">
    <h3><i class="bi bi-plus-circle"></i> Nuevo Encargado</h3>

    <form method="POST" action="index.php?route=encargados/store" class="card p-4 mt-3">
        <div class="mb-3">
            <label>Empresa</label>
            <select name="empresa_id" class="form-select" required>
                <option value="">Seleccionar empresa...</option>
                <?php foreach($empresas as $emp): ?>
                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Nombre del encargado</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Teléfono</label>
            <input type="text" name="telefono" class="form-control">
        </div>

        <div class="mb-3">
            <label>Correo electrónico</label>
            <input type="email" name="correo" class="form-control" required>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>Periodo desde</label>
                <input type="date" name="periodo_desde" class="form-control">
            </div>
            <div class="col">
                <label>Periodo hasta</label>
                <input type="date" name="periodo_hasta" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="bi bi-check2"></i> Guardar
        </button>
        <a href="index.php?route=encargados/list" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </form>
</div>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
