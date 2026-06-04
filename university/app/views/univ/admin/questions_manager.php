<?php require VIEW_PATH . "/layouts/header.php"; ?>

<style>
.option-row.is-correct {
    background-color: #d1e7dd !important; /* success-subtle */
}
.option-row.is-correct input[type="text"] {
    background-color: #d1e7dd;
    border-color: #a3cfbb;
}
.option-row.is-correct .input-group-text {
    background-color: #a3cfbb;
    border-color: #a3cfbb;
}
</style>

<div class="mb-3">
  <a href="<?= BASE_URL ?>/univAdmin" class="btn btn-sm btn-outline-secondary">
    <i class="fa-solid fa-arrow-left"></i> Volver a Cursos
  </a>
</div>

<div class="card mb-4 shadow-sm border-0">
  <div class="card-body bg-info text-white rounded">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h4 class="m-0"><i class="fa-solid fa-circle-question"></i> Banco de Preguntas</h4>
        <p class="mb-0">Curso: <strong><?= htmlspecialchars($course['nombre']) ?></strong></p>
      </div>
      <button class="btn btn-light fw-bold" onclick="newQuestion()">
        <i class="fa-solid fa-plus"></i> Nueva Pregunta
      </button>
    </div>
  </div>
</div>

<?php if (count($questions) < $course['total_preguntas_examen']): ?>
  <div class="alert alert-warning shadow-sm mb-4">
    <i class="fa-solid fa-triangle-exclamation"></i> 
    <strong>Atención:</strong> Tienes <?= count($questions) ?> preguntas. El curso requiere al menos <?= $course['total_preguntas_examen'] ?> para el examen.
  </div>
<?php endif; ?>

<div class="row">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-header bg-white fw-bold">Preguntas Registradas</div>
      <div class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;">
        <?php foreach ($questions as $index => $q): ?>
          <button type="button" class="list-group-item list-group-item-action p-3" onclick="editQuestion(<?= htmlspecialchars(json_encode($q)) ?>)">
            <div class="d-flex justify-content-between">
              <span class="fw-bold">#<?= $index + 1 ?></span>
              <span class="badge bg-light text-dark border"><?= count($q['options']) ?> opciones</span>
            </div>
            <div class="text-truncate small"><?= htmlspecialchars($q['texto_pregunta']) ?></div>
          </button>
        <?php endforeach; ?>
        <?php if (empty($questions)): ?>
          <div class="p-4 text-center text-muted italic">No hay preguntas creadas.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-md-7">
    <div class="card shadow-sm" id="questionEditor" style="display: none;">
      <div class="card-header bg-white fw-bold" id="editorTitle">Nueva Pregunta</div>
      <div class="card-body">
        <form action="<?= BASE_URL ?>/univAdmin/saveQuestion" method="POST">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
          <input type="hidden" name="id" id="q_id" value="0">

          <div class="mb-3">
            <label class="form-label fw-bold">Texto de la Pregunta</label>
            <textarea name="texto_pregunta" id="q_texto" class="form-control" rows="3" required placeholder="¿Cuál es la temperatura correcta de...?"></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold d-flex justify-content-between">
              Alternativas
              <span class="small text-muted fw-normal">Marca el círculo para la correcta</span>
            </label>
            <div id="optionsContainer">
              <!-- Se genera con JS -->
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addOptionField('', false)">
              <i class="fa-solid fa-plus"></i> Añadir Alternativa
            </button>
          </div>

          <div class="mt-4 pt-3 border-top d-flex justify-content-between">
            <button type="button" class="btn btn-outline-danger" id="btnDelQ" style="display: none;" onclick="confirmDelQ()">
              <i class="fa-solid fa-trash"></i> Eliminar
            </button>
            <button type="submit" class="btn btn-primary px-4 ms-auto">
              <i class="fa-solid fa-save"></i> Guardar Pregunta
            </button>
          </div>
        </form>
      </div>
    </div>

    <div id="qEmptyState" class="text-center py-5 border rounded bg-white shadow-sm">
      <i class="fa-solid fa-lightbulb fa-3x text-muted mb-3 opacity-50"></i>
      <h5>Selecciona una pregunta o crea una nueva</h5>
    </div>
  </div>
</div>

<script>
let optionCounter = 0;

function newQuestion() {
    document.getElementById('qEmptyState').style.display = 'none';
    document.getElementById('questionEditor').style.display = 'block';
    document.getElementById('editorTitle').innerText = 'Nueva Pregunta';
    document.getElementById('q_id').value = 0;
    document.getElementById('q_texto').value = '';
    document.getElementById('btnDelQ').style.display = 'none';
    
    document.getElementById('optionsContainer').innerHTML = '';
    optionCounter = 0;
    addOptionField('', true);
    addOptionField('', false);
}

function editQuestion(q) {
    document.getElementById('qEmptyState').style.display = 'none';
    document.getElementById('questionEditor').style.display = 'block';
    document.getElementById('editorTitle').innerText = 'Editar Pregunta';
    document.getElementById('q_id').value = q.id;
    document.getElementById('q_texto').value = q.texto_pregunta;
    document.getElementById('btnDelQ').style.display = 'block';

    document.getElementById('optionsContainer').innerHTML = '';
    optionCounter = 0;
    q.options.forEach(opt => {
        addOptionField(opt.texto_opcion, opt.es_correcta == 1);
    });
}

function addOptionField(text, isCorrect) {
    const container = document.getElementById('optionsContainer');
    const div = document.createElement('div');
    div.className = 'input-group mb-2 option-row' + (isCorrect ? ' is-correct' : '');
    
    div.innerHTML = `
      <div class="input-group-text">
        <input class="form-check-input mt-0" type="radio" name="correct_index" value="${optionCounter}" ${isCorrect ? 'checked' : ''} required onchange="highlightCorrect(this)">
      </div>
      <input type="text" name="options[${optionCounter}]" class="form-control" value="${text}" placeholder="Texto de la alternativa..." required>
      <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
    `;
    container.appendChild(div);
    optionCounter++;
}

function highlightCorrect(radio) {
    // Quitar highlight de todos
    const rows = document.querySelectorAll('.option-row');
    rows.forEach(row => {
        row.classList.remove('is-correct');
    });
    // Poner highlight al seleccionado
    const currentRow = radio.closest('.option-row');
    currentRow.classList.add('is-correct');
}

function confirmDelQ() {
    const id = document.getElementById('q_id').value;
    if (id > 0 && confirm('¿Eliminar esta pregunta permanentemente?')) {
        window.location.href = '<?= BASE_URL ?>/univAdmin/deleteQuestion/' + id + '/<?= $course['id'] ?>';
    }
}
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
