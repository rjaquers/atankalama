<?php require VIEW_PATH . "/layouts/header.php"; ?>

<!-- Mensajes flash -->
<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php
$typeIcons = ['salon' => '🏛️', 'sauna' => '🧖', 'quincho' => '🔥', 'oficina' => '🏢', 'terraza' => '🌿', 'otro' => '📦'];
$icon = $typeIcons[$space['space_type']] ?? '📦';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3>
      <?= $icon ?> <?= htmlspecialchars($space['name']) ?>
      <span class="badge bg-light text-dark border ms-2"><?= htmlspecialchars($space['code']) ?></span>
      <?php if ($space['active']): ?>
        <span class="badge bg-success ms-1">Activo</span>
      <?php else: ?>
        <span class="badge bg-secondary ms-1">Inactivo</span>
      <?php endif; ?>
    </h3>
  </div>
  <div class="d-flex gap-2">
    <?php if (AuthService::hasPermission('spaces_manage')): ?>
      <a href="<?= BASE_URL ?>/spaces/edit/<?= $space['id'] ?>" class="btn btn-warning btn-sm">
        <i class="fa-solid fa-pen"></i> Editar
      </a>
    <?php endif; ?>
    <?php if (AuthService::hasPermission('bookings_create')): ?>
      <a href="<?= BASE_URL ?>/bookings/create?space_id=<?= $space['id'] ?>" class="btn btn-atk btn-sm">
        <i class="fa-solid fa-plus"></i> Nueva Reserva
      </a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/spaces" class="btn btn-outline-secondary btn-sm">
      <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
  </div>
</div>

<div class="row g-4">
  <!-- Datos generales -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fa-solid fa-info-circle text-primary"></i> Información General</h6>
      </div>
      <div class="card-body">
        <table class="table table-borderless mb-0">
          <tr>
            <td class="text-muted" style="width: 40%;">Tipo:</td>
            <td>
              <span class="badge bg-primary"><?= $icon ?> <?= ucfirst($space['space_type']) ?></span>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Capacidad:</td>
            <td class="fw-bold"><?= $space['capacity'] ? $space['capacity'] . ' personas' : 'No definida' ?></td>
          </tr>
          <tr>
            <td class="text-muted">Ubicación:</td>
            <td><?= htmlspecialchars($space['location'] ?: '-') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Hotel:</td>
            <td><?= htmlspecialchars($space['hotel_name'] ?: '-') ?></td>
          </tr>
          <?php if (!empty($space['description'])): ?>
          <tr>
            <td class="text-muted">Descripción:</td>
            <td><?= nl2br(htmlspecialchars($space['description'])) ?></td>
          </tr>
          <?php endif; ?>
          <tr>
            <td class="text-muted">Creado por:</td>
            <td>
              <?= htmlspecialchars($space['created_by_name'] ?? 'Sistema') ?>
              <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($space['created_at'])) ?></small>
            </td>
          </tr>
        </table>
      </div>
    </div>

    <!-- Equipamiento y Restricciones -->
    <?php if (!empty($space['included_equipment']) || !empty($space['restrictions'])): ?>
    <div class="card mt-4">
      <div class="card-body">
        <?php if (!empty($space['included_equipment'])): ?>
          <h6 class="fw-bold"><i class="fa-solid fa-box-open text-success"></i> Equipamiento Incluido</h6>
          <p class="text-muted mb-3" style="white-space: pre-wrap;"><?= htmlspecialchars($space['included_equipment']) ?></p>
        <?php endif; ?>
        <?php if (!empty($space['restrictions'])): ?>
          <h6 class="fw-bold"><i class="fa-solid fa-exclamation-triangle text-warning"></i> Restricciones</h6>
          <div class="alert alert-warning mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($space['restrictions']) ?></div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Precios -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fa-solid fa-dollar-sign text-success"></i> Precios Referenciales</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <?php if ($space['allows_hourly']): ?>
          <div class="col-md-4">
            <div class="p-3 rounded text-center" style="background: var(--atk-bg-subtle);">
              <small class="text-muted d-block"><i class="fa-solid fa-clock"></i> Hora</small>
              <strong class="fs-5 text-primary">$<?= number_format((float)$space['base_price_hour'], 0, ',', '.') ?></strong>
            </div>
          </div>
          <?php endif; ?>
          <?php if ($space['allows_daily']): ?>
          <div class="col-md-4">
            <div class="p-3 rounded text-center" style="background: var(--atk-bg-subtle);">
              <small class="text-muted d-block"><i class="fa-solid fa-calendar-day"></i> Día</small>
              <strong class="fs-5 text-primary">$<?= number_format((float)$space['base_price_day'], 0, ',', '.') ?></strong>
            </div>
          </div>
          <?php endif; ?>
          <?php if ($space['allows_monthly']): ?>
          <div class="col-md-4">
            <div class="p-3 rounded text-center" style="background: var(--atk-bg-subtle);">
              <small class="text-muted d-block"><i class="fa-solid fa-calendar-alt"></i> Mes</small>
              <strong class="fs-5 text-primary">$<?= number_format((float)$space['base_price_month'], 0, ',', '.') ?></strong>
            </div>
          </div>
          <?php endif; ?>
        </div>
        <div class="mt-3">
          <small class="text-muted">Modalidades habilitadas:</small><br>
          <?php if ($space['allows_hourly']): ?><span class="badge bg-info me-1">Hora</span><?php endif; ?>
          <?php if ($space['allows_daily']): ?><span class="badge bg-primary me-1">Día</span><?php endif; ?>
          <?php if ($space['allows_monthly']): ?><span class="badge bg-warning text-dark">Mes</span><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Galería de Fotos (Añadido por requerimiento) -->
