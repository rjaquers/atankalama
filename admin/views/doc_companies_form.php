<?php include 'layout.php'; ?>

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php?route=doc_companies/list">Empresas</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= $company ? 'Editar Empresa' : 'Nueva Empresa' ?></li>
                </ol>
            </nav>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white d-flex align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-building me-2"></i>
                        <?= $company ? 'Editar Empresa: ' . htmlspecialchars($company['business_name']) : 'Nueva Empresa' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form action="index.php?route=doc_companies/<?= $company ? 'update' : 'store' ?>" method="POST">
                        <?php if ($company): ?>
                            <input type="hidden" name="id" value="<?= $company['id'] ?>">
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="business_name" class="form-label fw-semibold">Razón Social <span class="text-danger">*</span></label>
                                <input type="text" name="business_name" id="business_name" class="form-control" 
                                       value="<?= htmlspecialchars($company['business_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="rut" class="form-label fw-semibold">RUT</label>
                                <input type="text" name="rut" id="rut" class="form-control" 
                                       value="<?= htmlspecialchars($company['rut'] ?? '') ?>" placeholder="Ej: 12.345.678-9">
                            </div>

                            <div class="col-md-6">
                                <label for="trade_name" class="form-label fw-semibold">Nombre de Fantasía</label>
                                <input type="text" name="trade_name" id="trade_name" class="form-control" 
                                       value="<?= htmlspecialchars($company['trade_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="type" class="form-label fw-semibold">Tipo</label>
                                <select name="type" id="type" class="form-select">
                                    <option value="cliente" <?= ($company['type'] ?? '') === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                                    <option value="proveedor" <?= ($company['type'] ?? '') === 'proveedor' ? 'selected' : '' ?>>Proveedor</option>
                                </select>
                            </div>

                            <hr class="my-3">

                            <div class="col-md-6">
                                <label for="contact_name" class="form-label fw-semibold">Nombre de Contacto</label>
                                <input type="text" name="contact_name" id="contact_name" class="form-control" 
                                       value="<?= htmlspecialchars($company['contact_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="contact_email" class="form-label fw-semibold">Correo Electrónico</label>
                                <input type="email" name="contact_email" id="contact_email" class="form-control" 
                                       value="<?= htmlspecialchars($company['contact_email'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="contact_phone" class="form-label fw-semibold">Teléfono de Contacto</label>
                                <input type="text" name="contact_phone" id="contact_phone" class="form-control" 
                                       value="<?= htmlspecialchars($company['contact_phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label fw-semibold">Ciudad</label>
                                <input type="text" name="city" id="city" class="form-control" 
                                       value="<?= htmlspecialchars($company['city'] ?? '') ?>">
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label fw-semibold">Dirección</label>
                                <input type="text" name="address" id="address" class="form-control" 
                                       value="<?= htmlspecialchars($company['address'] ?? '') ?>">
                            </div>

                            <div class="col-12">
                                <label for="notes" class="form-label fw-semibold">Notas / Observaciones</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3"><?= htmlspecialchars($company['notes'] ?? '') ?></textarea>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="active" id="active" 
                                           <?= !isset($company) || $company['active'] ? 'checked' : '' ?>>
                                    <label class="form-check-input" for="active">Empresa Activa</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> <?= $company ? 'Actualizar Empresa' : 'Guardar Empresa' ?>
                            </button>
                            <a href="index.php?route=doc_companies/list" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../helpers/cierre.php'; ?>
