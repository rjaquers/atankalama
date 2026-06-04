<?php
// views/empresas/nuevo.php
declare(strict_types=1);
if (!function_exists('h')) {
    function h(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

include __DIR__ . '/../../includes/header.php';

$BASE_URL   = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '/custodia';
$guardarUrl = function_exists('url') ? url('/empresas/guardar') : ($BASE_URL . '/empresas/guardar');
$listarUrl  = function_exists('url') ? url('/empresas/listar')  : ($BASE_URL . '/empresas/listar');

$f        = $flash['form'] ?? [];
$errorMsg = $flash['error'] ?? '';
?>

<div class="container mt-4" style="max-width:760px">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Nueva empresa</h5>
        <a href="<?= h($listarUrl) ?>" class="btn btn-outline-secondary btn-sm">Ver listado</a>
    </div>

    <?php if ($errorMsg): ?>
        <div class="alert alert-danger py-2"><?= h($errorMsg) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= h($guardarUrl) ?>">

        <div class="card mb-3">
            <div class="card-header fw-semibold">Datos principales</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Razón social <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="business_name" maxlength="200" required
                               placeholder="Ej: Constructora Besalco S.A."
                               value="<?= h($f['business_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">RUT</label>
                        <input type="text" class="form-control" name="rut" maxlength="20"
                               placeholder="Ej: 76.123.456-7"
                               value="<?= h($f['rut'] ?? '') ?>">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Nombre de fantasía</label>
                        <input type="text" class="form-control" name="trade_name" maxlength="200"
                               placeholder="Ej: Besalco"
                               value="<?= h($f['trade_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="type">
                            <option value="cliente" <?= ($f['type'] ?? 'cliente') === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                            <option value="proveedor" <?= ($f['type'] ?? '') === 'proveedor' ? 'selected' : '' ?>>Proveedor</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header fw-semibold">Contacto</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre de contacto</label>
                        <input type="text" class="form-control" name="contact_name" maxlength="150"
                               placeholder="Ej: Juan Pérez"
                               value="<?= h($f['contact_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email de contacto</label>
                        <input type="email" class="form-control" name="contact_email" maxlength="200"
                               placeholder="Ej: juan@empresa.cl"
                               value="<?= h($f['contact_email'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" name="contact_phone" maxlength="50"
                               placeholder="Ej: +56 9 1234 5678"
                               value="<?= h($f['contact_phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" name="address" maxlength="255"
                               placeholder="Ej: Av. Providencia 123"
                               value="<?= h($f['address'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ciudad</label>
                        <input type="text" class="form-control" name="city" maxlength="100"
                               placeholder="Ej: Santiago"
                               value="<?= h($f['city'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header fw-semibold">Notas / Observaciones</div>
            <div class="card-body">
                <textarea class="form-control" name="notes" rows="3"
                          placeholder="Información adicional relevante..."><?= h($f['notes'] ?? '') ?></textarea>
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="active" name="active" value="1"
                           <?= (($f['active'] ?? 1) ? 'checked' : '') ?>>
                    <label class="form-check-label" for="active">Empresa activa</label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary">Guardar empresa</button>
            <a href="<?= h($listarUrl) ?>" class="btn btn-outline-secondary">Cancelar</a>
        </div>

    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmU6d5sVnb/PZDsX4BSAZ5CFKH3"
        crossorigin="anonymous"></script>
