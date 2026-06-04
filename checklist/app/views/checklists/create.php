<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center mb-4">
            <a href="<?= BASE_URL ?>/checklists" class="btn btn-link text-decoration-none ps-0">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <h2 class="mb-0 ms-2">Crear Nuevo Checklist</h2>
        </div>

        <form id="checklistForm">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">Información General</h5>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nombre del Checklist</label>
                            <input type="text" name="nombre" class="form-control"
                                placeholder="Ej: Auditoría de Limpieza Habitación" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Área / Departamento</label>
                            <select name="area" class="form-select" required>
                                <option value="">Seleccionar área...</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?= htmlspecialchars($area['nombre']) ?>">
                                        <?= htmlspecialchars($area['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-1 mb-3">
                            <label class="form-label">Hotel</label>
                            <select name="hotel" class="form-select" required>
                                <option value="Atankalama">Atankalama</option>
                                <option value="Atankalama Inn">Atankalama Inn</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label">Tipo de acceso</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="modo" id="modo_cerrado" value="cerrado" checked>
                                    <label class="form-check-label" for="modo_cerrado">
                                        <i class="bi bi-lock-fill text-secondary me-1"></i> Cerrado <span class="text-muted small">(uso interno, requiere login)</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="modo" id="modo_abierto" value="abierto">
                                    <label class="form-check-label" for="modo_abierto">
                                        <i class="bi bi-qr-code text-primary me-1"></i> Abierto <span class="text-muted small">(encuesta pública con QR)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Preguntas del Checklist</h5>
                <button type="button" id="addQuestion" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Añadir Pregunta
                </button>
            </div>

            <div id="questionsContainer">
                <!-- Preguntas dinámicas aquí -->
            </div>

            <div class="text-end mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-5">
                    Guardar Checklist
                </button>
            </div>
        </form>
    </div>
</div>

<template id="questionTemplate">
    <div class="question-card card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3 align-items-center">
                <div class="d-flex align-items-center">
                    <div class="drag-handle me-3" style="cursor: move;">
                        <i class="bi bi-grip-vertical fs-4 text-muted"></i>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary me-2 question-number">Pregunta #1</span>
                    <div class="input-group input-group-sm" style="width: 80px;">
                        <span class="input-group-text bg-light border-0 small text-muted">Pos.</span>
                        <input type="number" class="form-control form-control-sm p-orden-input" 
                               value="1" 
                               onchange="changePosition(this)"
                               min="1">
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-secondary move-up me-1" title="Subir"><i
                            class="bi bi-arrow-up"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary move-down me-2" title="Bajar"><i
                            class="bi bi-arrow-down"></i></button>
                    <button type="button" class="btn-close btn-sm remove-question"></button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Grupo / Sección (Opcional)</label>
                    <input type="text" name="preguntas[][grupo]" class="form-control"
                        placeholder="Ej: Habitación, Baño, etc.">
                </div>
            </div>
            <div class="row">
                <div class="col-md-7 mb-3">
                    <label class="form-label">Pregunta</label>
                    <input type="text" name="preguntas[][texto]" class="form-control"
                        placeholder="Describa que se debe evaluar" required>
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">Tipo de Respuesta</label>
                    <select name="preguntas[][tipo]" class="form-select type-selector" required>
                        <option value="boolean">Si / No / N/A</option>
                        <option value="numeric_scale">Escala Numérica</option>
                        <option value="text">Campo de Texto</option>
                        <option value="foto">Fotografía (Evidencia)</option>
                    </select>
                </div>
            </div>
            <div class="numeric-options d-none mt-2 bg-light p-3 rounded">
                <div class="row">
                    <div class="col-6">
                        <label class="form-label small">Valor Mínimo</label>
                        <input type="number" name="preguntas[][min]" class="form-control form-control-sm" value="1">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Valor Máximo</label>
                        <input type="number" name="preguntas[][max]" class="form-control form-control-sm" value="5">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('questionsContainer');
        const addButton = document.getElementById('addQuestion');
        const template = document.getElementById('questionTemplate');
        const form = document.getElementById('checklistForm');

        function addQuestion() {
            const index = container.children.length;
            const clone = template.content.cloneNode(true);
            const card = clone.querySelector('.question-card');

            const num = index + 1;
            card.querySelector('.question-number').textContent = `Pregunta #${num}`;
            card.querySelector('.p-orden-input').value = num;

            // Ajustar names para PHP indexado
            card.querySelectorAll('[name]').forEach(input => {
                const name = input.getAttribute('name');
                input.setAttribute('name', name.replace('[]', `[${index}]`));
            });

            // Toggle opciones numéricas
            const selector = card.querySelector('.type-selector');
            const numericOptions = card.querySelector('.numeric-options');
            selector.addEventListener('change', function () {
                if (this.value === 'numeric_scale') {
                    numericOptions.classList.remove('d-none');
                } else {
                    numericOptions.classList.add('d-none');
                }
            });

            // Botón eliminar
            card.querySelector('.remove-question').addEventListener('click', function () {
                card.remove();
                updateIndices();
            });

            // Botones subir/bajar
            card.querySelector('.move-up').addEventListener('click', function () {
                const prev = card.previousElementSibling;
                if (prev) {
                    container.insertBefore(card, prev);
                    updateIndices();
                }
            });

            card.querySelector('.move-down').addEventListener('click', function () {
                const next = card.nextElementSibling;
                if (next) {
                    container.insertBefore(next, card);
                    updateIndices();
                }
            });

            container.appendChild(clone);
        }

        function updateIndices() {
            Array.from(container.children).forEach((card, index) => {
                const num = index + 1;
                card.querySelector('.question-number').textContent = `Pregunta #${num}`;
                const ordenInput = card.querySelector('.p-orden-input');
                if (ordenInput) ordenInput.value = num;
                
                card.querySelectorAll('[name]').forEach(input => {
                    const name = input.getAttribute('name');
                    input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
                });
            });
        }

        window.changePosition = function(input) {
            const item = input.closest('.question-card');
            const items = Array.from(container.children);
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
            updateIndices();
        };

        addButton.addEventListener('click', addQuestion);

        // Añadir primera pregunta por defecto
        addQuestion();

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch('<?= BASE_URL ?>/api/checklists/guardar', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.id) {
                    window.location.href = '<?= BASE_URL ?>/checklists/editar/' + result.id;
                } else {
                    alert('Error: ' + (result.error || 'Ocurrió un error inesperado'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error crítico de conexión');
            }
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('questionsContainer');
        Sortable.create(el, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'bg-light',
            onEnd: function () {
                const event = new CustomEvent('sortEnd');
                document.dispatchEvent(event);
            },
        });
    });
    
    document.addEventListener('sortEnd', () => {
        const container = document.getElementById('questionsContainer');
        if (container) {
            const items = Array.from(container.children);
            items.forEach((card, index) => {
                const num = index + 1;
                const badge = card.querySelector('.question-number');
                if (badge) badge.textContent = `Pregunta #${num}`;
                const ordenInput = card.querySelector('.p-orden-input');
                if (ordenInput) ordenInput.value = num;
                
                card.querySelectorAll('[name]').forEach(input => {
                    const name = input.getAttribute('name');
                    input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
                });
            });
        }
    });
</script>