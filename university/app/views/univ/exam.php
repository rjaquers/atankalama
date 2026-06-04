<?php require VIEW_PATH . "/layouts/header.php"; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-dark text-white p-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0"><i class="fa-solid fa-file-pen"></i> Evaluación: <?= htmlspecialchars($enroll['nombre']) ?></h5>
                    <div id="examTimer" class="badge bg-danger fs-6">
                        <i class="fa-regular fa-clock"></i> <span id="timerDisplay">15:00</span>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <form id="examForm" action="<?= BASE_URL ?>/univ/submitExam" method="POST">
                        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                        <input type="hidden" name="enroll_id" value="<?= $enroll['id'] ?>">

                        <?php foreach ($questions as $index => $q): ?>
                            <div class="question-step" id="step-<?= $index ?>" style="<?= $index > 0 ? 'display:none;' : '' ?>">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="badge bg-light text-dark border">Pregunta <?= $index + 1 ?> de <?= count($questions) ?></span>
                                    <div class="progress w-50" style="height: 10px;">
                                        <div class="progress-bar bg-info" style="width: <?= (($index + 1) / count($questions)) * 100 ?>%"></div>
                                    </div>
                                </div>
                                
                                <h4 class="fw-bold mb-4"><?= htmlspecialchars($q['texto_pregunta']) ?></h4>

                                <div class="list-group mb-4">
                                    <?php foreach ($q['options'] as $opt): ?>
                                        <label class="list-group-item list-group-item-action p-3 rounded mb-2 border">
                                            <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $opt['id'] ?>" class="form-check-input me-3" required>
                                            <?= htmlspecialchars($opt['texto_opcion']) ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <?php if ($index > 0): ?>
                                        <button type="button" class="btn btn-outline-secondary px-4" onclick="showStep(<?= $index - 1 ?>)">Anterior</button>
                                    <?php else: ?>
                                        <div></div>
                                    <?php endif; ?>

                                    <?php if ($index < count($questions) - 1): ?>
                                        <button type="button" class="btn btn-primary px-5 fw-bold" onclick="showStep(<?= $index + 1 ?>)">Siguiente</button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-success px-5 fw-bold" onclick="return confirm('¿Estás seguro de enviar tus respuestas?')">FINALIZAR EXAMEN</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let totalSeconds = 15 * 60; // 15 minutos por defecto
const timerDisplay = document.getElementById('timerDisplay');
const examForm = document.getElementById('examForm');

function updateTimer() {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    timerDisplay.innerText = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    
    if (totalSeconds <= 60) {
        timerDisplay.parentElement.classList.replace('bg-danger', 'bg-warning');
    }
    
    if (totalSeconds <= 0) {
        alert('El tiempo ha terminado. El examen se enviará automáticamente.');
        examForm.submit();
    } else {
        totalSeconds--;
    }
}

const timerInterval = setInterval(updateTimer, 1000);

function showStep(step) {
    document.querySelectorAll('.question-step').forEach(div => div.style.display = 'none');
    document.getElementById('step-' + step).style.display = 'block';
}
</script>

<style>
.list-group-item input[type="radio"]:checked + span { font-weight: bold; }
.list-group-item:hover { background-color: #f8f9fa; cursor: pointer; }
</style>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
