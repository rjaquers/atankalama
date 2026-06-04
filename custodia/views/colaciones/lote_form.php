
<?php include __DIR__ . '/../../includes/header.php'; ?>
<?php if (!empty($excelCount)): ?>
    <div class="alert alert-info" style="margin-top:8px">
        Se detectaron <strong><?= (int)$excelCount ?></strong> filas desde Excel.
        <label style="margin-left:10px">
            <input type="checkbox" name="usar_excel" value="1" checked>
            Usar estos datos para asignar huésped a cada voucher
        </label>
    </div>
<?php endif; ?>

<h3>Nuevo Lote de Colaciones</h3>
<form method='post' action='/custodia/colaciones/lotes/guardar' class='row g-3'>
    <div class='col-md-6'>
        <label class='form-label'>Empresa</label>
        <select name='empresa_id' class='form-select' required>
            <option value=''>Seleccione...</option>
            <?php foreach ($empresas as $e): ?>
                <option value="<?=(int)$e['id']?>"><?=htmlspecialchars($e['nombre'])?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Fecha servicio</label>
        <input type="date" name="fecha_servicio" value="<?=date('Y-m-d')?>" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Tipo</label>
        <select name="servicio_tipo_id" class="form-select" required>
            <?php foreach ($tipos as $t): ?>
                <option value="<?=(int)$t['id']?>"><?=htmlspecialchars($t['nombre'])?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Cantidad de Vouchers</label>
        <input type="number" name="cantidad" min="1" class="form-control" required value="1">
        <?php if (!empty($excelCount)): ?>
            <div class="alert alert-info" style="margin-top:8px">
                Se detectaron <strong><?= (int)$excelCount ?></strong> filas desde Excel.
                <label style="margin-left:10px">
                    <input type="checkbox" name="usar_excel" value="1" checked>
                    Usar estos datos para asignar huésped a cada voucher
                </label>
            </div>
        <?php endif; ?>


    </div>
    <div class='col-md-3'>
        <label class='form-label '><strong>Hotel</strong></label><br>

        <input class='form-check-input' type='radio' name='hotel' value="Atan" checked="checked">Atankalama<br>
        <input class='form-check-input' type='radio' name='hotel' value="AtanInn">Atankalama Inn
    </div>
    <div class="col-md-6">
        <label class="form-label">Servicio</label><br>
        <?php foreach ($adds as $a): ?>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="adicionales[]" value="<?=(int)$a['id']?>" id="add<?=(int)$a['id']?>">
                <label class="form-check-label" for="add<?=(int)$a['id']?>"><?=htmlspecialchars($a['nombre'])?></label>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="col-12">
        <label class="form-label">Observaciones (opcional)</label>
        <input type="text" name="observaciones" class="form-control" maxlength="255">
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary">Guardar y Generar Vouchers</button>
    </div>
    <input type='hidden' name='from_upload_id' value="<?=(int)($_GET['from_upload_id'] ?? 0)?>">
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
