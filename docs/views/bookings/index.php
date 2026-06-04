<?php require VIEW_PATH . "/layouts/header.php"; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3><i class="fa-solid fa-calendar-check text-primary"></i> Reservas de Espacios</h3>
    <p class="text-muted mb-0">Gestión de arriendos de salones, sauna, quincho y más</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= BASE_URL ?>/bookings/calendar" class="btn btn-outline-primary">
      <i class="fa-solid fa-calendar"></i> Calendario
    </a>
    <?php if (AuthService::hasPermission('bookings_create')): ?>
      <a href="<?= BASE_URL ?>/bookings/create" class="btn btn-atk">
        <i class="fa-solid fa-plus"></i> Nueva Reserva
      </a>
    <?php endif; ?>
  </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
  <div class="card-body py-2">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-2">
        <label class="form-label small mb-0">Espacio</label>
        <select name="space_id" class="form-select form-select-sm">
          <option value="">Todos</option>
          <?php foreach ($spaces as $s): ?>
            <option value="<?= $s['id'] ?>" <?= ($filters['space_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($s['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-0">Estado</label>
        <select name="booking_status" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="confirmada" <?= ($filters['booking_status'] ?? '') === 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
          <option value="en_uso" <?= ($filters['booking_status'] ?? '') === 'en_uso' ? 'selected' : '' ?>>En Uso</option>
          <option value="finalizada" <?= ($filters['booking_status'] ?? '') === 'finalizada' ? 'selected' : '' ?>>Finalizada</option>
          <option value="cancelada" <?= ($filters['booking_status'] ?? '') === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-0">Desde</label>
        <input type="date" name="date_from" class="form-control form-control-sm" value="<?= $filters['date_from'] ?? '' ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-0">Hasta</label>
        <input type="date" name="date_to" class="form-control form-control-sm" value="<?= $filters['date_to'] ?? '' ?>">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa-solid fa-filter"></i> Filtrar</button>
        <a href="<?= BASE_URL ?>/bookings" class="btn btn-sm btn-outline-secondary">Limpiar</a>
      </div>
    </form>
  </div>
</div>

<!-- Tabla -->
<div class="card">
  <div class="card-body">
    <?php if (empty($bookings)): ?>
      <div class="text-center text-muted py-4">
        <i class="fa-solid fa-calendar-xmark fa-3x mb-3 opacity-25"></i>
        <p>No hay reservas registradas con los filtros seleccionados</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle" id="tableBookings">
          <thead class="table-light">
            <tr>
              <th>Folio</th>
              <th>Espacio</th>
              <th>Fecha/Hora</th>
              <th>Empresa / Cliente</th>
              <th class="text-end">Total</th>
              <th class="text-center">Estado</th>
              <th class="text-center">Cobro</th>
              <th class="text-center">Acciones</th>
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
                  <span class="badge bg-light text-dark border"><?= htmlspecialchars($b['space_code']) ?></span>
                  <?= htmlspecialchars($b['space_name']) ?>
                </td>
                <td>
                  <small>
                    <?= date('d/m/Y', strtotime($b['start_datetime'])) ?>
                    <span class="text-muted"><?= date('H:i', strtotime($b['start_datetime'])) ?> – <?= date('H:i', strtotime($b['end_datetime'])) ?></span>
                  </small>
                </td>
                <td><?= htmlspecialchars($b['company_name'] ?? $b['client_name'] ?? '-') ?></td>
                <td class="text-end fw-bold">
                  <?php if ($b['is_free']): ?>
                    <span class="badge bg-secondary">Gratis</span>
                  <?php else: ?>
                    $<?= number_format((float)$b['total_price'], 0, ',', '.') ?>
                  <?php endif; ?>
                </td>
                <td class="text-center"><span class="badge <?= $bColor ?>"><?= ucfirst(str_replace('_', ' ', $b['booking_status'])) ?></span></td>
                <td class="text-center"><span class="badge bg-light text-dark border"><?= ucfirst(str_replace('_', ' ', $b['charge_status'])) ?></span></td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <a href="<?= BASE_URL ?>/bookings/show/<?= $b['id'] ?>" class="btn btn-outline-primary" title="Ver">
                      <i class="fa-solid fa-eye"></i>
                    </a>
                    <?php if (AuthService::hasPermission('bookings_edit') && $b['booking_status'] !== 'cancelada'): ?>
                      <a href="<?= BASE_URL ?>/bookings/edit/<?= $b['id'] ?>" class="btn btn-outline-warning" title="Editar">
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
    if ($('#tableBookings').length) {
      $('#tableBookings').DataTable({ pageLength: 25 });
    }
  });
</script>
