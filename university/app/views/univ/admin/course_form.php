<?php require VIEW_PATH . "/layouts/header.php"; ?>

<div class="mb-3">
  <a href="<?= BASE_URL ?>/univAdmin" class="btn btn-sm btn-outline-secondary">
    <i class="fa-solid fa-arrow-left"></i> Volver al listado
  </a>
</div>

<div class="card">
  <div class="card-header bg-white py-3">
    <h4 class="m-0">
      <i class="fa-solid <?= $course ? 'fa-pen' : 'fa-plus' ?>"></i> 
      <?= $course ? 'Editar Curso: ' . htmlspecialchars($course['nombre']) : 'Crear Nuevo Curso' ?>
    </h4>
  </div>
  <div class="card-body">
    <form action="<?= BASE_URL ?>/univAdmin/store" method="POST">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="id" value="<?= $course['id'] ?? 0 ?>">

      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label fw-bold">Nombre del Curso</label>
          <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($course['nombre'] ?? '') ?>" required maxlength="150" placeholder="Ej: Manipulación de Alimentos Nivel 1">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-bold">Tipo de Curso</label>
          <select name="tipo" class="form-select">
            <option value="opcional" <?= ($course['tipo'] ?? '') == 'opcional' ? 'selected' : '' ?>>Opcional</option>
            <option value="obligatorio_area" <?= ($course['tipo'] ?? '') == 'obligatorio_area' ? 'selected' : '' ?>>Obligatorio por Área</option>
            <option value="obligatorio_legal" <?= ($course['tipo'] ?? '') == 'obligatorio_legal' ? 'selected' : '' ?>>Obligatorio Legal</option>
          </select>
        </div>

        <div class="col-12">
          <label class="form-label fw-bold">Descripción / Objetivo</label>
          <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($course['descripcion'] ?? '') ?></textarea>
        </div>

        <div class="col-md-3">
          <label class="form-label fw-bold">Créditos</label>
          <input type="number" name="creditos" class="form-control" value="<?= (int)($course['creditos'] ?? 0) ?>" min="0">
          <div class="form-text">Puntos que suma al perfil.</div>
        </div>

        <div class="col-md-3">
          <label class="form-label fw-bold">% Aprobación Mínimo</label>
          <div class="input-group">
            <input type="number" name="min_score_to_approve" class="form-control" value="<?= (int)($course['min_score_to_approve'] ?? 70) ?>" min="1" max="100">
            <span class="input-group-text">%</span>
          </div>
        </div>

        <div class="col-md-3">
          <label class="form-label fw-bold">Preguntas Examen</label>
          <input type="number" name="total_preguntas_examen" class="form-control" value="<?= (int)($course['total_preguntas_examen'] ?? 10) ?>" min="1" max="50">
        </div>

        <div class="col-md-3">
          <label class="form-label fw-bold">Vigencia (Meses)</label>
          <input type="number" name="vigencia_meses" class="form-control" value="<?= htmlspecialchars($course['vigencia_meses'] ?? '') ?>" min="1" placeholder="Ej: 12">
          <div class="form-text">Vacío si no vence.</div>
        </div>

        <div class="col-md-3">
          <label class="form-label fw-bold">Tiempo Límite (Min)</label>
          <input type="number" name="tiempo_limite_minutos" class="form-control" value="<?= (int)($course['tiempo_limite_minutos'] ?? 15) ?>" min="1">
        </div>

        <div class="col-md-3">
          <label class="form-label fw-bold">Intentos Máximos</label>
          <input type="number" name="max_intentos" class="form-control" value="<?= (int)($course['max_intentos'] ?? 3) ?>" min="1" max="10">
        </div>

        <div class="col-md-3 d-flex align-items-end">
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="activo" id="chkActivo" <?= ($course['activo'] ?? 1) ? 'checked' : '' ?>>
            <label class="form-check-label fw-bold" for="chkActivo">Curso Activo</label>
          </div>
        </div>
      </div>

      <!-- Bloque perfiles (solo para obligatorio_area) -->
      <div id="bloque-perfiles" class="mt-4 pt-3 border-top" style="display:none;">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div>
            <label class="form-label fw-bold mb-0">
              <i class="fa-solid fa-users me-1 text-warning"></i>Perfiles que deben tomar este curso
            </label>
            <div class="text-muted small">Al guardar, todos los alumnos activos con los perfiles seleccionados quedarán inscritos automáticamente.</div>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleTodos(true)">Seleccionar todos</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleTodos(false)">Quitar todos</button>
          </div>
        </div>
        <div class="row g-2">
          <?php foreach ($perfilesDisp as $p): ?>
          <div class="col-6 col-md-4 col-lg-3">
            <div class="form-check border rounded px-3 py-2">
              <input class="form-check-input chk-perfil" type="checkbox"
                     name="perfiles[]" value="<?= htmlspecialchars($p) ?>"
                     id="perf_<?= md5($p) ?>"
                     <?= in_array($p, $perfilesSelec) ? 'checked' : '' ?>>
              <label class="form-check-label" for="perf_<?= md5($p) ?>">
                <?= htmlspecialchars($p) ?>
              </label>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if (empty($perfilesDisp)): ?>
          <div class="col-12 text-muted small">No hay perfiles registrados en el sistema.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
        <a href="<?= BASE_URL ?>/univAdmin" class="btn btn-light">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4">
          <i class="fa-solid fa-floppy-disk"></i> Guardar Curso
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleBloquePerfiles() {
    const tipo  = document.querySelector('select[name="tipo"]').value;
    const bloque = document.getElementById('bloque-perfiles');
    bloque.style.display = (tipo === 'obligatorio_area') ? '' : 'none';
}
function toggleTodos(marcar) {
    document.querySelectorAll('.chk-perfil').forEach(function(c) { c.checked = marcar; });
}
document.querySelector('select[name="tipo"]').addEventListener('change', toggleBloquePerfiles);
document.addEventListener('DOMContentLoaded', toggleBloquePerfiles);
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
