<!DOCTYPE html>
<html lang='es'>

<head>
    <?php include(ROOT_PATH . '../public/static/templates/head.php'); ?>
</head>

<body class='pro-body'>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

    <div class='container py-4'>

        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom"
            style="border-color: var(--color-border) !important;">
            <h2 class="mb-0 fw-bold"><i class="bi bi-receipt me-2" style="color: var(--color-cta)"></i>Nueva Solicitud Particular</h2>
            <a href="index.php?page=recepcion/listado" class="btn btn-pro-action px-3" style="width: auto;"><i
                    class="bi bi-list me-1"></i>Ver Listado de Órdenes</a>
        </div>

        <form method='POST' action='index.php?page=recepcion/crear' id='ordenForm' enctype='multipart/form-data'>
            <input type="hidden" name="tipo_solicitante" value="particular">

            <!-- SECCIÓN 1: Información General -->
            <div class="pro-card border-0 mb-4">
                <div class="card-header bg-transparent py-3 px-4" style="border-bottom: 1px solid var(--color-border);">
                    <h5 class="fw-bold mb-0" style="color: var(--color-primary);"><i class="bi bi-info-circle me-2"
                            style="color: var(--color-cta)"></i>Información General</h5>
                </div>
                <div class="card-body px-4 pb-4 pt-4">
                    <div class="row g-4">
                        <div class='col-md-3 col-sm-6'>
                            <label class='form-label text-muted fw-bold d-flex align-items-center'><i
                                    class="bi bi-door-open fs-5 me-2 text-primary"></i>Nro. habitación <span
                                    class="text-danger ms-1">*</span></label>
                            <input type='text' class='form-control form-control-lg border-0 shadow-none'
                                name='habitacion' required oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                onkeypress='return event.charCode >= 48 && event.charCode <= 57' placeholder="Ej: 101">
                        </div>
                        <div class='col-md-3 col-sm-6'>
                            <label class='form-label text-muted fw-bold d-flex align-items-center'><i
                                    class="bi bi-geo-alt fs-5 me-2 text-primary"></i>Lugar de entrega <span
                                    class="text-danger ms-1">*</span></label>
                            <div class="btn-group w-100 shadow-sm" role="group" aria-label="Lugar de entrega">
                                <input type="radio" class="btn-check" name="lugar" id="lugar_habitacion"
                                    autocomplete="off" value="Habitacion" required>
                                <label class="btn btn-outline-primary" for="lugar_habitacion">Habitación</label>

                                <input type="radio" class="btn-check" name="lugar" id="lugar_comedor" autocomplete="off"
                                    value="Comedor" required checked>
                                <label class="btn btn-outline-primary" for="lugar_comedor">Comedor</label>
                            </div>
                        </div>
                        <div class='col-md-3 col-sm-6'>
                            <label class='form-label text-muted fw-bold d-flex align-items-center'><i
                                    class="bi bi-person fs-5 me-2 text-primary"></i>Solicitado por</label>
                            <input type='text' class='form-control form-control-lg border-0 shadow-none'
                                name='nombre_huesped' id='inputNombreHuesped' placeholder='Nombre huésped o recepción'>
                            <div class="form-text mt-1 text-muted"><i class="bi bi-info-circle me-1"></i>Opcional, pero
                                recomendado.</div>
                        </div>
                        <div class='col-md-3 col-sm-6'>
                            <label class='form-label text-muted fw-bold d-flex align-items-center'><i
                                    class="bi bi-people fs-5 me-2 text-primary"></i>Cantidad personas <span
                                    class="text-danger ms-1">*</span></label>
                            <input type='number' class='form-control form-control-lg border-0 shadow-none'
                                name='cantidad_personas' value='1' min='1' required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: Productos -->
            <div class="pro-card border-0 mb-4">
                <div class="card-header bg-transparent py-3 px-4 d-flex align-items-center"
                    style="border-bottom: 1px solid var(--color-border);">
                    <h5 class="fw-bold mb-0" style="color: var(--color-primary);"><i class="bi bi-box-seam me-2"
                            style="color: var(--color-cta)"></i>Productos a Pedir</h5>
                    <span class="badge ms-3"
                        style="background-color: var(--color-background); color: var(--color-text-muted); border: 1px solid var(--color-border); font-weight: normal;">I.V.A.
                        incluido</span>
                </div>
                <div class="card-body px-4 pb-4 pt-4">
                    <div class="row g-3">
                        <?php foreach ($productosLista as $p): ?>
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <input type="checkbox" class="btn-check" name="productos[<?= $p['nombre'] ?>][cantidad]"
                                    value="1" id="prod<?= str_replace(' ', '', $p['nombre']) ?>"
                                    data-precio="<?= $p['precio'] ?>"
                                    autocomplete="off">
                                <input type="hidden" name="productos[<?= $p['nombre'] ?>][precio]" value="<?= $p['precio'] ?>">
                                <label
                                    class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center py-2 px-3 shadow-none border-1"
                                    for="prod<?= str_replace(' ', '', $p['nombre']) ?>"
                                    style="border-radius: 12px; transition: all 0.2s ease;">
                                    <span class="text-truncate me-2 fs-6"
                                        title="<?= htmlspecialchars($p['nombre']) ?>"><?= htmlspecialchars($p['nombre']) ?></span>
                                    <span
                                        class="badge bg-primary rounded-pill fw-normal fs-6 px-3">$<?= number_format($p['precio'], 0, ',', '.') ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>

                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <input type="checkbox" class="btn-check" name="productos[Otro]" value="0" id="prodOtro"
                                autocomplete="off">
                            <label
                                class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center py-2 px-3 shadow-none border-1"
                                for="prodOtro" style="border-radius: 12px; transition: all 0.2s ease;">
                                <span class="fs-6">Otro producto</span>
                                <span class="badge bg-secondary rounded-pill fw-normal fs-6 px-3">$0</span>
                            </label>
                        </div>
                    </div>

                    <!-- Div Otros (Oculto inicialmente) -->
                    <div class='row g-3 mt-3 p-4 rounded-4 border border-warning border-opacity-50' id="div_otros"
                        style="display: none; background-color: var(--color-surface); box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                        <div class='col-md-8'>
                            <label class='form-label text-warning-emphasis fw-bold d-flex align-items-center'><i
                                    class="bi bi-card-text me-2"></i>Descripción detallada del otro producto</label>
                            <textarea class='form-control border-warning border-opacity-50 shadow-none' id='otros_desc'
                                name='otros_desc' rows='2'
                                placeholder="Escribe aquí el pedido especial que no está en la lista..."
                                style="resize: none;"></textarea>
                        </div>
                        <div class='col-md-4'>
                            <label class='form-label text-warning-emphasis fw-bold d-flex align-items-center'><i
                                    class="bi bi-tag me-2"></i>Precio ($)</label>
                            <input type='number' step='0.01'
                                class='form-control form-control-lg border-warning border-opacity-50 shadow-none font-monospace'
                                id='otros_precio' name='otros_precio' placeholder="Ej: 5000">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 3: Detalles de Pago y Horario -->
            <div class="pro-card border-0 mb-4">
                <div class="card-body p-4 pt-4">
                    <div class="row g-4 align-items-center">

                        <!-- Pago -->
                        <div class='col-md-5'>
                            <div class="d-flex align-items-center bg-transparent p-3 rounded-4 h-100 border"
                                style="border-color: var(--color-border) !important;">
                                <div
                                    class="form-check form-switch form-check-reverse fs-5 w-100 mb-0 d-flex justify-content-between align-items-center">
                                    <label class="form-check-label fw-bold d-flex align-items-center" for="pagado"
                                        style="cursor: pointer; color: var(--color-primary);">
                                        <div class="p-2 rounded-circle me-3 d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px; background-color: rgba(16, 185, 129, 0.2); color: #10b981;">
                                            <i class="bi bi-cash-coin"></i>
                                        </div>
                                        ¿Este pedido ya está pagado?
                                    </label>
                                    <input class="form-check-input ms-3 shadow-none" type="checkbox" role="switch"
                                        name="pagado" id="pagado" value="1"
                                        style="width: 3em; height: 1.5em; cursor: pointer;">
                                </div>
                            </div>
                        </div>

                        <!-- Boucher -->
                        <div class='col-md-7'>
                            <div class="bg-transparent p-3 rounded-4 border"
                                style="border-color: var(--color-border) !important;">
                                <label class='form-label text-muted fw-bold d-flex align-items-center mb-2'><i
                                        class="bi bi-receipt-cutoff fs-5 me-2" style="color: var(--color-cta)"></i>Nro.
                                    Voucher / Boleta de
                                    garantía</label>
                                <input type='text' class='form-control form-control-lg border-0 shadow-none'
                                    id='boucher' name='boucher'
                                    placeholder="Introduce el nro. comprobante (Requerido solo si está pagado)"
                                    style="background-color: var(--color-background) !important;">
                            </div>
                        </div>

                        <!-- Horario Diferido -->
                        <div class='col-12 mt-4'>
                            <div class="bg-transparent p-3 px-4 rounded-4 border"
                                style="border-color: var(--color-border) !important;">
                                <div class="form-check form-switch fs-5 mb-0 d-flex align-items-center">
                                    <input class="form-check-input me-3 shadow-none" type="checkbox" role="switch"
                                        id="horarioDiferido" name="usar_horario_diferido"
                                        style="width: 3em; height: 1.5em; cursor: pointer;">
                                    <label class="form-check-label fw-bold d-flex align-items-center"
                                        for="horarioDiferido" style="cursor: pointer; color: var(--color-primary);">
                                        <div class="p-2 rounded-circle me-3 d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px; background-color: rgba(59, 130, 246, 0.2); color: #3b82f6;">
                                            <i class="bi bi-clock-history"></i>
                                        </div>
                                        <div>
                                            ¿Programar entrega para una hora específica?
                                            <div class="fs-6 fw-normal text-muted mt-1" id="estadoProgramacionText">
                                                Si no lo activas, la orden entrará <strong
                                                    style="color: var(--color-cta)">inmediatamente</strong> a la cola de
                                                cocina.
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <input type='hidden' name='hora_entrega' id='hora_entrega'>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- SECCIÓN 4: Observaciones y Adjuntos -->
            <div class="pro-card border-0 mb-4">
                <div class="card-header bg-transparent py-3 px-4" style="border-bottom: 1px solid var(--color-border);">
                    <h5 class="fw-bold mb-0" style="color: var(--color-primary);"><i class="bi bi-chat-left-text me-2"
                            style="color: var(--color-cta)"></i>Observaciones y Adjuntos</h5>
                </div>
                <div class="card-body px-4 pb-4 pt-4">
                    <div class="row g-4">
                        <div class="col-md-8">
                            <label class="form-label text-muted fw-bold d-flex align-items-center mb-2">
                                <i class="bi bi-pencil-square fs-5 me-2 text-primary"></i>Observaciones adicionales
                            </label>
                            <textarea class="form-control border-0 shadow-none" name="observaciones" rows="3" 
                                placeholder="Escribe aquí cualquier detalle extra sobre la orden o el huésped..."
                                style="background-color: var(--color-background) !important; resize: none;"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted fw-bold d-flex align-items-center mb-2">
                                <i class="bi bi-paperclip fs-5 me-2 text-primary"></i>Archivo adjunto (Opcional)
                            </label>
                            <div class="bg-light p-3 rounded-4 border border-dashed text-center" style="border: 2px dashed var(--color-border) !important; background-color: var(--color-background) !important;">
                                <i class="bi bi-cloud-arrow-up fs-2 text-muted mb-2 d-block"></i>
                                <input type="file" class="form-control form-control-sm shadow-none" name="archivo_respaldo" id="archivo_respaldo" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
                                <div class="form-text mt-2 small">Sube capturas de WhatsApp, boletas o comprobantes. (JPG, PNG, PDF)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER: Total y Subir -->
            <div class="pro-card border-0 mb-5 pb-0 rounded-4 overflow-hidden">
                <div class="card-body p-0 d-flex justify-content-between align-items-stretch flex-wrap">
                    <div class="d-flex align-items-center p-4 bg-transparent flex-grow-1 border-end"
                        style="border-color: var(--color-border) !important;">
                        <div class="rounded-circle p-3 me-4 d-flex align-items-center justify-content-center"
                            style="width: 70px; height: 70px; background-color: rgba(59, 130, 246, 0.2); color: #3b82f6;">
                            <i class="bi bi-calculator fs-1"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase fw-bold small d-block tracking-wide">Total Estimado
                                Orden</span>
                            <div class="display-5 fw-bold lh-1 mt-1" style="color: var(--color-primary);">$<output
                                    id="total">0</output></div>
                        </div>
                    </div>
                    <button type="submit"
                        class="btn btn-pro-primary px-5 py-4 fw-bold text-white d-flex align-items-center justify-content-center flex-grow-1 flex-md-grow-0 fs-4"
                        style="border-radius: 0;">
                        <i class="bi bi-send-fill me-3 fs-3"></i>ENVIAR A COCINA
                    </button>
                </div>
            </div>

        </form>
    </div>


    <!--footer-->
    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>
    <!--footer-->



    <!-- Modal de selección de hora -->
    <div class='modal fade' id='modalHoraEntrega' tabindex='-1' aria-labelledby='modalHoraEntregaLabel'
        aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content border-primary'>
                <div class='modal-header bg-primary text-white'>
                    <h5 class='modal-title' id='modalHoraEntregaLabel'>Seleccionar hora de entrega</h5>
                    <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'
                        aria-label='Cerrar'></button>
                </div>
                <div class='modal-body'>
                    <label for='horaEntregaInput' class='form-label'>Hora deseada (formato 24 hrs):</label>
                    <input type='time' id='horaEntregaInput' class='form-control' step='60'>
                    <div id='horaPreview' class='mt-2 text-muted small'>
                        Entrega programada: <span id='horaTexto'>--:-- hrs</span>
                    </div>
                    <small class='form-text text-muted'>La hora sugerida es actual + 20 minutos. Puedes
                        modificarla.</small>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancelar</button>
                    <button type='button' class='btn btn-primary' id='confirmarHoraEntrega'>Confirmar</button>
                </div>
            </div>
        </div>
    </div>


    <!--alerta orden enviada-->
    <?php if (isset($_GET['ok'])): ?>
        <div class="modal fade" id="ordenEnviadaModal" tabindex="-1" role="dialog" aria-labelledby="ordenEnviadaLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-success">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="ordenEnviadaLabel">¡Orden enviada!</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        La orden fue enviada correctamente al sistema de cocina.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var ordenModal = new bootstrap.Modal(document.getElementById('ordenEnviadaModal'));
                ordenModal.show();
                setTimeout(function () { ordenModal.hide(); }, 4000);
            });
        </script>
    <?php endif; ?>


    <script>
        // ── Cálculo de total ─────────────────────────────────────────────────
        function calcularTotal() {
            let total = 0;
            document.querySelectorAll('input[name^="productos"]').forEach(el => {
                if (el.checked && el.type === 'checkbox') {
                    // Si es un producto de la lista
                    if (el.dataset.precio) {
                        total += parseFloat(el.dataset.precio);
                    } else if (el.id === 'prodOtro') {
                        // El precio de "Otro" se suma aparte
                    }
                }
            });
            const otrosDesc  = document.getElementById('otros_desc').value.trim();
            const otrosPrecio = parseFloat(document.getElementById('otros_precio').value);
            if (otrosDesc && !isNaN(otrosPrecio)) total += otrosPrecio;
            document.getElementById('total').value = total.toFixed(2);
        }
        document.querySelectorAll('input[name^="productos"], #otros_desc, #otros_precio').forEach(el => el.addEventListener('input', calcularTotal));

        // ── Boucher requerido si pagado ──────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            const pagadoCheckbox = document.getElementById('pagado');
            const boucherInput   = document.getElementById('boucher');
            function toggleBoucherRequerido() {
                pagadoCheckbox.checked
                    ? boucherInput.setAttribute('required', 'required')
                    : boucherInput.removeAttribute('required');
            }
            toggleBoucherRequerido();
            pagadoCheckbox.addEventListener('change', toggleBoucherRequerido);
        });

        // ── Precio requerido si hay "otro producto" ──────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            const otrosDesc  = document.getElementById('otros_desc');
            const otrosPrecio = document.getElementById('otros_precio');
            function togglePrecioRequerido() {
                otrosDesc.value.trim() !== ''
                    ? otrosPrecio.setAttribute('required', 'required')
                    : otrosPrecio.removeAttribute('required');
            }
            togglePrecioRequerido();
            otrosDesc.addEventListener('input', togglePrecioRequerido);
        });

        // ── Mostrar/ocultar sección "Otro producto" ──────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            const prodOtroCheckbox = document.getElementById('prodOtro');
            const divOtros         = document.getElementById('div_otros');
            function toggleDivOtros() {
                divOtros.style.display = prodOtroCheckbox.checked ? 'flex' : 'none';
            }
            toggleDivOtros();
            prodOtroCheckbox.addEventListener('change', toggleDivOtros);
        });

        // ── Selección de hora de entrega ─────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            const horarioCheckbox  = document.getElementById('horarioDiferido');
            const horaEntregaInput = document.getElementById('horaEntregaInput');
            const inputOcultoHora  = document.getElementById('hora_entrega');
            const horaTexto        = document.getElementById('horaTexto');

            horarioCheckbox.addEventListener('change', function () {
                if (horarioCheckbox.checked) {
                    const ahora = new Date();
                    ahora.setMinutes(ahora.getMinutes() + 20);
                    const horas   = String(ahora.getHours()).padStart(2, '0');
                    const minutos = String(ahora.getMinutes()).padStart(2, '0');
                    const horaSugerida = `${horas}:${minutos}`;
                    horaEntregaInput.value = horaSugerida;
                    horaTexto.textContent  = `${horaSugerida} hrs`;
                    new bootstrap.Modal(document.getElementById('modalHoraEntrega')).show();
                } else {
                    inputOcultoHora.value = '';
                    horaTexto.textContent = '--:-- hrs';
                }
            });

            horaEntregaInput.addEventListener('input', () => {
                horaTexto.textContent = `${horaEntregaInput.value} hrs`;
            });

            document.getElementById('confirmarHoraEntrega').addEventListener('click', function () {
                const horaSeleccionada = horaEntregaInput.value;
                if (horaSeleccionada) {
                    const hoy  = new Date();
                    const año  = hoy.getFullYear();
                    const mes  = String(hoy.getMonth() + 1).padStart(2, '0');
                    const dia  = String(hoy.getDate()).padStart(2, '0');
                    const [h, m] = horaSeleccionada.split(':');
                    inputOcultoHora.value = `${año}-${mes}-${dia} ${h}:${m}:00`;
                }
                bootstrap.Modal.getInstance(document.getElementById('modalHoraEntrega')).hide();
            });
        });
    </script>

</body>

</html>