<div class="container-fluid">

<!-- Toast de notificación -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
    <div id="checklistToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2500">
        <div class="d-flex">
            <div class="toast-body fs-6" id="toastMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    </div>
</div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Editar Checklist</h2>
        <a href="<?= BASE_URL ?>/checklists" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i> Volver
        </a>
    </div>

    <form id="editChecklistForm">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-semibold">Nombre del Checklist</label>
                        <input type="text" id="nombre" readonly class="form-control form-control-lg"
                            value="<?= htmlspecialchars($checklist['nombre']) ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Área / Departamento</label>
                        <select id="area" class="form-select form-select-lg" required>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= htmlspecialchars($area['nombre']) ?>"
                                    <?= $checklist['area'] == $area['nombre'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($area['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1 mb-3">
                        <label class="form-label fw-semibold">Hotel</label>
                        <select id="hotel" class="form-select form-select-lg" required>
                            <option value="Atankalama" <?= ($checklist['hotel'] ?? 'Atankalama') === 'Atankalama' ? 'selected' : '' ?>>Atankalama</option>
                            <option value="Atankalama Inn" <?= ($checklist['hotel'] ?? '') === 'Atankalama Inn' ? 'selected' : '' ?>>Atankalama Inn</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Tipo de acceso</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="modo" id="modo_cerrado" value="cerrado"
                                    <?= ($checklist['modo'] ?? 'cerrado') === 'cerrado' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="modo_cerrado">
                                    <i class="bi bi-lock-fill text-secondary me-1"></i> Cerrado <span class="text-muted small">(uso interno, requiere login)</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="modo" id="modo_abierto" value="abierto"
                                    <?= ($checklist['modo'] ?? 'cerrado') === 'abierto' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="modo_abierto">
                                    <i class="bi bi-qr-code text-primary me-1"></i> Abierto <span class="text-muted small">(encuesta pública con QR)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($checklist['token_publico'])): ?>
        <div class="card shadow-sm border-0 mb-4 border-start border-4 border-primary" id="qrPanel">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-qr-code fs-3 text-primary me-3"></i>
                    <div>
                        <h5 class="mb-0 fw-bold">Encuesta Pública</h5>
                        <small class="text-muted">Comparte este QR para que cualquier persona pueda responder sin login</small>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-4 text-center mb-3 mb-md-0">
                        <div id="qrcode" class="d-inline-block p-2 bg-white border rounded"></div>
                        <div class="mt-2">
                            <button type="button" onclick="descargarQR()" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i> Descargar QR
                            </button>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small text-muted text-uppercase fw-bold">URL de la encuesta</label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm bg-light" id="urlEncuesta"
                                value="<?= BASE_URL ?>/encuesta/<?= htmlspecialchars($checklist['token_publico']) ?>" readonly>
                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copiarUrl()">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <div class="mt-3 p-3 bg-warning-subtle rounded">
                            <small><i class="bi bi-info-circle me-1"></i>Si cambias el modo a <strong>Cerrado</strong> y guardas, este link y QR quedarán desactivados.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php elseif (($checklist['modo'] ?? 'cerrado') === 'abierto'): ?>
        <div class="alert alert-info d-flex align-items-center mb-4">
            <i class="bi bi-info-circle-fill me-2"></i>
            <span>Guarda los cambios para generar el código QR de esta encuesta.</span>
        </div>
        <?php endif; ?>

        <h4 class="mb-3 fw-bold">Preguntas de la Auditoría</h4>
        <div id="preguntasContainer">
            <?php foreach ($checklist['preguntas'] as $index => $pregunta): ?>
                <div class="card shadow-sm border-0 mb-3 pregunta-item" data-id="<?= $index ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3 align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="drag-handle me-3 cur-move" style="cursor: move;">
                                    <i class="bi bi-grip-vertical fs-4 text-muted"></i>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary me-2">#<?= $index + 1 ?></span>
                                <div class="input-group input-group-sm" style="width: 80px;">
                                    <span class="input-group-text bg-light border-0 small text-muted">Pos.</span>
                                    <input type="number" class="form-control form-control-sm p-orden-input" 
                                           value="<?= $index + 1 ?>" 
                                           onchange="changePosition(this)"
                                           min="1" 
                                           title="Escribe la posición para mover rápidamente">
                                </div>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary move-up me-1" title="Subir"
                                    onclick="moveUp(this)"><i class="bi bi-arrow-up"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-secondary move-down me-2" title="Bajar"
                                    onclick="moveDown(this)"><i class="bi bi-arrow-down"></i></button>
                                <button type="button" class="btn btn-outline-danger btn-sm border-0 remove-question"
                                    onclick="removeQuestion(this)"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-12 mb-2">
                                <label class="form-label small text-muted">Grupo / Sección (Opcional)</label>
                                <input type="text" class="form-control p-grupo"
                                    value="<?= htmlspecialchars($pregunta['grupo'] ?? '') ?>"
                                    placeholder="Ej: Habitación, Baño, etc.">
                            </div>
                        </div>
                        <div class="row align-items-end">
                            <div class="col-md-8 mb-2">
                                <label class="form-label small text-muted">Texto de la Pregunta</label>
                                <input type="text" class="form-control p-text"
                                    value="<?= htmlspecialchars($pregunta['pregunta']) ?>" required>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label small text-muted">Tipo de Respuesta</label>
                                <select class="form-select p-tipo">
                                    <option value="boolean" <?= $pregunta['tipo_respuesta'] == 'boolean' ? 'selected' : '' ?>>
                                        Si / No / N/A</option>
                                    <option value="numeric_scale" <?= $pregunta['tipo_respuesta'] == 'numeric_scale' ? 'selected' : '' ?>>
                                        Numérica (Puntaje)</option>
                                    <option value="text" <?= $pregunta['tipo_respuesta'] == 'text' ? 'selected' : '' ?>>Abierta
                                        (Texto)</option>
                                    <option value="foto" <?= $pregunta['tipo_respuesta'] == 'foto' ? 'selected' : '' ?>>
                                        Fotografía (Evidencia)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn btn-outline-primary mb-5" onclick="addQuestion()">
            <i class="bi bi-plus-lg me-2"></i> Añadir Pregunta
        </button>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
            <button type="button" class="btn btn-primary btn-lg px-5" onclick="saveChecklist()">
                <i class="bi bi-save me-2"></i> Guardar Cambios
            </button>
        </div>
    </form>
</div>

<script>
    function addQuestion() {
        const container = document.getElementById('preguntasContainer');
        const index = container.querySelectorAll('.pregunta-item').length;
        const div = document.createElement('div');
        div.className = 'card shadow-sm border-0 mb-3 pregunta-item';
        div.innerHTML = `
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3 align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="drag-handle me-3 cur-move" style="cursor: move;">
                            <i class="bi bi-grip-vertical fs-4 text-muted"></i>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary me-2">#${index + 1}</span>
                        <div class="input-group input-group-sm" style="width: 80px;">
                            <span class="input-group-text bg-light border-0 small text-muted">Pos.</span>
                            <input type="number" class="form-control form-control-sm p-orden-input" 
                                   value="${index + 1}" 
                                   onchange="changePosition(this)"
                                   min="1">
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary move-up me-1" title="Subir" onclick="moveUp(this)"><i class="bi bi-arrow-up"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-secondary move-down me-2" title="Bajar" onclick="moveDown(this)"><i class="bi bi-arrow-down"></i></button>
                        <button type="button" class="btn btn-outline-danger btn-sm border-0 remove-question" onclick="removeQuestion(this)"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-12 mb-2">
                        <label class="form-label small text-muted">Grupo / Sección (Opcional)</label>
                        <input type="text" class="form-control p-grupo" placeholder="Ej: Habitación, Baño, etc.">
                    </div>
                </div>
                <div class="row align-items-end">
                    <div class="col-md-8 mb-2">
                        <label class="form-label small text-muted">Texto de la Pregunta</label>
                        <input type="text" class="form-control p-text" placeholder="Ej: ¿Está limpia la habitación?" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label small text-muted">Tipo de Respuesta</label>
                        <select class="form-select p-tipo">
                            <option value="boolean">Si / No / N/A</option>
                            <option value="numeric_scale">Numérica (Puntaje)</option>
                            <option value="text">Abierta (Texto)</option>
                            <option value="foto">Fotografía (Evidencia)</option>
                        </select>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(div);
    }

    function removeQuestion(btn) {
        btn.closest('.pregunta-item').remove();
        updateAllNumbers();
    }

    function moveUp(btn) {
        const item = btn.closest('.pregunta-item');
        const prev = item.previousElementSibling;
        if (prev && prev.classList.contains('pregunta-item')) {
            item.parentNode.insertBefore(item, prev);
            updateAllNumbers();
        }
    }

    function moveDown(btn) {
        const item = btn.closest('.pregunta-item');
        const next = item.nextElementSibling;
        if (next && next.classList.contains('pregunta-item')) {
            item.parentNode.insertBefore(next, item);
            updateAllNumbers();
        }
    }

    function updateAllNumbers() {
        const items = document.querySelectorAll('.pregunta-item');
        items.forEach((item, index) => {
            const num = index + 1;
            const badge = item.querySelector('.badge');
            if (badge) badge.textContent = `#${num}`;
            const input = item.querySelector('.p-orden-input');
            if (input) input.value = num;
        });
    }

    function changePosition(input) {
        const item = input.closest('.pregunta-item');
        const container = document.getElementById('preguntasContainer');
        const items = Array.from(container.querySelectorAll('.pregunta-item'));
        let newPos = parseInt(input.value) - 1;
        
        if (newPos < 0) newPos = 0;
        if (newPos >= items.length) newPos = items.length - 1;
        
        const currentPos = items.indexOf(item);
        if (newPos === currentPos) return;

        if (newPos === 0) {
            container.insertBefore(item, container.firstChild);
        } else if (newPos >= items.length - 1) {
            container.appendChild(item);
        } else {
            const referenceNode = (newPos > currentPos) ? items[newPos].nextElementSibling : items[newPos];
            container.insertBefore(item, referenceNode);
        }
        updateAllNumbers();
    }

    function showToast(mensaje, tipo = 'success') {
        const toastEl = document.getElementById('checklistToast');
        const toastMsg = document.getElementById('toastMsg');
        toastEl.className = 'toast align-items-center border-0 text-white bg-' + (tipo === 'success' ? 'success' : 'danger');
        toastMsg.textContent = mensaje;
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }

    async function saveChecklist() {
        const nombre = document.getElementById('nombre').value;
        const area = document.getElementById('area').value;
        const hotel = document.getElementById('hotel').value;
        const modo = document.querySelector('input[name="modo"]:checked').value;
        const items = document.querySelectorAll('.pregunta-item');

        const preguntas = Array.from(items).map(item => ({
            texto: item.querySelector('.p-text').value,
            tipo: item.querySelector('.p-tipo').value,
            grupo: item.querySelector('.p-grupo').value
        }));

        if (!nombre || !area || preguntas.length === 0) {
            showToast('Por favor complete todos los datos', 'danger');
            return;
        }

        try {
            const formData = new URLSearchParams();
            formData.append('nombre', nombre);
            formData.append('area', area);
            formData.append('hotel', hotel);
            formData.append('modo', modo);
            preguntas.forEach((p, i) => {
                formData.append(`preguntas[${i}][texto]`, p.texto);
                formData.append(`preguntas[${i}][tipo]`, p.tipo);
                formData.append(`preguntas[${i}][grupo]`, p.grupo);
            });

            const res = await fetch('<?= BASE_URL ?>/api/checklists/actualizar/<?= $checklist['id'] ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            });

            const data = await res.json();
            if (res.ok) {
                showToast('✅ Checklist actualizado con éxito');
                setTimeout(() => {
                    window.location.href = data.redirect || '<?= BASE_URL ?>/checklists';
                }, 1500);
            } else {
                showToast(data.error || 'Error al guardar', 'danger');
            }
        } catch (err) {
            console.error(err);
            showToast('Error al conectar con el servidor', 'danger');
        }
    }

    function copiarUrl() {
        const input = document.getElementById('urlEncuesta');
        if (!input) return;
        navigator.clipboard.writeText(input.value).then(() => {
            showToast('URL copiada al portapapeles');
        });
    }

    function descargarQR() {
        const canvas = document.querySelector('#qrcode canvas');
        if (!canvas) return;
        const link = document.createElement('a');
        link.download = 'qr-encuesta.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }
</script>

<?php if (!empty($checklist['token_publico'])): ?>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new QRCode(document.getElementById('qrcode'), {
            text: '<?= BASE_URL ?>/encuesta/<?= htmlspecialchars($checklist['token_publico']) ?>',
            width: 180,
            height: 180,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    });
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('preguntasContainer');
        Sortable.create(el, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'bg-light',
            onEnd: function () {
                updateAllNumbers();
            },
        });
    });
</script>