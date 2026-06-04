<?php require VIEW_PATH . '/layouts/header.php'; ?>

<?php
$rolActual = $_SESSION['user_rol'] ?? '';
$esAdmin   = in_array($rolActual, ['Administrador', 'Jefe de Área'], true);
$esEdicion = isset($mant) && is_array($mant);
$actionUrl = $esEdicion
    ? BASE_URL . '/mantencion/editar/' . $mant['id']
    : BASE_URL . '/mantencion/crear';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/mantencion">Mantención</a></li>
    <?php if ($esEdicion): ?>
      <li class="breadcrumb-item">
        <a href="<?= BASE_URL ?>/mantencion/ver/<?= $mant['id'] ?>">
          <?= htmlspecialchars($mant['titulo']) ?>
        </a>
      </li>
    <?php endif; ?>
    <li class="breadcrumb-item active"><?= $esEdicion ? 'Editar' : 'Nueva mantención' ?></li>
  </ol>
</nav>

<div class="row justify-content-center">
  <div class="col-lg-9 col-xl-8">

    <div class="form-card">
      <h5 class="fw-bold mb-4">
        <i class="bi bi-<?= $esEdicion ? 'pencil-square' : 'plus-circle-fill' ?> text-primary me-2"></i>
        <?= $esEdicion ? 'Editar mantención' : 'Nueva orden de mantención' ?>
      </h5>

      <form method="POST"
            action="<?= $actionUrl ?>"
            enctype="multipart/form-data"
            id="formMantencion">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <div class="row g-3">

          <!-- Título -->
          <div class="col-12">
            <label class="form-label fw-semibold">
              Título <span class="text-danger">*</span>
            </label>
            <input type="text"
                   name="titulo"
                   class="form-control"
                   placeholder="Ej: Reparación calefacción habitación 203"
                   value="<?= htmlspecialchars($mant['titulo'] ?? '') ?>"
                   required
                   maxlength="200">
          </div>

          <!-- Descripción -->
          <div class="col-12">
            <label class="form-label fw-semibold">Descripción</label>
            <textarea name="descripcion"
                      class="form-control"
                      rows="3"
                      placeholder="Describe el problema o tarea de mantención con detalle..."
                      style="resize:vertical"><?= htmlspecialchars($mant['descripcion'] ?? '') ?></textarea>
          </div>

          <!-- Ubicación -->
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">
              <i class="bi bi-geo-alt me-1 text-danger"></i>Ubicación
            </label>
            <input type="text"
                   name="ubicacion"
                   class="form-control"
                   placeholder="Ej: Habitación 203, Piscina, Cocina, Lobby..."
                   value="<?= htmlspecialchars($mant['ubicacion'] ?? '') ?>"
                   maxlength="150">
          </div>

          <!-- Tipo -->
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
            <select name="tipo" class="form-select" id="selectTipo" required>
              <option value="correctiva" <?= (($mant['tipo'] ?? '') === 'correctiva') ? 'selected' : '' ?>>
                Correctiva — Reparar falla existente
              </option>
              <option value="preventiva" <?= (($mant['tipo'] ?? '') === 'preventiva') ? 'selected' : '' ?>>
                Preventiva — Mantenimiento programado
              </option>
              <option value="emergencia" <?= (($mant['tipo'] ?? '') === 'emergencia') ? 'selected' : '' ?>>
                Emergencia — Requiere atención inmediata
              </option>
            </select>
          </div>

          <!-- Prioridad -->
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Prioridad <span class="text-danger">*</span></label>
            <select name="prioridad" class="form-select" required>
              <option value="baja"    <?= (($mant['prioridad'] ?? 'media') === 'baja')    ? 'selected' : '' ?>>Baja</option>
              <option value="media"   <?= (($mant['prioridad'] ?? 'media') === 'media')   ? 'selected' : '' ?>>Media</option>
              <option value="alta"    <?= (($mant['prioridad'] ?? 'media') === 'alta')    ? 'selected' : '' ?>>Alta</option>
              <option value="urgente" <?= (($mant['prioridad'] ?? 'media') === 'urgente') ? 'selected' : '' ?>>Urgente</option>
            </select>
          </div>

          <!-- Área -->
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Área</label>
            <select name="area_id" class="form-select" id="selectArea">
              <option value="">Sin área asignada</option>
              <?php foreach ($areas as $area): ?>
              <option value="<?= $area['id'] ?>"
                      <?= (($mant['area_id'] ?? '') == $area['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($area['nombre']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Asignado a -->
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Asignado a</label>
            <select name="asignado_a" class="form-select" id="selectAsignado">
              <option value="">Sin asignar</option>
              <?php foreach ($usuarios as $usr): ?>
              <option value="<?= $usr['id'] ?>"
                      data-area="<?= $usr['area_id'] ?? '' ?>"
                      <?= (($mant['asignado_a'] ?? '') == $usr['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($usr['nombre']) ?>
                <?php if (!empty($usr['area_nombre'])): ?>
                  — <?= htmlspecialchars($usr['area_nombre']) ?>
                <?php endif; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Fecha programada (visible solo si tipo=preventiva) -->
          <div class="col-12 col-md-6" id="wrapFechaProgramada"
               style="<?= (($mant['tipo'] ?? '') !== 'preventiva') ? 'display:none' : '' ?>">
            <label class="form-label fw-semibold">
              <i class="bi bi-calendar3 me-1"></i>Fecha programada
            </label>
            <input type="date"
                   name="fecha_programada"
                   class="form-control"
                   value="<?= htmlspecialchars($mant['fecha_programada'] ?? '') ?>">
          </div>

          <!-- Costo estimado (solo admin/jefe) -->
          <?php if ($esAdmin): ?>
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">
              <i class="bi bi-currency-dollar text-warning me-1"></i>Costo estimado ($)
            </label>
            <input type="number"
                   name="costo_estimado"
                   class="form-control"
                   step="1"
                   min="0"
                   placeholder="0"
                   value="<?= htmlspecialchars($mant['costo_estimado'] ?? '') ?>">
          </div>
          <?php endif; ?>

          <?php if (!$esEdicion): ?>
          <!-- Fotos iniciales -->
          <div class="col-12">
            <hr>
            <label class="form-label fw-semibold">
              <i class="bi bi-camera-fill text-primary me-1"></i>Fotos iniciales
              <small class="text-muted fw-normal">(opcional)</small>
            </label>
            <div class="row g-2">
              <div class="col-12 col-sm-5">
                <select name="momento_fotos" class="form-select form-select-sm">
                  <option value="antes">Antes de iniciar</option>
                  <option value="durante">Durante el trabajo</option>
                </select>
              </div>
              <div class="col-12 col-sm-7">
                <input type="file"
                       name="fotos[]"
                       class="form-control form-control-sm"
                       accept="image/*"
                       multiple
                       id="inputFotosIniciales">
              </div>
            </div>
            <div class="photo-preview" id="previewFotosIniciales"></div>
          </div>
          <?php endif; ?>

        </div><!-- /row g-3 -->

        <!-- Botones -->
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
          <a href="<?= $esEdicion ? BASE_URL . '/mantencion/ver/' . $mant['id'] : BASE_URL . '/mantencion' ?>"
             class="btn btn-outline-secondary">
            <i class="bi bi-x-lg me-1"></i>Cancelar
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-<?= $esEdicion ? 'check-lg' : 'plus-lg' ?> me-1"></i>
            <?= $esEdicion ? 'Guardar cambios' : 'Crear mantención' ?>
          </button>
        </div>

      </form>
    </div><!-- /form-card -->

  </div>
</div>

<script>
// Toggle fecha programada segun tipo
document.getElementById('selectTipo').addEventListener('change', function () {
  var wrap = document.getElementById('wrapFechaProgramada');
  if (this.value === 'preventiva') {
    wrap.style.display = '';
  } else {
    wrap.style.display = 'none';
    wrap.querySelector('input[type=date]').value = '';
  }
});

// Preview fotos iniciales
var inputFotos = document.getElementById('inputFotosIniciales');
if (inputFotos) {
  inputFotos.addEventListener('change', function () {
    var preview = document.getElementById('previewFotosIniciales');
    preview.innerHTML = '';
    Array.from(this.files).forEach(function (file) {
      var reader = new FileReader();
      reader.onload = function (e) {
        var img = document.createElement('img');
        img.src = e.target.result;
        preview.appendChild(img);
      };
      reader.readAsDataURL(file);
    });
  });
}

// Filtrar asignados por área (ocultar/mostrar options)
function filtrarAsignados() {
  var areaId  = document.getElementById('selectArea').value;
  var select  = document.getElementById('selectAsignado');
  var options = select.querySelectorAll('option[data-area]');
  options.forEach(function (opt) {
    var optArea = opt.getAttribute('data-area');
    // Mostrar siempre si no hay área seleccionada, o si coincide, o si el usuario no tiene área
    var visible = !areaId || !optArea || optArea === areaId;
    opt.style.display = visible ? '' : 'none';
  });
  // Si el seleccionado quedó oculto, reset
  var selOpt = select.options[select.selectedIndex];
  if (selOpt && selOpt.style.display === 'none') {
    select.value = '';
  }
}
document.getElementById('selectArea').addEventListener('change', filtrarAsignados);
filtrarAsignados();
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
