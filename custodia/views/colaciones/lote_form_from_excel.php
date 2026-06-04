<?php include __DIR__.'/../../includes/inc_proyect.php'; ?>
/**
 * Resumen de la página:
 * - Crea Lote de Colaciones desde una carga Excel.
 * - Añade "fecha_inicio" (libre) y "fecha_fin" (solo si viene desde Excel, y >= fecha_inicio).
 * - Ambas fechas arrancan pre-llenadas con la fecha actual.
 * - Mantiene el resto de campos (empresa, tipo, hotel, adicionales, observaciones).
 */

$excelCount     = isset($excelCount) ? (int)$excelCount : (int)($_GET['excelCount'] ?? 0);
$from_upload_id = isset($from_upload_id) ? (int)$from_upload_id : (int)($_GET['from_upload_id'] ?? 0);
$canSubmit      = ($excelCount > 0 && $from_upload_id > 0);

$today = date('Y-m-d');
$showFechaFin = ($from_upload_id > 0);
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<h3>Nuevo Lote de Colaciones (desde Excel)</h3>

<?php if (!$canSubmit): ?>
    <div class="alert alert-warning" style="margin-top:8px">
        <strong>Faltan datos de la carga.</strong><br>
        Debes llegar a esta pantalla desde la confirmación del Excel.
        <div style="margin-top:6px">
            • <code>from_upload_id</code>: <?= (int)$from_upload_id ?> <br>
            • <code>excelCount</code>: <?= (int)$excelCount ?>
        </div>
        <div style="margin-top:8px">
            <a class="btn btn-secondary" href="/custodia/colaciones/excel/import">Volver a importar</a>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info" style="margin-top:8px">
        Se detectaron <strong><?= (int)$excelCount ?></strong> huéspedes desde Excel.
        Cada voucher se generará con <em>rut, nombre y habitación</em> según el archivo.
    </div>
<?php endif; ?>

