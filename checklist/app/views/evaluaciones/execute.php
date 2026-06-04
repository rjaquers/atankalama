<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center mb-4">
            <a href="<?= BASE_URL ?>/evaluaciones" class="btn btn-link text-decoration-none ps-0">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <h2 class="mb-0 ms-2">Realizar Evaluación</h2>
        </div>

        <div class="card shadow-sm border-0 mb-4 bg-primary text-white">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <small class="text-white-50 text-uppercase ls-1 fw-bold">Checklist Seleccionado</small>
                        <h3 class="mb-0">
                            <?= htmlspecialchars($checklist['nombre']) ?>
                        </h3>
                        <p class="mb-0 text-white-50 small mt-1">Área:
                            <?= htmlspecialchars($checklist['area']) ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <i class="bi bi-clipboard-check display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pantalla de inicio -->
        <div id="startScreen" class="text-center py-5 mb-4">
            <div class="mb-4">
                <i class="bi bi-clipboard-check display-1 text-success opacity-75"></i>
            </div>
            <h4 class="mb-2">¿Listo para comenzar?</h4>
            <p class="text-muted mb-4">Al presionar el botón se registrará la hora de inicio de la evaluación.</p>
            <button type="button" id="btnIniciar" class="btn btn-success btn-lg px-5 py-3 rounded-pill shadow fw-bold fs-5">
                <i class="bi bi-play-circle me-2"></i> Iniciar Evaluación
            </button>
        </div>

        <!-- Formulario (oculto hasta iniciar) -->
        <div id="evaluationContent" class="d-none">
        <div class="alert alert-success d-flex align-items-center mb-4" id="timerBadge">
            <i class="bi bi-clock me-2"></i>
            <span>Evaluación iniciada a las <strong id="horaInicio"></strong></span>
        </div>

        <form id="evaluationForm">
            <input type="hidden" name="checklist_id" value="<?= $checklist['id'] ?>">
            <input type="hidden" name="fecha_inicio" id="fecha_inicio">
            <input type="hidden" name="fecha_fin" id="fecha_fin">

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">Información del Evaluado</h5>
                    
                    <div class="mb-4">
                        <label class="form-label small text-muted text-uppercase fw-bold">¿Qué desea evaluar?</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_evaluado" id="tipo_persona" value="persona" checked>
                            <label class="btn btn-outline-primary" for="tipo_persona">
                                <i class="bi bi-person me-2"></i>Persona
                            </label>
                            
                            <input type="radio" class="btn-check" name="tipo_evaluado" id="tipo_espacio" value="espacio">
                            <label class="btn btn-outline-primary" for="tipo_espacio">
                                <i class="bi bi-building me-2"></i>Espacio / Habitación
                            </label>
                        </div>
                    </div>

                    <!-- Campos para Persona -->
                    <div id="section_persona" class="row">
                        <?php if (!empty($usuariosArea)): ?>
                            <div class="col-12 mb-3">
                                <label class="form-label">Persona Evaluada</label>
                                <select id="select_usuario_area" class="form-select">
                                    <option value="">Seleccione una persona...</option>
                                    <?php foreach ($usuariosArea as $u): ?>
                                        <option
                                            value="<?= $u['id'] ?>"
                                            data-nombre="<?= htmlspecialchars($u['nombre'] ?? '') ?>"
                                            data-apellido="<?= htmlspecialchars($u['apellido'] ?? '') ?>"
                                            data-email="<?= htmlspecialchars($u['email'] ?? '') ?>">
                                            <?= htmlspecialchars(trim(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? ''))) ?>
                                            <?php if (!empty($u['perfil'])): ?>
                                                <span class="text-muted"> — <?= htmlspecialchars($u['perfil']) ?></span>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <input type="hidden" name="evaluado_nombre"   id="evaluado_nombre">
                            <input type="hidden" name="evaluado_apellido" id="evaluado_apellido">
                            <input type="hidden" name="evaluado_email"    id="evaluado_email">
                        <?php else: ?>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="evaluado_nombre" id="evaluado_nombre" class="form-control" placeholder="Nombre completo">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apellido</label>
                                <input type="text" name="evaluado_apellido" id="evaluado_apellido" class="form-control" placeholder="Apellido completo">
                            </div>
                            <input type="hidden" name="evaluado_email" id="evaluado_email">
                        <?php endif; ?>
                    </div>

                    <!-- Campos para Espacio -->
                    <div id="section_espacio" class="row d-none">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hotel</label>
                            <select name="evaluado_hotel" id="evaluado_hotel" class="form-select">
                                <option value="">Seleccionar hotel...</option>
                                <option value="Atankalama">Atankalama</option>
                                <option value="Atankalama Inn">Atankalama Inn</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número de Habitación</label>
                            <input type="number" name="evaluado_habitacion" id="evaluado_habitacion" class="form-control" min="1" max="9999" placeholder="1 - 9999">
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="mb-3 px-2">Cuestionario</h5>

            <?php $currentGroup = null; ?>
            <?php foreach ($checklist['preguntas'] as $index => $pregunta): ?>
                <?php if (!empty($pregunta['grupo']) && $pregunta['grupo'] !== $currentGroup): ?>
                    <h6 class="mt-4 mb-3 text-primary fw-bold text-uppercase px-2 border-bottom pb-2">
                        <i class="bi bi-folder2-open me-2"></i><?= htmlspecialchars($pregunta['grupo']) ?>
                    </h6>
                    <?php $currentGroup = $pregunta['grupo']; ?>
                <?php endif; ?>

                <div class="card shadow-sm border-0 mb-3 question-item">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <span
                                class="badge bg-light text-dark border me-3 d-flex align-items-center justify-content-center"
                                style="width: 30px; height: 30px; border-radius: 50%;">
                                <?= $index + 1 ?>
                            </span>
                            <h6 class="mb-0 mt-1">
                                <?= htmlspecialchars($pregunta['pregunta']) ?>
                            </h6>
                        </div>

                        <?php if ($pregunta['tipo_respuesta'] === 'boolean'): ?>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="respuestas[<?= $pregunta['id'] ?>]"
                                    id="q<?= $pregunta['id'] ?>_yes" value="1" required>
                                <label class="btn btn-outline-success border-2 py-3"
                                    for="q<?= $pregunta['id'] ?>_yes">Sí</label>

                                <input type="radio" class="btn-check" name="respuestas[<?= $pregunta['id'] ?>]"
                                    id="q<?= $pregunta['id'] ?>_no" value="0">
                                <label class="btn btn-outline-danger border-2 py-3" for="q<?= $pregunta['id'] ?>_no">No</label>

                                <input type="radio" class="btn-check" name="respuestas[<?= $pregunta['id'] ?>]"
                                    id="q<?= $pregunta['id'] ?>_na" value="">
                                <label class="btn btn-outline-secondary border-2 py-3"
                                    for="q<?= $pregunta['id'] ?>_na">N/A</label>
                            </div>

                        <?php elseif ($pregunta['tipo_respuesta'] === 'numeric_scale'):
                            $eMin = !empty($pregunta['escala_min']) ? (int)$pregunta['escala_min'] : 1;
                            $eMax = !empty($pregunta['escala_max']) ? (int)$pregunta['escala_max'] : 10;
                        ?>
                            <div class="px-2">
                                <div class="range-wrapper mb-1">
                                    <span class="range-endpoint text-danger"><?= $eMin ?></span>
                                    <input type="range" class="range-custom" name="respuestas[<?= $pregunta['id'] ?>]"
                                        min="<?= $eMin ?>" max="<?= $eMax ?>" step="1"
                                        value="<?= $eMin ?>">
                                    <span class="range-endpoint text-success"><?= $eMax ?></span>
                                </div>
                                <div class="text-center small text-muted range-hint mb-1">← desliza para seleccionar →</div>
                                <div class="text-center h3 fw-bold text-primary range-value" style="min-height:2.5rem">
                                    <?= $eMin ?>
                                </div>
                            </div>

                        <?php elseif ($pregunta['tipo_respuesta'] === 'foto'): ?>
                            <div class="mt-2">
                                <input type="hidden" name="foto_questions[]" value="<?= $pregunta['id'] ?>">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-camera me-2 text-primary"></i>
                                    <span class="small text-muted fw-bold text-uppercase ls-1">Adjuntar Evidencia</span>
                                </div>
                                <input type="file" 
                                       name="fotos_<?= $pregunta['id'] ?>[]"
                                       class="form-control"
                                       accept="image/*"
                                       multiple
                                       capture="environment">
                                <div class="form-text small mt-1">Puedes subir múltiples fotos. Es opcional.</div>
                            </div>

                        <?php else: ?>
                            <textarea name="respuestas[<?= $pregunta['id'] ?>]" class="form-control" rows="3"
                                placeholder="Escriba su observación..."></textarea>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="text-end mt-4 mb-5 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-5 btn-lg rounded-pill shadow">
                    Finalizar Evaluación <i class="bi bi-send ms-2"></i>
                </button>
            </div>
        </form>
        </div><!-- fin #evaluationContent -->
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const STORAGE_KEY = 'eval_fecha_inicio';
        let evaluacionIniciada = false;

        // --- Botón Iniciar ---
        document.getElementById('btnIniciar').addEventListener('click', function () {
            // Borrar cualquier inicio previo y registrar el nuevo
            sessionStorage.removeItem(STORAGE_KEY);
            const ahora = new Date();
            sessionStorage.setItem(STORAGE_KEY, ahora.toISOString());

            document.getElementById('fecha_inicio').value = formatDatetimeLocal(ahora);
            document.getElementById('horaInicio').textContent = ahora.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

            document.getElementById('startScreen').classList.add('d-none');
            document.getElementById('evaluationContent').classList.remove('d-none');

            evaluacionIniciada = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // --- Alerta al salir sin finalizar ---
        window.addEventListener('beforeunload', function (e) {
            if (evaluacionIniciada) {
                e.preventDefault();
                e.returnValue = 'Tienes una evaluación en curso. Si sales ahora perderás los datos.';
            }
        });

        // --- Toggle de tipo de evaluado ---
        const radios = document.querySelectorAll('input[name="tipo_evaluado"]');
        const sectionPersona = document.getElementById('section_persona');
        const sectionEspacio = document.getElementById('section_espacio');

        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'persona') {
                    sectionPersona.classList.remove('d-none');
                    sectionEspacio.classList.add('d-none');
                } else {
                    sectionPersona.classList.add('d-none');
                    sectionEspacio.classList.remove('d-none');
                }
            });
        });

        // --- Auto-binding para selector de usuarios del área ---
        const selectUsuario = document.getElementById('select_usuario_area');
        if (selectUsuario) {
            selectUsuario.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                document.getElementById('evaluado_nombre').value   = opt.getAttribute('data-nombre')   || '';
                document.getElementById('evaluado_apellido').value = opt.getAttribute('data-apellido') || '';
                document.getElementById('evaluado_email').value    = opt.getAttribute('data-email')    || '';
            });
        }

        // --- Sliders: fill de track + actualizar valor + ocultar hint ---
        function updateRangeFill(range) {
            const min = parseFloat(range.min) || 0, max = parseFloat(range.max) || 100, val = parseFloat(range.value) || min;
            const pct = max > min ? ((val - min) / (max - min)) * 100 : 0;
            range.style.setProperty('--rf', pct + '%');
        }

        document.querySelectorAll('input[type="range"].range-custom').forEach(range => {
            const wrapper = range.closest('.px-2');
            const display = wrapper.querySelector('.range-value');
            const hint = wrapper.querySelector('.range-hint');
            updateRangeFill(range);
            range.addEventListener('input', function () {
                display.textContent = this.value;
                updateRangeFill(this);
                if (hint) { hint.style.opacity = '0'; hint.style.pointerEvents = 'none'; }
            });
        });

        // --- Preview de fotos ---
        document.addEventListener('change', function (e) {
            if (!e.target.classList.contains('foto-input')) return;
            const input = e.target;
            const preview = input.parentElement.querySelector('.foto-preview');
            preview.innerHTML = '';
            Array.from(input.files).forEach(function (file) {
                const reader = new FileReader();
                reader.onload = function (ev) {
                    const img = document.createElement('img');
                    img.src = ev.target.result;
                    img.className = 'img-thumbnail';
                    img.style.cssText = 'max-height:80px;max-width:120px;object-fit:cover;';
                    img.title = file.name;
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });

        // --- Submit del formulario ---
        const form = document.getElementById('evaluationForm');
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            // Validación manual basada en el tipo seleccionado
            const tipo = document.querySelector('input[name="tipo_evaluado"]:checked').value;
            if (tipo === 'persona') {
                <?php if (!empty($usuariosArea)): ?>
                const selUsr = document.getElementById('select_usuario_area');
                if (!selUsr || !selUsr.value) {
                    alert('Por favor seleccione una persona');
                    return;
                }
                <?php else: ?>
                if (!document.getElementById('evaluado_nombre').value) {
                    alert('Por favor ingrese el nombre');
                    return;
                }
                <?php endif; ?>
            } else {
                if (!document.getElementById('evaluado_hotel').value || !document.getElementById('evaluado_habitacion').value) {
                    alert('Por favor seleccione el hotel y el número de habitación');
                    return;
                }
            }

            // Registrar hora de fin
            const fin = new Date();
            document.getElementById('fecha_fin').value = formatDatetimeLocal(fin);

            // Desactivar alerta de salida
            evaluacionIniciada = false;
            sessionStorage.removeItem(STORAGE_KEY);

            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando...';

            const formData = new FormData(this);

            try {
                const response = await fetch('<?= BASE_URL ?>/api/evaluaciones/guardar', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.redirect) {
                    alert('Evaluación guardada correctamente');
                    window.location.href = result.redirect;
                } else {
                    alert('Error: ' + (result.error || 'Ocurrió un error'));
                    evaluacionIniciada = true;
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Finalizar Evaluación <i class="bi bi-send ms-2"></i>';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error crítico de conexión');
                evaluacionIniciada = true;
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Finalizar Evaluación <i class="bi bi-send ms-2"></i>';
            }
        });

        // --- Helper: formatea Date a "YYYY-MM-DD HH:MM:SS" para MySQL ---
        function formatDatetimeLocal(d) {
            const pad = n => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
        }
    });
