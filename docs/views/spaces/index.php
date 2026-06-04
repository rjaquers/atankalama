<?php require VIEW_PATH . "/layouts/header.php"; ?>

<!-- Mensajes flash -->
<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <i class="fa-solid fa-exclamation-circle"></i> <?= $_SESSION['flash_error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3><i class="fa-solid fa-door-open text-primary"></i> Espacios Arrendables</h3>
    <p class="text-muted mb-0">Gestión de salones, sauna, quincho, oficinas y más</p>
  </div>
  <div class="d-flex gap-2">
    <?php if (AuthService::hasPermission('spaces_manage')): ?>
      <a href="<?= BASE_URL ?>/spaces/extras" class="btn btn-outline-primary">
        <i class="fa-solid fa-puzzle-piece"></i> Extras
      </a>
      <a href="<?= BASE_URL ?>/spaces/create" class="btn btn-atk">
        <i class="fa-solid fa-plus"></i> Nuevo Espacio
      </a>
    <?php endif; ?>
  </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
  <div class="card-body py-2">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label small mb-0">Tipo</label>
        <select name="space_type" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="salon" <?= ($filters['space_type'] ?? '') === 'salon' ? 'selected' : '' ?>>🏛️ Salón</option>
          <option value="sauna" <?= ($filters['space_type'] ?? '') === 'sauna' ? 'selected' : '' ?>>🧖 Sauna</option>
          <option value="quincho" <?= ($filters['space_type'] ?? '') === 'quincho' ? 'selected' : '' ?>>🔥 Quincho</option>
          <option value="oficina" <?= ($filters['space_type'] ?? '') === 'oficina' ? 'selected' : '' ?>>🏢 Oficina</option>
          <option value="terraza" <?= ($filters['space_type'] ?? '') === 'terraza' ? 'selected' : '' ?>>🌿 Terraza</option>
          <option value="otro" <?= ($filters['space_type'] ?? '') === 'otro' ? 'selected' : '' ?>>📦 Otro</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-0">Estado</label>
        <select name="active" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="1" <?= (isset($filters['active']) && $filters['active'] == 1) ? 'selected' : '' ?>>Activo</option>
          <option value="0" <?= (isset($filters['active']) && $filters['active'] == 0) ? 'selected' : '' ?>>Inactivo</option>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa-solid fa-filter"></i> Filtrar</button>
        <a href="<?= BASE_URL ?>/spaces" class="btn btn-sm btn-outline-secondary">Limpiar</a>
      </div>
    </form>
  </div>
</div>

<!-- Tabla -->
<div class="card">
  <div class="card-body">
    <?php if (empty($spaces)): ?>
      <div class="text-center text-muted py-4">
        <i class="fa-solid fa-door-open fa-3x mb-3 opacity-25"></i>
        <p>No hay espacios registrados</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle" id="tableSpaces">
          <thead class="table-light">
            <tr>
              <th></th>
              <th>Código</th>
              <th>Nombre</th>
              <th>Tipo</th>
              <th class="text-center">Capacidad</th>
              <th>Hotel</th>
              <th>Modalidades</th>
              <th class="text-center">Estado</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $typeIcons = [
              'salon' => '🏛️', 'sauna' => '🧖', 'quincho' => '🔥',
              'oficina' => '🏢', 'terraza' => '🌿', 'otro' => '📦'
            ];
            foreach ($spaces as $s):
              $icon = $typeIcons[$s['space_type']] ?? '📦';
            ?>
              <tr>
                <td>
                  <img src="<?= $s['main_image_url'] ?>" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                </td>
                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($s['code']) ?></span></td>
                <td class="fw-bold">
                  <a href="<?= BASE_URL ?>/spaces/show/<?= $s['id'] ?>" class="text-decoration-none">
                    <?= htmlspecialchars($s['name']) ?>
                  </a>
                </td>
                <td><?= $icon ?> <?= ucfirst($s['space_type']) ?></td>
                <td class="text-center"><?= $s['capacity'] ? $s['capacity'] . ' pers.' : '-' ?></td>
                <td><?= htmlspecialchars($s['hotel_name'] ?? '-') ?></td>
                <td>
                  <?php if ($s['allows_hourly']): ?><span class="badge bg-info me-1">Hora</span><?php endif; ?>
                  <?php if ($s['allows_daily']): ?><span class="badge bg-primary me-1">Día</span><?php endif; ?>
                  <?php if ($s['allows_monthly']): ?><span class="badge bg-warning text-dark">Mes</span><?php endif; ?>
                </td>
                <td class="text-center">
                  <?php if ($s['active']): ?>
                    <span class="badge bg-success">Activo</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Inactivo</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <a href="<?= BASE_URL ?>/spaces/show/<?= $s['id'] ?>" class="btn btn-outline-primary" title="Ver">
                      <i class="fa-solid fa-eye"></i>
                    </a>
                    <?php if (AuthService::hasPermission('spaces_manage')): ?>
                      <a href="<?= BASE_URL ?>/spaces/edit/<?= $s['id'] ?>" class="btn btn-outline-warning" title="Editar">
                        <i class="fa-solid fa-pen"></i>
                      </a>
                    <?php endif; ?>
                  </div>
                </td>
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
    if ($('#tableSpaces').length) {
      $('#tableSpaces').DataTable({ pageLength: 25 });
    }
  });
</script>
