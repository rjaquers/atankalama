<!DOCTYPE html>
<html lang='es'>
<head>
    <?php include(ROOT_PATH . '../public/static/templates/head.php'); ?>
</head>
<body class='pro-body'>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

    <style>
        :root {
            --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
        }

        .segmented-control {
            display: inline-flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 12px;
            width: 100%;
        }

        .segmented-control .btn-check + .btn {
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            color: #64748b;
            font-weight: 600;
            font-size: 0.875rem;
            flex: 1;
            transition: transform 160ms var(--ease-out), background 160ms var(--ease-out), color 160ms var(--ease-out), box-shadow 160ms var(--ease-out);
            background: transparent;
        }

        .segmented-control .btn-check:checked + .btn {
            background: white;
            color: var(--color-cta);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .segmented-control .btn:active {
            transform: scale(0.97);
        }

        /* Polish for other buttons */
        .btn-pro-primary:active, .btn-pro-action:active, .btn-outline-primary:active, .day-check .btn:active {
            transform: scale(0.97);
        }

        .pro-card {
            transition: transform 200ms var(--ease-out), box-shadow 200ms var(--ease-out);
        }

        .form-control-lg, .form-select-lg {
            font-size: 1rem;
            padding: 0.6rem 1rem;
            border-radius: 10px;
        }

        .day-check .btn {
            border-radius: 8px;
            width: 38px;
            height: 38px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            transition: transform 160ms var(--ease-out);
        }
        
        .form-label.small {
            letter-spacing: 0.05em;
        }

        #camposEmpresa, #camposParticular {
            animation: slideUp 200ms var(--ease-out);
        }

        @keyframes slideUp {
            from { transform: translateY(10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>

    <div class='container py-4'>

        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom"
            style="border-color: var(--color-border) !important;">
            <h2 class="mb-0 fw-bold">
                <i class="bi bi-sun-fill me-2" style="color: var(--color-cta)"></i>Registrar Desayunos
            </h2>
            <a href="index.php?page=comanda/listado" class="btn btn-pro-action px-3" style="width:auto;">
                <i class="bi bi-list me-1"></i>Ver Listado de Comandas
            </a>
        </div>

        <?php if (isset($_GET['ok'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong><?= (int)$_GET['ok'] ?> desayuno(s)</strong> registrado(s) correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=comanda/guardarDesayuno" id="formDesayuno" enctype="multipart/form-data">

            <!-- SECCIÓN PRINCIPAL: Configuración del Servicio -->
            <div class="pro-card border-0 mb-4">
                <div class="card-body p-4">
                    <!-- Row 1: Rango de Fechas y Días -->
                    <div class="row g-3 mb-4 pb-4 border-bottom" style="border-color: #f1f5f9 !important;">
                        <div class="col-md-3">
                            <label class="form-label text-muted fw-bold small mb-2 d-block">DESDE</label>
                            <input type="date" class="form-control form-control-lg border-0 bg-light"
                                name="fecha_desde" id="fechaDesde"
                                value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted fw-bold small mb-2 d-block">HASTA</label>
                            <input type="date" class="form-control form-control-lg border-0 bg-light"
                                name="fecha_hasta" id="fechaHasta"
                                value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-bold small mb-2 d-block">DÍAS A INCLUIR</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <?php
                                $dias = ['L' => 'Lun', 'M' => 'Mar', 'X' => 'Mié', 'J' => 'Jue', 'V' => 'Vie', 'S' => 'Sáb', 'D' => 'Dom'];
                                $vals = [1, 2, 3, 4, 5, 6, 0];
                                $i = 0;
                                foreach ($dias as $key => $nombre):
                                    $checked = in_array($vals[$i], [1,2,3,4,5]) ? 'checked' : '';
                                ?>
                                <div class="form-check day-check p-0 m-0">
                                    <input class="btn-check" type="checkbox" name="dias_semana[]" value="<?= $vals[$i] ?>" id="dia_<?= $key ?>" <?= $checked ?>>
                                    <label class="btn btn-outline-primary" for="dia_<?= $key ?>"><?= $nombre ?></label>
                                </div>
                                <?php $i++; endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Hotel, Solicitante y Cantidad -->
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label text-muted fw-bold small mb-2 d-block">HOTEL</label>
                            <div class="segmented-control">
                                <input type="radio" class="btn-check" name="nombre_hotel" id="hotel_atan" value="Atankalama" checked>
                                <label class="btn" for="hotel_atan">Atankalama</label>
                                <input type="radio" class="btn-check" name="nombre_hotel" id="hotel_inn" value="Atankalama Inn">
                                <label class="btn" for="hotel_inn">Atankalama Inn</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-muted fw-bold small mb-2 d-block">TIPO SOLICITANTE</label>
                            <div class="segmented-control">
                                <input type="radio" class="btn-check" name="tipo_solicitante" id="sol_empresa" value="empresa" checked>
                                <label class="btn" for="sol_empresa"><i class="bi bi-building-fill me-1"></i>Empresa</label>
                                <input type="radio" class="btn-check" name="tipo_solicitante" id="sol_particular" value="particular">
                                <label class="btn" for="sol_particular"><i class="bi bi-person-fill me-1"></i>Particular</label>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label text-muted fw-bold small mb-2 d-block">PERSONAS</label>
                            <input type="number" class="form-control form-control-lg border-0 bg-light text-center fw-bold"
                                name="cantidad_personas" value="1" min="1" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label text-muted fw-bold small mb-2 d-block">CONSUMO</label>
                            <div class="segmented-control">
                                <input type="radio" class="btn-check" name="es_para_llevar" id="consumir_hotel" value="0" checked>
                                <label class="btn" for="consumir_hotel" title="En Comedor"><i class="bi bi-house-door"></i></label>
                                <input type="radio" class="btn-check" name="es_para_llevar" id="para_llevar" value="1">
                                <label class="btn" for="para_llevar" title="Para Llevar"><i class="bi bi-bag"></i></label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Row 3: Selección de Empresa o Nombre Particular (DINÁMICO) -->
                    <div class="row g-4 mt-2">
                        <div class="col-md-12">
                            <div id="dynamicSolicitanteFields" class="p-3 rounded-4" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                                 <!-- Campos empresa -->
                                 <div id="camposEmpresa" class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label text-muted fw-bold small mb-1 d-flex justify-content-between">
                                            <span>EMPRESA <span class="text-danger">*</span></span>
                                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalNuevaEmpresa">
                                                <i class="bi bi-plus-circle me-1"></i>Nueva
                                            </button>
                                        </label>
                                        <select class="form-select form-select-lg border-0 shadow-none" name="company_id" id="selectEmpresa">
                                            <option value="">— Selecciona —</option>
                                            <?php foreach ($empresasLista as $emp): ?>
                                                <option value="<?= $emp['id'] ?>" 
                                                    data-nombre="<?= htmlspecialchars($emp['business_name']) ?>" 
                                                    data-contacto="<?= htmlspecialchars($emp['contact_name'] ?? '') ?>">
                                                    <?= htmlspecialchars($emp['business_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-muted fw-bold small mb-1">NOMBRE EN COMANDA</label>
                                        <input type="text" class="form-control form-control-lg border-0 shadow-none" name="nombre_empresa" id="inputNombreEmpresa" placeholder="Ej: Ayca Día...">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-muted fw-bold small mb-1">CONTRATO</label>
                                        <select class="form-select form-select-lg border-0 shadow-none" name="contract_id" id="selectContrato" disabled>
                                            <option value="">— Elige empresa —</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="nombre_contacto" id="inputContacto">
                                 </div>
                                 
                                 <!-- Campos particular -->
                                 <div id="camposParticular" class="row g-3" style="display:none;">
                                    <div class="col-md-12">
                                        <label class="form-label text-muted fw-bold small mb-1">NOMBRE DEL HUÉSPED / TURISTA</label>
                                        <input type="text" class="form-control form-control-lg border-0 shadow-none" name="nombre_contacto_particular" placeholder="Nombre o referencia (opcional)">
                                    </div>
                                 </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview de fechas -->
                    <div id="previewFechas" class="mt-4 p-3 rounded-4 border-0" style="background: #f1f5f9; display:none;">
                        <div class="fw-bold text-muted small mb-2">
                            <i class="bi bi-calendar-check me-1 text-success"></i>
                            Fechas: <span id="totalFechas" class="text-success fw-bold">0</span>
                        </div>
                        <div id="listaFechas" class="d-flex flex-wrap gap-2"></div>
                    </div>
                    <div id="sinFechas" class="alert alert-warning mt-3 py-2 small" style="display:none;">
                        <i class="bi bi-exclamation-triangle me-1"></i> No hay fechas válidas.
                    </div>
                    <div id="inputsFechasOcultos"></div>
                </div>
            </div>

            <!-- SECCIÓN 2: Observaciones y Respaldos -->
            <div class="pro-card border-0 mb-4">
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-7">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label text-muted fw-bold small mb-0">OBSERVACIONES</label>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm py-0 px-2 btn-light border text-muted fw-bold" 
                                            style="font-size: 0.65rem; border-radius: 6px;" 
                                            onclick="addQuickObs('Turno Día')">+ Turno Día</button>
                                    <button type="button" class="btn btn-sm py-0 px-2 btn-light border text-muted fw-bold" 
                                            style="font-size: 0.65rem; border-radius: 6px;" 
                                            onclick="addQuickObs('Turno Noche')">+ Turno Noche</button>
                                </div>
                            </div>
                            <textarea class="form-control border-0 bg-light shadow-none"
                                name="observaciones" id="inputObservaciones" rows="2"
                                placeholder="Alergias, habitación, instrucciones especiales..."
                                style="resize:none; border-radius: 12px;"></textarea>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-muted fw-bold small mb-2 d-block">ARCHIVOS DE RESPALDO</label>
                            <div class="p-3 rounded-4 border bg-light bg-opacity-50 d-flex flex-column gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="btn btn-sm btn-outline-primary px-3 fw-bold" for="respaldos" style="cursor: pointer; border-radius: 8px;">
                                        <i class="bi bi-cloud-upload me-1"></i>Seleccionar
                                    </label>
                                    <input type="file" name="respaldos[]" id="respaldos" multiple hidden
                                           accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.msg,.eml">
                                    <span id="fileCount" class="text-muted small">Ninguno</span>
                                </div>
                                <div id="filePreview" class="mt-1 d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTÓN DE ACCIÓN -->
            <div class="mb-5">
                <button type="submit" id="btnEnviar"
                    class="btn btn-pro-primary w-100 py-3 fs-5 shadow-lg d-flex align-items-center justify-content-center"
                    style="border-radius: 16px;" disabled>
                    <i class="bi bi-floppy-fill me-2"></i>
                    REGISTRAR <span id="btnTextoFechas" class="ms-1">— selecciona fechas</span>
                </button>
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
            // Si ya tiene texto, añade una coma o espacio si no lo tiene
            if (current.endsWith(',') || current.endsWith('.')) {
                area.value = current + ' ' + text;
            } else {
                area.value = current + '. ' + text;
            }
        } else {
            area.value = text;
        }
        area.focus();
        
        // Efecto visual de feedback
        area.style.backgroundColor = '#e2e8f0';
        setTimeout(() => {
            area.style.backgroundColor = '#f8fafc';
        }, 150);
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
                    const selectEmpresa = document.getElementById('selectEmpresa');
                    const newOpt = new Option(res.business_name, res.id);
                    newOpt.dataset.nombre = res.business_name;
                    newOpt.dataset.contacto = res.contact_name || '';
                    selectEmpresa.add(newOpt);
                    selectEmpresa.value = res.id;
                    selectEmpresa.dispatchEvent(new Event('change'));
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

        document.querySelectorAll('input[name="tipo_solicitante"]').forEach(r => {
            r.addEventListener('change', function () {
                const esEmpresa = this.value === 'empresa';
                camposEmpresa.style.display    = esEmpresa ? '' : 'none';
                camposParticular.style.display = esEmpresa ? 'none' : '';
                selectEmpresa.required         = esEmpresa;
            });
        });

        // ── AJAX contratos ───────────────────────────────────
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

        // ── Generador de fechas ──────────────────────────────
        const nombresDia = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
        const nombresMes = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];

        function generarFechas() {
            const desde = document.getElementById('fechaDesde').value;
            const hasta = document.getElementById('fechaHasta').value;
            const diasCk = [...document.querySelectorAll('input[name="dias_semana[]"]:checked')]
                               .map(c => parseInt(c.value));

            const lista  = document.getElementById('listaFechas');
            const inputs = document.getElementById('inputsFechasOcultos');
            const preview = document.getElementById('previewFechas');
            const sinFechas = document.getElementById('sinFechas');
            const btnEnviar = document.getElementById('btnEnviar');
            const btnTexto  = document.getElementById('btnTextoFechas');

            lista.innerHTML  = '';
            inputs.innerHTML = '';

            if (!desde || !hasta || !diasCk.length || desde > hasta) {
                preview.style.display   = 'none';
                sinFechas.style.display = (!diasCk.length || (desde && hasta && desde <= hasta)) ? 'none' : '';
                btnEnviar.disabled = true;
                btnTexto.textContent = '— selecciona fechas';
                return;
            }

            const fechas = [];
            let cur = new Date(desde + 'T12:00:00');
            const fin = new Date(hasta + 'T12:00:00');

            while (cur <= fin) {
                if (diasCk.includes(cur.getDay())) {
                    fechas.push(new Date(cur));
                }
                cur.setDate(cur.getDate() + 1);
            }

            document.getElementById('totalFechas').textContent = fechas.length;

            if (!fechas.length) {
                preview.style.display   = 'none';
                sinFechas.style.display = '';
                btnEnviar.disabled = true;
                btnTexto.textContent = '— sin fechas válidas';
                return;
            }

            sinFechas.style.display = 'none';
            preview.style.display  = '';

            fechas.forEach(f => {
                const ymd  = f.toISOString().slice(0, 10);
                const diaN = nombresDia[f.getDay()];
                const mesN = nombresMes[f.getMonth()];
                const label = `${diaN} ${f.getDate()} ${mesN}`;

                lista.innerHTML += `<span class="badge bg-primary px-3 py-2" style="font-size:.85rem;">${label}</span>`;
                inputs.innerHTML += `<input type="hidden" name="fechas[]" value="${ymd}">`;
            });

            btnEnviar.disabled = false;
            btnTexto.textContent = `— ${fechas.length} día(s)`;
        }

        document.getElementById('fechaDesde').addEventListener('change', generarFechas);
        document.getElementById('fechaHasta').addEventListener('change', generarFechas);
        document.querySelectorAll('input[name="dias_semana[]"]').forEach(c => c.addEventListener('change', generarFechas));
        generarFechas();

        // ── Copiar contacto particular → nombre_contacto ─────
        const formDesayuno = document.getElementById('formDesayuno');
        formDesayuno.addEventListener('submit', function (e) {
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

        // ── Previsualización de respaldos ────────────────────────────────────
        const inputRespaldos = document.getElementById('respaldos');
        const fileCount      = document.getElementById('fileCount');
        const filePreview    = document.getElementById('filePreview');

        inputRespaldos.addEventListener('change', function() {
            filePreview.innerHTML = '';
            const files = this.files;
            
            if (files.length > 0) {
                fileCount.textContent = `${files.length} seleccionado(s)`;
                
                Array.from(files).forEach(file => {
                    const reader = new FileReader();
                    const card = document.createElement('div');
                    card.className = 'd-flex align-items-center p-2 border rounded-3 bg-white shadow-sm';
                    card.style.minWidth = '140px';
                    card.style.maxWidth = '200px';

                    const isImg = file.type.startsWith('image/');
                    
                    if (isImg) {
                        reader.onload = function(e) {
                            card.innerHTML = `
                                <img src="${e.target.result}" class="rounded me-2" style="width:32px; height:32px; object-fit:cover;">
                                <div class="text-truncate small flex-grow-1" title="${file.name}" style="font-size:0.7rem;">${file.name}</div>
                            `;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        const icon = file.type === 'application/pdf' ? 'bi-file-pdf text-danger' : 'bi-file-earmark-text text-primary';
                        card.innerHTML = `
                            <div class="d-flex align-items-center justify-content-center bg-light rounded me-2" style="width:32px; height:32px;">
                                <i class="bi ${icon} fs-5"></i>
                            </div>
                            <div class="text-truncate small flex-grow-1" title="${file.name}" style="font-size:0.7rem;">${file.name}</div>
                        `;
                    }
                    filePreview.appendChild(card);
                });
            } else {
                fileCount.textContent = 'Ninguno seleccionado';
            }
        });

    });
    </script>

</body>
</html>
