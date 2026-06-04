<!DOCTYPE html>
<html lang='es'>

<head>
    <?php 
    $pageTitle = "Recepción Particular";
    include(ROOT_PATH . '../public/static/templates/head.php'); 
    ?>
</head>

<body class='pro-body'>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

    <div class='container py-4'>

        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom"
            style="border-color: var(--color-border) !important;">
            <h2 class="mb-0 fw-bold">
                <i class="bi bi-person-fill me-2" style="color: var(--color-cta)"></i>Nueva Orden — Particular / Huésped
            </h2>
            <a href="index.php?page=recepcion/listado" class="btn btn-pro-action px-3" style="width: auto;">
                <i class="bi bi-list me-1"></i>Ver Listado de Órdenes
            </a>
        </div>

        <form method='POST' action='index.php?page=recepcion/crear' id='ordenForm'>
            <input type="hidden" name="tipo_solicitante" value="particular">

            <!-- SECCIÓN 1: Información General -->
            <div class="pro-card border-0 mb-4">
                <div class="card-header bg-transparent py-3 px-4" style="border-bottom: 1px solid var(--color-border);">
                    <h5 class="fw-bold mb-0" style="color: var(--color-primary);">
                        <i class="bi bi-info-circle me-2" style="color: var(--color-cta)"></i>Información General
                    </h5>
                </div>
                <div class="card-body px-4 pb-4 pt-4">
                    <div class="row g-4">
                        <div class='col-md-3 col-sm-6'>
                            <label class='form-label text-muted fw-bold d-flex align-items-center'>
                                <i class="bi bi-door-open fs-5 me-2 text-primary"></i>Nro. habitación
                                <span class="text-danger ms-1">*</span>
                            </label>
                            <input type='text' class='form-control form-control-lg border-0 shadow-none'
                                name='habitacion' required oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                onkeypress='return event.charCode >= 48 && event.charCode <= 57' placeholder="Ej: 101">
                        </div>
                        <div class='col-md-3 col-sm-6'>
                            <label class='form-label text-muted fw-bold d-flex align-items-center'>
                                <i class="bi bi-geo-alt fs-5 me-2 text-primary"></i>Lugar de entrega
                                <span class="text-danger ms-1">*</span>
                            </label>
                            <div class="btn-group w-100 shadow-sm" role="group">
                                <input type="radio" class="btn-check" name="lugar" id="lugar_habitacion"
                                    autocomplete="off" value="Habitacion" required>
                                <label class="btn btn-outline-primary" for="lugar_habitacion">Habitación</label>
                                <input type="radio" class="btn-check" name="lugar" id="lugar_comedor"
                                    autocomplete="off" value="Comedor" required checked>
                                <label class="btn btn-outline-primary" for="lugar_comedor">Comedor</label>
                            </div>
                        </div>
                        <div class='col-md-3 col-sm-6'>
                            <label class='form-label text-muted fw-bold d-flex align-items-center'>
                                <i class="bi bi-person fs-5 me-2 text-primary"></i>Solicitado por
                            </label>
                            <input type='text' class='form-control form-control-lg border-0 shadow-none'
                                name='nombre_huesped' placeholder='Nombre huésped o recepción'>
                            <div class="form-text mt-1 text-muted">
                                <i class="bi bi-info-circle me-1"></i>Opcional, pero recomendado.
                            </div>
                        </div>
                        <div class='col-md-3 col-sm-6'>
                            <label class='form-label text-muted fw-bold d-flex align-items-center'>
                                <i class="bi bi-people fs-5 me-2 text-primary"></i>Cantidad personas
                                <span class="text-danger ms-1">*</span>
                            </label>
                            <input type='number' class='form-control form-control-lg border-0 shadow-none'
                                name='cantidad_personas' id='cantidad_personas' value='1' min='1' required>
                        </div>
                        <div class="col-12 mt-2">
                            <label class="form-label text-muted fw-bold d-flex align-items-center">
                                <i class="bi bi-chat-left-text fs-5 me-2 text-primary"></i>Observaciones de la solicitud
                                <span class="text-muted fw-normal small ms-2">(opcional)</span>
                            </label>
                            <textarea class="form-control form-control-lg border-0 shadow-none"
                                name="observaciones" id="inputObservaciones" rows="1"
                                placeholder="Notas o requerimientos adicionales"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: Productos -->
            <div class="pro-card border-0 mb-4">
                <div class="card-header bg-transparent py-3 px-4 d-flex align-items-center"
                    style="border-bottom: 1px solid var(--color-border);">
                    <h5 class="fw-bold mb-0" style="color: var(--color-primary);">
                        <i class="bi bi-box-seam me-2" style="color: var(--color-cta)"></i>Productos a Pedir
                    </h5>
                    <span class="badge ms-3"
                        style="background-color: var(--color-background); color: var(--color-text-muted); border: 1px solid var(--color-border); font-weight: normal;">
                        I.V.A. incluido
                    </span>
                </div>
                <div class="card-body px-4 pb-4 pt-4">
                    <div class="row g-2">
                        <?php foreach ($productosLista as $p): ?>
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="p-2 border rounded-3 bg-light bg-opacity-50 h-100 d-flex align-items-center producto-item" 
                                     data-precio="<?= $p['precio'] ?>" style="transition: all 0.2s ease; min-height: 60px;">
                                    <div class="flex-grow-1 overflow-hidden me-2">
                                        <h6 class="fw-bold mb-0 text-truncate small" title="<?= htmlspecialchars($p['nombre']) ?>" style="font-size: 0.8rem; line-height: 1.1;">
                                            <?= htmlspecialchars($p['nombre']) ?>
                                        </h6>
                                        <span class="text-primary fw-bold small" style="font-size: 0.75rem;">$<?= number_format($p['precio'], 0, ',', '.') ?></span>
                                    </div>
                                    
                                    <!-- Input oculto para el precio -->
                                    <input type="hidden" name="productos[<?= $p['nombre'] ?>][precio]" value="<?= $p['precio'] ?>">
                                    
                                    <div class="d-flex align-items-center justify-content-between bg-white rounded-pill p-0 border shadow-sm" style="width: 90px; height: 26px; flex-shrink: 0;">
                                        <button type="button" class="btn btn-link text-decoration-none p-0 btn-qty d-flex align-items-center justify-content-center" data-action="minus" style="width: 26px; height: 26px;">
                                            <i class="bi bi-dash-circle-fill text-muted" style="font-size: 1rem;"></i>
                                        </button>
                                        <input type="number" 
                                               name="productos[<?= $p['nombre'] ?>][cantidad]" 
                                               class="form-control border-0 text-center fw-bold bg-transparent input-qty p-0 small" 
                                               value="0" min="0" style="width: 25px; box-shadow: none !important; font-size: 0.8rem;">
                                        <button type="button" class="btn btn-link text-decoration-none p-0 btn-qty d-flex align-items-center justify-content-center" data-action="plus" style="width: 26px; height: 26px;">
                                            <i class="bi bi-plus-circle-fill text-primary" style="font-size: 1rem;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Otro Producto -->
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="p-2 border rounded-3 bg-warning bg-opacity-10 h-100 d-flex align-items-center justify-content-center text-center producto-item" 
                                 id="btnMostrarOtro" style="cursor: pointer; border-style: dashed !important; min-height: 60px;">
                                <i class="bi bi-plus-circle fs-5 text-warning me-2"></i>
                                <span class="fw-bold text-warning-emphasis small" style="font-size: 0.75rem;">Pedido Especial</span>
                            </div>
                        </div>
                    </div>

                    <div class='row g-3 mt-4 p-4 rounded-4 border border-warning border-opacity-50 shadow-sm' id="div_otros"
                        style="display: none; background-color: var(--color-surface);">
                        <div class="col-12 mb-2 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-warning-emphasis mb-0">Detalle de Pedido Especial</h6>
                            <button type="button" class="btn-close btn-sm" id="btnCerrarOtro"></button>
                        </div>
                        <div class='col-md-8'>
                            <label class='form-label text-muted small fw-bold text-uppercase tracking-wider'>
                                <i class="bi bi-card-text me-2"></i>Descripción detallada
                            </label>
                            <textarea class='form-control border-warning border-opacity-25 shadow-none' id='otros_desc'
                                name='otros_desc' rows='2'
                                placeholder="Escribe aquí el pedido especial..."
                                style="resize: none;"></textarea>
                        </div>
                        <div class='col-md-4'>
                            <label class='form-label text-muted small fw-bold text-uppercase tracking-wider'>
                                <i class="bi bi-tag me-2"></i>Precio Unitario ($)
                            </label>
                            <input type='number' step='1'
                                class='form-control form-control-lg border-warning border-opacity-25 shadow-none font-monospace'
                                id='otros_precio' name='otros_precio' placeholder="0">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 3: Pago y Horario -->
            <div class="pro-card border-0 mb-4">
                <div class="card-body p-4 pt-4">
                    <div class="row g-4 align-items-center">

                        <!-- Pago -->
                        <div class='col-md-5'>
                            <div class="d-flex align-items-center bg-transparent p-3 rounded-4 h-100 border"
                                style="border-color: var(--color-border) !important;">
                                <div class="form-check form-switch form-check-reverse fs-5 w-100 mb-0 d-flex justify-content-between align-items-center">
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

                        <!-- Voucher -->
                        <div class='col-md-7'>
                            <div class="bg-transparent p-3 rounded-4 border"
                                id="containerVoucher"
                                style="border-color: var(--color-border) !important; transition: opacity 0.3s ease;">
                                <label class='form-label text-muted fw-bold d-flex align-items-center mb-2'>
                                    <i class="bi bi-receipt-cutoff fs-5 me-2" style="color: var(--color-cta)"></i>
                                    Nro. Voucher / Boleta de garantía
                                </label>
                                <input type='text' class='form-control form-control-lg border-0 shadow-none'
                                    id='boucher' name='boucher'
                                    placeholder="Introduce el nro. comprobante"
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
                                            <div class="fs-6 fw-normal text-muted mt-1">
                                                Si no lo activas, la orden entrará
                                                <strong style="color: var(--color-cta)">inmediatamente</strong>
                                                a la cola de cocina.
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

            <!-- FOOTER: Total y Enviar -->
            <div class="pro-card border-0 mb-5 pb-0 rounded-4 overflow-hidden shadow">
                <div class="card-body p-0 d-flex justify-content-between align-items-stretch flex-wrap">
                    <div class="d-flex align-items-center p-4 bg-transparent flex-grow-1 border-end"
                        style="border-color: var(--color-border) !important;">
                        <div class="rounded-circle p-3 me-4 d-flex align-items-center justify-content-center shadow-sm"
                            style="width: 70px; height: 70px; background-color: rgba(59, 130, 246, 0.2); color: #3b82f6;">
                            <i class="bi bi-cart-check fs-1"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase fw-bold small d-block tracking-wide">Total de la Orden</span>
                            <div class="display-5 fw-bold lh-1 mt-1" style="color: var(--color-primary);">
                                $<output id="total">0</output>
                            </div>
                        </div>
                    <button type="submit" id="btnEnviar"
                        class="btn btn-pro-primary px-5 py-4 fw-bold text-white d-flex align-items-center justify-content-center flex-grow-1 flex-md-grow-0 fs-4"
                        style="border-radius: 0; min-width: 300px;">
                        <i class="bi bi-send-fill me-3 fs-3"></i>ENVIAR A COCINA
                    </button>
                    </div>
                    </div>

                    </form>
                    </div>

                    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>

                    <script>
                    // Control de envío y feedback
                    document.getElementById('ordenForm').addEventListener('submit', function() {
                    const btn = document.getElementById('btnEnviar');
                    btn.disabled = true;
                    btn.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    PROCESANDO ENVÍO...
                    `;
                    });
                    </script>

    <!-- Modal de selección de hora -->
    <div class='modal fade' id='modalHoraEntrega' tabindex='-1' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content border-primary'>
                <div class='modal-header bg-primary text-white'>
                    <h5 class='modal-title'>Seleccionar hora de entrega</h5>
                    <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'></button>
                </div>
                <div class='modal-body'>
                    <label for='horaEntregaInput' class='form-label'>Hora deseada (formato 24 hrs):</label>
                    <input type='time' id='horaEntregaInput' class='form-control' step='60'>
                    <div id='horaPreview' class='mt-2 text-muted small'>
                        Entrega programada: <span id='horaTexto'>--:-- hrs</span>
                    </div>
                    <small class='form-text text-muted'>La hora sugerida es actual + 20 minutos.</small>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancelar</button>
                    <button type='button' class='btn btn-primary' id='confirmarHoraEntrega'>Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ── Cálculo de total dinámico ─────────────────────────────────────────
        function calcularTotal() {
            let totalGeneral = 0;
            
            // Productos del catálogo
            document.querySelectorAll('.producto-item').forEach(item => {
                const precio = parseFloat(item.dataset.precio);
                if (isNaN(precio)) return; // Saltar si no tiene precio (como el botón de pedido especial)

                const qtyInput = item.querySelector('.input-qty');
                if (!qtyInput) return;

                const cantidad = parseInt(qtyInput.value) || 0;
                
                if (cantidad > 0) {
                    totalGeneral += (precio * cantidad);
                    item.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
                    item.classList.remove('bg-light');
                } else {
                    item.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
                    item.classList.add('bg-light');
                }
            });

            // Pedido especial
            const otrosDesc = document.getElementById('otros_desc').value.trim();
            const otrosPrecio = parseFloat(document.getElementById('otros_precio').value) || 0;
            if (otrosDesc && otrosPrecio > 0) {
                totalGeneral += otrosPrecio;
            }

            document.getElementById('total').value = totalGeneral.toLocaleString('es-CL');
        }

        // ── Gestión de Cantidades (+/-) ───────────────────────────────────────
        document.querySelectorAll('.btn-qty').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const action = this.dataset.action;
                const input = this.closest('div').querySelector('.input-qty');
                let val = parseInt(input.value) || 0;
                
                if (action === 'plus') val++;
                else if (action === 'minus' && val > 0) val--;
                
                input.value = val;
                calcularTotal();
            });
        });

        document.querySelectorAll('.input-qty, #otros_desc, #otros_precio')
            .forEach(el => el.addEventListener('input', calcularTotal));

        // ── Gestión de Pedido Especial ───────────────────────────────────────
        const btnMostrarOtro = document.getElementById('btnMostrarOtro');
        const btnCerrarOtro  = document.getElementById('btnCerrarOtro');
        const divOtros       = document.getElementById('div_otros');
        const otrosDesc      = document.getElementById('otros_desc');
        const otrosPrecio    = document.getElementById('otros_precio');

        btnMostrarOtro.addEventListener('click', () => {
            divOtros.style.display = 'flex';
            btnMostrarOtro.style.display = 'none';
            otrosDesc.focus();
        });

        btnCerrarOtro.addEventListener('click', () => {
            divOtros.style.display = 'none';
            btnMostrarOtro.style.display = 'flex';
            otrosDesc.value = '';
            otrosPrecio.value = '';
            calcularTotal();
        });

        // ── Voucher requerido si pagado ──────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            const pagadoCheckbox = document.getElementById('pagado');
            const containerVoucher = document.getElementById('containerVoucher');
            const boucherInput   = document.getElementById('boucher');
            
            function toggleBoucher() {
                if (pagadoCheckbox.checked) {
                    containerVoucher.style.opacity = '1';
                    boucherInput.setAttribute('required', 'required');
                    boucherInput.classList.add('border-primary');
                } else {
                    containerVoucher.style.opacity = '0.7';
                    boucherInput.removeAttribute('required');
                    boucherInput.classList.remove('border-primary');
                }
            }
            toggleBoucher();
            pagadoCheckbox.addEventListener('change', toggleBoucher);
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
                    const horas      = String(ahora.getHours()).padStart(2, '0');
                    const minutos    = String(ahora.getMinutes()).padStart(2, '0');
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
                    const hoy = new Date();
                    const año = hoy.getFullYear();
                    const mes = String(hoy.getMonth() + 1).padStart(2, '0');
                    const dia = String(hoy.getDate()).padStart(2, '0');
                    const [h, m] = horaSeleccionada.split(':');
                    inputOcultoHora.value = `${año}-${mes}-${dia} ${h}:${m}:00`;
                }
                bootstrap.Modal.getInstance(document.getElementById('modalHoraEntrega')).hide();
            });
        });
    </script>

    <style>
        .input-qty::-webkit-outer-spin-button,
        .input-qty::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .input-qty {
            -moz-appearance: textfield;
        }
        .producto-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
    </style>
</body>
</html>

</body>
</html>
