<?php
include __DIR__ . '/../layout.php'; ?>

<div class="container mt-4">
    <h3><i class="bi bi-pencil-square"></i> Editar Empresa</h3>

    <form method="POST" action="index.php?route=empresas/update" class="card p-4 mt-3">
        <input type="hidden" name="id" value="<?= htmlspecialchars($empresa['id']) ?>">

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="business_name" class="form-label">Razón Social *</label>
                <input type="text" class="form-control" id="business_name" name="business_name"
                       value="<?= htmlspecialchars($empresa['business_name'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
                <label for="trade_name" class="form-label">Nombre de Fantasía</label>
                <input type="text" class="form-control" id="trade_name" name="trade_name"
                       value="<?= htmlspecialchars($empresa['trade_name'] ?? '') ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="rut" class="form-label">RUT</label>
                <input type="text" class="form-control" id="rut" name="rut"
                       value="<?= htmlspecialchars($empresa['rut'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label for="type" class="form-label">Tipo *</label>
                <select class="form-select" id="type" name="type" required>
                    <option value="cliente" <?= (($empresa['type'] ?? '') === 'cliente') ? 'selected' : '' ?>>Cliente</option>
                    <option value="proveedor" <?= (($empresa['type'] ?? '') === 'proveedor') ? 'selected' : '' ?>>Proveedor</option>
                </select>
            </div>
            <div class="col-md-4"></div>
        </div>

        <hr>
        <h5 class="mb-3">Información de Contacto</h5>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="contact_name" class="form-label">Nombre de Contacto</label>
                <input type="text" class="form-control" id="contact_name" name="contact_name"
                       value="<?= htmlspecialchars($empresa['contact_name'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label for="contact_email" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="contact_email" name="contact_email"
                       value="<?= htmlspecialchars($empresa['contact_email'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label for="contact_phone" class="form-label">Teléfono</label>
                <input type="text" class="form-control" id="contact_phone" name="contact_phone"
                       value="<?= htmlspecialchars($empresa['contact_phone'] ?? '') ?>">
            </div>
        </div>

        <hr>
        <h5 class="mb-3">Ubicación y Notas</h5>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="address" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="address" name="address"
                       value="<?= htmlspecialchars($empresa['address'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label for="city" class="form-label">Ciudad</label>
                <input type="text" class="form-control" id="city" name="city"
                       value="<?= htmlspecialchars($empresa['city'] ?? '') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notas Adicionales</label>
            <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($empresa['notes'] ?? '') ?></textarea>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="active" name="active"
                   <?= ($empresa['active'] ?? 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="active">Empresa activa</label>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save"></i> Guardar cambios
            </button>
            <a href="index.php?route=empresas/list" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