<div class="card mt-4 shadow-sm border-0">
  <div class="card-header bg-white py-3 border-0">
    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-images text-info me-2"></i> Galería de Fotos</h6>
  </div>
  <div class="card-body">
    <div class="row g-3">
      <?php if (!empty($space['main_image'])): ?>
        <div class="col-md-5">
          <div class="position-relative">
            <a href="<?= BASE_URL . $space['main_image'] ?>" data-fancybox="gallery" data-caption="Imagen Principal">
              <img src="<?= BASE_URL . $space['main_image'] ?>" class="img-fluid rounded shadow-sm border w-100" style="height: 300px; object-fit: cover;">
            </a>
            <div class="p-2 border border-top-0 rounded-bottom bg-light text-center small text-muted">
              <i class="fa-solid fa-star text-warning"></i> Foto Principal
            </div>
          </div>
        </div>
        <div class="col-md-7">
          <div class="row g-2">
            <?php if (!empty($photos)): ?>
              <?php foreach ($photos as $p): ?>
                <div class="col-md-4 col-6">
                  <a href="<?= $p['url'] ?>" data-fancybox="gallery" data-caption="<?= htmlspecialchars($p['original_name']) ?>">
                    <img src="<?= $p['url'] ?>" class="img-fluid rounded shadow-sm border w-100" style="height: 145px; object-fit: cover;">
                  </a>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12 text-center py-5">
                 <p class="text-muted small">No hay fotos adicionales en la galería.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <?php if (!empty($photos)): ?>
          <div class="col-12">
            <div class="row g-2">
              <?php foreach ($photos as $p): ?>
                <div class="col-md-3 col-6">
                  <a href="<?= $p['url'] ?>" data-fancybox="gallery" data-caption="<?= htmlspecialchars($p['original_name']) ?>">
                    <img src="<?= $p['url'] ?>" class="img-fluid rounded shadow-sm border w-100" style="height: 180px; object-fit: cover;">
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php else: ?>
          <div class="col-12 text-center py-5">
            <i class="fa-solid fa-camera-retro fa-3x text-light mb-3"></i>
            <p class="text-muted">No se han cargado fotografías para este espacio.</p>
            <?php if (AuthService::hasPermission('spaces_manage')): ?>
              <a href="<?= BASE_URL ?>/spaces/edit/<?= $space['id'] ?>" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-upload"></i> Subir Fotos
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Reservas recientes -->
<div class="card mt-4">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h6 class="mb-0"><i class="fa-solid fa-calendar-check text-primary"></i> Reservas de este espacio (<?= count($bookings) ?>)</h6>
  </div>
  <div class="card-body">
    <?php if (empty($bookings)): ?>
      <p class="text-muted mb-0"><i class="fa-solid fa-info-circle"></i> No hay reservas registradas para este espacio.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle" id="tableSpaceBookings">
          <thead class="table-light">
            <tr>
              <th>Folio</th>
              <th>Fecha/Hora</th>
              <th>Empresa</th>
              <th class="text-end">Total</th>
              <th class="text-center">Estado</th>
              <th class="text-center">Cobro</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $statusColors = [
              'borrador' => 'bg-secondary', 'confirmada' => 'bg-success',
              'en_uso' => 'bg-info', 'finalizada' => 'bg-dark',
              'cancelada' => 'bg-danger', 'no_asistio' => 'bg-warning text-dark'
            ];
            foreach ($bookings as $b):
              $bColor = $statusColors[$b['booking_status']] ?? 'bg-secondary';
            ?>
              <tr>
                <td>
                  <a href="<?= BASE_URL ?>/bookings/show/<?= $b['id'] ?>" class="text-decoration-none fw-bold">
                    <?= htmlspecialchars($b['folio']) ?>
                  </a>
                </td>
                <td>
                  <?= date('d/m/Y H:i', strtotime($b['start_datetime'])) ?> — 
                  <?= date('H:i', strtotime($b['end_datetime'])) ?>
                </td>
                <td><?= htmlspecialchars($b['company_name'] ?? $b['client_name'] ?? '-') ?></td>
                <td class="text-end fw-bold">
                  <?= $b['is_free'] ? '<span class="badge bg-secondary">Gratis</span>' : '$' . number_format((float)$b['total_price'], 0, ',', '.') ?>
                </td>
                <td class="text-center"><span class="badge <?= $bColor ?>"><?= ucfirst(str_replace('_', ' ', $b['booking_status'])) ?></span></td>
                <td class="text-center"><span class="badge bg-light text-dark border"><?= ucfirst(str_replace('_', ' ', $b['charge_status'])) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>

<script>
  $(document).ready(function() {
    if ($('#tableSpaceBookings').length) {
      $('#tableSpaceBookings').DataTable({ pageLength: 15 });
    }
  });
</script>
