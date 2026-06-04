<?php require VIEW_PATH . "/layouts/header.php"; ?>

<?php
$isEdit = !empty($space);
$title = $isEdit ? 'Editar Espacio' : 'Nuevo Espacio';
$action = $isEdit ? BASE_URL . '/spaces/update/' . $space['id'] : BASE_URL . '/spaces/store';
?>

<!-- Mensajes flash -->
<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <i class="fa-solid fa-exclamation-circle"></i> <?= $_SESSION['flash_error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h3><i class="fa-solid fa-door-open text-primary"></i> <?= $title ?></h3>
  <a href="<?= BASE_URL ?>/spaces" class="btn btn-outline-secondary">
    <i class="fa-solid fa-arrow-left"></i> Volver
  </a>
</div>

<form method="post" action="<?= $action ?>" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

  <div class="row g-4">
    <!-- Columna izquierda: Datos generales -->
    <div class="col-lg-7">
      <div class="card">
        <div class="card-header bg-white">
          <h6 class="mb-0"><i class="fa-solid fa-info-circle text-primary"></i> Datos Generales</h6>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label fw-bold">Código</label>
              <input type="text" name="code" class="form-control" placeholder="Auto-generado"
                     value="<?= htmlspecialchars($space['code'] ?? '') ?>">
              <small class="text-muted">Dejar vacío para auto-generar</small>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required
                     value="<?= htmlspecialchars($space['name'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold">Color Calendario</label>
              <input type="color" name="calendar_color" class="form-control form-control-color w-100"
                     value="<?= $space['calendar_color'] ?? '#198754' ?>" title="Color para el calendario">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold">Tipo <span class="text-danger">*</span></label>
              <select name="space_type" class="form-select" required>
                <?php
                $types = ['salon' => '🏛️ Salón', 'sauna' => '🧖 Sauna', 'quincho' => '🔥 Quincho',
                           'oficina' => '🏢 Oficina', 'terraza' => '🌿 Terraza', 'otro' => '📦 Otro'];
                foreach ($types as $val => $label):
                ?>
                  <option value="<?= $val ?>" <?= ($space['space_type'] ?? '') === $val ? 'selected' : '' ?>>
                    <?= $label ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold">Capacidad Máxima</label>
              <div class="input-group">
                <input type="number" name="capacity" class="form-control" min="1"
                       value="<?= $space['capacity'] ?? '' ?>">
                <span class="input-group-text">pers.</span>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold">Hotel</label>
              <select name="hotel_id" class="form-select">
                <option value="">-- Sin asignar --</option>
                <?php foreach ($hotels as $h): ?>
                  <option value="<?= $h['id'] ?>" <?= ($space['hotel_id'] ?? '') == $h['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($h['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">Ubicación</label>
              <input type="text" name="location" class="form-control" placeholder="Ej: Piso 1, ala norte"
                     value="<?= htmlspecialchars($space['location'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">Descripción</label>
              <textarea name="description" class="form-control" rows="3"
                        placeholder="Descripción general del espacio"><?= htmlspecialchars($space['description'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Fotografías -->
      <div class="card mt-4">
        <div class="card-header bg-white">
          <h6 class="mb-0"><i class="fa-solid fa-camera text-info"></i> Fotografías del Espacio</h6>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Imagen Principal</label>
              <?php if (!empty($space['main_image'])): ?>
                <div class="mb-2 position-relative d-inline-block" id="main-image-container">
                  <img src="<?= BASE_URL . $space['main_image'] ?>" class="rounded shadow-sm" style="max-height: 120px;">
                  <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle border-white"
                          onclick="removeMainImage(<?= $space['id'] ?>)" title="Eliminar imagen actual">
                    <i class="fa-solid fa-times"></i>
                  </button>
                </div>
              <?php endif; ?>
              <input type="file" name="main_image" class="form-control" accept="image/jpeg,image/png,image/webp">
              <small class="text-muted">Foto que se mostrará en listados y como portada.</small>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Galería Adicional</label>
              <input type="file" name="gallery_photos[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
              <small class="text-muted">Opcional. Puedes seleccionar varias imágenes.</small>
            </div>

            <?php if (!empty($photos)): ?>
              <div class="col-12 mt-3">
                <label class="form-label fw-bold d-block mb-2">Galería actual</label>
                <div class="row row-cols-3 row-cols-md-4 g-2">
                  <?php foreach ($photos as $p): ?>
                    <div class="col" id="photo-<?= $p['id'] ?>">
                      <div class="position-relative border rounded p-1">
                        <img src="<?= BASE_URL . $p['file_path'] ?>" class="img-fluid rounded" style="height: 80px; width: 100%; object-fit: cover;">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 bg-danger text-white rounded-circle"
                                onclick="deletePhoto(<?= $p['id'] ?>)" title="Eliminar de la galería" style="width: 22px; height: 22px; padding: 0;">
                          <i class="fa-solid fa-times" style="font-size: 10px;"></i>
                        </button>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Restricciones y Equipamiento -->
      <div class="card mt-4">
        <div class="card-header bg-white">
          <h6 class="mb-0"><i class="fa-solid fa-exclamation-triangle text-warning"></i> Restricciones y Equipamiento</h6>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-bold">Equipamiento Incluido</label>
              <textarea name="included_equipment" class="form-control" rows="2"
                        placeholder="Ej: 20 sillas, 1 mesa, 1 datashow, pizarra"><?= htmlspecialchars($space['included_equipment'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">Restricciones de Uso</label>
              <textarea name="restrictions" class="form-control" rows="2"
                        placeholder="Ej: No se permite comida, aforo máximo 30 personas, no disponible fines de semana"><?= htmlspecialchars($space['restrictions'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Columna derecha: Precios y Modalidades -->
    <div class="col-lg-5">
      <div class="card">
        <div class="card-header bg-white">
          <h6 class="mb-0"><i class="fa-solid fa-dollar-sign text-success"></i> Modalidades y Precios Base</h6>
        </div>
        <div class="card-body">
          <!-- Por Hora -->
          <div class="p-3 border rounded mb-3">
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" name="allows_hourly" value="1" id="swHourly"
                     <?= ($space['allows_hourly'] ?? 1) ? 'checked' : '' ?>>
              <label class="form-check-label fw-bold" for="swHourly">
                <i class="fa-solid fa-clock text-info"></i> Arriendo por Hora
              </label>
            </div>
            <div class="input-group input-group-sm">
              <span class="input-group-text">$</span>
              <input type="number" name="base_price_hour" class="form-control" step="100"
                     value="<?= $space['base_price_hour'] ?? '' ?>" placeholder="Valor base/hora">
              <span class="input-group-text">/ hora</span>
            </div>
          </div>

          <!-- Por Día -->
          <div class="p-3 border rounded mb-3">
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" name="allows_daily" value="1" id="swDaily"
                     <?= ($space['allows_daily'] ?? 1) ? 'checked' : '' ?>>
              <label class="form-check-label fw-bold" for="swDaily">
                <i class="fa-solid fa-calendar-day text-primary"></i> Arriendo por Día
              </label>
            </div>
            <div class="input-group input-group-sm">
              <span class="input-group-text">$</span>
              <input type="number" name="base_price_day" class="form-control" step="100"
                     value="<?= $space['base_price_day'] ?? '' ?>" placeholder="Valor base/día">
              <span class="input-group-text">/ día</span>
            </div>
          </div>

          <!-- Por Mes -->
          <div class="p-3 border rounded mb-3">
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" name="allows_monthly" value="1" id="swMonthly"
                     <?= ($space['allows_monthly'] ?? 0) ? 'checked' : '' ?>>
              <label class="form-check-label fw-bold" for="swMonthly">
                <i class="fa-solid fa-calendar-alt text-warning"></i> Arriendo por Mes
              </label>
            </div>
            <div class="input-group input-group-sm">
              <span class="input-group-text">$</span>
              <input type="number" name="base_price_month" class="form-control" step="1000"
                     value="<?= $space['base_price_month'] ?? '' ?>" placeholder="Valor base/mes">
              <span class="input-group-text">/ mes</span>
            </div>
          </div>
        </div>
      </div>

      <?php if ($isEdit): ?>
        <div class="card mt-4">
          <div class="card-body">
            <label class="form-label fw-bold">Estado</label>
            <select name="active" class="form-select">
              <option value="1" <?= $space['active'] ? 'selected' : '' ?>>✅ Activo</option>
              <option value="0" <?= !$space['active'] ? 'selected' : '' ?>>🚫 Inactivo</option>
            </select>
          </div>
        </div>
      <?php endif; ?>

      <!-- Botones -->
      <div class="card mt-4">
        <div class="card-body d-grid gap-2">
          <button type="submit" class="btn btn-atk btn-lg">
            <i class="fa-solid fa-save"></i> <?= $isEdit ? 'Guardar Cambios' : 'Crear Espacio' ?>
          </button>
          <a href="<?= BASE_URL ?>/spaces" class="btn btn-outline-secondary">Cancelar</a>
        </div>
      </div>
    </div>
  </div>
</form>


<script>
function removeMainImage(id) {
  if (!confirm('¿Seguro que deseas eliminar la imagen principal?')) return;
  fetch(`<?= BASE_URL ?>/spaces/removeMainImage/${id}`)
    .then(r => r.json())
    .then(data => {
      if (data.status) {
        document.getElementById('main-image-container').remove();
      } else {
        alert(data.message);
      }
    });
}

function deletePhoto(id) {
  if (!confirm('¿Seguro que deseas eliminar esta foto de la galería?')) return;
  fetch(`<?= BASE_URL ?>/spaces/deletePhoto/${id}`)
    .then(r => r.json())
    .then(data => {
      if (data.status) {
        document.getElementById('photo-' + id).remove();
      } else {
        alert(data.message);
      }
    });
}
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
