<?php include __DIR__ . '/../../includes/header2.php'; ?>
<?php include __DIR__.'/../../includes/inc_proyect.php'; ?>
<section class="container kiosko-container text-center">
    <form id="rutForm" method="get" action="/custodia/colaciones/buscar">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <input type="text" name="rut" id="rut"   class="form-control kiosko-input" placeholder="Ingrese su RUT" required>
                <div id="rutError" class="text-danger mt-3" style="display:none; font-size:1.5rem;">
                    ⚠ RUT inválido. Verifique el formato.
                </div>
            </div>
        </div>
        <button class="btn btn-primary kiosko-button" id="buscarBtn">   🔍 BUSCAR  </button>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const rutInput = document.getElementById('rut');
                if (rutInput) {
                    rutInput.focus();
                    rutInput.select(); // opcional: selecciona el texto si vuelve a la página
                }
            });
        </script>

    </form>
</section>
<section class="small container">
    <div class='row justify-content-center  mt-5'>   <span id='reloj'></span>  </div>
    <div class='row justify-content-center  mt-5 text-info'>
    Desayuno: 05:00 hrs - 09:00 hrs. | Almuerzo: 12:00 hrs - 15:00 hrs.  |   Cena: 17:00 hrs - 22:00 hrs.<br>
    </div>

    <div id='clock-box'>  <div id='clock-time'></div>   <div id='clock-date'></div>  </div>


</section>



<!-- ============================================================
     MODAL RESULTADO
=============================================================== -->
<?php if (!empty($resultado)): ?>
    <div class="modal fade" id="resultadoModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content text-center">

                <div class="modal-header border-0 d-flex justify-content-between align-items-center">
                    <h2 class="m-0">Resultado</h2>
                    <button type="button" class="btn btn-danger btn-sm px-3 py-2 fw-bold"
                            data-bs-dismiss="modal">
                        Cerrar ✕
                    </button>
<!--                    <button type='button'-->
<!--                            id='cerrarResultado'-->
<!--                            class='btn btn-danger btn-sm px-3 py-2 fw-bold'-->
<!--                            data-bs-dismiss='modal'>-->
<!--                        Cerrar ✕-->
<!--                    </button>-->


                </div>
                <div class="modal-body">

                    <!-- Datos del usuario -->
                    <h2><?= htmlspecialchars($resultado['nombre']) ?></h2>
                    <h3 class="text-info"><?= htmlspecialchars($resultado['rut']) ?></h3>

                    <h3 class="mt-3">
                        <strong>Estado:</strong> <?= htmlspecialchars($resultado['estado']) ?>
                    </h3>

                    <h3 class="mt-3">
                        <strong>Servicios activos:</strong><br>
                        <?= htmlspecialchars($resultado['servicios']) ?>
                    </h3>

                    <?php if (!empty($resultado['mensaje'])): ?>
                        <div class="alert kiosk-alert-click mt-4" onclick="mensajeClick()">

                            <i class="fa-solid fa-hand-pointer click-icon"></i>

                            <span class="alert-text">
            <?= htmlspecialchars($resultado['mensaje']) ?>
        </span>

                        </div>
                    <?php endif; ?>





                    <!--

                    Impresión de vouchers disponibles

                    -->


                    <?php if (!empty($resultado['vouchers'])): ?>

                        <!--  Verifico los servicios que se pueden imprimir-->
                        <?php foreach ($resultado['vouchers'] as $v): ?>
                            <?php
                            $servicio_id = (int)($v['servicio_id'] ?? 0);
                            $impreso = !empty($v['impreso']); // valor calculado en el controller
                            ?>

                            <!--  comparo la respuesta del controller con el valor calculado-->
                            <?php if ($servicio_id !== 0 && $impreso): ?>
                                <?php $hora_impresion = is_string($v['impreso']) ? $v['impreso'] : ''; ?>
                                <button class="mt-4 w-50 kiosk-alert-click2" disabled>
                                    <i class='fa-solid fa-triangle-exclamation'></i>
                                    Lo sentimos, Ticket ya fue impreso (<?= htmlspecialchars(strtoupper($v['servicio']), ENT_QUOTES) ?>)
                                    <?php if ($hora_impresion !== ''): ?>
                                        <br><small>Impreso a las <?= htmlspecialchars($hora_impresion) ?> hrs</small>
                                    <?php endif; ?>
                                </button>
                                <br><br>
                                <label class="h3">Diríjase a Recepción si necesita ayuda</label>


                            <!--  Si la respuesta del controller es falso, imprimo el voucher-->
                            <?php else: ?>
                                <?php
                                $urlImpresion = '/custodia/colaciones/voucher/imprimir-registrando'
                                    . '?r=' . urlencode($resultado['rut'])
                                    . '&s=' . $servicio_id
                                    . '&url=' . urlencode($v['url']);
                                ?>
                                <a class='btn btn-success kiosk-alert-click mt-4 w-50'
                                   href="#"
                                   data-url="<?= htmlspecialchars($urlImpresion, ENT_QUOTES) ?>"
                                   onclick="return imprimirUnaVez(this);">
                                    <i class="fa fa-print"></i>
                                    Ticket: <?= htmlspecialchars(strtoupper($v['servicio']), ENT_QUOTES) ?><small><sup>[<?= $servicio_id ?>]</sup></small>
                                </a>
                                <div id="popup-error-<?= $servicio_id ?>" class="alert alert-danger mt-2" style="display:none;">
                                    ⚠ No se pudo abrir la ventana de impresión. Verifique que su navegador permite ventanas emergentes o diríjase a Recepción.
                                </div>


                            <?php endif; ?>




                        <?php endforeach; ?>

                    <?php else: ?>
                        <!--  Si no hay servicios disponibles, muestro este mensaje-->
                        <div class="alert alert-warning text-danger mt-5">
                            Consulte en recepción por su caso particular.
                        </div>

                        <div class='text-muted mt-3' style='font-size:0.9rem'>
                            Presione ENTER para continuar
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