<form method="post" action="/custodia/colaciones/lotes/guardar" class="row g-3">

    <!-- Empresa -->
    <div class="col-md-6">
        <label class="form-label">Empresa</label>
        <select name="empresa_id" class="form-select" required>
            <option value="">Seleccione...</option>
            <?php foreach ($empresas as $e): ?>
                <option value="<?= (int)$e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Rango de fechas -->
    <div class="col-md-3">
        <label class="form-label">Fecha inicio</label>
        <input
                type="date"
                name="fecha_inicio"
                id="fecha_inicio"
                value="<?= $today ?>"
                class="form-control"
                required
        >
        <small class="text-muted">Puede ser cualquier fecha (pasada o futura).</small>
    </div>

    <?php if ($showFechaFin): ?>
        <div class="col-md-3">
            <label class="form-label">Fecha fin</label>
            <input
                    type="date"
                    name="fecha_fin_servicio"
                    id="fecha_fin_servicio"
                    value="<?= $today ?>"
                    class="form-control"
                    required
            >
            <small class="text-muted">Debe ser igual o posterior a la fecha inicio.</small>
        </div>
    <?php else: ?>
        <!-- Si NO viene desde Excel, no se muestra el campo y se iguala a inicio -->
        <input type="hidden" name="fecha_fin" id="fecha_fin" value="<?= $today ?>">
    <?php endif; ?>

    <!-- Tipo de servicio -->
    <div class="col-md-3">
        <label class="form-label">Tipo</label>


        <?php
        // Normaliza lo enviado previamente (soporta tanto servicios[] como el campo antiguo servicio_tipo_id)
        $serviciosSeleccionados = [];
        if (!empty($_POST['servicios']) && is_array($_POST['servicios'])) {
            $serviciosSeleccionados = array_map('intval', $_POST['servicios']);
        } elseif (!empty($_POST['servicio_tipo_id'])) {
            $serviciosSeleccionados = [(int)$_POST['servicio_tipo_id']];
        }
        ?>

        <div class="col-md-12">
            <label class="form-label">Tipo(s) de servicio</label>
            <div id="servicios-wrapper">
                <?php foreach ($tipos as $t):
                    $id = (int)$t['id'];
                    $checked = in_array($id, $serviciosSeleccionados, true) ? 'checked' : '';
                    ?>
                    <div class="form-check form-check-inline">
                        <input
                                class="form-check-input"
                                type="checkbox"
                                name="servicios[]"
                                id="srv<?= $id ?>"
                                value="<?= $id ?>"
                                <?= $checked ?>
                                required
                        >
                        <label class="form-check-label" for="srv<?= $id ?>">
                            <?= htmlspecialchars($t['nombre']) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="invalid-feedback" style="display:none" id="servicios-error">
                Debes seleccionar al menos un servicio.
            </div>
        </div>

        <script>
            (function () {
                // HTML5 'required' en checkboxes de un mismo name no siempre garantiza "al menos uno".
                // Forzamos validación: si ninguno marcado, bloquea el submit.
                var form = document.currentScript.closest('form');
                var wrapper = document.getElementById('servicios-wrapper');
                var errorEl = document.getElementById('servicios-error');

                function validarServicios() {
                    var checks = wrapper.querySelectorAll('input[type="checkbox"][name="servicios[]"]');
                    var alguno = Array.prototype.some.call(checks, function (c) { return c.checked; });
                    errorEl.style.display = alguno ? 'none' : 'block';
                    // Marca/desmarca 'required' dinámicamente para que el navegador ayude
                    checks.forEach(function (c) { c.required = !alguno; });
                    return alguno;
                }

                if (form && wrapper) {
                    form.addEventListener('submit', function (e) {
                        if (!validarServicios()) {
                            e.preventDefault();
                            e.stopPropagation();
                        }
                    });
                    wrapper.addEventListener('change', validarServicios);
                    // Inicial
                    validarServicios();
                }
            })();
        </script>

    </div>

    <!-- Hotel -->
    <div class="col-md-3">
        <label class="form-label"><strong>Hotel</strong></label><br>
        <label class="form-check-label">
            <input class="form-check-input" type="radio" name="hotel" value="Atan" checked> Atankalama
        </label><br>
        <label class="form-check-label">
            <input class="form-check-input" type="radio" name="hotel" value="AtanInn"> Atankalama Inn
        </label>
    </div>

    <!-- Adicionales -->
    <div class="col-md-9">
        <label class="form-label">Adicionales</label><br>
        <?php foreach ($adds as $a): ?>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox"
                       name="adicionales[]" value="<?= (int)$a['id'] ?>"
                       id="add<?= (int)$a['id'] ?>">
                <label class="form-check-label" for="add<?= (int)$a['id'] ?>">
                    <?= htmlspecialchars($a['nombre']) ?>
                </label>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Observaciones -->
    <div class="col-12">
        <label class="form-label">Observaciones (opcional)</label>
        <input type="text" name="observaciones" class="form-control" maxlength="255">
    </div>

    <!-- Metadatos de la carga Excel -->
    <input type="hidden" name="from_upload_id" value="<?= (int)$from_upload_id ?>">
    <input type="hidden" name="cantidad" value="<?= (int)$excelCount ?>">
    <input type="hidden" name="usar_excel" value="1">

    <div class="col-12">
        <button type="submit" class="btn btn-primary" <?= $canSubmit ? '' : 'disabled' ?>>
            Guardar y Generar Vouchers
        </button>
        <a href="/custodia/colaciones/excel/preview" class="btn btn-secondary">Volver</a>
    </div>
    <input type="hidden" name="excel" value="1">
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>


<script>
    (function () {
        const ini = document.getElementById('fecha_inicio');
        const fin = document.getElementById('fecha_fin'); // puede no existir si no viene desde Excel
        if (!ini) return;

        // Regla: fecha_fin >= fecha_inicio; ambas arrancan en hoy
        function syncFinMin() {
            if (!fin) return;
            // No restringimos inicio (puede ser pasada o futura).
            // Solo ajustamos el mínimo de fin al valor seleccionado en inicio.
            fin.min = ini.value || '<?= $today ?>';
            if (fin.value < fin.min) {
                fin.value = fin.min;
            }
        }

        ini.addEventListener('change', syncFinMin);
        // Inicializa al cargar
        syncFinMin();
    })();
</script>



