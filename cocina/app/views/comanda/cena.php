<!DOCTYPE html>
<html lang='es'>
<head>
    <?php 
    $pageTitle = ($tipoFijo === 'colacion_especial') ? "Nueva Colación Especial" : "Nueva Cena/Colación";
    include(ROOT_PATH . '../public/static/templates/head.php'); 
    ?>
</head>
<body class='pro-body'>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

<style>
    /* Emil Kowalski's Principles: Polish & Responsiveness */
    :root {
        --ease-out-expo: cubic-bezier(0.16, 1, 0.3, 1);
        --ease-out-back: cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .pro-card {
        transition: transform 0.2s var(--ease-out-expo), box-shadow 0.2s var(--ease-out-expo);
        opacity: 0;
        transform: translateY(10px);
        animation: slideUp 0.5s var(--ease-out-expo) forwards;
    }

    @keyframes slideUp {
        to { opacity: 1; transform: translateY(0); }
    }

    .pro-card:nth-child(1) { animation-delay: 0.05s; }
    .pro-card:nth-child(2) { animation-delay: 0.1s; }
    .pro-card:nth-child(3) { animation-delay: 0.15s; }
    .pro-card:nth-child(4) { animation-delay: 0.2s; }

    /* Button feedback */
    .btn-pro-primary, .btn-pro-action, .btn-outline-primary, .btn-outline-secondary, .btn-check + label {
        transition: transform 0.15s var(--ease-out-expo), background-color 0.15s ease, border-color 0.15s ease !important;
    }

    .btn-pro-primary:active, .btn-pro-action:active, .btn-outline-primary:active, .btn-outline-secondary:active, .btn-check + label:active {
        transform: scale(0.97) !important;
    }

    /* Custom Radio Buttons (Tipo Servicio) */
    .service-type-label {
        border-radius: 16px !important;
        border: 2px solid var(--color-border) !important;
        padding: 1.5rem !important;
        background: var(--color-surface) !important;
        color: var(--color-text-muted) !important;
        position: relative;
        overflow: hidden;
    }

    .btn-check:checked + .service-type-label {
        border-color: var(--color-cta) !important;
        background: #f0f7ff !important;
        color: var(--color-cta) !important;
        box-shadow: 0 0 0 1px var(--color-cta);
    }

    .service-type-label i {
        font-size: 2.2rem;
        transition: transform 0.3s var(--ease-out-back);
    }

    .btn-check:checked + .service-type-label i {
        transform: scale(1.1) rotate(5deg);
    }

    .btn-check:checked + .service-type-label::after {
        content: ' ';
        position: absolute;
        top: 10px;
        right: 10px;
        width: 20px;
        height: 20px;
        background: var(--color-cta);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-family: "bootstrap-icons";
        content: "\F26E"; /* bi-check */
        font-size: 12px;
    }
</style>

    <div class='container py-4'>

        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom"
            style="border-color: var(--color-border) !important;">
            <h2 class="mb-0 fw-bold">
                <?php if ($tipoFijo === 'colacion_especial'): ?>
                    <i class="bi bi-star-fill me-2" style="color: var(--color-cta)"></i>Nueva Colación Especial
                <?php else: ?>
                    <i class="bi bi-journal-plus me-2" style="color: var(--color-cta)"></i>Nueva Comanda
                <?php endif; ?>
            </h2>
            <a href="index.php?page=comanda/listado" class="btn btn-pro-action px-3" style="width:auto;">
                <i class="bi bi-list me-1"></i>Ver Listado
            </a>
        </div>

        <?php if (isset($_GET['ok'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong><?= (int)$_GET['ok'] ?> comanda(s)</strong> registrada(s) correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'sin_fechas'): ?>
            <div class="alert alert-warning alert-dismissible fade show mb-4">
                <i class="bi bi-exclamation-triangle me-2"></i>No hay fechas válidas. Selecciona al menos un día.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=comanda/guardarMulti" id="formComanda" enctype="multipart/form-data">

            <?php if ($tipoFijo === 'colacion_especial'): ?>
                <input type="hidden" name="tipo_servicio" value="colacion_especial">
            <?php endif; ?>

            <!-- SECCIÓN 1: Tipo de servicio (Restaurado diseño visual) -->
            <?php 
            if ($tipoFijo === null): 
                $tipoDefault = $_GET['tipo'] ?? 'cena';
            ?>
            <div class="pro-card border-0 mb-3">
                <div class="card-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="radio" class="btn-check" name="tipo_servicio"
                                id="tipo_almuerzo" value="almuerzo" autocomplete="off" <?= $tipoDefault === 'almuerzo' ? 'checked' : '' ?>>
                            <label class="btn service-type-label w-100 d-flex align-items-center gap-3"
                                for="tipo_almuerzo" style="padding: 1rem !important;">
                                <i class="bi bi-sun-fill text-warning" style="font-size: 1.8rem;"></i>
                                <div class="text-start">
                                    <div class="fw-bold fs-5">Almuerzo</div>
                                    <div class="small fw-normal opacity-75">Servicio mediodía</div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <input type="radio" class="btn-check" name="tipo_servicio"
                                id="tipo_cena" value="cena" autocomplete="off" <?= $tipoDefault === 'cena' ? 'checked' : '' ?>>
                            <label class="btn service-type-label w-100 d-flex align-items-center gap-3"
                                for="tipo_cena" style="padding: 1rem !important;">
                                <i class="bi bi-moon-stars-fill" style="color:#5c6bc0; font-size: 1.8rem;"></i>
                                <div class="text-start">
                                    <div class="fw-bold fs-5">Cena</div>
                                    <div class="small fw-normal opacity-75">Servicio noche</div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <input type="radio" class="btn-check" name="tipo_servicio"
                                id="tipo_colacion" value="colacion" autocomplete="off" <?= $tipoDefault === 'colacion' ? 'checked' : '' ?>>
                            <label class="btn service-type-label w-100 d-flex align-items-center gap-3"
                                for="tipo_colacion" style="padding: 1rem !important;">
                                <i class="bi bi-box-seam-fill text-success" style="font-size: 1.8rem;"></i>
                                <div class="text-start">
                                    <div class="fw-bold fs-5">Colación</div>
                                    <div class="small fw-normal opacity-75">Box lunch / faena</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- SECCIÓN 2: Rango de fechas + Hotel + Personas + Hora -->
            <div class="pro-card border-0 mb-3">
                <div class="card-body px-4 py-3">

                    <!-- Rango de fechas y días de semana -->
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label text-muted small fw-bold mb-1">
                                <i class="bi bi-calendar-event me-1"></i>Desde
                            </label>
                            <input type="date" class="form-control form-control-sm border-0 shadow-sm"
                                id="fechaDesde" name="fecha_desde"
                                value="<?= date('Y-m-d') ?>" required style="border-radius:8px;">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small fw-bold mb-1">
                                <i class="bi bi-calendar-event me-1"></i>Hasta
                            </label>
                            <input type="date" class="form-control form-control-sm border-0 shadow-sm"
                                id="fechaHasta" name="fecha_hasta"
                                value="<?= date('Y-m-d') ?>" required style="border-radius:8px;">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-muted small fw-bold mb-1">
                                <i class="bi bi-calendar-week me-1"></i>Días a incluir
                            </label>
                            <div class="d-flex gap-1">
                                <?php
                                $dias = ['L' => 'Lu', 'M' => 'Ma', 'X' => 'Mi', 'J' => 'Ju', 'V' => 'Vi', 'S' => 'Sá', 'D' => 'Do'];
                                $vals = [1, 2, 3, 4, 5, 6, 0];
                                $i = 0;
                                foreach ($dias as $key => $nombre):
                                    $checked = in_array($vals[$i], [1,2,3,4,5,6,0]) ? 'checked' : '';
                                ?>
                                <div class="form-check p-0 m-0">
                                    <input class="btn-check" type="checkbox" name="dias_semana[]" value="<?= $vals[$i] ?>" id="dia_<?= $key ?>" autocomplete="off" <?= $checked ?>>
                                    <label class="btn btn-outline-primary btn-xs fw-bold" for="dia_<?= $key ?>" style="border-radius:6px; padding: 2px 8px; font-size: 11px;">
                                        <?= $nombre ?>
                                    </label>
                                </div>
                                <?php $i++; endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold mb-1">
                                <i class="bi bi-building me-1"></i>Hotel
                            </label>
                            <select class="form-select form-select-sm border-0 shadow-sm" name="nombre_hotel" required style="border-radius:8px;">
                                <option value="Atankalama">Atankalama</option>
                                <option value="Atankalama Inn">Atankalama Inn</option>
                            </select>
                        </div>
                    </div>

                    <!-- Hotel, Personas, Hora (Fila reducida) -->
                    <div class="row g-3 mt-1">
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold mb-1">
                                <i class="bi bi-people me-1"></i>Personas
                            </label>
                            <input type="number" class="form-control form-control-sm border-0 shadow-sm"
                                name="cantidad_personas" id="cantidad_personas" value="1" min="1" required style="border-radius:8px;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold mb-1">
                                <i class="bi bi-clock me-1"></i>Hora servicio <span class="fw-normal opacity-50">(opt)</span>
                            </label>
                            <input type="time" class="form-control form-control-sm border-0 shadow-sm"
                                name="hora_servicio" style="border-radius:8px;">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                             <div id="previewFechas" class="p-2 rounded-3 border w-100 bg-light" style="display:none; font-size: 11px;">
                                <div class="fw-bold text-muted mb-1 d-flex justify-content-between">
                                    <span><i class="bi bi-calendar-check me-1 text-success"></i>Fechas:</span>
                                    <span id="totalFechas" class="text-success fw-bold">0</span>
                                </div>
                                <div id="listaFechas" class="d-flex flex-wrap gap-1"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="sinFechas" class="alert alert-warning mb-0 py-1 mt-2 small" style="display:none;">
                        <i class="bi bi-exclamation-triangle me-1"></i>Rango sin fechas válidas.
                    </div>
                    <div id="inputsFechasOcultos"></div>
                </div>
            </div>

            <!-- SECCIÓN 3: Quién solicita -->
            <div class="pro-card border-0 mb-3">
                <div class="card-body px-4 py-3">
                    <div class="d-flex align-items-center gap-4 mb-3">
                        <h6 class="fw-bold mb-0 text-primary" style="min-width:100px;">
                            <i class="bi bi-person-bounding-box me-2"></i>Solicitante
                        </h6>
                        <div class="d-flex gap-2">
                            <input type="radio" class="btn-check" name="tipo_solicitante" id="sol_empresa" value="empresa" autocomplete="off" checked>
                            <label class="btn btn-outline-primary btn-sm px-3 fw-bold" for="sol_empresa" style="border-radius:10px;">Empresa</label>

                            <input type="radio" class="btn-check" name="tipo_solicitante" id="sol_particular" value="particular" autocomplete="off">
                            <label class="btn btn-outline-secondary btn-sm px-3 fw-bold" for="sol_particular" style="border-radius:10px;">Particular</label>
                        </div>
                    </div>

                    <!-- Campos empresa -->
                    <div id="camposEmpresa" class="p-3 rounded-4 border bg-light bg-opacity-10">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-bold mb-1 d-flex justify-content-between">
                                    <span>Empresa <span class="text-danger">*</span></span>
                                    <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-bold small" data-bs-toggle="modal" data-bs-target="#modalNuevaEmpresa" style="font-size:11px;">+ Nueva</button>
                                </label>
                                <select class="form-select form-select-sm border-0 shadow-sm" name="company_id" id="selectEmpresa" style="border-radius:8px;">
                                    <option value="">— Selecciona —</option>
                                    <?php foreach ($empresasLista as $emp): ?>
                                        <option value="<?= $emp['id'] ?>" data-nombre="<?= htmlspecialchars($emp['business_name']) ?>" data-contacto="<?= htmlspecialchars($emp['contact_name'] ?? '') ?>">
                                            <?= htmlspecialchars($emp['business_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-bold mb-1">Nombre en comanda</label>
                                <input type="text" class="form-control form-control-sm border-0 shadow-sm" name="nombre_empresa" id="inputNombreEmpresa" style="border-radius:8px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-bold mb-1">Contacto / Encargado</label>
                                <input type="text" class="form-control form-control-sm border-0 shadow-sm" name="nombre_contacto" id="inputContacto" style="border-radius:8px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-bold mb-1">Contrato asignado</label>
                                <select class="form-select form-select-sm border-0 shadow-sm" name="contract_id" id="selectContrato" disabled style="border-radius:8px;">
                                    <option value="">— Elige empresa —</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Campos particular -->
                    <div id="camposParticular" style="display:none;">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-bold d-flex align-items-center">
                                    <i class="bi bi-person fs-5 me-2 text-primary"></i>Nombre del solicitante
                                </label>
                                <input type="text" class="form-control form-control-lg border-0 shadow-none"
                                    name="nombre_contacto_particular"
                                    placeholder="Nombre del huésped o recepcionista">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 4: Observaciones, Respaldos y Origen -->
            <div class="pro-card border-0 mb-3">
                <div class="card-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-muted small fw-bold mb-0">
                                    <i class="bi bi-chat-left-text me-1"></i>Observaciones <span class="fw-normal opacity-50">(opt)</span>
                                </label>
                                <div class="d-flex flex-wrap gap-1">
                                    <button type="button" class="btn btn-sm py-0 px-2 btn-light border text-muted fw-bold"
                                            style="font-size: 0.65rem; border-radius: 6px;"
                                            onclick="addQuickObs('Turno Día')">+ Turno Día</button>
                                    <button type="button" class="btn btn-sm py-0 px-2 btn-light border text-muted fw-bold"
                                            style="font-size: 0.65rem; border-radius: 6px;"
                                            onclick="addQuickObs('Turno Noche')">+ Turno Noche</button>
                                    <button type="button" class="btn btn-sm py-0 px-2 btn-light border text-muted fw-bold"
                                            style="font-size: 0.65rem; border-radius: 6px;"
                                            onclick="addQuickObs('Ensalada de fruta')">+ Ensalada de fruta</button>
                                    <button type="button" class="btn btn-sm py-0 px-2 btn-light border text-muted fw-bold"
                                            style="font-size: 0.65rem; border-radius: 6px;"
                                            onclick="addQuickObs('Hipocalórico')">+ Hipocalórico</button>
                                    <button type="button" class="btn btn-sm py-0 px-2 btn-light border text-muted fw-bold"
                                            style="font-size: 0.65rem; border-radius: 6px;"
                                            onclick="addQuickObs('Cena Especial')">+ Cena Especial</button>
                                </div>
                            </div>
                            <textarea class="form-control form-control-sm border-0 shadow-sm mb-2" name="observaciones" id="inputObservaciones" rows="1" placeholder="Instrucciones..." style="resize:none; border-radius:8px;"></textarea>

                            <!-- Carga de Archivos de Respaldo (Compacto) -->
                            <div class="p-2 rounded-3 border bg-light bg-opacity-50">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label class="form-label text-muted small fw-bold mb-0">
                                        <i class="bi bi-paperclip me-1"></i>Respaldos
                                    </label>
                                    <label class="btn btn-link btn-sm p-0 text-decoration-none fw-bold" for="respaldos" style="font-size:11px; cursor:pointer;">+ Adjuntar</label>
                                    <input type="file" name="respaldos[]" id="respaldos" multiple hidden accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.msg,.eml">
                                </div>
                                <div id="filePreview" class="mt-1 d-flex flex-wrap gap-1"></div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-muted small fw-bold mb-1">
                                <i class="bi bi-lightning-charge me-1"></i>Prioridad / Origen
                            </label>
                            <div class="d-flex gap-2">
                                <div class="flex-grow-1">
                                    <input type="radio" class="btn-check" name="origen" id="origen_programada" value="programada" autocomplete="off" checked>
                                    <label class="btn btn-outline-success btn-sm w-100 fw-bold py-2" for="origen_programada" style="border-radius:8px; font-size:12px;">Programada</label>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="radio" class="btn-check" name="origen" id="origen_urgente" value="urgente" autocomplete="off">
                                    <label class="btn btn-outline-danger btn-sm w-100 fw-bold py-2" for="origen_urgente" style="border-radius:8px; font-size:12px;">Urgente</label>
                                </div>
                            </div>
                            <div id="alertaUrgente" class="alert alert-danger py-1 mt-2 mb-0 small" style="display:none; font-size:11px;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Aparecerá ahora en cocina.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER: Botón enviar -->
            <div class="pro-card border-0 mb-4 rounded-4 overflow-hidden shadow-sm">
                <div class="card-body p-0">
                    <button type="submit" id="btnEnviar"
                        class="btn btn-pro-primary px-5 py-3 fw-bold text-white d-flex align-items-center justify-content-center w-100 fs-5"
                        style="border-radius:0; background: linear-gradient(135deg, var(--color-cta) 0%, #3b82f6 100%); border:none;" disabled>
                        <i class="bi bi-cloud-check-fill me-2 fs-5"></i>
                        REGISTRAR <span id="btnTextoFechas" class="ms-1">— selecciona fechas</span>
                    </button>
                </div>
            </div>

        </form>
    </div>

    <!-- Modal Nueva Empresa -->
    <div class="modal fade" id="modalNuevaEmpresa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold"><i class="bi bi-building-add me-2"></i>Registrar Nueva Empresa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formNuevaEmpresa">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-muted">Razón Social <span class="text-danger">*</span></label>
                                <input type="text" name="business_name" class="form-control" placeholder="Ej: Transportes Atacama S.A." required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">RUT</label>
                                <input type="text" name="rut" class="form-control" placeholder="12.345.678-9">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Nombre de Fantasía</label>
                                <input type="text" name="trade_name" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Ciudad</label>
                                <input type="text" name="city" class="form-control">
                            </div>
                            <hr class="my-2">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Nombre Contacto</label>
                                <input type="text" name="contact_name" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Teléfono</label>
                                <input type="text" name="contact_phone" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Email de Contacto</label>
                                <input type="email" name="contact_email" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 p-3">
                        <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold" id="btnGuardarEmpresa">
                            <i class="bi bi-check-lg me-1"></i>Guardar Empresa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>

    <script>
    function addQuickObs(text) {
        const area = document.getElementById('inputObservaciones');
        const current = area.value.trim();
        if (current) {
            if (current.endsWith(',') || current.endsWith('.')) {
                area.value = current + ' ' + text;
            } else {
                area.value = current + '. ' + text;
            }
        } else {
            area.value = text;
        }
        area.focus();
        area.style.backgroundColor = '#e2e8f0';
        setTimeout(() => { area.style.backgroundColor = ''; }, 150);
    }

    document.addEventListener('DOMContentLoaded', function () {

        // ── Nueva Empresa AJAX ──────────────────────────────
        const formNuevaEmpresa = document.getElementById('formNuevaEmpresa');
        const btnGuardarEmpresa = document.getElementById('btnGuardarEmpresa');
        const modalNuevaEmpresa = new bootstrap.Modal(document.getElementById('modalNuevaEmpresa'));

        formNuevaEmpresa.addEventListener('submit', function (e) {
            e.preventDefault();
            btnGuardarEmpresa.disabled = true;
            btnGuardarEmpresa.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

            const formData = new FormData(this);

            fetch('index.php?page=comanda/crearEmpresaAjax', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    // 1. Agregar a la lista de empresas
                    const selectEmpresa = document.getElementById('selectEmpresa');
                    const newOpt = new Option(res.business_name, res.id);
                    newOpt.dataset.nombre = res.business_name;
                    newOpt.dataset.contacto = res.contact_name || '';
                    selectEmpresa.add(newOpt);
                    
                    // 2. Seleccionarla
                    selectEmpresa.value = res.id;
                    
                    // 3. Disparar el evento change para actualizar otros campos
                    selectEmpresa.dispatchEvent(new Event('change'));
                    
                    // 4. Cerrar modal y limpiar
                    modalNuevaEmpresa.hide();
                    formNuevaEmpresa.reset();
                    
                    alert('Empresa creada correctamente');
                } else {
                    alert('Error: ' + res.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Ocurrió un error al procesar la solicitud.');
            })
            .finally(() => {
                btnGuardarEmpresa.disabled = false;
                btnGuardarEmpresa.innerHTML = '<i class="bi bi-check-lg me-1"></i>Guardar Empresa';
            });
        });

        // ── Toggle empresa / particular ──────────────────────
        const camposEmpresa    = document.getElementById('camposEmpresa');
        const camposParticular = document.getElementById('camposParticular');
        const selectEmpresa    = document.getElementById('selectEmpresa');

        function aplicarTipo(tipo) {
            const esEmpresa = tipo === 'empresa';
            camposEmpresa.style.display    = esEmpresa ? '' : 'none';
            camposParticular.style.display = esEmpresa ? 'none' : '';
            selectEmpresa.required         = esEmpresa;
        }
        document.querySelectorAll('input[name="tipo_solicitante"]').forEach(r => {
            r.addEventListener('change', () => aplicarTipo(r.value));
        });
        aplicarTipo('empresa');

        // ── Pre-llenar nombre empresa al seleccionar ─────────
        selectEmpresa.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            document.getElementById('inputNombreEmpresa').value = opt.dataset.nombre || '';
            document.getElementById('inputContacto').value      = opt.dataset.contacto || '';

            const sel = document.getElementById('selectContrato');
            sel.innerHTML = '<option value="">Cargando...</option>';
            sel.disabled  = true;

            if (!this.value) {
                sel.innerHTML = '<option value="">— Elige empresa primero —</option>';
                return;
            }

            fetch('index.php?page=recepcion/contratosEmpresa&company_id=' + this.value)
                .then(r => r.json())
                .then(contratos => {
                    sel.innerHTML = '<option value="">— Sin contrato (opcional) —</option>';
                    contratos.forEach(c => {
                        const estado = c.status.charAt(0).toUpperCase() + c.status.slice(1);
                        sel.innerHTML += `<option value="${c.id}">${c.code} — ${c.contract_type} [${estado}]</option>`;
                    });
                    if (!contratos.length)
                        sel.innerHTML += '<option disabled>Sin contratos activos</option>';
                    sel.disabled = false;
                })
                .catch(() => { sel.innerHTML = '<option value="">Error</option>'; sel.disabled = false; });
        });

        // ── Alerta urgente ───────────────────────────────────
        document.querySelectorAll('input[name="origen"]').forEach(r => {
            r.addEventListener('change', () => {
                document.getElementById('alertaUrgente').style.display =
                    r.value === 'urgente' && r.checked ? '' : 'none';
            });
        });

        // ── Generador de fechas ──────────────────────────────
        const nombresDia = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
        const nombresMes = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];

        function generarFechas() {
            const desde     = document.getElementById('fechaDesde').value;
            const hasta     = document.getElementById('fechaHasta').value;
            const diasCk    = [...document.querySelectorAll('input[name="dias_semana[]"]:checked')]
                                 .map(c => parseInt(c.value));
            const lista     = document.getElementById('listaFechas');
            const inputs    = document.getElementById('inputsFechasOcultos');
            const preview   = document.getElementById('previewFechas');
            const sinFechas = document.getElementById('sinFechas');
            const btnEnviar = document.getElementById('btnEnviar');
            const btnTexto  = document.getElementById('btnTextoFechas');

            lista.innerHTML  = '';
            inputs.innerHTML = '';

            if (!desde || !hasta || !diasCk.length || desde > hasta) {
                preview.style.display   = 'none';
                sinFechas.style.display = 'none';
                btnEnviar.disabled      = true;
                btnTexto.textContent    = '— selecciona fechas';
                return;
            }

            const fechas = [];
            let cur = new Date(desde + 'T12:00:00');
            const fin = new Date(hasta + 'T12:00:00');

            while (cur <= fin) {
                if (diasCk.includes(cur.getDay())) fechas.push(new Date(cur));
                cur.setDate(cur.getDate() + 1);
            }

            document.getElementById('totalFechas').textContent = fechas.length;

            if (!fechas.length) {
                preview.style.display   = 'none';
                sinFechas.style.display = '';
                btnEnviar.disabled      = true;
                btnTexto.textContent    = '— sin fechas válidas';
                return;
            }

            sinFechas.style.display = 'none';
            preview.style.display   = '';

            fechas.forEach(f => {
                const ymd   = f.toISOString().slice(0, 10);
                const diaN  = nombresDia[f.getDay()];
                const mesN  = nombresMes[f.getMonth()];
                const label = `${diaN} ${f.getDate()} ${mesN}`;
                lista.innerHTML  += `<span class="badge bg-primary px-3 py-2" style="font-size:.85rem;">${label}</span>`;
                inputs.innerHTML += `<input type="hidden" name="fechas[]" value="${ymd}">`;
            });

            btnEnviar.disabled   = false;
            btnTexto.textContent = `— ${fechas.length} día(s)`;
        }

        document.getElementById('fechaDesde').addEventListener('change', generarFechas);
        document.getElementById('fechaHasta').addEventListener('change', generarFechas);
        document.querySelectorAll('input[name="dias_semana[]"]').forEach(c => c.addEventListener('change', generarFechas));
        generarFechas();

        // ── Copiar nombre_contacto_particular → nombre_contacto ──
        const formComanda = document.getElementById('formComanda');
        formComanda.addEventListener('submit', function (e) {
            // Copiar contacto si es particular
            const p = document.querySelector('[name="nombre_contacto_particular"]');
            if (p && p.value && document.getElementById('sol_particular').checked) {
                document.querySelector('[name="nombre_contacto"]').value = p.value;
            }

            // Mostrar estado de carga
            const btn = document.getElementById('btnEnviar');
            btn.disabled = true;
            btn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                PROCESANDO REGISTRO...
            `;
        });

        // ── Previsualización de archivos de respaldo ─────────────────────────
        const inputRespaldos = document.getElementById('respaldos');
        const fileCount      = document.getElementById('fileCount');
        const filePreview    = document.getElementById('filePreview');

        inputRespaldos.addEventListener('change', function() {
            filePreview.innerHTML = '';
            const files = this.files;
            
            if (files.length > 0) {
                fileCount.textContent = `${files.length} archivo(s) seleccionado(s)`;
                
                Array.from(files).forEach(file => {
                    const reader = new FileReader();
                    const card = document.createElement('div');
                    card.className = 'd-flex align-items-center p-2 border rounded-3 bg-white shadow-sm';
                    card.style.minWidth = '150px';
                    card.style.maxWidth = '250px';

                    const isImg = file.type.startsWith('image/');
                    
                    if (isImg) {
                        reader.onload = function(e) {
                            card.innerHTML = `
                                <img src="${e.target.result}" class="rounded me-2" style="width:40px; height:40px; object-fit:cover;">
                                <div class="text-truncate small flex-grow-1" title="${file.name}">${file.name}</div>
                            `;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        const icon = file.type === 'application/pdf' ? 'bi-file-pdf text-danger' : 'bi-file-earmark-text text-primary';
                        card.innerHTML = `
                            <div class="d-flex align-items-center justify-content-center bg-light rounded me-2" style="width:40px; height:40px;">
                                <i class="bi ${icon} fs-4"></i>
                            </div>
                            <div class="text-truncate small flex-grow-1" title="${file.name}">${file.name}</div>
                        `;
                    }
                    filePreview.appendChild(card);
                });
            } else {
                fileCount.textContent = 'Ningún archivo seleccionado';
            }
        });

    });
    </script>

</body>
</html>