<!-- ============================================================
     VALIDACIÓN DE RUT
=============================================================== -->
<script>
    function validarRutJS(rutCompleto) {
        rutCompleto = rutCompleto.replace(/\./g, '').replace(/,/g, '').toLowerCase();
        if (!/^[0-9]+[-]{1}[0-9kK]{1}$/.test(rutCompleto)) return false;

        let [cuerpo, dv] = rutCompleto.split('-');
        dv = dv.toLowerCase();

        let suma = 0, factor = 2;
        for (let i = cuerpo.length - 1; i >= 0; i--) {
            suma += factor * parseInt(cuerpo[i]);
            factor = factor === 7 ? 2 : factor + 1;
        }

        let resto = suma % 11;
        let dvCalc = 11 - resto;

        if (dvCalc === 11) dvCalc = '0';
        else if (dvCalc === 10) dvCalc = 'k';

        return dv === dvCalc.toString();
    }

    document.getElementById('rutForm').addEventListener('submit', function (e) {
        const rut = document.getElementById('rut').value.trim();
        const errorDiv = document.getElementById('rutError');

        if (!validarRutJS(rut)) {
            e.preventDefault();
            errorDiv.style.display = 'block';
            return false;
        }

        errorDiv.style.display = 'none';
    });
</script>


<!-- ABRIR MODAL AUTOMÁTICAMENTE -->
<?php if (!empty($resultado)): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let modal = new bootstrap.Modal(document.getElementById('resultadoModal'));
            modal.show();
        });
    </script>
<?php endif; ?>


<!-- AUTO-RESET POR INACTIVIDAD -->
<script>
    const TIMEOUT_IDLE    = 25000; // 25s sin actividad → reset
    const TIMEOUT_PRINT   = 45000; // 45s después de abrir ventana de impresión

    let timeout = null;

    function resetTimer(duracion) {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            window.location.href = '/custodia/colaciones/buscar';
        }, duracion || TIMEOUT_IDLE);
    }

    function pausarReset() {
        clearTimeout(timeout);
    }

    ['click', 'mousemove', 'keydown', 'touchstart'].forEach(event => {
        document.addEventListener(event, () => resetTimer(), true);
    });

    // Cuando el usuario vuelve al foco (popup cerrado), reducir el tiempo restante
    window.addEventListener('focus', () => resetTimer(15000));

    resetTimer();
</script>


<!-- AUTOFORMATEO RUT -->
<script>
    document.getElementById('rut').addEventListener('input', function () {
        let rut = this.value.replace(/[^0-9kK]/g, '').toLowerCase();

        if (rut.length > 1) {
            this.value = rut.slice(0, -1) + '-' + rut.slice(-1);
        } else {
            this.value = rut;
        }
    });
</script>

<script>
    function actualizarReloj() {
        const f = new Date();

        const horas = String(f.getHours()).padStart(2, '0');
        const minutos = String(f.getMinutes()).padStart(2, '0');
        const segundos = String(f.getSeconds()).padStart(2, '0');

        const dia = String(f.getDate()).padStart(2, '0');
        const mes = String(f.getMonth() + 1).padStart(2, '0');
        const año = f.getFullYear();

        document.getElementById('clock-time').textContent = `${horas}:${minutos}:${segundos}`;
        document.getElementById('clock-date').textContent = `${dia}-${mes}-${año}`;
    }

    setInterval(actualizarReloj, 1000);
    actualizarReloj();
</script>

<script>
    function mensajeClick() {
        console.log('Mensaje clickeado');
        // puedes redirigir, abrir modal, etc.
    }
</script>


<script>
    // ---------------------------
    // BLOQUEA DOBLE IMPRESIÓN
    // ---------------------------
    let botonBloqueado = false;

    function imprimirUnaVez(btn) {
        if (botonBloqueado) return false;

        const url = btn.dataset.url;
        if (!url) return false;

        // Detectar el div de error asociado al botón (por servicio_id en la URL)
        const errorDivId = btn.nextElementSibling;

        // Abrir popup y verificar si fue bloqueado
        const ventana = window.open(url, '_blank', 'width=600,height=500,noopener');

        if (!ventana || ventana.closed || typeof ventana.closed === 'undefined') {
            // Popup bloqueado por el navegador
            if (errorDivId) errorDivId.style.display = 'block';
            return false;
        }

        // Popup abierto correctamente
        botonBloqueado = true;
        btn.style.pointerEvents = 'none';
        btn.style.opacity = '0.6';
        btn.innerHTML = "<i class='fa fa-spinner fa-spin'></i> Imprimiendo...";

        // Dar más tiempo antes del auto-reset
        resetTimer(TIMEOUT_PRINT);

        return false; // ya navegamos con window.open, no seguir el href
    }
</script>






<?php include __DIR__ . '/../../includes/footer.php'; ?>
