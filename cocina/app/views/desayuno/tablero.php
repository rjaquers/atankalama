<!DOCTYPE html>
<html lang='es'>
<head>
    <?php $pageTitle = 'Desayuno Masivo'; include(ROOT_PATH . '../public/static/templates/head.php'); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2/dist/css/tom-select.bootstrap5.min.css">
    <style>
        .ts-wrapper.single .ts-control { border-radius: 12px; border: 0; background: #f1f5f9; padding: 0.6rem 1rem; font-size: 1rem; }
        .ts-wrapper.single.input-active .ts-control { background: #fff; box-shadow: 0 0 0 3px rgba(13,110,253,.15); }
        .ts-dropdown { border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.12); border: 1px solid #e2e8f0; }
        .ts-dropdown .option { padding: .55rem 1rem; }
        .ts-dropdown .option:hover, .ts-dropdown .option.active { background: #eff6ff; color: #1d4ed8; }
        .cantidad-input { font-size: 1.1rem; font-weight: 700; }
        .tabla-masivo thead th { font-size: .7rem; letter-spacing: .05em; text-transform: uppercase; font-weight: 600; color: #64748b; border-bottom: 2px solid #e2e8f0; }
        .tabla-masivo td { vertical-align: middle; font-size: .88rem; }
        .btn-eliminar-fila { width: 28px; height: 28px; padding: 0; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; }
        .fila-desayuno { animation: fadeIn 180ms ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
        .hotel-badge { font-size: .75rem; padding: .3rem .8rem; border-radius: 8px; font-weight: 600; }
    </style>
</head>
<body class='pro-body'>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

    <div class='container-fluid px-4 py-4' style="max-width: 1200px;">

        <!-- Encabezado + formulario de navegación -->
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-3 border-bottom"
             style="border-color: var(--color-border) !important;">
            <h2 class="mb-0 fw-bold">
                <i class="bi bi-table me-2" style="color: var(--color-cta)"></i>Desayuno Masivo
            </h2>

            <form method="GET" action="index.php" id="formNavegacion" class="d-flex flex-wrap align-items-center gap-2">
                <input type="hidden" name="page" value="desayuno/tablero">

                <!-- Selector de fecha -->
                <div class="d-flex align-items-center gap-2 bg-light rounded-3 px-3 py-2">
                    <i class="bi bi-calendar3 text-primary"></i>
                    <input type="date" name="fecha" id="inputFecha"
                           value="<?= htmlspecialchars($fecha) ?>"
                           class="form-control border-0 bg-transparent shadow-none p-0"
                           style="max-width: 160px;">
                </div>

                <!-- Toggle hotel -->
                <div class="d-flex align-items-center gap-1 bg-light rounded-3 p-1">
                    <?php foreach (['Atankalama', 'Atankalama Inn'] as $h): ?>
                    <input type="radio" class="btn-check" name="hotel" id="hotel_<?= md5($h) ?>"
                           value="<?= htmlspecialchars($h) ?>" <?= $hotel === $h ? 'checked' : '' ?>>
                    <label class="btn btn-sm py-1 px-3 border-0 fw-semibold"
                           for="hotel_<?= md5($h) ?>"
                           style="border-radius: 8px; color: <?= $hotel === $h ? 'var(--color-cta)' : '#64748b' ?>;">
                        <?= htmlspecialchars($h) ?>
                    </label>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-arrow-right-circle me-1"></i>Ver
                </button>
            </form>
        </div>

        <!-- Alerta de éxito -->
        <?php if (isset($_GET['ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>
                <strong><?= (int)$_GET['ok'] ?> empresa(s)</strong> registradas para el
                <strong><?= date('d/m/Y', strtotime($fecha)) ?></strong>
                en <strong><?= htmlspecialchars($hotel) ?></strong>.
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= $_GET['error'] === 'fecha_invalida' ? 'Fecha no válida.' : 'Ocurrió un error.' ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Info de contexto -->
        <div class="d-flex align-items-center gap-3 mb-3">
            <span class="hotel-badge bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-building me-1"></i><?= htmlspecialchars($hotel) ?>
            </span>
            <span class="hotel-badge bg-secondary bg-opacity-10 text-secondary">
                <i class="bi bi-calendar-event me-1"></i>
                <?php
                $diasNombre = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
                $mesesNombre = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
                $ts = strtotime($fecha);
                echo $diasNombre[date('w',$ts)] . ' ' . date('j',$ts) . ' de ' . $mesesNombre[(int)date('n',$ts)-1] . ' de ' . date('Y',$ts);
                ?>
            </span>
        </div>

        <!-- Card principal -->
        <div class="pro-card border-0">
            <div class="card-body p-4">

                <!-- Selector para agregar empresa -->
                <div class="mb-4">
                    <label class="form-label text-muted fw-bold small mb-2 d-block">
                        <i class="bi bi-plus-circle me-1 text-success"></i>AGREGAR EMPRESA
                    </label>
                    <select id="selectAgregar" placeholder="Buscar empresa..."></select>
                    <div class="form-text text-muted mt-1" style="font-size:.8rem;">
                        Selecciona una empresa para agregarla a la lista de desayunos de este día.
                    </div>
                </div>

                <!-- Formulario principal -->
                <form method="POST" action="index.php?page=desayuno/guardar" id="formDesayuno">
                    <input type="hidden" name="fecha" value="<?= htmlspecialchars($fecha) ?>">
                    <input type="hidden" name="nombre_hotel" value="<?= htmlspecialchars($hotel) ?>">

                    <!-- Tabla de registros -->
                    <div class="table-responsive">
                        <table class="table tabla-masivo mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3" style="width:35%;">Empresa</th>
                                    <th style="width:22%;">Proyecto</th>
                                    <th class="text-center" style="width:110px;">Cantidad</th>
                                    <th>Observación</th>
                                    <th style="width:40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDesayunos">
                                <!-- filas dinámicas via JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Estado vacío -->
                    <div id="rowVacio" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 2.5rem; opacity: .3;"></i>
                        <p class="mt-3 mb-0 fw-semibold">Sin empresas agregadas</p>
                        <p class="small mt-1">Usa el selector de arriba para registrar desayunos.</p>
                    </div>

                    <!-- Pie: total + botón -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top"
                         style="border-color: #e2e8f0 !important;">
                        <div class="d-flex align-items-center gap-3">
                            <span class="text-muted fw-semibold">TOTAL DESAYUNOS:</span>
                            <span id="totalPersonas"
                                  class="badge bg-dark px-4 py-2 fs-5 shadow-sm"
                                  style="border-radius: 12px; min-width: 60px; text-align: center;">0</span>
                            <small class="text-muted">PAX</small>
                        </div>
                        <button type="submit" id="btnGuardar"
                                class="btn btn-pro-primary px-5 py-2 fs-6"
                                style="border-radius: 12px;" disabled>
                            <i class="bi bi-floppy-fill me-2"></i>GUARDAR TODOS
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>

    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2/dist/js/tom-select.complete.min.js"></script>
    <script>
    // ── Datos desde PHP ──────────────────────────────────────────────────────────
    const empresas           = <?= json_encode(array_values($empresas)) ?>;
    const registros          = <?= json_encode(array_values($registros)) ?>;
    const proyectosPrecarg   = <?= json_encode($proyectosPorEmpresa) ?>;

    // ── Estado ───────────────────────────────────────────────────────────────────
    let rowCounter   = 0;
    const selectedIds = new Set();
    let ts; // Tom Select instance
    let formDirty = false;

    // ── Helpers ──────────────────────────────────────────────────────────────────
    function esc(str) {
        const d = document.createElement('div');
        d.textContent = String(str ?? '');
        return d.innerHTML;
    }

    function buildProjectOptions(proyectos, selectedId) {
        let html = '<option value="">— Sin proyecto —</option>';
        (proyectos || []).forEach(p => {
            const sel = String(p.id) === String(selectedId) ? ' selected' : '';
            html += `<option value="${p.id}"${sel}>${esc(p.name)}</option>`;
        });
        return html;
    }

    // ── Agregar fila ─────────────────────────────────────────────────────────────
    function addRow(companyId, companyName, projectId, projectName, cantidad, observaciones, preloaded) {
        const cid = String(companyId);
        if (selectedIds.has(cid)) return;

        const idx = rowCounter++;
        selectedIds.add(cid);

        const proyectos     = proyectosPrecarg[companyId] || [];
        const tieneProyectos = proyectos.length > 0;

        let celdaProyecto;
        if (tieneProyectos) {
            celdaProyecto = `
                <input type="hidden" name="filas[${idx}][project_id]" id="hpid_${idx}" value="${projectId || ''}">
                <select class="form-select form-select-sm border-0 shadow-none"
                        style="min-width:150px;"
                        onchange="document.getElementById('hpid_${idx}').value=this.value; markDirty();">
                    ${buildProjectOptions(proyectos, projectId)}
                </select>`;
        } else if (!preloaded) {
            // Nueva fila: cargar proyectos via AJAX
            celdaProyecto = `
                <input type="hidden" name="filas[${idx}][project_id]" id="hpid_${idx}" value="">
                <span id="proj_label_${idx}" class="text-muted small">—</span>
                <span id="proj_spin_${idx}" class="spinner-border spinner-border-sm text-muted ms-1"></span>`;
        } else {
            celdaProyecto = `
                <input type="hidden" name="filas[${idx}][project_id]" id="hpid_${idx}" value="">
                <span class="text-muted small">—</span>`;
        }

        const tr = document.createElement('tr');
        tr.className = 'fila-desayuno';
        tr.dataset.companyId = cid;
        tr.innerHTML = `
            <td class="ps-3 fw-semibold">
                ${esc(companyName)}
                <input type="hidden" name="filas[${idx}][company_id]" value="${companyId}">
            </td>
            <td>${celdaProyecto}</td>
            <td class="text-center">
                <input type="number" name="filas[${idx}][cantidad]"
                       value="${parseInt(cantidad) || 1}" min="1"
                       class="form-control form-control-sm text-center cantidad-input border-0 bg-light"
                       style="width:76px; border-radius:8px;"
                       oninput="updateTotal(); markDirty();">
            </td>
            <td>
                <input type="text" name="filas[${idx}][observaciones]"
                       value="${esc(observaciones || '')}"
                       class="form-control form-control-sm border-0 bg-light shadow-none"
                       style="border-radius:8px;"
                       placeholder="Obs..."
                       oninput="markDirty();">
            </td>
            <td class="text-center pe-2">
                <button type="button"
                        class="btn btn-sm btn-outline-danger btn-eliminar-fila"
                        onclick="removeRow(this, '${cid}', ${JSON.stringify(companyName)})"
                        title="Quitar empresa">
                    <i class="bi bi-x-lg" style="font-size:.65rem;"></i>
                </button>
            </td>`;

        document.getElementById('tbodyDesayunos').appendChild(tr);

        // Cargar proyectos via AJAX sólo para filas nuevas sin precargar
        if (!preloaded && !tieneProyectos) {
            loadProjectsAjax(companyId, idx);
        }

        updateTotal();
        updateEmptyState();
        markDirty();
    }

    // ── Cargar proyectos via AJAX ─────────────────────────────────────────────────
    function loadProjectsAjax(companyId, idx) {
        fetch(`index.php?page=desayuno/proyectosAjax&company_id=${companyId}`)
            .then(r => r.json())
            .then(proyectos => {
                proyectosPrecarg[companyId] = proyectos;

                const spin = document.getElementById(`proj_spin_${idx}`);
                if (spin) spin.remove();

                if (proyectos.length === 0) return;

                const label = document.getElementById(`proj_label_${idx}`);
                if (!label) return;

                const cell = label.closest('td');
                cell.innerHTML = `
                    <input type="hidden" name="filas[${idx}][project_id]" id="hpid_${idx}" value="">
                    <select class="form-select form-select-sm border-0 shadow-none"
                            style="min-width:150px;"
                            onchange="document.getElementById('hpid_${idx}').value=this.value; markDirty();">
                        ${buildProjectOptions(proyectos, null)}
                    </select>`;
            })
            .catch(() => {
                const spin = document.getElementById(`proj_spin_${idx}`);
                if (spin) spin.remove();
            });
    }

    // ── Quitar fila ───────────────────────────────────────────────────────────────
    function removeRow(btn, companyId, companyName) {
        btn.closest('tr').remove();
        selectedIds.delete(companyId);

        ts.addOption({ value: companyId, text: companyName });
        ts.refreshOptions(false);

        updateTotal();
        updateEmptyState();
        markDirty();
    }

    // ── Actualizar total ──────────────────────────────────────────────────────────
    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.cantidad-input').forEach(i => { total += parseInt(i.value) || 0; });
        document.getElementById('totalPersonas').textContent = total;
        document.getElementById('btnGuardar').disabled =
            document.querySelectorAll('#tbodyDesayunos tr').length === 0;
    }

    function updateEmptyState() {
        const hayFilas = document.querySelectorAll('#tbodyDesayunos tr').length > 0;
        document.getElementById('rowVacio').style.display = hayFilas ? 'none' : '';
    }

    function markDirty() { formDirty = true; }

    // ── Init ─────────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        const selectedCompanies = new Set(registros.map(r => String(r.company_id)));
        const opcionesDisponibles = empresas
            .filter(e => !selectedCompanies.has(String(e.id)))
            .map(e => ({ value: String(e.id), text: e.business_name }));

        ts = new TomSelect('#selectAgregar', {
            options: opcionesDisponibles,
            valueField: 'value',
            labelField: 'text',
            searchField: ['text'],
            placeholder: 'Buscar empresa...',
            create: false,
            allowEmptyOption: false,
            onItemAdd(value) {
                const emp = empresas.find(e => String(e.id) === value);
                if (emp) addRow(parseInt(value), emp.business_name, null, null, 1, '', false);
                this.removeOption(value);
                this.clear(true);
                this.blur();
            }
        });

        // Cargar registros existentes
        registros.forEach(r => {
            addRow(
                parseInt(r.company_id),
                r.nombre_empresa,
                r.project_id ? parseInt(r.project_id) : null,
                r.nombre_proyecto || null,
                parseInt(r.cantidad),
                r.observaciones || '',
                true
            );
        });

        updateTotal();
        updateEmptyState();

        // Limpiar dirty después de la carga inicial
        setTimeout(() => { formDirty = false; }, 100);

        // Advertir al navegar con cambios sin guardar
        document.getElementById('formNavegacion').addEventListener('submit', e => {
            if (formDirty && document.querySelectorAll('#tbodyDesayunos tr').length > 0) {
                if (!confirm('Hay cambios sin guardar. ¿Continuar sin guardar?')) {
                    e.preventDefault();
                }
            }
        });

        // Auto-submit del formulario de navegación al cambiar fecha o hotel
        document.getElementById('inputFecha').addEventListener('change', function() {
            if (formDirty && document.querySelectorAll('#tbodyDesayunos tr').length > 0) {
                if (!confirm('Hay cambios sin guardar. ¿Continuar?')) return;
            }
            document.getElementById('formNavegacion').submit();
        });
        document.querySelectorAll('#formNavegacion input[name="hotel"]').forEach(r => {
            r.addEventListener('change', function() {
                if (formDirty && document.querySelectorAll('#tbodyDesayunos tr').length > 0) {
                    if (!confirm('Hay cambios sin guardar. ¿Continuar?')) return;
                }
                document.getElementById('formNavegacion').submit();
            });
        });

        // Limpiar dirty al enviar el formulario principal
        document.getElementById('formDesayuno').addEventListener('submit', () => {
            formDirty = false;
            const btn = document.getElementById('btnGuardar');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>GUARDANDO...';
        });
    });

    window.addEventListener('beforeunload', e => {
        if (formDirty) { e.preventDefault(); e.returnValue = ''; }
    });
    </script>
</body>
</html>
