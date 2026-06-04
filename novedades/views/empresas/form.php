<?php
include __DIR__ . '/../layout.php'; ?>

<div class="container mt-4">
    <h3><i class="bi bi-plus-circle"></i> Nueva Empresa</h3>

    <form method="POST" action="index.php?route=empresas/store" class="card p-4 mt-3">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="business_name" class="form-label">Razón Social *</label>
                <input type="text" class="form-control" id="business_name" name="business_name" placeholder="Ej: Compañía Minera Z, S.A." required>
            </div>
            <div class="col-md-6">
                <label for="trade_name" class="form-label">Nombre de Fantasía</label>
                <input type="text" class="form-control" id="trade_name" name="trade_name" placeholder="Ej: Minera Z">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="rut" class="form-label">RUT</label>
                <input type="text" class="form-control" id="rut" name="rut" placeholder="Ej: 76.123.456-7">
            </div>
            <div class="col-md-4">
                <label for="type" class="form-label">Tipo *</label>
                <select class="form-select" id="type" name="type" required>
                    <option value="cliente">Cliente</option>
                    <option value="proveedor">Proveedor</option>
                </select>
            </div>
            <div class="col-md-4"></div>
        </div>

        <hr>
        <h5 class="mb-3">Información de Contacto</h5>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="contact_name" class="form-label">Nombre de Contacto</label>
                <input type="text" class="form-control" id="contact_name" name="contact_name" placeholder="Ej: Juan Pérez">
            </div>
            <div class="col-md-4">
                <label for="contact_email" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="contact_email" name="contact_email" placeholder="Ej: contacto@empresa.cl">
            </div>
            <div class="col-md-4">
                <label for="contact_phone" class="form-label">Teléfono</label>
                <input type="text" class="form-control" id="contact_phone" name="contact_phone" placeholder="Ej: +56 9 8765 4321">
            </div>
        </div>

        <hr>
        <h5 class="mb-3">Ubicación y Notas</h5>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="address" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="address" name="address" placeholder="Ej: Av. Principal 123">
            </div>
            <div class="col-md-6">
                <label for="city" class="form-label">Ciudad</label>
                <input type="text" class="form-control" id="city" name="city" placeholder="Ej: Santiago">
            </div>
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notas Adicionales</label>
            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Información adicional (opcional)"></textarea>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check2"></i> Guardar
            </button>
            <a href="index.php?route=empresas/list" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