</script>

<style>
    .ls-1 {
        letter-spacing: 1px;
    }

    .question-item {
        transition: transform 0.2s;
    }

    .question-item:focus-within {
        transform: scale(1.02);
        border-left: 4px solid var(--bs-primary) !important;
    }

    .range-custom {
        -webkit-appearance: none;
        appearance: none;
        height: 6px !important;
        border-radius: 3px;
        outline: none;
        cursor: pointer;
        padding: 0;
        background: linear-gradient(to right, #0d6efd var(--rf, 0%), #dee2e6 var(--rf, 0%)) !important;
    }
    .range-custom::-webkit-slider-runnable-track {
        height: 6px;
        border-radius: 3px;
        background: linear-gradient(to right, #0d6efd var(--rf, 0%), #dee2e6 var(--rf, 0%));
    }
    .range-custom::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #0d6efd;
        cursor: grab;
        box-shadow: 0 2px 6px rgba(13,110,253,.4);
        transition: transform .15s, box-shadow .15s;
        margin-top: -9px;
    }
    .range-custom::-webkit-slider-thumb:hover,
    .range-custom:active::-webkit-slider-thumb {
        transform: scale(1.25);
        box-shadow: 0 3px 10px rgba(13,110,253,.5);
    }
    .range-custom::-moz-range-track {
        height: 6px;
        border-radius: 3px;
        background: transparent;
    }
    .range-custom::-moz-range-progress {
        height: 6px;
        border-radius: 3px 0 0 3px;
        background: #0d6efd;
    }
    .range-custom::-moz-range-thumb {
        width: 24px;
        height: 24px;
        border: none;
        border-radius: 50%;
        background: #0d6efd;
        cursor: grab;
        box-shadow: 0 2px 6px rgba(13,110,253,.4);
    }
    .range-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .range-wrapper input[type="range"] {
        flex: 1 1 0;
        min-width: 0;
    }
    .range-endpoint {
        font-size: 1.15rem;
        font-weight: 700;
        flex-shrink: 0;
        min-width: 2rem;
        text-align: center;
        user-select: none;
    }
    .range-hint {
        transition: opacity .3s;
    }
</style>