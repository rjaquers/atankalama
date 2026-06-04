<div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">

        <div class="card shadow-sm border-0 mb-4 bg-primary text-white">
            <div class="card-body p-4">
                <small class="text-white-50 text-uppercase fw-bold" style="letter-spacing:1px">Encuesta</small>
                <h3 class="mb-1 mt-1"><?= htmlspecialchars($checklist['nombre']) ?></h3>
                <p class="mb-0 text-white-50 small"><?= htmlspecialchars($checklist['area']) ?></p>
            </div>
        </div>

        <form id="encuestaForm">
            <input type="hidden" name="checklist_id" value="<?= $checklist['id'] ?>">

            <?php $currentGroup = null; ?>
            <?php foreach ($checklist['preguntas'] as $index => $pregunta): ?>

                <?php if (!empty($pregunta['grupo']) && $pregunta['grupo'] !== $currentGroup): ?>
                    <h6 class="mt-4 mb-3 text-primary fw-bold text-uppercase border-bottom pb-2" style="letter-spacing:1px">
                        <i class="bi bi-folder2-open me-2"></i><?= htmlspecialchars($pregunta['grupo']) ?>
                    </h6>
                    <?php $currentGroup = $pregunta['grupo']; ?>
                <?php endif; ?>

                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3 align-items-start">
                            <span class="badge bg-light text-dark border me-3 mt-1 flex-shrink-0"
                                style="width:28px;height:28px;border-radius:50%;display:flex!important;align-items:center;justify-content:center;">
                                <?= $index + 1 ?>
                            </span>
                            <h6 class="mb-0"><?= htmlspecialchars($pregunta['pregunta']) ?></h6>
                        </div>

                        <?php if ($pregunta['tipo_respuesta'] === 'boolean'): ?>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="respuestas[<?= $pregunta['id'] ?>]"
                                    id="q<?= $pregunta['id'] ?>_yes" value="1">
                                <label class="btn btn-outline-success border-2 py-3" for="q<?= $pregunta['id'] ?>_yes">Sí</label>

                                <input type="radio" class="btn-check" name="respuestas[<?= $pregunta['id'] ?>]"
                                    id="q<?= $pregunta['id'] ?>_no" value="0">
                                <label class="btn btn-outline-danger border-2 py-3" for="q<?= $pregunta['id'] ?>_no">No</label>

                                <input type="radio" class="btn-check" name="respuestas[<?= $pregunta['id'] ?>]"
                                    id="q<?= $pregunta['id'] ?>_na" value="">
                                <label class="btn btn-outline-secondary border-2 py-3" for="q<?= $pregunta['id'] ?>_na">N/A</label>
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
                                    <span class="small text-muted fw-bold text-uppercase" style="letter-spacing:1px">Adjuntar Fotografía</span>
                                </div>
                                <input type="file"
                                    name="fotos_<?= $pregunta['id'] ?>[]"
                                    class="form-control"
                                    accept="image/*"
                                    multiple
                                    capture="environment">
                                <div class="form-text small mt-1">Opcional. Puedes subir múltiples fotos.</div>
                            </div>

                        <?php else: ?>
                            <textarea name="respuestas[<?= $pregunta['id'] ?>]" class="form-control" rows="3"
                                placeholder="Escribe tu respuesta aquí..."></textarea>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="d-grid mt-4 mb-5">
                <button type="submit" id="btnEnviar" class="btn btn-primary btn-lg rounded-pill py-3 shadow">
                    Enviar Encuesta <i class="bi bi-send ms-2"></i>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    // Sliders: fill de track + actualizar valor + ocultar hint
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

    document.getElementById('encuestaForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const btn = document.getElementById('btnEnviar');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...';

        const formData = new FormData(this);

        try {
            const response = await fetch('<?= BASE_URL ?>/api/encuesta/<?= htmlspecialchars($checklist['token_publico']) ?>/guardar', {
                method: 'POST',
                body: formData
            });

            const text = await response.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (parseError) {
                console.error('Respuesta no es JSON:', text);
                alert('Error del servidor:\n' + text.substring(0, 300));
                btn.disabled = false;
                btn.innerHTML = 'Enviar Encuesta <i class="bi bi-send ms-2"></i>';
                return;
            }

            if (result.redirect) {
                document.querySelector('.col-lg-7').innerHTML = `
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size:5rem"></i>
                        </div>
                        <h2 class="fw-bold mb-3">¡Muchas gracias!</h2>
                        <p class="text-muted fs-5 mb-4">Tu respuesta ha sido registrada correctamente.</p>
                        <p class="text-muted small">Serás redirigido en unos segundos...</p>
                    </div>
                `;
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 2500);
            } else {
                alert('Error: ' + (result.error || 'Ocurrió un error inesperado'));
                btn.disabled = false;
                btn.innerHTML = 'Enviar Encuesta <i class="bi bi-send ms-2"></i>';
            }
        } catch (error) {
            console.error('Error de red:', error);
            alert('Error de red: ' + error.message);
            btn.disabled = false;
            btn.innerHTML = 'Enviar Encuesta <i class="bi bi-send ms-2"></i>';
        }
    });
</script>

<style>
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
