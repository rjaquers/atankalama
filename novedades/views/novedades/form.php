<?php include __DIR__ . '/../layout.php'; ?>

<!-- UI/UX Pro Max - Premium Design System -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

<style>
    :root {
        --primary-desert: #7c2d12;
        /* Terracotta Profundo */
        --secondary-desert: #b45309;
        /* Tierra / Ocre */
        --accent-sand: #f59e0b;
        /* Arena Dorada */
        --accent-sand-dark: #d97706;
        --glass-bg: rgba(255, 255, 255, 0.98);
        --glass-border: rgba(214, 211, 209, 0.5);
        /* Piedra suave */
        --text-main: #44403c;
        /* Piedra oscura */
        --text-muted: #78716c;
        --input-focus: #f59e0b;
    }

    body {
        font-family: 'DM Sans', sans-serif;
        background: linear-gradient(135deg, #fef3c7 0%, #fae8ff 100%, #fefce8 100%);
        /* Colores amanecer desierto */
        background-color: #fafaf9;
        color: var(--text-main);
        min-height: 100vh;
    }

    .premium-header {
        background: var(--primary-desert);
        color: white;
        padding: 3rem 0 5.5rem 0;
        /* Más espacio abajo para el subtítulo */
        margin-bottom: -4.5rem;
        /* Ajustado para el solapamiento elegante */
        border-radius: 0 0 50px 50px;
        box-shadow: 0 10px 30px rgba(124, 45, 18, 0.15);
    }

    .premium-header h1 {
        font-size: 1.8rem;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .premium-header p {
        font-size: 1rem;
        font-weight: 400;
        opacity: 0.9;
        margin-top: 5px;
    }

    .form-container {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        padding: 3rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
        margin-bottom: 4rem;
    }

    .form-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--secondary-navy);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-label i {
        color: var(--secondary-desert);
        font-size: 1.1rem;
    }

    .form-control,
    .form-select {
        border-radius: 12px;
        border: 1px solid #e7e5e4;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        background-color: #fcfcfb;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--accent-sand);
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
        background-color: white;
    }

    .btn-primary {
        background: var(--secondary-desert);
        border: none;
        border-radius: 12px;
        padding: 0.8rem 2rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(180, 83, 9, 0.2);
    }

    .btn-primary:hover {
        background: var(--primary-desert);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(124, 45, 18, 0.3);
    }

    .btn-dictado {
        border-radius: 50px;
        padding: 0.5rem 1.2rem;
        font-weight: 500;
        border: 2px solid var(--secondary-desert);
        color: var(--secondary-desert);
        background: white;
        transition: all 0.2s ease;
    }

    .btn-dictado:hover {
        background: var(--secondary-desert);
        color: white;
    }

    .btn-dictado.listening {
        background: #fee2e2;
        border-color: #ef4444;
        color: #ef4444;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% {
            opacity: 1;
            transform: scale(1);
        }

        50% {
            opacity: 0.8;
            transform: scale(1.02);
        }

        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    .type-selector {
        background: #f1f5f9;
        border-radius: 5px;
        padding: 1rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
        gap: 12px;  
        border: 1px solid #e2e8f0;
    }

    .type-option {
        position: relative;
    }

    .type-option input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    .type-option label {
        display: block;
        padding: 0.3rem;
        text-align: center;
        background: white;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s ease;
        margin-bottom: 0;
    }

    .type-option input:checked+label {
        background: var(--secondary-desert);
        color: white;
        border-color: var(--secondary-desert);
        box-shadow: 0 4px 12px rgba(180, 83, 9, 0.2);
    }

    .type-option:hover label {
        border-color: var(--accent-sand);
        transform: translateY(-1px);
    }

    .hotel-selector {
        display: flex;
        gap: 10px;
        margin-top: 5px;
    }

    .hotel-option input {
        display: none;
    }

    .hotel-option label {
        padding: 6px 16px;
        border-radius: 8px;
        background: #f1f5f9;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-muted);
        transition: all 0.2s;
    }

    .hotel-option input:checked+label {
        background: var(--secondary-desert);
        color: white;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 2rem;
        color: var(--primary-desert);
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 2px solid #fef3c7;
        padding-bottom: 5px;
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-header {
        background: var(--primary-desert);
        color: white;
        border-radius: 20px 20px 0 0;
        border: none;
        padding: 1.5rem;
    }

    .modal-title {
        font-weight: 700;
    }

    .btn-success {
        background-color: var(--accent-sand);
        border: none;
        border-radius: 10px;
        padding: 0.6rem 2rem;
        font-weight: 600;
        color: white;
    }

    .btn-success:hover {
        background-color: var(--accent-sand-dark);
        color: white;
    }

    .btn-secondary {
        border-radius: 10px;
        padding: 0.6rem 2rem;
    }
</style>

<div class="premium-header text-center">
    <div class="container">
        <h1>Registro de Novedades</h1>
        <p>Gestión de novedades y seguimiento operativo</p>
    </div>
</div>



<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="form-container">
                <form action="index.php?route=novedades/store" method="POST" enctype='multipart/form-data'
                    id='form-novedad'>

                    <div class="section-title">
                        <i class="bi bi-info-circle-fill"></i> Información General
                    </div>

                    <div class='row mb-4'>
                        <!-- Recepcionista -->
                        <div class='col-md-4'>
                            <label class='form-label'><i class="bi bi-person"></i> Recepcionista</label>
                            <select name='recepcionista_id' class='form-select' required>
                                <?php foreach ($recepcionistas as $r): ?>
                                    <option value="<?= (int) $r['id'] ?>">
                                        <?= htmlspecialchars($r['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Área -->
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-grid-1x2"></i> Área</label>
                            <select name='area' class='form-select'>
                                <option value='recepcion'>Recepción</option>
                                <option value='estacionamiento'>Estacionamiento</option>
                                <option value='comedor'>Comedor</option>
                                <option value='cocina'>Cocina</option>
                                 <option value='piscina'>Piscina</option>
                                <option value='habitacion'>Habitación</option>
                                <option value='Pasillos'>Pasillos</option>
                                <option value='Jardines'>Jardines</option>
                                <option value='otros'>Otros</option>
                            </select>
                        </div>

                        <!-- Hotel -->
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-building"></i> Hotel</label>
                            <div class="hotel-selector">
                                <div class="hotel-option">
                                    <input type="radio" name="hotel" id="hotel1" value="Atankalama" checked>
                                    <label for="hotel1">Atankalama</label>
                                </div>
                                <div class="hotel-option">
                                    <input type="radio" name="hotel" id="hotel2" value="Atankalama Inn">
                                    <label for="hotel2">Atankalama Inn</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tipo de Novedad -->
                    <div class='mb-5'>
                        <label class='form-label fw-bold mb-3'><i class="bi bi-tag-fill"></i> Departamento involucrado</label>
                        <div class="type-selector">
                            <div class="type-option">
                                <input type="radio" name="tipo_novedad" id="tipo_personal" value="Personal" required>
                                <label for="tipo_personal">RRHH</label>
                            </div>
                            <div class="type-option">
                                <input type="radio" name="tipo_novedad" id="tipo_aseo" value="Aseo">
                                <label for="tipo_aseo">Aseo</label>
                            </div>
                            <div class="type-option">
                                <input type="radio" name="tipo_novedad" id="tipo_cocina" value="Cocina">
                                <label for="tipo_cocina">Cocina</label>
                            </div>
                          
                            <div class="type-option">
                                <input type="radio" name="tipo_novedad" id="tipo_servicio" value="Servicio">
                                <label for="tipo_servicio">Housequeping</label>
                            </div>
                            <div class="type-option">
                                <input type="radio" name="tipo_novedad" id="tipo_tecnologia" value="Tecnologia">
                                <label for="tipo_tecnologia">Tecnología</label>
                            </div>
                            <div class="type-option">
                                <input type="radio" name="tipo_novedad" id="tipo_mantenimiento" value="mantenimiento">
                                <label for="tipo_mantenimiento">Mantenimiento</label>
                            </div>
                            <div class="type-option">
                                <input type="radio" name="tipo_novedad" id="tipo_otro" value="Otro" checked>
                                <label for="tipo_otro">Otro</label>
                            </div>
                        </div>
                    </div>

                    <div class="section-title">
                        <i class="bi bi-chat-left-text-fill"></i> Detalle de la Novedad
                    </div>

                    <div class="mb-4">
                        <textarea id="detalle" name="detalle" class="form-control" rows="4"
                            placeholder="Escribe aquí el detalle de lo ocurrido..." required></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button type="button" class="btn btn-dictado" id="btn-dictado" onclick="startDictado()">
                                <i class="bi bi-mic-fill"></i> Dictar por voz
                            </button>
                            <span class="text-muted small">Usa el dictado para mayor rapidez</span>
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class='col-md-6'>
                            <label class='form-label'><i class="bi bi-lightning-fill text-warning"></i> Nivel de
                                Importancia (1–10)</label>
                            <select name='nivel_importancia' class='form-select' required>
                                <option value='1' selected>1 (Baja)</option>
                                <?php for ($i = 2; $i <= 10; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?>
                                        <?= ($i >= 8 ? '(Crítica)' : ($i >= 4 ? '(Media)' : '')) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class='col-md-6'>
                            <label for='archivos' class='form-label'>
                                <i class='bi bi-paperclip'></i> Evidencia / Archivos (opcional)
                            </label>
                            <input type='file' class='form-control' name='archivos[]' id='archivos' multiple>
                        </div>
                    </div>

                    <div class="d-grid pt-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check2-circle me-2"></i> Registrar Novedad
                        </button>
                    </div>

                    <input type='hidden' name='requiere_seguimiento' id='requiere_seguimiento' value='0'>
                    <input type='hidden' name='tipo_seguimiento' id='tipo_seguimiento' value=''>
                    <input type='hidden' name='flexkeeping_id' id='flexkeeping_id' value=''>
                </form>
            </div>
        </div>
    </div>
</div>


<!--modal -->
<!-- Modal Requiere Seguimiento -->
<div class='modal fade' id='modalSeguimiento' tabindex='-1' aria-hidden='true'>
    <div class='modal-dialog modal-dialog-centered'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'>Confirmación</h5>
            </div>

            <div class='modal-body text-center' id='modalSeguimientoPaso1'>
                <p class='mb-3'><strong>¿Requiere Seguimiento?</strong></p>

                <div class='d-flex justify-content-center gap-3'>
                    <button type='button' class='btn btn-success' id='btnSeguimientoSi'>
                        Sí
                    </button>

                    <button type='button' class='btn btn-secondary' id='btnSeguimientoNo'>
                        No
                    </button>
                </div>
                <div class='small mt-2 text-muted'>Nota: Si selecciona Sí, el sistema pedirá registrar la tarea en
                    Flexkeeping.</div>
            </div>

            <div class='modal-body text-center d-none' id='modalSeguimientoPaso2'>
                <p class='mb-3'><strong>¿La actividad es una tarea o una reparación?</strong></p>

                <div class="mb-3 d-flex justify-content-center gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="modal_tipo_seguimiento" id="radioTarea"
                            value="tarea">
                        <label class="form-check-label" for="radioTarea">Es Tarea</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="modal_tipo_seguimiento" id="radioReparacion"
                            value="reparacion">
                        <label class="form-check-label" for="radioReparacion">Es Reparación</label>
                    </div>
                </div>

                <div class="mb-3 d-none text-start" id="flexkeepingIdContainer">
                    <label class="form-label" for="modalFlexkeepingId">Por favor ingresa el Nro ID entregado por
                        Flexkeeping:</label>
                    <input type="text" class="form-control" id="modalFlexkeepingId" placeholder="Ej: TASK-1234">
                    <div class="text-danger small mt-1 d-none" id="flexkeepingIdError">Debes ingresar el ID entregado
                        por Flexkeeping para continuar.</div>
                </div>

                <div class="d-none" id="btnGuardarFinalContainer">
                    <button type='button' class='btn btn-primary' id='btnGuardarFinal'>
                        Guardar Novedad
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const form = document.getElementById('form-novedad');
        const modalElement = document.getElementById('modalSeguimiento');
        const modal = new bootstrap.Modal(modalElement);

        const inputSeguimiento = document.getElementById('requiere_seguimiento');
        const inputTipoSeguimiento = document.getElementById('tipo_seguimiento');
        const inputFlexkeepingId = document.getElementById('flexkeeping_id');

        const btnSi = document.getElementById('btnSeguimientoSi');
        const btnNo = document.getElementById('btnSeguimientoNo');

        const paso1 = document.getElementById('modalSeguimientoPaso1');
        const paso2 = document.getElementById('modalSeguimientoPaso2');
        const radioTarea = document.getElementById('radioTarea');
        const radioReparacion = document.getElementById('radioReparacion');

        const fkContainer = document.getElementById('flexkeepingIdContainer');
        const flexIdInput = document.getElementById('modalFlexkeepingId');
        const errorMsg = document.getElementById('flexkeepingIdError');

        const btnGuardarFinalContainer = document.getElementById('btnGuardarFinalContainer');
        const btnGuardarFinal = document.getElementById('btnGuardarFinal');

        let submitConfirmado = false;

        // Interceptar submit
        form.addEventListener('submit', function (e) {
            if (!submitConfirmado) {
                e.preventDefault();
                modal.show();
            }
        });

        btnNo.addEventListener('click', function () {
            inputSeguimiento.value = 0;
            submitConfirmado = true;
            modal.hide();
            form.submit();
        });

        btnSi.addEventListener('click', function () {
            inputSeguimiento.value = 1;
            paso1.classList.add('d-none');
            paso2.classList.remove('d-none');
        });

        const handleRadioChange = (e) => {
            const tipo = e.target.value;
            inputTipoSeguimiento.value = tipo;
            fkContainer.classList.remove('d-none');
            btnGuardarFinalContainer.classList.remove('d-none');

            let url = '';
            if (tipo === 'tarea') {
                url = 'https://app.flexkeeping.com/assignment/task?filters=submitted';
            } else if (tipo === 'reparacion') {
                url = 'https://app.flexkeeping.com/assignment/repair?filters=submitted';
            }

            if (url) {
                window.open(url, '_blank');
            }
        };

        radioTarea.addEventListener('change', handleRadioChange);
        radioReparacion.addEventListener('change', handleRadioChange);

        btnGuardarFinal.addEventListener('click', function () {
            const val = flexIdInput.value.trim();
            if (!val) {
                errorMsg.classList.remove('d-none');
                return;
            }
            errorMsg.classList.add('d-none');
            inputFlexkeepingId.value = val;

            submitConfirmado = true;
            modal.hide();
            form.submit();
        });

        // Reset inputs when modal closes without submitting
        modalElement.addEventListener('hidden.bs.modal', function () {
            if (!submitConfirmado) {
                paso1.classList.remove('d-none');
                paso2.classList.add('d-none');
                fkContainer.classList.add('d-none');
                btnGuardarFinalContainer.classList.add('d-none');
                radioTarea.checked = false;
                radioReparacion.checked = false;
                flexIdInput.value = '';
                errorMsg.classList.add('d-none');

                inputSeguimiento.value = 0;
                inputTipoSeguimiento.value = '';
                inputFlexkeepingId.value = '';
            }
        });

    });
</script>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>


<script>
    function startDictado() {
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            alert("Tu navegador no soporta el dictado por voz. Intenta usar Google Chrome.");
            return;
        }

        var recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        var textarea = document.getElementById('detalle');
        var btn = document.querySelector('button[onclick="startDictado()"]');
        var originalBtnText = btn.innerHTML;

        recognition.lang = "es-CL";
        recognition.interimResults = false;

        recognition.onstart = function () {
            btn.innerHTML = '<i class="bi bi-mic-fill"></i> Escuchando...';
            btn.classList.add('listening');
        };

        recognition.onresult = function (event) {
            var text = event.results[0][0].transcript;

            // Si ya hay texto, agregamos un espacio antes del nuevo dictado
            if (textarea.value.trim() !== '') {
                textarea.value += ' ' + text;
            } else {
                textarea.value = text;
            }
        };

        recognition.onerror = function (event) {
            console.error("Error en dictado:", event.error);
            alert("Hubo un error al usar el micrófono. Por favor revisa los permisos.");
        };

        recognition.onend = function () {
            btn.innerHTML = originalBtnText;
            btn.classList.remove('listening');
        };

        recognition.start();
    }
</script>