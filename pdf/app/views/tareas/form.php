<?php require VIEW_PATH . '/layouts/header.php'; ?>

<?php
$esEdicion   = !empty($tarea);
$titulo      = $esEdicion ? 'Editar Tarea' : 'Nueva Tarea';
$action      = $esEdicion
    ? BASE_URL . '/tareas/editar/' . $tarea['id']
    : BASE_URL . '/tareas/crear';
$puedeAsignar = $esJefeAdmin ?? false;
$tipoActual   = $tarea['tipo'] ?? 'abierta';
?>

<!-- ===== BREADCRUMB ===== -->
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item">
      <a href="<?= BASE_URL ?>/tareas" class="text-decoration-none">
        <i class="bi bi-clipboard-check-fill me-1"></i>Tareas
      </a>
    </li>
    <?php if ($esEdicion): ?>
      <li class="breadcrumb-item">
        <a href="<?= BASE_URL ?>/tareas/ver/<?= $tarea['id'] ?>" class="text-decoration-none">
          <?= htmlspecialchars($tarea['titulo']) ?>
        </a>
      </li>
    <?php endif; ?>
    <li class="breadcrumb-item active"><?= $titulo ?></li>
  </ol>
</nav>

<!-- ===== FORMULARIO ===== -->
<div class="row justify-content-center">
  <div class="col-12 col-lg-8">
    <div class="form-card">

      <h5 class="fw-bold mb-4">
        <i class="bi bi-<?= $esEdicion ? 'pencil-fill' : 'plus-circle-fill' ?> text-primary me-2"></i>
        <?= $titulo ?>
      </h5>

      <form method="post"
            action="<?= $action ?>"
            enctype="multipart/form-data"
            id="formTarea"
            novalidate>

        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <!-- Título -->
        <div class="mb-3">
          <label for="titulo" class="form-label fw-semibold">
            Título <span class="text-danger">*</span>
          </label>
          <input type="text"
                 id="titulo"
                 name="titulo"
                 class="form-control"
                 value="<?= htmlspecialchars($tarea['titulo'] ?? '') ?>"
                 placeholder="Ej: Revisión de habitación 203"
                 required
                 maxlength="255">
          <div class="invalid-feedback">El título es obligatorio.</div>
        </div>

        <!-- Descripción -->
        <div class="mb-3">
          <label for="descripcion" class="form-label fw-semibold">Descripción</label>
          <textarea id="descripcion"
                    name="descripcion"
                    rows="4"
                    class="form-control"
                    placeholder="Detalla el trabajo a realizar..."><?= htmlspecialchars($tarea['descripcion'] ?? '') ?></textarea>
        </div>

        <!-- Tipo de tarea -->
        <?php if ($puedeAsignar || !$esEdicion): ?>
        <div class="mb-3">
          <label class="form-label fw-semibold">
            Tipo de tarea <span class="text-danger">*</span>
          </label>
          <div class="d-flex gap-4">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="tipo" id="tipoAbierta"
                     value="abierta" <?= $tipoActual === 'abierta' ? 'checked' : '' ?>
                     <?= ($esEdicion && !$puedeAsignar) ? 'disabled' : '' ?>>
              <label class="form-check-label" for="tipoAbierta">
                <i class="bi bi-people-fill me-1 text-info"></i>
                <strong>Abierta</strong>
                <small class="text-muted d-block">Para toda un área</small>
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="tipo" id="tipoDirigida"
                     value="dirigida" <?= $tipoActual === 'dirigida' ? 'checked' : '' ?>
                     <?= ($esEdicion && !$puedeAsignar) ? 'disabled' : '' ?>>
              <label class="form-check-label" for="tipoDirigida">
                <i class="bi bi-person-check-fill me-1 text-primary"></i>
                <strong>Dirigida</strong>
                <small class="text-muted d-block">A una persona específica</small>
              </label>
            </div>
          </div>
        </div>
        <?php else: ?>
          <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipoActual) ?>">
        <?php endif; ?>

        <div class="row g-3">
          <!-- Área -->
          <div class="col-md-6" id="campoArea">
            <label for="area_id" class="form-label fw-semibold">
              Área <span id="areaReq" class="text-danger">*</span>
            </label>
            <?php if ($puedeAsignar || !$esEdicion): ?>
            <select id="area_id" name="area_id" class="form-select">
              <option value="">Sin área</option>
              <?php foreach ($areas as $a): ?>
                <option value="<?= $a['id'] ?>"
                  <?= (($tarea['area_id'] ?? '') == $a['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Selecciona un área para tareas abiertas.</div>
            <?php else: ?>
              <p class="form-control-plaintext fw-semibold"><?= htmlspecialchars($tarea['area_nombre'] ?? '—') ?></p>
              <input type="hidden" name="area_id" value="<?= (int)($tarea['area_id'] ?? 0) ?: '' ?>">
            <?php endif; ?>
          </div>

          <!-- Asignado a -->
          <div class="col-md-6" id="campoAsignado">
            <label for="asignado_a" class="form-label fw-semibold">
              Asignado a <span id="asignadoReq" class="text-danger">*</span>
            </label>
            <?php if ($puedeAsignar || !$esEdicion): ?>
            <select id="asignado_a" name="asignado_a" class="form-select">
              <option value="">Sin asignar</option>
              <?php foreach ($usuarios as $u): ?>
                <option value="<?= $u['id'] ?>"
                  <?= (($tarea['asignado_a'] ?? '') == $u['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($u['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Selecciona una persona para tareas dirigidas.</div>
            <?php else: ?>
              <p class="form-control-plaintext fw-semibold"><?= htmlspecialchars($tarea['asignado_nombre'] ?? 'Sin asignar') ?></p>
            <?php endif; ?>
          </div>

          <!-- Prioridad -->
          <div class="col-md-6">
            <label for="prioridad" class="form-label fw-semibold">Prioridad</label>
            <select id="prioridad" name="prioridad" class="form-select">
              <?php
              $prioridades = ['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta', 'urgente' => 'Urgente'];
              foreach ($prioridades as $val => $lbl):
                $sel = (($tarea['prioridad'] ?? 'media') === $val) ? 'selected' : '';
              ?>
                <option value="<?= $val ?>" <?= $sel ?>><?= $lbl ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Fecha límite -->
          <div class="col-md-6">
            <label for="fecha_limite" class="form-label fw-semibold">Fecha límite</label>
            <input type="date"
                   id="fecha_limite"
                   name="fecha_limite"
                   class="form-control"
                   value="<?= htmlspecialchars($tarea['fecha_limite'] ?? '') ?>">
          </div>
        </div>

        <!-- Fotos adjuntas (solo en creación) -->
        <?php if (!$esEdicion): ?>
        <div class="mt-3">
          <label for="fotos" class="form-label fw-semibold">
            <i class="bi bi-images me-1 text-secondary"></i>Adjuntar fotos (opcional)
          </label>
          <input type="file"
                 id="fotos"
                 name="fotos[]"
                 accept="image/*"
                 multiple
                 class="form-control">
          <div class="photo-preview" id="previewFotos"></div>
        </div>
        <?php endif; ?>

        <!-- Acciones -->
        <div class="d-flex gap-2 justify-content-end mt-4 pt-3 border-top">
          <a href="<?= $esEdicion ? BASE_URL . '/tareas/ver/' . $tarea['id'] : BASE_URL . '/tareas' ?>"
             class="btn btn-outline-secondary">
            <i class="bi bi-x-lg me-1"></i>Cancelar
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-floppy-fill me-1"></i>Guardar Tarea
          </button>
        </div>

      </form>
    </div><!-- /form-card -->
  </div>
</div>

<script>
(function () {
  'use strict';

  // --- Preview fotos múltiples ---
  var inputFotos = document.getElementById('fotos');
  if (inputFotos) {
    inputFotos.addEventListener('change', function () {
      var preview = document.getElementById('previewFotos');
      preview.innerHTML = '';
      Array.prototype.forEach.call(this.files, function (file) {
        var img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        preview.appendChild(img);
      });
    });
  }

  // --- Tipo de tarea: abierta vs dirigida ---
  var radios       = document.querySelectorAll('input[name="tipo"]');
  var selectArea   = document.getElementById('area_id');
  var selectPerson = document.getElementById('asignado_a');

  function actualizarRequeridos() {
    var tipo = document.querySelector('input[name="tipo"]:checked');
    if (!tipo || !selectArea || !selectPerson) return;
    if (tipo.value === 'abierta') {
      selectArea.setAttribute('required', '');
      selectPerson.removeAttribute('required');
      document.getElementById('areaReq').style.display    = '';
      document.getElementById('asignadoReq').style.display = 'none';
    } else {
      selectPerson.setAttribute('required', '');
      selectArea.removeAttribute('required');
      document.getElementById('asignadoReq').style.display = '';
      document.getElementById('areaReq').style.display     = 'none';
    }
  }

  radios.forEach(function (r) {
    r.addEventListener('change', actualizarRequeridos);
  });
  actualizarRequeridos(); // estado inicial

  // --- Carga dinámica de usuarios por área ---
  var currentAsignado = '<?= (int)($tarea['asignado_a'] ?? 0) ?>';

  function loadUsuariosPorArea(areaId) {
    if (!selectPerson) return;
    if (!areaId) {
      location.reload();
      return;
    }
    fetch(window._BASE_URL + '/usuarios/porArea?id=' + encodeURIComponent(areaId))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        selectPerson.innerHTML = '<option value="">Sin asignar</option>';
        var usuarios = data.usuarios || data;
        usuarios.forEach(function (u) {
          var opt = document.createElement('option');
          opt.value = u.id;
          opt.textContent = u.nombre;
          if (String(u.id) === String(currentAsignado)) {
            opt.selected = true;
          }
          selectPerson.appendChild(opt);
        });
      })
      .catch(function (err) {
        console.warn('No se pudo cargar usuarios por área:', err);
      });
  }

  if (selectArea) {
    selectArea.addEventListener('change', function () {
      loadUsuariosPorArea(this.value);
    });
  }

  // --- Validación Bootstrap ---
  var form = document.getElementById('formTarea');
  if (form) {
    form.addEventListener('submit', function (e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  }
})();
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
